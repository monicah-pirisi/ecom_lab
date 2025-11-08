$(document).ready(function() {
    $('#login-form').submit(function(e) {
        e.preventDefault();

        // Get form values
        var email = $('#email').val().trim();
        var password = $('#password').val();

        // Clear previous error messages
        $('.error-message').remove();
        $('.form-control').removeClass('is-invalid');

        // Validation flags
        var isValid = true;
        var errorMessages = [];

        // Email validation
        if (email === '') {
            errorMessages.push('Email is required');
            $('#email').addClass('is-invalid');
            isValid = false;
        } else if (!isValidEmail(email)) {
            errorMessages.push('Please enter a valid email address');
            $('#email').addClass('is-invalid');
            isValid = false;
        }

        // Password validation
        if (password === '') {
            errorMessages.push('Password is required');
            $('#password').addClass('is-invalid');
            isValid = false;
        } else if (password.length < 6) {
            errorMessages.push('Password must be at least 6 characters long');
            $('#password').addClass('is-invalid');
            isValid = false;
        }

        // If validation fails, show error messages
        if (!isValid) {
            showValidationErrors(errorMessages);
            return;
        }

        // Show loading state
        var submitBtn = $('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Logging in...');

        // AJAX request to login
        $.ajax({
            url: '../actions/login_customer_action.php',
            type: 'POST',
            data: {
                email: email,
                password: password
            },
            dataType: 'json',
            success: function(response) {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        // Redirect to dashboard
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = '../dashboard.php';
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Failed',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                // Reset button state
                submitBtn.prop('disabled', false).html(originalText);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while logging in. Please try again.'
                });
                
                console.error('Login error:', error);
            }
        });
    });

    // Real-time validation
    $('#email').on('blur', function() {
        var email = $(this).val().trim();
        if (email !== '' && !isValidEmail(email)) {
            $(this).addClass('is-invalid');
            showFieldError($(this), 'Please enter a valid email address');
        } else {
            $(this).removeClass('is-invalid');
            hideFieldError($(this));
        }
    });

    $('#password').on('blur', function() {
        var password = $(this).val();
        if (password !== '' && password.length < 6) {
            $(this).addClass('is-invalid');
            showFieldError($(this), 'Password must be at least 6 characters long');
        } else {
            $(this).removeClass('is-invalid');
            hideFieldError($(this));
        }
    });

    // Clear validation on input
    $('.form-control').on('input', function() {
        $(this).removeClass('is-invalid');
        hideFieldError($(this));
    });
});

// Email validation function using regex
function isValidEmail(email) {
    var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

// Show validation errors
function showValidationErrors(errors) {
    var errorHtml = '<div class="alert alert-danger error-message mt-3">';
    errorHtml += '<ul class="mb-0">';
    errors.forEach(function(error) {
        errorHtml += '<li>' + error + '</li>';
    });
    errorHtml += '</ul></div>';
    
    $('#login-form').after(errorHtml);
}

// Show field-specific error
function showFieldError(field, message) {
    hideFieldError(field);
    field.after('<div class="invalid-feedback field-error">' + message + '</div>');
}

// Hide field-specific error
function hideFieldError(field) {
    field.siblings('.field-error').remove();
}
