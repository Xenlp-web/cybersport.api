# Cybersport API

Все ответы возвращаются в JSON с такими общими полями, как: message, status. При успешном выполнении status = "success", при неуспешном status = "error". В поле message можно получить подробное сообщение об ошибке и об успешном выполнении запроса.

В некоторых запросах необходимо передавать токен аутентификации в хедере (Authorization: Bearer {token}) - такие запросы будут помечены ключевым словом **AUTH**.

## Пользователи

**Вход** - POST https://domen.com/api/login (Аргументы: email, password) 
- Возвращает {message, status, token, user_data}
- token - токен доступа для пользователя. Лучше сохранить его в local storage или в redux.
- user_data - объект с данными пользователя (id, email, team_id, coins, coins_bonus, tickets, referal_code, coins_from_referals, confirmed_email, email_confirmation_code, banned).

**Регистрация** - POST https://domen.com/api/register (Аргументы: email, password, password_confirm, region_id)
- Возвращает {message, status, token, user_data}

**Редактировать информацию пользователя администратором** - POST https://domen.com/api/changeUserInfoByAdmin **AUTH ADMIN** (Аргументы: user_info)
- Возвращает {message, status}
- user_info - ассоциативный массив с данными пользователя

**Записаться на турнир** - POST https://domen.com/api/joinTournament **AUTH** (Аргументы: tournament_id, game_id)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры

**Отменить запись на турнир** - POST https://domen.com/api/cancelTournamentParticipation **AUTH** (Аргументы: tournament_id, game_id)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры

**Добавить информацию для игры** - POST https://domen.com/api/addGameInfo **AUTH** (Аргументы: game_id, game_info)
- Возвращает {message, status}
- game_id - id игры
- game_info - массив с данными для игры

**Отправить новый код подтверждения** - POST https://domen.com/api/sendNewEmailConfirmationCode **AUTH** (Аргументов нет)
- Возвращает {message, status}

**Подтвердить email** - POST https://domen.com/api/confirmEmail **AUTH** (Аргументы: confirmation_code)
- Возвращает {message, status}
- confirmation_code - код подтверждения введенный пользователем

**Загрузить аватарку** - POST https://domen.com/api/uploadAvatar **AUTH** (Аргументы: file)
- Возвращает {message, status}
- file - изображение
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

**Добавить новый турнир вручную будучи администратором** - POST https://domen.com/api/createTounamentByAdmin **AUTH ADMIN** (Аргументы: new_tournament, options)
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

**Редактировать турнир** POST https://domen.com/api/editTournamentInfo **AUTH ADMIN** (Аргументы: tournament_id, game_id, tournament_common_info, tournament_info_by_game)
- Возвращает {message, status}
- tournament_id - id турнира
- game_id - id игры
- tournament_common_info - массив с общей информацией для турниров (пример: title, game_id, tickets, img, start_time, ended, important, stream, region, lobby_id, lobby_pass)
- tournament_info_by_game - массив с информацией для турнира по конкретной игре (пример с pubg: tournament_id, map, mode, pov, current_players, max_players, winners, placement_award, kill_award, mvp_award)

**Получить информацию для входа в лобби** - POST https://domen.com/api/getLobbyInfo **AUTH** (Аргументы: tournament_id)
- Возвращает {message, lobby_info, status} lobby_info - массив с данными для входа
- tournament_id - id турнира

**Добавить результаты турнира** - POST https://domen.com/api/saveResult **AUTH ADMIN** (Аргументы: tournament_id, tournament_results)
- Возвращает {message, status}
- tournament_id - id турнира
- tournament_results - массив, содержащий массивы с результатами игроков. Пример для PUBG: [user_id, placement (занятое место), mvp(является ли MVP, может быть 1 или 0), kills, deaths].
____

## Статистика
**Получить статистику игроков** - GET https://domen.com/api/getStatisticForPlayers (Аргументы: game_id, stat_item, period = 'all')
- Возвращает {message, status, statistic}
- game_id - id игры
- stat_item - критерий выбора статистики (для pubg можно выбрать: earnings, kills, placements, tournaments)
- period (необязательный параметр) - определяет за какой период выбирать статистику (возможные варианты: day, month, week)

____

## Регионы
**Получить список всех регионов** - GET https://domen.com/api/getAllRegions (Аргументов нет)
- Возвращает {message, status, regions}
- regions - массив объектов регионов. Пример объекта: {"id": 1, "region": "ru"}
