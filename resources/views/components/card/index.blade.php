@props([
    'title' => null,
    'description' => null,
    'padding' => true,
])

<div {{ $attributes->class(['comp-section']) }}>
    @if ($title || $description || isset($header) || isset($actions))
        <div class="comp-section-header flex items-start justify-between gap-4">
            @isset($header)
                {{ $header }}
            @else
                <div>
                    @if ($title)
                        <p class="comp-section-title">{{ $title }}</p>
                    @endif
                    @if ($description)
                        <p class="comp-section-desc">{{ $description }}</p>
                    @endif
                </div>
            @endisset

            @isset($actions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    <div @class(['comp-section-body' => $padding])>
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="comp-section-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
