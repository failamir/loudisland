use serde::{Deserialize, Serialize};
use utoipa::ToSchema;

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct User {
    pub id: i64,
    pub uid: String,
    pub name: String,
    pub email: String,
    #[serde(skip_serializing)]
    pub password_hash: String,
    pub nik: Option<String>,
    pub no_hp: Option<String>,
    pub token_version: i32,
    pub created_at: String,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct RegisterRequest {
    pub name: String,
    pub email: String,
    pub password: Option<String>,
    pub uid: Option<String>,
    pub nik: Option<String>,
    pub no_hp: Option<String>,
    pub method: Option<String>,
    pub id_token: Option<String>,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct LoginRequest {
    pub email: String,
    pub password: Option<String>,
    pub method: Option<String>,
    pub id_token: Option<String>,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct GetTokenRequest {
    pub uid: String,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct AuthResponseData {
    pub id: i64,
    pub uid: String,
    pub name: String,
    pub email: String,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct AuthResponse {
    pub message: String,
    pub token: String,
    pub access_token: String,
    pub token_type: String,
    pub expires_in: i64,
    pub data: AuthResponseData,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct GetTokenResponse {
    pub token: String,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct UserRole {
    pub id: i64,
    pub title: String,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct MeResponse {
    pub id: i64,
    pub uid: String,
    pub name: String,
    pub email: String,
    pub nik: Option<String>,
    pub no_hp: Option<String>,
    pub roles: Vec<UserRole>,
    pub role: String,
}

#[derive(Debug, Serialize, Deserialize)]
pub struct Claims {
    pub sub: String,
    pub uid: String,
    pub email: String,
    pub tv: i32,
    pub exp: usize,
}
