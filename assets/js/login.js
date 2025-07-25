const container = document.querySelector('.container');
const registerBtn = document.querySelector('.register-btn');
const loginBtn = document.querySelector('.login-btn');
const togglePasswordButtons = document.querySelectorAll('.togglePassword');
const passwordInputs = document.querySelectorAll('.password');

registerBtn.addEventListener('click', () =>{
    container.classList.add('active');
});

loginBtn.addEventListener('click', () =>{
    container.classList.remove('active');
});

togglePasswordButtons.forEach((button, index) => {
    button.addEventListener("click", function() {
        const passwordInput = passwordInputs[index];
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
});

$(document).ready(function() {
    // Phone number masking
    $('input[name="phoneNo"]').inputmask({
        mask: '0399-9999999',  // Adjust this format as needed
        placeholder: '_',
        showMaskOnHover: true,
        showMaskOnFocus: true,
    });
});