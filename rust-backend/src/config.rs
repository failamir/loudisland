use std::env;

#[derive(Clone, Debug)]
pub struct Config {
    pub port: u16,
    pub jwt_secret: String,
    pub jwt_ttl_seconds: i64,
    pub midtrans_is_production: bool,
    pub midtrans_client_key: String,
    pub midtrans_server_key: String,
    pub database_url: Option<String>,
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

        let midtrans_is_production = env::var("MIDTRANS_IS_PRODUCTION")
            .map(|v| v == "true" || v == "1")
            .unwrap_or(false);

        let (midtrans_client_key, midtrans_server_key) = if midtrans_is_production {
            (
                env::var("MIDTRANS_PROD_CLIENT_KEY")
                    .or_else(|_| env::var("MIDTRANS_CLIENT_KEY"))
                    .unwrap_or_default(),
                env::var("MIDTRANS_PROD_SERVER_KEY")
                    .or_else(|_| env::var("MIDTRANS_SERVER_KEY"))
                    .unwrap_or_default(),
            )
        } else {
            (
                env::var("MIDTRANS_CLIENT_KEY").unwrap_or_default(),
                env::var("MIDTRANS_SERVER_KEY").unwrap_or_default(),
            )
        };

        let database_url = env::var("DATABASE_URL").ok();

        Self {
            port,
            jwt_secret,
            jwt_ttl_seconds,
            midtrans_is_production,
            midtrans_client_key,
            midtrans_server_key,
            database_url,
        }
    }
}
