{{--
  Modal ini pakai portal-pattern yang sudah kamu bangun (di-append ke
  document.body saat dibuka, lihat memori project: dynamic modal component).
  Sesuaikan wrapper <div class="modal ..."> dengan komponen modal Blade-mu
  sendiri — di sini saya tulis versi generik yang tetap konsisten class
  Netra UI (dropdown-panel/modal look) supaya gampang kamu tempel ke
  komponen modal aslimu.
--}}
<div id="ai-connect-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
  <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-[#1B1A2E] p-5 shadow-2xl">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
        <i class="fa-solid fa-key"></i>
      </div>
      <div>
        <h3 class="text-[14px] font-bold text-slate-900 dark:text-white">Hubungkan Akun ChatGPT</h3>
        <p class="text-[11.5px] text-slate-400">API key disimpan terenkripsi, hanya kamu yang bisa pakai.</p>
      </div>
    </div>

    <form action="{{ route('ai.connections.store') }}" method="POST" class="space-y-3">
      @csrf
      <input type="hidden" name="provider_code" value="openai">

      <div>
        <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300">OpenAI API Key</label>
        <input type="password" name="api_key" required placeholder="sk-…"
          class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-transparent px-3 py-2 text-[13px] outline-none focus:border-indigo-500" />
        <p class="text-[11px] text-slate-400 mt-1">
          Dapatkan di
          <a href="https://platform.openai.com/api-keys" target="_blank" class="text-indigo-500 hover:underline">platform.openai.com/api-keys</a>
        </p>
      </div>

      <div>
        <label class="text-[12px] font-medium text-slate-600 dark:text-slate-300">Model (opsional)</label>
        <input type="text" name="default_model" placeholder="gpt-4.1-mini"
          class="mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-transparent px-3 py-2 text-[13px] outline-none focus:border-indigo-500" />
      </div>

      <div class="flex items-center gap-2 pt-2">
        <button type="submit" class="flex-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-[13px] font-medium py-2.5">
          Hubungkan
        </button>
        <button type="button" onclick="closeAiConnectModal()" class="rounded-lg border border-slate-200 dark:border-slate-700 text-[13px] font-medium py-2.5 px-4">
          Batal
        </button>
      </div>
    </form>
  </div>
</div>
