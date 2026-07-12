@props(['target'])

<div {{ $attributes->class('flex gap-2') }}>
    <button data-nt-tree-expand-all data-target="{{ $target }}" class="nt-btn nt-btn-secondary nt-btn-sm">
        <i class="fa-solid fa-maximize"></i> Expand All
    </button>
    <button data-nt-tree-collapse-all data-target="{{ $target }}" class="nt-btn nt-btn-secondary nt-btn-sm">
        <i class="fa-solid fa-minimize"></i> Collapse
    </button>
</div>
