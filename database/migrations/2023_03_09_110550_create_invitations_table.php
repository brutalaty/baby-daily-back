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
        $status = config('invitations.status.unaccepted');

        Schema::create('invitations', function (Blueprint $table) use ($status) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('relation');
            $table->string('status')->default($status);
            $table->date('expiration');
            $table->unsignedInteger('family_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
