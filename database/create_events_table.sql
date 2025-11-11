-- Create events table
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `time` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `event_type` enum('academic','cultural','sports','workshop','conference','other') DEFAULT 'other',
  `organizer_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_date` (`event_date`),
  KEY `organizer_id` (`organizer_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample events if needed
INSERT INTO `events` (`title`, `description`, `event_date`, `end_date`, `time`, `location`, `event_type`, `is_active`) VALUES
('Annual Alumni Reunion 2025', 'Join us for our biggest alumni gathering of the year! Reconnect with classmates, network with professionals, and celebrate our shared legacy.', '2025-12-15', '2025-12-15', '18:00 - 22:00', 'University Campus, Main Auditorium', 'cultural', 1),
('Tech Leaders Networking Night', 'An exclusive evening for alumni working in technology. Share insights, explore collaborations, and expand your professional network.', '2025-12-22', '2025-12-22', '19:00 - 21:30', 'Tech Hub, Bengaluru', 'conference', 1),
('Alumni Career Fair 2025', 'Connect with top employers and explore exciting career opportunities. Featuring 50+ companies and exclusive alumni networking sessions.', '2026-01-05', '2026-01-05', '10:00 - 17:00', 'Convention Center, Delhi', 'workshop', 1),
('Mentorship Program Launch', 'Launch of our new mentorship program connecting experienced alumni with recent graduates. Learn how you can make a difference.', '2026-01-12', '2026-01-12', '17:00 - 19:00', 'Virtual Event (Zoom)', 'workshop', 1),
('Alumni Sports Day', 'Relive your college days with cricket, football, badminton, and more! Bring your family for a fun-filled day of sports and camaraderie.', '2026-01-20', '2026-01-20', '08:00 - 18:00', 'University Sports Complex', 'sports', 1),
('Annual Gala Dinner', 'An elegant evening celebrating alumni achievements. Featuring awards ceremony, live entertainment, and gourmet dining.', '2026-02-01', '2026-02-01', '19:00 - 23:00', 'Grand Hotel, Mumbai', 'cultural', 1),
('Republic Day Celebration', 'Join the university community in celebrating India''s Republic Day with flag hoisting, cultural performances, and patriotic speeches.', '2026-01-26', '2026-01-26', '08:00 - 12:00', 'University Grounds', 'cultural', 1),
('Holi Cultural Fest', 'Celebrate the festival of colors with traditional Holi games, music, dance, and authentic Indian cuisine. Open to all students and alumni.', '2026-03-15', '2026-03-15', '10:00 - 16:00', 'College Campus', 'cultural', 1),
('Ambedkar Jayanti', 'Commemorate the birth anniversary of Dr. B.R. Ambedkar with lectures, discussions, and cultural programs highlighting social justice.', '2026-04-14', '2026-04-14', '09:00 - 11:00', 'Auditorium', 'cultural', 1),
('Independence Day Festivities', 'National celebration with flag ceremony, parade, cultural dances, and speeches honoring India''s independence.', '2026-08-15', '2026-08-15', '07:00 - 13:00', 'University Stadium', 'cultural', 1),
('Gandhi Jayanti', 'Observe Mahatma Gandhi''s birthday with prayer meetings, cleanliness drives, and discussions on non-violence and peace.', '2026-10-02', '2026-10-02', '08:00 - 10:00', 'Campus Garden', 'cultural', 1),
('Children''s Day Celebration', 'Fun activities, games, and cultural programs for children in the university community, celebrating childhood and education.', '2026-11-14', '2026-11-14', '14:00 - 18:00', 'Community Hall', 'cultural', 1),
('Christmas Cultural Evening', 'Festive evening with carol singing, nativity plays, and holiday treats to celebrate Christmas with the alumni family.', '2026-12-25', '2026-12-25', '18:00 - 22:00', 'University Chapel', 'cultural', 1);
