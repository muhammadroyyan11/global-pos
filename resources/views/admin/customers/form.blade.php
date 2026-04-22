@extends('layouts.admin')
@section('title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('page-title', isset($customer) ? 'Edit Pelanggan' : 'Tambah Pelanggan')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h5>{{ isset($customer) ? 'Edit' : 'Tambah' }} Pelanggan</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ isset($customer) ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
            @csrf
            @if(isset($customer)) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
                @error('name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address ?? '') }}</textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
