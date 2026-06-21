<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class PythonAIService
{
    private string $baseUrl;

    public function __construct()
    {
        // Use a placeholder default if not set in .env
        $this->baseUrl = config('services.python_ai.url', env('PYTHON_AI_URL', 'http://127.0.0.1:5000'));
    }

    /**
     * Get property recommendations from the Python ML microservice based on a user's query.
     *
     * @param string $query
     * @param array<string, mixed> $filters
     * @return array<int, mixed>
     */
    public function getRecommendations(string $query, array $filters = []): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/api/recommend", [
                    'query' => $query,
                    'filters' => $filters,
                ])
                ->throw();

            return $response->json('recommendations') ?? [];
        } catch (RequestException $e) {
            Log::error('Python AI Service Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Process a chatbot message via the Python NLP microservice.
     *
     * @param string $message
     * @param int $userId
     * @return string
     */
    public function processChatbotMessage(string $message, int $userId): string
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->baseUrl}/api/chat", [
                    'message' => $message,
                    'user_id' => $userId,
                ])
                ->throw();

            return $response->json('reply') ?? 'I am currently unable to process your request.';
        } catch (RequestException $e) {
            Log::error('Python AI Chatbot Error: ' . $e->getMessage());
            return 'Sorry, the AI service is currently down.';
        }
    }
}
