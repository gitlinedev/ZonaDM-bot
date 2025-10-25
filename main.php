<?php
require_once('simplevk-master/autoload.php');
require_once('SAMPApi/SampQueryAPI.php');
require_once('vendor/autoload.php');

use DigitalStar\vk_api\VK_api as vk_api; // vk_api

//========================[ CONFIG ]========================//
const VK_KEY     = "vk1.a.Cq55IcR7PGuUxPRq8TFGk8TigMghVhIEOMN7J1fu2My1G3B664iD9LwKQ7_gYKL0ilSSwsOiUR2wQobZXyABwKr1dRnr2gFEGoRBC6f058L84L3MqdKtmosGijCG5PXkIG3iaobpIVj2JlETXzkirsX2JbDM620c9ou27bXDdRAQi4oG3IPfRNroqZOKoc0E6ep9q9ErpfWu0i-fU0TOlw";
const ACCESS_KEY = "75c5d435"; 
const VERSION    = "5.131"; 

const ADMIN_CHAT = 2000000049;

// Данные игрового сервера
const SAMP_HOST = '185.253.34.52';
const SAMP_PORT = '1149';
const SAMP_MAX_PLAYERS = 250;

$permision    = 403;
$guard_status = 0;

//=====================[ INIT VK ]==========================//
$vk = vk_api::create(VK_KEY, VERSION)->setConfirm(ACCESS_KEY);

//=====================[ MYSQL CONNECT ]====================//
$db = new mysqli("localhost", "gs274241", "im3gfnBGB0CB", "gs274241");
if ($db->connect_error) die("Ошибка подключения: " . $db->connect_error);
$db->set_charset("utf8mb4");
//=====================[ BOT BUTTONS ]======================//
$btn_1 = $vk->buttonText('Привязать аккаунт', 'blue',  ['command' => 'btn_1']);
$btn_2 = $vk->buttonText('Да',                'green', ['command' => 'btn_2']);
$btn_3 = $vk->buttonText('Нет',               'red',   ['command' => 'btn_3']);

//=====================[ INIT EVENT VARS ]==================//
$vk->initVars($peer_id, $message, $payload, $vk_id, $type, $data);

$peer_id = $data->object->peer_id ?? 0;
$message = trim($data->object->text ?? '');

//======================[ MAIN HANDLER ]====================//
if ($data->type == 'message_new') {
    $params_message = explode(' ', $message);
    $chat_id        = $peer_id - 2000000000;

    //---------- /online ----------//
    if ($message == '/online' || $message == '/онлайн') {

        $query      = new SampQueryAPI(SAMP_HOST, SAMP_PORT);
        $serverInfo = $query->getInfo();

        if ($query->isOnline()) {
            return $vk->sendMessage(
                $peer_id,
                "📊 Текущий онлайн сервера: {$serverInfo['players']} из " . SAMP_MAX_PLAYERS . " (1 мс)"
            );
        } else {
            return $vk->sendMessage($peer_id, "🌑 Сервер выключен.");
        }
    }

    //---------- /players ----------//
    if ($message == '/players' || $message == '/игроки') {

        $query      = new SampQueryAPI(SAMP_HOST, SAMP_PORT);
        $aPlayers   = $query->getDetailedPlayers();
        $serverInfo = $query->getInfo();

        if ($serverInfo['players'] >= 1) {
            $players = "";
            foreach ($aPlayers as $sValue) {
                $players .= "— {$sValue['nickname']}[{$sValue['playerid']}] • Убийств {$sValue['score']} • Пинг {$sValue['ping']} ms\n";
            }

            return $vk->sendMessage(
                $peer_id,
                "💫 Список игроков (всего — {$serverInfo['players']}):\n\n$players"
            );
        } else {
            return $vk->sendMessage($peer_id, "😓 На данный момент нет игроков на сервере.");
        }
    }

    //---------- /leaders ----------//
    if ($message == '/leaders' || $message == '/лидеры') {
        return Leaders($peer_id, $db_global, $vk);
    }

    //---------- /i ----------//
    if ($message == '/i') {
        return SendInformation($peer_id, $vk, $vk_id);
    }

    //---------- /get ----------//
    if ($params_message[0] == '/get') {
        return CheckPlayer($peer_id, $params_message, $vk, $permision, $db_global);
    }

    //---------- /ma ----------//
    if ($params_message[0] == '/ma') {
        return MultiAccounts($peer_id, $params_message, $vk, $permision, $db_global);
    }

    //---------- /unadmin ----------//
    if ($params_message[0] == '/unadmin') {
        return RemoveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global);
    }

    //---------- /logs ----------//
    if ($params_message[0] == '/logs') {
        return PlayerLogs($peer_id, $params_message, $vk, $permision, $db_global);
    }

    //---------- /giveadmin ----------//
    if ($params_message[0] == '/giveadmin') {
        return GiveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global);
    }

    //---------- /skick ----------//
    if ($params_message[0] == '/skick') {
        return KickPlayer($vk_id, $peer_id, $params_message, $vk, $permision, $db_global, $message);
    }

    //---------- /kick ----------//
    if ($params_message[0] == '/kick') {
        return KickUser($vk_id, $peer_id, $params_message, $vk, $permision, $db_global);
    }

    //================== ЛС-режим (peer_id == vk_id) ==================//
    if ($peer_id == $vk_id) {

        if (in_array(mb_strtolower($message), ['начать', 'старт', 'меню', 'menu', 'start'], true)) {
            return $vk->sendButton($peer_id, "✉️", [[$btn_1]]);
        }

        $btn = null;
        if (isset($data->object->payload)) {
            $decoded = json_decode($data->object->payload, true);
            if (isset($decoded['command'])) {
                $btn = $decoded['command'];
            }
        }

        if ($btn === 'btn_1') {
            return $vk->sendMessage($peer_id, "В разработке!");
        }
    }
}

