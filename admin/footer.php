    </div><!-- end admin-main -->
</div><!-- end admin-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle for mobile
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});

// Show mobile toggle on small screens
function checkWidth() {
    const toggle = document.getElementById('sidebarToggle');
    if (window.innerWidth <= 768 && toggle) toggle.style.display = 'flex';
    else if (toggle) toggle.style.display = 'none';
}
window.addEventListener('resize', checkWidth);
checkWidth();

// Auto-dismiss alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => {
        a.style.opacity = '0';
        a.style.transition = 'opacity 0.5s';
        setTimeout(() => a.remove(), 500);
    });
}, 4000);
</script>
</body>
</html>
