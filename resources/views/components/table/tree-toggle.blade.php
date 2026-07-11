{{--
    <x-table.tree-toggle> — Tombol expand/collapse untuk satu node tree.

    TIDAK ada live-click / live-target / nama function apapun hardcode di
    sini. Component ini murni menyediakan 2 attribute struktural yang wajib
    dibaca JS bawaan Netra (initTreeTable):
        data-node-id  — dipakai toggleNode() untuk temukan tombol ini
        data-loaded   — flag informatif (true/false), dibaca callback kamu
                        sendiri (mis. ntTreeBeforeLoad) untuk cegah fetch ulang

    Semua live-* / onclick / wire:click ditempel bebas oleh pemakai lewat
    $attributes, sesuai stack yang dipakai di masing-masing tree.

    Prop:
      nodeId — id unik node (wajib)
      loaded — true = children sudah ada di DOM, ikon default "minus"
               false = children belum ada, ikon default "plus", biasanya
               dipasangkan live-*/onclick oleh pemakai untuk lazy-load

    Contoh pemakaian (lazy-load lewat LiveDomJS):
      <x-table.tree-toggle node-id="{{ $division->id }}" :loaded="false"
          live-click="loadDivisionChildren({{ $division->id }})"
          live-target="#children-{{ $division->id }}"
          live-dom="append"
          live-callback-before="ntTreeBeforeLoad" />

    Contoh pemakaian (full client-side, tanpa lazy-load):
      <x-table.tree-toggle node-id="{{ $category->id }}" :loaded="true" />
--}}
@props(['nodeId', 'loaded' => true])

<button class="nt-tree-toggle" data-node-id="{{ $nodeId }}" data-loaded="{{ $loaded ? 'true' : 'false' }}"
    {{ $attributes }}>
    <i class="fa-solid fa-{{ $loaded ? 'minus' : 'plus' }}"></i>
</button>