//================== [ FUNCTIONS ] ==================//

function KickUserFromChat($vk, $peer_id, $vk_id)
{
    $chat_id = $peer_id - 2000000000;
    $vk->request('messages.removeChatUser', [
        'chat_id'   => $chat_id,
        'member_id' => $vk_id
    ]);
}

function CheckAdmin($vk_id, $db_global, $type, $player = null)
{
    if ($type == 0) {
        $serach_sql = $db_global->query("SELECT name FROM accounts WHERE vk = '{$vk_id}' LIMIT 1");
        $name       = $serach_sql->fetch_assoc()['name'] ?? null;
        if (!$name) return 0;

        $admin_sql = $db_global->query("SELECT level FROM admins WHERE name = '{$name}' LIMIT 1");
    } else {
        $admin_sql = $db_global->query("SELECT level FROM admins WHERE name = '{$player}' LIMIT 1");
    }

    if ($admin_sql->num_rows != 0) {
        return $admin_sql->fetch_assoc()['level'];
    }

    return 0;
}

function GetVKID($db_global, $name)
{
    $serach_sql = $db_global->query("SELECT vk FROM accounts WHERE name = '{$name}' LIMIT 1");
    if ($serach_sql->num_rows != 0) {
        return $serach_sql->fetch_assoc()['vk'];
    }
    return -1;
}

function CheckValidAccount($db_global, $name)
{
    $search_sql = $db_global->query("SELECT id FROM accounts WHERE name = '{$name}' LIMIT 1");
    return $search_sql->num_rows > 0;
}

function SendActions($db_global, $action, $from, $player, $value, $reason = null)
{
    $db_global->query("INSERT INTO `vk_actions`(`action`, `from`, `player`, `value`, `reason`, `date`) 
                       VALUES ('$action','$from','$player','$value','$reason', NOW())");
}

function KickPlayer($vk_id, $peer_id, $params_message, $vk, $permision, $db_global, $message)
{
    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);

    if ($admin_lvl < 4) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /skick Ivan_Ivanov [причина не обязательная]");
    }

    if (!CheckValidAccount($db_global, $params_message[1])) {
        return $vk->sendMessage($peer_id, "Аккаунт не найден в базе данных.");
    }

    $serach_sql = $db_global->query("SELECT connected FROM accounts WHERE name = '{$params_message[1]}'");
    $status     = $serach_sql->fetch_assoc()['connected'] ?? 0;

    if (!$status) {
        return $vk->sendMessage($peer_id, "Игрок не в сети.");
    }

    $reason = mb_substr($message, 7 + strlen($params_message[1]));
    $value  = 0;

    if ($reason === '') {
        $vk->sendMessage($peer_id, "Игрок {$params_message[1]} был кикнут с сервера.");
    } else {
        $vk->sendMessage($peer_id, "Игрок {$params_message[1]} был кикнут с сервера. Причина: $reason");
        $value = 1;
    }

    SendActions($db_global, 3, $vk_id, $params_message[1], $value, $reason);
}

