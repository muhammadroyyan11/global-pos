@extends('layouts.admin')
@section('title', 'Pelanggan')
@section('page-title', 'Pelanggan')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-users" style="color:var(--primary);"></i> Daftar Pelanggan</h5>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah
        </a>
    </div>
    <div class="card-body" style="padding-bottom:0;">
        <form method="GET" style="display:flex;gap:10px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama / telepon..." value="{{ request('search') }}" style="max-width:260px;">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Cari</button>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-rotate"></i></a>
        </form>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>Nama</th><th>Telepon</th><th>Email</th><th>Transaksi</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($customers as $c)
                <tr>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td>{{ $c->phone ?? '-' }}</td>
                    <td>{{ $c->email ?? '-' }}</td>
                    <td><span class="badge badge-info">{{ $c->transactions_count }}</span></td>
                    <td>
                        <a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" action="{{ route('admin.customers.destroy', $c) }}" style="display:inline;" onsubmit="return confirm('Hapus pelanggan?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">Belum ada pelanggan</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $customers->links() }}</div>
</div>
@endsection
