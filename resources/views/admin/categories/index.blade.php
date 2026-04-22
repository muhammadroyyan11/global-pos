@extends('layouts.admin')
@section('title', 'Kategori')
@section('page-title', 'Kategori')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-tags" style="color:var(--primary);"></i> Daftar Kategori</h5>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Jumlah Produk</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><strong>{{ $cat->name }}</strong></td>
                    <td><code>{{ $cat->slug }}</code></td>
                    <td><span class="badge badge-info">{{ $cat->products_count }} produk</span></td>
                    <td>
                        <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-secondary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" style="display:inline;" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">Belum ada kategori</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $categories->links() }}</div>
</div>
@endsection
