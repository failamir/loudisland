use serde::{Deserialize, Serialize};
use utoipa::ToSchema;

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct ProvinceItem {
    pub code: String,
    pub name: String,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct RegencyItem {
    pub code: String,
    #[serde(rename = "province_code")]
    pub province_code: Option<String>,
    pub name: String,
}
