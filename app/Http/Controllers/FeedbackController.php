<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $feedbacks = Feedback::with('user', 'admin')->get();
        return view('feedback.index', compact('feedbacks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('feedback.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required',
            'category' => 'required|in:Bug,Suggestion,Other',
            'priority' => 'required|in:Low,Medium,High',
            // 'attachment' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
        ]);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        Feedback::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'attachment_path' => $attachmentPath,
            'status' => 'New',
        ]);

        return redirect()->route('feedback.index')->with('success', 'Feedback submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback)
    {
        return view('feedback.show', compact('feedback'));
    }

    public function updateStatus(Request $request, Feedback $feedback)
    {
        $feedback->update([
            'status' => $request->status,
            'admin_resolved' => Auth::id(),
            'resolved_at' => now(),
            'admin_comments' => $request->admin_comments,
        ]);

        return redirect()->route('feedback.index')->with('success', 'Feedback updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
