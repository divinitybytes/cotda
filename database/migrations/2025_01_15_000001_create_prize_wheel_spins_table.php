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
        Schema::create('prize_wheel_spins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('spin_date');
            $table->string('prize_type'); // 'none', 'points', 'spot_bonus'
            $table->string('prize_name');
            $table->integer('points_awarded')->default(0);
            $table->decimal('cash_awarded', 8, 2)->default(0.00);
            $table->foreignId('spot_bonus_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['user_id', 'spin_date']); // One spin per user per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_wheel_spins');
    }
}; 