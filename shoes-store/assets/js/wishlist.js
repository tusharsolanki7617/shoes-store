/**
 * Wishlist Functionality
 */

document.addEventListener('DOMContentLoaded', function () {

    // Toggle Wishlist
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.wishlist-btn');
        if (!btn) return;

        console.log('Wishlist button clicked', btn.dataset.productId); // Debug log

        e.preventDefault();

        const productId = btn.dataset.productId;
        const icon = btn.querySelector('.fa-heart'); // More specific selector

        if (!icon) {
            console.error('Wishlist icon not found inside button');
            return;
        }

        // Add animation class
        icon.classList.add('fa-beat');

        fetch('ajax/toggle-wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
            .then(response => response.json())
            .then(data => {
                icon.classList.remove('fa-beat');

                if (data.success) {
                    if (data.action === 'added') {
                        icon.classList.remove('far');
                        icon.classList.add('fas', 'text-danger');
                        showToast('Added to Wishlist');
                    } else {
                        icon.classList.remove('fas', 'text-danger');
                        icon.classList.add('far');
                        showToast('Removed from Wishlist');
                    }

                    // Update header count
                    updateWishlistCount(data.count);
                } else {
                    if (data.message.includes('login')) {
                        window.location.href = 'user/login.php';
                    } else {
                        showToast(data.message, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                icon.classList.remove('fa-beat');
            });
    });

    // Function to update header count
    function updateWishlistCount(count) {
        const badge = document.querySelector('.wishlist-count');
        const iconLink = document.querySelector('.wishlist-icon');

        if (count > 0) {
            if (badge) {
                badge.textContent = count;
            } else if (iconLink) {
                const newBadge = document.createElement('span');
                newBadge.className = 'cart-badge wishlist-count rounded-pill';
                newBadge.style = 'background:var(--warning); right: -5px;';
                newBadge.textContent = count;
                iconLink.appendChild(newBadge);
            }
        } else {
            if (badge) badge.remove();
        }
    }

    // Helper toast function (if sweetalert is available, use that, else custom)
    function showToast(message, type = 'success') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        } else {
            alert(message);
        }
    }
});
