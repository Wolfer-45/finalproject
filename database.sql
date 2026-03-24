CREATE DATABASE IF NOT EXISTS wanderwise_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wanderwise_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password_hash VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT 'default-avatar.png',
  age INT,
  gender ENUM('male','female','other','prefer_not'),
  language VARCHAR(10) DEFAULT 'en',
  travel_style VARCHAR(50),
  travel_types VARCHAR(255),
  emergency_name VARCHAR(100),
  emergency_phone VARCHAR(20),
  emergency_rel VARCHAR(50),
  is_verified TINYINT(1) DEFAULT 0,
  otp_code VARCHAR(255),
  otp_expires DATETIME,
  login_attempts INT DEFAULT 0,
  locked_until DATETIME,
  notify_json TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE trips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  destination VARCHAR(200) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  duration INT NOT NULL,
  travel_type VARCHAR(100),
  travel_style VARCHAR(50),
  interests VARCHAR(255),
  budget DECIMAL(10,2),
  status ENUM('planned','active','completed') DEFAULT 'planned',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE itineraries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trip_id INT NOT NULL UNIQUE,
  user_id INT NOT NULL,
  content LONGTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE buddy_listings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  destination VARCHAR(200) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  group_size ENUM('duo','trio','group') DEFAULT 'duo',
  gender_pref ENUM('male','female','mixed','any') DEFAULT 'any',
  travel_style VARCHAR(100),
  interests VARCHAR(255),
  about_me TEXT,
  contact_email VARCHAR(150),
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE buddy_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  sender_id INT NOT NULL,
  message TEXT,
  status ENUM('pending','accepted','rejected') DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES buddy_listings(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE storybook (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trip_id INT,
  day_number INT DEFAULT 1,
  title VARCHAR(200) NOT NULL,
  content TEXT NOT NULL,
  location VARCHAR(200),
  mood ENUM('amazing','good','okay','tough') DEFAULT 'good',
  photo_path VARCHAR(500),
  travel_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE SET NULL
);

CREATE TABLE budgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trip_id INT NOT NULL UNIQUE,
  total_budget DECIMAL(10,2) NOT NULL,
  est_transport DECIMAL(10,2) DEFAULT 0,
  est_hotel DECIMAL(10,2) DEFAULT 0,
  est_food DECIMAL(10,2) DEFAULT 0,
  est_activities DECIMAL(10,2) DEFAULT 0,
  est_misc DECIMAL(10,2) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trip_id INT NOT NULL,
  category ENUM('transport','hotel','food','activities','misc') NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  note VARCHAR(300),
  expense_date DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
);

CREATE TABLE stories_cache (
  id INT AUTO_INCREMENT PRIMARY KEY,
  destination VARCHAR(200) NOT NULL UNIQUE,
  story_text LONGTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
