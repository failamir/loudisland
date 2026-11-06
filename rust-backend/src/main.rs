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
      padding: 20px;
      overflow-x: auto;
    }
    .terminal {
      background: #030712;
      border: 1px solid #1e293b;
      border-radius: 12px;
      padding: 24px 30px;
      box-shadow: 0 20px 50px rgba(0, 255, 204, 0.1);
      max-width: 95vw;
    }
    .header {
      display: flex;
      gap: 8px;
      margin-bottom: 20px;
      border-bottom: 1px solid #1e293b;
      padding-bottom: 12px;
    }
    .dot { width: 12px; height: 12px; border-radius: 50%; }
    .dot-red { background: #ef4444; }
    .dot-yellow { background: #eab308; }
    .dot-green { background: #22c55e; }
    pre {
      font-size: 13px;
      line-height: 1.25;
      white-space: pre;
      font-weight: 600;
      text-shadow: 0 0 8px rgba(0, 255, 204, 0.4);
    }
    .cursor {
      display: inline-block;
      width: 9px;
      height: 16px;
      background: #00ffcc;
      vertical-align: middle;
      animation: blink 0.8s infinite;
      box-shadow: 0 0 10px #00ffcc;
    }
    @keyframes blink {
      0%, 49% { opacity: 1; }
      50%, 100% { opacity: 0; }
    }
    .footer {
      margin-top: 20px;
      font-size: 12px;
      color: #64748b;
      text-align: center;
    }
    .badge {
      display: inline-block;
      margin-top: 10px;
      padding: 4px 12px;
      border-radius: 20px;
      background: rgba(0, 255, 204, 0.1);
      border: 1px solid rgba(0, 255, 204, 0.3);
      color: #38bdf8;
      font-size: 11px;
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
    <pre id="text"></pre><span class="cursor"></span>
  </div>
  <div class="footer">
    <div>🚀 REST API Engine: <strong>Axum 0.7 (Rust)</strong></div>
    <div class="badge">Endpoints: /api/v1/events • /api/v1/wilayah/provinces • /swagger-ui</div>
  </div>

  <script>
    const fullText = `  __  __                 _       _ _ _         
 |  \\/  | __ _ _ __   __| | __ _| (_) | ____ _ 
 | |\\/| |/ _` | '_ \\ / _` |/ _` | | | |/ / _` |
 | |  | | (_| | | | | (_| | (_| | | |   < (_| |
 |_|  |_|\\__,_|_| |_|\\__,_|\\__,_|_|_|_|\\_\\__,_|
                                               
  _  _____  ____  ____  ____  ___   ____  _   _ _   _   ____   ___ ____   __   
 | |/ / _ \\|  _ \\|  _ \\|  _ \\|_ _| |  _ \\| | | | \\ | | |___ \\ / _ \\___ \\ / /_  
 | ' / | | | |_) | |_) | |_) || |  | |_) | | | |  \\| |   __) | | | |__) | '_ \\ 
 | . \\ |_| |  _ <|  __/|  _ < | |  |  _ <| |_| | |\\  |  / __/| |_| / __/| (_) |
 |_|\\_\\___/|_| \\_\\_|   |_| \\_\\___| |_| \\_\\\\___/|_| \\_| |_____|\\___/_____|\\___/ 
                                                                               
                     Mandalika KORPRI Run 2026`;

    const el = document.getElementById("text");
    let idx = 0;
    const speed = 3; // ms per char

    function type() {
      if (idx < fullText.length) {
        el.textContent += fullText[idx];
        idx++;
        setTimeout(type, speed);
      }
    }
    type();
  </script>
</body>
</html>
"#;

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
        routes::admin::list_users,
        routes::admin::show_user,
        routes::admin::create_user,
        routes::admin::update_user,
        routes::admin::delete_user,
        routes::admin::revoke_tokens,
        routes::admin::list_roles,
        routes::admin::list_transactions,
        routes::admin::list_racepacks,
        routes::admin::list_staffs,
        routes::admin::list_nomor_punggung,
        routes::admin::list_promo_codes,
        routes::admin::list_referral_codes,
        routes::admin::list_withdrawals,
        routes::admin::withdrawals_summary,
        routes::admin::get_total_income,
        routes::admin::get_total_final_income,
    ),
    components(
        schemas(
            models::auth::RegisterRequest,
            models::auth::LoginRequest,
            models::auth::GetTokenRequest,
            models::auth::AuthResponse,
            models::auth::AuthResponseData,
            models::auth::GetTokenResponse,
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
            models::admin::AdminUserItem,
            models::admin::AdminUserUpsertRequest,
            models::admin::Role,
            models::admin::RoleUpsertRequest,
            models::admin::Permission,
            models::admin::PermissionUpsertRequest,
            models::admin::NomorPunggungItem,
            models::admin::NomorPunggungGenerateRequest,
            models::admin::NomorPunggungPairRequest,
            models::admin::PromoCodeItem,
            models::admin::PromoCodeUpsertRequest,
            models::admin::ReferralCodeItem,
            models::admin::ReferralCodeUpsertRequest,
            models::admin::WithdrawalItem,
            models::admin::WithdrawalSummaryResponse,
            models::admin::TotalIncomeResponse,
            models::admin::TotalIncomeSummary,
            models::admin::TotalFinalIncomeResponse,
            models::admin::RacepackItem,
            models::admin::BlastRequest,
        )
    ),
    modifiers(&SecurityAddon),
    tags(
        (name = "Auth", description = "Autentikasi & Profil Akun Peserta"),
        (name = "Tickets & Events", description = "Manajemen Event, Checkout Tiket, & Ukuran Jersey"),
        (name = "Wilayah", description = "Proxy API Wilayah Indonesia"),
        (name = "WhatsApp / WAHA", description = "Layanan Pesan WhatsApp WAHA"),
        (name = "Admin: Users & Roles", description = "Pengelolaan User, Hak Akses & Roles"),
        (name = "Admin: Commerce", description = "Transaksi & Riwayat Penjualan"),
        (name = "Admin: Operations", description = "Racepack, Penomoran BIB, & Staf"),
        (name = "Admin: Marketing & Finance", description = "Voucher, Referral, & Keuangan"),
    ),
    info(
        title = "Korpri Run & LoudIsland Backend API",
        version = "1.0.0",
        description = "Backend Rust berkinerja tinggi yang melayani Frontend Web App Korpri & Admin Dashboard."
    )
)]
struct ApiDoc;

struct SecurityAddon;

impl Modify for SecurityAddon {
    fn modify(&self, openapi: &mut utoipa::openapi::OpenApi) {
        if let Some(components) = openapi.components.as_mut() {
            components.add_security_scheme(
                "bearer_auth",
                SecurityScheme::Http(
                    HttpBuilder::new()
                        .scheme(HttpAuthScheme::Bearer)
                        .bearer_format("JWT")
                        .description(Some("Masukkan JWT access token"))
                        .build(),
                ),
            );
        }
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
        .merge(routes::wilayah::router())
        .merge(routes::admin::router());

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
