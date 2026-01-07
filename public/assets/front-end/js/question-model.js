let pendingQuestion = '';

$('#startChatBtn').on('click', function (e) {
    e.preventDefault();

    pendingQuestion = $('#userQuestion').val().trim();

    if (!pendingQuestion) {
        alert('Please enter your question');
        return;
    }

    if (window.isCustomerLoggedIn) {
        startChat(pendingQuestion);
    } else {
        var emailModal = new bootstrap.Modal(document.getElementById('guestEmailModal'));
        emailModal.show();
    }
});

$('#guestEmailForm').on('submit', function (e) {
    e.preventDefault();

    let email = $('#guestEmail').val().trim();
    let guestCheckout = $('#guest-chat-route').data('url');
    let question = window.pendingQuestionForPayment || $('#userQuestion').val().trim();
    if (!email || !email.includes('@')) {
        alert('Please enter a valid email');
        return;
    }
    $('#loading').removeClass('d--none');

    $.ajax({
        url: guestCheckout,
        type: "POST",
        data: {
            email: email,
            question: question,
            _token: window.csrfToken
        },
     success: function (res) {
    $('#loading').addClass('d--none');

    if (res.success) {
        toastr.success(res.message || 'Success');

        bootstrap.Modal
            .getInstance(document.getElementById('guestEmailModal'))
            .hide();

        if (res.payment_url) {
            window.location.href = res.payment_url;
        }
    } else {
        toastr.error(res.message || 'Unable to process your request. Please try again.');
    }
},

error: function (xhr) {
    $('#loading').addClass('d--none');

    let errorMsg = 'Network error. Please try again.';

    if (xhr.responseJSON) {
        if (xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
        } else if (xhr.responseJSON.errors) {
            // Laravel validation errors handle
            Object.values(xhr.responseJSON.errors).forEach(function (errArr) {
                errArr.forEach(function (msg) {
                    toastr.error(msg);
                });
            });
            return;
        }
    }

    toastr.error(errorMsg);
}

    });
});

function startChat(question) {
    let startChatRoute = $('#start-chat-route').data('url');

    $('#loading').removeClass('d--none');
    $.ajax({
        url: startChatRoute,
        type: "POST",
        data: {
            question: question,
            _token: window.csrfToken
        },
        success: function (res) {
            $('#loading').addClass('d--none');
            if (res.success && res.payment_url) {
                window.location.href = res.payment_url;
            } else {
                alert(res.message || 'Something went wrong');
            }
        },
        error: function () {
            $('#loading').addClass('d--none');
            alert('Error. Try again.');
        }
    });
}