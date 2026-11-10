use axum::{
    extract::State,
    http::StatusCode,
    response::IntoResponse,
    routing::{get, post},
    Json, Router,
};
use chrono::{Duration, Utc};
use jsonwebtoken::{encode, EncodingKey, Header};
use uuid::Uuid;

use crate::{
    error::{AppError, AppResult},
    middleware::auth::AuthUser,
    models::auth::{
        AuthResponse, AuthResponseData, Claims, GetTokenRequest, GetTokenResponse,
        LoginRequest, MeResponse, RegisterRequest, User, UserRole,
    },
    state::AppState,
};

pub fn router() -> Router<AppState> {
    Router::new()
        .route("/register", post(register))
        .route("/login", post(login))
        .route("/refresh", post(refresh))
        .route("/get-token", post(get_token))
        .route("/me", get(me))
}

fn generate_token(user: &User, secret: &str, ttl: i64) -> Result<String, AppError> {
    let exp = Utc::now()
        .checked_add_signed(Duration::seconds(ttl))
        .expect("valid timestamp")
        .timestamp() as usize;

    let claims = Claims {
        sub: user.id.to_string(),
        uid: user.uid.clone(),
        email: user.email.clone(),
        tv: user.token_version,
        exp,
    };

    encode(
        &Header::default(),
        &claims,
        &EncodingKey::from_secret(secret.as_bytes()),
    )
    .map_err(|e| AppError::Internal(format!("Failed to sign JWT: {}", e)))
}

#[utoipa::path(
    post,
    path = "/api/v1/register",
    tag = "Auth",
    request_body = RegisterRequest,
    responses(
        (status = 201, description = "Berhasil mendaftar", body = AuthResponse),
        (status = 400, description = "Bad Request"),
        (status = 500, description = "Internal Server Error")
    )
)]
pub async fn register(
    State(state): State<AppState>,
    Json(payload): Json<RegisterRequest>,
) -> AppResult<impl IntoResponse> {
    let email = payload.email.trim().to_lowercase();
    if email.is_empty() {
        return Err(AppError::BadRequest("Email is required".into()));
    }

    let mut users = state.users.write().await;
    if users.iter().any(|u| u.email == email) {
        return Err(AppError::BadRequest("Email sudah terdaftar".into()));
    }

    let password = payload.password.as_deref().unwrap_or("password123");
    let password_hash = bcrypt::hash(password, 4)
        .map_err(|e| AppError::Internal(format!("Hash error: {}", e)))?;

    let new_id = (users.len() as i64) + 1;
    let uid = payload.uid.unwrap_or_else(|| Uuid::new_v4().to_string());

    let user = User {
        id: new_id,
        uid: uid.clone(),
        name: payload.name.clone(),
        email: email.clone(),
        password_hash,
        nik: payload.nik,
        no_hp: payload.no_hp,
        token_version: 0,
        created_at: Utc::now().to_rfc3339(),
    };

    let token = generate_token(&user, &state.config.jwt_secret, state.config.jwt_ttl_seconds)?;
    users.push(user.clone());

    // Persist new registered user to MySQL if available
    if let Some(ref pool) = state.db {
        let _ = sqlx::query(
            "INSERT INTO users (uid, name, email, password, nik, no_hp, created_at, updated_at) 
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
        )
        .bind(&user.uid)
        .bind(&user.name)
        .bind(&user.email)
        .bind(&user.password_hash)
        .bind(&user.nik)
        .bind(&user.no_hp)
        .execute(pool)
        .await;
    }

    let response = AuthResponse {
        message: "Berhasil mendaftar".to_string(),
        token: token.clone(),
        access_token: token,
        token_type: "bearer".to_string(),
        expires_in: state.config.jwt_ttl_seconds,
        data: AuthResponseData {
            id: user.id,
            uid: user.uid,
            name: user.name,
            email: user.email,
        },
    };

    Ok((StatusCode::CREATED, Json(response)))
}

