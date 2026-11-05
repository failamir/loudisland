use std::net::SocketAddr;
use axum::{
    http::{header, HeaderMap, HeaderValue, StatusCode},
    response::IntoResponse,
    routing::get,
};
use tower_http::{
    cors::{Any, CorsLayer},
    trace::TraceLayer,
};
use tracing_subscriber::{layer::SubscriberExt, util::SubscriberInitExt};
use utoipa::{
    openapi::security::{HttpAuthScheme, HttpBuilder, SecurityScheme},
    Modify, OpenApi,
};
use utoipa_swagger_ui::SwaggerUi;

mod config;
mod error;
mod middleware;
mod models;
mod routes;
mod state;

use config::Config;
use state::AppState;

const ASCII_TEXT: &str = r#"  __  __                 _       _ _ _         
 |  \/  | __ _ _ __   __| | __ _| (_) | ____ _ 
 | |\/| |/ _` | '_ \ / _` |/ _` | | | |/ / _` |
 | |  | | (_| | | | | (_| | (_| | | |   < (_| |
 |_|  |_|\__,_|_| |_|\__,_|\__,_|_|_|_|\_\__,_|
                                               
  _  _____  ____  ____  ____  ___   ____  _   _ _   _   ____   ___ ____   __   
 | |/ / _ \|  _ \|  _ \|  _ \|_ _| |  _ \| | | | \ | | |___ \ / _ \___ \ / /_  
 | ' / | | | |_) | |_) | |_) || |  | |_) | | | |  \| |   __) | | | |__) | '_ \ 
 | . \ |_| |  _ <|  __/|  _ < | |  |  _ <| |_| | |\  |  / __/| |_| / __/| (_) |
 |_|\_\___/|_| \_\_|   |_| \_\___| |_| \_\\___/|_| \_| |_____|\___/_____|\___/ 
                                                                               
                     Mandalika KORPRI Run 2026"#;

const HTML_PAGE: &str = r#"<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mandalika KORPRI Run 2026</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background-color: #0b0f19;
      color: #00ffcc;
      font-family: 'Courier New', Courier, monospace;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .terminal {
      background: #030712;
      border: 1px solid #1e293b;
      border-radius: 12px;
      padding: 18px 20px;
      box-shadow: 0 20px 50px rgba(0, 255, 204, 0.1);
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
    }
    .header {
      display: flex;
      gap: 8px;
      margin-bottom: 16px;
      border-bottom: 1px solid #1e293b;
      padding-bottom: 10px;
      align-items: center;
    }
    .dot { width: 11px; height: 11px; border-radius: 50%; }
    .dot-red { background: #ef4444; }
    .dot-yellow { background: #eab308; }
    .dot-green { background: #22c55e; }
    .terminal-body {
      overflow-x: auto;
      max-width: 100%;
      padding-bottom: 6px;
      -webkit-overflow-scrolling: touch;
    }
    pre {
      font-size: clamp(6px, 1.4vw, 13px);
      line-height: 1.15;
      white-space: pre;
      font-weight: 600;
      text-shadow: 0 0 8px rgba(0, 255, 204, 0.4);
      display: inline-block;
      min-width: fit-content;
    }
    .cursor {
      display: inline-block;
      width: 8px;
      height: 14px;
      background: #00ffcc;
      vertical-align: middle;
      animation: blink 0.8s infinite;
      box-shadow: 0 0 10px #00ffcc;
      margin-left: 4px;
    }
    @keyframes blink {
      0%, 49% { opacity: 1; }
      50%, 100% { opacity: 0; }
    }
    .footer {
      margin-top: 18px;
      font-size: 13px;
      color: #94a3b8;
      text-align: center;
      line-height: 1.6;
    }
    .badge-container {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: center;
      margin-top: 12px;
    }
    .badge {
      display: inline-block;
      padding: 5px 14px;
      border-radius: 20px;
      background: rgba(0, 255, 204, 0.08);
      border: 1px solid rgba(0, 255, 204, 0.25);
      color: #38bdf8;
      font-size: 12px;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    .badge:hover {
      background: rgba(0, 255, 204, 0.2);
      border-color: #00ffcc;
      color: #ffffff;
      transform: translateY(-1px);
    }
  </style>
</head>
<body>
  <div class="terminal">
    <div class="header">
      <div class="dot dot-red"></div>
      <div class="dot dot-yellow"></div>
      <div class="dot dot-green"></div>
      <span style="color: #64748b; font-size: 12px; margin-left: 10px;">api.failamir.tech ~ bash</span>
    </div>
    <div class="terminal-body">
      <pre id="text">  __  __                 _       _ _ _         
 |  \/  | __ _ _ __   __| | __ _| (_) | ____ _ 
 | |\/| |/ _` | '_ \ / _` |/ _` | | | |/ / _` |
 | |  | | (_| | | | | (_| | (_| | | |   < (_| |
 |_|  |_|\__,_|_| |_|\__,_|\__,_|_|_|_|\_\__,_|
                                               
  _  _____  ____  ____  ____  ___   ____  _   _ _   _   ____   ___ ____   __   
 | |/ / _ \|  _ \|  _ \|  _ \|_ _| |  _ \| | | | \ | | |___ \ / _ \___ \ / /_  
 | ' / | | | |_) | |_) | |_) || |  | |_) | | | |  \| |   __) | | | |__) | '_ \ 
 | . \ |_| |  _ <|  __/|  _ < | |  |  _ <| |_| | |\  |  / __/| |_| / __/| (_) |
 |_|\_\___/|_| \_\_|   |_| \_\___| |_| \_\\___/|_| \_| |_____|\___/_____|\___/ 
                                                                               
                     Mandalika KORPRI Run 2026</pre><span class="cursor"></span>
    </div>
  </div>
  <div class="footer">
    <div>🚀 REST API Engine: <strong>Axum 0.7 (Rust)</strong></div>
    <div class="badge-container">
      <a class="badge" href="/swagger-ui/">📖 Swagger UI Documentation</a>
      <a class="badge" href="/api/v1/events">🎟️ /api/v1/events</a>
      <a class="badge" href="/api/v1/wilayah/provinces">📍 /api/v1/wilayah/provinces</a>
    </div>
  </div>
</body>
</html>
"#;

#[derive(OpenApi)]
#[openapi(
    paths(
        routes::auth::register,
        routes::auth::login,
        routes::auth::refresh,
        routes::auth::get_token,
        routes::auth::me,
        routes::ticket::list_events,
        routes::ticket::buy_tickets,
        routes::ticket::payment_status,
        routes::ticket::my_order,
        routes::ticket::my_ticket,
        routes::ticket::update_shirt_size,
        routes::ticket::waha_send_image,
        routes::wilayah::get_provinces,
        routes::wilayah::get_regencies,
    ),
    components(
        schemas(
            models::auth::RegisterRequest,
            models::auth::LoginRequest,
            models::auth::GetTokenRequest,
            models::auth::GetTokenResponse,
            models::auth::AuthResponse,
            models::auth::AuthResponseData,
            models::auth::MeResponse,
            models::auth::UserRole,
            models::ticket::Event,
            models::ticket::EventListResponse,
            models::ticket::MultiOrderRequest,
            models::ticket::OrderParticipantRequest,
            models::ticket::OrderSuccessResponse,
            models::ticket::OrderSuccessParticipant,
            models::ticket::PaymentStatusResponse,
            models::ticket::PaymentUserDetail,
            models::ticket::PaymentParticipantDetail,
            models::ticket::MyOrderListResponse,
            models::ticket::MyOrderItem,
            models::ticket::MyOrderParticipantDecoded,
            models::ticket::MyTicketListResponse,
            models::ticket::MyTicketItem,
            models::ticket::MyTicketEvent,
            models::ticket::MyTicketParticipant,
            models::ticket::UpdateShirtSizeRequest,
            models::ticket::UpdateShirtSizeResponse,
            models::ticket::SendImageRequest,
            models::ticket::SendImageResponse,
            models::wilayah::ProvinceItem,
            models::wilayah::RegencyItem,
        )
    ),
    modifiers(&SecurityAddon),
    tags(
        (name = "auth", description = "Authentication Endpoints"),
        (name = "events", description = "Event & Ticket Management Endpoints"),
        (name = "wilayah", description = "Wilayah / Regional Data Endpoints")
    ),
    info(
        title = "LoudIsland / Mandalika KORPRI Run API",
        version = "0.1.0",
        description = "High-performance Rust Axum backend powering Mandalika KORPRI Run 2026."
    )
)]
struct ApiDoc;

struct SecurityAddon;

impl Modify for SecurityAddon {
    fn modify(&self, openapi: &mut utoipa::openapi::OpenApi) {
        if let Some(components) = openapi.components.as_mut() {
            components.add_security_scheme(
                "bearerAuth",
                SecurityScheme::Http(
                    HttpBuilder::new()
                        .scheme(HttpAuthScheme::Bearer)
                        .bearer_format("JWT")
                        .build(),
                ),
            );
        }
    }
}

async fn root_handler(headers: HeaderMap) -> impl IntoResponse {
    let accept = headers
        .get(header::ACCEPT)
        .and_then(|v| v.to_str().ok())
        .unwrap_or("");

    if accept.contains("text/html") {
        (
            StatusCode::OK,
            [(header::CONTENT_TYPE, HeaderValue::from_static("text/html; charset=utf-8"))],
            HTML_PAGE,
        )
    } else {
        (
            StatusCode::OK,
            [(header::CONTENT_TYPE, HeaderValue::from_static("text/plain; charset=utf-8"))],
            ASCII_TEXT,
        )
    }
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
        .merge(SwaggerUi::new("/swagger-ui").url("/api-docs/openapi.json", ApiDoc::openapi()))
        .nest("/api/v1", api_v1)
        .layer(cors)
        .layer(TraceLayer::new_for_http())
        .with_state(state);

    let addr = SocketAddr::from(([0, 0, 0, 0], port));
    tracing::info!("LoudIsland / Korpri Rust Backend listening on http://{}", addr);
    tracing::info!("Swagger UI documentation available at http://{}/swagger-ui", addr);

    let listener = tokio::net::TcpListener::bind(addr)
        .await
        .expect("Failed to bind TCP listener");

    axum::serve(listener, app).await.unwrap();
}
