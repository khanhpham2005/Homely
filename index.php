<?php
declare(strict_types=1);
require __DIR__ . '/config/config.php';
require __DIR__ . '/app/db.php';
require __DIR__ . '/app/helpers.php';

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$path = preg_replace('#^ltw/?#', '', $path);
$path = trim($path, '/');
$segments = $path === '' ? [] : explode('/', $path);
$page = $segments[0] ?? 'home';

function render(string $title, string $content, string $description = ''): void
{
    $user = current_user();
    $company = ['phone' => '090 123 4567', 'email' => 'hello@homely.local', 'address' => 'TP Hồ Chí Minh'];
    try {
        foreach (db()->query('SELECT setting_key,setting_value FROM company_settings')->fetchAll() as $setting) {
            $company[$setting['setting_key']] = $setting['setting_value'];
        }
    } catch (PDOException $ignored) {
    }
    $success = flash('success');
    $error = flash('error');
    $meta = e($description ?: 'Homely - Đồ gia dụng tiện ích, bền đẹp cho mọi gia đình.');
    echo '<!doctype html><html lang="vi"><head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>' . e($title) . ' | Homely</title><meta name="description" content="' . $meta . '"><link rel="stylesheet" href="' . url('assets/css/style.css?v=2026102621') . '"></head><body>';
    $cartCount = 0;
    if ($user) {
        $cartStmt = db()->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?');
        $cartStmt->execute([$user['id']]);
        $cartCount = (int) $cartStmt->fetchColumn();
    }
    echo '<div class="topbar"><div class="container">Đồ gia dụng chính hãng · Giao hàng toàn quốc · Hotline: ' . e($company['phone']) . '</div></div><nav><div class="container nav-inner"><a class="brand" href="' . url() . '">Home<span>ly</span></a><button class="menu-toggle" aria-label="Mở menu">☰</button><div class="nav-links"><a href="' . url() . '">Trang chủ</a><a href="' . url('gioi-thieu') . '">Về Homely</a><a href="' . url('san-pham') . '">Đồ gia dụng</a><a href="' . url('hoi-dap') . '">Hỏi đáp</a><a href="' . url('lien-he') . '">Liên hệ</a><a href="' . url('gio-hang') . '">Giỏ hàng (' . $cartCount . ')</a>';
    if ($user) {
        $avatar = !empty($user['avatar']) ? image_html($user['avatar'], $user['name'], 'menu-avatar') : '';
        echo '<a class="user-menu-link" href="' . url($user['role'] === 'admin' ? 'admin' : 'tai-khoan') . '">' . $avatar . '<span>' . e($user['name']) . '</span></a>' . ($user['role'] === 'member' ? '<a href="' . url('wishlist') . '">Yêu thích</a>' : '') . '<a href="' . url('dang-xuat') . '">Thoát</a>';
    } else {
        echo '<a href="' . url('dang-nhap') . '">Đăng nhập</a>';
    }
    echo '</div></div></nav>';
    echo '<main>';
    if ($success) echo '<div class="container"><div class="alert success">' . e($success) . '</div></div>';
    if ($error) echo '<div class="container"><div class="alert">' . e($error) . '</div></div>';
    echo $content . '</main><footer class="footer"><div class="container footer-grid"><div><div class="brand">Home<span>ly</span></div><p class="muted">Đồ gia dụng hữu ích, giúp việc nhà nhẹ nhàng và cuộc sống tiện nghi hơn.</p></div><div><strong>Khám phá</strong><p><a href="' . url('gioi-thieu') . '">Về Homely</a><br><a href="' . url('san-pham') . '">Đồ gia dụng</a><br><a href="' . url('hoi-dap') . '">Hỏi đáp</a></p></div><div><strong>Liên hệ</strong><p class="muted">' . e($company['phone']) . '<br>' . e($company['email']) . '<br>' . e($company['address']) . '</p></div></div><div class="container copyright">© ' . date('Y') . ' Homely. All rights reserved.</div></footer><script src="' . url('assets/js/app.js?v=2026102718') . '"></script></body></html>';
}

function page_head(string $title, string $intro): string
{
    return '<div class="page-head"><div class="container"><div class="eyebrow">Homely home essentials</div><h1>' . e($title) . '</h1><p class="muted">' . e($intro) . '</p></div></div>';
}

function products_html(array $products): string
{
    $html = '<div class="grid">';
    foreach ($products as $product) {
        $html .= '<article class="card"><div class="card-img">' . image_html($product['image'] ?? null, $product['name']) . '</div><div class="card-body"><span class="tag">Homely home essentials</span><h3>' . e($product['name']) . '</h3><p class="muted">' . e($product['description']) . '</p><div class="price">' . format_price($product['price'] ?? null) . '</div><p><a class="btn" href="' . url('san-pham/' . e($product['slug'])) . '">Xem chi tiết</a></p></div></article>';
    }
    return $html . '</div>';
}

