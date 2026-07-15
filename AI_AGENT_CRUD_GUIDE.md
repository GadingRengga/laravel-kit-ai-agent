# AI Agent CRUD Guide - User Management Example

## Ringkasan Masalah

AI Agent tidak bisa membuat data karena **3 komponen yang belum ada**:

1. Permission `user.create` belum terdaftar
2. Tool `create_user` belum dibuat
3. Tool belum didaftarkan di AiServiceProvider

## Solusi yang Sudah Diimplementasikan

### 1. ✅ Permission Baru Ditambahkan

**File:** `database/seeders/PermissionSeeder.php`

Permission manajemen user yang baru:

- `user.view` - Melihat daftar user
- `user.create` - Menambahkan user baru
- `user.edit` - Mengubah data user
- `user.delete` - Menghapus user

### 2. ✅ Custom Tool Dibuat

**File:** `app/Services/AI/Tools/CreateUserTool.php`

Tool ini menangani:

- Validasi data user (name, email, username, password)
- Resolve role berdasarkan nama atau ID
- Hash password otomatis
- Assign role ke user baru
- Permission check via `user.create`

### 3. ✅ Tool Terdaftar

**File:** `app/Providers/AiServiceProvider.php`

CreateUserTool sudah didaftarkan di registry.

### 4. ✅ Menu-Permission Di-link

**File:** `database/seeders/MenuPermissionSeeder.php`

Menu "users" sekarang terhubung dengan permission:

- user.view
- user.create
- user.edit
- user.delete
- user-role.assign

## Cara Menjalankan

### Step 1: Jalankan Seeder

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=MenuPermissionSeeder
```

### Step 2: Assign Permission ke Role

Login sebagai Super User, lalu:

1. Buka menu **Role**
2. Pilih role yang ingin diberi akses (misal: "Staff")
3. Assign permission `user.create`
4. Pastikan role tersebut sudah memiliki permission untuk mengakses menu Users

### Step 3: Test AI Agent

Login dengan user yang memiliki permission `user.create`, lalu coba:

#### Contoh 1: Buat User dengan Role

```
Buatkan user baru dengan nama John Doe, email john@example.com,
username johndoe, password password123, role Staff
```

#### Contoh 2: Buat User Tanpa Role (akan default ke Staff)

```
Tambah user baru: Jane Smith, jane@test.com, janesmith, pass456
```

#### Contoh 3: Buat User dengan Status Non-Aktif

```
Buat user baru dengan nama Bob Johnson, email bob@test.com,
username bobjohnson, password secure123, role Admin, tidak aktif
```

## Cara Kerja Sistem

### Permission Flow

```
User Request → AI Agent
    ↓
Check Permission (user.create)
    ↓
Jika punya → Tawarkan tool create_user
Jika tidak → "Maaf, kamu tidak punya akses"
```

### Tool Execution Flow

```
User: "Buat user John Doe..."
    ↓
AI Agent: parse parameters
    ↓
CreateUserTool::toDraft()
    - Validasi data
    - Resolve role
    - Hash password
    - Buat draft
    ↓
Tampilkan draft ke user untuk konfirmasi
    ↓
User: "Ya, buat"
    ↓
CreateUserTool::confirm()
    - Create user
    - Assign role
    - Return user baru
    ↓
