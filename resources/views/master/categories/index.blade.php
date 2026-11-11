<x-app-layout title="Kategori Barang">
    <div class="master-page-heading">
        <div class="min-w-0">
            <h2 class="master-page-title">Kategori Barang</h2>
            <p class="master-page-description">Kelola kategori yang digunakan untuk mengelompokkan barang.</p>
        </div>
        <a href="{{ route('master.items.index') }}" class="btn-secondary shrink-0">Kembali ke Master Barang</a>
    </div>

    <div class="grid gap-4 2xl:grid-cols-12">
        <section class="card h-fit 2xl:col-span-4">
            <div class="card-body">
                <h3 class="section-heading">Tambah kategori</h3>
                <p class="mt-0.5 text-xs text-slate-500">Kode dan nama kategori harus unik.</p>
                <form method="POST" action="{{ route('master.categories.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="category-code" class="mb-1.5 block text-sm font-medium text-slate-700">Kode kategori</label>
                        <input id="category-code" name="code" class="input" value="{{ old('code') }}" placeholder="Contoh: ELEKTRONIK" required />
                        @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="category-name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama kategori</label>
                        <input id="category-name" name="name" class="input" value="{{ old('name') }}" placeholder="Nama kategori baru" required />
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn-primary w-full justify-center">Tambah kategori</button>
                </form>
            </div>
        </section>

        <section class="card 2xl:col-span-8">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Daftar kategori</h3>
                    <p class="card-subtitle mt-0.5">{{ $categories->count() }} kategori</p>
                </div>
            </div>
            <div class="card-body">
                @forelse ($categories as $category)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 py-3 first:pt-0 last:border-0 last:pb-0">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $category->name }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $category->code }}</p>
                        </div>
                        <span class="badge {{ $category->is_active ? 'badge-success' : 'badge-default' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                @empty
                    <div class="empty-state"><p class="text-sm text-slate-500">Belum ada kategori.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
