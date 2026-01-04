-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-01-2026 a las 21:14:20
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

--
-- Volcado de datos para la tabla `mse_carts`
--

INSERT INTO `mse_carts` (`id`, `user_id`, `ia_name`, `created_at`, `ia_item_id`, `quantity`) VALUES
(25, 6, 'ttrpg', '2026-01-02', 1, 1),
(26, 6, 'ttrpg', '2026-01-02', 6, 1);

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
(1, 1, 'paid', '2025-12-30'),
(3, 1, 'paid', '2025-12-30'),
(13, 1, 'paid', '2025-12-30'),
(14, 1, 'paid', '2026-01-02'),
(15, 6, 'paid', '2026-01-02'),
(16, 6, 'paid', '2026-01-02'),
(19, 9, 'paid', '2026-01-04');

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
(1, 1, 'ttrpg', 2, 2, 75.00, '8'),
(2, 1, 'ttrpg', 1, 6, 40.00, '9'),
(3, 1, 'techShop', 40, 1, 189.00, 'N/A'),
(6, 3, 'ttrpg', 1, 1, 40.00, '12'),
(7, 3, 'ttrpg', 4, 1, 49.00, '13'),
(8, 3, 'techShop', 35, 1, 211.00, 'N/A'),
(9, 3, 'techShop', 41, 1, 237.00, 'N/A'),
(28, 13, 'ttrpg', 8, 1, 13.00, '32'),
(29, 13, 'ttrpg', 9, 1, 33.00, '33'),
(30, 13, 'techShop', 13, 1, 96.00, 'N/A'),
(31, 13, 'techShop', 27, 1, 143.00, 'N/A'),
(32, 14, 'techShop', 33, 1, 192.00, '51'),
(33, 14, 'techShop', 35, 1, 211.00, '52'),
(34, 14, 'ttrpg', 4, 1, 49.00, '34'),
(35, 15, 'ttrpg', 1, 2, 40.00, '35'),
(36, 15, 'ttrpg', 3, 1, 61.00, '36'),
(37, 16, 'ttrpg', 9, 1, 33.00, '37'),
(38, 16, 'techShop', 43, 1, 218.00, '53'),
(39, 16, 'techShop', 32, 1, 179.00, '54'),
(44, 19, 'ttrpg', 3, 1, 61.00, '40');

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
(1, '123@gmail.com', '$2y$10$b3h/u5xhZLZn7bMNMoYFy.wWIKq97xjN6IrnAMvaOk2/ADjfVSKaG', 1, '2025-12-29'),
(2, '1234@gmail.com', '$2y$10$3p.nkNNhDo0DsDaZDqBcGetiMxtHDAo4wOb.D.P3THmZ4YF5hW6fa', 1, '2025-12-29'),
(3, '12345@gmail.com', '$2y$10$BS/0oB1oppUtNOBjNvt2r.VHYX193QG8kUKVz0oVhcUWbsYeoUb6e', 1, '2025-12-29'),
(4, '1212@gmail.com', '$2y$10$ZaqBBhWNKnaIQMawM.PG3e.d080vqnPR1dvG2dgJIMSsAXRedliPa', 1, '2025-12-29'),
(6, '121212@gmail.com', '$2y$10$nwPmBiSdIBUBqXAOAcgaZuEK0jeCu2NOfG4MFAC.Y99P4NHq.ljAG', 1, '2026-01-02'),
(9, 'algarse04@gmail.com', '$2y$10$4SePVA4nyEVxRe7kdXvIUeXI3lqC0puXskndVGEkT0L.lbrh/8eLi', 1, '2026-01-02'),
(14, 'alu.140009@usj.es', '$2y$10$ufqKAvWsJHBSIBfdtxgvqeru/DwiSVmjwVgW5OmYov2GcUWnhnvJe', 1, '2026-01-04');

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
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `mse_orders`
--
ALTER TABLE `mse_orders`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `mse_order_items`
--
ALTER TABLE `mse_order_items`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `mse_tokens`
--
ALTER TABLE `mse_tokens`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `mse_users`
--
ALTER TABLE `mse_users`
  MODIFY `id` int(32) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
