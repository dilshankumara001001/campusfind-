<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>">🎓 <span>Campus</span>Find</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>browse.php">Browse</a></li>
                <?php if (isLoggedIn()): ?>
                    <?php $unread = getUnreadNotificationCount($_SESSION['user_id']); ?>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>user/dashboard.php">Dashboard</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>user/notifications.php">
                            🔔 <?php if ($unread > 0): ?><span class="badge bg-danger"><?= $unread ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">👋 <?= htmlspecialchars($_SESSION['username']) ?></a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>user/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>user/lost-items.php">My Lost</a></li>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>user/found-items.php">My Found</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>admin/">⚙️ Admin</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-primary" href="<?= SITE_URL ?>register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>