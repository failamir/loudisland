use axum::{
    extract::{Path, State},
    response::IntoResponse,
    routing::get,
    Json, Router,
};
use serde_json::Value;

use crate::{
    error::{AppError, AppResult},
    models::wilayah::{ProvinceItem, RegencyItem},
    state::AppState,
};

pub fn router() -> Router<AppState> {
    Router::new()
        .route("/wilayah/provinces", get(get_provinces))
        .route("/wilayah/regencies/:province_code", get(get_regencies))
}

#[utoipa::path(
    get,
    path = "/api/v1/wilayah/provinces",
    tag = "Wilayah",
    responses(
        (status = 200, description = "Daftar provinsi di Indonesia")
    )
)]
pub async fn get_provinces(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let url = "https://wilayah.id/api/provinces.json";
    let resp = state
        .http_client
        .get(url)
        .send()
        .await
        .map_err(|e| AppError::Internal(format!("Failed to fetch provinces: {}", e)))?;

    let json: Value = resp
        .json()
        .await
        .map_err(|e| AppError::Internal(format!("Failed to parse provinces: {}", e)))?;

    Ok(Json(json))
}

#[utoipa::path(
    get,
    path = "/api/v1/wilayah/regencies/{province_code}",
    tag = "Wilayah",
    params(
        ("province_code" = String, Path, description = "Kode provinsi wilayah (e.g. 52)")
    ),
    responses(
        (status = 200, description = "Daftar kabupaten / kota berdasarkan kode provinsi")
    )
)]
pub async fn get_regencies(
    Path(province_code): Path<String>,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let url = format!("https://wilayah.id/api/regencies/{}.json", province_code);
    let resp = state
        .http_client
        .get(&url)
        .send()
        .await
        .map_err(|e| AppError::Internal(format!("Failed to fetch regencies: {}", e)))?;

    let json: Value = resp
        .json()
        .await
        .map_err(|e| AppError::Internal(format!("Failed to parse regencies: {}", e)))?;

    Ok(Json(json))
}
