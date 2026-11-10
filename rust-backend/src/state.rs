use std::sync::Arc;
use tokio::sync::RwLock;
use sqlx::Row;

use crate::{
    config::Config,
    db::DbPool,
    models::{
        admin::{NomorPunggungItem, Permission, PromoCodeItem, ReferralCodeItem, Role, WithdrawalItem},
        auth::User,
        ticket::{Event, ParticipantRecord, TransactionRecord},
    },
};

#[derive(Clone)]
pub struct AppState {
    pub config: Config,
    pub http_client: reqwest::Client,
    pub db: Option<DbPool>,
    pub users: Arc<RwLock<Vec<User>>>,
    pub events: Arc<RwLock<Vec<Event>>>,
    pub transactions: Arc<RwLock<Vec<TransactionRecord>>>,
    pub roles: Arc<RwLock<Vec<Role>>>,
    pub permissions: Arc<RwLock<Vec<Permission>>>,
    pub nomor_punggung: Arc<RwLock<Vec<NomorPunggungItem>>>,
    pub promo_codes: Arc<RwLock<Vec<PromoCodeItem>>>,
    pub referral_codes: Arc<RwLock<Vec<ReferralCodeItem>>>,
    pub withdrawals: Arc<RwLock<Vec<WithdrawalItem>>>,
}

