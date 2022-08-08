<?php

namespace App\Http\Requests;

use App\Models\Tiket;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateTiketRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('tiket_edit');
    }

    public function rules()
    {
        return [
            'no_tiket' => [
                'string',
                'nullable',
            ],
            'total_bayar' => [
                'string',
                'nullable',
            ],
        ];
    }
}
