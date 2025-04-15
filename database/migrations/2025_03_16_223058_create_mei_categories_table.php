<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mei_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user')
                ->constrained(table: 'users', indexName: 'id')
                ->cascadeOnDelete();
            $table->string('type', 20);
            $table->date('creation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mei_categories');
    }
};
