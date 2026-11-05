use serde::{Deserialize, Serialize};

#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct Event {
    pub id: i64,
    pub nama_event: String,
    pub event_code: String,
    pub harga: String,
    pub tanggal_mulai: String,
}

#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct ParticipantRecord {
    pub participant_id: String,
    pub ticket_id: String,
    pub name: String,
    pub nik: Option<String>,
    pub email: String,
    pub phone: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
    pub shirt_size: Option<String>,
    pub status: i32,
    pub status_racepack: i32,
}

#[derive(Clone, Debug, Serialize, Deserialize)]
pub struct TransactionRecord {
    pub id: i64,
    pub invoice: String,
    pub peserta_id: i64,
    pub uid: Option<String>,
    pub email: String,
    pub nama: String,
    pub phone: Option<String>,
    pub nik: Option<String>,
    pub amount: String,
    pub note: Option<String>,
    pub status: String,
    pub created_at: String,
    pub payment_url: Option<String>,
    pub expired_snap_time: Option<String>,
    pub events: Vec<i64>,
    pub participants: Vec<ParticipantRecord>,
}

#[derive(Debug, Deserialize)]
pub struct OrderParticipantRequest {
    #[serde(rename = "ticketId")]
    pub ticket_id: Option<String>,
    pub nik: Option<String>,
    pub name: Option<String>,
    pub email: Option<String>,
    pub phone: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
    #[serde(rename = "shirtSize")]
    pub shirt_size: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct MultiOrderRequest {
    #[serde(rename = "userId")]
    pub user_id: String,
    pub participants: Vec<OrderParticipantRequest>,
}

#[derive(Debug, Serialize)]
pub struct OrderSuccessParticipant {
    #[serde(rename = "ticketId")]
    pub ticket_id: Option<String>,
    pub name: Option<String>,
    pub email: Option<String>,
    pub phone: Option<String>,
    pub nik: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
}

#[derive(Debug, Serialize)]
pub struct OrderSuccessResponse {
    pub invoice: String,
    pub data: String, // snap redirect url
    pub participants: Vec<OrderSuccessParticipant>,
    pub service_fee: f64,
    pub total_payment: f64,
    pub total_amount: f64,
    pub total_ticket: String,
    pub expired_snap_time: String,
}

#[derive(Debug, Serialize)]
pub struct PaymentUserDetail {
    pub id: i64,
    pub nama: String,
    pub email: String,
    pub no_hp: Option<String>,
    pub status_payment: Option<String>,
    pub event_id: Option<i64>,
    pub nomor_punggung: Option<String>,
    pub start_at: Option<String>,
    pub finish_at: Option<String>,
}

#[derive(Debug, Serialize)]
pub struct PaymentParticipantDetail {
    pub participant_id: String,
    pub name: String,
    pub nik: Option<String>,
    pub email: String,
    pub phone: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
    pub ticket_id: String,
    pub status_racepack: i32,
    pub qr_url: String,
}

#[derive(Debug, Serialize)]
pub struct PaymentStatusResponse {
    pub invoice: String,
    pub status: String,
    pub amount: String,
    pub no_tiket: Option<String>,
    pub qr_url: Option<String>,
    pub expired_snap_time: Option<String>,
    pub user: Option<PaymentUserDetail>,
    pub participants: Vec<PaymentParticipantDetail>,
}

#[derive(Debug, Serialize)]
pub struct MyOrderParticipantDecoded {
    #[serde(rename = "ticketId")]
    pub ticket_id: Option<String>,
    pub event_name: Option<String>,
    pub nik: Option<String>,
    pub name: Option<String>,
    pub email: Option<String>,
    pub phone: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
    pub shirt_size: Option<String>,
}

#[derive(Debug, Serialize)]
pub struct MyOrderItem {
    pub invoice: String,
    pub amount: String,
    pub note: Option<String>,
    pub status: String,
    pub created_at: String,
    pub payment_url: Option<String>,
    pub nama: String,
    pub email: String,
    pub no_hp: Option<String>,
    pub nik: Option<String>,
    pub participants_decoded: Vec<MyOrderParticipantDecoded>,
}

#[derive(Debug, Serialize)]
pub struct MyOrderListResponse {
    pub message: String,
    pub status: i32,
    pub data: Vec<MyOrderItem>,
}

#[derive(Debug, Serialize)]
pub struct MyTicketParticipant {
    pub nik: Option<String>,
    pub name: String,
    pub email: String,
    pub phone: Option<String>,
    pub province: Option<String>,
    pub city: Option<String>,
    pub participant_id: String,
    pub shirt_size: Option<String>,
    pub qr_url: String,
}

#[derive(Debug, Serialize)]
pub struct MyTicketEvent {
    pub id: i64,
    pub nama_event: String,
    pub harga: f64,
    pub tanggal_mulai: Option<String>,
}

#[derive(Debug, Serialize)]
pub struct MyTicketItem {
    pub invoice: String,
    pub transaction_id: i64,
    pub status: String,
    pub created_at: String,
    pub event: Option<MyTicketEvent>,
    pub participant: Option<MyTicketParticipant>,
}

#[derive(Debug, Serialize)]
pub struct MyTicketListResponse {
    pub data: Vec<MyTicketItem>,
}

#[derive(Debug, Deserialize)]
pub struct UpdateShirtSizeRequest {
    #[serde(rename = "ticketId")]
    pub ticket_id: Option<String>,
    #[serde(rename = "shirtSize")]
    pub shirt_size: Option<String>,
}

#[derive(Debug, Deserialize)]
pub struct SendImageRequest {
    #[serde(rename = "chatId")]
    pub chat_id: String,
    pub url: String,
}

