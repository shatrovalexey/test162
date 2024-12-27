LOAD DATA
    INFILE 'data/users.csv'
REPLACE INTO TABLE
    `users`
FIELDS
    TERMINATED BY ','
    ENCLOSED BY '"'
LINES
    TERMINATED BY '\r\n'
(`id`, `fullname`);