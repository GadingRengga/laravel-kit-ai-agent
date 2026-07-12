{{--
    <x-table.tree-row> — Satu baris node di tree table.

    Tombol toggle TIDAK dirender otomatis di sini — isi lewat named slot
    <x-slot:toggle> berisi <x-table.tree-toggle>. Ini yang bikin DX-nya
    dinamis: kamu bebas tempel live-click/live-target/onclick apapun di
    slot itu, berbeda-beda per tree, tanpa mengubah component ini.

    Kalau node ini expandable dan children-nya belum di-load (lazy-load),
    render placeholder kosong tepat di bawah row lewat prop `childrenTargetId`
    — ini yang jadi live-target tempat baris anak nanti disisipkan.

    Prop:
      nodeId           — id unik node (wajib)
      parent           — id node parent (wajib jika level > 1)
      level             — 1 | 2 | 3
      label             — teks label node
      icon              — class font-awesome (kosongkan untuk leaf/anggota)
      expandable        — false = leaf node (tanpa slot toggle sama sekali dipakai)
      childrenTargetId  — id untuk <tr> placeholder anak. Default "children-{nodeId}".
                          Isi eksplisit kalau butuh id lain, atau null untuk skip
                          placeholder sepenuhnya (mis. tree yang full client-side,
                          anak-anaknya sudah langsung dirender semua).
      hidden            — true = baris ini disembunyikan saat load awal
--}}
@props([
    'nodeId',
    'parent' => null,
    'level' => 1,
    'label',
    'icon' => null,
    'expandable' => true,
    'childrenTargetId' => true,
    'hidden' => false,
])

@php
    $targetId = match (true) {
        $childrenTargetId === true => "children-{$nodeId}",
        is_string($childrenTargetId) => $childrenTargetId,
        default => null,
    };
@endphp

<tr {{ $attributes->class([
    "nt-tree-l{$level}",
    'nt-tree-node',
    $level > 1 ? 'nt-tree-child' : '',
    $hidden ? 'hidden' : '',
]) }}
    data-node-id="{{ $nodeId }}" @if ($parent) data-parent="{{ $parent }}" @endif>
    <td>
        <div class="flex items-center nt-tree-indent nt-tree-indent-{{ $level }}">
            @if ($level > 1)
                <span class="nt-tree-line"></span>
            @endif

            @if ($expandable)
                {{ $toggle ?? '' }}
            @elseif ($level === 3)
                <span class="nt-tree-spacer"></span>
            @endif

            @if ($icon)
                <i
                    class="{{ $icon }} text-[12px] mr-1 {{ $expandable ? 'nt-text-primary' : 'nt-text-faint' }}"></i>
            @endif

            <span class="nt-text-sm">{{ $label }}</span>
        </div>
    </td>

    {{ $slot }}
</tr>

@if ($expandable && $targetId)
    {{-- placeholder kosong: live-target menyisip baris anak persis di sini --}}
    <tr id="{{ $targetId }}" class="nt-tree-children-placeholder" style="display:contents"></tr>
@endif
