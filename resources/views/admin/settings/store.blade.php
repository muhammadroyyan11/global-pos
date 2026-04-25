@extends('layouts.admin')
@section('title', 'Pengaturan Toko')
@section('page-title', 'Pengaturan Toko')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header"><h5>Informasi Toko</h5></div>
    <div class="card-body">
        @if(session('success'))
            <div style="background:#d4edda;color:#155724;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.875rem;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.store.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama Toko *</label>
                <input type="text" name="store_name" class="form-control"
                    value="{{ old('store_name', $settings['store_name'] ?? '') }}" required>
                @error('store_name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tagline</label>
                <input type="text" name="store_tagline" class="form-control"
                    value="{{ old('store_tagline', $settings['store_tagline'] ?? '') }}"
                    placeholder="Contoh: No Sugar · No Water · 100% Pure Orange">
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="store_address" class="form-control" rows="2"
                    placeholder="Alamat lengkap toko">{{ old('store_address', $settings['store_address'] ?? '') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon</label>
                <input type="text" name="store_phone" class="form-control"
                    value="{{ old('store_phone', $settings['store_phone'] ?? '') }}"
                    placeholder="Contoh: 0812-3456-7890">
            </div>

            <div class="form-group">
                <label class="form-label">Media Sosial / Website</label>
                <input type="text" name="store_social" class="form-control"
                    value="{{ old('store_social', $settings['store_social'] ?? '') }}"
                    placeholder="Contoh: IG: @tokoku | tokoku.id">
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
</div>
@endsection
