{{--
    <x-table> — Root wrapper untuk basic table.

    Mode:
      - mode="client" (default) → data-nt-basic-table aktif, pagination/search
        murni di browser. Cocok untuk data kecil (puluhan-ratusan baris) atau
        saat kamu memang sengaja load semua baris ke client sekaligus.
      - mode="server" → TIDAK ada data-nt-basic-table sama sekali, JS client
        tidak ikut campur. Kamu wajib isi prop `rows` (LengthAwarePaginator)
        dan render pagination Laravel bawaan.

    id:
      - Kalau tidak diisi, auto-generate biar tetap unik (aman dipakai lebih
        dari 1 tabel dalam 1 halaman). Isi manual kalau butuh target pasti,
        misal untuk live-target LiveDomJS: "#{{ $id }}-tbody".
--}}
@props([
    'title' => null,
    'searchable' => false,
    'selectable' => false,
    'striped' => false,
    'bordered' => false,
    'compact' => false,
    'responsive' => false,
    'sticky' => false,
    'perPage' => 10,
    'pagination' => true,
    'wrapClass' => 'nt-table-wrap-bare',

    'mode' => 'client', // 'client' | 'server'
    'rows' => null, // wajib LengthAwarePaginator kalau mode="server"
    'searchName' => 'search', // nama query string search kalau mode="server"

    'id' => null,
])

@php
    $isServer = $mode === 'server';

    if ($isServer && !$rows) {
        throw new \InvalidArgumentException('<x-table mode="server"> wajib diisi prop `rows` (LengthAwarePaginator).');
    }

    $tableId = $id ?? 'nt-table-' . \Illuminate\Support\Str::random(6);

    $tableClasses = trim(
        implode(' ', [
            'nt-table',
            $striped ? 'nt-table-striped' : '',
            $bordered ? 'nt-table-bordered' : '',
            $compact ? 'nt-table-compact' : '',
            $responsive ? 'nt-table-responsive-card' : '',
        ]),
    );

    $hasToolbar = $title || $searchable || $selectable || isset($actions);
@endphp

<div {{ $attributes->class('comp-section-body p-0') }} id="{{ $tableId }}"
    @unless ($isServer)
        data-nt-basic-table data-nt-page-size="{{ $perPage }}"
    @else
        data-nt-server-table
    @endunless>

    @if ($hasToolbar)
        <div class="nt-table-toolbar">
            @if ($title)
                <span class="nt-table-title">{{ $title }}</span>
            @endif

            @if ($selectable)
                <span data-nt-selected-count class="text-[12px] text-indigo-500 font-medium hidden">0 dipilih</span>
            @endif

            <div class="ml-auto flex items-center gap-2">
                @if ($searchable)
                    @if ($isServer)
                        <form method="GET" class="nt-table-search" data-nt-server-search-form>
                            @foreach (request()->except([$searchName, 'page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <i class="fa-solid fa-magnifying-glass text-[11px] nt-text-soft"></i>
                            <input type="text" name="{{ $searchName }}" value="{{ request($searchName) }}"
                                placeholder="Cari…" data-nt-search-input autocomplete="off">
                        </form>
                    @else
                        <div class="nt-table-search">
                            <i class="fa-solid fa-magnifying-glass text-[11px] nt-text-soft"></i>
                            <input type="text" placeholder="Cari…" data-nt-search-input />
                        </div>
                    @endif
                @endif

                {{ $actions ?? '' }}
            </div>
        </div>
    @endif

    <div class="nt-table-wrap {{ $wrapClass }}">
        @if ($sticky)
            <div class="nt-table-sticky-head">
                <table class="{{ $tableClasses }}" id="{{ $tableId }}-table">
                    <thead>
                        <tr>{{ $head ?? '' }}</tr>
                    </thead>
                    <tbody id="{{ $tableId }}-tbody" data-nt-table-body>{{ $slot }}</tbody>
                </table>
            </div>
        @else
            <table class="{{ $tableClasses }}" id="{{ $tableId }}-table">
                <thead>
                    <tr>{{ $head ?? '' }}</tr>
                </thead>
                <tbody id="{{ $tableId }}-tbody" data-nt-table-body>{{ $slot }}</tbody>
            </table>
        @endif
    </div>

    @if ($pagination)
        <div class="nt-table-footer">
            @if ($isServer)
                <span class="text-[12px] nt-text-soft">
                    Menampilkan {{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }}
                    entri
                </span>
                {{ $rows->links('vendor.pagination.netra') }}
            @else
                <span data-nt-table-info class="text-[12px] nt-text-soft"></span>
                <div class="nt-pagination" data-nt-pagination></div>
            @endif
        </div>
    @endif
</div>
