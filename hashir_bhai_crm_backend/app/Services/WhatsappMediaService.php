<?php

namespace App\Services;

use App\Models\WhatsappAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WhatsappMediaService
{
    public function download(WhatsappAccount $account, string $mediaId, string $type): ?string
    {
        if (! $account->isConfigured()) {
            return null;
        }

        $version = $account->api_version ?: 'v21.0';

        try {
            $metaResponse = Http::withToken((string) $account->access_token)
                ->acceptJson()
                ->timeout(20)
                ->get("https://graph.facebook.com/{$version}/{$mediaId}");

            if ($metaResponse->failed()) {
                Log::warning('WhatsApp media lookup failed', ['response' => $metaResponse->json()]);

                return null;
            }

            $url = $metaResponse->json('url');

            if (blank($url)) {
                return null;
            }

            $fileResponse = Http::withToken((string) $account->access_token)->timeout(60)->get($url);

            if ($fileResponse->failed()) {
                Log::warning('WhatsApp media download failed', ['status' => $fileResponse->status()]);

                return null;
            }

            $extension = match ($type) {
                'image' => 'jpg',
                'video' => 'mp4',
                'audio' => 'ogg',
                default => 'bin',
            };

            $path = 'whatsapp/'.now()->format('Y/m').'/'.Str::uuid().'.'.$extension;

            Storage::disk('public')->put($path, $fileResponse->body());

            return $path;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp media download error: '.$e->getMessage());

            return null;
        }
    }
}
