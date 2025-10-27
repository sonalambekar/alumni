<?php
// Sample events data (in a real app, this would come from a database)
$events = [
    '2025-12-15' => [
        ['title' => 'Annual Alumni Reunion 2025', 'time' => '18:00 - 22:00', 'location' => 'University Campus, Main Auditorium', 'description' => 'Join us for our biggest alumni gathering of the year! Reconnect with classmates, network with professionals, and celebrate our shared legacy.'],
    ],
    '2025-12-22' => [
        ['title' => 'Tech Leaders Networking Night', 'time' => '19:00 - 21:30', 'location' => 'Tech Hub, Bengaluru', 'description' => 'An exclusive evening for alumni working in technology. Share insights, explore collaborations, and expand your professional network.'],
    ],
    '2026-01-05' => [
        ['title' => 'Alumni Career Fair 2025', 'time' => '10:00 - 17:00', 'location' => 'Convention Center, Delhi', 'description' => 'Connect with top employers and explore exciting career opportunities. Featuring 50+ companies and exclusive alumni networking sessions.'],
    ],
    '2026-01-12' => [
        ['title' => 'Mentorship Program Launch', 'time' => '17:00 - 19:00', 'location' => 'Virtual Event (Zoom)', 'description' => 'Launch of our new mentorship program connecting experienced alumni with recent graduates. Learn how you can make a difference.'],
    ],
    '2026-01-20' => [
        ['title' => 'Alumni Sports Day', 'time' => '08:00 - 18:00', 'location' => 'University Sports Complex', 'description' => 'Relive your college days with cricket, football, badminton, and more! Bring your family for a fun-filled day of sports and camaraderie.'],
    ],
    '2026-02-01' => [
        ['title' => 'Annual Gala Dinner', 'time' => '19:00 - 23:00', 'location' => 'Grand Hotel, Mumbai', 'description' => 'An elegant evening celebrating alumni achievements. Featuring awards ceremony, live entertainment, and gourmet dining.'],
    ],
    '2026-01-26' => [
        ['title' => 'Republic Day Celebration', 'time' => '08:00 - 12:00', 'location' => 'University Grounds', 'description' => 'Join the university community in celebrating India\'s Republic Day with flag hoisting, cultural performances, and patriotic speeches.'],
    ],
    '2026-03-15' => [
        ['title' => 'Holi Cultural Fest', 'time' => '10:00 - 16:00', 'location' => 'College Campus', 'description' => 'Celebrate the festival of colors with traditional Holi games, music, dance, and authentic Indian cuisine. Open to all students and alumni.'],
    ],
    '2026-04-14' => [
        ['title' => 'Ambedkar Jayanti', 'time' => '09:00 - 11:00', 'location' => 'Auditorium', 'description' => 'Commemorate the birth anniversary of Dr. B.R. Ambedkar with lectures, discussions, and cultural programs highlighting social justice.'],
    ],
    '2026-08-15' => [
        ['title' => 'Independence Day Festivities', 'time' => '07:00 - 13:00', 'location' => 'University Stadium', 'description' => 'National celebration with flag ceremony, parade, cultural dances, and speeches honoring India\'s independence.'],
    ],
    '2026-10-02' => [
        ['title' => 'Gandhi Jayanti', 'time' => '08:00 - 10:00', 'location' => 'Campus Garden', 'description' => 'Observe Mahatma Gandhi\'s birthday with prayer meetings, cleanliness drives, and discussions on non-violence and peace.'],
    ],
    '2026-11-14' => [
        ['title' => 'Children\'s Day Celebration', 'time' => '14:00 - 18:00', 'location' => 'Community Hall', 'description' => 'Fun activities, games, and cultural programs for children in the university community, celebrating childhood and education.'],
    ],
    '2026-12-25' => [
        ['title' => 'Christmas Cultural Evening', 'time' => '18:00 - 22:00', 'location' => 'University Chapel', 'description' => 'Festive evening with carol singing, nativity plays, and holiday treats to celebrate Christmas with the alumni family.'],
    ]
];
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

        .event-dot {
            width: 8px;
            height: 8px;
            background-color: var(--secondary-color);
            border-radius: 50%;
            margin: 3px auto;
            display: block;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .event-dot:hover {
            transform: scale(1.5);
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

        /* Event Details Modal */
        .event-details {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
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
        const calendarDays = document.getElementById('calendarDays');
        const currentMonthElement = document.getElementById('currentMonth');
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');
        const eventDetails = document.getElementById('eventDetails');
        const closeEventBtn = document.getElementById('closeEvent');

        let currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();

        // Sample events data (in a real app, this would come from an API)
        const events = <?php echo json_encode($events); ?>;

        // Initialize calendar
        function initCalendar() {
            renderCalendar(currentMonth, currentYear);
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

                // Add event indicator
                if (events[dateStr]) {
                    const eventDot = document.createElement('span');
                    eventDot.className = 'event-dot';
                    eventDot.title = `${events[dateStr][0].title} - ${events[dateStr][0].time}`;
                    eventDot.onclick = (e) => {
                        e.stopPropagation();
                        showEventDetails(events[dateStr]);
                    };
                    dayElement.appendChild(eventDot);

                    // Make the whole day clickable
                    dayElement.style.cursor = 'pointer';
                    dayElement.onclick = () => showEventDetails(events[dateStr]);
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

        // Show event details
        function showEventDetails(eventList) {
            const eventDetailsContent = document.querySelector('.event-details-content');
            eventDetailsContent.innerHTML = '';

            if (Array.isArray(eventList) && eventList.length > 1) {
                // Multiple events on the same day
                eventList.forEach((event, index) => {
                    const eventDiv = document.createElement('div');
                    eventDiv.style.borderBottom = index < eventList.length - 1 ? '1px solid #eee' : 'none';
                    eventDiv.style.paddingBottom = '15px';
                    eventDiv.style.marginBottom = index < eventList.length - 1 ? '15px' : '0';
                    eventDiv.innerHTML = `
                        <h3>${event.title}</h3>
                        <p><strong>Time:</strong> ${event.time}</p>
                        <p><strong>Location:</strong> ${event.location}</p>
                        <p><strong>Description:</strong> ${event.description}</p>
                    `;
                    eventDetailsContent.appendChild(eventDiv);
                });
            } else {
                // Single event
                const event = eventList[0] || eventList;
                eventDetailsContent.innerHTML = `
                    <h3>${event.title}</h3>
                    <p><strong>Time:</strong> ${event.time}</p>
                    <p><strong>Location:</strong> ${event.location}</p>
                    <p><strong>Description:</strong> ${event.description}</p>
                `;
            }

            eventDetails.style.display = 'flex';
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

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target === eventDetails) {
                eventDetails.style.display = 'none';
            }
        });

        // Initialize calendar
        initCalendar();
    });
    </script>
</body>
</html>
