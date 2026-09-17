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
                <a href="{{ route('database.demo') }}" class="nav-link {{ request()->routeIs('database.demo') ? 'active' : '' }}">
                    Database Demo
                </a>
            </li>
        </ul>
    </nav>
</div>
