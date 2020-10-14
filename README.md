# Cybersport API

Все ответы возвращаются в JSON с такими общими полями, как: message, status. При успешном выполнении status = "success", при неуспешном status = "error". В поле message можно получить подробное сообщение об ошибке и об успешном выполнении запроса.

В некоторых запросах необходимо передавать токен аутентификации в хедере (Authorization: Bearer {token}) - такие запросы будут помечены ключевым словом **AUTH**.

## Пользователи

**Вход** - POST https://domen.com/api/login (Аргументы: email, password) 
- Возвращает {message, status, token, user_data}
- token - токен доступа для пользователя. Лучше сохранить его в local storage или в redux.
- user_data - объект с данными пользователя (id, email, team_id, coins, coins_bonus, tickets, referal_code, coins_from_referals, confirmed_email, email_confirmation_code, banned).

**Регистрация** - POST https://domen.com/api/login (Аргументы: email, password, password_confirm)
- Возвращает {message, status, token, user_data}

**Редактировать информацию пользователя** - POST https://domen.com/api/changeUserInfoByAdmin **AUTH ADMIN** (Аргументы: user_info)
- Возвращает {message, status}
- user_info - ассоциативный массив с данными пользователя

**Записаться на турнир** - POST https://domen.com/api/joinTournament **AUTH** (Аргументы: user_id, tournament_id, game_id)
- Возвращает {message, status}
- user_id - id пользователя
- tournament_id - id турнира
- game_id - id игры

**Отменить запись на турнир** - POST https://domen.com/api/cancelTournamentParticipation **AUTH** (Аргументы: user_id, tournament_id, game_id)
- Возвращает {message, status}
- user_id - id пользователя
- tournament_id - id турнира
- game_id - id игры

**Добавить информацию для игры** - POST https://domen.com/api/addGameInfo **AUTH** (Аргументы: user_id, game_id, game_info)
- Возвращает {message, status}
- user_id - id пользователя
- game_id - id игры
- game_info - массив с данными для игры
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

**Добавить новый турнир вручную будучи администратором** - POST https://domen.com/api/createTounamentByAdmin **AUTH ADMIN** (Аргументы: new_tournament, options, user_id)
- Возвращает {message, status}
- new_tournament - массив, содержащий title, game_id, tickets, img, start_time, region
- options - массив, содержащий дополнительную информацию о турнире, основываясь на игре (Например для pubg: map, mode, pov, max_players, winners, placement_award, kill_award, mvp_award, lobby_pass)

**Добавить настройки для автотурнира** POST https://domen.com/api/saveAutoTournOptions **AUTH ADMIN** (Аргументы: game_id, options)
- Возвращает {message, status}
- game_id - id игры, для которой применяются настройки
- options - многомерный массив. 
    Структура для PUBG: 
    [
        tournament_options => [mode, tickets, kill_award, mvp_award, max_players, placement_award, winners], 
        schedule_options => [day_of_week(Цифра от 0 до 6, где 0 это воскресенье), time(формат 24ч), region] 
    ]
