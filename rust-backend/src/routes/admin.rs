use axum::{
    extract::{Path, Query, State},
    http::StatusCode,
    response::IntoResponse,
    routing::{delete, get, patch, post, put},
    Json, Router,
};
use chrono::Utc;
use serde::Deserialize;
use serde_json::json;
use uuid::Uuid;

use crate::{
    error::{AppError, AppResult},
    middleware::auth::AuthUser,
    models::{
        admin::{
            AdminUserItem, AdminUserUpsertRequest, BlastRequest, NomorPunggungGenerateRequest,
            NomorPunggungItem, NomorPunggungPairRequest, Permission, PermissionUpsertRequest,
            PromoCodeItem, PromoCodeUpsertRequest, RacepackItem, ReferralCodeItem,
            ReferralCodeUpsertRequest, Role, RoleUpsertRequest, TotalFinalIncomeResponse,
            TotalIncomeResponse, TotalIncomeSummary, WithdrawalItem, WithdrawalSummaryResponse,
        },
        auth::User,
        ticket::TransactionRecord,
    },
    state::AppState,
};

pub fn router() -> Router<AppState> {
    Router::new()
        // Users & RBAC
        .route("/users", get(list_users).post(create_user))
        .route("/users/:id", get(show_user).put(update_user).delete(delete_user))
        .route("/admin/users/:id/revoke-tokens", post(revoke_tokens))
        .route("/roles", get(list_roles).post(create_role))
        .route("/roles/:id", get(show_role).put(update_role).delete(delete_role))
        .route("/permissions", get(list_permissions).post(create_permission))
        .route("/permissions/:id", get(show_permission).put(update_permission).delete(delete_permission))
        // Transactions & Offline
        .route("/transactions", get(list_transactions).post(create_transaction))
        .route("/transactions/simple", get(list_transactions_simple))
        .route("/transactions/show", get(show_transaction_query))
        .route("/transactions/:id", get(show_transaction))
        .route("/offline-import", post(offline_import))
        // Operations: Racepacks, Staffs, BIB
        .route("/racepacks", get(list_racepacks))
        .route("/staffs", get(list_staffs))
        .route("/nomor-punggung", get(list_nomor_punggung))
        .route("/nomor-punggung/generate", post(generate_nomor_punggung))
        .route("/nomor-punggung/pair", post(pair_nomor_punggung))
        .route("/participants/missing", get(missing_participants))
        .route("/participants/duplicates", get(duplicate_participants))
        .route("/participants/whatsapp-blast", post(whatsapp_blast))
        .route("/participants/email-blast", post(email_blast))
        // Marketing & Finance
        .route("/promo-codes", get(list_promo_codes).post(create_promo_code))
        .route("/promo-codes/:id", delete(delete_promo_code))
        .route("/referral-codes", get(list_referral_codes).post(create_referral_code))
        .route("/referral-codes/:id", patch(update_referral_code).delete(delete_referral_code))
        .route("/withdrawals", get(list_withdrawals).post(create_withdrawal))
        .route("/withdrawals/summary", get(withdrawals_summary))
        .route("/total-income", get(get_total_income))
        .route("/total-final-income-participants", get(get_total_final_income))
}

#[derive(Debug, Deserialize)]
pub struct FilterQuery {
    pub status: Option<String>,
    pub search: Option<String>,
    pub id: Option<i64>,
    pub invoice: Option<String>,
}

// ----------------- USERS -----------------
#[utoipa::path(
    get,
    path = "/api/v1/users",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List users", body = Vec<AdminUserItem>))
)]
pub async fn list_users(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let list: Vec<AdminUserItem> = users
        .iter()
        .map(|u| AdminUserItem {
            id: u.id,
            uid: u.uid.clone(),
            name: u.name.clone(),
            email: u.email.clone(),
            nik: u.nik.clone(),
            no_hp: u.no_hp.clone(),
            roles: vec!["admin".to_string()],
            token_version: u.token_version,
            created_at: u.created_at.clone(),
        })
        .collect();
    Ok(Json(json!({ "data": list })))
}

