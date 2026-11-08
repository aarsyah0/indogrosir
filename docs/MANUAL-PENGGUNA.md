# Manual Pengguna Sistem Pengelolaan ATK/ABT

Panduan ini disusun dari halaman aplikasi, route, dan aturan proses yang ada di kode. Menu yang tampak dan tindakan yang bisa dilakukan bergantung pada hak akses akun. Istilah **Manager**, **GA**, **SAM**, dan **SM** mengikuti role aplikasi.

## 1. Gambaran alur

```text
Staff buat pengajuan → Manager menyetujui / mengembalikan / menolak
→ GA memeriksa kuantitas dan mengirim versi final ke SAM
→ SAM menyetujui / mengembalikan ke GA
→ SM menyetujui / mengembalikan ke GA
→ GA mencatat pembelian → GA mencatat penerimaan → stok bertambah
```

Pembelian hanya dapat dicatat setelah approval Manager, GA, SAM, dan SM selesai untuk versi pengajuan yang sama. Stok bertambah saat penerimaan disimpan, bukan saat pembelian dicatat. Pembelian dan penerimaan boleh dicatat bertahap.

## 2. Masuk dan navigasi

1. Buka alamat aplikasi dan masuk menggunakan akun yang diberikan administrator.
2. Setelah masuk, halaman **Dashboard** menampilkan ringkasan sesuai akses akun.
3. Gunakan menu navigasi untuk membuka pengajuan, inbox, approval, pembelian, penerimaan, stok, laporan, master data, notifikasi, dan profil. Menu hanya muncul jika akun memiliki permission terkait.
4. Buka **Notifikasi** untuk melihat pemberitahuan proses. Notifikasi dapat dibuka satu per satu atau ditandai sudah dibaca semua.
5. Di **Profil**, pengguna dapat memperbarui informasi akun dan kata sandi.

## 3. Staff: membuat dan mengirim pengajuan

1. Buka **Pengajuan Saya** lalu pilih buat pengajuan baru.
2. Pilih periode yang masih dibuka. Divisi terisi mengikuti akun.
3. Tambahkan satu atau lebih barang dari katalog, masukkan kuantitas dan alasan kebutuhan untuk setiap barang. Catatan umum dapat diisi bila diperlukan.
4. Periksa estimasi total yang ditampilkan.
5. Pilih **Simpan draft** untuk melanjutkan nanti, atau **Kirim pengajuan** untuk mengirim ke Manager. Pengajuan yang dikirim harus memiliki setidaknya satu barang.
6. Pantau status dan riwayat pengajuan melalui daftar atau halaman detail. Gunakan filter status bila diperlukan.
7. Jika dikembalikan untuk revisi, baca komentar/alasan, ubah pengajuan, lalu kirim kembali. Jika Manager menolak, status penolakan bersifat akhir dalam alur ini.

Pengajuan baru hanya dapat dibuat pada periode aktif. Setelah versi dikunci GA untuk approval berikutnya, perubahan diproses sebagai versi baru dan approval terkait perlu dilakukan kembali.

## 4. Manager: memproses pengajuan

1. Buka **Approval Manager** dan pilih pengajuan yang menunggu keputusan.
2. Periksa divisi, pemohon, daftar barang, kuantitas, alasan, dan catatan.
3. Pilih setujui untuk meneruskan ke GA, kembalikan untuk revisi, atau tolak.
4. Saat mengembalikan atau menolak, isi alasan/komentar. Pengaju tidak dapat menyetujui pengajuannya sendiri.

Manager hanya dapat menangani divisi yang ditugaskan kepadanya.

## 5. GA: verifikasi dan finalisasi

1. Buka **Verifikasi GA** dan pilih pengajuan pada antrean.
2. Tinjau barang, kuantitas yang disetujui Manager, stok saat ini, dan informasi pemakaian yang tersedia.
3. Isi kuantitas rekomendasi/final untuk setiap barang. Jika mengubah kuantitas dari nilai sebelumnya, isi alasan perubahan.
4. Pilih pengembalian ke divisi bila pemohon perlu memperbaiki pengajuan; isi catatan pengembalian. Atau pilih **Finalisasi & kirim ke SAM** setelah seluruh kuantitas final diperiksa.
5. Setelah dikirim, versi pengajuan terkunci untuk approval SAM/SM. Bila SAM atau SM meminta revisi, GA memperbaiki versi melalui tahap GA lalu mengirim ulang.

## 6. SAM dan SM: persetujuan lanjutan

1. Buka antrean **Approval SAM** atau **Approval SM** sesuai role.
2. Periksa pengajuan dan versi finalnya, lalu setujui atau kembalikan dengan komentar yang menjelaskan revisi.
3. SAM harus menyetujui sebelum pengajuan dapat diproses SM. SAM dan SM dapat menyetujui atau mengembalikan; penolakan tidak tersedia pada tahap ini.
4. Setelah SM menyetujui, status pengajuan menjadi **Disetujui untuk Pembelian**.

## 7. GA: mencatat pembelian

