# API Implementation: Complaint Evidence File Upload

## Status: ✅ READY TO IMPLEMENT

## Overview

Implementasi lengkap untuk fitur upload bukti kejadian pada complaint form sudah dibuat dan siap untuk diintegrasikan ke dalam API.

---

## 📁 File-File yang Sudah Dibuat

### 1. Migration File

**Path:** `database/migrations/2025_12_23_000001_add_evidence_files_to_complaints_table.php`

**Fungsi:** Menambahkan kolom `evidence_files` (JSON) ke tabel complaints

**Cara Menjalankan:**

```bash
php artisan migrate
```

### 2. Updated ComplaintController

**Path:** `app/Http/Controllers/ComplaintController_with_evidence.php`

**Fitur Baru:**

-   ✅ Handle multiple file upload di method `storeComplaint()`
-   ✅ Validasi file (type, size max 10MB)
-   ✅ Simpan file ke `storage/app/public/complaints/evidence`
-   ✅ Generate unique filename untuk setiap file
-   ✅ Simpan metadata file (nama, path, URL, mime type, size) ke database
-   ✅ Delete files dari storage saat complaint dihapus
-   ✅ Method `getEvidence()` untuk retrieve evidence files

**Cara Menggunakan:**

1. Backup file lama: `cp ComplaintController.php ComplaintController.backup.php`
2. Replace: `cp ComplaintController_with_evidence.php ComplaintController.php`
3. Atau copy-paste isi filenya

### 3. Updated Complaints Model

**Path:** `app/Models/Complaints_with_evidence.php`

**Perubahan:**

-   ✅ Tambah cast untuk `evidence_files` dari JSON ke array

**Cara Menggunakan:**

1. Backup file lama: `cp Complaints.php Complaints.backup.php`
2. Replace: `cp Complaints_with_evidence.php Complaints.php`
3. Atau hanya tambahkan line ini di `$casts`:

```php
'evidence_files' => 'array',
```

---

## 🔧 Setup Storage

### 1. Link Storage ke Public

```bash
php artisan storage:link
```

### 2. Buat Direktori Evidence

```bash
mkdir -p storage/app/public/complaints/evidence
chmod -R 775 storage/app/public/complaints/evidence
```

### 3. Update .gitignore

Pastikan storage folder untuk evidence di-ignore:

```
storage/app/public/complaints/evidence/*
!storage/app/public/complaints/evidence/.gitkeep
```

---

## 🛣️ API Routes

Tambahkan route baru di `routes/api.php`:

```php
// Evidence endpoint
Route::get('/complaints/{uuid}/evidence', [ComplaintController::class, 'getEvidence']);
```

Route yang sudah ada masih berfungsi normal:

-   `POST /complaints` - Sekarang support file upload
-   `GET /complaints/{uuid}` - Evidence files included in response
-   `DELETE /complaints/{uuid}` - Akan delete files juga

---

## 📝 API Documentation

### Create Complaint with Evidence Files

**Endpoint:** `POST /api/complaints`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (FormData):**

```
title: "Masalah Bullying di Sekolah"
description: "Deskripsi lengkap masalah"
chronology: "Kronologi kejadian detail"
category: "pengaduan"
evidence[]: (file) image1.jpg
evidence[]: (file) document1.pdf
evidence[]: (file) image2.png
```

**Response Success (201):**

```json
{
    "success": true,
    "message": "Complaint submitted successfully",
    "data": {
        "complaint_id": "uuid-here",
        "user_id": "user-uuid",
        "title": "Masalah Bullying di Sekolah",
        "description": "Deskripsi lengkap masalah",
        "chronology": "Kronologi kejadian detail",
        "category": "pengaduan",
        "status": "new",
        "classification": null,
        "evidence_files": [
            {
                "filename": "image1.jpg",
                "path": "complaints/evidence/1703345678_abc123def4.jpg",
                "url": "https://api.example.com/storage/complaints/evidence/1703345678_abc123def4.jpg",
                "mime_type": "image/jpeg",
                "size": 245678
            },
            {
                "filename": "document1.pdf",
                "path": "complaints/evidence/1703345679_xyz789ghi0.pdf",
                "url": "https://api.example.com/storage/complaints/evidence/1703345679_xyz789ghi0.pdf",
                "mime_type": "application/pdf",
                "size": 512345
            }
        ],
        "created_at": "2025-12-23T10:30:00.000000Z",
        "updated_at": "2025-12-23T10:30:00.000000Z"
    }
}
```