#[utoipa::path(
    get,
    path = "/api/v1/users/{id}",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "Detail user", body = AdminUserItem))
)]
pub async fn show_user(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let u = users.iter().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("User not found".into()))?;
    Ok(Json(AdminUserItem {
        id: u.id,
        uid: u.uid.clone(),
        name: u.name.clone(),
        email: u.email.clone(),
        nik: u.nik.clone(),
        no_hp: u.no_hp.clone(),
        roles: vec!["admin".to_string()],
        token_version: u.token_version,
        created_at: u.created_at.clone(),
    }))
}

#[utoipa::path(
    post,
    path = "/api/v1/users",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    request_body = AdminUserUpsertRequest,
    responses((status = 201, description = "User created", body = AdminUserItem))
)]
pub async fn create_user(
    State(state): State<AppState>,
    Json(payload): Json<AdminUserUpsertRequest>,
) -> AppResult<impl IntoResponse> {
    let mut users = state.users.write().await;
    let new_id = (users.len() as i64) + 1;
    let pass = payload.password.as_deref().unwrap_or("password");
    let user = User {
        id: new_id,
        uid: Uuid::new_v4().to_string(),
        name: payload.name.clone(),
        email: payload.email.clone(),
        password_hash: bcrypt::hash(pass, 4).unwrap(),
        nik: payload.nik.clone(),
        no_hp: payload.no_hp.clone(),
        token_version: 0,
        created_at: Utc::now().to_rfc3339(),
    };
    users.push(user.clone());
    Ok((StatusCode::CREATED, Json(AdminUserItem {
        id: user.id,
        uid: user.uid,
        name: user.name,
        email: user.email,
        nik: user.nik,
        no_hp: user.no_hp,
        roles: payload.roles.unwrap_or_else(|| vec!["staff".to_string()]),
        token_version: 0,
        created_at: user.created_at,
    })))
}

#[utoipa::path(
    put,
    path = "/api/v1/users/{id}",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    request_body = AdminUserUpsertRequest,
    responses((status = 200, description = "User updated", body = AdminUserItem))
)]
pub async fn update_user(
    Path(id): Path<i64>,
    State(state): State<AppState>,
    Json(payload): Json<AdminUserUpsertRequest>,
) -> AppResult<impl IntoResponse> {
    let mut users = state.users.write().await;
    let u = users.iter_mut().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("User not found".into()))?;
    u.name = payload.name;
    u.email = payload.email;
    u.nik = payload.nik;
    u.no_hp = payload.no_hp;
    if let Some(pass) = payload.password {
        u.password_hash = bcrypt::hash(pass, 4).unwrap();
    }
    Ok(Json(json!({ "message": "User updated successfully" })))
}

#[utoipa::path(
    delete,
    path = "/api/v1/users/{id}",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "User deleted"))
)]
pub async fn delete_user(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut users = state.users.write().await;
    users.retain(|u| u.id != id);
    Ok(Json(json!({ "message": "User deleted" })))
}

#[utoipa::path(
    post,
    path = "/api/v1/admin/users/{id}/revoke-tokens",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "Tokens revoked"))
)]
pub async fn revoke_tokens(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut users = state.users.write().await;
    let u = users.iter_mut().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("User not found".into()))?;
    u.token_version += 1;
    Ok(Json(json!({ "message": "User tokens revoked successfully", "token_version": u.token_version })))
}

// ----------------- ROLES & PERMISSIONS -----------------
#[utoipa::path(
    get,
    path = "/api/v1/roles",
    tag = "Admin: Users & Roles",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List roles", body = Vec<Role>))
)]
pub async fn list_roles(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let roles = state.roles.read().await;
    Ok(Json(json!({ "data": *roles })))
}

pub async fn show_role(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let roles = state.roles.read().await;
    let r = roles.iter().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("Role not found".into()))?;
    Ok(Json(r.clone()))
}

