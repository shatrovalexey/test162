### ЗАДАЧА
* `Задание.pdf`

### ИСПОЛЬЗОВАННОЕ ПО
* MySQL v8x
* PHP v7.4x

### НАСТРОЙКА
* Настроить подключение к СУБД и БД в файле `conf/db.php`
* Выполнить миграции `migration/[1-3].*.sql`
* Для посева данных можно выполнить миграцию `migration/4.seed.sql`
* или с помощью "POST" + "multipart/form-data" передать в "http://127.0.0.1:8000/upload.php"
файл вроде "data/users.csv" с именем поля HTML-формы "data"

### ЗАПУСК
* `php -S127.0.0.1:8000 -tweb`
* `php console/sender.php`
* Добавить в `crontab -e` директиву для запуска `console/send.php`

### АВТОР
Шатров Алексей Сергеевич <mail@ashatrov.ru>