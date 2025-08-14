<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-3" dir="rtl">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="{{ url('/') }}">HF System</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hfTopNav" aria-controls="hfTopNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="hfTopNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        @auth
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.create') }}">المبيعات</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">العملاء</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('branches.*') ? 'active' : '' }}" href="{{ route('branches.index') }}">الفروع</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">المنتجات</a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">التصنيفات</a>
          </li>
        @endauth
      </ul>
      <ul class="navbar-nav ms-auto">
        @auth
        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger btn-sm">تسجيل الخروج</button>
          </form>
        </li>
        @else
        <li class="nav-item">
          <a class="btn btn-primary btn-sm" href="{{ route('login') }}">تسجيل الدخول</a>
        </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>
