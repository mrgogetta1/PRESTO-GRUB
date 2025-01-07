-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 15, 2024 at 10:01 PM
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
-- Database: `prestogrubs`
--

-- --------------------------------------------------------

--
-- Table structure for table `carousel_images`
--

CREATE TABLE `carousel_images` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) NOT NULL,
  `position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carousel_images`
--

INSERT INTO `carousel_images` (`id`, `image_path`, `alt_text`, `position`) VALUES
(1, 'uploads/Untitled.png', 'asdas', 1),
(5, 'uploads/advertise.png', '2', 2),
(6, 'uploads/advertise.png', '2', 2);

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(2, 'test'),
(3, 'test 2'),
(4, 'test 3');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` int(11) DEFAULT 0,
  `order_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `recipient_id`, `message`, `timestamp`, `is_read`, `order_id`, `type`) VALUES
(278, 103, 32, 'New order received: Product: Shawarma, Quantity: 1, Variant: None (SKU: N/A), Room Number: 401, Order Date: 2024-12-15 06:58:36, Order ID: 648, Status: Checked Out', '2024-12-15 05:58:36', 0, 648, 'system'),
(279, 70, 32, 'New order received: Product: Shawarma, Quantity: 1, Variant: None (SKU: N/A), Room Number: 401, Order Date: 2024-12-15 18:36:27, Order ID: 649, Status: Checked Out', '2024-12-15 17:36:27', 0, 649, 'system'),
(280, 70, 32, 'New order received: Product: Shawarma, Quantity: 1, Variant: None (SKU: N/A), Room Number: 401, Order Date: 2024-12-15 18:36:30, Order ID: 650, Status: Checked Out', '2024-12-15 17:36:30', 0, 650, 'system'),
(281, 70, 32, 'New order received: Product: Shawarma, Quantity: 1, Variant: None (SKU: N/A), Room Number: 401, Order Date: 2024-12-15 18:36:33, Order ID: 651, Status: Checked Out', '2024-12-15 17:36:33', 0, 651, 'system'),
(282, 70, 70, 'New order received: Product: Baked Potato, Quantity: 1, Variant: 322 (SKU: asd), Room Number: 401, Order Date: 2024-12-15 18:38:52, Order ID: 653, Status: Checked Out', '2024-12-15 17:38:52', 0, 653, 'system'),
(283, 70, 70, 'Your order status has been updated to \'Complete\'.', '2024-12-15 10:39:01', 0, 653, 'system');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expire_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`id`, `user_id`, `verification_code`, `is_verified`, `created_at`, `expire_at`) VALUES
(58, 69, '532028', 1, '2024-12-07 20:50:11', '2024-12-07 22:05:11'),
(60, 70, '401868', 1, '2024-12-08 02:29:06', '2024-12-08 03:44:06'),
(62, 72, '933614', 1, '2024-12-08 03:46:43', '2024-12-08 05:01:43'),
(86, 103, 'd6779a', 1, '2024-12-14 20:19:57', '2024-12-16 04:19:57'),
(91, 108, '42e989', 1, '2024-12-15 18:39:54', '2024-12-17 02:39:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` int(11) DEFAULT 0,
  `notification_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `created_at`, `is_read`, `notification_type`) VALUES
(1, 31, 'You have a new reply from the seller regarding your order.', '2024-12-04 15:12:13', 1, NULL),
(2, 31, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 08:12:28', 1, NULL),
(3, 31, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 09:12:01', 1, NULL),
(4, 31, 'Your order status has been updated to \'Complete\'.', '2024-12-04 09:16:02', 1, NULL),
(5, 31, 'Your order status has been updated to \'Pending\'.', '2024-12-04 09:19:35', 1, NULL),
(6, 31, 'Your order status has been updated to \'Complete\'.', '2024-12-04 09:19:45', 1, NULL),
(7, 32, 'A new order has been placed for your product.', '2024-12-04 17:28:18', 1, NULL),
(8, 31, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 10:59:45', 1, NULL),
(9, 31, 'Your order status has been updated to \'Complete\'.', '2024-12-04 11:00:23', 1, NULL),
(10, 31, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 11:21:42', 1, NULL),
(11, 31, 'Your order status has been updated to \'Complete\'.', '2024-12-04 11:22:08', 1, NULL),
(12, 37, 'Your order status has been updated to \'Complete\'.', '2024-12-04 12:35:56', 1, NULL),
(13, 32, 'A new order has been placed for your product.', '2024-12-04 19:36:22', 1, NULL),
(14, 37, 'Your order status has been updated to \'Complete\'.', '2024-12-04 12:54:42', 1, NULL),
(15, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 20:43:22', 1, NULL),
(16, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 20:43:30', 1, NULL),
(17, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 20:52:17', 1, NULL),
(18, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:14:56', 1, NULL),
(19, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:19:38', 1, NULL),
(20, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:22:38', 1, NULL),
(21, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:26:44', 1, NULL),
(22, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:26:51', 1, NULL),
(23, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:26:52', 1, NULL),
(24, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:26:53', 1, NULL),
(25, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:28:18', 1, NULL),
(26, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:29:10', 1, NULL),
(27, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:29:19', 1, NULL),
(28, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:29:21', 1, NULL),
(29, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:29:22', 1, NULL),
(30, 37, 'You have a new reply from the seller regarding your order.', '2024-12-04 21:33:09', 1, NULL),
(31, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:33:44', 1, NULL),
(32, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:33:46', 1, NULL),
(33, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:41:02', 1, NULL),
(34, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:41:09', 1, NULL),
(35, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:41:13', 1, NULL),
(36, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:41:24', 1, NULL),
(37, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 21:41:26', 1, NULL),
(38, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 14:42:55', 1, NULL),
(39, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 14:45:28', 1, NULL),
(40, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 14:47:12', 1, NULL),
(41, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 14:50:05', 1, NULL),
(42, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 14:50:10', 1, NULL),
(43, 37, 'Your order status has been updated to \'Checked Out\'.', '2024-12-04 15:06:16', 1, NULL),
(44, 37, 'Your order status has been updated to \'Processing\'.', '2024-12-04 15:43:17', 1, NULL),
(45, 37, 'Your order status has been updated to \'Processing\'.', '2024-12-04 15:43:28', 1, NULL),
(46, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 15:43:30', 1, NULL),
(47, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 15:50:13', 1, NULL),
(48, 37, 'Your order status has been updated to \'Checked Out\'.', '2024-12-04 15:50:58', 1, NULL),
(49, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 15:51:02', 1, NULL),
(50, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 15:56:12', 1, NULL),
(51, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 15:58:33', 1, NULL),
(52, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 15:58:38', 1, NULL),
(53, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 16:01:53', 1, NULL),
(54, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 16:01:59', 1, NULL),
(55, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 16:08:49', 1, NULL),
(56, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 16:08:55', 1, NULL),
(57, 37, 'Your order status has been updated to \'Checked Out\'.', '2024-12-04 16:10:08', 1, NULL),
(58, 37, 'Your order status has been updated to \'Pending\'.', '2024-12-04 16:10:11', 1, NULL),
(59, 37, 'Your order status has been updated to \'Delivering\'.', '2024-12-04 16:11:18', 1, NULL),
(60, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:11:48', 1, NULL),
(61, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:13:37', 1, NULL),
(62, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:16:59', 1, NULL),
(63, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:19:39', 1, NULL),
(64, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:37:47', 1, NULL),
(65, 37, 'You have a new message from the seller regarding your order.', '2024-12-04 23:37:51', 1, NULL),
(66, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:00:13', 1, NULL),
(67, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:00:29', 1, NULL),
(68, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:01:43', 1, NULL),
(69, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:07:38', 1, NULL),
(70, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:22:28', 1, NULL),
(71, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:22:32', 1, NULL),
(72, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:22:35', 1, NULL),
(73, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 01:22:40', 1, NULL),
(74, 36, 'A new order has been placed for your product.', '2024-12-05 14:47:30', 1, NULL),
(75, 36, 'A new order has been placed for your product.', '2024-12-05 14:47:51', 1, NULL),
(76, 37, 'Your order status has been updated to \'Complete\'.', '2024-12-05 08:20:18', 1, NULL),
(77, 37, 'You have a new reply from the seller regarding your order.', '2024-12-05 19:45:09', 1, NULL),
(78, 37, 'You have a new reply from the seller regarding your order.', '2024-12-05 19:45:10', 1, NULL),
(79, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 21:04:31', 1, NULL),
(80, 37, 'You have a new message from the seller regarding your order.', '2024-12-05 21:04:35', 1, NULL),
(81, 37, 'You have a new message from the seller regarding your order.', '2024-12-06 18:31:50', 1, NULL),
(82, 37, 'You have a new message from the seller regarding your order.', '2024-12-06 18:31:55', 1, NULL),
(83, 32, 'A new order has been placed for your product.', '2024-12-08 10:42:20', 1, NULL),
(84, 32, 'A new order has been placed for your product.', '2024-12-08 10:45:51', 1, NULL),
(85, 32, 'A new order has been placed for your product.', '2024-12-08 10:45:51', 1, NULL),
(86, 32, 'A new order has been placed for your product.', '2024-12-08 10:46:10', 1, NULL),
(87, 32, 'A new order has been placed for your product.', '2024-12-08 10:46:57', 1, NULL),
(88, 32, 'A new order has been placed for your product.', '2024-12-08 10:47:06', 1, NULL),
(89, 32, 'A new order has been placed for your product.', '2024-12-08 11:01:11', 1, NULL),
(90, 32, 'A new order has been placed for your product.', '2024-12-08 11:24:45', 1, NULL),
(91, 72, 'A new order has been placed for your product.', '2024-12-08 11:49:35', 1, NULL),
(92, 72, 'A new order has been placed for your product.', '2024-12-08 12:11:38', 1, NULL),
(93, 72, 'A new order has been placed for your product.', '2024-12-08 12:14:16', 1, NULL),
(94, 72, 'A new order has been placed for your product.', '2024-12-08 12:17:51', 1, NULL),
(95, 72, 'A new order has been placed for your product.', '2024-12-08 12:24:46', 1, NULL),
(96, 72, 'A new order has been placed for your product.', '2024-12-08 12:25:47', 1, NULL),
(97, 72, 'A new order has been placed for your product.', '2024-12-08 12:26:18', 1, NULL),
(98, 72, 'A new order has been placed for your product.', '2024-12-08 12:30:03', 1, NULL),
(99, 72, 'A new order has been placed for your product.', '2024-12-08 12:32:34', 1, NULL),
(100, 72, 'A new order has been placed for your product.', '2024-12-08 12:54:24', 1, NULL),
(101, 72, 'A new order has been placed for your product.', '2024-12-08 13:05:18', 1, NULL),
(102, 72, 'A new order has been placed for your product.', '2024-12-08 13:05:58', 1, NULL),
(103, 70, 'You have a new message from the seller regarding your order.', '2024-12-08 13:54:46', 1, NULL),
(104, 72, 'A new order has been placed for your product.', '2024-12-08 14:52:15', 1, NULL),
(105, 32, 'A new order has been placed for your product.', '2024-12-08 14:52:15', 1, NULL),
(106, 32, 'A new order has been placed for your product.', '2024-12-08 14:52:15', 1, NULL),
(107, 32, 'A new order has been placed for your product.', '2024-12-08 14:52:15', 1, NULL),
(108, 32, 'A new order has been placed for your product.', '2024-12-08 14:55:33', 1, NULL),
(109, 32, 'A new order has been placed for your product.', '2024-12-08 14:55:33', 1, NULL),
(110, 32, 'A new order has been placed for your product.', '2024-12-08 14:59:33', 1, NULL),
(111, 32, 'A new order has been placed for your product.', '2024-12-08 15:09:57', 1, NULL),
(112, 36, 'A new order has been placed for your product.', '2024-12-08 15:16:52', 1, NULL),
(113, 32, 'A new order has been placed for your product.', '2024-12-08 15:19:21', 1, NULL),
(114, 32, 'A new order has been placed for your product.', '2024-12-08 15:38:23', 1, NULL),
(115, 32, 'A new order has been placed for your product.', '2024-12-08 15:38:23', 1, NULL),
(116, 32, 'A new order has been placed for your product.', '2024-12-08 15:38:23', 1, NULL),
(117, 32, 'A new order has been placed for your product.', '2024-12-08 15:53:01', 1, NULL),
(118, 32, 'A new order has been placed for your product.', '2024-12-08 15:55:02', 1, NULL),
(119, 70, 'Product added to your cart!', '2024-12-10 14:39:08', 1, NULL),
(120, 72, 'New Order:\nProduct: milk tea\nQuantity: 1\nPrice: ₱30.00\nStatus: Checked Out', '2024-12-10 22:23:54', 0, NULL),
(121, 101, 'Product added to your cart!', '2024-12-10 21:29:50', 1, NULL),
(122, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 591\nQuantity: 1\nTotal Price: ₱30.', '2024-12-11 04:30:00', 0, NULL),
(123, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 592\nQuantity: 1\nTotal Price: ₱30.', '2024-12-11 04:30:22', 0, NULL),
(124, 101, 'Product added to your cart!', '2024-12-10 21:33:40', 1, NULL),
(125, 101, 'Product quantity updated in your cart.', '2024-12-10 21:34:13', 1, NULL),
(126, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 593\nQuantity: 1\nTotal Price: ₱30.', '2024-12-11 04:35:46', 0, NULL),
(127, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 597\nQuantity: 1\nTotal Price: ₱30.', '2024-12-11 06:32:33', 0, NULL),
(128, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 598\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:36:15', 0, NULL),
(129, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 599\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:38:49', 0, NULL),
(130, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 600\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:39:03', 0, NULL),
(131, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 601\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:39:17', 0, NULL),
(132, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 602\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:39:55', 0, NULL),
(133, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 603\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:43:32', 0, NULL),
(134, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 604\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:46:32', 0, NULL),
(135, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 605\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:47:00', 0, NULL),
(136, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 607\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:52:18', 0, NULL),
(137, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 609\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:53:22', 0, NULL),
(138, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 612\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:53:24', 0, NULL),
(139, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 614\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:53:54', 0, NULL),
(140, 72, 'New order placed:\nProduct ID: 78\nOrder ID: 617\nQuantity: 1\nTotal Price: ₱40.', '2024-12-11 06:53:56', 0, NULL),
(141, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:48:24', 1, NULL),
(142, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:50:23', 1, NULL),
(143, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:50:27', 1, NULL),
(144, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:50:34', 1, NULL),
(145, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:25', 1, NULL),
(146, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:26', 1, NULL),
(147, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:34', 1, NULL),
(148, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:52', 1, NULL),
(149, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:56', 1, NULL),
(150, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:53:57', 1, NULL),
(151, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:54:04', 1, NULL),
(152, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:54:08', 1, NULL),
(153, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:56:51', 1, NULL),
(154, 101, 'You have a new message from the seller regarding your order.', '2024-12-11 08:56:52', 1, NULL),
(155, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:37:00', 1, NULL),
(156, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:37:04', 1, NULL),
(157, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:37:06', 1, NULL),
(158, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:42:12', 1, NULL),
(159, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:42:13', 1, NULL),
(160, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:42:14', 1, NULL),
(161, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:42:15', 1, NULL),
(162, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:41', 1, NULL),
(163, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:44', 1, NULL),
(164, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:46', 1, NULL),
(165, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:55', 1, NULL),
(166, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:56', 1, NULL),
(167, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:43:57', 1, NULL),
(168, 101, 'Your order status has been updated to \'Delivering\'.', '2024-12-11 02:46:33', 1, NULL),
(169, 101, 'Your order status has been updated to \'Pending\'.', '2024-12-11 02:55:02', 1, NULL),
(170, 101, 'Your order status has been updated to \'Delivering\'.', '2024-12-11 02:55:22', 1, NULL),
(171, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:58:37', 1, NULL),
(172, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:58:38', 1, NULL),
(173, 101, 'You have a new reply from the seller regarding your order.', '2024-12-11 09:58:51', 1, NULL),
(174, 70, 'Your order status has been updated to \'Complete\'.', '2024-12-15 18:39:01', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `order_description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Checked Out',
  `total_price` decimal(10,2) NOT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `student_number` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `product_id`, `payment_method`, `order_date`, `quantity`, `order_description`, `status`, `total_price`, `room_number`, `receiver_name`, `product_name`, `variant_id`, `student_number`) VALUES
