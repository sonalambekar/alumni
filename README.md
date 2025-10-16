# Alumni Connect - University Alumni Website

A modern, responsive alumni website built with PHP, HTML, CSS, and JavaScript featuring a sidebar navigation layout.

## 🎨 Design Features

- **Primary Color**: #5b1f1f (Deep Maroon)
- **Secondary Color**: #ecc35c (Golden Yellow)
- **Clean, elegant, and modern design**
- **Fixed sidebar navigation**
- **Fully responsive layout**
- **Smooth animations and transitions**

## 📁 Project Structure

```
alumni/
├── index.php                 # Homepage
├── sidebar.php              # Reusable sidebar component
├── README.md                # This file
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   ├── js/
│   │   └── script.js       # JavaScript functionality
│   └── images/             # Image assets (add your images here)
└── pages/
    ├── directory.php       # Alumni directory
    ├── events.php          # Events listing
    ├── jobs.php            # Job board
    ├── noticeboard.php     # Announcements
    ├── news.php            # News corner
    ├── galleries.php       # Photo galleries
    ├── members-nearby.php  # Location-based alumni
    ├── yearbook.php        # Digital yearbook
    ├── fundraising.php     # Fundraising campaigns
    ├── mentorship.php      # Mentorship program
    ├── special-groups.php  # Special interest groups
    ├── business-connect.php # Business networking
    └── member-support.php  # Support center
```

## 🚀 Setup Instructions

### Prerequisites
- XAMPP (or any PHP server)
- Modern web browser

### Installation

1. **Copy the project to XAMPP**
   - The project is already in: `c:\xampp_ss\htdocs\alumni`

2. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start Apache server

3. **Access the website**
   - Open your browser
   - Navigate to: `http://localhost/alumni/`

## 🗺️ Mapbox Integration

The homepage includes an interactive world map showing alumni locations using Mapbox.

### To enable the map:

1. Get a free Mapbox access token:
   - Visit: https://www.mapbox.com/
   - Sign up for a free account
   - Copy your access token

2. Update the token in `assets/js/script.js`:
   ```javascript
   mapboxgl.accessToken = 'YOUR_MAPBOX_TOKEN_HERE';
   ```

**Note**: Without a Mapbox token, the map section will display a fallback message.

## 📱 Features

### Homepage Sections
1. **Hero Section** - About Alumni Network with image
2. **Vision & Stats** - Animated counters showing member statistics
3. **Alumni Highlights** - Featured alumni profiles
4. **Alumni Groups** - Special interest groups
5. **Regional Chapters** - City-based chapters
6. **World Map** - Interactive Mapbox integration
7. **Footer** - Contact info and social links

### Navigation Pages
- **Home** - Main landing page
- **Noticeboard** - Important announcements
- **News Corner** - Latest alumni news
- **Galleries** - Photo albums from events
- **Alumni** (Dropdown)
  - Directory - Searchable alumni profiles
  - Members Nearby - Location-based discovery
  - Yearbook - Digital yearbooks
- **Events** - Upcoming alumni events
- **Jobs** - Job board with opportunities
- **PRO** (Dropdown)
  - Fund Raising - Donation campaigns
  - Mentorship - Mentor/mentee matching
  - Special Interest Groups
- **Enterprise** (Dropdown)
  - Business Connect - B2B networking
  - Member Support - Help center

## 🎯 Key Features

### Sidebar Navigation
- Fixed left sidebar with collapsible menu
- Dropdown menus for organized navigation
- Mobile-responsive with toggle button
- Smooth animations and hover effects

### Responsive Design
- Desktop: Full sidebar visible
- Tablet: Sidebar remains functional
- Mobile: Collapsible sidebar with overlay

### Interactive Elements
- Animated stat counters
- Hover effects on cards
- Smooth scroll animations
- Dropdown menus
- Interactive map markers

## 🎨 Customization

### Colors
Edit CSS variables in `assets/css/style.css`:
```css
:root {
    --primary-color: #5b1f1f;
    --secondary-color: #ecc35c;
}
```

### Images
Replace placeholder images with your own:
- Add images to `assets/images/`
- Update image paths in PHP files
- Current images use Unsplash placeholders

### Content
- Edit text content directly in PHP files
- Update alumni data in respective pages
- Modify footer information in `index.php`

## 🔧 Technical Details

### Technologies Used
- **PHP** - Server-side scripting
- **HTML5** - Structure
- **CSS3** - Styling with Flexbox/Grid
- **JavaScript** - Interactivity
- **Mapbox GL JS** - Interactive maps
- **Google Fonts** - Poppins font family

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## 📝 Adding New Pages

1. Create new PHP file in `pages/` directory
2. Include sidebar: `<?php include '../sidebar.php'; ?>`
3. Add main content wrapper: `<div class="main-content" id="mainContent">`
4. Include JavaScript: `<script src="../assets/js/script.js"></script>`
5. Update sidebar.php to add navigation link

## 🎓 Usage Tips

1. **Update Alumni Data**: Modify the profile cards in `pages/directory.php`
2. **Add Events**: Update event listings in `pages/events.php`
3. **Post News**: Add news articles in `pages/news.php`
4. **Upload Photos**: Add gallery albums in `pages/galleries.php`

## 🔐 Security Notes

- This is a frontend template
- Add proper authentication for production
- Sanitize all user inputs
- Implement CSRF protection
- Use prepared statements for database queries

## 📞 Support

For issues or questions:
- Check the code comments
- Review browser console for errors
- Ensure XAMPP Apache is running
- Verify file paths are correct

## 🌟 Future Enhancements

Consider adding:
- User authentication system
- Database integration (MySQL)
- Admin panel for content management
- Email notifications
- Advanced search filters
- Payment gateway for donations
- Real-time chat functionality

## 📄 License

This project is created for educational purposes. Customize as needed for your institution.

---

**Built with ❤️ for Alumni Communities**
