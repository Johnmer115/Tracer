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
        Schema::create('sarf_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // A0-A10
            $table->string('file_path');
            $table->string('original_filename');
            $table->timestamps();

            $table->foreignId('activity_id')
                ->constrained('activities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sarf_documents');
    }
};
