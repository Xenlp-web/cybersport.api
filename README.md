# Cybersport API

Все ответы возвращаются в JSON с такими общими полями, как: message, status. При успешном выполнении status = "success", при неуспешном status = "error". В поле message можно получить подробное сообщение об ошибке и об успешном выполнении запроса.

В некоторых запросах необходимо передавать токен аутентификации в хедере (Authorization: Bearer {token}) - такие запросы будут помечены ключевым словом **AUTH**.

## Пользователи

**Вход** - POST https://domen.com/api/login (Аргументы: email, password) 
- Возвращает {message, status, token, user_data}
- token - токен доступа для пользователя. Лучше сохранить его в local storage или в redux.
- user_data - объект с данными пользователя (id, email, team_id, coins, coins_bonus, tickets, referal_code, coins_from_referals, confirmed_email, email_confirmation_code, banned).

**Регистрация** - POST https://domen.com/api/register (Аргументы: nickname, email, password, password_confirm, region_id)
- Возвращает {message, status, token, user_data}

**Получить информацию о текущем пользователе** - GET https://domen.com/api/get-current-user-info **AUTH** (Аргументов нет)
- Возвращает {message, status, user}
- user - объект с данными пользователя

**Получить информацию о пользователе по id или нику** - GET https://domen.com/api/get-user-info () (Аргументы: query)
- Возвращает {message, status, user}
- user - объект с данными пользователя
- query - ник либо id

**Редактировать информацию пользователя администратором** - POST https://domen.com/api/change-user-info-by-admin **AUTH ADMIN** (Аргументы: user_id, user_info)
- Возвращает {message, status}
- user_id - id пользователя
- user_info - объект с данными пользователя. Пример {nickname: 'newNickname', email: 'newEmail'}

**Редактировать информацию пользователя** - POST https://domen.com/api/change-user-info **AUTH** (Аргументы: user_info)
- Возвращает {message, status}
- user_info - объект с данными пользователя. Пример {nickname: 'newNickname', email: 'newEmail'}

**Записаться на турнир** - POST https://domen.com/api/join-tournament **AUTH** (Аргументы: tournament_id, game_id)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры

**Отменить запись на турнир** - POST https://domen.com/api/cancel-tournament-participation **AUTH** (Аргументы: tournament_id, game_id)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры

**Добавить информацию для игры** - POST https://domen.com/api/add-game-info **AUTH** (Аргументы: game_id, game_info)
- Возвращает {message, status}
- game_id - id игры
- game_info - массив с данными для игры

**Отправить новый код подтверждения** - POST https://domen.com/api/send-new-email-confirmation-code **AUTH** (Аргументов нет)
- Возвращает {message, status}

**Подтвердить email** - POST https://domen.com/api/confirm-email **AUTH** (Аргументы: confirmation_code)
- Возвращает {message, status}
- confirmation_code - код подтверждения введенный пользователем

**Загрузить аватарку** - POST https://domen.com/api/upload-avatar **AUTH** (Аргументы: file)
- Возвращает {message, status}
- file - изображение

**Получить рейтинг для игры** - GET https://domen.com/api/get-rating (Аргументы: game_id, user_id)
- Возвращает {message, status, rating}

**Активировать реферальный код** - POST https://domen.com/api/use-referal-code **AUTH** (Аргументы: referal_code)
- Возвращает {message, status}
____

## Игры
**Получить список игр** - GET https://domen.com/api/get-all-games (Нет аргументов)
- Возвращает {message, status, games}.
- games - массив с объектами игр (id, name, image, active).
____

## Цены
**Получить цену на билеты** - GET https://domen.com/api/get-ticket-price (Необязательный аргумент **count**. Если указан - выдаст цену на **count** шт. билетов)
- Возвращает {message, status, price}.
- price - цена
____

## Чат
**Получить последние 100 сообщений из глобального чата** - GET https://domen.com/api/get-global-chat-messages (Нет аргументов)
- Возвращает {message, status, **messages**}
- messages - массив с сообщениями в чате

**Отправить сообщение в глобальный чат** - POST https://domen.com/api/send-message-to-global-chat **AUTH** (Аргументы: message)
- Возвращает {message, status}
____

