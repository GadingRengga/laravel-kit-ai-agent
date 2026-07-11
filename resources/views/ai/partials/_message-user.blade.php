<div class="chat-msg-row user">
  <div class="chat-avatar user">{{ \Illuminate\Support\Str::of(Auth::user()->name)->substr(0, 2)->upper() }}</div>
  <div class="chat-msg-col">
    <div class="chat-bubble user">{{ $text }}</div>
    <div class="chat-meta-row"><span class="chat-meta-time">{{ now()->format('H:i') }}</span></div>
  </div>
</div>
