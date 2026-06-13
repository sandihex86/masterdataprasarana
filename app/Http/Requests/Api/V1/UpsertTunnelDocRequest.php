<?php

namespace App\Http\Requests\Api\V1;

use App\Services\TunnelDocumentUploadService;
use Illuminate\Foundation\Http\FormRequest;

class UpsertTunnelDocRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...TunnelDetailRules::docs(''),
            ...TunnelDocumentUploadService::validationRules(),
        ];
    }
}
