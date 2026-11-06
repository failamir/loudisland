use serde::{Deserialize, Serialize};
use utoipa::ToSchema;

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct Role {
    pub id: i64,
    pub title: String,
    pub permission_ids: Vec<i64>,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct Permission {
    pub id: i64,
    pub title: String,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct RoleUpsertRequest {
    pub title: String,
    #[serde(rename = "permission_ids")]
    pub permission_ids: Option<Vec<i64>>,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct PermissionUpsertRequest {
    pub title: String,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct AdminUserItem {
    pub id: i64,
    pub uid: String,
    pub name: String,
    pub email: String,
    pub nik: Option<String>,
    pub no_hp: Option<String>,
    pub roles: Vec<String>,
    pub token_version: i32,
    pub created_at: String,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct AdminUserUpsertRequest {
    pub name: String,
    pub email: String,
    pub password: Option<String>,
    pub nik: Option<String>,
    pub no_hp: Option<String>,
    pub roles: Option<Vec<String>>,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct NomorPunggungItem {
    pub id: i64,
    pub nomor: String,
    pub participant_id: Option<String>,
    pub paired_at: Option<String>,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct NomorPunggungGenerateRequest {
    pub count: usize,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct NomorPunggungPairRequest {
    pub nomor: String,
    pub participant_id: String,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct PromoCodeItem {
    pub id: i64,
    pub code: String,
    pub discount_amount: f64,
    pub discount_percent: Option<f64>,
    pub max_usage: i32,
    pub usage_count: i32,
    pub is_active: bool,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct PromoCodeUpsertRequest {
    pub code: String,
    pub discount_amount: Option<f64>,
    pub discount_percent: Option<f64>,
    pub max_usage: Option<i32>,
    pub is_active: Option<bool>,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct ReferralCodeItem {
    pub id: i64,
    pub user_id: i64,
    pub code: String,
    pub commission_percent: f64,
    pub total_earned: f64,
    pub status: String,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct ReferralCodeUpsertRequest {
    pub code: String,
    pub commission_percent: Option<f64>,
}

#[derive(Clone, Debug, Serialize, Deserialize, ToSchema)]
pub struct WithdrawalItem {
    pub id: i64,
    pub user_id: i64,
    pub amount: f64,
    pub bank_name: String,
    pub account_number: String,
    pub status: String,
    pub created_at: String,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct WithdrawalSummaryResponse {
    pub total_pending: f64,
    pub total_approved: f64,
    pub total_rejected: f64,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct TotalIncomeResponse {
    pub total_income: f64,
    pub summary: TotalIncomeSummary,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct TotalIncomeSummary {
    pub profit: f64,
    pub successful_transactions: usize,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct TotalFinalIncomeResponse {
    pub total_final_price: f64,
    pub total_participants: usize,
}

#[derive(Debug, Serialize, ToSchema)]
pub struct RacepackItem {
    pub id: i64,
    pub name: String,
    pub email: String,
    pub phone: Option<String>,
    pub shirt_size: Option<String>,
    pub racepack_collected: bool,
    pub bib_number: Option<String>,
    pub invoice: String,
    pub status: String,
}

#[derive(Debug, Deserialize, ToSchema)]
pub struct BlastRequest {
    pub message: String,
    pub target: Option<String>,
}
