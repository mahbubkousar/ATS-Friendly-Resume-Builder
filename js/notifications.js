


const markAllReadBtn = document.getElementById('markAllReadBtn');
if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', () => {
        const unreadCards = document.querySelectorAll('.notification-card.unread');
        unreadCards.forEach(card => {
            card.classList.remove('unread');
        });
        updateFilterCounts();
        showNotification('All notifications marked as read');
    });
}


const clearAllBtn = document.getElementById('clearAllBtn');
if (clearAllBtn) {
    clearAllBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
            const notificationsList = document.getElementById('notificationsList');
            notificationsList.innerHTML = '<div class="empty-state" style="text-align: center; padding: 4rem 2rem;"><i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i><h3 style="color: var(--text-dark); margin-bottom: 0.5rem;">No notifications</h3><p style="color: var(--text-muted);">You\'re all caught up!</p></div>';
            updateFilterCounts();
            showNotification('All notifications cleared');
        }
    });
}


const filterTabs = document.querySelectorAll('.filter-tab');
filterTabs.forEach(tab => {
    tab.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        
        filterTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        
        const filter = this.getAttribute('data-filter');
        const notificationCards = document.querySelectorAll('.notification-card');
        const notificationGroups = document.querySelectorAll('.notification-group');

        notificationCards.forEach(card => {
            const cardType = card.getAttribute('data-type');
            const isUnread = card.classList.contains('unread');

            let shouldShow = false;
            if (filter === 'all') {
                shouldShow = true;
            } else if (filter === 'unread') {
                shouldShow = isUnread;
            } else {
                shouldShow = cardType === filter;
            }

            card.style.display = shouldShow ? 'flex' : 'none';
        });

        
        notificationGroups.forEach(group => {
            const cards = group.querySelectorAll('.notification-card');
            let hasVisible = false;

            cards.forEach(card => {
                if (card.style.display === 'flex') {
                    hasVisible = true;
                }
            });

            group.style.display = hasVisible ? 'flex' : 'none';
        });
    });
});


const notificationCards = document.querySelectorAll('.notification-card');
notificationCards.forEach(card => {
    card.addEventListener('click', function(e) {
        
        if (e.target.closest('.notification-menu-btn')) {
            return;
        }

        this.classList.remove('unread');
        updateFilterCounts();
    });
});


const menuButtons = document.querySelectorAll('.notification-menu-btn');
menuButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        showNotificationMenu(this);
    });
});


function showNotificationMenu(button) {
    
    document.querySelectorAll('.notification-context-menu').forEach(menu => menu.remove());

    const menu = document.createElement('div');
    menu.className = 'notification-context-menu';
    menu.innerHTML = `
        <button class="context-menu-item" data-action="mark-read">
            <i class="fas fa-check"></i> Mark as read
        </button>
        <button class="context-menu-item" data-action="delete">
            <i class="fas fa-trash"></i> Delete
        </button>
    `;

    
    const rect = button.getBoundingClientRect();
    menu.style.position = 'fixed';
    menu.style.top = rect.bottom + 5 + 'px';
    menu.style.right = (window.innerWidth - rect.right) + 'px';

    document.body.appendChild(menu);

    
    const menuItems = menu.querySelectorAll('.context-menu-item');
    const card = button.closest('.notification-card');

    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            const action = item.getAttribute('data-action');

            if (action === 'mark-read') {
                card.classList.remove('unread');
                updateFilterCounts();
                showNotification('Notification marked as read');
            } else if (action === 'delete') {
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    card.remove();
                    updateFilterCounts();
                    showNotification('Notification deleted');

                    
                    const group = card.closest('.notification-group');
                    if (group && group.querySelectorAll('.notification-card').length === 0) {
                        group.remove();
                    }
                }, 300);
            }

            menu.remove();
        });
    });

    
    setTimeout(() => {
        document.addEventListener('click', function closeMenu(e) {
            if (!menu.contains(e.target) && e.target !== button) {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            }
        });
    }, 0);
}


function updateFilterCounts() {
    const allCount = document.querySelectorAll('.notification-card').length;
    const unreadCount = document.querySelectorAll('.notification-card.unread').length;
    const successCount = document.querySelectorAll('.notification-card[data-type="success"]').length;
    const infoCount = document.querySelectorAll('.notification-card[data-type="info"]').length;
    const warningCount = document.querySelectorAll('.notification-card[data-type="warning"]').length;

    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        const filter = tab.getAttribute('data-filter');
        const countSpan = tab.querySelector('.filter-count');

        if (filter === 'all') countSpan.textContent = allCount;
        else if (filter === 'unread') countSpan.textContent = unreadCount;
        else if (filter === 'success') countSpan.textContent = successCount;
        else if (filter === 'info') countSpan.textContent = infoCount;
        else if (filter === 'warning') countSpan.textContent = warningCount;
    });

    
    const navBadge = document.querySelector('.notification-badge');
    if (navBadge) {
        if (unreadCount > 0) {
            navBadge.textContent = unreadCount;
            navBadge.style.display = 'block';
        } else {
            navBadge.style.display = 'none';
        }
    }
}


const contextMenuStyles = document.createElement('style');
contextMenuStyles.textContent = `
    .notification-context-menu {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        min-width: 160px;
        overflow: hidden;
        animation: slideDown 0.2s ease;
    }

    .context-menu-item {
        width: 100%;
        padding: 0.75rem 1rem;
        border: none;
        background: none;
        cursor: pointer;
        text-align: left;
        font-size: 0.875rem;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: background 0.2s ease;
    }

    .context-menu-item:hover {
        background: var(--bg-light);
    }

    .context-menu-item i {
        width: 16px;
        text-align: center;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeOut {
        to {
            opacity: 0;
            transform: scale(0.95);
        }
    }
`;
document.head.appendChild(contextMenuStyles);


async function markAsRead(notificationId) {
    try {
        const response = await fetch('/ATS/api/mark-notification-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        });

        const result = await response.json();

        if (result.success) {
            
            const notificationCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationCard) {
                notificationCard.classList.remove('unread');
                
                const actionsDiv = notificationCard.querySelector('.notification-actions');
                if (actionsDiv) {
                    actionsDiv.innerHTML = `
                        <button class="btn-mark-unread" onclick="markAsUnread(${notificationId})" title="Mark as unread">
                            <i class="fas fa-envelope"></i>
                        </button>
                    `;
                }
            }
            updateFilterCounts();
            showNotification('Notification marked as read');

            
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = Math.max(0, currentCount - 1);
                if (newCount > 0) {
                    badge.textContent = newCount;
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
        showNotification('Failed to mark notification as read', 'error');
    }
}


async function markAsUnread(notificationId) {
    try {
        const response = await fetch('/ATS/api/mark-notification-unread.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ notification_id: notificationId })
        });

        const result = await response.json();

        if (result.success) {
            
            const notificationCard = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationCard) {
                notificationCard.classList.add('unread');
                
                const actionsDiv = notificationCard.querySelector('.notification-actions');
                if (actionsDiv) {
                    actionsDiv.innerHTML = `
                        <button class="btn-mark-read" onclick="markAsRead(${notificationId})" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                    `;
                }
            }
            updateFilterCounts();
            showNotification('Notification marked as unread');

            
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = currentCount + 1;
                badge.textContent = newCount;
                badge.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error marking notification as unread:', error);
        showNotification('Failed to mark notification as unread', 'error');
    }
}

console.log('Notifications page loaded');
