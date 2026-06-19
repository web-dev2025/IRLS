<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('phrase');
            $table->text('translation')->nullable();
            $table->text('comment')->nullable();
            // Coordinates as percentages (0..100) so they survive image rescaling
            $table->float('x');
            $table->float('y');
            $table->float('width')->default(0);
            $table->float('height')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
