<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('type')->default('general'); // general, announcement, reminder
            $table->boolean('is_pinned')->default(false);

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_messages');
    }
};
