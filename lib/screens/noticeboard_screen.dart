import 'package:flutter/material.dart';
import '../widgets/app_drawer.dart';
import 'package:intl/intl.dart';
import '../config/app_config.dart';
import '../models/notice_model.dart';
import '../models/event_model.dart';
import '../services/api_service.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

class NoticeboardScreen extends StatefulWidget {
  final int initialIndex;
  const NoticeboardScreen({super.key, this.initialIndex = 0});

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
  Map<String, bool> submittedFeedbackEvents = {};
  Map<String, bool> registeredEventsMap = {};
  bool isLoadingEvents = true;
  String? eventsError;
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  DateTime? lastCheckedNotices;
  DateTime? lastCheckedEvents;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this, initialIndex: widget.initialIndex);
    _tabController.addListener(_handleTabSelection);
    _loadLastChecked();
    fetchNotices();
    fetchEvents();
  }

  Future<void> _loadLastChecked() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      final noticesStr = prefs.getString('lastCheckedNotices');
      if (noticesStr != null) lastCheckedNotices = DateTime.parse(noticesStr);
      
      final eventsStr = prefs.getString('lastCheckedEvents');
      if (eventsStr != null) lastCheckedEvents = DateTime.parse(eventsStr);
    });
    // Immediately mark the initial tab as checked
    if (mounted) _handleTabSelection();
  }

  Future<void> _handleTabSelection() async {
    if (!mounted) return;
    final prefs = await SharedPreferences.getInstance();
    final now = DateTime.now();
    if (_tabController.index == 0) {
      await prefs.setString('lastCheckedNotices', now.toIso8601String());
      setState(() {
        lastCheckedNotices = now;
      });
    } else if (_tabController.index == 1) {
      await prefs.setString('lastCheckedEvents', now.toIso8601String());
      setState(() {
        lastCheckedEvents = now;
      });
    }
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
        
        // Also fetch registered events to know feedback status
        try {
          final regResponse = await ApiService.get('/alumni/get_registered_events.php');
          if (regResponse.data['success'] == true) {
            final List regEvents = regResponse.data['data'];
            for (var reg in regEvents) {
              final eventId = reg['id'].toString();
              registeredEventsMap[eventId] = true;
              if (reg['has_submitted_feedback'] == 1 || reg['has_submitted_feedback'] == '1' || reg['has_submitted_feedback'] == true) {
                submittedFeedbackEvents[eventId] = true;
              }
            }
          } else {
            print("get_registered_events failed: ${regResponse.data}");
          }
        } catch (e) {
          print("Error fetching registered events: $e");
        }

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

  Future<void> _handleEventTap(EventModel event) async {
    if (submittedFeedbackEvents[event.id] == true) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('You have already submitted feedback for this event. Thank you!'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.green,
        )
      );
      return;
    }

    final bool hasEventEnded = event.endDate != null
        ? DateTime.now().isAfter(event.endDate!)
        : DateTime.now().isAfter(event.eventDate.add(const Duration(hours: 4)));

    if (registeredEventsMap[event.id] == true && !hasEventEnded) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('You can submit feedback after the event has ended.'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.orange,
        )
      );
      // Wait, should we block them from viewing the event completely, or just block them from giving feedback?
      // The user said "and feedback only after teh end time is done", implying they shouldn't give feedback.
      // But they might want to view the event details or scan QR (if they aren't marked attended yet, but if they are registered, they might want to scan).
      // Let's allow the tap, but in the dialog we will disable the feedback button or change its text!
      // Actually, wait, the user's request: "and feedback only after teh end time is done". Let's handle it in the dialog!
    }

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
        bool hasSubmittedFeedback = false;
        
        for (var regEvent in registeredEvents) {
          if (regEvent['id'].toString() == event.id) {
            isRegistered = true;
            hasSubmittedFeedback = regEvent['has_submitted_feedback'] == 1 || regEvent['has_submitted_feedback'] == '1' || regEvent['has_submitted_feedback'] == true;
            break;
          }
        }
        
        if (mounted) {
          if (isRegistered && hasSubmittedFeedback) {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('You have already submitted feedback for this event. Thank you!'),
                behavior: SnackBarBehavior.floating,
                backgroundColor: Colors.green,
              )
            );
          } else {
            final bool hasEventEnded = event.endDate != null
                ? DateTime.now().isAfter(event.endDate!)
                : DateTime.now().isAfter(event.eventDate.add(const Duration(hours: 4)));
            _showEventActionDialog(context, event, isRegistered, hasEventEnded);
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
    final int newNoticesCount = lastCheckedNotices != null 
        ? notices.where((n) => n.createdAt.isAfter(lastCheckedNotices!)).length
        : notices.where((n) => DateTime.now().difference(n.createdAt).inDays < 2).length;
        
    final int newEventsCount = lastCheckedEvents != null
        ? events.where((e) => e.createdAt.isAfter(lastCheckedEvents!)).length
        : events.where((e) => DateTime.now().difference(e.createdAt).inDays < 2).length;

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
          tabs: [
            Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Notices'),
                  if (newNoticesCount > 0) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.redAccent,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '$newNoticesCount',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Tab(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Events'),
                  if (newEventsCount > 0) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.redAccent,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '$newEventsCount',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ],
              ),
            ),
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
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
        border: isUrgent 
            ? Border.all(color: Colors.redAccent.withOpacity(0.3), width: 1.5)
            : Border.all(color: Colors.grey.withOpacity(0.15), width: 1.5),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _showNoticeDetails(context, notice),
          borderRadius: BorderRadius.circular(16),
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
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        notice.title,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppConfig.textColor,
                          letterSpacing: -0.3,
                        ),
                      ),
                    ),
                  ],
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
    final bool isRegistered = registeredEventsMap[event.id] == true;
    final bool hasSubmittedFeedback = submittedFeedbackEvents[event.id] == true;
    final bool hasEventEnded = event.endDate != null
        ? DateTime.now().isAfter(event.endDate!)
        : DateTime.now().isAfter(event.eventDate.add(const Duration(hours: 4)));

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.grey.withOpacity(0.15), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _handleEventTap(event),
          borderRadius: BorderRadius.circular(16),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Flexible(
                            child: Text(
                              event.title, 
                              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 17, color: AppConfig.textColor, letterSpacing: -0.3),
                            ),
                          ),
                          if (hasSubmittedFeedback) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.green.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: Colors.green.withOpacity(0.3)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.check_circle_rounded, color: Colors.green, size: 12),
                                  const SizedBox(width: 4),
                                  const Text('Submitted', style: TextStyle(color: Colors.green, fontSize: 10, fontWeight: FontWeight.w800)),
                                ],
                              ),
                            ),
                          ] else if (isRegistered && hasEventEnded) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: Colors.orange.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(6),
                                border: Border.all(color: Colors.orange.withOpacity(0.3)),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.error_outline_rounded, color: Colors.orange, size: 12),
                                  const SizedBox(width: 4),
                                  const Text('Pending', style: TextStyle(color: Colors.orange, fontSize: 10, fontWeight: FontWeight.w800)),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: Colors.blueGrey.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Icon(Icons.calendar_month_rounded, size: 14, color: Colors.blueGrey[700]),
                    ),
                    const SizedBox(width: 10),
                    Text(
                      DateFormat('MMM dd, yyyy - hh:mm a').format(event.eventDate.toLocal()),
                      style: TextStyle(color: Colors.grey[700], fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showEventActionDialog(BuildContext context, EventModel event, bool isRegistered, bool hasEventEnded) {
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
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppConfig.primaryColor.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isRegistered ? Icons.rate_review_rounded : Icons.event_available_rounded, 
                  size: 48, 
                  color: AppConfig.primaryColor
                ),
              ),
              const SizedBox(height: 24),
              Text(
                event.title,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 22.0,
                  fontWeight: FontWeight.w800,
                  color: AppConfig.textColor,
                  letterSpacing: -0.5,
                ),
              ),
              const SizedBox(height: 16),
              Text(
                isRegistered 
                  ? (hasEventEnded 
                      ? 'Thank you for being a part of this event! We would love to hear your thoughts. Would you like to share your feedback?'
                      : 'You are registered for this event. You can submit your feedback after the event has ended.')
                  : 'You are not registered for this event yet. Would you like to scan the QR code to register now?',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 15.0,
                  color: Colors.grey[700],
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 32),
              if (!isRegistered)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      context.push('/scan-qr');
                    },
                    icon: const Icon(Icons.qr_code_scanner_rounded),
                    label: const Text('Register via QR', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppConfig.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                )
              else
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: hasEventEnded ? () {
                      Navigator.pop(context);
                      context.push('/event-rating', extra: int.parse(event.id));
                    } : null,
                    icon: const Icon(Icons.stars_rounded),
                    label: const Text('Give Feedback', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppConfig.primaryColor,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: () => Navigator.pop(context),
                style: TextButton.styleFrom(
                  foregroundColor: Colors.grey[600],
                  padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 24),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Maybe Later', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
