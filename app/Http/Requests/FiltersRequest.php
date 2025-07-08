<?php

namespace App\Http\Requests;

use App\BusinessLogicLayer\FilterService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FiltersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Define validation rules for filters.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [];

        foreach (FilterService::$filters as $filterableModel) {
            $filterSingularSlug = $filterableModel::getSingularSlug();
            $rules[$filterSingularSlug] = [
                'nullable',
                Rule::in($filterableModel::pluck('slug')->toArray()),
            ];
        }

        return $rules;
    }

    /**
     * Prepare the data for validation.
     * Only keep filters that have a valid value, and set invalid filters to null.
     */
    protected function prepareForValidation(): void
    {
        $filtered = array_filter($this->all(), function ($value, $key) {
            return isset($this->rules()[$key]) && $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        foreach ($this->rules() as $filter => $rule) {
            if (! isset($filtered[$filter])) {
                $this->merge([$filter => null]);
            }
        }
    }
}
