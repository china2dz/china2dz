-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 29, 2026 at 06:18 PM
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
-- Database: `china2dz`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent_profiles`
--

CREATE TABLE `agent_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `national_id` varchar(50) NOT NULL,
  `id_card_name` varchar(200) NOT NULL,
  `company_name` varchar(200) NOT NULL,
  `wilaya` varchar(100) DEFAULT NULL,
  `rc_number` varchar(100) NOT NULL,
  `rc_owner_name` varchar(200) NOT NULL,
  `admin_note` text DEFAULT NULL,
  `id_card_file` varchar(255) DEFAULT '',
  `rc_file` varchar(255) DEFAULT '',
  `whatsapp` varchar(20) DEFAULT NULL,
  `ccp_account` varchar(30) DEFAULT NULL,
  `ccp_rip` varchar(30) DEFAULT NULL,
  `ccp_owner` varchar(100) DEFAULT NULL,
  `deposit_amount` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_profiles`
--

INSERT INTO `agent_profiles` (`id`, `user_id`, `first_name`, `last_name`, `national_id`, `id_card_name`, `company_name`, `wilaya`, `rc_number`, `rc_owner_name`, `admin_note`, `id_card_file`, `rc_file`, `whatsapp`, `ccp_account`, `ccp_rip`, `ccp_owner`, `deposit_amount`) VALUES
(55, 82, 'ines', 'bmz', '12345', 'ines boumaza', 'china2', 'Algiers', '1234', 'nadine', NULL, 'uploads/agents/id_82.jpg', 'uploads/agents/rc_82.jpg', NULL, '123456', '16122004', 'ines boumaza', 10000),
(56, 83, 'ines', 'boumaza', '12345', 'inou', 'china', NULL, '1234', 'oumaima', NULL, 'uploads/agents/id_83.jpg', 'uploads/agents/rc_83.jpg', NULL, NULL, NULL, NULL, NULL),
(57, 86, 'hacen', 'b', '123456', 'hacen b', 'auto annaba', 'Skikda', '1234567', 'ahcen', NULL, 'uploads/agents/id_86.jpeg', 'uploads/agents/rc_86.jpeg', NULL, NULL, NULL, NULL, NULL),
(59, 88, 'mohammed', 'ben', '123456', 'mohammed ben', 'auto oran', NULL, '1234567', 'ines', NULL, 'uploads/agents/id_88.jpg', 'uploads/agents/rc_88.jpg', NULL, NULL, NULL, NULL, NULL),
(60, 89, 'mohammed', 'b', '123456', 'mohammed ben', 'auto oran', NULL, '1234', 'ines', NULL, 'uploads/agents/id_89.jpg', 'uploads/agents/rc_89.jpg', NULL, NULL, NULL, NULL, NULL),
(61, 90, 'aya', 'b', '1234', 'aya boumaza', 'A motors', NULL, '123456', 'aya boumaza', NULL, 'uploads/agents/id_90.jpg', 'uploads/agents/rc_90.jpg', NULL, NULL, NULL, NULL, NULL),
(62, 91, 'aya', 'b', '12345', 'aya b', 'A motors', NULL, '1234', 'aya b', NULL, 'uploads/agents/id_91.jpg', 'uploads/agents/rc_91.jpg', NULL, NULL, NULL, NULL, NULL),
(63, 92, 'oumaima', 'ch', '123', 'oumaima ch', 'ch motors', 'Guelma', '12345', 'oumaima ch', NULL, 'uploads/agents/id_92.jpg', 'uploads/agents/rc_92.jpg', NULL, NULL, NULL, NULL, NULL),
(64, 94, 'aymen', 'fadli', '54342312', 'aymen fadli', 'Trustcar Dz', NULL, '2345678', 'aymen fadli', NULL, 'uploads/agents/id_94.jpg', 'uploads/agents/rc_94.jpg', NULL, '1112233', '001122', 'aymen fadli', 10000),
(65, 96, 'ayouch', 'boumaza', '12345', 'ayouch', 'aya\'s cars', 'Djanet', '1234', 'ayouch', NULL, 'uploads/agents/id_96.jpeg', 'uploads/agents/rc_96.jpeg', NULL, '123321', '12345678', 'ayouch boumaza', 150000);

-- --------------------------------------------------------

--
-- Table structure for table `agent_reports`
--

CREATE TABLE `agent_reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) DEFAULT NULL,
  `reporter_name` varchar(100) DEFAULT NULL,
  `agent_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('open','resolved') DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_reports`
--

