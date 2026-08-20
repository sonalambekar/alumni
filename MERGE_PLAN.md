# App Merge Plan

## Overview
Merge features from `lib/lib` Flutter app and `gmu_alumini/gmu_alumini/api` APIs into the current `gmu_alumni_app`.

## Current App Features (gmu_alumni_app)
- ✅ Login/Auth
- ✅ Home Screen
- ✅ Profile
- ✅ News
- ✅ Events
- ✅ Jobs
- ✅ Galleries
- ✅ Noticeboard
- ✅ Core Team
- ✅ Proud Alumni

## Features to Add from lib/lib
1. **Social Posts** - Create, view, like, share posts
2. **Messaging** - Chat with other alumni
3. **Announcements** - Director announcements
4. **Alumni Registration** - Register new alumni
5. **User Discovery** - Find and connect with alumni
6. **Director Dashboard** - Approve posts, manage content
7. **Director Stats** - View statistics

## APIs to Integrate from gmu_alumini
1. **posts.php** - Social posts CRUD, likes, shares
2. **messages.php** - Messaging system
3. **announcements.php** - Announcements CRUD
4. **users.php** - User discovery and profiles
5. **auth.php** - Enhanced authentication
6. **alumni_registration.php** - Alumni registration
7. **upload_media.php** - Media upload for posts

## Database Tables Needed
- posts
- likes
- shares
- messages
- announcements
- (users table already exists)

## Merge Steps

### Phase 1: Copy APIs
1. Copy API files from gmu_alumini to api folder
2. Update database config paths
3. Create database tables

### Phase 2: Add Flutter Screens
1. Copy screens from lib/lib to gmu_alumni_app
2. Update imports and dependencies
3. Add models for new features

### Phase 3: Update Navigation
1. Add new routes to app_router.dart
2. Update drawer with new menu items

### Phase 4: Testing
1. Test each new feature
2. Fix API endpoints
3. Verify data flow

## Next Steps
Run this merge process?
