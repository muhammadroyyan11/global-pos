<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir — POS Jeruk Lokal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --primary: #e85d04; --primary-dark: #c44d00; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f0f0; height: 100vh; overflow: hidden; }

        /* Layout */
        .pos-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            grid-template-rows: 56px 1fr;
            height: 100vh;
        }

        /* Topbar */
        .pos-topbar {
            grid-column: 1 / -1;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            gap: 12px;
        }
        .pos-topbar-brand { font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
        .pos-topbar-right { display: flex; align-items: center; gap: 10px; }
        .pos-topbar a, .pos-topbar button {
            color: #fff; text-decoration: none; background: rgba(255,255,255,.2);
            border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;
            font-size: .8rem; display: flex; align-items: center; gap: 6px;
        }
        .pos-topbar a:hover, .pos-topbar button:hover { background: rgba(255,255,255,.35); }

        /* Left: Products */
        .pos-products {
            background: #f5f5f5;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .pos-search-bar {
            padding: 12px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pos-search-bar input {
            flex: 1;
            min-width: 140px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: .875rem;
        }
        .pos-search-bar input:focus { outline: none; border-color: var(--primary); }
        .category-tabs {
            padding: 8px 12px;
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .category-tabs::-webkit-scrollbar { display: none; }
        .cat-tab {
            padding: 5px 14px;
            border-radius: 20px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
            font-size: .8rem;
            white-space: nowrap;
            transition: all .2s;
        }
        .cat-tab.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .products-grid {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
            align-content: start;
        }
        .product-card {
            background: #fff;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            transition: all .2s;
            border: 2px solid transparent;
            text-align: center;
        }
        .product-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(232,93,4,.15); }
        .product-card img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-bottom: 8px; }
        .product-card .no-img {
            width: 60px; height: 60px; border-radius: 8px; background: #f0f0f0;
            display: flex; align-items: center; justify-content: center;
            color: #bbb; font-size: 1.4rem; margin: 0 auto 8px;
        }
        .product-card .p-name { font-size: .8rem; font-weight: 600; margin-bottom: 4px; line-height: 1.3; }
        .product-card .p-price { font-size: .85rem; color: var(--primary); font-weight: 700; }
        .product-card .p-stock { font-size: .7rem; color: #888; }
        .bundle-card { border: 2px dashed #e85d04 !important; }

        /* Right: Cart */
        .pos-cart {
            background: #fff;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #e0e0e0;
            min-height: 0;
            overflow: hidden;
        }
        .cart-header {
            padding: 12px 16px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .cart-header h6 { font-weight: 700; font-size: .95rem; }
        .btn-clear { background: none; border: none; color: #dc3545; cursor: pointer; font-size: .8rem; }
        .cart-items {
            overflow-y: auto;
            padding: 8px;
            flex: 1 1 auto;
            min-height: 100px;
            -webkit-overflow-scrolling: touch;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 6px;
            background: #fafafa;
            border: 1px solid #f0f0f0;
        }
        .cart-item-info { flex: 1; min-width: 0; }
        .cart-item-info .name { font-size: .8rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cart-item-info .price { font-size: .75rem; color: var(--primary); }
        .qty-control { display: flex; align-items: center; gap: 4px; }
        .qty-btn {
            width: 24px; height: 24px; border-radius: 6px; border: 1px solid #ddd;
            background: #fff; cursor: pointer; font-size: .85rem; display: flex;
            align-items: center; justify-content: center; transition: all .15s;
        }
        .qty-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .qty-val { width: 28px; text-align: center; font-size: .85rem; font-weight: 600; }
        .btn-remove { background: none; border: none; color: #dc3545; cursor: pointer; font-size: .85rem; padding: 2px; }

        /* Cart footer */
        .cart-footer {
            padding: 12px 16px 80px 16px;
            border-top: 1px solid #e0e0e0;
            overflow-y: auto;
            flex-shrink: 0;
            background: #fff;
            box-shadow: 0 -2px 8px rgba(0,0,0,0.08);
            max-height: 45vh;
            -webkit-overflow-scrolling: touch;
        }
        .cart-summary { margin-bottom: 12px; }
        .summary-row {
            display: flex; justify-content: space-between;
            font-size: .85rem; margin-bottom: 4px;
        }
        .summary-row.total {
            font-size: 1rem; font-weight: 700; color: var(--primary);
            border-top: 2px solid #e0e0e0; padding-top: 8px; margin-top: 4px;
        }

        .payment-methods { display: flex; gap: 6px; margin-bottom: 10px; }
        .pay-method {
            flex: 1; padding: 8px 4px; border: 2px solid #ddd; border-radius: 8px;
            background: #fff; cursor: pointer; font-size: .75rem; text-align: center;
            transition: all .2s;
        }
        .pay-method.active { border-color: var(--primary); background: #fff5f0; color: var(--primary); font-weight: 600; }
        .pay-method i { display: block; font-size: 1rem; margin-bottom: 2px; }
        .paid-input { margin-bottom: 10px; }
        .paid-input label { font-size: .8rem; color: #888; display: block; margin-bottom: 4px; }
        .paid-input input {
            width: 100%; padding: 8px 12px; border: 1px solid #ddd;
            border-radius: 8px; font-size: .9rem; font-weight: 600;
        }
        .paid-input input:focus { outline: none; border-color: var(--primary); }
        .change-display {
            background: #f0fff4; border: 1px solid #c3e6cb; border-radius: 8px;
            padding: 8px 12px; margin-bottom: 10px; display: flex;
            justify-content: space-between; align-items: center;
        }
        .change-display span { font-size: .8rem; color: #155724; }
        .change-display strong { color: #28a745; font-size: 1rem; }
        .btn-checkout {
            width: 100%; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 10px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: all .2s; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            margin-bottom: 8px;
        }
        .btn-checkout:hover { background: var(--primary-dark); }
        .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }

        /* Empty cart */
        .cart-empty {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: #bbb; gap: 8px;
        }
        .cart-empty i { font-size: 2.5rem; }
        .cart-empty p { font-size: .85rem; }

        /* Modal */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal-box {
            background: #fff; border-radius: 16px; padding: 24px;
            width: 90%; max-width: 400px; text-align: center;
        }
        .modal-box .success-icon { font-size: 3rem; color: #28a745; margin-bottom: 12px; }
        .modal-box h4 { margin-bottom: 8px; }
        .modal-box .invoice { font-size: .85rem; color: #888; margin-bottom: 16px; }
        .modal-box .change-big { font-size: 1.5rem; font-weight: 700; color: #28a745; margin-bottom: 16px; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .modal-actions a, .modal-actions button {
            padding: 10px 20px; border-radius: 8px; font-size: .875rem;
            font-weight: 600; cursor: pointer; text-decoration: none;
            border: none; display: flex; align-items: center; gap: 6px;
        }

        /* Responsive — Tablet */
        @media (max-width: 1024px) and (min-width: 769px) {
            .pos-layout { grid-template-columns: 1fr 300px; }
            .pos-cart { overflow: hidden; }
            .cart-items { flex: 1 1 auto; min-height: 80px; }
            .cart-footer { 
                overflow-y: auto; 
                max-height: 50vh;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Responsive — Mobile */
        @media (max-width: 768px) {
            body { overflow: auto; }
            .pos-layout {
                grid-template-columns: 1fr;
                grid-template-rows: 56px auto auto;
                height: auto;
            }
            .pos-products { height: 60vh; }
            .pos-cart { height: auto; min-height: 50vh; border-left: none; border-top: 2px solid var(--primary); }
            .products-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
        }
    </style>
</head>
<body>
<div class="pos-layout">
    <!-- Topbar -->
    <header class="pos-topbar">
        <div class="pos-topbar-brand">
            <i class="fa-solid fa-cash-register"></i>
            <span>POS Kasir</span>
        </div>
        <div class="pos-topbar-right">
            <span style="font-size:.8rem;opacity:.9;">{{ auth()->user()->name }}</span>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Admin</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit"><i class="fa-solid fa-right-from-bracket"></i> Keluar</button>
            </form>
        </div>
    </header>

    <!-- Products Panel -->
    <section class="pos-products">
        <div class="pos-search-bar">
            <input type="text" id="searchInput" placeholder="&#xf002; Cari produk..." oninput="onSearch()">
        </div>
        <div class="category-tabs" id="categoryTabs">
            <button class="cat-tab active" id="tabAll" onclick="showTab('all', this)">Semua</button>
            <button class="cat-tab" onclick="showTab('popular', this)">
                <i class="fa-solid fa-fire"></i> Sering Terjual
            </button>
            <button class="cat-tab" onclick="showTab('bundle', this)">
                <i class="fa-solid fa-boxes-stacked"></i> Bundling
            </button>
            @foreach($categories as $cat)
            <button class="cat-tab" onclick="filterCategory({{ $cat->id }}, this)">{{ $cat->name }}</button>
            @endforeach
        </div>
        <div class="products-grid" id="productsGrid">
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:#bbb;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;"></i>
            </div>
        </div>
    </section>

    <!-- Cart Panel -->
    <aside class="pos-cart">
        <div class="cart-header">
            <h6><i class="fa-solid fa-cart-shopping" style="color:var(--primary);"></i> Keranjang (<span id="cartCount">0</span>)</h6>
            <button class="btn-clear" onclick="clearCart()"><i class="fa-solid fa-trash"></i> Kosongkan</button>
        </div>

        <div id="cartItems" class="cart-items">
            <div class="cart-empty">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>Keranjang kosong</p>
                <p style="font-size:.75rem;">Klik produk untuk menambahkan</p>
            </div>
        </div>

        <div class="cart-footer">
            <div class="cart-summary">
                <div class="summary-row"><span>Subtotal</span><span id="subtotalDisplay">Rp 0</span></div>
                <div class="summary-row">
                    <span>Diskon</span>
                    <input type="number" id="discountInput" value="0" min="0" style="width:100px;padding:2px 6px;border:1px solid #ddd;border-radius:4px;font-size:.8rem;text-align:right;" oninput="updateSummary()">
                </div>
                <div class="summary-row total"><span>TOTAL</span><span id="totalDisplay">Rp 0</span></div>
            </div>

            <div class="payment-methods">
                <button class="pay-method active" data-method="cash" onclick="selectPayment('cash', this)">
                    <i class="fa-solid fa-money-bill"></i> Tunai
                </button>
                <button class="pay-method" data-method="transfer" onclick="selectPayment('transfer', this)">
                    <i class="fa-solid fa-building-columns"></i> Transfer
                </button>
                <button class="pay-method" data-method="qris" onclick="selectPayment('qris', this)">
                    <i class="fa-solid fa-qrcode"></i> QRIS
                </button>
            </div>

            <div class="paid-input" id="paidInputWrap">
                <label>Jumlah Bayar</label>
                <input type="number" id="paidAmount" placeholder="0" oninput="updateChange()">
            </div>

            <div class="change-display" id="changeDisplay" style="display:none;">
                <span>Kembalian</span>
                <strong id="changeAmount">Rp 0</strong>
            </div>

            <button class="btn-checkout" id="checkoutBtn" onclick="checkout()" disabled>
                <i class="fa-solid fa-check-circle"></i> Bayar Sekarang
            </button>
        </div>
    </aside>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal-box">
        <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
        <h4>Transaksi Berhasil!</h4>
        <div class="invoice" id="modalInvoice"></div>
        <div>Kembalian</div>
        <div class="change-big" id="modalChange"></div>
        <div class="modal-actions">
            <a href="#" id="btnPrintReceipt" target="_blank" style="background:#28a745;color:#fff;">
                <i class="fa-solid fa-print"></i> Struk
            </a>
            <button onclick="closeModal()" style="background:#6c757d;color:#fff;">
                <i class="fa-solid fa-plus"></i> Transaksi Baru
            </button>
        </div>
    </div>
</div>

<script>
let cart = [];
let currentTab = 'all';
let currentCategory = null;
let paymentMethod = 'cash';
let searchTimer = null;

function showTab(tab, el) {
    currentTab = tab; currentCategory = null;
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    loadItems();
}
function filterCategory(id, el) {
    currentTab = 'category'; currentCategory = id;
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    loadItems();
}
function onSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadItems, 250);
}
async function loadItems() {
    showLoading();
    const search = document.getElementById('searchInput').value.trim();
    if (search) {
        const res = await fetch(`/pos/all?search=${encodeURIComponent(search)}`);
        const items = await res.json();
        renderItems(items, `Hasil: "${search}" — ${items.length} item`);
        return;
    }
    if (currentTab === 'all') {
        const res = await fetch('/pos/all');
        renderItems(await res.json());
    } else if (currentTab === 'popular') {
        const res = await fetch('/pos/popular');
        renderPopular(await res.json());
    } else if (currentTab === 'bundle') {
        const res = await fetch('/pos/bundles');
        renderBundles(await res.json());
    } else if (currentTab === 'category') {
        const res = await fetch(`/pos/products?category_id=${currentCategory}`);
        renderItems(await res.json());
    }
}
function renderItems(items, label) {
    if (!items.length) { showEmpty('Tidak ada item ditemukan'); return; }
    const hdr = label ? `<div style="grid-column:1/-1;font-size:.8rem;color:#888;padding:2px 0;">${label}</div>` : '';
    document.getElementById('productsGrid').innerHTML = hdr + items.map(i => i.type === 'bundle' ? bundleCard(i) : productCard(i)).join('');
}
function renderPopular(items) {
    if (!items.length) { showEmpty('Belum ada data penjualan'); return; }
    document.getElementById('productsGrid').innerHTML =
        `<div style="grid-column:1/-1;font-size:.85rem;font-weight:700;color:#e85d04;"><i class="fa-solid fa-fire"></i> Sering Terjual</div>` +
        items.map(p => productCard(p, `<div style="font-size:.7rem;color:#e85d04;font-weight:600;">🔥 ${p.total_sold}× terjual</div>`)).join('');
}
function renderBundles(bundles) {
    if (!bundles.length) { showEmpty('Belum ada bundling'); return; }
    document.getElementById('productsGrid').innerHTML =
        `<div style="grid-column:1/-1;font-size:.85rem;font-weight:700;color:#e85d04;"><i class="fa-solid fa-boxes-stacked"></i> Paket Bundling</div>` +
        bundles.map(b => bundleCard(b)).join('');
}
function productCard(p, extra) {
    const badge = extra || `<div class="p-stock">Stok: ${p.stock}</div>`;
    return `<div class="product-card" onclick='addToCart(${JSON.stringify({id:p.id,type:"product",name:p.name,price:p.price,stock:p.stock})})'>${p.image_url?`<img src="${p.image_url}" alt="">`:`<div class="no-img"><i class="fa-solid fa-cup-straw"></i></div>`}<div class="p-name">${escHtml(p.name)}</div>${p.category?`<div style="font-size:.68rem;color:#aaa;">${escHtml(p.category)}</div>`:''}<div class="p-price">Rp ${fmt(p.price)}</div>${badge}</div>`;
}
function bundleCard(b) {
    const list = b.items.map(i=>`${i.product_name} ×${i.quantity}`).join(', ');
    const click = b.available ? `onclick='addBundleToCart(${JSON.stringify(b).replace(/'/g,"\\'")})'` : `style="opacity:.5;cursor:not-allowed;"`;
    return `<div class="product-card bundle-card" ${click}><div style="position:relative;display:inline-block;">${b.image_url?`<img src="${b.image_url}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;margin-bottom:6px;">`:`<div class="no-img" style="background:linear-gradient(135deg,#e85d04,#f4a261);color:#fff;"><i class="fa-solid fa-boxes-stacked"></i></div>`}${b.saving>0?`<span style="position:absolute;top:-4px;right:-4px;background:#28a745;color:#fff;font-size:.55rem;font-weight:700;padding:2px 4px;border-radius:8px;">HEMAT</span>`:''}</div><div class="p-name">📦 ${escHtml(b.name)}</div><div style="font-size:.68rem;color:#888;margin-bottom:3px;">${escHtml(list)}</div>${b.saving>0?`<div style="font-size:.7rem;color:#bbb;text-decoration:line-through;">Rp ${fmt(b.normal_price)}</div>`:''}<div class="p-price">Rp ${fmt(b.price)}</div>${b.saving>0?`<div style="font-size:.7rem;color:#28a745;font-weight:600;">Hemat Rp ${fmt(b.saving)}</div>`:''}${!b.available?`<div style="font-size:.7rem;color:#dc3545;">Stok tidak cukup</div>`:''}</div>`;
}
function addToCart(item) {
    const ex = cart.find(i => i.type==='product' && i.id===item.id);
    if (ex) { if (ex.qty >= item.stock) { alert('Stok tidak mencukupi!'); return; } ex.qty++; }
    else cart.push({...item, qty:1});
    renderCart();
}
function addBundleToCart(b) {
    const ex = cart.find(i => i.type==='bundle' && i.id===b.id);
    if (ex) ex.qty++; else cart.push({id:b.id,type:'bundle',name:b.name,price:b.price,items:b.items,qty:1});
    renderCart();
}
function updateQty(i, d) { cart[i].qty+=d; if(cart[i].qty<=0) cart.splice(i,1); renderCart(); }
function removeItem(i) { cart.splice(i,1); renderCart(); }
function clearCart() { cart=[]; renderCart(); }
function renderCart() {
    const c = document.getElementById('cartItems');
    document.getElementById('cartCount').textContent = cart.reduce((s,i)=>s+i.qty,0);
    if (!cart.length) {
        c.innerHTML=`<div class="cart-empty"><i class="fa-solid fa-cart-shopping"></i><p>Keranjang kosong</p><p style="font-size:.75rem;">Klik produk untuk menambahkan</p></div>`;
        document.getElementById('checkoutBtn').disabled=true; updateSummary(); return;
    }
    c.innerHTML=cart.map((item,i)=>`<div class="cart-item"><div class="cart-item-info"><div class="name">${item.type==='bundle'?'📦 ':''}${escHtml(item.name)}</div><div class="price">Rp ${fmt(item.price)} × ${item.qty} = <strong>Rp ${fmt(item.price*item.qty)}</strong></div>${item.type==='bundle'&&item.items?`<div style="font-size:.7rem;color:#888;">${item.items.map(x=>x.product_name+' ×'+(x.quantity*item.qty)).join(', ')}</div>`:''}</div><div class="qty-control"><button class="qty-btn" onclick="updateQty(${i},-1)">−</button><span class="qty-val">${item.qty}</span><button class="qty-btn" onclick="updateQty(${i},1)">+</button></div><button class="btn-remove" onclick="removeItem(${i})"><i class="fa-solid fa-xmark"></i></button></div>`).join('');
    document.getElementById('checkoutBtn').disabled=false; updateSummary();
}
function updateSummary() {
    const sub=cart.reduce((s,i)=>s+i.price*i.qty,0);
    const disc=parseFloat(document.getElementById('discountInput').value)||0;
    const tot=Math.max(0,sub-disc);
    document.getElementById('subtotalDisplay').textContent='Rp '+fmt(sub);
    document.getElementById('totalDisplay').textContent='Rp '+fmt(tot);
    updateChange();
}
function updateChange() {
    const disc=parseFloat(document.getElementById('discountInput').value)||0;
    const sub=cart.reduce((s,i)=>s+i.price*i.qty,0);
    const tot=Math.max(0,sub-disc);
    const paid=parseFloat(document.getElementById('paidAmount').value)||0;
    const chg=paid-tot;
    const el=document.getElementById('changeDisplay');
    if(paid>0){el.style.display='flex';document.getElementById('changeAmount').textContent='Rp '+fmt(Math.max(0,chg));el.style.background=chg>=0?'#f0fff4':'#fff5f5';el.style.borderColor=chg>=0?'#c3e6cb':'#f5c6cb';document.getElementById('changeAmount').style.color=chg>=0?'#28a745':'#dc3545';}
    else el.style.display='none';
}
function selectPayment(m,el){
    paymentMethod=m;
    document.querySelectorAll('.pay-method').forEach(b=>b.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('paidInputWrap').style.display=m==='cash'?'block':'none';
    if(m!=='cash'){const disc=parseFloat(document.getElementById('discountInput').value)||0;const sub=cart.reduce((s,i)=>s+i.price*i.qty,0);document.getElementById('paidAmount').value=Math.max(0,sub-disc);document.getElementById('changeDisplay').style.display='none';}
}
async function checkout(){
    if(!cart.length)return;
    const disc=parseFloat(document.getElementById('discountInput').value)||0;
    const sub=cart.reduce((s,i)=>s+i.price*i.qty,0);
    const tot=Math.max(0,sub-disc);
    const paid=parseFloat(document.getElementById('paidAmount').value)||tot;
    if(paymentMethod==='cash'&&paid<tot){alert('Jumlah bayar kurang dari total!');return;}
    const btn=document.getElementById('checkoutBtn');
    btn.disabled=true;btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
    try{
        const res=await fetch('/pos/checkout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({items:cart.map(i=>({type:i.type,id:i.id,quantity:i.qty})),customer_id:null,discount:disc,paid_amount:paid,payment_method:paymentMethod})});
        const data=await res.json();
        if(data.success){document.getElementById('modalInvoice').textContent=data.transaction.invoice_number;document.getElementById('modalChange').textContent='Rp '+fmt(data.transaction.change_amount);document.getElementById('btnPrintReceipt').href='/pos/receipt/'+data.transaction.id+'?autoprint=1';document.getElementById('successModal').classList.add('show');}
        else alert(data.message||'Terjadi kesalahan.');
    }catch(e){alert('Gagal terhubung ke server.');}
    btn.disabled=false;btn.innerHTML='<i class="fa-solid fa-check-circle"></i> Bayar Sekarang';
}
function closeModal(){
    document.getElementById('successModal').classList.remove('show');
    cart=[];document.getElementById('discountInput').value=0;document.getElementById('paidAmount').value='';
    renderCart();loadItems();
}
function showLoading(){document.getElementById('productsGrid').innerHTML='<div style="grid-column:1/-1;text-align:center;padding:40px;color:#bbb;"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;"></i></div>';}
function showEmpty(msg){document.getElementById('productsGrid').innerHTML=`<div style="grid-column:1/-1;text-align:center;padding:40px;color:#bbb;"><i class="fa-solid fa-box-open" style="font-size:2rem;display:block;margin-bottom:8px;"></i>${msg}</div>`;}
function fmt(n){return new Intl.NumberFormat('id').format(Math.round(n));}
function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}

loadItems();
</script>
</body>
</html>
