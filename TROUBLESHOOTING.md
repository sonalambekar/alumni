# 🔧 Troubleshooting Guide - GMU Alumni App

## ✅ Build Error - FIXED!

### Error: "Dependency requires core library desugaring"
**Status:** ✅ FIXED

**Solution Applied:**
Updated `android/app/build.gradle.kts` with desugaring support.

**To apply the fix:**
```bash
cd gmu_alumni_app
flutter clean
flutter pub get
flutter run
```

---

## Common Issues & Solutions

### 1. Cannot Connect to API

**Symptoms:**
- App shows "No data" or loading forever
- Login fails
- Screens are empty

**Solutions:**

**A. Check XAMPP is Running**
```
1. Open XAMPP Control Panel
2. Start Apache (should be green)
3. Start MySQL (should be green)
```

**B. Test API in Browser**
```
http://localhost/alumni/api/core-team/list.php
```
Should show JSON data. If not, API is not working.

**C. Update API URL in App**

For **Android Emulator:**
```dart
// In lib/config/app_config.dart
static const String baseUrl = 'http://10.0.2.2/alumni';
```

For **Physical Device:**
```dart
// Use your computer's IP address
static const String baseUrl = 'http://192.168.1.XXX/alumni';
```

**D. Find Your Computer's IP:**
```bash
# Windows
ipconfig

# Look for "IPv4 Address" under your active network
# Example: 192.168.1.100
```

**E. Ensure Phone and Computer on Same WiFi**
- Both must be on the same network
- Disable mobile data on phone
- Connect to same WiFi as computer

---

### 2. Build Fails

**Error: "Gradle task assembleDebug failed"**

**Solution 1: Clean and Rebuild**
```bash
cd gmu_alumni_app
flutter clean
flutter pub get
flutter run
```

**Solution 2: Check Flutter Doctor**
```bash
flutter doctor
```
Fix any issues shown (usually Android licenses)

**Solution 3: Accept Android Licenses**
```bash
flutter doctor --android-licenses
```
Press 'y' to accept all

**Solution 4: Update Flutter**
```bash
flutter upgrade
```

---

### 3. No Devices Found

**Error: "No devices found"**

**For Android Emulator:**
```
1. Open Android Studio
2. Tools → Device Manager
3. Click "Create Device" if none exists
4. Select a device (e.g., Pixel 5)
5. Download system image if needed
6. Click "Start" to launch emulator
```

**For Physical Device:**
```
1. Enable Developer Options:
   - Settings → About Phone
   - Tap "Build Number" 7 times
   
2. Enable USB Debugging:
   - Settings → Developer Options
   - Turn on "USB Debugging"
   
3. Connect via USB cable

4. Allow USB debugging prompt on phone

5. Verify connection:
   flutter devices
```

---

### 4. Dependencies Error

**Error: "pub get failed"**

**Solution:**
```bash
cd gmu_alumni_app
flutter clean
rm pubspec.lock
flutter pub get
```

---

### 5. Hot Reload Not Working

**Solution:**
```bash
# In terminal where app is running:
# Press 'R' (capital R) for hot restart
# Or stop and restart:
flutter run
```

---

### 6. App Crashes on Startup

**Check Console for Errors:**
Look for red error messages in terminal

**Common Causes:**

**A. API URL Wrong**
```dart
// Check lib/config/app_config.dart
// Make sure baseUrl is correct
```

**B. Missing Dependencies**
```bash
flutter pub get
```

**C. Cache Issues**
```bash
flutter clean
flutter pub get
flutter run
```

---

### 7. Login Not Working

**Symptoms:**
- "Invalid credentials" error
- Login button does nothing

**Solutions:**

**A. Check Database**
```sql
-- In phpMyAdmin, run:
SELECT * FROM users WHERE email = 'your@email.com';
```

**B. Check Password**
- Passwords are hashed in database
- Use correct password you registered with

**C. Test API Directly**
```bash
# Test login API with curl:
curl -X POST http://localhost/alumni/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

**D. Check API Response**
Should return:
```json
{
  "success": true,
  "token": "...",
  "user": {...}
}
```

---

### 8. Images Not Loading

**Symptoms:**
- Broken image icons
- Gray boxes instead of images

**Solutions:**

**A. Check Image Paths**
Images should be accessible via:
```
http://localhost/alumni/assets/images/...
```

**B. Update Image URLs in API**
Make sure API returns full URLs:
```php
// In API files:
$imageUrl = 'http://localhost/alumni/' . $imagePath;
```

---

### 9. Slow Performance

**Solutions:**

**A. Use Release Build**
```bash
flutter run --release
```

**B. Enable Multidex** (already done)
Check `android/app/build.gradle.kts` has:
```kotlin
multiDexEnabled = true
```

**C. Optimize Images**
- Compress images before uploading
- Use appropriate image sizes

---

### 10. File Picker Warnings

**Warning: "file_picker references default plugin"**

**Status:** ⚠️ Warning only (not an error)

This is a known issue with the file_picker package. It doesn't affect functionality. You can safely ignore these warnings.

---

## Platform-Specific Issues

### Android

**Issue: "Minimum SDK version"**
```
Solution: Already set to minSdk = 21 (Android 5.0)
```

**Issue: "Multidex error"**
```
Solution: Already enabled in build.gradle.kts
```

### iOS

**Issue: "CocoaPods not installed"**
```bash
sudo gem install cocoapods
cd ios
pod install
```

---

## Verification Checklist

Before reporting issues, verify:

- [ ] XAMPP is running (Apache + MySQL)
- [ ] API works in browser: `http://localhost/alumni/api/core-team/list.php`
- [ ] Updated `baseUrl` in `lib/config/app_config.dart`
- [ ] Ran `flutter pub get`
- [ ] Ran `flutter clean` if build fails
- [ ] Device/emulator is connected: `flutter devices`
- [ ] No errors in `flutter doctor`

---

## Getting Help

### Check Logs

**Flutter Console:**
Look for error messages in terminal where you ran `flutter run`

**Android Logcat:**
```bash
flutter logs
```

**Verbose Output:**
```bash
flutter run -v
```

### Common Error Patterns

**"Connection refused"**
→ API URL is wrong or XAMPP not running

**"404 Not Found"**
→ API files not in correct location

**"Unauthorized"**
→ Token expired or invalid, try logging in again

**"No data"**
→ Database is empty or API query failed

---

## Quick Fixes Summary

| Issue | Quick Fix |
|-------|-----------|
| Build fails | `flutter clean && flutter pub get` |
| API not connecting | Check XAMPP, update baseUrl |
| No devices | Start emulator or enable USB debugging |
| Login fails | Check database, test API |
| Images broken | Check image paths and URLs |
| Slow performance | Use `flutter run --release` |

---

## Still Having Issues?

1. **Check all documentation:**
   - `RUN_APP.md` - Quick start
   - `FLUTTER_APP_SETUP_GUIDE.md` - Detailed setup
   - `PROJECT_SUMMARY.md` - Overview

2. **Run diagnostics:**
   ```bash
   flutter doctor -v
   flutter analyze
   ```

3. **Clean everything:**
   ```bash
   flutter clean
   rm -rf build/
   flutter pub get
   flutter run
   ```

4. **Check API manually:**
   - Test each endpoint in browser
   - Verify JSON responses
   - Check database has data

---

**Most issues are solved by:**
1. Running `flutter clean && flutter pub get`
2. Checking XAMPP is running
3. Verifying API URL is correct
4. Testing API in browser first

Good luck! 🚀
