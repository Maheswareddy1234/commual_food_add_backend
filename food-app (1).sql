-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 08:28 AM
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
-- Database: `food-app`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `full_address` text NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `portion_size` varchar(50) DEFAULT 'Regular',
  `spice_level` varchar(50) DEFAULT 'Medium',
  `oil_level` varchar(50) DEFAULT 'Medium',
  `salt_level` varchar(50) DEFAULT 'Normal',
  `instructions` text DEFAULT NULL,
  `customization` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_customizations`
--

CREATE TABLE `cart_customizations` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `spice_level` varchar(20) DEFAULT NULL,
  `oil_level` varchar(20) DEFAULT NULL,
  `salt_level` varchar(20) DEFAULT NULL,
  `sweetness` varchar(20) DEFAULT NULL,
  `portion_size` varchar(20) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('chef','customer') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `receiver_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `order_id`, `sender_id`, `sender_type`, `message`, `is_read`, `created_at`, `receiver_id`) VALUES
(1, 10, 7, 'customer', 'Hlo', 1, '2026-01-03 09:20:08', 1),
(2, 10, 7, 'customer', 'Good', 1, '2026-01-05 03:13:59', 1),
(3, 10, 1, 'chef', 'Goid', 1, '2026-01-05 03:15:40', 7),
(4, 7, 1, 'chef', 'Hlo', 0, '2026-01-05 04:08:10', 7),
(5, 11, 7, 'customer', 'Hlo', 0, '2026-01-05 04:41:55', 1),
(6, 20, 7, 'customer', 'Hlo', 0, '2026-01-06 07:07:27', 5),
(7, 22, 7, 'customer', 'Hlofgvv', 1, '2026-01-06 16:22:58', 4),
(8, 22, 4, 'chef', 'Hloo', 1, '2026-01-06 16:26:47', 7),
(9, 24, 7, 'customer', 'Hlo', 0, '2026-01-07 16:30:03', 5);

-- --------------------------------------------------------

--
-- Table structure for table `chefs`
--

CREATE TABLE `chefs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `fcm_token` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chefs`
--

INSERT INTO `chefs` (`id`, `name`, `email`, `phone`, `password`, `created_at`, `latitude`, `longitude`, `fcm_token`) VALUES
(1, 'mani', 'mani@gmail.com', '9876543210', '$2y$10$.gklfUELI8F8bK.wpKpkE.ujzJyYRUu.S/WIqrmhcYtSfGXfPww3q', '2025-12-30 09:47:24', NULL, NULL, 'fRRv3lHdSF60019_v6k7RH:APA91bHqmvivaGiOeahC81o3-uk8T2-dLQ-8JW2N9jd21fYNQEclP2WaKDwrlf2peCOMdI25PSye23AGP-5hF4tvW3vfAFIuoSHoynMLVCTk6PWU6BqhR6k'),
(4, 'uppaluru Maheswarreddy', 'mahi@gmail.com', '9381093770', '$2y$10$NneNppyQBs37FtbXq361eeeDp3fZBT.UlfePl.dCKMNxN3rfqkJyi', '2026-01-05 15:45:33', 13.03026800, 80.01818820, 'fRRv3lHdSF60019_v6k7RH:APA91bHqmvivaGiOeahC81o3-uk8T2-dLQ-8JW2N9jd21fYNQEclP2WaKDwrlf2peCOMdI25PSye23AGP-5hF4tvW3vfAFIuoSHoynMLVCTk6PWU6BqhR6k'),
(5, 'praveen', 'praveen@gmail.com', '9381093770', '$2y$10$S0hfOd3mfr993KYrD6vrVu4numlfCQ7SZk3R0XRB12ugNEXY9HYJq', '2026-01-05 15:51:38', 13.02959000, 80.01768500, 'fRRv3lHdSF60019_v6k7RH:APA91bHqmvivaGiOeahC81o3-uk8T2-dLQ-8JW2N9jd21fYNQEclP2WaKDwrlf2peCOMdI25PSye23AGP-5hF4tvW3vfAFIuoSHoynMLVCTk6PWU6BqhR6k'),
(6, 'Sathish', 'sathish@gmail.com', '9381093770', '$2y$10$qnofqn4h9GHggqmIXiaZ..Aps.sAu5ws5ocnrjtuxUMj7Tveec2lK', '2026-01-05 15:54:27', 13.03027530, 80.01818320, 'fRRv3lHdSF60019_v6k7RH:APA91bHqmvivaGiOeahC81o3-uk8T2-dLQ-8JW2N9jd21fYNQEclP2WaKDwrlf2peCOMdI25PSye23AGP-5hF4tvW3vfAFIuoSHoynMLVCTk6PWU6BqhR6k');

