<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký — Quản Lý Học Sinh THPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a2a4a 0%, #1a3a5c 40%, #1a5a4a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative circles */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        body::before { width: 500px; height: 500px; top: -150px; left: -150px; }
        body::after  { width: 400px; height: 400px; bottom: -100px; right: -100px; }

        .register-wrapper {
            width: 100%;
            max-width: 520px;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 28px;
            color: #fff;
        }
        .logo-box {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.12);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .register-header h1 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .register-header p {
            font-size: 0.875rem;
            opacity: 0.75;
        }

        /* Card */
        .register-card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        /* Form fields */
        .field-group { margin-bottom: 18px; }
        .field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .field-label .req { color: #ef4444; }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
        }
        .form-input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: border-color .2s, box-shadow .2s;
            background: #f9fafb;
            outline: none;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
            background: #fff;
        }
        .form-input.is-invalid { border-color: #ef4444; }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }

        .toggle-pw {
            position: absolute;
            right: 12px;
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: 1rem; padding: 4px;
        }
        .toggle-pw:hover { color: #374151; }

        .invalid-msg {
            font-size: 0.78rem;
            color: #ef4444;
            margin-top: 4px;
        }

        /* Password strength */
        .pw-strength { margin-top: 8px; }
        .pw-strength-bar {
            height: 4px;
            border-radius: 4px;
            background: #e5e7eb;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .pw-strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .3s, background .3s;
            width: 0%;
        }
        .pw-reqs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 12px;
        }
        .pw-req {
            font-size: 0.72rem;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color .2s;
        }
        .pw-req.met { color: #10b981; }
        .pw-req i { font-size: 0.7rem; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 20px 0 16px;
            color: #9ca3af; font-size: 0.78rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: #e5e7eb;
        }

        /* Submit */
        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1a3a5c, #1a5a4a);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-register:hover  { opacity: .9; }
        .btn-register:active { transform: scale(.99); }

        .login-link {
            text-align: center;
            margin-top: 18px;
            font-size: 0.85rem;
            color: #6b7280;
        }
        .login-link a {
            color: #3b82f6;
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover { text-decoration: underline; }

        /* Alert */
        .alert-success-custom {
            background: #f0fdf4; border: 1px solid #86efac;
            border-radius: 10px; padding: 12px 16px;
            color: #166534; font-size: 0.85rem;
            display: flex; gap: 10px; align-items: flex-start;
            margin-bottom: 20px;
        }
        .alert-error-custom {
            background: #fef2f2; border: 1px solid #fca5a5;
            border-radius: 10px; padding: 12px 16px;
            color: #991b1b; font-size: 0.85rem;
            display: flex; gap: 10px; align-items: flex-start;
            margin-bottom: 20px;
        }

        /* Note */
        .register-note {
            background: #fffbeb; border: 1px solid #fde68a;
            border-radius: 10px; padding: 10px 14px;
            font-size: 0.78rem; color: #92400e;
            display: flex; gap: 8px; align-items: flex-start;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .register-card { padding: 28px 24px; }
            .pw-reqs { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="register-wrapper">

    {{-- Header --}}
    <div class="register-header">
        <div class="logo-box">🎓</div>
        <h1>Quản Lý Học Sinh THPT </h1>
        <p>Tạo tài khoản giáo viên mới</p>
    </div>

    <div class="register-card">

        {{-- Success --}}
        @if(session('success'))
        <div class="alert-success-custom">
            <i class="bi bi-check-circle-fill flex-shrink-0 mt-1"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
        <div class="alert-error-custom">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Note --}}
        <div class="register-note">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <div>Tài khoản mới sẽ được tạo với vai trò <strong>Giáo viên</strong>.
            Quản trị viên có thể thay đổi sau khi đăng ký.</div>
        </div>

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            {{-- Họ tên --}}
            <div class="field-group">
                <label class="field-label" for="name">
                    Họ và tên <span class="req">*</span>
                </label>
                <div class="input-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" id="name" name="name"
                           class="form-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           value="{{ old('name') }}"
                           placeholder="Nguyễn Văn A"
                           autocomplete="name" required>
                </div>
                @error('name')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="field-group">
                <label class="field-label" for="email">
                    Địa chỉ Email <span class="req">*</span>
                </label>
                <div class="input-wrap">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" id="email" name="email"
                           class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                           value="{{ old('email') }}"
                           placeholder="example@school.com"
                           autocomplete="email" required>
                </div>
                @error('email')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror
            </div>

            {{-- Số điện thoại --}}
            <div class="field-group">
                <label class="field-label" for="phone">Số điện thoại</label>
                <div class="input-wrap">
                    <i class="bi bi-telephone input-icon"></i>
                    <input type="tel" id="phone" name="phone"
                           class="form-input"
                           value="{{ old('phone') }}"
                           placeholder="09xxxxxxxx">
                </div>
            </div>

            {{-- Mật khẩu --}}
            <div class="field-group">
                <label class="field-label" for="password">
                    Mật khẩu <span class="req">*</span>
                </label>
                <div class="input-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                           class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="Tối thiểu 8 ký tự"
                           autocomplete="new-password" required
                           oninput="checkStrength(this.value)">
                    <button type="button" class="toggle-pw" onclick="togglePw('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-msg">{{ $message }}</div>
                @enderror

                {{-- Strength bar --}}
                <div class="pw-strength">
                    <div class="pw-strength-bar">
                        <div class="pw-strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="pw-reqs">
                        <div class="pw-req" id="req-len">
                            <i class="bi bi-circle-fill"></i> Ít nhất 8 ký tự
                        </div>
                        <div class="pw-req" id="req-upper">
                            <i class="bi bi-circle-fill"></i> Có chữ hoa
                        </div>
                        <div class="pw-req" id="req-lower">
                            <i class="bi bi-circle-fill"></i> Có chữ thường
                        </div>
                        <div class="pw-req" id="req-num">
                            <i class="bi bi-circle-fill"></i> Có chữ số
                        </div>
                    </div>
                </div>
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="field-group">
                <label class="field-label" for="password_confirmation">
                    Xác nhận mật khẩu <span class="req">*</span>
                </label>
                <div class="input-wrap">
                    <i class="bi bi-lock-fill input-icon"></i>
                    <input type="password" id="password_confirmation"
                           name="password_confirmation"
                           class="form-input"
                           placeholder="Nhập lại mật khẩu"
                           autocomplete="new-password" required
                           oninput="checkMatch()">
                    <button type="button" class="toggle-pw"
                            onclick="togglePw('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="invalid-msg" id="matchMsg" style="display:none">
                    Mật khẩu xác nhận không khớp
                </div>
            </div>

            {{-- Submit --}}
            <div class="divider">hoặc đã có tài khoản</div>

            <button type="submit" class="btn-register" id="submitBtn">
                <i class="bi bi-person-plus-fill"></i>
                Tạo tài khoản
            </button>
        </form>

        <div class="login-link">
            Đã có tài khoản?
            <a href="{{ route('login') }}">Đăng nhập ngay</a>
        </div>
    </div>
</div>

<script>
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function checkStrength(val) {
    const reqs = {
        'req-len':   val.length >= 8,
        'req-upper': /[A-Z]/.test(val),
        'req-lower': /[a-z]/.test(val),
        'req-num':   /[0-9]/.test(val),
    };

    let met = Object.values(reqs).filter(Boolean).length;

    Object.entries(reqs).forEach(([id, ok]) => {
        const el = document.getElementById(id);
        el.classList.toggle('met', ok);
        el.querySelector('i').className = ok
            ? 'bi bi-check-circle-fill'
            : 'bi bi-circle-fill';
    });

    const fill   = document.getElementById('strengthFill');
    const colors = ['#ef4444','#f97316','#eab308','#10b981'];
    fill.style.width      = (met / 4 * 100) + '%';
    fill.style.background = colors[met - 1] || '#e5e7eb';

    checkMatch();
}

function checkMatch() {
    const pw    = document.getElementById('password').value;
    const conf  = document.getElementById('password_confirmation').value;
    const msg   = document.getElementById('matchMsg');
    const input = document.getElementById('password_confirmation');

    if (conf.length > 0) {
        const ok = pw === conf;
        msg.style.display = ok ? 'none' : 'block';
        input.style.borderColor = ok ? '' : '#ef4444';
    } else {
        msg.style.display = 'none';
        input.style.borderColor = '';
    }
}

// Thêm loading khi submit
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
    btn.disabled  = true;
});
</script>
</body>
</html>