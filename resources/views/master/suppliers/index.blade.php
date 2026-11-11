<x-app-layout title="Master Pemasok">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Master Pemasok</h2>
            <p class="master-page-description">Simpan informasi pemasok yang digunakan pada transaksi pembelian.</p>
        </div>
        <a href="{{ route('master.suppliers.create') }}" class="btn-primary shrink-0">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah pemasok
        </a>
    </div>

    <section class="card master-data-card supplier-table">
            <div class="card-header flex-col sm:flex-row sm:items-center">
                <h2 class="card-title">Daftar pemasok</h2>
                <x-master-search id="supplier-search" label="Cari kode, nama, atau kontak pemasok" placeholder="Nama atau kontak pemasok" reset-route="master.suppliers.index" />
            </div>
            <div class="card-body">
                <div class="table-wrap">
                    <table class="w-full table-fixed text-sm">
                        <colgroup>
                            <col class="w-[15%]" />
                            <col class="w-[28%]" />
                            <col class="w-[22%]" />
                            <col class="w-[14%]" />
                            <col class="w-[21%]" />
                        </colgroup>
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
                                        <div class="flex flex-wrap justify-end gap-1.5">
                                            <a href="{{ route('master.suppliers.edit', $supplier) }}" class="btn-secondary btn-sm">Edit</a>
                                            <form method="POST" action="{{ route('master.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus pemasok ini? Jika sudah memiliki transaksi, pemasok akan dinonaktifkan.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-secondary btn-sm text-rose-600">Hapus</button>
                                            </form>
                                        </div>
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
                <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-2 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs text-slate-500">{{ $suppliers->total() }} pemasok</span>
                    {{ $suppliers->links() }}
                </div>
            </div>
    </section>
</x-app-layout>
