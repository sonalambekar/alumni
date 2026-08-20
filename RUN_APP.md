# 🚀 Quick Start - Run Your Flutter App

## Step-by-Step Instructions

### 1. Update API Configuration (1 minute)

Open this file in any text editor:
```
gmu_alumni_app\lib\config\app_config.dart
```

Find line 6 and change it based on your setup:

**For Android Emulator:**
```dart
static const String baseUrl = 'http://10.0.2.2/alumni';
```

**For Physical Android Device:**
```dart
static const String baseUrl = 'http://192.168.1.XXX/alumni';  // Replace XXX with your computer's IP
```

**For iOS Simulator:**
```dart
static const String baseUrl = 'http://localhost/alumni';
```

**How to find your computer's IP:**
- Windows: Open CMD and type `ipconfig`
- Look for "IPv4 Address" under your active network adapter
- Example: 192.168.1.100

### 2. Start XAMPP (30 seconds)

- Open XAMPP Control Panel
- Start **Apache**
- Start **MySQL**
- Both should show green "Running" status

### 3. Test API (30 seconds)

Open your browser and visit:
```
http://localhost/alumni/api/core-team/list.php
```

You should see JSON data like:
```json
{
  "success": true,
  "data": [...]
}
```

If you see this, your API is working! ✅

### 4. Run Flutter App (2 minutes)

Open Command Prompt or Terminal and run:

```bash
cd C:\xampp_ss\htdocs\alumni\gmu_alumni_app
flutter pub get
flutter run
```

**Select your device:**
- Press `1` for Android emulator
- Press `2` for connected physical device
- Press `3` for Chrome (web)

### 5. Test Login

**Option A: Use existing account**
- Email: Any email from your database
- Password: Corresponding password

**Option B: Register new account**
- Click "Register" (if available)
- Fill in details
- Login with new credentials

---

## 🎯 Quick Commands

### Run on Android Emulator
```bash
cd gmu_alumni_app
flutter run -d emulator
```

### Run on Physical Device
```bash
cd gmu_alumni_app
flutter run -d <device-id>
```

### Build APK
```bash
cd gmu_alumni_app
flutter build apk --release
```

APK will be at: `build\app\outputs\flutter-apk\app-release.apk`

### Check for Issues
```bash
cd gmu_alumni_app
flutter doctor
```

---

## ❓ Troubleshooting

### Problem: "Cannot connect to API"

**Solution 1:** Check XAMPP is running
- Open XAMPP Control Panel
- Apache and MySQL should be green

**Solution 2:** Verify API URL
- Open `lib\config\app_config.dart`
- For emulator use: `http://10.0.2.2/alumni`
- For device use your computer's IP

**Solution 3:** Test API in browser
- Visit: `http://localhost/alumni/api/core-team/list.php`
- Should show JSON data

### Problem: "Dependencies error"

**Solution:**
```bash
cd gmu_alumni_app
flutter clean
flutter pub get
```

### Problem: "No devices found"

**Solution for Emulator:**
1. Open Android Studio
2. Tools → Device Manager
3. Create/Start an emulator

**Solution for Physical Device:**
1. Enable Developer Options on phone
2. Enable USB Debugging
3. Connect via USB
4. Allow USB debugging prompt

### Problem: "Build failed"

**Solution:**
```bash
flutter doctor
```
Fix any issues shown (usually Android SDK or licenses)

---

## 📱 App Features to Test

Once app is running, test these features:

1. **Login Screen**
   - Enter email and password
   - Click Login button

2. **Home Screen**
   - Should show welcome message
   - Grid of feature cards

3. **Navigation Drawer**
   - Swipe from left or tap menu icon
   - Try different menu items

4. **Core Team**
   - Should show team members
   - Circular avatars with names

5. **Other Features**
   - Noticeboard
   - News
   - Events
   - Jobs
   - Galleries
   - Profile

---

## 🎨 What You Should See

### Splash Screen (2 seconds)
- GMU Alumni logo
- Loading indicator

### Login Screen
- Email input field
- Password input field
- Login button
- Maroon and gold colors

### Home Screen
- Welcome message with user name
- 8 feature cards in grid
- Navigation drawer icon
- Notification icon

### Other Screens
- App bar with title
- Back button
- Content specific to each feature

---

## 📊 Expected Behavior

### First Time Running
1. Splash screen appears (2 sec)
2. Redirects to Login screen
3. Enter credentials
4. Redirects to Home screen
5. Can navigate to all features

### Subsequent Runs
1. Splash screen appears (2 sec)
2. Auto-login if previously logged in
3. Directly to Home screen

---

## 🔥 Hot Reload

While app is running, you can make changes:

1. Edit any Dart file
2. Save the file
3. Press `r` in terminal for hot reload
4. Press `R` for hot restart

Changes appear instantly without rebuilding!

---

## 📦 Build for Distribution

### Debug APK (for testing)
```bash
flutter build apk --debug
```

### Release APK (for distribution)
```bash
flutter build apk --release
```

### Split APKs (smaller size)
```bash
flutter build apk --split-per-abi
```

---

## ✅ Success Checklist

- [ ] XAMPP is running (Apache + MySQL)
- [ ] API returns JSON when tested in browser
- [ ] Updated `baseUrl` in `app_config.dart`
- [ ] Ran `flutter pub get` successfully
- [ ] App launches without errors
- [ ] Can login successfully
- [ ] Home screen displays
- [ ] Can navigate to different screens
- [ ] Data loads from API

---

## 🎉 You're All Set!

Your Flutter app is now running with:
- ✅ Beautiful UI matching website theme
- ✅ All major features implemented
- ✅ Backend API integration
- ✅ Smooth navigation
- ✅ User authentication

**Enjoy your GMU Alumni Connect mobile app! 📱**

---

## 📞 Need Help?

1. Check `FLUTTER_APP_SETUP_GUIDE.md` for detailed setup
2. Check `PROJECT_SUMMARY.md` for complete overview
3. Check `gmu_alumni_app/README.md` for technical details

---

**Last Updated:** November 2024
**Version:** 1.0.0
