(function () {
    const context = window.NOTIFICATION_CONTEXT;
    const navLinks = document.querySelector('nav .nav-links');

    if (!context || !navLinks) {
        return;
    }

    const shell = document.createElement('div');
    shell.className = 'notifications-shell';
    shell.innerHTML = `
        <button class="notifications-trigger" id="notifications-trigger" type="button" aria-label="Notifications">
            <span class="notifications-icon">&#128276;</span>
            <span class="notifications-badge hidden" id="notifications-badge">0</span>
        </button>
        <div class="notifications-panel hidden" id="notifications-panel">
            <div class="notifications-header">
                <strong>Notifications</strong>
                <button class="notifications-mark-read" id="notifications-mark-read" type="button">Mark all read</button>
            </div>
            <div class="notifications-list" id="notifications-list">
                <div class="notifications-empty">No notifications yet.</div>
            </div>
        </div>
        <div class="notifications-toast hidden" id="notifications-toast">
            <div class="notifications-toast-header">
                <span class="notifications-toast-icon">&#128276;</span>
                <span class="notifications-toast-title" id="notifications-toast-title"></span>
            </div>
            <div class="notifications-toast-message" id="notifications-toast-message"></div>
        </div>
    `;
    navLinks.appendChild(shell);

    const trigger = document.getElementById('notifications-trigger');
    const panel = document.getElementById('notifications-panel');
    const badge = document.getElementById('notifications-badge');
    const list = document.getElementById('notifications-list');
    const markReadBtn = document.getElementById('notifications-mark-read');
    const toast = document.getElementById('notifications-toast');
    const toastTitle = document.getElementById('notifications-toast-title');
    const toastMessage = document.getElementById('notifications-toast-message');

    let toastTimer;

    async function fetchNotifications() {
        try {
            const params = new URLSearchParams({
                role: context.role,
                recipient_id: String(context.recipientId)
            });
            const res = await fetch(`notifications.php?${params.toString()}`);
            const data = await res.json();

            if (!data.success) {
                return;
            }

            renderNotifications(data.notifications || []);
            updateBadge(data.unread_count || 0);
            maybeShowToast(data.notifications || []);
        } catch (error) {
            console.error('Failed to load notifications:', error);
        }
    }

    function updateBadge(unreadCount) {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    function renderNotifications(items) {
        if (!items.length) {
            list.innerHTML = '<div class="notifications-empty">No notifications yet.</div>';
            return;
        }

        list.innerHTML = items.map((item) => `
            <a
                class="notifications-item ${item.is_read == 0 ? 'unread' : ''}"
                href="${item.link_url || '#'}"
                data-id="${item.id}"
                data-link="${item.link_url || ''}"
            >
                <div class="notifications-item-title">${escapeHtml(item.title)}</div>
                <div class="notifications-item-message">${escapeHtml(item.message)}</div>
                <div class="notifications-item-time">${formatTime(item.created_at)}</div>
            </a>
        `).join('');
    }

    async function markRead(notificationId) {
        try {
            await fetch('notifications_mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    role: context.role,
                    recipient_id: context.recipientId,
                    notification_id: notificationId
                })
            });
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    }

    async function markAllRead() {
        try {
            const res = await fetch('notifications_mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    role: context.role,
                    recipient_id: context.recipientId
                })
            });
            const data = await res.json();
            if (data.success) {
                fetchNotifications();
            }
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    }

    function maybeShowToast(items) {
        const newestUnread = items.find((item) => Number(item.is_read) === 0);
        if (!newestUnread) {
            return;
        }

        const storageKey = `notifications:last-shown:${context.role}:${context.recipientId}`;
        const lastShownId = Number(sessionStorage.getItem(storageKey) || 0);

        if (newestUnread.id <= lastShownId) {
            return;
        }

        sessionStorage.setItem(storageKey, String(newestUnread.id));
        toastTitle.textContent = newestUnread.title;
        toastMessage.textContent = newestUnread.message;
        toast.classList.remove('hidden');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    }

    function formatTime(value) {
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }
        return date.toLocaleString();
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    trigger.addEventListener('click', () => {
        panel.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!shell.contains(event.target)) {
            panel.classList.add('hidden');
        }
    });

    list.addEventListener('click', async (event) => {
        const item = event.target.closest('.notifications-item');
        if (!item) {
            return;
        }

        const id = Number(item.dataset.id);
        const link = item.dataset.link;

        event.preventDefault();
        await markRead(id);
        panel.classList.add('hidden');
        await fetchNotifications();

        if (link) {
            window.location.href = link;
        }
    });

    markReadBtn.addEventListener('click', markAllRead);

    fetchNotifications();
    window.setInterval(fetchNotifications, 30000);
})();
