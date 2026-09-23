import 'package:flutter/material.dart';

class AppConfig {
  // API Configuration
  static const String baseUrl = 'http://your-domain.com/alumni';
  static const String apiUrl = '$baseUrl/api';
  
  // Colors
  static const Color primaryColor = Color(0xFF5B1F1F);
  static const Color secondaryColor = Color(0xFFECC35C);
  static const Color bgLight = Color(0xFFF5F7FB);
  static const Color textColor = Color(0xFF333333);
  static const Color textLight = Color(0xFF666666);
  
  // API Endpoints
  static const String loginEndpoint = '$apiUrl/auth/login.php';
  static const String registerEndpoint = '$apiUrl/auth/register.php';
  static const String noticeboardEndpoint = '$apiUrl/noticeboard/list.php';
  static const String newsEndpoint = '$apiUrl/news/list.php';
  static const String eventsEndpoint = '$apiUrl/events/list.php';
  static const String jobsEndpoint = '$apiUrl/jobs/list.php';
  static const String galleriesEndpoint = '$apiUrl/galleries/list.php';
  static const String coreTeamEndpoint = '$apiUrl/core-team/list.php';
  static const String proudAlumniEndpoint = '$apiUrl/proud-alumni/list.php';
  static const String directoryEndpoint = '$apiUrl/alumni/directory.php';
  static const String profileEndpoint = '$apiUrl/profile/me.php';
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userIdKey = 'user_id';
  static const String userDataKey = 'user_data';
}
