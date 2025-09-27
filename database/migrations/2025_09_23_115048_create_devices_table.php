<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id('device_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->enum('device_type', ['web', 'mobile', 'tablet', 'smart_tv'])->default('web');
            $table->string('device_name');
            $table->timestamp('last_active')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['user_id', 'is_active']);
            $table->index('device_type');
            $table->index('last_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
}