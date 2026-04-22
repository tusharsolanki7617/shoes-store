</main>

<!-- jQuery -->
<script src="<?= ASSETS_URL ?>js/lib/jquery.min.js"></script>

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery Validation -->
<script src="<?= ASSETS_URL ?>js/lib/jquery.validate.min.js"></script>

<!-- Custom Validation -->
<script src="<?= ASSETS_URL ?>js/validation.js"></script>

<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Close sidebar on outside click (mobile)
    $(document).on('click', function(e) {
        if ($(window).width() < 992) {
            if (!$(e.target).closest('.admin-sidebar, .header-hamburger').length) {
                var sidebar = document.querySelector('.admin-sidebar');
                var backdrop = document.getElementById('sidebarBackdrop');
                if (sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    if (backdrop) backdrop.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }
        }
    });

    // Animate stat cards on load
    $('.animate-in').each(function(i) {
        $(this).css({ opacity: 0 });
        setTimeout(() => {
            $(this).css({ animation: 'fadeInUp 0.35s ease forwards' });
        }, i * 60);
    });
});
</script>

</body>
</html>