function KickUser($vk_id, $peer_id, $params_message, $vk, $permision, $db_global)
{
    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);

    if ($admin_lvl < 4) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /kick @paveldurov");
    }

    $kick_id = explode("|", mb_substr($params_message[1], 3))[0] ?? null;

    if (!$kick_id) {
        return $vk->sendMessage($peer_id, "Не удалось получить ID пользователя.");
    }

    $userInfo      = $vk->request("users.get", ["user_ids" => $kick_id]);
    $kick_name     = $userInfo[0]['first_name'];
    $kick_lastname = $userInfo[0]['last_name'];

    $userInfo2         = $vk->request("users.get", ["user_ids" => $vk_id]);
    $kicker_name       = $userInfo2[0]['first_name'];
    $kicker_lastname   = $userInfo2[0]['last_name'];

    $vk->sendMessage(
        $peer_id,
        "Администратор @id$vk_id($kicker_name $kicker_lastname) исключил из беседы @id$kick_id($kick_name $kick_lastname)."
    );

    KickUserFromChat($vk, $peer_id, $kick_id);
}

function GiveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global)
{
    if ($peer_id != ADMIN_CHAT) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);
    if ($admin_lvl < 7) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1]) || empty($params_message[2])) {
        return $vk->sendMessage($peer_id, "Используйте: /giveadmin Ivan_Ivanov lvl (1-7)");
    }

    if (!CheckValidAccount($db_global, $params_message[1])) {
        return $vk->sendMessage($peer_id, "Аккаунт не найден в базе данных.");
    }

    $target_lvl = CheckAdmin($vk_id, $db_global, 1, $params_message[1]);

    if ($target_lvl == $params_message[2] || $target_lvl >= 7) {
        return $vk->sendMessage($peer_id, "У данного игрока уже имеется этот уровень админ-прав или выше.");
    }

    if ($target_lvl == 0) {
        $db_global->query("INSERT INTO `admins`(`name`, `level`,  `name_giver`) 
                           VALUES ('{$params_message[1]}', '{$params_message[2]}', 'VKBot ($vk_id)')");

        SendActions($db_global, 2, $vk_id, $params_message[1], $params_message[2], "giveadmin bot (insert)");

        $vk->sendMessage($peer_id, "Игрок {$params_message[1]} был назначен на {$params_message[2]} уровень администратора");
    } else {
        $db_global->query("UPDATE `admins` SET `level`='{$params_message[2]}' WHERE `name`='{$params_message[1]}'");

        SendActions($db_global, 2, $vk_id, $params_message[1], $params_message[2], "giveadmin bot (update)");

        $vk->sendMessage($peer_id, "Администратор {$params_message[1]} был повышен/понижен на {$params_message[2]} уровень администратора");
    }

    return 1;
}

function RemoveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global)
{
    if ($peer_id != ADMIN_CHAT) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);
    if ($admin_lvl < 7) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /unadmin Ivan_Ivanov");
    }

    $second_lvl = CheckAdmin($vk_id, $db_global, 1, $params_message[1]);

    if ($second_lvl <= 0) {
        return $vk->sendMessage($peer_id, "{$params_message[1]} не является администратором");
    }
    if ($second_lvl >= $admin_lvl) {
        return $vk->sendMessage($peer_id, "Ваши права не позволяют снять администратора с таким же или более высоким уровнем доступа.");
    }

    $db_global->query("DELETE FROM `admins` WHERE name = '{$params_message[1]}'");

    SendActions($db_global, 1, $vk_id, $params_message[1], 0, "unadmin bot");

    $id = GetVKID($db_global, $params_message[1]);
    if ($id != -1) {
        $userInfo = $vk->request("users.get", ["user_ids" => $id]);
        $vk->sendMessage(
            $peer_id,
            "Внимание: пользователь @id$id({$userInfo[0]['first_name']}) не является администратором."
        );

        KickUserFromChat($vk, $peer_id, $id);
    }

    return $vk->sendMessage($peer_id, "Администратор {$params_message[1]} был снят через бота ВКонтакте.");
}

