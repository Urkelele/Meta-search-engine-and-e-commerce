-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-01-2026 a las 19:21:57
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tech_shop`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attributes`
--

CREATE TABLE `attributes` (
  `attribute_id` int(32) UNSIGNED NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `attributes`
--

INSERT INTO `attributes` (`attribute_id`, `name`) VALUES
(1, 'Mechanical'),
(2, 'Membrane'),
(3, '60%'),
(4, 'Full-size'),
(5, 'Wired'),
(6, 'Wireless'),
(7, 'Gaming'),
(8, 'Ergonomic'),
(9, 'Lightweight'),
(10, '1080p'),
(11, '4K'),
(12, '60Hz'),
(13, '144Hz'),
(14, '240Hz'),
(15, 'IPS'),
(16, 'OLED'),
(17, 'Over-ear'),
(18, 'In-ear'),
(19, 'RGB'),
(20, 'ANC');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(32) UNSIGNED NOT NULL,
  `user_id` int(32) UNSIGNED NOT NULL,
  `status` enum('open','completed','expired','') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(32) UNSIGNED NOT NULL,
  `cart_id` int(32) UNSIGNED NOT NULL,
  `product_id` int(32) UNSIGNED NOT NULL,
  `quantity` int(32) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `category_id` int(16) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Keyboard'),
(2, 'Mouse'),
(3, 'Monitor'),
(4, 'Headset');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `order_id` int(32) UNSIGNED NOT NULL,
  `user_id` int(32) UNSIGNED NOT NULL,
  `total_price` decimal(30,2) NOT NULL,
  `status` enum('paid','shipped','','') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(1, 29, 482.00, 'shipped', '2025-12-19 02:08:26'),
(2, 5, 827.00, 'shipped', '2025-12-19 02:08:26'),
(3, 23, 1236.00, 'paid', '2025-12-19 02:08:26'),
(4, 20, 392.00, 'shipped', '2025-12-19 02:08:26'),
(5, 4, 1425.00, 'shipped', '2025-12-19 02:08:26'),
(6, 19, 1179.00, 'paid', '2025-12-19 02:08:26'),
(7, 13, 1858.00, 'paid', '2025-12-19 02:08:26'),
(8, 27, 900.00, 'paid', '2025-12-19 02:08:26'),
(9, 8, 239.00, 'paid', '2025-12-19 02:08:26'),
(10, 16, 1434.00, 'shipped', '2025-12-19 02:08:26'),
(11, 26, 491.00, 'shipped', '2025-12-19 02:08:26'),
(12, 13, 1822.50, 'paid', '2025-12-19 02:08:26'),
(13, 20, 971.00, 'paid', '2025-12-19 02:08:26'),
(14, 17, 418.00, 'paid', '2025-12-19 02:08:26'),
(15, 24, 99.00, 'paid', '2025-12-19 02:08:26'),
(16, 15, 981.00, 'paid', '2025-12-19 02:08:26'),
(17, 15, 752.00, 'paid', '2025-12-19 02:08:26'),
(18, 17, 876.98, 'paid', '2025-12-19 02:08:26'),
(19, 24, 1256.97, 'paid', '2025-12-19 02:08:26'),
(20, 25, 241.00, 'shipped', '2025-12-19 02:08:26'),
(21, 27, 1146.00, 'paid', '2025-12-19 02:08:26'),
(22, 30, 1230.00, 'shipped', '2025-12-19 02:08:26'),
(23, 20, 192.00, 'shipped', '2025-12-19 02:08:26'),
(24, 19, 393.00, 'shipped', '2025-12-19 02:08:26'),
(25, 26, 1317.00, 'shipped', '2025-12-19 02:08:26'),
(26, 32, 570.00, 'shipped', '2025-12-19 02:08:26'),
(27, 9, 169.00, 'shipped', '2025-12-19 02:08:26'),
(28, 14, 999.99, 'shipped', '2025-12-19 02:08:26'),
(29, 3, 562.00, 'paid', '2025-12-19 02:08:26'),
(30, 13, 765.98, 'shipped', '2025-12-19 02:08:26'),
(31, 9, 288.00, 'paid', '2025-12-19 02:08:26'),
(32, 26, 736.00, 'shipped', '2025-12-19 02:08:26'),
(33, 30, 1065.00, 'paid', '2025-12-19 02:08:26'),
(34, 7, 868.00, 'paid', '2025-12-19 02:08:26'),
(35, 18, 1408.00, 'shipped', '2025-12-19 02:08:26'),
(36, 16, 1026.00, 'shipped', '2025-12-19 02:08:26'),
(37, 4, 1626.00, 'shipped', '2025-12-19 02:08:26'),
(38, 29, 1280.00, 'shipped', '2025-12-19 02:08:26'),
(39, 32, 950.00, 'shipped', '2025-12-19 02:08:26'),
(40, 9, 770.00, 'paid', '2025-12-19 02:08:26'),
(41, 5, 992.97, 'paid', '2025-12-19 02:08:26'),
(42, 17, 198.00, 'shipped', '2025-12-19 02:08:26'),
(43, 23, 693.00, 'shipped', '2025-12-19 02:08:26'),
(44, 7, 915.00, 'shipped', '2025-12-19 02:08:26'),
(45, 25, 231.00, 'paid', '2025-12-19 02:08:26'),
(46, 5, 207.50, 'shipped', '2025-12-19 02:08:26'),
(47, 32, 59.50, 'paid', '2025-12-19 02:08:26'),
(48, 30, 412.98, 'paid', '2025-12-19 02:08:26'),
(49, 17, 1160.50, 'shipped', '2025-12-19 02:08:26'),
(50, 19, 779.00, 'paid', '2025-12-19 02:08:26'),
(51, 34, 192.00, 'paid', '2026-01-02 16:48:32'),
(52, 34, 211.00, 'paid', '2026-01-02 16:48:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(32) NOT NULL,
  `order_id` int(32) UNSIGNED NOT NULL,
  `product_id` int(32) UNSIGNED NOT NULL,
  `quantity` int(32) NOT NULL,
  `price` decimal(30,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 28, 2, 241.00),
(2, 2, 34, 3, 131.00),
(3, 2, 25, 1, 83.00),
(4, 2, 33, 1, 192.00),
(5, 2, 39, 3, 53.00),
(6, 3, 28, 3, 241.00),
(7, 3, 11, 1, 138.00),
(8, 3, 13, 1, 96.00),
(9, 3, 10, 1, 279.00),
(10, 4, 6, 3, 61.00),
(11, 4, 18, 1, 209.00),
(12, 5, 7, 2, 144.00),
(13, 5, 30, 2, 241.00),
(14, 5, 40, 3, 189.00),
(15, 5, 20, 1, 88.00),
(16, 6, 33, 1, 192.00),
(17, 6, 20, 3, 88.00),
(18, 6, 28, 2, 241.00),
(19, 6, 16, 1, 241.00),
(20, 7, 19, 1, 235.00),
(21, 7, 30, 3, 241.00),
(22, 7, 8, 3, 300.00),
(23, 8, 8, 2, 300.00),
(24, 8, 26, 3, 64.00),
(25, 8, 15, 3, 36.00),
(26, 9, 27, 1, 143.00),
(27, 9, 13, 1, 96.00),
(28, 10, 35, 2, 211.00),
(29, 10, 16, 2, 241.00),
(30, 10, 22, 2, 28.00),
(31, 10, 41, 2, 237.00),
(32, 11, 10, 1, 279.00),
(33, 11, 21, 1, 212.00),
(34, 12, 23, 3, 99.00),
(35, 12, 21, 3, 212.00),
(36, 12, 41, 3, 237.00),
(37, 12, 2, 3, 59.50),
(38, 13, 41, 2, 237.00),
(39, 13, 43, 2, 218.00),
(40, 13, 6, 1, 61.00),
(41, 14, 18, 2, 209.00),
(42, 15, 23, 1, 99.00),
(43, 16, 24, 3, 147.00),
(44, 16, 7, 3, 144.00),
(45, 16, 15, 3, 36.00),
(46, 17, 31, 3, 157.00),
(47, 17, 36, 1, 281.00),
(48, 18, 25, 3, 83.00),
(49, 18, 33, 2, 192.00),
(50, 18, 1, 2, 89.99),
(51, 18, 26, 1, 64.00),
(52, 19, 4, 3, 129.99),
(53, 19, 8, 2, 300.00),
(54, 19, 22, 3, 28.00),
(55, 19, 6, 3, 61.00),
(56, 20, 5, 1, 241.00),
(57, 21, 39, 3, 53.00),
(58, 21, 7, 1, 144.00),
(59, 21, 36, 3, 281.00),
(60, 22, 34, 3, 131.00),
(61, 22, 10, 3, 279.00),
(62, 23, 26, 3, 64.00),
(63, 24, 34, 3, 131.00),
(64, 25, 23, 2, 99.00),
(65, 25, 11, 3, 138.00),
(66, 25, 14, 1, 273.00),
(67, 25, 7, 3, 144.00),
(68, 26, 17, 2, 116.00),
(69, 26, 39, 2, 53.00),
(70, 26, 44, 2, 116.00),
(71, 27, 39, 1, 53.00),
(72, 27, 17, 1, 116.00),
(73, 28, 18, 1, 209.00),
(74, 28, 28, 2, 241.00),
(75, 28, 4, 1, 129.99),
(76, 28, 32, 1, 179.00),
(77, 29, 36, 2, 281.00),
(78, 30, 4, 2, 129.99),
(79, 30, 42, 2, 253.00),
(80, 31, 25, 3, 83.00),
(81, 31, 37, 3, 13.00),
(82, 32, 42, 2, 253.00),
(83, 32, 24, 1, 147.00),
(84, 32, 25, 1, 83.00),
(85, 33, 13, 3, 96.00),
(86, 33, 19, 2, 235.00),
(87, 33, 36, 1, 281.00),
(88, 33, 37, 2, 13.00),
(89, 34, 44, 2, 116.00),
(90, 34, 21, 3, 212.00),
(91, 35, 40, 3, 189.00),
(92, 35, 28, 2, 241.00),
(93, 35, 25, 1, 83.00),
(94, 35, 11, 2, 138.00),
(95, 36, 42, 2, 253.00),
(96, 36, 37, 2, 13.00),
(97, 36, 44, 1, 116.00),
(98, 36, 40, 2, 189.00),
(99, 37, 2, 2, 59.50),
(100, 37, 40, 2, 189.00),
(101, 37, 36, 3, 281.00),
(102, 37, 27, 2, 143.00),
(103, 38, 5, 3, 241.00),
(104, 38, 36, 1, 281.00),
(105, 38, 11, 2, 138.00),
(106, 39, 44, 3, 116.00),
(107, 39, 43, 2, 218.00),
(108, 39, 25, 2, 83.00),
(109, 40, 37, 1, 13.00),
(110, 40, 20, 3, 88.00),
(111, 40, 12, 1, 205.00),
(112, 40, 13, 3, 96.00),
(113, 41, 16, 3, 241.00),
(114, 41, 1, 3, 89.99),
(115, 42, 23, 2, 99.00),
(116, 43, 44, 3, 116.00),
(117, 43, 32, 1, 179.00),
(118, 43, 25, 2, 83.00),
(119, 44, 24, 3, 147.00),
(120, 44, 41, 2, 237.00),
(121, 45, 37, 2, 13.00),
(122, 45, 12, 1, 205.00),
(123, 46, 6, 2, 61.00),
(124, 46, 37, 2, 13.00),
(125, 46, 2, 1, 59.50),
(126, 47, 2, 1, 59.50),
(127, 48, 22, 1, 28.00),
(128, 48, 13, 2, 96.00),
(129, 48, 1, 2, 89.99),
(130, 48, 37, 1, 13.00),
(131, 49, 19, 1, 235.00),
(132, 49, 2, 3, 59.50),
(133, 49, 3, 3, 249.00),
(134, 50, 31, 2, 157.00),
(135, 50, 23, 1, 99.00),
(136, 50, 29, 2, 36.00),
(137, 50, 24, 2, 147.00),
(138, 51, 33, 1, 192.00),
(139, 52, 35, 1, 211.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `product_id` int(32) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `category_id` int(32) UNSIGNED NOT NULL,
  `description` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) NOT NULL,
  `shipping_price` decimal(10,2) NOT NULL,
  `available_stock` int(32) NOT NULL,
  `reserved_stock` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`product_id`, `name`, `category_id`, `description`, `created_at`, `price`, `shipping_price`, `available_stock`, `reserved_stock`) VALUES
(1, 'HyperKey RGB Full-size Mechanical Keyboard', 1, 'Mechanical, RGB, ideal for gaming.', '2025-12-19 01:51:09', 89.99, 8.99, 20, 0),
(2, 'FeatherMouse Wireless 16000 DPI', 2, 'Ultra-lightweight mouse with high-precision sensor.', '2025-12-19 01:51:09', 59.50, 4.99, 35, 0),
(3, 'ViewPro 27\" IPS 144Hz', 3, 'IPS 144Hz monitor, ideal for gaming.', '2025-12-19 01:51:09', 249.00, 15.99, 10, 0),
(4, 'SoundMax Wireless ANC Headset', 4, 'Wireless headphones with active noise cancellation.', '2025-12-19 01:51:09', 129.99, 9.99, 18, 0),
(5, 'Product 239', 2, 'Auto generated product description', '2025-12-19 02:08:25', 241.00, 10.00, 13, 0),
(6, 'Product 916', 3, 'Auto generated product description', '2025-12-19 02:08:25', 61.00, 13.00, 43, 0),
(7, 'Product 948', 2, 'Auto generated product description', '2025-12-19 02:08:25', 144.00, 17.00, 15, 0),
(8, 'Product 529', 4, 'Auto generated product description', '2025-12-19 02:08:25', 300.00, 13.00, 32, 0),
(9, 'Product 178', 3, 'Auto generated product description', '2025-12-19 02:08:25', 111.00, 6.00, 27, 0),
(10, 'Product 926', 3, 'Auto generated product description', '2025-12-19 02:08:25', 279.00, 6.00, 15, 0),
(11, 'Product 917', 1, 'Auto generated product description', '2025-12-19 02:08:25', 138.00, 17.00, 10, 0),
(12, 'Product 368', 1, 'Auto generated product description', '2025-12-19 02:08:25', 205.00, 20.00, 50, 0),
(13, 'Product 927', 3, 'Auto generated product description', '2025-12-19 02:08:25', 96.00, 4.00, 10, 0),
(14, 'Product 957', 4, 'Auto generated product description', '2025-12-19 02:08:25', 273.00, 4.00, 29, 0),
(15, 'Product 474', 3, 'Auto generated product description', '2025-12-19 02:08:25', 36.00, 10.00, 48, 0),
(16, 'Product 522', 3, 'Auto generated product description', '2025-12-19 02:08:25', 241.00, 11.00, 34, 0),
(17, 'Product 705', 1, 'Auto generated product description', '2025-12-19 02:08:25', 116.00, 14.00, 17, 0),
(18, 'Product 337', 1, 'Auto generated product description', '2025-12-19 02:08:25', 209.00, 3.00, 40, 0),
(19, 'Product 838', 2, 'Auto generated product description', '2025-12-19 02:08:25', 235.00, 16.00, 11, 0),
(20, 'Product 582', 2, 'Auto generated product description', '2025-12-19 02:08:25', 88.00, 10.00, 27, 0),
(21, 'Product 930', 4, 'Auto generated product description', '2025-12-19 02:08:25', 212.00, 20.00, 13, 0),
(22, 'Product 251', 3, 'Auto generated product description', '2025-12-19 02:08:25', 28.00, 9.00, 21, 0),
(23, 'Product 409', 3, 'Auto generated product description', '2025-12-19 02:08:25', 99.00, 20.00, 17, 0),
(24, 'Product 482', 1, 'Auto generated product description', '2025-12-19 02:08:25', 147.00, 19.00, 25, 0),
(25, 'Product 678', 1, 'Auto generated product description', '2025-12-19 02:08:25', 83.00, 11.00, 32, 0),
(26, 'Product 302', 4, 'Auto generated product description', '2025-12-19 02:08:25', 64.00, 6.00, 45, 0),
(27, 'Product 349', 1, 'Auto generated product description', '2025-12-19 02:08:25', 143.00, 17.00, 49, 0),
(28, 'Product 228', 3, 'Auto generated product description', '2025-12-19 02:08:25', 241.00, 6.00, 15, 0),
(29, 'Product 377', 4, 'Auto generated product description', '2025-12-19 02:08:26', 36.00, 14.00, 47, 0),
(30, 'Product 835', 3, 'Auto generated product description', '2025-12-19 02:08:26', 241.00, 16.00, 35, 0),
(31, 'Product 637', 2, 'Auto generated product description', '2025-12-19 02:08:26', 157.00, 17.00, 24, 0),
(32, 'Product 698', 1, 'Auto generated product description', '2025-12-19 02:08:26', 179.00, 7.00, 35, 0),
(33, 'Product 117', 2, 'Auto generated product description', '2025-12-19 02:08:26', 192.00, 8.00, 6, 0),
(34, 'Product 558', 4, 'Auto generated product description', '2025-12-19 02:08:26', 131.00, 14.00, 13, 0),
(35, 'Product 278', 1, 'Auto generated product description', '2025-12-19 02:08:26', 211.00, 11.00, 16, 0),
(36, 'Product 230', 3, 'Auto generated product description', '2025-12-19 02:08:26', 281.00, 4.00, 24, 0),
(37, 'Product 461', 1, 'Auto generated product description', '2025-12-19 02:08:26', 13.00, 7.00, 39, 0),
(38, 'Product 325', 2, 'Auto generated product description', '2025-12-19 02:08:26', 38.00, 4.00, 13, 0),
(39, 'Product 527', 1, 'Auto generated product description', '2025-12-19 02:08:26', 53.00, 10.00, 50, 0),
(40, 'Product 706', 4, 'Auto generated product description', '2025-12-19 02:08:26', 189.00, 8.00, 48, 0),
(41, 'Product 610', 3, 'Auto generated product description', '2025-12-19 02:08:26', 237.00, 16.00, 20, 0),
(42, 'Product 317', 2, 'Auto generated product description', '2025-12-19 02:08:26', 253.00, 4.00, 19, 0),
(43, 'Product 338', 4, 'Auto generated product description', '2025-12-19 02:08:26', 218.00, 12.00, 10, 0),
(44, 'Product 582', 2, 'Auto generated product description', '2025-12-19 02:08:26', 116.00, 11.00, 5, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_attributes`
--

CREATE TABLE `product_attributes` (
  `product_id` int(32) UNSIGNED NOT NULL,
  `attribute_id` int(32) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `product_attributes`
--

INSERT INTO `product_attributes` (`product_id`, `attribute_id`) VALUES
(1, 1),
(1, 4),
(1, 19),
(2, 6),
(2, 9),
(3, 13),
(3, 16),
(4, 6),
(4, 19),
(4, 20),
(5, 4),
(5, 15),
(6, 3),
(7, 8),
(7, 15),
(7, 16),
(8, 11),
(8, 20),
(9, 7),
(9, 10),
(9, 15),
(9, 17),
(10, 4),
(10, 10),
(11, 12),
(11, 13),
(11, 18),
(12, 6),
(12, 12),
(12, 13),
(12, 16),
(13, 5),
(13, 9),
(13, 18),
(13, 19),
(14, 6),
(14, 13),
(15, 7),
(15, 12),
(15, 16),
(15, 17),
(16, 10),
(17, 5),
(17, 20),
(18, 6),
(18, 15),
(19, 3),
(19, 4),
(19, 6),
(19, 19),
(20, 14),
(21, 3),
(21, 4),
(22, 16),
(23, 7),
(23, 9),
(23, 10),
(23, 14),
(24, 16),
(24, 18),
(25, 1),
(25, 2),
(25, 5),
(25, 14),
(26, 3),
(26, 7),
(26, 17),
(27, 10),
(27, 15),
(28, 9),
(29, 8),
(29, 19),
(30, 9),
(30, 17),
(30, 19),
(31, 6),
(32, 18),
(33, 1),
(33, 9),
(33, 18),
(34, 18),
(35, 2),
(35, 11),
(35, 16),
(35, 18),
(36, 15),
(37, 6),
(37, 10),
(37, 14),
(37, 19),
(38, 18),
(39, 3),
(39, 6),
(39, 9),
(39, 10),
(40, 15),
(41, 4),
(41, 11),
(41, 13),
(42, 7),
(43, 4),
(43, 12),
(43, 16),
(44, 1),
(44, 6),
(44, 11),
(44, 18);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategories`
--

CREATE TABLE `subcategories` (
  `attribute_id` int(32) UNSIGNED NOT NULL,
  `category_id` int(32) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategories`
--

INSERT INTO `subcategories` (`attribute_id`, `category_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 2),
(5, 4),
(6, 2),
(6, 4),
(7, 2),
(7, 4),
(8, 2),
(9, 2),
(10, 3),
(11, 3),
(12, 3),
(13, 3),
(14, 3),
(15, 3),
(16, 3),
(17, 4),
(18, 4),
(19, 1),
(20, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tokens`
--

CREATE TABLE `tokens` (
  `token_id` int(32) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `user_id` int(32) UNSIGNED NOT NULL,
  `type` enum('email_verify','password_reset','','') NOT NULL,
  `expire_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `user_id` int(32) UNSIGNED NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`user_id`, `email`, `password`, `name`, `is_verified`, `is_admin`, `created_at`) VALUES
(1, 'alu.140009@usj.es', '$2y$10$uwvhh1idPVVnyEV8YxPoROFbUB/jcKkVTNizsWuO9Zxqn6V6rcotK', 'Alvaro', 1, 0, '2025-12-19 02:59:59'),
(2, 'algarse04@gmail.com', '$2y$10$wNKME03KEu8kCOK7EsF13OlFUyT.0XdkU7ZIYyGnboLLuz2a2szPu', 'AlvaroAdmin', 1, 1, '2025-12-19 03:05:06'),
(3, 'user6212@mail.com', '$2y$10$IYH3sUE1CVCLISgQWQIw8un0DXqbuwRf1IABZqgS.01PAwmQ85tZe', 'Carlos', 1, 0, '2025-12-19 03:08:24'),
(4, 'user4651@mail.com', '$2y$10$bPf8E35LZ.hW2b4wFKoxG.KW7gr8ON1dNfVOePxShQfNOcAcx3xme', 'Ana', 1, 0, '2025-12-19 03:08:24'),
(5, 'user1426@mail.com', '$2y$10$YJM2jPNqH./p8Y0yMfKcte3x07f.eXTissYuCXziVCaafvx1YutsO', 'Elena', 1, 0, '2025-12-19 03:08:24'),
(6, 'user9712@mail.com', '$2y$10$DpGUHR0IAajWXqeokQahJO5akN1VKhwaIwoI4qGSK3aDe0J2VEWVi', 'Sara', 1, 0, '2025-12-19 03:08:24'),
(7, 'user4867@mail.com', '$2y$10$KMu.NaObaiToc1sz6JrQNOVksenzIK4b3lRIEc9Bp7THP0gtQ/uDy', 'Juan', 1, 0, '2025-12-19 03:08:24'),
(8, 'user3909@mail.com', '$2y$10$SWlv4RCYkDU4QTvP1zR8w.Hjccv/PSyhFSjVSRs2OOtkWUBuci0eK', 'Juan', 1, 0, '2025-12-19 03:08:24'),
(9, 'user2241@mail.com', '$2y$10$vFGW.tLXUBTahHdPRx4SoOV8t5LgGCOdIk8rDYfCVuEr6DcBMHYs.', 'Pablo', 1, 0, '2025-12-19 03:08:24'),
(10, 'user5435@mail.com', '$2y$10$tQoE/9AaOZmOVedfsFqFXOJJSCD0AO6XKdfzZQ7UvQ0LphDffFOs2', 'David', 1, 0, '2025-12-19 03:08:24'),
(11, 'user8323@mail.com', '$2y$10$g6oBuJGn0MfZuqIHo/x1L.y7FMY8oUqA0bNL/5TPuCweJpZVeVVtm', 'Carlos', 1, 0, '2025-12-19 03:08:24'),
(12, 'user4777@mail.com', '$2y$10$6EEPs7s0MTYaIDKJZfCE9OJqGIzNPbmpRPwlBGjGz/zS033LYNZp6', 'Pablo', 1, 0, '2025-12-19 03:08:24'),
(13, 'user5910@mail.com', '$2y$10$Wc7QRTcggJdOgO1QA3Dy5.YOZRTfLHsoF0ggy12QTKk9UyYF62CSO', 'Ana', 1, 0, '2025-12-19 03:08:24'),
(14, 'user1669@mail.com', '$2y$10$uDx.5sxMmdXRt7IJj45dXeI0BV5GTZosBuY9.gIuN99WJ8MoBo/Ui', 'Juan', 1, 0, '2025-12-19 03:08:24'),
(15, 'user7067@mail.com', '$2y$10$HEpUP0pgcAJbGPRN7FAt3.E/w2lJ2yZTauN4Zoktx5DC./glAdLT.', 'Carlos', 1, 0, '2025-12-19 03:08:24'),
(16, 'user8081@mail.com', '$2y$10$Fe5UQjLWPF3iXxQ4WnVouOG2.TebtyQZ9.Zvucpnw0Vp1p63ns7C6', 'Sara', 1, 0, '2025-12-19 03:08:25'),
(17, 'user1894@mail.com', '$2y$10$mCvcYt2MVN25a94JTAUEIOMSyF9vznjmLYPRqmk7p47S3tP/dwxbe', 'David', 1, 0, '2025-12-19 03:08:25'),
(18, 'user7730@mail.com', '$2y$10$J90x9UFiZ7MnA.f/lQi/4OoePfh5b8WzMYkLgQg1rNlyuDLrwwaUC', 'Sara', 1, 0, '2025-12-19 03:08:25'),
(19, 'user9539@mail.com', '$2y$10$Wkt/hsnzR.6YG72U13RTsOXy3qtzki1PzZ4NpfNBZp7gS7G3gYxXW', 'Mario', 1, 0, '2025-12-19 03:08:25'),
(20, 'user4919@mail.com', '$2y$10$CiH6aaG/DuwiGWzbba1w6uYqrwDZOYPYOxIWwmJhWm0xQ6ACqxkFK', 'Carlos', 1, 0, '2025-12-19 03:08:25'),
(21, 'user2379@mail.com', '$2y$10$obyLysVF2bicTUQ3.izZBe6yR1YWeJQvonvXtTArpV4sKp/Vu2uWG', 'Mario', 1, 0, '2025-12-19 03:08:25'),
(22, 'user7909@mail.com', '$2y$10$aVB9fooKT29wUJKAncd.F.wxMJk3EiFbSKKwuJ1omtf/WhDbhQixu', 'Carlos', 1, 0, '2025-12-19 03:08:25'),
(23, 'user1251@mail.com', '$2y$10$dcnn2oVB9N5YKjZv62Q3/uIH9w8VGQLjwEt.rl21791APhmeY.yma', 'Sara', 1, 0, '2025-12-19 03:08:25'),
(24, 'user6545@mail.com', '$2y$10$xCWkI82eDiDBBlJAtY1pxOgJePPVcDTUIQo09Jko7sQ1PS/i4Q5tC', 'Lucia', 1, 0, '2025-12-19 03:08:25'),
(25, 'user2632@mail.com', '$2y$10$6Hx02OxhCBDFpPhg8HsY0ua4Sv1sY5/tVWjjNQYKWCaKlspC7YV.6', 'Elena', 1, 0, '2025-12-19 03:08:25'),
(26, 'user4845@mail.com', '$2y$10$dzqbayBafcwOf5uHXXalD.U6c81LfNuzA6hwphcfBvty1gR3EDL2y', 'Elena', 1, 0, '2025-12-19 03:08:25'),
(27, 'user1580@mail.com', '$2y$10$5/zghXRlL0xSnLPjwhyjo.69ly/3EJDeyHaYHjFpLq.RqD/Cghx2O', 'Sara', 1, 0, '2025-12-19 03:08:25'),
(28, 'user6559@mail.com', '$2y$10$bK9j4.ULC/gitqdOnszIr.af9KsSeGti7k55wE4VjBYO6AxqZU7C6', 'Elena', 1, 0, '2025-12-19 03:08:25'),
(29, 'user1450@mail.com', '$2y$10$Auz.dzIG/L8uiEGyutbuCubTu8nyc6r1E0qbenWGXB5v2GOYBKFsq', 'David', 1, 0, '2025-12-19 03:08:25'),
(30, 'user2571@mail.com', '$2y$10$AZuefHILKJtqUWSOm9yxQesXZhOea5Bd7K4FI3LXu7f5xzguj1YFK', 'Laura', 1, 0, '2025-12-19 03:08:25'),
(31, 'user5078@mail.com', '$2y$10$NIQ66FJB0BXUU1ge6A6j5.mvJP6ET4pjlCi9ZuqZwAN1GZLwYbH9y', 'Juan', 1, 0, '2025-12-19 03:08:25'),
(32, 'user8202@mail.com', '$2y$10$z6tCvD/xyGanNIRR6XCfZup3W7wWmsHMkWOev1q98p8AcMDE1yDQ2', 'Lucia', 1, 0, '2025-12-19 03:08:25'),
(34, 'mse@system.local', 'NOLOGIN', 'MSE System', 1, 0, '2025-12-30 13:11:33'),
(35, 'mse@system.local', '$2y$10$abcdefghijklmnopqrstuv123456789012345678901234567890', 'MSE System', 1, 0, '2025-12-30 13:14:53');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`attribute_id`);

--
-- Indices de la tabla `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_items_ibfk_1` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `products_ibfk_1` (`category_id`);

--
-- Indices de la tabla `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`product_id`,`attribute_id`),
  ADD KEY `attribute_option_id` (`attribute_id`);

--
-- Indices de la tabla `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`attribute_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `attributes`
--
ALTER TABLE `attributes`
  MODIFY `attribute_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(16) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `tokens`
--
ALTER TABLE `tokens`
  MODIFY `token_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(32) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_attributes_ibfk_3` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subcategories_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tokens`
--
ALTER TABLE `tokens`
  ADD CONSTRAINT `tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
