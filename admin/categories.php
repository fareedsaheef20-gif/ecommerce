<?php
/**
 * Admin - Categories Management
 */

$page_title = 'Manage Categories';
require_once 'header.php';

$conn = getDB();
$errors = [];

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id      = (int)($_POST['cat_id'] ?? 0);
    $name        = sanitize(trim($_POST['name'] ?? ''));
    $description = sanitize(trim($_POST['description'] ?? ''));
    $icon        = sanitize(trim($_POST['icon'] ?? 'fas fa-tag'));
    $is_active   = isset($_POST['is_active']) ? 1 : 0;

    if (strlen($name) < 2) {
        $errors[] = 'Category name must be at least 2 characters.';
    } else {
        $slug = slugify($name);
        if ($cat_id > 0) {
            $conn->query("UPDATE categories SET name='$name', slug='$slug', description='$description', icon='$icon', is_active=$is_active WHERE id=$cat_id");
            setFlash('success', 'Category updated successfully.');
        } else {
            $conn->query("INSERT INTO categories (name, slug, description, icon, is_active) VALUES ('$name','$slug','$description','$icon',$is_active)");
            setFlash('success', 'Category "' . $name . '" added successfully.');
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE category_id=$id AND is_active=1")->fetch_assoc()['c'];
    if ($count > 0) {
        setFlash('danger', 'Cannot delete category with ' . $count . ' active products.');
    } else {
        $conn->query("UPDATE categories SET is_active=0 WHERE id=$id");
        setFlash('success', 'Category deleted.');
    }
    redirect(SITE_URL . '/admin/categories.php');
}

// Fetch for edit
$edit_cat = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $r = $conn->query("SELECT * FROM categories WHERE id=$edit_id LIMIT 1");
    $edit_cat = $r ? $r->fetch_assoc() : null;
}

// Get all categories with product count
$result = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Common icon options
$icons = [
    'fas fa-mobile-alt'=>'Mobile Phone','fas fa-laptop'=>'Laptop','fas fa-headphones'=>'Headphones',
    'fas fa-clock'=>'Smart Watch','fas fa-tablet-alt'=>'Tablet','fas fa-camera'=>'Camera',
    'fas fa-gamepad'=>'Gaming','fas fa-plug'=>'Accessories','fas fa-tv'=>'TV','fas fa-microchip'=>'Microchip',
    'fas fa-keyboard'=>'Keyboard','fas fa-mouse'=>'Mouse','fas fa-print'=>'Printer','fas fa-tags'=>'Tags',
];
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4"><ul class="mb-0 ps-3"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add/Edit Form -->
    <div class="col-lg-4">
        <div class="admin-card" style="position:sticky;top:80px;">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">
                <?php echo $edit_cat ? '<i class="fas fa-edit me-2 text-primary"></i>Edit Category' : '<i class="fas fa-plus-circle me-2 text-primary"></i>Add Category'; ?>
            </h6>
            <form method="POST">
                <?php if ($edit_cat): ?>
                <input type="hidden" name="cat_id" value="<?php echo $edit_cat['id']; ?>">
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" class="form-control" required minlength="2"
                           value="<?php echo htmlspecialchars($edit_cat ? $edit_cat['name'] : ''); ?>"
                           placeholder="e.g. Smart Watches">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"
                              placeholder="Brief description of this category"><?php echo htmlspecialchars($edit_cat ? $edit_cat['description'] : ''); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Font Awesome class)</label>
                    <select name="icon" class="form-select" id="iconSelect">
                        <?php foreach ($icons as $class => $label): ?>
                        <option value="<?php echo $class; ?>" <?php echo ($edit_cat && $edit_cat['icon']==$class)?'selected':''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="margin-top:8px;background:#F8FAFC;border-radius:8px;padding:10px;text-align:center;">
                        <i id="iconPreview" class="<?php echo $edit_cat ? $edit_cat['icon'] : 'fas fa-mobile-alt'; ?> fa-2x text-primary"></i>
                        <div style="font-size:0.75rem;color:#9CA3AF;margin-top:4px;">Preview</div>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               <?php echo (!$edit_cat || $edit_cat['is_active']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="is_active" style="font-size:0.88rem;font-weight:600;">Active</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-<?php echo $edit_cat?'save':'plus'; ?> me-2"></i>
                    <?php echo $edit_cat ? 'Update Category' : 'Add Category'; ?>
                </button>
                <?php if ($edit_cat): ?>
                <a href="categories.php" class="btn btn-outline-secondary w-100 mt-2">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="admin-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h6 style="font-weight:800;margin:0;"><i class="fas fa-tags me-2 text-primary"></i>All Categories</h6>
                <span style="font-size:0.85rem;color:#6B7280;"><?php echo count($categories); ?> total</span>
            </div>

            <?php if (empty($categories)): ?>
            <p style="text-align:center;padding:30px;color:#9CA3AF;">No categories yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Slug</th>
                        <th style="text-align:center;">Products</th>
                        <th style="text-align:center;">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div style="width:38px;height:38px;background:#F0F7FF;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#0066FF;font-size:0.9rem;">
                                    <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;"><?php echo htmlspecialchars($cat['name']); ?></div>
                                    <?php if ($cat['description']): ?>
                                    <div style="font-size:0.75rem;color:#9CA3AF;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo htmlspecialchars($cat['description']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;font-size:0.8rem;color:#6B7280;"><?php echo htmlspecialchars($cat['slug']); ?></td>
                        <td style="text-align:center;">
                            <a href="products.php?category=<?php echo $cat['id']; ?>"
                               style="font-weight:700;color:#0066FF;text-decoration:none;">
                                <?php echo $cat['product_count']; ?>
                            </a>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($cat['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="?edit=<?php echo $cat['id']; ?>"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?php echo SITE_URL; ?>/products.php?category=<?php echo $cat['slug']; ?>"
                                   class="btn btn-sm btn-outline-secondary" target="_blank" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($cat['product_count'] == 0): ?>
                                <a href="?delete=<?php echo $cat['id']; ?>"
                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                   onclick="return confirm('Delete this category?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('iconSelect').addEventListener('change', function() {
    document.getElementById('iconPreview').className = this.value + ' fa-2x text-primary';
});
</script>

<?php require_once 'footer.php'; ?>
