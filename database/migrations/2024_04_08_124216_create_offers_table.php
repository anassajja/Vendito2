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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 8, 2); //8 digits and 2 decimals
            $table->string('image')->nullable();
            $table->string('category')->default('others');
            $table->enum('status', ['accepted', 'inaccepted'])->default('inaccepted');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); //foreign key
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
