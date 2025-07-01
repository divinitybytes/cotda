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
        Schema::create('point_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->integer('points'); // Can be positive (addition) or negative (deduction)
            $table->date('adjustment_date');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->enum('type', ['bonus', 'penalty', 'correction', 'other'])->default('other');
            $table->timestamps();
            
            $table->index(['user_id', 'adjustment_date']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_adjustments');
    }
};
