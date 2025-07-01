<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('award_date');
            $table->integer('points_earned');
            $table->decimal('cash_amount', 8, 2)->default(10.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['award_date']); // Only one winner per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_awards');
    }
};
