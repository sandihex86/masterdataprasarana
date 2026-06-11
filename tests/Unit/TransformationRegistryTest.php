<?php

namespace Tests\Unit;

use App\Services\Import\TransformationRegistry;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TransformationRegistryTest extends TestCase
{
    public function test_it_applies_multiple_transformations_in_order(): void
    {
        $registry = new TransformationRegistry;

        $value = $registry->apply(' ab-01 ', ['trim', 'uppercase', 'normalize_code']);

        $this->assertSame('AB-01', $value);
    }

    public function test_it_converts_decimal_comma_to_nullable_float(): void
    {
        $registry = new TransformationRegistry;

        $value = $registry->apply('106,8704', ['decimal_comma_to_dot', 'nullable_float']);

        $this->assertSame(106.8704, $value);
    }

    public function test_it_rejects_unsupported_transformation(): void
    {
        $registry = new TransformationRegistry;

        $this->expectException(ValidationException::class);

        $registry->apply('value', ['unsafe_php_callback']);
    }
}
