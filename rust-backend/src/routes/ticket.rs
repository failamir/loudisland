use axum::{
    extract::{Path, State},
    http::StatusCode,
    response::IntoResponse,
    routing::{get, post, put},
    Json, Router,
};
use chrono::Utc;
use serde_json::json;
use uuid::Uuid;

use crate::{
    error::{AppError, AppResult},
    middleware::auth::AuthUser,
    models::ticket::{
        EventListResponse, MultiOrderRequest, MyOrderItem, MyOrderListResponse,
        MyOrderParticipantDecoded, MyTicketEvent, MyTicketItem, MyTicketListResponse,
        MyTicketParticipant, OrderSuccessParticipant, OrderSuccessResponse, ParticipantRecord,
        PaymentParticipantDetail, PaymentStatusResponse, PaymentUserDetail, SendImageRequest,
        SendImageResponse, TransactionRecord, UpdateShirtSizeRequest, UpdateShirtSizeResponse,
    },
    state::AppState,
};

pub fn router() -> Router<AppState> {
    Router::new()
        .route("/events", get(list_events))
        .route("/buy", post(buy_tickets))
        .route("/payment/:invoice", get(payment_status))
        .route("/myorder", post(my_order))
        .route("/myticket", post(my_ticket))
        .route("/participants/:participant_id/shirt-size", put(update_shirt_size))
        .route("/waha/sendImage", post(waha_send_image))
}

#[utoipa::path(
    get,
    path = "/api/v1/events",
    tag = "Tickets & Events",
    responses(
        (status = 200, description = "Daftar event / tiket kategori", body = EventListResponse)
    )
)]
pub async fn list_events(State(state): State<AppState>) -> AppResult<impl IntoResponse> {
    let events = state.events.read().await;
    Ok(Json(EventListResponse {
        data: events.clone(),
    }))
}

