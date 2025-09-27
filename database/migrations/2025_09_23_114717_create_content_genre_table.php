<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentGenreTable extends Migration
{
    public function up()
    {
        Schema::create('content_genre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained('content', 'content_id')->onDelete('cascade');
            $table->foreignId('genre_id')->constrained('genres', 'genre_id')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['content_id', 'genre_id']);
            $table->index('content_id');
            $table->index('genre_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('content_genre');
    }
}