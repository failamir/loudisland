use std::sync::Arc;
use tokio::sync::RwLock;

use crate::{
    config::Config,
    models::{
        auth::User,
        ticket::{Event, ParticipantRecord, TransactionRecord},
    },
};

#[derive(Clone)]
pub struct AppState {
    pub config: Config,
    pub http_client: reqwest::Client,
    pub users: Arc<RwLock<Vec<User>>>,
    pub events: Arc<RwLock<Vec<Event>>>,
    pub transactions: Arc<RwLock<Vec<TransactionRecord>>>,
}

impl AppState {
    pub fn new(config: Config) -> Self {
        let http_client = reqwest::Client::builder().build().unwrap();

        // Seed initial events
        let seeded_events = vec![
            Event {
                id: 1,
                nama_event: "5K Fun Run".to_string(),
                event_code: "5K-KORPRI".to_string(),
                harga: "150000".to_string(),
                tanggal_mulai: "2026-11-20".to_string(),
            },
            Event {
                id: 2,
                nama_event: "10K Race".to_string(),
                event_code: "10K-KORPRI".to_string(),
                harga: "250000".to_string(),
                tanggal_mulai: "2026-11-20".to_string(),
            },
            Event {
                id: 3,
                nama_event: "Half Marathon 21K".to_string(),
                event_code: "21K-KORPRI".to_string(),
                harga: "350000".to_string(),
                tanggal_mulai: "2026-11-20".to_string(),
            },
        ];

        // Seed default user for testing
        // Password is "password"
        let seeded_users = vec![
            User {
                id: 1,
                uid: "user-default-uid-123".to_string(),
                name: "Peserta Korpri".to_string(),
                email: "peserta@korpri.go.id".to_string(),
                password_hash: bcrypt::hash("password", 4).unwrap(),
                nik: Some("5201010101900001".to_string()),
                no_hp: Some("081234567890".to_string()),
                token_version: 0,
                created_at: "2026-01-01T00:00:00Z".to_string(),
            },
        ];

        // Seed sample completed transaction & ticket
        let seeded_transactions = vec![
            TransactionRecord {
                id: 1,
                invoice: "INV-KORPRI-001".to_string(),
                peserta_id: 1,
                uid: Some("user-default-uid-123".to_string()),
                email: "peserta@korpri.go.id".to_string(),
                nama: "Peserta Korpri".to_string(),
                phone: Some("081234567890".to_string()),
                nik: Some("5201010101900001".to_string()),
                amount: "150000".to_string(),
                note: Some("Pembelian Tiket 5K".to_string()),
                status: "success".to_string(),
                created_at: "2026-10-15 10:00:00".to_string(),
                payment_url: Some("https://app.sandbox.midtrans.com/snap/v2/vtweb/demo-001".to_string()),
                expired_snap_time: Some("2026-11-01 00:00:00".to_string()),
                events: vec![1],
                participants: vec![
                    ParticipantRecord {
                        participant_id: "PART-001".to_string(),
                        ticket_id: "1".to_string(),
                        name: "Peserta Korpri".to_string(),
                        nik: Some("5201010101900001".to_string()),
                        email: "peserta@korpri.go.id".to_string(),
                        phone: Some("081234567890".to_string()),
                        province: Some("Nusa Tenggara Barat".to_string()),
                        city: Some("Kota Mataram".to_string()),
                        shirt_size: Some("L".to_string()),
                        status: 1,
                        status_racepack: 0,
                    }
                ],
            }
        ];

        Self {
            config,
            http_client,
            users: Arc::new(RwLock::new(seeded_users)),
            events: Arc::new(RwLock::new(seeded_events)),
            transactions: Arc::new(RwLock::new(seeded_transactions)),
        }
    }
}

