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
            <a href="{{ route('staff_osa.activity.index') }}" class="sb-item {{ request()->routeIs('staff_osa.activity.*') ? 'active' : '' }}" title="Activities">
                <i class="fas fa-file-alt"></i><span>Activities</span>
            </a>
        </li>
        <li>
            <a href="{{ route('staff_osa.approval.index') }}" class="sb-item {{ request()->routeIs('staff_osa.approval.*') ? 'active' : '' }}" title="Approvals">
                <i class="fas fa-check-circle"></i><span>Approvals</span>
            </a>
        </li>
        <li>
            <a href="{{ route('staff_osa.paar.index') }}" class="sb-item {{ request()->routeIs('staff_osa.paar.*') ? 'active' : '' }}" title="PAAR">
                <i class="fas fa-file-medical"></i><span>Post-Activity Report</span>
            </a>
        </li>
        <li>
            <a href="{{ route('staff_osa.tracer.index') }}" class="sb-item {{ request()->routeIs('staff_osa.tracer.*') ? 'active' : '' }}" title="Tracer">
                <i class="fas fa-route"></i><span>Tracer</span>
            </a>
        </li>
    </ul>
</nav>
</aside>
