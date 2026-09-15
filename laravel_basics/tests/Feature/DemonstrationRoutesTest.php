<?php

namespace Tests\Feature;

use Tests\TestCase;

class DemonstrationRoutesTest extends TestCase
{
    /**
     * Test GET / (home route) returns 200 and renders controller data passed via compact().
     */
    public function test_home_route_renders_successfully(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Laravel Basics - Week 3 Day 2');
        $response->assertSee('Routing Architecture');
        $response->assertSee('Blade Templating Engine');
    }

    /**
     * Test GET /about with default parameter.
     */
    public function test_about_default_topic(): void
    {
        $response = $this->get(route('about'));

        $response->assertStatus(200);
        $response->assertSee('About - Internship');
        $response->assertSee('Cynaris Web Development Internship');
    }

    /**
     * Test GET /about/{topic} with a specific parameter.
     */
    public function test_about_with_route_parameter(): void
    {
        $response = $this->get(route('about', ['topic' => 'mvc']));

        $response->assertStatus(200);
        $response->assertSee('About - Mvc');
        $response->assertSee('Model-View-Controller');
    }

    /**
     * Test GET /form renders the form and department options.
     */
    public function test_form_index_renders_successfully(): void
    {
        $response = $this->get(route('form.index'));

        $response->assertStatus(200);
        $response->assertSee('Contact & Feedback Demonstration');
        $response->assertSee('Internship Coordinator');
        $response->assertSee('Choose a Department');
    }

    /**
     * Test POST /form with valid inputs redirects back with success message.
     */
    public function test_form_submit_with_valid_data(): void
    {
        $payload = [
            'name' => 'Alice Intern',
            'email' => 'alice@example.com',
            'department' => 'internship',
            'message' => 'This is a test message for week 3 day 2 evaluation.',
        ];

        $response = $this->post(route('form.submit'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $response->assertSessionHas('submitted_data');
    }

    /**
     * Test POST /form validation failure.
     */
    public function test_form_submit_validation_failure(): void
    {
        $response = $this->post(route('form.submit'), [
            'name' => '',
            'email' => 'invalid-email',
            'department' => 'unknown-dept',
            'message' => 'hi',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'email', 'department', 'message']);
    }

    /**
     * Test POST /subscribe with valid email.
     */
    public function test_newsletter_subscribe_success(): void
    {
        $response = $this->post(route('newsletter.subscribe'), [
            'subscriber_email' => 'intern@cynaris.com',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('newsletter_success');
    }

    /**
     * Test POST /subscribe with invalid email.
     */
    public function test_newsletter_subscribe_validation_failure(): void
    {
        $response = $this->post(route('newsletter.subscribe'), [
            'subscriber_email' => 'not-an-email',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['subscriber_email']);
    }
}
