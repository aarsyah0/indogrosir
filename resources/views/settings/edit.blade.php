<x-app-layout title="Pengaturan Sistem">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-auto">
            <x-page-header
                header="Pengaturan Sistem"
                subheader="Preferensi yang memengaruhi tampilan nilai dan kontrol operasional."
                class="mb-4"
            >
                <a href="{{ route('settings.edit') }}" class="btn-secondary">Batal</a>
            </x-page-header>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <section>
                    <h2 class="section-heading">Format tampilan</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Dipakai untuk menampilkan harga, tanggal, dan angka.</p>
                    <div class="grid gap-4 md:grid-cols-2 mt-3">
                        <div>
                            <label for="currency-code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode mata uang</label>
                            <input id="currency-code" name="currency_code" class="input" value="{{ old('currency_code', $settings['currency_code'] ?? 'IDR') }}" required />
                        </div>
                        <div>
                            <label for="currency-symbol" class="block text-sm font-medium text-slate-700 mb-1.5">Simbol mata uang</label>
                            <input id="currency-symbol" name="currency_symbol" class="input" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? 'Rp') }}" required />
                        </div>
                        <div>
                            <label for="date-format" class="block text-sm font-medium text-slate-700 mb-1.5">Format tanggal</label>
                            <input id="date-format" name="date_format" class="input" value="{{ old('date_format', $settings['date_format'] ?? 'd/m/Y') }}" required />
                            <p class="text-xs text-slate-500 mt-1">Contoh: d/m/Y untuk 04/11/2026.</p>
                        </div>
                        <div>
                            <label for="number-format" class="block text-sm font-medium text-slate-700 mb-1.5">Format angka</label>
                            <input id="number-format" name="number_format" class="input" value="{{ old('number_format', $settings['number_format'] ?? '0,0') }}" required />
                            <p class="text-xs text-slate-500 mt-1">Contoh: 1.000,50 sesuai format lokal Indonesia.</p>
                        </div>
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-4">
                    <h2 class="section-heading">Kontrol pembelian</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Aturan yang membantu meninjau kuantitas dan pembelian di atas batas.</p>
                    <div class="grid gap-4 md:grid-cols-2 mt-3">
                        <div>
                            <label for="quantity-multiplier" class="block text-sm font-medium text-slate-700 mb-1.5">Multiplier kuantitas tidak wajar</label>
                            <input id="quantity-multiplier" type="number" step="0.1" min="1" name="unreasonable_qty_multiplier" class="input" value="{{ old('unreasonable_qty_multiplier', $settings['unreasonable_qty_multiplier'] ?? 2) }}" required />
                            <p class="text-xs text-slate-500 mt-1">Batas pemicu pemeriksaan dibanding kebutuhan sebelumnya.</p>
                        </div>
                        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3.5 text-sm font-normal cursor-pointer">
                            <input type="checkbox" name="over_purchase_requires_authorization" value="1" style="width: auto;" />
                            <span><span class="font-semibold text-slate-800">Wajibkan otorisasi pembelian berlebih</span><span class="mt-0.5 block text-xs text-slate-500">Aktifkan persetujuan khusus jika pembelian melebihi kuantitas yang disetujui.</span></span>
                        </label>
                    </div>
                </section>

                <section class="border-t border-slate-100 pt-4">
                    <h2 class="section-heading">Notifikasi</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Pilih peringatan yang perlu dikirim oleh aplikasi.</p>
                    <div class="grid gap-3 md:grid-cols-2 mt-3">
                        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3.5 text-sm font-normal cursor-pointer">
                            <input type="checkbox" name="email_notifications_enabled" value="1" style="width: auto;" />
                            <span><span class="font-semibold text-slate-800">Notifikasi email</span><span class="mt-0.5 block text-xs text-slate-500">Kirim pemberitahuan proses melalui email.</span></span>
                        </label>
                        <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3.5 text-sm font-normal cursor-pointer">
                            <input type="checkbox" name="min_stock_notification" value="1" style="width: auto;" />
                            <span><span class="font-semibold text-slate-800">Peringatan stok minimum</span><span class="mt-0.5 block text-xs text-slate-500">Tandai barang yang perlu diperiksa atau diisi ulang.</span></span>
                        </label>
                    </div>
                </section>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">Perubahan berlaku untuk seluruh pengguna.</p>
                    <button type="submit" class="btn-primary">Simpan pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
