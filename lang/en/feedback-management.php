<?php

return [
    'list' => [
        'page_title' => 'Feedback List',
        'new' => 'New Feedback',
        'card_title' => 'Feedback List',
        'collapse' => 'Collapse',
        'table' => [
            'number' => 'No.',
            'title' => 'Title',
            'category' => 'Category',
            'status' => 'Status',
            'priority' => 'Priority',
            'action' => 'Action',
        ],
        'view' => 'View',
    ],
    'create' => [
        'page_title' => 'Create Feedback',
        'card_title' => 'Create Feedback',
        'collapse' => 'Collapse',
        'fields' => [
            'title' => 'Title:',
            'description' => 'Description:',
            'category' => 'Category:',
            'priority' => 'Priority:',
        ],
        'categories' => [
            'bug' => 'Bug',
            'suggestion' => 'Suggestion',
            'other' => 'Other',
        ],
        'priorities' => [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ],
        'submit' => 'Submit Feedback',
    ],
    'detail' => [
        'page_title' => 'Feedback View Details',
        'card_title' => 'View Feedback',
        'collapse' => 'Collapse',
        'fields' => [
            'description' => 'Description:',
            'category' => 'Category:',
            'status' => 'Status:',
            'priority' => 'Priority:',
            'submitted_by' => 'Submitted by:',
            'admin_comments' => 'Admin Comments:',
            'attachment' => 'Attachment:',
            'update_status' => 'Update Status:',
        ],
        'anonymous' => 'Anonymous',
        'none' => 'None',
        'view_attachment' => 'View Attachment',
        'status_options' => [
            'new' => 'New',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
        ],
        'update' => 'Update Feedback',
        'back' => 'Back to Feedback List',
    ],
    'categories' => [
        'bug' => 'Bug',
        'suggestion' => 'Suggestion',
        'other' => 'Other',
    ],
    'status' => [
        'new' => 'New',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
    ],
    'priorities' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
    ],
    'messages' => [
        'submitted' => 'Feedback submitted successfully.',
        'updated' => 'Feedback updated successfully.',
    ],
];
