<?php

namespace App\Services\MasterData;

class MasterDataChecksumService
{
    public function generate(array $payload): string
    {
        $normalized = $this->normalize($payload);

        return hash('sha256', json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function normalize(array $payload): array
    {
        unset(
            $payload['id'],
            $payload['uuid'],
            $payload['created_at'],
            $payload['updated_at'],
            $payload['deleted_at'],
            $payload['checksum'],
            $payload['version']
        );

        return $this->sortRecursively($payload);
    }

    private function sortRecursively(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item) => $this->sortRecursively($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->sortRecursively($item);
        }

        return $value;
    }
}