(410, 31, 46, 'cash_on_delivery', '2024-12-04 15:11:43', 1, NULL, 'Complete', 70.00, NULL, NULL, NULL, 0, ''),
(411, 31, 47, 'cash_on_delivery', '2024-12-04 15:18:00', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(412, 31, 47, 'cash_on_delivery', '2024-12-04 15:26:00', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(413, 31, 47, 'cash_on_delivery', '2024-12-04 15:32:28', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(414, 31, 47, 'cash_on_delivery', '2024-12-04 15:32:53', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(415, 31, 47, 'cash_on_delivery', '2024-12-04 15:34:12', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(416, 31, 47, 'cash_on_delivery', '2024-12-04 15:36:00', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(417, 31, 47, 'cash_on_delivery', '2024-12-04 15:42:27', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(418, 31, 47, NULL, '2024-12-04 15:58:56', 1, NULL, 'Complete', 50.00, '0', 'ferdie', NULL, 0, ''),
(419, 31, 48, NULL, '2024-12-04 16:01:11', 1, NULL, 'Cancelled', 50.00, '0', 'ferdie', NULL, 0, ''),
(420, 31, 48, NULL, '2024-12-04 16:12:36', 1, NULL, 'Cancelled', 50.00, '413', 'ferdeasd', NULL, 0, ''),
(421, 31, 47, 'cod', '2024-12-04 17:28:18', 2, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(422, 31, 45, NULL, '2024-12-04 17:58:54', 1, NULL, 'Complete', 69.00, '0', 'Lei Benedict Arzadon', NULL, 0, ''),
(423, 31, 45, NULL, '2024-12-04 18:21:10', 1, NULL, 'Complete', 69.00, '311', 'Lei Benedict Arzadon', NULL, 0, ''),
(424, 37, 48, NULL, '2024-12-04 19:31:42', 1, NULL, 'Complete', 50.00, '401', 'mark', NULL, 0, ''),
(425, 37, 44, 'cod', '2024-12-04 19:36:22', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(426, 37, 44, NULL, '2024-12-04 19:50:14', 1, NULL, 'Cancelled', 60.00, '401', 'mark', NULL, 0, ''),
(427, 37, 48, NULL, '2024-12-04 19:50:36', 1, NULL, 'Complete', 50.00, '401', 'mark', NULL, 0, ''),
(428, 37, 48, NULL, '2024-12-04 19:55:16', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(429, 37, 48, NULL, '2024-12-04 20:03:46', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(430, 37, 48, NULL, '2024-12-04 20:55:47', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(431, 37, 48, NULL, '2024-12-04 21:20:50', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(432, 37, 48, NULL, '2024-12-04 21:41:48', 1, NULL, 'Delivering', 50.00, '401', ' mark', NULL, 0, ''),
(433, 37, 48, NULL, '2024-12-05 14:42:57', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(434, 37, 48, NULL, '2024-12-05 14:43:54', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(435, 37, 48, NULL, '2024-12-05 14:46:50', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(436, 37, 48, 'cod', '2024-12-05 14:47:30', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(437, 37, 48, 'cod', '2024-12-05 14:47:51', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(438, 37, 48, NULL, '2024-12-05 14:48:11', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(439, 37, 48, NULL, '2024-12-05 14:49:51', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(440, 37, 48, NULL, '2024-12-05 14:50:02', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(441, 37, 48, NULL, '2024-12-05 14:52:18', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(442, 37, 48, NULL, '2024-12-05 14:52:46', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(443, 37, 48, NULL, '2024-12-05 14:54:30', 1, NULL, 'Cancelled', 40.00, '401', 'mark', NULL, 0, ''),
(444, 37, 47, NULL, '2024-12-05 14:55:04', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(445, 37, 48, NULL, '2024-12-05 15:00:45', 1, NULL, 'Pending', 40.00, '401', 'mark', NULL, 0, ''),
(446, 37, 77, NULL, '2024-12-05 15:03:29', 1, NULL, 'Complete', 50.00, '401', 'mark', NULL, 0, ''),
(447, 37, 77, NULL, '2024-12-05 19:44:35', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(448, 70, 46, 'cod', '2024-12-08 10:42:20', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(449, 70, 44, NULL, '2024-12-08 10:43:20', 1, NULL, 'Cancelled', 60.00, '123', 'mark villamer', NULL, 0, ''),
(450, 70, 44, NULL, '2024-12-08 10:45:29', 1, NULL, 'Cancelled', 60.00, '401', 'mark villamer', NULL, 0, ''),
(451, 70, 46, 'cod', '2024-12-08 10:45:51', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(452, 70, 46, 'cod', '2024-12-08 10:45:51', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(453, 70, 44, NULL, '2024-12-08 10:45:54', 1, NULL, 'Cancelled', 60.00, '401', 'mark villamer', NULL, 0, ''),
(454, 70, 46, 'cod', '2024-12-08 10:46:10', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(455, 70, 44, NULL, '2024-12-08 10:46:12', 1, NULL, 'Cancelled', 60.00, '401', 'mark villamer', NULL, 0, ''),
(456, 70, 77, NULL, '2024-12-08 10:46:41', 1, NULL, 'Cancelled', 50.00, '401', 'mark villamer', NULL, 0, ''),
(457, 70, 45, 'cod', '2024-12-08 10:46:57', 12, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(458, 70, 45, 'cod', '2024-12-08 10:47:06', 12, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(459, 70, 77, NULL, '2024-12-08 10:47:10', 1, NULL, 'Cancelled', 50.00, '401', 'mark villamer', NULL, 0, ''),
(460, 70, 45, 'cod', '2024-12-08 11:01:11', 12, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(461, 70, 77, NULL, '2024-12-08 11:01:19', 1, NULL, 'Cancelled', 50.00, '401', 'mark villamer', NULL, 0, ''),
(462, 70, 46, 'cod', '2024-12-08 11:24:45', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(463, 70, 77, NULL, '2024-12-08 11:39:32', 1, NULL, 'Cancelled', 50.00, '401', ' ', NULL, 0, ''),
(464, 70, 78, 'cod', '2024-12-08 11:49:35', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(465, 70, 77, NULL, '2024-12-08 11:49:38', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(466, 70, 77, NULL, '2024-12-08 11:51:26', 1, NULL, 'Cancelled', 50.00, '401', 'mark', NULL, 0, ''),
(467, 70, 78, NULL, '2024-12-08 11:52:24', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(468, 70, 78, NULL, '2024-12-08 11:52:58', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(469, 70, 78, NULL, '2024-12-08 11:54:26', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(470, 70, 78, NULL, '2024-12-08 11:54:32', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(471, 70, 78, NULL, '2024-12-08 11:58:16', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(472, 70, 78, NULL, '2024-12-08 11:59:03', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(473, 70, 78, NULL, '2024-12-08 11:59:50', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(474, 70, 78, NULL, '2024-12-08 12:00:35', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(475, 70, 78, NULL, '2024-12-08 12:02:04', 1, NULL, 'Cancelled', 30.00, '123', 'mark', NULL, 0, ''),
(476, 70, 78, NULL, '2024-12-08 12:03:57', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(477, 70, 78, NULL, '2024-12-08 12:05:02', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(478, 70, 78, NULL, '2024-12-08 12:06:23', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(479, 70, 78, NULL, '2024-12-08 12:07:37', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(480, 70, 78, NULL, '2024-12-08 12:08:29', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(481, 70, 78, NULL, '2024-12-08 12:10:04', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(482, 70, 78, 'cod', '2024-12-08 12:11:38', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(483, 70, 78, NULL, '2024-12-08 12:11:40', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(484, 70, 78, 'cod', '2024-12-08 12:14:16', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(485, 70, 78, NULL, '2024-12-08 12:14:18', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(486, 70, 78, NULL, '2024-12-08 12:15:57', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(487, 70, 78, 'cod', '2024-12-08 12:17:51', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(488, 70, 78, NULL, '2024-12-08 12:17:57', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(489, 70, 78, NULL, '2024-12-08 12:21:39', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(490, 70, 78, 'cod', '2024-12-08 12:24:46', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(491, 70, 78, 'cod', '2024-12-08 12:25:47', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(492, 70, 78, 'cod', '2024-12-08 12:26:18', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(493, 70, 78, NULL, '2024-12-08 12:26:21', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(494, 70, 78, NULL, '2024-12-08 12:27:17', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(495, 70, 78, 'cod', '2024-12-08 12:30:03', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(496, 70, 78, NULL, '2024-12-08 12:30:05', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(497, 70, 78, NULL, '2024-12-08 12:31:57', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(498, 70, 78, 'cod', '2024-12-08 12:32:34', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(499, 70, 78, NULL, '2024-12-08 12:32:36', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(500, 70, 78, 'cod', '2024-12-08 12:54:24', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(501, 70, 78, NULL, '2024-12-08 12:54:27', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(502, 70, 78, NULL, '2024-12-08 12:58:49', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(503, 70, 78, NULL, '2024-12-08 13:01:13', 1, NULL, 'Cancelled', 30.00, '401', 'mark', 'milk tea', 0, ''),
(504, 70, 78, NULL, '2024-12-08 13:01:13', 1, NULL, 'Cancelled', 30.00, '401', 'mark', 'milk tea', 0, ''),
(505, 70, 78, 'cod', '2024-12-08 13:05:18', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(506, 70, 78, 'cod', '2024-12-08 13:05:58', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(507, 70, 78, NULL, '2024-12-08 13:05:58', 1, NULL, 'Cancelled', 30.00, 'Not Provided', 'mark', 'milk tea', 0, ''),
(508, 70, 78, NULL, '2024-12-08 13:06:32', 1, NULL, 'Cancelled', 30.00, '401', 'mark', 'milk tea', 0, ''),
(509, 70, 78, NULL, '2024-12-08 13:06:32', 1, NULL, 'Cancelled', 30.00, '401', 'mark', 'milk tea', 0, ''),
(510, 70, 78, NULL, '2024-12-08 13:11:35', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(511, 70, 78, NULL, '2024-12-08 13:11:35', 1, NULL, 'Cancelled', 30.00, '401', 'mark', NULL, 0, ''),
(512, 70, 78, 'cod', '2024-12-08 14:52:15', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(513, 70, 46, 'cod', '2024-12-08 14:52:15', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(514, 70, 47, 'cod', '2024-12-08 14:52:15', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(515, 70, 47, 'cod', '2024-12-08 14:52:15', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(516, 70, 47, 'cod', '2024-12-08 14:55:33', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(517, 70, 46, 'cod', '2024-12-08 14:55:33', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(518, 70, 46, 'cod', '2024-12-08 14:59:33', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(519, 70, 46, 'cod', '2024-12-08 15:09:57', 4, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(520, 70, 77, 'cod', '2024-12-08 15:16:52', 8, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(521, 70, 47, 'cod', '2024-12-08 15:19:21', 4, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(522, 70, 47, 'cod', '2024-12-08 15:38:23', 2, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(523, 70, 46, 'cod', '2024-12-08 15:38:23', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(524, 70, 45, 'cod', '2024-12-08 15:38:23', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(525, 70, 46, 'cod', '2024-12-08 15:53:01', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(526, 70, 46, 'cod', '2024-12-08 15:55:02', 25, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(527, 70, 45, 'cash_on_delivery', '2024-12-08 20:28:43', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(528, 70, 45, 'cash_on_delivery', '2024-12-08 22:28:22', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(529, 70, 77, 'cash_on_delivery', '2024-12-09 02:46:52', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, 0, ''),
(530, 70, 77, 'cod', '2024-12-09 08:22:51', 5, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(531, 70, 78, '0', '2024-12-09 09:39:57', 3, NULL, 'Cancelled', 120.00, NULL, NULL, NULL, 66, ''),
(533, 70, 78, '0', '2024-12-09 11:13:57', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(535, 70, 44, 'cod', '2024-12-09 11:19:12', 1, NULL, 'Cancelled', 0.00, NULL, NULL, NULL, 0, ''),
(536, 70, 44, '0', '2024-12-09 11:20:43', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(537, 70, 44, '0', '2024-12-09 11:39:43', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(538, 70, 44, '0', '2024-12-09 12:43:40', 1, NULL, 'Cancelled', 60.00, '401', 'mark', NULL, NULL, ''),
(539, 70, 45, '0', '2024-12-09 12:56:25', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(540, 70, 44, '0', '2024-12-09 12:56:57', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(541, 70, 78, '0', '2024-12-09 12:56:57', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(542, 70, 80, '0', '2024-12-09 13:09:28', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, NULL, ''),
(543, 70, 44, '0', '2024-12-09 13:13:44', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(544, 70, 44, '0', '2024-12-09 13:21:39', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(545, 70, 44, 'cash_on_delivery', '2024-12-09 13:25:35', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(546, 70, 46, 'cash_on_delivery', '2024-12-09 13:26:16', 1, NULL, 'Cancelled', 70.00, NULL, NULL, NULL, 0, ''),
(547, 70, 44, 'cash_on_delivery', '2024-12-09 13:44:30', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(548, 70, 44, '0', '2024-12-09 13:45:07', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(549, 70, 78, '0', '2024-12-09 13:45:24', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(550, 70, 45, '0', '2024-12-09 13:46:11', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(551, 70, 78, '0', '2024-12-09 13:47:01', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(552, 70, 44, '0', '2024-12-09 13:56:38', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(553, 70, 45, 'cash_on_delivery', '2024-12-09 14:16:37', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(554, 70, 78, 'cash_on_delivery', '2024-12-09 14:35:55', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(555, 70, 45, '0', '2024-12-09 15:17:26', 2, NULL, 'Cancelled', 138.00, NULL, NULL, NULL, NULL, ''),
(556, 37, 44, '0', '2024-12-09 16:39:15', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(557, 70, 78, '0', '2024-12-10 17:53:32', 2, NULL, 'Cancelled', 80.00, NULL, NULL, NULL, 66, ''),
(558, 70, 78, 'cash_on_delivery', '2024-12-10 21:31:45', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, 0, ''),
(559, 70, 78, 'cash_on_delivery', '2024-12-10 21:31:49', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, 0, ''),
(560, 70, 47, '0', '2024-12-10 21:39:48', 1, NULL, 'Cancelled', 50.00, NULL, NULL, NULL, NULL, ''),
(561, 70, 78, 'cash_on_delivery', '2024-12-10 22:23:54', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, NULL, ''),
(562, 70, 44, 'cash_on_delivery', '2024-12-10 22:33:17', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(563, 70, 78, 'cash_on_delivery', '2024-12-10 22:48:57', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, 0, ''),
(564, 70, 78, 'cash_on_delivery', '2024-12-10 23:23:08', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(565, 70, 78, 'cash_on_delivery', '2024-12-10 23:24:31', 1, NULL, 'Cancelled', 40.00, NULL, NULL, NULL, 66, ''),
(566, 70, 80, 'cash_on_delivery', '2024-12-10 23:25:07', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, 0, ''),
(567, 70, 80, 'cash_on_delivery', '2024-12-10 23:25:10', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, 0, ''),
(568, 82, 81, 'cash_on_delivery', '2024-12-10 23:31:05', 2, NULL, 'Cancelled', 88.00, NULL, NULL, NULL, 69, ''),
(569, 82, 81, 'cash_on_delivery', '2024-12-10 23:31:08', 2, NULL, 'Cancelled', 88.00, NULL, NULL, NULL, 69, ''),
(570, 82, 81, '0', '2024-12-10 23:34:33', 4, NULL, 'Cancelled', 176.00, NULL, NULL, NULL, 69, ''),
(571, 82, 44, 'cash_on_delivery', '2024-12-11 00:30:31', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(572, 82, 44, 'cash_on_delivery', '2024-12-11 00:30:59', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(573, 82, 44, 'cash_on_delivery', '2024-12-11 00:31:03', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(574, 82, 44, 'cash_on_delivery', '2024-12-11 00:31:06', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(575, 82, 44, 'cash_on_delivery', '2024-12-11 00:31:10', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(576, 82, 81, 'cash_on_delivery', '2024-12-11 00:32:20', 1, NULL, 'Cancelled', 44.00, NULL, NULL, NULL, 69, ''),
(577, 82, 81, 'cash_on_delivery', '2024-12-11 00:32:24', 1, NULL, 'Cancelled', 44.00, NULL, NULL, NULL, 69, ''),
(578, 82, 81, 'cash_on_delivery', '2024-12-11 00:32:27', 1, NULL, 'Cancelled', 44.00, NULL, NULL, NULL, 69, ''),
(579, 82, 81, 'cash_on_delivery', '2024-12-11 00:33:41', 2, NULL, 'Cancelled', 88.00, NULL, NULL, NULL, 69, ''),
(580, 82, 44, '0', '2024-12-11 00:34:10', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(581, 82, 45, 'cash_on_delivery', '2024-12-11 00:41:11', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(582, 82, 45, 'cash_on_delivery', '2024-12-11 00:55:02', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(583, 82, 45, 'cash_on_delivery', '2024-12-11 00:55:05', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(584, 82, 45, 'cash_on_delivery', '2024-12-11 00:57:38', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, 0, ''),
(585, 82, 81, 'cash_on_delivery', '2024-12-11 00:57:53', 2, NULL, 'Cancelled', 88.00, NULL, NULL, NULL, 69, ''),
(586, 82, 44, 'cash_on_delivery', '2024-12-11 01:02:50', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, 0, ''),
(587, 82, 81, 'cash_on_delivery', '2024-12-11 01:19:46', 1, NULL, 'Cancelled', 11.00, NULL, NULL, NULL, 0, ''),
(588, 82, 81, 'cash_on_delivery', '2024-12-11 01:20:56', 1, NULL, 'Cancelled', 11.00, NULL, NULL, NULL, 0, ''),
(589, 82, 81, '0', '2024-12-11 01:21:37', 1, NULL, 'Cancelled', 44.00, NULL, NULL, NULL, 69, ''),
(590, 82, 81, 'cash_on_delivery', '2024-12-11 01:22:45', 1, NULL, 'Cancelled', 11.00, NULL, NULL, NULL, 0, ''),
(591, 101, 78, NULL, '2024-12-11 04:30:00', 1, NULL, 'Cancelled', 30.00, '401', 'mark villamer', NULL, NULL, '21-00740'),
(592, 101, 78, NULL, '2024-12-11 04:30:22', 1, NULL, 'Cancelled', 30.00, '401', 'mark villamer', NULL, NULL, '21-00740'),
(618, 101, 45, 'cash_on_delivery', '2024-12-11 07:00:08', 1, NULL, 'Cancelled', 69.00, '401', NULL, NULL, 0, ''),
(619, 101, 81, 'cash_on_delivery', '2024-12-11 07:00:41', 1, NULL, 'Cancelled', 11.00, '401', NULL, NULL, 0, ''),
(620, 101, 81, 'cash_on_delivery', '2024-12-11 07:04:22', 1, NULL, 'Cancelled', 11.00, '401', NULL, NULL, 0, ''),
(621, 101, 81, 'cash_on_delivery', '2024-12-11 07:05:27', 1, NULL, 'Cancelled', 44.00, '401', NULL, NULL, 69, ''),
(622, 101, 45, 'cash_on_delivery', '2024-12-11 07:07:22', 1, NULL, 'Cancelled', 69.00, '401', NULL, NULL, 0, ''),
(623, 101, 81, 'cash_on_delivery', '2024-12-11 07:07:46', 1, NULL, 'Cancelled', 44.00, '401', NULL, NULL, 69, ''),
(624, 101, 45, 'cash_on_delivery', '2024-12-11 07:13:12', 1, NULL, 'Cancelled', 69.00, '401', NULL, NULL, 0, ''),
(625, 101, 44, 'cash_on_delivery', '2024-12-11 07:13:29', 1, NULL, 'Cancelled', 60.00, '401', NULL, NULL, 0, ''),
(626, 101, 45, 'cash_on_delivery', '2024-12-11 07:16:51', 1, NULL, 'Cancelled', 69.00, '401', NULL, NULL, 0, ''),
(627, 101, 45, 'cash_on_delivery', '2024-12-11 07:19:25', 1, NULL, 'Cancelled', 69.00, '5', NULL, NULL, 0, ''),
(628, 101, 45, 'cash_on_delivery', '2024-12-11 07:20:58', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(629, 101, 45, 'cash_on_delivery', '2024-12-11 07:23:43', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(630, 101, 45, 'cash_on_delivery', '2024-12-11 07:23:57', 3, NULL, 'Cancelled', 207.00, '401', 'mark.603', NULL, 0, '21-00740'),
(631, 101, 45, 'cash_on_delivery', '2024-12-11 07:25:27', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(632, 101, 45, '0', '2024-12-11 07:28:50', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(633, 101, 45, '0', '2024-12-11 07:33:06', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(634, 101, 45, '0', '2024-12-11 07:36:59', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(635, 101, 45, '0', '2024-12-11 07:38:41', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(636, 101, 44, '0', '2024-12-11 07:40:17', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(637, 101, 44, '0', '2024-12-11 07:41:51', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(638, 101, 44, '0', '2024-12-11 07:59:47', 1, NULL, 'Cancelled', 60.00, NULL, NULL, NULL, NULL, ''),
(639, 101, 45, '0', '2024-12-11 07:59:47', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(640, 101, 45, '0', '2024-12-11 08:02:10', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(641, 101, 45, 'cash_on_delivery', '2024-12-11 08:02:24', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(642, 101, 45, 'cash_on_delivery', '2024-12-11 08:20:42', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(643, 101, 45, 'cash_on_delivery', '2024-12-11 08:23:10', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(644, 101, 46, 'cash_on_delivery', '2024-12-11 08:27:00', 1, NULL, 'Cancelled', 70.00, '401', 'mark.603', NULL, 0, '21-00740'),
(645, 101, 45, 'cash_on_delivery', '2024-12-11 08:30:36', 1, NULL, 'Cancelled', 69.00, '401', 'mark.603', NULL, 0, '21-00740'),
(646, 101, 81, 'cash_on_delivery', '2024-12-11 08:47:53', 1, NULL, 'Cancelled', 44.00, '401', 'mark.603', NULL, 69, '21-00740'),
(647, 101, 45, '0', '2024-12-11 10:54:30', 1, NULL, 'Cancelled', 69.00, NULL, NULL, NULL, NULL, ''),
(648, 103, 44, 'cash_on_delivery', '2024-12-15 13:58:32', 1, NULL, 'Cancelled', 60.00, '401', 'mark.603', NULL, 0, '21-00740'),
(649, 70, 44, 'cash_on_delivery', '2024-12-16 01:36:23', 1, NULL, 'Cancelled', 60.00, '401', 'mark', NULL, 0, '21-00740'),
(650, 70, 44, 'cash_on_delivery', '2024-12-16 01:36:27', 1, NULL, 'Cancelled', 60.00, '401', 'mark', NULL, 0, '21-00740'),
(651, 70, 44, 'cash_on_delivery', '2024-12-16 01:36:31', 1, NULL, 'Checked Out', 60.00, '401', 'mark', NULL, 0, '21-00740'),
(652, 70, 80, '0', '2024-12-16 01:37:56', 1, NULL, 'Cancelled', 30.00, NULL, NULL, NULL, NULL, ''),
(653, 70, 81, 'cash_on_delivery', '2024-12-16 01:38:49', 1, NULL, 'Complete', 44.00, '401', 'mark', NULL, 69, '21-00740');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`, `created_at`) VALUES
(1, 'markvillamer603@gmail.com', '4007f83885e521dfba860b9652e50aab3bc727a5ed8811ca0a2ef2b6f02895089874ac5828426e34d685ddfe418d7288d80f', 1733336191, '2024-12-04 17:46:31'),
(2, 'electric.gaming322@gmail.com', '33dff9b96eef238325a99e66d9564c59ea384024ab4d296b107054e9d3a3754462334a0bdb3930f597ea799d572e68d4aa87', 1733336338, '2024-12-04 17:48:58'),
(3, 'electric.gaming322@gmail.com', '93f17857c18f7cc84d963da9a2659e20f0d86b0c32935c8c3c80f0b4d3a3714f9d7d23f038dd63a9b62596cb8a86029e9fba', 1733336340, '2024-12-04 17:49:00'),
(4, 'electric.gaming322@gmail.com', '198cbbdf521879328d66f066591b491a3461a0fec4c1a8b46712bc73f716297576ee72ba346f7ceff2da401825bab73d72af', 1733336413, '2024-12-04 17:50:13'),
(5, 'electric.gaming322@gmail.com', 'e0b2f65cb2434560441f225ecd7a8a381a729cef6af3cfc597e714ee5e8999dd7062cf76ba99c8b3e88166037d4ad764947f', 1733336442, '2024-12-04 17:50:42'),
(6, 'electric.gaming322@gmail.com', 'f449b7dbadf71c2228b0704f09288dcbc364f83e39b3e82c86de3fb5936b7ccb967629873cf881855c797acc5e3f196e3d8a', 1733338335, '2024-12-04 18:22:15'),
(7, 'electric.gaming322@gmail.com', '250163c26ead6c5fa3b900a4b4c3a2eb41c3ff24c46c170d23207ac000499bb4e5881239872ff4b1b510b1601d0107dd7d44', 1733338353, '2024-12-04 18:22:33'),
(8, 'electric.gaming322@gmail.com', '84c801b6a4f40e61015b641fd61e4fb56214cea4ee2d21a8ed3dfbdd0bb9f3121afb4e32529028197eb5bcfd0dbeead5c9c3', 1733338444, '2024-12-04 18:24:04'),
(9, 'electric.gaming322@gmail.com', '5e3352c4ae89885a55c2dd07029931d220e326f74b074eb1aa00152d9b5dc155281ca5e138ca3fa45932c8301d9ab4712191', 1733338506, '2024-12-04 18:25:06'),
(11, 'electric.gaming322@gmail.com', 'f25f6a1ebcbf34eb95cbcf68fc27683906ced4317a000707782a6ea90b7da2485cec8fe84a6e6a463707a6686118e0d09978', 1733338690, '2024-12-04 18:28:10'),
(13, 'electric.gaming322@gmail.com', '94a736cf46271d2be4bd6d1cbadd6f59081776e370503ca21a52bdb096f0acf81bc9638c55bb0bedfca520ab6ebe31b0e308', 1733338775, '2024-12-04 18:29:35'),
(16, 'electric.gaming322@gmail.com', 'e61f7e7333ad6d17fcb3afa28125194560f80726da3714b1b20b43830221719c2f903e36b54c0af81d31673061776c302dbe', 1733581492, '2024-12-07 13:54:52'),
(17, 'electric.gaming322@gmail.com', '905034327d9f54152b8b284d047889d7c44d234231a1b53970371a4e3fb8efa29e1a856ea5a874f5758c253352d2f1462bbe', 1733581720, '2024-12-07 13:58:40'),
(18, 'electric.gaming322@gmail.com', '49035feae72d7bf44a6aa8d4dc070faed59b750e6140a2d5e70744d10d3fdf304d2dc7bfa9211ed37eecd88bf80926141e17', 1733581783, '2024-12-07 13:59:43'),
(19, 'electric.gaming322@gmail.com', 'cbc64ca941ae24f91951ca6af7013a61ad212292c2a8b791a4c250d7bcd1c2d2f1390f80c31721f267722e29392c87b28464', 1733581936, '2024-12-07 14:02:16'),
(20, 'electric.gaming322@gmail.com', '65939925ddb614143eaefd5fc768faef52a2d610651e66db11fc574c9b034439bd4712cbb11e9c4c4f0465bea8769e62e268', 1733581939, '2024-12-07 14:02:19'),
(21, 'electric.gaming322@gmail.com', '80b84f0ad308b967e2ef7b7f6f8cec0634ea28c62933ee108f270c805a9d91d029a5d7e863cdc9fb119d2b30a98f3fe922d1', 1733581997, '2024-12-07 14:03:17'),
(22, 'electric.gaming322@gmail.com', 'ad5690d77b20b83dd4e84df09c823e749636b791fef14685dc37022d3cd44e72e38115aa12b64c284aaae9d2a3cff206d4bb', 1733582000, '2024-12-07 14:03:20'),
(23, 'electric.gaming322@gmail.com', 'f9f5d548c50c2c0d2bba02a4958901eff96f0c4acb251fe164813297fdb1a4ae3dc1b3ba0de1c2f410cd6674a85cb12c810b', 1733582092, '2024-12-07 14:04:52'),
(24, 'electric.gaming322@gmail.com', '07de4e150c8c7b3c2a03edea4f732d22e3cfb6cfb7c535552425b078116a925e5c0073e60d005a121ccf9d49bc2144bc9d49', 1733582111, '2024-12-07 14:05:11'),
(25, 'electric.gaming322@gmail.com', 'd1a23ca19263ae60e2f04590122b6209d8cfb03f8fea707f799ac754dac3b02bd2fd9eb2e53d8da28435b92fa26271122342', 1733582116, '2024-12-07 14:05:16'),
(26, 'electric.gaming322@gmail.com', '9c74c1396f204ac5140141c9bb0d2245151da5492fddf6238c8f952fe32a19140ed7840968e17be637c32f1fe61c53891e7b', 1733582147, '2024-12-07 14:05:47'),
(27, 'electric.gaming322@gmail.com', '4488dd76f98cd7c22c4fc42fe032f8cc3af7f18001a2e8fcc5264e456c8bb2e19d0a2c04502f61075408010bf62fbf040e9b', 1733582150, '2024-12-07 14:05:50'),
(28, 'electric.gaming322@gmail.com', '73de8f587ce0f6f110c83567c527a09d49bbb53a83ec536756a2349ef425aa4dbb07362b5c2391ffe0573582c7a80011e663', 1733582365, '2024-12-07 14:09:25'),
(29, 'electric.gaming322@gmail.com', '7c3df081f9237a8ba58a3354197c6c178b902c541138d2e379340cc4ab15169021b3545bb648114859c9d76f476faf8e1b9d', 1733582441, '2024-12-07 14:10:41'),
(30, 'electric.gaming322@gmail.com', 'e08b907fdc38b19e3b93a9ae050da24345a962e98c1bb6fc904b87372261de87554fcd3c18bcade8df97e89b998733be070b', 1733582563, '2024-12-07 14:12:43'),
(31, 'markvillamer603@gmail.com', '440a1636a1923510b5edca25090ac53691eda00f14c0b6f314a8a6fa3fe86f8e6c5c29262ef93515c19f71e9dfd07923b4e2', 1733584597, '2024-12-07 14:46:37'),
(32, 'electric.gaming322@gmail.com', 'bb8eeb91ec84e9eb9cf9d5bd2f3604ff634104ba6af467035f0f80dc5e539888feef3f72a2e3807593fec9667c1628819040', 1733589700, '2024-12-07 16:11:40'),
(33, 'electric.gaming322@gmail.com', 'd3add1b2881a8c2a311e82cdd03b1fa1f40b7ea04cade09c288c94cafe2aba116ee10433ebec4018e02ae4da3b56839aba18', 1733589727, '2024-12-07 16:12:07'),
(34, 'electric.gaming322@gmail.com', '332f20c72917d5b01690640df922d22c07dcb0a8a2d854a43781e9b70c85a856ab63525f0fa36bbcda63b79074854e3bee05', 1733589780, '2024-12-07 16:13:00'),
(35, 'electric.gaming322@gmail.com', '1216f2f560209befdfa577b111e8e8ce95f10bfa7809561132ebfb5deff5fc9c02839f33ebde02908182c224e21948a3f0fd', 1733590117, '2024-12-07 16:18:37'),
(37, 'electric.gaming322@gmail.com', 'aa1a66b1a20e4951d652b25b510f03259d910b441065e06135042d9b6fc5181ad5439cdb377f7c14e66e7797500677cace92', 1733590178, '2024-12-07 16:19:38'),
(40, 'electric.gaming322@gmail.com', 'd455da512f20d1021a0e3d74bf9632b0f6b57ac4b980cedcff6dff8c1bf59b7b67cbd8700b837fd91927df38926d8ed62348', 1733725402, '2024-12-09 05:53:22'),
(41, 'electric.gaming322@gmail.com', '7df21d3dbd2f1b824f7c9a7eb812036c48e5f3c77cdc230d59cace2ed2810815a802f626206e5b32329a324ac68a572cd7c4', 1733725487, '2024-12-09 05:54:47'),
(42, 'electric.gaming322@gmail.com', '80b9d053c02006dba31db590466c5fc7797403aeeddb4fb90f1bce591e89258deb8e2290025cf8ba9ed1b6dea4187a559809', 1733728432, '2024-12-09 06:43:52'),
(43, 'markvillamer603@gmail.com', 'bf06340a4a37859974a7f7f61aa948d6934234fc5b23c72acb315c2129ef9073fa040cfeb2ca0541482257c4262078ee696e', 1733860289, '2024-12-10 19:21:29'),
(44, 'markvillamer603@gmail.com', '24f49179d27f61c6486c46487d4ac4deae8303fa427dcdd830d70c8e552ce14519c3a5f1331f2040029e57801154d7213d2f', 1733860415, '2024-12-10 19:23:35'),
(46, 'markvillamer603@gmail.com', '6f156f43a27308cdf3b8320a166adcfbf5667e41c5f2705e4e6ccc678448b9c2d2c3bb71743a8f9420287095c4ffbea55904', 1733896347, '2024-12-11 05:22:27');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `store_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `out_of_stock` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `category` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `image`, `store_id`, `user_id`, `out_of_stock`, `stock_quantity`, `category`, `is_featured`, `category_id`) VALUES
(44, 'Shawarma', 'Chicken and Beef meat only.', 60.00, 'shawarma.jpg', 39, 32, 0, 27, NULL, 0, 2),
(45, 'Spam Burger', 'A Classic Spam and Soft Buns with Vegetables', 69.00, 'burger.jpg', 39, 32, 0, 27, NULL, 0, 2),
(46, 'Shawarma Rice', 'Made of spit-roasted layers of lamb, beef, or other meat that are sliced and often wrapped in or served with pita. ', 70.00, 'shawarmarice.jpg', 39, 32, 0, 13, NULL, 0, 2),
(47, 'Pastil', 'A Food Originated from our Muslim Brother', 50.00, 'chicken pastil 50.jpg', 40, 32, 0, 0, NULL, 0, 2),
(77, 'burger', 'asd', 50.00, 'burger.jpg', 42, 36, 0, 0, NULL, 0, 3),
(78, 'milk tea', 'milk tea', 30.00, 'Pearl milktea 50.jpg', 43, 72, 0, 0, NULL, 0, 4),
(80, 'Baked Potato', 'asd', 30.00, 'advertise.png', 42, 36, 0, 0, NULL, 0, 4),
(81, 'Baked Potato', 'test', 11.00, '35se.jpg', 44, 70, 0, 50, 'Meals', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_comments`
--

CREATE TABLE `product_comments` (
  `comment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_comments`
--

INSERT INTO `product_comments` (`comment_id`, `product_id`, `user_id`, `username`, `rating`, `comment`, `date_posted`) VALUES
(22, 44, 31, 'Lei Benedict Arzadon', 5, 'ok', '2024-11-25 08:12:34'),
(23, 45, 31, 'Lei Benedict Arzadon', 3, 'good', '2024-11-25 08:34:33'),
(24, 46, 31, 'Lei Benedict Arzadon', 5, 'adsasdasd', '2024-11-25 19:12:18'),
(25, 45, 31, 'Lei Benedict Arzadon', 1, 'adasd\r\n', '2024-11-25 19:14:47'),
(26, 45, 31, 'Lei Benedict Arzadon', 5, 'asad', '2024-11-25 19:14:54'),
(27, 77, 70, 'mark villamer', 5, 'asd', '2024-12-10 13:37:20');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_name` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_name`, `stock_quantity`, `sku`, `price`, `created_at`, `updated_at`) VALUES
(66, 78, 'mark', 11, 'mark', 10.00, '2024-12-09 00:04:29', '2024-12-09 00:04:29'),
(67, 77, 'mark test', 10, 'test', 10.00, '2024-12-09 00:05:37', '2024-12-09 00:05:37'),
(68, 77, 'mark test', 10, 'asd22', 30.00, '2024-12-09 00:08:00', '2024-12-09 00:08:00'),
(69, 81, '322', 33, 'asd', 33.00, '2024-12-10 15:27:22', '2024-12-10 15:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `store_id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `store_description` text NOT NULL,
  `store_contact` varchar(50) NOT NULL,
  `store_location` varchar(255) NOT NULL,
  `store_image` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`store_id`, `store_name`, `store_description`, `store_contact`, `store_location`, `store_image`, `user_id`, `id`, `views`) VALUES
(39, 'Bowls and Buns', 'Breakfast, Lunch, and Dinner is Available', '09198372353', 'TCU - Canteen', 'bowls and bun.jpg', 32, 0, 0),
(40, 'RT 3C', 'Different Kind of Food', '09198372353', 'TCU - Canteen', 'store pic.jpg', 32, 0, 0),
(43, 'test', 'test', '123', 'taguig', '49f85a378fbf603c30c0a2ba04b2cfa1.jpg', 72, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `isAdmin` tinyint(3) DEFAULT 0,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` char(60) DEFAULT NULL,
  `user_address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `course` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_number`, `isAdmin`, `first_name`, `last_name`, `email`, `password_hash`, `user_address`, `contact_no`, `registered_at`, `contact`, `address`, `store_id`, `course`, `section`, `is_active`, `profile_picture`, `username`, `verified`) VALUES
(31, '', 0, 'Lei Benedict', 'Arzadon', 'UserBndct@gmail.com', '$2y$10$FICZ/vLe9HLEhqcqleqH4.hhIhYvzHmCuIcvSfeKf1Zu5CH6LcBwq', 'Blk 68 Lot 12 Tibi St. Upper Bicutan, Taguig City', '09198372353', '2024-11-24 14:12:38', NULL, NULL, NULL, 'BSIS', 'B2021', 0, NULL, '', 0),
(32, '', 1, 'Lei Benedict', 'Arzadon', 'SellerBndct@gmail.com', '$2y$10$7GVwu01MsTXnjpVHehqtCexxnkUdQMEL6NlcAoI0m.72fwymZDDcS', 'Blk 68 Lot 12 Tibi St. Upper Bicutan, Taguig City', '09198372353', '2024-11-24 14:14:41', NULL, NULL, NULL, 'BSIS', 'B2021', 0, NULL, '', 0),
(33, '', 2, 'Lei Benedict', 'Arzadon', 'AdminBndct@gmail.com', '$2y$10$3j3QlynWGFsCHghtwZeUrOnayajTAqpOrchP/UcIPxVeplqSn6MbW', 'Blk 68 Lot 12 Tibi St. Upper Bicutan, Taguig City', '09198372353', '2024-11-24 14:16:11', NULL, NULL, NULL, 'BSIS', 'B2021', 0, NULL, '', 0),
(34, '', 0, 'Normal', 'User', 'NorSer@gmail.com', '$2y$10$MBdt0uF8GW5tkUvRwe8TEOIYhkrcJboj7FtQ4Q32koEqlijXiTpP2', 'Blk 68 Lot 12 Tibi St. Upper Bicutan, Taguig City', '09198372353', '2024-11-25 19:05:49', NULL, NULL, NULL, 'BSIS', 'B2021', 0, NULL, '', 0),
(35, '', 0, 'Lei Benedict', 'Arzadon', 'bndct@gmail.com', '$2y$10$V1kYouNv5zlvyRpb8OiVS.avy/dCRRO/42caUt4VKC8wnZ.OsJJRK', 'Blk 68 Lot 12 Tibi St. Upper Bicutan, Taguig City', '09198372353', '2024-11-26 06:59:10', NULL, NULL, NULL, 'BSIS', 'B2021', 0, NULL, '', 0),
(36, '', 1, 'mark', 'seller', 'markseller@gmail.com', '$2y$10$P07PR4i4Y4Xc4qhwQr5ldOHf7/eitlWmkp4fjGZcGj2jbXI4v7U9.', 'taguig', '09669986236', '2024-11-27 14:58:54', NULL, NULL, NULL, 'is', 'b2021', 0, NULL, '', 0),
(37, '', 0, 'mark', 'villamer', 'markvillamer@gmail.com', '$2y$10$QqXnnZOvQ0W9tWCobc4ruevlv8uA6fUBvjJdgGGlf15nM3PTeooYG', 'taguig', '09669986236', '2024-11-28 16:44:59', NULL, NULL, NULL, 'is', 'b2021', 0, NULL, '', 0),
(69, '', 2, 'raphiel', 'admin', 'raphiel322@gmail.com', '$2y$10$n.3OMtwTXglLkdRi9Ohi6e.Be8qI4LQAgpSnD/UaLpYBFfVeaC/qy', NULL, '09669986236', '2024-12-07 20:50:11', NULL, NULL, NULL, '', '', 0, NULL, 'raphiel', 0),
(70, '21-00740', 1, 'mark', 'villamer', 'electric.gaming322@gmail.com', '$2y$10$VZ8XZPwuZThOEKiRHKRItuXf5wppM5UL7D5.hvFmWN46mL4Q2b3uS', '', '09669986236', '2024-12-08 02:29:05', NULL, NULL, NULL, '', '', 0, '34d6f88fc234236ba8b728999b83ed8d-removebg-preview.png', 'mark', 0),
(108, '21-00740', 0, 'mark', 'villamer', 'markvillamer603@gmail.com', '$2y$10$bOML3X2GTO11UkYnIgoqoudpWTHy2mWkHTziS4s0lx4bOpoekuEXC', NULL, '09669986236', '2024-12-15 18:39:54', NULL, NULL, NULL, 'is', 'b2021', 1, '34d6f88fc234236ba8b728999b83ed8d-removebg-preview.png', 'mark.603', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carousel_images`
--
ALTER TABLE `carousel_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`product_id`,`variant_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_store_id` (`store_id`),
  ADD KEY `fk_category` (`category_id`);

--
-- Indexes for table `product_comments`
--
ALTER TABLE `product_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`store_id`),
  ADD KEY `id` (`id`),
  ADD KEY `fk_user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carousel_images`
--
ALTER TABLE `carousel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=654;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `product_comments`
--
ALTER TABLE `product_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`);

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
