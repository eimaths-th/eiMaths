<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();

            $table->string('parent_name');
            $table->string('phone');
            $table->string('line_id')->nullable();
            $table->string('email')->nullable();
            $table->string('preferred_contact_time')->nullable();

            $table->string('student_name');
            $table->string('student_grade');
            $table->string('student_school')->nullable();
            $table->unsignedTinyInteger('student_age')->nullable();

            $table->text('pain_point')->nullable();
            $table->string('interest')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('fbclid')->nullable();
            $table->string('gclid')->nullable();
            $table->string('ttclid')->nullable();
            $table->text('referrer')->nullable();
            $table->text('landing_page')->nullable();
            $table->string('first_touch_source')->nullable();
            $table->string('last_touch_source')->nullable();

            $table->string('created_from')->default('lead_registration_website');
            $table->string('lead_status')->default('new_lead');
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('trial_scheduled_at')->nullable();
            $table->timestamp('trial_completed_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();

            $table->json('assessment_answers')->nullable();
            $table->string('assessment_level')->nullable();
            $table->text('assessment_note')->nullable();
            $table->json('answers')->nullable();
            $table->integer('test_score')->nullable();
            $table->integer('total_questions')->nullable();
            $table->json('weak_topics')->nullable();
            $table->integer('test_duration_seconds')->nullable();

            $table->foreignId('duplicate_of_lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->timestamp('duplicate_checked_at')->nullable();

            $table->timestamps();

            $table->index(['phone', 'created_at']);
            $table->index('lead_status');
            $table->index('utm_source');
            $table->index('utm_campaign');
            $table->index('assigned_staff_id');
            $table->index('next_follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_leads');
    }
};
