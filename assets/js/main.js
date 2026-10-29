/**
 * ElectroHub - Main JavaScript
 * Handles cart interactions, UI effects, and AJAX calls
 */

const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || '';

// ── Toast Notifications ──────────────────────
const toastContainer = (() => {
    const el = document.createElement('div');
    el.className = 'toast-container-custom';
    document.body.appendChild(el);
    return el;
})();

function showToast(message, type = 'info', duration = 3000) {
    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-circle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${message}</span>`;
    toastContainer.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ── Cart Functionality ────────────────────────
async function addToCart(productId, quantity = 1, btn = null) {
    if (btn) {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
        btn.disabled = true;
        btn.dataset.original = original;
    }

    try {
        const resp = await fetch(`${SITE_URL}/ajax/cart.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        });
        const data = await resp.json();
        if (data.success) {
            showToast(data.message || 'Added to cart!', 'success');
            updateCartCount(data.cart_count);
        } else {
            showToast(data.message || 'Could not add to cart.', 'error');
        }
    } catch (e) {
        showToast('Something went wrong. Please try again.', 'error');
    }

    if (btn) {
        setTimeout(() => {
            btn.innerHTML = btn.dataset.original || '<i class="fas fa-shopping-cart me-1"></i> Add to Cart';
            btn.disabled = false;
        }, 800);
    }
}

async function updateCartQty(productId, quantity) {
    const resp = await fetch(`${SITE_URL}/ajax/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    });
    const data = await resp.json();
    if (data.success) {
        updateCartCount(data.cart_count);
        return data;
    }
    return null;
}

async function removeFromCart(productId) {
    const resp = await fetch(`${SITE_URL}/ajax/cart.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&product_id=${productId}`
    });
    const data = await resp.json();
    if (data.success) {
        updateCartCount(data.cart_count);
        showToast('Item removed from cart', 'info');
        return true;
    }
    return false;
}

function updateCartCount(count) {
    document.querySelectorAll('.cart-badge').forEach(badge => {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    });
}

// ── Cart Page Interactions ───────────────────
function initCartPage() {
    // Qty increase
    document.querySelectorAll('.qty-increase').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.qty-field[data-product-id="${productId}"]`);
            const newQty = parseInt(input.value) + 1;
            const data = await updateCartQty(productId, newQty);
            if (data) {
                input.value = newQty;
                updateCartRow(productId, newQty, data.item_total, data.cart_total);
            }
        });
    });

    // Qty decrease
    document.querySelectorAll('.qty-decrease').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const input = document.querySelector(`.qty-field[data-product-id="${productId}"]`);
            const newQty = parseInt(input.value) - 1;
            if (newQty < 1) return;
            const data = await updateCartQty(productId, newQty);
            if (data) {
                input.value = newQty;
                updateCartRow(productId, newQty, data.item_total, data.cart_total);
            }
        });
    });

    // Remove buttons
    document.querySelectorAll('.remove-cart-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            const productId = this.dataset.productId;
            const row = document.querySelector(`.cart-row[data-product-id="${productId}"]`);
            const removed = await removeFromCart(productId);
            if (removed) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                row.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    row.remove();
                    checkEmptyCart();
                    // Reload to update totals properly
                    location.reload();
                }, 300);
            }
        });
    });
}

function updateCartRow(productId, qty, itemTotal, cartTotal) {
    const totalEl = document.querySelector(`.item-total[data-product-id="${productId}"]`);
    const cartTotalEl = document.querySelector('.cart-total-amount');
    if (totalEl && itemTotal) totalEl.textContent = itemTotal;
    if (cartTotalEl && cartTotal) cartTotalEl.textContent = cartTotal;
}

function checkEmptyCart() {
    const rows = document.querySelectorAll('.cart-row');
    if (rows.length === 0) {
        location.reload();
    }
}

// ── Product Detail Quantity ───────────────────
function initQtyControls() {
    const qtyInput = document.getElementById('qty');
    if (!qtyInput) return;

    document.querySelector('.qty-plus')?.addEventListener('click', () => {
        const max = parseInt(qtyInput.max) || 99;
        if (parseInt(qtyInput.value) < max) qtyInput.value = parseInt(qtyInput.value) + 1;
    });

    document.querySelector('.qty-minus')?.addEventListener('click', () => {
        if (parseInt(qtyInput.value) > 1) qtyInput.value = parseInt(qtyInput.value) - 1;
    });

    // Product detail add to cart
    document.getElementById('addToCartBtn')?.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const qty = parseInt(qtyInput.value) || 1;
        addToCart(productId, qty, this);
    });
}

// ── Image Gallery ─────────────────────────────
function initGallery() {
    const mainImg = document.getElementById('mainProductImg');
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', function() {
            document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            if (mainImg) mainImg.src = this.dataset.src || this.src;
        });
    });
}

// ── Price Range Filter ────────────────────────
function initPriceFilter() {
    const range = document.getElementById('priceRange');
    const display = document.getElementById('priceDisplay');
    if (!range || !display) return;
    range.addEventListener('input', () => {
        display.textContent = '$0 - $' + range.value;
    });
}

// ── Back to Top ───────────────────────────────
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
    if (backToTop) {
        if (window.scrollY > 300) backToTop.classList.add('visible');
        else backToTop.classList.remove('visible');
    }
});
backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

// ── Sticky Navbar shadow ───────────────────────
window.addEventListener('scroll', () => {
    const nav = document.querySelector('.main-nav');
    if (nav) {
        if (window.scrollY > 50) nav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.3)';
        else nav.style.boxShadow = 'none';
    }
});

// ── Form Validation ───────────────────────────
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showToast('Please fill in all required fields.', 'warning');
            }
            this.classList.add('was-validated');
        });
    });
}

// ── Product Search Autocomplete (simple) ──────
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;
    // Simple keypress animation
    searchInput.addEventListener('focus', () => {
        searchInput.parentElement.style.boxShadow = '0 0 0 3px rgba(0,102,255,0.2)';
    });
    searchInput.addEventListener('blur', () => {
        searchInput.parentElement.style.boxShadow = 'none';
    });
}

// ── Admin Sidebar Toggle ──────────────────────
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.querySelector('.admin-sidebar')?.classList.toggle('open');
});

// ── Initialize on DOM Ready ───────────────────
document.addEventListener('DOMContentLoaded', () => {
    initCartPage();
    initQtyControls();
    initGallery();
    initPriceFilter();
    initFormValidation();
    initSearch();

    // Add to cart buttons (product listing)
    document.querySelectorAll('.btn-add-cart[data-product-id]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('out-of-stock')) {
                showToast('This product is out of stock.', 'warning');
                return;
            }
            addToCart(this.dataset.productId, 1, this);
        });
    });

    // Animate cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, i * 60);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.product-card, .category-card, .stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        observer.observe(card);
    });
});
