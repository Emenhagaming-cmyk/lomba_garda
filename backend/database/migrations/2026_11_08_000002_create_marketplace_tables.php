<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('open_hours')->default('08.00 - 21.00');
            $table->string('status')->default('open');
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->decimal('distance_km', 4, 1)->default(0);
            $table->unsignedTinyInteger('map_x')->default(50);
            $table->unsignedTinyInteger('map_y')->default(50);
            $table->boolean('supports_shipping')->default(true);
            $table->boolean('supports_cod')->default(false);
            $table->date('reopens_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['city', 'status']);
        });

        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            $table->string('unit')->default('pcs');
            $table->timestamps();
        });

        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'shop_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->string('sender_type')->default('buyer');
            $table->string('sender_name')->nullable();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('threads');
        Schema::dropIfExists('shop_items');
        Schema::dropIfExists('shops');
    }
};
