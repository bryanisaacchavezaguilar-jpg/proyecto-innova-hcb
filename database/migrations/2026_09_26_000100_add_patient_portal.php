<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->unique()->constrained()->nullOnDelete();
        });

        Schema::create('patient_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->string('document_number', 50)->index();
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_access_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('patient_id');
        });
    }
};