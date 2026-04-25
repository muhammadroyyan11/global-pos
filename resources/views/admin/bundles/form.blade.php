@extends('layouts.admin')
@section('title', isset($bundle) ? 'Edit Bundling' : 'Buat Bundling')
@section('page-title', isset($bundle) ? 'Edit Bundling' : 'Buat Bundling')

@section('content')
<style>
.bundle-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.product-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #fafafa;
    border-radius: 8px;
    margin-bottom: 6px;
    border: 1px solid #f0f0f0;
}
.product-row-info { flex: 1; font-size: .85rem; }
.product-row-qty { display: flex; align-items: center; gap: 4px; }
@media (max-width: 768px) {
    .bundle-grid {
        grid-template-columns: 1fr;
    }
    .form-row {
        grid-template-columns: 1fr;
    }
    .preview-sticky {
        position: static !important;
    }
    .product-select-wrap {
        flex-direction: column;
    }
    .product-select-wrap .btn {
        width: 100%;
    }
}
</style>
<div class="bundle-grid">

    <!-- Form -->
    <div class="card">
        <div class="card-header">
            <h5>{{ isset($bundle) ? 'Edit' : 'Buat' }} Bundling</h5>
        </div>
        <div class="card-body">
            <form method="POST" id="bundleForm"
                action="{{ isset($bundle) ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}"
                enctype="multipart/form-data">
                @csrf
                @if(isset($bundle)) @method('PUT') @endif

                <div class="form-group">
                    <label class="form-label">Nama Bundling *</label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name', $bundle->name ?? '') }}" required
                        placeholder="Contoh: Paket Hemat Sunkist">
                    @error('name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2"
                        placeholder="Deskripsi singkat bundling...">{{ old('description', $bundle->description ?? '') }}</textarea>
                </div>

                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Harga Bundling * <small style="color:#888;">(bisa lebih murah dari normal)</small></label>
                        <input type="number" name="price" id="bundlePrice" class="form-control"
                            value="{{ old('price', $bundle->price ?? '') }}" min="0" required
                            oninput="updatePreview()">
                        @error('price')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-control">
                            <option value="1" {{ old('is_active', $bundle->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $bundle->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Foto Bundling</label>
                    @if(isset($bundle) && $bundle->image_url)
                        <div style="margin-bottom:8px;">
                            <img src="{{ $bundle->image_url }}" style="width:80px;height:80px;border-radius:8px;object-fit:cover;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <!-- Pilih Produk -->
                <div class="form-group">
                    <label class="form-label">Tambah Produk ke Bundling *</label>
                    <div class="product-select-wrap" style="display:flex;gap:8px;">
                        <select id="productSelect" class="form-control">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                    data-name="{{ $p->name }}"
                                    data-price="{{ $p->price }}"
                                    data-stock="{{ $p->stock }}"
                                    data-unit="{{ $p->unit }}">
                                    {{ $p->name }} — Rp {{ number_format($p->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary" onclick="addProduct()">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    @error('products')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>

                <!-- List produk yang dipilih -->
                <div id="selectedProducts" style="margin-bottom:16px;"></div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Simpan Bundling
                    </button>
                    <a href="{{ route('admin.bundles.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview -->
    <div class="card preview-sticky" style="position:sticky;top:80px;">
        <div class="card-header">
            <h5><i class="fa-solid fa-eye" style="color:var(--primary);"></i> Preview di POS</h5>
        </div>
        <div class="card-body">
            <div id="bundlePreview" style="border:2px dashed #e85d04;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:2rem;margin-bottom:8px;">📦</div>
                <div id="previewName" style="font-weight:700;font-size:.95rem;margin-bottom:6px;">Nama Bundling</div>
                <div id="previewItems" style="font-size:.78rem;color:#888;margin-bottom:10px;text-align:left;"></div>
                <div id="previewNormal" style="font-size:.8rem;color:#aaa;text-decoration:line-through;"></div>
                <div id="previewPrice" style="font-size:1.2rem;font-weight:800;color:#e85d04;">Rp 0</div>
                <div id="previewSaving" style="font-size:.75rem;color:#28a745;font-weight:600;margin-top:4px;"></div>            </div>
        </div>
    </div>
</div>

<script>
let selectedProducts = [];

// Pre-fill jika edit
@if(isset($bundle) && $bundle->items->count())
    @foreach($bundle->items as $item)
    selectedProducts.push({
        id: {{ $item->product_id }},
        name: "{{ addslashes($item->product->name ?? '') }}",
        price: {{ $item->product->price ?? 0 }},
        stock: {{ $item->product->stock ?? 0 }},
        unit: "{{ $item->product->unit ?? 'pcs' }}",
        qty: {{ $item->quantity }},
    });
    @endforeach
    renderSelected();
@endif

function addProduct() {
    const sel = document.getElementById('productSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) return;

    const existing = selectedProducts.find(p => p.id == opt.value);
    if (existing) {
        existing.qty++;
    } else {
        selectedProducts.push({
            id: parseInt(opt.value),
            name: opt.dataset.name,
            price: parseFloat(opt.dataset.price),
            stock: parseInt(opt.dataset.stock),
            unit: opt.dataset.unit,
            qty: 1,
        });
    }
    sel.value = '';
    renderSelected();
}

function removeProduct(id) {
    selectedProducts = selectedProducts.filter(p => p.id !== id);
    renderSelected();
}

function changeQty(id, val) {
    const p = selectedProducts.find(p => p.id === id);
    if (p) { p.qty = Math.max(1, parseInt(val) || 1); }
    renderSelected();
}

function renderSelected() {
    const container = document.getElementById('selectedProducts');

    if (!selectedProducts.length) {
        container.innerHTML = '<div style="text-align:center;padding:16px;color:#bbb;border:1px dashed #ddd;border-radius:8px;font-size:.85rem;">Belum ada produk dipilih</div>';
        updatePreview();
        return;
    }

    container.innerHTML = selectedProducts.map((p, i) => `
        <div class="product-row">
            <input type="hidden" name="products[]" value="${p.id}">
            <input type="hidden" name="quantities[]" value="${p.qty}">
            <div class="product-row-info">
                <strong>${p.name}</strong>
                <div style="font-size:.75rem;color:#888;">Rp ${fmt(p.price)} / ${p.unit} | Stok: ${p.stock}</div>
            </div>
            <div class="product-row-qty">
                <button type="button" onclick="changeQty(${p.id}, ${p.qty - 1})"
                    style="width:28px;height:28px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer;font-size:1rem;">−</button>
                <input type="number" value="${p.qty}" min="1"
                    onchange="changeQty(${p.id}, this.value)"
                    style="width:44px;text-align:center;border:1px solid #ddd;border-radius:6px;padding:2px;font-size:.85rem;">
                <button type="button" onclick="changeQty(${p.id}, ${p.qty + 1})"
                    style="width:28px;height:28px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer;font-size:1rem;">+</button>
            </div>
            <button type="button" onclick="removeProduct(${p.id})"
                style="background:none;border:none;color:#dc3545;cursor:pointer;font-size:.9rem;padding:4px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `).join('');

    updatePreview();
}

function updatePreview() {
    const name = document.querySelector('[name="name"]').value || 'Nama Bundling';
    const price = parseFloat(document.getElementById('bundlePrice').value) || 0;
    const normalPrice = selectedProducts.reduce((s, p) => s + p.price * p.qty, 0);
    const saving = Math.max(0, normalPrice - price);

    document.getElementById('previewName').textContent = name;
    document.getElementById('previewPrice').textContent = 'Rp ' + fmt(price);
    document.getElementById('previewNormal').textContent = normalPrice > 0 ? 'Rp ' + fmt(normalPrice) : '';
    document.getElementById('previewSaving').textContent = saving > 0 ? 'Hemat Rp ' + fmt(saving) : '';
    document.getElementById('previewItems').innerHTML = selectedProducts.map(p =>
        `<div>• ${p.name} ×${p.qty}</div>`
    ).join('') || '<div style="color:#bbb;">Belum ada produk</div>';
}

document.querySelector('[name="name"]').addEventListener('input', updatePreview);

function fmt(n) {
    return new Intl.NumberFormat('id').format(Math.round(n));
}
</script>
@endsection
