{{--
    Tool Confirm Card — menampilkan usulan data dari AI untuk:
    - CREATE: form baru
    - UPDATE: form edit
    - DELETE: konfirmasi hapus
--}}
@php
    $status = $actionLog->status; // proposed | confirmed | rejected | failed
    $toolName = $actionLog->tool_name ?? '';

    // Tentukan tipe tool untuk styling
    $toolType = 'create';
    if (str_contains($toolName, 'update') || str_contains($toolName, 'edit')) {
        $toolType = 'update';
    } elseif (str_contains($toolName, 'delete') || str_contains($toolName, 'hapus')) {
        $toolType = 'delete';
    }

    // Field yang tidak boleh ditampilkan
    $hiddenFields = ['password', 'password_confirmation', 'remember_token'];
@endphp

<div class="chat-msg-row ai" id="ai-action-{{ $actionLog->id }}" data-log-id="{{ $actionLog->id }}"
    data-confirm-url="{{ route('ai.action.confirm', $actionLog) }}"
    data-reject-url="{{ route('ai.action.reject', $actionLog) }}" data-csrf="{{ csrf_token() }}">
    <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
    <div class="chat-msg-col">

        <div class="chat-tool-card chat-tool-card--{{ $status }} chat-tool-card--{{ $toolType }}">

            {{-- Header dengan icon sesuai tipe --}}
            <div class="chat-tool-card-head">
                <i
                    class="fa-solid fa-{{ $toolType === 'create' ? 'wand-magic-sparkles' : ($toolType === 'update' ? 'pen-to-square' : 'trash-can') }}"></i>
                <span>
                    @if ($toolType === 'create')
                        Usulan data baru
                    @elseif ($toolType === 'update')
                        Usulan perubahan data
                    @else
                        Konfirmasi penghapusan
                    @endif
                </span>
                @if ($status === 'confirmed')
                    <span class="chat-tool-badge chat-tool-badge--ok"><i class="fa-solid fa-check"></i> Tersimpan</span>
                @elseif ($status === 'rejected')
                    <span class="chat-tool-badge chat-tool-badge--muted">Dibatalkan</span>
                @elseif ($status === 'failed')
                    <span class="chat-tool-badge chat-tool-badge--error"><i class="fa-solid fa-xmark"></i> Gagal</span>
                @endif
            </div>

            {{-- Summary --}}
            <p class="chat-tool-summary">{!! $actionLog->summary !!}</p>

            {{-- Error message --}}
            @if ($status === 'failed' && $actionLog->failure_reason)
                <p class="chat-tool-error-note">{{ $actionLog->failure_reason }}</p>
            @endif

            {{-- Fields --}}
            <div class="chat-tool-fields">
                @foreach ($actionLog->payload as $key => $value)
                    @continue(is_null($value))
                    @continue(in_array($key, $hiddenFields, true))

                    <div class="chat-tool-field">
                        <dt>{{ \Illuminate\Support\Str::headline($key) }}</dt>
                        <dd>
                            @if (is_bool($value))
                                <span class="chat-badge chat-badge-{{ $value ? 'success' : 'danger' }}">
                                    {{ $value ? 'Ya' : 'Tidak' }}
                                </span>
                            @elseif (is_array($value))
                                <span class="chat-tool-array">
                                    {{ implode(', ', $value) }}
                                </span>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </div>

            {{-- Actions --}}
            @if ($status === 'proposed')
                <div class="chat-tool-actions">
                    @if ($toolType === 'delete')
                        <button type="button" class="chat-tool-btn-confirm chat-tool-btn--danger"
                            onclick="confirmAiAction({{ $actionLog->id }})">
                            <i class="fa-solid fa-trash-can"></i> Ya, Hapus
                        </button>
                    @else
                        <button type="button" class="chat-tool-btn-confirm"
                            onclick="confirmAiAction({{ $actionLog->id }})">
                            <i class="fa-solid fa-check"></i>
                            {{ $toolType === 'update' ? 'Simpan Perubahan' : 'Buat Sekarang' }}
                        </button>
                    @endif
                    <button type="button" class="chat-tool-btn-edit" onclick="editAiAction({{ $actionLog->id }})">
                        <i class="fa-regular fa-pen-to-square"></i> Edit
                    </button>
                    <button type="button" class="chat-tool-btn-cancel" onclick="rejectAiAction({{ $actionLog->id }})">
                        Batal
                    </button>
                </div>
            @endif

        </div>

        <div class="chat-meta-row">
            <span class="chat-meta-time">{{ $actionLog->updated_at->format('H:i') }}</span>
        </div>
    </div>
</div>
