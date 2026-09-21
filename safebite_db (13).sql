-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 09:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `safebite_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `allergies`
--

CREATE TABLE `allergies` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `allergies`
--

INSERT INTO `allergies` (`id`, `name`) VALUES
(2, 'Dairy / Milk'),
(6, 'Eggs'),
(7, 'Fish / Seafood'),
(3, 'Gluten / Wheat'),
(11, 'Mustard & Celery'),
(1, 'Peanuts'),
(8, 'Sesame'),
(9, 'Shellfish'),
(5, 'Soy'),
(4, 'Tree Nuts');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percent`, `expiry_date`) VALUES
(1, 'SAFE10', 10.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','delivering','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `delivery_region` enum('north','haifa','center','jerusalem','south') NOT NULL DEFAULT 'north',
  `delivery_user_id` int(11) DEFAULT NULL,
  `delivery_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `delivery_earning` decimal(10,2) NOT NULL DEFAULT 0.00,
  `picked_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`, `total_amount`, `delivery_address`, `delivery_region`, `delivery_user_id`, `delivery_rate`, `delivery_earning`, `picked_at`, `delivered_at`, `cancelled_at`, `delivery_date`, `delivery_time`) VALUES
(1, 2, 6.50, 'delivered', '2026-03-16 00:18:34', 0.00, NULL, 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL),
(2, 2, 4.99, 'delivered', '2026-03-16 00:18:49', 0.00, NULL, 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL),
(3, 2, 3.99, 'delivered', '2026-05-03 17:52:12', 0.00, NULL, 'north', 13, 25.00, 1.00, '2026-08-17 17:05:18', '2026-08-17 17:05:22', NULL, NULL, NULL),
(4, 3, 9.48, 'delivered', '2026-05-03 18:36:26', 0.00, NULL, 'north', 13, 25.00, 2.37, '2026-08-17 16:43:14', '2026-08-17 17:05:25', NULL, NULL, NULL),
(5, 6, 4.99, 'delivered', '2026-05-18 05:59:04', 0.00, NULL, 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL),
(6, 2, NULL, 'pending', '2026-07-26 19:03:20', 4.99, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-30', '23:00:00'),
(7, 2, NULL, 'pending', '2026-07-26 19:03:50', 4.99, 'outman abn affan', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '23:03:00'),
(8, 2, NULL, 'pending', '2026-07-26 19:04:35', 4.99, 'outman abn affan', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '23:03:00'),
(9, 2, NULL, 'pending', '2026-07-26 19:05:23', 4.99, 'outman abn affan', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '23:03:00'),
(10, 2, NULL, 'pending', '2026-07-26 19:06:04', 5.99, 'outman abn affan', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-30', '22:07:00'),
(11, 2, NULL, 'pending', '2026-07-26 19:08:30', 4.99, '3081100', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-11-12', '22:08:00'),
(12, 2, NULL, 'pending', '2026-07-26 19:19:37', 4.99, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-29', '22:19:00'),
(13, 2, 4.99, 'pending', '2026-07-26 19:31:41', 0.00, 'outman abn affan', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-27', '22:29:00'),
(14, 2, 5.99, 'pending', '2026-07-26 19:32:38', 0.00, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '22:32:00'),
(15, 2, 4.49, 'pending', '2026-07-26 19:41:17', 0.00, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-27', '22:41:00'),
(16, 2, 4.99, 'pending', '2026-07-26 19:49:30', 0.00, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-27', '22:49:00'),
(17, 2, 5.99, 'pending', '2026-07-26 21:11:08', 0.00, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '00:11:00'),
(18, 2, 4.99, 'pending', '2026-07-26 21:16:58', 0.00, 'ראשי', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-28', '00:16:00'),
(19, 2, 11.98, 'pending', '2026-07-26 21:22:09', 0.00, 'tamra', 'north', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-07-31', '00:22:00'),
(20, 7, 4.99, 'delivered', '2026-07-26 21:45:06', 0.00, 'ראשי', 'north', 10, 25.00, 1.25, '2026-07-31 14:31:34', '2026-07-31 14:31:49', NULL, '2026-02-01', '14:33:00'),
(21, 2, 499.00, 'delivering', '2026-07-26 21:55:01', 0.00, '12', 'north', 13, 25.00, 0.00, '2026-08-18 12:02:00', NULL, NULL, '2026-07-22', '00:59:00'),
(22, 12, 249.49, 'delivered', '2026-07-31 14:47:01', 0.00, 'Haifa, Skate Park', 'haifa', 13, 25.00, 62.37, '2026-08-17 16:37:48', '2026-08-17 16:37:54', NULL, '2026-07-31', '10:00:00'),
(23, 11, 139.23, 'cancelled', '2026-08-01 14:48:28', 0.00, 'Haifa Port 53', 'haifa', NULL, 0.00, 0.00, NULL, NULL, '2026-08-01 17:49:08', '2026-08-01', '10:00:00'),
(24, 11, 19.46, 'cancelled', '2026-08-01 15:02:10', 0.00, 'Haifa port 53', 'haifa', NULL, 0.00, 0.00, NULL, NULL, '2026-08-01 18:04:56', '2026-08-01', '23:01:00'),
(25, 11, 9.48, 'cancelled', '2026-08-01 15:09:30', 0.00, 'marj ebn amer house no.5', 'haifa', NULL, 0.00, 0.00, NULL, NULL, '2026-08-01 18:12:23', '2026-08-01', '21:09:00'),
(26, 14, 112.31, 'pending', '2026-08-18 08:16:23', 0.00, 'Haifa, Down City 51', 'haifa', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-08-25', '09:15:00'),
(27, 11, 59.90, 'cancelled', '2026-08-20 06:21:22', 0.00, 'Jaffa Port', 'center', NULL, 0.00, 0.00, NULL, NULL, '2026-08-22 15:51:30', '2026-08-21', '12:00:00'),
(28, 11, 91.66, 'cancelled', '2026-08-21 13:28:13', 0.00, 'University of Haifa', 'haifa', NULL, 0.00, 0.00, NULL, NULL, '2026-08-21 17:27:19', '2026-08-26', '21:31:00'),
(29, 11, 94.31, 'cancelled', '2026-08-21 14:29:38', 0.00, 'University of Haifa', 'haifa', NULL, 0.00, 0.00, NULL, NULL, '2026-08-22 15:51:06', '2026-08-25', '10:00:00'),
(30, 2, 56.33, 'pending', '2026-08-25 06:05:14', 0.00, 'University', 'haifa', NULL, 0.00, 0.00, NULL, NULL, NULL, '2026-08-27', '21:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 4, 1, 6.50),
(2, 2, 1, 1, 4.99),
(5, 4, 2, 1, 5.49),
(6, 5, 1, 1, 4.99),
(8, 14, 110, 1, 5.99),
(9, 15, 109, 1, 4.49),
(11, 17, 110, 1, 5.99),
(12, 18, 108, 1, 4.99),
(13, 19, 106, 1, 5.99),
(14, 19, 47, 1, 5.99),
(17, 22, 86, 10, 2.49),
(18, 22, 105, 40, 5.49),
(19, 22, 108, 1, 4.99),
(20, 23, 109, 10, 4.49),
(21, 23, 108, 10, 4.99),
(22, 23, 110, 10, 5.99),
(23, 24, 109, 1, 4.49),
(25, 25, 109, 1, 4.49),
(27, 26, 110, 10, 5.99),
(28, 26, 108, 1, 4.99),
(29, 26, 106, 10, 5.99),
(30, 27, 110, 5, 5.99),
(31, 27, 106, 5, 5.99),
(32, 28, 109, 1, 4.49),
(33, 28, 107, 15, 6.49),
(34, 29, 110, 5, 5.99),
(35, 29, 109, 10, 4.49),
(36, 29, 108, 6, 4.99),
(37, 30, 113, 1, 5.99),
(38, 30, 105, 1, 5.49),
(39, 30, 103, 15, 2.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(255) NOT NULL,
  `dietary_tags` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `price`, `description`, `image`, `created_at`, `category`, `dietary_tags`, `type`, `stock`) VALUES
(1, 'Fresh Whole Milk', 4.99, '1 Gallon of fresh whole milk. Rich in calcium and vitamin D.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTsqUwD0kOEK8BZ3I4xDWr53LYT23W2A5S8rjBRHauBQQ&s=10', '2026-03-15 23:57:23', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 100),
(2, 'Creamy Peanut Butter', 5.49, 'Smooth and creamy peanut butter. Perfect for sandwiches and baking.', 'https://www.jif.com/jif/products/simply-creamy-unsweetened/100783/image-thumb__100783__responsive_1534_JPEG/simply-creamy-unsweetened_00051500937976_C1R1.4876edf9.jpg', '2026-03-15 23:57:23', 'Peanuts', 'Vegan, Vegetarian, Halal, Kosher', 'snacks', 100),
(4, 'Organic Gala Apples (1kg)', 6.50, 'Crisp, sweet, and naturally delicious organic apples.', 'https://dtgxwmigmg3gc.cloudfront.net/imagery/assets/derivations/icon/512/512/true/eyJpZCI6IjMyNTFjOWY3N2RhN2FkYzQ0YjI3NGE4ODA1MzRkMGM2LmpwZyIsInN0b3JhZ2UiOiJwdWJsaWNfc3RvcmUifQ?signature=4db81a92909809f5ebb873fd189b4f92741f70d4d9c904680d604f6374a772f3', '2026-03-15 23:57:23', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(8, 'Dark Chocolate Bar with Almonds', 2.99, 'Rich 70% dark chocolate packed with roasted tree nuts.', 'https://www.foodandwine.com/thmb/CSymEoT8VULnc6G04fWtErI1YGI=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/dark-chocolate-bark-roasted-almonds-and-seeds-hero-01-FT-RECIPE1222-53401fe9698f439bbbe17cefac9bef95.jpg', '2026-03-15 23:57:23', 'Tree Nuts', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(9, 'Fresh Chicken Breast (1kg)', 9.99, 'Boneless, skinless chicken breasts. High in protein.', 'https://tendergourmetbutchery.com.au/wp-content/uploads/2024/07/raw-chicken-breast-on-board-600x360.jpg', '2026-03-15 23:57:23', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(10, 'Mayonnaise', 4.89, 'Creamy spread made with real eggs and oil.', 'https://media-cldnry.s-nbcnews.com/image/upload/t_fit-760w,f_auto,q_auto:best/rockcms/2022-07/best-mayonnaise-WHOLE-FOODS-365-ORGANIC-mc-220708-cf6abf.jpg', '2026-03-15 23:57:23', 'Eggs, Mustard & Celery', 'Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 50),
(11, 'whole wheat bread', 2.99, '', 'https://schnellers.co.il/wp-content/uploads/2025/03/7290006322104.jpg', '2026-03-15 23:57:35', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher', 'wheat', 50),
(13, 'Organic Bananas', 1.99, 'Fresh organic bananas, high in potassium.', 'https://spoonacular.com/cdn/ingredients_500x500/bananas.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(14, 'Gala Apples (1kg)', 3.49, 'Crisp, sweet, and naturally delicious organic apples.', 'https://spoonacular.com/cdn/ingredients_500x500/apple.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(15, 'Fresh Strawberries', 4.99, 'Sweet and juicy strawberries, perfect for snacking.', 'https://spoonacular.com/cdn/ingredients_500x500/strawberries.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(16, 'Seedless Grapes', 5.99, 'Crisp green seedless grapes.', 'https://images.unsplash.com/photo-1596363505729-4190a9506133?w=400', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(17, 'Organic Blueberries', 6.49, 'High in antioxidants and naturally sweet.', 'https://spoonacular.com/cdn/ingredients_500x500/blueberries.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(18, 'Navel Oranges', 4.29, 'Juicy navel oranges packed with Vitamin C.', 'https://spoonacular.com/cdn/ingredients_500x500/orange.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(19, 'Fresh Lemons', 2.99, 'Bright, tart lemons for cooking and drinks.', 'https://spoonacular.com/cdn/ingredients_500x500/lemon.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(20, 'Whole Watermelon', 7.99, 'Refreshing and sweet seedless watermelon.', 'https://spoonacular.com/cdn/ingredients_500x500/watermelon.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(21, 'Broccoli Crowns', 2.49, 'Fresh broccoli crowns, great for steaming.', 'https://spoonacular.com/cdn/ingredients_500x500/broccoli.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(22, 'Baby Carrots', 1.99, 'Peeled and ready-to-eat baby carrots.', 'https://spoonacular.com/cdn/ingredients_500x500/baby-carrots.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(23, 'Fresh Spinach', 3.29, 'Pre-washed baby spinach leaves.', 'https://spoonacular.com/cdn/ingredients_500x500/spinach.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(24, 'Romaine Lettuce', 1.89, 'Crisp romaine lettuce for fresh salads.', 'https://spoonacular.com/cdn/ingredients_500x500/romaine.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(25, 'Roma Tomatoes', 2.99, 'Firm and flavorful roma tomatoes.', 'https://spoonacular.com/cdn/ingredients_500x500/tomato.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(26, 'English Cucumbers', 1.49, 'Seedless cucumbers, perfect for slicing.', 'https://spoonacular.com/cdn/ingredients_500x500/cucumber.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(27, 'Red Bell Peppers', 2.19, 'Sweet and crunchy red bell peppers.', 'https://spoonacular.com/cdn/ingredients_500x500/red-bell-pepper.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(28, 'Yellow Onions (2kg)', 3.99, 'Versatile yellow onions for cooking.', 'https://www.themealdb.com/images/ingredients/Onion.png', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(29, 'Garlic Bulbs', 1.29, 'Fresh garlic bulbs to add flavor to any dish.', 'https://spoonacular.com/cdn/ingredients_500x500/garlic.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(30, 'Russet Potatoes', 4.49, 'Classic baking and mashing potatoes.', 'https://www.themealdb.com/images/ingredients/Potatoes.png', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(31, 'Sweet Potatoes', 3.99, 'Nutrient-dense sweet potatoes.', 'https://spoonacular.com/cdn/ingredients_500x500/sweet-potato.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(32, 'Hass Avocados', 2.50, 'Creamy and ripe avocados.', 'https://spoonacular.com/cdn/ingredients_500x500/avocado.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'fruit&vege', 50),
(33, 'Fresh Chicken Breast', 9.99, 'Boneless, skinless chicken breasts. High in protein.', 'https://spoonacular.com/cdn/ingredients_500x500/chicken-breasts.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(34, 'Chicken Thighs', 8.49, 'Juicy bone-in chicken thighs.', 'https://spoonacular.com/cdn/ingredients_500x500/chicken-thighs.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(35, 'Lean Ground Beef', 7.99, '90% lean ground beef, perfect for burgers and tacos.', 'https://www.themealdb.com/images/ingredients/Minced%20Beef.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(36, 'Ribeye Steak', 14.99, 'Premium cut ribeye steak for grilling.', 'https://www.themealdb.com/images/ingredients/Beef.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(37, 'Lamb Chops', 18.99, 'Tender and flavorful fresh lamb chops.', 'https://spoonacular.com/cdn/ingredients_500x500/lamb-chops.jpg', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(38, 'Turkey Breast', 10.99, 'Lean whole turkey breast.', 'https://spoonacular.com/cdn/ingredients_500x500/turkey-breast.jpg', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(39, 'Ground Turkey', 6.99, 'Healthy alternative for ground meat recipes.', 'https://banner2.cleanpng.com/20180620/q/kisspng-ground-turkey-cargill-meatloaf-fresh-meat-5b29ec8b199c60.2024454515294741871049.jpg', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(40, 'Fresh Salmon Fillet', 12.99, 'Rich in Omega-3 fatty acids.', 'https://coldstorage.com.sg/_next/image?url=https%3A%2F%2Fmcos.coldstorage.com.sg%2Fuploads%2Fproduct%2F272459%2F005162652.jpg%3Fv%3D1775805995&w=3840&q=75', '2026-07-26 16:31:30', 'Fish / Seafood', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(41, 'Canned Light Tuna', 1.99, 'High-quality chunk light tuna in water.', 'https://spoonacular.com/cdn/ingredients_500x500/canned-tuna.png', '2026-07-26 16:31:30', 'Fish / Seafood', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(42, 'Tilapia Fillets', 8.99, 'Mild and flaky white fish fillets.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRvkdET8glTlyu_ZsrNeEMyJci9y3iR95flWFL7TTDJbQ&s=10', '2026-07-26 16:31:30', 'Fish / Seafood', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(43, 'Cod Fillets', 11.99, 'Wild-caught cod, great for baking or frying.', 'https://ecom-su-static-prod.wtrecom.com/images/products/4/LN_615396_BP_4.jpg', '2026-07-26 16:31:30', 'Fish / Seafood', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(44, 'Chicken Wings', 7.49, 'Party-ready chicken wings.', 'https://spoonacular.com/cdn/ingredients_500x500/chicken-wings.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(45, 'Beef Roast', 16.99, 'Slow-cooker ready beef chuck roast.', 'https://spoonacular.com/cdn/ingredients_500x500/beef-roast.jpg', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(46, 'Lamb Shank', 15.99, 'Perfect for slow braising.', 'https://www.themealdb.com/images/ingredients/Lamb.png', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(47, 'Beef Hot Dogs', 5.99, 'Classic 100% beef franks.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSEFKQBd06Pj1G0PHl9DXVDARdTX59ZGs0NHJbvOC4UoA&s', '2026-07-26 16:31:30', '', 'Halal, Kosher, Sugar-Free', 'meat', 50),
(49, 'Skim Milk', 4.49, 'Fat-free milk alternative.', 'https://trinityvalleydairy.com/wp-content/uploads/2025/04/Skim-Milk-Gallon.jpg', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(50, 'Sharp Cheddar Cheese', 5.49, 'Aged sharp cheddar cheese block.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRH5WdHesgohFwt2L2NkumJZhs4VDP-Fz-wgSGXOTkJbg&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(51, 'Mozzarella Cheese', 4.99, 'Perfect for pizzas and pasta.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSK0veApyAeknZpgwzZ8niA5g9uVpVo8PwCUsdVGTo0sA&s', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(52, 'Swiss Cheese Slices', 5.99, 'Deli-sliced Swiss cheese.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcToDScbg5sTxI6Svl2stSTJzvPsCzUJteDXAHxHItYggw&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(53, 'Feta Cheese', 6.49, 'Crumbly traditional Greek feta.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRA1w-gEluBGik0-d_qUmAHbEJWQNsyxKGkan9coBr7Nw&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(54, 'Brie Cheese', 7.99, 'Soft and creamy French brie.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSVACsPzMNOJQvSOey3UnTC3QdOhKHaNcre9fXKnLADhw&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(55, 'Plain Greek Yogurt', 3.99, 'High-protein plain Greek yogurt.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4D2xGtz3HwM_13WJHjzMBL0OQB6Unr9Ozh66wOG0zKg&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(56, 'Strawberry Yogurt', 1.49, 'Sweetened strawberry blend yogurt.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0YjlQkm7lqsj1tngiLWzha9LJE86FuUSmi5jGCFj-pw&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher', 'dairy', 50),
(57, 'Salted Butter', 3.49, 'Creamy salted butter sticks.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTjg6MMEDPhqWJOwyNy3FEVFUw8VfGL0An7LH-M7gxE_g&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(58, 'Cream Cheese', 2.99, 'Original spreadable cream cheese.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQunFsdYgbUZgs7HkMr2a4PfCvJWTc5ykbjA-uyg5S-_A&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(59, 'Premium Almond Milk', 4.29, 'Unsweetened almond milk, a dairy-free alternative.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQSbenbDiy86xFhliE7iIPxdb_ZJqv3t8CSvfW4izhEgA&s=10', '2026-07-26 16:31:30', 'Tree Nuts', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(60, 'Oat Milk', 4.49, 'Creamy oat-based milk alternative.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBwVPGsobY5oOhylcptzTbW206H7vOgf8N94D9U_FnhA&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(61, 'Soy Milk', 3.99, 'Classic protein-rich soy milk.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ3ruBC4dUVWa0FGUy0T9c7T05ZMhL2uy38as0r2wfTFQ&s', '2026-07-26 16:31:30', 'Soy', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(62, 'Parmesan Cheese', 6.99, 'Grated parmesan cheese.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRYNihKns6w67RAU4BYKze6jNqA0q86UyjUlMFXtgvZsg&s', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'dairy', 50),
(64, 'Classic Potato Chips', 2.99, 'Crispy, salted potato chips.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQu-ylCCo4sLeXRyqjxNCYWNwPlyfYKE57GdRW1JdUIQA&s', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher', 'snacks', 50),
(65, 'Tortilla Chips', 3.49, 'Corn tortilla chips, perfect for salsa.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1Kusu4bxmJdlYZYvCYKu6ZJrF5TNmyLA_znCvnd_0aWeGPRMS2wl9d6A&s=10', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(66, 'Salted Pretzels', 2.49, 'Oven-baked crunchy pretzels.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSo_hF-5ygRrBPF1y2VWFMwDDMl37VrAs0vYkj_WWl8BQ&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher', 'snacks', 50),
(67, 'Microwave Popcorn', 3.99, 'Butter flavored microwave popcorn.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSpMlovoth5XD7Rm188l7rpy6bSocC2khJUZuOdbWNZZQ&s', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(68, 'Almond Butter', 8.99, 'All-natural creamy almond butter.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTxVcNvZ6j-IxB-QdYwvaKZFqeAmdOfBc-y2LKzJVr7yA&s', '2026-07-26 16:31:30', 'Tree Nuts', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(70, 'Dark Chocolate Bar', 2.99, 'Rich 70% dark chocolate packed with flavor.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR3UOO-40pSXwNwldcEyhypbPsjCRLC_rmxP2uTzDj2ng&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(71, 'Milk Chocolate Bar', 2.49, 'Creamy and sweet milk chocolate.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTjop80PX5ymN-Qbm8EgWQnynsaOPNfuPFZKIKBx5pAAw&s=10', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(72, 'Gummy Bears', 1.99, 'Fruity and chewy gummy bears.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTMyTQClGR79k5GliNndu78uh_HYmP4rg-yQIE6yMON_w&s', '2026-07-26 16:31:30', '', '', 'snacks', 50),
(73, 'Rice Cakes', 2.99, 'Lightly salted puffed rice cakes.', 'https://healthyheartmarket.com/cdn/shop/products/quaker-lightly-salted-rice-cake-4.47-oz-healthy-heart-market.jpg?v=1527536006', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(74, 'Trail Mix', 5.99, 'Energy-packed dried fruit and nut mix.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQK5VTHVdgIr8Kv69tOpbaC00daLVwefCuTlHnLaqMY_Jki5y8KxiZXPo8D&s=10', '2026-07-26 16:31:30', 'Tree Nuts, Peanuts', 'Vegan, Vegetarian, Halal, Kosher', 'snacks', 50),
(75, 'Corn Chips', 3.29, 'Crunchy rolled corn snacks.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTT18FK9m5PNGNPGKRqS-FK3ryHQ1ubIOcKTY5oEizDX_OWw0dpvY42H-c&s=10', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher', 'snacks', 50),
(76, 'Roasted Pistachios', 10.99, 'In-shell roasted pistachios.', 'https://cdn.prod.website-files.com/68065ca761a5c33a7233a669/691f314ac63137745a6c0390_hero--ns-rs.png', '2026-07-26 16:31:30', 'Tree Nuts', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(77, 'Raw Cashews', 11.49, 'Unsalted raw cashews.', 'https://sydney.ooooby.org/_next/image?url=https%3A%2F%2Fstatic.ooooby.org%2Fimage%2Fproduct%2Fnfs%2F79ab36fd-68e2-4962-aa2a-d27a9f24a2b1.570239d7.jpg&w=3840&q=75', '2026-07-26 16:31:30', 'Tree Nuts', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(78, 'Walnut Halves', 8.49, 'Rich and earthy walnut halves.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZZISPzI9I1Rwr8i541ndGB_M1J-_FWQChq4LPyC7U2w&s=10', '2026-07-26 16:31:30', 'Tree Nuts', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(79, 'Sunflower Seeds', 3.99, 'Roasted sunflower seeds in shell.', 'https://theerthaa.com/cdn/shop/files/sunflower_seeds_premium_product_1310x1500_1.webp?v=1786435570&width=1200', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(80, 'Pumpkin Seeds', 4.49, 'Hulled pepitas, great for salads.', 'https://smartorganic.com/wp-content/uploads/2021/01/603_1.jpg', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'snacks', 50),
(81, 'Oat Granola Bar', 1.29, 'Chewy honey and oat granola bar.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR0bV0_P0BHLK2AZdklUCJdImyuwbg5dtqHwcR8yKZstw&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(82, 'Protein Bar', 2.99, 'Chocolate peanut butter protein bar.', 'https://healf.com/_next/image?url=https%3A%2F%2Fcdn.shopify.com%2Fs%2Ffiles%2F1%2F0405%2F7291%2F1765%2Ffiles%2F4_146f9fe5-a54b-41ae-8e9f-ec8cb8551abc.jpg%3Fv%3D1727266071&w=640&q=75', '2026-07-26 16:31:30', 'Soy, Dairy / Milk, Peanuts', 'Vegetarian, Halal, Kosher', 'snacks', 50),
(84, 'White Sandwich Bread', 2.99, 'Soft and fluffy white bread.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRU-26lDDXnqWg2J6hKkzxdWMAk4wIInuiFIbyt0q9NjQuHaALotw99v2SF&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher', 'wheat', 50),
(85, 'Sourdough Boule', 4.99, 'Artisan baked sourdough bread.', 'https://www.kroger.com/product/images/large/top/0003967707225', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(86, 'French Baguette', 2.49, 'Crispy crust traditional baguette.', 'https://spoonacular.com/cdn/ingredients_500x500/baguette.jpg', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(87, 'Butter Croissants', 5.99, 'Pack of 4 flaky butter croissants.', 'https://www.generalmillsfoodservice.com/_next/image?url=https%3A%2F%2Fmojo.generalmills.com%2Fapi%2Fpublic%2Fcontent%2FUz604_6YTdekFUwM20WLWQ_gmi_hi_res_jpeg.jpeg%3Fv%3Da081e041%26t%3Dbc0cec1fd4bc4c35b967df95af8c1fcc&w=3840&q=75', '2026-07-26 16:31:30', 'Gluten / Wheat, Dairy / Milk', 'Vegetarian, Halal, Kosher', 'wheat', 50),
(88, 'Spaghetti Pasta', 1.99, 'Classic Italian dry spaghetti.', 'https://www.barilla.com/next/assets/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2Fniz9afzgmmyd%2F6ubFWr7HdUi8zLhdMtYo3a%2Fe7d240488b4c3671f10ddc18ec40ed96%2FBarilla_Thick_Spaghetti_Pasta.png&w=3840&q=75', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(89, 'Penne Rigate', 1.99, 'Short tube pasta for thick sauces.', 'https://www.barilla.com/next/assets/_next/image?url=https%3A%2F%2Fimages.ctfassets.net%2Fniz9afzgmmyd%2F5feqgH0YEWafUoux9QH9Zy%2F9ba4bd99ea9326262304a46e8f5422cc%2F3DF_Primary_pack_PENNE_RIGATI_BA_410g_CAN__1_.png&w=3840&q=75', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(91, 'Pita Bread', 2.99, 'Soft middle-eastern pocket bread.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRDCzfTQyPb4JVXfzlclWvYdGAqwWeMwtL8Tw1oaiVWdg&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(92, 'Flour Tortillas', 3.49, 'Large wraps for burritos.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ0eL1t9wCHZIYfwbq8qB7nRpCePMPZsrgPwjQ_PAIGKA&s=10', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(93, 'Hamburger Buns', 3.99, 'Pack of 8 sesame seed buns.', 'https://schnucks.com/_next/image?url=https%3A%2F%2Fstorage.googleapis.com%2Fschnucks-item-catalog%2Fa8ec0ea6-9885-40e8-9081-e3dbd42761fb-00014100046738-main-20250904033555.png&w=640&q=75', '2026-07-26 16:31:30', 'Gluten / Wheat, Sesame', 'Vegan, Vegetarian, Halal, Kosher', 'wheat', 50),
(95, 'Pizza Crust', 4.49, 'Pre-baked ready to use pizza crust.', 'https://yi-files.yellowimages.com/products/1040000/1040635/1734312-cover.jpg', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(96, 'All-Purpose Flour', 2.99, 'Versatile baking flour (1kg).', 'https://www.petersoncheese.com/Files/Images/Products/29407.jpg', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(97, 'Whole Wheat Flour', 3.49, 'Unbleached whole wheat baking flour.', 'https://www.themealdb.com/images/ingredients/Flour.png', '2026-07-26 16:31:30', 'Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'wheat', 50),
(98, 'Traditional Soy Sauce', 3.49, 'Brewed soy sauce to add rich umami flavor.', 'https://onggi.com/cdn/shop/files/DSF8645.jpg?v=1687796078', '2026-07-26 16:31:30', 'Soy, Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 50),
(100, 'Tomato Ketchup', 3.99, 'Classic sweet and tangy tomato ketchup.', 'https://images.albertsons-media.com/is/image/ABS/105010003-C1N1?$ng-ecom-pdp-mobile$&defaultImage=Not_Available', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher', 'sauce', 50),
(101, 'Yellow Mustard', 2.49, 'Classic mild yellow mustard.', 'https://spoonacular.com/cdn/ingredients_500x500/mustard.jpg', '2026-07-26 16:31:30', 'Mustard & Celery', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 50),
(103, 'Hot Sauce', 2.99, 'Spicy cayenne pepper hot sauce.', 'https://www.themealdb.com/images/ingredients/Hot%20Sauce.png', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 35),
(104, 'Mild Salsa', 3.99, 'Chunky tomato salsa with mild heat.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR92lf3qbJ3o18WUTz6mMxhWontQIw-X9ZBJiZpnhPkwh2iaZgBV5eRZTI&s=10', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 50),
(105, 'Marinara Pasta Sauce', 5.49, 'Italian style tomato and herb pasta sauce.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTN66wxV8qMgRLiMU9raelr2nwzGoMUQbo5gAycpHb8tg&s', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 49),
(106, 'Alfredo Sauce', 5.99, 'Creamy parmesan and garlic sauce.', 'https://www.ragu.com/wp-content/uploads/2021/10/ragu-16oz-cheese-classic-alfredo-sauce-495x495-1.jpg', '2026-07-26 16:31:30', 'Dairy / Milk', 'Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 40),
(107, 'Basil Pesto', 6.49, 'Rich basil, garlic, and pine nut blend.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_F86WyEol4UkAPYfqbOpkcwR7HGoOZKaTRarXCPeFaA&s=10', '2026-07-26 16:31:30', 'Dairy / Milk, Tree Nuts', 'Vegetarian, Halal, Kosher, Sugar-Free', 'sauce', 50),
(108, 'Ranch Dressing', 4.99, 'Creamy buttermilk and herb dressing.', 'https://m.media-amazon.com/images/I/81WnPBGxURL.jpg', '2026-07-26 16:31:30', 'Dairy / Milk, Eggs', 'Vegetarian, Halal, Kosher', 'sauce', 49),
(109, 'Teriyaki Marinade', 4.49, 'Sweet and savory Japanese style glaze.', 'https://kikkomanusa.com/wp-content/uploads/2025/01/01022-Teriyaki-MS-Original.webp', '2026-07-26 16:31:30', 'Soy, Gluten / Wheat', 'Vegan, Vegetarian, Halal, Kosher', 'sauce', 50),
(110, 'Sriracha Sauce', 5.99, 'Popular garlic chili hot sauce.', 'https://www.themealdb.com/images/ingredients/Sriracha.png', '2026-07-26 16:31:30', '', 'Vegan, Vegetarian, Halal, Kosher', 'sauce', 40),
(113, 'Gluten Free Bread', 5.99, 'Gluten Free Bread, all Organic.', 'https://carbonaut.co/wp-content/uploads/2024/11/us-featured-bread-gf-seeded-2400x2383.webp', '2026-08-21 15:27:06', 'Gluten/Wheat', 'Vegan,Vegetarian,Kosher,Halal', 'Wheat', 49);

-- --------------------------------------------------------

--
-- Table structure for table `product_allergies`
--

CREATE TABLE `product_allergies` (
  `product_id` int(11) NOT NULL,
  `allergy_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_allergies`
--

INSERT INTO `product_allergies` (`product_id`, `allergy_id`) VALUES
(1, 2),
(2, 1),
(8, 2),
(8, 4),
(10, 5),
(10, 6),
(10, 11),
(11, 3),
(11, 5),
(40, 7),
(41, 7),
(42, 7),
(43, 7),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 4),
(60, 3),
(61, 5),
(62, 2),
(66, 3),
(67, 2),
(68, 4),
(70, 2),
(71, 2),
(74, 1),
(74, 4),
(76, 4),
(77, 4),
(78, 4),
(81, 3),
(82, 1),
(82, 2),
(82, 5),
(84, 3),
(85, 3),
(86, 3),
(87, 2),
(87, 3),
(88, 3),
(89, 3),
(91, 3),
(92, 3),
(93, 3),
(93, 8),
(95, 3),
(96, 3),
(97, 3),
(98, 3),
(98, 5),
(101, 11),
(106, 2),
(107, 2),
(107, 4),
(108, 2),
(108, 6),
(109, 3),
(109, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 2, 'hello', 'dfbgdafg', 'closed', '2026-05-04 06:41:04'),
(4, 11, 'Work With Us - Admin: Working as an admin', 'I want to work as an admin, I have 3 years of experience with a touch on management.', 'open', '2026-08-10 15:11:10');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_replies`
--

CREATE TABLE `ticket_replies` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_replies`
--

INSERT INTO `ticket_replies` (`id`, `ticket_id`, `user_id`, `message`, `created_at`) VALUES
(4, 1, 3, 'lll', '2026-05-25 06:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','delivery') DEFAULT 'user',
  `delivery_region` enum('north','haifa','center','jerusalem','south') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `reset_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `theme_nav` varchar(10) DEFAULT '#4caf50',
  `theme_bg` varchar(10) DEFAULT '#f9fcf9',
  `theme_image` varchar(255) DEFAULT NULL,
  `total_spent` decimal(10,2) DEFAULT 0.00,
  `total_orders` int(11) DEFAULT 0,
  `name` varchar(255) DEFAULT 'Customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `delivery_region`, `created_at`, `failed_attempts`, `locked_until`, `reset_otp`, `otp_expiry`, `theme_nav`, `theme_bg`, `theme_image`, `total_spent`, `total_orders`, `name`) VALUES
(2, 'eisa', 'eisa130104@gmail.com', '$2y$10$.63IWX8gs8LWkdOagW873OPhJdGhsM933w19sppp1ZeTr.rtTrQjK', 'user', NULL, '2026-03-15 23:04:06', 0, NULL, NULL, NULL, '#2196f3', '#e3f2fd', 'uploads/1785083935_myphoto.jpg', 624.70, 40, 'Customer'),
(3, 'Yeezus', 'yeezus.bdeer419@gmail.com', '$2y$10$wgkt94obS3TedYdnEJv0Yu9OgjW3RXaKdQe3s0MEP8aMsMvjbhgRm', 'admin', NULL, '2026-04-03 08:49:38', 0, NULL, NULL, NULL, '#4caf50', '#f9fcf9', NULL, 0.00, 0, 'Customer'),
(6, 'bob', 'bob@gmail.com', '123123', 'admin', NULL, '2026-05-18 05:56:57', 2, NULL, NULL, NULL, '#4caf50', '#f9fcf9', NULL, 0.00, 0, 'Customer'),
(7, 'eisa', 'majd.diab33@gmail.com', '$2y$10$Xbh8eAbb1Fmx7rV6/NKGs.soBE7zccdBUUUGX2jtqCeLfYwDDbZdC', 'user', NULL, '2026-07-26 21:25:44', 0, '2026-07-27 01:05:22', NULL, NULL, '#c608d4', '#f9fcf9', 'uploads/1785102587_myphoto.jpg', 4.99, 1, 'Customer'),
(9, 'eisa', 'e.0524114111@gmail.com', '$2y$10$Jc04.otzTO2jKt/.mW3vOeqoEKeNKpMj8MFNupG7wKge.L2WsU5C.', 'admin', NULL, '2026-07-28 22:39:37', 0, NULL, NULL, NULL, '#4caf50', '#f9fcf9', NULL, 0.00, 0, 'Customer'),
(11, 'yaman', 'yamanbdeir151@gmail.com', '$2y$10$a8icM2Cmv8dGBGQyod6Xo.clyev6EPOdu2XR1BRudJ2skHuPD.dgi', 'user', NULL, '2026-07-31 14:21:14', 0, NULL, '954642', '2026-07-31 16:46:36', '#4caf50', '#f9fcf9', NULL, 0.00, 0, 'Customer'),
(12, 'fofa', 'zarorafatme@gmail.com', '$2y$10$ltsswnjq5ZY6WvA047JvyeRUbUGCkCDXalH/kAgaxE.E0MTCGxxIG', '', NULL, '2026-07-31 14:43:12', 0, NULL, '302359', '2026-07-31 17:02:36', '#4caf50', '#f9fcf9', NULL, 249.49, 1, 'Customer'),
(13, 'Raol', 'yazeedcool33@gmail.com', '$2y$10$BUySglxi2kQGfWDQQU7/segwRA7k1OzdYWpbrDHwAQAAL6fBqw85q', 'delivery', 'center', '2026-08-02 09:17:02', 0, NULL, NULL, NULL, '#4caf50', '#f9fcf9', NULL, 0.00, 0, 'Customer'),
(14, 'zezo', 'bdeeryazeed@gmail.com', '$2y$10$LcwOk/aaQZfgeQt0wrEbtee.UCnhCXMtPs.A4RZ/IXRlkLoBy1xju', 'user', NULL, '2026-08-18 08:00:53', 1, NULL, NULL, NULL, '#4caf50', '#f9fcf9', NULL, 112.31, 1, 'Customer');

-- --------------------------------------------------------

--
-- Table structure for table `user_allergies`
--

CREATE TABLE `user_allergies` (
  `user_id` int(11) NOT NULL,
  `allergy_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_allergies`
--

INSERT INTO `user_allergies` (`user_id`, `allergy_id`) VALUES
(2, 2),
(2, 3),
(3, 4),
(6, 4),
(7, 2),
(11, 1),
(11, 6);

-- --------------------------------------------------------

--
-- Table structure for table `user_rewards`
--

CREATE TABLE `user_rewards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 50.00,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_rewards`
--

INSERT INTO `user_rewards` (`id`, `user_id`, `coupon_code`, `discount_amount`, `is_used`, `created_at`, `expires_at`) VALUES
(1, 2, 'AVG40-250494', 15.62, 0, '2026-08-25 06:05:14', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `allergies`
--
ALTER TABLE `allergies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_allergies`
--
ALTER TABLE `product_allergies`
  ADD PRIMARY KEY (`product_id`,`allergy_id`),
  ADD KEY `allergy_id` (`allergy_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_allergies`
--
ALTER TABLE `user_allergies`
  ADD PRIMARY KEY (`user_id`,`allergy_id`),
  ADD KEY `allergy_id` (`allergy_id`);

--
-- Indexes for table `user_rewards`
--
ALTER TABLE `user_rewards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `allergies`
--
ALTER TABLE `allergies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_rewards`
--
ALTER TABLE `user_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_allergies`
--
ALTER TABLE `product_allergies`
  ADD CONSTRAINT `product_allergies_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_allergies_ibfk_2` FOREIGN KEY (`allergy_id`) REFERENCES `allergies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_replies`
--
ALTER TABLE `ticket_replies`
  ADD CONSTRAINT `ticket_replies_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_allergies`
--
ALTER TABLE `user_allergies`
  ADD CONSTRAINT `user_allergies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_allergies_ibfk_2` FOREIGN KEY (`allergy_id`) REFERENCES `allergies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
