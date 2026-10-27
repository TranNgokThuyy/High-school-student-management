<?php
namespace App\Http\Controllers;

use App\Models\HocSinh;
use App\Models\User;
use App\Services\KetQuaPhuHuynhService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Cổng phụ huynh: đăng ký bằng mã học sinh + mã tra cứu, theo dõi nhiều con trong một tài khoản
class PhuHuynhController extends Controller
{
    public const QUAN_HE = ['Cha', 'Mẹ', 'Người giám hộ'];

    private const LOI_MA = 'Mã học sinh hoặc mã tra cứu không đúng.';

    public function dangKyForm()
    {
        return view('phu-huynh.dang-ky', ['quanHe' => self::QUAN_HE]);
    }

    public function dangKy(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', new \App\Rules\HoTen()],
            'email'       => 'required|email:rfc|max:255|unique:users,email',
            'phone'       => ['nullable', new \App\Rules\SoDienThoai()],
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'quan_he'     => 'required|in:' . implode(',', self::QUAN_HE),
            'ma_hoc_sinh' => 'required|string|max:30',
            'ma_tra_cuu'  => 'required|string|max:20',
        ], [
            'email.unique'        => 'Email này đã được sử dụng. Nếu đã có tài khoản, hãy đăng nhập rồi thêm con.',
            'password.confirmed'  => 'Xác nhận mật khẩu không khớp.',
        ]);

        $hocSinh = HocSinh::timTheoMaTraCuu($validated['ma_hoc_sinh'], $validated['ma_tra_cuu']);
        if (!$hocSinh) {
            return back()->withInput($request->except('password', 'password_confirmation', 'ma_tra_cuu'))
                ->withErrors(['ma_hoc_sinh' => self::LOI_MA]);
        }

        // Mã tra cứu chỉ nhà trường phát cho gia đình nên tài khoản được kích hoạt ngay
        $user = DB::transaction(function () use ($validated, $hocSinh) {
            $user = User::create([
                'name' => $validated['name'], 'email' => $validated['email'], 'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']), 'is_active' => true,
            ]);
            $user->assignRole('phu-huynh');
            $user->con()->attach($hocSinh->id, ['quan_he' => $validated['quan_he']]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('phu-huynh.index')->with('success', "Đã tạo tài khoản và liên kết với học sinh {$hocSinh->ho_ten}.");
    }

    public function index(KetQuaPhuHuynhService $ketQua)
    {
        $con = auth()->user()->con()->with('lopHoc')->orderBy('ho_ten')->get();
        $tomTat = $con->mapWithKeys(fn ($hs) => [$hs->id => $ketQua->tomTat($hs)]);

        return view('phu-huynh.index', ['con' => $con, 'tomTat' => $tomTat, 'quanHe' => self::QUAN_HE]);
    }

    public function show(HocSinh $hocSinh, KetQuaPhuHuynhService $ketQua)
    {
        $this->kiemTraLaCon($hocSinh);

        return view('phu-huynh.show', $ketQua->duLieu($hocSinh));
    }

    public function themCon(Request $request)
    {
        $validated = $request->validate([
            'ma_hoc_sinh' => 'required|string|max:30',
            'ma_tra_cuu'  => 'required|string|max:20',
            'quan_he'     => 'required|in:' . implode(',', self::QUAN_HE),
        ]);

        $hocSinh = HocSinh::timTheoMaTraCuu($validated['ma_hoc_sinh'], $validated['ma_tra_cuu']);
        if (!$hocSinh) {
            return back()->withInput($request->only('ma_hoc_sinh'))->withErrors(['ma_hoc_sinh' => self::LOI_MA]);
        }

        auth()->user()->con()->syncWithoutDetaching([$hocSinh->id => ['quan_he' => $validated['quan_he']]]);

        return redirect()->route('phu-huynh.index')->with('success', "Đã thêm học sinh {$hocSinh->ho_ten}.");
    }

    public function boLienKet(HocSinh $hocSinh)
    {
        $this->kiemTraLaCon($hocSinh);
        auth()->user()->con()->detach($hocSinh->id);

        return redirect()->route('phu-huynh.index')->with('success', "Đã bỏ liên kết với học sinh {$hocSinh->ho_ten}.");
    }

    private function kiemTraLaCon(HocSinh $hocSinh): void
    {
        abort_unless(auth()->user()->con()->whereKey($hocSinh->id)->exists(), 404);
    }
}
