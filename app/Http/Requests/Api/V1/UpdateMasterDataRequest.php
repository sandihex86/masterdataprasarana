<?php

namespace App\Http\Requests\Api\V1;

class UpdateMasterDataRequest extends StoreMasterDataRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach ($rules as $field => $fieldRules) {
            if ($field === 'status') {
                continue;
            }

            array_unshift($rules[$field], 'sometimes');
        }

        $rules['status'] = ['sometimes', ...$rules['status']];

        return $rules;
    }
}
