@extends('layouts.admin')
@section('title', 'Bundling')
@section('page-title', 'Bundling Produk')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-boxes-stacked" style="color:var(--primary);"></i> Daftar Bundling</h5>
        <a href="{{ route('admin.bundles.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Buat Bundling
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Bundling</th>
                    <th>Isi Produk</th>
                    <th>Harga Normal</th>
                    <th>Harga Bundle</th>
                    <th>Hemat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bundles as $bundle)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($bundle->image_url)
                                <img src="{{ $bundle->image_url }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                            @else
                                <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(135deg,#e85d04,#f4a261);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                            @endif
                            <div>
                                <strong>{{ $bundle->name }}</strong>
                                @if($bundle->description)
                                    <div style="font-size:.75rem;color:#888;">{{ Str::limit($bundle->description, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $bundle->items_count }} produk</span>
                    </td>
                    <td style="color:#888;text-decoration:line-through;">
                        Rp {{ number_format($bundle->normal_price, 0, ',', '.') }}
                    </td>
                    <td>
                        <strong style="color:var(--primary);">Rp {{ number_format($bundle->price, 0, ',', '.') }}</strong>
                    </td>
                    <td>
                        @if($bundle->saving > 0)
                            <span class="badge badge-success">Hemat Rp {{ number_format($bundle->saving, 0, ',', '.') }}</span>
                        @else
                            <span style="color:#aaa;">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $bundle->is_active ? 'badge-success' : 'badge-secondary' }}">
                            {{ $bundle->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.bundles.edit', $bundle) }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.bundles.destroy', $bundle) }}" style="display:inline;" onsubmit="return confirm('Hapus bundling ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:#888;">
                        <i class="fa-solid fa-boxes-stacked" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        Belum ada bundling. <a href="{{ route('admin.bundles.create') }}" style="color:var(--primary);">Buat sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $bundles->links() }}</div>
</div>
@endsection
