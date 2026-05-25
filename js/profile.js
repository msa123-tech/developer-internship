$(document).ready(function () {
    var token = localStorage.getItem('sessionToken');

    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    loadProfile();

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            dataType: 'json',
            data: {
                token: token,
                action: 'update',
                full_name: $('#full_name').val(),
                phone: $('#phone').val(),
                age: $('#age').val(),
                dob: $('#dob').val(),
                address: $('#address').val()
            },
            success: function (response) {
                showAlert(response.success ? 'success' : 'danger', response.message);

                if (response.success) {
                    loadProfile();
                }

                if (!response.success && response.message.indexOf('Session') !== -1) {
                    clearLocalSession();
                    window.location.href = 'login.html';
                }
            },
            error: function () {
                showAlert('danger', 'Server error. Please try again.');
            }
        });
    });

    $('#logoutBtn').on('click', function () {
        $.ajax({
            url: 'php/logout.php',
            type: 'POST',
            dataType: 'json',
            data: { token: token },
            complete: function () {
                clearLocalSession();
                window.location.href = 'login.html';
            }
        });
    });

    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            type: 'GET',
            dataType: 'json',
            data: { token: token },
            success: function (response) {
                if (!response.success) {
                    showAlert('danger', response.message);
                    clearLocalSession();
                    window.location.href = 'login.html';
                    return;
                }

                $('#viewEmail').text(response.profile.email);
                $('#viewName').text(response.profile.full_name);
                $('#viewPhone').text(response.profile.phone || '-');
                $('#viewAge').text(response.profile.age || '-');
                $('#viewDob').text(response.profile.dob || '-');
                $('#viewAddress').text(response.profile.address || '-');

                $('#full_name').val(response.profile.full_name);
                $('#phone').val(response.profile.phone);
                $('#age').val(response.profile.age);
                $('#dob').val(response.profile.dob);
                $('#address').val(response.profile.address);
            },
            error: function () {
                showAlert('danger', 'Could not load profile.');
            }
        });
    }

    function clearLocalSession() {
        localStorage.removeItem('sessionToken');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userId');
    }

    function showAlert(type, message) {
        $('#profileAlert')
            .removeClass('d-none alert-success alert-danger')
            .addClass('alert-' + type)
            .text(message)
            .show();
    }
});
