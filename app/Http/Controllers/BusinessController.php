<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $business = $request->user()->businesses()->first();

        return response()->json(['data' => ['business' => $business?->only([
            'id', 'name', 'type', 'currency', 'payment_methods', 'phone', 'email', 'address',
        ])]]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['retail', 'fnb', 'fashion', 'reseller', 'service', 'production'])],
            'currency' => ['sometimes', 'string', 'max:8'],
            'payment_methods' => ['sometimes', 'array'],
            'payment_methods.*' => ['string', 'max:50'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $business = $request->user()->businesses()->first() ?? new Business;

        $business->name = $validated['name'];
        $business->type = $validated['type'];
        $business->currency = $validated['currency'] ?? 'IDR';
        $business->payment_methods = $validated['payment_methods'] ?? ['cash'];
        $business->phone = $validated['phone'] ?? null;
        $business->email = $validated['email'] ?? null;
        $business->address = $validated['address'] ?? null;

        if (! $business->exists) {
            $business->user_id = $request->user()->id;
        }

        $business->save();

        return response()->json(['data' => ['business' => $business]], 201);
    }
}
