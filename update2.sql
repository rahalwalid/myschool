USE myschool;
ALTER TABLE users MODIFY role ENUM('student','teacher','admin') DEFAULT 'student', ADD COLUMN subject VARCHAR(100) NULL;
ALTER TABLE lessons ADD COLUMN teacher_id INT NULL;
ALTER TABLE exams ADD COLUMN teacher_id INT NULL;
ALTER TABLE videos ADD COLUMN teacher_id INT NULL;
CREATE TABLE IF NOT EXISTS messages(id INT AUTO_INCREMENT PRIMARY KEY,sender_id INT NOT NULL,receiver_id INT NOT NULL,body VARCHAR(1000) NOT NULL,is_read TINYINT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(receiver_id,is_read),INDEX(sender_id,receiver_id),FOREIGN KEY(sender_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(receiver_id) REFERENCES users(id) ON DELETE CASCADE);
