# 🔌 Connect Flutter App to Real Database Data

## ✅ API Files Updated!

All API files have been updated to match your actual database schema.

---

## 📋 What Was Fixed

### API Endpoints Updated:

1. **Noticeboard API** (`api/noticeboard/list.php`)
   - ✅ Uses `priority` column (not `category`)
   - ✅ Orders by `publish_date`

2. **News API** (`api/news/list.php`)
   - ✅ Uses `featured_image` column (not `image`)
   - ✅ Orders by `publish_date`

3. **Events API** (`api/events/list.php`)
   - ✅ Uses `featured_image` column
   - ✅ Correct column mapping

4. **Jobs API** (`api/jobs/list.php`)
   - ✅ Uses `salary_range` column (not `salary`)
   - ✅ Correct schema

5. **Galleries API** (`api/galleries/list.php`)
   - ✅ Uses `title` column (not `name`)
   - ✅ Checks for `gallery_images` table
   - ✅ Handles both cases

---

## 🧪 Test Your APIs

### 1. Make Sure XAMPP is Running

- Open XAMPP Control Panel
- Start **Apache** (should be green)
- Start **MySQL** (should be green)

### 2. Test APIs in Browser

Open these URLs in your browser:

```
http://localhost/alumni/api/noticeboard/list.php
http://localhost/alumni/api/news/list.php
http://localhost/alumni/api/events/list.php
http://localhost/alumni/api/jobs/list.php
http://localhost/alumni/api/galleries/list.php
http://localhost/alumni/api/core-team/list.php
```

**Expected Response:**
```json
{
  "success": true,
  "data": [...]
}
```

**If you see error:**
```json
{
  "success": false,
  "message": "Server error: ..."
}
```
This tells you what's wrong!

---

## 📱 Update Flutter Screens to Use Real Data

Currently, the Flutter screens use sample/dummy data. Here's how to connect them to real API:

### Example: Update Noticeboard Screen

**Current (Dummy Data):**
```dart
// In noticeboard_screen.dart
final List<NoticeModel> notices = [
  NoticeModel(
    id: '1',
    title: 'Annual Alumni Meet 2024',
    content: 'Join us for the annual alumni meet...',
    ...
  ),
];
```

**Updated (Real Data):**
```dart
import '../services/api_service.dart';
import '../config/app_config.dart';

class NoticeboardScreen extends StatefulWidget {
  const NoticeboardScreen({super.key});

  @override
  State<NoticeboardScreen> createState() => _NoticeboardScreenState();
}

class _NoticeboardScreenState extends State<NoticeboardScreen> {
  List<NoticeModel> notices = [];
  bool isLoading = true;
  String? error;

  @override
  void initState() {
    super.initState();
    fetchNotices();
  }

  Future<void> fetchNotices() async {
    try {
      final response = await ApiService.get('/noticeboard/list.php');
      
      if (response.data['success']) {
        final data = response.data['data'] as List;
        setState(() {
          notices = data.map((json) => NoticeModel.fromJson(json)).toList();
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        error = e.toString();
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        appBar: AppBar(title: const Text('Noticeboard')),
        body: const Center(child: CircularProgressIndicator()),
      );
    }

    if (error != null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Noticeboard')),
        body: Center(child: Text('Error: $error')),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Noticeboard')),
      body: ListView.builder(
        itemCount: notices.length,
        itemBuilder: (context, index) {
          final notice = notices[index];
          return Card(
            child: ListTile(
              title: Text(notice.title),
              subtitle: Text(notice.content),
            ),
          );
        },
      ),
    );
  }
}
```

---

## 🔧 Quick Implementation Guide

### For Each Screen:

1. **Import required packages:**
   ```dart
   import '../services/api_service.dart';
   import '../config/app_config.dart';
   ```

2. **Convert to StatefulWidget** (if not already)

3. **Add state variables:**
   ```dart
   List<YourModel> items = [];
   bool isLoading = true;
   String? error;
   ```

4. **Add fetch method:**
   ```dart
   Future<void> fetchData() async {
     try {
       final response = await ApiService.get('/your-endpoint/list.php');
       if (response.data['success']) {
         final data = response.data['data'] as List;
         setState(() {
           items = data.map((json) => YourModel.fromJson(json)).toList();
           isLoading = false;
         });
       }
     } catch (e) {
       setState(() {
         error = e.toString();
         isLoading = false;
       });
     }
   }
   ```

5. **Call in initState:**
   ```dart
   @override
   void initState() {
     super.initState();
     fetchData();
   }
   ```

6. **Update build method** to show loading/error states

---

## 📊 Screens to Update

| Screen | Endpoint | Status |
|--------|----------|--------|
| Noticeboard | `/noticeboard/list.php` | ✅ API Ready |
| News | `/news/list.php` | ✅ API Ready |
| Events | `/events/list.php` | ✅ API Ready |
| Jobs | `/jobs/list.php` | ✅ API Ready |
| Galleries | `/galleries/list.php` | ✅ API Ready |
| Core Team | `/core-team/list.php` | ✅ API Ready |
| Proud Alumni | `/proud-alumni/list.php` | ✅ API Ready |

---

## 🎯 Testing Checklist

Before updating Flutter screens:

- [ ] XAMPP is running
- [ ] All API endpoints return JSON in browser
- [ ] Database has data in tables
- [ ] API URL is correct in `app_config.dart`
- [ ] Phone/emulator can reach the API

---

## 🐛 Troubleshooting

### API Returns Empty Array `[]`

**Cause:** No data in database table

**Solution:** Add data to your database tables

### API Returns 404

**Cause:** XAMPP not running or wrong path

**Solution:** 
1. Start XAMPP
2. Check URL: `http://localhost/alumni/api/...`

### API Returns Error Message

**Cause:** Database error (check error message)

**Solution:** Check the error message in JSON response

### Flutter Can't Connect

**Cause:** Wrong API URL

**Solution:** 
- Emulator: Use `http://10.0.2.2/alumni`
- Device: Use `http://YOUR_IP/alumni`

---

## 📝 Example: Complete Updated Screen

See the example above for Noticeboard screen. Apply the same pattern to:
- `news_screen.dart`
- `events_screen.dart`
- `jobs_screen.dart`
- `galleries_screen.dart`

---

## ✅ Summary

1. ✅ **API files updated** to match database schema
2. ✅ **Error messages added** for debugging
3. ✅ **Column names fixed** (priority, featured_image, salary_range, etc.)
4. 📱 **Flutter screens** need to be updated to use ApiService
5. 🧪 **Test APIs** in browser first before updating Flutter

---

**Next Step:** Test all API endpoints in browser, then update Flutter screens one by one!
