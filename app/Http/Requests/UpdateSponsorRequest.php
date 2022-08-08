<?php

namespace App\Http\Requests;

use App\Models\Sponsor;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateSponsorRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('sponsor_edit');
    }

    public function rules()
    {
        return [
            'nama' => [
                'string',
                'nullable',
            ],
        ];
    }
}
