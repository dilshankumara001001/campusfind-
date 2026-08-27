<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5>🎓 <?= SITE_NAME ?></h5>
                <p class="text-muted">Helping university communities find lost items since 2026.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="<?= SITE_URL ?>browse.php">Browse</a></li>
                    <li><a href="<?= SITE_URL ?>about.php">About</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Report</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= SITE_URL ?>user/add-lost.php">Report Lost</a></li>
                    <li><a href="<?= SITE_URL ?>user/add-found.php">Report Found</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="text-center text-muted">
            <small>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Made with ❤️</small>
        </div>
    </div>
</footer>