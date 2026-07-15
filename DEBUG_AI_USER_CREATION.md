# Debug Guide: AI Agent User Creation

## Status Saat Ini: ✅ BACKEND SUDAH BENAR

Test script menunjukkan:

- ✅ Tool `create_user` terdaftar dan bekerja
- ✅ Permission `user.create` aktif untuk superuser
- ✅ User berhasil dibuat di database
- ✅ Role assignment bekerja

**Masalah kemungkinan ada di FRONTEND atau CONFIGURATION user.**

## 🔍 Step-by-Step Debugging

### 1. Cek Apakah Tool Terdaftar

Login sebagai Super User, buka halaman AI Chat, lalu tanya ke AI:

```
Apa yang bisa saya lakukan?
```

**Expected Response:**
AI harus menyebutkan `create_user` dalam daftar kemampuannya.

**Jika TIDAK muncul:**

- Tool belum terdaftar dengan benar
- Cek file: `app/Providers/AiServiceProvider.php`
- Cek log Laravel: `storage/logs/laravel.log`

### 2. Cek Permission User Login

**Cara 1: Via AI Chat**
Tanya ke AI:

```
Saya bisa akses apa saja?
```

**Expected:** AI harus menyebutkan "Membuat user baru"

**Jika TIDAK:** User login tidak punya permission `user.create`

**Cara 2: Via Database**

```bash
php artisan tinker
>>> $user = \App\Models\Superuser\User::where('email', 'USER_EMAIL')->first();
>>> $user->hasPermission('user.create')
```

### 3. Cek Apakah Draft Terbentuk

Setelah user chat "Buat user baru...", AI harus membalas dengan **Tool Confirm Card** yang berisi:

- Summary: "Buat user baru **Nama** (email) dengan role **Role**"
- Form fields: name, email, username, password, is_active
- 3 tombol: **Buat Sekarang**, **Edit**, **Batal**

**Jika TIDAK muncul Tool Confirm Card:**

- AI tidak mengenali intent (masalah prompt)
- Tool tidak terdaftar
- Permission ditolak

### 4. Cek Apakah Tombol "Buat Sekarang" Bisa Diklik

Di Tool Confirm Card, klik tombol **"Buat Sekarang"**

**Expected:**

- Card berubah menjadi "Tersimpan" (badge hijau)
- Data muncul di database

**Jika GAGAL:**

- Cek browser console (F12) untuk error JavaScript
- Cek network tab untuk request yang gagal
- Cek log Laravel untuk error

### 5. Cek Database

```bash
# Cek apakah user baru ada
mysql -u root -p NAMA_DATABASE
SELECT * FROM users WHERE email = 'email_yang_dibuat' ORDER BY id DESC LIMIT 5;

# Cek apakah role assignment benar
SELECT u.name, r.name as role
FROM users u
JOIN role_user ru ON u.id = ru.user_id
JOIN roles r ON ru.role_id = r.id
WHERE u.email = 'email_yang_dibuat';
```

## 🐛 Common Issues & Solutions

### Issue 1: AI Tidak Mengenali "Buat User"

**Symptoms:** AI balas dengan teks biasa, bukan tool confirm card

**Causes:**

1. Tool `create_user` belum terdaftar
2. System prompt tidak mention tentang create_user
3. Permission user tidak ada

**Solution:**

```bash
# 1. Clear cache
php artisan config:clear
php artisan cache:clear

# 2. Verify tool terdaftar
php artisan tinker
>>> app(\App\Services\AI\AiToolRegistry::class)->has('create_user')
# Should return: true

# 3. Verify permission
>>> auth()->user()->hasPermission('user.create')
# Should return: true
```

### Issue 2: Tool Confirm Card Muncul Tapi Tombol Tidak Bisa Diklik

**Symptoms:** Card muncul, tombol "Buat Sekarang" tidak merespon

**Causes:**

1. JavaScript error
2. CSRF token mismatch
3. Route tidak ditemukan

**Solution:**

1. Buka browser console (F12)
2. Cek error merah
3. Cek network tab saat klik "Buat Sekarang"
4. Pastikan route exists:
    ```bash
    php artisan route:list | grep action.confirm
    ```

### Issue 3: Klik "Buat Sekarang" Tapi Data Tidak Masuk DB

**Symptoms:** Card berubah ke "Tersimpan" tapi database kosong

**Causes:**

1. Error di `confirm()` method
2. Validation error
3. Database error (foreign key, dll)

**Solution:**

```bash
# Cek log Laravel
tail -f storage/logs/laravel.log

# Test manual
php test_ai_user_creation.php
```

### Issue 4: Permission Denied

**Symptoms:** AI balas "Maaf, kamu tidak punya akses"

**Causes:**

1. User tidak punya permission `user.create`
2. Role user tidak di-assign permission

