# GMU Alumni Mobile App - Features Checklist

## Website Features to Implement in Mobile App

### ✅ Core Features (Check if implemented in mobile app)

#### 1. **Home Dashboard**
- [ ] Welcome screen with user info
- [ ] Quick access to main features
- [ ] Recent updates/notifications

#### 2. **Core Team** (NEW - Just added to website)
- [ ] Display core team members
- [ ] Circular profile images
- [ ] Member names and positions
- [ ] Contact information

#### 3. **Noticeboard**
- [ ] View all notices
- [ ] Filter by category
- [ ] Search notices
- [ ] View notice details
- [ ] Attachments download

#### 4. **News Corner**
- [ ] Browse news articles
- [ ] View news details
- [ ] Images in news
- [ ] Share news

#### 5. **Photo Galleries**
- [ ] View gallery collections
- [ ] Browse photos in gallery
- [ ] Full-screen image view
- [ ] Image zoom/pinch
- [ ] Share photos

#### 6. **Alumni Directory**
- [ ] Search alumni by name
- [ ] Filter by batch/year
- [ ] Filter by department
- [ ] View alumni profile
- [ ] Contact alumni

#### 7. **Announcements**
- [ ] View announcements
- [ ] Filter by type
- [ ] Notification for new announcements

#### 8. **Members Nearby**
- [ ] Map view of nearby alumni
- [ ] Location-based search
- [ ] Distance calculation
- [ ] Contact nearby members

#### 9. **Quarterly Newsletter (Yearbook)**
- [ ] View newsletters
- [ ] Download PDF
- [ ] Archive access

#### 10. **Institute Scholarship**
- [ ] View scholarship information
- [ ] Application details
- [ ] Eligibility criteria

#### 11. **Events**
- [ ] Browse upcoming events
- [ ] View event details
- [ ] RSVP/Register for events
- [ ] Add to calendar
- [ ] Past events archive

#### 12. **Jobs Portal**
- [ ] Browse job listings
- [ ] Filter by location/type
- [ ] Search jobs
- [ ] Apply for jobs
- [ ] Post job (for employers)
- [ ] Save favorite jobs

#### 13. **Proud Alumni**
- [ ] View featured alumni
- [ ] Alumni achievements
- [ ] Success stories
- [ ] Images and descriptions

#### 14. **Mentorship Program (PRO)**
- [ ] Find mentors
- [ ] Become a mentor
- [ ] Mentorship requests
- [ ] Chat with mentor/mentee

#### 15. **User Profile**
- [ ] View own profile
- [ ] Edit profile information
- [ ] Upload profile picture
- [ ] Update contact details
- [ ] Change password

#### 16. **Authentication**
- [ ] Login
- [ ] Register
- [ ] Forgot password
- [ ] Logout
- [ ] Session management

---

## Additional Features Found in Website (May not be in app)

### 17. **Business Connect**
- [ ] Business networking
- [ ] Business listings

### 18. **Couch Surfing**
- [ ] Accommodation sharing
- [ ] Host/Guest system

### 19. **Fundraising**
- [ ] View fundraising campaigns
- [ ] Donate
- [ ] Campaign details

### 20. **Internships**
- [ ] Browse internships
- [ ] Apply for internships
- [ ] Post internships

### 21. **Member Support**
- [ ] Support requests
- [ ] Help center

### 22. **Special Groups**
- [ ] Interest-based groups
- [ ] Join groups
- [ ] Group discussions

### 23. **Feedback**
- [ ] Submit feedback
- [ ] Rate features
- [ ] Suggestions

### 24. **Institute Medal**
- [ ] Medal recipients
- [ ] Award information

---

## API Endpoints Needed for Mobile App

### Base URL
```
http://your-domain.com/alumni/api/
```

### Authentication
- `POST /api/auth/login.php` - User login
- `POST /api/auth/register.php` - User registration
- `POST /api/auth/logout.php` - User logout
- `POST /api/auth/forgot-password.php` - Password reset

### Core Team (NEW)
- `GET /api/core-team/list.php` - Get all core team members

### Noticeboard
- `GET /api/noticeboard/list.php` - Get all notices
- `GET /api/noticeboard/details.php?id={id}` - Get notice details

### News
- `GET /api/news/list.php` - Get all news
- `GET /api/news/details.php?id={id}` - Get news details

### Galleries
- `GET /api/galleries/list.php` - Get all galleries
- `GET /api/galleries/images.php?gallery_id={id}` - Get gallery images

### Alumni
- `GET /api/alumni/directory.php` - Get alumni list
- `GET /api/alumni/profile.php?id={id}` - Get alumni profile
- `GET /api/alumni/nearby.php?lat={lat}&lng={lng}` - Get nearby alumni

### Events
- `GET /api/events/list.php` - Get all events
- `GET /api/events/details.php?id={id}` - Get event details
- `POST /api/events/rsvp.php` - RSVP to event

### Jobs
- `GET /api/jobs/list.php` - Get all jobs
- `GET /api/jobs/details.php?id={id}` - Get job details
- `POST /api/jobs/apply.php` - Apply for job

### Proud Alumni
- `GET /api/proud-alumni/list.php` - Get proud alumni list

### Profile
- `GET /api/profile/me.php` - Get current user profile
- `PUT /api/profile/update.php` - Update profile
- `POST /api/profile/upload-picture.php` - Upload profile picture

### Announcements
- `GET /api/announcements/list.php` - Get announcements

### Mentorship
- `GET /api/mentorship/mentors.php` - Get mentor list
- `POST /api/mentorship/request.php` - Request mentorship

---

## Database Tables to Check

Ensure your mobile app can access these tables:
- `users` - User accounts
- `noticeboard` - Notices
- `news` - News articles
- `galleries` - Photo galleries
- `gallery_images` - Gallery images
- `events` - Events
- `jobs` - Job listings
- `announcements` - Announcements
- `mentorship` - Mentorship data
- `core_team` - Core team members (may need to create)

---

## Implementation Priority

### High Priority (Essential Features)
1. Core Team (NEW)
2. Noticeboard
3. News Corner
4. Events
5. Jobs Portal
6. Alumni Directory
7. Profile Management

### Medium Priority
1. Photo Galleries
2. Proud Alumni
3. Announcements
4. Members Nearby
5. Mentorship

### Low Priority (Nice to Have)
1. Quarterly Newsletter
2. Institute Scholarship
3. Special Groups
4. Feedback
5. Business Connect
6. Couch Surfing
7. Fundraising
8. Internships

---

## Next Steps

1. Review your mobile app and check which features are already implemented
2. Create missing API endpoints for features not yet in the app
3. Implement UI screens for missing features
4. Test API connectivity
5. Add push notifications for important updates
6. Implement offline caching for better performance

---

## Notes
- All API endpoints should return JSON format
- Implement proper authentication tokens (JWT recommended)
- Add pagination for list endpoints
- Include error handling in all API responses
- Add image compression for mobile uploads
- Implement pull-to-refresh on list screens