#[utoipa::path(
    post,
    path = "/api/v1/buy",
    tag = "Tickets & Events",
    security(
        ("bearer_auth" = [])
    ),
    request_body = MultiOrderRequest,
    responses(
        (status = 201, description = "Order tiket multi-peserta berhasil dibuat", body = OrderSuccessResponse),
        (status = 400, description = "Peserta kosong atau data tidak valid"),
        (status = 401, description = "Unauthorized")
    )
)]
pub async fn buy_tickets(
    auth_user: AuthUser,
    State(state): State<AppState>,
    Json(payload): Json<MultiOrderRequest>,
) -> AppResult<impl IntoResponse> {
    if payload.participants.is_empty() {
        return Err(AppError::BadRequest("Participants list cannot be empty".into()));
    }

    let events = state.events.read().await;
    let mut total_amount: f64 = 0.0;
    let mut participant_records = Vec::new();
    let mut event_ids = Vec::new();
    let mut success_participants = Vec::new();

    for p in &payload.participants {
        let t_id = p.ticket_id.clone().unwrap_or_else(|| "1".to_string());
        let ev_id: i64 = t_id.parse().unwrap_or(1);
        event_ids.push(ev_id);

        let event = events.iter().find(|e| e.id == ev_id);
        let price: f64 = event
            .and_then(|e| e.harga.parse().ok())
            .unwrap_or(150000.0);
        total_amount += price;

        let pid = format!("PART-{}", Uuid::new_v4().to_string()[..8].to_uppercase());

        participant_records.push(ParticipantRecord {
            participant_id: pid.clone(),
            ticket_id: t_id.clone(),
            name: p.name.clone().unwrap_or_else(|| "Peserta".to_string()),
            nik: p.nik.clone(),
            email: p.email.clone().unwrap_or_else(|| auth_user.email.clone()),
            phone: p.phone.clone(),
            province: p.province.clone(),
            city: p.city.clone(),
            shirt_size: p.shirt_size.clone(),
            status: 1,
            status_racepack: 0,
        });

        success_participants.push(OrderSuccessParticipant {
            ticket_id: Some(t_id),
            name: p.name.clone(),
            email: p.email.clone(),
            phone: p.phone.clone(),
            nik: p.nik.clone(),
            province: p.province.clone(),
            city: p.city.clone(),
        });
    }

    let service_fee: f64 = 5000.0;
    let total_payment = total_amount + service_fee;
    let invoice = format!("INV-{}", Uuid::new_v4().to_string()[..8].to_uppercase());
    let expired_snap_time = Utc::now()
        .checked_add_signed(chrono::Duration::days(1))
        .unwrap()
        .format("%Y-%m-%d %H:%M:%S")
        .to_string();

    let first_participant = participant_records.first();
    let cust_name = first_participant.map(|p| p.name.clone()).unwrap_or_else(|| auth_user.email.clone());
    let cust_phone = first_participant.and_then(|p| p.phone.clone()).unwrap_or_default();

    let mut payment_url = if state.config.midtrans_is_production {
        format!("https://app.midtrans.com/snap/v2/vtweb/{}", invoice)
    } else {
        format!("https://app.sandbox.midtrans.com/snap/v2/vtweb/{}", invoice)
    };

    if !state.config.midtrans_server_key.is_empty() {
        let snap_api_url = if state.config.midtrans_is_production {
            "https://app.midtrans.com/snap/v1/transactions"
        } else {
            "https://app.sandbox.midtrans.com/snap/v1/transactions"
        };

        use base64::Engine;
        let basic_auth = format!(
            "Basic {}",
            base64::engine::general_purpose::STANDARD.encode(format!("{}:", state.config.midtrans_server_key))
        );

        let snap_body = json!({
            "transaction_details": {
                "order_id": invoice,
                "gross_amount": total_payment as i64
            },
            "customer_details": {
                "first_name": cust_name,
                "email": auth_user.email,
                "phone": cust_phone
            }
        });

        if let Ok(resp) = state.http_client
            .post(snap_api_url)
            .header("Authorization", basic_auth)
            .header("Content-Type", "application/json")
            .header("Accept", "application/json")
            .json(&snap_body)
            .send()
            .await
        {
            if let Ok(snap_res) = resp.json::<serde_json::Value>().await {
                if let Some(redirect_url) = snap_res.get("redirect_url").and_then(|u| u.as_str()) {
                    payment_url = redirect_url.to_string();
                }
            }
        }
    }

    let new_trx = TransactionRecord {
        id: (state.transactions.read().await.len() as i64) + 1,
        invoice: invoice.clone(),
        peserta_id: auth_user.id,
        uid: Some(auth_user.uid),
        email: auth_user.email,
        nama: participant_records.first().map(|p| p.name.clone()).unwrap_or_default(),
        phone: participant_records.first().and_then(|p| p.phone.clone()),
        nik: participant_records.first().and_then(|p| p.nik.clone()),
        amount: total_payment.to_string(),
        note: Some("Pembelian Tiket Korpri Run".to_string()),
        status: "pending".to_string(),
        created_at: Utc::now().format("%Y-%m-%d %H:%M:%S").to_string(),
        payment_url: Some(payment_url.clone()),
        expired_snap_time: Some(expired_snap_time.clone()),
        events: event_ids,
        participants: participant_records,
    };

    state.transactions.write().await.push(new_trx);

    let response = OrderSuccessResponse {
        invoice,
        data: payment_url,
        participants: success_participants,
        service_fee,
        total_payment,
        total_amount,
        total_ticket: payload.participants.len().to_string(),
        expired_snap_time,
    };

    Ok((StatusCode::CREATED, Json(response)))
}

