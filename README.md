# Buku Servis — UTS Pengembangan Perangkat Lunak

Aplikasi pencatatan servis motor: backend Laravel 12 (REST API + Sanctum), frontend Vue 3 (Axios), MySQL, seluruh sistem dijalankan dengan Docker Compose dan backend dilayani **FrankenPHP**.

- Repository: <https://github.com/Ryzen0000001/uts-pl-24-535196-SV-24171>
- NIM: 24/535196/SV/24171
- Kelas: PL5B1

## Struktur

```
.
├── backend/                 # Laravel 12 + Sanctum + FrankenPHP
├── frontend/                # Vue 3 + Vite + Axios + Nginx
├── postman/                 # Postman Collection
├── laporan/                 # Sumber LaTeX dan gambar laporan
├── .github/workflows/ci.yml # Test, build, dan deploy GHCR
├── docker-compose.yml
├── .env.example
└── README.md
```

## Menjalankan seluruh sistem

```bash
cp .env.example .env
# isi APP_KEY (php artisan key:generate --show dari folder backend)
# isi DB_PASSWORD dan DB_ROOT_PASSWORD
docker compose up -d --build
```

- Frontend: <http://localhost:8080>
- API langsung: <http://localhost:8000/api>
- Akun demo: `demo@example.com` / `password123` (dibuat oleh `DemoSeeder` saat backend mulai)

Data MySQL disimpan di named volume `uts-pl_db_data`, sehingga tetap ada setelah `docker compose down` lalu `docker compose up -d`.

### Perintah yang sering dipakai

```bash
docker compose ps
docker compose logs -f backend
docker compose down
docker compose down -v   # hapus volume (data ikut terhapus)
```

## Endpoint API

| Method | Endpoint | Keterangan | Status |
| --- | --- | --- | --- |
| POST | `/api/login` | Mendapatkan token | 200 / 401 / 422 |
| GET | `/api/me` | Data user pemilik token | 200 / 401 |
| POST | `/api/logout` | Mencabut token aktif | 200 / 401 |
| GET | `/api/service-logs` | Daftar catatan milik user | 200 / 401 |
| POST | `/api/service-logs` | Tambah catatan | 201 / 401 / 422 |
| GET | `/api/service-logs/{id}` | Detail catatan | 200 / 401 / 404 |
| PUT | `/api/service-logs/{id}` | Ubah catatan | 200 / 401 / 404 / 422 |
| DELETE | `/api/service-logs/{id}` | Hapus catatan | 200 / 401 / 404 |

Semua endpoint selain `POST /api/login` memerlukan header `Authorization: Bearer <token>`.

Contoh:

```bash
curl -s -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"password123"}'
```

## Pengujian

```bash
cd backend
php artisan test
```

## Alamat API di frontend

Alamat API tidak ditulis di komponen. Nilainya berasal dari `VITE_API_URL`:

- `.env` lokal: `VITE_API_URL=http://localhost:8000/api`
- Docker: build arg `VITE_API_URL=/api`, lalu Nginx meneruskan `/api/` ke `http://backend:8000` (nama service Docker).

## CI/CD dan rahasia

`.github/workflows/ci.yml` menjalankan:

1. `backend-test` — `php artisan test`
2. `frontend-build` — `npm ci` dan `npm run build`
3. `deploy` — hanya pada push ke `main`; membangun image backend dan frontend lalu mendorongnya ke GHCR dengan tag commit SHA.

Rahasia disimpan di **Settings → Secrets and variables → Actions**: `APP_KEY`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`. Tidak ada nilai rahasia di dalam repository maupun Dockerfile.

Image yang dihasilkan:

```
ghcr.io/ryzen0000001/uts-pl-24-535196-SV-24171-backend:<commit-sha>
ghcr.io/ryzen0000001/uts-pl-24-535196-SV-24171-frontend:<commit-sha>
```

