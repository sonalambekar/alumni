class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? batch;
  final String? department;
  final String? profilePicture;
  final String? location;
  final String? company;
  final String? designation;

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.batch,
    this.department,
    this.profilePicture,
    this.location,
    this.company,
    this.designation,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      batch: json['batch'],
      department: json['department'],
      profilePicture: json['profile_picture'],
      location: json['location'],
      company: json['company'],
      designation: json['designation'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'batch': batch,
      'department': department,
      'profile_picture': profilePicture,
      'location': location,
      'company': company,
      'designation': designation,
    };
  }
}
