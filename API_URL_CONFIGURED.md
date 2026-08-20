# ✅ API URL CONFIGURED!

## 🎯 Your App is Now Configured

Your Flutter app is now configured to connect to your server at:

```
http://172.21.81.215/alumni
```

---

## 📱 Configuration Applied

**File Updated:** `gmu_alumni_app/lib/config/app_config.dart`

**API Base URL:**
```dart
static const String baseUrl = 'http://172.21.81.215/alumni';
```

**API Endpoints:**
```
http://172.21.81.215/alumni/api/noticeboard/list.php
http://172.21.81.215/alumni/api/news/list.php
http://172.21.81.215/alumni/api/events/list.php
http://172.21.81.215/alumni/api/jobs/list.php
http://172.21.81.215/alumni/api/galleries/list.php
http://172.21.81.215/alumni/api/core-team/list.php
```

---

## 🧪 Test Your Connection

### 1. Test APIs in Browser

Open these URLs in your browser to verify they work:

```
http://172.21.81.215/alumni/api/test_all_apis.php
http://172.21.81.215/alumni/api/noticeboard/list.php
http://172.21.81.215/alumni/api/news/list.php
```

**Expected Response:**
```json
{
  "success": true,
  "data": [...]
}
```

### 2. Run Your Flutter App

```bash
cd gmu_alumni_app
flutter run
```

---

## 📋 Requirements

For the app to connect successfully:

1. ✅ **XAMPP Running**
   - Apache: Started
   - MySQL: Started

2. ✅ **Network Connection**
   - Phone/emulator on same network
   - Can reach IP: 172.21.81.215

3. ✅ **Firewall**
   - Port 80 open
   - Allow incoming connections

---

## 🔍 Troubleshooting

### If App Can't Connect:

**1. Test API in Browser First**
```
http://172.21.81.215/alumni/api/noticeboard/list.php
```

**2. Check Network**
- Phone and computer on same WiFi?
- Can you ping 172.21.81.215?

**3. Check XAMPP**
- Apache running?
- MySQL running?

**4. Check Firewall**
- Windows Firewall allowing Apache?
- Port 80 open?

---

## 🎯 What Happens Now

When you run the app:

1. **App starts** → Shows splash screen
2. **Login screen** → Enter credentials
3. **Home screen** → Shows dashboard
4. **Navigate to any screen** → Fetches real data from:
   ```
   http://172.21.81.215/alumni/api/...
   ```

---

## 📱 Screens Connected

All these screens now fetch from your server:

- ✅ **Noticeboard** → `http://172.21.81.215/alumni/api/noticeboard/list.php`
- ✅ **News** → `http://172.21.81.215/alumni/api/news/list.php`
- ✅ **Events** → `http://172.21.81.215/alumni/api/events/list.php`
- ✅ **Jobs** → `http://172.21.81.215/alumni/api/jobs/list.php`
- ✅ **Galleries** → `http://172.21.81.215/alumni/api/galleries/list.php`

---

## ✅ Ready to Run!

Your app is now configured and ready to use!

**Just run:**
```bash
cd gmu_alumni_app
flutter run
```

**And your app will connect to your server at 172.21.81.215!** 🎉

---

## 📝 Notes

- **IP Address:** 172.21.81.215
- **Port:** 80 (default HTTP)
- **Path:** /alumni
- **Database:** Same as website

---

**Everything is configured! Run the app and it will fetch real data from your database!** ✅
