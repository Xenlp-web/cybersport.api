# Cybersport API

Все ответы возвращаются в JSON с такими общими полями, как: message, status. При успешном выполнении status = "success", при неуспешном status = "error". В поле message можно получить подробное сообщение об ошибке и об успешном выполнении запроса.

В некоторых запросах необходимо передавать токен аутентификации в хедере (Authorization: Bearer {token}) - такие запросы будут помечены ключевым словом **AUTH**

## Пользователи

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

## Чат
**Получить последние 100 сообщений из глобального чата** - GET https://domen.com/api/getGlobalChatMessages (Нет аргументов)
- Возвращает {message, status, **messages**}
- messages - массив с сообщениями в чате

**Отправить сообщение в глобальный чат** - POST https://domen.com/api/sendMessageToGlobalChat **AUTH** (Аргументы: message, user_name)
- Возвращает {message, status}
____

## Турниры
**Получить турниры для определенной игры** - GET https://domen.com/api/getTournamentsByGame (Аргументы: game_id)
- Возвращает {message, status, tournaments}
- tournaments - массив турниров

**Добавить новый турнир вручную будучи администратором** - POST https://domen.com/api/createTounamentByAdmin **AUTH** (Аргументы: new_tournament, options, user_id)
- Возвращает {message, status}
- new_tournament - массив, содержащий title, game_id, tickets, img, start_time, region
- options - массив, содержащий дополнительную информацию о турнире, основываясь на игре (Например для pubg: map, mode, pov, max_players, winners, placement_award, kill_award, mvp_award, lobby_pass)
