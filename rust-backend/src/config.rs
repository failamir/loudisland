use std::env;

#[derive(Clone, Debug)]
pub struct Config {
    pub port: u16,
    pub jwt_secret: String,
    pub jwt_ttl_seconds: i64,
}

impl Config {
    pub fn from_env() -> Self {
        let port = env::var("PORT")
            .ok()
            .and_then(|p| p.parse().ok())
            .unwrap_or(8080);

        let jwt_secret = env::var("JWT_SECRET").unwrap_or_else(|_| "default_korpri_jwt_secret_key_123456789".to_string());
        let jwt_ttl_seconds = env::var("JWT_TTL")
            .ok()
            .and_then(|v| v.parse().ok())
            .unwrap_or(86400 * 7); // 7 days default

        Self {
            port,
            jwt_secret,
            jwt_ttl_seconds,
        }
    }
}

