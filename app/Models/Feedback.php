<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property string $category
 * @property string $status
 * @property string $priority
 * @property string $attachment_path
 * @property string $resolved_at
 * @property string $admin_comments
 * @property integer $admin_resolved
 * @property string $created_at
 * @property string $updated_at
 */
class Feedback extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'feedbacks';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        
        'title',
        'description',
        'image_feedback',
        'category',
        'status',
        'priority',
        'attachment_path',
        'resolved_at',
        'admin_comments',
        'admin_resolved',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_resolved');
    }
    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }
}
