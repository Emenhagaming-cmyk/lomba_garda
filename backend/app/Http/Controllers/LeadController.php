<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lead::where('business_id', $this->businessId($request->user()));

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        $leads = $query->latest()->get();

        return response()->json([
            'data' => [
                'leads' => $leads->map(fn (Lead $lead) => $this->serialize($lead)),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:100'],
            'stage' => ['sometimes', 'string', Rule::in(Lead::STAGES)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'next_follow_up_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $lead = new Lead($validated);
        $lead->business_id = $this->businessId($request->user());
        $lead->stage = $validated['stage'] ?? 'new';
        $lead->save();

        return response()->json(['data' => ['lead' => $this->serialize($lead)]], 201);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        abort_unless($lead->business_id === $this->businessId($request->user()), 404);

        $validated = $request->validate([
            'stage' => ['sometimes', 'string', Rule::in(Lead::STAGES)],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'next_follow_up_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $lead->fill($validated);

        if (($validated['stage'] ?? null) === 'converted' && $lead->converted_customer_id === null) {
            $customer = Customer::create([
                'business_id' => $lead->business_id,
                'name' => $lead->name,
                'phone' => $lead->phone,
                'email' => $lead->email,
                'notes' => 'Dikonversi dari lead '.$lead->id,
            ]);

            $lead->converted_customer_id = $customer->id;
        }

        $lead->save();

        return response()->json(['data' => ['lead' => $this->serialize($lead->fresh())]]);
    }

    private function serialize(Lead $lead): array
    {
        return [
            'id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'source' => $lead->source,
            'stage' => $lead->stage,
            'notes' => $lead->notes,
            'next_follow_up_at' => $lead->next_follow_up_at?->toIso8601String(),
            'converted_customer_id' => $lead->converted_customer_id,
            'created_at' => $lead->created_at->toIso8601String(),
        ];
    }
}
