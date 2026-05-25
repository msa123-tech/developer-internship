$(document).ready(function () {
    $('#registerForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            dataType: 'json',
            data: {
                full_name: $('#full_name').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            },
            success: function (response) {
                showAlert(response.success ? 'success' : 'danger', response.message);

                if (response.success) {
                    setTimeout(function () {
                        window.location.href = 'login.html';
                    }, 1200);
                }
            },
            error: function (xhr) {
                var message = 'Server error. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed.message) {
                            message = parsed.message;
                        }
                    } catch (e) {
                        // keep default message
                    }
                }

                showAlert('danger', message);
            }
        });
    });

    function showAlert(type, message) {
        $('#registerAlert')
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }
});
