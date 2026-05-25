$(document).ready(function () {
    // Redirect if already logged in
    if (localStorage.getItem('sessionToken')) {
        window.location.href = 'profile.html';
        return;
    }

    $('#loginForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            dataType: 'json',
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function (response) {
                showAlert(response.success ? 'success' : 'danger', response.message);

                if (response.success) {
                    localStorage.setItem('sessionToken', response.token);
                    localStorage.setItem('userEmail', response.user.email);
                    localStorage.setItem('userId', response.user.id);

                    setTimeout(function () {
                        window.location.href = 'profile.html';
                    }, 800);
                }
            },
            error: function () {
                showAlert('danger', 'Server error. Please try again.');
            }
        });
    });

    function showAlert(type, message) {
        $('#loginAlert')
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }
});