pub async fn create_role(State(state): State<AppState>, Json(payload): Json<RoleUpsertRequest>) -> AppResult<impl IntoResponse> {
    let mut roles = state.roles.write().await;
    let new_id = (roles.len() as i64) + 1;
    let role = Role {
        id: new_id,
        title: payload.title,
        permission_ids: payload.permission_ids.unwrap_or_default(),
    };
    roles.push(role.clone());
    Ok((StatusCode::CREATED, Json(role)))
}

pub async fn update_role(Path(id): Path<i64>, State(state): State<AppState>, Json(payload): Json<RoleUpsertRequest>) -> AppResult<impl IntoResponse> {
    let mut roles = state.roles.write().await;
    let r = roles.iter_mut().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("Role not found".into()))?;
    r.title = payload.title;
    if let Some(p) = payload.permission_ids {
        r.permission_ids = p;
    }
    Ok(Json(r.clone()))
}

pub async fn delete_role(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut roles = state.roles.write().await;
    roles.retain(|r| r.id != id);
    Ok(Json(json!({ "message": "Role deleted" })))
}

pub async fn list_permissions(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let permissions = state.permissions.read().await;
    Ok(Json(json!({ "data": *permissions })))
}

pub async fn show_permission(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let p = state.permissions.read().await;
    let item = p.iter().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("Permission not found".into()))?;
    Ok(Json(item.clone()))
}

pub async fn create_permission(State(state): State<AppState>, Json(payload): Json<PermissionUpsertRequest>) -> AppResult<impl IntoResponse> {
    let mut p = state.permissions.write().await;
    let new_id = (p.len() as i64) + 1;
    let perm = Permission { id: new_id, title: payload.title };
    p.push(perm.clone());
    Ok((StatusCode::CREATED, Json(perm)))
}

pub async fn update_permission(Path(id): Path<i64>, State(state): State<AppState>, Json(payload): Json<PermissionUpsertRequest>) -> AppResult<impl IntoResponse> {
    let mut p = state.permissions.write().await;
    let item = p.iter_mut().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("Permission not found".into()))?;
    item.title = payload.title;
    Ok(Json(item.clone()))
}

pub async fn delete_permission(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut p = state.permissions.write().await;
    p.retain(|x| x.id != id);
    Ok(Json(json!({ "message": "Permission deleted" })))
}

// ----------------- TRANSACTIONS -----------------
#[utoipa::path(
    get,
    path = "/api/v1/transactions",
    tag = "Admin: Commerce",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List transactions", body = Vec<TransactionRecord>))
)]
pub async fn list_transactions(
    Query(query): Query<FilterQuery>,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let filtered: Vec<&TransactionRecord> = trxs
        .iter()
        .filter(|t| {
            if let Some(status) = &query.status {
                if !t.status.eq_ignore_ascii_case(status) {
                    return false;
                }
            }
            if let Some(q) = &query.search {
                if !t.invoice.to_lowercase().contains(&q.to_lowercase()) && !t.nama.to_lowercase().contains(&q.to_lowercase()) {
                    return false;
                }
            }
            true
        })
        .collect();

    Ok(Json(json!({
        "data": filtered,
        "total": filtered.len()
    })))
}

pub async fn list_transactions_simple(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    Ok(Json(json!({ "data": *trxs })))
}

pub async fn show_transaction_query(
    Query(query): Query<FilterQuery>,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let item = trxs.iter().find(|t| {
        if let Some(id) = query.id {
            t.id == id
        } else if let Some(inv) = &query.invoice {
            t.invoice.eq_ignore_ascii_case(inv)
        } else {
            false
        }
    }).ok_or_else(|| AppError::NotFound("Transaction not found".into()))?;

    Ok(Json(item.clone()))
}

pub async fn show_transaction(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let item = trxs.iter().find(|t| t.id == id).ok_or_else(|| AppError::NotFound("Transaction not found".into()))?;
    Ok(Json(item.clone()))
}

