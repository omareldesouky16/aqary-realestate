<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\PythonAIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    private PythonAIService $aiService;

    public function __construct(PythonAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get property recommendations from the AI microservice.
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
            'filters' => 'nullable|array',
        ]);

        $query = $request->input('query');
        $filters = $request->input('filters', []);

        $recommendationIds = $this->aiService->getRecommendations($query, $filters);

        // Fetch the actual Property models based on AI recommended IDs
        if (!empty($recommendationIds)) {
            // Preserve the order of recommended IDs
            $placeholders = implode(',', array_fill(0, count($recommendationIds), '?'));
            $properties = Property::whereIn('id', $recommendationIds)
                ->orderByRaw("FIELD(id, {$placeholders})", $recommendationIds)
                ->get();
        } else {
            $properties = collect([]);
        }

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Chat with the AI assistant via Microservice Proxy.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'nullable|string',
        ]);

        $message = $request->input('message');
        
        // Use provided session_id or generate a new UUID for the chat session
        $sessionId = $request->input('session_id') ?? \Illuminate\Support\Str::uuid()->toString();

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->post('http://127.0.0.1:8001/api/chat', [
                    'message' => $message,
                    'session_id' => $sessionId,
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'reply' => 'I am currently unable to connect to the recommendation system.',
                'error' => $response->body()
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reply' => 'The recommendation microservice is offline.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
