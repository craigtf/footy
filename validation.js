document.addEventListener('DOMContentLoaded', function () {
    const profanityList = [];
    const usernameInput = document.getElementById('username');
    const usernameError = document.getElementById('usernameError');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const registerButton = document.getElementById('registerButton');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');

    const replacements = {
        '0': 'o',
        '1': 'i',
        '2': 'z',
        '3': 'e',
        '4': 'a',
        '5': 's',
        '6': 'g',
        '7': 't',
        '8': 'b',
        '9': 'p',
        '@': 'a',
        '$': 's',
        '!': 'i',
        '|': 'i'
    };

    fetch('profanity.txt')
        .then(response => response.text())
        .then(text => {
            profanityList.push(...text.split('\n').map(word => word.trim().toLowerCase()));
        });

    function clearForm() {
        usernameInput.value = '';
        firstNameInput.value = '';
        lastNameInput.value = '';
        emailInput.value = '';
        passwordInput.value = '';
        confirmPasswordInput.value = '';
        usernameError.textContent = '';
        emailError.textContent = '';
        passwordError.textContent = '';
        confirmPasswordError.textContent = '';
        registerButton.disabled = true;
    }

    function debounce(func, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    async function validateUsername() {
        const username = usernameInput.value.trim().toLowerCase();
        usernameError.textContent = '';

        if (username.length < 3 || username.length > 10) {
            setError(usernameError, 'Username must be between 3 and 10 characters long.');
            return false;
        }

        if (/[^a-z0-9_]/i.test(username)) {
            setError(usernameError, 'Username can only contain letters, numbers, and underscores.');
            return false;
        }

        const normalizedUsername = normalizeUsername(username);

        if (containsProfanity(normalizedUsername)) {
            setError(usernameError, 'Username contains profane words.');
            return false;
        }

        try {
            const response = await fetch(`validate_username.php?username=${encodeURIComponent(username)}`);
            const data = await response.json();
            if (data.error || !data.available) {
                setError(usernameError, data.error ? 'Error checking username availability.' : 'Username is already taken.');
                return false;
            } else {
                setValid(usernameError, 'Username is available.');
                return true;
            }
        } catch (error) {
            setError(usernameError, 'Error checking username availability.');
            return false;
        }
    }

    function normalizeUsername(username) {
        return username.replace(/[0123456789@$!|]/g, char => replacements[char] || char);
    }

    function containsProfanity(username) {
        for (let profaneWord of profanityList) {
            let normalizedProfaneWord = normalizeUsername(profaneWord);
            let profanePattern = new RegExp(normalizedProfaneWord.split('').join('.*'), 'i');
            if (profanePattern.test(username)) {
                return true;
            }
        }
        return false;
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        if (!email) {
            setError(emailError, 'Email is required.');
            return false;
        }
        if (!/\S+@\S+\.\S+/.test(email)) {
            setError(emailError, 'Invalid email address.');
            return false;
        }
        setValid(emailError, '');
        return true;
    }

    function validatePassword() {
        const password = passwordInput.value;
        if (password.length < 8) {
            setError(passwordError, 'Password must be at least 8 characters long.');
            return false;
        }
        if (!/[A-Z]/.test(password) || !/\d/.test(password) || /[^a-zA-Z\d]/.test(password)) {
            setError(passwordError, 'Password must contain at least one uppercase letter, one number, and no special characters.');
            return false;
        } else {
            setValid(passwordError, '');
            return true;
        }
    }

    function validateConfirmPassword() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        if (password !== confirmPassword) {
            setError(confirmPasswordError, 'Passwords do not match.');
            return false;
        } else {
            setValid(confirmPasswordError, '');
            return true;
        }
    }

    async function validateForm() {
        const isUsernameValid = await validateUsername();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isConfirmPasswordValid = validateConfirmPassword();
        registerButton.disabled = !(isUsernameValid && isEmailValid && isPasswordValid && isConfirmPasswordValid);
    }

    function setError(element, message) {
        if (element.textContent !== message) {
            element.textContent = message;
            element.classList.add('error');
            element.classList.remove('valid');
        }
    }

    function setValid(element, message) {
        if (element.textContent !== message) {
            element.textContent = message;
            element.classList.remove('error');
            element.classList.add('valid');
        }
    }

    // Clear form on page load
    clearForm();

    usernameInput.addEventListener('input', debounce(validateForm, 300));
    firstNameInput.addEventListener('input', validateForm);
    lastNameInput.addEventListener('input', validateForm);
    emailInput.addEventListener('input', validateForm);
    passwordInput.addEventListener('input', validateForm);
    confirmPasswordInput.addEventListener('input', validateForm);

    registrationForm.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent form submission until validation is complete
        validateForm().then(async () => {
            if (registerButton.disabled) {
                return;
            }

            // Perform final email check before submitting the form
            const email = emailInput.value.trim();
            try {
                const response = await fetch(`check_email.php?email=${encodeURIComponent(email)}`);
                const data = await response.json();
                if (!data.available) {
                    setError(emailError, 'Email is already in use.');
                    registerButton.disabled = true;
                } else {
                    registrationForm.submit(); // Submit the form if email is available
                }
            } catch (error) {
                setError(emailError, 'Error checking email availability.');
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const loginError = document.getElementById('loginError');

    loginForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const formData = new FormData(loginForm);
        const response = await fetch('login.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.error) {
            loginError.style.display = 'block';
            loginError.classList.add('error'); // Ensure the error class is added
            loginError.textContent = result.error;
        } else if (result.success) {
            window.location.href = 'dashboard.php';
        }
    });
});
