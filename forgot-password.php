<?php
session_start();

// Initialize variables
$error_message = '';
$success_message = '';
$email_sent = false;

// Database configuration (replace with your actual database details)
// $db_host = 'localhost';
// $db_name = 'assignment_tracker';
// $db_user = 'your_username';
// $db_pass = 'your_password';

// Function to generate secure reset token
function generateResetToken() {
    return bin2hex(random_bytes(32));
}

// Function to send reset email (using PHPMailer or mail())
function sendResetEmail($email, $reset_token) {
    // For demo purposes, we'll simulate email sending
    // In production, use PHPMailer or similar service
    
    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $reset_token;
    
    $subject = "Password Reset Request - Assignment Tracker";
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
                <h2>📚 Assignment Tracker</h2>
                <p>Password Reset Request</p>
            </div>
            <div class='content'>
                <h3>Reset Your Password</h3>
                <p>We received a request to reset your password. Click the button below to create a new password:</p>
                <p style='text-align: center; margin: 30px 0;'>
                    <a href='{$reset_link}' class='button'>Reset Password</a>
                </p>
                <p><strong>Security Information:</strong></p>
                <ul>
                    <li>This link will expire in 1 hour for security reasons</li>
                    <li>If you didn't request this reset, please ignore this email</li>
                    <li>For security, never share this link with anyone</li>
                </ul>
                <div class='footer'>
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p style='word-break: break-all;'>{$reset_link}</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Assignment Tracker <noreply@assignmenttracker.com>" . "\r\n";
    
    // For demo purposes, we'll just return true
    // In production, use: return mail($email, $subject, $message, $headers);
    
    // Log the reset attempt (in production, save to database)
    error_log("Password reset requested for: $email");
    error_log("Reset link: $reset_link");
    
    return true; // Simulate successful email sending
}

// Function to check if email exists in database
function emailExists($email) {
    // For demo purposes, we'll use a simple check
    // In production, check against your actual database
    $valid_emails = [
        'student@university.edu',
        'john.doe@university.edu',
        'jane.smith@university.edu',
        'chama@gmail.com'
    ];
    
    return in_array(strtolower($email), array_map('strtolower', $valid_emails));
}

// Function to save reset token to database
function saveResetToken($email, $token) {
    // In production, save to database with expiration time
    // Example SQL: INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
    
    // For demo, we'll use session storage
    $_SESSION['reset_tokens'][$token] = [
        'email' => $email,
        'expires' => time() + 3600 // 1 hour from now
    ];
    
    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Basic validation
    if (empty($email)) {
        $error_message = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email exists in database
        if (emailExists($email)) {
            // Generate reset token
            $reset_token = generateResetToken();
            
            // Save token to database
            if (saveResetToken($email, $reset_token)) {
                // Send reset email
                if (sendResetEmail($email, $reset_token)) {
                    $email_sent = true;
                    $success_message = "Password reset instructions have been sent to your email address.";
                } else {
                    $error_message = 'There was an error sending the reset email. Please try again.';
                }
            } else {
                $error_message = 'There was an error processing your request. Please try again.';
            }
        } else {
            // For security, don't reveal that email doesn't exist
            // Show success message anyway to prevent email enumeration
            $email_sent = true;
            $success_message = "If an account with that email exists, password reset instructions have been sent.";
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
    <title>Forgot Password - Assignment Tracker</title>
    <link rel="stylesheet" href="css/forgot-password-style.css">
    <meta name="description" content="Reset your assignment tracker password">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔑</text></svg>">
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <?php if (!$email_sent): ?>
                <!-- Reset Request Form -->
                <div class="reset-header">
                    <h1 class="reset-title">
                        🔑 Forgot Password?
                    </h1>
                    <p class="reset-subtitle">Don't worry! Enter your email address and we'll send you instructions to reset your password.</p>
                </div>

                <div class="info-card">
                    <span class="info-icon">💡</span>
                    <div class="info-text">
                        Enter the email address associated with your account and we'll send you a secure link to reset your password.
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message show">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="resetForm" novalidate>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <span class="input-icon">📧</span>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="Enter your registered email address"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                required
                                autocomplete="email"
                                aria-describedby="email-error"
                            >
                        </div>
                        <div id="email-error" class="field-error" style="display: none;"></div>
                    </div>

                    <button type="submit" class="reset-btn" id="resetBtn">
                        <div class="spinner"></div>
                        <span class="btn-text">📨 Send Reset Instructions</span>
                    </button>
                </form>

                <div class="back-link">
                    <a href="login.php">
                        ← Back to Sign In
                    </a>
                </div>

            <?php else: ?>
                <!-- Success State -->
                <div class="success-card">
                    <div class="success-icon">✅</div>
                    <h2 class="success-title">Check Your Email</h2>
                    <p class="success-text">
                        We've sent password reset instructions to your email address. 
                        Please check your inbox and follow the secure link to create a new password.
                    </p>
                    
                    <div class="info-card">
                        <span class="info-icon">⏰</span>
                        <div class="info-text">
                            <strong>Important:</strong> The reset link will expire in 1 hour for security reasons. 
                            If you don't see the email, check your spam folder.
                        </div>
                    </div>

                    <div class="success-actions">
                        <a href="login.php" class="reset-btn">
                            🚀 Back to Sign In
                        </a>
                        <a href="forgot-password.php" class="secondary-btn">
                            📨 Resend Email
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Professional form validation and enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetForm');
            
            if (form) {
                const resetBtn = document.getElementById('resetBtn');
                const emailInput = document.getElementById('email');

                // Enhanced real-time validation
                emailInput.addEventListener('blur', validateEmail);
                emailInput.addEventListener('input', clearFieldError);

                // Professional form submission handling
                form.addEventListener('submit', function(e) {
                    const isEmailValid = validateEmail();
                    
                    if (!isEmailValid) {
                        e.preventDefault();
                        return;
                    }

                    // Show professional loading state
                    resetBtn.classList.add('loading');
                    resetBtn.disabled = true;
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

                function clearFieldError() {
                    const emailError = document.getElementById('email-error');
                    if (emailError.style.display === 'block') {
                        hideFieldError(emailError);
                        removeInputErrorState(emailInput);
                    }
                }

                function showFieldError(errorElement, message) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }

                function hideFieldError(errorElement) {
                    errorElement.style.display = 'none';
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

                // Focus email input on page load
                emailInput.focus();

                // Enhanced keyboard navigation
                form.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (validateEmail()) {
                            form.submit();
                        }
                    }
                });
            }
        });

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

        // Professional link hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-1px)';
                });
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>