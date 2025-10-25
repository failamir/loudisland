<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->text('tnc')->nullable()->after('metadata');
            $table->decimal('min_purchase', 12, 2)->nullable()->after('tnc');
            $table->unsignedInteger('max_purchase')->nullable()->after('min_purchase');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['tnc', 'min_purchase', 'max_purchase']);
        });
    }
};
