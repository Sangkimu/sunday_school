-- AGC Bomet Area Sunday School Database Schema
-- MySQL-compatible SQL

CREATE DATABASE IF NOT EXISTS sunday_school;
USE sunday_school;

CREATE TABLE IF NOT EXISTS classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(255) NOT NULL,
    level VARCHAR(100) NOT NULL,
    teacher_name VARCHAR(255),
    capacity INT DEFAULT 30,
    schedule VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS children (
    child_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    gender VARCHAR(20),
    age INT,
    class_id INT,
    guardian_name VARCHAR(255),
    phone VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Active',
    date_registered DATE NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    role VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance_records (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    remarks TEXT,
    FOREIGN KEY (child_id) REFERENCES children(child_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bible_stories (
    story_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    theme VARCHAR(255),
    lesson_date DATE,
    summary TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS memory_verses (
    verse_id INT AUTO_INCREMENT PRIMARY KEY,
    verse_text TEXT NOT NULL,
    reference VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    date_added DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS songs (
    song_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    language VARCHAR(50) DEFAULT 'English',
    has_audio TINYINT(1) DEFAULT 1,
    has_video TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(50) NOT NULL UNIQUE,
    local_church VARCHAR(255) NOT NULL,
    district_church VARCHAR(255) NOT NULL,
    area_church VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'User',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS calendar_events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_date DATE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    announcement_date DATE NOT NULL,
    audience VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS award_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS awards (
    award_id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    category_id INT NOT NULL,
    award_date DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Presented',
    FOREIGN KEY (child_id) REFERENCES children(child_id),
    FOREIGN KEY (category_id) REFERENCES award_categories(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS certificates (
    certificate_id INT AUTO_INCREMENT PRIMARY KEY,
    award_id INT NOT NULL,
    child_name VARCHAR(255) NOT NULL,
    award_title VARCHAR(255) NOT NULL,
    award_date DATE NOT NULL,
    teacher_signature VARCHAR(255),
    church_stamp VARCHAR(255) DEFAULT 'AGC Bomet Area',
    status VARCHAR(50) DEFAULT 'Printed',
    FOREIGN KEY (award_id) REFERENCES awards(award_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Data
INSERT INTO classes (class_name, level, teacher_name, capacity, schedule) VALUES
('Primary 1', 'Early Years', 'Mrs. Ruth Kipchoge', 25, 'Sunday 9:00 AM'),
('Primary 2', 'Lower Primary', 'Mr. Daniel Kibet', 30, 'Sunday 9:00 AM'),
('Primary 3', 'Lower Primary', 'Mrs. Mary Chepkwony', 28, 'Sunday 10:00 AM'),
('Primary 4', 'Upper Primary', 'Mr. Samuel Kiprop', 30, 'Sunday 10:00 AM'),
('Primary 5', 'Upper Primary', 'Mrs. Grace Langat', 27, 'Sunday 11:00 AM'),
('Primary 6', 'Upper Primary', 'Mr. David Kipkemei', 26, 'Sunday 11:00 AM');

INSERT INTO children (full_name, gender, age, class_id, guardian_name, phone, status, date_registered) VALUES
('Brian Kiptoo', 'Male', 10, 3, 'Mr. Kiptoo', '0712345678', 'Active', '2024-01-15'),
('Grace Mwangi', 'Female', 9, 2, 'Mrs. Mwangi', '0723456789', 'Active', '2024-02-10'),
('David Kipkemei', 'Male', 11, 4, 'Mr. Kipkemei', '0734567890', 'Active', '2024-01-20'),
('Sarah Rotich', 'Female', 8, 2, 'Mrs. Rotich', '0745678901', 'Active', '2024-03-05'),
('Emily Chepkwony', 'Female', 12, 5, 'Mr. Chepkwony', '0756789012', 'Active', '2024-04-01'),
('Moses Kiprop', 'Male', 10, 3, 'Mrs. Kiprop', '0767890123', 'Active', '2024-02-22');

INSERT INTO teachers (full_name, role, phone, email, status) VALUES
('Mrs. Ruth Kipchoge', 'Class Teacher', '0711111111', 'ruth@agcbomet.org', 'Active'),
('Mr. Daniel Kibet', 'Class Teacher', '0722222222', 'daniel@agcbomet.org', 'Active'),
('Mrs. Mary Chepkwony', 'Music Teacher', '0733333333', 'mary@agcbomet.org', 'Active'),
('Mr. Samuel Kiprop', 'Sunday School Coordinator', '0744444444', 'samuel@agcbomet.org', 'Active');

INSERT INTO attendance_records (child_id, attendance_date, status, remarks) VALUES
(1, '2026-07-01', 'Present', 'Arrived on time'),
(2, '2026-07-01', 'Present', 'Participated in Bible Quiz prep'),
(3, '2026-07-01', 'Absent', 'Sick'),
(4, '2026-07-01', 'Present', 'Excellent participation'),
(5, '2026-07-01', 'Present', 'Present for memory verse'),
(6, '2026-07-01', 'Present', 'Joined choir practice');

INSERT INTO bible_stories (title, theme, lesson_date, summary) VALUES
('David and Goliath', 'Courage and Faith', '2026-07-01', 'David defeated Goliath through faith in God.'),
('Noah and the Ark', 'Obedience and Salvation', '2026-07-08', 'Noah obeyed God and built the ark.'),
('Joseph and His Dreams', 'Purpose and Integrity', '2026-07-15', 'Joseph remained faithful despite trials.');

INSERT INTO memory_verses (verse_text, reference, category, date_added) VALUES
('Children obey your parents in the Lord, for this is right.', 'Ephesians 6:1', 'Daily', '2026-07-01'),
('Trust in the Lord with all your heart and lean not on your own understanding.', 'Proverbs 3:5', 'Weekly', '2026-07-01'),
('I can do all things through Christ who strengthens me.', 'Philippians 4:13', 'Weekly', '2026-07-02');

INSERT INTO songs (title, category, language, has_audio, has_video) VALUES
('Jesus Loves Me', 'Hymn', 'English', 1, 1),
('I am a Child of God', 'Hymn', 'English', 1, 1),
('Kiala Jeso', 'Kalenjin Hymn', 'Kalenjin', 1, 0),
('Nyo Inye Kaberurindet', 'Kalenjin Hymn', 'Kalenjin', 1, 0),
('Clap Your Hands', 'Action Song', 'English', 1, 0);

INSERT INTO calendar_events (event_date, title, description) VALUES
('2026-07-05', 'Bible Quiz', 'Bible Quiz competition for children.'),
('2026-07-12', 'Children''s Choir', 'Choir performance practice and presentation.'),
('2026-07-19', 'Parents Meeting', 'Parents and teachers meet to review progress.'),
('2026-07-26', 'Sports Day', 'Games and fun activities for children.'),
('2026-08-02', 'Talent Sunday', 'Children showcase talents and gifts.'),
('2026-08-16', 'Bible Memory Competition', 'Memory verse competition for all children.'),
('2026-08-23', 'Graduation', 'Graduation and recognition service.');

INSERT INTO announcements (title, message, announcement_date, audience) VALUES
('Sunday School Resumes', 'Sunday School activities resume at 9:00 AM every Sunday. Please arrive early.', '2026-07-01', 'All'),
('Bible Quiz Preparation', 'Children are encouraged to prepare for the upcoming Bible Quiz on 5 July.', '2026-07-02', 'All'),
('Teachers Meeting', 'All teachers are requested to report by 8:00 AM before Sunday School starts.', '2026-07-02', 'Teachers Only');

INSERT INTO award_categories (category_name, description, status) VALUES
('Bible Knowledge', 'Excellence in memorizing and understanding Scripture', 'Active'),
('Attendance', 'Perfect or near-perfect attendance record', 'Active'),
('Leadership', 'Demonstrated leadership and peer mentoring', 'Active'),
('Service', 'Outstanding community and church service', 'Active'),
('Choir Excellence', 'Outstanding performance in choir and music', 'Active'),
('Overall Excellence', 'Exceptional performance across all areas', 'Active');

INSERT INTO awards (child_id, category_id, award_date, status) VALUES
(1, 1, '2026-07-10', 'Presented'),
(2, 2, '2026-06-28', 'Presented'),
(3, 3, '2026-06-25', 'Presented'),
(4, 5, '2026-06-22', 'Presented'),
(6, 4, '2026-06-20', 'Presented'),
(5, 6, '2026-06-15', 'Presented');

INSERT INTO certificates (award_id, child_name, award_title, award_date, teacher_signature, church_stamp, status) VALUES
(1, 'Brian Kiptoo', 'Bible Quiz Competition', '2026-07-10', 'Mrs. Mary Kipchoge', 'AGC Bomet Area', 'Printed'),
(2, 'Grace Mwangi', 'Perfect Attendance', '2026-06-28', 'Mr. David Kipkemei', 'AGC Bomet Area', 'Printed'),
(3, 'David Kipkemei', 'Leadership Excellence', '2026-06-25', 'Mrs. Mary Kipchoge', 'AGC Bomet Area', 'Printed');
