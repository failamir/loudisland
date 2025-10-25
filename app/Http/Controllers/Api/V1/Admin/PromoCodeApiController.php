<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PromoCodeApiController extends Controller
{
    public function index(Request $request)
    {
        $query = PromoCode::query();

        if ($search = $request->string('q')) {
            $query->where('code', 'like', '%' . $search . '%');
        }

        $perPage = max(1, (int) $request->input('per_page', 20));
        return response()->json($query->orderByDesc('id')->paginate($perPage));
    }

    public function show(PromoCode $promo_code)
    {
        return response()->json($promo_code);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:promo_codes,code'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'metadata' => ['nullable', 'array'],
            'tnc' => ['nullable', 'string'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_purchase' => ['nullable', 'integer', 'min:0'],
        ]);

        $promo = PromoCode::create($validated);

        return response()->json($promo, 201);
    }

    public function update(Request $request, PromoCode $promo_code)
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'string', 'max:100', Rule::unique('promo_codes', 'code')->ignore($promo_code->id)],
            'discount_type' => ['sometimes', Rule::in(['percent', 'fixed'])],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'metadata' => ['nullable', 'array'],
            'tnc' => ['nullable', 'string'],
            'min_purchase' => ['nullable', 'numeric', 'min:0'],
            'max_purchase' => ['nullable', 'integer', 'min:0'],
        ]);

        $promo_code->update($validated);

        return response()->json($promo_code);
    }

    public function destroy(PromoCode $promo_code)
    {
        $promo_code->delete();
        return response()->json(['status' => 'ok']);
    }
}
