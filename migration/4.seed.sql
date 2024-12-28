-- загрузка файла в таблицу

SET @`file_upload` := 'users.csv';
SET @`path_upload` := (SELECT concat_ws('/', @@secure_file_priv, @`file_upload`) AS `path_upload`);

LOAD DATA
    INFILE @`path_upload`
REPLACE INTO TABLE
    `users`
FIELDS
    TERMINATED BY ','
    ENCLOSED BY '"'
LINES
    TERMINATED BY '\r\n'
(`id`, `fullname`);