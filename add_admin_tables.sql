-- Add remaining tables for Admin Dashboard to existing alumni database
-- This script adds the missing tables needed for the admin dashboard functionality

USE alumni;

-- --------------------------------------------------------
-- Table structure for table `noticeboard`
-- --------------------------------------------------------
CREATE TABLE `noticeboard` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `priority` enum('high','medium','low') DEFAULT 'medium',
  `is_active` tinyint(1) DEFAULT 1,
  `publish_date` datetime DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `news`
-- --------------------------------------------------------
CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `publish_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `events`
-- --------------------------------------------------------
CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `registration_deadline` datetime DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `event_type` varchar(100) DEFAULT 'General',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `event_registrations`
-- --------------------------------------------------------
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` enum('registered','attended','cancelled') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `jobs`
-- --------------------------------------------------------
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `job_type` enum('full-time','part-time','internship','contract','freelance') DEFAULT 'full-time',
  `experience_level` enum('entry','mid','senior','executive') DEFAULT 'entry',
  `salary_min` decimal(10,2) DEFAULT NULL,
  `salary_max` decimal(10,2) DEFAULT NULL,
  `application_deadline` date DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `application_link` varchar(500) DEFAULT NULL,
  `posted_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `galleries`
-- --------------------------------------------------------
CREATE TABLE `galleries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `photos`
-- --------------------------------------------------------
CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `gallery_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `interest_groups`
-- --------------------------------------------------------
CREATE TABLE `interest_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `group_image` varchar(255) DEFAULT NULL,
  `max_members` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `group_members`
-- --------------------------------------------------------
CREATE TABLE `group_members` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `role` enum('member','moderator','admin') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Add AUTO_INCREMENT and PRIMARY KEYS
-- --------------------------------------------------------
ALTER TABLE `noticeboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`);

ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`event_id`,`user_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

ALTER TABLE `galleries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gallery_id` (`gallery_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

ALTER TABLE `interest_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

ALTER TABLE `group_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_membership` (`group_id`,`user_id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `user_id` (`user_id`);

-- --------------------------------------------------------
-- Set AUTO_INCREMENT values
-- --------------------------------------------------------
ALTER TABLE `noticeboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `galleries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `interest_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `group_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- Add FOREIGN KEY constraints
-- --------------------------------------------------------
ALTER TABLE `noticeboard`
  ADD CONSTRAINT `noticeboard_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `galleries`
  ADD CONSTRAINT `galleries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `photos_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `interest_groups`
  ADD CONSTRAINT `interest_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `group_members`
  ADD CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `interest_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------
-- Insert sample admin user if not exists (for admin dashboard)
-- --------------------------------------------------------
INSERT IGNORE INTO `users` (
  `name`, `usn`, `year_of_graduation`, `phone_number`, `email_id`,
  `institute`, `branch`, `designation`, `password`, `profile_picture`,
  `bio`, `is_director`, `is_active`
) VALUES (
  'Admin User', 'ADMIN001', 2023, '9999999999', 'admin@gmu.ac.in',
  'GMU', 'ADMIN', 'System Administrator',
  '$2y$10$.9lMhm4Zzn8/AoK9c5YfyOlLXdVW10EtVWXuZshABLv452D1sUTDi',
  'default.jpg', 'System Administrator for Alumni Platform', 0, 1
);

-- --------------------------------------------------------
-- Insert sample data for testing
-- --------------------------------------------------------
-- Sample notice
INSERT INTO `noticeboard` (`title`, `content`, `author_id`, `priority`, `is_active`)
SELECT 'Welcome to GMU Alumni Platform', 'Welcome to the official GMU Alumni Connect platform. Stay connected with your fellow alumni and participate in various activities.', `id`, 'high', 1
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

-- Sample news
INSERT INTO `news` (`title`, `content`, `excerpt`, `author_id`, `is_featured`, `is_active`)
SELECT 'Alumni Reunion 2025 Announced', 'We are excited to announce the annual alumni reunion for 2025. The event will be held at the university campus and will feature various activities, guest speakers, and networking opportunities.', 'Join us for the annual alumni reunion featuring guest speakers and networking opportunities.', `id`, 1, 1
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

-- Sample event
INSERT INTO `events` (`title`, `description`, `event_date`, `location`, `max_attendees`, `organizer_id`, `event_type`)
SELECT 'Annual Alumni Meet 2025', 'Annual gathering of GMU alumni to reconnect, network, and celebrate achievements.', '2025-12-15 10:00:00', 'GMU Campus Auditorium', 200, `id`, 'Reunion'
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

-- Sample job
INSERT INTO `jobs` (`title`, `company`, `description`, `location`, `job_type`, `experience_level`, `posted_by`)
SELECT 'Software Engineer', 'Tech Solutions Inc.', 'Looking for talented software engineers to join our growing team.', 'Bangalore', 'full-time', 'mid', `id`
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

-- Sample gallery
INSERT INTO `galleries` (`name`, `description`, `created_by`)
SELECT 'Campus Memories', 'Collection of memorable moments from campus life', `id`
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

-- Sample interest group
INSERT INTO `interest_groups` (`name`, `description`, `category`, `created_by`)
SELECT 'Technology Enthusiasts', 'A group for alumni interested in technology, innovation, and digital transformation.', 'Technology', `id`
FROM `users` WHERE `email_id` = 'admin@gmu.ac.in' LIMIT 1;

COMMIT;
