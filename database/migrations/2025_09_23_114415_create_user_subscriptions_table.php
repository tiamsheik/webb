<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id('subscription_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('plan_id')->constrained('subscription_plans', 'plan_id');
            $table->enum('status', ['active', 'canceled', 'expired'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('auto_renew')->default(true);
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('end_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
}