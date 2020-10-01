# Cybersport API
## Пользователи

Все ответы возвращаются в JSON с такими общими полями, как: message, status. При успешном выполнении status = "success", при неуспешном status = "error". В поле message можно получить подробное сообщение об ошибке и об успешном выполнении запроса.

**Вход** - POST https://domen.com/api/login (Аргументы: email, password) 
- Возвращает {message, status, token, user_data}
- token - токен доступа для пользователя. Лучше сохранить его в local storage или в redux.
- user_data - объект с данными пользователя (id, email, team_id, coins, coins_bonus, tickets, referal_code, coins_from_referals, confirmed_email, email_confirmation_code, banned).

**Регистрация** - POST https://domen.com/api/login (Аргументы: email, password, password_confirm)
- Возвращает {message, status, token, user_data}
____

## Игры
**Получить список игр** - GET https://domen.com/api/getAllGames (Нет аргументов)
- Возвращает {message, status, games}.
- games - массив с объектами игр (id, name, image, active).
____

## Цены
**Получить цену на билеты** - GET https://domen.com/api/getTicketPrice (Необязательный аргумент **count**. Если указан - выдаст цену на **count** шт. билетов)
- Возвращает {message, status, price}.
- price - цена
____
