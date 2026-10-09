<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_books', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('title', 200);
            $t->string('description', 500)->nullable();
            $t->string('accent', 7)->default('#F77A26');
            $t->unsignedBigInteger('published_version_id')->nullable();
            $t->timestamps();
        });
        Schema::create('ebook_catalog', function (Blueprint $t) {
            $t->foreignId('book_id')->constrained('ebook_books')->cascadeOnDelete();
            // Legacy bookuses use an existing catalog. No per-student ebook assignment.
            $t->unsignedBigInteger('bookuse_id');
            $t->primary(['book_id', 'bookuse_id']);
            $t->index('bookuse_id');
        });
        Schema::create('ebook_versions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained('ebook_books')->cascadeOnDelete();
            $t->unsignedInteger('number');
            $t->string('status', 20)->default('queued');
            $t->string('source_path');
            $t->unsignedInteger('page_count')->default(0);
            $t->text('error')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->unique(['book_id', 'number']);
        });
        Schema::create('ebook_pages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('version_id')->constrained('ebook_versions')->cascadeOnDelete();
            $t->unsignedInteger('number');
            $t->string('image_path');
            $t->unsignedInteger('width');
            $t->unsignedInteger('height');
            $t->unique(['version_id', 'number']);
        });
        Schema::create('ebook_sessions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('student_id')->unique();
            $t->string('device_id', 64);
            $t->string('device_label', 100);
            $t->char('token_hash', 64)->unique();
            $t->dateTime('expires_at');
            $t->dateTime('absolute_expires_at');
            $t->timestamps();
        });
        Schema::create('ebook_work', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('student_id');
            $t->foreignId('page_id')->constrained('ebook_pages');
            $t->json('document');
            $t->unsignedInteger('revision')->default(0);
            $t->uuid('last_mutation_id')->nullable();
            $t->timestamps();
            $t->unique(['student_id', 'page_id']);
        });
        Schema::create('ebook_events', function (Blueprint $t) {
            $t->id();
            $t->uuid('event_id');
            $t->unsignedBigInteger('student_id');
            $t->uuid('session_id')->nullable();
            $t->string('device_id', 64)->nullable();
            $t->unsignedBigInteger('book_id')->nullable();
            $t->unsignedBigInteger('page_id')->nullable();
            $t->string('action', 60);
            $t->string('source', 10)->default('server');
            $t->json('metadata')->nullable();
            $t->dateTime('client_at')->nullable();
            $t->dateTime('received_at');
            $t->unique(['student_id', 'event_id']);
            $t->index(['student_id', 'received_at']);
            $t->index(['book_id', 'received_at']);
        });
    }

    public function down(): void
    {
        foreach (['ebook_events', 'ebook_work', 'ebook_sessions', 'ebook_pages', 'ebook_versions', 'ebook_catalog', 'ebook_books'] as $name) {
            Schema::dropIfExists($name);
        }
    }
};
