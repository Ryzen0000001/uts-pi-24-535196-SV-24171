<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('motor', 100);
            $table->date('serviced_at');
            $table->unsignedInteger('odometer_km');
            $table->string('service_type', 100);
            $table->unsignedInteger('cost');
            $table->string('workshop', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'serviced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};