pub async fn create_transaction(
    auth_user: AuthUser,
    State(state): State<AppState>,
    Json(payload): Json<serde_json::Value>,
) -> AppResult<impl IntoResponse> {
    let mut trxs = state.transactions.write().await;
    let new_id = (trxs.len() as i64) + 1;
    let invoice = format!("TRX-{}", Uuid::new_v4().to_string()[..8].to_uppercase());
    let trx = TransactionRecord {
        id: new_id,
        invoice,
        peserta_id: auth_user.id,
        uid: Some(auth_user.uid),
        email: payload["email"].as_str().unwrap_or(&auth_user.email).to_string(),
        nama: payload["nama"].as_str().unwrap_or("Offline Buyer").to_string(),
        phone: payload["phone"].as_str().map(|s| s.to_string()),
        nik: payload["nik"].as_str().map(|s| s.to_string()),
        amount: payload["amount"].as_str().unwrap_or("150000").to_string(),
        note: payload["note"].as_str().map(|s| s.to_string()),
        status: payload["status"].as_str().unwrap_or("success").to_string(),
        created_at: Utc::now().format("%Y-%m-%d %H:%M:%S").to_string(),
        payment_url: None,
        expired_snap_time: None,
        events: vec![1],
        participants: Vec::new(),
    };
    trxs.push(trx.clone());
    Ok((StatusCode::CREATED, Json(trx)))
}

pub async fn offline_import(State(_state): State<AppState>) -> AppResult<impl IntoResponse> {
    Ok(Json(json!({
        "status": "success",
        "message": "CSV imported successfully (mock)",
        "inserted": 10
    })))
}

// ----------------- OPERATIONS & RACEPACKS -----------------
#[utoipa::path(
    get,
    path = "/api/v1/racepacks",
    tag = "Admin: Operations",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List racepack participants", body = Vec<RacepackItem>))
)]
pub async fn list_racepacks(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let mut list = Vec::new();
    let mut idx = 1;

    for t in trxs.iter() {
        for p in &t.participants {
            list.push(RacepackItem {
                id: idx,
                name: p.name.clone(),
                email: p.email.clone(),
                phone: p.phone.clone(),
                shirt_size: p.shirt_size.clone(),
                racepack_collected: p.status_racepack == 1,
                bib_number: Some("1001".to_string()),
                invoice: t.invoice.clone(),
                status: t.status.clone(),
            });
            idx += 1;
        }
    }

    Ok(Json(json!({ "data": list })))
}

#[utoipa::path(
    get,
    path = "/api/v1/staffs",
    tag = "Admin: Operations",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List staff users", body = Vec<AdminUserItem>))
)]
pub async fn list_staffs(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let users = state.users.read().await;
    let list: Vec<AdminUserItem> = users
        .iter()
        .map(|u| AdminUserItem {
            id: u.id,
            uid: u.uid.clone(),
            name: u.name.clone(),
            email: u.email.clone(),
            nik: u.nik.clone(),
            no_hp: u.no_hp.clone(),
            roles: vec!["staff".to_string()],
            token_version: u.token_version,
            created_at: u.created_at.clone(),
        })
        .collect();
    Ok(Json(json!({ "data": list })))
}

#[utoipa::path(
    get,
    path = "/api/v1/nomor-punggung",
    tag = "Admin: Operations",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List BIB numbers", body = Vec<NomorPunggungItem>))
)]
pub async fn list_nomor_punggung(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let items = state.nomor_punggung.read().await;
    Ok(Json(json!({ "data": *items })))
}

pub async fn generate_nomor_punggung(
    State(state): State<AppState>,
    Json(payload): Json<NomorPunggungGenerateRequest>,
) -> AppResult<impl IntoResponse> {
    let mut items = state.nomor_punggung.write().await;
    let count = payload.count.max(1);
    for _ in 0..count {
        let next_id = (items.len() as i64) + 1;
        let num = 1000 + next_id;
        items.push(NomorPunggungItem {
            id: next_id,
            nomor: num.to_string(),
            participant_id: None,
            paired_at: None,
        });
    }
    Ok(Json(json!({ "message": format!("Successfully generated {} BIB numbers", count) })))
}

