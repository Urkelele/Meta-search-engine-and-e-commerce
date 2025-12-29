-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-12-2025 a las 17:56:13
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
-- Base de datos: `ttrpg_shop`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `book_properties`
--

CREATE TABLE `book_properties` (
  `item_id` int(11) NOT NULL,
  `system` varchar(100) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `format` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `book_properties`
--

INSERT INTO `book_properties` (`item_id`, `system`, `type`, `format`) VALUES
(1, 'D&D 5e', 'Campaign', 'Softcover'),
(2, 'Pathfinder 2e', 'Campaign', 'Softcover'),
(3, 'Warhammer', 'Supplement', 'Softcover'),
(4, 'Pathfinder 2e', 'Core Book', 'Hardcover'),
(5, 'Pathfinder 2e', 'Adventure', 'PDF'),
(6, 'Warhammer', 'Supplement', 'Softcover');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Books'),
(2, 'Dice Sets'),
(3, 'Miniatures');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dice_properties`
--

CREATE TABLE `dice_properties` (
  `item_id` int(11) NOT NULL,
  `material` varchar(100) DEFAULT NULL,
  `dice_count` int(11) DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `dice_properties`
--

INSERT INTO `dice_properties` (`item_id`, `material`, `dice_count`, `theme`) VALUES
(7, 'Acrylic', 10, 'Galaxy'),
(8, 'Metal', 10, 'Galaxy'),
(9, 'Stone', 10, 'Fire'),
(10, 'Acrylic', 12, 'Necrotic'),
(11, 'Acrylic', 12, 'Frost'),
(12, 'Resin', 12, 'Bloodstone'),
(18, NULL, NULL, NULL),
(19, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `category_id`, `name`, `price`, `stock`, `weight`, `shipping_cost`, `description`, `image_path`) VALUES
(1, 1, 'Books Realm 1', 40.00, 12, 4.00, 6.00, 'Auto-generated description for Books Realm 1.', ''),
(2, 1, 'Books Forgotten 2', 75.00, 2, 2.00, 3.00, 'Auto-generated description for Books Forgotten 2.', ''),
(3, 1, 'Books Arcane 3', 61.00, 15, 5.00, 8.00, 'Auto-generated description for Books Arcane 3.', ''),
(4, 1, 'Books Forgotten 4', 49.00, 14, 1.00, 9.00, 'Auto-generated description for Books Forgotten 4.', ''),
(5, 1, 'Books Arcane 5', 8.00, 12, 3.00, 8.00, 'Auto-generated description for Books Arcane 5.', ''),
(6, 1, 'Books Shadow 6', 19.00, 6, 2.00, 4.00, 'Auto-generated description for Books Shadow 6.', ''),
(7, 2, 'Dice Sets Eternal 1', 35.00, 9, 4.00, 8.00, 'Auto-generated description for Dice Sets Eternal 1.', ''),
(8, 2, 'Dice Sets Shadow 2', 13.00, 13, 1.00, 8.00, 'Auto-generated description for Dice Sets Shadow 2.', ''),
(9, 2, 'Dice Sets Forgotten 3', 33.00, 18, 2.00, 7.00, 'Auto-generated description for Dice Sets Forgotten 3.', ''),
(10, 2, 'Dice Sets Ancient 4', 33.00, 3, 2.00, 3.00, 'Auto-generated description for Dice Sets Ancient 4.', ''),
(11, 2, 'Dice Sets Eternal 5', 75.00, 19, 2.00, 7.00, 'Auto-generated description for Dice Sets Eternal 5.', ''),
(12, 2, 'Dice Sets Dragon 6', 24.00, 2, 4.00, 8.00, 'Auto-generated description for Dice Sets Dragon 6.', ''),
(13, 3, 'Miniatures Crystal 1', 37.00, 1, 5.00, 9.00, 'Auto-generated description for Miniatures Crystal 1.', ''),
(14, 3, 'Miniatures Realm 2', 25.00, 3, 5.00, 9.00, 'Auto-generated description for Miniatures Realm 2.', ''),
(15, 3, 'Miniatures Dragon 3', 49.00, 18, 3.00, 7.00, 'Auto-generated description for Miniatures Dragon 3.', ''),
(16, 3, 'Miniatures Arcane 4', 47.00, 15, 2.00, 5.00, 'Auto-generated description for Miniatures Arcane 4.', ''),
(17, 3, 'Miniatures Eternal 5', 22.00, 13, 4.00, 3.00, 'Auto-generated description for Miniatures Eternal 5.', ''),
(18, 2, 'Miniatures Dragon 6', 22.00, 10, 2.00, 4.00, 'Auto-generated description for Miniatures Dragon 6.', ''),
(19, 3, 'Skibidy', 35.00, 6, 67.00, 10000.00, 'BABABOI', '1765733287_a423121e562904f05882849cf6c79f1f.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mini_properties`
--

CREATE TABLE `mini_properties` (
  `item_id` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `creature_type` varchar(100) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mini_properties`
--

INSERT INTO `mini_properties` (`item_id`, `size`, `creature_type`, `material`) VALUES
(13, 'Large', 'Humanoid', 'Resin'),
(14, 'Small', 'Elemental', 'Plastic'),
(15, 'Huge', 'Humanoid', 'Resin'),
(16, 'Large', 'Elemental', 'Plastic'),
(17, 'Small', 'Undead', 'Plastic'),
(18, 'Huge', 'Beast', 'Metal'),
(19, 'Huge', 'Beast', 'Metal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `status` enum('pending','paid','shipped','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `date_created`, `status`) VALUES
(1, 3, '2025-12-10 17:34:41', 'shipped'),
(2, 8, '2025-12-10 17:34:41', 'paid'),
(3, 5, '2025-12-10 17:34:41', 'pending'),
(4, 11, '2025-12-10 17:34:41', 'paid'),
(5, 5, '2025-12-10 17:34:41', 'shipped'),
(6, 12, '2025-12-10 17:55:17', 'paid'),
(7, 12, '2025-12-14 18:06:59', 'paid');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_id`, `quantity`, `purchase_price`) VALUES
(1, 1, 17, 3, 22.00),
(2, 1, 5, 2, 8.00),
(3, 1, 15, 3, 49.00),
(4, 2, 15, 1, 49.00),
(5, 3, 12, 2, 24.00),
(6, 3, 10, 3, 33.00),
(7, 4, 13, 1, 37.00),
(8, 5, 8, 3, 13.00),
(9, 6, 5, 1, 8.00),
(10, 7, 3, 1, 61.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `confirmed` tinyint(1) DEFAULT 0,
  `confirmation_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `is_admin`, `confirmed`, `confirmation_token`, `reset_token`) VALUES
(1, 'admin@example.com', '$2y$10$183WCzdHy4wwqkfTgOJaI.sdY0GtBVLHzRn/FNjLd8Uf66B2OsVoK', 1, 1, NULL, NULL),
(2, 'user1@example.com', '$2y$10$Dl5G1WMZHJ8ek32f/0FWYe/HEUPD.igvL5jh5vKblgxzlAQVNMx2u', 0, 1, NULL, NULL),
(3, 'user2@example.com', '$2y$10$oJ6OxuPFNXkTlI/bvSrpB.VBmEtOkupgo75e8yks4Q8r3LLDE.kqW', 0, 1, NULL, NULL),
(4, 'user3@example.com', '$2y$10$yl1hlYAGIcX8FagI9ikWXOjF2lgs0dEOcoIi/KtDkF10Qiq/S3aPW', 0, 1, NULL, NULL),
(5, 'user4@example.com', '$2y$10$v1/m6.n8z2X1JOf5qt1pv.B.C6w70GkhoFo2eiQIizIvKqM70NJEa', 0, 1, NULL, NULL),
(6, 'user5@example.com', '$2y$10$cpmqFMGY0O21JOc6IX3vr.XFG5TI3efLn3vfoNuYro4ohZkuCesjC', 0, 1, NULL, NULL),
(7, 'user6@example.com', '$2y$10$8x5xNo2sSRLPrA3L4RRN4OadteAnFjghbeVWeTRyEj4BKgIVNpDbS', 0, 1, NULL, NULL),
(8, 'user7@example.com', '$2y$10$J8MHZAgfRKVqX9JxsjSHgeNgPr4ejkUewFkOPhZD2480SykiRpryW', 0, 1, NULL, NULL),
(9, 'user8@example.com', '$2y$10$oQ1UvAdeqT/DDgzxJGyrouyRRZbcTgSV211g0k9F8PCywbFqvnZIe', 0, 1, NULL, NULL),
(10, 'user9@example.com', '$2y$10$NV8pqYb5gRH/m0sas0e2iurKbkgE7SazFyhNoZOV9OBhnnwssTKmC', 0, 1, NULL, NULL),
(11, 'user10@example.com', '$2y$10$M0p6yXS8MPUTYWO4R9UItuNNCIK80ziqd55w3dcQfM1Fnc6DNDqqG', 0, 1, NULL, NULL),
(12, 'urkoaguillo@gmail.com', '$2y$10$Co.VAAK5oV9NRMSV/gax1eZNmA4MApRzbvqEU2HHcjYP0XGIqJpnS', 0, 1, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `book_properties`
--
ALTER TABLE `book_properties`
  ADD PRIMARY KEY (`item_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `dice_properties`
--
ALTER TABLE `dice_properties`
  ADD PRIMARY KEY (`item_id`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indices de la tabla `mini_properties`
--
ALTER TABLE `mini_properties`
  ADD PRIMARY KEY (`item_id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `book_properties`
--
ALTER TABLE `book_properties`
  ADD CONSTRAINT `book_properties_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `dice_properties`
--
ALTER TABLE `dice_properties`
  ADD CONSTRAINT `dice_properties_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Filtros para la tabla `mini_properties`
--
ALTER TABLE `mini_properties`
  ADD CONSTRAINT `mini_properties_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
