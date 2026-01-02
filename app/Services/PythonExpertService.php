<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PythonExpertService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = getWebConfig('base_url_python');
    }

    /**
     * Call Expert Recommendation API
     * POST /api/recommend
     */
    public function recommendExperts(string $question): array
    {
        try {
            $response = Http::timeout(10)
                ->post($this->baseUrl . '/api/recommend', [
                    'question' => $question
                ]);

            if ($response->failed()) {
                Log::error('Python recommend API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                throw new Exception('Expert recommendation service failed.');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Python recommend API exception', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Trigger model training
     * POST /api/train
     */
    public function trainModel(): bool
    {
        try {
            $response = Http::timeout(10)
                ->post($this->baseUrl . '/api/train');

            if ($response->failed()) {
                Log::error('Python train API failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Python train API exception', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Health check (optional)
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::get($this->baseUrl . '/');

            return $response->ok();
        } catch (Exception $e) {
            return false;
        }
    }
}
