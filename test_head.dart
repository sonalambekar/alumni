import 'package:http/http.dart' as http;

void main() async {
  try {
    var res = await http.get(Uri.parse('https://leap.gmu.ac.in/alumni/api/get_students.php'));
    print(res.body);
  } catch (e) {}
}
