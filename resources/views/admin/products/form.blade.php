@extends('layouts.admin')
@section('title', isset($product) ? 'Edit Produk' : 'Tambah Produk')
@section('page-title', isset($product) ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="card" style="max-width:700px;">
    <div class="card-header">
        <h5>{{ isset($product) ? 'Edit' : 'Tambah' }} Produk</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($product) ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @if(isset($product)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Produk *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
                    @error('name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}" placeholder="Auto-generate jika kosong">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Satuan *</label>
                    <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'pcs') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Harga Jual *</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" min="0" required>
                    @error('price')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Modal</label>
                    <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price ?? '') }}" min="0">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Stok *</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $product->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $product->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Foto Produk</label>
                @if(isset($product) && $product->image_url)
                    <div style="margin-bottom:8px;">
                        <img src="{{ $product->image_url }}" style="width:80px;height:80px;border-radius:8px;object-fit:cover;">
                    </div>
                @endif
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
