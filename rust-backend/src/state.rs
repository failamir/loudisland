use std::sync::Arc;
use tokio::sync::RwLock;

use crate::{
    config::Config,
    models::{
        admin::{NomorPunggungItem, Permission, PromoCodeItem, ReferralCodeItem, Role, WithdrawalItem},
        auth::User,
        ticket::{Event, ParticipantRecord, TransactionRecord},
        omnichannel::{
            BroadcastHistoryItem, OmnichannelAssignee, OmnichannelChannel, OmnichannelCustomer,
            OmnichannelLastMessage, OmnichannelMessage, OmnichannelQuickReply, OmnichannelTag,
            OmnichannelThread, OmnichannelAssignment,
        },
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
    pub omnichannel_threads: Arc<RwLock<Vec<OmnichannelThread>>>,
    pub omnichannel_messages: Arc<RwLock<Vec<OmnichannelMessage>>>,
    pub omnichannel_tags: Arc<RwLock<Vec<OmnichannelTag>>>,
    pub omnichannel_quick_replies: Arc<RwLock<Vec<OmnichannelQuickReply>>>,
    pub omnichannel_assignees: Arc<RwLock<Vec<OmnichannelAssignee>>>,
    pub omnichannel_broadcasts: Arc<RwLock<Vec<BroadcastHistoryItem>>>,
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

        let seeded_threads = vec![
            OmnichannelThread {
                id: "th-001".to_string(),
                customer: OmnichannelCustomer {
                    id: "c-001".to_string(),
                    name: "Budi Santoso".to_string(),
                    initials: "BS".to_string(),
                    avatar_url: None,
                    phone: "6281311223344".to_string(),
                    email: Some("budi@gmail.com".to_string()),
                    bib_number: Some("5001".to_string()),
                    event_category: Some("5K Fun Run".to_string()),
                    shirt_size: Some("L".to_string()),
                    payment_status: Some("PAID".to_string()),
                },
                channel: OmnichannelChannel {
                    channel_type: "whatsapp".to_string(),
                    session_name: "korpri-cs-wa".to_string(),
                },
                last_message: OmnichannelLastMessage {
                    text: "Halo min, apakah nomor BIB saya sudah bisa diambil di Mandalika?".to_string(),
                    message_type: "text".to_string(),
                    time_display: "10:25".to_string(),
                    timestamp: "2026-11-10T10:25:00Z".to_string(),
                },
                assignment: OmnichannelAssignment {
                    is_assigned: true,
                    assigned_agent_id: Some("2".to_string()),
                    assigned_agent_name: Some("CS Agent Dewi".to_string()),
                },
                unread_count: 1,
                status: "open".to_string(),
                is_favourite: true,
                tags: vec!["Racepack".to_string(), "5K".to_string()],
                remarks: Some("Peserta tanya lokasi & jadwal RPC".to_string()),
                sla_status: Some("normal".to_string()),
                created_at: "2026-11-10T09:00:00Z".to_string(),
                updated_at: "2026-11-10T10:25:00Z".to_string(),
            },
            OmnichannelThread {
                id: "th-002".to_string(),
                customer: OmnichannelCustomer {
                    id: "c-002".to_string(),
                    name: "Siti Rahmawati".to_string(),
                    initials: "SR".to_string(),
                    avatar_url: None,
                    phone: "628198765432".to_string(),
                    email: Some("siti.rahma@korpri.go.id".to_string()),
                    bib_number: None,
                    event_category: Some("10K Race".to_string()),
                    shirt_size: Some("M".to_string()),
                    payment_status: Some("PENDING".to_string()),
                },
                channel: OmnichannelChannel {
                    channel_type: "whatsapp".to_string(),
                    session_name: "korpri-cs-wa".to_string(),
                },
                last_message: OmnichannelLastMessage {
                    text: "Selamat pagi, pembayaran QRIS saya tadi terpotong tapi status belum paid?".to_string(),
                    message_type: "text".to_string(),
                    time_display: "08:14".to_string(),
                    timestamp: "2026-11-10T08:14:00Z".to_string(),
                },
                assignment: OmnichannelAssignment {
                    is_assigned: false,
                    assigned_agent_id: None,
                    assigned_agent_name: None,
                },
                unread_count: 2,
                status: "open".to_string(),
                is_favourite: false,
                tags: vec!["Payment Issue".to_string()],
                remarks: None,
                sla_status: Some("warning".to_string()),
                created_at: "2026-11-10T08:00:00Z".to_string(),
                updated_at: "2026-11-10T08:14:00Z".to_string(),
            },
            OmnichannelThread {
                id: "th-003".to_string(),
                customer: OmnichannelCustomer {
                    id: "c-003".to_string(),
                    name: "Agus Prasetyo".to_string(),
                    initials: "AP".to_string(),
                    avatar_url: None,
                    phone: "6287711223399".to_string(),
                    email: Some("agus.p@dinas.go.id".to_string()),
                    bib_number: Some("2105".to_string()),
                    event_category: Some("Half Marathon 21K".to_string()),
                    shirt_size: Some("XL".to_string()),
                    payment_status: Some("PAID".to_string()),
                },
                channel: OmnichannelChannel {
                    channel_type: "whatsapp".to_string(),
                    session_name: "korpri-cs-wa".to_string(),
                },
                last_message: OmnichannelLastMessage {
                    text: "Terima kasih infonya min, sudah jelas.".to_string(),
                    message_type: "text".to_string(),
                    time_display: "Kemarin".to_string(),
                    timestamp: "2026-11-09T16:00:00Z".to_string(),
                },
                assignment: OmnichannelAssignment {
                    is_assigned: true,
                    assigned_agent_id: Some("1".to_string()),
                    assigned_agent_name: Some("Administrator Korpri".to_string()),
                },
                unread_count: 0,
                status: "resolved".to_string(),
                is_favourite: false,
                tags: vec!["Informasi".to_string(), "21K".to_string()],
                remarks: Some("Case resolved, info shuttle bus disampaikan".to_string()),
                sla_status: Some("normal".to_string()),
                created_at: "2026-11-09T14:00:00Z".to_string(),
                updated_at: "2026-11-09T16:00:00Z".to_string(),
            },
        ];

        let seeded_messages = vec![
            OmnichannelMessage {
                id: "msg-1".to_string(),
                thread_id: "th-001".to_string(),
                sender_type: "customer".to_string(),
                sender_name: "Budi Santoso".to_string(),
                direction: "inbound".to_string(),
                content: "Halo admin Korpri Run 2026".to_string(),
                message_type: "text".to_string(),
                media_url: None,
                is_private_note: false,
                delivery_status: "read".to_string(),
                created_at: "2026-11-10T10:20:00Z".to_string(),
            },
            OmnichannelMessage {
                id: "msg-2".to_string(),
                thread_id: "th-001".to_string(),
                sender_type: "agent".to_string(),
                sender_name: "CS Agent Dewi".to_string(),
                direction: "outbound".to_string(),
                content: "Halo Bapak Budi! Ada yang bisa kami bantu seputar tiket Mandalika Korpri Run?".to_string(),
                message_type: "text".to_string(),
                media_url: None,
                is_private_note: false,
                delivery_status: "delivered".to_string(),
                created_at: "2026-11-10T10:22:00Z".to_string(),
            },
            OmnichannelMessage {
                id: "msg-3".to_string(),
                thread_id: "th-001".to_string(),
                sender_type: "customer".to_string(),
                sender_name: "Budi Santoso".to_string(),
                direction: "inbound".to_string(),
                content: "Halo min, apakah nomor BIB saya sudah bisa diambil di Mandalika?".to_string(),
                message_type: "text".to_string(),
                media_url: None,
                is_private_note: false,
                delivery_status: "read".to_string(),
                created_at: "2026-11-10T10:25:00Z".to_string(),
            },
            OmnichannelMessage {
                id: "msg-4".to_string(),
                thread_id: "th-002".to_string(),
                sender_type: "customer".to_string(),
                sender_name: "Siti Rahmawati".to_string(),
                direction: "inbound".to_string(),
                content: "Selamat pagi, pembayaran QRIS saya tadi terpotong tapi status belum paid?".to_string(),
                message_type: "text".to_string(),
                media_url: None,
                is_private_note: false,
                delivery_status: "delivered".to_string(),
                created_at: "2026-11-10T08:14:00Z".to_string(),
            },
        ];

        let seeded_tags = vec![
            OmnichannelTag { id: "tag-1".to_string(), name: "Racepack".to_string(), color: "#3B82F6".to_string(), usage_count: 1 },
            OmnichannelTag { id: "tag-2".to_string(), name: "Payment Issue".to_string(), color: "#EF4444".to_string(), usage_count: 1 },
            OmnichannelTag { id: "tag-3".to_string(), name: "5K".to_string(), color: "#10B981".to_string(), usage_count: 1 },
            OmnichannelTag { id: "tag-4".to_string(), name: "10K".to_string(), color: "#F59E0B".to_string(), usage_count: 0 },
            OmnichannelTag { id: "tag-5".to_string(), name: "21K".to_string(), color: "#8B5CF6".to_string(), usage_count: 1 },
            OmnichannelTag { id: "tag-6".to_string(), name: "Informasi".to_string(), color: "#06B6D4".to_string(), usage_count: 1 },
        ];

        let seeded_quick_replies = vec![
            OmnichannelQuickReply {
                id: "qr-1".to_string(),
                shortcut: "/rpc".to_string(),
                title: "Jadwal & Lokasi Pengambilan Racepack".to_string(),
                content: "Halo! Pengambilan Racepack Korpri Run 2026 dapat dilakukan di Sirkuit Mandalika pada tanggal 18-19 November 2026 pukul 09:00 - 18:00 WITA. Wajib membawa e-ticket dan KTP asli ya!".to_string(),
                category: "Racepack".to_string(),
            },
            OmnichannelQuickReply {
                id: "qr-2".to_string(),
                shortcut: "/bayar".to_string(),
                title: "Kendala Pembayaran / Midtrans".to_string(),
                content: "Mohon kirimkan bukti mutasi/potong saldo dan Order ID Anda, tim finance kami akan segera melakukan verifikasi pembayaran dalam 10 menit.".to_string(),
                category: "Pembayaran".to_string(),
            },
            OmnichannelQuickReply {
                id: "qr-3".to_string(),
                shortcut: "/jersey".to_string(),
                title: "Perubahan Ukuran Jersey".to_string(),
                content: "Perubahan ukuran jersey dapat dilakukan maksimal H-14 acara langsung melalui menu 'Tiket Saya' di aplikasi sebelum kuota ukuran terkunci.".to_string(),
                category: "Jersey".to_string(),
            },
        ];

        let seeded_assignees = vec![
            OmnichannelAssignee {
                id: "1".to_string(),
                name: "Administrator Korpri".to_string(),
                role: "Super Admin".to_string(),
                role_badge: "Admin".to_string(),
                email: "admin@korpri.go.id".to_string(),
                active_chats_count: 1,
                is_unassigned: false,
            },
            OmnichannelAssignee {
                id: "2".to_string(),
                name: "CS Agent Dewi".to_string(),
                role: "Helpdesk Support".to_string(),
                role_badge: "CS".to_string(),
                email: "cs.dewi@korpri.go.id".to_string(),
                active_chats_count: 1,
                is_unassigned: false,
            },
        ];

        let seeded_broadcasts = vec![
            BroadcastHistoryItem {
                id: "bc-001".to_string(),
                title: "Pemberitahuan Racepack Collection Mandalika".to_string(),
                message: "Halo Pelari Korpri Run! Jangan lupa jadwal pengambilan racepack dimulai tanggal 18 November 2026.".to_string(),
                total_recipients: 1500,
                success_count: 1492,
                status: "completed".to_string(),
                created_at: "2026-11-05 09:00:00".to_string(),
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
            omnichannel_threads: Arc::new(RwLock::new(seeded_threads)),
            omnichannel_messages: Arc::new(RwLock::new(seeded_messages)),
            omnichannel_tags: Arc::new(RwLock::new(seeded_tags)),
            omnichannel_quick_replies: Arc::new(RwLock::new(seeded_quick_replies)),
            omnichannel_assignees: Arc::new(RwLock::new(seeded_assignees)),
            omnichannel_broadcasts: Arc::new(RwLock::new(seeded_broadcasts)),
        }
    }
}
