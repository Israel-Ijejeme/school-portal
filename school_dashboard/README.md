# School Dashboard System

A PHP and MySQL-based school dashboard for the Nigerian education system with support for students, teachers, and administrators.

## Features

- **Three account types**: Student, Teacher, and Admin
- **Student features**: View grades, CGPA calculation, course registration, profile management
- **Teacher features**: Manage assigned courses, grade students, view students in their courses
- **Admin features**: Manage users, courses, departments, and semesters
- **Nigerian education system**: 5.0 max CGPA, semester-based

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone or download this repository to your web server's document root.
2. Create a MySQL database named `school_dashboard`.
3. Import the database schema and dummy data by running the SQL script:
   ```
   php school_dashboard/config/init_db.php
   ```
4. Configure your web server to point to the `school_dashboard` directory.
5. Access the system through your web browser.

## Default Login Credentials

### Admin
- Email: admin@school.edu
- Password: Admin125$#.

### Teachers
- Email: john.doe@school.edu
- Password: Teacher125$#.

- Email: jane.smith@school.edu
- Password: teacher123

- Email: mike.johnson@school.edu
- Password: teacher123

### Students
- Email: emma.wilson@school.edu
- Password: Student125$#.

- Email: david.brown@school.edu
- Password: student123

- Email: sophia.lee@school.edu
- Password: student123

- Email: james.taylor@school.edu
- Password: student123

## Directory Structure

- `/admin`: Admin area files
- `/teacher`: Teacher area files
- `/student`: Student area files
- `/classes`: PHP class files (User, Course, Grade, etc.)
- `/config`: Configuration files
- `/includes`: Shared components (header, footer)
- `/assets`: CSS, JS, images

## Usage

1. Visit the homepage to select your user type (student, teacher, or admin).
2. Log in with your credentials.
3. Use the sidebar navigation to access different features based on your role.

## Nigerian Education System

- Maximum CGPA: 5.0
- Grade scale:
  - 70-100: A (5.0 points)
  - 60-69: B (4.0 points)
  - 50-59: C (3.0 points)
  - 45-49: D (2.0 points)
  - 40-44: E (1.0 points)
  - 0-39: F (0.0 points)
- CGPA classifications:
  - 4.5-5.0: First Class
  - 3.5-4.49: Second Class Upper
  - 2.5-3.49: Second Class Lower
  - 1.5-2.49: Third Class
  - 1.0-1.49: Pass
  - Below 1.0: Fail

## License

This project is open-source and available for educational purposes. 