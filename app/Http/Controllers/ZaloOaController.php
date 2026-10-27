<?php
namespace App\Http\Controllers;

use App\Models\ZaloToken;
use App\Services\Sms\ZaloTokenService;
use Illuminate\Http\Request;
use Throwable;

// Quản trị viên xem trạng thái kết nối Zalo OA, dán token mới, làm mới thủ công
class ZaloOaController extends Controller
{
    public function index(ZaloTokenService $zalo)
    {
        return view('zalo-oa.index', [
            'token' => ZaloToken::hienTai(),
            'coTheLamMoi' => $zalo->coTheLamMoi(),
            'coToken' => $zalo->coToken(),
            'cauHinh' => [
                'ZALO_APP_ID' => filled(config('services.zalo.app_id')),
                'ZALO_APP_SECRET' => filled(config('services.zalo.app_secret')),
                'ZALO_ZNS_TEMPLATE_ID' => filled(config('services.zalo.template_id')),
            ],
        ]);
    }

    public function luu(Request $request, ZaloTokenService $zalo)
    {
        $validated = $request->validate([
            'access_token' => 'required|string|max:2000',
            'refresh_token' => 'nullable|string|max:2000',
            'het_han_gio' => 'nullable|integer|min:1|max:48',
        ]);

        $zalo->luu($validated['access_token'], $validated['refresh_token'] ?? null, (int) ($validated['het_han_gio'] ?? 25) * 3600);

        return redirect()->route('zalo-oa.index')->with('success', 'Đã lưu token Zalo OA.');
    }

    public function lamMoi(ZaloTokenService $zalo)
    {
        if (!$zalo->coTheLamMoi()) {
            return back()->with('error', 'Cần khai báo ZALO_APP_ID, ZALO_APP_SECRET và refresh token trước.');
        }

        try {
            $token = $zalo->lamMoi();
        } catch (Throwable $e) {
            return back()->with('error', 'Làm mới thất bại: ' . $e->getMessage());
        }

        return back()->with('success', 'Đã làm mới token, hết hạn lúc ' . $token->het_han_luc->format('H:i d/m/Y') . '.');
    }
}
