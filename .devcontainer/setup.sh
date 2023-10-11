composer install 
cp system/data/db_config.php.example system/data/db_config.php
php sura -migrate
php sura -make:add-user Ivan Ivanov ivanov@example.ru example
