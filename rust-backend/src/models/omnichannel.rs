use serde::{Deserialize, Serialize};
use utoipa::ToSchema;

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelCustomer {
    pub id: String,
    pub name: String,
    pub initials: String,
    pub avatar_url: Option<String>,
    pub phone: String,
    pub email: Option<String>,
    pub bib_number: Option<String>,
    pub event_category: Option<String>,
    pub shirt_size: Option<String>,
    pub payment_status: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelChannel {
    #[serde(rename = "type")]
    pub channel_type: String,
    pub session_name: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelLastMessage {
    pub text: String,
    #[serde(rename = "type")]
    pub message_type: String,
    pub time_display: String,
    pub timestamp: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelAssignment {
    pub is_assigned: bool,
    pub assigned_agent_id: Option<String>,
    pub assigned_agent_name: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelThread {
    pub id: String,
    pub customer: OmnichannelCustomer,
    pub channel: OmnichannelChannel,
    pub last_message: OmnichannelLastMessage,
    pub assignment: OmnichannelAssignment,
    pub unread_count: i64,
    pub status: String, // open, resolved, pending
    pub is_favourite: bool,
    pub tags: Vec<String>,
    pub remarks: Option<String>,
    pub sla_status: Option<String>, // normal, warning, breached
    pub created_at: String,
    pub updated_at: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelMessage {
    pub id: String,
    pub thread_id: String,
    pub sender_type: String, // customer, agent, system
    pub sender_name: String,
    pub direction: String, // inbound, outbound
    pub content: String,
    pub message_type: String, // text, image, audio, document
    pub media_url: Option<String>,
    pub is_private_note: bool,
    pub delivery_status: String, // sent, delivered, read
    pub created_at: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelTag {
    pub id: String,
    pub name: String,
    pub color: String,
    pub usage_count: i64,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelQuickReply {
    pub id: String,
    pub shortcut: String,
    pub title: String,
    pub content: String,
    pub category: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelAssignee {
    pub id: String,
    pub name: String,
    pub role: String,
    pub role_badge: String,
    pub email: String,
    pub active_chats_count: i64,
    pub is_unassigned: bool,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelMetrics {
    pub tabs: OmnichannelTabMetrics,
    pub total_open: i64,
    pub total_unread: i64,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct OmnichannelTabMetrics {
    pub mine: i64,
    pub unassigned: i64,
    pub all: i64,
    pub resolved: i64,
    pub favourites: i64,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct SendMessagePayload {
    pub content: String,
    #[serde(default = "default_text")]
    pub message_type: String,
    pub media_url: Option<String>,
    pub is_private_note: Option<bool>,
}

fn default_text() -> String {
    "text".to_string()
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct AssignThreadPayload {
    pub agent_id: Option<String>,
    pub agent_name: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct UpdateThreadStatusPayload {
    pub status: String,
    pub close_comment: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct ThreadTagPayload {
    pub tag: String,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct BroadcastPayload {
    pub title: String,
    pub message: String,
    pub target_category: Option<String>, // all, 5K, 10K, 21K
    pub media_url: Option<String>,
}

#[derive(Debug, Clone, Serialize, Deserialize, ToSchema)]
pub struct BroadcastHistoryItem {
    pub id: String,
    pub title: String,
    pub message: String,
    pub total_recipients: i64,
    pub success_count: i64,
    pub status: String,
    pub created_at: String,
}
