<?php
/**
 * Admin - Add Product
 */

$page_title = 'Add Product';
require_once 'header.php';

$conn = getDB();
$categories = getCategories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = sanitize(trim($_POST['name'] ?? ''));
    $category_id = (int)($_POST['category_id'] ?? 0);
    $brand       = sanitize(trim($_POST['brand'] ?? ''));
    $sku         = sanitize(trim($_POST['sku'] ?? ''));
    $price       = (float)($_POST['price'] ?? 0);
    $sale_price  = (float)($_POST['sale_price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);
    $short_desc  = sanitize(trim($_POST['short_description'] ?? ''));
    $description = sanitize(trim($_POST['description'] ?? ''));
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Specs
    $spec_keys   = $_POST['spec_keys'] ?? [];
    $spec_values = $_POST['spec_values'] ?? [];
    $specs = [];
    foreach ($spec_keys as $i => $key) {
        if (!empty(trim($key)) && !empty(trim($spec_values[$i] ?? ''))) {
            $specs[trim($key)] = trim($spec_values[$i]);
        }
    }
    $specs_json = !empty($specs) ? addslashes(json_encode($specs)) : '';

    if (empty($name))       $errors[] = 'Product name is required.';
    if ($category_id == 0)  $errors[] = 'Please select a category.';
    if ($price <= 0)        $errors[] = 'Price must be greater than 0.';

    // Handle image upload
    $image_name = 'no-image.png';
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG or WebP.';
        } else {
            $image_name = slugify($name) . '-' . time() . '.' . $ext;
            $dest = __DIR__ . '/../assets/images/products/' . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (empty($errors)) {
        $slug = slugify($name) . '-' . time();
        $sale_price_sql = $sale_price > 0 ? $sale_price : 'NULL';
        $specs_sql = !empty($specs_json) ? "'$specs_json'" : 'NULL';

        $conn->query("
            INSERT INTO products
            (category_id, name, slug, brand, sku, short_description, description,
             price, sale_price, stock, image, specifications, is_featured)
            VALUES
            ($category_id, '$name', '$slug', '$brand', '$sku', '$short_desc', '$description',
             $price, $sale_price_sql, $stock, '$image_name', $specs_sql, $is_featured)
        ");

        if ($conn->insert_id) {
            setFlash('success', 'Product "' . $name . '" added successfully!');
            redirect(SITE_URL . '/admin/products.php');
        } else {
            $errors[] = 'Failed to add product. The SKU may already be in use.';
        }
    }
}
?>

<div style="margin-bottom:20px;">
    <a href="products.php" style="color:#6B7280;font-size:0.88rem;text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Back to Products
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2 ps-3">
        <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Basic Info -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Product Information</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                           placeholder="e.g. Samsung Galaxy S24 Ultra">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['brand'] ?? ''); ?>"
                           placeholder="e.g. Samsung, Apple, Sony">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SKU (Stock Keeping Unit)</label>
                    <input type="text" name="sku" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>"
                           placeholder="e.g. SAM-S24U-BLK">
                </div>
                <div class="col-12">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="short_description" class="form-control" maxlength="500"
                           value="<?php echo htmlspecialchars($_POST['short_description'] ?? ''); ?>"
                           placeholder="Brief one-line product summary (shown on cards)">
                </div>
                <div class="col-12">
                    <label class="form-label">Full Description</label>
                    <textarea name="description" class="form-control" rows="5"
                              placeholder="Detailed product description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Category & Pricing -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Category & Pricing</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Regular Price (Rs.) *</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" name="price" class="form-control" required step="0.01" min="0"
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>"
                               placeholder="0.00">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Sale Price (Rs.) <span style="color:#9CA3AF;font-weight:400;">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0"
                               value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>"
                               placeholder="0.00">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" required min="0"
                           value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                </div>
            </div>
        </div>

        <!-- Product Image -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Product Image</h6>
            <div style="border:2px dashed #E5E7EB;border-radius:12px;padding:24px;text-align:center;cursor:pointer;background:#F8FAFC;"
                 onclick="document.getElementById('imageInput').click()">
                <img id="imagePreview" src="" style="display:none;max-width:100%;max-height:160px;border-radius:8px;margin-bottom:10px;">
                <div id="imageHint">
                    <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:#9CA3AF;margin-bottom:8px;"></i>
                    <div style="font-size:0.88rem;font-weight:600;color:#374151;">Click to upload image</div>
                    <div style="font-size:0.78rem;color:#9CA3AF;margin-top:4px;">JPG, PNG, WebP (max 5MB)</div>
                </div>
            </div>
            <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;"
                   onchange="previewImage(this)">
        </div>

        <!-- Options -->
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Options</h6>
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured"
                       <?php echo !empty($_POST['is_featured']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_featured" style="font-size:0.88rem;font-weight:600;">
                    <i class="fas fa-star text-warning me-1"></i>Mark as Featured
                </label>
                <div style="font-size:0.78rem;color:#9CA3AF;">Featured products appear on homepage</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3" style="font-weight:700;font-size:1rem;">
            <i class="fas fa-plus-circle me-2"></i>Add Product
        </button>
        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
    </div>
</div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('imageHint').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('addSpec').addEventListener('click', function() {
    const container = document.getElementById('specsContainer');
    const row = document.createElement('div');
    row.className = 'spec-row row g-2 mb-2';
    row.innerHTML = `
        <div class="col-5"><input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name"></div>
        <div class="col-6"><input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value"></div>
        <div class="col-1"><button type="button" onclick="this.closest('.spec-row').remove()" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:1rem;padding:6px;"><i class="fas fa-times"></i></button></div>
    `;
    container.appendChild(row);
});
</script>

<?php require_once 'footer.php'; ?>
