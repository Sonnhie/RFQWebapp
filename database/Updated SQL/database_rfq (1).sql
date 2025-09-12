-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 02:45 AM
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
-- Table structure for table `attachment_table`
--

CREATE TABLE `attachment_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attachment_table`
--

INSERT INTO `attachment_table` (`id`, `control_number`, `item_name`, `path_file`, `created_at`) VALUES
(18, 'RFQ-202509-0001', 'Bolts', '/Uploads/Items/file_68bb8755cae40.jpg', '2025-09-06 08:59:01'),
(19, 'RFQ-202509-0001', 'Wire', '/Uploads/Items/file_68bb8755cb0be.jpg', '2025-09-06 08:59:01'),
(20, 'RFQ-202509-0002', 'Bolts', '/Uploads/Items/file_68bb9e05c3ef2.jpg', '2025-09-06 10:35:49'),
(21, 'RFQ-202509-0002', 'Wire', '/Uploads/Items/file_68bb9e05c4087.jpg', '2025-09-06 10:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `comparison_table`
--

CREATE TABLE `comparison_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_uom` varchar(25) NOT NULL,
  `supplier_name` varchar(50) NOT NULL,
  `supplier_price` double NOT NULL,
  `currency` varchar(5) NOT NULL,
  `supplier_discount` double NOT NULL,
  `payment_terms` varchar(50) NOT NULL,
  `delivery_time` varchar(50) NOT NULL,
  `total_price` double NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currency_table`
--

CREATE TABLE `currency_table` (
  `id` int(11) NOT NULL,
  `currency_id` varchar(5) NOT NULL,
  `currency_value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `currency_table`
--

INSERT INTO `currency_table` (`id`, `currency_id`, `currency_value`) VALUES
(1, 'PHP', 0.017),
(2, 'USD', 58.1734);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_table`
--

CREATE TABLE `delivery_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_amount` double NOT NULL,
  `supplier_name` varchar(50) NOT NULL,
  `delivery_date` date NOT NULL,
  `received_date` datetime DEFAULT NULL,
  `item_status` varchar(50) NOT NULL,
  `item_remarks` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_table`
--

INSERT INTO `delivery_table` (`id`, `control_number`, `item_name`, `item_description`, `item_quantity`, `item_amount`, `supplier_name`, `delivery_date`, `received_date`, `item_status`, `item_remarks`, `created_at`) VALUES
(1, 'RFQ-202507-0001', 'Bolts', 'T-head bolts', 10, 1234, 'ABC', '2025-07-24', NULL, 'Pending', '', '2025-07-15 11:29:05');

-- --------------------------------------------------------

--
-- Table structure for table `department_paths`
--

CREATE TABLE `department_paths` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `upload_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_paths`
--

INSERT INTO `department_paths` (`id`, `department_name`, `upload_path`) VALUES
(1, 'HRGA', '/img/HRGAIT'),
(2, 'IT', '/img/HRGAIT'),
(3, 'Procurement', '/img/Procurement'),
(4, 'PCD', '/img/PCD'),
(5, 'Injection', '/img/Injection'),
(6, '2nd Process - OA', '/img/2ndProcess'),
(7, '2nd Process - YAZAKI', '/img/2ndProcess'),
(8, 'Accounting', '/img/Accounting'),
(9, 'Sales', '/img/Sales'),
(10, 'Mold Maintenance', '/img/Mold'),
(11, 'Technical', '/img/Mold'),
(12, 'Machine Maintenance', '/img/MM'),
(13, 'QAQC', '/img/QAQC'),
(14, 'Facility', '/img/Facility'),
(15, 'Health & Safety', '/img/Facility');

-- --------------------------------------------------------

--
-- Table structure for table `department_table`
--

CREATE TABLE `department_table` (
  `id` int(11) NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_table`
--

INSERT INTO `department_table` (`id`, `department`) VALUES
(1, 'IT'),
(2, 'HRGA'),
(3, 'Procurement'),
(4, 'PCD'),
(5, 'Injection'),
(6, '2nd Process - OA'),
(7, '2nd Process - YAZAKI'),
(8, 'Accounting'),
(9, 'Sales'),
(10, 'Mold Maintenance'),
(11, 'Machine Maintenance'),
(12, 'QAQC'),
(13, 'Facility'),
(14, 'Health & Safety'),
(15, 'Technical');

-- --------------------------------------------------------

--
-- Table structure for table `email_table`
--

CREATE TABLE `email_table` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `emailadd` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_table`
--

INSERT INTO `email_table` (`id`, `name`, `emailadd`, `department`) VALUES
(1, 'Ma. Elizabeth Aguilar', 'maelizabeth.aguilar@nidec.com', 'Accounting'),
(2, 'Gloria Litana', 'gloria.litana@nidec.com', 'Accounting'),
(3, 'Morena Joy Nanza', 'morenajoy.nanza@nidec.com', 'Accounting'),
(4, 'Emmie Rose Caredon', 'emmierose.ceredon@nidec.com', 'Accounting'),
(5, 'Vanessa Sabinay', 'vannesaellaine.sabiney@nidec.com', 'Accounting'),
(6, 'Cecilia Lindo', 'macecilia.lindo@nidec.com', 'HRGA'),
(7, 'Kristel Delas Alas', 'kristel.delasalas@nidec.com', 'HRGA'),
(8, 'Lyra Joy Alcaraz', 'lyrajoy.alcaraz@nidec.com', 'HRGA'),
(9, 'Patricia May Torres', 'patriciamay.torres@nidec.com', 'HRGA'),
(10, 'Anna Rose Atienza', 'annarose.atienza@nidec.com', 'HRGA'),
(11, 'Glady Anne Nequinto', 'gladysanne.nequinto@nidec.com', 'HRGA'),
(12, 'Dhara Heralla', 'dhara.heralla@nidec.com', 'HRGA'),
(13, 'Jocelyn Marcaida', 'jocelyn.marcaida@nidec.com', 'Facility'),
(14, 'Carlos Mabilin Jr.', 'carlos.mabilinjr@nidec.com', 'Facility'),
(15, 'Canave Marlon Jr.', 'marlonjulian.canavejr@nidec.com', 'Facility'),
(16, 'Amante Manzano Jr.', 'amante.manzanojr@nidec.com', 'Facility'),
(17, 'Nurse', 'keith.obien@nidec.com', 'Health & Safety'),
(18, 'Jerwin Hikong', 'jerwin.hikong@nidec.com', 'Health & Safety'),
(19, 'Chester Pagne', 'chester.pagne@nidec.com', 'Injection'),
(20, 'Edwin Belen', 'edwin.belen@nidec.com', 'Injection'),
(21, 'Danica Barraquio', 'danica.barraquio@nidec.com', 'Injection'),
(22, 'Djonelen Reyes', 'djonelen.reyes@nidec.com', 'IT'),
(23, 'Marvin Gicole', 'marvin.gicole@nidec.com', 'IT'),
(24, 'Sonny Boy Del Rosario', 'sonnyboy.delrosario@nidec.com', 'IT'),
(25, 'Adrian Amada', 'adrian.amada@nidec.com', 'Machine Maintenance'),
(26, 'Alexander Pagdagdagan', 'alexander.pagdagdagan@nidec.com', 'Machine Maintenance'),
(27, 'Artchie Nuñez', 'artchie.nunez@nidec.com', 'Machine Maintenance'),
(28, 'Minoru Kikai', 'minoru.kikai@nidec.com', 'Mold Maintenance'),
(29, 'Michel Malabag', 'michel.malabag@nidec.com', 'Mold Maintenance'),
(30, 'Carlos Engreso', 'engreso.carlos@nidec.com', 'Mold Maintenance'),
(31, 'Martin Cartoneros', 'martin.cartoneros@nidec.com', 'Mold Maintenance'),
(32, 'Mark Canezo', 'mark.canezo@nidec.com', 'Mold Maintenance'),
(33, 'April Grace De Leon', 'aprilgrace.deleon@nidec.com', 'Mold Maintenance'),
(34, 'Romar Borja', 'romar.borja@nidec.com', '2nd Process - OA'),
(35, 'Nhoelyn Melor', 'nhoelyn.melor@nidec.com', '2nd Process - OA'),
(36, 'Richelyn Magno', 'richelyn.magno@nidec.com', '2nd Process - OA'),
(37, 'Willy Arante', 'wilfredo.arante@nidec.com', 'PCD'),
(38, 'Ellaine Porteza', 'ellaine.porteza@nidec.com', 'PCD'),
(39, 'Marites Estardo', 'marites.estardo@nidec.com', 'PCD'),
(40, 'Crismark Agillon', 'crismark.agillon@nidec.com', 'PCD'),
(41, 'Edison, Vivas', 'ediezon.vivas@nidec.com', 'PCD'),
(42, 'Nico Andrew Marquez', 'nicoandrew.marquez@nidec.com', 'PCD'),
(43, 'Jinky Regala', 'jinky.regala@nidec.com', 'PCD'),
(44, 'Jonar Rocreo', 'jonar.rocreo@nidec.com', 'PCD'),
(45, 'Goddee Sanchez', 'goddee.sanchez@nidec.com', 'PCD'),
(46, 'Ryan Mulle', 'ryan.mulle@nidec.com', 'PCD'),
(47, 'John Mark Vidal', 'johnmark.vidal@nidec.com', 'PCD'),
(48, 'Roxanne Haboc', 'roxanne.haboc@nidec.com', 'PCD'),
(49, 'Melanie Gancayco', 'melanie.gancayco@nidec.com', 'Procurement'),
(50, 'Regine Baniago', 'regine.guellena@nidec.com', 'Procurement'),
(51, 'Kate Baniago', 'kate.baniago@nidec.com', 'Procurement'),
(52, 'Kristine Joy Boongaling', 'kristinejoy.boongaling@nidec.com', 'QAQC'),
(53, 'Mikasa Vallyne Rodriguez', 'mikasavallyne.rodriguez@nidec.com', 'QAQC'),
(54, 'Bernadette Braza', 'bernadette.braza@nidec.com', 'QAQC'),
(55, 'Girlie Almeida', 'girlie.almeida@nidec.com', 'QAQC'),
(56, 'Kimberly Garcia', 'kimberly.garcia@nidec.com', 'QAQC'),
(57, 'Genalyn Cantoneros', 'genalyn.cartoneros@nidec.com', 'QAQC'),
(58, 'Teresa Tipo', 'teresa.tipo@nidec.com', 'QAQC'),
(59, 'Ernielyn Lozano', 'ernielyn.lozano@nidec.com', 'QAQC'),
(60, 'Anchie Balagtas', 'anchie.balagtas@nidec.com', 'QAQC'),
(61, 'Genalyn Mendoza', 'genalyn.mendoza@nidec.com', 'QAQC'),
(62, 'Joyce Taguiwalo', 'joyce.taguiwalo@nidec.com', 'QAQC'),
(63, 'Nekkita Kae Fausto', 'nekkitakae.fausto@nidec.com', 'QAQC'),
(64, 'Arvin Edgar Vergara', 'arvinedgar.vergara@nidec.com', 'Sales'),
(65, 'Mary Ann Maliksi', 'maryann.maliksi@nidec.com', 'Sales'),
(66, 'Amielyne Bawalan', 'amielyne.bawalan@nidec.com', 'Sales'),
(67, 'Glenda Anaña', 'glenda.anana@nidec.com', 'Sales'),
(68, 'Susan Gella', 'susan.gella@nidec.com', 'Sales'),
(69, 'Jonathan Niego', 'jonathan.niego@nidec.com', 'Sales'),
(70, 'Michael Bugayong', 'michael.bugayong@nidec.com', 'Sales'),
(71, 'Michael Angelo Gomez', 'michaelangelo.gomez@nidec.com', 'Technical'),
(72, 'Adrian Bertudes', 'adrian.bertudes@nidec.com', 'Technical'),
(73, 'Joseph Deticio', 'joseph.deticio@nidec.com', 'Technical'),
(74, 'Mary Jane Deloy', 'maryjane.deloy@nidec.com', '2nd Process - YAZAKI'),
(75, 'Linie Padeno', 'linie.padeno@nidec.com', '2nd Process - YAZAKI'),
(76, 'Marenele Flores', 'marenele.flores@nidec.com', '2nd Process - YAZAKI'),
(77, 'Cecilia Lindo', 'macecilia.lindo@nidec.com', 'IT'),
(78, 'Willy Arante', 'wilfredo.arante@nidec.com', '2nd Process - OA'),
(79, 'Willy Arante', 'wilfredo.arante@nidec.com', '2nd Process - YAZAKI'),
(80, 'Willy Arante', 'wilfredo.arante@nidec.com', 'Procurement'),
(81, 'Willy Arante', 'wilfredo.arante@nidec.com', 'Injection');

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
(0, 'RFQ-202506-0019', 'New request created by 2nd Process - OA with Control Number: RFQ-202506-0019', '2nd Process - OA', '', '2025-06-24 10:35:57'),
(0, 'RFQ-202507-0001', 'New request created by IT with Control Number: RFQ-202507-0001', 'IT', '', '2025-07-14 17:47:55'),
(0, 'RFQ-202507-0002', 'New request created by IT with Control Number: RFQ-202507-0002', 'IT', '', '2025-07-15 08:17:10'),
(0, 'RFQ-202507-0003', 'New request created by IT with Control Number: RFQ-202507-0003', 'IT', '', '2025-07-23 13:33:05'),
(0, 'RFQ-202507-0004', 'New request created by IT with Control Number: RFQ-202507-0004', 'IT', '', '2025-07-23 13:37:11');

-- --------------------------------------------------------

--
-- Table structure for table `request_logs_table`
--

CREATE TABLE `request_logs_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `update_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_logs_table`
--

INSERT INTO `request_logs_table` (`id`, `control_number`, `status`, `remarks`, `update_at`) VALUES
(12, 'RFQ-202509-0001', 'Pending', 'Created Request', '2025-09-06 08:59:01'),
(13, 'RFQ-202509-0002', 'Pending', 'Created Request', '2025-09-06 10:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `request_table`
--

CREATE TABLE `request_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `item_description` varchar(50) NOT NULL,
  `item_purpose` varchar(50) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_uom` varchar(50) NOT NULL,
  `item_status` varchar(50) NOT NULL,
  `item_remarks` varchar(100) NOT NULL,
  `item_section` varchar(50) NOT NULL,
  `item_requestor` varchar(50) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_table`
--

INSERT INTO `request_table` (`id`, `control_number`, `item_name`, `item_description`, `item_purpose`, `item_quantity`, `item_uom`, `item_status`, `item_remarks`, `item_section`, `item_requestor`, `created_at`, `updated_at`) VALUES
(18, 'RFQ-202509-0001', 'Bolts', 'T-head bolts', 'No stock', 34, 'Piece', 'Pending', 'For Quotation', 'Procurement', 'Regine Guellena', '2025-09-06', '0000-00-00 00:00:00'),
(19, 'RFQ-202509-0001', 'Wire', 'coaxial cable', 'No stock', 15, 'Meter', 'Pending', 'For Quotation', 'Procurement', 'Regine Guellena', '2025-09-06', '0000-00-00 00:00:00'),
(20, 'RFQ-202509-0002', 'Bolts', 'T-head bolts', 'No stock', 34, 'Piece', 'Pending', 'For Quotation', 'Procurement', 'Regine Guellena', '2025-09-06', '0000-00-00 00:00:00'),
(21, 'RFQ-202509-0002', 'Wire', 'coaxial cable', 'No stock', 15, 'Meter', 'Pending', 'For Quotation', 'Procurement', 'Regine Guellena', '2025-09-06', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `role_table`
--

CREATE TABLE `role_table` (
  `id` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `access_level` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_table`
--

INSERT INTO `role_table` (`id`, `role`, `access_level`) VALUES
(1, 1, 'Admin'),
(2, 2, 'Requestor'),
(3, 3, 'Verifier'),
(4, 4, 'Section-Approver'),
(5, 5, 'Requestor-Approver'),
(6, 6, 'Verifier-Approver'),
(7, 7, 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `signature_table`
--

CREATE TABLE `signature_table` (
  `id` int(11) NOT NULL,
  `requestor_name` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `role` varchar(40) NOT NULL,
  `signature_path` varchar(255) NOT NULL,
  `printed_name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `signature_table`
--

INSERT INTO `signature_table` (`id`, `requestor_name`, `section`, `role`, `signature_path`, `printed_name`, `created_at`) VALUES
(3, 'Djone Len Reyes', 'IT', 'Supervisor', 'img/HRGAIT/sig_68b7fc7e74351.jpg', 'Djone Len Reyes', '2025-09-03 16:29:50'),
(4, 'Sonny Del Rosario', 'IT', 'Staff', 'img/HRGAIT/sig_68b90c19b6940.jpg', 'Sonny Del Rosario', '2025-09-04 08:53:32'),
(5, 'Regine Guellena', 'Procurement', 'Staff', 'img/Procurement/sig_68b9219c1c44e.jpg', 'Regine Guellena', '2025-09-04 13:09:17'),
(6, 'Melanie Gancayco', 'Procurement', 'Supervisor', 'img/Procurement/sig_68b9217fac55f.jpg', 'Melanie Gancayco', '2025-09-04 13:09:17'),
(7, 'Wilfredo Arante', 'Procurement', 'GenManager', 'img/Procurement/sig_68b9215722e2a.jpg', 'Wilfredo Arante', '2025-09-04 13:12:28'),
(9, 'Ma. Cecilia Lindo', 'IT', 'Manager', 'img/HRGAIT/sig_68b9380f44e1d.jpg', 'Ma. Cecilia Lindo', '2025-09-04 14:56:15');

-- --------------------------------------------------------

--
-- Table structure for table `uom_table`
--

CREATE TABLE `uom_table` (
  `id` int(11) NOT NULL,
  `unit_code` varchar(50) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uom_table`
--

INSERT INTO `uom_table` (`id`, `unit_code`, `unit_name`, `created_at`, `updated_at`) VALUES
(1, 'Pcs', 'Pieces', '2025-07-23 13:11:40', '2025-07-23 13:11:40'),
(2, 'Box', 'Box', '2025-07-23 13:11:40', '2025-07-23 13:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `role` int(11) NOT NULL,
  `machine_token` varchar(255) NOT NULL,
  `user_status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`id`, `username`, `password`, `name`, `position`, `department`, `role`, `machine_token`, `user_status`, `created_at`) VALUES
(1, 'itstaff', '$2y$10$zInxD3t26B9mZgFBbo/2se7bLG9O0yYKVCN.EeTpw7d5./l8fbWoi', 'Sonny Del Rosario', 'Staff', 'IT', 2, '', 'Offline', '2025-05-08 02:29:06'),
(2, 'procurement01', '$2y$10$3qkJmmRpFVAqDU/rGd96N.tRT2uVRZG3SJO7KkT7h1WEhxla3QEkW', 'Regine Guellena', 'Staff', 'Procurement', 3, 'd9024e3a-2aba-46ce-acf5-30df0667f49f', 'Active', '2025-05-08 02:29:06'),
(3, 'itsupervisor01', '$2y$10$ws..Sk37UojA6VU94iKNmOSxai5f3kce5uS6tjI3heAv08IhJ9nVO', 'Djone Len Reyes', '', 'IT', 4, '', 'Offline', '2025-05-30 03:09:05'),
(5, 'procurement02', '$2y$10$CBSF0w1AzQ8phnji4ycr2u6t.gn.Tgzj5BIpnHUXtij6qC9Bat4e2', 'Melanie Gancayco', 'Supervisor', 'Procurement', 6, '', 'Offline', '2025-06-05 07:23:10'),
(10, 'admin', '$2y$10$ohiS6ktfWWssXl6SUxapUuJ4KFsdqytvXL0IEh31kMVpKc3Yq3Hdu', 'Sonny Del Rosario', 'Staff', 'IT', 2, '', '', '2025-06-10 09:26:43'),
(12, 'injectio01', '$2y$10$mGacEJjbXxPpLbAJ4EXTaO01U5dq/nmmzGtbRceulur5HIQDJ8BgW', 'Danica Baraquio', '', 'Injection', 2, '', '', '2025-06-10 09:30:13'),
(13, 'admin01', '$2y$10$T6A3VRQXvNhcRbGaN5291uIAxP1cSCDoJMy9gacxmQn3di5QaoLDG', 'Sonny Del Rosario', '', 'IT', 1, '', 'Offline', '2025-06-10 09:38:48'),
(14, 'procurement03', '$2y$10$KsCYEDtrz0Gtl1K87ruY6.AmroNYWndcUV2KJZJw2IIfHknSR0lTm', 'Tadahiro Toriyama', '', 'Procurement', 3, '', '', '2025-06-10 10:51:38'),
(15, 'procurement04', '$2y$10$SDG99lxnSWCGxZFsEx.Ml.WfAcG4G58hHgaAKbxdqNJ7wDtnqpPXu', 'Jainne Dela Rosa', '', 'Procurement', 2, '', '', '2025-06-10 10:52:48'),
(16, 'oa12345', '$2y$10$B98AYZmeNht5P8t9MDq76O.aQFtqpyXRQGeP96f4WwqQzjjMXY7zy', 'Richelyn Magno', '', '2nd Process - OA', 2, '', 'Offline', '2025-06-10 10:53:40'),
(19, 'yazaki01', '$2y$10$7wy1HhSXVjAxTXVupLpmr.vkwM/mEiSOrDWtH8nQZy2FL.4IkHDge', 'Ruby Solis', '', '2nd Process - YAZAKI', 2, '', '', '2025-06-10 16:06:01'),
(20, 'NIPC00149', '$2y$10$y6NUH411lu5FMlwayzxRfOiG/wRmu5Wot7dSlbzZE48dyJayU/dcu', 'Wilfredo Arante', 'GenManager', 'Procurement', 7, '', 'Offline', '2025-08-08 10:33:37'),
(21, 'Administrator', '$2y$10$3zNJflWk/3.GJqb.8/oij.1X1K4aLjS8XI.yD8zoy8SXSkQCipMv2', 'Sonny Del Rosario', '', 'IT', 1, '', 'Offline', '2025-08-19 10:08:42'),
(24, 'NIPC00407', '$2y$10$c72uCzdRSnZ6h5/H4gAgNOF37QsXmC9eT2M7CDXW7jwjG.RGxTKDG', 'Djone Len Reyes', '', 'IT', 4, '', '', '2025-09-03 16:29:50'),
(25, 'NIPC01', '$2y$10$fMMevijh1uqopWSVAXwZB.tn6l.a/TLOvr5NIKki82pxbnRPIcdmW', 'Ma. Cecilia Lindo', 'Manager', 'IT', 4, '', '', '2025-09-04 14:56:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachment_table`
--
ALTER TABLE `attachment_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comparison_table`
--
ALTER TABLE `comparison_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currency_table`
--
ALTER TABLE `currency_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_table`
--
ALTER TABLE `delivery_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `department_paths`
--
ALTER TABLE `department_paths`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `department_table`
--
ALTER TABLE `department_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_table`
--
ALTER TABLE `email_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request_logs_table`
--
ALTER TABLE `request_logs_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request_table`
--
ALTER TABLE `request_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_table`
--
ALTER TABLE `role_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `signature_table`
--
ALTER TABLE `signature_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `uom_table`
--
ALTER TABLE `uom_table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`),
  ADD UNIQUE KEY `unit_code` (`unit_code`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachment_table`
--
ALTER TABLE `attachment_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `comparison_table`
--
ALTER TABLE `comparison_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currency_table`
--
ALTER TABLE `currency_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_table`
--
ALTER TABLE `delivery_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `department_paths`
--
ALTER TABLE `department_paths`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `department_table`
--
ALTER TABLE `department_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `email_table`
--
ALTER TABLE `email_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `request_logs_table`
--
ALTER TABLE `request_logs_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `request_table`
--
ALTER TABLE `request_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `role_table`
--
ALTER TABLE `role_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `signature_table`
--
ALTER TABLE `signature_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `uom_table`
--
ALTER TABLE `uom_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
