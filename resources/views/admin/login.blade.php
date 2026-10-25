<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Login - Khelo Store</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #f0f2f5;
            --primary: #1877f2;
            --primary-hover: #166fe5;
            --danger: #ef4444;
            --text-primary: #1c1e21;
            --text-secondary: #65676b;
            --border-color: #ced0d4;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            background-color: var(--bg-primary);
        }

        body {
            color: var(--text-primary);
            min-height: 100vh;
            min-height: -webkit-fill-available;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 16px;
            box-sizing: border-box;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-card {
            width: 100%;
            max-width: 100%;
            background: #ffffff;
            border: 1px solid #e4e6eb;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            padding: 34px 28px;
            box-sizing: border-box;
            position: relative;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 26px;
            width: 100%;
        }

        .brand-icon-wrap {
            font-size: 34px;
            color: #1877f2;
            margin-bottom: 10px;
            display: inline-block;
        }

        .brand-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: #1c1e21;
            letter-spacing: -0.3px;
        }

        .brand-header p {
            color: var(--text-secondary);
            font-size: 13.5px;
            margin-top: 4px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 18px;
            width: 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #4b4f56;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            box-sizing: border-box;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #8a8d91;
            font-size: 15px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            max-width: 100%;
            height: 48px;
            padding: 0 44px 0 40px;
            background-color: #ffffff;
            border: 1.5px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            outline: none;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(24, 119, 242, 0.15);
        }

        .btn-toggle-eye {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #65676b;
            cursor: pointer;
            padding: 8px 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.2s;
        }

        .btn-toggle-eye:hover {
            color: var(--primary);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            margin-bottom: 24px;
            font-size: 13.5px;
            width: 100%;
            box-sizing: border-box;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #4b4f56;
            font-weight: 500;
            user-select: none;
        }

        .remember-me input {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: var(--primary);
            border-radius: 4px;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15.5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-sizing: border-box;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(24, 119, 242, 0.25);
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .error-message {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            margin-bottom: 20px;
            list-style: none;
            font-weight: 500;
            width: 100%;
            box-sizing: border-box;
        }

        /* Mobile full-width fixed view (Zero horizontal scroll/cut) */
        @media (max-width: 480px) {
            body {
                padding: 16px;
                background-color: #ffffff;
                align-items: center;
            }

            .login-wrapper {
                width: 100%;
                max-width: 100%;
            }

            .login-card {
                border: none;
                box-shadow: none;
                padding: 10px 0;
                border-radius: 0;
            }

            .brand-header {
                margin-bottom: 22px;
            }

            .brand-icon-wrap {
                font-size: 30px;
            }

            .brand-header h1 {
                font-size: 21px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="brand-header">
                <div class="brand-icon-wrap">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <h1>Khelo Store Admin</h1>
                <p>Sign in to manage bot, users & key inventory</p>
            </div>

            @if(session('status') || session('success'))
                <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; box-sizing: border-box; width: 100%;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('status') ?: session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <ul class="error-message">
                    @foreach($errors->all() as $error)
                        <li><i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('admin.login') }}" method="POST" style="width: 100%; box-sizing: border-box;">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon-left"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="admin@khelostore.com" value="{{ old('email', 'admin@khelostore.com') }}" required autofocus style="padding-right: 14px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon-left"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <button type="button" id="btnTogglePassword" class="btn-toggle-eye" onclick="togglePasswordVisibility()" title="Show/Hide Password" aria-label="Toggle Password Visibility">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember" checked>
                        <span>Keep me logged in</span>
                    </label>
                    <a href="{{ route('admin.password.request') }}" class="forgot-link">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit" class="btn-submit">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fa-regular fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fa-regular fa-eye';
            }
        }
    </script>
</body>
</html>
