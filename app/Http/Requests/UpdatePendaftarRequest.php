<?php

namespace App\Http\Requests;

use App\Models\Pendaftar;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdatePendaftarRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('pendaftar_edit');
    }

    public function rules()
    {
        return [
            'no_tiket' => [
                'string',
                'nullable',
            ],
            'nama' => [
                'string',
                'required',
            ],
            'nik' => [
                'string',
                'required',
                'unique:pendaftars,nik,' . request()->route('pendaftar')->id,
            ],
            'email' => [
                'string',
                'required',
            ],
            'no_hp' => [
                'string',
                'required',
            ],
            'total_bayar' => [
                'string',
                'nullable',
            ],
        ];
    }
}
