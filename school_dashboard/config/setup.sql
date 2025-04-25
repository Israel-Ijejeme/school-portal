-- Create database
CREATE DATABASE IF NOT EXISTS school_dashboard;
USE school_dashboard;

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create user_types table
CREATE TABLE IF NOT EXISTS user_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL
);

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type_id INT NOT NULL,
    department_id INT,
    age INT,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_type_id) REFERENCES user_types(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Create semester table
CREATE TABLE IF NOT EXISTS semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    is_current BOOLEAN DEFAULT FALSE
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    title VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    semester_id INT NOT NULL,
    credit_units INT NOT NULL,
    teacher_id INT,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Create student_courses table for course registration
CREATE TABLE IF NOT EXISTS student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id)
);

-- Create grades table
CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    semester_id INT NOT NULL,
    score DECIMAL(5,2) DEFAULT NULL,
    grade CHAR(2) DEFAULT NULL,
    grade_point DECIMAL(3,2) DEFAULT NULL,
    graded_by INT NOT NULL,
    graded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (semester_id) REFERENCES semesters(id),
    FOREIGN KEY (graded_by) REFERENCES users(id)
);

-- Insert user types
INSERT INTO user_types (type) VALUES ('admin'), ('teacher'), ('student');

-- Insert departments
INSERT INTO departments (name) VALUES 
('Computer Science'),
('Electrical Engineering'),
('Mathematics'),
('Physics'),
('Chemistry'),
('Biology');

-- Insert semesters
INSERT INTO semesters (name, is_current) VALUES 
('First Semester', TRUE),
('Second Semester', FALSE);

-- Insert admin user
-- Password: admin123 (hashed)
INSERT INTO users (full_name, email, password, user_type_id, department_id, age)
VALUES ('Admin User', 'admin@school.edu', '$2y$10$iLQ5d7j25Z0BnlJ5gxiMBe9vfYayMV.Jf3XE1cc0oc4QQcS7ASlIu', 1, 1, 35);

-- Insert teachers
-- Password: teacher123 (hashed)
INSERT INTO users (full_name, email, password, user_type_id, department_id, age)
VALUES 
('John Doe', 'john.doe@school.edu', '$2y$10$Rb3r1OIR6ClpWgBYiDfBDeXuDCdYz1SXzBdGu7YsCSL.zGQzMa6y6', 2, 1, 40),
('Jane Smith', 'jane.smith@school.edu', '$2y$10$Rb3r1OIR6ClpWgBYiDfBDeXuDCdYz1SXzBdGu7YsCSL.zGQzMa6y6', 2, 2, 38),
('Mike Johnson', 'mike.johnson@school.edu', '$2y$10$Rb3r1OIR6ClpWgBYiDfBDeXuDCdYz1SXzBdGu7YsCSL.zGQzMa6y6', 2, 3, 45);

-- Insert students
-- Password: student123 (hashed)
INSERT INTO users (full_name, email, password, user_type_id, department_id, age)
VALUES 
('Emma Wilson', 'emma.wilson@school.edu', '$2y$10$JsINRQBdgYBLh/Yc/Ul6h.q3P7u1UmRe1fQeZrByK0U1qXz0RpXHm', 3, 1, 22),
('David Brown', 'david.brown@school.edu', '$2y$10$JsINRQBdgYBLh/Yc/Ul6h.q3P7u1UmRe1fQeZrByK0U1qXz0RpXHm', 3, 1, 21),
('Sophia Lee', 'sophia.lee@school.edu', '$2y$10$JsINRQBdgYBLh/Yc/Ul6h.q3P7u1UmRe1fQeZrByK0U1qXz0RpXHm', 3, 2, 23),
('James Taylor', 'james.taylor@school.edu', '$2y$10$JsINRQBdgYBLh/Yc/Ul6h.q3P7u1UmRe1fQeZrByK0U1qXz0RpXHm', 3, 3, 20);

-- Insert courses
INSERT INTO courses (course_code, title, department_id, semester_id, credit_units, teacher_id)
VALUES 
('CSC101', 'Introduction to Computer Science', 1, 1, 3, 2),
('CSC202', 'Data Structures and Algorithms', 1, 2, 3, 2),
('ENG101', 'Basic Electrical Engineering', 2, 1, 3, 3),
('ENG202', 'Digital Electronics', 2, 2, 3, 3),
('MTH101', 'Calculus I', 3, 1, 3, 4),
('MTH202', 'Linear Algebra', 3, 2, 3, 4);

-- Register students for courses
INSERT INTO student_courses (student_id, course_id, semester_id)
VALUES 
(5, 1, 1), -- Emma for CSC101
(5, 3, 1), -- Emma for ENG101
(5, 5, 1), -- Emma for MTH101
(6, 1, 1), -- David for CSC101
(6, 5, 1), -- David for MTH101
(7, 3, 1), -- Sophia for ENG101
(7, 5, 1), -- Sophia for MTH101
(8, 5, 1); -- James for MTH101

-- Add some grades
INSERT INTO grades (student_id, course_id, semester_id, score, grade, grade_point, graded_by)
VALUES 
(5, 1, 1, 85.00, 'A', 5.00, 2), -- Emma, CSC101, graded by John
(5, 3, 1, 78.00, 'B', 4.00, 3), -- Emma, ENG101, graded by Jane
(5, 5, 1, 92.00, 'A', 5.00, 4), -- Emma, MTH101, graded by Mike
(6, 1, 1, 75.00, 'B', 4.00, 2), -- David, CSC101, graded by John
(6, 5, 1, 68.00, 'C', 3.00, 4), -- David, MTH101, graded by Mike
(7, 3, 1, 82.00, 'A', 5.00, 3), -- Sophia, ENG101, graded by Jane
(7, 5, 1, 77.00, 'B', 4.00, 4); -- Sophia, MTH101, graded by Mike 