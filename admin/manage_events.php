<?php
// Admin Events Management
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../includes/db_config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            // Add new event
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $event_date = $_POST['event_date'] ?? '';
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $location = $_POST['location'] ?? '';
            $event_type = $_POST['event_type'] ?? 'other';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($description) && !empty($event_date)) {
                try {
                    // Check if organizer_id column exists in events table
                    $columns = $pdo->query("DESCRIBE events")->fetchAll(PDO::FETCH_ASSOC);
                    $hasOrganizerId = false;
                    foreach ($columns as $column) {
                        if ($column['Field'] === 'organizer_id') {
                            $hasOrganizerId = true;
                            break;
                        }
                    }

                    if ($hasOrganizerId) {
                        // Insert with organizer_id
                        $stmt = $pdo->prepare("
                            INSERT INTO events (title, description, event_date, end_date, location, event_type, organizer_id, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$title, $description, $event_date, $end_date, $location, $event_type, $_SESSION['user_id'], $is_active]);
                    } else {
                        // Insert without organizer_id
                        $stmt = $pdo->prepare("
                            INSERT INTO events (title, description, event_date, end_date, location, event_type, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$title, $description, $event_date, $end_date, $location, $event_type, $is_active]);
                    }

                    $success = "Event added successfully!";
                } catch(PDOException $e) {
                    $error = "Error adding event: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'edit') {
            // Update event
            $id = $_POST['event_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $event_date = $_POST['event_date'] ?? '';
            $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            $location = $_POST['location'] ?? '';
            $event_type = $_POST['event_type'] ?? 'other';
            $is_active = isset($_POST['is_active']) ? 1 : 0;

            if (!empty($title) && !empty($description) && !empty($event_date) && !empty($id)) {
                try {
                    // Check if organizer_id column exists in events table
                    $columns = $pdo->query("DESCRIBE events")->fetchAll(PDO::FETCH_ASSOC);
                    $hasOrganizerId = false;
                    foreach ($columns as $column) {
                        if ($column['Field'] === 'organizer_id') {
                            $hasOrganizerId = true;
                            break;
                        }
                    }

                    if ($hasOrganizerId) {
                        // Update with organizer_id
                        $stmt = $pdo->prepare("
                            UPDATE events
                            SET title = ?, description = ?, event_date = ?, end_date = ?, location = ?, event_type = ?, is_active = ?, organizer_id = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $event_date, $end_date, $location, $event_type, $is_active, $_SESSION['user_id'], $id]);
                    } else {
                        // Update without organizer_id
                        $stmt = $pdo->prepare("
                            UPDATE events
                            SET title = ?, description = ?, event_date = ?, end_date = ?, location = ?, event_type = ?, is_active = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$title, $description, $event_date, $end_date, $location, $event_type, $is_active, $id]);
                    }

                    $success = "Event updated successfully!";
                } catch(PDOException $e) {
                    $error = "Error updating event: " . $e->getMessage();
                }
            } else {
                $error = "Please fill in all required fields";
            }
        } elseif ($action === 'delete') {
            // Delete event
            $id = $_POST['event_id'] ?? 0;

            if (!empty($id)) {
                try {
                    $stmt = $pdo->delete("DELETE FROM events WHERE id = ?", [$id]);
                    $success = "Event deleted successfully!";
                } catch(PDOException $e) {
                    $error = "Error deleting event: " . $e->getMessage();
                }
            }
        }
    }
}

// Initialize variables
$events = [];
$success = '';
$error = '';

