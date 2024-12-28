DELIMITER $$

-- таблица `messages`

DROP TABLE IF EXISTS `messages`
$$

CREATE TABLE IF NOT EXISTS `messages`(
    `id` BIGINT UNSIGNED NOT null AUTO_INCREMENT COMMENT 'ID'
    , PRIMARY KEY(`id`)
) COMMENT 'сообщение' AS
SELECT
    uuid() AS `title`
    , sha1(uuid()) AS `body`;
$$

-- фиктивный пользователь чтобы упростить поиск неотправленных сообщений

INSERT INTO
    `users`
SET
    `id` := 0
    , `fullname` := ''
$$

-- чтобы при создании сообщения отмечалась его отправка фиктивному пользователю

DROP TRIGGER IF EXISTS `messages_AFTER_INSERT`
$$

CREATE TRIGGER `messages_AFTER_INSERT` AFTER INSERT ON `messages` FOR EACH ROW
    INSERT INTO
        `users_messages`
    SET
        `id_users` := (SELECT min(`u1`.`id`) FROM `users` AS `u1`)
        , `id_messages` := new.`id`
        , `created_at` := current_timestamp();
$$

DELIMITER ;