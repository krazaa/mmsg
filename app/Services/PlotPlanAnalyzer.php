<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PlotPlanAnalyzer
{
    public function analyze(string $contents, string $mimeType): array
    {
        if (! config('services.gemini.api_key')) {
            throw ValidationException::withMessages(['plan' => 'Plot plan analysis is not configured. Add GEMINI_API_KEY to the application environment.']);
        }

        try {
            $model = rawurlencode(config('services.gemini.model'));
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
                ->timeout(120)->post("https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent", [
                    'contents' => [[
                        'parts' => [
                            ['text' => $this->instructions()],
                            ['inline_data' => ['mime_type' => $mimeType, 'data' => base64_encode($contents)]],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseJsonSchema' => $this->schema(),
                    ],
                ])->throw();
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['plan' => 'The plan analysis service could not be reached. Please try again.']);
        } catch (RequestException $exception) {
            report($exception);
            throw ValidationException::withMessages(['plan' => 'The plan could not be analyzed. Check the API configuration and image, then try again.']);
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        $result = is_string($text) ? json_decode($text, true) : null;

        if (! is_array($result) || empty($result['blocks'])) {
            throw ValidationException::withMessages(['plan' => 'No reliable plots were detected. Upload a clearer, higher-resolution plan.']);
        }

        return $result;
    }

    private function instructions(): string
    {
        return <<<'PROMPT'
Analyze this real-estate plotting plan for an inventory import. Group plots by clearly bounded block or sector. Extract every legible individual plot number and its printed dimensions or shared dimensions label. Convert square feet to marla using 272.25 sq ft per marla. If dimensions are feet, size_marla = width * length / 272.25. Never invent text hidden by markings, cropped edges, roads, parks, mosques, or open spaces. Plot numbers may repeat in different blocks. Give unnamed groups stable names such as "Detected Block 1". Confidence is from 0 to 1. Add a concise warning for uncertain numbering, grouping, or size. Return only the required schema.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'summary' => ['type' => 'string'],
                'warnings' => ['type' => 'array', 'items' => ['type' => 'string']],
                'blocks' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string'],
                        'plots' => ['type' => 'array', 'items' => [
                            'type' => 'object',
                            'properties' => [
                                'plot_number' => ['type' => 'string'], 'dimensions' => ['type' => 'string'],
                                'size_marla' => ['type' => 'number'], 'confidence' => ['type' => 'number'], 'note' => ['type' => 'string'],
                            ],
                            'required' => ['plot_number', 'dimensions', 'size_marla', 'confidence', 'note'], 'additionalProperties' => false,
                        ]],
                    ],
                    'required' => ['name', 'plots'], 'additionalProperties' => false,
                ]],
            ],
            'required' => ['summary', 'warnings', 'blocks'], 'additionalProperties' => false,
        ];
    }
}
