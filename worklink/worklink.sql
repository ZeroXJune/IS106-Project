-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 09:10 AM
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
-- Database: `worklink`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_id` int(11) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `resume_link` varchar(500) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('Applied','Under Review','Interview','Offered','Rejected') NOT NULL DEFAULT 'Applied',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `employer_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `company` varchar(200) NOT NULL,
  `location` varchar(150) NOT NULL,
  `type` enum('Full-time','Part-time','Internship','Contract') NOT NULL DEFAULT 'Full-time',
  `salary` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `employer_id`, `title`, `company`, `location`, `type`, `salary`, `description`, `tags`, `deadline`, `created_at`) VALUES
(1, 1, 'Frontend Developer', 'TechCorp PH', 'Makati, Metro Manila', 'Full-time', '₱35,000–₱50,000', 'Build responsive web interfaces. Collaborate with design and backend teams.', 'PHP,CSS,JavaScript', '2026-06-01', '2026-05-19 06:23:06'),
(2, 2, 'Systems Analyst', 'BDO Unibank', 'Ortigas, Pasig', 'Full-time', '₱30,000–₱45,000', 'Analyze and document business processes. Work with stakeholders on IT solutions.', 'Analysis,Documentation,SQL', '2026-06-15', '2026-05-19 06:23:06'),
(3, 1, 'IT Support Specialist', 'TechCorp PH', 'BGC, Taguig', 'Full-time', '₱22,000–₱28,000', 'Provide technical support and troubleshoot hardware/software issues.', 'Networking,Hardware,Windows', '2026-05-30', '2026-05-19 06:23:06'),
(4, 3, 'Junior Data Analyst', 'Globe Telecom', 'Mandaluyong', 'Full-time', '₱28,000–₱38,000', 'Collect and analyze datasets. Create dashboards and reports.', 'Excel,SQL,Power BI', '2026-06-20', '2026-05-19 06:23:06'),
(5, 3, 'Web Developer Intern', 'Globe Telecom', 'Remote', 'Internship', '₱500/day allowance', 'Assist in building web applications. Learn agile practices.', 'HTML,CSS,PHP', '2026-05-25', '2026-05-19 06:23:06'),
(6, 1, 'Backend Developer', 'TechCorp PH', 'Bohol', 'Full-time', '44,000', 'asdjfl;kasf', 'Phyton', '2026-05-19', '2026-05-19 06:24:47'),
(7, 6, 'Frontend Developer', 'ZeroXJune', 'Bohol', 'Full-time', '35,000', '5 Years of Experience Needed', 'HTML, CSS, Bootstrap', '2026-05-19', '2026-05-19 06:35:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('applicant','employer') NOT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `about` text DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `headline`, `location`, `skills`, `about`, `company`, `position`, `created_at`) VALUES
(1, 'Rico Reyes', 'rico@techcorp.ph', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', NULL, NULL, NULL, NULL, 'TechCorp PH', 'HR Manager', '2026-05-19 06:23:06'),
(2, 'Ana Lim', 'ana@bdo.com.ph', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', NULL, NULL, NULL, NULL, 'BDO Unibank', 'Talent Acquisition', '2026-05-19 06:23:06'),
(3, 'Mark Dela Cruz', 'mark@globe.com.ph', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'employer', NULL, NULL, NULL, NULL, 'Globe Telecom', 'Recruitment Lead', '2026-05-19 06:23:06'),
(4, 'Maria Santos', 'maria@email.com', '$2y$10$FfoifyhQnLOiY8voIVsMhuE5zO7HsqS.D2grCFaSN8NVKYZIM17zS', 'applicant', 'BS Information Systems | 3rd Year', 'Bacolod City', 'PHP,SQL,Bootstrap', 'Passionate IS student looking for opportunities to grow.', NULL, NULL, '2026-05-19 06:23:06'),
(5, 'Alber June Mumar', 'alber@email.com', '$2y$10$4lmxD3hvaCNXhYM4YACieOBBJGYZBeCtTB7uhwUYhYtLMWiZqu0rm', 'applicant', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 06:28:26'),
(6, 'Alber June M. Mumar', 'zeroxjune@gmail.com', '$2y$10$9RHIpspM0khvDCbq.QjevOG6ndiDG/D4ysXlduKxA.RlSCI3d9/na', 'employer', NULL, NULL, NULL, NULL, 'ZeroXJune', 'CEO', '2026-05-19 06:34:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_application` (`job_id`,`applicant_id`),
  ADD KEY `applicant_id` (`applicant_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employer_id` (`employer_id`);

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
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`applicant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
