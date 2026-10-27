<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255', new \App\Rules\HoTen()],
            'email'    => ['required', 'email:rfc', 'unique:users,email'],
            'phone'    => ['nullable', new \App\Rules\SoDienThoai()],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
            ],
        ], [
            'name.required'      => 'Vui lòng nhập họ và tên.',
            'email.required'     => 'Vui lòng nhập địa chỉ email.',
            'email.email'        => 'Địa chỉ email không hợp lệ.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.mixed_case'=> 'Mật khẩu phải có cả chữ hoa và chữ thường.',
            'password.numbers'   => 'Mật khẩu phải có ít nhất 1 chữ số.',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            // Tài khoản tự đăng ký phải chờ quản trị viên kích hoạt
            'is_active' => false,
        ]);

        // Gán role mặc định là giáo viên bộ môn (quyền thấp nhất); admin sẽ điều chỉnh khi duyệt
        $user->assignRole('giao-vien-bo-mon');

        return redirect()->route('login')
            ->with('success', 'Đăng ký thành công! Tài khoản của bạn đang chờ quản trị viên kích hoạt.');
    }
}