-- --------------------------------------------------------

--
-- Table structure for table `chef_notifications`
--

CREATE TABLE `chef_notifications` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chef_notifications`
--

INSERT INTO `chef_notifications` (`id`, `chef_id`, `type`, `title`, `message`, `order_id`, `is_read`, `created_at`) VALUES
(1, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000010) from uppaluru Maheswarreddy. Tap to view details.', 10, 0, '2026-01-03 09:13:17'),
(2, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000011) from uppaluru Maheswarreddy. Tap to view details.', 11, 0, '2026-01-05 04:41:40'),
(3, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000012) from uppaluru Maheswarreddy. Tap to view details.', 12, 0, '2026-01-05 07:53:32'),
(4, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000013) from uppaluru Maheswarreddy. Tap to view details.', 13, 0, '2026-01-05 07:56:15'),
(5, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000014) from uppaluru Maheswarreddy. Tap to view details.', 14, 0, '2026-01-05 08:27:23'),
(6, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000015) from uppaluru Maheswarreddy. Tap to view details.', 15, 0, '2026-01-05 08:37:50'),
(7, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000016) from uppaluru Maheswarreddy. Tap to view details.', 16, 0, '2026-01-05 08:59:29'),
(8, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000017) from uppaluru Maheswarreddy. Tap to view details.', 17, 0, '2026-01-05 09:04:37'),
(9, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000018) from uppaluru Maheswarreddy. Tap to view details.', 18, 0, '2026-01-05 09:31:21'),
(10, 1, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000019) from uppaluru Maheswarreddy. Tap to view details.', 19, 0, '2026-01-05 15:24:34'),
(11, 5, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000020) from uppaluru Maheswarreddy. Tap to view details.', 20, 0, '2026-01-06 07:07:08'),
(12, 4, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000021) from uppaluru Maheswarreddy. Tap to view details.', 21, 0, '2026-01-06 16:13:11'),
(13, 4, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000022) from uppaluru Maheswarreddy. Tap to view details.', 22, 0, '2026-01-06 16:21:57'),
(14, 5, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000023) from uppaluru Maheswarreddy. Tap to view details.', 23, 0, '2026-01-06 16:48:09'),
(15, 5, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000024) from uppaluru Maheswarreddy. Tap to view details.', 24, 0, '2026-01-07 16:29:19'),
(16, 4, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000024) from uppaluru Maheswarreddy. Tap to view details.', 24, 0, '2026-01-07 16:29:22'),
(17, 4, 'new_order', 'New Order Received!', 'You have a new order (#ORD-2026-000025) from uppaluru Maheswarreddy. Tap to view details.', 25, 0, '2026-01-07 16:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `chef_orders`
--

CREATE TABLE `chef_orders` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL,
  `total_amount` int(11) NOT NULL,
  `status` enum('PREPARING','CONFIRMED','DELIVERED') DEFAULT 'PREPARING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chef_profiles`
--

CREATE TABLE `chef_profiles` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `cuisine` varchar(150) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `reviews` int(11) DEFAULT NULL,
  `distance` varchar(20) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `availability` varchar(50) DEFAULT NULL,
  `orders` varchar(50) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `specialties` text DEFAULT NULL,
  `availability_start` varchar(20) DEFAULT NULL,
  `availability_end` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chef_profiles`
--

INSERT INTO `chef_profiles` (`id`, `chef_id`, `cuisine`, `rating`, `reviews`, `distance`, `experience`, `availability`, `orders`, `about`, `tags`, `image`, `specialties`, `availability_start`, `availability_end`) VALUES
(1, 1, 'North Indian, Diabetic Meals', 4.8, 124, '0.5 km', '5', 'Mon-Sat', '350', 'yy', 'Low Salt,High Protein', 'uploads/chef_profiles/chef_1_1767368945.jpg', 'Low Salt,High Protein', '09:18 PM', '09:19 PM');

-- --------------------------------------------------------

--
-- Table structure for table `chef_reviews`
--

CREATE TABLE `chef_reviews` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `rating` decimal(2,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chef_reviews`
--

