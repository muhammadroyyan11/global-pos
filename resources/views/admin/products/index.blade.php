@extends('layouts.admin')
@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-box" style="color:var(--primary);"></i> Daftar Produk</h5>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah
        </a>
    </div>
    <div class="card-body" style="padding-bottom:0;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama / SKU..." value="{{ request('search') }}" style="max-width:220px;">
            <select name="category_id" class="form-control" style="max-width:180px;">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Cari</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate"></i> Reset</a>
        </form>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                            @else
                                <div style="width:40px;height:40px;border-radius:8px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#aaa;">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <div style="font-size:.75rem;color:#888;">{{ $product->sku }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $product->category?->name ?? '-' }}</td>
                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $product->stock <= 5 ? 'badge-danger' : ($product->stock <= 20 ? 'badge-warning' : 'badge-success') }}">
                            {{ $product->stock }} {{ $product->unit }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="display:inline;" onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:20px;color:#888;">Belum ada produk</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $products->links() }}</div>
</div>
@endsection
