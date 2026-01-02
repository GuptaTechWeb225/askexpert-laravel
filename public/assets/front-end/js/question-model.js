$('#startChatBtn').on('click', function () {
    let question = $('#userQuestion').val().trim();

    if (!question) {
        alert('Please enter your question');
        return;
    }

    // If user is authenticated
    if (window.isCustomerLoggedIn) {
        startChat(question);
    } else {
        // Get route URLs from span tags
        let returnUrlRoute = $('#store-return-url-route').data('url');

        $.post(returnUrlRoute, {
            _token: window.csrfToken,
            return_url: window.location.href,
            pending_question: question // raw question
        });

        var authModal = new bootstrap.Modal(document.getElementById('authRequiredModal'));
        authModal.show();
    }
});



$('#startChatBotBtn').on('click', function () {
    let question = $('#userChatQuestion').val().trim();

    if (!question) {
        alert('Please enter your question');
        return;
    }

    // If user is authenticated
    if (window.isCustomerLoggedIn) {
        startChat(question);
    } else {
        // Get route URLs from span tags
        let returnUrlRoute = $('#store-return-url-route').data('url');

        $.post(returnUrlRoute, {
            _token: window.csrfToken,
            return_url: window.location.href,
            pending_question: question // raw question
        });

        var authModal = new bootstrap.Modal(document.getElementById('authRequiredModal'));
        authModal.show();
    }
});

// Function to call startChat AJAX
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
            if (res.success) {
                if (res.requires_payment) {
                    window.location.href = res.payment_url;
                } else {
                    window.location.href = res.redirect_url;
                }
            } else {
                alert(res.message || 'Something went wrong');
                
            }
        },
        error: function () {
            $('#loading').addClass('d--none');
            alert('Something went wrong. Try again.');
        }
    });
}


document.getElementById('cancelAuthFlow').addEventListener('click', function () {
    let sessionUrl = $('#session-clear-route').data('url');

    fetch(sessionUrl, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": window.csrfToken,
            "Accept": "application/json"
        }
    }).finally(() => {
        bootstrap.Modal.getInstance(
            document.getElementById('authRequiredModal')
        ).hide();
    });

});