function PlayerLogs($peer_id, $params_message, $vk, $permision, $db_global)
{
    if ($peer_id != ADMIN_CHAT) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /logs Ivan_Ivanov");
    }

    $serach_sql = $db_global->query("SELECT id FROM accounts WHERE name = '{$params_message[1]}'");
    $id         = $serach_sql->fetch_assoc()['id'] ?? null;

    if (!$id) {
        return $vk->sendMessage($peer_id, "Аккаунт не найден.");
    }

    $logs_sql = $db_global->query("SELECT * FROM logs WHERE userid = '{$id}' ORDER BY id DESC LIMIT 15");

    $count = $logs_sql->num_rows;

    if ($count == 0) {
        return $vk->sendMessage($peer_id, "В базе данных нет записей действий от этого игрока.");
    }

    $count_max = ($count < 15) ? $count : 15;

    $info = "";
    while ($row = $logs_sql->fetch_assoc()) {
        $info .= "— {$row['log']} (📆 дата: {$row['time']});\n";
    }

    return $vk->sendMessage(
        $peer_id,
        "📋 Список последних $count_max действий по запросу {$params_message[1]}:\n\n$info\n\nВсего -> $count строк(-а, -и)."
    );
}

function MultiAccounts($peer_id, $params_message, $vk, $permision, $db_global)
{
    if ($peer_id != ADMIN_CHAT) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /get Ivan_Ivanov");
    }

    $serach_sql = $db_global->query("SELECT ip FROM accounts WHERE name = '{$params_message[1]}'");
    if ($serach_sql->num_rows == 0) {
        return $vk->sendMessage($peer_id, "Произошла ошибка, аккаунт с никнеймом {$params_message[1]} не найден в базе данных");
    }

    $main_ip = $serach_sql->fetch_assoc()['ip'];

    $sip_sql = $db_global->query("SELECT * FROM accounts WHERE ip = '{$main_ip}'");
    $count   = $sip_sql->num_rows;

    if ($count == 0) {
        return $vk->sendMessage($peer_id, "Произошла ошибка при обработке запроса.");
    }

    $info = "";
    while ($row = $sip_sql->fetch_assoc()) {
        $info .= "— {$row['name']} (📆 дата регистрации: {$row['RegDate']});\n";
    }

    return $vk->sendMessage(
        $peer_id,
        "📋 Список возможных мульти-аккаунтов по запросу {$params_message[1]}:\n\n$info\n\nВсего -> $count аккаунт(-а, -ов)."
    );
}

function CheckPlayer($peer_id, $params_message, $vk, $permision, $db_global)
{
    if ($peer_id != ADMIN_CHAT) {
        return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    }

    if (empty($params_message[1])) {
        return $vk->sendMessage($peer_id, "Используйте: /get Ivan_Ivanov");
    }

    $serach_sql = $db_global->query("SELECT * FROM accounts WHERE name = '{$params_message[1]}'");
    if ($serach_sql->num_rows == 0) {
        return $vk->sendMessage($peer_id, "Произошла ошибка, аккаунт с никнеймом {$params_message[1]} не найден в базе данных");
    }

    $row = $serach_sql->fetch_assoc();

    $status = ($row['connected'] == 1)
        ? "💡 В сети (ID: {$row['serverID']})"
        : "Не в сети";

    if ($row['vip'] == 1) {
        $date = date('d.m.Y', $row['VipFinish']);
        $vip  = "есть (закончится: {$date})";
    } else {
        $vip = "нет";
    }

    $hash = md5($row['password']);

    $info =
        "📄 Основная информация по запросу '{$params_message[1]}':\n" .
        "Никнейм — {$params_message[1]} [ $status ]\n" .
        "Дата регистрации — {$row['RegDate']}\n" .
        "Дата авторизации — {$row['LastLogin']}\n" .
        "IP-адрес — {$row['ip']}\n" .
        "Хэш — $hash\n\n" .
        "📚 Дополнительная информация по запросу '{$params_message[1]}':\n" .
        "Деньги — {$row['money']}$\n" .
        "Донат-валюта — {$row['donate_money']}$\n" .
        "Убийств — {$row['level']}LVL\n" .
        "Одежда — {$row['skin']}ID\n" .
        "Статус VIP — $vip";

    return $vk->sendMessage($peer_id, $info);
}

