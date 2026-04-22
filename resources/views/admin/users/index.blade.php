@extends('layouts.admin')
@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fa-solid fa-user-gear" style="color:var(--primary);"></i> Daftar Pengguna</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah
        </a>
    </div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        @if($user->id === auth()->id()) <span class="badge badge-info">Anda</span> @endif
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ $user->role === 'admin' ? 'badge-warning' : 'badge-secondary' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-secondary"><i class="fa-solid fa-pen"></i></a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline;" onsubmit="return confirm('Hapus pengguna ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#888;">Belum ada pengguna</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body">{{ $users->links() }}</div>
</div>
@endsection
