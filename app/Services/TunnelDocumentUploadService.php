<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TunnelDocumentUploadService
{
    public const FILE_FIELDS = [
        'ded_bed_kajian_teknis',
        'spesifikasi_teknis',
        'shop_drawing',
        'as_built_drawing',
        'dok_hasil_uji',
    ];

    /**
     * @return array<string, array<int, string>>
     */
    public static function validationRules(): array
    {
        $rules = [
            'docs_files' => ['nullable', 'array'],
        ];

        foreach (self::FILE_FIELDS as $field) {
            $rules['docs_files.'.$field] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'];
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function mergeUploadedFiles(Request $request, array $payload, bool $nestedDocs = true): array
    {
        unset($payload['docs_files']);

        foreach (self::FILE_FIELDS as $field) {
            $file = $request->file('docs_files.'.$field);

            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($nestedDocs) {
                $payload['docs'] ??= [];
                $payload['docs'][$field] = $this->store($file);
            } else {
                $payload[$field] = $this->store($file);
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function store(UploadedFile $file): array
    {
        $path = $file->store('tunnels/docs/'.now()->format('Y/m'), 'public');

        return [
            'disk' => 'public',
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_at' => now()->toIso8601String(),
        ];
    }
}
