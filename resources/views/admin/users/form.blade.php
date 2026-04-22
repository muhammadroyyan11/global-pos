@extends('layouts.admin')
@section('title', isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h5>{{ isset($user) ? 'Edit' : 'Tambah' }} Pengguna</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? '') }}" required>
                @error('name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email ?? '') }}" required>
                @error('email')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password {{ isset($user) ? '(kosongkan jika tidak diubah)' : '*' }}</label>
                    <input type="password" name="password" class="form-control" {{ isset($user) ? '' : 'required' }}>
                    @error('password')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Role *</label>
                    <select name="role" class="form-control" required>
                        <option value="cashier" {{ old('role', $user->role ?? '') === 'cashier' ? 'selected' : '' }}>Kasir</option>
                        <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ old('is_active', $user->is_active ?? 1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $user->is_active ?? 1) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
