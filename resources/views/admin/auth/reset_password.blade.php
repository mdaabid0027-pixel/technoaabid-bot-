<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Set New Password - Khelo Store Admin</title>
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
            margin-top: 24px;
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
                    <i class="fa-solid fa-lock-open"></i>
                </div>
                <h1>Create New Password</h1>
                <p>Enter your new admin password below.</p>
            </div>

            @if($errors->any())
                <ul class="error-message">
                    @foreach($errors->all() as $error)
                        <li><i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i> {{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('admin.password.store') }}" method="POST" style="width: 100%; box-sizing: border-box;">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon-left"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required autofocus minlength="6">
                        <button type="button" class="btn-toggle-eye" onclick="togglePass('password', this)" title="Show/Hide Password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-check-double input-icon-left"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password" required minlength="6">
                        <button type="button" class="btn-toggle-eye" onclick="togglePass('password_confirmation', this)" title="Show/Hide Password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Set New Password & Login</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePass(fieldId, btn) {
            const input = document.getElementById(fieldId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }
    </script>
</body>
</html>
