<?php

namespace App\Http\Requests;

use App\Models\Transaksi;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateTransaksiRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('transaksi_edit');
    }

    public function rules()
    {
        return [
            'invoice' => [
                'string',
                'nullable',
            ],
            'amount' => [
                'string',
                'nullable',
            ],
            'snap_token' => [
                'string',
                'nullable',
            ],
        ];
    }
}