#[utoipa::path(
    post,
    path = "/api/v1/login",
    tag = "Auth",
    request_body = LoginRequest,
    responses(
        (status = 200, description = "Login berhasil", body = AuthResponse),
        (status = 401, description = "Unauthorized"),
        (status = 500, description = "Internal Server Error")
    )
)]
pub async fn login(
    State(state): State<AppState>,
    Json(payload): Json<LoginRequest>,
) -> AppResult<impl IntoResponse> {
    let email = payload.email.trim().to_lowercase();
    let is_google = payload.method.as_deref() == Some("google");

    let mut users = state.users.write().await;

    let user = if is_google {
        if let Some(existing) = users.iter().find(|u| u.email == email) {
            existing.clone()
        } else {
            let new_id = (users.len() as i64) + 1;
            let uid = Uuid::new_v4().to_string();
            let new_user = User {
                id: new_id,
                uid,
                name: email.split('@').next().unwrap_or("User").to_string(),
                email: email.clone(),
                password_hash: bcrypt::hash("google_auth", 4).unwrap(),
                nik: None,
                no_hp: None,
                token_version: 0,
                created_at: Utc::now().to_rfc3339(),
            };
            users.push(new_user.clone());
            new_user
        }
    } else {
        let user = users
            .iter()
            .find(|u| u.email == email)
            .ok_or_else(|| AppError::Unauthorized("Username atau Password salah".into()))?;

        let password = payload.password.as_deref().unwrap_or("");
        let matches = bcrypt::verify(password, &user.password_hash).unwrap_or(false);
        if !matches {
            return Err(AppError::Unauthorized("Username atau Password salah".into()));
        }

        user.clone()
    };

    let token = generate_token(&user, &state.config.jwt_secret, state.config.jwt_ttl_seconds)?;

    let response = AuthResponse {
        message: "Login berhasil".to_string(),
        token: token.clone(),
        access_token: token,
        token_type: "bearer".to_string(),
        expires_in: state.config.jwt_ttl_seconds,
        data: AuthResponseData {
            id: user.id,
            uid: user.uid,
            name: user.name,
            email: user.email,
        },
    };

    Ok(Json(response))
}

#[utoipa::path(
    post,
    path = "/api/v1/refresh",
    tag = "Auth",
    security(
        ("bearer_auth" = [])
    ),
    responses(
        (status = 200, description = "Token refreshed", body = AuthResponse),
        (status = 401, description = "Unauthorized")
    )
)]
pub async fn refresh(
    auth_user: AuthUser,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let user = users
        .iter()
        .find(|u| u.id == auth_user.id)
        .ok_or_else(|| AppError::NotFound("User tidak ditemukan".into()))?;

    let token = generate_token(user, &state.config.jwt_secret, state.config.jwt_ttl_seconds)?;

    let response = AuthResponse {
        message: "Token refreshed".to_string(),
        token: token.clone(),
        access_token: token,
        token_type: "bearer".to_string(),
        expires_in: state.config.jwt_ttl_seconds,
        data: AuthResponseData {
            id: user.id,
            uid: user.uid.clone(),
            name: user.name.clone(),
            email: user.email.clone(),
        },
    };

    Ok(Json(response))
}

#[utoipa::path(
    post,
    path = "/api/v1/get-token",
    tag = "Auth",
    request_body = GetTokenRequest,
    responses(
        (status = 200, description = "Token generated", body = GetTokenResponse),
        (status = 404, description = "User not found")
    )
)]
pub async fn get_token(
    State(state): State<AppState>,
    Json(payload): Json<GetTokenRequest>,
) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let user = users
        .iter()
        .find(|u| u.uid == payload.uid)
        .ok_or_else(|| AppError::NotFound("User tidak ditemukan".into()))?;

    let token = generate_token(user, &state.config.jwt_secret, state.config.jwt_ttl_seconds)?;

    Ok(Json(GetTokenResponse { token }))
}

#[utoipa::path(
    get,
    path = "/api/v1/me",
    tag = "Auth",
    security(
        ("bearer_auth" = [])
    ),
    responses(
        (status = 200, description = "Current authenticated user profile", body = MeResponse),
        (status = 401, description = "Unauthorized")
    )
)]
pub async fn me(
    auth_user: AuthUser,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let user = users
        .iter()
        .find(|u| u.id == auth_user.id)
        .ok_or_else(|| AppError::NotFound("User tidak ditemukan".into()))?;

    let response = MeResponse {
        id: user.id,
        uid: user.uid.clone(),
        name: user.name.clone(),
        email: user.email.clone(),
        nik: user.nik.clone(),
        no_hp: user.no_hp.clone(),
        roles: vec![UserRole {
            id: 2,
            title: "Peserta".to_string(),
        }],
        role: "Peserta".to_string(),
    };

    Ok(Json(response))
}
