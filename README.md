# POS Jeruk Lokal 🍊

Point of Sale berbasis Laravel 10 dengan admin panel mobile-friendly.

## Cara Menjalankan

```bash
cd pos-app
docker compose up --build -d
```

## Akses

| Service     | URL                          |
|-------------|------------------------------|
| Aplikasi    | http://localhost:8100        |
| phpMyAdmin  | http://localhost:8101        |
| MySQL       | localhost:3308               |

## Akun Demo

| Role  | Email                      | Password |
|-------|----------------------------|----------|
| Admin | admin@jeruklokal.com       | password |
| Kasir | kasir@jeruklokal.com       | password |

## Fitur

- **Kasir POS** — tambah produk ke keranjang, pilih pelanggan, bayar (tunai/transfer/QRIS), cetak struk
- **Dashboard Admin** — grafik penjualan 7 hari, statistik, produk terlaris
- **Manajemen Produk** — CRUD produk + kategori + upload foto
- **Manajemen Pelanggan** — data pelanggan
- **Laporan Transaksi** — filter by tanggal, status, cetak detail
- **Manajemen User** — admin & kasir dengan role-based access
