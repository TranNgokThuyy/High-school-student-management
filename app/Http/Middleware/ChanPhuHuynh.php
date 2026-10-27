<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Tài khoản phụ huynh chỉ dùng cổng /phu-huynh, không vào được các trang của cán bộ
class ChanPhuHuynh
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->laPhuHuynh()) {
            if ($request->isMethod('GET') && !$request->expectsJson()) {
                return redirect()->route('phu-huynh.index');
            }
            abort(403, 'Tài khoản phụ huynh không có quyền thực hiện thao tác này!');
        }

        return $next($request);
    }
}
