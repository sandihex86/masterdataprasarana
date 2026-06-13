<?php

namespace App\Http\Requests\Web;

class UpdateBridgeSourceRequest extends StoreBridgeSourceRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach ($rules as $field => $fieldRules) {
            if (in_array($field, ['active', 'status', 'statusdata'], true)) {
                continue;
            }

            array_unshift($rules[$field], 'sometimes');
        }

        $rules['active'] = ['sometimes', ...$rules['active']];
        $rules['status'] = ['sometimes', ...$rules['status']];
        $rules['statusdata'] = ['sometimes', ...$rules['statusdata']];

        return $rules;
    }
}
