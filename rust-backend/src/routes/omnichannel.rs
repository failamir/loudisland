use axum::{
    extract::{Path, Query, State},
    http::StatusCode,
    response::IntoResponse,
    routing::{delete, get, post, put},
    Json, Router,
};
use chrono::Utc;
use serde::Deserialize;
use serde_json::json;

use crate::{
    models::omnichannel::{
        AssignThreadPayload, BroadcastHistoryItem, BroadcastPayload, OmnichannelAssignee,
        OmnichannelMetrics, OmnichannelQuickReply, OmnichannelTabMetrics, OmnichannelTag,
        OmnichannelThread, OmnichannelMessage, SendMessagePayload, ThreadTagPayload,
        UpdateThreadStatusPayload,
    },
    state::AppState,
};

pub fn router() -> Router<AppState> {
    Router::new()
        // Metrics & Lookups
        .route("/omnichannel/metrics", get(get_metrics))
        .route("/omnichannel/lookups", get(get_lookups))
        .route("/omnichannel/assignees", get(get_assignees))
        // Threads
        .route("/omnichannel/threads", get(list_threads))
        .route("/omnichannel/threads", post(upsert_thread))
        .route("/omnichannel/threads/:id", get(get_thread))
        .route("/omnichannel/threads/:id/status", put(update_thread_status))
        .route("/omnichannel/threads/:id/assign", put(assign_thread))
        .route("/omnichannel/threads/:id/favourite", put(toggle_favourite))
        .route("/omnichannel/threads/:id/tags", get(get_thread_tags))
        .route("/omnichannel/threads/:id/tags", post(add_thread_tag))
        .route("/omnichannel/threads/:id/tags/:tag", delete(remove_thread_tag))
        // Messages
        .route("/omnichannel/threads/:id/messages", get(list_messages))
        .route("/omnichannel/threads/:id/messages", post(send_message))
        // Quick Replies
        .route("/omnichannel/quick-replies", get(list_quick_replies))
        .route("/omnichannel/quick-replies", post(create_quick_reply))
        .route("/omnichannel/quick-replies/:id", delete(delete_quick_reply))
        // Tags Master
        .route("/omnichannel/tags", get(list_tags))
        .route("/omnichannel/tags", post(create_tag))
        .route("/omnichannel/tags/:id", delete(delete_tag))
        // Broadcast
        .route("/omnichannel/broadcast", post(create_broadcast))
        .route("/omnichannel/broadcast/history", get(list_broadcast_history))
        // Webhook receiver (simulated inbound WAHA)
        .route("/omnichannel/webhook", post(handle_webhook))
}

