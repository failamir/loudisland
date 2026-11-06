use std::sync::Arc;
use tokio::sync::RwLock;

use crate::{
    config::Config,
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

        // Seed default users (admin, staff, participant)
        let seeded_users = vec![
            User {
                id: 1,
                uid: "user-admin-uid-001".to_string(),
                name: "Super Admin".to_string(),
                email: "admin@loudisland.test".to_string(),
                password_hash: bcrypt::hash("password", 4).unwrap(),
                nik: Some("5201010101900001".to_string()),
                no_hp: Some("081234567890".to_string()),
                token_version: 0,
                created_at: "2026-01-01T00:00:00Z".to_string(),
            },
            User {
                id: 2,
                uid: "user-staff-uid-002".to_string(),
                name: "Staff Korpri".to_string(),
                email: "staff@loudisland.test".to_string(),
                password_hash: bcrypt::hash("password", 4).unwrap(),
                nik: Some("5201010101900002".to_string()),
                no_hp: Some("081234567891".to_string()),
                token_version: 0,
                created_at: "2026-01-01T00:00:00Z".to_string(),
            },
            User {
                id: 3,
                uid: "user-default-uid-123".to_string(),
                name: "Peserta Korpri".to_string(),
                email: "peserta@korpri.go.id".to_string(),
                password_hash: bcrypt::hash("password", 4).unwrap(),
                nik: Some("5201010101900003".to_string()),
                no_hp: Some("081234567892".to_string()),
                token_version: 0,
                created_at: "2026-01-01T00:00:00Z".to_string(),
            },
        ];

        // Seed sample completed transaction & ticket
        let seeded_transactions = vec![
            TransactionRecord {
                id: 1,
                invoice: "INV-KORPRI-001".to_string(),
                peserta_id: 3,
                uid: Some("user-default-uid-123".to_string()),
                email: "peserta@korpri.go.id".to_string(),
                nama: "Peserta Korpri".to_string(),
                phone: Some("081234567892".to_string()),
                nik: Some("5201010101900003".to_string()),
                amount: "155000".to_string(),
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
                        nik: Some("5201010101900003".to_string()),
                        email: "peserta@korpri.go.id".to_string(),
                        phone: Some("081234567892".to_string()),
                        province: Some("Nusa Tenggara Barat".to_string()),
                        city: Some("Kota Mataram".to_string()),
                        shirt_size: Some("L".to_string()),
                        status: 1,
                        status_racepack: 0,
                    }
                ],
            }
        ];

        let seeded_roles = vec![
            Role {
                id: 1,
                title: "Admin".to_string(),
                permission_ids: vec![1, 2, 3, 4],
            },
            Role {
                id: 2,
                title: "Staff".to_string(),
                permission_ids: vec![1, 2],
            },
            Role {
                id: 3,
                title: "Peserta".to_string(),
                permission_ids: vec![1],
            },
        ];

        let seeded_permissions = vec![
            Permission { id: 1, title: "view_dashboard".to_string() },
            Permission { id: 2, title: "manage_racepacks".to_string() },
            Permission { id: 3, title: "manage_transactions".to_string() },
            Permission { id: 4, title: "manage_users".to_string() },
        ];

        let seeded_nomor_punggung = vec![
            NomorPunggungItem {
                id: 1,
                nomor: "1001".to_string(),
                participant_id: Some("PART-001".to_string()),
                paired_at: Some("2026-10-16 09:00:00".to_string()),
            },
            NomorPunggungItem {
                id: 2,
                nomor: "1002".to_string(),
                participant_id: None,
                paired_at: None,
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
