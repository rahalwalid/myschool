USE myschool;
CREATE TABLE IF NOT EXISTS views(user_id INT NOT NULL,item_type ENUM('lesson','exam','correction','video') NOT NULL,item_id INT NOT NULL,hits INT DEFAULT 1,first_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,last_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(user_id,item_type,item_id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE);
