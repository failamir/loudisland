<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public string $text;
    public ?string $url;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $text, ?string $url = null)
    {
        $this->phone = $phone;
        $this->text = $text;
        $this->url = $url;
        // You can also set $this->onQueue('notifications'); // if you want a named queue
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $base = rtrim(config('services.waha.base_url'), '/');
            $session = config('services.waha.session');
            $apiKey = config('services.waha.api_key');
            $chatId = $this->normalizePhone($this->phone);

            // For now, send simple text (no image)
            Http::withHeaders([
                'x-api-key' => $apiKey,
            ])->post($base . '/api/sendText', [
                'chatId' => $chatId,
                'session' => $session,
                'text' => $this->text,
            ]);
        } catch (\Throwable $e) {
            Log::warning('WA send failed (queued): ' . $e->getMessage());
            $this->release(10); // backoff retry
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strpos($digits, '0') === 0) {
            $digits = '62' . substr($digits, 1);
        } elseif (strpos($digits, '62') !== 0) {
            // assume already in international without +, prefix with 62 if missing
            $digits = '62' . $digits;
        }
        return $digits;
    }
}
