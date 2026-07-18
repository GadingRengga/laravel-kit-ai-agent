{{--
    <x-avatar.upload> — Avatar dengan overlay edit saat hover, untuk upload foto profil.

    Contoh:
        <x-avatar.upload name="avatar" initials="GD" color="gradient-indigo" size="2xl" />
        <x-avatar.upload name="logo" shape="square" initials="CO" color="gradient-violet" size="2xl" icon="fa-solid fa-pen" />
        <x-avatar.upload name="avatar" src="https://api.dicebear.com/9.x/avataaars/svg?seed=Upload" size="2xl" />

    Props:
        name      string — dipakai untuk atribut `name` pada <input type="file">
        shape     circle | square             (default: circle)
        icon      class FontAwesome untuk overlay (default: fa-solid fa-camera)
        accept    string — accept attribute file input (default: image/*)
        semua props <x-avatar> lain (src, initials, color, size) turut didukung.

    JS: butuh netra-avatar.js — otomatis meng-preview gambar & menembak event `nt:avatar:upload`
    untuk di-hook ke Livewire/AJAX.
--}}
@props([
    'name' => 'avatar',
    'src' => null,
    'initials' => null,
    'color' => null,
    'size' => '2xl',
    'shape' => 'circle',
    'icon' => 'fa-solid fa-camera',
    'accept' => 'image/*',
])

@php
    $shapeClass = $shape === 'square' ? 'nt-avatar-upload-square' : null;
    $avatarShapeClass = $shape === 'square' ? 'nt-avatar-square' : null;
@endphp

<label {{ $attributes->class(['nt-avatar-upload', $shapeClass]) }} data-nt-avatar-upload
    aria-label="Upload foto">
    <div class="nt-avatar nt-avatar-{{ $size }} {{ $avatarShapeClass }} {{ $color ? "nt-avatar-{$color}" : '' }}">
        @if($src)
            <img src="{{ $src }}" alt="Current avatar" data-nt-upload-preview />
        @else
            {{ $initials }}
        @endif
    </div>
    <div class="nt-avatar-upload-overlay" @if($shape === 'square') style="border-radius:10px" @endif>
        <i class="{{ $icon }}"></i>
    </div>
    <input type="file" name="{{ $name }}" accept="{{ $accept }}" data-nt-upload-input aria-hidden="true" />
</label>
