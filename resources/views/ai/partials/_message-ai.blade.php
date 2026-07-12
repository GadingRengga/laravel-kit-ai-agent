{{-- Tidak ada perubahan dari versi asli — tetap dipakai apa adanya. --}}
<div class="chat-msg-row ai">
    <div class="chat-avatar ai"><i class="fa-solid fa-sparkles"></i></div>
    <div class="chat-msg-col">
        <div class="chat-bubble ai">{{ $message->content }}</div>
        <div class="chat-meta-row">
            <span class="chat-meta-time">{{ $message->created_at->format('H:i') }}</span>
        </div>
    </div>
</div>
