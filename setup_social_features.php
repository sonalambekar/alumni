<?php
require_once 'includes/db_config.php';

try {
    echo "<h2>Setting up Social Features Tables</h2>";
    
    // Create posts table
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content TEXT NOT NULL,
        media_type ENUM('none', 'image', 'video') DEFAULT 'none',
        media_url VARCHAR(500),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Posts table created</p>";
    
    // Create post_likes table
    $sql = "CREATE TABLE IF NOT EXISTS post_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
        UNIQUE KEY unique_like (user_id, post_id)
    )";
    $pdo->exec($sql);
    echo "<p>✓ Post likes table created</p>";
    
    // Create post_shares table
    $sql = "CREATE TABLE IF NOT EXISTS post_shares (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Post shares table created</p>";
    
    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Messages table created</p>";
    
    // Create announcements table
    $sql = "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        director_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (director_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p>✓ Announcements table created</p>";
    
    // Add sample data
    $count = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
    if ($count == 0) {
        $samplePosts = [
            [
                'user_id' => 1,
                'content' => 'Welcome to GMU Alumni Network! Share your experiences and connect with fellow alumni.',
                'status' => 'approved'
            ],
            [
                'user_id' => 1,
                'content' => 'Excited to announce our upcoming alumni meet! Stay tuned for more details.',
                'status' => 'approved'
            ]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, status) VALUES (:user_id, :content, :status)");
        foreach ($samplePosts as $post) {
            $stmt->execute($post);
        }
        echo "<p>✓ Sample posts added</p>";
    }
    
    echo "<h3 style='color: green;'>✓ Social features setup completed successfully!</h3>";
    echo "<p><a href='index.php'>Go to Home</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