AI Agent: "User John Doe berhasil dibuat"
```

## Struktur Data User

### Model: User

```php
- name: string (required)
- email: string (required, unique)
- username: string (nullable, unique)
- password: string (hashed)
- is_active: boolean (default: true)
- created_by: integer (auto-filled)
- roles: many-to-many (Role model)
```

### Role Resolution

Tool akan mencari role dengan cara:

1. Cek apakah input adalah ID role
2. Cari by name (case-insensitive)
3. Cari by slug (lowercase, underscore)
4. Default ke "Staff" jika tidak ditemukan

## Contoh Role yang Ada

Pastikan role ini ada di database (biasanya sudah ada dari seeder):

- Super User (slug: super_user)
- Admin (slug: admin)
- Staff (slug: staff)
- Manager (slug: manager)

## Troubleshooting

### AI Tidak Bisa Buat User

**Penyebab:** User tidak punya permission `user.create`

**Solusi:**

1. Login sebagai Super User
2. Buka menu Role
3. Edit role user tersebut
4. Assign permission `user.create`
5. Pastikan role memiliki permission untuk menu Users (`user.view` atau lainnya)

### Role Tidak Ditemukan

**Penyebab:** Role yang dimaksud tidak ada di database

**Solusi:**

1. Cek daftar role di menu Role
2. Gunakan nama role yang exact (misal: "Staff" bukan "staff")
3. Atau buat role baru terlebih dahulu

### Password Terlalu Pendek

**Penyebab:** Password kurang dari 8 karakter

**Solusi:** Gunakan password minimal 8 karakter

### Email Sudah Terdaftar

**Penyebab:** Email sudah ada di database

**Solusi:** Gunakan email yang berbeda

## Contoh Role-Based Access

### Super User

- Permission: SEMUA permission (otomatis)
- Bisa: Buat, edit, hapus user
- AI Tools: Semua tools tersedia

### Admin

- Permission: user.create, user.view, user.edit, user.delete
- Bisa: Kelola user
- AI Tools: create_user, (tools lain sesuai permission)

### Staff

- Permission: user.view (hanya melihat)
- Bisa: Melihat daftar user
- AI Tools: Tidak ada tool create_user (karena tidak punya user.create)

## Testing Checklist

- [ ] PermissionSeeder berhasil dijalankan (20 permissions)
- [ ] MenuPermissionSeeder berhasil dijalankan
- [ ] Super User bisa membuat user via AI
- [ ] User dengan permission `user.create` bisa membuat user via AI
- [ ] User tanpa permission `user.create` dapat pesan "akses ditolak"
- [ ] Role tersimpan dengan benar
- [ ] Password ter-hash dengan benar
- [ ] Email unique constraint bekerja
- [ ] Username opsional bekerja

## Next Steps: CRUD Lainnya

Untuk menambahkan fitur AI Agent untuk entity lain (misal: Employee, Department):

### 1. Tambah Permission

```php
// di PermissionSeeder
['name' => 'Tambah Employee', 'slug' => 'employee.create', ...]
```

### 2. Buat Custom Tool (jika perlu)

```php
// app/Services/AI/Tools/CreateEmployeeTool.php
// (jika ada logic bisnis yang kompleks)
```

### 3. Atau Pakai GenericModelTool

```php
// di config/ai_tools.php
[
    'name' => 'create_employee',
    'model' => \App\Models\Employee::class,
    'permission' => 'employee.create',
    'fields' => [...]
]
```

### 4. Link Menu-Permission

```php
// di MenuPermissionSeeder
$this->link('employees', ['employee.create', ...]);
```

### 5. Test

```bash
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=MenuPermissionSeeder
```

## Catatan Penting

1. **Password Hashing:** Tool otomatis hash password menggunakan Hash::make()
2. **Role Assignment:** Tool otomatis assign role setelah user dibuat
3. **Permission Check:** Tool cek permission SEBELUM membuat draft
4. **Error Handling:** Jika tool gagal, AI akan memberikan pesan yang user-friendly
5. **Super User:** Otomatis punya semua permission, tidak perlu assign manual

## Referensi File

- `app/Services/AI/Tools/CreateUserTool.php` - Tool implementation
- `app/Providers/AiServiceProvider.php` - Tool registration
- `database/seeders/PermissionSeeder.php` - Permission definitions
- `database/seeders/MenuPermissionSeeder.php` - Menu-permission linkage
- `config/ai_tools.php` - Generic tool configurations
- `app/Models/Superuser/User.php` - User model dengan permission methods
