<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiGradingService
{
    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->model = config('services.openai.model', 'gpt-4o-mini');
    }

    public function gradeShortAnswer(string $questionText, string $correctAnswer, string $studentAnswer, ?string $explanation = null): array
    {
        // Check if AI grading is enabled
        if (! config('exam.grading.ai_enabled')) {
            return [
                'score' => 0,
                'reasoning' => 'AI grading disabled',
                'provider' => 'none',
            ];
        }

        if (empty($this->apiKey)) {
            return [
                'score' => 0,
                'reasoning' => 'API key not configured',
                'provider' => 'none',
            ];
        }

        $prompt = "Grade this short answer:\n\n";
        $prompt .= "Question: {$questionText}\n";
        $prompt .= "Correct Answer: {$correctAnswer}\n";
        $prompt .= "Student Answer: {$studentAnswer}\n";
        if ($explanation) {
            $prompt .= "Explanation: {$explanation}\n";
        }
        $prompt .= "\nScore from 0 to 1 (1=fully correct, 0=completely wrong). Return JSON: {\"score\": float, \"reasoning\": string}";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.3,
            ]);

            if ($response->failed()) {
                Log::warning('OpenAI API request failed: '.$response->body());

                return [
                    'score' => 0,
                    'reasoning' => 'API request failed: '.$response->body(),
                    'provider' => 'openai',
                ];
            }

            $content = $response->json('choices.0.message.content');

            // Try to parse JSON from response
            $data = json_decode($content, true);

            if (! $data || ! isset($data['score'])) {
                // Maybe the response is wrapped in markdown code block
                preg_match('/```json\s*(.*?)\s*```/s', $content, $matches);
                if (isset($matches[1])) {
                    $data = json_decode($matches[1], true);
                }
            }

            if ($data && isset($data['score'])) {
                return [
                    'score' => (float) $data['score'],
                    'reasoning' => $data['reasoning'] ?? 'No reasoning provided',
                    'provider' => 'openai',
                ];
            }

            return [
                'score' => 0,
                'reasoning' => 'Could not parse AI response',
                'provider' => 'openai',
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI grading error: '.$e->getMessage());

            return [
                'score' => 0,
                'reasoning' => 'Error: '.$e->getMessage(),
                'provider' => 'openai',
            ];
        }
    }
}
