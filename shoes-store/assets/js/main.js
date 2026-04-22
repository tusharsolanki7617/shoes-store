/* ============================================
   KICKS & COMFORT - Main JavaScript
   ============================================ */

$(document).ready(function () {

    // ========================================
    // Get CSRF Token from meta tag or hidden input
    // ========================================
    function getCsrfToken() {
        // Try to get from meta tag first
        let token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            // Fallback to hidden input
            token = $('input[name="csrf_token"]').val();
        }
        if (!token) {
            // Last resort: from any form on the page
            token = $('form input[name="csrf_token"]').first().val();
        }
        return token;
    }

    // ========================================
    // Add to Cart (AJAX)
    // ========================================
    $('.add-to-cart').on('click', function (e) {
        e.preventDefault();

        const btn = $(this);
        const productId = btn.data('product-id');
        const quantity = $('#quantity').val() || 1;

        // Size validation — only enforce if a size selector exists on this page
        const sizeSelector = $('input[name="size"]');
        const selectedSize = sizeSelector.length ? sizeSelector.filter(':checked').val() : null;

        // Reset error
        $('#sizeError').addClass('d-none');

        if (sizeSelector.length && !selectedSize) {
            // Show error only when size options exist but none is chosen
            $('#sizeError').removeClass('d-none');
            $('#sizeSelector').addClass('animate__animated animate__shakeX');
            setTimeout(() => {
                $('#sizeSelector').removeClass('animate__animated animate__shakeX');
            }, 1000);
            return;
        }


        // Disable button
        btn.prop('disabled', true);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm"></span> Adding...');

        $.ajax({
            url: BASE_URL + 'ajax/add-to-cart.php',
            method: 'POST',
            data: {
                product_id: productId,
                quantity: quantity,
                size: selectedSize,
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount();

                    // Cart icon bounce animation
                    $('.cart-icon').addClass('cart-bounce');
                    setTimeout(() => {
                        $('.cart-icon').removeClass('cart-bounce');
                    }, 300);

                    // Show success toast
                    showToast('success', response.message || 'Product added to cart!');

                    // Reset button
                    btn.html(originalText);
                    btn.prop('disabled', false);
                } else {
                    showToast('error', response.message || 'Failed to add product');
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            },
            error: function () {
                showToast('error', 'An error occurred. Please try again.');
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // ========================================
    // Update Cart Count
    // ========================================
    function updateCartCount() {
        $.ajax({
            url: BASE_URL + 'ajax/get-cart-count.php',
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.count !== undefined) {
                    $('.cart-count').text(response.count);
                    if (response.count > 0) {
                        $('.cart-badge').show();
                    } else {
                        $('.cart-badge').hide();
                    }
                }
            }
        });
    }

    // ========================================
    // Update Cart Quantity
    // ========================================
    $('.cart-quantity').on('change', function () {
        const input = $(this);
        const itemKey = input.data('item-key');
        const quantity = input.val();

        $.ajax({
            url: BASE_URL + 'ajax/update-cart.php',
            method: 'POST',
            data: {
                item_key: itemKey,
                quantity: quantity,
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Update prices
                    location.reload(); // Reload to update totals
                } else {
                    showToast('error', response.message || 'Failed to update quantity');
                    input.val(response.old_quantity || 1);
                }
            },
            error: function () {
                showToast('error', 'An error occurred');
            }
        });
    });

    // ========================================
    // Remove from Cart
    // ========================================
    $('.remove-from-cart').on('click', function (e) {
        e.preventDefault();

        const btn = $(this);
        const itemKey = btn.data('item-key');
        const row = btn.closest('tr');

        Swal.fire({
            title: 'Remove Item?',
            text: "Do you want to remove this item from your cart?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + 'ajax/remove-from-cart.php',
                    method: 'POST',
                    data: {
                        item_key: itemKey,
                        csrf_token: getCsrfToken()
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            row.fadeOut(300, function () {
                                $(this).remove();
                                location.reload(); // Reload to update totals
                            });
                            Swal.fire(
                                'Removed!',
                                'Item has been removed from your cart.',
                                'success'
                            )
                        } else {
                            showToast('error', 'Failed to remove item');
                        }
                    },
                    error: function () {
                        showToast('error', 'An error occurred');
                    }
                });
            }
        });
    });

    // ========================================
    // Live Search
    // ========================================
    let searchTimeout;
    $('#searchInput').on('input', function () {
        const query = $(this).val();

        clearTimeout(searchTimeout);

        if (query.length >= 2) {
            searchTimeout = setTimeout(function () {
                // Perform search (redirect or AJAX)
                window.location.href = BASE_URL + 'search.php?q=' + encodeURIComponent(query);
            }, 500);
        }
    });

    // ========================================
    // Toast Notification System
    // ========================================
    function showToast(type, message, duration = 3000) {
        const iconMap = {
            'success': '<i class="fas fa-check-circle"></i>',
            'error': '<i class="fas fa-times-circle"></i>',
            'warning': '<i class="fas fa-exclamation-triangle"></i>',
            'info': '<i class="fas fa-info-circle"></i>'
        };

        const colorMap = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning',
            'info': 'bg-info'
        };

        const toast = $(`
            <div class="toast-notification ${colorMap[type]} text-white toast-enter" role="alert">
                <div class="d-flex align-items-center">
                    <span class="me-2">${iconMap[type]}</span>
                    <span>${message}</span>
                </div>
            </div>
        `);

        // Create toast container if it doesn't exist
        if ($('#toastContainer').length === 0) {
            $('body').append('<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
        }

        $('#toastContainer').append(toast);

        // Auto remove after duration
        setTimeout(function () {
            toast.removeClass('toast-enter').addClass('toast-exit');
            setTimeout(function () {
                toast.remove();
            }, 300);
        }, duration);
    }

    // Make showToast globally available
    window.showToast = showToast;

    // ========================================
    // Smooth Scroll
    // ========================================
    $('a[href^="#"]').on('click', function (e) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // ========================================
    // Quantity Increment/Decrement
    // ========================================
    $('.qty-increase').on('click', function () {
        const input = $(this).siblings('.qty-input');
        const max = parseInt(input.attr('max')) || 100;
        const current = parseInt(input.val()) || 1;
        if (current < max) {
            input.val(current + 1).trigger('change');
        }
    });

    $('.qty-decrease').on('click', function () {
        const input = $(this).siblings('.qty-input');
        const min = parseInt(input.attr('min')) || 1;
        const current = parseInt(input.val()) || 1;
        if (current > min) {
            input.val(current - 1).trigger('change');
        }
    });

    // ========================================
    // Image Gallery (Product Detail)
    // ========================================
    $('.gallery-thumb').on('click', function () {
        const newSrc = $(this).attr('src');
        $('#mainImage').attr('src', newSrc);
        $('.gallery-thumb').removeClass('active');
        $(this).addClass('active');
    });

    // ========================================
    // Apply Coupon
    // ========================================
    $('#applyCoupon').on('click', function () {
        const code = $('#couponCode').val().trim();

        if (!code) {
            showToast('warning', 'Please enter a coupon code');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).text('Applying...');

        $.ajax({
            url: BASE_URL + 'ajax/apply-coupon.php',
            method: 'POST',
            data: {
                code: code,
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    showToast('success', response.message);
                    location.reload(); // Reload to show discount
                } else {
                    showToast('error', response.message);
                    btn.prop('disabled', false).text('Apply');
                }
            },
            error: function () {
                showToast('error', 'An error occurred');
                btn.prop('disabled', false).text('Apply');
            }
        });
    });

    // ========================================
    // Price Range Filter
    // ========================================
    $('#priceRange').on('input', function () {
        const value = $(this).val();
        $('#priceValue').text('₹' + value);
    });

    // ========================================
    // Auto-hide alerts
    // ========================================
    setTimeout(function () {
        $('.alert').fadeOut(300);
    }, 5000);

    // ========================================
    // Scroll Reveal Animation (Intersection Observer)
    // ========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Only animate once
            }
        });
    }, observerOptions);

    // Elements to animate
    $('.reveal, .product-card, .category-card, .section-title').each(function () {
        $(this).addClass('reveal-hidden');
        observer.observe(this);
    });

    // ========================================
    // Initialize on load
    // ========================================
    updateCartCount();
});
