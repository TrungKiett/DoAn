<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'farm');

// Database connection function
function getDatabaseConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
        }
    }
    
    return $pdo;
}

// Test database connection
function testDatabaseConnection(): array {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("SELECT 1");
        return ['ok' => true, 'message' => 'Kết nối database thành công'];
    } catch (Exception $e) {
        return ['ok' => false, 'message' => $e->getMessage()];
    }
}

function getDashboardStats(): array {
    try {
        $pdo = getDatabaseConnection();
        
        // Đếm số lượng người dùng
        $stmt = $pdo->query("SELECT COUNT(*) FROM nguoi_dung");
        $users = $stmt->fetchColumn();
        
        return [
            'users' => (int)$users,
            'products' => 0, // Chưa có bảng sản phẩm
            'orders' => 0,   // Chưa có bảng đơn hàng
            'revenue' => 0,  // Chưa có bảng doanh thu
        ];
    } catch (Exception $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
        return [
            'users' => 0,
            'products' => 0,
            'orders' => 0,
            'revenue' => 0,
        ];
    }
}

function getUsers(): array {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("SELECT * FROM nguoi_dung ORDER BY ma_nguoi_dung DESC");
        $users = $stmt->fetchAll();
        
        // Chuyển đổi cấu trúc dữ liệu để phù hợp với view
        $result = [];
        foreach ($users as $user) {
            $result[] = [
                'id' => $user['ma_nguoi_dung'],
                'username' => $user['ten_dang_nhap'],
                'name' => $user['ho_ten'],
                'email' => '', // Không có cột email trong database
                'phone' => $user['so_dien_thoai'],
                'role' => mapDatabaseRoleToView($user['vai_tro']),
                'status' => 'Hoạt động', // Mặc định vì database không có trường status
                'created_at' => date('Y-m-d', strtotime($user['ngay_tao'])),
            ];
        }
        
        return $result;
    } catch (Exception $e) {
        error_log("Error getting users: " . $e->getMessage());
        return [];
    }
}

function mapDatabaseRoleToView(string $dbRole): string {
    switch ($dbRole) {
        case 'quan_ly':
            return 'quản trị';
        case 'nong_dan':
            return 'nông dân';
        case 'phan_phoi':
            return 'phân phối';
        default:
            return $dbRole;
    }
}

function mapViewRoleToDatabase(string $viewRole): string {
    switch ($viewRole) {
        case 'quản trị':
            return 'quan_ly';
        case 'nông dân':
            return 'nong_dan';
        case 'phân phối':
            return 'phan_phoi';
        default:
            return $viewRole;
    }
}

function mapStatusToBadgeClass(string $status): string {
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
}

function usernameExists(string $username, ?int $excludeId = null): bool {
    try {
        $pdo = getDatabaseConnection();
        $sql = "SELECT COUNT(*) FROM nguoi_dung WHERE LOWER(ten_dang_nhap) = LOWER(?)";
        $params = [$username];
        
        if ($excludeId !== null) {
            $sql .= " AND ma_nguoi_dung != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        error_log("Error checking username exists: " . $e->getMessage());
        return false;
    }
}

function validateUserInput(array $data, bool $isUpdate = false, ?int $userId = null): array {
    $errors = [];
    if (empty($data['username'])) {
        $errors[] = 'Tên đăng nhập là bắt buộc.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,}$/', $data['username'])) {
        $errors[] = 'Tên đăng nhập tối thiểu 3 ký tự, chỉ chữ/số/._-';
    } elseif (usernameExists($data['username'], $isUpdate ? $userId : null)) {
        $errors[] = 'Tên đăng nhập đã tồn tại.';
    }

    if (!$isUpdate && (empty($data['password']) || strlen($data['password']) < 6)) {
        $errors[] = 'Mật khẩu tối thiểu 6 ký tự.';
    }

    if (empty($data['name'])) {
        $errors[] = 'Họ tên là bắt buộc.';
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }

    if (!empty($data['phone']) && !preg_match('/^0[0-9]{9}$/', $data['phone'])) {
        $errors[] = 'Số điện thoại không hợp lệ (10 số, bắt đầu bằng 0).';
    }

    if (empty($data['role']) || !in_array($data['role'], ['nông dân', 'quản trị', 'phân phối'], true)) {
        $errors[] = 'Quyền truy cập không hợp lệ.';
    }

    return $errors;
}

function addUser(array $data): array {
    $errors = validateUserInput($data);
    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }

    try {
        $pdo = getDatabaseConnection();
        $sql = "INSERT INTO nguoi_dung (ten_dang_nhap, mat_khau, ho_ten, vai_tro, so_dien_thoai, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['username'],
            $data['password'], // Lưu mật khẩu dưới dạng plain text
            $data['name'],
            mapViewRoleToDatabase($data['role']),
            $data['phone'] ?? null
        ]);
        
        $userId = $pdo->lastInsertId();
        return ['ok' => true, 'id' => $userId];
    } catch (Exception $e) {
        error_log("Error adding user: " . $e->getMessage());
        return ['ok' => false, 'errors' => ['Lỗi hệ thống: ' . $e->getMessage()]];
    }
}

