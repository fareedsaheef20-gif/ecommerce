<?php
/**
 * Admin - Edit Product
 */

$page_title = 'Edit Product';
require_once 'header.php';

$conn = getDB();
$categories = getCategories();
$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect(SITE_URL . '/admin/products.php');

$product = getProductById($id);
if (!$product) { setFlash('danger','Product not found.'); redirect(SITE_URL . '/admin/products.php'); }

$specs = [];
if (!empty($product['specifications'])) $specs = json_decode($product['specifications'], true) ?? [];

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

    $spec_keys   = $_POST['spec_keys'] ?? [];
    $spec_values = $_POST['spec_values'] ?? [];
    $new_specs = [];
    foreach ($spec_keys as $i => $key) {
        if (!empty(trim($key))) $new_specs[trim($key)] = trim($spec_values[$i] ?? '');
    }
    $specs_json = addslashes(json_encode($new_specs));

    if (empty($name))      $errors[] = 'Product name is required.';
    if ($category_id == 0) $errors[] = 'Please select a category.';
    if ($price <= 0)       $errors[] = 'Price must be greater than 0.';

    // Handle image upload
    $image_name = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be JPG, PNG or WebP.';
        } else {
            $image_name = slugify($name) . '-' . time() . '.' . $ext;
            $dest = __DIR__ . '/../assets/images/products/' . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
        }
    }

    if (empty($errors)) {
        $sale_price_sql = $sale_price > 0 ? $sale_price : 'NULL';
        $conn->query("
            UPDATE products SET
            category_id=$category_id, name='$name', brand='$brand', sku='$sku',
            short_description='$short_desc', description='$description',
            price=$price, sale_price=$sale_price_sql, stock=$stock,
            image='$image_name', specifications='$specs_json', is_featured=$is_featured
            WHERE id=$id
        ");
        setFlash('success', 'Product updated successfully!');
        redirect(SITE_URL . '/admin/products.php');
    }
    // Re-read posted values
    $product = array_merge($product, ['name'=>$_POST['name'],'brand'=>$_POST['brand'],'sku'=>$_POST['sku'],'price'=>$_POST['price'],'sale_price'=>$_POST['sale_price'],'stock'=>$_POST['stock'],'short_description'=>$_POST['short_description'],'description'=>$_POST['description'],'category_id'=>$_POST['category_id'],'is_featured'=>isset($_POST['is_featured'])?1:0]);
    $specs = $new_specs;
}
?>

<div style="margin-bottom:20px;">
    <a href="products.php" style="color:#6B7280;font-size:0.88rem;text-decoration:none;">
        <i class="fas fa-arrow-left me-1"></i>Back to Products
    </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
    <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Product Information</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($product['brand']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Short Description</label>
                    <input type="text" name="short_description" class="form-control" value="<?php echo htmlspecialchars($product['short_description']); ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">full Description</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Review</label>
                    <textarea name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                
            </div>
        </div>

        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Specifications</h6>
            <div id="specsContainer">
                <?php if (!empty($specs)): foreach ($specs as $key => $val): ?>
                <div class="spec-row row g-2 mb-2">
                    <div class="col-5"><input type="text" name="spec_keys[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($key); ?>"></div>
                    <div class="col-6"><input type="text" name="spec_values[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($val); ?>"></div>
                    <div class="col-1"><button type="button" onclick="this.closest('.spec-row').remove()" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:1rem;padding:6px;"><i class="fas fa-times"></i></button></div>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <button type="button" id="addSpec" class="btn btn-outline-secondary btn-sm mt-2">
                <i class="fas fa-plus me-1"></i>Add Specification
            </button>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Category & Pricing</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id']==$cat['id']?'selected':''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Regular Price ($) *</label>
                    <div class="input-group"><span class="input-group-text">$</span>
                    <input type="number" name="price" class="form-control" required step="0.01" min="0" value="<?php echo $product['price']; ?>"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Sale Price ($)</label>
                    <div class="input-group"><span class="input-group-text">$</span>
                    <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?php echo $product['sale_price']; ?>"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" name="stock" class="form-control" min="0" value="<?php echo $product['stock']; ?>">
                </div>
            </div>
        </div>

        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Product Image</h6>
            <div style="text-align:center;margin-bottom:12px;">
                <img id="imagePreview"
                     src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                     onerror="this.src='<?php echo SITE_URL; ?>/assets/images/no-image.png'"
                     style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid #E5E7EB;">
            </div>
            <div style="border:2px dashed #E5E7EB;border-radius:12px;padding:16px;text-align:center;cursor:pointer;background:#F8FAFC;"
                 onclick="document.getElementById('imageInput').click()">
                <i class="fas fa-cloud-upload-alt" style="color:#9CA3AF;margin-bottom:6px;font-size:1.4rem;"></i>
                <div style="font-size:0.82rem;color:#6B7280;">Click to replace image</div>
            </div>
            <input type="file" id="imageInput" name="image" accept="image/*" style="display:none;" onchange="previewImage(this)">
        </div>

        <div class="admin-card mb-4">
            <h6 style="font-weight:800;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #E5E7EB;">Options</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?php echo $product['is_featured']?'checked':''; ?>>
                <label class="form-check-label" for="is_featured" style="font-size:0.88rem;font-weight:600;">
                    <i class="fas fa-star text-warning me-1"></i>Mark as Featured
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3" style="font-weight:700;font-size:1rem;">
            <i class="fas fa-save me-2"></i>Update Product
        </button>
        <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
    </div>
</div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('imagePreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
document.getElementById('addSpec').addEventListener('click', function() {
    const c = document.getElementById('specsContainer');
    const row = document.createElement('div');
    row.className = 'spec-row row g-2 mb-2';
    row.innerHTML = '<div class="col-5"><input type="text" name="spec_keys[]" class="form-control form-control-sm" placeholder="Spec name"></div><div class="col-6"><input type="text" name="spec_values[]" class="form-control form-control-sm" placeholder="Spec value"></div><div class="col-1"><button type="button" onclick="this.closest(\'.spec-row\').remove()" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:1rem;padding:6px;"><i class="fas fa-times"></i></button></div>';
    c.appendChild(row);
});
</script>

<?php require_once 'footer.php'; ?>
