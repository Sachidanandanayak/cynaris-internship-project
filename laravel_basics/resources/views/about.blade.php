@extends('layouts.app')

@section('title', $title)

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.85rem; font-weight: 800; margin-bottom: 0.5rem;">{{ $title }}</h1>
        <p style="color: var(--text-muted);">
            Demonstration of Laravel's route parameter capturing via <code>routes/web.php</code> and controller injection into views.
        </p>
    </div>

    <div class="card">
        <h2>Select an Internship Topic</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1rem;">
            Click a topic below to invoke the parameterized route <code>/about/{topic?}</code>:
        </p>
        <div class="topic-pills">
            @foreach ($topics as $key => $desc)
                <a href="{{ route('about', $key) }}" class="pill {{ $topicKey === $key ? 'active' : '' }}">
                    {{ ucfirst($key) }}
                </a>
            @endforeach
            <a href="{{ url('/about/custom-topic') }}" class="pill {{ $topicKey === 'custom-topic' ? 'active' : '' }}">
                Custom Topic
            </a>
        </div>

        <div style="background-color: #f8fafc; border-left: 4px solid var(--primary); padding: 1.25rem; border-radius: 4px;">
            <h3 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: var(--primary);">
                Active Topic: {{ $formattedTopic }}
            </h3>
            <p style="font-size: 1rem; color: var(--text-main);">
                {{ $topicDetails }}
            </p>
        </div>
    </div>

    <div class="card">
        <h2>How Route Parameters & compact() Work</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 0.5rem 0;">Component</th>
                    <th style="padding: 0.5rem 0;">Code Sample</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 0; font-weight: 600;">Route Definition</td>
                    <td style="padding: 0.75rem 0;"><code>Route::get('/about/{topic?}', [HomeController::class, 'about'])->name('about');</code></td>
                </tr>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 0.75rem 0; font-weight: 600;">Controller Method</td>
                    <td style="padding: 0.75rem 0;"><code>public function about(?string $topic = 'internship')</code></td>
                </tr>
                <tr>
                    <td style="padding: 0.75rem 0; font-weight: 600;">View Return</td>
                    <td style="padding: 0.75rem 0;"><code>return view('about', compact('title', 'formattedTopic', 'topicKey', 'topicDetails', 'topics'));</code></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
