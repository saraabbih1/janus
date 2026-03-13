<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexHabitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'active' => [
                'nullable',
                Rule::in(['true', 'false', '1', '0', 1, 0, true, false]),
            ],
        ];
    }
}
