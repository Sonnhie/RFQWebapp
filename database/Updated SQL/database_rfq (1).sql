-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2025 at 05:30 AM
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
  `item_attachment` longblob NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comparison_table`
--

CREATE TABLE `comparison_table` (
  `id` int(11) NOT NULL,
  `control_number` varchar(50) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `supplier_name` varchar(50) NOT NULL,
  `supplier_price` double NOT NULL,
  `supplier_discount` double NOT NULL,
  `total_price` double NOT NULL,
  `remarks` varchar(50) NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(76, 'Marenele Flores', 'marenele.flores@nidec.com', '2nd Process - YAZAKI');

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
(6, 6, 'Verifier-Approver');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `department` varchar(50) NOT NULL,
  `role` int(11) NOT NULL,
  `machine_token` varchar(255) NOT NULL,
  `user_status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`id`, `username`, `password`, `name`, `department`, `role`, `machine_token`, `user_status`, `created_at`) VALUES
(1, 'itstaff', '$2y$10$zInxD3t26B9mZgFBbo/2se7bLG9O0yYKVCN.EeTpw7d5./l8fbWoi', 'Sonny Del Rosario', 'IT', 2, '', '', '2025-05-08 02:29:06'),
(2, 'procurement01', '$2y$10$3qkJmmRpFVAqDU/rGd96N.tRT2uVRZG3SJO7KkT7h1WEhxla3QEkW', 'Regine Guellena', 'Procurement', 3, '', '', '2025-05-08 02:29:06'),
(3, 'itsupervisor01', '$2y$10$ws..Sk37UojA6VU94iKNmOSxai5f3kce5uS6tjI3heAv08IhJ9nVO', 'Djone Len Reyes', 'IT', 4, '', '', '2025-05-30 03:09:05'),
(5, 'procurement02', '$2y$10$CBSF0w1AzQ8phnji4ycr2u6t.gn.Tgzj5BIpnHUXtij6qC9Bat4e2', 'Melanie Gancayco', 'Procurement', 6, '', '', '2025-06-05 07:23:10'),
(10, 'admin', '$2y$10$ohiS6ktfWWssXl6SUxapUuJ4KFsdqytvXL0IEh31kMVpKc3Yq3Hdu', 'Sonny Del Rosario', 'IT', 1, '', '', '2025-06-10 09:26:43'),
(12, 'injectio01', '$2y$10$mGacEJjbXxPpLbAJ4EXTaO01U5dq/nmmzGtbRceulur5HIQDJ8BgW', 'Danica Baraquio', 'Injection', 2, '', '', '2025-06-10 09:30:13'),
(13, 'admin01', '$2y$10$T6A3VRQXvNhcRbGaN5291uIAxP1cSCDoJMy9gacxmQn3di5QaoLDG', 'Sonny Del Rosario', 'IT', 1, '', '', '2025-06-10 09:38:48'),
(14, 'procurement03', '$2y$10$KsCYEDtrz0Gtl1K87ruY6.AmroNYWndcUV2KJZJw2IIfHknSR0lTm', 'Tadahiro Toriyama', 'Procurement', 3, '', '', '2025-06-10 10:51:38'),
(15, 'procurement04', '$2y$10$SDG99lxnSWCGxZFsEx.Ml.WfAcG4G58hHgaAKbxdqNJ7wDtnqpPXu', 'Jainne Dela Rosa', 'Procurement', 2, '', '', '2025-06-10 10:52:48'),
(16, 'oa12345', '$2y$10$H2QirAiL/i2HhqDEXmf4juR50KywHxHtAGUVZAvabfuUZn02Jp6tS', 'Richelyn Magno', '2nd Process - OA', 2, '', '', '2025-06-10 10:53:40'),
(19, 'yazaki01', '$2y$10$7wy1HhSXVjAxTXVupLpmr.vkwM/mEiSOrDWtH8nQZy2FL.4IkHDge', 'Ruby Solis', '2nd Process - YAZAKI', 2, '', '', '2025-06-10 16:06:01');

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
-- Indexes for table `delivery_table`
--
ALTER TABLE `delivery_table`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comparison_table`
--
ALTER TABLE `comparison_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_table`
--
ALTER TABLE `delivery_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department_table`
--
ALTER TABLE `department_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `email_table`
--
ALTER TABLE `email_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `request_logs_table`
--
ALTER TABLE `request_logs_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_table`
--
ALTER TABLE `request_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_table`
--
ALTER TABLE `role_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