INSERT INTO `chef_reviews` (`id`, `chef_id`, `rating`) VALUES
(1, 1, 4.5),
(2, 1, 5.0),
(3, 1, 4.8),
(4, 1, 4.6),
(5, 2, 4.2);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `fcm_token` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `password`, `created_at`, `latitude`, `longitude`, `dob`, `fcm_token`) VALUES
(7, 'uppaluru Maheswarreddy', 'mahi@gmail.com', '', '$2y$10$uQgrPxrCe/IZfnNiHNHllOe9GobxcJ0Z3xt9IynLnBAKUn7NfRvIq', '2025-12-31 07:35:53', NULL, NULL, '2026-01-02', 'fRRv3lHdSF60019_v6k7RH:APA91bHqmvivaGiOeahC81o3-uk8T2-dLQ-8JW2N9jd21fYNQEclP2WaKDwrlf2peCOMdI25PSye23AGP-5hF4tvW3vfAFIuoSHoynMLVCTk6PWU6BqhR6k');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_slots`
--

CREATE TABLE `delivery_slots` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `slot_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `slot_time` time NOT NULL,
  `is_booked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `dish_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `preparation_time` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `food_type` enum('Veg','Non-Veg') NOT NULL,
  `health_tags` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`id`, `chef_id`, `dish_name`, `description`, `price`, `preparation_time`, `category`, `food_type`, `health_tags`, `image`, `created_at`, `is_available`) VALUES
