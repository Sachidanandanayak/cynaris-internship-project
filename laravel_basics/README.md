# Cynaris Web Development Internship — Week 3 Day 2: Laravel Basics

## Project Overview
This project contains the **Laravel Basics** implementation for Week 3 Day 2 of the Cynaris Internship. It demonstrates fundamental Laravel MVC architectural patterns, route handling, controller logic, Blade template inheritance, server-side form validation, and CSRF protection.

- **Framework Version:** Laravel Framework `13.32.0`
- **PHP Version:** PHP `8.5.10`
- **Composer Version:** Composer `2.10.3`
- **Environment:** Local Development (Windows / PHP CLI)

---

## Setup & Local Configuration

### 1. Prerequisites
Ensure PHP 8.2+ (tested on PHP 8.5.10) with the `fileinfo` extension enabled in `php.ini`, and Composer are installed.

### 2. Initial Setup Commands
```bash
# Navigate to the Laravel project directory
cd laravel_basics

# Install dependencies (if not already installed)
composer install

# Generate the application encryption key
php artisan key:generate

# Clear any cached configuration
php artisan config:clear
```

### 3. Environment Configuration (`.env`)
For lightweight local development without requiring external database services:
- `APP_ENV=local`
- `APP_DEBUG=true`
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`

> **Note:** The `.env` file is excluded from version control via `.gitignore` to safeguard secrets and environment-specific settings.

### 4. Running the Application Locally
Start the built-in development server:
```bash
php artisan serve
```
The application will be accessible at:
```
http://127.0.0.1:8000
```

---

## Demonstration Routes List

The project defines **exactly 5 demonstration routes** in `routes/web.php` (3 GET routes and 2 POST routes):

| HTTP Method | URI Pattern | Route Name | Controller Action | Description |
| :--- | :--- | :--- | :--- | :--- |
| **GET** | `/` | `home` | `HomeController@index` | Main landing page displaying internship overview and dynamically passed feature cards. |
| **GET** | `/about/{topic?}` | `about` | `HomeController@about` | Parameterized route demonstrating optional parameter capture (`{topic?}`) with defaults and topic switching. |
| **GET** | `/form` | `form.index` | `FormController@index` | Interactive feedback and contact form page with department options. |
| **POST** | `/form` | `form.submit` | `FormController@submit` | Handles contact form submissions with server-side validation and `@csrf` token protection. |
| **POST** | `/subscribe` | `newsletter.subscribe` | `FormController@subscribe` | Handles quick newsletter subscription POST requests with email validation. |

Verify the registered routes anytime using Artisan:
```bash
php artisan route:list
```

---

## Controllers

All route logic is organized within two controllers located in `app/Http/Controllers/`:

### 1. `HomeController` (`app/Http/Controllers/HomeController.php`)
- `index(): View`
  - Prepares page metadata, introductory text, and feature lists.
  - Passes data to `resources/views/home.blade.php` using the `compact()` helper.
- `about(?string $topic = 'internship'): View`
  - Accepts an optional `$topic` route parameter (default: `'internship'`).
  - Resolves topic details from an associative map.
  - Passes formatted topic and descriptive content to `resources/views/about.blade.php` using `compact()`.

### 2. `FormController` (`app/Http/Controllers/FormController.php`)
- `index(): View`
  - Prepares the department selections list.
  - Passes available departments to `resources/views/form.blade.php` using `compact()`.
- `submit(Request $request): RedirectResponse`
  - Enforces server-side validation:
    - `name`: `required|string|min:2|max:50`
    - `email`: `required|email`
    - `department`: `required|string|in:internship,support,feedback,general`
    - `message`: `required|string|min:5|max:500`
  - Redirects back with input (`withInput()`) and flash session status (`with('success', ...)`).
- `subscribe(Request $request): RedirectResponse`
  - Validates `subscriber_email`: `required|email`.
  - Redirects back with flash message (`with('newsletter_success', ...)`).

---

## Blade Templating Concepts Demonstrated

The template layer is modularized under `resources/views/`:

```
resources/views/
├── layouts/
│   └── app.blade.php       # Master layout with HTML skeleton, header, footer, CSS
├── partials/
│   └── navbar.blade.php    # Reusable navigation bar partial
├── home.blade.php          # Home view extending master layout
├── about.blade.php         # Parameterized about view
└── form.blade.php          # POST forms with validation & feedback
```

### Key Directives Demonstrated:
1. **`@extends('layouts.app')`**: Inherits the common HTML shell, CSS styles, header, and footer in all child views.
2. **`@yield('title', '...')` & `@yield('content')`**: Defines content placeholders inside `layouts/app.blade.php` that child views populate.
3. **`@section('title', ...)` & `@section('content') ... @endsection`**: Injects page-specific titles and main body content into the parent layout.
4. **`@include('partials.navbar')`**: Embeds the reusable navigation bar component into the header.
5. **`@csrf`**: Injects a hidden CSRF token input (`<input type="hidden" name="_token" value="...">`) into HTML forms to prevent Cross-Site Request Forgery.
6. **`@if`, `@error`, `@foreach`**: Conditional rendering for alert messages, field-level error messages, and iterating over controller collections.

---

## How Controller Data is Passed Using `compact()`

In PHP and Laravel, `compact()` takes string variable names and bundles them into an associative array where the variable names become the keys and their values become the array values.

### Example in `HomeController`:
```php
public function index(): View
{
    $title = 'Laravel Basics - Week 3 Day 2';
    $subtitle = 'Welcome to the Cynaris Internship Week 3 Day 2 Laravel Demonstration.';
    $frameworkFeatures = [ ... ];

    // compact('title', 'subtitle', 'frameworkFeatures') produces:
    // ['title' => $title, 'subtitle' => $subtitle, 'frameworkFeatures' => $frameworkFeatures]
    return view('home', compact('title', 'subtitle', 'frameworkFeatures'));
}
```

### Rendering Inside the Blade View (`resources/views/home.blade.php`):
```blade
<h1>{{ $title }}</h1>
<p>{{ $subtitle }}</p>

@foreach ($frameworkFeatures as $feature)
    <div class="feature-card">
        <h3>{{ $feature['title'] }}</h3>
        <p>{{ $feature['description'] }}</p>
    </div>
@endforeach
```

---

## Security & Best Practices

1. **CSRF Protection:** All POST forms include `@csrf`. Unauthenticated or forged POST requests receive HTTP 419 (Page Expired).
2. **XSS Prevention:** All user-supplied data (such as echoed submitted payloads) is rendered using Blade's default `{{ $data }}` syntax, which automatically escapes output via PHP's `htmlspecialchars()`.
3. **Environment Security:** Secrets and app keys are stored exclusively in `.env`, which is ignored by Git.

---

## Automated Verification & Testing

Run the test suite covering route responses, parameter handling, form validation, and session flashing:
```bash
php artisan test
```
All 10 tests (30 assertions) pass successfully:
- `GET /` returns 200 and displays controller data.
- `GET /about` returns 200 with default topic data.
- `GET /about/{topic}` returns 200 with dynamic topic data.
- `GET /form` returns 200 with department choices.
- `POST /form` succeeds with valid inputs and redirects with session flash.
- `POST /form` catches validation errors when fields are invalid.
- `POST /subscribe` validates email and flashes confirmation.
