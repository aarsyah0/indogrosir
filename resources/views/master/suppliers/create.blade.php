<x-app-layout title="Tambah Pemasok">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Tambah Pemasok</h2>
            <p class="master-page-description">Tambahkan informasi pemasok untuk digunakan pada transaksi pembelian.</p>
        </div>
        <a href="{{ route('master.suppliers.index') }}" class="btn-secondary shrink-0">Kembali ke Master Pemasok</a>
    </div>

    <section class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('master.suppliers.store') }}" class="space-y-5">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="supplier-code" class="mb-1.5 block text-sm font-medium text-slate-700">Kode pemasok</label>
                        <input id="supplier-code" name="code" class="input" value="{{ old('code') }}" required />
                        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="supplier-name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama pemasok</label>
                        <input id="supplier-name" name="name" class="input" value="{{ old('name') }}" required />
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="supplier-contact" class="mb-1.5 block text-sm font-medium text-slate-700">Nama kontak</label>
                        <input id="supplier-contact" name="contact_name" class="input" value="{{ old('contact_name') }}" />
                        @error('contact_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="supplier-phone" class="mb-1.5 block text-sm font-medium text-slate-700">Telepon</label>
                        <input id="supplier-phone" name="phone" class="input" value="{{ old('phone') }}" />
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="supplier-email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input id="supplier-email" type="email" name="email" class="input" value="{{ old('email') }}" />
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="supplier-address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
                        <textarea id="supplier-address" name="address" rows="3" class="input">{{ old('address') }}</textarea>
                        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="supplier-notes" class="mb-1.5 block text-sm font-medium text-slate-700">Catatan <span class="font-normal text-slate-500">(opsional)</span></label>
                        <textarea id="supplier-notes" name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <a href="{{ route('master.suppliers.index') }}" class="btn-secondary">Batal</a>
                    <button class="btn-primary">Simpan pemasok</button>
                </div>
            </form>
        </div>
    </section>
</x-app-layout>
