import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';

void main() async {
  final url = Uri.parse('https://leap.gmu.ac.in/alumni/api/feedback/submit.php');
  var request = http.MultipartRequest('POST', url);
  request.fields['user_id'] = '3';
  request.fields['rating'] = '5';
  request.fields['feedback_text'] = 'test';
  
  // Create dummy audio file
  final file = File('test_audio.m4a');
  await file.writeAsString('fake audio data');
  
  request.files.add(await http.MultipartFile.fromPath('video', file.path));
  
  try {
    var response = await request.send();
    var responseData = await response.stream.bytesToString();
    print('Upload status: ${response.statusCode}');
    print('Response: $responseData');
  } catch (e) {
    print('Upload error: $e');
  }
}
