{{-- Contoh: replikasi "Default Table" dari tema, tapi dengan data dinamis --}}

<x-table title="Data Pengguna" searchable selectable per-page="8">

    <x-slot:actions>
        <button class="nt-btn nt-btn-primary nt-btn-sm">
            <i class="fa-solid fa-plus"></i> Tambah
        </button>
        <button class="nt-btn nt-btn-secondary nt-btn-sm">
            <i class="fa-solid fa-download"></i> Export
        </button>
    </x-slot:actions>

    <x-slot:head>
        <x-table.check-th />
        <x-table.th col="name" sortable default="asc">Nama</x-table.th>
        <x-table.th col="email" sortable>Email</x-table.th>
        <x-table.th col="role" sortable>Role</x-table.th>
        <x-table.th col="status" sortable>Status</x-table.th>
        <x-table.th col="joined" sortable>Bergabung</x-table.th>
        <x-table.th align="right">Aksi</x-table.th>
    </x-slot:head>

    @foreach ($users as $user)
        <x-table.row clickable>
            <x-table.check-cell />

            <x-table.cell label="Nama" :sort-value="strtolower($user->name)">
                <div class="flex items-center gap-2">
                    <div class="nt-avatar nt-bg-{{ $user->avatar_color }}">{{ $user->initials }}</div>
                    <div>
                        <p class="font-medium text-[13px] text-slate-900 dark:text-slate-200">{{ $user->name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $user->email }}</p>
                    </div>
                </div>
            </x-table.cell>

            <x-table.cell label="Email">{{ $user->email }}</x-table.cell>

            <x-table.cell label="Role">
                <span class="nt-badge nt-badge-primary nt-text-xs">{{ $user->role }}</span>
            </x-table.cell>

            <x-table.cell label="Status" :sort-value="$user->status">
                <span class="nt-badge nt-badge-{{ $user->status_variant }}">
                    <span class="nt-badge-dot nt-bg-{{ $user->status_dot }}"></span>
                    {{ $user->status_label }}
                </span>
            </x-table.cell>

            <x-table.cell label="Bergabung" class="font-mono text-[12px]">
                {{ $user->joined_at->format('d/m/Y') }}
            </x-table.cell>

            <x-table.cell>
                <x-table.actions>
                    <x-table.action icon="fa-regular fa-eye" title="Detail" href="{{ route('users.show', $user) }}" />
                    <x-table.action icon="fa-regular fa-pen-to-square" title="Edit" href="{{ route('users.edit', $user) }}" />
                    <x-table.action icon="fa-regular fa-trash-can" title="Hapus" danger
                        wire:click="delete({{ $user->id }})"
                        wire:confirm="Yakin hapus {{ $user->name }}?" />
                </x-table.actions>
            </x-table.cell>
        </x-table.row>
    @endforeach

</x-table>

{{--
Varian modifier, tinggal tambah prop di <x-table>:
  <x-table striped>              → baris belang-belang
  <x-table bordered>             → semua sel ada border
  <x-table compact>               → padding lebih rapat
  <x-table responsive>            → jadi card di mobile (wajib pasang label= di setiap cell)
  <x-table sticky>                → header tetap saat scroll
  <x-table :pagination="false">   → tanpa footer pagination (cocok utk mini table widget dashboard)
--}}
