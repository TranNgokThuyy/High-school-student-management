<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Chặn mọi trang cho tới khi người dùng đổi mật khẩu được cấp / bật xác thực 2 bước (nếu bắt buộc)
class YeuCauBaoMat
{
    private const LUON_CHO_PHEP = ['logout', 'api.logout', 'api.me', 'api.csrf-token'];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $request->routeIs(...self::LUON_CHO_PHEP)) {
            return $next($request);
        }

        if ($user->phai_doi_mat_khau && !$request->routeIs('doi-mat-khau', 'password.update')) {
            return $this->chan($request, 'doi-mat-khau', 'Vui lòng đổi mật khẩu được cấp trước khi tiếp tục.');
        }

        if (!$user->phai_doi_mat_khau && $user->batBuocHaiBuoc() && !$user->daBatHaiBuoc() && !$request->routeIs('bao-mat.*')) {
            return $this->chan($request, 'bao-mat.index', 'Tài khoản quản trị viên phải bật xác thực 2 bước trước khi tiếp tục.');
        }

        return $next($request);
    }

    private function chan(Request $request, string $route, string $thongBao)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $thongBao], 403);
        }

        return redirect()->route($route)->with('error', $thongBao);
    }
}
