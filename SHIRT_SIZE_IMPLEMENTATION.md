# Implementasi Field Ukuran Baju (Shirt Size)

## Ringkasan
Solusi untuk menambahkan field ukuran baju pada sistem pendaftaran yang sudah berjalan.

## Perubahan Backend

### 1. Migration Database
File: `database/migrations/2025_10_13_000900_add_shirt_size_to_participants_table.php`
- Menambahkan kolom `shirt_size` (nullable) ke tabel `participants`
- Posisi: setelah kolom `city`

**Jalankan migration:**
```bash
php artisan migrate
```

### 2. Model Participant
File: `app/Models/Participant.php`
- Menambahkan `shirt_size` ke array `$fillable`

### 3. Controller & Validasi
File: `app/Http/Controllers/Api/V1/Admin/PendaftarController.php`

**Perubahan:**
- Validasi di method `beliApi()`: menambahkan `'participants.*.shirt_size' => 'nullable|string|in:XS,S,M,L,XL,XXL,XXXL'`
- Method `postPaymentSuccessActions()`: menambahkan `'shirt_size' => $p['shirt_size'] ?? null` saat create participant

**Endpoint baru untuk update:**
- `PUT /api/v1/participants/{participant_id}/shirt-size` - Update satu participant
- `POST /api/v1/participants/bulk-update-shirt-size` - Bulk update multiple participants

### 4. Routes API
File: `routes/api.php`
- Menambahkan route untuk update shirt size (single & bulk)

## Perubahan Frontend

### Contoh Implementasi di Form Pendaftaran

Tambahkan field shirt size untuk setiap participant di form:

```jsx
// Contoh untuk OrderWizard.jsx atau form pendaftaran lainnya

const SHIRT_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];

// Di dalam form participant:
<div>
  <label className="block text-sm mb-1">Ukuran Baju</label>
  <select 
    className="form-select w-full" 
    value={participant.shirt_size || ''} 
    onChange={e => updateParticipant(index, 'shirt_size', e.target.value)}
  >
    <option value="">Pilih Ukuran</option>
    {SHIRT_SIZES.map(size => (
      <option key={size} value={size}>{size}</option>
    ))}
  </select>
</div>
```

### Payload ke API `/buy`

Pastikan setiap participant object menyertakan `shirt_size`:

```javascript
const payload = {
  userId: userUid,
  participants: [
    {
      ticketId: 1,
      name: "John Doe",
      email: "john@example.com",
      phone: "08123456789",
      nik: "1234567890123456",
      province: "Nusa Tenggara Barat",
      city: "Lombok Tengah",
      shirt_size: "L"  // ← Field baru (optional)
    },
    // ... participant lainnya
  ]
};

await axios.post(`${API_URL}/buy`, payload);
```

## Solusi untuk Data yang Sudah Ada

### Opsi 1: Update Manual via API

Gunakan endpoint bulk update untuk mengisi shirt_size peserta yang sudah terdaftar:

```javascript
// Contoh request
POST /api/v1/participants/bulk-update-shirt-size
Content-Type: application/json

{
  "updates": [
    {
      "participant_id": "PID-ABC123",
      "shirt_size": "L"
    },
    {
      "participant_id": "PID-XYZ789",
      "shirt_size": "M"
    }
  ]
}
```

**Response:**
```json
{
  "message": "Bulk update completed",
  "updated_count": 2,
  "failed_count": 0,
  "updated": [...],
  "failed": []
}
```

### Opsi 2: Update via Interface Admin

Buat halaman admin untuk:
1. List semua participants yang belum punya shirt_size
2. Form untuk update shirt_size per participant atau bulk update
3. Export/import CSV untuk update massal

### Opsi 3: Kirim Form ke Peserta

1. Buat form sederhana yang bisa diakses peserta via link
2. Peserta input participant_id dan pilih shirt_size
3. Form submit ke endpoint update shirt size

## Ukuran Baju yang Tersedia

- **XS** - Extra Small
- **S** - Small  
- **M** - Medium
- **L** - Large
- **XL** - Extra Large
- **XXL** - Double Extra Large
- **XXXL** - Triple Extra Large

## Testing

### Test Migration
```bash
php artisan migrate:fresh --seed  # Hati-hati! Ini akan reset database
# atau
php artisan migrate  # Hanya jalankan migration baru
```

### Test API Endpoints

**1. Test update single participant:**
```bash
curl -X PUT http://localhost/api/v1/participants/PID-ABC123/shirt-size \
  -H "Content-Type: application/json" \
  -d '{"shirt_size": "L"}'
```

**2. Test bulk update:**
```bash
curl -X POST http://localhost/api/v1/participants/bulk-update-shirt-size \
  -H "Content-Type: application/json" \
  -d '{
    "updates": [
      {"participant_id": "PID-ABC123", "shirt_size": "L"},
      {"participant_id": "PID-XYZ789", "shirt_size": "M"}
    ]
  }'
```

**3. Test pendaftaran baru dengan shirt_size:**
```bash
curl -X POST http://localhost/api/v1/buy \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "userId": "USER123",
    "participants": [{
      "ticketId": 1,
      "name": "Test User",
      "email": "test@example.com",
      "phone": "08123456789",
      "nik": "1234567890123456",
      "province": "Nusa Tenggara Barat",
      "city": "Lombok Tengah",
      "shirt_size": "L"
    }]
  }'
```

## Catatan Penting

1. **Field shirt_size bersifat OPTIONAL (nullable)**
   - Pendaftaran lama tanpa shirt_size tetap valid
   - Tidak akan error jika shirt_size tidak diisi

2. **Validasi**
   - Hanya menerima nilai: XS, S, M, L, XL, XXL, XXXL
   - Case sensitive (harus huruf kapital)

3. **Backward Compatibility**
   - Sistem tetap berjalan untuk data lama
   - Tidak ada breaking changes

4. **Data Migration**
   - Tidak perlu update data lama secara paksa
   - Bisa diupdate bertahap sesuai kebutuhan

## Langkah Deployment

1. **Backup database terlebih dahulu**
   ```bash
   php artisan db:backup  # atau manual backup
   ```

2. **Pull code terbaru**
   ```bash
   git pull origin main
   ```

3. **Jalankan migration**
   ```bash
   php artisan migrate
   ```

4. **Clear cache (opsional)**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

5. **Update frontend**
   - Deploy frontend dengan form yang sudah diupdate
   - Atau update bertahap

## Troubleshooting

### Error: Column not found 'shirt_size'
**Solusi:** Jalankan migration
```bash
php artisan migrate
```

### Error: Validation failed for shirt_size
**Solusi:** Pastikan nilai yang dikirim adalah salah satu dari: XS, S, M, L, XL, XXL, XXXL (huruf kapital)

### Participant lama tidak punya shirt_size
**Solusi:** Ini normal. Gunakan bulk update API untuk mengisi data yang sudah ada.

## Kontak

Jika ada pertanyaan atau issue, hubungi tim development.
