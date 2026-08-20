# 🔴 404 ERROR FOUND - HERE'S THE FIX!

## ❌ The Problem

Your app is getting **404 Not Found** error when trying to access:
```
http://172.21.81.215/alumni/api/noticeboard/list.php
```

**This means:** Apache/XAMPP is either:
1. Not running
2. Running on a different port
3. Not configured to serve the `/alumni/` directory

---

## ✅ Solution Steps

### Step 1: Check if XAMPP is Running

1. Open **XAMPP Control Panel**
2. Check if **Apache** shows "Running" (green)
3. Check if **MySQL** shows "Running" (green)

**If not running:**
- Click "Start" next to Apache
- Click "Start" next to MySQL

### Step 2: Test if Apache is Working

Open browser and try:
```
http://localhost/
```

**Expected:** Should show XAMPP dashboard or a page

**If you get 404:** Apache is not running or misconfigured

### Step 3: Check Apache Port

XAMPP might be running on a different port (not 80).

**Check in XAMPP Control Panel:**
- Look at Apache line
- It might say "Port: 8080" or another number

**If using port 8080:**
```
http://localhost:8080/
```

### Step 4: Test the Alumni Directory

Once Apache is working, test:
```
http://localhost/alumni/
```

**Or if using port 8080:**
```
http://localhost:8080/alumni/
```

### Step 5: Test the API

```
http://localhost/alumni/api/test_all_apis.php
```

**Or:**
```
http://localhost:8080/alumni/api/test_all_apis.php
```

---

## 🔧 Update Flutter App with Correct URL

Once you find the working URL, update your Flutter app:

### If Apache is on Port 80:
```dart
// In gmu_alumni_app/lib/config/app_config.dart
static const String baseUrl = 'http://172.21.81.215/alumni';
```

### If Apache is on Port 8080:
```dart
// In gmu_alumni_app/lib/config/app_config.dart
static const String baseUrl = 'http://172.21.81.215:8080/alumni';
```

### If Apache is on Port 8000:
```dart
// In gmu_alumni_app/lib/config/app_config.dart
static const String baseUrl = 'http://172.21.81.215:8000/alumni';
```

---

## 🧪 Quick Test Commands

Run these in browser to find what works:

```
http://localhost/
http://localhost:8080/
http://localhost:8000/
http://localhost:8888/

http://localhost/alumni/
http://localhost:8080/alumni/

http://localhost/alumni/api/test_all_apis.php
http://localhost:8080/alumni/api/test_all_apis.php
```

---

## 📋 Checklist

- [ ] XAMPP Control Panel open
- [ ] Apache shows "Running" (green)
- [ ] MySQL shows "Running" (green)
- [ ] Note the port number Apache is using
- [ ] Test `http://localhost:[PORT]/` in browser
- [ ] Test `http://localhost:[PORT]/alumni/` in browser
- [ ] Test `http://localhost:[PORT]/alumni/api/test_all_apis.php`
- [ ] Update Flutter app with correct URL including port
- [ ] Run `flutter run` again

---

## 🎯 Most Common Solutions

### Solution 1: Start XAMPP
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Test: http://localhost/alumni/
```

### Solution 2: Use Correct Port
```
1. Check XAMPP Control Panel for Apache port
2. Update Flutter app:
   static const String baseUrl = 'http://172.21.81.215:PORT/alumni';
3. Run flutter run
```

### Solution 3: Check Firewall
```
1. Windows Firewall might be blocking
2. Allow Apache through firewall
3. Test again
```

---

## 📝 What to Do Next

1. **Open XAMPP Control Panel**
2. **Start Apache and MySQL**
3. **Find the correct port** (usually 80, 8080, or 8000)
4. **Test in browser:** `http://localhost:PORT/alumni/api/test_all_apis.php`
5. **Update Flutter app** with correct URL
6. **Run app again**

---

**Once you find the working URL, let me know and I'll update the Flutter app configuration!**
