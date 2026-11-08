<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Shop;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $threads = Thread::query()
            ->with(['shop:id,name,city', 'messages' => fn ($query) => $query->latest('id')->limit(1)])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => [
                'threads' => $threads->map(function (Thread $thread) {
                    $last = $thread->messages->first();

                    return [
                        'id' => $thread->id,
                        'subject' => $thread->subject,
                        'shop' => [
                            'id' => $thread->shop->id,
                            'name' => $thread->shop->name,
                            'city' => $thread->shop->city,
                        ],
                        'last_message' => $last?->body,
                        'last_sender' => $last?->sender_type,
                        'last_message_at' => $thread->last_message_at?->toIso8601String(),
                        'unread_count' => $thread->unread_count,
                    ];
                }),
                'unread_total' => $threads->sum('unread_count'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'message' => ['required', 'string', 'max:2000'],
            'subject' => ['nullable', 'string', 'max:120'],
        ], [
            'shop_id.required' => 'Pilih toko tujuan pemesanan.',
            'shop_id.exists' => 'Toko tidak ditemukan.',
            'message.required' => 'Tulis pesan pemesanan terlebih dahulu.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
        ]);

        $shop = Shop::query()->active()->findOrFail($payload['shop_id']);

        $thread = Thread::query()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'shop_id' => $shop->id,
            ],
            [
                'business_id' => $this->businessId($request->user()),
                'subject' => $payload['subject'] ?? 'Pemesanan ke '.$shop->name,
                'unread_count' => 0,
            ],
        );

        Message::query()->create([
            'thread_id' => $thread->id,
            'sender_type' => 'buyer',
            'sender_name' => $request->user()->name,
            'body' => $payload['message'],
            'read_at' => now(),
        ]);

        $thread->update([
            'business_id' => $thread->business_id ?? $this->businessId($request->user()),
            'last_message_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'thread' => [
                    'id' => $thread->id,
                    'subject' => $thread->subject,
                ],
            ],
        ], 201);
    }

    public function messages(Request $request, Thread $thread): JsonResponse
    {
        abort_unless($thread->user_id === $request->user()->id, 404);

        Message::query()
            ->where('thread_id', $thread->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $thread->update(['unread_count' => 0]);

        $messages = $thread->messages()
            ->get()
            ->map(fn (Message $message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_name,
                'body' => $message->body,
                'read_at' => $message->read_at?->toIso8601String(),
                'created_at' => $message->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'data' => [
                'thread' => [
                    'id' => $thread->id,
                    'subject' => $thread->subject,
                    'shop' => [
                        'id' => $thread->shop->id,
                        'name' => $thread->shop->name,
                        'city' => $thread->shop->city,
                    ],
                ],
                'messages' => $messages,
            ],
        ]);
    }

    public function reply(Request $request, Thread $thread): JsonResponse
    {
        abort_unless($thread->user_id === $request->user()->id, 404);

        $payload = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Tulis pesan terlebih dahulu.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
        ]);

        $message = Message::query()->create([
            'thread_id' => $thread->id,
            'sender_type' => 'buyer',
            'sender_name' => $request->user()->name,
            'body' => $payload['message'],
            'read_at' => now(),
        ]);

        $thread->update(['last_message_at' => now()]);

        return response()->json([
            'data' => [
                'message' => [
                    'id' => $message->id,
                    'sender_type' => $message->sender_type,
                    'sender_name' => $message->sender_name,
                    'body' => $message->body,
                    'read_at' => $message->read_at?->toIso8601String(),
                    'created_at' => $message->created_at?->toIso8601String(),
                ],
            ],
        ], 201);
    }
}
