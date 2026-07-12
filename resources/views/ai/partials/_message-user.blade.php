{{--
  $text        : string
  $attachments : array of storage paths (disk "public"), opsional
--}}
<div class="chat-msg-row user">
    <div class="chat-avatar user">{{ \Illuminate\Support\Str::of(Auth::user()->name)->substr(0, 2)->upper() }}</div>
    <div class="chat-msg-col">
        @if (!empty($attachments))
            <div class="chat-msg-images">
                @foreach ($attachments as $path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" target="_blank"
                        class="chat-msg-image">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}"
                            alt="Lampiran gambar" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif

        @if (filled($text))
            <div class="chat-bubble user">{{ $text }}</div>
        @endif

        <div class="chat-meta-row"><span class="chat-meta-time">{{ now()->format('H:i') }}</span></div>
    </div>
</div>
