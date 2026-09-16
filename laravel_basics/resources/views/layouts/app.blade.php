<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Cynaris Internship Week 3 Day 2 - Laravel Basics Demonstration">
    <title>@yield('title', 'Laravel Basics') | Cynaris Internship</title>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success-bg: #ecfdf5;
            --success-border: #a7f3d0;
            --success-text: #065f46;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --error-text: #991b1b;
            --radius: 10px;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navbar */
        .site-header {
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 4rem;
        }

        .brand-link {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            align-items: center;
        }

        .nav-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
        }

        .nav-link:hover {
            color: var(--primary);
            background-color: #f1f5f9;
        }

        .nav-link.active {
            color: var(--primary);
            background-color: #eef2ff;
            font-weight: 600;
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 2.5rem 0;
        }

        /* Hero */
        .hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
        }

        .hero h1 {
            font-size: 2rem;
            margin-bottom: 0.75rem;
            font-weight: 800;
        }

        .hero p {
            font-size: 1.1rem;
            opacity: 0.95;
            max-width: 650px;
        }

        /* Cards & Grid */
        .card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .card h2 {
            font-size: 1.35rem;
            margin-bottom: 1rem;
            color: var(--text-main);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }

        .feature-card {
            background-color: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
        }

        .feature-badge {
            display: inline-block;
            background-color: #eef2ff;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            margin-bottom: 0.5rem;
        }

        .feature-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .feature-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.65rem 1.25rem;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background-color: #f1f5f9;
        }

        .btn-danger {
            background-color: #dc2626;
            color: #ffffff;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        .btn-secondary {
            background-color: #64748b;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-sm {
            padding: 0.35rem 0.65rem;
            font-size: 0.825rem;
            border-radius: 4px;
        }

        /* Data Tables */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--surface);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.925rem;
        }

        .data-table th {
            background-color: #f8fafc;
            color: var(--text-muted);
            font-weight: 600;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover td {
            background-color: #f8fafc;
        }

        .badge-category {
            display: inline-block;
            background-color: #e0e7ff;
            color: #3730a3;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
        }

        .badge-price {
            font-weight: 700;
            color: #059669;
            font-size: 1rem;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            font-size: 0.95rem;
        }

        .alert-success {
            background-color: var(--success-bg);
            border-color: var(--success-border);
            color: var(--success-text);
        }

        .alert-error {
            background-color: var(--error-bg);
            border-color: var(--error-border);
            color: var(--error-text);
        }

        .alert ul {
            margin-left: 1.25rem;
            margin-top: 0.5rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            outline: none;
            font-family: inherit;
            transition: border-color 0.15s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            color: var(--error-text);
            font-size: 0.825rem;
            margin-top: 0.25rem;
        }

        /* Topic Tabs */
        .topic-pills {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .pill {
            text-decoration: none;
            font-size: 0.875rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            border: 1px solid var(--border);
            color: var(--text-muted);
            background: var(--surface);
            transition: all 0.15s;
        }

        .pill.active, .pill:hover {
            background-color: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }

        /* Footer */
        .site-footer {
            background-color: var(--surface);
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .newsletter-inline {
            display: flex;
            gap: 0.5rem;
        }

        .newsletter-inline input {
            padding: 0.45rem 0.75rem;
            font-size: 0.875rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            outline: none;
        }
    </style>
</head>
<body>
    <header class="site-header">
        @include('partials.navbar')
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <p><strong>Cynaris Internship</strong> &bull; Week 3 Day 2: Laravel Basics</p>
                <p style="font-size: 0.8rem; margin-top: 0.25rem;">Built with Laravel 13 &bull; PHP 8.5</p>
            </div>
            <div>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-inline">
                    @csrf
                    <input type="email" name="subscriber_email" placeholder="Quick subscribe email..." required value="{{ old('subscriber_email') }}">
                    <button type="submit" class="btn btn-primary" style="padding: 0.45rem 0.85rem; font-size: 0.875rem;">Subscribe</button>
                </form>
                @if (session('newsletter_success'))
                    <p style="color: var(--success-text); font-size: 0.8rem; margin-top: 0.4rem;">
                        {{ session('newsletter_success') }}
                    </p>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
