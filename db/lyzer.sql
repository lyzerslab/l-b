-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 03, 2024 at 08:25 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lyzer`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `access_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `blocked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `access_logs`
--

INSERT INTO `access_logs` (`id`, `ip_address`, `access_time`, `blocked`) VALUES
(101, '27.147.150.102', '2024-03-29 09:44:07', 0),
(102, '27.147.150.102', '2024-03-30 08:56:35', 0),
(103, '27.147.150.102', '2024-03-30 08:57:25', 0),
(104, '27.147.150.102', '2024-04-01 08:25:51', 0),
(105, '27.147.150.102', '2024-04-01 08:55:46', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `profile_photo` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `profile_photo`, `is_admin`, `date`) VALUES
(0, 'rana', '$2y$10$RW2pSFacmGQdARoarTeEfeg5SiqZF3NLSxseNUOyC.SqBAI.wZkES', 'rana@gmail.com', 'super-admin/rana/pexels-tima-miroshnichenko-5439443.jpg', 0, '2024-02-15 10:01:16'),
(21, 'zkrana', '$2y$10$kiRvG1wE418aRMjXZkCmuemut2iZLCOZTTZzMnglnZXLdLcyfTwAC', 'zkranao@gmail.com', 'super-admin/zkrana/handsome-man-with-laptop.jpg', 0, '2024-01-26 05:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_ips`
--

CREATE TABLE `blocked_ips` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `blocked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blocked_ips`
--

INSERT INTO `blocked_ips` (`id`, `ip_address`, `blocked_until`) VALUES
(21, '127.0.0.1', '2024-02-02 12:27:09'),
(22, '202.181.16.15', '2024-03-18 18:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employeeID` int(11) NOT NULL,
  `employeeName` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `totalExperience` varchar(50) DEFAULT NULL,
  `joiningDate` date DEFAULT NULL,
  `fieldOfExpertise` text DEFAULT NULL,
  `currentAddress` varchar(255) DEFAULT NULL,
  `presentAddress` varchar(255) DEFAULT NULL,
  `employeeType` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employeeID`, `employeeName`, `photo`, `designation`, `totalExperience`, `joiningDate`, `fieldOfExpertise`, `currentAddress`, `presentAddress`, `employeeType`) VALUES
(4, 'Md. Tareq Monjur', 'pexels-mikhail-nilov-6930538.jpg', 'Senior Software Engineer', '10', '2024-01-25', 'Laravel, PHP, React, React Native', 'Feni', 'Feni', 'remote'),
(5, 'Ziaul Kabir', 'handsome-man-with-laptop.jpg', 'CEO & Full-stack Developer', '3', '2023-12-12', 'PHP, My-Sql, React, Next.js, WordPress, Domain-Hosting', 'Dhaka', 'Dhaka', 'remote');

-- --------------------------------------------------------

--
-- Table structure for table `formdata`
--

CREATE TABLE `formdata` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service` varchar(100) NOT NULL,
  `web_package` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `formdata`
--

INSERT INTO `formdata` (`id`, `name`, `email`, `phone`, `service`, `web_package`, `message`, `created_at`) VALUES
(3, 'Ecommerce-Admin-update', 'keyword@gmail.com', '01284224157', 'web-development', NULL, 'x', '2024-03-29 09:33:40'),
(4, 'Summer Collection', 'zkranao@gmail.com', '5555555555555', 'app-development', NULL, 'xzcvcb', '2024-03-29 09:35:33'),
(5, 'Ecommerce-Admin-update', 'zkranao@sdagmail.com', '01284224157', 'web-development', NULL, 'cfgh', '2024-03-29 09:35:51'),
(6, 'Ecommerce-Admin-update', 'ads@gmail.com', '01284224157', 'web-development', NULL, 'ds', '2024-03-29 09:36:39'),
(7, 'Summer Collection', 'ads@gmail.com', '01284224157', 'web-development', 'custom', 'xcvc', '2024-03-29 09:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `ProjectID` int(11) NOT NULL,
  `ProjectName` varchar(255) NOT NULL,
  `ProjectDescription` text DEFAULT NULL,
  `ClientName` varchar(255) DEFAULT NULL,
  `ProjectType` varchar(100) DEFAULT NULL,
  `Duration` varchar(50) DEFAULT NULL,
  `ProjectStart` date DEFAULT NULL,
  `ProjectEnd` date DEFAULT NULL,
  `AssignTo` varchar(255) DEFAULT NULL,
  `ProjectNote` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`ProjectID`, `ProjectName`, `ProjectDescription`, `ClientName`, `ProjectType`, `Duration`, `ProjectStart`, `ProjectEnd`, `AssignTo`, `ProjectNote`, `CreatedAt`) VALUES
(2, 'Speaknix', 'Text to speech conversion', 'Derill', 'App', '1.5 Months', '2024-03-20', '2024-05-29', '4,5', 'Projects is interrupted due to api for 1 months.', '2024-04-03 06:03:38');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(31, 'zkranao@gmail.com', '2024-03-29 09:13:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employeeID`);

--
-- Indexes for table `formdata`
--
ALTER TABLE `formdata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`ProjectID`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `blocked_ips`
--
ALTER TABLE `blocked_ips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employeeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `formdata`
--
ALTER TABLE `formdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `ProjectID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
