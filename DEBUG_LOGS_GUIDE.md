# 🐛 Debug Logs Added - How to View Errors

## ✅ Debug Statements Added

I've added detailed print statements to all screens and the API service to help identify errors.

---

## 📱 How to View Debug Logs

### Method 1: Flutter Run Terminal

When you run `flutter run`, all print statements appear in the terminal.

**Look for these symbols:**
- 🔵 = Info/Progress messages
- ✅ = Success messages
- ❌ = Error messages
- 🌐 = API Service messages

### Method 2: Flutter Logs Command

In a separate terminal:
```bash
cd gmu_alumni_app
flutter logs
```

---

## 🔍 What You'll See

### When You Click a Feature (e.g., News):

```
🔵 News: Starting to fetch news...
🔵 API URL: http://172.21.81.215/alumni/api/news/list.php
🌐 API Service: Making GET request
🌐 Base URL: http://172.21.81.215/alumni/api
🌐 Endpoint: /news/list.php
🌐 Full URL: http://172.21.81.215/alumni/api/news/list.php
✅ API Service: Response received
✅ Status Code: 200
✅ Response Data: {success: true, data: [...]}
🔵 Response received: {success: true, data: [...]}
✅ Success! Found 5 news articles
```

### If There's an Error:

```
🔵 News: Starting to fetch news...
🔵 API URL: http://172.21.81.215/alumni/api/news/list.php
🌐 API Service: Making GET request
❌ API Service ERROR: DioException [connection timeout]
❌ DioException Type: connectionTimeout
❌ DioException Message: Connection timeout
❌ ERROR in fetchNews: DioException...
❌ Stack trace: ...
```

---

## 🎯 Common Errors and What They Mean

### 1. Connection Timeout
```
❌ DioException Type: connectionTimeout
```
**Cause:** Can't reach the server
**Fix:**
- Check XAMPP is running
- Verify IP address: 172.21.81.215
- Check phone/emulator on same network

### 2. Connection Refused
```
❌ DioException Type: connectionError
❌ Connection refused
```
**Cause:** Server not responding
**Fix:**
- Start Apache in XAMPP
- Check firewall settings
- Verify port 80 is open

### 3. 404 Not Found
```
✅ Status Code: 404
```
**Cause:** API file doesn't exist
**Fix:**
- Check API files exist in `/alumni/api/`
- Verify path is correct

### 4. 500 Server Error
```
✅ Status Code: 500
❌ Response Data: {success: false, message: "..."}
```
**Cause:** PHP error on server
**Fix:**
- Check PHP error logs
- Test API in browser
- Check database connection

### 5. JSON Parse Error
```
❌ ERROR in fetchNews: FormatException: Unexpected character
```
**Cause:** API not returning valid JSON
**Fix:**
- Test API in browser
- Check for PHP errors/warnings
- Verify API returns proper JSON

---

## 🧪 Testing Steps

### 1. Run the App with Logs Visible

```bash
cd gmu_alumni_app
flutter run
```

Keep this terminal open to see logs.

### 2. Click on Each Feature

Click on:
- Noticeboard
- News
- Events
- Jobs
- Galleries

### 3. Watch the Terminal

Look for:
- 🔵 Blue messages = What's happening
- ✅ Green checkmarks = Success
- ❌ Red X = Errors

### 4. Copy Error Messages

If you see errors, copy the entire error message including:
- The error type
- The error message
- The stack trace

---

## 📋 Debug Checklist

When you get an error, check these in order:

1. **Network Connection**
   ```
   Can you open http://172.21.81.215/alumni in browser?
   ```

2. **XAMPP Running**
   ```
   Apache: Green?
   MySQL: Green?
   ```

3. **API Works in Browser**
   ```
   http://172.21.81.215/alumni/api/test_all_apis.php
   ```

4. **Phone on Same Network**
   ```
   Same WiFi as computer?
   ```

5. **Firewall**
   ```
   Windows Firewall allowing Apache?
   ```

---

## 🔍 Example Debug Session

### Scenario: News Screen Shows Error

**Step 1: Click News**

**Terminal Shows:**
```
🔵 News: Starting to fetch news...
🔵 API URL: http://172.21.81.215/alumni/api/news/list.php
🌐 API Service: Making GET request
❌ API Service ERROR: DioException [connection timeout]
```

**Step 2: Identify Problem**
- Connection timeout = Can't reach server

**Step 3: Test in Browser**
```
http://172.21.81.215/alumni/api/news/list.php
```

**Step 4: Fix**
- If browser works: Phone not on same network
- If browser fails: XAMPP not running or firewall blocking

---

## 📝 What Each Screen Logs

### Noticeboard
```
🔵 Noticeboard: Starting to fetch notices...
🔵 API URL: http://172.21.81.215/alumni/api/noticeboard/list.php
```

### News
```
🔵 News: Starting to fetch news...
🔵 API URL: http://172.21.81.215/alumni/api/news/list.php
```

### Events
```
🔵 Events: Starting to fetch events...
🔵 API URL: http://172.21.81.215/alumni/api/events/list.php
```

### Jobs
```
🔵 Jobs: Starting to fetch jobs...
🔵 API URL: http://172.21.81.215/alumni/api/jobs/list.php
```

### Galleries
```
🔵 Galleries: Starting to fetch galleries...
🔵 API URL: http://172.21.81.215/alumni/api/galleries/list.php
```

---

## 🎯 Quick Debug Commands

### View Logs in Real-Time
```bash
flutter logs
```

### Clear and Restart
```bash
flutter clean
flutter pub get
flutter run
```

### Check Device Connection
```bash
flutter devices
```

---

## ✅ What to Look For

### Success Pattern:
```
🔵 Starting...
🌐 Making request...
✅ Response received
✅ Status Code: 200
✅ Success! Found X items
```

### Error Pattern:
```
🔵 Starting...
🌐 Making request...
❌ ERROR: ...
❌ Stack trace: ...
```

---

## 📞 Next Steps

1. **Run the app** with `flutter run`
2. **Click on a feature** (e.g., News)
3. **Watch the terminal** for debug messages
4. **Copy any error messages** you see
5. **Share the error** so we can fix it!

---

**Now when you click any feature, you'll see exactly what's happening in the terminal!** 🐛✅
