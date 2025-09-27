<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['system', 'new_content', 'subscription', 'recommendation'])->default('system');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}