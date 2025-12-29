<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/DataBaseManagement/DB.php';
require_once __DIR__ . '/WebVisuals/header.php';

$page = $_GET['page'] ?? 'home';

function view($path){
    $file = __DIR__ . "/src/{$path}.php";
    if (file_exists($file)) require $file;
    else { http_response_code(404); echo "<p>View not found: {$path}</p>"; }
}

switch ($page) {
    case 'home':
        echo "<p>Home page works!</p>";
        $dataB = DB::get();
        echo "<p><a href='?page=products'>View products</a></p>";
        break;
    case 'products':
        view('products');
        break;
    case 'product':
        view('product');
        break;
    case 'cart':
        view('cart');
        break;
    case 'checkout':
        view('checkout');
        break;
    case 'register':
        view('register');
        break;
    case 'login':
        view('login');
        break;
    case 'verify':
        view('verify');
        break;
    case 'forgot_password':
        view('forgot_password');
        break;
    case 'password_recover':
        view('password_recover');
        break;
    case 'reset_password':
        view('reset_password');
        break;
    case 'admin':
        view('admin/admin_dashboard');
        break;
    case 'admin_products':
        view('admin/admin_products');
        break;
    case 'admin_product_add':
        view('admin/product_add');
        break;
    case 'admin_orders':
        view('admin/admin_orders');
        break;
    case 'admin_users':
        view('admin/admin_users');
        break;
    case 'logout':
        view('logout');
        break;
    default:
        http_response_code(404);
        echo "<p>Page not found: {$page}</p>";
}
require_once __DIR__ . '/WebVisuals/footer.php';

?>