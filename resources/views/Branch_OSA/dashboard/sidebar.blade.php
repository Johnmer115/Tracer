<aside class="sidebar" id="sidebar">
    <div class="sb-logo">
        <div class="sb-logo-icon">
            <img src="{{ asset('image/logo/arellano_logo.png') }}" alt="Logo">
        </div>
        <div class="sb-logo-name">AU-SARF Tracking <span>Management System</span></div>
    </div>

    <nav class="sb-nav">
    <p class="sb-section">Main</p>
        <ul class="sb-list">
            <li>
            <a href="{{ route('branch_osa.index') }}" class="sb-item {{ request()->routeIs('branch_osa.index') ? 'active' : '' }}" title="Dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">SARF</p>
    <ul class="sb-list">
        <li>
            <a title="SARF Activities">
                <i class="fas fa-file-alt"></i><span>SARF Activities</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">Management</p>
    <ul class="sb-list">
        <li>
            <a title="Activities">
                <i class="fas fa-file-alt"></i><span>Activities</span>
            </a>
        </li>
    </ul>
</nav>
</aside>
