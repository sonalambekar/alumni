-- Add location column to users table
ALTER TABLE users 
ADD COLUMN location POINT DEFAULT NULL 
COMMENT 'Stores user\'s current location in POINT(longitude, latitude) format';
