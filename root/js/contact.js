$('#contact form').submit(function(e) {
    e.preventDefault();
    var name = $('#ctc_name').val();
    var email = $('#ctc_email').val();
    var message = $('#ctc_msg').val();
    var sendButton = $('#ctc');

    $(this)[0].reset();
    sendButton.text('Thanks for contacting!');
    $.ajax({
        type: 'POST',
        url: 'contact.php',
        data: {
            'name': name,
            'email': email,
            'message': message
        }
    });
});

function ctcValidation() {
    var email = document.getElementById('ctc_email');
    var message = document.getElementById('ctc_msg');
    var valid = true;

    // Message Validation
    if (message.value == null || (message.value).length == 0 || /^\s+$/.test(message.value)) {
        message.focus();
        message.className = 'error';
        valid = false;
    } else message.className = '';

    // Email Validation
    if (!(/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.([a-zA-Z]{2,4})+$/.test(email.value))) {
        email.focus();
        email.className = 'error';
        valid = false;
    } else email.className = '';

    return valid;
}

// Validate Contact
var ctcButton = document.getElementById('ctc');
ctcButton.onclick = ctcValidation;