<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('school_year_code')->nullable();

            // Location context — top of form
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->json('level')->nullable();                      // e.g. "Elementary", "Senior High School"
            $table->json('department')->nullable();                 // stored as JSON array

            // SARF Detail
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('objectives')->nullable();
            $table->string('type_of_activity')->nullable();         // Extra-Curricular / Co-Curricular
            $table->string('event_type')->nullable();               // Internal / External
            $table->string('activity_level')->nullable();           // Organization / Local / Interbranch / Off-Campus
            $table->string('participants_profile')->nullable();     // e.g. "All students, Faculty"
            $table->unsignedInteger('participants_count')->nullable(); // numeric head count
            $table->date('date_of_activity')->nullable();
            $table->string('time_of_activity')->nullable();
            $table->string('public_poster')->nullable();            // With / Without
            $table->string('mode_of_conduct')->nullable();          // Face to Face / Online / Hybrid
            $table->string('venue')->nullable();
            $table->string('venue_type')->nullable();               // On-Campus / Off-Campus
            $table->string('platform')->nullable();

            // Budget
            $table->string('funds')->nullable();                    // With Budget / ATC / No Fee
            $table->string('source')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('expected_collection', 12, 2)->nullable();
            $table->string('canteen')->nullable();                  // With / Without
            $table->string('procurement')->nullable();              // With / Without
            $table->text('late_submission_reason')->nullable();

            // Meta
            $table->unsignedBigInteger('received_by')->nullable();  // FK → users.id
            $table->unsignedBigInteger('encoded_by')->nullable();   // FK → users.id
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('received_by')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('encoded_by')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};