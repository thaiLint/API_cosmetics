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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name_brand');
            $table->text('description')->nullable();
            $table->string('image_brand')->nullable();

           $table->unsignedBigInteger('category_id')->nullable();
$table->foreign('category_id')
      ->references('id')
      ->on('categories')
      ->onDelete('set null');


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
