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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // For public access, e.g. /files/{uuid}
            $table->string('file_name')->unique(); // e.g. image.jpg
            $table->string('file_path');      // e.g. uploads/image.jpg
            $table->string('extension'); // e.g. jpg
            $table->integer('seq_no')->nullable();
            $table->string('mime_type')->nullable();
            $table->float('file_size')->nullable();
            $table->float('height')->nullable();
            $table->float('width')->nullable();
            $table->json('meta')->nullable(); // For any additional metadata like exif, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
