<?php
require_once '../includes/db_config.php';

// Initialize events array
$events = [];

// Check database connection
if (!$pdo) {
    die("Database connection failed");
}

// Check if table exists
$tableCheck = $pdo->query("SHOW TABLES LIKE 'events'");
if ($tableCheck->rowCount() == 0) {
    die("Events table does not exist");
}

// Fetch active events from database
$currentDate = date('Y-m-d');
$query = "
    SELECT 
        id, 
        title, 
        description, 
        event_date, 
        end_date,
        location,
        event_type
    FROM events 
    WHERE is_active = 1 
    AND (end_date IS NULL OR end_date >= :currentDate)
    ORDER BY event_date ASC
";

$stmt = $pdo->prepare($query);
$stmt->execute(['currentDate' => $currentDate]);
$dbEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format events for the calendar
foreach ($dbEvents as $event) {
    $eventDate = $event['event_date'];
    $endDate = $event['end_date'] ?? $event['event_date'];
    
    // If it's a multi-day event, create entries for each day
    $current = new DateTime($eventDate);
    $end = new DateTime($endDate);
    $end->modify('+1 day'); // Include the end date
    
    while ($current < $end) {
        $dateKey = $current->format('Y-m-d');
        
        $events[$dateKey][] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'location' => $event['location'] ?? '',
            'description' => $event['description'] ?? '',
            'event_type' => $event['event_type']
        ];
        
        $current->modify('+1 day');
    }
}

// Add sample events for November, December, and January
$sampleEvents = [
    [
        'id' => 'sample1',
        'title' => 'Alumni Networking Mixer',
        'description' => 'Join us for an evening of networking with fellow alumni. Drinks and appetizers will be served.',
        'location' => 'GMU Alumni Center',
        'event_date' => date('Y-11-15 18:00:00'),
        'event_type' => 'networking'
    ],
    [
        'id' => 'sample2',
        'title' => 'Annual Alumni Gala',
        'description' => 'Our biggest event of the year! Celebrate with fellow alumni and support student scholarships.',
        'location' => 'Grand Ballroom, University Center',
        'event_date' => date('Y-12-05 19:00:00'),
        'event_type' => 'gala'
    ],
    [
        'id' => 'sample3',
        'title' => 'Holiday Social',
        'description' => 'Celebrate the holiday season with your GMU family. Ugly sweaters encouraged!',
        'location' => 'The Hub',
        'event_date' => date('Y-12-20 17:00:00'),
        'event_type' => 'social'
    ],
    [
        'id' => 'sample4',
        'title' => 'New Year Kickoff Brunch',
        'description' => 'Start the new year right with a delicious brunch and goal-setting workshop.',
        'location' => 'Johnson Center',
        'event_date' => (date('Y')+1) . '-01-10 11:00:00',
        'event_type' => 'workshop'
    ],
    [
        'id' => 'sample5',
        'title' => 'Alumni Career Fair',
        'description' => 'Connect with top employers and explore career opportunities.',
        'location' => 'Dewberry Hall',
        'event_date' => (date('Y')+1) . '-01-25 10:00:00',
        'event_type' => 'career'
    ]
];

