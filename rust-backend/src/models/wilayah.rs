use serde::{Deserialize, Serialize};

#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct ProvinceItem {
    pub code: String,
    pub name: String,
}

#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct RegencyItem {
    pub code: String,
    #[serde(rename = "province_code")]
    pub province_code: Option<String>,
    pub name: String,
}

