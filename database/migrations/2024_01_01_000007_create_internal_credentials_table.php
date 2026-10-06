<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internal_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->string('username');
            $table->string('secret_value');
            $table->string('environment');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_credentials');
    }
};