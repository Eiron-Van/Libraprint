document.addEventListener("DOMContentLoaded", () => {

    const passwordInput = document.getElementById('password');
    const showPasswordCheckbox = document.getElementById('show-password');

    showPasswordCheckbox.addEventListener('change', (e) => {
        if (e.target.checked) {
        passwordInput.type = 'text';
        } else {
        passwordInput.type = 'password';
        }
    });

});

