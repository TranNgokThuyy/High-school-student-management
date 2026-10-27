<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function csrfToken(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'ma_xac_thuc' => ['nullable', 'string', 'max:20'],
        ]);
        unset($credentials['ma_xac_thuc']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email hoặc mật khẩu không chính xác.']);
        }

        if (! $request->user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => 'Tài khoản đã bị vô hiệu hóa.']);
        }

        // Tài khoản bật xác thực 2 bước phải gửi kèm mã từ ứng dụng xác thực (hoặc mã khôi phục)
        $user = $request->user();
        if ($user->daBatHaiBuoc() && ! $user->kiemTraMaHaiBuoc((string) $request->input('ma_xac_thuc'))) {
            Auth::guard('web')->logout();
            throw ValidationException::withMessages(['ma_xac_thuc' => 'Cần mã xác thực 2 bước hợp lệ.']);
        }

        $request->session()->regenerate();
        $request->user()->update(['last_login_at' => now()]);

        return $this->me($request);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Đã đăng xuất.']);
    }
}
