<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get filter parameters
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// ============================================================
// Build the query based on filters
// ============================================================

$where_conditions = [];
$params = [];

// Type filter
if ($type === 'lost') {
    $table = 'lost_items';
    $type_condition = "'lost' as type";
} elseif ($type === 'found') {
    $table = 'found_items';
    $type_condition = "'found' as type";
} else {
    // All items - use UNION
    $use_union = true;
}

// Category filter
if ($category) {
    $where_conditions[] = "category = ?";
    $params[] = $category;
}

// Search filter
if ($search) {
    $where_conditions[] = "(title LIKE ? OR description LIKE ? OR location LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// ============================================================
// Execute Query
// ============================================================

if (isset($use_union) && $use_union) {
    // All items - UNION query
    $sql = "
        (SELECT 'lost' as type, li.*, u.username 
         FROM lost_items li
         JOIN users u ON li.user_id = u.id
         $where_clause)
        UNION ALL
        (SELECT 'found' as type, fi.*, u.username 
         FROM found_items fi
         JOIN users u ON fi.user_id = u.id
         $where_clause)
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    // Add pagination parameters
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    // Count total
    $count_sql = "
        SELECT COUNT(*) FROM (
            (SELECT 'lost' as type FROM lost_items $where_clause)
            UNION ALL
            (SELECT 'found' as type FROM found_items $where_clause)
        ) as total
    ";
    $count_params = array_slice($params, 0, count($params) - 2);
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total = $count_stmt->fetchColumn();
    
} else {
    // Single type - direct query
    $sql = "
        SELECT $type_condition, $table.*, u.username 
        FROM $table
        JOIN users u ON $table.user_id = u.id
        $where_clause
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    // Add pagination parameters
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
    
    // Count total
    $count_sql = "SELECT COUNT(*) FROM $table $where_clause";
    $count_params = array_slice($params, 0, count($params) - 2);
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total = $count_stmt->fetchColumn();
}

$total_pages = ceil($total / $limit);
$categories = ['Electronics', 'Keys', 'Books', 'Accessories', 'Clothing', 'Documents', 'Wallets', 'Bags', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container py-4">
        <h1 class="mb-4">🔍 Browse Items</h1>
        
        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search items..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">All Types</option>
                            <option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>Lost</option>
                            <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>Found</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $category === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <?php if (empty($items)): ?>
            <div class="text-center py-5">
                <h3>😕 No items found</h3>
                <p class="text-muted">Try adjusting your search filters</p>
                <a href="browse.php" class="btn btn-primary">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($items as $item): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 item-card">
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?= $item['type'] ?>/<?= $item['image'] ?>" 
                                     class="card-img-top" alt="<?= $item['title'] ?>" 
                                     style="height:200px; object-fit:cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <span class="display-1 text-muted">📦</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?= $item['type'] === 'lost' ? 'danger' : 'success' ?>">
                                        <?= ucfirst($item['type']) ?>
                                    </span>
                                    <?= getStatusBadge($item['status']) ?>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                                <p class="card-text text-muted small">
                                    📍 <?= htmlspecialchars($item['location']) ?>
                                </p>
                                <p class="card-text text-muted small">
                                    👤 <?= htmlspecialchars($item['username']) ?>
                                </p>
                                <p class="card-text small text-muted">
                                    🕐 <?= timeAgo($item['created_at']) ?>
                                </p>
                                <a href="item-details.php?type=<?= $item['type'] ?>&id=<?= $item['id'] ?>" 
                                   class="btn btn-primary btn-sm w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
            
            <div class="text-center mt-3 text-muted">
                <small>Showing <?= count($items) ?> of <?= $total ?> items</small>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>