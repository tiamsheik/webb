<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavoritesTable extends Migration
{
    public function up()
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id('favorite_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('content_id')->constrained('content', 'content_id');
            $table->timestamps();
            $table->unique(['user_id', 'content_id']);
            $table->index('user_id');
            $table->index('content_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('favorites');
    }
}