try {
    try { db()->exec('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL'); } catch (PDOException $ignored) {}
    try { db()->exec('ALTER TABLE contacts ADD COLUMN user_id INT UNSIGNED NULL'); } catch (PDOException $ignored) {}
    db()->exec("CREATE TABLE IF NOT EXISTS wishlist (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    db()->exec("CREATE TABLE IF NOT EXISTS cart_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, quantity TINYINT UNSIGNED NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($page === 'dang-xuat') {
        unset($_SESSION['user'], $_SESSION['cart']); flash('success', 'Bạn đã đăng xuất.'); redirect('');
    }
    if ($page === 'tai-khoan' || $page === 'wishlist' || $page === 'hoi-dap') {
        $schema = db();
        try { $schema->exec('ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL'); } catch (PDOException $ignored) {}
        try { $schema->exec('ALTER TABLE contacts ADD COLUMN user_id INT UNSIGNED NULL'); } catch (PDOException $ignored) {}
        $schema->exec("CREATE TABLE IF NOT EXISTS wishlist (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, product_id INT UNSIGNED NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY user_product (user_id, product_id)) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    if ($page === 'dang-ky' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) throw new RuntimeException('Thông tin đăng ký chưa hợp lệ.');
        $stmt = db()->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Đăng ký thành công, bạn có thể đăng nhập.'); redirect('dang-nhap');
    }
    if ($page === 'dang-nhap' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1'); $stmt->execute([trim($_POST['email'] ?? '')]); $user = $stmt->fetch();
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) throw new RuntimeException('Email hoặc mật khẩu không đúng.');
        if ((int) $user['status'] !== 1) throw new RuntimeException('Tài khoản hiện không khả dụng');
        unset($user['password'], $_SESSION['cart']); $_SESSION['user'] = $user; redirect($user['role'] === 'admin' ? 'admin' : '');
    }
    if ($page === 'lien-he' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $name = trim($_POST['name'] ?? ''); $email = trim($_POST['email'] ?? ''); $message = trim($_POST['message'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') throw new RuntimeException('Vui lòng điền đầy đủ thông tin liên hệ.');
        db()->prepare('INSERT INTO contacts (user_id,name,email,message) VALUES (?,?,?,?)')->execute([current_user() ? current_user()['id'] : null, $name, $email, $message]);
        flash('success', 'Cảm ơn bạn. Homely sẽ phản hồi sớm nhất.'); redirect('lien-he');
    }
    if ($page === 'them-gio' && isset($segments[1])) {
        require_login();
        $productId = (int) $segments[1];
        $productCheck = db()->prepare('SELECT id FROM products WHERE id=? AND status=1 LIMIT 1');
        $productCheck->execute([$productId]);
        if (!$productCheck->fetch()) throw new RuntimeException('Sản phẩm không tồn tại hoặc đã ngừng bán.');
        db()->prepare('INSERT INTO cart_items (user_id,product_id,quantity) VALUES (?,?,1) ON DUPLICATE KEY UPDATE quantity=LEAST(99, quantity+1)')->execute([current_user()['id'], $productId]);
        flash('success', 'Đã thêm sản phẩm vào giỏ hàng.'); redirect('gio-hang');
    }
    if ($page === 'them-yeu-thich' && isset($segments[1])) {
        require_login(); db()->prepare('INSERT IGNORE INTO wishlist (user_id,product_id) VALUES (?,?)')->execute([current_user()['id'], (int) $segments[1]]); flash('success', 'Đã thêm sản phẩm vào yêu thích.'); redirect('wishlist');
    }
    if ($page === 'xoa-yeu-thich' && isset($segments[1])) {
        require_login(); db()->prepare('DELETE FROM wishlist WHERE user_id=? AND product_id=?')->execute([current_user()['id'], (int) $segments[1]]); flash('success', 'Đã xóa sản phẩm khỏi yêu thích.'); redirect('wishlist');
    }
    if ($page === 'huy-don' && isset($segments[1]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_login(); verify_csrf(); $stmt = db()->prepare("UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'"); $stmt->execute([(int) $segments[1], current_user()['id']]); flash('success', $stmt->rowCount() ? 'Đã hủy đơn hàng.' : 'Đơn hàng không thể hủy ở trạng thái hiện tại.'); redirect('tai-khoan');
    }
    if ($page === 'gio-hang' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_login(); verify_csrf();
        $update = db()->prepare('UPDATE cart_items SET quantity=? WHERE user_id=? AND product_id=?');
        $remove = db()->prepare('DELETE FROM cart_items WHERE user_id=? AND product_id=?');
        foreach (($_POST['qty'] ?? []) as $id => $qty) {
            $productId = (int) $id; $quantity = (int) $qty;
            if ($quantity <= 0) $remove->execute([current_user()['id'], $productId]);
            else $update->execute([min(99, $quantity), current_user()['id'], $productId]);
        }
        flash('success', 'Đã cập nhật giỏ hàng.'); redirect('gio-hang');
    }
    if ($page === 'binh-luan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        require_login(); verify_csrf(); $productId=(int)($_POST['product_id']??0); $content=trim($_POST['content']??''); $rating=(int)($_POST['rating']??0); $check=db()->prepare('SELECT id FROM products WHERE id=? AND status=1'); $check->execute([$productId]); if(!$check->fetch()||$content===''||$rating<1||$rating>5) throw new RuntimeException('Đánh giá chưa hợp lệ.'); db()->prepare('INSERT INTO comments (user_id,product_id,name,content,rating,status) VALUES (?,?,?,?,?,0)')->execute([current_user()['id'],$productId,current_user()['name'],$content,$rating]); flash('success','Đã gửi đánh giá, chờ admin duyệt.'); redirect('san-pham/'.e($_POST['slug']??'')); 
    }

    if ($page === 'home') {
        $products = db()->query('SELECT * FROM products WHERE status=1 ORDER BY id DESC LIMIT 3')->fetchAll();
        $content = '<section class="hero"><div class="container hero-grid"><div><div class="eyebrow">Useful · Simple · Homely</div><h1>Việc nhà nhẹ hơn, cuộc sống vui hơn.</h1><p>Homely sản xuất và tuyển chọn đồ gia dụng thiết thực, an toàn và bền đẹp để mỗi căn bếp, phòng tắm và góc sống trở nên tiện nghi hơn.</p><p><a class="btn" href="' . url('san-pham') . '">Mua sắm đồ gia dụng</a> <a class="btn btn-light" href="' . url('gioi-thieu') . '">Tìm hiểu Homely</a></p></div><div class="hero-art">' . image_html('home.jpg', 'Đồ gia dụng Homely', 'home-image') . '</div></div></section><section class="section"><div class="container"><div class="section-head"><h2>Sản phẩm nổi bật</h2><a href="' . url('san-pham') . '">Xem tất cả →</a></div>' . products_html($products) . '</div></section><section class="section features"><div class="container grid"><div class="feature"><strong>Thiết thực mỗi ngày</strong><span class="muted">Sản phẩm được tạo ra để dùng thường xuyên và dễ dàng.</span></div><div class="feature"><strong>An toàn, bền bỉ</strong><span class="muted">Ưu tiên chất liệu tốt và tiêu chuẩn sản xuất rõ ràng.</span></div><div class="feature"><strong>Giao hàng tận nơi</strong><span class="muted">Đóng gói cẩn thận, hỗ trợ nhanh chóng sau mua hàng.</span></div></div></section>';
        render('Trang chủ', $content); exit;
    }
    if ($page === 'san-pham' && isset($segments[1])) {
        $stmt = db()->prepare('SELECT * FROM products WHERE slug = ? AND status=1 LIMIT 1'); $stmt->execute([$segments[1]]); $product = $stmt->fetch();
        if (!$product) { http_response_code(404); render('Không tìm thấy', page_head('Không tìm thấy sản phẩm', 'Sản phẩm này không còn tồn tại.') . '<div class="section container"><a class="btn" href="' . url('san-pham') . '">Xem sản phẩm khác</a></div>'); exit; }
        $reviews = db()->prepare('SELECT name,content,rating,created_at FROM comments WHERE product_id=? AND status=1 ORDER BY id DESC'); $reviews->execute([$product['id']]); $reviewHtml='<h2>Đánh giá sản phẩm</h2>';
        foreach($reviews->fetchAll() as $review) $reviewHtml.='<article class="admin-item"><strong>'.e($review['name']).' · '.(int)$review['rating'].'/5</strong><p>'.e($review['content']).'</p></article>';
        $reviewForm = current_user() ? '<form method="post" action="'.url('binh-luan').'" class="admin-form"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><input type="hidden" name="product_id" value="'.$product['id'].'"><input type="hidden" name="slug" value="'.e($product['slug']).'"><label>Đánh giá<select class="form-control" name="rating"><option value="5">5/5</option><option value="4">4/5</option><option value="3">3/5</option><option value="2">2/5</option><option value="1">1/5</option></select></label><label>Nội dung<textarea class="form-control" name="content" required></textarea></label><button class="btn">Gửi đánh giá</button></form>' : '<p class="muted">Vui lòng đăng nhập để đánh giá sản phẩm.</p>';
        $wish = current_user() ? '<a class="btn btn-light" href="'.url('them-yeu-thich/'.$product['id']).'">Thêm yêu thích</a>' : '';
        render($product['name'], page_head($product['name'], 'Sản phẩm gia dụng được Homely kiểm tra và tuyển chọn.') . '<section class="section"><div class="container hero-grid"><div class="hero-art">' . image_html($product['image'] ?? null, $product['name'], 'detail-image') . '</div><div><h2>' . e($product['name']) . '</h2><p class="muted">' . e($product['description']) . '</p><p class="price">' . format_price($product['price'] ?? null) . '</p><a class="btn" href="' . url('them-gio/' . $product['id']) . '">Thêm vào giỏ hàng</a> '.$wish.'</div></div>'.$reviewHtml.$reviewForm.'</div></section>'); exit;
    }
    if ($page === 'san-pham') {
        $keyword = trim($_GET['q'] ?? ''); $stmt = db()->prepare('SELECT * FROM products WHERE status=1 AND (name LIKE ? OR description LIKE ?) ORDER BY id DESC'); $like = '%' . $keyword . '%'; $stmt->execute([$like,$like]);
        if (isset($_GET['live']) && $_GET['live'] === '1') {
            header('Content-Type: application/json; charset=UTF-8');
            $products = [];
            foreach ($stmt->fetchAll() as $product) {
                $products[] = [
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'price' => format_price($product['price']),
                    'image' => local_image_url($product['image'] ?? null),
                ];
            }
            echo json_encode(['products' => $products], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        }
        render('Đồ gia dụng', page_head('Đồ gia dụng', 'Sản phẩm tiện ích cho căn bếp, phòng tắm và mọi góc nhỏ trong gia đình.') . '<section class="section"><div class="container"><form class="search" method="get" data-live-search><input class="form-control" name="q" value="' . e($keyword) . '" placeholder="Tìm đồ gia dụng..." autocomplete="off"><button class="btn" type="submit">Tìm kiếm</button></form><p class="search-status muted" aria-live="polite"></p><div class="search-results">' . products_html($stmt->fetchAll()) . '</div></div></section>'); exit;
    }
    if ($page === 'gioi-thieu') { $pageData = db()->prepare('SELECT * FROM site_pages WHERE slug=? LIMIT 1'); $pageData->execute(['gioi-thieu']); $pageData = $pageData->fetch(); $pageTitle = $pageData['title'] ?? 'Về Homely'; $pageContent = $pageData['content'] ?? 'Tiện ích bắt đầu từ điều nhỏ nhất'; render('Giới thiệu', page_head($pageTitle, $pageData['meta_description'] ?? 'Thương hiệu đồ gia dụng Việt giúp việc nhà trở nên đơn giản và dễ chịu hơn.') . '<section class="section"><div class="container form-wrap"><div class="page-content">' . nl2br(e($pageContent)) . '</div></div></section>'); exit; }
    if ($page === 'hoi-dap') {
        $faqs=db()->query('SELECT question,answer FROM faqs WHERE status=1 ORDER BY id DESC')->fetchAll(); $html='<div class="form-wrap"><h2>Câu hỏi thường gặp</h2>'; foreach($faqs as $faq)$html.='<article class="admin-item"><h3>'.e($faq['question']).'</h3><p>'.nl2br(e($faq['answer'])).'</p></article>'; $html.='</div>'; render('Hỏi đáp',page_head('Hỏi đáp','Giải đáp nhanh các câu hỏi thường gặp khi mua hàng.' ).'<section class="section"><div class="container">'.$html.'</div></section>'); exit;
    }
    if ($page === 'wishlist') {
        require_login(); $stmt=db()->prepare('SELECT p.* FROM wishlist w JOIN products p ON p.id=w.product_id WHERE w.user_id=? ORDER BY w.id DESC');$stmt->execute([current_user()['id']]);$items=$stmt->fetchAll();$html=products_html($items);if(!$items)$html='<p class="muted">Danh sách yêu thích đang trống.</p>';foreach($items as $item)$html.='<p><a class="btn btn-small" href="'.url('xoa-yeu-thich/'.$item['id']).'">Xóa khỏi yêu thích: '.e($item['name']).'</a></p>';render('Yêu thích',page_head('Sản phẩm yêu thích','Danh sách sản phẩm bạn đã lưu.').'<section class="section"><div class="container">'.$html.'</div></section>');exit;
    }
    if ($page === 'chi-tiet-don' && isset($segments[1])) {
        require_login();$stmt=db()->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');$stmt->execute([(int)$segments[1],current_user()['id']]);$order=$stmt->fetch();if(!$order){http_response_code(404);render('Không tìm thấy',page_head('Không tìm thấy đơn hàng','Đơn hàng không thuộc tài khoản của bạn.'));exit;}$items=db()->prepare('SELECT * FROM order_items WHERE order_id=?');$items->execute([$order['id']]);$rows='';foreach($items->fetchAll() as $item)$rows.='<tr><td>'.e($item['product_name']).'</td><td>'.format_price($item['price']).'</td><td>'.(int)$item['quantity'].'</td></tr>';render('Chi tiết đơn hàng',page_head('Chi tiết đơn #'.$order['id'],'Thông tin đơn hàng của bạn.').'<section class="section"><div class="container"><p>Khách hàng: '.e($order['customer_name']).'<br>Điện thoại: '.e($order['phone']).'<br>Địa chỉ: '.e($order['address']).'<br>Trạng thái: '.e($order['status']).'</p><table class="table"><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th></tr>'.$rows.'</table><p class="price">Tổng: '.format_price($order['total']).'</p></div></section>');exit;
    }
    if ($page === 'lien-he') { $company = []; foreach (db()->query('SELECT setting_key,setting_value FROM company_settings')->fetchAll() as $setting) $company[$setting['setting_key']] = $setting['setting_value']; render('Liên hệ', page_head('Cần hỗ trợ mua hàng?', 'Đội ngũ Homely sẵn sàng tư vấn sản phẩm và tiếp nhận yêu cầu bảo hành.') . '<section class="section"><div class="container form-wrap"><p class="muted">Hotline: ' . e($company['phone'] ?? '') . '<br>Email: ' . e($company['email'] ?? '') . '<br>Địa chỉ: ' . e($company['address'] ?? '') . '<br>Giờ hỗ trợ: ' . e($company['hotline_hours'] ?? '') . '</p><form method="post" data-validate><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><div class="form-group"><label>Họ và tên *</label><input class="form-control" name="name" required></div><div class="form-group"><label>Email *</label><input class="form-control" type="email" name="email" required></div><div class="form-group"><label>Nội dung cần hỗ trợ *</label><textarea class="form-control" name="message" rows="6" required></textarea></div><button class="btn">Gửi yêu cầu</button></form></div></section>'); exit; }
    if ($page === 'dang-nhap' || $page === 'dang-ky') {
        $register = $page === 'dang-ky'; $form = '<section class="section"><div class="container form-wrap"><h2>' . ($register ? 'Tạo tài khoản Homely' : 'Chào mừng trở lại') . '</h2><form method="post" accept-charset="UTF-8" data-validate><input type="hidden" name="csrf" value="' . e(csrf_token()) . '">' . ($register ? '<div class="form-group"><label>Họ và tên *</label><input class="form-control" name="name" required></div>' : '') . '<div class="form-group"><label>Email *</label><input class="form-control" type="email" name="email" required></div><div class="form-group"><label>Mật khẩu *</label><input class="form-control" type="password" name="password" required minlength="6"></div><button class="btn">' . ($register ? 'Đăng ký' : 'Đăng nhập') . '</button></form><p class="muted">' . ($register ? 'Đã có tài khoản? ' . '<a href="' . url('dang-nhap') . '">Đăng nhập</a>' : 'Chưa có tài khoản? <a href="' . url('dang-ky') . '">Đăng ký</a>') . '</p></div></section>';
        render($register ? 'Đăng ký' : 'Đăng nhập', $form); exit;
    }
    if ($page === 'gio-hang') {
        require_login();
        $cartStmt = db()->prepare('SELECT p.*, c.quantity AS cart_quantity FROM cart_items c INNER JOIN products p ON p.id=c.product_id WHERE c.user_id=? AND p.status=1 ORDER BY c.id DESC');
        $cartStmt->execute([current_user()['id']]); $items = $cartStmt->fetchAll(); $total = 0;
        $rows = '<form method="post"><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><table class="table"><tr><th>Sản phẩm</th><th>Đơn giá</th><th>Số lượng</th></tr>';
        foreach ($items as $item) { $qty = (int) $item['cart_quantity']; $price = is_numeric($item['price'] ?? null) ? (float) $item['price'] : 0.0; $total += $price * $qty; $rows .= '<tr><td><div class="cart-product"><div class="cart-image-frame">' . image_html($item['image'] ?? null, $item['name'], 'cart-product-image') . '</div><span>' . e($item['name']) . '</span></div></td><td>' . format_price($item['price'] ?? null) . '</td><td><input class="form-control" type="number" min="0" name="qty[' . $item['id'] . ']" value="' . $qty . '"></td></tr>'; }
        render('Giỏ hàng', page_head('Giỏ hàng', 'Kiểm tra lựa chọn của bạn trước khi đặt hàng.') . '<section class="section"><div class="container">' . ($items ? $rows . '</table><p class="price">Tổng cộng: ' . format_price($total) . '</p><button class="btn">Cập nhật giỏ hàng</button> <a class="btn" href="' . url('thanh-toan') . '">Thanh toán</a></form>' : '<p class="muted">Giỏ hàng đang trống.</p>') . '</div></section>'); exit;
    }
    if ($page === 'thanh-toan') {
        require_login();
        $cartStmt = db()->prepare('SELECT p.*, c.quantity AS cart_quantity FROM cart_items c INNER JOIN products p ON p.id=c.product_id WHERE c.user_id=? AND p.status=1 ORDER BY c.id DESC');
        $cartStmt->execute([current_user()['id']]); $cartItems = $cartStmt->fetchAll();
        if (!$cartItems) { flash('error', 'Giỏ hàng đang trống.'); redirect('san-pham'); }
        $checkoutSummary = '<div class="checkout-products">';
        foreach ($cartItems as $checkoutProduct) {
            $checkoutSummary .= '<div class="checkout-product"><div class="cart-image-frame">' . image_html($checkoutProduct['image'] ?? null, $checkoutProduct['name'], 'cart-product-image') . '</div><span>' . e($checkoutProduct['name']) . ' × ' . (int) $checkoutProduct['cart_quantity'] . '</span></div>';
        }
        $checkoutSummary .= '</div>';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf(); $total = 0; $items = [];
            foreach ($cartItems as $item) { $qty = (int) $item['cart_quantity']; $price = is_numeric($item['price'] ?? null) ? (float) $item['price'] : 0.0; $total += $price * $qty; $items[] = [$item, $qty]; }
            $name = trim($_POST['name'] ?? ''); $phone = trim($_POST['phone'] ?? ''); $address = trim($_POST['address'] ?? '');
            if ($name === '' || $phone === '' || $address === '') throw new RuntimeException('Vui lòng điền đủ thông tin nhận hàng.');
            $pdo = db(); $pdo->beginTransaction(); $pdo->prepare('INSERT INTO orders (user_id,customer_name,phone,address,total) VALUES (?,?,?,?,?)')->execute([current_user()['id'],$name,$phone,$address,$total]); $orderId = (int) $pdo->lastInsertId();
            $line = $pdo->prepare('INSERT INTO order_items (order_id,product_id,product_name,price,quantity) VALUES (?,?,?,?,?)'); foreach ($items as $entry) $line->execute([$orderId,$entry[0]['id'],$entry[0]['name'],$entry[0]['price'],$entry[1]]); $pdo->prepare('DELETE FROM cart_items WHERE user_id=?')->execute([current_user()['id']]); $pdo->commit(); flash('success', 'Đặt hàng thành công. Mã đơn #' . $orderId . '.'); redirect('tai-khoan');
        }
        render('Thanh toán', page_head('Thanh toán', 'Thông tin giao hàng của bạn được bảo mật.') . '<section class="section"><div class="container form-wrap"><h2>Sản phẩm trong đơn</h2>' . $checkoutSummary . '<form method="post" data-validate><input type="hidden" name="csrf" value="' . e(csrf_token()) . '"><div class="form-group"><label>Họ tên *</label><input class="form-control" name="name" value="' . e(current_user()['name']) . '" required></div><div class="form-group"><label>Số điện thoại *</label><input class="form-control" name="phone" required></div><div class="form-group"><label>Địa chỉ nhận hàng *</label><textarea class="form-control" name="address" required rows="4"></textarea></div><button class="btn">Xác nhận đặt hàng</button></form></div></section>'); exit;
    }
    if ($page === 'tai-khoan') {
        require_login();
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            verify_csrf(); $action=$_POST['member_action']??'';
            if($action==='profile'){ $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException('Thông tin hồ sơ chưa hợp lệ.');$avatar=current_user()['avatar']??null;if(!empty($_FILES['avatar']['name'])){if($_FILES['avatar']['error']!==UPLOAD_ERR_OK||$_FILES['avatar']['size']>2*1024*1024||@getimagesize($_FILES['avatar']['tmp_name'])===false)throw new RuntimeException('Avatar không hợp lệ hoặc vượt quá 2MB.');$ext=strtolower(pathinfo($_FILES['avatar']['name'],PATHINFO_EXTENSION));if(!in_array($ext,['jpg','jpeg','png','webp'],true))throw new RuntimeException('Avatar chỉ nhận JPG, PNG hoặc WebP.');$avatar='avatar-'.current_user()['id'].'-'.bin2hex(random_bytes(5)).'.'.$ext;if(!move_uploaded_file($_FILES['avatar']['tmp_name'],__DIR__.'/assets/images/'.$avatar))throw new RuntimeException('Không thể lưu avatar.');}db()->prepare('UPDATE users SET name=?,email=?,avatar=? WHERE id=?')->execute([$name,$email,$avatar,current_user()['id']]);$_SESSION['user']['name']=$name;$_SESSION['user']['email']=$email;$_SESSION['user']['avatar']=$avatar;flash('success','Đã cập nhật hồ sơ.');redirect('tai-khoan');}
            if($action==='password'){if(!password_verify($_POST['current_password']??'',db()->query('SELECT password FROM users WHERE id='.(int)current_user()['id'])->fetchColumn()))throw new RuntimeException('Mật khẩu hiện tại không đúng.');$new=$_POST['new_password']??'';if(strlen($new)<6||$new!==($_POST['confirm_password']??''))throw new RuntimeException('Mật khẩu mới chưa hợp lệ.');db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),current_user()['id']]);flash('success','Đã đổi mật khẩu.');redirect('tai-khoan');}
        }
        $stmt = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC'); $stmt->execute([current_user()['id']]); $rows = ''; foreach ($stmt->fetchAll() as $order) { $itemStmt = db()->prepare('SELECT oi.product_name, p.image FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE oi.order_id=? LIMIT 1'); $itemStmt->execute([$order['id']]); $orderItem = $itemStmt->fetch(); $orderImage = $orderItem ? '<div class="cart-image-frame">' . image_html($orderItem['image'] ?? null, $orderItem['product_name'], 'cart-product-image') . '</div>' : ''; $cancel=$order['status']==='pending'?'<form method="post" action="'.url('huy-don/'.$order['id']).'" class="inline-form"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><button class="btn btn-small">Hủy</button></form>':''; $rows .= '<tr><td><div class="cart-product">' . $orderImage . '<span>#' . $order['id'] . '</span></div></td><td>' . format_price($order['total'] ?? null) . '</td><td>' . e($order['status']) . '</td><td><a href="'.url('chi-tiet-don/'.$order['id']).'">Chi tiết</a> '.$cancel.'</td><td>' . e($order['created_at']) . '</td></tr>'; }
        $contacts=db()->prepare('SELECT * FROM contacts WHERE user_id=? OR (user_id IS NULL AND email=?) ORDER BY id DESC');$contacts->execute([current_user()['id'],current_user()['email']]);$contactRows='';foreach($contacts->fetchAll() as $contact)$contactRows.='<tr><td>'.e($contact['message']).'</td><td>'.($contact['is_replied']?'Đã phản hồi':'Đang xử lý').'</td><td>'.e($contact['response']??'').'</td></tr>';
        $avatar=current_user()['avatar']?image_html(current_user()['avatar'],current_user()['name'],'account-avatar'):'';
        $profile='<h2>Hồ sơ cá nhân</h2><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><input type="hidden" name="member_action" value="profile">'.$avatar.'<label>Họ tên<input class="form-control" name="name" value="'.e(current_user()['name']).'" required></label><label>Email<input class="form-control" type="email" name="email" value="'.e(current_user()['email']).'" required></label><label>Avatar<input class="form-control" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp"></label><button class="btn">Lưu hồ sơ</button></form><h2>Đổi mật khẩu</h2><form method="post" class="admin-form"><input type="hidden" name="csrf" value="'.e(csrf_token()).'"><input type="hidden" name="member_action" value="password"><input class="form-control" type="password" name="current_password" placeholder="Mật khẩu hiện tại" required><input class="form-control" type="password" name="new_password" placeholder="Mật khẩu mới" minlength="6" required><input class="form-control" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" minlength="6" required><button class="btn">Đổi mật khẩu</button></form>';
        render('Tài khoản', page_head('Tài khoản của tôi', 'Theo dõi hồ sơ, đơn hàng và liên hệ của bạn.') . '<section class="section"><div class="container">'.$profile.'<h2>Đơn hàng của tôi</h2><table class="table"><tr><th>Mã đơn</th><th>Tổng</th><th>Trạng thái</th><th>Thao tác</th><th>Ngày đặt</th></tr>' . ($rows ?: '<tr><td colspan="5">Chưa có đơn hàng.</td></tr>') . '</table><h2>Lịch sử liên hệ</h2><table class="table"><tr><th>Nội dung</th><th>Trạng thái</th><th>Phản hồi</th></tr>'.($contactRows?:'<tr><td colspan="3">Chưa có liên hệ.</td></tr>').'</table></div></section>'); exit;
    }
    if ($page === 'admin') {
        require_admin();
        $pdo = db();
        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, product_id INT UNSIGNED NULL, name VARCHAR(120) NOT NULL, content TEXT NOT NULL, rating TINYINT UNSIGNED DEFAULT NULL, status TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        try { $pdo->exec('ALTER TABLE comments ADD COLUMN rating TINYINT UNSIGNED DEFAULT NULL'); } catch (PDOException $ignored) {}
        $pdo->exec("CREATE TABLE IF NOT EXISTS faqs (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, question VARCHAR(255) NOT NULL, answer TEXT NOT NULL, status TINYINT(1) DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_pages (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) NOT NULL UNIQUE, title VARCHAR(180) NOT NULL, content TEXT NOT NULL, meta_description VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS company_settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        try { $pdo->exec('ALTER TABLE contacts ADD COLUMN response TEXT DEFAULT NULL'); } catch (PDOException $ignored) {}
        try { $pdo->exec('ALTER TABLE contacts ADD COLUMN is_replied TINYINT(1) DEFAULT 0'); } catch (PDOException $ignored) {}
        $section = $_GET['section'] ?? 'overview';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = $_POST['admin_action'] ?? '';
            if ($action === 'delete_user') {
                $id = (int) ($_POST['id'] ?? 0);
                if ($id === (int) current_user()['id']) throw new RuntimeException('Không thể xóa tài khoản admin đang đăng nhập.');
                $pdo->prepare('DELETE FROM users WHERE id=? AND role <> ?')->execute([$id, 'admin']);
                flash('success', 'Đã xóa người dùng.'); redirect('admin?section=users');
            }
            if ($action === 'save_user') {
                $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $role=$_POST['role']==='admin'?'admin':'member'; $status=(int)($_POST['status']??1);
                if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Tên và email người dùng chưa hợp lệ.');
                if ($id) { $pdo->prepare('UPDATE users SET name=?,email=?,role=?,status=? WHERE id=?')->execute([$name,$email,$role,$status,$id]); } else { $password=trim($_POST['password']??''); if(strlen($password)<6) throw new RuntimeException('Mật khẩu mới phải có ít nhất 6 ký tự.'); $pdo->prepare('INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,?,?)')->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role,$status]); }
                flash('success','Đã lưu người dùng.'); redirect('admin?section=users');
            }
            if ($action === 'reset_password') { $password=trim($_POST['password']??''); if(strlen($password)<6) throw new RuntimeException('Mật khẩu reset phải có ít nhất 6 ký tự.'); $pdo->prepare('UPDATE users SET password=? WHERE id=? AND id<>?')->execute([password_hash($password,PASSWORD_DEFAULT),(int)$_POST['id'],(int)current_user()['id']]); flash('success','Đã reset mật khẩu người dùng.'); redirect('admin?section=users'); }
            if ($action === 'toggle_user') { $pdo->prepare('UPDATE users SET status=1-status WHERE id=? AND id<>?')->execute([(int) $_POST['id'], (int) current_user()['id']]); flash('success', 'Đã cập nhật trạng thái người dùng.'); redirect('admin?section=users'); }
            if ($action === 'save_product') {
                $id = (int) ($_POST['id'] ?? 0); $name = trim($_POST['name'] ?? ''); $description = trim($_POST['description'] ?? ''); $price = is_numeric($_POST['price'] ?? null) ? (float) $_POST['price'] : 0; $existingImage = '';
                if ($id) { $existing = $pdo->prepare('SELECT image FROM products WHERE id=?'); $existing->execute([$id]); $existingImage = (string) ($existing->fetchColumn() ?: ''); }
                if ($name === '') throw new RuntimeException('Tên sản phẩm không được để trống.');
                $image = null;
                if (!empty($_FILES['image']['name'])) {
                    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK || $_FILES['image']['size'] > 5 * 1024 * 1024 || @getimagesize($_FILES['image']['tmp_name']) === false) throw new RuntimeException('Ảnh không hợp lệ hoặc vượt quá 5MB.');
                    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) throw new RuntimeException('Chỉ nhận JPG, JPEG, PNG hoặc WebP.');
                    $baseName = slugify(pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
                    if ($baseName === '') $baseName = slugify($name);
                    $image = $baseName . '.' . $ext;
                    $counter = 1;
                    while (is_file(__DIR__ . '/assets/images/' . $image)) {
                        $image = $baseName . '-' . $counter . '.' . $ext;
                        $counter++;
                    }
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/assets/images/' . $image)) throw new RuntimeException('Không thể lưu ảnh sản phẩm.');
                }
                $slugSource = $image ?: $existingImage;
                $slug = slugify(pathinfo($slugSource, PATHINFO_FILENAME));
                if ($slug === '') $slug = slugify($name);
                if ($slug === '') throw new RuntimeException('Không thể tạo đường dẫn sản phẩm từ tên hoặc ảnh.');
                if ($id) {
                    $sql = 'UPDATE products SET name=?,slug=?,description=?,price=?' . ($image ? ',image=?' : '') . ' WHERE id=?'; $args = [$name,$slug,$description,$price]; if ($image) $args[] = $image; $args[] = $id; $pdo->prepare($sql)->execute($args);
                } else { $pdo->prepare('INSERT INTO products (name,slug,description,price,image) VALUES (?,?,?,?,?)')->execute([$name,$slug,$description,$price,$image]); }
                flash('success', 'Đã lưu sản phẩm.'); redirect('admin?section=products');
            }
            if ($action === 'delete_product') { $pdo->prepare('DELETE FROM products WHERE id=?')->execute([(int) $_POST['id']]); flash('success', 'Đã xóa sản phẩm.'); redirect('admin?section=products'); }
            if ($action === 'order_status') { $status=$_POST['status']??''; if(!in_array($status,['pending','confirmed','completed','cancelled'],true)) throw new RuntimeException('Trạng thái đơn hàng không hợp lệ.'); $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$status, (int) $_POST['id']]); flash('success', 'Đã cập nhật trạng thái đơn hàng.'); redirect('admin?section=orders'); }
            if ($action === 'save_page') { $id=(int)($_POST['id']??0); $args=[trim($_POST['slug']??''),trim($_POST['title']??''),trim($_POST['content']??''),trim($_POST['meta_description']??'')]; if($args[0]===''||$args[1]==='') throw new RuntimeException('Slug và tiêu đề trang không được để trống.'); if($id){$args[]=$id;$pdo->prepare('UPDATE site_pages SET slug=?,title=?,content=?,meta_description=? WHERE id=?')->execute($args);}else{$pdo->prepare('INSERT INTO site_pages (slug,title,content,meta_description) VALUES (?,?,?,?)')->execute($args);} flash('success','Đã lưu nội dung trang.'); redirect('admin?section=pages'); }
            if ($action === 'save_company') { foreach(['phone','email','address','hotline_hours'] as $key) $pdo->prepare('INSERT INTO company_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)')->execute([$key,trim($_POST[$key]??'')]); flash('success','Đã cập nhật thông tin công ty.'); redirect('admin?section=company'); }
            if ($action === 'contact_update') { $pdo->prepare('UPDATE contacts SET is_read=1,is_replied=?,response=? WHERE id=?')->execute([trim($_POST['response']??'')!==''?1:0,trim($_POST['response']??''),(int)$_POST['id']]); flash('success','Đã cập nhật liên hệ.'); redirect('admin?section=contacts'); }
            if ($action === 'comment_status') { $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute([(int)$_POST['status'],(int)$_POST['id']]); flash('success','Đã cập nhật bình luận.'); redirect('admin?section=comments'); }
            if ($action === 'delete_comment') { $pdo->prepare('DELETE FROM comments WHERE id=?')->execute([(int)$_POST['id']]); flash('success','Đã xóa bình luận.'); redirect('admin?section=comments'); }
            if ($action === 'save_faq') { $id=(int)($_POST['id']??0); $args=[trim($_POST['question']??''),trim($_POST['answer']??'')]; if($args[0]===''||$args[1]==='') throw new RuntimeException('Câu hỏi và câu trả lời không được để trống.'); if($id){$args[]=$id;$pdo->prepare('UPDATE faqs SET question=?,answer=? WHERE id=?')->execute($args);}else{$pdo->prepare('INSERT INTO faqs (question,answer) VALUES (?,?)')->execute($args);} flash('success','Đã lưu hỏi đáp.'); redirect('admin?section=faqs'); }
            if ($action === 'delete_faq') { $pdo->prepare('DELETE FROM faqs WHERE id=?')->execute([(int)$_POST['id']]); flash('success','Đã xóa hỏi đáp.'); redirect('admin?section=faqs'); }
        }
        $csrf = '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
        $tabs = '<nav class="admin-tabs"><a href="' . url('admin') . '">Tổng quan</a><a href="' . url('admin?section=users') . '">Người dùng</a><a href="' . url('admin?section=products') . '">Sản phẩm</a><a href="' . url('admin?section=orders') . '">Đơn hàng</a><a href="' . url('admin?section=contacts') . '">Liên hệ</a><a href="' . url('admin?section=comments') . '">Bình luận</a><a href="' . url('admin?section=faqs') . '">Hỏi đáp</a><a href="' . url('admin?section=pages') . '">Nội dung trang</a><a href="' . url('admin?section=company') . '">Thông tin công ty</a></nav>';
        $body = $tabs;
        if ($section === 'users') { $edit=[]; if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM users WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:[];} $body.='<h2>'.($edit?'Sửa':'Thêm').' người dùng</h2><form method="post" class="admin-form">'.$csrf.'<input type="hidden" name="admin_action" value="save_user"><input type="hidden" name="id" value="'.e($edit['id']??'').'"><label>Họ tên<input class="form-control" name="name" required value="'.e($edit['name']??'').'"></label><label>Email<input class="form-control" type="email" name="email" required value="'.e($edit['email']??'').'"></label><label>Vai trò<select class="form-control" name="role"><option value="member">Thành viên</option><option value="admin" '.(($edit['role']??'')==='admin'?'selected':'').'>Admin</option></select></label><label>Trạng thái<select class="form-control" name="status"><option value="1">Hoạt động</option><option value="0" '.(isset($edit['status'])&&!$edit['status']?'selected':'').'>Đã khóa</option></select></label>'.(!$edit?'<label>Mật khẩu<input class="form-control" type="password" name="password" minlength="6" required></label>':'').'<button class="btn">Lưu người dùng</button></form><h2>Danh sách người dùng</h2><table class="table"><tr><th>Tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Thao tác</th></tr>'; foreach($pdo->query('SELECT id,name,email,role,status,created_at FROM users ORDER BY id DESC')->fetchAll() as $u) $body.='<tr><td>'.e($u['name']).'</td><td>'.e($u['email']).'</td><td>'.e($u['role']).'</td><td>'.($u['status']?'Hoạt động':'Đã khóa').'</td><td><a class="btn btn-small" href="'.url('admin?section=users&edit='.$u['id']).'">Sửa</a> <form method="post" class="inline-form">'.$csrf.'<input type="hidden" name="admin_action" value="toggle_user"><input type="hidden" name="id" value="'.$u['id'].'"><button class="btn btn-small">Khóa/mở</button></form> <form method="post" class="inline-form">'.$csrf.'<input type="hidden" name="admin_action" value="reset_password"><input type="hidden" name="id" value="'.$u['id'].'"><input name="password" type="password" minlength="6" placeholder="Mật khẩu mới" required><button class="btn btn-small">Reset MK</button></form> <form method="post" class="inline-form" onsubmit="return confirm(\'Xóa người dùng này?\')">'.$csrf.'<input type="hidden" name="admin_action" value="delete_user"><input type="hidden" name="id" value="'.$u['id'].'"><button class="btn btn-danger btn-small">Xóa</button></form></td></tr>'; $body.='</table>'; }
        if ($section === 'products') { $edit = isset($_GET['edit']) ? $pdo->prepare('SELECT * FROM products WHERE id=?') : null; if($edit){$edit->execute([(int)$_GET['edit']]);$edit=$edit->fetch()?:[];}else{$edit=[];} $body.='<h2>'.($edit?'Sửa':'Thêm').' sản phẩm</h2><p class="muted">Đường dẫn sản phẩm sẽ tự tạo theo tên file ảnh (hoặc tên sản phẩm nếu chưa có ảnh).</p><form method="post" enctype="multipart/form-data" class="admin-form">'.$csrf.'<input type="hidden" name="admin_action" value="save_product"><input type="hidden" name="id" value="'.e($edit['id']??'').'"><label>Tên sản phẩm<input class="form-control" name="name" required value="'.e($edit['name']??'').'"></label><label>Mô tả<textarea class="form-control" name="description" rows="3">'.e($edit['description']??'').'</textarea></label><label>Giá<input class="form-control" type="number" min="0" name="price" value="'.e((string)($edit['price']??0)).'"></label><label>Ảnh sản phẩm<input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></label><button class="btn">Lưu sản phẩm</button></form><h2>Danh sách sản phẩm</h2><table class="table"><tr><th>Ảnh</th><th>Tên</th><th>Giá</th><th>Hiển thị</th><th>Thao tác</th></tr>'; foreach($pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll() as $p) $body.='<tr><td>'.image_html($p['image'],$p['name'],'admin-product-image').'</td><td>'.e($p['name']).'</td><td>'.format_price($p['price']).'</td><td>'.($p['status']?'Có':'Ẩn').'</td><td><a class="btn btn-small" href="'.url('admin?section=products&edit='.$p['id']).'">Sửa</a> <form method="post" class="inline-form" onsubmit="return confirm(\'Xóa sản phẩm này?\')">'.$csrf.'<input type="hidden" name="admin_action" value="delete_product"><input type="hidden" name="id" value="'.$p['id'].'"><button class="btn btn-danger btn-small">Xóa</button></form></td></tr>'; $body.='</table>'; }
        if ($section === 'orders') { $rows=''; foreach($pdo->query('SELECT * FROM orders ORDER BY id DESC')->fetchAll() as $o) {$rows.='<tr><td>#'.$o['id'].'</td><td>'.e($o['customer_name']).'</td><td>'.format_price($o['total']).'</td><td><form method="post">'.$csrf.'<input type="hidden" name="admin_action" value="order_status"><input type="hidden" name="id" value="'.$o['id'].'"><select class="form-control" name="status"><option '.($o['status']==='pending'?'selected':'').' value="pending">Chờ xử lý</option><option '.($o['status']==='confirmed'?'selected':'').' value="confirmed">Đã xác nhận</option><option '.($o['status']==='completed'?'selected':'').' value="completed">Hoàn tất</option><option '.($o['status']==='cancelled'?'selected':'').' value="cancelled">Đã hủy</option></select><button class="btn btn-small">Lưu</button></form></td><td><a href="'.url('admin?section=order&id='.$o['id']).'">Chi tiết</a></td></tr>';}$body.='<h2>Quản lý đơn hàng</h2><table class="table"><tr><th>Mã</th><th>Khách hàng</th><th>Tổng</th><th>Trạng thái</th><th></th></tr>'.$rows.'</table>'; }
        if ($section === 'order' && isset($_GET['id'])) { $s=$pdo->prepare('SELECT * FROM orders WHERE id=?');$s->execute([(int)$_GET['id']]);$o=$s->fetch();$s=$pdo->prepare('SELECT * FROM order_items WHERE order_id=?');$s->execute([(int)$_GET['id']]);$rows='';foreach($s->fetchAll() as $i)$rows.='<tr><td>'.e($i['product_name']).'</td><td>'.format_price($i['price']).'</td><td>'.(int)$i['quantity'].'</td></tr>';$body.='<h2>Chi tiết đơn #'.(int)$_GET['id'].'</h2><p>Khách: '.e($o['customer_name']??'').' · '.e($o['phone']??'').'<br>Địa chỉ: '.e($o['address']??'').'</p><table class="table"><tr><th>Sản phẩm</th><th>Giá</th><th>SL</th></tr>'.$rows.'</table>'; }
        if ($section === 'contacts') { $body.='<h2>Liên hệ khách hàng</h2>'; foreach($pdo->query('SELECT * FROM contacts ORDER BY id DESC')->fetchAll() as $c)$body.='<article class="admin-item"><strong>'.e($c['name']).' · '.e($c['email']).'</strong><p>'.e($c['message']).'</p><form method="post">'.$csrf.'<input type="hidden" name="admin_action" value="contact_update"><input type="hidden" name="id" value="'.$c['id'].'"><textarea class="form-control" name="response" placeholder="Nội dung phản hồi">'.e($c['response']??'').'</textarea><button class="btn btn-small">Đánh dấu đã đọc/phản hồi</button></form></article>'; }
        if ($section === 'comments') { $body.='<h2>Kiểm duyệt bình luận/đánh giá</h2><table class="table"><tr><th>Tên</th><th>Nội dung</th><th>Đánh giá</th><th>Trạng thái</th><th></th></tr>'; foreach($pdo->query('SELECT * FROM comments ORDER BY id DESC')->fetchAll() as $c)$body.='<tr><td>'.e($c['name']).'</td><td>'.e($c['content']).'</td><td>'.($c['rating']!==null?(int)$c['rating'].'/5':'-').'</td><td>'.($c['status']?'Đã duyệt':'Chờ duyệt').'</td><td><form method="post" class="inline-form">'.$csrf.'<input type="hidden" name="admin_action" value="comment_status"><input type="hidden" name="id" value="'.$c['id'].'"><input type="hidden" name="status" value="'.($c['status']?0:1).'"><button class="btn btn-small">'.($c['status']?'Ẩn':'Duyệt').'</button></form> <form method="post" class="inline-form">'.$csrf.'<input type="hidden" name="admin_action" value="delete_comment"><input type="hidden" name="id" value="'.$c['id'].'"><button class="btn btn-danger btn-small">Xóa</button></form></td></tr>'; $body.='</table>'; }
        if ($section === 'faqs') { $edit=[]; if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM faqs WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:[];} $body.='<h2>'.($edit?'Sửa':'Thêm').' hỏi đáp</h2><form method="post" class="admin-form">'.$csrf.'<input type="hidden" name="admin_action" value="save_faq"><input type="hidden" name="id" value="'.e($edit['id']??'').'"><label>Câu hỏi<input class="form-control" name="question" required value="'.e($edit['question']??'').'"></label><label>Trả lời<textarea class="form-control" name="answer" required>'.e($edit['answer']??'').'</textarea></label><button class="btn">Lưu hỏi đáp</button></form><h2>Danh sách hỏi đáp</h2>'; foreach($pdo->query('SELECT * FROM faqs ORDER BY id DESC')->fetchAll() as $f)$body.='<p><strong>'.e($f['question']).'</strong>: '.e($f['answer']).' <a class="btn btn-small" href="'.url('admin?section=faqs&edit='.$f['id']).'">Sửa</a> <form method="post" class="inline-form">'.$csrf.'<input type="hidden" name="admin_action" value="delete_faq"><input type="hidden" name="id" value="'.$f['id'].'"><button class="btn btn-danger btn-small">Xóa</button></form></p>'; }
        if ($section === 'pages') { $edit=[]; if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM site_pages WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch()?:[];} $body.='<h2>'.($edit?'Sửa':'Thêm').' nội dung trang</h2><form method="post" class="admin-form">'.$csrf.'<input type="hidden" name="admin_action" value="save_page"><input type="hidden" name="id" value="'.e($edit['id']??'').'"><label>Slug<input class="form-control" name="slug" required value="'.e($edit['slug']??'').'"></label><label>Tiêu đề<input class="form-control" name="title" required value="'.e($edit['title']??'').'"></label><label>Nội dung<textarea class="form-control" name="content" rows="8" required>'.e($edit['content']??'').'</textarea></label><label>Mô tả SEO<input class="form-control" name="meta_description" value="'.e($edit['meta_description']??'').'"></label><button class="btn">Lưu nội dung</button></form><h2>Trang đã cấu hình</h2>'; foreach($pdo->query('SELECT * FROM site_pages ORDER BY slug')->fetchAll() as $p)$body.='<p><strong>'.e($p['title']).'</strong> ('.e($p['slug']).') <a class="btn btn-small" href="'.url('admin?section=pages&edit='.$p['id']).'">Sửa</a></p>'; }
        if ($section === 'company') { $company=[]; foreach($pdo->query('SELECT setting_key,setting_value FROM company_settings')->fetchAll() as $s)$company[$s['setting_key']]=$s['setting_value']; $body.='<h2>Thông tin liên hệ công ty</h2><form method="post" class="admin-form">'.$csrf.'<input type="hidden" name="admin_action" value="save_company"><label>Số điện thoại<input class="form-control" name="phone" value="'.e($company['phone']??'').'"></label><label>Email<input class="form-control" type="email" name="email" value="'.e($company['email']??'').'"></label><label>Địa chỉ<input class="form-control" name="address" value="'.e($company['address']??'').'"></label><label>Giờ hỗ trợ<input class="form-control" name="hotline_hours" value="'.e($company['hotline_hours']??'').'"></label><button class="btn">Lưu thông tin công ty</button></form>'; }
        if ($section === 'overview') { $body.='<h2>Bảng điều khiển</h2><p>Quản lý người dùng, sản phẩm, đơn hàng, tin tức, liên hệ, bình luận, hỏi đáp và bảng giá từ các mục bên trên.</p>'; }
        render('Quản trị', page_head('Bảng điều khiển', 'Quản lý dữ liệu Homely.') . '<section class="section"><div class="container admin-panel">' . $body . '</div></section>'); exit;
    }
    http_response_code(404); render('Không tìm thấy', page_head('404', 'Trang bạn tìm kiếm không tồn tại.') . '<div class="section container"><a class="btn" href="' . url() . '">Về trang chủ</a></div>');
} catch (Throwable $exception) {
    if ($exception instanceof PDOException) {
        render('Lỗi kết nối', page_head('Chưa kết nối được dữ liệu', 'Apache vẫn có thể đang chạy bình thường, nhưng MySQL hoặc thông tin kết nối chưa sẵn sàng.') . '<section class="section"><div class="container form-wrap"><div class="alert">Hãy Start MySQL trong XAMPP Control Panel và kiểm tra DB_HOST, DB_NAME, DB_USER, DB_PASS trong config/config.php. Sau đó import database.sql vào phpMyAdmin.</div></div></section>');
    } else {
        flash('error', $exception->getMessage()); redirect('');
    }
}
