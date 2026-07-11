<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\StoreAiConnectionRequest;
use App\Models\Ai\AiConnection;
use App\Models\Ai\AiProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AiConnectionController extends Controller
{
    /**
     * Simpan / update API key user untuk sebuah provider.
     * Dipanggil dari modal "Hubungkan Akun AI" di halaman chat.
     */
    public function store(StoreAiConnectionRequest $request): RedirectResponse
    {
        $provider = AiProvider::where('code', $request->provider_code)->firstOrFail();

        AiConnection::updateOrCreate(
            [
                'user_id'        => Auth::id(),
                'ai_provider_id' => $provider->id,
            ],
            [
                'api_key'          => $request->api_key, // otomatis ter-enkripsi lewat cast di Model
                'default_model'    => $request->default_model,
                'is_active'        => true,
                'last_verified_at' => now(),
            ]
        );

        // Catatan: idealnya di sini ada 1x panggilan ringan ke API (mis. list models)
        // untuk memastikan key valid sebelum disimpan sebagai "verified".
        // Sengaja tidak ditulis di skeleton ini supaya tidak menambah biaya
        // tanpa sepengetahuanmu — tinggal tambahkan providernya sendiri.

        return back()->with('success', 'Akun ChatGPT berhasil dihubungkan.');
    }

    public function destroy(AiConnection $connection): RedirectResponse
    {
        abort_unless($connection->user_id === Auth::id(), 403);

        $connection->delete();

        return back()->with('success', 'Koneksi AI diputus.');
    }
}
