<div class="container navbar">
    <a href="{{ route('home') }}" class="brand-link">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16.5 9.4 7.55 4.24a1.78 1.78 0 0 0-2.5 1.55v12.42a1.78 1.78 0 0 0 2.5 1.55L16.5 14.6a1.78 1.78 0 0 0 0-3.2z"></path>
        </svg>
        <span>Cynaris Laravel</span>
    </a>

    <nav>
        <ul class="nav-links">
            <li>
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    Home
                </a>
            </li>
            <li>
                <a href="{{ route('about') }}" class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}">
                    About
                </a>
            </li>
            <li>
                <a href="{{ route('form.index') }}" class="nav-link {{ request()->routeIs('form.index') ? 'active' : '' }}">
                    Contact Form
                </a>
            </li>
            <li>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    Products (CRUD)
                </a>
            </li>
            <li>
                <a href="{{ route('blog.index') }}" class="nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">
                    Blog (CRUD)
                </a>
            </li>
            <li>
                <a href="{{ route('database.demo') }}" class="nav-link {{ request()->routeIs('database.demo') ? 'active' : '' }}">
                    Database Demo
                </a>
            </li>
            <li>
                <a href="{{ route('api.demo') }}" class="nav-link {{ request()->routeIs('api.demo') ? 'active' : '' }}">
                    API Demo
                </a>
            </li>
            <li>
                <a href="{{ route('debug.demo') }}" class="nav-link {{ request()->routeIs('debug.demo') ? 'active' : '' }}">
                    Debug Demo
                </a>
            </li>
            @auth
                <li>
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" style="font-weight: 600; color: var(--primary);">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('account') }}" class="nav-link {{ request()->routeIs('account') ? 'active' : '' }}">
                        Account
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        Profile
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="nav-link" style="background: none; border: none; cursor: pointer; font-family: inherit; font-size: 0.95rem;">
                            Log Out ({{ Auth::user()->name }})
                        </button>
                    </form>
                </li>
            @else
                <li>
                    <a href="{{ route('login') }}" class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}">
                        Log in
                    </a>
                </li>
                <li>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm" style="color: #ffffff; text-decoration: none;">
                        Register
                    </a>
                </li>
            @endauth
        </ul>
    </nav>
</div>
