<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập — Quản Lý Học Sinh THPT</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 50%, #1a8a6e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Hiệu ứng hình tròn nền */
        body::before {
            content: '';
            position: fixed;
            top: -150px; right: -150px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -150px; left: -100px;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }

        /* Các hình trang trí */
        .bg-shape {
            position: fixed;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            pointer-events: none;
        }
        .bg-shape-1 { width:300px; height:300px; top:10%; left:5%;  }
        .bg-shape-2 { width:200px; height:200px; top:60%; right:8%; }
        .bg-shape-3 { width:150px; height:150px; top:30%; right:20%; }

        /* Card đăng nhập */
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            animation: slideUp 0.5s ease;
            position: relative;
            z-index: 10;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Header card */
        .login-header {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            padding: 36px 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-header::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 150px; height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -30px; left: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .school-logo {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.15);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 36px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
            position: relative; z-index: 1;
        }

        .login-header h4 {
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0 0 6px;
            position: relative; z-index: 1;
        }

        .login-header p {
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            margin: 0;
            position: relative; z-index: 1;
        }

        /* Body card */
        .login-body {
            padding: 36px 40px 32px;
        }

        /* Form label */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #374151;
            margin-bottom: 6px;
        }

        /* Input */
        .form-control {
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: #fafafa;
        }
        .form-control:focus {
            border-color: #2d6a9f;
            box-shadow: 0 0 0 3px rgba(45,106,159,0.12);
            background: white;
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            background: #fff8f8;
        }

        /* Input group icon */
        .input-group-text {
            background: #f3f4f6;
            border: 1.5px solid #e5e7eb;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #6b7280;
            padding: 11px 14px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #2d6a9f;
            color: #2d6a9f;
        }

        /* Nút toggle password */
        .btn-password {
            border: 1.5px solid #e5e7eb;
            border-left: none;
            border-radius: 0 10px 10px 0 !important;
            background: #f3f4f6;
            color: #6b7280;
            padding: 11px 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-password:hover { background: #e5e7eb; color: #374151; }
        .input-group:focus-within .btn-password { border-color: #2d6a9f; }

        /* Checkbox */
        .form-check-input:checked {
            background-color: #2d6a9f;
            border-color: #2d6a9f;
        }
        .form-check-label { font-size: 0.85rem; color: #6b7280; }

        /* Forgot link */
        .forgot-link {
            color: #2d6a9f;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; color: #1e3a5f; }

        /* Nút đăng nhập */
        .btn-login {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-size: 0.95rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #162d4a, #245a8c);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(45,106,159,0.4);
            color: white;
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled {
            opacity: 0.7;
            transform: none;
            cursor: not-allowed;
        }

        /* Loading spinner trong nút */
        .btn-login .spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: #9ca3af;
            font-size: 0.8rem;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* Thông tin tài khoản demo */
        .demo-accounts {
            background: #f0f7ff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 14px 16px;
        }
        .demo-accounts h6 {
            color: #1e40af;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .demo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #e0eeff;
            cursor: pointer;
            transition: background 0.15s;
            border-radius: 6px;
            padding: 6px 8px;
            margin: 0 -8px;
        }
        .demo-item:last-child { border-bottom: none; }
        .demo-item:hover { background: #dbeafe; }
        .demo-role {
            font-size: 0.75rem;
            font-weight: 600;
            color: #1e40af;
        }
        .demo-email {
            font-size: 0.72rem;
            color: #6b7280;
            font-family: monospace;
        }
        .demo-badge {
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 20px;
        }

        /* Footer */
        .login-footer {
            text-align: center;
            padding: 0 40px 28px;
        }
        .login-footer p {
            font-size: 0.75rem;
            color: #9ca3af;
            margin: 0;
        }

        /* Alert error */
        .alert-login {
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 10px 14px;
            border: none;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-header { padding: 28px 24px 22px; }
            .login-body   { padding: 28px 24px 24px; }
            .login-footer { padding: 0 24px 22px; }
        }
    </style>
</head>
<body>

    <!-- Hình trang trí nền -->
    <div class="bg-shape bg-shape-1"></div>
    <div class="bg-shape bg-shape-2"></div>
    <div class="bg-shape bg-shape-3"></div>

    <div class="login-card">

        <!-- Header -->
        <div class="login-header">
            <div class="school-logo">🎓</div>
            <h4>Quản Lý Học Sinh THPT Lý Thường Kiệt</h4>
            <p>Vui lòng đăng nhập để tiếp tục</p>
        </div>

        <!-- Body -->
        <div class="login-body">

            {{-- Thông báo lỗi --}}
            @if(session('error'))
                <div class="alert alert-danger alert-login d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success alert-login d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">
                        Địa chỉ Email
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="example@school.com"
                               autocomplete="email"
                               autofocus
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Mật khẩu --}}
                <div class="mb-3">
                    <label for="password" class="form-label">
                        Mật khẩu
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Nhập mật khẩu..."
                               autocomplete="current-password"
                               required>
                        <button type="button"
                                class="btn btn-password"
                                id="togglePassword"
                                tabindex="-1">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Ghi nhớ & Quên mật khẩu --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <div class="text-center mt-3" style="font-size:0.85rem;color:#6b7280">
    Chưa có tài khoản?
    <a href="{{ route('register') }}" style="color:#3b82f6;font-weight:600;text-decoration:none">
        Đăng ký ngay
    </a>
</div>
                </div>

                {{-- Nút đăng nhập --}}
                <button type="submit" class="btn btn-login" id="btnLogin">
                    <span class="btn-text d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Đăng nhập
                    </span>
                    <span class="spinner mx-auto" id="loadingSpinner"></span>
                </button>
            </form>

            {{-- Tài khoản demo --}}
            <div class="divider">Tài khoản thử nghiệm</div>

            <div class="demo-accounts">
                <h6>
                    <i class="bi bi-key me-1"></i>
                    Nhấn để điền nhanh thông tin đăng nhập:
                </h6>

                <div class="demo-item"
                     onclick="fillLogin('admin@school.com','Admin@123456')">
                    <div>
                        <div class="demo-role">Quản trị viên</div>
                        <div class="demo-email">admin@school.com</div>
                    </div>
                    <span class="badge bg-danger demo-badge">Admin</span>
                </div>

                <div class="demo-item"
                     onclick="fillLogin('hoa.gvcn@school.com','Gvcn@123456')">
                    <div>
                        <div class="demo-role">Giáo viên chủ nhiệm</div>
                        <div class="demo-email">hoa.gvcn@school.com</div>
                    </div>
                    <span class="badge bg-warning text-dark demo-badge">GVCN</span>
                </div>

                <div class="demo-item"
                     onclick="fillLogin('minh.gv@school.com','Gv@123456')">
                    <div>
                        <div class="demo-role">Giáo viên bộ môn</div>
                        <div class="demo-email">minh.gv@school.com</div>
                    </div>
                    <span class="badge bg-info demo-badge">GV</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>
                <i class="bi bi-shield-check me-1 text-success"></i>
                Hệ thống quản lý học sinh THPT &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle hiển thị mật khẩu
        document.getElementById('togglePassword').addEventListener('click', function () {
            const pwd     = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type          = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                pwd.type          = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });

        // Điền nhanh tài khoản demo
        function fillLogin(email, password) {
            document.getElementById('email').value    = email;
            document.getElementById('password').value = password;
            document.getElementById('password').type  = 'password';
            document.getElementById('eyeIcon').className = 'bi bi-eye';

            // Hiệu ứng highlight
            ['email','password'].forEach(id => {
                const el = document.getElementById(id);
                el.style.transition   = 'background 0.3s';
                el.style.background   = '#e8f4fd';
                setTimeout(() => { el.style.background = ''; }, 600);
            });
        }

        // Loading khi submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn     = document.getElementById('btnLogin');
            const text    = btn.querySelector('.btn-text');
            const spinner = document.getElementById('loadingSpinner');
            btn.disabled          = true;
            text.style.display    = 'none';
            spinner.style.display = 'block';
        });

        // Focus email nếu chưa có giá trị
        window.addEventListener('load', () => {
            const email = document.getElementById('email');
            if (!email.value) email.focus();
            else document.getElementById('password').focus();
        });
    </script>
</body>
</html>