<?php

namespace App\Http\Requests;

use App\Models\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // For now, allow all authenticated users. We can add specific logic later.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'short_description' => 'required|string|max:' . Organisation::SHORT_DESCRIPTION_LIMIT,
            'description' => 'required|string',
            'solution_types' => 'required|array|min:1',
            'solution_types.*' => 'exists:solution_types,id',
            'technology_types' => 'required|array|min:1',
            'technology_types.*' => 'exists:technology_types,id',
        ];
    }
}
