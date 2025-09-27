<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id('plan_id');
            $table->string('name');
            $table->decimal('price_monthly', 8, 2);
            $table->decimal('price_yearly', 8, 2);
            $table->integer('max_screens');
            $table->enum('video_quality', ['SD', 'HD', '4K'])->default('HD');
            $table->text('description');
            $table->json('features');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}