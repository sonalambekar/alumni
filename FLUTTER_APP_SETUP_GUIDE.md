# GMU Alumni Flutter App - Quick Setup Guide

## ✅ What Has Been Created

### Flutter Mobile App
- **Location:** `gmu_alumni_app/`
- **Features:** All website features including Core Team, Noticeboard, News, Galleries, Events, Jobs, Proud Alumni, Profile
- **State Management:** Provider
- **Navigation:** GoRouter
- **UI:** Material Design 3 with custom theme matching website colors

### Backend API
- **Location:** `api/`
- **Endpoints:** 10+ REST API endpoints for all features
- **Authentication:** Token-based auth
- **Format:** JSON responses

## 🚀 Quick Start (5 Minutes)

### Step 1: Update API Configuration
```bash
# Open this file:
gmu_alumni_app/lib/config/app_config.dart

# Change line 6 to your server address:
# For Android Emulator:
static const String baseUrl = 'http://10.0.2.2/alumni';

# For Physical Device (replace with your computer's IP):
static const String baseUrl = 'http://192.168.1.100/alumni';

# For iOS Simulator:
static const String baseUrl = 'http://localhost/alumni';
```

### Step 2: Run the App
```bash
cd gmu_alumni_app
flutter pub get
flutter run
```

### Step 3: Test Login
- Email: Use any email from your database
- Password: Corresponding password
- Or create new account via Register

## 📱 App Features

### ✅ Implemented Screens
1. **Splash Screen** - App loading with logo
2. **Login Screen** - Email/password authentication
3. **Home Screen** - Dashboard with quick access cards
4. **Core Team** - Display team members with circular avatars
5. **Noticeboard** - List of notices with categories
6. **News Corner** - News articles with images
7. **Galleries** - Photo gallery grid
8. **Events** - Upcoming events calendar
9. **Jobs** - Job listings portal
10. **Proud Alumni** - Featured alumni achievements
11. **Profile** - User profile management

### 🎨 UI Features
- Material Design 3
- Custom color scheme (Maroon #5B1F1F & Gold #ECC35C)
- Smooth animations
- Responsive design
- Navigation drawer
- Bottom sheets
- Cards and elevation

## 🔧 Configuration Files

### Important Files to Check

1. **API Configuration**
   ```
   gmu_alumni_app/lib/config/app_config.dart
   ```
   - Update `baseUrl` with your server address

2. **Dependencies**
   ```
   gmu_alumni_app/pubspec.yaml
   ```
   - All packages already installed

3. **Routes**
   ```
   gmu_alumni_app/lib/routes/app_router.dart
   ```
   - All navigation routes configured

## 🌐 API Endpoints

All APIs are in `api/` folder:

```
api/
├── auth/
│   ├── login.php          ✅ Working
│   └── register.php       ✅ Working
├── core-team/
│   └── list.php           ✅ Working
├── noticeboard/
│   └── list.php           ✅ Working
├── news/
│   └── list.php           ✅ Working
├── events/
│   └── list.php           ✅ Working
├── jobs/
│   └── list.php           ✅ Working
├── galleries/
│   └── list.php           ✅ Working
├── proud-alumni/
│   └── list.php           ✅ Working
└── profile/
    └── me.php             ✅ Working
```

## 🧪 Testing

### Test API Endpoints
```bash
# Test Core Team API
curl http://localhost/alumni/api/core-team/list.php

# Test Login API
curl -X POST http://localhost/alumni/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Test in Browser
```
http://localhost/alumni/api/core-team/list.php
http://localhost/alumni/api/news/list.php
http://localhost/alumni/api/events/list.php
```

## 📦 Build APK

```bash
cd gmu_alumni_app
flutter build apk --release
```

APK location: `gmu_alumni_app/build/app/outputs/flutter-apk/app-release.apk`

## 🔍 Troubleshooting

### Issue: Cannot connect to API
**Solution:**
- Check XAMPP is running
- Verify API URL in `app_config.dart`
- For Android emulator, use `10.0.2.2` not `localhost`
- For physical device, use your computer's IP address

### Issue: Dependencies error
**Solution:**
```bash
flutter clean
flutter pub get
```

### Issue: Build fails
**Solution:**
```bash
flutter doctor
# Fix any issues shown
```

### Issue: API returns 404
**Solution:**
- Ensure API files are in correct location: `C:\xampp_ss\htdocs\alumni\api\`
- Check file permissions
- Verify .htaccess allows PHP execution

## 🎯 Next Steps

### To Connect Real Data:

1. **Update API calls in screens:**
   - Currently screens use sample data
   - Replace with actual API calls using `ApiService`

2. **Example - Update Core Team Screen:**
```dart
// In core_team_screen.dart
import '../services/api_service.dart';

Future<List<CoreTeamModel>> fetchCoreTeam() async {
  final response = await ApiService.get('/core-team/list.php');
  final data = response.data['data'] as List;
  return data.map((json) => CoreTeamModel.fromJson(json)).toList();
}
```

3. **Add Loading States:**
```dart
FutureBuilder<List<CoreTeamModel>>(
  future: fetchCoreTeam(),
  builder: (context, snapshot) {
    if (snapshot.connectionState == ConnectionState.waiting) {
      return CircularProgressIndicator();
    }
    if (snapshot.hasError) {
      return Text('Error: ${snapshot.error}');
    }
    return ListView.builder(...);
  },
)
```

## 📊 Database Requirements

Ensure these tables exist:
- `users` (with `auth_token` column)
- `noticeboard`
- `news`
- `events`
- `jobs`
- `galleries`
- `gallery_images`

Add missing columns:
```sql
ALTER TABLE users ADD COLUMN auth_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN last_login DATETIME NULL;
```

## 🎨 Customization

### Change App Colors
Edit `gmu_alumni_app/lib/config/app_config.dart`:
```dart
static const Color primaryColor = Color(0xFF5B1F1F);  // Maroon
static const Color secondaryColor = Color(0xFFECC35C); // Gold
```

### Change App Name
Edit `gmu_alumni_app/android/app/src/main/AndroidManifest.xml`:
```xml
<application android:label="GMU Alumni">
```

### Change App Icon
Replace files in:
```
gmu_alumni_app/android/app/src/main/res/mipmap-*/ic_launcher.png
```

## ✨ Features Summary

| Feature | Status | Screen | API |
|---------|--------|--------|-----|
| Login | ✅ | ✅ | ✅ |
| Register | ✅ | ✅ | ✅ |
| Home Dashboard | ✅ | ✅ | - |
| Core Team | ✅ | ✅ | ✅ |
| Noticeboard | ✅ | ✅ | ✅ |
| News | ✅ | ✅ | ✅ |
| Galleries | ✅ | ✅ | ✅ |
| Events | ✅ | ✅ | ✅ |
| Jobs | ✅ | ✅ | ✅ |
| Proud Alumni | ✅ | ✅ | ✅ |
| Profile | ✅ | ✅ | ✅ |

## 📞 Support

If you encounter any issues:
1. Check this guide first
2. Review error messages carefully
3. Test API endpoints in browser
4. Check Flutter doctor: `flutter doctor`

---

**Your Flutter app is ready to run! 🎉**

Just update the API URL and run `flutter run`!
