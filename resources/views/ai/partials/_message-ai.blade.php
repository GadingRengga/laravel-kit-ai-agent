{{--
    Bubble AI — otomatis mendeteksi format konten:
    - Detail Card (DATA DETAIL) → kartu informasi dengan section
    - Data Table (Ditemukan X data) → daftar item dengan badge role
    - Teks biasa → chat bubble dengan format markdown minimal
--}}
<div class="chat-msg-row ai">
    <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
    <div class="chat-msg-col">
        @php
            $content = $message->content ?? '';
        @endphp

        {{-- ── DETAIL CARD: single item dengan section --}}
        @if (preg_match('/^─{3,}\s*DATA DETAIL\s*─{3,}/m', $content))
            <div class="chat-detail-card">
                @php
                    $lines = explode("\n", $content);
                    $currentSection = '';
                    $sectionOpen = false;
                @endphp

                @foreach ($lines as $line)
                    @if (preg_match('/^─{3,}\s*(.+?)\s*─{3,}$/', trim($line), $m))
                        @if ($sectionOpen)
            </div>
        @endif
        @php
            $currentSection = $m[1];
            $sectionOpen = true;
        @endphp
        <div class="chat-detail-section">
            <h4 class="chat-detail-section-title">{{ $currentSection }}</h4>
        @elseif (str_contains($line, ':') && !str_starts_with(trim($line), 'INSTRUKSI') && !str_starts_with(trim($line), '---'))
            @php
                $parts = explode(':', $line, 2);
                $key = trim($parts[0] ?? '');
                $val = trim($parts[1] ?? '');
            @endphp
            @if ($key && $val)
                <div class="chat-detail-row">
                    <span class="chat-detail-label">{{ \Illuminate\Support\Str::headline($key) }}</span>
                    <span class="chat-detail-value">
                        @if (in_array(strtolower($val), ['aktif', 'ya', 'true']))
                            <span class="chat-badge chat-badge-success">{{ $val }}</span>
                        @elseif (in_array(strtolower($val), ['tidak aktif', 'tidak', 'false']))
                            <span class="chat-badge chat-badge-danger">{{ $val }}</span>
                        @else
                            {{ $val }}
                        @endif
                    </span>
                </div>
            @endif
            @endif
            @endforeach

            @if ($sectionOpen)
        </div>
        @endif
    </div>

    {{-- ── DATA TABLE: multiple items --}}
@elseif (preg_match('/^Ditemukan \d+ data:/m', $content))
    <div class="chat-data-table">
        @php
            $lines = explode("\n", $content);
            $instructionFound = false;
            $headerText = $lines[0] ?? 'Hasil Pencarian';
        @endphp
        <div class="chat-data-table-header">
            <div class="chat-data-table-header-left">
                <i class="fa-solid fa-database"></i>
                <span>{{ $headerText }}</span>
            </div>
            <span class="chat-data-table-count">
                {{ preg_match('/\d+/', $headerText, $m) ? $m[0] : '' }} item
            </span>
        </div>
        <div class="chat-data-table-body">
            @foreach (array_slice($lines, 1) as $line)
                @if (str_contains($line, 'INSTRUKSI') || str_contains($line, '---'))
                    @php $instructionFound = true; @endphp
                @endif
                @if ($instructionFound)
                    @continue
                @endif
                @if (trim($line) === '')
                    @continue
                @endif
                @if (preg_match('/^- (.+?) \(ID: (\d+)\)(\s*\[(.+?)\])?$/', trim($line), $m))
                    <div class="chat-data-row">
                        <div class="chat-data-row-info">
                            <span class="chat-data-row-name">{{ $m[1] }}</span>
                            <span class="chat-data-row-id">#{{ $m[2] }}</span>
                        </div>
                        @if (!empty($m[4]))
                            <div class="chat-data-row-meta">
                                <i class="fa-solid fa-user-tag"></i>
                                {{ $m[4] }}
                            </div>
                        @endif
                    </div>
                @elseif (preg_match('/^- (.+)/', trim($line), $m))
                    <div class="chat-data-row">
                        <div class="chat-data-row-info">
                            <span class="chat-data-row-name">{{ $m[1] }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- ── TEKS BIASA --}}
@else
    <div class="chat-bubble ai">
        {!! nl2br(
            e(
                preg_replace(
                    ['/\*\*(.+?)\*\*/', '/\*(.+?)\*/', '/^- (.+)/m', '/^(\d+)\.\s(.+)/m'],
                    [
                        '<strong>$1</strong>',
                        '<em>$1</em>',
                        '<span class="chat-list-item">• $1</span>',
                        '<span class="chat-list-item">$1. $2</span>',
                    ],
                    $content,
                ),
            ),
        ) !!}
    </div>
    @endif

    <div class="chat-meta-row">
        <span class="chat-meta-time">{{ $message->created_at->format('H:i') }}</span>
        @if (($message->prompt_tokens ?? 0) > 0)
            <span class="chat-meta-tokens" title="Total token yang digunakan">
                <i class="fa-regular fa-file-lines"></i>
                {{ ($message->prompt_tokens ?? 0) + ($message->completion_tokens ?? 0) }}
            </span>
        @endif
    </div>
</div>
</div>
