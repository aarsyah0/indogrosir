# Audit dan arah desain antarmuka

## Audit aplikasi

- **Stack:** Laravel 11, Blade, Tailwind CSS, Alpine.js, Vite, dan Chart.js. Route serta aturan akses berada di Laravel; menu disaring oleh `App\Support\Navigation` berdasarkan permission pengguna.
- **Area layar:** halaman publik dan autentikasi; dashboard; pengajuan, inbox, dan empat tahap persetujuan; pembelian dan penerimaan; stok, kebutuhan, rekap, laporan, dan grafik; master barang/divisi/pemasok/periode; pengguna, pengaturan, notifikasi, profil, dan audit.
- **Alur yang dipertahankan:** pengajuan → persetujuan → pembelian → penerimaan → pembaruan stok. Form, filter, tabel, unduhan, dan tindakan tetap menggunakan route serta data yang sudah ada.
- **Aset:** repo awal tidak memiliki aset logo Indogrosir. Logo resmi yang dipakai situs Indogrosir kini ditampilkan langsung dari `www.indogrosir.co.id` melalui komponen Blade bersama dan dipakai juga sebagai favicon; file favicon bawaan Laravel dihapus. Tidak ada foto atau ilustrasi lokal/pihak ketiga di halaman aplikasi.

## Arah desain

- Kanvas abu-abu sangat muda, permukaan putih, teks arang, garis batas abu-abu, dan aksen merah untuk aksi/penekanan merek. Palet ini memakai arah yang disarankan dalam brief; ambil nilai logo resmi dari aset merek berizin sebelum mengklaim kecocokan warna presisi.
- Plus Jakarta Sans dipertahankan sebagai sans-serif yang sudah dipakai aplikasi. Spacing Tailwind yang ada tetap menjadi skala dasar.
- Komponen bersama: `.btn`, `.btn-primary`, `.btn-secondary`, `.input`, `.card`, dan `.table-wrap`, ditambah aturan konsisten untuk tabel Blade yang belum memakai wrapper tersebut.
- Sidebar terang mengelompokkan menu menurut fungsi, menandai route aktif, dan tetap menjadi drawer di perangkat kecil. Route, role, permission, dan middleware tidak berubah.
- Semua layar modul mewarisi komponen dan aturan baru dari `layouts/app.blade.php` dan `resources/css/app.css`; landing page, autentikasi, dan dashboard memakai treatment merek yang sama.

## Pemeriksaan

Pemeriksaan struktur dilakukan pada route, provider menu/permission, layout, dan seluruh template Blade. Jalankan `npm run build` untuk memvalidasi kompilasi aset setelah perubahan.

## Penyempurnaan admin dan monitoring

- Menu sidebar kini memakai kelompok yang dapat dibuka/tutup; kelompok route aktif otomatis terbuka. Laporan, stok, kebutuhan, rekap, dan grafik juga memiliki navigasi tab bersama.
- Daftar pengguna, divisi, pemasok, dan periode kini dapat dicari. Form perubahan per baris ditaruh dalam panel disclosure agar tabel tetap mudah dipindai. Field pemasok yang memang diterima controller (email, alamat, dan catatan) tersedia pada form edit.
- Dashboard admin menampilkan ringkasan global dan pintasan administrasi. Halaman stok menampilkan form pengeluaran dan penyesuaian hanya kepada pengguna dengan permission `stock.manage`.
- Laporan bulanan kini menampilkan filter yang sudah didukung backend beserta unduhan Excel/PDF; rekap pembelian menampilkan filter kategori/status dan unduhan Excel. Grafik memperjelas tren bulanan dan menampilkan riwayat harga referensi.
- Ringkasan kebutuhan menampilkan kuantitas per barang; total estimasi tidak lagi diulang pada setiap baris barang.