#[derive(Debug, Deserialize)]
pub struct ThreadQueryParams {
    pub scope: Option<String>, // mine, unassigned, all, resolved, favourites
    pub status: Option<String>,
    pub search: Option<String>,
    pub tag: Option<String>,
}

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/metrics",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "Omnichannel inbox counter metrics", body = OmnichannelMetrics)
    )
)]
pub async fn get_metrics(State(state): State<AppState>) -> impl IntoResponse {
    let threads = state.omnichannel_threads.read().await;

    let mut mine = 0;
    let mut unassigned = 0;
    let mut all = 0;
    let mut resolved = 0;
    let mut favourites = 0;
    let mut total_unread = 0;
    let mut total_open = 0;

    for th in threads.iter() {
        all += 1;
        total_unread += th.unread_count;
        if th.status == "open" {
            total_open += 1;
        }
        if th.is_favourite {
            favourites += 1;
        }
        if th.status == "resolved" {
            resolved += 1;
        }
        if !th.assignment.is_assigned {
            unassigned += 1;
        } else {
            // Assume currently logged in agent is Dewi / id 2 or 1
            mine += 1;
        }
    }

    let metrics = OmnichannelMetrics {
        tabs: OmnichannelTabMetrics {
            mine,
            unassigned,
            all,
            resolved,
            favourites,
        },
        total_open,
        total_unread,
    };

    (StatusCode::OK, Json(json!({ "success": true, "data": metrics })))
}

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/lookups",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "Omnichannel Lookups metadata")
    )
)]
pub async fn get_lookups(State(state): State<AppState>) -> impl IntoResponse {
    let tags = state.omnichannel_tags.read().await.clone();
    let assignees = state.omnichannel_assignees.read().await.clone();
    let quick_replies = state.omnichannel_quick_replies.read().await.clone();

    (
        StatusCode::OK,
        Json(json!({
            "success": true,
            "data": {
                "tags": tags,
                "assignees": assignees,
                "quick_replies": quick_replies,
                "channels": [
                    { "id": "whatsapp", "name": "WhatsApp Official", "session": "korpri-cs-wa" }
                ],
                "statuses": ["open", "resolved", "pending"]
            }
        })),
    )
}

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/assignees",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "List assignable agents", body = Vec<OmnichannelAssignee>)
    )
)]
pub async fn get_assignees(State(state): State<AppState>) -> impl IntoResponse {
    let assignees = state.omnichannel_assignees.read().await.clone();
    (StatusCode::OK, Json(json!({ "success": true, "data": assignees })))
}

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/threads",
    tag = "Omnichannel",
    params(
        ("scope" = Option<String>, Query, description = "mine, unassigned, all, resolved, favourites"),
        ("search" = Option<String>, Query, description = "Search name or phone"),
        ("tag" = Option<String>, Query, description = "Filter tag")
    ),
    responses(
        (status = 200, description = "List conversations", body = Vec<OmnichannelThread>)
    )
)]
pub async fn list_threads(
    Query(params): Query<ThreadQueryParams>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let threads = state.omnichannel_threads.read().await;

    let filtered: Vec<OmnichannelThread> = threads
        .iter()
        .filter(|t| {
            if let Some(scope) = &params.scope {
                match scope.as_str() {
                    "mine" => {
                        if !t.assignment.is_assigned {
                            return false;
                        }
                    }
                    "unassigned" => {
                        if t.assignment.is_assigned {
                            return false;
                        }
                    }
                    "resolved" => {
                        if t.status != "resolved" {
                            return false;
                        }
                    }
                    "favourites" => {
                        if !t.is_favourite {
                            return false;
                        }
                    }
                    _ => {}
                }
            }

            if let Some(q) = &params.search {
                let q_lower = q.to_lowercase();
                let match_name = t.customer.name.to_lowercase().contains(&q_lower);
                let match_phone = t.customer.phone.contains(&q_lower);
                let match_text = t.last_message.text.to_lowercase().contains(&q_lower);
                if !match_name && !match_phone && !match_text {
                    return false;
                }
            }

            if let Some(tag) = &params.tag {
                if !t.tags.contains(tag) {
                    return false;
                }
            }

            true
        })
        .cloned()
        .collect();

    (
        StatusCode::OK,
        Json(json!({
            "success": true,
            "data": filtered,
            "total": filtered.len()
        })),
    )
}

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/threads/{id}",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "Get thread detail", body = OmnichannelThread),
        (status = 404, description = "Thread not found")
    )
)]
pub async fn get_thread(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let threads = state.omnichannel_threads.read().await;
    if let Some(thread) = threads.iter().find(|t| t.id == id) {
        (StatusCode::OK, Json(json!({ "success": true, "data": thread })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

pub async fn upsert_thread(
    State(state): State<AppState>,
    Json(payload): Json<OmnichannelThread>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(pos) = threads.iter().position(|t| t.id == payload.id) {
        threads[pos] = payload.clone();
    } else {
        threads.insert(0, payload.clone());
    }
    (StatusCode::OK, Json(json!({ "success": true, "data": payload })))
}

#[utoipa::path(
    put,
    path = "/api/v1/omnichannel/threads/{id}/status",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "Thread status updated")
    )
)]
pub async fn update_thread_status(
    Path(id): Path<String>,
    State(state): State<AppState>,
    Json(payload): Json<UpdateThreadStatusPayload>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
        thread.status = payload.status.clone();
        if let Some(close_comment) = payload.close_comment {
            thread.remarks = Some(close_comment);
        }
        thread.updated_at = Utc::now().to_rfc3339();
        (StatusCode::OK, Json(json!({ "success": true, "data": thread })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

#[utoipa::path(
    put,
    path = "/api/v1/omnichannel/threads/{id}/assign",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "Thread assignment updated")
    )
)]
pub async fn assign_thread(
    Path(id): Path<String>,
    State(state): State<AppState>,
    Json(payload): Json<AssignThreadPayload>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
        if let Some(agent_id) = payload.agent_id {
            thread.assignment.is_assigned = true;
            thread.assignment.assigned_agent_id = Some(agent_id);
            thread.assignment.assigned_agent_name = payload.agent_name;
        } else {
            thread.assignment.is_assigned = false;
            thread.assignment.assigned_agent_id = None;
            thread.assignment.assigned_agent_name = None;
        }
        thread.updated_at = Utc::now().to_rfc3339();
        (StatusCode::OK, Json(json!({ "success": true, "data": thread })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

pub async fn toggle_favourite(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
        thread.is_favourite = !thread.is_favourite;
        (
            StatusCode::OK,
            Json(json!({
                "success": true,
                "data": { "id": thread.id, "is_favourite": thread.is_favourite }
            })),
        )
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

pub async fn get_thread_tags(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let threads = state.omnichannel_threads.read().await;
    if let Some(thread) = threads.iter().find(|t| t.id == id) {
        (StatusCode::OK, Json(json!({ "success": true, "data": thread.tags })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

pub async fn add_thread_tag(
    Path(id): Path<String>,
    State(state): State<AppState>,
    Json(payload): Json<ThreadTagPayload>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
        if !thread.tags.contains(&payload.tag) {
            thread.tags.push(payload.tag.clone());
        }
        (StatusCode::OK, Json(json!({ "success": true, "data": thread.tags })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

pub async fn remove_thread_tag(
    Path((id, tag)): Path<(String, String)>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let mut threads = state.omnichannel_threads.write().await;
    if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
        thread.tags.retain(|t| t != &tag);
        (StatusCode::OK, Json(json!({ "success": true, "data": thread.tags })))
    } else {
        (
            StatusCode::NOT_FOUND,
            Json(json!({ "success": false, "message": "Thread not found" })),
        )
    }
}

// ============ MESSAGES ============ //

#[utoipa::path(
    get,
    path = "/api/v1/omnichannel/threads/{id}/messages",
    tag = "Omnichannel",
    responses(
        (status = 200, description = "List messages in thread", body = Vec<OmnichannelMessage>)
    )
)]
pub async fn list_messages(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    // Mark thread unread as 0
    {
        let mut threads = state.omnichannel_threads.write().await;
        if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
            thread.unread_count = 0;
        }
    }

    let messages = state.omnichannel_messages.read().await;
    let thread_messages: Vec<OmnichannelMessage> = messages
        .iter()
        .filter(|m| m.thread_id == id)
        .cloned()
        .collect();

    (
        StatusCode::OK,
        Json(json!({ "success": true, "data": thread_messages })),
    )
}

#[utoipa::path(
    post,
    path = "/api/v1/omnichannel/threads/{id}/messages",
    tag = "Omnichannel",
    responses(
        (status = 201, description = "Message sent successfully", body = OmnichannelMessage)
    )
)]
pub async fn send_message(
    Path(id): Path<String>,
    State(state): State<AppState>,
    Json(payload): Json<SendMessagePayload>,
) -> impl IntoResponse {
    let now = Utc::now();
    let msg_id = format!("msg-{}", now.timestamp_millis());

    let new_message = OmnichannelMessage {
        id: msg_id.clone(),
        thread_id: id.clone(),
        sender_type: if payload.is_private_note.unwrap_or(false) {
            "internal_note".to_string()
        } else {
            "agent".to_string()
        },
        sender_name: "CS Agent".to_string(),
        direction: "outbound".to_string(),
        content: payload.content.clone(),
        message_type: payload.message_type,
        media_url: payload.media_url,
        is_private_note: payload.is_private_note.unwrap_or(false),
        delivery_status: "sent".to_string(),
        created_at: now.to_rfc3339(),
    };

    // Append to messages
    {
        let mut messages = state.omnichannel_messages.write().await;
        messages.push(new_message.clone());
    }

    // Update last message in thread
    {
        let mut threads = state.omnichannel_threads.write().await;
        if let Some(thread) = threads.iter_mut().find(|t| t.id == id) {
            if !new_message.is_private_note {
                thread.last_message.text = new_message.content.clone();
                thread.last_message.time_display = now.format("%H:%M").to_string();
                thread.last_message.timestamp = now.to_rfc3339();
            }
            thread.updated_at = now.to_rfc3339();
        }
    }

    (
        StatusCode::CREATED,
        Json(json!({ "success": true, "data": new_message })),
    )
}

// ============ QUICK REPLIES ============ //

pub async fn list_quick_replies(State(state): State<AppState>) -> impl IntoResponse {
    let qrs = state.omnichannel_quick_replies.read().await.clone();
    (StatusCode::OK, Json(json!({ "success": true, "data": qrs })))
}

pub async fn create_quick_reply(
    State(state): State<AppState>,
    Json(mut payload): Json<OmnichannelQuickReply>,
) -> impl IntoResponse {
    if payload.id.is_empty() {
        payload.id = format!("qr-{}", Utc::now().timestamp_millis());
    }
    let mut qrs = state.omnichannel_quick_replies.write().await;
    qrs.push(payload.clone());
    (StatusCode::CREATED, Json(json!({ "success": true, "data": payload })))
}

pub async fn delete_quick_reply(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let mut qrs = state.omnichannel_quick_replies.write().await;
    qrs.retain(|q| q.id != id);
    (StatusCode::OK, Json(json!({ "success": true })))
}

// ============ TAGS MASTER ============ //

pub async fn list_tags(State(state): State<AppState>) -> impl IntoResponse {
    let tags = state.omnichannel_tags.read().await.clone();
    (StatusCode::OK, Json(json!({ "success": true, "data": tags })))
}

pub async fn create_tag(
    State(state): State<AppState>,
    Json(mut payload): Json<OmnichannelTag>,
) -> impl IntoResponse {
    if payload.id.is_empty() {
        payload.id = format!("tag-{}", Utc::now().timestamp_millis());
    }
    let mut tags = state.omnichannel_tags.write().await;
    tags.push(payload.clone());
    (StatusCode::CREATED, Json(json!({ "success": true, "data": payload })))
}

pub async fn delete_tag(
    Path(id): Path<String>,
    State(state): State<AppState>,
) -> impl IntoResponse {
    let mut tags = state.omnichannel_tags.write().await;
    tags.retain(|t| t.id != id);
    (StatusCode::OK, Json(json!({ "success": true })))
}

// ============ BROADCAST ============ //

pub async fn create_broadcast(
    State(state): State<AppState>,
    Json(payload): Json<BroadcastPayload>,
) -> impl IntoResponse {
    let now = Utc::now();
    let bc_item = BroadcastHistoryItem {
        id: format!("bc-{}", now.timestamp_millis()),
        title: payload.title,
        message: payload.message,
        total_recipients: 100,
        success_count: 100,
        status: "completed".to_string(),
        created_at: now.format("%Y-%m-%d %H:%M:%S").to_string(),
    };

    let mut broadcasts = state.omnichannel_broadcasts.write().await;
    broadcasts.insert(0, bc_item.clone());

    (StatusCode::CREATED, Json(json!({ "success": true, "data": bc_item })))
}

pub async fn list_broadcast_history(State(state): State<AppState>) -> impl IntoResponse {
    let broadcasts = state.omnichannel_broadcasts.read().await.clone();
    (StatusCode::OK, Json(json!({ "success": true, "data": broadcasts })))
}

// ============ SIMULATED WEBHOOK ============ //

pub async fn handle_webhook(
    State(state): State<AppState>,
    Json(payload): Json<serde_json::Value>,
) -> impl IntoResponse {
    tracing::info!("Received WAHA omnichannel webhook: {:?}", payload);
    (StatusCode::OK, Json(json!({ "success": true })))
}
