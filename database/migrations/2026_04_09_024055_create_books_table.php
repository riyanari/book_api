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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
            $table->string('source_id')->nullable();
            $table->string('isbn')->nullable()->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('published_year')->nullable();
            $table->text('description')->nullable();
            $table->text('cover_url')->nullable();
            $table->string('normalized_title')->nullable()->index();
            $table->string('normalized_author')->nullable()->index();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