function updateUserRole(int $id, string $role): array {
    if (!in_array($role, ['nông dân', 'quản trị', 'phân phối'], true)) {
        return ['ok' => false, 'error' => 'Quyền không hợp lệ.'];
    }
    
    try {
        $pdo = getDatabaseConnection();
        $sql = "UPDATE nguoi_dung SET vai_tro = ? WHERE ma_nguoi_dung = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([mapViewRoleToDatabase($role), $id]);
        
        if ($stmt->rowCount() > 0) {
            return ['ok' => true];
        } else {
            return ['ok' => false, 'error' => 'Không tìm thấy người dùng.'];
        }
    } catch (Exception $e) {
        error_log("Error updating user role: " . $e->getMessage());
        return ['ok' => false, 'error' => 'Lỗi hệ thống: Không thể cập nhật quyền người dùng'];
    }
}

function deleteUser(int $id): array {
    try {
        $pdo = getDatabaseConnection();
        $sql = "DELETE FROM nguoi_dung WHERE ma_nguoi_dung = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            return ['ok' => true];
        } else {
            return ['ok' => false, 'error' => 'Không tìm thấy người dùng.'];
        }
    } catch (Exception $e) {
        error_log("Error deleting user: " . $e->getMessage());
        return ['ok' => false, 'error' => 'Lỗi hệ thống: Không thể xóa người dùng'];
    }
}

// ---- Production Planning ----
function getPlans(): array {
    if (!isset($_SESSION['plans'])) {
        $_SESSION['plans'] = [];
    }
    return $_SESSION['plans'];
}

function planCodeExists(string $lotCode): bool {
    foreach (getPlans() as $p) {
        if (mb_strtolower($p['lot_code']) === mb_strtolower($lotCode)) {
            return true;
        }
    }
    return false;
}

function validatePlanInput(array $data, bool $isUpdate = false, ?string $originalLotCode = null): array {
    $errors = [];
    if (empty($data['season'])) {
        $errors[] = 'Mùa vụ là bắt buộc.';
    }
    if (empty($data['time_range'])) {
        $errors[] = 'Thời gian canh tác là bắt buộc.';
    }
    if (empty($data['lot_code'])) {
        $errors[] = 'Mã lô là bắt buộc.';
    } else {
        $exists = planCodeExists($data['lot_code']);
        if ($exists && (!$isUpdate || mb_strtolower($data['lot_code']) !== mb_strtolower((string)$originalLotCode))) {
            $errors[] = 'Mã lô đã tồn tại.';
        }
    }
    if (!isset($data['workers']) || (int)$data['workers'] <= 0) {
        $errors[] = 'Số lượng công nhân phải > 0.';
    }
    if (empty($data['seed']) || (int)$data['seed_qty'] <= 0) {
        $errors[] = 'Giống và số lượng phải hợp lệ.';
    }
    if ((float)$data['area'] <= 0) {
        $errors[] = 'Diện tích phải > 0.';
    }
    if ((int)$data['expected_yield'] <= 0) {
        $errors[] = 'Sản lượng dự kiến phải > 0.';
    }
    if (empty($data['quality'])) {
        $errors[] = 'Tiêu chuẩn chất lượng là bắt buộc.';
    }
    return $errors;
}

function addPlan(array $data): array {
    $errors = validatePlanInput($data, false, null);
    if (!empty($errors)) {
        return ['ok' => false, 'errors' => $errors];
    }
    $plans = getPlans();
    $nextId = empty($plans) ? 1 : (max(array_column($plans, 'id')) + 1);
    $plans[] = [
        'id' => $nextId,
        'season' => $data['season'],
        'time_range' => $data['time_range'],
        'lot_code' => $data['lot_code'],
        'workers' => (int)$data['workers'],
        'seed' => $data['seed'],
        'seed_qty' => (int)$data['seed_qty'],
        'area' => (float)$data['area'],
        'expected_yield' => (int)$data['expected_yield'],
        'supplies' => $data['supplies'] ?? '',
        'quality' => $data['quality'],
        'created_at' => date('Y-m-d'),
    ];
    $_SESSION['plans'] = $plans;
    return ['ok' => true, 'id' => $nextId];
}

function updatePlan(int $id, array $data): array {
    $plans = getPlans();
    $original = null;
    foreach ($plans as $p) {
        if ($p['id'] === $id) { $original = $p; break; }
    }
    if (!$original) {
        return ['ok' => false, 'error' => 'Không tìm thấy kế hoạch.'];
    }
    $errors = validatePlanInput($data, true, $original['lot_code']);
    if (!empty($errors)) { return ['ok' => false, 'errors' => $errors]; }
    foreach ($plans as &$p) {
        if ($p['id'] === $id) {
            $p['season'] = $data['season'];
            $p['time_range'] = $data['time_range'];
            $p['lot_code'] = $data['lot_code'];
            $p['workers'] = (int)$data['workers'];
            $p['seed'] = $data['seed'];
            $p['seed_qty'] = (int)$data['seed_qty'];
            $p['area'] = (float)$data['area'];
            $p['expected_yield'] = (int)$data['expected_yield'];
            $p['supplies'] = $data['supplies'] ?? '';
            $p['quality'] = $data['quality'];
        }
    }
    $_SESSION['plans'] = $plans;
    return ['ok' => true];
}

function deletePlan(int $id): array {
    $plans = getPlans();
    $found = false;
    $plans = array_values(array_filter($plans, function ($p) use ($id, &$found) {
        if ($p['id'] === $id) { $found = true; return false; }
        return true;
    }));
    if (!$found) {
        return ['ok' => false, 'error' => 'Không tìm thấy kế hoạch.'];
    }
    $_SESSION['plans'] = $plans;
    return ['ok' => true];
}


