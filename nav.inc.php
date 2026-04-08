<?php 
require_once 'notifications.php';

// Get notification count for logged in user
$notification_count = 0;
$notifications = [];
if (isset($_SESSION['user_id'])) {
    $notification_count = getUnreadCount($_SESSION['user_id']);
    $notifications = getUserNotifications($_SESSION['user_id'], 10);
}
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Montserrat', sans-serif;
    }
    
    .navbar {
        background: white;
        border-bottom: 1px solid #ebebeb;
        padding: 16px 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .nav-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo {
        font-size: 24px;
        font-weight: bold;
        color: #ff385c;
        text-decoration: none;
    }
    
    .nav-links {
        display: flex;
        gap: 24px;
        align-items: center;
    }
    
    .nav-links a {
        text-decoration: none;
        color: #222;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.2s;
    }
    
    .nav-links a:hover {
        color: #ff385c;
    }
    
    .notification-icon {
        position: relative;
        cursor: pointer;
    }
    
    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff385c;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: bold;
        min-width: 18px;
        text-align: center;
    }
    
    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 380px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        z-index: 1001;
        display: none;
        max-height: 500px;
        overflow-y: auto;
    }
    
    .notification-dropdown.show {
        display: block;
    }
    
    .notification-header {
        padding: 16px;
        border-bottom: 1px solid #ebebeb;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .mark-all-read {
        font-size: 12px;
        color: #ff385c;
        cursor: pointer;
        text-decoration: none;
    }
    
    .notification-item {
        padding: 16px;
        border-bottom: 1px solid #ebebeb;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .notification-item:hover {
        background: #f7f7f7;
    }
    
    .notification-item.unread {
        background: #fff5f5;
    }
    
    .notification-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .notification-message {
        font-size: 13px;
        color: #717171;
        margin-bottom: 8px;
        white-space: pre-line;
    }
    
    .notification-time {
        font-size: 11px;
        color: #b0b0b0;
    }
    
    .notification-empty {
        padding: 40px;
        text-align: center;
        color: #717171;
    }
    
    @media (max-width: 768px) {
        .nav-links {
            gap: 12px;
        }
        .nav-links a {
            font-size: 12px;
        }
        .notification-dropdown {
            width: calc(100vw - 40px);
            right: -20px;
        }
    }
</style>

<script>
function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

function markAsRead(notificationId) {
    fetch('mark-notification-read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + notificationId
    }).then(() => {
        location.reload();
    });
}

function markAllAsRead() {
    fetch('mark-all-notifications-read.php', {
        method: 'POST'
    }).then(() => {
        location.reload();
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const icon = document.querySelector('.notification-icon');
    if (dropdown && icon && !dropdown.contains(event.target) && !icon.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">airbnb</a>
        <div class="nav-links">
            <a href="add-listing.php">Become a host</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="notification-icon" onclick="toggleNotifications()">
                    <a href="#" style="display: flex; gap: 8px;">
                        🔔
                        <?php if($notification_count > 0): ?>
                            <span class="notification-badge"><?= $notification_count ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <div id="notificationDropdown" class="notification-dropdown">
                        <div class="notification-header">
                            <span>Notifications</span>
                            <?php if($notification_count > 0): ?>
                                <a href="#" onclick="markAllAsRead(); return false;" class="mark-all-read">Mark all as read</a>
                            <?php endif; ?>
                        </div>
                        <?php if(count($notifications) > 0): ?>
                            <?php foreach($notifications as $notif): ?>
                                <div class="notification-item <?= $notif['is_read'] ? '' : 'unread' ?>" onclick="markAsRead(<?= $notif['id'] ?>)">
                                    <div class="notification-title">
                                        <?php 
                                        $icon = '';
                                        switch($notif['type']) {
                                            case 'booking_confirmed': $icon = '✅'; break;
                                            case 'new_booking': $icon = '📅'; break;
                                            case 'booking_cancelled': $icon = '❌'; break;
                                            default: $icon = '🔔';
                                        }
                                        echo $icon . ' ' . htmlspecialchars($notif['title']);
                                        ?>
                                    </div>
                                    <div class="notification-message"><?= nl2br(htmlspecialchars($notif['message'])) ?></div>
                                    <div class="notification-time"><?= date('M j, g:i A', strtotime($notif['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notification-empty">
                                🔕 No notifications yet
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="my-bookings.php">My trips</a>
                <a href="ai-assistant.php">🤖 AI Assistant</a>
                <a href="profile.php">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="search-section">
    <div class="search-container">
        <form method="GET" action="index.php" class="search-form">
            <div class="search-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="Search destinations" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
            </div>
            <div class="search-group">
                <label>Check in</label>
                <input type="date" name="check_in" value="<?= htmlspecialchars($_GET['check_in'] ?? '') ?>">
            </div>
            <div class="search-group">
                <label>Check out</label>
                <input type="date" name="check_out" value="<?= htmlspecialchars($_GET['check_out'] ?? '') ?>">
            </div>
            <div class="search-group">
                <label>Guests</label>
                <input type="number" name="guests" placeholder="1 guest" value="<?= htmlspecialchars($_GET['guests'] ?? '') ?>">
            </div>
            <button type="submit" class="search-btn">Search</button>
        </form>
    </div>
</div>

<style>
.search-section {
    background: white;
    padding: 20px 0;
    border-bottom: 1px solid #ebebeb;
}

.search-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.search-form {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.search-group {
    flex: 1;
    min-width: 150px;
}

.search-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 4px;
    color: #222;
}

.search-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ebebeb;
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
}

.search-group input:focus {
    outline: none;
    border-color: #ff385c;
}

.search-btn {
    background: #ff385c;
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    font-size: 14px;
}

.search-btn:hover {
    background: #e31c5f;
}

@media (max-width: 768px) {
    .search-form {
        flex-direction: column;
    }
    .search-btn {
        width: 100%;
    }
}
</style>
