<?php

use illuminate\database\migrations\migration;
use illuminate\database\schema\blueprint;
use illuminate\support\facades\schema;

return new class extends migration
{
    /**
     * run the migrations.
     */
    public function up(): void
    {
        schema::create('blogs', function (blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->longtext('content');
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * reverse the migrations.
     */
    public function down(): void
    {
        schema::dropifexists('blogs');
    }
};
