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
        Schema::create('product_feature_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_feature_id')->constrained()->onDelete('cascade');
            $table->string('locale'); // kh, en, zh, tw
            $table->string('feature_text');
            $table->timestamps();
            $table->unique(['product_feature_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_feature_translations');
    }
};
