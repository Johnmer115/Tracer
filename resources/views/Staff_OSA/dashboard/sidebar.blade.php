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
            <a href="{{ route('staff_osa.index') }}" class="sb-item {{ request()->routeIs('staff_osa.index') ? 'active' : '' }}" title="Dashboard">
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
            <a title="Approvals">
                <i class="fas fa-check-circle"></i><span>Approvals</span>
            </a>
        </li>
        <li>
            <a title="PAAR">
                <i class="fas fa-file-medical"></i><span>Post-Activity Report</span>
            </a>
        </li>
    </ul>
</nav>
</aside>