pub async fn pair_nomor_punggung(
    State(state): State<AppState>,
    Json(payload): Json<NomorPunggungPairRequest>,
) -> AppResult<impl IntoResponse> {
    let mut items = state.nomor_punggung.write().await;
    let item = items.iter_mut().find(|x| x.nomor == payload.nomor).ok_or_else(|| AppError::NotFound("BIB number not found".into()))?;
    item.participant_id = Some(payload.participant_id);
    item.paired_at = Some(Utc::now().format("%Y-%m-%d %H:%M:%S").to_string());
    Ok(Json(json!({ "message": "Nomor punggung berhasil di-pair" })))
}

pub async fn missing_participants(State(_state): State<AppState>) -> AppResult<impl IntoResponse> {
    Ok(Json(json!({ "data": [] })))
}

pub async fn duplicate_participants(State(_state): State<AppState>) -> AppResult<impl IntoResponse> {
    Ok(Json(json!({ "data": [] })))
}

pub async fn whatsapp_blast(Json(_payload): Json<BlastRequest>) -> AppResult<impl IntoResponse> {
    Ok(Json(json!({ "status": "success", "message": "WhatsApp blast queued" })))
}

pub async fn email_blast(Json(_payload): Json<BlastRequest>) -> AppResult<impl IntoResponse> {
    Ok(Json(json!({ "status": "success", "message": "Email blast queued" })))
}

// ----------------- MARKETING & FINANCE -----------------
#[utoipa::path(
    get,
    path = "/api/v1/promo-codes",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List promo codes", body = Vec<PromoCodeItem>))
)]
pub async fn list_promo_codes(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let codes = state.promo_codes.read().await;
    Ok(Json(json!({ "data": *codes })))
}

pub async fn create_promo_code(
    State(state): State<AppState>,
    Json(payload): Json<PromoCodeUpsertRequest>,
) -> AppResult<impl IntoResponse> {
    let mut codes = state.promo_codes.write().await;
    let new_id = (codes.len() as i64) + 1;
    let item = PromoCodeItem {
        id: new_id,
        code: payload.code.to_uppercase(),
        discount_amount: payload.discount_amount.unwrap_or(0.0),
        discount_percent: payload.discount_percent,
        max_usage: payload.max_usage.unwrap_or(100),
        usage_count: 0,
        is_active: payload.is_active.unwrap_or(true),
    };
    codes.push(item.clone());
    Ok((StatusCode::CREATED, Json(item)))
}

pub async fn delete_promo_code(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut codes = state.promo_codes.write().await;
    codes.retain(|x| x.id != id);
    Ok(Json(json!({ "message": "Promo code deleted" })))
}

#[utoipa::path(
    get,
    path = "/api/v1/referral-codes",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List referral codes", body = Vec<ReferralCodeItem>))
)]
pub async fn list_referral_codes(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let codes = state.referral_codes.read().await;
    Ok(Json(json!({ "data": *codes })))
}

pub async fn create_referral_code(
    auth_user: AuthUser,
    State(state): State<AppState>,
    Json(payload): Json<ReferralCodeUpsertRequest>,
) -> AppResult<impl IntoResponse> {
    let mut codes = state.referral_codes.write().await;
    let new_id = (codes.len() as i64) + 1;
    let item = ReferralCodeItem {
        id: new_id,
        user_id: auth_user.id,
        code: payload.code.to_uppercase(),
        commission_percent: payload.commission_percent.unwrap_or(10.0),
        total_earned: 0.0,
        status: "active".to_string(),
    };
    codes.push(item.clone());
    Ok((StatusCode::CREATED, Json(item)))
}

