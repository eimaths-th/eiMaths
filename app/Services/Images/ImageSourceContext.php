<?php

namespace App\Services\Images;

final class ImageSourceContext
{
    public const IMAGE_STORE = 'image_store';
    public const IMAGE_STORE_AVATAR = 'image_store_avatar';
    public const IMAGE_STORE_STATIC = 'image_store_static';
    public const MEDIA_MANAGER = 'media_manager';
    public const STANDALONE_IMAGE_UPLOAD = 'standalone_image_upload';
    public const BLOG_THUMBNAIL = 'blog_thumbnail';
    public const COURSE_IMAGE = 'course_image';
    public const COURSE_PAYMENT_SLIP = 'course_payment_slip';
    public const BANK_PAYMENT_IMAGE = 'bank_payment_image';
    public const QUESTION_IMAGE = 'question_image';
    public const QUESTION_EXPLANATION_IMAGE = 'question_explanation_image';
    public const QUESTION_IMAGE_OPTION = 'question_image_option';
    public const QUIZ_QUESTION_IMAGE = 'quiz_question_image';
    public const QUIZ_EXPLANATION_IMAGE = 'quiz_explanation_image';
    public const QUIZ_IMAGE_OPTION = 'quiz_image_option';
    public const STUDENT_HISTORY_IMAGE = 'student_history_image';
    public const API_STUDENT_HISTORY_IMAGE = 'api_student_history_image';
    public const PARENT_FEEDBACK_IMAGE = 'parent_feedback_image';
    public const PROMOTION_IMAGE = 'promotion_image';
    public const AFFILIATE_CONTENT_IMAGE = 'affiliate_content_image';

    private function __construct()
    {
    }
}
