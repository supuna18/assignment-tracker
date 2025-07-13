<?php
session_start();

// Initialize variables
$error_message = '';
$success_message = '';
$registration_success = false;

// Database configuration (replace with your actual database details)
// $db_host = 'localhost';
// $db_name = 'assignment_tracker';
// $db_user = 'your_username';
// $db_pass = 'your_password';

// Function to validate email format and domain
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Additional domain validation (optional)
    $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'university.edu', 'student.edu'];
    $domain = substr(strrchr($email, "@"), 1);
    
    // For demo, we'll allow any domain
    return true;
}

// Function to check if email already exists
function emailExists($email) {
    // For demo purposes, we'll simulate some existing emails
    $existing_emails = [
        'john.doe@university.edu',
        'jane.smith@university.edu',
        'existing@gmail.com'
    ];
    
    return in_array(strtolower($email), array_map('strtolower', $existing_emails));
}

// Function to validate password strength
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }
    
    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }
    
    return $errors;
}

// Function to create user account
function createUser($firstName, $lastName, $email, $password) {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // In production, insert into database
    // Example SQL: INSERT INTO users (first_name, last_name, email, password, created_at) VALUES (?, ?, ?, ?, NOW())
    
    // For demo purposes, we'll simulate successful registration
    $user_data = [
        'id' => rand(1000, 9999),
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => $hashedPassword,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Log successful registration
    error_log("New user registered: $email");
    
    return $user_data;
}

// Function to send welcome email
function sendWelcomeEmail($email, $firstName) {
    $subject = "Welcome to Assignment Tracker!";
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3b82f6; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
            .button { display: inline-block; background: #3b82f6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: 600; }
            .footer { margin-top: 20px; font-size: 14px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>📚 Welcome to Assignment Tracker</h2>
            </div>
            <div class='content'>
                <h3>Hello {$firstName}!</h3>
                <p>Welcome to Assignment Tracker! Your account has been successfully created.</p>
                <p>You can now:</p>
                <ul>
                    <li>Track your assignments and deadlines</li>
                    <li>Organize your coursework efficiently</li>
                    <li>Set reminders for important dates</li>
                    <li>Monitor your academic progress</li>
                </ul>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='http://" . $_SERVER['HTTP_HOST'] . "/login.php' class='button'>Start Using Assignment Tracker</a>
                </p>
                <div class='footer'>
                    <p>If you have any questions, feel free to contact our support team.</p>
                    <p>Happy studying!</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Assignment Tracker <welcome@assignmenttracker.com>" . "\r\n";
    
    // For demo purposes, we'll just log the email
    error_log("Welcome email sent to: $email");
    
    return true; // Simulate successful email sending
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $agreeTerms = isset($_POST['agree_terms']);
    
    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (emailExists($email)) {
        $error_message = 'An account with this email address already exists.';
    } elseif ($password !== $confirmPassword) {
        $error_message = 'Passwords do not match.';
    } elseif (!empty(validatePassword($password))) {
        $passwordErrors = validatePassword($password);
        $error_message = implode('. ', $passwordErrors) . '.';
    } elseif (!$agreeTerms) {
        $error_message = 'You must agree to the Terms of Service and Privacy Policy.';
    } else {
        // Create user account
        $newUser = createUser($firstName, $lastName, $email, $password);
        
        if ($newUser) {
            // Send welcome email
            sendWelcomeEmail($email, $firstName);
            
            // Set success state
            $registration_success = true;
            $success_message = "Account created successfully! You can now sign in with your credentials.";
            
            // Optionally, automatically log in the user
            // $_SESSION['user_logged_in'] = true;
            // $_SESSION['user_email'] = $email;
            // $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        } else {
            $error_message = 'There was an error creating your account. Please try again.';
        }
    }
}

// Redirect if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Assignment Tracker</title>
    <link rel="stylesheet" href="css/register-style.css">
    <meta name="description" content="Create your assignment tracker account">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📝</text></svg>">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <?php if (!$registration_success): ?>
                <!-- Registration Form -->
                <div class="register-header">
                    <h1 class="register-title">
                        📚 Create Account
                    </h1>
                    <p class="register-subtitle">Join Assignment Tracker and organize your academic life</p>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message show">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="registerForm" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    class="form-input" 
                                    placeholder="John"
                                    value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                    required
                                    autocomplete="given-name"
                                    aria-describedby="first-name-error"
                                >
                            </div>
                            <div id="first-name-error" class="field-error" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <div class="input-wrapper">
                                <span class="input-icon">👤</span>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    class="form-input" 
                                    placeholder="Doe"
                                    value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                    required
                                    autocomplete="family-name"
                                    aria-describedby="last-name-error"
                                >
                            </div>
                            <div id="last-name-error" class="field-error" style="display: none;"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="john.doe@university.edu"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
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
                                placeholder="Create a strong password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-error"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                                👁️
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-label">Password Strength:</div>
                            <div class="strength-bar">
                                <div id="strengthBar" class="strength-bar-fill"></div>
                            </div>
                            <div id="strengthText" class="strength-text">Enter a password to see strength</div>
                        </div>
                        <div id="password-error" class="field-error" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Confirm your password"
                                required
                                autocomplete="new-password"
                                aria-describedby="confirm-password-error"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')" aria-label="Toggle password visibility">
                                👁️
                            </button>
                        </div>
                        <div id="confirm-password-error" class="field-error" style="display: none;"></div>
                    </div>

                    <div class="terms-section">
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="agree_terms" name="agree_terms" required>
                            <label for="agree_terms">
                                I agree to the <a href="terms.php" class="terms-link" target="_blank">Terms of Service</a> 
                                and <a href="privacy.php" class="terms-link" target="_blank">Privacy Policy</a>
                            </label>
                        </div>
                        <div id="terms-error" class="field-error" style="display: none;"></div>
                    </div>

                    <button type="submit" class="register-btn" id="registerBtn">
                        <div class="spinner"></div>
                        <span class="btn-text">🚀 Create Account</span>
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>

            <?php else: ?>
                <!-- Success State -->
                <div class="success-card">
                    <div class="success-icon">🎉</div>
                    <h2 class="success-title">Account Created!</h2>
                    <p class="success-text">
                        Your Assignment Tracker account has been successfully created. 
                        You can now sign in and start organizing your academic life!
                    </p>

                    <div class="success-actions">
                        <a href="login.php" class="register-btn">
                            🚀 Sign In Now
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Professional form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            
            if (form) {
                const registerBtn = document.getElementById('registerBtn');
                const firstNameInput = document.getElementById('first_name');
                const lastNameInput = document.getElementById('last_name');
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                const confirmPasswordInput = document.getElementById('confirm_password');
                const termsCheckbox = document.getElementById('agree_terms');
                const strengthBar = document.getElementById('strengthBar');
                const strengthText = document.getElementById('strengthText');

                // Real-time validation
                firstNameInput.addEventListener('blur', validateFirstName);
                firstNameInput.addEventListener('input', () => clearFieldError('first-name-error'));
                
                lastNameInput.addEventListener('blur', validateLastName);
                lastNameInput.addEventListener('input', () => clearFieldError('last-name-error'));
                
                emailInput.addEventListener('input', () => clearFieldError('email-error'));
                
                passwordInput.addEventListener('input', function() {
                    validatePassword();
                    updatePasswordStrength();
                    clearFieldError('password-error');
                });
                
                confirmPasswordInput.addEventListener('input', function() {
                    validateConfirmPassword();
                    clearFieldError('confirm-password-error');
                });

                passwordInput.addEventListener('blur', validatePassword);
                confirmPasswordInput.addEventListener('blur', validateConfirmPassword);
                termsCheckbox.addEventListener('change', validateTerms);

                // Form submission
                form.addEventListener('submit', function(e) {
                    const isFirstNameValid = validateFirstName();
                    const isLastNameValid = validateLastName();
                    const isEmailValid = validateEmail();
                    const isPasswordValid = validatePassword();
                    const isConfirmPasswordValid = validateConfirmPassword();
                    const isTermsValid = validateTerms();
                    
                    if (!isFirstNameValid || !isLastNameValid || !isEmailValid || 
                        !isPasswordValid || !isConfirmPasswordValid || !isTermsValid) {
                        e.preventDefault();
                        return;
                    }

                    // Show loading state
                    registerBtn.classList.add('loading');
                    registerBtn.disabled = true;
                });

                function validateFirstName() {
                    const firstName = firstNameInput.value.trim();
                    const firstNameError = document.getElementById('first-name-error');
                    
                    if (!firstName) {
                        showFieldError(firstNameError, 'First name is required.');
                        addInputErrorState(firstNameInput);
                        return false;
                    } else if (firstName.length < 2) {
                        showFieldError(firstNameError, 'First name must be at least 2 characters.');
                        addInputErrorState(firstNameInput);
                        return false;
                    } else if (!isValidName(firstName)) {
                        showFieldError(firstNameError, 'First name contains invalid characters.');
                        addInputErrorState(firstNameInput);
                        return false;
                    } else {
                        hideFieldError(firstNameError);
                        removeInputErrorState(firstNameInput);
                        return true;
                    }
                }

                function validateLastName() {
                    const lastName = lastNameInput.value.trim();
                    const lastNameError = document.getElementById('last-name-error');
                    
                    if (!lastName) {
                        showFieldError(lastNameError, 'Last name is required.');
                        addInputErrorState(lastNameInput);
                        return false;
                    } else if (lastName.length < 2) {
                        showFieldError(lastNameError, 'Last name must be at least 2 characters.');
                        addInputErrorState(lastNameInput);
                        return false;
                    } else if (!isValidName(lastName)) {
                        showFieldError(lastNameError, 'Last name contains invalid characters.');
                        addInputErrorState(lastNameInput);
                        return false;
                    } else {
                        hideFieldError(lastNameError);
                        removeInputErrorState(lastNameInput);
                        return true;
                    }
                }

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
                    } else if (password.length < 8) {
                        showFieldError(passwordError, 'Password must be at least 8 characters long.');
                        addInputErrorState(passwordInput);
                        return false;
                    } else if (!hasUpperCase(password)) {
                        showFieldError(passwordError, 'Password must contain at least one uppercase letter.');
                        addInputErrorState(passwordInput);
                        return false;
                    } else if (!hasLowerCase(password)) {
                        showFieldError(passwordError, 'Password must contain at least one lowercase letter.');
                        addInputErrorState(passwordInput);
                        return false;
                    } else if (!hasNumber(password)) {
                        showFieldError(passwordError, 'Password must contain at least one number.');
                        addInputErrorState(passwordInput);
                        return false;
                    } else {
                        hideFieldError(passwordError);
                        removeInputErrorState(passwordInput);
                        return true;
                    }
                }

                function validateConfirmPassword() {
                    const password = passwordInput.value;
                    const confirmPassword = confirmPasswordInput.value;
                    const confirmPasswordError = document.getElementById('confirm-password-error');
                    
                    if (!confirmPassword) {
                        showFieldError(confirmPasswordError, 'Please confirm your password.');
                        addInputErrorState(confirmPasswordInput);
                        return false;
                    } else if (password !== confirmPassword) {
                        showFieldError(confirmPasswordError, 'Passwords do not match.');
                        addInputErrorState(confirmPasswordInput);
                        return false;
                    } else {
                        hideFieldError(confirmPasswordError);
                        removeInputErrorState(confirmPasswordInput);
                        return true;
                    }
                }

                function validateTerms() {
                    const termsError = document.getElementById('terms-error');
                    
                    if (!termsCheckbox.checked) {
                        showFieldError(termsError, 'You must agree to the Terms of Service and Privacy Policy.');
                        return false;
                    } else {
                        hideFieldError(termsError);
                        return true;
                    }
                }

                function updatePasswordStrength() {
                    const password = passwordInput.value;
                    let strength = 0;
                    let strengthLabel = '';
                    let strengthClass = '';

                    if (password.length >= 8) strength += 20;
                    if (hasUpperCase(password)) strength += 20;
                    if (hasLowerCase(password)) strength += 20;
                    if (hasNumber(password)) strength += 20;
                    if (hasSpecialChar(password)) strength += 20;

                    if (strength === 0) {
                        strengthLabel = 'Enter a password to see strength';
                        strengthClass = '';
                    } else if (strength <= 40) {
                        strengthLabel = 'Weak';
                        strengthClass = 'strength-weak';
                    } else if (strength <= 60) {
                        strengthLabel = 'Fair';
                        strengthClass = 'strength-fair';
                    } else if (strength <= 80) {
                        strengthLabel = 'Good';
                        strengthClass = 'strength-good';
                    } else {
                        strengthLabel = 'Strong';
                        strengthClass = 'strength-strong';
                    }

                    strengthBar.style.width = strength + '%';
                    strengthBar.className = 'strength-bar-fill ' + strengthClass;
                    strengthText.textContent = strengthLabel;
                }

                function isValidName(name) {
                    return /^[a-zA-Z\s'-]+$/.test(name);
                }

                function isValidEmail(email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return emailRegex.test(email);
                }

                function hasUpperCase(str) {
                    return /[A-Z]/.test(str);
                }

                function hasLowerCase(str) {
                    return /[a-z]/.test(str);
                }

                function hasNumber(str) {
                    return /\d/.test(str);
                }

                function hasSpecialChar(str) {
                    return /[!@#$%^&*(),.?":{}|<>]/.test(str);
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
                        const inputId = errorId.replace('-error', '').replace('-', '_');
                        const input = document.getElementById(inputId);
                        if (input) {
                            removeInputErrorState(input);
                        }
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

                // Focus first input on page load
                firstNameInput.focus();

                // Enhanced keyboard navigation
                const formInputs = [firstNameInput, lastNameInput, emailInput, passwordInput, confirmPasswordInput];
                
                formInputs.forEach((input, index) => {
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            if (index < formInputs.length - 1) {
                                formInputs[index + 1].focus();
                            } else {
                                termsCheckbox.focus();
                            }
                        }
                    });
                });

                termsCheckbox.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // Validate all fields before submitting
                        const allValid = validateFirstName() && validateLastName() && 
                                       validateEmail() && validatePassword() && 
                                       validateConfirmPassword() && validateTerms();
                        if (allValid) {
                            form.submit();
                        }
                    }
                });
            }
        });

        // Password toggle functionality
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = passwordInput.nextElementSibling;
            
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
        }, 8000);

        // Enhanced security - disable form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Real-time email availability check (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            let emailCheckTimeout;
            
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    clearTimeout(emailCheckTimeout);
                    emailCheckTimeout = setTimeout(() => {
                        // In production, you could add AJAX call to check email availability
                        // checkEmailAvailability(this.value);
                    }, 1000);
                });
            }
        });

        // Form data preservation on page refresh
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            if (form) {
                // Save form data to sessionStorage on input
                const inputs = form.querySelectorAll('input[type="text"], input[type="email"]');
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        sessionStorage.setItem('register_' + this.name, this.value);
                    });
                    
                    // Restore data on page load
                    const savedValue = sessionStorage.getItem('register_' + input.name);
                    if (savedValue && !input.value) {
                        input.value = savedValue;
                    }
                });

                // Clear saved data on successful submission
                form.addEventListener('submit', function() {
                    inputs.forEach(input => {
                        sessionStorage.removeItem('register_' + input.name);
                    });
                });
            }
        });
    </script>
</body>
</html>