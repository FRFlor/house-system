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
        Schema::create('chore_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chore_completion_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // Amount in cents
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chore_expenses');
    }
};
