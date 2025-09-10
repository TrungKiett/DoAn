<?php

declare(strict_types=1);

require_once __DIR__ . '/../model/model.php';

$alert = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_user') {
        $result = addUser([
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'] ?? '',
        ]);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Thêm tài khoản thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => 'Vui lòng kiểm tra lại thông tin', 'errors' => $result['errors']];
        }
    } elseif ($action === 'update_role') {
        $id = (int)($_POST['id'] ?? 0);
        $role = $_POST['role'] ?? '';
        $result = updateUserRole($id, $role);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Cập nhật quyền thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => $result['error'] ?? 'Cập nhật quyền thất bại', 'errors' => []];
        }
    } elseif ($action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        $result = deleteUser($id);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Xóa tài khoản thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => $result['error'] ?? 'Xóa tài khoản thất bại', 'errors' => []];
        }
    } elseif ($action === 'add_plan') {
        $result = addPlan([
            'season' => trim($_POST['season'] ?? ''),
            'time_range' => trim($_POST['time_range'] ?? ''),
            'lot_code' => trim($_POST['lot_code'] ?? ''),
            'workers' => (int)($_POST['workers'] ?? 0),
            'seed' => trim($_POST['seed'] ?? ''),
            'seed_qty' => (int)($_POST['seed_qty'] ?? 0),
            'area' => (float)($_POST['area'] ?? 0),
            'expected_yield' => (int)($_POST['expected_yield'] ?? 0),
            'supplies' => trim($_POST['supplies'] ?? ''),
            'quality' => trim($_POST['quality'] ?? ''),
        ]);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Lập kế hoạch sản xuất thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => 'Vui lòng kiểm tra lại thông tin kế hoạch', 'errors' => $result['errors'] ?? []];
        }
    } elseif ($action === 'update_plan') {
        $id = (int)($_POST['id'] ?? 0);
        $result = updatePlan($id, [
            'season' => trim($_POST['season'] ?? ''),
            'time_range' => trim($_POST['time_range'] ?? ''),
            'lot_code' => trim($_POST['lot_code'] ?? ''),
            'workers' => (int)($_POST['workers'] ?? 0),
            'seed' => trim($_POST['seed'] ?? ''),
            'seed_qty' => (int)($_POST['seed_qty'] ?? 0),
            'area' => (float)($_POST['area'] ?? 0),
            'expected_yield' => (int)($_POST['expected_yield'] ?? 0),
            'supplies' => trim($_POST['supplies'] ?? ''),
            'quality' => trim($_POST['quality'] ?? ''),
        ]);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Cập nhật kế hoạch sản xuất thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => $result['error'] ?? 'Vui lòng kiểm tra lại thông tin kế hoạch', 'errors' => $result['errors'] ?? []];
        }
    } elseif ($action === 'delete_plan') {
        $id = (int)($_POST['id'] ?? 0);
        $result = deletePlan($id);
        if ($result['ok']) {
            $alert = ['type' => 'success', 'message' => 'Xóa kế hoạch thành công', 'errors' => []];
        } else {
            $alert = ['type' => 'danger', 'message' => $result['error'] ?? 'Xóa kế hoạch thất bại', 'errors' => []];
        }
    }
}

$stats = getDashboardStats();
$users = getUsers();
$plans = getPlans();

// Make helper available in view
$statusToBadge = function (string $status): string { return mapStatusToBadgeClass($status); };

// Chỉ include view khi không được gọi từ quanli.php
if (!defined('QUANLI_VIEW_LOADED')) {
    define('QUANLI_VIEW_LOADED', true);
    require __DIR__ . '/../view/quanli.php';
}


