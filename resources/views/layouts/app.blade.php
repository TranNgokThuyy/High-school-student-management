<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa')
    <title>{{ config('app.name') }} - @yield('title', 'Trang chủ')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        body { background-color: #f0f2f5; }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a5f 0%, #2d5a8e 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-brand {
            padding: 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h5 {
            color: white;
            font-weight: 700;
            font-size: 0.95rem;
            margin: 0;
            line-height: 1.4;
        }
        
        .sidebar-nav .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 10px 20px;
            border-radius: 0;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.15);
            padding-left: 28px;
        }
        
        .sidebar-nav .nav-section {
            color: rgba(255,255,255,0.4);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 16px 20px 6px;
        }
        
        /* Main content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }
        
        /* Topbar */
        .topbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .content-area {
            padding: 24px;
        }
        
        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        
        .stat-card:hover { transform: translateY(-2px); }
        
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }
        
        /* Tables */
        .table-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
        
        /* Badges */
        .badge-role {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 20px;
        }

        /* Alert flash messages */
        .alert-flash {
            position: fixed;
            top: 70px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media print {
            .sidebar, .topbar, .no-print, .alert-flash { display: none !important; }
            .main-content { margin-left: 0; }
            body { background: #fff; }
        }

        /* Responsive */
        .sidebar-backdrop { display: none; }
        @media (max-width: 767.98px) {
            .sidebar { transform: translateX(-100%); box-shadow: none; }
            .sidebar.show { transform: translateX(0); box-shadow: 0 0 24px rgba(0,0,0,.35); }
            .sidebar-backdrop.show { display: block; position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 999; }
            body.sidebar-open { overflow: hidden; }
            .main-content { margin-left: 0; }
            .topbar { padding: 0 12px; z-index: 998; }
            .content-area { padding: 16px 12px; }
            /* Tiêu đề trang + nút thao tác: xuống dòng thay vì bóp chữ */
            .content-area > .d-flex.justify-content-between { flex-wrap: wrap; gap: .5rem; }
            .content-area > .d-flex.justify-content-between > h4 { font-size: 1.15rem; flex: 1 1 100%; }
            .table thead th { white-space: nowrap; }
            .alert-flash { left: 12px; right: 12px; min-width: 0; }
            /* Bảng nhập liệu nhiều cột: giữ cột họ tên khi cuộn ngang */
            .bang-co-dinh th:nth-child(2), .bang-co-dinh td:nth-child(2) { position: sticky; left: 0; z-index: 1; background: #fff; box-shadow: 1px 0 0 #e5e7eb; }
            .bang-co-dinh thead th:nth-child(2) { background: #f8fafc; }
            .bang-co-dinh th:first-child, .bang-co-dinh td:first-child { display: none; }
            .form-control, .form-select { font-size: 16px; } /* iOS không tự phóng to khi chạm ô nhập */
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-warning rounded-2 p-2">
                    <i class="bi bi-mortarboard-fill text-dark fs-5"></i>
                </div>
                <div>
                    <h5>Quản Lý<br>Học Sinh THPT</h5>
                </div>
            </div>
        </div>
        
        <div class="sidebar-nav mt-2">
            @if(auth()->user()->laPhuHuynh())
            <span class="nav-section">Phụ huynh</span>
            <a href="{{ route('phu-huynh.index') }}" class="nav-link {{ request()->routeIs('phu-huynh.index') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Con của tôi
            </a>
            @foreach(auth()->user()->con()->orderBy('ho_ten')->get() as $conPH)
            <a href="{{ route('phu-huynh.show', $conPH) }}" class="nav-link {{ request()->route('hocSinh')?->id === $conPH->id ? 'active' : '' }}">
                <i class="bi bi-person"></i> {{ $conPH->ho_ten }}
            </a>
            @endforeach
            <span class="nav-section">Tài khoản</span>
            <a href="{{ route('doi-mat-khau') }}" class="nav-link {{ request()->routeIs('doi-mat-khau') ? 'active' : '' }}">
                <i class="bi bi-key"></i> Đổi mật khẩu
            </a>
            @else
            <span class="nav-section">Tổng quan</span>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{ route('thong-bao.index') }}" class="nav-link {{ request()->routeIs('thong-bao.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone"></i> Thông báo
            </a>
            @can('gui-tin-phu-huynh')
            <a href="{{ route('tin-nhan.index') }}" class="nav-link {{ request()->routeIs('tin-nhan.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Tin nhắn phụ huynh
            </a>
            @endcan
            
            @can('xem-hoc-sinh')
            <span class="nav-section">Học sinh</span>
            <a href="{{ route('hoc-sinh.index') }}" class="nav-link {{ request()->routeIs('hoc-sinh.*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Quản lý học sinh
            </a>
            @endcan
            
            @can('quan-ly-bien-dong')
            <a href="{{ route('bien-dong.index') }}" class="nav-link {{ request()->routeIs('bien-dong.*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Chuyển trường / thôi học
            </a>
            @endcan
            @can('xem-lop-hoc')
            <a href="{{ route('lop-hoc.index') }}" class="nav-link {{ request()->routeIs('lop-hoc.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Quản lý lớp học
            </a>
            @endcan
            @can('xem-diem-danh')
            <a href="{{ route('diem-danh.index') }}" class="nav-link {{ request()->routeIs('diem-danh.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Điểm danh
            </a>
            @endcan
            @can('xem-khen-thuong')
            <a href="{{ route('khen-thuong.index') }}" class="nav-link {{ request()->routeIs('khen-thuong.*') ? 'active' : '' }}">
                <i class="bi bi-trophy"></i> Khen thưởng - Kỷ luật
            </a>
            @endcan
            @can('xem-thoi-khoa-bieu')
<a href="{{ route('thoi-khoa-bieu.index') }}"
   class="nav-link {{ request()->routeIs('thoi-khoa-bieu.*') ? 'active' : '' }}">
    <i class="bi bi-calendar-week"></i> Thời khóa biểu
</a>
@endcan
            <span class="nav-section">Học tập</span>
            @can('xem-mon-hoc')
            <a href="{{ route('mon-hoc.index') }}" 
   class="nav-link {{ request()->routeIs('mon-hoc.*') ? 'active' : '' }}">
    <i class="bi bi-book-half"></i> Quản lý môn học
</a>
            @endcan
            @can('quan-ly-giao-vien')
            <a href="{{ route('giao-vien.index') }}" class="nav-link {{ request()->routeIs('giao-vien.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Giáo viên & giờ dạy
            </a>
            @endcan
            @can('xem-phan-cong')
            <a href="{{ route('phan-cong.index') }}" class="nav-link {{ request()->routeIs('phan-cong.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i> Phân công giảng dạy
            </a>
            @endcan
            @can('xem-diem')
            <a href="{{ route('diem-so.bang-diem') }}" class="nav-link {{ request()->routeIs('diem-so.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Quản lý điểm số
            </a>
            @endcan
            
            @can('xem-hanh-kiem')
            <a href="{{ route('hanh-kiem.index') }}" class="nav-link {{ request()->routeIs('hanh-kiem.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-check"></i> Rèn luyện & Chuyên cần
            </a>
            @endcan
            
            @can('quan-ly-ky-thi')
            <a href="{{ route('ky-thi.index') }}" class="nav-link {{ request()->routeIs('ky-thi.*') ? 'active' : '' }}">
                <i class="bi bi-pencil-square"></i> Kỳ thi & phòng thi
            </a>
            @endcan
            @can('xem-tong-ket')
            <a href="{{ route('tong-ket.index') }}" class="nav-link {{ request()->routeIs('tong-ket.*') ? 'active' : '' }}">
                <i class="bi bi-award"></i> Tổng kết & Lên lớp
            </a>
            @endcan

            @can('xem-hoc-phi')
            <span class="nav-section">Tài chính</span>
            <a href="{{ route('hoc-phi.index') }}" class="nav-link {{ request()->routeIs('hoc-phi.*', 'khoan-thu.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i> Học phí & Khoản thu
            </a>
            @endcan

            @can('xem-bao-cao')
            <span class="nav-section">Báo cáo</span>
            <a href="{{ route('bao-cao.index') }}" class="nav-link {{ request()->routeIs('bao-cao.*') && !request()->routeIs('bao-cao.thong-ke') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Báo cáo
            </a>
            <a href="{{ route('bao-cao.thong-ke') }}" class="nav-link {{ request()->routeIs('bao-cao.thong-ke') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Thống kê
            </a>
            @endcan
            
            @can('xem-tai-khoan')
            <span class="nav-section">Hệ thống</span>
            <a href="{{ route('tai-khoan.index') }}" class="nav-link {{ request()->routeIs('tai-khoan.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i> Quản lý tài khoản
            </a>
            @endcan
            @can('xem-nhat-ky')
            <a href="{{ route('nhat-ky.index') }}" class="nav-link {{ request()->routeIs('nhat-ky.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Nhật ký thao tác
            </a>
            @endcan
            @can('quan-ly-sao-luu')
            <a href="{{ route('sao-luu.index') }}" class="nav-link {{ request()->routeIs('sao-luu.*') ? 'active' : '' }}">
                <i class="bi bi-database-check"></i> Sao lưu dữ liệu
            </a>
            @endcan
            @role('quan-tri-vien')
            <a href="{{ route('zalo-oa.index') }}" class="nav-link {{ request()->routeIs('zalo-oa.*') ? 'active' : '' }}">
                <i class="bi bi-chat-square-text"></i> Kết nối Zalo OA
            </a>
            @endrole
            @endif
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <button class="btn btn-light btn-sm d-md-none" id="toggleSidebar" aria-label="Mở menu" aria-controls="sidebar" aria-expanded="false">
                <i class="bi bi-list fs-5"></i>
            </button>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline">
                    <i class="bi bi-calendar3"></i> {{ now()->format('d/m/Y') }}
                </span>
            </div>
            
            <!-- User dropdown -->
            <div class="dropdown">
                <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2" 
                        data-bs-toggle="dropdown">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                         style="width:32px;height:32px;font-size:13px;font-weight:600;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <span class="small fw-semibold d-none d-sm-inline">{{ auth()->user()->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <div class="dropdown-header">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->email }}</div>
                            <div class="mt-1">
                                @foreach(auth()->user()->getRoleNames() as $role)
                                    <span class="badge bg-primary badge-role">{{ \App\Support\PhanQuyen::TEN_VAI_TRO[$role] ?? $role }}</span>
                                @endforeach
                            </div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('doi-mat-khau') }}"><i class="bi bi-key me-2"></i>Đổi mật khẩu</a></li>
                    <li><a class="dropdown-item" href="{{ route('bao-mat.index') }}"><i class="bi bi-shield-lock me-2"></i>Xác thực 2 bước</a></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content-area">
            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible alert-flash" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible alert-flash" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Menu trên điện thoại: mở/đóng sidebar, bấm nền tối / Esc / chọn mục để đóng
        (function () {
            const sidebar = document.getElementById('sidebar');
            const nen = document.getElementById('sidebarBackdrop');
            const nut = document.getElementById('toggleSidebar');
            const datTrangThai = (mo) => {
                sidebar.classList.toggle('show', mo);
                nen.classList.toggle('show', mo);
                document.body.classList.toggle('sidebar-open', mo);
                nut?.setAttribute('aria-expanded', mo ? 'true' : 'false');
            };
            nut?.addEventListener('click', () => datTrangThai(!sidebar.classList.contains('show')));
            nen.addEventListener('click', () => datTrangThai(false));
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') datTrangThai(false); });
            sidebar.querySelectorAll('a.nav-link').forEach(a => a.addEventListener('click', () => datTrangThai(false)));
        })();
        
        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-flash').forEach(el => {
                new bootstrap.Alert(el).close();
            });
        }, 4000);
    </script>
    
    @include('partials.kiem-tra-form')
    @stack('scripts')
</body>
</html>