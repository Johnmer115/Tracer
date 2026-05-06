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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('school_year_code')->nullable();
            $table->string('branch_id')->nullable();
            $table->string('level')->nullable();
            $table->string('department')->nullable();
            $table->string('organization')->nullable();
            $table->string('type_of_activity')->nullable();
            $table->string('title')->nullable();
            $table->date('date_of_activity')->nullable();
            $table->string('time_of_activity')->nullable();
            $table->string('participants')->nullable();
            $table->string('description')->nullable();
            $table->string('objectives')->nullable();
            $table->string('mode_of_conduct')->nullable();
            $table->string('venue')->nullable();
            $table->string('public_poster')->nullable();
            $table->string('event_type')->nullable();
            $table->string('funds')->nullable();
            $table->string('source')->nullable();
            $table->string('canteen')->nullable();
            $table->string('procurement')->nullable();
            $table->string('received_by')->nullable();
            $table->string('encoded_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