#[utoipa::path(
    get,
    path = "/api/v1/payment/{invoice}",
    tag = "Tickets & Events",
    params(
        ("invoice" = String, Path, description = "Kode invoice transaksi")
    ),
    responses(
        (status = 200, description = "Detail status pembayaran dan tiket", body = PaymentStatusResponse),
        (status = 404, description = "Transaksi tidak ditemukan")
    )
)]
pub async fn payment_status(
    Path(invoice): Path<String>,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let transactions = state.transactions.read().await;
    let trx = transactions
        .iter()
        .find(|t| t.invoice == invoice)
        .ok_or_else(|| AppError::NotFound("Transaksi tidak ditemukan".into()))?;

    let participants: Vec<PaymentParticipantDetail> = trx
        .participants
        .iter()
        .map(|p| PaymentParticipantDetail {
            participant_id: p.participant_id.clone(),
            name: p.name.clone(),
            nik: p.nik.clone(),
            email: p.email.clone(),
            phone: p.phone.clone(),
            province: p.province.clone(),
            city: p.city.clone(),
            ticket_id: p.ticket_id.clone(),
            status_racepack: p.status_racepack,
            qr_url: format!("https://api.qrserver.com/v1/create-qr-code/?data={}&size=200x200", p.participant_id),
        })
        .collect();

    let user_detail = PaymentUserDetail {
        id: trx.peserta_id,
        nama: trx.nama.clone(),
        email: trx.email.clone(),
        no_hp: trx.phone.clone(),
        status_payment: Some(trx.status.clone()),
        event_id: trx.events.first().copied(),
        nomor_punggung: None,
        start_at: None,
        finish_at: None,
    };

    let response = PaymentStatusResponse {
        invoice: trx.invoice.clone(),
        status: trx.status.clone(),
        amount: trx.amount.clone(),
        no_tiket: None,
        qr_url: None,
        expired_snap_time: trx.expired_snap_time.clone(),
        user: Some(user_detail),
        participants,
    };

    Ok(Json(response))
}

#[utoipa::path(
    post,
    path = "/api/v1/myorder",
    tag = "Tickets & Events",
    security(
        ("bearer_auth" = [])
    ),
    responses(
        (status = 200, description = "Riwayat pembelian user", body = MyOrderListResponse),
        (status = 401, description = "Unauthorized")
    )
)]
pub async fn my_order(
    auth_user: AuthUser,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let transactions = state.transactions.read().await;
    let events = state.events.read().await;

    let user_trxs: Vec<&TransactionRecord> = transactions
        .iter()
        .filter(|t| t.peserta_id == auth_user.id || t.email == auth_user.email)
        .collect();

    let items: Vec<MyOrderItem> = user_trxs
        .into_iter()
        .map(|t| {
            let participants_decoded = t
                .participants
                .iter()
                .map(|p| {
                    let ev_id: i64 = p.ticket_id.parse().unwrap_or(0);
                    let event_name = events.iter().find(|e| e.id == ev_id).map(|e| e.nama_event.clone());
                    MyOrderParticipantDecoded {
                        ticket_id: Some(p.ticket_id.clone()),
                        event_name,
                        nik: p.nik.clone(),
                        name: Some(p.name.clone()),
                        email: Some(p.email.clone()),
                        phone: p.phone.clone(),
                        province: p.province.clone(),
                        city: p.city.clone(),
                        shirt_size: p.shirt_size.clone(),
                    }
                })
                .collect();

            MyOrderItem {
                invoice: t.invoice.clone(),
                amount: t.amount.clone(),
                note: t.note.clone(),
                status: t.status.clone(),
                created_at: t.created_at.clone(),
                payment_url: t.payment_url.clone(),
                nama: t.nama.clone(),
                email: t.email.clone(),
                no_hp: t.phone.clone(),
                nik: t.nik.clone(),
                participants_decoded,
            }
        })
        .collect();

    Ok(Json(MyOrderListResponse {
        message: "success".to_string(),
        status: 200,
        data: items,
    }))
}