pub async fn update_referral_code(
    Path(id): Path<i64>,
    State(state): State<AppState>,
    Json(payload): Json<ReferralCodeUpsertRequest>,
) -> AppResult<impl IntoResponse> {
    let mut codes = state.referral_codes.write().await;
    let item = codes.iter_mut().find(|x| x.id == id).ok_or_else(|| AppError::NotFound("Referral code not found".into()))?;
    item.code = payload.code.to_uppercase();
    if let Some(cp) = payload.commission_percent {
        item.commission_percent = cp;
    }
    Ok(Json(item.clone()))
}

pub async fn delete_referral_code(Path(id): Path<i64>, State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let mut codes = state.referral_codes.write().await;
    codes.retain(|x| x.id != id);
    Ok(Json(json!({ "message": "Referral code deleted" })))
}

#[utoipa::path(
    get,
    path = "/api/v1/withdrawals",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "List withdrawals", body = Vec<WithdrawalItem>))
)]
pub async fn list_withdrawals(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let items = state.withdrawals.read().await;
    Ok(Json(json!({ "data": *items })))
}

pub async fn create_withdrawal(
    auth_user: AuthUser,
    State(state): State<AppState>,
    Json(payload): Json<serde_json::Value>,
) -> AppResult<impl IntoResponse> {
    let mut items = state.withdrawals.write().await;
    let new_id = (items.len() as i64) + 1;
    let w = WithdrawalItem {
        id: new_id,
        user_id: auth_user.id,
        amount: payload["amount"].as_f64().unwrap_or(100000.0),
        bank_name: payload["bank_name"].as_str().unwrap_or("BCA").to_string(),
        account_number: payload["account_number"].as_str().unwrap_or("123456789").to_string(),
        status: "pending".to_string(),
        created_at: Utc::now().format("%Y-%m-%d %H:%M:%S").to_string(),
    };
    items.push(w.clone());
    Ok((StatusCode::CREATED, Json(w)))
}

#[utoipa::path(
    get,
    path = "/api/v1/withdrawals/summary",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "Withdrawal summary", body = WithdrawalSummaryResponse))
)]
pub async fn withdrawals_summary(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let items = state.withdrawals.read().await;
    let mut pending = 0.0;
    let mut approved = 0.0;
    let mut rejected = 0.0;

    for w in items.iter() {
        match w.status.as_str() {
            "pending" => pending += w.amount,
            "approved" => approved += w.amount,
            "rejected" => rejected += w.amount,
            _ => {}
        }
    }

    Ok(Json(WithdrawalSummaryResponse {
        total_pending: pending,
        total_approved: approved,
        total_rejected: rejected,
    }))
}

#[utoipa::path(
    get,
    path = "/api/v1/total-income",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "Total income dashboard", body = TotalIncomeResponse))
)]
pub async fn get_total_income(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let mut total_income = 0.0;
    let mut success_count = 0;

    for t in trxs.iter() {
        if t.status == "success" {
            total_income += t.amount.parse::<f64>().unwrap_or(0.0);
            success_count += 1;
        }
    }

    let profit = total_income * 0.15; // 15% margin mock

    Ok(Json(TotalIncomeResponse {
        total_income,
        summary: TotalIncomeSummary {
            profit,
            successful_transactions: success_count,
        },
    }))
}

#[utoipa::path(
    get,
    path = "/api/v1/total-final-income-participants",
    tag = "Admin: Marketing & Finance",
    security(("bearer_auth" = [])),
    responses((status = 200, description = "Total final income participants", body = TotalFinalIncomeResponse))
)]
pub async fn get_total_final_income(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let trxs = state.transactions.read().await;
    let mut total_final_price = 0.0;
    let mut total_participants = 0;

    for t in trxs.iter() {
        if t.status == "success" {
            total_final_price += t.amount.parse::<f64>().unwrap_or(0.0);
            total_participants += t.participants.len();
        }
    }

    Ok(Json(TotalFinalIncomeResponse {
        total_final_price,
        total_participants,
    }))
}