INSERT INTO `agent_reports` (`id`, `reporter_id`, `reporter_name`, `agent_id`, `reason`, `details`, `status`, `created_at`) VALUES
(1, 85, 'nadina bmz', 82, 'Fake car listings', '', '', '2026-05-13 19:17:33'),
(2, 85, 'nadina bmz', 82, 'Fake car listings', '', 'open', '2026-05-13 19:21:59'),
(3, 85, 'nadina bmz', 82, 'Abusive or inappropriate behavior', '', 'open', '2026-05-13 19:30:36'),
(4, 85, 'nadina bmz', 82, 'Wrong or misleading information', '', 'resolved', '2026-05-13 19:41:53'),
(5, 80, 'ines bmz', 86, 'Other', '', 'resolved', '2026-05-13 21:06:09'),
(6, 86, 'hacen b', 86, 'Wrong or misleading information', '', 'resolved', '2026-05-13 22:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `agent_reviews`
--

CREATE TABLE `agent_reviews` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `reviewer_name` varchar(100) DEFAULT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `likes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_reviews`
--

INSERT INTO `agent_reviews` (`id`, `agent_id`, `reviewer_id`, `reviewer_name`, `rating`, `comment`, `reply`, `created_at`, `likes`) VALUES
(1, 82, 85, 'nadina bmz', 5, 'a good and trustworthy agent, i received my car a few days ago', NULL, '2026-05-11 19:26:24', 1),
(2, 94, 93, 'daniel dani', 4, 'good agent ,trust and reliable service', NULL, '2026-05-26 15:36:32', 0),
(3, 82, 93, 'daniel dani', 4, 'professional handling from start to finish', NULL, '2026-05-27 14:26:44', 0),
(4, 92, 85, 'nadina bmz', 4, 'fast response and clear communication', NULL, '2026-05-27 14:30:41', 0),
(5, 96, 81, 'ines bmz', 5, 'you are very helpful and respectful', NULL, '2026-05-27 21:22:59', 0);

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_email` varchar(150) NOT NULL,
  `client_phone` varchar(20) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year_min` int(11) DEFAULT NULL,
  `year_max` int(11) DEFAULT NULL,
  `budget_min` decimal(12,2) DEFAULT NULL,
  `budget_max` decimal(12,2) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `frequency` enum('instant','daily','weekly') DEFAULT 'instant',
  `status` enum('open','responded','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `client_id`, `client_name`, `client_email`, `client_phone`, `brand`, `model`, `year_min`, `year_max`, `budget_min`, `budget_max`, `body_type`, `fuel_type`, `description`, `frequency`, `status`, `created_at`) VALUES
(1, 80, 'ines bmz', 'inouinou346@gmail.com', '0787653412', '', '', NULL, NULL, NULL, NULL, '', '', 'à', 'instant', 'responded', '2026-05-04 17:07:40'),
(2, 80, 'ines bmz', 'inouinou346@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'haval', 'instant', 'responded', '2026-05-13 22:45:18'),
(3, 80, 'ines bmz', 'inouinou346@gmail.com', '', 'changan', NULL, NULL, NULL, NULL, NULL, '', '', '', 'instant', 'open', '2026-05-13 22:53:48'),
(4, 93, 'daniel dani', 'cheraitoumaima0@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'livan', 'instant', 'responded', '2026-05-18 11:07:45'),
(5, 93, 'daniel dani', 'cheraitoumaima0@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'jac', 'instant', 'responded', '2026-05-27 14:33:42'),
(6, 81, 'ines bmz', 'chinadz563@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'jetour', 'instant', 'responded', '2026-05-27 19:53:44'),
(7, 85, 'nadina bmz', 'nadinebmz13@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'changan', 'instant', 'open', '2026-05-27 23:48:36'),
(8, 85, 'nadina bmz', 'nadinebmz13@gmail.com', '', '', NULL, NULL, NULL, NULL, NULL, '', '', 'changan', 'instant', 'open', '2026-05-27 23:48:47');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_users`
--

CREATE TABLE `blocked_users` (
  `id` int(11) NOT NULL,
  `blocker_id` int(11) NOT NULL,
  `blocked_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'available',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fuel_type` varchar(50) DEFAULT NULL,
  `body_type` varchar(50) DEFAULT NULL,
  `wilaya` varchar(100) DEFAULT NULL,
  `mileage` int(11) DEFAULT 0,
  `transmission` varchar(50) DEFAULT NULL,
  `drive_type` varchar(20) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `color_ext` varchar(50) DEFAULT NULL,
  `color_int` varchar(50) DEFAULT NULL,
  `engine` varchar(100) DEFAULT NULL,
  `power` varchar(50) DEFAULT NULL,
  `consumption` varchar(50) DEFAULT NULL,
  `delivery` varchar(50) DEFAULT NULL,
  `duty_free` tinyint(1) DEFAULT 1,
  `specs_text` text DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `reservation_status` enum('available','reserved') DEFAULT 'available',
  `reserved_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `agent_id`, `title`, `brand`, `year`, `price`, `status`, `description`, `created_at`, `fuel_type`, `body_type`, `wilaya`, `mileage`, `transmission`, `drive_type`, `seats`, `color_ext`, `color_int`, `engine`, `power`, `consumption`, `delivery`, `duty_free`, `specs_text`, `contact_phone`, `reservation_status`, `reserved_until`) VALUES
(7, 82, 'Chery Tiggo 8 pro 2024', 'Chery', 2024, 31000000.00, 'available', '7-seater family SUV with a 1.6L turbo engine and automatic transmission. Features a 10.25-inch touchscreen, 360° camera, leather seats, dual-zone automatic climate control, lane assist, and blind spot monitor. Brand new, imported directly from China, duty-free. Available for delivery across Algeria', '2026-04-27 20:47:36', 'Essence', 'SUV', 'Alger', 0, 'Automatic', 'FWD', 7, 'White', 'Black', '1.5T Turbo', '156 HP', '7.4L/100Km', '1 month', 1, '', '', 'available', NULL),
(8, 82, 'MG ZS 2023', 'MG', 2023, 3800000.00, 'available', 'A modern and reliable compact SUV designed for daily driving. The MG ZS 2023 offers a comfortable interior, smooth performance, and practical features, making it a great choice for both city and highway use.', '2026-05-03 18:44:53', 'Essence', 'SUV', 'Alger', 0, 'Automatic', 'FWD', 5, 'White', 'Black', '1.5L Petrol\r', '118 HP\r', '6.5L/100km\r', 'Engine: 1.5L Petrol\r', 0, 'Engine: 1.5L Petrol\r\nPower: 118 HP\r\nTorque: 150 Nm\r\nTransmission: CVT Automatic\r\nConsumption: 6.5L/100km\r\nTop Speed: 170 km/h\r\n0-100 km/h: 12.5 sec\r\nFuel Tank: 48L\r\nWarranty: 5 years', NULL, 'reserved', '2026-05-24 12:30:37'),
(9, 92, 'GMW haval h6', 'Haval', 2026, 5800000.00, 'available', 'Modern SUV with premium design, advanced features and excellent comfort', '2026-05-16 19:54:15', 'Essence', 'SUV', 'Constantine', 0, 'Automatic', 'FWD', 5, 'Gray', 'Black', '1.5L Turbo\r', '169 HP\r', '7.5L/100km\r', 'Engine: 1.5L Turbo\r', 0, 'Engine: 1.5L Turbo\r\nPower: 169 HP\r\nConsumption: 7.5L/100km\r\nTransmission: Automatic\r\nDrive Type: FWD\r\nInterior: Premium Black\r\nFeatures: 360 Camera, Smart Screen, Sensors\r\nWarranty: 5 Years', NULL, 'available', NULL),
(10, 94, 'Livan X3 Pro', 'Livan', 2023, 2870000.00, 'available', 'the price includes customs clearance\r\nthe car is very clean and in excellent condition✅', '2026-05-18 11:54:01', 'Essence', 'SUV', 'Annaba', 14, 'Automatic', 'FWD', 4, 'Blue', 'Black', '1.5L 4-cylinder\r', '103 HP\r', '6.8L/100 km', 'Engine: 1.5L 4-cylinder\r', 0, 'Engine: 1.5L 4-cylinder\r\nPower: 103 HP\r\n Consumption: 6.8L/100 km', NULL, 'reserved', '2026-05-30 00:53:14'),
(11, 94, 'JAC JS4', 'JAC', 2025, 4200000.00, 'available', 'Brand new JAC JS4 without customs fees', '2026-05-27 14:46:21', 'Essence', 'SUV', 'Annaba', 0, 'Automatic', 'FWD', 5, 'Red', 'Black', '1.5T', '147 ', '7.2L/100km', '2 months', 0, '', NULL, 'available', NULL),
(12, 96, 'jetour dashing', 'jetour', 2025, 4980000.00, 'available', 'sporty design, premium interior,panoramic sunroof,touchscreen display', '2026-05-27 20:19:44', 'Essence', 'SUV', 'Djanet', 0, 'Automatic', 'FWD', 5, 'Gray', 'Black', '1.5 Turbo', '156 HP', '6.9L/100Km', '4-6 weeks', 0, '', NULL, 'available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `car_photos`
--

CREATE TABLE `car_photos` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_photos`
--

INSERT INTO `car_photos` (`id`, `car_id`, `photo_path`, `is_primary`) VALUES
(1, 1, '/uploads/cars/1_1776426864_0.jpeg', 1),
(2, 2, '/uploads/cars/2_1776455159_0.jpeg', 1),
(3, 2, '/uploads/cars/2_1776455159_1.jpeg', 0),
(4, 2, '/uploads/cars/2_1776455159_2.jpeg', 0),
(5, 2, '/uploads/cars/2_1776455159_3.jpeg', 0),
(6, 3, '/uploads/cars/3_1776597277_0.jpeg', 1),
(7, 3, '/uploads/cars/3_1776597277_1.jpeg', 0),
(8, 3, '/uploads/cars/3_1776597277_2.jpeg', 0),
(9, 4, '/uploads/cars/4_1776758472_0.jpeg', 1),
(10, 4, '/uploads/cars/4_1776758472_1.jpeg', 0),
(11, 4, '/uploads/cars/4_1776758472_2.jpeg', 0),
(12, 4, '/uploads/cars/4_1776758472_3.jpeg', 0),
(13, 5, '/uploads/cars/5_1777062196_0.jpeg', 1),
(17, 7, '/uploads/cars/7_1777322856_0.jpeg', 0),
(18, 7, '/uploads/cars/7_1777322856_1.jpeg', 0),
(20, 8, '/uploads/cars/8_1777833893_0.jpeg', 0),
(21, 8, '/uploads/cars/8_1777833893_1.jpeg', 0),
(22, 8, '/uploads/cars/8_1777833893_2.jpeg', 0),
(23, 8, '/uploads/cars/8_1777833893_3.jpeg', 0),
(24, 9, 'uploads/cars/9_1778961255_0.jpeg', 0),
(25, 9, 'uploads/cars/9_1778961255_1.jpeg', 0),
(26, 9, 'uploads/cars/9_1778961255_2.jpeg', 0),
(27, 10, 'uploads/cars/10_1779105241_0.jpeg', 0),
(28, 10, 'uploads/cars/10_1779105241_1.jpeg', 0),
(29, 10, 'uploads/cars/10_1779105241_2.jpeg', 0),
(30, 10, 'uploads/cars/10_1779105241_3.jpeg', 0),
(31, 10, 'uploads/cars/10_1779105241_4.jpeg', 0),
(32, 10, 'uploads/cars/10_1779105241_5.jpeg', 0),
(33, 10, 'uploads/cars/10_1779105241_6.jpeg', 0),
(34, 7, 'uploads/cars/car_7_1779471262_0.jpeg', 0),
(35, 11, 'uploads/cars/11_1779893181_0.jpeg', 0),
(36, 11, 'uploads/cars/11_1779893181_1.jpeg', 0),
(37, 11, 'uploads/cars/11_1779893181_2.jpeg', 0),
(38, 12, 'uploads/cars/12_1779913184_0.jpeg', 0),
(39, 12, 'uploads/cars/12_1779913184_1.jpeg', 0),
(40, 12, 'uploads/cars/12_1779913184_2.jpeg', 0),
(41, 12, 'uploads/cars/12_1779913184_3.jpeg', 0),
(42, 12, 'uploads/cars/12_1779913184_4.jpeg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `car_reservations`
--

CREATE TABLE `car_reservations` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `payment_method` enum('cheque','golden_card') NOT NULL,
  `payment_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','refused') DEFAULT 'pending',
  `visited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reserved_until` datetime DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_reservations`
--

INSERT INTO `car_reservations` (`id`, `car_id`, `client_id`, `agent_id`, `conversation_id`, `first_name`, `last_name`, `phone`, `payment_method`, `payment_file`, `status`, `visited`, `created_at`, `reserved_until`, `reminder_sent`) VALUES
(1, 7, 85, 82, 5, 'nadina', 'boumaza', '+213 0699542293', 'cheque', 'uploads/reservations/res_1778865405_693.jpg', 'accepted', 0, '2026-05-15 17:16:45', '2026-05-20 22:39:21', 1),
(2, 7, 80, 82, 2, 'ines', 'boumaza', '+213 0699542294', 'cheque', 'uploads/reservations/res_1778867023_235.jpg', 'pending', 0, '2026-05-15 17:43:43', NULL, 0),
(3, 7, 85, 82, 5, 'nadina', 'boumaza', '+213 0699542293', 'cheque', 'uploads/reservations/res_1778867580_488.jpg', 'pending', 0, '2026-05-15 17:53:00', NULL, 0),
(4, 7, 85, 82, 5, 'nadina', 'bmz', '+213 0699542293', 'cheque', 'uploads/reservations/res_1778872156_419.jpeg', 'pending', 0, '2026-05-15 19:09:16', NULL, 0),
(5, 7, 85, 82, 5, 'nadina', 'bmz', '+213 0699542293', 'cheque', 'uploads/reservations/res_1778872583_866.jpeg', 'refused', 0, '2026-05-15 19:16:23', NULL, 0),
(6, 7, 80, 82, 2, 'ines', 'boumaza', '+213 0699542294', 'golden_card', 'uploads/reservations/res_1778874468_227.txt', 'accepted', 0, '2026-05-15 19:47:48', '2026-05-19 21:57:49', 1),
(7, 9, 93, 92, 7, 'daniel', 'dani', '+213 0545321234', 'cheque', 'uploads/reservations/res_1778961965_806.png', 'accepted', 1, '2026-05-16 20:06:05', '2026-05-20 23:03:00', 0),
(8, 8, 93, 82, 8, 'daniel', 'dani', '+213 0545321234', 'cheque', 'uploads/reservations/res_1779272740_976.jpg', 'accepted', 1, '2026-05-20 10:25:40', '2026-05-24 12:30:37', 0),
(9, 10, 93, 94, 13, 'daniel', 'dani', '+213 0545321234', 'cheque', 'uploads/reservations/res_1779745321_110.jpeg', 'accepted', 0, '2026-05-25 21:42:01', '2026-05-30 00:53:14', 0),
(10, 12, 81, 96, 14, 'ines', 'boumaza', '+213 0699542295', 'cheque', 'uploads/reservations/res_1779913458_710.jpg', 'refused', 0, '2026-05-27 20:24:18', '2026-05-31 22:24:42', 0),
(11, 11, 81, 94, 17, 'ines', 'boumaza', '+213 0699542295', 'cheque', 'uploads/reservations/res_1779926486_316.jpg', 'pending', 0, '2026-05-28 00:01:26', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `car_reviews`
--

CREATE TABLE `car_reviews` (
  `id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `car_reviews`
--

INSERT INTO `car_reviews` (`id`, `car_id`, `user_id`, `rating`, `review_text`, `created_at`, `parent_id`) VALUES
(1, 7, 80, 4, 'i love this car', '2026-05-01 13:41:57', NULL),
(5, 7, 80, 3, 'graet', '2026-05-01 16:21:08', NULL),
(6, 7, 80, 4, 'super', '2026-05-01 16:21:42', NULL),
(7, 7, 80, 4, 'woow', '2026-05-01 16:31:08', NULL),
(8, 7, 80, 3, '🤍', '2026-05-01 16:38:29', NULL),
(35, 7, 93, 5, 'great value for the price', '2026-05-27 13:22:46', NULL),
(36, 8, 93, 5, 'this model is one of my favorites', '2026-05-27 13:24:13', NULL),
(37, 9, 85, 5, 'powerful performance and elegant design', '2026-05-27 13:32:55', NULL),
(38, 11, 95, 5, 'i really liked the design and the full option features of this car', '2026-05-27 19:08:13', NULL),
(39, 9, 95, 4, 'one of my favorite cars 🤩', '2026-05-27 19:09:43', NULL),
(40, 11, 81, 3, 'a bit expensive for me, but the alert features is really useful if you want to wait for a lower price', '2026-05-27 19:23:49', NULL),
(41, 12, 81, 5, 'this is what i want 🥰', '2026-05-27 20:21:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_deleted_notifications`
--

CREATE TABLE `client_deleted_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_type` varchar(50) NOT NULL,
  `comment_text` text NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_deleted_notifications`
--

INSERT INTO `client_deleted_notifications` (`id`, `user_id`, `comment_type`, `comment_text`, `deleted_at`, `is_read`) VALUES
(1, 80, 'car_review', 'fhjhyu', '2026-05-24 13:30:40', 0),
(2, 80, 'car_review', '', '2026-05-24 13:31:00', 0),
(3, 80, 'car_review', '', '2026-05-24 13:40:38', 0),
(4, 80, 'index_review', 'i love this site', '2026-05-24 13:40:58', 0),
(5, 80, 'index_review', 'oumaima', '2026-05-24 13:41:39', 0),
(6, 85, 'index_review', '💕💕', '2026-05-24 13:43:39', 0),
(7, 85, 'car_review', 'i love this car', '2026-05-24 14:16:03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `comment_reports`
--

CREATE TABLE `comment_reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `comment_type` enum('index_review','car_review','car_reply','dealer_review','dealer_reply') NOT NULL,
  `comment_id` int(11) NOT NULL,
  `comment_text` text DEFAULT NULL,
  `reason` varchar(200) NOT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('open','resolved') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_reports`
--

INSERT INTO `comment_reports` (`id`, `reporter_id`, `comment_type`, `comment_id`, `comment_text`, `reason`, `page_url`, `reported_at`, `status`) VALUES
(1, 85, 'index_review', 7, 'bouchra', 'Harassment', 'http://localhost/index.php', '2026-05-24 14:56:09', 'open'),
(2, 85, 'car_review', 30, 'hello', 'Harassment', 'http://localhost/listing.html?id=7', '2026-05-24 15:14:36', 'open'),
(3, 80, 'dealer_review', 1, 'a good and trustworthy agent, i received my car a few days ago', 'Other', 'http://localhost/dealers.php', '2026-05-24 15:48:21', 'open'),
(4, 80, 'dealer_review', 1, 'a good and trustworthy agent, i received my car a few days ago', 'False information', 'http://localhost/dealers.php', '2026-05-24 15:48:50', 'open'),
(5, 80, 'car_review', 8, '🤍', 'Harassment', 'http://localhost/listing.html?id=7', '2026-05-24 15:51:14', 'open'),
(6, 80, 'car_review', 30, 'hello', 'Spam', 'http://localhost/listing.html?id=7', '2026-05-24 15:58:54', 'open'),
(7, 80, 'car_review', 30, 'hello', 'Inappropriate content', 'http://localhost/listing.html?id=7', '2026-05-24 16:03:16', 'open'),
(8, 96, 'index_review', 12, 'i found the car i wanted in just a few minutes', 'Inappropriate content', 'http://localhost/index.php#home', '2026-05-27 20:44:10', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `car_id` varchar(100) DEFAULT NULL,
  `car_name` varchar(150) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_by_client` tinyint(1) DEFAULT 0,
  `deleted_by_agent` tinyint(1) DEFAULT 0,
  `blocked_by_client` tinyint(1) DEFAULT 0,
  `blocked_by_agent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `client_id`, `agent_id`, `car_id`, `car_name`, `last_message`, `last_message_at`, `created_at`, `deleted_by_client`, `deleted_by_agent`, `blocked_by_client`, `blocked_by_agent`) VALUES
(1, 80, 83, '5', 'byd', 'II', '2026-05-15 18:24:54', '2026-04-24 20:27:21', 0, 0, 0, 0),
(2, 80, 82, '7', 'Chery Tiggo 8 pro 2024', 'Reservation Request', '2026-05-15 19:47:48', '2026-04-28 07:50:37', 0, 0, 0, 0),
(3, 80, 82, '8', 'MG MG ZS 2023 2023', 'uuiiuyg', '2026-05-04 01:46:03', '2026-05-04 01:18:48', 0, 0, 0, 0),
(4, 82, 82, '7', 'Chery Tiggo 8 pro 2024', NULL, '2026-05-06 18:15:55', '2026-05-06 18:15:55', 0, 0, 0, 0),
(5, 85, 82, '7', 'Chery Tiggo 8 pro 2024', 'off', '2026-05-22 22:19:25', '2026-05-06 18:21:41', 0, 0, 0, 0),
(6, 85, 82, '0', 'General Inquiry', 'dealers', '2026-05-11 23:56:20', '2026-05-11 23:56:20', 0, 0, 0, 0),
(7, 93, 92, '9', 'GMW haval h6', 'Reservation Request', '2026-05-16 20:06:05', '2026-05-16 20:05:16', 0, 0, 0, 0),
(8, 93, 82, '8', 'MG ZS 2023', 'Reservation Request', '2026-05-20 10:25:40', '2026-05-20 10:22:08', 0, 0, 0, 0),
(9, 85, 92, '9', 'GMW haval h6', NULL, '2026-05-22 21:08:54', '2026-05-22 21:08:54', 0, 0, 0, 0),
(10, 85, 94, '10', 'Livan X3 Pro', NULL, '2026-05-22 21:10:13', '2026-05-22 21:10:13', 0, 0, 0, 0),
(11, 85, 82, '8', 'MG ZS 2023', NULL, '2026-05-22 22:43:22', '2026-05-22 22:43:22', 0, 0, 0, 0),
(12, 80, 92, '9', 'GMW haval h6', NULL, '2026-05-24 16:15:43', '2026-05-24 16:15:43', 0, 0, 0, 0),
(13, 93, 94, '10', 'Livan X3 Pro', 'hi', '2026-05-26 16:25:53', '2026-05-24 17:21:12', 0, 0, 0, 0),
(14, 81, 96, '12', 'jetour dashing', 'hi', '2026-05-27 20:36:37', '2026-05-27 20:21:04', 0, 0, 0, 0),
(15, 85, 96, '12', 'jetour dashing', NULL, '2026-05-27 20:27:06', '2026-05-27 20:27:06', 0, 0, 0, 0),
(16, 93, 96, '12', 'jetour dashing', NULL, '2026-05-27 23:29:18', '2026-05-27 23:29:18', 0, 0, 0, 0),
(17, 81, 94, '11', 'JAC JS4', 'Reservation Request', '2026-05-28 00:01:26', '2026-05-27 23:59:26', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `dealer_review_replies`
--

CREATE TABLE `dealer_review_replies` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `user_photo` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dealer_review_replies`
--

INSERT INTO `dealer_review_replies` (`id`, `review_id`, `user_id`, `user_name`, `user_photo`, `content`, `created_at`) VALUES
(2, 1, 80, 'ines bmz', 'uploads/photos/69f23ae87d980.jpg', 'hi', '2026-05-12 13:49:22');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_conversations`
--

CREATE TABLE `deleted_conversations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_conversations`
--

INSERT INTO `deleted_conversations` (`id`, `user_id`, `conversation_id`, `deleted_at`) VALUES
(1, 85, 6, '2026-05-12 00:18:40'),
(2, 82, 6, '2026-05-15 19:35:07'),
(3, 82, 3, '2026-05-15 19:35:11'),
(4, 82, 4, '2026-05-15 19:35:16'),
(5, 80, 2, '2026-05-22 22:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `car_id` varchar(100) NOT NULL,
  `car_name` varchar(150) DEFAULT NULL,
  `car_image` varchar(255) DEFAULT NULL,
  `car_price` varchar(50) DEFAULT NULL,
  `car_link` varchar(500) DEFAULT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `car_id`, `car_name`, `car_image`, `car_price`, `car_link`, `saved_at`) VALUES
(1, 79, '4', 'mg7', 'uploads/cars/4_1776758472_0.jpeg', '2,000,000', 'index.php#car-4', '2026-04-24 10:34:14'),
(6, 82, '7', 'Chery Tiggo 8 pro 2024', 'http://localhost/uploads/cars/7_1777322856_0.jpeg', '30,000,000', 'index.php#car-7', '2026-04-28 07:56:28'),
(10, 92, '8', 'MG ZS 2023', 'http://localhost//uploads/cars/8_1777833893_0.jpeg', '3,800,000', 'index.php#car-8', '2026-05-18 10:54:59'),
(13, 93, '9', 'GMW haval h6', 'http://localhost/uploads/cars/9_1778961255_0.jpeg', '5,800,000', 'index.php#car-9', '2026-05-27 13:49:46'),
(14, 93, '8', 'MG ZS 2023', 'http://localhost//uploads/cars/8_1777833893_0.jpeg', '3,800,000', 'index.php#car-8', '2026-05-27 13:49:47'),
(15, 93, '7', 'Chery Tiggo 8 pro 2024', 'http://localhost//uploads/cars/7_1777322856_0.jpeg', '31,000,000', 'index.php#car-7', '2026-05-27 13:49:50'),
(16, 85, '12', 'jetour dashing', 'http://localhost/uploads/cars/12_1779913184_0.jpeg', '4,980,000', 'index.php#car-12', '2026-05-27 23:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `free_trials`
--

CREATE TABLE `free_trials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `started_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered` tinyint(1) DEFAULT 0,
  `seen_at` datetime DEFAULT NULL,
  `message_type` enum('text','reservation') DEFAULT 'text',
  `reservation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `message`, `is_read`, `sent_at`, `delivered`, `seen_at`, `message_type`, `reservation_id`) VALUES
(1, 1, 80, 'salam', 1, '2026-04-24 21:49:14', 1, '2026-04-25 10:40:46', 'text', NULL),
(2, 1, 80, 'hi', 1, '2026-04-24 21:56:01', 1, '2026-04-25 10:40:46', 'text', NULL),
(3, 1, 80, 'hello', 1, '2026-04-24 22:24:11', 1, '2026-04-25 10:40:46', 'text', NULL),
(4, 1, 83, 'ouii', 1, '2026-04-25 09:27:42', 1, '2026-04-25 10:38:10', 'text', NULL),
(5, 2, 80, 'hiii', 1, '2026-04-28 07:50:45', 1, '2026-04-29 19:19:25', 'text', NULL),
(6, 2, 82, 'hello', 1, '2026-04-28 07:51:14', 1, '2026-04-28 08:51:15', 'text', NULL),
(7, 2, 80, 'uyt', 1, '2026-04-30 16:24:11', 1, '2026-05-04 02:16:49', 'text', NULL),
(8, 3, 80, 'hiiiiii', 1, '2026-05-04 01:18:48', 1, '2026-05-04 02:19:02', 'text', NULL),
(9, 3, 80, 'hello ines', 1, '2026-05-04 01:25:52', 1, '2026-05-04 02:26:06', 'text', NULL),
(10, 3, 80, 'bla bla bla', 1, '2026-05-04 01:31:31', 1, '2026-05-06 18:38:09', 'text', NULL),
(11, 3, 80, 'uuiiuyg', 1, '2026-05-04 01:46:03', 1, '2026-05-06 18:38:09', 'text', NULL),
(12, 5, 85, 'hi i\'m interested in the car you posted', 1, '2026-05-06 18:24:00', 1, '2026-05-06 22:52:42', 'text', NULL),
(13, 5, 85, 'hi', 1, '2026-05-06 21:51:13', 1, '2026-05-06 22:52:42', 'text', NULL),
(14, 5, 85, 'iii', 1, '2026-05-06 22:21:12', 1, '2026-05-06 23:21:28', 'text', NULL),
(15, 6, 85, 'dealers', 1, '2026-05-11 23:56:20', 1, '2026-05-15 19:21:38', 'text', NULL),
(16, 2, 80, 'iii', 1, '2026-05-15 13:53:32', 1, '2026-05-15 18:44:19', 'text', NULL),
(17, 5, 85, 'I have submitted a reservation request for this car. Please review and confirm.', 1, '2026-05-15 17:16:45', 1, '2026-05-15 18:17:29', 'text', NULL),
(18, 2, 80, 'reservation_request', 1, '2026-05-15 17:43:43', 1, '2026-05-15 18:44:19', 'reservation', 50),
(19, 5, 85, 'reservation_request', 1, '2026-05-15 17:53:00', 1, '2026-05-15 19:01:42', 'reservation', 51),
(20, 1, 80, 'II', 0, '2026-05-15 18:24:54', 1, NULL, 'text', NULL),
(21, 2, 80, 'UII', 1, '2026-05-15 18:25:05', 1, '2026-05-15 20:48:35', 'text', NULL),
(22, 5, 85, 'reservation_request', 1, '2026-05-15 19:09:16', 1, '2026-05-15 20:17:38', 'reservation', 54),
(23, 5, 85, 'reservation_request', 1, '2026-05-15 19:16:23', 1, '2026-05-15 20:17:38', 'reservation', 5),
(24, 2, 80, 'reservation_request', 1, '2026-05-15 19:47:48', 1, '2026-05-15 20:48:35', 'reservation', 6),
(25, 7, 93, 'hi', 1, '2026-05-16 20:05:21', 1, '2026-05-16 21:50:03', 'text', NULL),
(26, 7, 93, 'reservation_request', 1, '2026-05-16 20:06:05', 1, '2026-05-16 21:50:03', 'reservation', 7),
(27, 8, 93, 'salut .. ..', 1, '2026-05-20 10:22:17', 1, '2026-05-20 11:27:38', 'text', NULL),
(28, 8, 93, 'reservation_request', 1, '2026-05-20 10:25:40', 1, '2026-05-20 11:27:38', 'reservation', 8),
(29, 5, 85, 'off', 0, '2026-05-22 22:19:25', 1, NULL, 'text', NULL),
(30, 13, 93, 'reservation_request', 1, '2026-05-25 21:42:01', 1, '2026-05-26 16:34:17', 'reservation', 9),
(31, 13, 94, 'hi', 1, '2026-05-26 15:31:50', 1, '2026-05-26 17:13:22', 'text', NULL),
(32, 13, 94, 'hello', 1, '2026-05-26 16:05:01', 1, '2026-05-26 17:13:22', 'text', NULL),
(33, 13, 94, 'iii', 1, '2026-05-26 16:12:40', 1, '2026-05-26 17:13:22', 'text', NULL),
(34, 13, 93, 'yes', 0, '2026-05-26 16:20:36', 1, NULL, 'text', NULL),
(35, 13, 93, 'hi', 0, '2026-05-26 16:25:53', 1, NULL, 'text', NULL),
(36, 14, 81, 'reservation_request', 0, '2026-05-27 20:24:18', 0, NULL, 'reservation', 10),
(37, 14, 81, 'hi', 0, '2026-05-27 20:36:37', 1, NULL, 'text', NULL),
(38, 17, 81, 'reservation_request', 0, '2026-05-28 00:01:26', 0, NULL, 'reservation', 11);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','offer','alert') DEFAULT 'info',
  `related_id` int(11) DEFAULT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `sender_id`, `title`, `message`, `type`, `related_id`, `link`, `is_read`, `created_at`) VALUES
(1, 83, NULL, 'New message', 'You have a new message from ', 'info', NULL, NULL, 1, '2026-04-24 21:49:14'),
(2, 83, NULL, 'New message', 'You have a new message from ', 'info', NULL, NULL, 1, '2026-04-24 21:56:01'),
(3, 83, NULL, 'New message', 'You have a new message from ', 'info', NULL, NULL, 1, '2026-04-24 22:24:11'),
(4, 80, NULL, 'New message', 'You have a new message', 'info', NULL, NULL, 1, '2026-04-25 09:27:42'),
(6, 80, NULL, 'New message', 'You have a new message', 'info', NULL, NULL, 0, '2026-04-28 07:51:14'),
(16, 82, 85, 'nadina didou sent you a message', 'iii', '', NULL, 'profile.php?tab=chat&conv=5', 1, '2026-05-06 22:21:12'),
(17, 82, 85, 'nadina didou commented on your car', 'i love this car', '', NULL, 'listing.html?id=8#review-28', 1, '2026-05-06 22:38:36'),
(19, 82, 85, 'nadina didou commented on your car', 'hello', '', NULL, 'listing.html?id=7#review-30', 1, '2026-05-06 22:57:03'),
(24, 85, 82, 'ines bmz liked your review', 'ines bmz liked your review', '', NULL, 'listing.html?id=7#review-30', 1, '2026-05-08 23:20:49'),
(25, 80, 85, 'nadina didou replied to your review', 'ii', '', NULL, 'index.php#rev_7$scrollreply=1', 0, '2026-05-08 23:22:06'),
(26, 80, 82, 'ines bmz liked your review', 'oumaima', '', NULL, 'index.php#rev_1&scrollreply=like', 0, '2026-05-09 00:49:58'),
(27, 85, 82, 'ines bmz liked your review', '💕💕', '', NULL, 'index.php#rev_9&scrollreply=like', 1, '2026-05-09 00:51:50'),
(28, 82, 85, 'nadina didou liked your review', 'iiii', '', NULL, 'index.php?scrollreply=like#rev_10', 1, '2026-05-09 01:17:00'),
(29, 80, 82, 'ines bmz liked your review', 'bouchra', '', NULL, 'index.php?scrollreply=like#rev_7', 0, '2026-05-09 01:47:08'),
(30, 84, 82, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Pro بمبلغ 4900 DZD', 'info', 7, NULL, 0, '2026-05-09 20:51:26'),
(31, 82, NULL, 'Subscription Approved! ✅', 'Your subscription has been approved. You now have full access to China2DZ.', 'success', NULL, NULL, 1, '2026-05-10 14:06:30'),
(32, 84, 82, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Pro بمبلغ 4900 DZD', 'info', 8, NULL, 0, '2026-05-10 14:53:58'),
(33, 82, NULL, 'Subscription Approved! ✅', 'Your subscription has been approved. You now have full access to China2DZ.', 'success', NULL, NULL, 1, '2026-05-10 14:55:09'),
(35, 84, NULL, '🚩 New Dealer Report', 'A report was submitted against dealer: ines . Reason: Fake car listings', 'warning', NULL, NULL, 0, '2026-05-13 18:17:33'),
(36, 84, NULL, 'New Dealer Report', 'Report against dealer: ines  — Reason: Fake car listings', 'warning', NULL, NULL, 0, '2026-05-13 18:21:59'),
(37, 84, 85, 'New Dealer Report', 'Report against: ines  — Reason: Abusive or inappropriate behavior', 'warning', NULL, NULL, 0, '2026-05-13 18:30:36'),
(38, 84, 85, 'New Dealer Report', 'Report against: ines  — Reason: Wrong or misleading information', 'warning', NULL, NULL, 0, '2026-05-13 18:41:53'),
(39, 84, 80, 'New Dealer Report', 'Report against: hacen b — Reason: Other', 'warning', NULL, NULL, 0, '2026-05-13 20:06:09'),
(40, 86, NULL, 'Account Temporarily Suspended', 'Your account has been suspended for 3 days. Reason: ', 'warning', NULL, NULL, 0, '2026-05-13 20:06:38'),
(41, 84, 86, 'New Dealer Report', 'Report against: hacen b — Reason: Wrong or misleading information', 'warning', NULL, NULL, 0, '2026-05-13 21:42:53'),
(42, 86, NULL, 'Account Temporarily Suspended', 'Your account has been suspended for 3 days. Reason: ', 'warning', NULL, NULL, 0, '2026-05-13 21:43:23'),
(44, 83, 80, '🔔 New Car Alert from ines bmz', 'Any car', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-13 22:45:18'),
(45, 82, 80, '🔔 New Car Alert from ines bmz', 'Brand: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 1, '2026-05-13 22:53:48'),
(46, 83, 80, '🔔 New Car Alert from ines bmz', 'Brand: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-13 22:53:48'),
(47, 82, 80, 'ines bmz sent you a message', 'iii', '', NULL, 'profile.php?tab=chat&conv=2', 1, '2026-05-15 13:53:32'),
(48, 82, NULL, '🔒 New Reservation Request', 'nadina boumaza wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 17:16:45'),
(49, 82, 85, 'nadina bmz sent you a message', 'I have submitted a reservation request for this car. Please review and confirm.', '', NULL, 'profile.php?tab=chat&conv=5', 1, '2026-05-15 17:16:45'),
(50, 82, NULL, '🔒 New Reservation Request', 'ines boumaza wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 17:43:43'),
(51, 82, NULL, '🔒 New Reservation Request', 'nadina boumaza wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 17:53:00'),
(52, 83, 80, 'ines bmz sent you a message', 'II', '', NULL, 'profile.php?tab=chat&conv=1', 0, '2026-05-15 18:24:54'),
(53, 82, 80, 'ines bmz sent you a message', 'UII', '', NULL, 'profile.php?tab=chat&conv=2', 1, '2026-05-15 18:25:05'),
(54, 82, NULL, '🔒 New Reservation Request', 'nadina bmz wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 19:09:16'),
(55, 82, NULL, '🔒 New Reservation Request', 'nadina bmz wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 19:16:23'),
(56, 85, NULL, '❌ Reservation Refused', 'Unfortunately your reservation request was not accepted.', '', NULL, NULL, 1, '2026-05-15 19:36:28'),
(57, 82, NULL, '🔒 New Reservation Request', 'ines boumaza wants to reserve a car. Review and confirm in Client Requests.', '', NULL, NULL, 1, '2026-05-15 19:47:48'),
(58, 80, NULL, '🎉 Reservation Confirmed!', 'Your car reservation has been confirmed. You have 4 days to complete the process.', '', NULL, NULL, 0, '2026-05-15 19:57:49'),
(60, 84, 88, 'New Agent Registration', 'mohammed ben has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-15 22:19:03'),
(61, 84, 89, 'New Agent Registration', 'mohammed b has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-15 22:59:35'),
(62, 84, 90, 'New Agent Registration', 'aya b has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-16 10:51:28'),
(63, 84, 91, 'New Agent Registration', 'aya b has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-16 12:12:59'),
(64, 84, 92, 'New Agent Registration', 'oumaima ch has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-16 19:31:11'),
(65, 80, 92, '🚗 Car matching your alert is now available!', 'A new car matching your alert has been listed: GMW haval h6 (2026) — 5,800,000 DZD', 'offer', 9, 'listing.php?id=9', 1, '2026-05-16 19:54:15'),
(66, 80, 92, '🚗 Car matching your alert is now available!', 'A new car matching your alert has been listed: GMW haval h6 (2026) — 5,800,000 DZD', 'offer', 9, 'listing.php?id=9', 0, '2026-05-16 19:54:15'),
(67, 92, 93, 'رسالة جديدة من daniel dani', 'يريد التواصل معك بخصوص: GMW haval h6', '', NULL, 'profile.php?tab=chat&conv=7', 0, '2026-05-16 20:05:16'),
(68, 92, 93, 'daniel dani sent you a message', 'hi', '', NULL, 'profile.php?tab=chat&conv=7', 0, '2026-05-16 20:05:21'),
(69, 92, NULL, '🔒 New Reservation Request', 'daniel dani wants to reserve: GMW haval h6. Review and confirm in Client Requests.', '', NULL, NULL, 0, '2026-05-16 20:06:05'),
(70, 85, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 1, '2026-05-16 20:39:21'),
(71, 93, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 1, '2026-05-16 21:03:00'),
(72, 93, NULL, 'Visit Confirmed', 'The agent has confirmed your visit. Your car remains reserved.', 'success', NULL, 'profile.php?tab=tracking', 1, '2026-05-16 21:32:36'),
(73, 93, 92, 'Car Status Update: Order Confirmed in China', 'GMW haval h6 — Order Confirmed in China', 'info', NULL, 'profile.php?tab=tracking', 1, '2026-05-16 21:33:11'),
(74, 82, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 1, '2026-05-18 11:07:45'),
(75, 83, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(76, 88, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(77, 89, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(78, 90, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(79, 91, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(80, 92, 93, '🔔 New Car Alert from daniel dani', 'Search: livan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-18 11:07:45'),
(81, 84, 94, 'New Agent Registration', 'aymen fadli has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-18 11:27:48'),
(82, 93, 94, '🚗 Car matching your alert is now available!', 'A new car matching your alert has been listed: Livan X3 Pro (2023) — 2,870,000 DZD', 'offer', 10, 'listing.php?id=10', 1, '2026-05-18 11:54:01'),
(83, 82, 93, 'رسالة جديدة من daniel dani', 'يريد التواصل معك بخصوص: MG ZS 2023', '', NULL, 'profile.php?tab=chat&conv=8', 0, '2026-05-20 10:22:08'),
(84, 82, 93, 'daniel dani sent you a message', 'salut .. ..', '', NULL, 'profile.php?tab=chat&conv=8', 0, '2026-05-20 10:22:17'),
(85, 82, NULL, '🔒 New Reservation Request', 'daniel dani wants to reserve: MG ZS 2023. Review and confirm in Client Requests.', '', NULL, NULL, 0, '2026-05-20 10:25:40'),
(86, 82, NULL, 'Did the client visit? — Chery Tiggo 8 pro 2024', 'The 4-day window has passed for ines bmz. Please update the visit status.', '', NULL, 'agent_dashboard.php', 0, '2026-05-20 10:27:44'),
(87, 93, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 0, '2026-05-20 10:28:27'),
(88, 93, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 0, '2026-05-20 10:30:37'),
(89, 93, 82, 'Car Status Update: Order Confirmed in China', 'MG ZS 2023 — Order Confirmed in China', 'info', NULL, 'profile.php?tab=tracking', 0, '2026-05-20 10:34:44'),
(90, 93, 82, 'Car Status Update: Shipped — On the Way', 'MG ZS 2023 — Shipped — On the Way', 'info', NULL, 'profile.php?tab=tracking', 0, '2026-05-20 10:35:27'),
(91, 82, NULL, 'Did the client visit? — Chery Tiggo 8 pro 2024', 'The 4-day window has passed for nadina bmz. Please update the visit status.', '', NULL, 'agent_dashboard.php', 0, '2026-05-22 16:51:00'),
(92, 92, 85, 'رسالة جديدة من nadina bmz', 'يريد التواصل معك بخصوص: GMW haval h6', '', NULL, 'profile.php?tab=chat&conv=9', 0, '2026-05-22 21:08:54'),
(94, 82, 85, 'nadina bmz sent you a message', 'off', '', NULL, 'profile.php?tab=chat&conv=5', 0, '2026-05-22 22:19:25'),
(95, 82, 85, 'رسالة جديدة من nadina bmz', 'يريد التواصل معك بخصوص: MG ZS 2023', '', NULL, 'profile.php?tab=chat&conv=11', 0, '2026-05-22 22:43:22'),
(102, 85, NULL, 'Comment Removed', 'Your comment has been removed by the admin: \"i love this car\"', 'warning', NULL, NULL, 1, '2026-05-24 14:16:03'),
(103, 84, 85, 'Comment Reported', 'nadina bmz reported a Index review: \"bouchra\" — Reason: Harassment', 'warning', NULL, NULL, 0, '2026-05-24 14:56:09'),
(104, 84, 85, 'Reviews reported', 'nadina bmz reported a Car review: \"hello\" — Reason: Harassment', 'warning', NULL, NULL, 0, '2026-05-24 15:14:36'),
(105, 84, 80, 'Reviews reported', 'ines bmz reported a review by nadina bmz: \"a good and trustworthy agent, i received my car a few days ago\" — Reason: Other', 'warning', NULL, NULL, 0, '2026-05-24 15:48:21'),
(106, 84, 80, 'Reviews reported', 'ines bmz reported a review by nadina bmz: \"a good and trustworthy agent, i received my car a few days ago\" — Reason: False information', 'warning', NULL, NULL, 0, '2026-05-24 15:48:50'),
(107, 84, 80, 'Reviews reported', 'ines bmz reported a review: \"🤍\" — Reason: Harassment', 'warning', NULL, NULL, 0, '2026-05-24 15:51:14'),
(108, 84, 80, 'Reviews reported', 'ines bmz reported a review: \"hello\" — Reason: Spam', 'warning', NULL, NULL, 0, '2026-05-24 15:58:54'),
(109, 84, 80, 'Reviews reported', 'ines bmz reported a review: \"hello\" — Reason: Inappropriate content', 'warning', NULL, NULL, 0, '2026-05-24 16:03:16'),
(110, 92, 80, 'رسالة جديدة من ines bmz', 'يريد التواصل معك بخصوص: GMW haval h6', '', NULL, 'profile.php?tab=chat&conv=12', 0, '2026-05-24 16:15:43'),
(112, 94, NULL, '🔒 New Reservation Request', 'daniel dani wants to reserve: Livan X3 Pro. Review and confirm in Client Requests.', '', NULL, NULL, 0, '2026-05-25 21:42:01'),
(113, 84, 94, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Business بمبلغ 9900 DZD', 'info', 16, NULL, 0, '2026-05-25 21:45:58'),
(114, 84, 94, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Business بمبلغ 17900 DZD', 'info', 17, NULL, 0, '2026-05-25 22:36:39'),
(115, 84, 94, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Pro بمبلغ 22000 DZD', 'info', 18, NULL, 0, '2026-05-25 22:46:20'),
(116, 94, NULL, 'Subscription Approved! ✅', 'Your subscription has been approved. You now have full access to China2DZ.', 'success', NULL, NULL, 0, '2026-05-25 22:48:14'),
(117, 93, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 0, '2026-05-25 22:53:14'),
(118, 93, 94, 'sent you a messageaymen fadli', 'hi', '', NULL, 'profile.php?tab=chat&conv=13', 0, '2026-05-26 15:31:50'),
(119, 93, 94, 'sent you a messageaymen fadli', 'hello', '', NULL, 'profile.php?tab=chat&conv=13', 0, '2026-05-26 16:05:01'),
(120, 93, 94, 'sent you a messageaymen fadli', 'iii', '', NULL, 'profile.php?tab=chat&conv=13', 0, '2026-05-26 16:12:40'),
(121, 94, 93, 'daniel dani sent you a message', 'yes', '', NULL, 'profile.php?tab=chat&conv=13', 0, '2026-05-26 16:20:36'),
(122, 94, 93, 'daniel dani sent you a message', 'hi', '', NULL, 'profile.php?tab=chat&conv=13', 0, '2026-05-26 16:25:53'),
(123, 82, 93, 'daniel dani commented on your car', 'great value for the price', '', NULL, 'listing.html?id=7#review-35', 0, '2026-05-27 13:22:46'),
(124, 82, 93, 'daniel dani commented on your car', 'this model is one of my favorites', '', NULL, 'listing.html?id=8#review-36', 0, '2026-05-27 13:24:13'),
(125, 92, 85, 'nadina bmz commented on your car', 'powerful performance and elegant design', '', NULL, 'listing.html?id=9#review-37', 0, '2026-05-27 13:32:55'),
(126, 82, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(127, 83, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(128, 88, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(129, 89, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(130, 90, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(131, 91, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(132, 92, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(133, 94, 93, '🔔 New Car Alert from daniel dani', 'Search: jac', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 14:33:42'),
(134, 93, 94, '🚗 Car matching your alert is now available!', 'A new car matching your alert has been listed: JAC JS4 (2025) — 4,200,000 DZD', 'alert', 11, 'index.php?highlight=11', 1, '2026-05-27 14:46:21'),
(135, 94, 95, 'foufa foufa commented on your car', 'i really liked the design and the full option features of this car', '', NULL, 'listing.html?id=11#review-38', 0, '2026-05-27 19:08:13'),
(136, 92, 95, 'foufa foufa commented on your car', 'one of my favorite cars 🤩', '', NULL, 'listing.html?id=9#review-39', 0, '2026-05-27 19:09:43'),
(137, 94, 81, 'ines bmz commented on your car', 'a bit expensive for me, but the alert features is really useful if you want to wait for a lower price', '', NULL, 'listing.html?id=11#review-40', 0, '2026-05-27 19:23:49'),
(138, 84, 92, 'طلب اشتراك جديد', 'الوكيل   طلب اشتراك Pro بمبلغ 4900 DZD', 'info', 19, NULL, 0, '2026-05-27 19:29:09'),
(139, 92, NULL, 'Subscription Approved! ✅', 'Your subscription has been approved. You now have full access to China2DZ.', 'success', NULL, NULL, 0, '2026-05-27 19:29:27'),
(140, 82, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(141, 83, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(142, 88, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(143, 89, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(144, 90, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(145, 91, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(146, 92, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(147, 94, 81, '🔔 New Car Alert from ines bmz', 'Search: jetour', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 19:53:44'),
(148, 84, 96, 'New Agent Registration', 'ayouch boumaza has registered and is awaiting approval.', 'info', NULL, NULL, 0, '2026-05-27 20:10:23'),
(149, 81, 96, '🚗 Car matching your alert is now available!', 'A new car matching your alert has been listed: jetour dashing (2025) — 4,980,000 DZD', 'alert', 12, 'listing.html?id=12', 1, '2026-05-27 20:19:44'),
(150, 96, 81, 'رسالة جديدة من ines bmz', 'يريد التواصل معك بخصوص: jetour dashing', '', NULL, 'profile.php?tab=chat&conv=14', 0, '2026-05-27 20:21:04'),
(151, 96, 81, 'ines bmz commented on your car', 'this is what i want 🥰', '', NULL, 'listing.html?id=12#review-41', 0, '2026-05-27 20:21:41'),
(152, 96, NULL, '🔒 New Reservation Request', 'ines boumaza wants to reserve: jetour dashing. Review and confirm in Client Requests.', '', NULL, NULL, 0, '2026-05-27 20:24:18'),
(153, 81, NULL, 'Reservation Confirmed', 'Your reservation has been confirmed. You have 4 days to visit the agent and complete the process.', '', NULL, 'profile.php?tab=tracking', 0, '2026-05-27 20:24:42'),
(154, 96, 85, 'رسالة جديدة من nadina bmz', 'يريد التواصل معك بخصوص: jetour dashing', '', NULL, 'profile.php?tab=chat&conv=15', 0, '2026-05-27 20:27:06'),
(155, 81, NULL, 'Reservation Cancelled', 'Your reservation has been cancelled and the car is now available again.', '', NULL, 'profile.php?tab=tracking', 0, '2026-05-27 20:28:14'),
(156, 96, 81, 'ines bmz sent you a message', 'hi', '', NULL, 'profile.php?tab=chat&conv=14', 0, '2026-05-27 20:36:37'),
(157, 84, 96, 'Reviews reported', 'ayouch boumaza reported a review by nadina bmz: \"i found the car i wanted in just a few minutes\" — Reason: Inappropriate content', 'warning', NULL, NULL, 0, '2026-05-27 20:44:10'),
(158, 96, 93, 'new message fromdaniel dani', 'wanna talks with you aboutjetour dashing', '', NULL, 'profile.php?tab=chat&conv=16', 0, '2026-05-27 23:29:18'),
(159, 82, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(160, 83, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(161, 88, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(162, 89, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(163, 90, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(164, 91, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(165, 92, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(166, 94, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(167, 96, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:36'),
(168, 82, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(169, 83, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(170, 88, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(171, 89, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(172, 90, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(173, 91, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(174, 92, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(175, 94, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(176, 96, 85, '🔔 New Car Alert from nadina bmz', 'Search: changan', 'offer', NULL, 'agent_dashboard.php?tab=alerts', 0, '2026-05-27 23:48:47'),
(177, 94, 81, 'new message fromines bmz', 'wanna talks with you aboutJAC JS4', '', NULL, 'profile.php?tab=chat&conv=17', 0, '2026-05-27 23:59:26'),
(178, 94, NULL, '🔒 New Reservation Request', 'ines boumaza wants to reserve: JAC JS4. Review and confirm in Client Requests.', '', NULL, NULL, 0, '2026-05-28 00:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `method` enum('whatsapp','email') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `code`, `method`, `expires_at`, `used`) VALUES
(78, 80, '625896', 'email', '2026-04-24 13:52:06', 1),
(79, 81, '263448', 'email', '2026-04-24 14:12:07', 1),
(80, 82, '823270', 'email', '2026-04-24 17:43:05', 1),
(81, 83, '631704', 'email', '2026-04-24 20:43:18', 1),
(82, 85, '538220', 'email', '2026-05-06 19:30:58', 1),
(83, 86, '625346', 'email', '2026-05-10 17:26:16', 1),
(85, 88, '482957', 'email', '2026-05-15 23:29:05', 1),
(86, 89, '992737', 'email', '2026-05-16 00:09:37', 1),
(87, 90, '460609', 'email', '2026-05-16 12:01:29', 1),
(88, 91, '150239', 'email', '2026-05-16 13:23:02', 1),
(89, 92, '728300', 'email', '2026-05-16 20:41:14', 1),
(90, 93, '664886', 'email', '2026-05-16 20:49:08', 1),
(91, 94, '560509', 'email', '2026-05-18 12:37:52', 1),
(92, 95, '335011', 'email', '2026-05-27 20:09:35', 1),
(93, 96, '473805', 'email', '2026-05-27 21:20:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `rating`, `content`, `created_at`) VALUES
(4, 80, 5, 'great experience ❤', '2026-04-30 23:59:37'),
(5, 80, 3, 'super', '2026-05-01 00:21:16'),
(11, 93, 5, 'Great website,very easy to use', '2026-05-27 14:20:36'),
(12, 85, 4, 'i found the car i wanted in just a few minutes', '2026-05-27 14:29:12'),
(13, 95, 5, 'One of the best car websites I’ve used. The alert feature is super helpful and easy to use.', '2026-05-27 20:04:31'),
(14, 81, 4, 'Excellent service and smooth experience the alert notification system is a great idea', '2026-05-27 20:20:39');

-- --------------------------------------------------------

--
-- Table structure for table `review_likes`
--

CREATE TABLE `review_likes` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_likes`
--

INSERT INTO `review_likes` (`id`, `review_id`, `user_id`) VALUES
(2, 1, 80),
(3, 1, 82),
(11, 1, 85),
(47, 5, 85);

-- --------------------------------------------------------

--
-- Table structure for table `review_likes_index`
--

CREATE TABLE `review_likes_index` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_likes_index`
--

INSERT INTO `review_likes_index` (`id`, `review_id`, `user_id`) VALUES
(17, 4, 85),
(15, 5, 82);

-- --------------------------------------------------------

--
-- Table structure for table `review_replies`
--

CREATE TABLE `review_replies` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipment_tracking`
--

CREATE TABLE `shipment_tracking` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL,
  `stage` varchar(50) NOT NULL DEFAULT 'purchased',
  `stage_label` varchar(100) NOT NULL,
  `stage_note` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipment_tracking`
--

INSERT INTO `shipment_tracking` (`id`, `reservation_id`, `car_id`, `client_id`, `agent_id`, `stage`, `stage_label`, `stage_note`, `updated_at`, `created_at`) VALUES
(1, 7, 9, 93, 92, 'purchased', 'Order Confirmed in China', '', '2026-05-16 21:33:11', '2026-05-16 21:33:11'),
(2, 8, 8, 93, 82, 'shipped', 'Shipped — On the Way', '', '2026-05-20 10:35:27', '2026-05-20 10:34:44');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'DZD',
  `payment_method` varchar(100) DEFAULT NULL,
  `payment_reference` varchar(200) DEFAULT NULL,
  `proof_file` varchar(255) DEFAULT '',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `test_started_at` datetime DEFAULT NULL,
  `plan` varchar(50) DEFAULT 'Pro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `amount`, `currency`, `payment_method`, `payment_reference`, `proof_file`, `start_date`, `end_date`, `status`, `admin_note`, `created_at`, `test_started_at`, `plan`) VALUES
(2, 83, 1500.00, 'DZD', 'CCP', '12345', 'uploads/payments/pay_83_1777059956.jpg', NULL, NULL, 'pending', NULL, '2026-04-24 19:45:56', NULL, 'Pro'),
(3, 83, 1500.00, 'DZD', 'CCP', '12345', 'uploads/payments/pay_83_1777059966.jpg', NULL, NULL, 'pending', NULL, '2026-04-24 19:46:06', NULL, 'Pro'),
(4, 82, 1500.00, 'DZD', 'CCP', '1234', 'uploads/payments/pay_82_1777152264.jpeg', NULL, NULL, 'pending', NULL, '2026-04-25 21:24:24', NULL, 'Pro'),
(5, 82, 4900.00, 'DZD', 'CCP', '123', 'uploads/payments/pay_82_1778358356.jpeg', NULL, NULL, 'pending', NULL, '2026-05-09 20:25:56', NULL, 'Pro'),
(6, 82, 4900.00, 'DZD', 'CCP', '1234', 'uploads/payments/pay_82_1778358612.jpeg', NULL, NULL, 'pending', NULL, '2026-05-09 20:30:12', NULL, 'Pro'),
(7, 82, 4900.00, 'DZD', 'CCP', '1234', 'uploads/payments/pay_82_1778359886.jpeg', '2026-05-10', '2026-05-10', 'approved', NULL, '2026-05-09 20:51:26', NULL, 'Pro'),
(8, 82, 4900.00, 'DZD', 'CCP', '123', 'uploads/payments/pay_82_1778424838.jpeg', '2026-05-10', '2026-06-09', 'approved', NULL, '2026-05-10 14:53:58', NULL, 'Pro'),
(10, 86, 0.00, 'DZD', NULL, NULL, '', '2026-05-10', '2026-05-17', 'approved', NULL, '2026-05-10 17:24:21', NULL, 'Pro'),
(11, 88, 0.00, 'DZD', NULL, NULL, '', '2026-05-15', '2026-05-22', 'approved', NULL, '2026-05-15 22:20:36', NULL, 'Trial'),
(12, 89, 0.00, 'DZD', NULL, NULL, '', '2026-05-16', '2026-05-23', 'approved', NULL, '2026-05-15 23:01:51', NULL, 'Trial'),
(13, 90, 0.00, 'DZD', NULL, NULL, '', '2026-05-16', '2026-05-23', 'approved', NULL, '2026-05-16 10:53:19', NULL, 'Trial'),
(14, 92, 0.00, 'DZD', NULL, NULL, '', '2026-05-16', '2026-05-23', 'approved', NULL, '2026-05-16 19:33:00', NULL, 'Trial'),
(15, 94, 0.00, 'DZD', NULL, NULL, '', '2026-05-18', '2026-05-25', 'approved', NULL, '2026-05-18 11:31:19', NULL, 'Trial'),
(16, 94, 9900.00, 'DZD', 'CCP', '123', 'uploads/payments/pay_94_1779745558.jpeg', NULL, NULL, 'pending', NULL, '2026-05-25 21:45:58', NULL, 'Business'),
(17, 94, 17900.00, 'DZD', 'CCP', '123', 'uploads/payments/pay_94_1779748599.jpeg', NULL, NULL, 'pending', NULL, '2026-05-25 22:36:39', NULL, 'Business'),
(18, 94, 22000.00, 'DZD', 'CCP', '1234', 'uploads/payments/pay_94_1779749180.jpeg', '2026-05-25', '2026-11-21', 'approved', NULL, '2026-05-25 22:46:20', NULL, 'Pro'),
(19, 92, 4900.00, 'DZD', 'CCP', '12345', 'uploads/payments/pay_92_1779910149.jpeg', '2026-05-27', '2026-06-26', 'approved', NULL, '2026-05-27 19:29:09', NULL, 'Pro'),
(20, 96, 0.00, 'DZD', NULL, NULL, '', '2026-05-27', '2026-06-03', 'approved', NULL, '2026-05-27 20:11:18', NULL, 'Trial');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` enum('client','agent','admin') NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT '',
  `profile_photo` varchar(500) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected','suspended','blocked') DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `reset_code` varchar(10) DEFAULT NULL,
  `reset_exp` datetime DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `wilaya` varchar(100) DEFAULT NULL,
  `trial_started_at` datetime DEFAULT NULL,
  `trial_used` tinyint(1) DEFAULT 0,
  `trial_refused` tinyint(1) DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  `block_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `first_name`, `last_name`, `full_name`, `profile_photo`, `email`, `phone`, `password_hash`, `is_verified`, `created_at`, `status`, `last_seen`, `is_online`, `reset_code`, `reset_exp`, `bio`, `wilaya`, `trial_started_at`, `trial_used`, `trial_refused`, `blocked_until`, `block_reason`) VALUES
(80, 'client', 'ines', 'bmz', '', 'uploads/photos/69f23ae87d980.jpg', 'inouinou346@gmail.com', '+213 0699542294', '$2y$10$cS1kYzKJr/XxCnuplBlfvuGB9ahhpztoepfwSgzqVtpThI/abaEUS', 1, '2026-04-24 12:42:03', 'pending', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(81, 'client', 'ines', 'bmz', '', 'uploads/photos/client_81.jpg', 'chinadz563@gmail.com', '+213 0699542295', '$2y$10$CwANA.Fxhs0wdiCxHjZDt.Cb/XMWuv0G3S5xwL3jlavp6ed/j..py', 1, '2026-04-24 13:02:04', 'pending', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(82, 'agent', 'ines', '', 'ines', 'uploads/photos/69ff5688dc9c1.jpeg', 'nuussa7@gmail.com', '0699542293', '$2y$10$6SF5IX4uX3bclZ5F1D4Oa.VezDvez7O6GB/ikuP8U1iOiGlkObfcu', 1, '2026-04-24 16:33:04', 'approved', NULL, 1, NULL, NULL, '', 'Algiers', '2026-05-09 19:40:09', 1, 0, NULL, NULL),
(83, 'agent', 'ines', 'boumaza', '', NULL, 'noussa21bmz@gmail.com', '+213 0654236596', '$2y$10$HrC5AgXKfzmlAgifWTxIUO5sLnbSM.w0mTAtx9ZKWq0nkc24/0iw2', 1, '2026-04-24 19:33:16', 'approved', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(84, 'admin', 'Admin', 'Admin', '', NULL, 'admin@china2dz.com', '0000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '2026-04-25 21:38:46', 'approved', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(85, 'client', 'nadina', 'bmz', 'nadina bmz', 'uploads/photos/client_85.jpeg', 'nadinebmz13@gmail.com', '+213 0699542293', '$2y$10$CZKGJV1Kk6f6TM6ZXbCQfe6KrXmqFxe.q1aTLL5bRsnG9LvsMeF4S', 1, '2026-05-06 18:20:54', 'pending', NULL, 0, '109357', '2026-05-09 17:50:14', 'informaticienne', 'Annaba', NULL, 0, 0, NULL, NULL),
(86, 'agent', 'hacen', 'b', 'hacen b', 'uploads/photos/6a00c132b0ff0.jpeg', 'hacenb05@gmail.com', '+213 0799542293', '$2y$10$M5XujGGao27Zb3nmB3JcFubbdwbhT35eGReNFOv5vUoTl5P3mpkbK', 1, '2026-05-10 16:16:12', '', NULL, 0, NULL, NULL, '', 'Skikda', NULL, 0, 0, NULL, NULL),
(87, 'agent', 'mohammed', 'ben', '', 'uploads/photos/agent_87.jpeg', 'inesbmz970@gmail.com', '+213 0756452345', '$2y$10$ZNZ7EM8qpTzBghfhoskqcez0JxhByY/pLRrpDU6j4JOZmM.HTTHEa', 1, '2026-05-15 21:41:38', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(88, 'agent', 'mohammed', 'ben', '', 'uploads/photos/agent_88.jpeg', 'belf2669@gmail.com', '+213 0798654532', '$2y$10$QGy4/vZkt0uNloCPWmntjO4ROG46W5cFRIVTBmlXPSN9CzXnguvhS', 1, '2026-05-15 22:19:03', 'approved', NULL, 0, NULL, NULL, NULL, NULL, '2026-05-15 23:20:36', 1, 0, NULL, NULL),
(89, 'agent', 'mohammed', 'b', '', 'uploads/photos/agent_89.jpeg', 'noussabmz21@gmail.com', '+213 0754236595', '$2y$10$iTk.iyOKu8zj7v2D632mpeVc59keQGBrTDWuuhoJKkVjMpD03lsJO', 1, '2026-05-15 22:59:35', 'approved', NULL, 0, NULL, NULL, NULL, NULL, '2026-05-16 00:01:51', 1, 0, NULL, NULL),
(90, 'agent', 'aya', 'b', '', 'uploads/photos/agent_90.jpeg', 'nadinedidou87@gmail.com', '+213 0756453212', '$2y$10$pfXSCZdRb.ttMDT3q2WmNO5Ql.oiWxx6hm5uEa5fHRBzc4kCf5Rvm', 1, '2026-05-16 10:51:28', 'approved', NULL, 0, NULL, NULL, NULL, NULL, '2026-05-16 11:53:19', 1, 0, NULL, NULL),
(91, 'agent', 'aya', 'b', '', 'uploads/photos/agent_91.jpeg', 'ayab68072@gmail.com', '+213 0786543423', '$2y$10$5LHYsN1EHN80SDC7W3zr7.DQeJ5ZtlAcGMr7h01oiqjDQMcsFGM7.', 1, '2026-05-16 12:12:59', 'approved', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(92, 'agent', 'oumaima', 'ch', 'oumaima ch', NULL, 'cheraitoumaima5@gmail.com', '+213 0699654323', '$2y$10$NfDORTgp26R8602cZmxureASVo.5f4gR4GfwgnyKuyR0I4Z2ZWWqi', 1, '2026-05-16 19:31:11', 'approved', NULL, 1, NULL, NULL, '', 'Guelma', '2026-05-16 20:33:00', 1, 0, NULL, NULL),
(93, 'client', 'daniel', 'dani', '', 'uploads/photos/client_93.jpeg', 'cheraitoumaima0@gmail.com', '+213 0545321234', '$2y$10$3tA1MYOJT8BRsrkRpHmgKeVztA2T2Bm8rlDsYzdOsAZi9CNIaPgCm', 1, '2026-05-16 19:38:56', '', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(94, 'agent', 'aymen', 'fadli', 'aymen fadli', 'uploads/photos/agent_94.jpeg', 'hamicisameh@gmail.com', '+213 0544342312', '$2y$10$82QrrD0AN3qClaNOvQj3he83hNim6Pw4WXjChG0Fv.gQV2mHFz0fm', 1, '2026-05-18 11:27:48', 'approved', NULL, 1, NULL, NULL, '', 'Annaba', '2026-05-18 12:31:19', 1, 0, NULL, NULL),
(95, 'client', 'foufa', 'foufa', '', 'uploads/photos/client_95.jpeg', 'f50089432@gmail.com', '+213 0765432312', '$2y$10$vxsZbUJZyeBzVwIh/1Vy8epBf0iIliN5Ym7RCu7OGBgDCV/hrhGhe', 1, '2026-05-27 18:59:22', '', NULL, 0, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL),
(96, 'agent', 'ayouch', 'boumaza', 'ayouch boumaza', 'uploads/photos/agent_96.jpeg', 'nurseaya926@gmail.com', '+213 0699435433', '$2y$10$18XQenF3KBGffURC6Fy7qeLuZ0BQHWHARwlhl1/svJrSAlfRkQGPS', 1, '2026-05-27 20:10:23', 'approved', NULL, 1, NULL, NULL, '', 'Djanet', '2026-05-27 21:11:18', 1, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `expires_at`, `created_at`) VALUES
(1, 73, '68a6b1d6eecdaa5eee6bee3d49acae2d0897b95ec4448f28049ebe1b32d15f1c_73_1777023153', '2026-05-24 11:32:33', '2026-04-24 09:32:33'),
(2, 74, 'cccf10b38e56af75e507ed998edc10230cfdf3ef471ff1f90c9f7539c22636bf_74_1777023338', '2026-05-24 11:35:38', '2026-04-24 09:35:38'),
(3, 75, 'c78d6d052bbc48a7a81d60fdfe79dc67e44c9327e8b0e85045588f12960c0b81_75_1777024652', '2026-05-24 11:57:32', '2026-04-24 09:57:32'),
(4, 76, 'f4a9c426cae73631d5aa37bc14ff691b415f756d0be36d9567e0962cd1d9238d_76_1777025345', '2026-05-24 12:09:05', '2026-04-24 10:09:05'),
(5, 77, '3a18c92ae3d95024fd87726bbd7e6defda7357fb7ef6cb687ce060e21b47850d_77_1777025614', '2026-05-24 12:13:34', '2026-04-24 10:13:34'),
(6, 78, '66dda6fe9f1c2ab2715569ba83e0c8e510a33cf4cc9d484b5b25ef55c30e5925_78_1777026081', '2026-05-24 12:21:21', '2026-04-24 10:21:21'),
(10, 81, 'f3cb0728abb1c9d19e6776dd97dcd6460336f0dad2a94917bf4102e5082adc9c_81_1777035754', '2026-05-24 15:02:34', '2026-04-24 13:02:34'),
(12, 24, 'f5a0ac250e6947d9c9e04db77db121e6141815ccd27105a576f84fe3aa2cb5ef_24_1777048461', '2026-05-24 18:34:21', '2026-04-24 16:34:21'),
(13, 83, '95782d9ade90897e28e6ff5db946b14534df4c1c1fe19aaef3a24d3340a6f19d_83_1777059231', '2026-05-24 21:33:51', '2026-04-24 19:33:51'),
(14, 24, 'c38b418ee5e23df1ae61459ff51c272aba070189edf2333a8d4b5555f33b69a4_24_1777059312', '2026-05-24 21:35:12', '2026-04-24 19:35:12'),
(20, 84, 'cedf8a559522f7f5435995248b13681b8dca757a6394133cf7b13a7e4f8d1625_84_1777153893', '2026-05-25 23:51:33', '2026-04-25 21:51:33'),
(53, 84, '7c4e7b1c402893ba0c6ee8c0a34b021e8e0f2a552b577b0cd3fd639dc335f909_84_1777979645', '2026-06-04 13:14:05', '2026-05-05 11:14:05'),
(54, 84, 'b0d22582e90dbc00cce148e3fb0f22c950b7518da125a03e9546e82865d6f9d4_84_1777979646', '2026-06-04 13:14:06', '2026-05-05 11:14:06'),
(75, 85, 'c40b39bb0a5e3b1d8cc972c6ce7d37ccfce9998e99f6a9fe3ec1dc15d2f58c1f_85_1778343517', '2026-06-08 18:18:37', '2026-05-09 16:18:37'),
(80, 84, '3143b260b6a1393b17d7477f45e9eb5ccba8098e65481b3a326fff62822b7046_84_1778356057', '2026-06-08 21:47:37', '2026-05-09 19:47:37'),
(82, 82, 'f3135c02ac75098b255b3b3cd6118b7e65d9d684bfedceffdf55eb9763248f55_82_1778356340', '2026-06-08 21:52:20', '2026-05-09 19:52:20'),
(114, 82, 'bc20b11f2ae54ef2cd8c71447593e9b0bc0c97c90da51040e9a01ab27a65ec7f_82_1778691060', '2026-06-12 18:51:00', '2026-05-13 16:51:00'),
(120, 84, '1e2e8e6d9997b9d8147f4fb21426da2431a44a351e5d4fd616ca0beee9210b54_84_1778697083', '2026-06-12 20:31:23', '2026-05-13 18:31:23'),
(121, 84, '7002ca68c9f0d9b0c0bc1efaf383b67c32a5f0d69400610bf38ce78b20d5e939_84_1778697742', '2026-06-12 20:42:22', '2026-05-13 18:42:22'),
(122, 84, '3832e1a16f4eedecab2381a44f6c5908ebb5f09ae6759fdd9cc2df9cfc50912d_84_1778699120', '2026-06-12 21:05:20', '2026-05-13 19:05:20'),
(123, 84, '35f3630458c420eaa558c635689d4bd54a306ecacf2c2c30930eabfb026eda84_84_1778699939', '2026-06-12 21:18:59', '2026-05-13 19:18:59'),
(124, 84, '7a8626171806dc7bd58b0fd39e94e10a6b6e26f6e5fd8613bd8a6f2190cfb3de_84_1778702645', '2026-06-12 22:04:05', '2026-05-13 20:04:05'),
(127, 84, '414c4c12dc41c2e51c0486019ed7354931fc41dd3cbf7f59903a846c9fd7af9c_84_1778705461', '2026-06-12 22:51:01', '2026-05-13 20:51:01'),
(128, 84, 'ff1511771aa9114ea0f5756dc52c39a8abd9532f915654b71e2e618c25aa5c01_84_1778706539', '2026-06-12 23:08:59', '2026-05-13 21:08:59'),
(129, 84, 'b57b708e98cf8b262e9e35a4876dcda34163d861a6d1f708d26bca589aedf95d_84_1778707590', '2026-06-12 23:26:30', '2026-05-13 21:26:30'),
(130, 84, '0436fb7f30bdedfb897865c3499547d211ef48b0ac930f9f1d9e588d4c561ea2_84_1778707833', '2026-06-12 23:30:33', '2026-05-13 21:30:33'),
(132, 86, '50a73916b6093fa1265786bde9e293964f0f8c9b905036abf1d79075085643e9_86_1778708510', '2026-06-12 23:41:50', '2026-05-13 21:41:50'),
(138, 80, '6e9502c26f8a20f7028dff61294c6abcf60340e8c9af7294d57170d7c9c80b21_80_1778712760', '2026-06-13 00:52:40', '2026-05-13 22:52:40'),
(150, 85, '6dc98f6301f24bc11c49d709d47778e1252fe2619c746b209717556bfbec7712_85_1778867553', '2026-06-14 19:52:33', '2026-05-15 17:52:33'),
(153, 85, '2bc48dc4b8919700e3f84ec6fcabda6ec3fed81ac3bf0904ab37f0c5c545084c_85_1778868062', '2026-06-14 20:01:02', '2026-05-15 18:01:02'),
(158, 82, 'ac12134bf4c38a31063253709ad6cf85ee1a2aff501c3eba22dfcef27a5dd97f_82_1778869449', '2026-06-14 20:24:09', '2026-05-15 18:24:09'),
(165, 89, '2ea8d1505038223ee4d9112c9f7dfc7bdcef67606aa0fa1b9ed41efccf33133a_89_1778886027', '2026-06-15 01:00:27', '2026-05-15 23:00:27'),
(168, 84, '9fe9e9f6e8b77d1b36d168e542affa8cf6a4cc766e8677798281cf597ffeaccb_84_1778928741', '2026-06-15 12:52:21', '2026-05-16 10:52:21'),
(172, 80, '9e7a23cd9caf97a29d32612a0ccf263c5a3265fbbbe80c44e8cb5af4e177967c_80_1778952506', '2026-06-15 19:28:26', '2026-05-16 17:28:26'),
(173, 92, '5461c12f44e65db762df693418051712bccd640e3d03e88434f5df70d4b0be2b_92_1778959912', '2026-06-15 21:31:52', '2026-05-16 19:31:52'),
(174, 84, '0f789e9d6289ce88563ef7252d95cce737214c1a2919a6e515c33856550530d7_84_1778959948', '2026-06-15 21:32:28', '2026-05-16 19:32:28'),
(175, 93, '29a9edb92b23d309c5ad56c1ba2137ce20ce5cbe806178fa1be80fedec6e7fce_93_1778960403', '2026-06-15 21:40:03', '2026-05-16 19:40:03'),
(176, 93, '7a9c621c60df70dd56446bbc0ec6ff694b71167de3d9fec5576f3aecfc29c554_93_1778962772', '2026-06-15 22:19:32', '2026-05-16 20:19:32'),
(177, 92, 'f97dd2ce307236f821e2ac7c9567369af2db1a3f8c29377a5637c300bf7a1e18_92_1778964227', '2026-06-15 22:43:47', '2026-05-16 20:43:47'),
(179, 93, '44f2b47e12c676b45e583c5423c60319bd0ea26ad7ae83644b1c59e1e051c466_93_1778967304', '2026-06-15 23:35:04', '2026-05-16 21:35:04'),
(180, 93, 'f60daba9769e859cf1055d80107d9719baed125d900321bb5bd6c3a8186e5792_93_1779102379', '2026-06-17 13:06:19', '2026-05-18 11:06:19'),
(181, 82, '3cffc6ab2cc45f6b925a53ea2a92afcf454fca6bc08e57375d29bb797678a385_82_1779102604', '2026-06-17 13:10:04', '2026-05-18 11:10:04'),
(184, 93, 'e460e6f02db692ea4ab0de11136a10eefe7780a57633d3f9c7af295fc0a3347b_93_1779105382', '2026-06-17 13:56:22', '2026-05-18 11:56:22'),
(190, 93, '7ea5f6952943ef501ec335a61d549813788b991e20235b0ba57991469302cef7_93_1779273084', '2026-06-19 12:31:24', '2026-05-20 10:31:24'),
(192, 82, 'c09a61669dafe2d3f1f858e7c42a15694fe14015bd7c289f32fb928cfd05f040_82_1779467596', '2026-06-21 18:33:16', '2026-05-22 16:33:16'),
(201, 85, '6f39a6daab5810b6bf9446cc3da05a8771ddf2daa0870f618745f0f55a9a19f6_85_1779630172', '2026-06-23 15:42:52', '2026-05-24 13:42:52'),
(202, 85, 'e45e6ab69bb28570fe2f8753f70629f8c0d0cd7d39dec4ab285082ecc1e1b1dc_85_1779635615', '2026-06-23 17:13:35', '2026-05-24 15:13:35'),
(203, 85, '4e7a90b409c425079ea5e612fac3a6d3530583c10f08734d97f6a3ebcccde245_85_1779635647', '2026-06-23 17:14:07', '2026-05-24 15:14:07'),
(213, 84, '9c9d63e7b9a8f1ff74c170266bcac38ba3ac86396b171b3a40f7c599dfadaa2f_84_1779644095', '2026-06-23 19:34:55', '2026-05-24 17:34:55'),
(218, 94, '5d0aecebf8f91e480f258c84ba4250d01bcae4ab146668a6284e0be456e0acea_94_1779745429', '2026-06-24 23:43:49', '2026-05-25 21:43:49'),
(219, 94, '4ec91e24600ebc2490437c31582bd84d5ef7214aeab403182a20e1190210bf35_94_1779747972', '2026-06-25 00:26:12', '2026-05-25 22:26:12'),
(220, 84, '493da8e9ce21dc870a6b2b3b9bde13101cab5fd330e8f1a459f3999972c1bab9_84_1779749238', '2026-06-25 00:47:18', '2026-05-25 22:47:18'),
(235, 94, '3f6fa8a8c694ad20ec63097fd304ea8018641baf6f961d7ba5eab81d4f7ab5a4_94_1779892501', '2026-06-26 16:35:01', '2026-05-27 14:35:01'),
(260, 94, 'fa937532c149ea3091bceabbf643c40f56b9ff63f4063b8fb7554ac3e6ce694b_94_1779926242', '2026-06-27 01:57:22', '2026-05-27 23:57:22'),
(264, 93, '75328c5091c5fba6254c8ed5ce667b7fa4a9ff4750356ef8308a32d73e2b3317_93_1779926737', '2026-06-27 02:05:37', '2026-05-28 00:05:37'),
(265, 94, 'b8e2d2e0aca2e072477ce8c30d4ed16038897c790e918e31a30e8f1b373c28fb_94_1780070047', '2026-06-28 17:54:07', '2026-05-29 15:54:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `agent_reports`
--
ALTER TABLE `agent_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `agent_reviews`
--
ALTER TABLE `agent_reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `blocked_users`
--
ALTER TABLE `blocked_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_block` (`blocker_id`,`blocked_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `car_photos`
--
ALTER TABLE `car_photos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `car_reservations`
--
ALTER TABLE `car_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`);

--
-- Indexes for table `car_reviews`
--
ALTER TABLE `car_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `client_deleted_notifications`
--
ALTER TABLE `client_deleted_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comment_reports`
--
ALTER TABLE `comment_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dealer_review_replies`
--
ALTER TABLE `dealer_review_replies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deleted_conversations`
--
ALTER TABLE `deleted_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_del` (`user_id`,`conversation_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`car_id`);

--
-- Indexes for table `free_trials`
--
ALTER TABLE `free_trials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`review_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `review_likes_index`
--
ALTER TABLE `review_likes_index`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`review_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `agent_reports`
--
ALTER TABLE `agent_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `agent_reviews`
--
ALTER TABLE `agent_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blocked_users`
--
ALTER TABLE `blocked_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `car_photos`
--
ALTER TABLE `car_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `car_reservations`
--
ALTER TABLE `car_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `car_reviews`
--
ALTER TABLE `car_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `client_deleted_notifications`
--
ALTER TABLE `client_deleted_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `comment_reports`
--
ALTER TABLE `comment_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `dealer_review_replies`
--
ALTER TABLE `dealer_review_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `deleted_conversations`
--
ALTER TABLE `deleted_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `free_trials`
--
ALTER TABLE `free_trials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `review_likes`
--
ALTER TABLE `review_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `review_likes_index`
--
ALTER TABLE `review_likes_index`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `review_replies`
--
ALTER TABLE `review_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipment_tracking`
--
ALTER TABLE `shipment_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=266;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agent_profiles`
--
ALTER TABLE `agent_profiles`
  ADD CONSTRAINT `agent_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `car_reservations`
--
ALTER TABLE `car_reservations`
  ADD CONSTRAINT `car_reservations_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `car_reviews`
--
ALTER TABLE `car_reviews`
  ADD CONSTRAINT `car_reviews_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `car_reviews_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `car_reviews` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_likes`
--
ALTER TABLE `review_likes`
  ADD CONSTRAINT `review_likes_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `car_reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_likes_index`
--
ALTER TABLE `review_likes_index`
  ADD CONSTRAINT `review_likes_index_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_likes_index_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD CONSTRAINT `review_replies_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
