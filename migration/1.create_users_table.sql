DELIMITER $$

-- создать таблицу `users`

DROP TABLE IF EXISTS `users`
$$

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT null COMMENT 'ID'
    , `fullname` VARCHAR( 100 ) NOT null COMMENT 'имя'

    , PRIMARY KEY(`id`)
) COMMENT = 'пользователи'
$$

DELIMITER ;