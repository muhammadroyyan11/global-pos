@extends('layouts.admin')
@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')
@section('page-title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
<div class="card" style="max-width:600px;">
    <div class="card-header">
        <h5>{{ isset($category) ? 'Edit' : 'Tambah' }} Kategori</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf
            @if(isset($category)) @method('PUT') @endif

            <div class="form-group">
                <label class="form-label">Nama Kategori *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
                @error('name')<div style="color:#dc3545;font-size:.8rem;margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
