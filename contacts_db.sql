-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 07:51 AM
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
-- Database: `contacts`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('super','admin') DEFAULT NULL,
  `can_edit_results` tinyint(1) DEFAULT NULL,
  `can_delete_results` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `login_count` int(11) DEFAULT NULL,
  `can_edit_contacts` tinyint(1) DEFAULT NULL,
  `can_delete_contacts` tinyint(1) DEFAULT NULL,
  `can_register_users` tinyint(1) DEFAULT NULL,
  `can_manage_admins` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `role`, `can_edit_results`, `can_delete_results`, `is_active`, `last_login`, `created_at`, `updated_at`, `email`, `login_count`, `can_edit_contacts`, `can_delete_contacts`, `can_register_users`, `can_manage_admins`) VALUES
(1, 'super', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super', 1, 1, 1, '2025-10-22 09:54:01', '2025-10-22 09:50:57', '2025-10-22 09:50:57', 'super@contacts.com', 0, 1, 1, 1, 1),
(2, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 0, 1, '2025-10-22 09:58:34', '2025-10-22 09:50:57', '2025-10-22 09:50:57', 'admin@contacts.com', 0, 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `Caller_ID` varchar(255) DEFAULT NULL,
  `floor` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `Caller_ID`, `floor`, `contact_name`, `department`, `remarks`) VALUES
(1, '3000', '3rd', 'Switchboard', 'Switchboard', NULL),
(2, '3001', '3rd', 'switchboard', 'switchboard', ''),
(5, '3004', '3rd', 'Maurice Kamau', 'ICT', ''),
(6, '3005', '3rd', 'Mathew Mburu', 'ICT', ''),
(8, '3007', '3rd', 'Fridah Katua', 'ICT', ''),
(9, '3008', '3rd', 'Security Office', 'Security Officer', ''),
(10, '3009', '3rd', 'Januaris Muli', 'ICT', ''),
(11, '3042', '3rd', 'ICT Support', 'ICT', ''),
(51, '4000', '4th', 'Elias  Njoroge', 'Senior Manager,Finance', ''),
(54, '4003', '4th', 'Rachel Okuom', 'HR', ''),
(55, '4004', '4th', 'Hanniel Nyingi', 'Accounts', ''),
(56, '4005', '4th', 'Lucy Sona', 'Accounts', 'Casual'),
(58, '4007', '4th', 'Reuben Kosgei', 'Accounts', ''),
(59, '4008', '4th', 'Agnes Irungu', 'Accounts', ''),
(61, '4010', '4th', 'Zamzam Aden', 'Accounts', 'Casual'),
(63, '4012', '4th', 'Samuel Leiyan', 'Accounts', 'Casual'),
(64, '4013', '4th', 'Pauline Waweru', 'Salaries', ''),
(65, '4014', '4th', 'Philomena Matindi', 'HR', ''),
(66, '4015', '4th', 'James Kinyanjui', 'HR', ''),
(68, '4017', '4th', 'Rael Akhaenda', 'HR', ''),
(70, '4019', '4th', 'Ali Njoroge', 'Salaries', ''),
(74, '4023', '4th', 'Evelyne Wangui', 'HR', ''),
(76, '4025', '4th', 'Philip Nganga', 'Accounts', ''),
(77, '4026', '4th', 'Francis Kiplangat', 'Finance', ''),
(78, '4027', '4th', 'Caroline Omenya', 'Accounts', ''),
(79, '4028', '4th', 'Maurice Muliango', 'Accounts', ''),
(81, '4038', '4th', 'Jackline Lugalia', 'Salaries', ''),
(83, '4032', '4th', 'Ibrahim Hussein', 'Accounts', ''),
(84, '4033', '4th', 'Eunice Muasya', 'HR', 'Casual'),
(86, '4035', '4th', 'Agnes Waweru', 'Administration', ''),
(87, '4036', '4th', 'Rukia Hussein', 'Administration', ''),
(88, '4037', '4th', 'Milkah Mwangi', 'Finance', ''),
(90, '4039', '4th', 'Simon Nguli ', 'Personnel/Registry', ''),
(94, '4043', '4th', 'Sharon Wacuka', 'Accounts', 'Casual'),
(95, '4044', '4th', 'Mary Mwakai', 'Personnel/Registry', ''),
(97, '4046', '4th', 'Catherine Adhiambo', 'HR', ''),
(98, '4047', '4th', 'Jecinta Akinyi', 'HR', 'Casual'),
(100, '4049', '4th', 'Hezekiel Kimani', 'Accounts', 'Casual'),
(102, '4051', '4th', 'Eric Omondi', 'Finance', 'Casual'),
(103, '5000', '5th', 'Fred Ayiera', 'Procurement', ''),
(104, '5001', '5th', 'Winnie Chelagat', 'Procurement', ''),
(105, '5002', '5th', 'Charles Obudho ', 'Transport', ''),
(106, '5003', '5th', 'Bevalin Lundu', 'Procurement', ''),
(107, '5004', '5th', 'Thadeus Odinga', 'Procurement', ''),
(108, '5005', '5th', 'Samson Kalungu', 'Procurement', ''),
(110, '5007', '5th', ' Martin Shikuku', 'Procurement', ''),
(111, '5008', '5th', 'Johnson Kitema ', 'Procurement', ''),
(112, '5009', '5th', 'Joseph Simwa ', 'Procurement', ''),
(113, '5010', '5th', 'Pamela Aketch ', 'Transport', ''),
(117, '5014', '5th', 'Vitalis Ochola', 'Administration', ''),
(119, '5016', '5th', 'Faith Ngichu', 'Procurement', ''),
(120, '5017', '5th', ' Samuel Migwi', 'Procurement', ''),
(127, '6000', '6th', 'Robert Nderitu', 'Director Production', ''),
(128, '6001', '6th', 'Mary Eliajah', 'Production', ''),
(132, '6005', '6th', 'James Gatundu ', 'NIPFFN Project', ''),
(133, '6006', '6th', 'Stephen Ngugi', 'Production', ''),
(134, '6007', '6th', 'Silvester Maingi', 'Production', ''),
(135, '6008', '6th', 'John Mburu ', 'Production', ''),
(136, '6009', '6th', 'Tabaitha  Weru', 'Production', ''),
(138, '6011', '6th', 'Christopher Kyangu', 'Production', ''),
(139, '6012', '6th', 'Christopher Mwangi', 'Production', ''),
(140, '6013', '6th', 'Paul Nderitu', 'Production', ''),
(141, '6014', '6th', 'Simon  Gaitho', 'Production', ''),
(146, '6019', '6th', 'Maurice Otieno', 'Production', ''),
(148, '6021', '6th', 'Rebbeca Waweru', 'Production', ''),
(151, '6024', '6th', 'Penina Kamau', 'Production', ''),
(155, '6028', '6th', 'Newton Kigwiri', 'Production', ''),
(157, '6030', '6th', 'P.Gichochi', 'Production', ''),
(158, '6031', '6th', 'Benson Karugu ', 'Production', ''),
(159, '6032', '6th', 'Njoroge Nyoike', 'Production', ''),
(160, '6033', '6th', 'Tupege Kasongwa', 'NIPFFN Project', ''),
(162, '6035', '6th', 'Erick Macharia', 'NIPFFN Project', ''),
(164, '6037', '6th', 'Lilian Wambui', 'NIPFFN Project', ''),
(166, '6039', '6th', 'Allan Gathuru', 'NIPFFN Project', ''),
(167, '6040', '6th', 'Tom Mutua', 'NIPFFN Project', ''),
(169, '6042', '6th', 'Ali Noor ', 'Production', ''),
(170, '6043', '6th', ' Alice Wanjohi', 'Production', ''),
(171, '6044', '6th', 'Wiinnie Kiraka', 'Production', ''),
(172, '6045', '6th', ' Benson Muroki ', 'Production', ''),
(173, '6046', '6th', 'Vincent Kemei', 'Production', ''),
(175, '6048', '6th', 'Robert Mumo', 'Production', ''),
(177, '6050', '6th', 'Sammy Kibet', 'Production', ''),
(183, '6056', '6th', 'Domminic Oduge', 'Production', ''),
(194, '1000', '10th', 'Collins Omondi', 'Director MACRO', ''),
(195, '1001', '10th', 'Gladys Ogega ', 'Macro', ''),
(197, '1003', '10th', 'Benjamin Avusevwa', 'Macro', ''),
(198, '1004', '10th', 'Benjamin Muchiri', 'Macro', ''),
(199, '1005', '10th', 'William Etwasi', 'Macro', ''),
(200, '1006', '10th', 'Peter Kihara', 'Macro', ''),
(201, '1007', '10th', ' Gladys Mbaruku', 'Macro', ''),
(204, '1010', '10th', 'Johnston Poipoi', 'Macro', ''),
(205, '1011', '10th', 'Tabitha Waweru', 'Macro', ''),
(206, '1012', '10th', 'Rose Malova', 'Macro', ''),
(207, '1013', '10th', ' Peter Kamau ', 'Macro', ''),
(208, '1014', '10th', 'Eunice Munga', 'Macro', ''),
(209, '1015', '10th', ' Pauline Wangeci', 'Macro', ''),
(210, '1016', '10th', 'Antony Makau', 'Macro', ''),
(211, '1017', '10th', ' Milton Tonui', 'Macro', ''),
(212, '1018', '10th', 'Cynthia Mulama', 'Macro', ''),
(213, '1019', '10th', ' William Mbusya', 'Macro', ''),
(214, '1020', '10th', 'Martin Maende', 'Macro', ''),
(215, '1021', '10th', 'Adan Hussein', 'Macro', ''),
(217, '1023', '10th', 'Justus Ndambuki', 'Macro', ''),
(218, '1024', '10th', 'Collins Ndamire', 'Macro', ''),
(219, '1025', '10th', 'Joseph Thoya', 'Macro', ''),
(222, '1028', '10th', 'Linah Ngumba', 'Macro', ''),
(224, '1030', '10th', 'James Abuya', 'Macro', ''),
(231, '1037', '10th', 'Oliver Mukolwe', 'Macro', ''),
(234, '1040', '10y', 'Doris Syombua', 'Macro', ''),
(235, '1041', '10th', 'Justin Ruto', 'Macro', ''),
(236, '1042', '10th', 'Lensa Apondi', 'Macro', ''),
(237, '1043', '10th', 'Rosemary Bowen', 'Macro', ''),
(238, '1044', '10th', 'Hirum Mbatia', 'Macro', ''),
(249, '1100', '11th', 'Samuel Ogola', 'Population', ''),
(250, '1101', '11th', 'Abdikadir Awes', 'Population', ''),
(251, '1102', '11th', 'Robert Buluma', 'Population', ''),
(252, '1103', '11th', 'Godfrey Otieno', 'Population', ''),
(253, '1104', '11th', 'Macdonald Obudho', 'Population', ''),
(256, '1107', '11th', 'Florance Nyokabi', 'Population', ''),
(257, '1108', '11th', 'Joshua Musyimi', 'Population', ''),
(258, '1109', '11th', 'James Munguti', 'Population', ''),
(259, '1110', '11th', 'Rosemary Kongani', 'Population', ''),
(268, '1119', '11th', 'Winnie Kemunto', 'Population', ''),
(275, '1126', '11th', 'Renice Bunde', 'Population', ''),
(277, '1128', '11th', ' Caroline Gatwiri', 'Population', ''),
(279, '1130', '11th', 'Lewis Macharia ', 'Population', ''),
(280, '1131', '11th', 'Peter Wanjohi', 'Population', ''),
(283, '1134', '11th', 'Mose Job', 'Population', ''),
(284, '1135', '11th', ' Andrew Ibwaga', 'Population', ''),
(287, '1138', '11th', 'Elias Nyaga', 'Population', ''),
(288, '1139', '11th', 'Michael Musyoka', 'Population', ''),
(289, '1140', '11th', 'John Makau', 'Population', ''),
(294, '1145', '11th', 'Jim Kirimi', 'Population', ''),
(299, '1150', '11th', 'Paul Waweru', 'Population', ''),
(301, '1152', '11th', 'Schola Kingi', 'Population', ''),
(302, '1153', '11th', ' Priscila Ndayara', 'Population', ''),
(307, '1200', '12th', 'Paul Samoei', 'AG. Director', ''),
(308, '1201', '12th', 'Mary Kimani', 'Sampling', ''),
(309, '1202', '12th', 'Paul Samoei', 'Sampling', ''),
(310, '1203', '12th', 'Henry Osoro', 'Sampling', ''),
(312, '1205', '12th', 'Mary Karimi', 'Sampling', ''),
(314, '1207', '12th', 'Mirembo Pauline', 'Sampling', ''),
(326, '1219', '12th', 'Silas Mulwa', 'Sampling', ''),
(327, '1220', '12th', 'Oganga Caneble', 'Sampling', ''),
(331, '1224', '12th', 'Geofrey Kariuki', 'Sampling', ''),
(332, '1225', '12th', 'Edwin Mtto', 'Sampling', ''),
(334, '1227', '12th', 'Zachary Ochola', 'Sampling', ''),
(335, '1228', '12th', 'Prisca Mwangi', 'Sampling', ''),
(336, '1229', '12th', 'James Nganga', 'Sampling', ''),
(337, '1230', '12th', 'Samuel Mwenda ', 'Sampling', ''),
(343, '1236', '12th', 'Francis Mwandembo', 'Sampling', ''),
(344, '1237', '12th', 'Thomas Alubokho', 'Sampling', ''),
(347, '1240', '12th', 'George Magara', 'Sampling', ''),
(350, '1243', '12th', 'Samuel Kipruto', 'Sampling', ''),
(354, '1247', '12th', 'Nganga Pius', 'Sampling', ''),
(355, '1248', '12th', 'Sarah Omache', 'Sampling', ''),
(358, '1251', '12th', 'David Ngesa', 'Sampling', ''),
(359, '1300', '13th', 'Macdonald G. Obudho(DG)', 'Director General', ''),
(360, '1301', '13th', 'David Mwangi', 'Internal Audit', ''),
(361, '1302', '13th', 'Mwanyika Trizer', 'Manager, Communication', ''),
(362, '1303', '13th', 'Rose Awino', 'HR', ''),
(363, '1304', '13th', 'Betty Kawira', 'DG\'s Office', ''),
(364, '1305', '13th', 'Rajab Mbaruku', 'ICT', ''),
(365, '1306', '13th', 'Jane Kamau', 'DG\'s Office', ''),
(367, '1308', '13th', 'Jane Weru', 'Corporate Service', ''),
(368, '1309', '13th', 'Winnie Makoma', 'Corporate Service', ''),
(370, '1311', '13th', 'Rebeca Gikonyo', 'Internal Audit', ''),
(371, '1312', '13th', 'Samantha Mwangi', 'Intern-Communication', ''),
(372, '1313', '13th', 'Salome Kihara', 'Strategy', ''),
(373, '1314', '13th', 'George Awino', 'Internal Audit', ''),
(375, '1316', '13th', 'Rose Ayugu', 'HR', '');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `attempt_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(255) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_caller` (`Caller_ID`),
  ADD KEY `idx_contact_department` (`department`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=376;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
