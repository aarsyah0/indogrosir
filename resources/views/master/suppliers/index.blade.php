<x-app-layout title="Master Pemasok">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Master Pemasok</h2>
            <p class="master-page-description">Simpan informasi pemasok yang digunakan pada transaksi pembelian.</p>
        </div>
        <a href="#supplier-create" class="btn-primary shrink-0">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah pemasok
        </a>
    </div>

    <div class="grid gap-4 2xl:grid-cols-12">
        <!-- Add supplier form -->
        <section id="supplier-create" class="order-2 card h-fit scroll-mt-24 2xl:order-1 2xl:col-span-4">
            <div class="card-body">
                <h2 class="section-heading">Tambah pemasok</h2>
                <p class="text-xs text-slate-500 mt-0.5">Nama dan kode wajib diisi; informasi kontak bersifat opsional.</p>
                <form method="POST" action="{{ route('master.suppliers.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div>
                            <label for="supplier-code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode</label>
                            <input id="supplier-code" name="code" class="input" value="{{ old('code') }}" required />
                        </div>
                        <div>
                            <label for="supplier-name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama pemasok</label>
                            <input id="supplier-name" name="name" class="input" value="{{ old('name') }}" required />
                        </div>
                        <div>
                            <label for="supplier-contact" class="block text-sm font-medium text-slate-700 mb-1.5">Nama kontak</label>
                            <input id="supplier-contact" name="contact_name" class="input" value="{{ old('contact_name') }}" />
                        </div>
                        <div>
                            <label for="supplier-phone" class="block text-sm font-medium text-slate-700 mb-1.5">Telepon</label>
                            <input id="supplier-phone" name="phone" class="input" value="{{ old('phone') }}" />
                        </div>
                        <div class="sm:col-span-2 xl:col-span-1 2xl:col-span-2">
                            <label for="supplier-email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                            <input id="supplier-email" type="email" name="email" class="input" value="{{ old('email') }}" />
                        </div>
                    </div>
                    <div>
                        <label for="supplier-address" class="block text-sm font-medium text-slate-700 mb-1.5">Alamat</label>
                        <textarea id="supplier-address" name="address" rows="2" class="input">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label for="supplier-notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan <span class="font-normal text-slate-500">(opsional)</span></label>
                        <textarea id="supplier-notes" name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
                    </div>
                    <button class="btn-primary w-full justify-center">Tambah pemasok</button>
                </form>
            </div>
        </section>

        <!-- Supplier list -->
        <section class="order-1 card master-data-card 2xl:order-2 2xl:col-span-8">
            <div class="card-header flex-col sm:flex-row sm:items-center">
                <h2 class="card-title">Daftar pemasok</h2>
                <x-master-search id="supplier-search" label="Cari kode, nama, atau kontak pemasok" placeholder="Nama atau kontak pemasok" reset-route="master.suppliers.index" />
            </div>
            <div class="card-body">
                <div class="table-wrap">
                    <table class="min-w-full text-sm">
                        <thead class="bg-navy-50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kode</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pemasok</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Kontak</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Kelola</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/80">
                                    <td class="px-4 py-3 font-semibold text-navy-900">{{ $supplier->code }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-800">{{ $supplier->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $supplier->email ?: 'Email belum diisi' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="text-slate-600">{{ $supplier->contact_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $supplier->phone ?: 'Telepon belum diisi' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge badge-{{ $supplier->is_active ? 'success' : 'default' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <details class="group text-left">
                                            <summary class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50">
                                                <svg aria-hidden="true" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                                Edit pemasok
                                                <svg aria-hidden="true" class="h-3 w-3 transition-transform group-open:rotate-180" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 3 5 5-5 5"/></svg>
                                            </summary>
                                            <form method="POST" action="{{ route('master.suppliers.update', $supplier) }}" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left space-y-4">
                                                @csrf
                                                @method('PUT')
                                                <div class="grid gap-3 sm:grid-cols-2">
                                                    <div>
                                                        <label for="supplier-code-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Kode</label>
                                                        <input id="supplier-code-{{ $supplier->id }}" name="code" value="{{ old('code', $supplier->code) }}" class="input" required />
                                                    </div>
                                                    <div>
                                                        <label for="supplier-name-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Nama</label>
                                                        <input id="supplier-name-{{ $supplier->id }}" name="name" value="{{ old('name', $supplier->name) }}" class="input" required />
                                                    </div>
                                                    <div>
                                                        <label for="supplier-contact-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Kontak</label>
                                                        <input id="supplier-contact-{{ $supplier->id }}" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="input" />
                                                    </div>
                                                    <div>
                                                        <label for="supplier-phone-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Telepon</label>
                                                        <input id="supplier-phone-{{ $supplier->id }}" name="phone" value="{{ old('phone', $supplier->phone) }}" class="input" />
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label for="supplier-email-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Email</label>
                                                        <input id="supplier-email-{{ $supplier->id }}" type="email" name="email" value="{{ old('email', $supplier->email) }}" class="input" />
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label for="supplier-address-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Alamat</label>
                                                        <textarea id="supplier-address-{{ $supplier->id }}" name="address" rows="2" class="input">{{ old('address', $supplier->address) }}</textarea>
                                                    </div>
                                                    <div class="sm:col-span-2">
                                                        <label for="supplier-notes-{{ $supplier->id }}" class="block text-xs font-medium text-slate-600 mb-1.5">Catatan</label>
                                                        <textarea id="supplier-notes-{{ $supplier->id }}" name="notes" rows="2" class="input">{{ old('notes', $supplier->notes) }}</textarea>
                                                    </div>
                                                </div>
                                                <label class="flex items-center gap-2.5 text-sm font-normal">
                                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active)) />
                                                    Pemasok aktif
                                                </label>
                                                <div class="flex gap-2">
                                                    <button class="btn-primary flex-1 justify-center">Simpan perubahan</button>
                                                    <button type="button" class="btn-secondary" @click="document.querySelector('details').open = false">Batal</button>
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('master.suppliers.destroy', $supplier) }}" class="mt-2" onsubmit="return confirm('Hapus pemasok ini? Jika sudah memiliki transaksi, pemasok akan dinonaktifkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-secondary btn-sm text-rose-600">Hapus pemasok</button>
                                            </form>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center">
                                        <div class="empty-state">
                                            <p class="text-sm text-slate-500">Belum ada pemasok.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs text-slate-500">{{ $suppliers->total() }} pemasok</span>
                    {{ $suppliers->links() }}
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
