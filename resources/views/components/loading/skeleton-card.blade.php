{{--
    <x-loading.skeleton-card> — Bungkus siap pakai untuk beberapa <x-loading.skeleton>.
    Sekadar wrapper ".nt-skeleton-card" supaya kamu bisa menyusun kombinasi bebas di slot.

    Contoh:
        <x-loading.skeleton-card>
            <div class="nt-skeleton-row mb-4">
                <x-loading.skeleton variant="avatar" />
                <div class="flex-1">
                    <x-loading.skeleton width="60" />
                    <x-loading.skeleton width="40" />
                </div>
            </div>
            <x-loading.skeleton width="100" />
            <x-loading.skeleton width="90" />
            <x-loading.skeleton width="75" />
        </x-loading.skeleton-card>
--}}
<div {{ $attributes->class(['nt-skeleton-card']) }}>
    {{ $slot }}
</div>
