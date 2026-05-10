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
            <a href="{{ route('dean_osa.index') }}" class="sb-item {{ request()->routeIs('dean_osa.index') ? 'active' : '' }}" title="Dashboard">
                <i class="fas fa-home"></i><span>Dashboard</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">SARF</p>
    <ul class="sb-list">
        <li>
            <a href="{{ route('dean_osa.activity.index') }}" class="sb-item {{ request()->routeIs('dean_osa.activity.index') ? 'active' : '' }}" title="Activities">
                <i class="fas fa-file-alt"></i><span>Activities</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.approval.index') }}" class="sb-item {{ request()->routeIs('dean_osa.approval.index') ? 'active' : '' }}" title="Approvals">
                <i class="fas fa-check-circle"></i><span>Approvals</span>
            </a>
        </li>
        <li>
            <a title="PAAR">
                <i class="fas fa-file-medical"></i><span>Post-Activity Report</span>
            </a>
        </li>
         <li>
            <a title="Tracer">
                <i class="fas fa-file-medical"></i><span>Tracer</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">Management</p>
    <ul class="sb-list">
        <li>
            <a href="{{ route('dean_osa.schoolyear.index') }}" class="sb-item {{ request()->routeIs('dean_osa.schoolyear.index') ? 'active' : '' }}" title="School Year">
                <i class="fas fa-calendar"></i><span>School Year</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.account.index') }}" class="sb-item {{ request()->routeIs('dean_osa.account.index') ? 'active' : '' }}" title="Account">
                <i class="fas fa-users"></i><span>Account</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.branch.index') }}" class="sb-item {{ request()->routeIs('dean_osa.branch.index') ? 'active' : '' }}" title="Branch">
                <i class="fas fa-users"></i><span>Branch</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">Monitoring</p>
    <ul class="sb-list">
        
        <li>
            <a title="System Logs">
                <i class="fas fa-history"></i><span>System Logs</span>
            </a>
        </li>
    </ul>
</nav>
</aside>
