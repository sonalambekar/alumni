import 'package:http/http.dart' as http;

void main() async {
  final url = Uri.parse('https://leap.gmu.ac.in/alumni/api/director/get_feedback.php?user_id=4');
  try {
    var response = await http.get(url);
    print(response.body);
  } catch (e) {
    print(e);
  }
}
