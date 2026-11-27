<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->string('period', 20);
            $table->decimal('gross', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('net', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['seller_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payouts');
    }
};
