<?php

namespace App\Http\Requests;

use App\Data\ActivityLogFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityLogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:150'],
            'log' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', Rule::in(['created', 'updated', 'deleted'])],
        ];
    }

    public function filters(): ActivityLogFilters
    {
        $validated = $this->validated();

        return new ActivityLogFilters(
            search: $this->nullableTrimmed($validated['search'] ?? null),
            logName: $this->nullableTrimmed($validated['log'] ?? null),
            event: $this->nullableTrimmed($validated['event'] ?? null),
        );
    }

    private function nullableTrimmed(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }
}
