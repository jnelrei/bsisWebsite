<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - BSIS</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #020617;
            --bg-elevated: #020617;
            --bg-card: #020617;
            --bg-soft: #020617;
            --accent: #22c55e;
            --accent-soft: rgba(34, 197, 94, 0.16);
            --accent-strong: #4ade80;
            --accent-alt: #0ea5e9;
            --text-main: #e5e7eb;
            --text-soft: #9ca3af;
            --border-subtle: rgba(148, 163, 184, 0.25);
            --glass: rgba(15, 23, 42, 0.82);
            --radius-lg: 22px;
            --radius-md: 18px;
            --radius-pill: 999px;
            --shadow-soft: 0 24px 60px rgba(0, 0, 0, 0.7);
            --shadow-glow: 0 0 80px rgba(34, 197, 94, 0.45);
        }

        * {
            box-sizing: border-box;
            padding: 0;
            margin: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Space Grotesk', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #0f172a 0, #020617 45%, #000 100%);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: 40px 32px;
            box-shadow: var(--shadow-soft);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .login-header p {
            color: var(--text-soft);
            font-size: 14px;
        }

        .form-field {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-size: 15px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .form-input.invalid {
            border-color: #ef4444;
        }

        .form-error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
            display: block;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .btn {
            width: 100%;
            padding: 14px 24px;
            background: var(--accent);
            color: #020617;
            border: none;
            border-radius: var(--radius-md);
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
            margin-top: 8px;
        }

        .btn:hover {
            background: var(--accent-strong);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(2, 6, 23, 0.3);
            border-radius: 50%;
            border-top-color: #020617;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .footer-note {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-soft);
        }

        .back-link {
            text-align: center;
            margin-top: 16px;
        }

        .back-link a {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Sign in with TechnoPal</h1>
                <p>Enter your credentials to continue</p>
            </div>

            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>

            <form id="loginForm" autocomplete="off">
                <div class="form-field">
                    <label class="form-label" for="txtUserName">User Name</label>
                    <input 
                        class="form-input" 
                        type="text" 
                        id="txtUserName" 
                        name="txtUserName" 
                        placeholder="Enter your username" 
                        required
                        autocomplete="username"
                    >
                    <span class="form-error" id="usernameError"></span>
                </div>

                <div class="form-field">
                    <label class="form-label" for="txtPassword">Password</label>
                    <input 
                        class="form-input" 
                        type="password" 
                        id="txtPassword" 
                        name="txtPassword" 
                        placeholder="Enter your password" 
                        required
                        autocomplete="current-password"
                    >
                    <span class="form-error" id="passwordError"></span>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    Log in
                </button>
            </form>

            <div class="footer-note">
                By logging in you agree to the department's acceptable use policy.
            </div>

            <div class="back-link">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const submitBtn = document.getElementById('submitBtn');
        const txtUserName = document.getElementById('txtUserName');
        const txtPassword = document.getElementById('txtPassword');
        const usernameError = document.getElementById('usernameError');
        const passwordError = document.getElementById('passwordError');

        // Clear errors on input
        txtUserName.addEventListener('input', () => {
            clearError(txtUserName);
            hideMessages();
        });

        txtPassword.addEventListener('input', () => {
            clearError(txtPassword);
            hideMessages();
        });

        function clearError(input) {
            input.classList.remove('invalid');
            if (input.id === 'txtUserName') {
                usernameError.textContent = '';
            } else if (input.id === 'txtPassword') {
                passwordError.textContent = '';
            }
        }

        function setError(input, message) {
            input.classList.add('invalid');
            if (input.id === 'txtUserName') {
                usernameError.textContent = message;
            } else if (input.id === 'txtPassword') {
                passwordError.textContent = message;
            }
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
            successMessage.style.display = 'none';
        }

        function showSuccess(message) {
            successMessage.textContent = message;
            successMessage.style.display = 'block';
            errorMessage.style.display = 'none';
        }

        function hideMessages() {
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';
        }

        function setLoading(loading) {
            if (loading) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span>Logging in...';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Log in';
            }
        }

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideMessages();
            clearError(txtUserName);
            clearError(txtPassword);

            // Validate inputs
            let isValid = true;
            if (!txtUserName.value.trim()) {
                setError(txtUserName, 'User Name is required.');
                isValid = false;
            }
            if (!txtPassword.value) {
                setError(txtPassword, 'Password is required.');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            setLoading(true);

            try {
                // Prepare form data
                const formData = new URLSearchParams();
                formData.append('txtUserName', txtUserName.value.trim());
                formData.append('txtPassword', txtPassword.value);

                // Call the API
                const response = await fetch('BCCWeb/TPLoginAPI.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });

                // Check if response is ok
                if (!response.ok) {
                    let errorData;
                    try {
                        errorData = await response.json();
                    } catch (e) {
                        errorData = { message: `HTTP Error ${response.status}: ${response.statusText}` };
                    }
                    throw new Error(errorData.message || errorData.error || 'An error occurred');
                }

                const data = await response.json();

                if (data.success) {
                    showSuccess('Login successful! Redirecting...');
                    
                    // Store user data in session via PHP handler
                    // We'll create a session handler endpoint or redirect with session
                    
                    // Determine redirect based on user type
                    let redirectUrl = 'index.php';
                    if (data.data.user_type === 'student') {
                        redirectUrl = 'student/main.php';
                    } else if (data.data.user_type === 'user') {
                        // Handle different user roles
                        if (data.data.role === 'admin') {
                            redirectUrl = 'admin/dashboard/main.php';
                        } else if (data.data.role === 'superadmin') {
                            redirectUrl = 'admin/dashboard/main.php';
                        } else {
                            redirectUrl = 'student/main.php';
                        }
                    }

                    // Set session via a separate endpoint
                    await setSession(data.data);

                    // Redirect after a short delay
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1000);

                } else {
                    showError(data.message || 'Invalid username or password.');
                    setLoading(false);
                }

            } catch (error) {
                console.error('Login error:', error);
                const errorMessage = error.message || 'An error occurred. Please try again.';
                showError(errorMessage);
                setLoading(false);
            }
        });

        // Function to set session via PHP
        async function setSession(userData) {
            try {
                const response = await fetch('api/set_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });
                return await response.json();
            } catch (error) {
                console.error('Session error:', error);
            }
        }

        // Focus on username field when page loads
        window.addEventListener('load', () => {
            txtUserName.focus();
        });
    </script>
</body>
</html>

