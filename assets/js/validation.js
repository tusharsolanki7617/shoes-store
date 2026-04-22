/* ============================================
   KICKS & COMFORT - Form Validation (jQuery Plugin)
   ============================================ */

$(document).ready(function () {
    console.log("Validation script loaded and ready");

    // ========================================
    // 1. GLOBAL DEFAULTS (Bootstrap 5 Support)
    // ========================================
    if ($.validator) {
        $.validator.setDefaults({
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            },
            errorPlacement: function (error, element) {
                if (element.parent('.input-group').length ||
                    element.prop('type') === 'checkbox' ||
                    element.prop('type') === 'radio') {
                    error.insertAfter(element.parent());
                } else if (element.hasClass('select2-hidden-accessible')) {
                    error.insertAfter(element.next('.select2'));
                } else {
                    error.insertAfter(element);
                }
            }
        });

        // Custom Methods
        $.validator.addMethod("strongPassword", function (value, element) {
            return this.optional(element) || (value.length >= 8 && /[A-Z]/.test(value) && /[a-z]/.test(value) && /[0-9]/.test(value));
        }, "Password must contain at least 8 chars, 1 uppercase, 1 lowercase, and 1 number");
    }

    // ========================================
    // 2. USER FORMS
    // ========================================

    // Login Form
    $('#loginForm').validate({
        rules: {
            email: { required: true, email: true },
            password: { required: true }
        },
        messages: {
            email: { required: "Please enter your email", email: "Please enter a valid email address" },
            password: { required: "Please enter your password" }
        }
    });

    // Registration Form
    $('#registerForm').validate({
        rules: {
            first_name: { required: true, minlength: 2 },
            last_name: { required: true, minlength: 2 },
            email: { required: true, email: true },
            password: { required: true, strongPassword: true },
            confirm_password: { required: true, equalTo: "#password" }
        },
        messages: {
            confirm_password: { equalTo: "Passwords do not match" }
        }
    });

    // Contact Form
    $('#contactForm').validate({
        rules: {
            name: { required: true, minlength: 3 },
            email: { required: true, email: true },
            subject: { required: true, minlength: 5 },
            message: { required: true, minlength: 10 }
        }
    });

    // Profile Form
    $('#profileForm').validate({
        rules: {
            first_name: { required: true, minlength: 2 },
            last_name: { required: true, minlength: 2 },
            phone: { required: true, digits: true, minlength: 10, maxlength: 10 }
        },
        messages: {
            phone: "Please enter a valid 10-digit mobile number"
        }
    });

    // Change Password Form
    $('#changePasswordForm').validate({
        rules: {
            current_password: { required: true },
            new_password: { required: true, strongPassword: true },
            confirm_password: { required: true, equalTo: "#new_password" }
        }
    });

    // Forgot Password Form
    $('#forgotPasswordForm').validate({
        rules: {
            email: { required: true, email: true }
        }
    });

    // Reset Password Form
    $('#resetPasswordForm').validate({
        rules: {
            password: { required: true, strongPassword: true },
            confirm_password: { required: true, equalTo: "#password" }
        }
    });

    // Review Form (Product Detail)
    $('#reviewForm').validate({
        rules: {
            rating: { required: true },
            review: { required: true, minlength: 10 }
        },
        messages: {
            rating: "Please select a star rating",
            review: "Please write at least 10 characters"
        },
        errorPlacement: function (error, element) {
            if (element.attr("name") == "rating") {
                error.appendTo(element.closest('.rating-css').parent());
            } else {
                error.insertAfter(element);
            }
        }
    });

    // Checkout Form
    $('#checkoutForm').validate({
        rules: {
            first_name: { required: true },
            last_name: { required: true },
            email: { required: true, email: true },
            phone: { required: true, digits: true, minlength: 10, maxlength: 10 },
            shipping_address: { required: true, minlength: 10 },
            city: { required: true },
            state: { required: true },
            pincode: { required: true, digits: true, minlength: 6, maxlength: 6 },
            payment_method: { required: true }
        },
        messages: {
            phone: "Please enter a valid 10-digit mobile number",
            pincode: "Please enter a valid 6-digit pincode"
        }
    });

    // ========================================
    // 3. ADMIN FORMS
    // ========================================

    // Admin: Add/Edit Product Form
    $('#productForm').validate({
        rules: {
            name: { required: true, minlength: 3 },
            price: { required: true, number: true, min: 0 },
            stock: { required: true, digits: true, min: 0 },
            category_id: { required: true },
            description: { required: true }
        }
    });

    // Coupon Form
    $('#couponForm').validate({
        rules: {
            code: { required: true, minlength: 3 },
            discount_type: { required: true },
            discount_value: { required: true, number: true, min: 0 },
            min_purchase: { number: true, min: 0 },
            valid_until: { required: true, date: true }
        }
    });

});
