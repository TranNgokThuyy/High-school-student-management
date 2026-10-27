<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = $request->user();
        $batBuoc = $user->phai_doi_mat_khau;
        $user->update([
            'password' => Hash::make($validated['password']),
            'phai_doi_mat_khau' => false,
        ]);

        // Vừa đổi mật khẩu bắt buộc xong thì vào trang chính
        if ($batBuoc) {
            return redirect('/')->with('success', 'Đã đổi mật khẩu.');
        }

        return back()->with('status', 'password-updated');
    }
}
