-- Database Schema Updates for Institute-based Filtering
-- Execute these commands on your MySQL database: alumni

-- Add institute filtering columns to announcements table
ALTER TABLE announcements 
ADD COLUMN target_institute VARCHAR(255) NULL AFTER content,
ADD COLUMN is_global BOOLEAN DEFAULT FALSE AFTER target_institute;

-- Add institute filtering columns to posts table  
ALTER TABLE posts 
ADD COLUMN target_institute VARCHAR(255) NULL AFTER status,
ADD COLUMN is_global BOOLEAN DEFAULT FALSE AFTER target_institute;

-- Add indexes for better performance
CREATE INDEX idx_announcements_target_institute ON announcements(target_institute);
CREATE INDEX idx_announcements_is_global ON announcements(is_global);
CREATE INDEX idx_posts_target_institute ON posts(target_institute);
CREATE INDEX idx_posts_is_global ON posts(is_global);

-- Set all existing announcements and posts as global (backward compatibility)
UPDATE announcements SET is_global = TRUE WHERE target_institute IS NULL;
UPDATE posts SET is_global = TRUE WHERE target_institute IS NULL;

-- Verify the changes
SELECT 'Announcements table structure:' as info;
DESCRIBE announcements;

SELECT 'Posts table structure:' as info;
DESCRIBE posts;

SELECT 'Announcements count by type:' as info;
SELECT 
    COUNT(*) as total_announcements,
    SUM(CASE WHEN is_global = 1 THEN 1 ELSE 0 END) as global_announcements,
    SUM(CASE WHEN is_global = 0 THEN 1 ELSE 0 END) as institute_specific_announcements
FROM announcements;

SELECT 'Posts count by type:' as info;
SELECT 
    COUNT(*) as total_posts,
    SUM(CASE WHEN is_global = 1 THEN 1 ELSE 0 END) as global_posts,
    SUM(CASE WHEN is_global = 0 THEN 1 ELSE 0 END) as institute_specific_posts
FROM posts;
