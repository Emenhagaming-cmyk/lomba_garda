<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category')->default('Tips');
            $table->string('author')->default('Tim TokoKu');
            $table->string('excerpt', 400);
            $table->longText('body');
            $table->unsignedInteger('read_minutes')->default(3);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
