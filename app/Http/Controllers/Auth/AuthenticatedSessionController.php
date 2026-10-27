<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Đã bật xác thực 2 bước: chưa cho đăng nhập, chuyển sang bước nhập mã (hiệu lực 5 phút)
        if ($user->daBatHaiBuoc()) {
            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->put('dang_nhap_2fa', [
                'id' => $user->id, 'ghi_nho' => $request->boolean('remember'), 'het_han' => now()->addMinutes(5)->timestamp,
            ]);

            return redirect()->route('xac-thuc-hai-buoc');
        }

        return self::hoanTatDangNhap($request, $user);
    }

    public static function hoanTatDangNhap(Request $request, \App\Models\User $user): RedirectResponse
    {
        $request->session()->regenerate();

        // Ghi nhận thời gian đăng nhập cuối
        $user->update(['last_login_at' => now()]);

        if ($user->laPhuHuynh()) {
            return redirect()->route('phu-huynh.index');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Đăng xuất
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}