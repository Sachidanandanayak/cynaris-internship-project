<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormController extends Controller
{
    /**
     * Display the contact and feedback form.
     * Demonstrates compact() helper usage to pass department choices.
     */
    public function index(): View
    {
        $title = 'Contact & Feedback Demonstration';
        $departments = [
            'internship' => 'Internship Coordinator',
            'support' => 'Technical Support',
            'feedback' => 'Curriculum Feedback',
            'general' => 'General Inquiry',
        ];

        return view('form', compact('title', 'departments'));
    }

    /**
     * Handle the main contact form submission.
     * Demonstrates CSRF validation, input validation, and flash messaging.
     */
    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'email' => 'required|email',
            'department' => 'required|string|in:internship,support,feedback,general',
            'message' => 'required|string|min:5|max:500',
        ]);

        return back()
            ->withInput()
            ->with('success', "Thank you, {$validated['name']}! Your message for {$validated['department']} was received successfully.")
            ->with('submitted_data', $validated);
    }

    /**
     * Handle a quick newsletter subscription submission.
     * Demonstrates a second POST route with validation and flash messaging.
     */
    public function subscribe(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subscriber_email' => 'required|email',
        ]);

        return back()
            ->with('newsletter_success', "Thank you! {$validated['subscriber_email']} has been subscribed to updates.");
    }
}
