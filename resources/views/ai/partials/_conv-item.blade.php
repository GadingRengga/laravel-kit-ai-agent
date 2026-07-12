{{--
  Dipisah dari chat.blade.php supaya gampang di-reuse (misalnya kalau nanti
  "Chat Baru" mau ditambahkan ke list via AJAX tanpa reload halaman).
  $item  : App\Models\Ai\AiConversation (sudah eager-load 1 pesan terakhir,
           lihat AiChatController::index())
  $active: bool
--}}
@php
    // BUGFIX (history kurang informatif):
    // - $item->title sekarang otomatis terisi dari pesan pertama user
    //   (lihat AiChatService::sendUserMessage()), jadi tidak lagi selalu
    //   "Percakapan baru" untuk semua chat.
    // - Cuplikan (snippet) sebelumnya menampilkan jam relatif yang SAMA
    //   dengan yang di kanan (duplikat, tidak informatif). Sekarang
    //   tampilkan isi pesan terakhir yang sesungguhnya.
    $lastMessage = $item->messages->first();
    $snippetText = match (true) {
        !$lastMessage => 'Belum ada pesan',
        $lastMessage->role === 'user' && $lastMessage->content => $lastMessage->content,
        $lastMessage->role === 'assistant' && $lastMessage->content => $lastMessage->content,
        (bool) $lastMessage->tool_name => 'Meminta konfirmasi aksi…',
        default => 'Melampirkan gambar',
    };
@endphp
<div class="chat-conv-item {{ $active ? 'active' : '' }}" data-conv-id="{{ $item->id }}">
  <a href="{{ route('ai.chat.index', ['conversation' => $item->id]) }}" class="chat-conv-link">
    <div class="chat-conv-icon"><i class="fa-solid fa-message"></i></div>
    <div class="chat-conv-body">
      <p class="chat-conv-title">{{ $item->title ?: 'Percakapan baru' }}</p>
      <p class="chat-conv-snippet">{{ \Illuminate\Support\Str::limit($snippetText, 48) }}</p>
    </div>
    <span class="chat-conv-time">{{ $item->updated_at->diffForHumans(null, true) }}</span>
  </a>
  <button type="button" class="chat-conv-delete-btn" title="Hapus percakapan"
    onclick="deleteAiConversation({{ $item->id }})">
    <i class="fa-regular fa-trash-can"></i>
  </button>
</div>

