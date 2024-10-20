<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magic_links', function (Blueprint $table) {
            $table->uuid('token');
            $table->foreignUuid('user')
                ->constrained(table: 'users', indexName: 'id')
                ->cascadeOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_links');
    }
};
