<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TaiKhoanController extends Controller
{


    public function index(Request $request)
    {
        $query = User::with('roles')
            ->when($request->search, fn($q) => 
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->has('is_active') && $request->is_active !== '',
                fn($q) => $q->where('is_active', $request->is_active)
            )
            ->orderBy('name');

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('tai-khoan.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('tai-khoan.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users|max:100',
            'phone' => 'nullable|string|max:15',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ], [
            'email.unique' => 'Email này đã được sử dụng!',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự!',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('tai-khoan.index')
            ->with('success', "Đã tạo tài khoản cho {$user->name} thành công!");
    }

    public function edit(User $taiKhoan)
    {
        $roles = Role::all();
        return view('tai-khoan.edit', compact('taiKhoan', 'roles'));
    }

    public function update(Request $request, User $taiKhoan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$taiKhoan->id}|max:100",
            'phone' => 'nullable|string|max:15',
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);
// ❌ Không cho phép thay đổi vai trò của chính mình
    if ($taiKhoan->id === auth()->id()) {
        if ($request->role !== $taiKhoan->getRoleNames()->first()) {
            return back()
                ->withInput()
                ->with('error', 'Bạn không thể thay đổi vai trò của chính mình!');
        }
    }
// ❌ Không cho phép đổi vai trò quản trị viên sang vai trò khác
    if ($taiKhoan->hasRole('quan-tri-vien') && $request->role !== 'quan-tri-vien') {
        return back()
            ->withInput()
            ->with('error', 'Không thể thay đổi vai trò của tài khoản Quản trị viên!');
    }
        $taiKhoan->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($validated['password']) {
            $taiKhoan->update(['password' => Hash::make($validated['password'])]);
        }

        // Cập nhật role
        $taiKhoan->syncRoles([$validated['role']]);

        return redirect()->route('tai-khoan.index')
            ->with('success', "Đã cập nhật tài khoản {$taiKhoan->name} thành công!");
    }

    public function destroy(User $taiKhoan)
    {
        if ($taiKhoan->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập!');
        }

        $tenUser = $taiKhoan->name;
        $taiKhoan->delete();

        return redirect()->route('tai-khoan.index')
            ->with('success', "Đã xóa tài khoản {$tenUser}!");
    }

    // Kích hoạt / vô hiệu hóa tài khoản
    public function toggleActive(User $taiKhoan)
    {
        $taiKhoan->update(['is_active' => !$taiKhoan->is_active]);
        $trangThai = $taiKhoan->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        
        return back()->with('success', "Đã {$trangThai} tài khoản {$taiKhoan->name}!");
    }
}