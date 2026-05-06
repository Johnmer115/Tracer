/**
 * layout.js — Shared sidebar & notification logic
 * Used by: Dean_OSA, Staff_OSA, Branch_OSA layouts
 */

(function () {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar    = document.getElementById('sidebar');
    const isMobile   = () => window.innerWidth <= 768;

    // Restore collapsed state on load
    if (localStorage.getItem('sidebarCollapsed') === 'true' && !isMobile()) {
        document.body.classList.add('sidebar-collapsed');
    }

    // Toggle sidebar
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            if (isMobile()) {
                sidebar.classList.toggle('open');
                return;
            }
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                'sidebarCollapsed',
                document.body.classList.contains('sidebar-collapsed')
            );
        });
    }

    // Close mobile sidebar on resize
    window.addEventListener('resize', () => {
        if (!isMobile() && sidebar) {
            sidebar.classList.remove('open');
        }
    });

    // ── Notifications (only if the notif panel exists in this layout) ──
    const notifBtn   = document.getElementById('notif-btn');
    const notifPanel = document.getElementById('notif-panel');
    const notifBadge = document.getElementById('notif-badge');
    const notifList  = document.getElementById('notif-list');
    const markAllBtn = document.getElementById('notif-mark-all');

    if (!notifBtn) return; // Skip if layout has no notification bell

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.content : '';

    function notificationIcon(type) {
        if (type === 'approval')   return 'fa-stamp';
        if (type === 'submission') return 'fa-file-circle-plus';
        return 'fa-info-circle';
    }

    function renderNotifications(data) {
        notifBadge.textContent    = data.count > 9 ? '9+' : data.count;
        notifBadge.style.display  = data.count > 0 ? 'flex' : 'none';

        if (!data.notifications || data.notifications.length === 0) {
            notifList.innerHTML = `<div class="notif-empty">
                <i class="fas fa-check-circle" style="font-size:22px;margin-bottom:6px;display:block;color:#86efac;"></i>
                You're all caught up!
            </div>`;
            return;
        }

        notifList.innerHTML = data.notifications.map(n => `
            <form method="POST" action="/admin/notifications/${n.id}/read" style="margin:0;">
                <input type="hidden" name="_token" value="${csrfToken}">
                <button type="submit" class="notif-item"
                    style="width:100%;text-align:left;background:none;border:none;font-family:inherit;">
                    <div class="notif-icon ${n.type ?? 'status'}">
                        <i class="fas ${notificationIcon(n.type)}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="notif-title">${n.title}</div>
                        <div class="notif-msg">${n.message}</div>
                        <div class="notif-time">${n.created_at}</div>
                    </div>
                </button>
            </form>
        `).join('');
    }

    function fetchNotifications() {
        fetch('/admin/notifications', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(renderNotifications)
        .catch(() => {});
    }

    notifBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        notifPanel.classList.toggle('open');
        if (notifPanel.classList.contains('open')) fetchNotifications();
    });

    document.addEventListener('click', (e) => {
        const wrapper = document.getElementById('notif-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            notifPanel.classList.remove('open');
        }
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            fetch('/admin/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(() => fetchNotifications());
        });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
})();
