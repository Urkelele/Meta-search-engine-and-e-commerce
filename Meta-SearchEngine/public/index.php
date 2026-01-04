<?php
session_start();

$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');  // /.../public
$base = preg_replace('#/public$#', '', $base);         // /... (project root)

$page = $_GET['page'] ?? 'home';

// lista blanca de páginas permitidas
$routes = [
  'home'     => __DIR__ . '/../views/home.php',
  'product'  => __DIR__ . '/../views/product.php',
  'cart'     => __DIR__ . '/../views/cart.php',
  'orders'   => __DIR__ . '/../views/orders.php',
  'login'    => __DIR__ . '/../views/login.php',
  'register' => __DIR__ . '/../views/register.php',
  'verify'   => __DIR__ . '/../views/verify.php',
  'logout'   => __DIR__ . '/../views/logout.php',
  'checkout' => __DIR__ . '/../views/checkout.php',
];

$view = $routes[$page] ?? null;
if (!$view || !file_exists($view)) {
  $view = __DIR__ . '/../views/404.php';
}

// Variables globales disponibles para las vistas:
$GLOBALS['BASE'] = $base;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>MSE Shop</title>
</head>
<body>

<?php require __DIR__ . '/../includes/topbar.php'; ?>

<div style="max-width:1100px;margin:0 auto;padding:14px;">
  <?php require $view; ?>
</div>

</body>
</html>