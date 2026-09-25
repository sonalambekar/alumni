import 'package:flutter/material.dart';
import '../widgets/app_drawer.dart';
import 'package:intl/intl.dart';
import '../config/app_config.dart';
import '../models/notice_model.dart';
import '../models/event_model.dart';
import '../services/api_service.dart';
import 'package:go_router/go_router.dart';

class NoticeboardScreen extends StatefulWidget {
  const NoticeboardScreen({super.key});

  @override
  State<NoticeboardScreen> createState() => _NoticeboardScreenState();
}

class _NoticeboardScreenState extends State<NoticeboardScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  
  List<NoticeModel> notices = [];
  bool isLoadingNotices = true;
  String? noticesError;

  List<EventModel> events = [];
  List<EventModel> filteredEvents = [];
  bool isLoadingEvents = true;
  String? eventsError;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    fetchNotices();
    fetchEvents();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> fetchNotices() async {
    try {
      setState(() => isLoadingNotices = true);
      final response = await ApiService.get('/noticeboard/list.php');
      
      if (response.data['success'] == true) {
        final data = response.data['data'] as List;
        setState(() {
          notices = data.map((json) => NoticeModel.fromJson(json)).toList();
          isLoadingNotices = false;
        });
      } else {
        setState(() {
          noticesError = 'Failed to load notices';
          isLoadingNotices = false;
        });
      }
    } catch (e) {
      setState(() {
        noticesError = 'Something went wrong. Please check your connection.';
        isLoadingNotices = false;
      });
    }
  }

  Future<void> fetchEvents() async {
    try {
      setState(() => isLoadingEvents = true);
      final response = await ApiService.get('/events/list.php');
      
      if (response.data['success'] == true) {
        final data = response.data['data'] as List;
        setState(() {
          events = data.map((json) => EventModel.fromJson(json)).toList();
          filteredEvents = events;
          isLoadingEvents = false;
        });
      } else {
        setState(() {
          eventsError = 'Failed to load events';
          isLoadingEvents = false;
        });
      }
    } catch (e) {
      setState(() {
        eventsError = 'Something went wrong. Please check your connection.';
        isLoadingEvents = false;
      });
    }
  }

  void _filterEvents(String query) {
    setState(() {
      _searchQuery = query;
      if (query.isEmpty) {
        filteredEvents = events;
      } else {
        filteredEvents = events
            .where((e) => e.title.toLowerCase().contains(query.toLowerCase()))
            .toList();
      }
    });
  }

  Future<void> _checkRegistrationAndFeedback(EventModel event) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => const Center(child: CircularProgressIndicator()),
    );
    
    try {
      final response = await ApiService.get('/alumni/get_registered_events.php');
      if (mounted) Navigator.pop(context); // close loading
      
      if (response.data['success'] == true) {
        final List registeredEvents = response.data['data'];
        bool isRegistered = false;
        
        for (var regEvent in registeredEvents) {
          if (regEvent['id'].toString() == event.id) {
            isRegistered = true;
            break;
          }
        }
        
        if (isRegistered) {
          if (mounted) context.push('/feedback-options', extra: int.parse(event.id));
        } else {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Please register for this event first by scanning the QR code.'),
                backgroundColor: Colors.orange,
                duration: Duration(seconds: 4),
              )
            );
          }
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to check registration status.'))
          );
        }
      }
    } catch (e) {
      if (mounted) {
        Navigator.pop(context); // close loading
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'))
        );
      }
    }
  }

  String _getTimeAgo(DateTime dateTime) {
    final now = DateTime.now();
    final difference = now.difference(dateTime);

    if (difference.inDays > 7) {
      return DateFormat('MMM dd, yyyy').format(dateTime);
    } else if (difference.inDays >= 1) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours >= 1) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes >= 1) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      backgroundColor: AppConfig.bgLight,
      appBar: AppBar(
        leading: Builder(
          builder: (context) => IconButton(
            icon: const Icon(Icons.menu),
            onPressed: () => Scaffold.of(context).openDrawer(),
          ),
        ),
        title: const Text('Alerts & Events'),
        elevation: 0,
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          unselectedLabelStyle: const TextStyle(fontWeight: FontWeight.normal, fontSize: 15),
          tabs: const [
            Tab(text: 'Notices'),
            Tab(text: 'Events'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          RefreshIndicator(
            onRefresh: fetchNotices,
            color: AppConfig.primaryColor,
            child: _buildNoticesBody(),
          ),
          RefreshIndicator(
            onRefresh: fetchEvents,
            color: AppConfig.primaryColor,
            child: _buildEventsBody(),
          ),
        ],
      ),
    );
  }

  Widget _buildNoticesBody() {
    if (isLoadingNotices && notices.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (noticesError != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.error_outline_rounded, size: 80, color: Colors.red[200]),
              const SizedBox(height: 16),
              Text(
                noticesError!,
                textAlign: TextAlign.center,
                style: TextStyle(color: Colors.grey[600], fontSize: 16),
              ),
              const SizedBox(height: 24),
              ElevatedButton.icon(
                onPressed: fetchNotices,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Try Again'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppConfig.primaryColor,
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (notices.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.notifications_none_rounded, size: 80, color: Colors.grey[200]),
            const SizedBox(height: 16),
            Text(
              'No notices available',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.grey[400],
              ),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
      itemCount: notices.length,
      itemBuilder: (context, index) {
        return _buildNoticeCard(notices[index]);
      },
    );
  }

  Widget _buildNoticeCard(NoticeModel notice) {
    final bool isUrgent = notice.category?.toLowerCase() == 'urgent' || 
                         notice.title.toLowerCase().contains('urgent');

    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(15),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
        border: isUrgent 
            ? Border.all(color: Colors.redAccent.withOpacity(0.3), width: 1)
            : null,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _showNoticeDetails(context, notice),
          borderRadius: BorderRadius.circular(15),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    if (notice.category != null)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: isUrgent 
                              ? Colors.red[50] 
                              : AppConfig.secondaryColor.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          notice.category!.toUpperCase(),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: isUrgent ? Colors.redAccent : AppConfig.primaryColor,
                          ),
                        ),
                      ),
                    Text(
                      _getTimeAgo(notice.createdAt),
                      style: TextStyle(fontSize: 12, color: Colors.grey[500]),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  notice.title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppConfig.textColor,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  notice.content,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(fontSize: 14, color: Colors.grey[700]),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showNoticeDetails(BuildContext context, NoticeModel notice) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.only(
            topLeft: Radius.circular(20),
            topRight: Radius.circular(20),
          ),
        ),
        padding: const EdgeInsets.all(24),
        constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.85),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40, height: 4,
                  margin: const EdgeInsets.only(bottom: 24),
                  decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(2)),
                ),
              ),
              Text(
                notice.title,
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppConfig.textColor),
              ),
              const SizedBox(height: 8),
              Text(
                DateFormat('MMMM dd, yyyy - hh:mm a').format(notice.createdAt.toLocal()),
                style: TextStyle(fontSize: 14, color: Colors.grey[500]),
              ),
              const Padding(padding: EdgeInsets.symmetric(vertical: 16), child: Divider()),
              Text(
                notice.content,
                style: const TextStyle(fontSize: 16, color: AppConfig.textColor, height: 1.6),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppConfig.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  child: const Text('Close'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEventsBody() {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(16.0),
          child: TextField(
            controller: _searchController,
            onChanged: _filterEvents,
            decoration: InputDecoration(
              hintText: 'Search Event Name',
              prefixIcon: const Icon(Icons.search),
              filled: true,
              fillColor: Colors.white,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide.none,
              ),
            ),
          ),
        ),
        Expanded(
          child: isLoadingEvents && events.isEmpty
              ? const Center(child: CircularProgressIndicator())
              : filteredEvents.isEmpty
                  ? const Center(child: Text('No events found'))
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      itemCount: filteredEvents.length,
                      itemBuilder: (context, index) {
                        return _buildEventCard(filteredEvents[index]);
                      },
                    ),
        ),
      ],
    );
  }

  Widget _buildEventCard(EventModel event) {
    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 2,
      shadowColor: Colors.black12,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
      child: InkWell(
        onTap: () => _showEventActionDialog(context, event),
        borderRadius: BorderRadius.circular(15),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      event.title, 
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppConfig.textColor),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const Icon(Icons.chevron_right, color: Colors.grey),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Icon(Icons.calendar_today_rounded, size: 14, color: AppConfig.primaryColor),
                  const SizedBox(width: 6),
                  Text(
                    DateFormat('MMM dd, yyyy - hh:mm a').format(event.eventDate.toLocal()),
                    style: TextStyle(color: Colors.grey[600], fontSize: 13, fontWeight: FontWeight.w500),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showEventActionDialog(BuildContext context, EventModel event) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        elevation: 0,
        backgroundColor: Colors.transparent,
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            shape: BoxShape.rectangle,
            borderRadius: BorderRadius.circular(20),
            boxShadow: const [
              BoxShadow(
                color: Colors.black26,
                blurRadius: 10.0,
                offset: Offset(0.0, 10.0),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppConfig.primaryColor.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.event_available_rounded, size: 40, color: AppConfig.primaryColor),
              ),
              const SizedBox(height: 24),
              Text(
                event.title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 20.0,
                  fontWeight: FontWeight.bold,
                  color: AppConfig.textColor,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                'Would you like to register for this event or share your feedback?',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14.0,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: 32),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    context.push('/scan-qr');
                  },
                  icon: const Icon(Icons.qr_code_scanner),
                  label: const Text('Register', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppConfig.primaryColor,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    _checkRegistrationAndFeedback(event);
                  },
                  icon: const Icon(Icons.feedback_outlined),
                  label: const Text('Give Feedback', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppConfig.primaryColor,
                    side: const BorderSide(color: AppConfig.primaryColor, width: 1.5),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              TextButton(
                onPressed: () => Navigator.pop(context),
                style: TextButton.styleFrom(foregroundColor: Colors.grey[600]),
                child: const Text('Cancel'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
