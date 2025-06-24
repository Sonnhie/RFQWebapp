-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2025 at 04:39 AM
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
-- Database: `database_rfq`
--

-- --------------------------------------------------------

--
-- Table structure for table `notification_table`
--

CREATE TABLE `notification_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `section` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_table`
--

INSERT INTO `notification_table` (`id`, `control_number`, `message`, `section`, `role`, `created_at`) VALUES
(0, 'RFQ-202506-0019', 'New request created by IT with Control Number: RFQ-202506-0019', 'IT', '', '2025-06-24 09:17:13'),
(0, 'RFQ-202506-0019', 'New request created by Procurement with Control Number: RFQ-202506-0019', 'Procurement', '', '2025-06-24 09:32:09'),
(0, 'RFQ-202506-0019', 'New request created by IT with Control Number: RFQ-202506-0019', 'IT', '', '2025-06-24 09:32:49'),
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 09:36:59'),
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 09:53:16'),
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 10:32:31'),
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 10:34:35'),
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 10:35:57');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