(31, 1, 'Mutton Briyani', 'Mutton Biryani is a rich, aromatic South Asian layered rice dish featuring tender, marinated mutton (goat/lamb), fragrant basmati rice, and a complex blend of spices like cardamom, cloves, and saffron, often slow-cooked (dum style) with yogurt, fried onions, and fresh herbs (mint/coriander) for a festive, celebratory meal with deep, complex flavors.', 399.00, 60, 'North Indian', 'Non-Veg', 'Low Salt,High Protein', 'dish_1767587884_695b402ce956b.jpg', '2026-01-05 04:38:05', 1),
(32, 4, 'Curd rice', 'Curd rice, or Thayir Sadam, is a soothing, traditional South Indian comfort food made from soft, cooked rice mashed with yogurt (curd) and milk, then seasoned with a flavorful tempering of mustard seeds, curry leaves, ginger, green chilies, and asafoetida, often garnished with pomegranate seeds or cashews and served chilled or tepid as a digestive aid after spicy meals.', 120.00, 30, 'South Indian', 'Veg', 'Low Salt,Heart Healthy', 'dish_1767628687_695bdf8f98227.jpg', '2026-01-05 15:58:09', 1),
(33, 5, 'Curd rice', 'Curd rice, or Thayir Sadam, is a soothing, traditional South Indian comfort food made from soft, cooked rice mashed with yogurt (curd) and milk, then seasoned with a flavorful tempering of mustard seeds, curry leaves, ginger, green chilies, and asafoetida, often garnished with pomegranate seeds or cashews and served chilled or tepid as a digestive aid after spicy meals.', 90.00, 30, 'South Indian', 'Veg', 'Gluten Free,High Protein', 'dish_1767628863_695be03fd74e6.jpg', '2026-01-05 16:01:04', 1),
(34, 6, 'Curd rice', 'Curd rice, or Thayir Sadam, is a soothing, traditional South Indian comfort food made from soft, cooked rice mashed with yogurt (curd) and milk, then seasoned with a flavorful tempering of mustard seeds, curry leaves, ginger, green chilies, and asafoetida, often garnished with pomegranate seeds or cashews and served chilled or tepid as a digestive aid after spicy meals.', 90.00, 30, 'South Indian', 'Veg', 'Low Salt,Diabetic Friendly', 'dish_1767628968_695be0a897b61.jpg', '2026-01-05 16:02:49', 1),
(35, 4, 'Chicken Biryani', 'Chicken Biryani is a fragrant, layered South Asian mixed rice dish featuring marinated chicken, long-grain basmati rice, whole spices, herbs (mint, cilantro), and fried onions, slow-cooked in a sealed pot (dum style) for tender meat and flavorful, infused rice, creating a festive, aromatic, and hearty meal.', 120.00, 30, 'South Indian', 'Non-Veg', 'Heart Healthy,High Protein', 'dish_1767719328_695d41a08f455.jpg', '2026-01-06 17:08:49', 1),
(36, 4, 'Veg biryani', 'Veg biryani is an aromatic, flavorful, and rich South Asian rice dish made with long-grain basmati rice, mixed vegetables, and a complex blend of whole and ground spices. It is a hearty, complete meal often prepared for celebrations and family dinners.', 99.00, 50, 'South Indian', 'Veg', 'Diabetic Friendly,Low Salt', 'dish_1767719549_695d427d07ca2.jpg', '2026-01-06 17:12:30', 1),
(37, 4, 'Mutton biryani', 'Mutton Biryani is a rich, aromatic South Asian layered rice dish featuring tender, marinated mutton (goat/lamb), fragrant basmati rice, and a complex blend of spices like cardamom, cloves, and saffron, often slow-cooked (dum style) with yogurt, fried onions, and fresh herbs (mint/coriander) for a festive, celebratory meal with deep, complex flavors.', 299.00, 60, 'South Indian', 'Non-Veg', 'Low Salt,Soft Diet', 'dish_1767719706_695d431a30bc6.jpg', '2026-01-06 17:15:07', 1),
(38, 5, 'Mutton biryani', 'Mutton Biryani is a rich, aromatic South Asian layered rice dish featuring tender, marinated mutton (goat/lamb), fragrant basmati rice, and a complex blend of spices like cardamom, cloves, and saffron, often slow-cooked (dum style) with yogurt, fried onions, and fresh herbs (mint/coriander) for a festive, celebratory meal with deep, complex flavors.', 299.00, 60, 'North Indian', 'Non-Veg', 'Low Salt,Soft Diet', 'dish_1767719897_695d43d90e699.jpg', '2026-01-06 17:18:17', 1),
(39, 5, 'Chicken Biryani', 'Chicken Biryani is a fragrant, layered South Asian mixed rice dish featuring marinated chicken, long-grain basmati rice, whole spices, herbs (mint, cilantro), and fried onions, slow-cooked in a sealed pot (dum style) for tender meat and flavorful, infused rice, creating a festive, aromatic, and hearty meal.', 150.00, 50, 'North Indian', 'Non-Veg', 'Diabetic Friendly,Soft Diet', 'dish_1767719991_695d44374f0ae.jpg', '2026-01-06 17:19:53', 1),
(40, 5, 'Veg biryani', 'Veg biryani is an aromatic, flavorful, and rich South Asian rice dish made with long-grain basmati rice, mixed vegetables, and a complex blend of whole and ground spices. It is a hearty, complete meal often prepared for celebrations and family dinners.', 99.00, 40, 'North Indian', 'Veg', 'Low Salt,Soft Diet', 'dish_1767720103_695d44a75e5c1.jpg', '2026-01-06 17:21:44', 1),
(41, 6, 'veg biryani', 'Veg biryani is an aromatic, flavorful, and rich South Asian rice dish made with long-grain basmati rice, mixed vegetables, and a complex blend of whole and ground spices. It is a hearty, complete meal often prepared for celebrations and family dinners.', 99.00, 40, 'Continental', 'Veg', 'Low Salt,Gluten Free', 'dish_1767720252_695d453c11eb7.jpg', '2026-01-06 17:24:12', 1),
(42, 6, 'Chicken Biryani', 'Chicken Biryani is a fragrant, layered South Asian mixed rice dish featuring marinated chicken, long-grain basmati rice, whole spices, herbs (mint, cilantro), and fried onions, slow-cooked in a sealed pot (dum style) for tender meat and flavorful, infused rice, creating a festive, aromatic, and hearty meal.', 150.00, 50, 'South Indian', 'Non-Veg', 'Soft Diet,Low Salt', 'dish_1767720296_695d4568cb271.jpg', '2026-01-06 17:24:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `dish_customizations`
--

CREATE TABLE `dish_customizations` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `spice_level` varchar(20) DEFAULT NULL,
  `oil_level` varchar(20) DEFAULT NULL,
  `salt_level` varchar(20) DEFAULT NULL,
  `sweetness` varchar(20) DEFAULT NULL,
  `portion_size` varchar(20) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dish_customizations`
--

INSERT INTO `dish_customizations` (`id`, `customer_id`, `dish_id`, `spice_level`, `oil_level`, `salt_level`, `sweetness`, `portion_size`, `instructions`, `created_at`) VALUES
(1, 1, 5, 'Medium', 'Medium', 'Low', 'Medium', 'Regular', 'No onion, less oil', '2025-12-17 08:56:27'),
(2, 1, 5, 'Medium', 'Medium', 'Low', 'Medium', 'Regular', 'No onion, less oil', '2025-12-26 06:55:21');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `reviews` int(11) DEFAULT 0,
  `cook_time` varchar(50) DEFAULT NULL,
  `calories` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `protein` varchar(50) DEFAULT NULL,
  `carbs` varchar(50) DEFAULT NULL,
  `fat` varchar(50) DEFAULT NULL,
  `fiber` varchar(50) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foods`
--

INSERT INTO `foods` (`id`, `name`, `price`, `rating`, `reviews`, `cook_time`, `calories`, `description`, `protein`, `carbs`, `fat`, `fiber`, `tags`, `ingredients`, `image`) VALUES
(1, 'Palak Paneer', 120, 4.8, 124, '25 mins', '280 kcal', 'Cottage cheese cooked in spinach gravy', '18g', '12g', '10g', '6g', 'Diabetic Friendly,Low Salt', 'Spinach,Paneer,Garlic,Onion,Spices', 'food_1.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `food_categories`
--

CREATE TABLE `food_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_categories`
--

INSERT INTO `food_categories` (`id`, `name`, `icon`, `color`, `description`, `display_order`, `is_active`, `created_at`) VALUES
(1, 'Vegetarian', '🥗', '#DCFCE7', 'Fresh vegetable-based meals', 1, 1, '2026-01-06 07:22:28'),
(2, 'Non-Vegetarian', '🍗', '#FFE2E2', 'Protein-rich chicken & meat dishes', 2, 1, '2026-01-06 07:22:28'),
(3, 'Diabetic Friendly', '🥬', '#DBEAFE', 'Low sugar, controlled carbs', 3, 1, '2026-01-06 07:22:28'),
(4, 'Soft Diet', '🥣', '#FEF9C2', 'Easy to chew and digest', 4, 1, '2026-01-06 07:22:28'),
(5, 'Low Sodium', '🧂', '#F3E8FF', 'Heart-healthy, reduced salt', 5, 1, '2026-01-06 07:22:28'),
(6, 'High Protein', '💪', '#FFEDD4', 'Muscle building & recovery', 6, 1, '2026-01-06 07:22:28');

-- --------------------------------------------------------

--
-- Table structure for table `health_preferences`
--

CREATE TABLE `health_preferences` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `dietary_type` varchar(50) DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `health_goals` text DEFAULT NULL,
  `calorie_limit` int(11) DEFAULT NULL,
  `avoid_ingredients` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_preferences`
--

INSERT INTO `health_preferences` (`id`, `customer_id`, `dietary_type`, `allergies`, `health_goals`, `calorie_limit`, `avoid_ingredients`, `created_at`, `updated_at`) VALUES
(1, 7, '', '[\"Gluten\"]', '[\"High Protein\"]', 20, '[]', '2026-01-03 04:03:27', '2026-01-03 04:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `home_chefs`
--

CREATE TABLE `home_chefs` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) NOT NULL,
  `kitchen_address` text NOT NULL,
  `cuisines` varchar(255) NOT NULL,
  `experience` int(11) NOT NULL,
  `id_proof` varchar(255) DEFAULT NULL,
  `kitchen_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_chefs`
--

INSERT INTO `home_chefs` (`id`, `full_name`, `email`, `phone`, `location`, `kitchen_address`, `cuisines`, `experience`, `id_proof`, `kitchen_image`, `status`, `created_at`, `latitude`, `longitude`) VALUES
(1, 'uppaluru ', 'maheswarreddyuppaluru96@gmail.com', '938109377', 'zz', 'gvv', 'vv', 88, '1767097811_id_upload_1767097804513.jpg', '1767097811_kitchen_upload_1767097804530.jpg', 'pending', '2025-12-30 12:30:11', NULL, NULL),
(3, 'praveen ', 'p@gmail.com', '9381093770', 'india', 'Chennai, Tamil Nadu, 602105', 'North Indian', 3, '1767336880_id_upload_1767336870678.jpg', '1767336880_kitchen_upload_1767336870704.jpg', 'pending', '2026-01-02 06:54:40', 13.02831090, 80.01580860),
(4, 'uppaluru Maheswarreddy ', 'mahi@gmail.com', '9381093770', 'india', 'Kuthambakkam, Tamil Nadu, 602105', 'North indian', 3, '1767628118_id_upload_1767628113113.jpg', '1767628118_kitchen_upload_1767628113133.jpg', 'pending', '2026-01-05 15:48:38', 13.03026800, 80.01818820),
(5, 'praveen ', 'praveen@gmail.com', '9381093770', 'indian', 'Chennai, Tamil Nadu, 602105', 'South indian', 3, '1767628389_id_upload_1767628387284.jpg', '1767628389_kitchen_upload_1767628387298.jpg', 'pending', '2026-01-05 15:53:09', 13.02959000, 80.01768500),
(6, 'sathish', 'sathish@gmail.com', '9381093770', 'indian', 'Kuthambakkam, Tamil Nadu, 602105', 'Gujarath ', 3, '1767628530_id_upload_1767628529291.jpg', '1767628530_kitchen_upload_1767628529305.jpg', 'pending', '2026-01-05 15:55:30', 13.03027530, 80.01818320);

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `chef_id`, `item_name`, `is_active`) VALUES
(1, 1, 'Veg Thali', 1),
(2, 1, 'Paneer Butter Masala', 1),
(3, 1, 'Dal Rice', 1),
(4, 1, 'Chapati', 0),
(5, 2, 'Chicken Curry', 1),
(6, 2, 'Egg Fried Rice', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `delivery_date` varchar(50) DEFAULT NULL,
  `delivery_time` varchar(50) DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `order_number`, `subtotal`, `discount`, `total`, `payment_method`, `delivery_date`, `delivery_time`, `status`, `created_at`) VALUES
(1, 7, '#ORD-2026-000001', 140.00, 14.00, 126.00, 'phonepe', '03 Jan 2026', '03:20 am', 'delivered', '2026-01-02 03:01:06'),
(2, 7, '#ORD-2026-000002', 240.00, 24.00, 216.00, 'phonepe', '04 Jan 2026', '12:19 pm', 'confirmed', '2026-01-02 04:29:22'),
(3, 7, '#ORD-2026-000003', 120.00, 0.00, 120.00, 'gpay', '05 Jan 2026', '12:20 am', 'confirmed', '2026-01-02 04:46:49'),
(4, 7, '#ORD-2026-000004', 6.00, 0.60, 5.40, 'gpay', '02 Jan 2026', '12:52 pm', 'pending', '2026-01-02 07:24:00'),
(5, 7, '#ORD-2026-000005', 6.00, 0.60, 5.40, 'phonepe', '02 Jan 2026', '01:18 pm', 'pending', '2026-01-02 07:48:44'),
(6, 7, '#ORD-2026-000006', 6.00, 0.60, 5.40, 'gpay', '03 Jan 2026', '01:24 pm', 'pending', '2026-01-02 15:55:34'),
(7, 7, '#ORD-2026-000007', 6.00, 0.60, 5.40, 'gpay', '04 Jan 2026', '09:54 pm', 'pending', '2026-01-02 16:24:58'),
(8, 7, '#ORD-2026-000008', 390.00, 39.00, 351.00, 'gpay', '03 Jan 2026', '10:24 am', 'pending', '2026-01-03 04:55:16'),
(9, 7, '#ORD-2026-000009', 44.00, 4.40, 39.60, 'phonepe', '03 Jan 2026', '01:36 pm', 'pending', '2026-01-03 08:07:14'),
(10, 7, '#ORD-2026-000010', 55.00, 5.50, 49.50, 'razorpay', '03 Jan 2026', '04:42 pm', 'delivered', '2026-01-03 09:13:17'),
(11, 7, '#ORD-2026-000011', 798.00, 79.80, 718.20, 'razorpay', '06 Jan 2026', '02:10 am', 'preparing', '2026-01-05 04:41:40'),
(12, 7, '#ORD-2026-000012', 399.00, 39.90, 359.10, 'gpay', '05 Jan 2026', '01:23 pm', 'preparing', '2026-01-05 07:53:32'),
(13, 7, '#ORD-2026-000013', 1197.00, 119.70, 1077.30, 'razorpay', '05 Jan 2026', '01:25 pm', 'pending', '2026-01-05 07:56:15'),
(14, 7, '#ORD-2026-000014', 1596.00, 159.60, 1436.40, 'razorpay', '05 Jan 2026', '11:56 pm', 'pending', '2026-01-05 08:27:23'),
(15, 7, '#ORD-2026-000015', 1596.00, 159.60, 1436.40, 'razorpay', '06 Jan 2026', '03:07 pm', 'confirmed', '2026-01-05 08:37:50'),
(16, 7, '#ORD-2026-000016', 399.00, 39.90, 359.10, 'razorpay', '06 Jan 2026', '03:28 pm', 'pending', '2026-01-05 08:59:29'),
(17, 7, '#ORD-2026-000017', 798.00, 79.80, 718.20, 'razorpay', '06 Jan 2026', '03:23 pm', 'pending', '2026-01-05 09:04:37'),
(18, 7, '#ORD-2026-000018', 798.00, 0.00, 798.00, 'razorpay', '06 Jan 2026', '04:01 pm', 'delivered', '2026-01-05 09:31:21'),
(19, 7, '#ORD-2026-000019', 399.00, 39.90, 359.10, 'razorpay', '06 Jan 2026', '03:15 pm', 'confirmed', '2026-01-05 15:24:34'),
(20, 7, '#ORD-2026-000020', 180.00, 0.00, 180.00, 'razorpay', '07 Jan 2026', '12:36 pm', 'pending', '2026-01-06 07:07:08'),
(21, 7, '#ORD-2026-000021', 480.00, 48.00, 432.00, 'razorpay', '06 Jan 2026', '09:42 pm', 'pending', '2026-01-06 16:13:11'),
(22, 7, '#ORD-2026-000022', 240.00, 0.00, 240.00, 'razorpay', '07 Jan 2026', '03:51 pm', 'delivered', '2026-01-06 16:21:57'),
(23, 7, '#ORD-2026-000023', 180.00, 0.00, 180.00, 'razorpay', '07 Jan 2026', '12:00 pm', 'pending', '2026-01-06 16:48:09'),
(24, 7, '#ORD-2026-000024', 396.00, 0.00, 396.00, 'razorpay', '08 Jan 2026', '09:58 pm', 'pending', '2026-01-07 16:29:19'),
(25, 7, '#ORD-2026-000025', 1196.00, 119.60, 1076.40, 'razorpay', '08 Jan 2026', '10:08 pm', 'pending', '2026-01-07 16:38:52');

-- --------------------------------------------------------

--
-- Table structure for table `order_delivery_slots`
--

CREATE TABLE `order_delivery_slots` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `chef_id` int(11) NOT NULL,
  `slot_day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `slot_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `dish_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL,
  `customization` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `dish_id`, `dish_name`, `quantity`, `price`, `customization`) VALUES
(1, 1, 25, 'Dal Tadka', 1, 140.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Medium\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"yy\"}'),
(2, 2, 16, 'gg', 2, 120.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Low\",\"instructions\":\"rr\"}'),
(3, 3, 16, 'gg', 1, 120.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"rr\"}'),
(4, 4, 17, 'uu', 2, 3.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"\"}'),
(5, 5, 17, 'uu', 2, 3.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"eee\"}'),
(6, 6, 17, 'uu', 2, 3.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"rr\"}'),
(7, 7, 17, 'uu', 2, 3.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"pp\"}'),
(8, 8, 15, 'gg', 2, 55.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"tt\"}'),
(9, 8, 25, 'Dal Tadka', 2, 140.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"tt\"}'),
(10, 9, 13, 'ffg', 2, 22.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"tt\"}'),
(11, 10, 15, 'gg', 1, 55.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"\"}'),
(12, 11, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"no\"}'),
(13, 12, 31, 'Mutton Briyani', 1, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"ubs\"}'),
(14, 13, 31, 'Mutton Briyani', 3, 399.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"vsbs\"}'),
(15, 14, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"rr\"}'),
(16, 14, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"ff\"}'),
(17, 15, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"yyy\"}'),
(18, 15, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"pp\"}'),
(19, 16, 31, 'Mutton Briyani', 1, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"1\"}'),
(20, 17, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"0\"}'),
(21, 18, 31, 'Mutton Briyani', 2, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Normal\",\"instructions\":\"8\"}'),
(22, 19, 31, 'Mutton Briyani', 1, 399.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"r\"}'),
(23, 20, 33, 'Curd rice', 2, 90.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"yy\"}'),
(24, 21, 32, 'Curd rice', 4, 120.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"mm\"}'),
(25, 22, 32, 'Curd rice', 2, 120.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"pp\"}'),
(26, 23, 33, 'Curd rice', 2, 90.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"mmm\"}'),
(27, 24, 40, 'Veg biryani', 2, 99.00, '{\"portion_size\":\"Regular\",\"spice_level\":\"Medium\",\"oil_level\":\"Medium\",\"salt_level\":\"Low\",\"instructions\":\"gg\"}'),
(28, 24, 36, 'Veg biryani', 2, 99.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"ggg\"}'),
(29, 25, 37, 'Mutton biryani', 2, 299.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Low\",\"instructions\":\"hh\"}'),
(30, 25, 37, 'Mutton biryani', 2, 299.00, '{\"portion_size\":\"Large\",\"spice_level\":\"Spicy\",\"oil_level\":\"High\",\"salt_level\":\"Normal\",\"instructions\":\"gh\"}');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active',
  `discount_percent` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `customer_id`, `plan_name`, `amount`, `payment_id`, `start_date`, `end_date`, `status`, `discount_percent`, `created_at`) VALUES
(1, 7, 'Weekly Plan', 699.00, 'pay_1767196796452', '2025-12-31', '2026-01-07', 'active', 10, '2025-12-31 15:59:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_customizations`
--
ALTER TABLE `cart_customizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_sender` (`sender_id`,`sender_type`);

--
-- Indexes for table `chefs`
--
ALTER TABLE `chefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `chef_notifications`
--
ALTER TABLE `chef_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chef_id` (`chef_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `chef_orders`
--
ALTER TABLE `chef_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chef_id` (`chef_id`);

--
-- Indexes for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chef_id` (`chef_id`);

--
-- Indexes for table `chef_reviews`
--
ALTER TABLE `chef_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chef_id` (`chef_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dish_customizations`
--
ALTER TABLE `dish_customizations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`customer_id`,`dish_id`);

--
-- Indexes for table `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `food_categories`
--
ALTER TABLE `food_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `health_preferences`
--
ALTER TABLE `health_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `home_chefs`
--
ALTER TABLE `home_chefs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chef_id` (`chef_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_delivery_slots`
--
ALTER TABLE `order_delivery_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `cart_customizations`
--
ALTER TABLE `cart_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `chefs`
--
ALTER TABLE `chefs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chef_notifications`
--
ALTER TABLE `chef_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `chef_orders`
--
ALTER TABLE `chef_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chef_reviews`
--
ALTER TABLE `chef_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_slots`
--
ALTER TABLE `delivery_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `dish_customizations`
--
ALTER TABLE `dish_customizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `food_categories`
--
ALTER TABLE `food_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `health_preferences`
--
ALTER TABLE `health_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `home_chefs`
--
ALTER TABLE `home_chefs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `order_delivery_slots`
--
ALTER TABLE `order_delivery_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chef_orders`
--
ALTER TABLE `chef_orders`
  ADD CONSTRAINT `chef_orders_ibfk_1` FOREIGN KEY (`chef_id`) REFERENCES `chefs` (`id`);

--
-- Constraints for table `chef_profiles`
--
ALTER TABLE `chef_profiles`
  ADD CONSTRAINT `chef_profiles_ibfk_1` FOREIGN KEY (`chef_id`) REFERENCES `chefs` (`id`);

--
-- Constraints for table `chef_reviews`
--
ALTER TABLE `chef_reviews`
  ADD CONSTRAINT `chef_reviews_ibfk_1` FOREIGN KEY (`chef_id`) REFERENCES `chefs` (`id`);

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`chef_id`) REFERENCES `chefs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
