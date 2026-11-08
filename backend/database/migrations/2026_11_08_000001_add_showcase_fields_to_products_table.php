<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->boolean('is_showcased')->default(false)->after('is_active');
            $table->index('is_showcased');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_showcased']);
            $table->dropColumn(['description', 'is_showcased']);
        });
    }
};