// Get all events with attendee counts
try {
    // Check if tables exist before querying
    $tablesExist = true;
    try {
        $pdo->query("SELECT 1 FROM events LIMIT 1");
        $pdo->query("SELECT 1 FROM users LIMIT 1");
    } catch(PDOException $e) {
        $tablesExist = false;
    }

    if ($tablesExist) {
        try {
            // First check if organizer_id column exists in events table
            $columns = $pdo->query("DESCRIBE events")->fetchAll(PDO::FETCH_ASSOC);
            $hasOrganizerId = false;
            foreach ($columns as $column) {
                if ($column['Field'] === 'organizer_id') {
                    $hasOrganizerId = true;
                    break;
                }
            }

            if ($hasOrganizerId) {
                // Try to get organizer name, fallback to user ID if name doesn't exist
                $stmt = $pdo->query("
                    SELECT e.*,
                           COALESCE(u.full_name, u.name, CONCAT('User #', e.organizer_id)) as organizer_name,
                           COALESCE(COUNT(er.id), 0) as current_attendees
                    FROM events e
                    LEFT JOIN users u ON e.organizer_id = u.id
                    LEFT JOIN event_registrations er ON e.id = er.event_id AND er.attendance_status != 'cancelled'
                    GROUP BY e.id
                    ORDER BY e.event_date DESC
                ");
                $events = $stmt->fetchAll();
            } else {
                // Events table doesn't have organizer_id column, query without it
                $stmt = $pdo->query("
                    SELECT e.*,
                           CONCAT('System Event') as organizer_name,
                           COALESCE(COUNT(er.id), 0) as current_attendees
                    FROM events e
                    LEFT JOIN event_registrations er ON e.id = er.event_id AND er.attendance_status != 'cancelled'
                    GROUP BY e.id
                    ORDER BY e.event_date DESC
                ");
                $events = $stmt->fetchAll();
            }
        } catch(PDOException $e) {
            // If the join fails, try without the author name
            $stmt = $pdo->query("
                SELECT e.*,
                       CONCAT('System Event') as author_name,
                       COALESCE(COUNT(er.id), 0) as current_attendees
                FROM events e
                LEFT JOIN event_registrations er ON e.id = er.event_id AND er.attendance_status != 'cancelled'
                GROUP BY e.id
                ORDER BY e.event_date DESC
            ");
            $events = $stmt->fetchAll();
        }
    } else {
        $error = "Database tables not found. Please run the database setup first.";
    }
} catch(PDOException $e) {
    $error = "Error loading events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f5f7fb;
            --text-color: #333;
            --text-light: #6b7280;
            --border-color: #e5e7eb;
            --white: #ffffff;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .admin-header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .admin-nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .admin-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .back-btn {
            background: var(--text-light);
            color: var(--white);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4b5563;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-color);
        }

        .add-btn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s ease;
        }

        .add-btn:hover {
            background: #4a1919;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-container {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .form-container h3 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-input, .form-textarea, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(91, 31, 31, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background: #4a1919;
        }

        .btn-secondary {
            background: var(--text-light);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        .btn-danger {
            background: #dc2626;
            color: var(--white);
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .events-table {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }

        .table-header {
            background: var(--bg-light);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-color);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .type-meetup {
            background: #ddd6fe;
            color: #7c3aed;
        }

        .type-conference {
            background: #bfdbfe;
            color: #1d4ed8;
        }

        .type-workshop {
            background: #d1fae5;
            color: #065f46;
        }

        .type-social {
            background: #fed7d7;
            color: #991b1b;
        }

        .type-webinar {
            background: #fef3c7;
            color: #d97706;
        }

        .type-other {
            background: #f3f4f6;
            color: #374151;
        }

        .attendees-info {
            font-size: 12px;
            color: var(--text-light);
        }

        .actions-cell {
            white-space: nowrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="admin-nav">
            <div class="admin-brand">Events Management</div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="dashboard-container">

        <!-- Add Event Form -->
        <div class="form-container">
            <h3><i class="fas fa-plus-circle"></i> Add New Event</h3>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="title">Event Title *</label>
                        <input type="text" id="title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="event_type">Event Type</label>
                        <select id="event_type" name="event_type" class="form-select">
                            <option value="meetup">Meetup</option>
                            <option value="conference">Conference</option>
                            <option value="workshop">Workshop</option>
                            <option value="social">Social Event</option>
                            <option value="webinar">Webinar</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="description">Description *</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="event_date">Start Date & Time *</label>
                        <input type="datetime-local" id="event_date" name="event_date" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="end_date">End Date & Time (Optional)</label>
                        <input type="datetime-local" id="end_date" name="end_date" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="location">Location</label>
                        <input type="text" id="location" name="location" class="form-input" placeholder="City, State/Country">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="is_active" name="is_active" checked> Event is Active (Published)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Event
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>

        <!-- Events List -->
        <div class="events-table">
            <div class="table-header">
                <h3 class="table-title"><i class="fas fa-list"></i> All Events</h3>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Attendees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($events) && !empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                        <br>
                                        <small style="color: var(--text-light);">
                                            by <?php echo htmlspecialchars($event['author_name']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="type-badge type-<?php echo $event['event_type']; ?>">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($event['event_date'])); ?>
                                        <br>
                                        <small style="color: var(--text-light);">
                                            <?php echo date('g:i A', strtotime($event['event_date'])); ?>
                                        </small>
                                    </td>
                                    <td><?php echo isset($event['current_attendees']) ? $event['current_attendees'] : 0; ?></td>
                                    <td><?php echo isset($event['organizer_name']) ? htmlspecialchars($event['organizer_name']) : 'System'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo (isset($event['is_active']) && $event['is_active']) ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo (isset($event['is_active']) && $event['is_active']) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-cell">
                                        <button class="btn btn-secondary" onclick="editEvent(<?php echo $event['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-light);">
                                    <i class="fas fa-calendar-alt" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                    No events found. Create your first event above.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function editEvent(eventId) {
            // Find the event data and populate the modal
            const events = <?php echo json_encode($events); ?>;
            const event = events.find(e => e.id == eventId);

            if (event) {
                document.getElementById('edit_event_id').value = event.id;
                document.getElementById('edit_title').value = event.title || '';
                document.getElementById('edit_event_type').value = event.event_type || 'other';
                document.getElementById('edit_description').value = event.description || '';
                document.getElementById('edit_event_date').value = event.event_date ? event.event_date.slice(0, 16) : '';
                document.getElementById('edit_end_date').value = event.end_date ? event.end_date.slice(0, 16) : '';
                document.getElementById('edit_location').value = event.location || '';
                document.getElementById('edit_is_active').checked = (event.is_active == 1);

                document.getElementById('editModal').classList.add('show');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('show');
        }
    </script>

    <!-- Edit Modal -->
    <div class="modal" id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; border-radius: 8px; padding: 30px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--primary-color);">
                <h3 class="modal-title">Edit Event</h3>
                <button class="close-btn" onclick="closeEditModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="event_id" id="edit_event_id">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_title">Event Title *</label>
                        <input type="text" id="edit_title" name="title" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_event_type">Event Type</label>
                        <select id="edit_event_type" name="event_type" class="form-select">
                            <option value="meetup">Meetup</option>
                            <option value="conference">Conference</option>
                            <option value="workshop">Workshop</option>
                            <option value="social">Social Event</option>
                            <option value="webinar">Webinar</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="edit_description">Description *</label>
                    <textarea id="edit_description" name="description" class="form-textarea" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_event_date">Start Date & Time *</label>
                        <input type="datetime-local" id="edit_event_date" name="event_date" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="edit_end_date">End Date & Time (Optional)</label>
                        <input type="datetime-local" id="edit_end_date" name="end_date" class="form-input">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location" class="form-input" placeholder="City, State/Country">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="edit_is_active" name="is_active"> Event is Active (Published)
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Event
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
