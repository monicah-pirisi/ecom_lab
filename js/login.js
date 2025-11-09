$(document).ready(function() {
    $('#login-form').submit(function(e) {
        e.preventDefault();

        var email = $('#email').val().trim();
        var password = $('#password').val();

        $('.error-message').remove();
        $('.form-control').removeClass('is-invalid');

        var isValid = true;
        var errorMessages = [];

        if (email === '') {
            errorMessages.push('Email is required');
            $('#email').addClass('is-invalid');
            isValid = false;
        } else if (!isValidEmail(email)) {
            errorMessages.push('Please enter a valid email address');
            $('#email').addClass('is-invalid');
            isValid = false;
        }

        if (password === '') {
            errorMessages.push('Password is required');
            $('#password').addClass('is-invalid');
            isValid = false;
        } else if (password.length < 6) {
            errorMessages.push('Password must be at least 6 characters long');
            $('#password').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            showValidationErrors(errorMessages);
            return;
        }

        var submitBtn = $('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Logging in...');

        // Get CSRF token from hidden input
        var csrfToken = $('input[name="csrf_token"]').val();

        $.ajax({
            url: '../actions/login_customer_action.php',
            type: 'POST',
            data: { 
                email: email, 
                password: password, 
                csrf_token: csrfToken // include CSRF token here
            },
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success', 
                        title: 'Success!', 
                        text: response.message, 
                        timer: 2000, 
                        showConfirmButton: false
                    }).then(function() {
                        window.location.href = response.redirect || '../dashboard.php';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Login Failed', text: response.message });
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);
                Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred while logging in. Please try again.' });
                console.error('Login error:', error);
            }
        });
    });

    function isValidEmail(email) {
        return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
    }

    function showValidationErrors(errors) {
        var html = '<div class="alert alert-danger error-message mt-3"><ul>';
        errors.forEach(function(e){ html += '<li>'+e+'</li>'; });
        html += '</ul></div>';
        $('#login-form').after(html);
    }
});