### Get Evidence Files

**Endpoint:** `GET /api/complaints/{uuid}/evidence`

**Headers:**

```
Authorization: Bearer {token}
```

**Response Success (200):**

```json
{
    "success": true,
    "message": "Evidence files retrieved successfully",
    "data": [
        {
            "filename": "image1.jpg",
            "path": "complaints/evidence/1703345678_abc123def4.jpg",
            "url": "https://api.example.com/storage/complaints/evidence/1703345678_abc123def4.jpg",
            "mime_type": "image/jpeg",
            "size": 245678
        }
    ]
}
```

---

## 🧪 Testing

### 1. Test File Upload dengan cURL

```bash
curl -X POST http://api.mindfulme.test/api/complaints \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "title=Test Complaint with Evidence" \
  -F "description=Test description" \
  -F "chronology=Test chronology detail" \
  -F "category=pengaduan" \
  -F "evidence[]=@/path/to/test-image.jpg" \
  -F "evidence[]=@/path/to/test-document.pdf"
```

### 2. Test Tanpa Evidence (Optional)

```bash
curl -X POST http://api.mindfulme.test/api/complaints \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "title=Test Complaint without Evidence" \
  -F "description=Test description" \
  -F "chronology=Test chronology" \
  -F "category=pengaduan"
```

### 3. Test Get Evidence

```bash
curl -X GET http://api.mindfulme.test/api/complaints/{uuid}/evidence \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### 4. Test Delete Complaint (Should delete files too)

```bash
curl -X DELETE http://api.mindfulme.test/api/complaints/{uuid} \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🔒 Security Features

✅ **Implemented:**

1. File type validation (only: jpeg, jpg, png, pdf, doc, docx)
2. File size limit (max 10MB per file)
3. Unique filename generation (prevents overwriting)
4. Files stored in storage/app/public (outside web root)
5. Authorization check (auth()->user())

⚠️ **Recommended (Future Enhancement):**

1. Virus scanning for uploaded files
2. Rate limiting on upload endpoints
3. Compression for large images
4. Thumbnail generation for images
5. CDN integration for file serving

---

## 📋 Checklist Implementasi

### Backend (API)

-   [x] Create migration file
-   [x] Update ComplaintController dengan file upload
-   [x] Update Complaints Model dengan cast evidence_files
-   [x] Add getEvidence() method
-   [x] Delete files when complaint deleted
-   [ ] Run migration: `php artisan migrate`
-   [ ] Link storage: `php artisan storage:link`
-   [ ] Replace files (Controller & Model)
-   [ ] Add route for getEvidence
-   [ ] Test dengan Postman/cURL
-   [ ] Deploy ke production

### Frontend (Dashboard)

-   [x] Update createComplaint function di userComplaint.ts
-   [x] Update ComplaintFormPage dengan file upload UI
-   [x] Add drag & drop support
-   [x] Add file preview
-   [x] Add remove file functionality
-   [x] Update validation
-   [x] Test upload functionality

---

## 🚀 Deployment Steps

1. **Development:**

```bash
cd /path/to/api
php artisan migrate
php artisan storage:link
php artisan config:clear
php artisan cache:clear
```

2. **Production:**

```bash
# Backup database first
php artisan down
git pull origin main
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

3. **Verify:**

-   Check storage link exists: `ls -la public/storage`
-   Check directory permissions: `ls -la storage/app/public/complaints`
-   Test upload via frontend
-   Check files saved in storage/app/public/complaints/evidence

---

## 📞 Support

Jika ada masalah:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check storage permissions: `chmod -R 775 storage`
3. Check storage link: `php artisan storage:link`
4. Check .env FILESYSTEM_DISK=local or public

---

## 📚 Additional Resources

-   Laravel File Storage: https://laravel.com/docs/10.x/filesystem
-   File Upload Validation: https://laravel.com/docs/10.x/validation#rule-file
-   FormData in JavaScript: https://developer.mozilla.org/en-US/docs/Web/API/FormData