impl AppState {
    pub async fn new(config: Config) -> Self {
        let http_client = reqwest::Client::builder().build().unwrap();

        let db_pool = if let Some(ref url) = config.database_url {
            match crate::db::init_pool(url).await {
                Ok(pool) => Some(pool),
                Err(e) => {
                    tracing::error!("Failed to initialize database pool: {}. Operating in in-memory mode.", e);
                    None
                }
            }
        } else {
            None
        };

        // Initialize with default fallback events
        let mut seeded_events = vec![
            Event {
                id: 1,
                nama_event: "Tiket Untuk ASN".to_string(),
                event_code: "KORPRI_ASN".to_string(),
                harga: "75000".to_string(),
                tanggal_mulai: "2026-11-20".to_string(),
            },
            Event {
                id: 2,
                nama_event: "Tiket Untuk Umum".to_string(),
                event_code: "KORPRI_UMUM".to_string(),
                harga: "200000".to_string(),
                tanggal_mulai: "2026-11-20".to_string(),
            },
        ];

        let mut seeded_users: Vec<User> = Vec::new();

        let mut seeded_transactions = Vec::new();

        // If MySQL connection is established, hydrate events & users & transactions from DB
        if let Some(ref pool) = db_pool {
            // Load events from MySQL
            if let Ok(rows) = sqlx::query(
                "SELECT id, nama_event, event_code, harga, tanggal_mulai FROM events WHERE deleted_at IS NULL"
            )
            .fetch_all(pool)
            .await
            {
                if !rows.is_empty() {
                    seeded_events = rows
                        .into_iter()
                        .map(|r| {
                            let id: u64 = r.get("id");
                            let nama_event: Option<String> = r.get("nama_event");
                            let event_code: Option<String> = r.get("event_code");
                            let harga: Option<String> = r.get("harga");
                            let tanggal_mulai: Option<chrono::NaiveDateTime> = r.get("tanggal_mulai");

                            Event {
                                id: id as i64,
                                nama_event: nama_event.unwrap_or_default(),
                                event_code: event_code.unwrap_or_default(),
                                harga: harga.unwrap_or_else(|| "0".to_string()),
                                tanggal_mulai: tanggal_mulai
                                    .map(|d| d.format("%Y-%m-%d").to_string())
                                    .unwrap_or_else(|| "2026-11-20".to_string()),
                            }
                        })
                        .collect();
                    tracing::info!("Loaded {} events from MySQL database", seeded_events.len());
                }
            }

            // Load users from MySQL
            if let Ok(rows) = sqlx::query(
                "SELECT id, uid, name, email, password, nik, no_hp, created_at FROM users WHERE deleted_at IS NULL LIMIT 200"
            )
            .fetch_all(pool)
            .await
            {
                if !rows.is_empty() {
                    for r in rows {
                        let email: Option<String> = r.get("email");
                        let email_str = email.unwrap_or_default();
                        if email_str.is_empty() { continue; }

                        let id: u64 = r.get("id");
                        let uid: Option<String> = r.get("uid");
                        let name: Option<String> = r.get("name");
                        let password: Option<String> = r.get("password");
                        let nik: Option<String> = r.get("nik");
                        let no_hp: Option<String> = r.get("no_hp");
                        let created_at: Option<chrono::DateTime<chrono::Utc>> = r.get("created_at");

                        seeded_users.push(User {
                            id: id as i64,
                            uid: uid.unwrap_or_else(|| format!("usr-{}", id)),
                            name: name.unwrap_or_else(|| "User".to_string()),
                            email: email_str,
                            password_hash: password.unwrap_or_default(),
                            nik,
                            no_hp,
                            token_version: 0,
                            created_at: created_at
                                .map(|d| d.to_rfc3339())
                                .unwrap_or_else(|| "2026-10-01T00:00:00Z".to_string()),
                        });
                    }
                    tracing::info!("Loaded {} users from MySQL database", seeded_users.len());
                }
            }

            // Load transactions from MySQL
            if let Ok(rows) = sqlx::query(
                "SELECT id, invoice, amount, status, email, nama, no_hp, nik, created_at, payment_url, participants FROM transactions WHERE deleted_at IS NULL LIMIT 100"
            )
            .fetch_all(pool)
            .await
            {
                for r in rows {
                    let invoice: Option<String> = r.get("invoice");
                    let invoice_str = invoice.unwrap_or_default();
                    if invoice_str.is_empty() { continue; }

                    let id: u64 = r.get("id");
                    let amount: Option<f64> = r.try_get("amount").ok();
                    let status: Option<String> = r.get("status");
                    let email: Option<String> = r.get("email");
                    let nama: Option<String> = r.get("nama");
                    let no_hp: Option<String> = r.get("no_hp");
                    let nik: Option<String> = r.get("nik");
                    let created_at: Option<chrono::DateTime<chrono::Utc>> = r.get("created_at");
                    let payment_url: Option<String> = r.get("payment_url");
                    let participants_str: String = r.get("participants");

                    let mut participants = Vec::new();
                    if let Ok(parsed) = serde_json::from_str::<Vec<serde_json::Value>>(&participants_str) {
                        for (idx, p) in parsed.into_iter().enumerate() {
                            participants.push(ParticipantRecord {
                                participant_id: format!("PART-{}", idx + 1),
                                ticket_id: p.get("ticketId").and_then(|v| v.as_str()).unwrap_or("1").to_string(),
                                name: p.get("name").and_then(|v| v.as_str()).unwrap_or_default().to_string(),
                                nik: p.get("nik").and_then(|v| v.as_str()).map(|s| s.to_string()),
                                email: p.get("email").and_then(|v| v.as_str()).unwrap_or_default().to_string(),
                                phone: p.get("phone").and_then(|v| v.as_str()).map(|s| s.to_string()),
                                province: p.get("province").and_then(|v| v.as_str()).map(|s| s.to_string()),
                                city: p.get("city").and_then(|v| v.as_str()).map(|s| s.to_string()),
                                shirt_size: p.get("shirt_size").and_then(|v| v.as_str()).map(|s| s.to_string()),
                                status: 1,
                                status_racepack: 0,
                            });
                        }
                    }

                    seeded_transactions.push(TransactionRecord {
                        id: id as i64,
                        invoice: invoice_str,
                        peserta_id: 1,
                        uid: None,
                        email: email.unwrap_or_default(),
                        nama: nama.unwrap_or_default(),
                        phone: no_hp,
                        nik,
                        amount: amount.map(|a| a.to_string()).unwrap_or_else(|| "0".to_string()),
                        note: Some("Pembelian Tiket Korpri Run".to_string()),
                        status: status.unwrap_or_else(|| "pending".to_string()),
                        created_at: created_at.map(|d| d.to_string()).unwrap_or_default(),
                        payment_url,
                        expired_snap_time: None,
                        events: vec![1],
                        participants,
                    });
                }
                tracing::info!("Loaded {} transactions from MySQL database", seeded_transactions.len());
            }
        }

        let seeded_roles = vec![
            Role {
                id: 1,
                title: "Super Admin".to_string(),
                permission_ids: vec![1, 2, 3, 4, 5],
            },
            Role {
                id: 2,
                title: "Admin".to_string(),
                permission_ids: vec![1, 2, 3],
            },
            Role {
                id: 3,
                title: "User".to_string(),
                permission_ids: vec![2],
            },
        ];

        let seeded_permissions = vec![
            Permission { id: 1, title: "user_management_access".to_string() },
            Permission { id: 2, title: "ticket_access".to_string() },
            Permission { id: 3, title: "order_access".to_string() },
            Permission { id: 4, title: "withdrawal_access".to_string() },
            Permission { id: 5, title: "report_access".to_string() },
        ];

        let seeded_nomor_punggung = vec![
            NomorPunggungItem {
                id: 1,
                nomor: "A-001".to_string(),
                participant_id: Some("PART-1".to_string()),
                paired_at: Some("2026-10-25 10:00:00".to_string()),
            },
            NomorPunggungItem {
                id: 2,
                nomor: "B-002".to_string(),
                participant_id: Some("PART-2".to_string()),
                paired_at: Some("2026-10-25 10:05:00".to_string()),
            },
        ];

        let seeded_promo_codes = vec![
            PromoCodeItem {
                id: 1,
                code: "KORPRIHEBAT".to_string(),
                discount_amount: 25000.0,
                discount_percent: None,
                max_usage: 100,
                usage_count: 5,
                is_active: true,
            }
        ];

        let seeded_referral_codes = vec![
            ReferralCodeItem {
                id: 1,
                user_id: 1,
                code: "REF-ADMIN".to_string(),
                commission_percent: 10.0,
                total_earned: 15500.0,
                status: "active".to_string(),
            }
        ];

        let seeded_withdrawals = vec![
            WithdrawalItem {
                id: 1,
                user_id: 1,
                amount: 500000.0,
                bank_name: "BCA".to_string(),
                account_number: "1234567890".to_string(),
                status: "approved".to_string(),
                created_at: "2026-10-20 14:00:00".to_string(),
            }
        ];

        Self {
            config,
            http_client,
            db: db_pool,
            users: Arc::new(RwLock::new(seeded_users)),
            events: Arc::new(RwLock::new(seeded_events)),
            transactions: Arc::new(RwLock::new(seeded_transactions)),
            roles: Arc::new(RwLock::new(seeded_roles)),
            permissions: Arc::new(RwLock::new(seeded_permissions)),
            nomor_punggung: Arc::new(RwLock::new(seeded_nomor_punggung)),
            promo_codes: Arc::new(RwLock::new(seeded_promo_codes)),
            referral_codes: Arc::new(RwLock::new(seeded_referral_codes)),
            withdrawals: Arc::new(RwLock::new(seeded_withdrawals)),
        }
    }
}
