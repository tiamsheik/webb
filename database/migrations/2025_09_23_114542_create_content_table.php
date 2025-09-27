<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTable extends Migration
{
    public function up()
    {
        Schema::create('content', function (Blueprint $table) {
            $table->id('content_id');
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['movie', 'tv_show', 'documentary', 'original']);
            $table->integer('duration'); // in minutes
            $table->integer('release_year');
            $table->enum('rating', ['G', 'PG', 'PG-13', 'R', 'NC-17'])->default('PG');
            $table->string('thumbnail_url');
            $table->string('video_url');
            $table->integer('file_size'); // in bytes
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users', 'user_id');
            $table->timestamps();
            $table->index('category');
            $table->index('is_featured');
            $table->index('is_active');
            $table->index('release_year');
            $table->index(['title']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('content');
    }
}