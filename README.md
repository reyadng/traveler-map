Телеграм-бот, которому можно отправить свою локацию, он опеределит, в каком городе вы находитесь и отобразит в общем списке с пагинацией.
Запускается на AWS Lambda с очередями SQS для запуска ларавеловских джоб. Для деплоя используется Serverless. В качестве БД амазоновская же DynamoDb. Для преобразования координат в населенный пункт geocoder, работающий поверх яндексовского геокодера. Для работы с телеграмом BotMan.
Обернут в докер-контейнер, но чтобы запустить локально нужны некоторые пляски с бубном, чтобы телеграм-бот мог подключится к компьютеру и чтобы создать таблицы в DynamoDb, ибо для него нет ларавеловских миграций. Не буду сейчас это тут описывать. Потыкаться можно здесь https://t.me/traveler_map_bot_staging_bot (координаты можно отправить любые).

Точка входа: app/Http/Controllers/WhController.php


Выглядит это примерно так
![image](https://github.com/user-attachments/assets/23427857-aef4-4972-af2b-3da4dad53185)

