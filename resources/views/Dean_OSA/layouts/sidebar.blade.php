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
            <a href="{{ route('dean_osa.activity.index') }}" class="sb-item {{ request()->routeIs('dean_osa.activity.*') ? 'active' : '' }}" title="Activities">
                <i class="fas fa-file-alt"></i><span>Activities</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.approval.index') }}" class="sb-item {{ request()->routeIs('dean_osa.approval.*') ? 'active' : '' }}" title="Approvals">
                <i class="fas fa-check-circle"></i><span>Approvals</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.paar.index') }}" class="sb-item {{ request()->routeIs('dean_osa.paar.*') ? 'active' : '' }}" title="PAAR">
                <i class="fas fa-file-medical"></i><span>Post-Activity Report</span>
            </a>
        </li>
         <li>
            <a href="{{ route('dean_osa.tracer.index') }}" class="sb-item {{ request()->routeIs('dean_osa.tracer.*') ? 'active' : '' }}" title="Tracer">
                <i class="fas fa-file-medical"></i><span>Tracer</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">Management</p>
    <ul class="sb-list">
        <li>
            <a href="{{ route('dean_osa.schoolyear.index') }}" class="sb-item {{ request()->routeIs('dean_osa.schoolyear.*') ? 'active' : '' }}" title="School Year">
                <i class="fas fa-calendar"></i><span>School Year</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.account.index') }}" class="sb-item {{ request()->routeIs('dean_osa.account.*') ? 'active' : '' }}" title="Account">
                <i class="fas fa-users"></i><span>Account</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.branch.index') }}" class="sb-item {{ request()->routeIs('dean_osa.branch.*') ? 'active' : '' }}" title="Branch">
                <i class="fas fa-users"></i><span>Branch</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.department.index') }}" class="sb-item {{ request()->routeIs('dean_osa.department.*') ? 'active' : '' }}" title="Department">
                <i class="fas fa-sitemap"></i><span>Department</span>
            </a>
        </li>
        <li>
            <a href="{{ route('dean_osa.orgs.index') }}" class="sb-item {{ request()->routeIs('dean_osa.orgs.*') ? 'active' : '' }}" title="Organization">
                <i class="fas fa-user-friends"></i><span>Organization</span>
            </a>
        </li>
    </ul>

    <p class="sb-section">Monitoring</p>
    <ul class="sb-list">
        
        <li>
            <a href="{{ route('dean_osa.system-logs.index') }}" class="sb-item {{ request()->routeIs('dean_osa.system-logs.*') ? 'active' : '' }}" title="System Logs">
                <i class="fas fa-history"></i><span>System Logs</span>
            </a>
        </li>
    </ul>
</nav>
</aside>