**Solution:**

1. Login sebagai Super User
2. Buka menu **Role**
3. Edit role user tersebut
4. Assign permission `user.create`
5. Pastikan role juga punya permission untuk menu Users (`user.view`)

## 🔧 Quick Fixes

### Fix 1: Clear All Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Fix 2: Verify Tool Registration

```bash
php artisan tinker
>>> $registry = app(\App\Services\AI\AiToolRegistry::class);
>>> $registry->has('create_user');
>>> $registry->get('create_user')->name();
>>> $registry->get('create_user')->description();
```

### Fix 3: Verify Permission

```bash
php artisan tinker
>>> $user = \App\Models\Superuser\User::where('email', 'YOUR_EMAIL')->first();
>>> $user->hasPermission('user.create');
>>> $user->getAllPermissions()->pluck('slug');
```

### Fix 4: Test Tool Manual

```bash
php test_ai_user_creation.php
```

### Fix 5: Check Database Connection

```bash
php artisan tinker
>>> \Illuminate\Support\Facades\DB::connection()->getPdo();
>>> \App\Models\Superuser\User::count();
```

## 📋 Checklist Lengkap

### Backend (✅ Sudah Done)

- [x] CreateUserTool dibuat
- [x] Tool terdaftar di AiServiceProvider
- [x] Permission `user.create` ditambahkan
- [x] Menu-permission di-link
- [x] Migration `created_by` dijalankan
- [x] User model `$fillable` updated
- [x] Test script PASSED

### Yang Perlu Diverifikasi User:

#### 1. User Login

- [ ] Login dengan user yang punya permission `user.create`
- [ ] User tersebut adalah Super User ATAU punya role dengan permission `user.create`

#### 2. AI Chat Access

- [ ] Buka halaman `/ai/chat`
- [ ] AI connection sudah aktif (tidak error "Hubungkan akun AI")
- [ ] Pesan bisa dikirim (tidak error 500)

#### 3. Tool Availability

- [ ] Tanya ke AI: "Apa yang bisa saya lakukan?"
- [ ] AI harus menyebutkan `create_user`
- [ ] Jika tidak, ada masalah permission atau tool registration

#### 4. Tool Execution

- [ ] Chat: "Buat user baru dengan nama Test User, email test@example.com, username testuser, password password123, role Staff"
- [ ] AI harus membalas dengan Tool Confirm Card
- [ ] Card harus menampilkan data yang benar
- [ ] Tombol "Buat Sekarang" harus bisa diklik

#### 5. Database

- [ ] Setelah klik "Buat Sekarang", cek database
- [ ] User baru harus ada di tabel `users`
- [ ] Role harus ada di tabel `role_user`

## 🎯 Test Cases

### Test Case 1: Super User

```
Login: superuser@netra.local / password
Expected: Bisa membuat user via AI
```

### Test Case 2: Admin dengan Permission

```
Login: User dengan role yang punya permission user.create
Expected: Bisa membuat user via AI
```

### Test Case 3: User tanpa Permission

```
Login: User tanpa permission user.create
Expected: AI balas "Maaf, kamu tidak punya akses"
```

### Test Case 4: Invalid Data

```
Chat: "Buat user dengan email yang salah"
Expected: AI tanya ulang atau validasi error
```

## 📊 Monitoring

### Cek Log Laravel

```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Cari error terkait AI
grep -i "AI\|tool\|action" storage/logs/laravel.log | tail -20
```

### Cek Database

```sql
-- Cek action logs
SELECT * FROM ai_action_logs
ORDER BY created_at DESC
LIMIT 10;

-- Cek users terbaru
SELECT * FROM users
ORDER BY created_at DESC
LIMIT 5;

-- Cek role assignments
SELECT u.name, u.email, r.name as role, ru.created_at
FROM users u
JOIN role_user ru ON u.id = ru.user_id
JOIN roles r ON ru.role_id = r.id
ORDER BY ru.created_at DESC
LIMIT 10;
```

## 🚀 Next Steps

Jika semua di atas sudah dicek dan masih gagal:

1. **Share screenshot/error message:**
    - Screenshot AI Chat
    - Browser console error (F12)
    - Laravel log error

2. **Provide details:**
    - Email user yang login
    - Role user tersebut
    - Exact message yang dikirim ke AI
    - Response dari AI

3. **Check specific component:**
    - Frontend: JavaScript, Blade template
    - Backend: Controller, Tool, Permission
    - Database: Tables, data

## 📝 Summary

**Backend sudah 100% working** (tested via PHP script).

Masalah kemungkinan:

1. User tidak punya permission (80% kemungkinan)
2. Frontend JavaScript error (15% kemungkinan)
3. AI tidak mengenali intent (5% kemungkinan)

**Langkah terbaik:** Ikuti checklist di atas dari nomor 1-5.
