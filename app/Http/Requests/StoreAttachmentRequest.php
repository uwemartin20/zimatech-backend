<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files'   => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:20480', // 20 MB per file before processing
                'mimes:jpeg,jpg,png,webp,pdf',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'files'   => 'Dateien',
            'files.*' => 'Datei',
        ];
    }

    public function messages(): array
    {
        return [
            'files.*.mimes' => 'Nur JPEG, PNG, WEBP und PDF Dateien sind erlaubt.',
            'files.*.max'   => 'Jede Datei darf maximal 20 MB groß sein.',
            'files.required'=> 'Bitte mindestens eine Datei auswählen.',
        ];
    }
}