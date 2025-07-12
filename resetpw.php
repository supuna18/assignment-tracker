<?php
session_start();

// Initialize variables
$error_message = '';
$success_message = '';
$valid_token = false;
$token = '';
$email = '';

// Get token from URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token (in production, check against database)
    if (isset($_SESSION['reset_tokens'][$token])) {
        $token_data = $_SESSION['reset_tokens'][$token];
        
        // Check if token has expired
        if (time() <= $token_data['expires']) {
            $valid_token = true;
            $email = $token_data['email'];
        } else {
            $error_message = 'This reset link has expired. Please request a new password reset.';
        }
    } else {
        $error_message = 'Invalid or expired reset link. Please request a new password reset.';
    }
} else {
    $error_message = 'No reset token provided. Please use the link from your email.';
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        $error_message = 'Please fill in all fields.';
    } elseif (strlen($new_password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
        $error_message = 'Password must contain at least one uppercase letter, one lowercase letter, and one number.';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // In production, update the password in the database
        // Example: UPDATE users SET password = ? WHERE email = ?
        
        // For demo purposes, we'll simulate successful password update
        $password_updated = true; // In production: updateUserPassword($email, $hashed_password);
        
        if ($password_updated) {
            // Remove the used token
            unset($_SESSION['reset_tokens'][$token]);
            
            $success_message = 'Your password has been successfully updated. You can now sign in with your new password.';
            
            // Log the successful password reset
            error_log("Password successfully reset for: $email");
        } else {
            $error_message = 'There was an error updating your password. Please try again.';
        }
    }
}

// Function to update user password (production implementation)
function updateUserPassword($email, $hashed_password) {
    // Example database update
    // $stmt = $pdo->prepare("UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE email = ?");
    // return $stmt->execute([$hashed_password, $email]);
    
    return true; // Simulate success for demo
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Assignment Tracker</title>
    <link rel="stylesheet" href="css/forgot-password-style.css">
    <meta name="description" content="Create a new password for your assignment tracker account">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔐</text></svg>">
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <?php if (!$valid_token): ?>
                <!-- Invalid Token State -->
                <div class="reset-header">
                    <h1 class="reset-title">
                        ❌ Invalid Reset Link
                    </h1>
                    <p class="reset-subtitle">This password reset link is invalid or has expired.</p>
                </div>

                <div class="error-message show">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>

                <div class="success-actions">
                    <a href="forgot-password.php" class="reset-btn">
                        🔑 Request New Reset Link
                    </a>
                    <a href="login.php" class="secondary-btn">
                        ← Back to Sign In
                    </a>
                </div>

            <?php elseif (!empty($success_message)): ?>
                <!-- Success State -->
                <div class="success-card">
                    <div class="success-icon">✅</div>
                    <h2 class="success-title">Password Updated!</h2>
                    <p class="success-text">
                        Your password has been successfully updated. You can now sign in to your account using your new password.
                    </p>
                    
                    <div class="info-card">
                        <span class="info-icon">🔒</span>
                        <div class="info-text">
                            <strong>Security tip:</strong> Keep your password safe and don't share it with anyone. 
                            Consider using a password manager for better security.
                        </div>
                    </div>

                    <div class="success-actions">
                        <a href="login.php" class="reset-btn">
                            🚀 Sign In Now
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Password Reset Form -->
                <div class="reset-header">
                    <h1 class="reset-title">
                        🔐 Create New Password
                    </h1>
                    <p class="reset-subtitle">Choose a strong password for your account: <strong><?php echo htmlspecialchars($email); ?></strong></p>
                </div>

                <div class="info-card">
                    <span class="info-icon">🛡️</span>
                    <div class="info-text">
                        Your password must be at least 8 characters long and include uppercase, lowercase, and numeric characters.
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message show">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . htmlspecialchars($token); ?>" id="resetPasswordForm" novalidate>
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Enter your new password"
                                required
                                autocomplete="new-password"
                                aria-describedby="password-error"
                                minlength="8"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword('password')" aria-label="Toggle password visibility">
                                👁️
                            </button>
                        </div>
                        <div id="password-error" class="field-error" style="display: none;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Confirm your new password"
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

                    <!-- Password Strength Indicator -->
                    <div class="password-strength" style="margin-bottom: 1.5rem;">
                        <div class="strength-label" style="font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem;">
                            Password Strength:
                        </div>
                        <div class="strength-bar" style="height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden;">
                            <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s ease; background: #dc2626;"></div>
                        </div>
                        <div id="strengthText" style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                            Enter a password to see strength
                        </div>
                    </div>

                    <button type="submit" class="reset-btn" id="resetPasswordBtn">
                        <div class="spinner"></div>
                        <span class="btn-text">🔐 Update Password</span>
                    </button>
                </form>

                <div class="back-link">
                    <a href="login.php">
                        ← Back to Sign In
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Professional form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            
            if (form) {
                const resetBtn = document.getElementById('resetPasswordBtn');
                const passwordInput = document.getElementById('password');
                const confirmPasswordInput = document.getElementById('confirm_password');
                const strengthBar = document.getElementById('strengthBar');
                const strengthText = document.getElementById('strengthText');

                // Real-time validation
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

                // Form submission
                form.addEventListener('submit', function(e) {
                    const isPasswordValid = validatePassword();
                    const isConfirmValid = validateConfirmPassword();
                    
                    if (!isPasswordValid || !isConfirmValid) {
                        e.preventDefault();
                        return;
                    }

                    // Show loading state
                    resetBtn.classList.add('loading');
                    resetBtn.disabled = true;
                });

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

                function updatePasswordStrength() {
                    const password = passwordInput.value;
                    let strength = 0;
                    let strengthLabel = '';
                    let strengthColor = '#dc2626';

                    if (password.length >= 8) strength += 20;
                    if (hasUpperCase(password)) strength += 20;
                    if (hasLowerCase(password)) strength += 20;
                    if (hasNumber(password)) strength += 20;
                    if (hasSpecialChar(password)) strength += 20;

                    if (strength === 0) {
                        strengthLabel = 'Enter a password to see strength';
                        strengthColor = '#e5e7eb';
                    } else if (strength <= 40) {
                        strengthLabel = 'Weak';
                        strengthColor = '#dc2626';
                    } else if (strength <= 60) {
                        strengthLabel = 'Fair';
                        strengthColor = '#f59e0b';
                    } else if (strength <= 80) {
                        strengthLabel = 'Good';
                        strengthColor = '#3b82f6';
                    } else {
                        strengthLabel = 'Strong';
                        strengthColor = '#10b981';
                    }

                    strengthBar.style.width = strength + '%';
                    strengthBar.style.background = strengthColor;
                    strengthText.textContent = strengthLabel;
                    strengthText.style.color = strengthColor;
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
                        const input = errorId === 'password-error' ? passwordInput : confirmPasswordInput;
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

                // Focus first input on page load
                passwordInput.focus();
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

        // Auto-hide messages
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

        // Enhanced security
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // Prevent password field copying
        document.addEventListener('DOMContentLoaded', function() {
            const passwordFields = document.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                field.addEventListener('copy', function(e) {
                    e.preventDefault();
                });
                field.addEventListener('cut', function(e) {
                    e.preventDefault();
                });
            });
        });
    </script>
</body>
</html>