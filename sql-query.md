ALTER TABLE staff 
CHANGE first_name name VARCHAR(300);

ALTER TABLE staff DROP COLUMN last_name;

ALTER TABLE users DROP FOREIGN KEY users_ibfk_1;