1. Buka **Pembelian**, pilih pengajuan yang sudah disetujui, lalu buka form pembelian.
2. Pilih pemasok dan isi tanggal pembelian. Nomor invoice, diskon, pajak, biaya lain, catatan, serta bukti pembelian dapat ditambahkan.
3. Isi kuantitas yang benar-benar dibeli dan harga satuan untuk barang yang dibeli.
4. Simpan pembelian. Pembelian parsial diperbolehkan; transaksi berikutnya dapat dicatat untuk sisa kebutuhan.
5. Jumlah kumulatif dibeli dibatasi kuantitas yang disetujui SM. Jika melebihi batas, sistem memerlukan otorisasi khusus.

## 8. GA: mencatat penerimaan barang

1. Buka **Penerimaan**, pilih transaksi pembelian yang akan diterima.
2. Isi tanggal diterima, kuantitas yang datang untuk setiap barang, kondisi barang (baik, rusak, atau tidak sesuai), serta catatan selisih bila ada.
3. Tambahkan catatan umum atau bukti penerimaan bila diperlukan.
4. Pastikan kuantitas tidak melebihi sisa pembelian, lalu simpan penerimaan.
5. Penerimaan dapat dilakukan beberapa kali. Sistem mencatat stok masuk sesuai kuantitas yang disimpan. Kekurangan atau kondisi selain baik membuat penerimaan perlu pemeriksaan.

## 9. Stok

Menu **Stok** menampilkan stok per barang, jumlah masuk/keluar/penyesuaian, saldo, status terhadap stok minimum, dan kartu mutasi.

- Pengguna dengan hak pengelolaan stok dapat mencatat pengeluaran dengan barang, jumlah, catatan, dan divisi tujuan.
- Penyesuaian stok memerlukan jumlah perubahan (positif untuk menambah, negatif untuk mengurangi) serta alasan.
- Stok tidak dapat menjadi negatif. Mutasi penerimaan tercatat otomatis saat penerimaan disimpan.

## 10. Rekap, kebutuhan, laporan, dan grafik

Pengguna dengan hak laporan dapat membuka **Rekap**, **Kebutuhan**, **Laporan**, dan **Grafik**. Halaman laporan menyediakan filter periode/tahun, bulan, divisi, kategori/barang, pemasok, atau status sesuai halaman. Laporan dapat diunduh sebagai Excel atau PDF. Staff dan Manager dapat memiliki cakupan data yang dibatasi ke divisinya.

## 11. Master data dan administrasi

Menu ini hanya tersedia bagi role yang berwenang:

- **Barang:** mengelola katalog barang beserta kategori, satuan, harga acuan, dan stok minimum.
- **Divisi:** mengelola data divisi.
- **Pemasok:** menambah, mengubah, atau menghapus data pemasok.
- **Periode:** membuat periode pengajuan dan membuka/menutupnya. Periode tertutup tidak menerima pengajuan baru.
- **Pengguna:** mengelola akun, divisi, status, dan role.
- **Pengaturan:** mengatur preferensi aplikasi yang tersedia.
- **Audit:** melihat catatan aktivitas untuk akun yang memiliki akses.

Pastikan master barang, pemasok, divisi, periode, pengguna, dan role sudah disiapkan sebelum operasional berjalan.

## 12. Arti status penting

| Status | Arti |
|---|---|
| Draft | Pengajuan disimpan, belum dikirim |
| Menunggu Approval Manager | Menunggu keputusan Manager |
| Revisi oleh Divisi | Pengaju perlu memperbaiki pengajuan |
| Ditolak Manager | Ditolak pada tahap Manager |
| Menunggu Verifikasi GA | Menunggu pemeriksaan GA |
| Revisi oleh GA | Pengajuan dikembalikan oleh GA |
| Menunggu Approval SAM / SM | Menunggu persetujuan tahap terkait |
| Revisi setelah SAM/SM | GA perlu meninjau kembali setelah komentar SAM/SM |
| Disetujui untuk Pembelian | Semua persetujuan selesai; pembelian dapat dicatat |
| Dibatalkan | Pengajuan dibatalkan |

Status pembelian dan penerimaan ditampilkan terpisah dari status approval. Pembelian dapat berstatus belum dibeli, sebagian, atau selesai; penerimaan dapat menunggu, sebagian, selesai, atau perlu pemeriksaan.

## 13. Jika terjadi kendala

- **Tidak bisa membuat pengajuan:** periksa apakah periode masih terbuka dan akun memiliki divisi serta hak membuat pengajuan.
- **Barang tidak tersedia:** minta petugas master data memeriksa katalog barang.
- **Tidak bisa menyetujui:** pastikan pengajuan sedang menunggu tahap Anda dan akun memiliki role/permission yang benar.
- **Jumlah pembelian ditolak sistem:** periksa kuantitas yang disetujui SM dan pembelian sebelumnya; minta otorisasi jika memang harus melebihi batas.
- **Penerimaan tidak dapat disimpan:** periksa kuantitas sisa pembelian dan nilai yang dimasukkan.
- **Tidak melihat menu tertentu:** menu mengikuti hak akses akun; hubungi administrator untuk memeriksa role dan penugasan.

## Catatan untuk penyempurnaan manual

Panduan ini menjelaskan fungsi yang tercermin dalam aplikasi. URL produksi, kebijakan internal untuk approval/pembelian, batas ukuran dan format lampiran, serta kontak dukungan perlu dilengkapi oleh organisasi sebelum manual dibagikan. Tidak ada akses pendaftaran publik yang tercantum sebagai alur navigasi aplikasi; akun operasional dikelola melalui menu pengguna oleh admin.
