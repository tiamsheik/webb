<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id('rating_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('content_id')->constrained('content', 'content_id');
            $table->integer('rating')->unsigned()->checkBetween([1, 5]); 
            $table->text('review')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'content_id']);
            $table->index('content_id');
            $table->index('rating');
            $table->index('is_approved');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ratings');
    }
}