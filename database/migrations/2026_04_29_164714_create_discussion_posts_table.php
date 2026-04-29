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
        Schema::create('discussion_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');//post, trivia, poll, reply
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('author_ip');
            $table->unsignedBigInteger('poll_id')->nullable();
            $table->unsignedBigInteger('trivia_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discussion_posts');
    }
};
