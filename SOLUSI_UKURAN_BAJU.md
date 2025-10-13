# Solusi Penambahan Field Ukuran Baju

## Masalah
Client lupa menambahkan form ukuran baju untuk setiap partisipan, sedangkan pendaftaran sudah berjalan.

## Solusi yang Diterapkan

### ✅ Backend Changes

#### 1. **Database Migration**
- File: `database/migrations/2025_10_13_000900_add_shirt_size_to_participants_table.php`
- Menambahkan kolom `shirt_size` (nullable) ke tabel `participants`
- **Cara menjalankan:**
  ```bash
  php artisan migrate
  ```

#### 2. **Model Update**
- File: `app/Models/Participant.php`
- Menambahkan `shirt_size` ke `$fillable` array

#### 3. **Controller Updates**
- File: `app/Http/Controllers/Api/V1/Admin/PendaftarController.php`
- **Validasi baru:** `'participants.*.shirt_size' => 'nullable|string|in:XS,S,M,L,XL,XXL,XXXL'`
- **Endpoint baru:**
  - `PUT /api/v1/participants/{participant_id}/shirt-size` - Update 1 peserta
  - `POST /api/v1/participants/bulk-update-shirt-size` - Bulk update banyak peserta

#### 4. **Routes**
- File: `routes/api.php`
- Menambahkan 2 route baru untuk update shirt size

### ✅ Frontend Changes

#### 1. **Admin Page untuk Update Shirt Size**
- File: `fe-korpri/src/pages/participants/UpdateShirtSizePage.jsx`
- Fitur:
  - List semua peserta
  - Search by nama/email/ID
  - Update individual atau bulk update
  - Real-time feedback

#### 2. **Dokumentasi Lengkap**
- File: `SHIRT_SIZE_IMPLEMENTATION.md`
- Berisi panduan lengkap implementasi dan testing

## Cara Menggunakan

### Untuk Pendaftaran Baru

Tambahkan field `shirt_size` di form pendaftaran:

```jsx
<select 
  value={participant.shirt_size || ''} 
  onChange={e => updateParticipant(index, 'shirt_size', e.target.value)}
>
  <option value="">Pilih Ukuran</option>
  <option value="XS">XS</option>
  <option value="S">S</option>
  <option value="M">M</option>
  <option value="L">L</option>
  <option value="XL">XL</option>
  <option value="XXL">XXL</option>
  <option value="XXXL">XXXL</option>
</select>
```

### Untuk Data yang Sudah Ada

**Opsi 1: Via Admin Interface**
1. Akses halaman `/participants/update-shirt-size`
2. Cari peserta yang ingin diupdate
3. Pilih ukuran baju
4. Klik "Update" atau "Simpan Semua"

**Opsi 2: Via API (Bulk Update)**
```bash
POST /api/v1/participants/bulk-update-shirt-size
{
  "updates": [
    {"participant_id": "PID-ABC123", "shirt_size": "L"},
    {"participant_id": "PID-XYZ789", "shirt_size": "M"}
  ]
}
```

**Opsi 3: Via CSV Import**
Buat script untuk import dari CSV dengan format:
```
participant_id,shirt_size
PID-ABC123,L
PID-XYZ789,M
```

## Keunggulan Solusi Ini

✅ **Backward Compatible** - Data lama tetap berfungsi tanpa error
✅ **Non-Breaking** - Tidak mengubah flow yang sudah ada
✅ **Flexible** - Field bersifat optional (nullable)
✅ **Easy to Update** - Tersedia endpoint untuk update data lama
✅ **Validated** - Hanya menerima ukuran yang valid

## Langkah Deployment

1. **Backup database**
   ```bash
   mysqldump -u user -p database > backup.sql
   ```

2. **Pull latest code**
   ```bash
   git pull origin main
   ```

3. **Run migration**
   ```bash
   php artisan migrate
   ```

4. **Clear cache (optional)**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. **Deploy frontend**
   - Build dan deploy frontend dengan perubahan terbaru

## Testing Checklist

- [ ] Migration berhasil dijalankan
- [ ] Pendaftaran baru dengan shirt_size berhasil
- [ ] Pendaftaran baru tanpa shirt_size tetap berhasil (backward compatible)
- [ ] Update single participant berhasil
- [ ] Bulk update berhasil
- [ ] Admin page berfungsi dengan baik
- [ ] Data lama tetap bisa diakses

## File yang Diubah/Ditambahkan

### Backend
- ✅ `database/migrations/2025_10_13_000900_add_shirt_size_to_participants_table.php` (NEW)
- ✅ `app/Models/Participant.php` (MODIFIED)
- ✅ `app/Http/Controllers/Api/V1/Admin/PendaftarController.php` (MODIFIED)
- ✅ `routes/api.php` (MODIFIED)

### Frontend
- ✅ `fe-korpri/src/pages/participants/UpdateShirtSizePage.jsx` (NEW)

### Documentation
- ✅ `SHIRT_SIZE_IMPLEMENTATION.md` (NEW)
- ✅ `SOLUSI_UKURAN_BAJU.md` (NEW - file ini)

## Support

Jika ada pertanyaan atau masalah:
1. Cek dokumentasi di `SHIRT_SIZE_IMPLEMENTATION.md`
2. Test menggunakan endpoint yang tersedia
3. Hubungi tim development

---

**Status:** ✅ Ready for deployment
**Tested:** ⏳ Pending testing
**Deployed:** ⏳ Pending deployment
