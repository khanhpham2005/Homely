<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars($value === null ? '' : (string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . ($path === '' ? '/' : '/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Phiên biểu mẫu đã hết hạn. Vui lòng thử lại.');
    }
}

function flash(string $key, $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        flash('error', 'Vui lòng đăng nhập để tiếp tục.');
        redirect('dang-nhap');
    }
}

function require_admin(): void
{
    require_login();
    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Bạn không có quyền truy cập.');
    }
}

function placeholder(string $label = 'Homely'): string
{
    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="600"><rect width="100%" height="100%" fill="#eee9e1"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#8d7b68" font-size="34" font-family="Arial">' . $label . '</text></svg>'
    );
}

function local_image_url($filename)
{
    if ($filename === null || $filename === '') {
        return null;
    }

    $safeName = basename((string) $filename);
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed, true)) {
        return null;
    }

    $path = __DIR__ . '/../assets/images/' . $safeName;
    return is_file($path) ? url('assets/images/' . rawurlencode($safeName)) : null;
}

function image_html($filename, $alt, $class = 'product-image'): string
{
    $imageUrl = local_image_url($filename);
    if (!$imageUrl) {
        return '<span class="image-placeholder">' . e($alt) . '</span>';
    }

    return '<img class="' . e($class) . '" src="' . e($imageUrl) . '" alt="' . e($alt) . '" loading="lazy">';
}

function slugify($value): string
{
    $value = trim((string) $value);
    $value = strtr($value, [
        'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'đ' => 'd', 'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        'Á' => 'a', 'À' => 'a', 'Ả' => 'a', 'Ã' => 'a', 'Ạ' => 'a',
        'Ă' => 'a', 'Ắ' => 'a', 'Ằ' => 'a', 'Ẳ' => 'a', 'Ẵ' => 'a', 'Ặ' => 'a',
        'Â' => 'a', 'Ấ' => 'a', 'Ầ' => 'a', 'Ẩ' => 'a', 'Ẫ' => 'a', 'Ậ' => 'a',
        'Đ' => 'd', 'É' => 'e', 'È' => 'e', 'Ẻ' => 'e', 'Ẽ' => 'e', 'Ẹ' => 'e',
        'Ê' => 'e', 'Ế' => 'e', 'Ề' => 'e', 'Ể' => 'e', 'Ễ' => 'e', 'Ệ' => 'e',
        'Í' => 'i', 'Ì' => 'i', 'Ỉ' => 'i', 'Ĩ' => 'i', 'Ị' => 'i',
        'Ó' => 'o', 'Ò' => 'o', 'Ỏ' => 'o', 'Õ' => 'o', 'Ọ' => 'o',
        'Ô' => 'o', 'Ố' => 'o', 'Ồ' => 'o', 'Ổ' => 'o', 'Ỗ' => 'o', 'Ộ' => 'o',
        'Ơ' => 'o', 'Ớ' => 'o', 'Ờ' => 'o', 'Ở' => 'o', 'Ỡ' => 'o', 'Ợ' => 'o',
        'Ú' => 'u', 'Ù' => 'u', 'Ủ' => 'u', 'Ũ' => 'u', 'Ụ' => 'u',
        'Ư' => 'u', 'Ứ' => 'u', 'Ừ' => 'u', 'Ử' => 'u', 'Ữ' => 'u', 'Ự' => 'u',
        'Ý' => 'y', 'Ỳ' => 'y', 'Ỷ' => 'y', 'Ỹ' => 'y', 'Ỵ' => 'y'
    ]);
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
}

function format_price($value): string
{
    if ($value === null || $value === '' || !is_numeric($value)) {
        $value = 0.0;
    }

    return number_format((float) $value, 0, ',', '.') . ' đ';
}
