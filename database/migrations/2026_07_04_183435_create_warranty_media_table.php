<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warranty_id')
                ->constrained('warranties')
                ->cascadeOnDelete();

            $table->enum('stage', ['reception', 'completion'])->index();

            $table->enum('type', ['image', 'video'])->index();

            $table->string('file_path');

            $table->string('original_name')->nullable();

            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('file_size')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_media');
    }
};