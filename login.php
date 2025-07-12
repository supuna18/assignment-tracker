<?php
session_start();

// Initialize variables
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Here you would typically check against your database
        // For demo purposes, we'll use dummy credentials
        if ($email === 'student@university.edu' && $password === 'password123') {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            
            if ($remember_me) {
                // Set remember me cookie (expires in 30 days)
                setcookie('remember_user', $email, time() + (30 * 24 * 60 * 60), '/');
            }
            
            // Redirect to dashboard or assignment tracker
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = 'Invalid email or password. Please try again.';
        }
    }
}

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

// Check for remember me cookie
$remembered_email = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back - Assignment Tracker</title>
    <link rel="stylesheet" href="css/login-style.css">
    <meta name="description" content="Sign in to your assignment tracker account">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📚</text></svg>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1 class="login-title">
                    📚 Welcome Back
                </h1>
                <p class="login-subtitle">Sign in to access your assignment tracker</p>
            </div>

            <!-- Error Message -->
            <?php if (!empty($error_message)): ?>
                <div class="error-message show">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (!empty($success_message)): ?>
                <div class="success-message show">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <span class="input-icon">📧</span>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="chama@gmail.com"
                            value="<?php echo htmlspecialchars($remembered_email); ?>"
                            required
                            autocomplete="email"
                            aria-describedby="email-error"
                        >
                    </div>
                    <div id="email-error" class="field-error" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            aria-describedby="password-error"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                            👁️
                        </button>
                    </div>
                    <div id="password-error" class="field-error" style="display: none;"></div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember_me" name="remember_me" <?php echo !empty($remembered_email) ? 'checked' : ''; ?>>
                        <label for="remember_me">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn" id="loginBtn">
                    <div class="spinner"></div>
                    <span class="btn-text">🚀 Sign In</span>
                </button>
            </form>

            <div class="register-link">
                Don't have an account? <a href="register.php">Create one here</a>
            </div>
        </div>
    </div>

    <script>
        // Professional form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            // Enhanced real-time validation
            emailInput.addEventListener('blur', validateEmail);
            emailInput.addEventListener('input', clearFieldError.bind(null, 'email-error'));
            passwordInput.addEventListener('blur', validatePassword);
            passwordInput.addEventListener('input', clearFieldError.bind(null, 'password-error'));

            // Professional form submission handling
            form.addEventListener('submit', function(e) {
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();
                
                if (!isEmailValid || !isPasswordValid) {
                    e.preventDefault();
                    return;
                }

                // Show professional loading state
                loginBtn.classList.add('loading');
                loginBtn.disabled = true;
            });

            function validateEmail() {
                const email = emailInput.value.trim();
                const emailError = document.getElementById('email-error');
                
                if (!email) {
                    showFieldError(emailError, 'Email address is required.');
                    addInputErrorState(emailInput);
                    return false;
                } else if (!isValidEmail(email)) {
                    showFieldError(emailError, 'Please enter a valid email address.');
                    addInputErrorState(emailInput);
                    return false;
                } else {
                    hideFieldError(emailError);
                    removeInputErrorState(emailInput);
                    return true;
                }
            }

            function validatePassword() {
                const password = passwordInput.value;
                const passwordError = document.getElementById('password-error');
                
                if (!password) {
                    showFieldError(passwordError, 'Password is required.');
                    addInputErrorState(passwordInput);
                    return false;
                } else if (password.length < 6) {
                    showFieldError(passwordError, 'Password must be at least 6 characters long.');
                    addInputErrorState(passwordInput);
                    return false;
                } else {
                    hideFieldError(passwordError);
                    removeInputErrorState(passwordInput);
                    return true;
                }
            }

            function showFieldError(errorElement, message) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }

            function hideFieldError(errorElement) {
                errorElement.style.display = 'none';
            }

            function clearFieldError(errorId) {
                const errorElement = document.getElementById(errorId);
                if (errorElement.style.display === 'block') {
                    hideFieldError(errorElement);
                    const input = errorId === 'email-error' ? emailInput : passwordInput;
                    removeInputErrorState(input);
                }
            }

            function addInputErrorState(input) {
                input.style.borderColor = '#dc2626';
                input.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
            }

            function removeInputErrorState(input) {
                input.style.borderColor = '#e5e7eb';
                input.style.boxShadow = 'none';
            }

            function isValidEmail(email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            // Enhanced keyboard navigation
            form.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.target.type !== 'submit') {
                    e.preventDefault();
                    const formElements = Array.from(form.querySelectorAll('input, button'));
                    const currentIndex = formElements.indexOf(e.target);
                    const nextElement = formElements[currentIndex + 1];
                    
                    if (nextElement && nextElement.type !== 'submit') {
                        nextElement.focus();
                    } else {
                        // Validate and submit if on last field
                        if (validateEmail() && validatePassword()) {
                            form.submit();
                        }
                    }
                }
            });

            // Focus first empty input on page load
            if (!emailInput.value) {
                emailInput.focus();
            } else if (!passwordInput.value) {
                passwordInput.focus();
            }
        });

        // Professional password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = '🙈';
                toggleBtn.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = '👁️';
                toggleBtn.setAttribute('aria-label', 'Show password');
            }
        }

        // Auto-hide messages with fade effect
        setTimeout(function() {
            const messages = document.querySelectorAll('.error-message.show, .success-message.show');
            messages.forEach(function(message) {
                message.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 500);
            });
        }, 6000);

        // Enhanced security - disable form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>