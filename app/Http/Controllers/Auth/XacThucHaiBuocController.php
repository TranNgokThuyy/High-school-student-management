<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Bước 2 của đăng nhập: mật khẩu đúng rồi mới hỏi mã từ ứng dụng xác thực
class XacThucHaiBuocController extends Controller
{
    public function create(Request $request)
    {
        if (!$this->nguoiDangCho($request)) {
            return redirect()->route('login');
        }

        return view('auth.xac-thuc-hai-buoc');
    }

    public function store(Request $request)
    {
        $request->validate(['ma' => 'required|string|max:20'], ['ma.required' => 'Vui lòng nhập mã xác thực.']);

        $user = $this->nguoiDangCho($request);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại.']);
        }

        if (!$user->kiemTraMaHaiBuoc($request->ma)) {
            return back()->withErrors(['ma' => 'Mã xác thực không đúng.']);
        }

        $ghiNho = $request->session()->pull('dang_nhap_2fa')['ghi_nho'] ?? false;
        Auth::login($user, $ghiNho);

        return AuthenticatedSessionController::hoanTatDangNhap($request, $user);
    }

    private function nguoiDangCho(Request $request): ?User
    {
        $cho = $request->session()->get('dang_nhap_2fa');
        if (!$cho || $cho['het_han'] < now()->timestamp) {
            $request->session()->forget('dang_nhap_2fa');

            return null;
        }

        $user = User::find($cho['id']);

        return $user && $user->is_active && $user->daBatHaiBuoc() ? $user : null;
    }
}
