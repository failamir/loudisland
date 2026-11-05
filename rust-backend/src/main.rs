use std::net::SocketAddr;
use axum::{response::IntoResponse, routing::get};
use tower_http::{
    cors::{Any, CorsLayer},
    trace::TraceLayer,
};
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};

mod config;
mod error;
mod middleware;
mod models;
mod routes;
mod state;

use config::Config;
use state::AppState;

const ASCII_ART: &str = r#"
  __  __                 _       _ _ _         
 |  \/  | __ _ _ __   __| | __ _| (_) | ____ _ 
 | |\/| |/ _` | '_ \ / _` |/ _` | | | |/ / _` |
 | |  | | (_| | | | | (_| | (_| | | |   < (_| |
 |_|  |_|\__,_|_| |_|\__,_|\__,_|_|_|_|\_\__,_|
                                               
  _  _____  ____  ____  ____  ___   ____  _   _ _   _   ____   ___ ____   __   
 | |/ / _ \|  _ \|  _ \|  _ \|_ _| |  _ \| | | | \ | | |___ \ / _ \___ \ / /_  
 | ' / | | | |_) | |_) | |_) || |  | |_) | | | |  \| |   __) | | | |__) | '_ \ 
 | . \ |_| |  _ <|  __/|  _ < | |  |  _ <| |_| | |\  |  / __/| |_| / __/| (_) |
 |_|\_\___/|_| \_\_|   |_| \_\___| |_| \_\\___/|_| \_| |_____|\___/_____|\___/ 
                                                                               
                     Mandalika KORPRI Run 2026
"#;

async fn root_handler() -> impl IntoResponse {
    ASCII_ART
}

#[tokio::main]
async fn main() {
    dotenvy::dotenv().ok();

    tracing_subscriber::registry()
        .with(
            tracing_subscriber::EnvFilter::try_from_default_env()
                .unwrap_or_else(|_| "rust_backend=debug,tower_http=debug,axum=trace".into()),
        )
        .with(tracing_subscriber::fmt::layer())
        .init();

    let config = Config::from_env();
    let port = config.port;
    let state = AppState::new(config);

    let cors = CorsLayer::new()
        .allow_origin(Any)
        .allow_methods(Any)
        .allow_headers(Any);

    let api_v1 = axum::Router::new()
        .merge(routes::auth::router())
        .merge(routes::ticket::router())
        .merge(routes::wilayah::router());

    let app = axum::Router::new()
        .route("/", get(root_handler))
        .nest("/api/v1", api_v1)
        .layer(cors)
        .layer(TraceLayer::new_for_http())
        .with_state(state);

    let addr = SocketAddr::from(([0, 0, 0, 0], port));
    tracing::info!("LoudIsland / Korpri Rust Backend listening on http://{}", addr);

    let listener = tokio::net::TcpListener::bind(addr)
        .await
        .expect("Failed to bind TCP listener");

    axum::serve(listener, app).await.unwrap();
}
