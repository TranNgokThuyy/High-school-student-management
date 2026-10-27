<?php
namespace App\Http\Controllers;

use App\Support\Totp;
use Illuminate\Http\Request;

// Người dùng tự bật / tắt xác thực 2 bước và tạo lại mã khôi phục
class BaoMatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $qr = $khoa = null;

        if (!$user->daBatHaiBuoc()) {
            // Khóa tạm giữ trong phiên cho tới khi người dùng nhập đúng mã lần đầu
            $khoa = $request->session()->get('khoa_2fa_tam') ?? tap(Totp::taoKhoa(), fn ($k) => $request->session()->put('khoa_2fa_tam', $k));
            $qr = Totp::qrSvg(Totp::uri($khoa, $user->email, config('app.ten_truong') ?: config('app.name')));
        }

        return view('bao-mat.index', [
            'user' => $user,
            'khoa' => $khoa ? trim(chunk_split($khoa, 4, ' ')) : null,
            'qr' => $qr,
            'maKhoiPhuc' => session('ma_khoi_phuc'),
        ]);
    }

    public function bat(Request $request)
    {
        $request->validate(['ma' => 'required|string']);
        $user = $request->user();
        $khoa = $request->session()->get('khoa_2fa_tam');

        $buoc = $khoa ? Totp::kiemTra($khoa, $request->ma) : null;
        if ($buoc === null) {
            return back()->withErrors(['ma' => 'Mã xác thực không đúng. Kiểm tra lại giờ trên điện thoại và nhập mã mới nhất.']);
        }

        $maKhoiPhuc = Totp::taoMaKhoiPhuc();
        $user->forceFill([
            'two_factor_secret' => $khoa,
            'two_factor_recovery_codes' => $maKhoiPhuc,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => $buoc,
        ])->save();
        $request->session()->forget('khoa_2fa_tam');

        return redirect()->route('bao-mat.index')
            ->with('success', 'Đã bật xác thực 2 bước.')
            ->with('ma_khoi_phuc', $maKhoiPhuc);
    }

    public function taoLaiMaKhoiPhuc(Request $request)
    {
        $request->validate(['current_password' => 'required|current_password'], ['current_password.current_password' => 'Mật khẩu không đúng.']);
        abort_unless($request->user()->daBatHaiBuoc(), 404);

        $maKhoiPhuc = Totp::taoMaKhoiPhuc();
        $request->user()->forceFill(['two_factor_recovery_codes' => $maKhoiPhuc])->save();

        return redirect()->route('bao-mat.index')
            ->with('success', 'Đã tạo bộ mã khôi phục mới, các mã cũ không còn dùng được.')
            ->with('ma_khoi_phuc', $maKhoiPhuc);
    }

    public function tat(Request $request)
    {
        $request->validate(['current_password' => 'required|current_password'], ['current_password.current_password' => 'Mật khẩu không đúng.']);
        $user = $request->user();

        if ($user->batBuocHaiBuoc()) {
            return back()->with('error', 'Tài khoản quản trị viên bắt buộc dùng xác thực 2 bước.');
        }

        $user->forceFill([
            'two_factor_secret' => null, 'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null, 'two_factor_last_step' => null,
        ])->save();

        return redirect()->route('bao-mat.index')->with('success', 'Đã tắt xác thực 2 bước.');
    }
}