#[utoipa::path(
    post,
    path = "/api/v1/myticket",
    tag = "Tickets & Events",
    security(
        ("bearer_auth" = [])
    ),
    responses(
        (status = 200, description = "Tiket aktif milik user", body = MyTicketListResponse),
        (status = 401, description = "Unauthorized")
    )
)]
pub async fn my_ticket(
    auth_user: AuthUser,
    State(state): State<AppState>,
) -> AppResult<impl IntoResponse> {
    let transactions = state.transactions.read().await;
    let events = state.events.read().await;

    let user_trxs: Vec<&TransactionRecord> = transactions
        .iter()
        .filter(|t| (t.peserta_id == auth_user.id || t.email == auth_user.email) && t.status == "success")
        .collect();

    let mut ticket_items = Vec::new();

    for t in user_trxs {
        for p in &t.participants {
            let ev_id: i64 = p.ticket_id.parse().unwrap_or(0);
            let ev = events.iter().find(|e| e.id == ev_id).map(|e| MyTicketEvent {
                id: e.id,
                nama_event: e.nama_event.clone(),
                harga: e.harga.parse().unwrap_or(0.0),
                tanggal_mulai: Some(e.tanggal_mulai.clone()),
            });

            ticket_items.push(MyTicketItem {
                invoice: t.invoice.clone(),
                transaction_id: t.id,
                status: t.status.clone(),
                created_at: t.created_at.clone(),
                event: ev,
                participant: Some(MyTicketParticipant {
                    nik: p.nik.clone(),
                    name: p.name.clone(),
                    email: p.email.clone(),
                    phone: p.phone.clone(),
                    province: p.province.clone(),
                    city: p.city.clone(),
                    participant_id: p.participant_id.clone(),
                    shirt_size: p.shirt_size.clone(),
                    qr_url: format!("https://api.qrserver.com/v1/create-qr-code/?data={}&size=200x200", p.participant_id),
                }),
            });
        }
    }

    Ok(Json(MyTicketListResponse { data: ticket_items }))
}

#[utoipa::path(
    put,
    path = "/api/v1/participants/{participant_id}/shirt-size",
    tag = "Tickets & Events",
    params(
        ("participant_id" = String, Path, description = "ID Peserta (e.g. PART-001)")
    ),
    request_body = UpdateShirtSizeRequest,
    responses(
        (status = 200, description = "Ukuran jersey berhasil diperbarui", body = UpdateShirtSizeResponse),
        (status = 400, description = "Bad Request"),
        (status = 404, description = "Peserta tidak ditemukan")
    )
)]
pub async fn update_shirt_size(
    Path(participant_id): Path<String>,
    State(state): State<AppState>,
    Json(payload): Json<UpdateShirtSizeRequest>,
) -> AppResult<impl IntoResponse> {
    let new_size = payload.shirt_size.ok_or_else(|| {
        AppError::BadRequest("shirtSize is required".into())
    })?;

    let mut transactions = state.transactions.write().await;
    let mut updated = false;

    for trx in transactions.iter_mut() {
        for p in trx.participants.iter_mut() {
            if p.participant_id == participant_id {
                p.shirt_size = Some(new_size.clone());
                updated = true;
                break;
            }
        }
        if updated {
            break;
        }
    }

    if !updated {
        return Err(AppError::NotFound("Participant not found".into()));
    }

    Ok(Json(UpdateShirtSizeResponse {
        message: "Shirt size updated successfully".to_string(),
        participant_id,
        shirt_size: new_size,
    }))
}

#[utoipa::path(
    post,
    path = "/api/v1/waha/sendImage",
    tag = "WhatsApp / WAHA",
    request_body = SendImageRequest,
    responses(
        (status = 200, description = "Pesan gambar WhatsApp terkirim", body = SendImageResponse)
    )
)]
pub async fn waha_send_image(
    Json(payload): Json<SendImageRequest>,
) -> AppResult<impl IntoResponse> {
    Ok(Json(SendImageResponse {
        status: "success".to_string(),
        message: "Image sent successfully".to_string(),
        chat_id: payload.chat_id,
    }))
}
