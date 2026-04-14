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

        $request->session()->regenerate();

        // Ghi nhận thời gian đăng nhập cuối
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['last_login_at' => now()]);

        // Redirect về trang dashboard (phân quyền xử lý trong view)
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