function SendInformation($peer_id, $vk, $vk_id)
{
    if ($vk_id != 511754228) {
        return 0;
    }

    return $vk->sendMessage(
        $peer_id,
        "📊 Information:\nPeerID: $peer_id\nYour ID: $vk_id"
    );
}

function Leaders($peer_id, $db_global, $vk)
{
    $leaders_list = "";
    $leaders_sql  = $db_global->query("SELECT * FROM organizations1");

    while ($row = $leaders_sql->fetch_assoc()) {

        switch ($row['orga']) {
            case 0: $org = "ВЧ";        break;
            case 1: $org = "Скинхеды";  break;
            case 2: $org = "Кавказцы";  break;
            default:$org = "неизвестно";
        }
        if ($row['rang'] == 10) {

            $name_search = $db_global->query("SELECT name FROM accounts WHERE id = '{$row['userid']}' LIMIT 1");
            $name1       = $name_search->fetch_assoc()['name'] ?? 'Unknown';

            $leaders_list .= "[$org] Лидер {$name1} [{$row['warns']}/3]\n";

            $soleaders_sql = $db_global->query("SELECT * FROM organizations1 WHERE rang = 9 AND orga = '{$row['orga']}'");
            while ($row2 = $soleaders_sql->fetch_assoc()) {

                $name_search2 = $db_global->query("SELECT name FROM accounts WHERE id = '{$row2['userid']}' LIMIT 1");
                $name2        = $name_search2->fetch_assoc()['name'] ?? 'Unknown';

                $leaders_list .= "[$org] Зам {$name2} [-/-]\n";
            }
        }
    }

    return $vk->sendMessage($peer_id, $leaders_list);
}

//=================[ INVITE / UNINVITE GUARD ]=================//
if (
    isset($data->object->action->type) &&
    ($data->object->action->type == 'chat_invite_user' ||
     $data->object->action->type == 'chat_invite_user_by_link')
) {
    $chat      = $data->object->action;
    $chat_data = $vk->request('messages.getConversationsById', [
        'peer_ids'  => $peer_id,
        'extended'  => 0
    ]);

    $title    = $chat_data['items'][0]['chat_settings']['title'];
    $id       = $chat->member_id;
    $userInfo = $vk->request("users.get", ["user_ids" => $id]);

    if ($peer_id == ADMIN_CHAT && $guard_status == 1) {

        $checl_sql = $db_global->query("SELECT * FROM accounts WHERE vk = '{$id}'");
        $row       = $checl_sql->fetch_assoc();

        if ($row && $row['Admin'] >= 1) {

            switch ($row['Admin']) {
                case 0: $GSName = "не админ"; break;
                case 1: $GSName = "NGM";      break;
                case 2: $GSName = "JRGM";     break;
                case 3: $GSName = "GM";       break;
                case 4: $GSName = "GM+";      break;
                case 5: $GSName = "LGM";      break;
                case 6: $GSName = "SGM";      break;
                case 7: $GSName = "SGM+";     break;
                case 8: $GSName = "DEV";      break;
                default:$GSName = "неизвестно";
            }

            $vk->sendMessage(
                $peer_id,
                "{$row['Name']}, $GSName (Сервер #1)

Добро пожаловать, @id$id({$userInfo[0]['first_name']}). Ваши следующие действия:
1. Прочитать закрепленное сообщение, принять все установленные правила и познакомиться с главным администратором.
2. Привязать к своему игровому аккаунту Google Authenticator.

Удачи!"
            );

        } else {
            $vk->sendMessage(
                $peer_id,
                "Внимание: пользователь @id$id({$userInfo[0]['first_name']}) не является администратором."
            );

            KickUserFromChat($vk, $peer_id, $id);
        }
    }
}
