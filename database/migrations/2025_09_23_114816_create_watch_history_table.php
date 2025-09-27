<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatchHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('watch_history', function (Blueprint $table) {
            $table->id('watch_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('content_id')->constrained('content', 'content_id');
            $table->integer('progress_time')->default(0); // seconds watched
            $table->integer('total_duration'); // total content duration in seconds
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->timestamp('last_watched_at')->useCurrent();
            $table->timestamps();
            $table->unique(['user_id', 'content_id']);
            $table->index(['user_id', 'last_watched_at']);
            $table->index('content_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('watch_history');
    }
}