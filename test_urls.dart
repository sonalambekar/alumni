import 'package:http/http.dart' as http;

void main() async {
  final url1 = 'https://leap.gmu.ac.in/alumni/uploads/feedback_videos/feedback_3_1790617085.mp4';
  
  try {
    var res1 = await http.head(Uri.parse(url1));
    print('URL1 ($url1): ${res1.statusCode}');
  } catch (e) {
    print('URL1 error: $e');
  }
}
