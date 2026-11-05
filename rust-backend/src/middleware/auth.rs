use axum::{
    async_trait,
    extract::{FromRef, FromRequestParts},
    http::request::Parts,
};
use jsonwebtoken::{decode, DecodingKey, Validation};

use crate::{
    error::AppError,
    models::auth::{Claims, User},
    state::AppState,
};

#[derive(Clone, Debug)]
pub struct AuthUser {
    pub id: i64,
    pub uid: String,
    pub email: String,
}

#[async_trait]
impl<S> FromRequestParts<S> for AuthUser
where
    AppState: FromRef<S>,
    S: Send + Sync,
{
    type Rejection = AppError;

    async fn from_request_parts(parts: &mut Parts, state: &S) -> Result<Self, Self::Rejection> {
        let app_state = AppState::from_ref(state);

        let auth_header = parts
            .headers
            .get("Authorization")
            .and_then(|h| h.to_str().ok())
            .ok_or_else(|| AppError::Unauthorized("Missing Authorization header".into()))?;

        let token = if let Some(stripped) = auth_header.strip_prefix("Bearer ") {
            stripped.trim()
        } else {
            return Err(AppError::Unauthorized("Invalid token format".into()));
        };

        let token_data = decode::<Claims>(
            token,
            &DecodingKey::from_secret(app_state.config.jwt_secret.as_bytes()),
            &Validation::default(),
        )
        .map_err(|e| AppError::Unauthorized(format!("Invalid token: {}", e)))?;

        let user_id: i64 = token_data.claims.sub.parse().map_err(|_| {
            AppError::Unauthorized("Invalid user identity in token".into())
        })?;

        // Verify user existence in state
        let users: tokio::sync::RwLockReadGuard<'_, Vec<User>> = app_state.users.read().await;
        let user = users
            .iter()
            .find(|u| u.id == user_id)
            .ok_or_else(|| AppError::Unauthorized("User not found".into()))?;

        if user.token_version != token_data.claims.tv {
            return Err(AppError::Unauthorized("Token has been revoked".into()));
        }

        Ok(AuthUser {
            id: user.id,
            uid: user.uid.clone(),
            email: user.email.clone(),
        })
    }
}