## Турниры
**Получить турниры для определенной игры** - GET https://domen.com/api/get-tournaments-by-game (Аргументы: game_id)
- Возвращает {message, status, tournaments}
- tournaments - массив турниров, содержащий в себе еще 3 массива: tournamentsToday, tournamentsTommorrow, tournamentsEnded в которых есть поле participation (показывает участвует ли пользователь в турнире или нет)

**Добавить новый турнир вручную будучи администратором** - POST https://domen.com/api/create-tounament-by-admin **AUTH ADMIN** (Аргументы: new_tournament, options, game_id)
- Возвращает {message, status}
- new_tournament - объект, содержащий title, game_id, tickets, img, start_time, region
- options - объект, содержащий дополнительную информацию о турнире, основываясь на игре (Например для pubg: map, mode, pov, max_players, winners, placement_award, kill_award, mvp_award, lobby_pass)

**Добавить настройки для автотурнира** POST https://domen.com/api/save-auto-tourn-options **AUTH ADMIN** (Аргументы: game_id, options)
- Возвращает {message, status}
- game_id - id игры, для которой применяются настройки
- options - многомерный массив. 
    Структура для PUBG: 
    [
        tournament_options => [mode, tickets, kill_award, mvp_award, max_players, placement_award, winners], 
        schedule_options => [day_of_week(Цифра от 0 до 6, где 0 это воскресенье), time(формат 24ч), region] 
    ]

**Редактировать турнир** POST https://domen.com/api/edit-tournament-info **AUTH ADMIN** (Аргументы: tournament_id, game_id, tournament_common_info, tournament_info_by_game)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры
- tournament_common_info - массив с общей информацией для турниров (пример: title, game_id, tickets, img, start_time, ended, important, stream, region, lobby_id, lobby_pass)
- tournament_info_by_game - массив с информацией для турнира по конкретной игре (пример с pubg: tournament_id, map, mode, pov, current_players, max_players, winners, placement_award, kill_award, mvp_award)

**Получить информацию для входа в лобби** - POST https://domen.com/api/get-lobby-info **AUTH** (Аргументы: tournament_id)
- Возвращает {message, lobby_info, status} lobby_info - массив с данными для входа
- tournament_id - id турнира

**Добавить результаты турнира** - POST https://domen.com/api/save-result **AUTH ADMIN** (Аргументы: tournament_id, tournament_results)
- Возвращает {message, status}
- tournament_id - id турнира
- tournament_results - массив, содержащий массивы с результатами игроков. Пример для PUBG: [user_id, placement (занятое место), mvp(является ли MVP, может быть 1 или 0), kills, deaths].

**Получить список всех стримов** - GET https://domen.com/api/get-all-streams (Аргументов нет)
- Возвращает {message, streams, status} lobby_info - массив с данными для входа
- streams - массив объектов, содержит title (название турнира), stream (ссылка на стрим)

**Получить список турниров в админке** - GET https://domen.com/api/get-tournaments-for-admin **AUTH ADMIN** (Аргументы (все не обязательные): tournament_id, tournament_title, start_date, start_time)
- Возвращает {message, status, tournaments}

**Получить данные для турнира в админке** - GET https://domen.com/api/get-tournaments-option-for-admin **AUTH ADMIN** (Аргументы: tournament_id)
- Возвращает {message, status, tournamentOption}

**Получить списко участников турнира** - GET https://domen.com/api/get-participants (Аргументы: tournament_id)
- Возвращает {message, status, users}

**Удалить турнир** - DELETE https://domen.com/api/remove-tournament **AUTH ADMIN** (Аргументы: tournament_id, game_id)
- Возвращает {message, status}

**Получить информацию о турнире** - GET https://domen.com/api/get-tournament-info (Аргументы: tournament_id, game_id)
- Взвращает {message, status, tournament}
____

## Статистика
**Получить статистику игроков** - GET https://domen.com/api/get-statistic-for-players (Аргументы: game_id, stat_item, period = 'all')
- Возвращает {message, status, statistic}
- game_id - id игры
- stat_item - критерий выбора статистики (для pubg можно выбрать: earnings, kills, placements, tournaments)
- period (необязательный параметр) - определяет за какой период выбирать статистику (возможные варианты: day, month, week)
- statistic содержит: user_id, nickname, stat_item

____

## Регионы
**Получить список всех регионов** - GET https://domen.com/api/get-all-regions (Аргументов нет)
- Возвращает {message, status, regions}
- regions - массив объектов регионов. Пример объекта: {"id": 1, "region": "ru"}
