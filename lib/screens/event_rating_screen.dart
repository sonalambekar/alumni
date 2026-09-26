import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'dart:convert';
import 'package:provider/provider.dart';
import 'package:dio/dio.dart';
import '../../config/app_config.dart';
import '../../services/api_service.dart';
import '../../providers/auth_provider.dart';

class EventRatingScreen extends StatefulWidget {
  final int? eventId;

  const EventRatingScreen({super.key, this.eventId});

  @override
  State<EventRatingScreen> createState() => _EventRatingScreenState();
}

class _EventRatingScreenState extends State<EventRatingScreen> {
  int? _ratingOverall;
  int? _ratingAgenda;
  int? _ratingFood;
  int? _ratingVenue;
  int? _ratingFuture;
  bool _isSubmitting = false;

  bool get _isAllRated => 
      _ratingOverall != null && 
      _ratingAgenda != null && 
      _ratingFood != null && 
      _ratingVenue != null && 
      _ratingFuture != null;

  Widget _buildRatingQuestion(String question, int? currentVal, Function(int) onSelected) {
    bool isAnswered = currentVal != null;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      margin: const EdgeInsets.symmetric(vertical: 10, horizontal: 16),
      decoration: BoxDecoration(
        color: isAnswered ? Colors.white : Colors.white.withOpacity(0.9),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isAnswered ? AppConfig.primaryColor.withOpacity(0.5) : Colors.grey.shade200,
          width: isAnswered ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: isAnswered ? AppConfig.primaryColor.withOpacity(0.1) : Colors.black.withOpacity(0.05),
            blurRadius: isAnswered ? 12 : 8,
            offset: const Offset(0, 4),
          )
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(
                  isAnswered ? Icons.check_circle_rounded : Icons.help_outline_rounded,
                  color: isAnswered ? AppConfig.primaryColor : Colors.grey.shade400,
                  size: 24,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    question,
                    style: TextStyle(
                      fontSize: 16,
                      height: 1.4,
                      fontWeight: isAnswered ? FontWeight.w700 : FontWeight.w600,
                      color: isAnswered ? AppConfig.primaryColor : AppConfig.textColor,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: List.generate(5, (index) {
                final starValue = index + 1;
                final isSelected = starValue <= (currentVal ?? 0);
                return GestureDetector(
                  onTap: () => onSelected(starValue),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    curve: Curves.easeOutBack,
                    transform: Matrix4.identity()..scale(isSelected ? 1.1 : 1.0),
                    child: Icon(
                      isSelected ? Icons.star_rounded : Icons.star_outline_rounded,
                      color: isSelected ? Colors.amber : Colors.grey.shade300,
                      size: 42,
                    ),
                  ),
                );
              }),
            ),
          ],
        ),
      ),
    );
  }

  void _proceedToSuggestion() {
    if (!_isAllRated) return;

    final ratingsJson = jsonEncode({
      'overall': _ratingOverall,
      'agenda': _ratingAgenda,
      'food': _ratingFood,
      'venue': _ratingVenue,
      'future': _ratingFuture,
    });

    context.push('/feedback-options', extra: {
      'event_id': widget.eventId,
      'ratings': ratingsJson,
    });
  }

  Future<void> _submitRatingsOnly() async {
    if (!_isAllRated) return;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final user = authProvider.user;

    if (user == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('You must be logged in to submit feedback.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final ratingsJson = jsonEncode({
        'overall': _ratingOverall,
        'agenda': _ratingAgenda,
        'food': _ratingFood,
        'venue': _ratingVenue,
        'future': _ratingFuture,
      });

      FormData formData = FormData.fromMap({
        'user_id': user.id,
        'detailed_ratings': ratingsJson,
        if (widget.eventId != null) 'event_id': widget.eventId,
      });

      final response = await ApiService.post('/feedback/submit.php', data: formData, includeAuth: false);

      if (response.statusCode == 200 && response.data['success'] == true) {
        authProvider.markFeedbackSubmitted();
        
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Thank you! Your ratings have been submitted.'), backgroundColor: Colors.green),
          );
          context.go('/home');
        }
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text(response.data['message'] ?? 'Failed to submit ratings.'), backgroundColor: Colors.red),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('An error occurred. Please try again.'), backgroundColor: Colors.red),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppConfig.bgLight,
      appBar: AppBar(
        title: const Text('Event Feedback', style: TextStyle(fontWeight: FontWeight.bold)),
        backgroundColor: AppConfig.primaryColor,
        foregroundColor: Colors.white,
        centerTitle: true,
        elevation: 0,
      ),
      body: Column(
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
            decoration: const BoxDecoration(
              color: AppConfig.primaryColor,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              children: [
                const Icon(Icons.stars_rounded, color: Colors.amber, size: 48),
                const SizedBox(height: 12),
                Text(
                  'We value your feedback!',
                  style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white.withOpacity(0.9)),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 8),
                Text(
                  'Please rate your experience on a scale of 1 to 5 stars.',
                  style: TextStyle(fontSize: 14, color: Colors.white.withOpacity(0.8)),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: ListView(
              padding: const EdgeInsets.only(bottom: 24),
              children: [
                _buildRatingQuestion(
                  'How would you rate the event flow and stage activities?',
                  _ratingAgenda,
                  (val) => setState(() => _ratingAgenda = val),
                ),
                _buildRatingQuestion(
                  'How satisfied were you with the food and refreshments provided?',
                  _ratingFood,
                  (val) => setState(() => _ratingFood = val),
                ),
                _buildRatingQuestion(
                  'How satisfied were you with the venue and hospitality?',
                  _ratingVenue,
                  (val) => setState(() => _ratingVenue = val),
                ),
                _buildRatingQuestion(
                  'How likely are you to attend future alumni meets and recommend the event to fellow graduates?',
                  _ratingFuture,
                  (val) => setState(() => _ratingFuture = val),
                ),
                _buildRatingQuestion(
                  'How would you rate your overall experience of today’s event?',
                  _ratingOverall,
                  (val) => setState(() => _ratingOverall = val),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(16),
            width: double.infinity,
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -5),
                )
              ],
            ),
            child: Column(
              children: [
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: (_isAllRated && !_isSubmitting) ? _proceedToSuggestion : null,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppConfig.primaryColor,
                      side: const BorderSide(color: AppConfig.primaryColor, width: 2),
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text(
                      'Add a Suggestion (Optional)',
                      style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: (_isAllRated && !_isSubmitting) ? _submitRatingsOnly : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppConfig.primaryColor,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isSubmitting 
                      ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text(
                        'Submit Ratings',
                        style: TextStyle(fontSize: 18, color: Colors.white, fontWeight: FontWeight.bold),
                      ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
