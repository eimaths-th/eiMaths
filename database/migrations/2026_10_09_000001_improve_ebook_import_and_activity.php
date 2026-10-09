<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebook_versions', function (Blueprint $t) {
            $t->string('original_filename')->nullable();
            $t->unsignedBigInteger('original_size')->nullable();
            $t->unsignedBigInteger('uploaded_by')->nullable();
            $t->string('uploaded_by_name')->nullable();
            $t->unsignedBigInteger('published_by')->nullable();
            $t->string('published_by_name')->nullable();
        });
        Schema::create('ebook_admin_events', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('actor_id')->nullable();
            $t->string('actor_name');
            $t->foreignId('book_id')->constrained('ebook_books');
            $t->foreignId('version_id')->constrained('ebook_versions');
            $t->string('action', 40);
            $t->json('metadata')->nullable();
            $t->dateTime('occurred_at'); // UTC
            $t->index(['book_id', 'occurred_at']);
        });
        Schema::create('ebook_session_history', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->unsignedBigInteger('student_id')->index();
            $t->string('device_id', 64);
            $t->string('device_label', 100);
            $t->dateTime('started_at'); // UTC; no bearer tokens retained
            $t->dateTime('ended_at')->nullable();
            $t->string('end_reason', 40)->nullable();
        });
        Schema::table('ebook_events', function (Blueprint $t) {
            $t->dateTime('occurred_at')->nullable()->index(); // UTC
            $t->uuid('received_session_id')->nullable();
            $t->string('session_origin', 20)->default('unknown');
        });
        DB::table('ebook_sessions')->orderBy('id')->each(function ($s) {
            DB::table('ebook_session_history')->insert([
                'id' => $s->id, 'student_id' => $s->student_id, 'device_id' => $s->device_id,
                'device_label' => $s->device_label,
                'started_at' => Carbon::parse($s->created_at, config('app.timezone'))->utc(),
            ]);
        });
        DB::table('ebook_events')->orderBy('id')->chunkById(500, function ($events) {
            foreach ($events as $event) {
                DB::table('ebook_events')->where('id', $event->id)->update([
                    'occurred_at' => $event->client_at
                        ? Carbon::parse($event->client_at, 'UTC')
                        : Carbon::parse($event->received_at, config('app.timezone'))->utc(),
                    'received_session_id' => $event->session_id,
                    'session_origin' => $event->source === 'server' ? 'server' : 'legacy',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('ebook_events', fn (Blueprint $t) => $t->dropColumn(['occurred_at', 'received_session_id', 'session_origin']));
        Schema::dropIfExists('ebook_session_history');
        Schema::dropIfExists('ebook_admin_events');
        Schema::table('ebook_versions', fn (Blueprint $t) => $t->dropColumn(['original_filename', 'original_size', 'uploaded_by', 'uploaded_by_name', 'published_by', 'published_by_name']));
    }
};
