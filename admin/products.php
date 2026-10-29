<?php
/**
 * Admin - Products Management
 */

$page_title = 'Manage Products';
require_once 'header.php';

$conn = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("UPDATE products SET is_active=0 WHERE id=$id");
    setFlash('success', 'Product deleted successfully.');
    redirect(SITE_URL . '/admin/products.php');
}

// Filters
$search = sanitize($_GET['search'] ?? '');
$cat_filter = (int)($_GET['category'] ?? 0);
$where = "p.is_active = 1";
if ($search) $where .= " AND (p.name LIKE '%$search%' OR p.brand LIKE '%$search%' OR p.sku LIKE '%$search%')";
if ($cat_filter) $where .= " AND p.category_id = $cat_filter";

$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 15;
$offset = ($page - 1) * $per;

$total = $conn->query("SELECT COUNT(*) as c FROM products p WHERE $where")->fetch_assoc()['c'];
$total_pages = ceil($total / $per);

$result = $conn->query("
    SELECT p.*, c.name AS cat_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $where ORDER BY p.created_at DESC LIMIT $per OFFSET $offset
");
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$categories = getCategories();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <p style="color:#6B7280;font-size:0.88rem;margin:0;">Manage your product catalog</p>
    </div>
    <a href="add-product.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">Search Products</label>
            <input type="text" name="search" class="form-control" placeholder="Name, brand or SKU..."
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" style="font-size:0.82rem;font-weight:600;color:#6B7280;">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $cat_filter==$cat['id']?'selected':''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-2">
            <a href="products.php" class="btn btn-outline-secondary w-100">Clear</a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <span style="font-size:0.88rem;color:#6B7280;">
            Showing <strong><?php echo count($products); ?></strong> of <strong><?php echo $total; ?></strong> products
        </span>
    </div>
    <div style="overflow-x:auto;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>review</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>Rating</th>
                <th>Actions</th>
                 
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
            <tr><td colspan="8" style="text-align:center;padding:40px;color:#9CA3AF;">No products found.</td></tr>
            <?php else: ?>
            <?php foreach ($products as $p):
                $price = getProductPrice($p);
                $on_sale = $p['sale_price'] && $p['sale_price'] > 0;
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($p['image']); ?>"
                             onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                             style="width:50px;height:50px;object-fit:contain;background:#F8FAFC;border-radius:8px;border:1px solid #E5E7EB;padding:3px;">
                        <div>
                            <div style="font-weight:700;font-size:0.88rem;max-width:200px;"><?php echo htmlspecialchars($p['name']); ?></div>
                            <div style="font-size:0.76rem;color:#6B7280;"><?php echo htmlspecialchars($p['brand']); ?></div>
                        </div>
                    </div>
                </td>
                <td style="font-family:monospace;font-size:0.8rem;color:#6B7280;"><?php echo htmlspecialchars($p['sku']); ?></td>
                <td>
                    <span style="background:#F0F7FF;color:#0066FF;padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                        <?php echo htmlspecialchars($p['cat_name']); ?>
                    </span>
                </td>
                <td>
                    <div style="font-weight:800;color:#0066FF;"><?php echo "Rs.".($price); ?></div>
                    <?php if ($on_sale): ?>
                        <div style="font-size:0.75rem;text-decoration:line-through;color:#9CA3AF;"><?php echo "Rs.".($p['price']); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['stock'] == 0): ?>
                        <span class="badge bg-danger">Out of Stock</span>
                    <?php elseif ($p['stock'] <= 5): ?>
                        <span class="badge bg-warning text-dark"><?php echo $p['stock']; ?> left</span>
                    <?php else: ?>
                        <span style="font-weight:700;color:#10B981;"><?php echo $p['stock']; ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['is_featured']): ?>
                        <i class="fas fa-star text-warning"></i>
                    <?php else: ?>
                        <i class="far fa-star text-muted"></i>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-size:0.85rem;"><?php echo renderStars($p['rating']); ?></div>
                    <div style="font-size:0.75rem;color:#6B7280;">(<?php echo $p['review_count']; ?>)</div>
                </td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <a href="edit-product.php?id=<?php echo $p['id']; ?>"
                           class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?php echo SITE_URL; ?>/product.php?slug=<?php echo $p['slug']; ?>"
                           class="btn btn-sm btn-outline-secondary" title="View" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="products.php?delete=<?php echo $p['id']; ?>"
                           class="btn btn-sm btn-outline-danger" title="Delete"
                           onclick="return confirm('Delete this product?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:20px;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $cat_filter; ?>"
           style="width:36px;height:36px;border:1px solid <?php echo $i==$page?'#0066FF':'#E5E7EB'; ?>;background:<?php echo $i==$page?'#0066FF':'white'; ?>;color:<?php echo $i==$page?'white':'#374151'; ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:600;text-decoration:none;">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'footer.php'; ?>