// Add sample events to the events array
foreach ($sampleEvents as $event) {
    $dateKey = date('Y-m-d', strtotime($event['event_date']));
    $events[$dateKey][] = [
        'id' => $event['id'],
        'title' => $event['title'],
        'description' => $event['description'],
        'location' => $event['location'],
        'event_type' => $event['event_type'],
        'event_date' => $event['event_date']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar - Alumni Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5b1f1f;
            --secondary-color: #ecc35c;
            --bg-light: #f8f9fa;
            --text-color: #333;
            --light-gray: #f0f0f0;
            --border-color: #e0e0e0;
        }

        .events-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .page-subtitle {
            color: var(--primary-color);
            text-align: center;
            margin: 20px 0 10px;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .calendar-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .calendar-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .current-month {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0 20px;
            min-width: 200px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .calendar-nav-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        .calendar-nav-btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: scale(1.1);
        }

        .calendar {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }

        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 15px 0;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: var(--light-gray);
        }

        .calendar-day {
            min-height: 120px;
            padding: 10px;
            background: white;
            position: relative;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background: #f9f9f9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .day-number {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .event-indicator {
            width: 8px;
            height: 8px;
            background-color: var(--primary-color);
            border-radius: 50%;
            margin: 4px auto 0;
            opacity: 0.9;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
            position: relative;
        }

        .event-indicator:hover {
            transform: scale(1.3);
            opacity: 1;
        }

        .event-type-social {
            background-color: #3b82f6; /* Blue */
        }

        .event-type-workshop {
            background-color: #10b981; /* Green */
        }

        .event-type-conference {
            background-color: #8b5cf6; /* Purple */
        }

        .event-type-other {
            background-color: #6b7280; /* Gray */
        }

        .other-month {
            color: #ccc;
            background: #fafafa;
        }

        .today {
            background-color: rgba(236, 195, 92, 0.15);
            border: 1px solid var(--secondary-color);
        }

        .today .day-number {
            color: var(--primary-color);
            font-weight: 700;
        }

        .event-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin: 2px auto 0;
            position: relative;
        }
        
        .event-type-academic { background-color: #5b1f1f; }
        .event-type-cultural { background-color: #e74c3c; }
        .event-type-sports { background-color: #2ecc71; }
        .event-type-workshop { background-color: #3498db; }
        .event-type-conference { background-color: #9b59b6; }
        .event-type-other { background-color: #f39c12; }
        
        .event-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--secondary-color);
            color: var(--primary-color);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* Event Details Modal */
        .event-details {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .event-details.active {
            opacity: 1;
            visibility: visible;
        }

        .event-details-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            transform: translateY(20px);
            transition: transform 0.3s ease;
        }

        .event-details.active .event-details-content {
            transform: translateY(0);
        }

        .event-item {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9fafb;
            border-left: 4px solid var(--primary-color);
        }

        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .event-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.3rem;
        }

        .event-type-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .event-description {
            margin-top: 10px;
            color: #4b5563;
            line-height: 1.6;
        }

        .event-divider {
            border: none;
            height: 1px;
            background-color: #e5e7eb;
            margin: 15px 0;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.2s ease;
        }

        .close-btn:hover {
            color: var(--primary-color);
        }
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(3px);
        }

        .event-details-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            position: relative;
            animation: modalFadeIn 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            max-height: 70vh;
            overflow-y: auto;
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .event-details h3 {
            color: var(--primary-color);
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--secondary-color);
        }

        .event-details p {
            margin: 10px 0;
            line-height: 1.6;
            color: var(--text-color);
        }

        .event-details p:before {
            margin-right: 10px;
            font-weight: 500;
        }

        .close-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .close-btn:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .event-item {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .event-type-badge {
            background-color: #f0f0f0;
            color: #555;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: capitalize;
        }
        
        .event-description {
            margin-top: 10px;
            color: #555;
            line-height: 1.5;
        }
        
        .event-item p {
            margin: 5px 0;
            display: flex;
            align-items: center;
        }
        
        .event-item p i {
            margin-right: 8px;
            color: var(--primary-color);
            width: 16px;
            text-align: center;
        }
        
        .registration-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .registration-link:hover {
            text-decoration: underline;
            color: #4a1a1a;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .calendar-day {
                min-height: 80px;
                padding: 5px;
                font-size: 0.9rem;
            }

            .day-number {
                font-size: 0.9rem;
            }

            .event-dot {
                width: 6px;
                height: 6px;
            }

            .current-month {
                font-size: 1.2rem;
                min-width: 160px;
            }

            .calendar-nav-btn {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../sidebar.php'; ?>

    <div class="main-content" id="mainContent">
        <div class="events-container">
            <h2 class="page-subtitle">Events Calendar</h2>

            <div class="calendar-header">
                <div class="calendar-nav">
                    <button id="prevMonth" class="calendar-nav-btn">❮</button>
                    <h3 id="currentMonth" class="current-month">December 2025</h3>
                    <button id="nextMonth" class="calendar-nav-btn">❯</button>
                </div>
            </div>

            <div class="calendar">
                <div class="calendar-weekdays">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div class="calendar-days" id="calendarDays"></div>
            </div>

            <div class="event-details" id="eventDetails">
                <div class="event-details-content">
                    <!-- Dynamic content will be inserted here -->
                </div>
                <button class="close-btn" id="closeEvent">Close</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make sure the events data is available globally
        window.events = <?php echo json_encode($events); ?>;
        const calendarDays = document.getElementById('calendarDays');
        const currentMonthElement = document.getElementById('currentMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');
        const eventDetails = document.getElementById('eventModal');
        const closeEventBtn = document.getElementById('closeEvent');

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        // Initialize calendar
        function initCalendar() {
            renderCalendar(currentMonth, currentYear);
            addDayClickHandlers();
            
            // Close modal when clicking outside
            document.addEventListener('click', (e) => {
                const modal = document.getElementById('eventModal');
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
            
            // Close button
            document.getElementById('closeEvent').addEventListener('click', () => {
                document.getElementById('eventModal').classList.remove('active');
            });
        }

        // Render calendar
        function renderCalendar(month, year) {
            // Set current month and year in header
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            currentMonthElement.textContent = `${monthNames[month]} ${year}`;

            // Get first day of month and total days
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            // Clear previous calendar
            calendarDays.innerHTML = '';

            // Previous month's days
            for (let i = firstDay; i > 0; i--) {
                const dayElement = createDayElement(daysInPrevMonth - i + 1, true);
                calendarDays.appendChild(dayElement);
            }

            // Current month's days
            const today = new Date();
            for (let i = 1; i <= daysInMonth; i++) {
                const dayElement = createDayElement(i, false);
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;

                // Highlight today
                if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    dayElement.classList.add('today');
                }

                // Add event indicators to days with events
                if (window.events[dateStr] && window.events[dateStr].length > 0) {
                    const indicator = document.createElement('div');
                    indicator.className = 'event-indicator';
                    
                    // Add a class based on event type for color coding
                    const eventType = window.events[dateStr][0].event_type || 'other';
                    indicator.classList.add(`event-type-${eventType}`);
                    
                    // Add title for hover
                    const eventTitles = window.events[dateStr].map(e => e.title).join(', ');
                    indicator.setAttribute('title', eventTitles);
                    
                    // Show number of events if more than one
                    if (window.events[dateStr].length > 1) {
                        const countBadge = document.createElement('span');
                        countBadge.className = 'event-count';
                        countBadge.textContent = window.events[dateStr].length;
                        indicator.appendChild(countBadge);
                    }
                    
                    // Add click handler to the indicator
                    indicator.addEventListener('click', (e) => {
                        e.stopPropagation();
                        showEventDetails(window.events[dateStr]);
                        document.getElementById('eventModal').classList.add('active');
                    });
                    
                    dayElement.appendChild(indicator);
                    
                    // Make the entire day clickable
                    dayElement.style.cursor = 'pointer';
                    dayElement.addEventListener('click', () => {
                        showEventDetails(window.events[dateStr]);
                        document.getElementById('eventModal').classList.add('active');
                    });
                }

                calendarDays.appendChild(dayElement);
            }

            // Next month's days
            const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
            const remainingDays = totalCells - (firstDay + daysInMonth);

            for (let i = 1; i <= remainingDays; i++) {
                const dayElement = createDayElement(i, true);
                calendarDays.appendChild(dayElement);
            }
        }

        // Create day element
        function createDayElement(day, isOtherMonth) {
            const dayElement = document.createElement('div');
            dayElement.className = `calendar-day ${isOtherMonth ? 'other-month' : ''}`;

            const dayNumber = document.createElement('div');
            dayNumber.className = 'day-number';
            dayNumber.textContent = day;

            dayElement.appendChild(dayNumber);
            return dayElement;
        }

        // Add click event to calendar days
        function addDayClickHandlers() {
            const dayElements = document.querySelectorAll('.calendar-day:not(.other-month)');
            dayElements.forEach(day => {
                day.addEventListener('click', function() {
                    const date = this.querySelector('.day-number').textContent;
                    const month = currentMonth + 1;
                    const year = currentYear;
                    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                    if (window.events[dateStr] && window.events[dateStr].length > 0) {
                        showEventDetails(window.events[dateStr]);
                        // Show the modal
                        eventDetails.style.display = 'block';
                    }
                });
            });
        }

        // Show event details in modal
        function showEventDetails(eventList) {
            const eventDetailsContent = document.querySelector('.event-details-content');
            if (!eventDetailsContent) return;
            
            eventDetailsContent.innerHTML = '';

            if (Array.isArray(eventList) && eventList.length > 0) {
                // Add close button
                const closeBtn = document.createElement('button');
                closeBtn.className = 'close-btn';
                closeBtn.innerHTML = '&times;';
                closeBtn.onclick = () => {
                    document.getElementById('eventModal').classList.remove('active');
                };
                eventDetailsContent.appendChild(closeBtn);

                // Add title
                const title = document.createElement('h2');
                title.textContent = eventList.length > 1 ? 'Events on this day' : 'Event Details';
                title.style.marginTop = '0';
                title.style.color = 'var(--primary-color)';
                eventDetailsContent.appendChild(title);

                // Add events
                eventList.forEach((event, index) => {
                    const eventElement = document.createElement('div');
                    eventElement.className = `event-item event-type-${event.event_type || 'other'}`;
                    
                    // Format date
                    const eventDate = new Date(event.event_date);
                    const formattedDate = eventDate.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    eventElement.innerHTML = `
                        <div class="event-header">
                            <h3>${event.title}</h3>
                            ${event.event_type ? `<span class="event-type-badge">${event.event_type.charAt(0).toUpperCase() + event.event_type.slice(1)}</span>` : ''}
                        </div>
                        <p><i class="far fa-calendar-alt"></i> ${formattedDate}</p>
                        ${event.location ? `<p><i class="fas fa-map-marker-alt"></i> ${event.location}</p>` : ''}
                        ${event.description ? `<div class="event-description">${event.description}</div>` : ''}
                    `;
                    
                    eventDetailsContent.appendChild(eventElement);
                    
                    // Add divider between events if there are multiple
                    if (index < eventList.length - 1) {
                        const divider = document.createElement('hr');
                        divider.className = 'event-divider';
                        eventDetailsContent.appendChild(divider);
                    }
                });
            }        
        }

        // Event Listeners
        prevMonthBtn.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar(currentMonth, currentYear);
        });

        nextMonthBtn.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        });

        closeEventBtn.addEventListener('click', () => {
            eventDetails.style.display = 'none';
        });

        // Close modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('eventModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        // Initialize calendar
        initCalendar();
    });
    </script>
</body>
</html>
