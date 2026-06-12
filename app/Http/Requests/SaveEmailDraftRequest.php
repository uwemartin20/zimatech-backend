<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEmailDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10'],
        ];
    }

    public function attributes(): array
    {
        return [
            'subject' => 'Betreff',
            'body' => 'Nachricht',
        ];
    }
}
