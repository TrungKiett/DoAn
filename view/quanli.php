<?php
// Đánh dấu view đã được load để tránh include controller nhiều lần
if (!defined('QUANLI_VIEW_LOADED')) {
    define('QUANLI_VIEW_LOADED', true);
    // Include controller để xử lý logic theo đúng mô hình MVC
    require_once __DIR__ . '/../controller/controller.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/DoAn/public/css/style.css" rel="stylesheet">
</head>
<body>
    <?php
    if (!isset($stats) || !is_array($stats)) {
        $stats = [
            'users' => 0,
            'products' => 0,
            'orders' => 0,
            'revenue' => 0,
        ];
    }
    if (!isset($users) || !is_array($users)) {
        $users = [];
    }
    if (!isset($statusToBadge) || !is_callable($statusToBadge)) {
        $statusToBadge = function (string $status): string {
            switch ($status) {
                case 'Hoạt động':
                    return 'bg-success';
                case 'Chờ duyệt':
                    return 'bg-warning';
                case 'Tạm khóa':
                    return 'bg-danger';
                default:
                    return 'bg-secondary';
            }
        };
    }
    ?>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-white text-center mb-4">
                            <i class="fas fa-cogs me-2"></i>Quản lý
                        </h4>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link" href="#account">
                                <i class="fas fa-users me-2"></i>Quản lý tài khoản
                            </a>
                            <a class="nav-link" href="#products">
                                <i class="fas fa-box me-2"></i>Sản phẩm
                            </a>
                            <a class="nav-link" href="#orders">
                                <i class="fas fa-shopping-cart me-2"></i>Đơn hàng
                            </a>
                            <a class="nav-link" href="#planning">
                                <i class="fas fa-seedling me-2"></i>Lập kế hoạch sản xuất
                            </a>
                            <a class="nav-link" href="#reports">
                                <i class="fas fa-chart-bar me-2"></i>Báo cáo
                            </a>
                            <a class="nav-link" href="#settings">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <?php if ($alert && $alert['type'] && $alert['message']): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($alert['type']); ?> alert-custom">
                        <?php echo htmlspecialchars($alert['message']); ?>
                        <?php if (!empty($alert['errors'])): ?>
                            <ul class="mb-0 mt-2">
                            <?php foreach ($alert['errors'] as $err): ?>
                                <li><?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <!-- Header -->
                    <div class="crm-header">
                        <div>
                            <h2 class="text-dark mb-1">Xin chào, Quản trị viên!</h2>
                            <div class="crm-subtitle">Theo dõi hoạt động bán hàng, khách hàng và đơn hàng tại đây.</div>
                        </div>
                        <div class="d-flex align-items-center filters-group">
                            <div class="search-box me-2">
                                <i class="fas fa-search me-2 text-muted"></i>
                                <input type="text" class="border-0 bg-transparent" placeholder="Tìm kiếm...">
                            </div>
                            <button class="btn btn-outline-secondary"><i class="fas fa-filter me-2"></i>Bộ lọc</button>
                            <button class="btn btn-primary-custom btn-custom ms-2"><i class="fas fa-user-plus me-2"></i>Thêm tài khoản</button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4" id="dashboard-block">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card kpi-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <div class="kpi-value"><?php echo number_format($stats['users']); ?></div>
                                    <div class="crm-subtitle">Tổng người dùng</div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card kpi-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-box fa-2x mb-3"></i>
                                    <div class="kpi-value"><?php echo number_format($stats['products']); ?></div>
                                    <div class="crm-subtitle">Sản phẩm</div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card kpi-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                    <div class="kpi-value"><?php echo number_format($stats['orders']); ?></div>
                                    <div class="crm-subtitle">Đơn hàng</div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card kpi-card">
                                <div class="card-body text-center">
                                    <i class="fas fa-dollar-sign fa-2x mb-3"></i>
                                    <div class="kpi-value"><?php echo '$' . number_format($stats['revenue']); ?></div>
                                    <div class="crm-subtitle">Doanh thu</div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table -->
                    <div class="card" id="account" style="display:none">
                        <div class="card-header">
                            <div class="card-title-bar">
                                <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Quản lý tài khoản</h5>
                                <div>
                                    <span class="chip me-2">Hôm nay</span>
                                    <span class="chip">Tháng này</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên đăng nhập</th>
                                            <th>Họ tên</th>
                                            <th>Số điện thoại</th>
                                            <th>Quyền</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars((string)$user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($user['role'] ?? ''); ?></td>
                                            <td><span class="badge <?php echo $statusToBadge($user['status']); ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                            <td class="table-actions">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="action" value="update_role">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$user['id']); ?>">
                                                    <select name="role" class="form-select form-select-sm d-inline w-auto me-1">
                                                        <option value="nông dân" <?php echo (($user['role'] ?? '')==='nông dân')?'selected':''; ?>>Nông dân</option>
                                                        <option value="quản trị" <?php echo (($user['role'] ?? '')==='quản trị')?'selected':''; ?>>Quản trị</option>
                                                        <option value="phân phối" <?php echo (($user['role'] ?? '')==='phân phối')?'selected':''; ?>>Phân phối</option>
                                                    </select>
                                                    <button class="btn btn-sm btn-outline-secondary me-1" type="submit" title="Phân quyền"><i class="fas fa-user-shield"></i></button>
                                                </form>
                                                <form method="post" class="d-inline delete-form">
                                                    <input type="hidden" name="action" value="delete_user">
                                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$user['id']); ?>">
                                                    <button class="btn btn-sm btn-outline-danger" type="submit" title="Xóa"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-custom justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>

                    <!-- Planning Section -->
                    <div class="card mt-4" id="planning" style="display:none">
                        <div class="card-header">
                            <div class="card-title-bar">
                                <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Lập kế hoạch sản xuất</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="action" value="add_plan">
                                <input type="hidden" name="id" id="plan-id" value="">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Mùa vụ</label>
                                        <input type="text" class="form-control" name="season" id="plan-season" placeholder="VD: Vụ Xuân 2025" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Thời gian canh tác</label>
                                        <input type="text" class="form-control" name="time_range" id="plan-time-range" placeholder="VD: 01/03/2025 - 30/06/2025" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mã lô</label>
                                        <input type="text" class="form-control" name="lot_code" id="plan-lot-code" placeholder="VD: LO-001" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Số lượng công nhân</label>
                                        <input type="number" class="form-control" name="workers" id="plan-workers" min="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Giống</label>
                                        <input type="text" class="form-control" name="seed" id="plan-seed" placeholder="VD: Lúa ST25" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Số lượng giống</label>
                                        <input type="number" class="form-control" name="seed_qty" id="plan-seed-qty" min="1" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Diện tích (ha)</label>
                                        <input type="number" step="0.01" class="form-control" name="area" id="plan-area" min="0.01" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Sản lượng dự kiến (tấn)</label>
                                        <input type="number" class="form-control" name="expected_yield" id="plan-expected-yield" min="1" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tiêu chuẩn chất lượng</label>
                                        <select name="quality" id="plan-quality" class="form-control" required>
                                            <option value="VietGAP">VietGAP</option>
                                            <option value="OCOP">OCOP</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Vật tư (mô tả ngắn)</label>
                                        <textarea class="form-control" name="supplies" id="plan-supplies" rows="3" placeholder="Hạt giống, phân bón, thuốc BVTV..."></textarea>
                                    </div>

                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary-custom btn-custom" id="plan-submit"><i class="fas fa-save me-2"></i>Lưu kế hoạch</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-3 planning-only" style="display:none">
                        <div class="card-header">
                            <div class="card-title-bar">
                                <h6 class="mb-0"><i class="fas fa-th me-2"></i>Các lô canh tác</h6>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Lô 1 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="1" data-lat="10.8231" data-lng="106.6297">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô A1</h6>
                                                <span class="badge bg-success">Sẵn sàng</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng phía Bắc</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 2.5 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="1">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lô 2 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="2" data-lat="10.8241" data-lng="106.6307">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô A2</h6>
                                                <span class="badge bg-warning">Đang chuẩn bị</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng phía Đông</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 3.2 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="2">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lô 3 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="3" data-lat="10.8251" data-lng="106.6317">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô A3</h6>
                                                <span class="badge bg-secondary">Chưa bắt đầu</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng phía Nam</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 1.8 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="3">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lô 4 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="4" data-lat="10.8261" data-lng="106.6327">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô B1</h6>
                                                <span class="badge bg-info">Đang canh tác</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng phía Tây</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 4.1 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="4">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lô 5 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="5" data-lat="10.8271" data-lng="106.6337">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô B2</h6>
                                                <span class="badge bg-success">Hoàn thành</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng trung tâm</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 2.8 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="5">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Lô 6 -->
                                <div class="col-md-4">
                                    <div class="card h-100 lot-card" data-lot="6" data-lat="10.8281" data-lng="106.6347">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h6 class="card-title text-primary">Lô B3</h6>
                                                <span class="badge bg-danger">Cần bảo trì</span>
                                            </div>
                                            <div class="lot-info">
                                                <p class="mb-2"><i class="fas fa-map-marker-alt text-danger me-2"></i><strong>Vị trí:</strong> Cánh đồng phía Đông Bắc</p>
                                                <p class="mb-2"><i class="fas fa-expand-arrows-alt text-info me-2"></i><strong>Diện tích:</strong> 3.5 ha</p>
                                                <p class="mb-2"><i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> Chưa chọn</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> Chưa xác định</p>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <button class="btn btn-primary btn-sm w-100 btn-fill-lot" data-lot="6">
                                                <i class="fas fa-edit me-2"></i>Điền thông tin
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Thêm tài khoản -->
    <div class="modal fade" id="addEditModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Thêm tài khoản
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="action" value="add_user">
                        <div class="mb-3">
                            <label class="form-label">Tên đăng nhập</label>
                            <input type="text" class="form-control" name="username" placeholder="Nhập tên đăng nhập" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu</label>
                            <input type="password" class="form-control" name="password" placeholder="Nhập mật khẩu" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" name="name" placeholder="Nhập họ tên" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" name="phone" placeholder="VD: 0901234567">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Quyền truy cập</label>
                            <select class="form-control" name="role" required>
                                <option value="nông dân">Nông dân</option>
                                <option value="quản trị">Quản trị</option>
                                <option value="phân phối">Phân phối</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary-custom btn-custom">Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Điền thông tin lô canh tác -->
    <div class="modal fade" id="lotInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-seedling me-2"></i>Thông tin lô canh tác
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="lotInfoForm">
                        <input type="hidden" id="lotId" value="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mã lô</label>
                                <input type="text" class="form-control" id="lotCode" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Trạng thái</label>
                                <select class="form-control" id="lotStatus">
                                    <option value="Sẵn sàng">Sẵn sàng</option>
                                    <option value="Đang chuẩn bị">Đang chuẩn bị</option>
                                    <option value="Chưa bắt đầu">Chưa bắt đầu</option>
                                    <option value="Đang canh tác">Đang canh tác</option>
                                    <option value="Hoàn thành">Hoàn thành</option>
                                    <option value="Cần bảo trì">Cần bảo trì</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loại cây trồng</label>
                                <select class="form-control" id="cropType">
                                    <option value="">Chọn loại cây</option>
                                    <option value="Lúa">Lúa</option>
                                    <option value="Ngô">Ngô</option>
                                    <option value="Khoai tây">Khoai tây</option>
                                    <option value="Rau xanh">Rau xanh</option>
                                    <option value="Cà chua">Cà chua</option>
                                    <option value="Ớt">Ớt</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mùa vụ</label>
                                <select class="form-control" id="season">
                                    <option value="">Chọn mùa vụ</option>
                                    <option value="Vụ Xuân 2025">Vụ Xuân 2025</option>
                                    <option value="Vụ Hè Thu 2025">Vụ Hè Thu 2025</option>
                                    <option value="Vụ Đông Xuân 2025-2026">Vụ Đông Xuân 2025-2026</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngày bắt đầu</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ngày kết thúc dự kiến</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số lượng hạt giống (kg)</label>
                                <input type="number" class="form-control" id="seedAmount" min="0" step="0.1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số công nhân</label>
                                <input type="number" class="form-control" id="workers" min="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" id="notes" rows="3" placeholder="Ghi chú về lô canh tác..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-success" id="saveLotInfo">
                        <i class="fas fa-save me-2"></i>Lưu thông tin
                    </button>
                    <button type="button" class="btn btn-info" id="viewMap">
                        <i class="fas fa-map me-2"></i>Xem vị trí trên bản đồ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Google Maps -->
    <div class="modal fade" id="mapModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-map-marked-alt me-2"></i>Vị trí lô canh tác
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Sidebar navigation + section toggling
        const sections = {
            '#dashboard': ['#dashboard-block'],
            '#account': ['#account'],
            '#planning': ['#planning']
        };
        function showSectionByHash(hash) {
            const config = sections[hash];
            if (!config) return;
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            const activeLink = document.querySelector(`.sidebar .nav-link[href="${hash}"]`);
            if (activeLink) activeLink.classList.add('active');

            // Hide all top-level sections first
            ['#dashboard-block', '#account', '#planning'].forEach(sel => {
                const el = document.querySelector(sel);
                if (el) el.style.display = 'none';
            });
            // Also hide planning-only blocks by default
            document.querySelectorAll('.planning-only').forEach(el => el.style.display = 'none');

            // Show requested section
            config.forEach(sel => {
                const el = document.querySelector(sel);
                if (el) el.style.display = '';
            });

            // If planning is active, show planning-only blocks
            if (hash === '#planning') {
                document.querySelectorAll('.planning-only').forEach(el => el.style.display = '');
            }
            const first = document.querySelector(config[0]);
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('href');
                history.replaceState(null, '', target);
                showSectionByHash(target);
            });
        });

        // Open section from URL hash on load (e.g. #planning) or default based on URL
        if (location.hash && sections[location.hash]) {
            showSectionByHash(location.hash);
        } else {
            // If accessing quanli.php directly, show account section
            if (location.pathname.includes('quanli.php')) {
                showSectionByHash('#account');
            } else {
                showSectionByHash('#dashboard');
            }
        }

        // Search functionality
        document.querySelector('.search-box input').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.table-custom tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Add new button
        document.querySelector('.btn-primary-custom').addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('addEditModal'));
            modal.show();
        });

        // Edit buttons
        document.querySelectorAll('.btn-outline-primary').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('addEditModal'));
                modal.show();
            });
        });

        // Confirm delete forms
        document.querySelectorAll('form.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa tài khoản này?')) {
                    e.preventDefault();
                }
            });
        });

        // Auto refresh stats every 30 seconds
        setInterval(function() {
            // Simulate data update
            const statsNumbers = document.querySelectorAll('.stats-number');
            statsNumbers.forEach(stat => {
                const currentValue = parseInt(stat.textContent.replace(/[^\d]/g, ''));
                const newValue = currentValue + Math.floor(Math.random() * 10);
                stat.textContent = stat.textContent.replace(/\d+/, newValue);
            });
        }, 30000);

        // Plans: view & edit
        const planForm = document.querySelector('#planning form');
        const actionInput = planForm?.querySelector('input[name="action"]');
        const idInput = document.getElementById('plan-id');
        const fillPlanForm = (p) => {
            if (!planForm) return;
            planForm.querySelector('#plan-season').value = p.season || '';
            planForm.querySelector('#plan-time-range').value = p.time_range || '';
            planForm.querySelector('#plan-lot-code').value = p.lot_code || '';
            planForm.querySelector('#plan-workers').value = p.workers || '';
            planForm.querySelector('#plan-seed').value = p.seed || '';
            planForm.querySelector('#plan-seed-qty').value = p.seed_qty || '';
            planForm.querySelector('#plan-area').value = p.area || '';
            planForm.querySelector('#plan-expected-yield').value = p.expected_yield || '';
            planForm.querySelector('#plan-quality').value = p.quality || 'VietGAP';
            planForm.querySelector('#plan-supplies').value = p.supplies || '';
        };

        document.querySelectorAll('.btn-plan-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const data = btn.getAttribute('data-plan');
                const p = JSON.parse(data);
                fillPlanForm(p);
                if (idInput) idInput.value = p.id;
                if (actionInput) actionInput.value = 'update_plan';
                // Focus section
                const planning = document.getElementById('planning');
                if (planning) planning.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Click tile to view details
        document.querySelectorAll('.lot-tile').forEach(tile => {
            tile.addEventListener('click', (e) => {
                // avoid triggering when clicking action buttons inside footer
                if (e.target.closest('button, form')) return;
                const data = tile.getAttribute('data-plan');
                const p = JSON.parse(data);
                alert(`Lô ${p.lot_code}\nMùa vụ: ${p.season}\nThời gian: ${p.time_range}\nCông nhân: ${p.workers}\nGiống: ${p.seed} (${p.seed_qty})\nDiện tích: ${p.area} ha\nSản lượng: ${p.expected_yield} tấn\nChất lượng: ${p.quality}\nVật tư: ${p.supplies || ''}`);
            });
        });

        // Confirm delete plan
        document.querySelectorAll('form.plan-delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('Bạn có chắc chắn muốn xóa kế hoạch này?')) {
                    e.preventDefault();
                }
            });
        });

        // Lot management functionality
        let currentLot = null;
        let map = null;
        let markers = [];

        // Handle lot card clicks
        document.querySelectorAll('.btn-fill-lot').forEach(btn => {
            btn.addEventListener('click', function() {
                const lotCard = this.closest('.lot-card');
                const lotId = lotCard.getAttribute('data-lot');
                const lotCode = lotCard.querySelector('.card-title').textContent;
                const lat = lotCard.getAttribute('data-lat');
                const lng = lotCard.getAttribute('data-lng');
                
                currentLot = {
                    id: lotId,
                    code: lotCode,
                    lat: parseFloat(lat),
                    lng: parseFloat(lng)
                };
                
                // Fill modal with lot info
                document.getElementById('lotId').value = lotId;
                document.getElementById('lotCode').value = lotCode;
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('lotInfoModal'));
                modal.show();
            });
        });

        // Save lot info
        document.getElementById('saveLotInfo').addEventListener('click', function() {
            const lotData = {
                id: document.getElementById('lotId').value,
                code: document.getElementById('lotCode').value,
                status: document.getElementById('lotStatus').value,
                cropType: document.getElementById('cropType').value,
                season: document.getElementById('season').value,
                startDate: document.getElementById('startDate').value,
                endDate: document.getElementById('endDate').value,
                seedAmount: document.getElementById('seedAmount').value,
                workers: document.getElementById('workers').value,
                notes: document.getElementById('notes').value
            };
            
            // Update the lot card with new info
            updateLotCard(lotData);
            
            // Show success message
            alert('Thông tin lô canh tác đã được lưu thành công!');
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('lotInfoModal'));
            modal.hide();
        });

        // View map
        document.getElementById('viewMap').addEventListener('click', function() {
            if (currentLot) {
                const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
                mapModal.show();
                
                // Initialize map after modal is shown
                setTimeout(() => {
                    initMap();
                }, 500);
            }
        });

        // Update lot card display
        function updateLotCard(data) {
            const lotCard = document.querySelector(`[data-lot="${data.id}"]`);
            if (lotCard) {
                // Update status badge
                const statusBadge = lotCard.querySelector('.badge');
                statusBadge.textContent = data.status;
                statusBadge.className = 'badge ' + getStatusClass(data.status);
                
                // Update crop type
                const cropInfo = lotCard.querySelector('.lot-info p:nth-child(3)');
                if (cropInfo) {
                    cropInfo.innerHTML = `<i class="fas fa-seedling text-success me-2"></i><strong>Loại cây:</strong> ${data.cropType || 'Chưa chọn'}`;
                }
                
                // Update season
                const seasonInfo = lotCard.querySelector('.lot-info p:last-child');
                if (seasonInfo) {
                    seasonInfo.innerHTML = `<i class="fas fa-calendar text-warning me-2"></i><strong>Mùa vụ:</strong> ${data.season || 'Chưa xác định'}`;
                }
            }
        }

        // Get status badge class
        function getStatusClass(status) {
            switch(status) {
                case 'Sẵn sàng': return 'bg-success';
                case 'Đang chuẩn bị': return 'bg-warning';
                case 'Chưa bắt đầu': return 'bg-secondary';
                case 'Đang canh tác': return 'bg-info';
                case 'Hoàn thành': return 'bg-success';
                case 'Cần bảo trì': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }

        // Initialize Leaflet Map (OpenStreetMap)
        function initMap() {
            if (!currentLot) return;
            
            const mapElement = document.getElementById('map');
            if (!mapElement) return;
            
            // Clear existing map
            if (map) {
                map.remove();
            }
            
            const lotPosition = [currentLot.lat, currentLot.lng];
            
            // Initialize map
            map = L.map('map').setView(lotPosition, 18);
            
            // Add OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            // Add satellite layer option
            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '© Esri'
            });
            
            // Add layer control
            const baseMaps = {
                "Bản đồ": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }),
                "Vệ tinh": satelliteLayer
            };
            
            L.control.layers(baseMaps).addTo(map);
            
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
            
            // Add marker for the lot
            const marker = L.marker(lotPosition, {
                title: currentLot.code
            }).addTo(map);
            
            markers.push(marker);
            
            // Add info popup
            const popupContent = `
                <div style="padding: 10px; min-width: 200px;">
                    <h6 style="margin: 0 0 10px 0; color: #2c5aa0;">${currentLot.code}</h6>
                    <p style="margin: 0; font-size: 14px;">
                        <strong>Vị trí:</strong> ${currentLot.lat.toFixed(6)}, ${currentLot.lng.toFixed(6)}<br>
                        <strong>Trạng thái:</strong> ${document.getElementById('lotStatus').value}<br>
                        <strong>Loại cây:</strong> ${document.getElementById('cropType').value || 'Chưa chọn'}
                    </p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            
            // Add farm boundary (simulated)
            const farmBoundary = L.rectangle([
                [currentLot.lat + 0.001, currentLot.lng - 0.001],
                [currentLot.lat - 0.001, currentLot.lng + 0.001]
            ], {
                color: '#4CAF50',
                fillColor: '#4CAF50',
                fillOpacity: 0.2,
                weight: 2
            }).addTo(map);
            
            // Fit map to show the boundary
            map.fitBounds(farmBoundary.getBounds());
        }

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert && alert.parentNode) {
                        alert.style.transition = 'opacity 0.5s ease-out';
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 500);
                    }
                }, 5000);
            });
        });

        // Clear form when modal is closed
        document.addEventListener('DOMContentLoaded', function() {
            const lotInfoModal = document.getElementById('lotInfoModal');
            if (lotInfoModal) {
                lotInfoModal.addEventListener('hidden.bs.modal', function() {
                    // Clear form
                    document.getElementById('lotInfoForm').reset();
                    currentLot = null;
                });
            }
        });

        // Add some CSS for lot cards and map
        const style = document.createElement('style');
        style.textContent = `
            .lot-card {
                transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
                cursor: pointer;
            }
            .lot-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }
            .lot-info p {
                font-size: 14px;
                margin-bottom: 8px;
            }
            .lot-info i {
                width: 16px;
                text-align: center;
            }
            #map {
                height: 500px !important;
                width: 100% !important;
                border-radius: 8px;
            }
            .leaflet-container {
                height: 500px !important;
                width: 100% !important;
            }
            .alert-custom {
                margin-bottom: 20px;
                border-radius: 8px;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
