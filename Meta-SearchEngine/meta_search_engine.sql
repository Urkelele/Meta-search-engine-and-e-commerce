-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-01-2026 a las 20:11:16
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
-- Base de datos: `meta_search_engine`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mse_carts`
--

CREATE TABLE `mse_carts` (
  `id` int(32) NOT NULL,
  `user_id` int(32) NOT NULL,
  `ia_name` varchar(32) NOT NULL,
  `created_at` date NOT NULL,
  `ia_item_id` int(32) NOT NULL,
  `quantity` int(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mse_orders`
--

CREATE TABLE `mse_orders` (
  `id` int(32) NOT NULL,
  `user_id` int(32) NOT NULL,
  `status` varchar(32) NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mse_orders`
--

INSERT INTO `mse_orders` (`id`, `user_id`, `status`, `created_at`) VALUES
(22, 15, 'paid', '2026-01-05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mse_order_items`
--

CREATE TABLE `mse_order_items` (
  `id` int(32) NOT NULL,
  `order_id` int(32) NOT NULL,
  `ia_name` varchar(32) NOT NULL,
  `ia_item_id` int(32) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `ia_order_ref` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mse_order_items`
--

INSERT INTO `mse_order_items` (`id`, `order_id`, `ia_name`, `ia_item_id`, `quantity`, `price_at_purchase`, `ia_order_ref`) VALUES
(53, 22, 'ttrpg', 6, 1, 19.00, '46'),
(54, 22, 'ttrpg', 7, 1, 35.00, '47'),
(55, 22, 'techShop', 1, 1, 89.99, '60'),
(56, 22, 'techShop', 4, 1, 129.99, '61');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mse_tokens`
--

CREATE TABLE `mse_tokens` (
  `id` int(32) NOT NULL,
  `user_id` int(32) NOT NULL,
  `token` varchar(255) NOT NULL,
  `type` varchar(32) NOT NULL,
  `expire_time` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mse_users`
--

CREATE TABLE `mse_users` (
  `id` int(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mse_users`
--

INSERT INTO `mse_users` (`id`, `email`, `password_hash`, `is_verified`, `created_at`) VALUES
(15, 'algarse04@gmail.com', '$2y$10$MnGU6sUmYV/rMb0UGTilwOpG7L9QRFdVkthAUKJMsz.UhH5FF9R5y', 1, '2026-01-05');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `mse_carts`
--
ALTER TABLE `mse_carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_id` (`user_id`);

--
-- Indices de la tabla `mse_orders`
--
ALTER TABLE `mse_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `mse_order_items`
--
ALTER TABLE `mse_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indices de la tabla `mse_tokens`
--
ALTER TABLE `mse_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token` (`token`),
  ADD KEY `idx_user_type` (`user_id`,`type`);

--
-- Indices de la tabla `mse_users`
--
ALTER TABLE `mse_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `mse_carts`
--
ALTER TABLE `mse_carts`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `mse_orders`
--
ALTER TABLE `mse_orders`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `mse_order_items`
--
ALTER TABLE `mse_order_items`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `mse_tokens`
--
ALTER TABLE `mse_tokens`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `mse_users`
--
ALTER TABLE `mse_users`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `mse_carts`
--
ALTER TABLE `mse_carts`
  ADD CONSTRAINT `users_id` FOREIGN KEY (`user_id`) REFERENCES `mse_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mse_orders`
--
ALTER TABLE `mse_orders`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_id`) REFERENCES `mse_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mse_order_items`
--
ALTER TABLE `mse_order_items`
  ADD CONSTRAINT `order_id` FOREIGN KEY (`order_id`) REFERENCES `mse_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mse_tokens`
--
ALTER TABLE `mse_tokens`
  ADD CONSTRAINT `fk_mse_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `mse_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
