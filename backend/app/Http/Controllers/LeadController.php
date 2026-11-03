<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

    public function followUps(Request $request): JsonResponse
    {
        $businessId = $this->businessId($request->user());
        $today = Carbon::today();
        $weekAhead = Carbon::today()->endOfDay()->addDays(7);

        $leads = Lead::where('business_id', $businessId)
            ->whereNotNull('next_follow_up_at')
            ->where('stage', '!=', 'converted')
            ->orderBy('next_follow_up_at')
            ->get();

        $overdue = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->lt($today))->values();
        $dueToday = $leads->filter(fn (Lead $lead) => $lead->next_follow_up_at->isSameDay($today))->values();
        $upcoming = $leads
            ->filter(fn (Lead $lead) => $lead->next_follow_up_at->gt($today->copy()->endOfDay()) && $lead->next_follow_up_at->lte($weekAhead))
            ->values();

        $customers = Customer::where('business_id', $businessId)
            ->withCount('sales')
            ->withSum('sales', 'total')
            ->withMax('sales', 'created_at')
            ->get()
            ->map(function (Customer $customer): ?array {
                $last = $customer->sales_max_created_at;

                if ($last === null) {
                    return null;
                }

                $spending = (int) ($customer->sales_sum_total ?? 0);
                $orders = (int) ($customer->sales_count ?? 0);
                $days = Carbon::parse($last)->diffInDays();
                $segment = Customer::segmentFor($spending, $orders, $last);

                if ($days <= Customer::AT_RISK_DAYS) {
                    return null;
                }

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'segment' => $segment,
                    'days_since' => (int) $days,
                    'total_spending' => $spending,
                    'order_count' => $orders,
                    'last_purchase' => Carbon::parse($last)->toIso8601String(),
                ];
            })
            ->filter(fn (?array $customer) => $customer !== null)
            ->sortByDesc('days_since')
            ->values();

        return response()->json([
            'data' => [
                'leads' => [
                    'overdue' => $overdue->map(fn (Lead $lead) => $this->serialize($lead)),
                    'today' => $dueToday->map(fn (Lead $lead) => $this->serialize($lead)),
                    'upcoming' => $upcoming->map(fn (Lead $lead) => $this->serialize($lead)),
                ],
                'customers' => $customers,
                'counts' => [
                    'overdue' => $overdue->count(),
                    'today' => $dueToday->count(),
                    'upcoming' => $upcoming->count(),
                    'customers' => $customers->count(),
                ],
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
