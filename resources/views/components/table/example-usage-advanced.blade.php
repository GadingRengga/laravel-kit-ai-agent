{{-- ══════════════════════════════════════════════
     1. TREE TABLE (hierarki)
════════════════════════════════════════════════ --}}

<div class="comp-section-header flex items-center justify-between">
    <p class="comp-section-title">Struktur Organisasi &amp; Anggaran</p>
    <x-table.tree-actions target="#tree-table" />
</div>

<x-table.tree id="tree-table">
    <x-slot:head>
        <th class="nt-min-w-260">Divisi / Tim / Anggota</th>
        <th>Kepala</th>
        <th>Anggaran</th>
    </x-slot:head>

    <x-table.tree-row node-id="div-tech" level="1" icon="fa-solid fa-folder-open" label="Divisi Teknologi">
        <x-table.cell>Andi R.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 2.400Jt</x-table.cell>
    </x-table.tree-row>

    <x-table.tree-row node-id="tm-eng" parent="div-tech" level="2" icon="fa-solid fa-folder-open" label="Tim Engineering">
        <x-table.cell>Budi S.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 900Jt</x-table.cell>
    </x-table.tree-row>

    <x-table.tree-row node-id="mb-fe" parent="tm-eng" level="3" :expandable="false" icon="fa-regular fa-user" label="Frontend Dev">
        <x-table.cell>Citra L.</x-table.cell>
        <x-table.cell class="font-mono text-[12.5px]">Rp 350Jt</x-table.cell>
    </x-table.tree-row>
</x-table.tree>


{{-- ══════════════════════════════════════════════
     2. COLLAPSE TABLE (accordion detail)
     jumlah kolom thead = 5 → colspan detail-row = 5
════════════════════════════════════════════════ --}}

<x-table.collapse id="collapse-table">
    <x-slot:head>
        <th class="nt-w-30"></th>
        <th>Order ID</th>
        <th>Pelanggan</th>
        <th>Total</th>
        <th>Status</th>
    </x-slot:head>

    @foreach ($orders as $order)
        <x-table.collapse-row target="detail-row-{{ $order->id }}">
            <x-table.expand-cell target="detail-row-{{ $order->id }}" />
            <x-table.cell class="font-mono text-[12px] nt-text-primary">#{{ $order->code }}</x-table.cell>
            <x-table.cell class="font-medium nt-text-strong">{{ $order->customer_name }}</x-table.cell>
            <x-table.cell class="font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</x-table.cell>
            <x-table.cell><span class="nt-badge nt-badge-info">{{ $order->status_label }}</span></x-table.cell>
        </x-table.collapse-row>

        <x-table.detail-row id="detail-row-{{ $order->id }}" colspan="5">
            <x-table.detail-grid>
                <x-table.detail-item label="No. HP">{{ $order->phone }}</x-table.detail-item>
                <x-table.detail-item label="Alamat">{{ $order->address }}</x-table.detail-item>
                <x-table.detail-item label="Kurir">{{ $order->courier }}</x-table.detail-item>
            </x-table.detail-grid>
        </x-table.detail-row>
    @endforeach
</x-table.collapse>


{{-- ══════════════════════════════════════════════
     3. EDITABLE TABLE
     status-rules: array PHP biasa, di-encode ke JSON otomatis oleh komponen
════════════════════════════════════════════════ --}}

<x-table.editable
    id="editable-table"
    currency-prefix="Rp "
    name-field="name"
    entity-label="Produk"
    status-field="stock"
    :status-rules="[
        ['max' => 0, 'label' => 'Habis', 'cls' => 'nt-badge-danger', 'color' => '#DC2626'],
        ['max' => 4, 'label' => 'Low Stock', 'cls' => 'nt-badge-warning', 'color' => '#D97706'],
        ['max' => 999999, 'label' => 'Aktif', 'cls' => 'nt-badge-success', 'color' => ''],
    ]"
>
    <x-slot:head>
        <th class="col-check"><input type="checkbox" class="form-checkbox" /></th>
        <x-table.editable-th editable>Nama Produk</x-table.editable-th>
        <x-table.editable-th editable>Harga</x-table.editable-th>
        <x-table.editable-th editable>Stok</x-table.editable-th>
        <th>SKU</th>
        <th>Status</th>
        <th class="nt-th-right">Aksi</th>
    </x-slot:head>

    @foreach ($products as $product)
        <x-table.editable-row :row-id="$product->id">
            <td class="col-check"><input type="checkbox" class="form-checkbox"></td>

            <x-table.editable-cell field="name" :value="$product->name">{{ $product->name }}</x-table.editable-cell>
            <x-table.editable-cell field="price" :value="$product->price" type="number" format="currency" class="font-mono">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </x-table.editable-cell>
            <x-table.editable-cell field="stock" :value="$product->stock" type="number" format="integer" class="font-mono">
                {{ $product->stock }}
            </x-table.editable-cell>

            <td class="font-mono text-[11.5px] nt-cell-sku">{{ $product->sku }}</td>

            <x-table.status-cell>
                <span class="nt-badge nt-badge-{{ $product->status_variant }}">{{ $product->status_label }}</span>
            </x-table.status-cell>

            <td>
                <x-table.actions>
                    <x-table.action icon="fa-regular fa-eye" title="Detail" data-nt-row-detail />
                    <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger data-nt-row-delete />
                </x-table.actions>
            </td>
        </x-table.editable-row>
    @endforeach
</x-table.editable>


{{-- ══════════════════════════════════════════════
     4. GANTT TABLE
     $tasks bisa dari Eloquent collection, cukup map ke bentuk array ini
════════════════════════════════════════════════ --}}

<div class="comp-section-header flex items-center justify-between flex-wrap gap-2">
    <p class="comp-section-title">Project Roadmap</p>
    <x-table.gantt-nav />
</div>

<x-table.gantt
    month="2026-06"
    :tasks="[
        ['label' => 'Netra UI Design System', 'pic' => 'Eka N.', 'start' => '2026-06-01', 'end' => '2026-06-20', 'progress' => 85, 'color' => 'indigo'],
        ['label' => 'API Gateway v2', 'pic' => 'Rian S.', 'start' => '2026-06-10', 'end' => '2026-06-25', 'progress' => 40, 'color' => 'green'],
        ['label' => 'Rilis Beta', 'pic' => 'Tim Produk', 'start' => '2026-06-30', 'milestone' => true, 'color' => 'amber'],
    ]"
/>
