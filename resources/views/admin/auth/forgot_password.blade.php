<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forgot Password - Khelo Store Admin</title>
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
            margin-bottom: 24px;
            width: 100%;
        }

        .brand-icon-wrap {
            font-size: 32px;
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
            margin-top: 5px;
            line-height: 1.5;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
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
            padding: 0 16px 0 40px;
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

        .success-message {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #059669;
            padding: 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.5;
            width: 100%;
            box-sizing: border-box;
        }

        .back-link-box {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e4e6eb;
            width: 100%;
        }

        .back-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
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
                margin-bottom: 20px;
            }

            .brand-icon-wrap {
                font-size: 28px;
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
                    <i class="fa-solid fa-key"></i>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your admin email and we will send a password reset link to your Gmail inbox.</p>
            </div>

            @if(session('status'))
                <div class="success-message">
                    <i class="fa-solid fa-circle-check" style="font-size: 16px; margin-top: 2px;"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if($errors->any())
                <ul class="error-message">
                    @foreach($errors->all() as $error)
                        <li><i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('admin.password.email') }}" method="POST" style="width: 100%; box-sizing: border-box;">
                @csrf
                <div class="form-group">
                    <label for="email">Admin Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon-left"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="admin@khelostore.com" value="{{ old('email', 'admin@khelostore.com') }}" required autofocus>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Reset Link</span>
                </button>
            </form>

            <div class="back-link-box">
                <a href="{{ route('admin.login') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Sign In
                </a>
            </div>
        </div>
    </div>

</body>
</html>
