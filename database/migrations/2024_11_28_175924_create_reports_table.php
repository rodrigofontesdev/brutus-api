<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user')
                ->constrained(table: 'users', indexName: 'id')
                ->cascadeOnDelete();
            $table->integer('trade_with_invoice')->nullable();
            $table->integer('trade_without_invoice')->nullable();
            $table->integer('industry_with_invoice')->nullable();
            $table->integer('industry_without_invoice')->nullable();
            $table->integer('services_with_invoice')->nullable();
            $table->integer('services_without_invoice')->nullable();
            $table->date('period');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
