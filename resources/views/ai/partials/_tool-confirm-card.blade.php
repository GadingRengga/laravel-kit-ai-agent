@php
  $status = $actionLog->status; // proposed | confirmed | rejected | failed
@endphp

<div class="chat-msg-row ai" id="ai-action-{{ $actionLog->id }}" data-log-id="{{ $actionLog->id }}">
  <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
  <div class="chat-msg-col">

    <div class="chat-tool-card chat-tool-card--{{ $status }}">

      <div class="chat-tool-card-head">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Usulan data baru</span>
        @if ($status === 'confirmed')
          <span class="chat-tool-badge chat-tool-badge--ok"><i class="fa-solid fa-check"></i> Tersimpan</span>
        @elseif ($status === 'rejected')
          <span class="chat-tool-badge chat-tool-badge--muted">Dibatalkan</span>
        @elseif ($status === 'failed')
          <span class="chat-tool-badge chat-tool-badge--error"><i class="fa-solid fa-xmark"></i> Gagal</span>
        @endif
      </div>

      <p class="chat-tool-summary">{!! $actionLog->summary !!}</p>

      @if ($status === 'failed' && $actionLog->failure_reason)
        <p class="chat-tool-error-note">{{ $actionLog->failure_reason }}</p>
      @endif

      <dl class="chat-tool-fields">
        @foreach ($actionLog->payload as $key => $value)
          @continue(blank($value))
          <div class="chat-tool-field">
            <dt>{{ \Illuminate\Support\Str::headline($key) }}</dt>
            <dd>{{ $value }}</dd>
          </div>
        @endforeach
      </dl>

      @if ($status === 'proposed')
        <div class="chat-tool-actions">
          <button type="button" class="chat-tool-btn-confirm" onclick="confirmAiAction({{ $actionLog->id }})">
            <i class="fa-solid fa-check"></i> Buat Sekarang
          </button>
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
