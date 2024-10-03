<?php

require_once('simplevk-master/autoload.php');
require_once('SAMPApi/SampQueryAPI.php'); 
require_once('vendor/autoload.php');

use DigitalStar\vk_api\VK_api as vk_api; // vk_api

const VK_KEY = "vk1.a.Cq55IcR7PGuUxPRq8TFGk8TigMghVhIEOMN7J1fu2My1G3B664iD9LwKQ7_gYKL0ilSSwsOiUR2wQobZXyABwKr1dRnr2gFEGoRBC6f058L84L3MqdKtmosGijCG5PXkIG3iaobpIVj2JlETXzkirsX2JbDM620c9ou27bXDdRAQi4oG3IPfRNroqZOKoc0E6ep9q9ErpfWu0i-fU0TOlw";  // token
const ACCESS_KEY = "75c5d435";  //callback
const VERSION = "5.131"; // version API VK

$vk = vk_api::create(VK_KEY, VERSION)->setConfirm(ACCESS_KEY);

//=====================================[ Erorrs ]==================================================
$permision = 403;
//=====================================[Buttons VK]==================================================
$btn_1 = $vk->buttonText('Привязать аккаунт', 'blue', ['command' => 'btn_1']);

$btn_2 = $vk->buttonText('Да', 'green', ['command' => 'btn_2']);
$btn_3 = $vk->buttonText('Нет', 'red', ['command' => 'btn_3']);
//============================================================================================
$host_global = "164.132.206.179";
$username_global = "gs272375";
$password_global = "6rfBaTQDDs8P";
$database_global = "gs272375";

const ADMIN_CHAT = 2000000049;
//=================== [ MYSQL CONNECT ] =========== \\

$db_global = new mysqli($host_global, $username_global, $password_global, $database_global);


if ($db_global->connect_error) {
    die("Ошибка подключения к базе данных (server_zona): " . $db_global->connect_error);
}

$db_global->set_charset($charset);
//============================================================================================

$vk->initVars($peer_id, $message, $payload, $vk_id, $type, $data);

$peer_id = $data->object->peer_id;// ChatID
$message = $data->object->text; // Message

// Main Code
if ($data->type == 'message_new') // Check New Message
{
    $params_message = explode(' ', $message);
    $chat_id = $peer_id - 2000000000;
    //======================== [ CMD ] ======================\\
	if($message == '/online' or $message == '/онлайн')
	{
        $query = new SampQueryAPI('5.39.108.55', '1789'); 
        $serverInfo = $query->getInfo(); 

        $vk->sendMessage($peer_id, "📊 Текущий онлайн сервера: {$serverInfo['players']} из 250 (1 мс)");
	}
    if($message == '/players' or $message == '/игроки')
	{
        $query = new SampQueryAPI('5.39.108.55', '1789'); 
        $aPlayers = $query->getDetailedPlayers(); 
        $serverInfo = $query->getInfo(); 
        
        $players = "";
        
        if($serverInfo['players'] >= 1)
        {
            foreach($aPlayers as $sValue)
            {
                $players .= "— {$sValue['nickname']}[{$sValue['playerid']}] • Убийств {$sValue['score']} • Пинг {$sValue['ping']} ms";
            }
    
            return $vk->sendMessage($peer_id, "💫 Список игроков (всего — {$serverInfo['players']}):\n\n$players");
        }
        else
        {
            return $vk->sendMessage($peer_id, "😓 На данный момент нет игроков на сервере.");
        }
	}
	if($message == '/leaders' or $message == '/лидеры') return Leaders($peer_id, $db_global, $vk);
    if($message == '/i') return SendInformation($peer_id, $vk, $vk_id);
    if($params_message[0] == '/get') return CheckPlayer($peer_id, $params_message, $vk, $permision, $db_global);
    if($params_message[0] == '/ma') return MultiAccounts($peer_id, $params_message, $vk, $permision, $db_global);
    if($params_message[0] == '/unadmin') return RemoveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global);
    if($params_message[0] == '/logs') return PlayerLogs($peer_id, $params_message, $vk, $permision, $db_global);
    if($params_message[0] == '/giveadmin') return GiveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global);
    //============================================================================================================================================\\
    if($peer_id == $vk_id) 
    {
        if(in_array(mb_strtolower($message), ['начать', 'старт', 'меню', 'menu', 'start'], true)) 
        {
            return $vk->sendButton($peer_id, "✉️", [[$btn_1]]);
        }
        //======================= [ Buttons ] =======================\\
        if (isset($data->object->payload)) $btn = json_decode($data->object->payload, True);
        else $btn = null;
        $btn = $btn['command'];

        if ($btn == 'btn_1') 
        {
            return $vk->sendMessage($peer_id, "В разработке!");  
        }
    }
}

//========================= [ FUNCTION ] =========================\\
function CheckAdmin($vk_id, $db_global, $type, $player = null)
{
    if($type == 0)
    {
        $serach_sql = $db_global->query("SELECT name FROM accounts WHERE vk = '{$vk_id}' LIMIT 1");
        $name = $serach_sql->fetch_assoc()['name'];

        $admin_sql = $db_global->query("SELECT level FROM admins WHERE name = '{$name}' LIMIT 1");

        if($admin_sql->num_rows != 0) $level = $admin_sql->fetch_assoc()['level'];
        else $level = 0;

        return $level;
    }
    else
    {
        $admin_sql = $db_global->query("SELECT level FROM admins WHERE name = '{$player}' LIMIT 1");

        if($admin_sql->num_rows != 0) $level = $admin_sql->fetch_assoc()['level'];
        else $level = 0;

        return $level;
    }
}
function GetVKID($db_global, $name)
{
    $serach_sql = $db_global->query("SELECT vk FROM accounts WHERE name = '{$name}' LIMIT 1");

    if($serach_sql->num_rows != 0) $vk = $serach_sql->fetch_assoc()['vk'];
    else $vk = -1;

    return $vk;
}
function CheckValidAccount($db_global, $name)
{
    $search_sql = $db_global->query("SELECT id FROM accounts WHERE name = '{$name}' LIMIT 1");

    return $search_sql->num_rows > 0;
}
function SendActions($db_global, $action, $from, $player, $value, $reason = null)
{
    $db_global->query("INSERT INTO `vk_actions`(`action`, `from`, `player`, `value`, `reason`, `date`) VALUES ('$action','$from','$player','$value','$reason', NOW())");
}
function GiveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global)
{
    if($peer_id != ADMIN_CHAT) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");

    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);
    
    if($admin_lvl <= 0 or $admin_lvl < 7) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    if($params_message[1] == '') return $vk->sendMessage($peer_id, "Используйте: /giveadmin Ivan_Ivanov lvl (1-7)");
    if($params_message[2] == '') return $vk->sendMessage($peer_id, "Используйте: /giveadmin Ivan_Ivanov lvl (1-7)");

    if(CheckValidAccount($db_global, $params_message[1]) == false) return $vk->sendMessage($peer_id, "Аккаунт не найден в базе данных."); 

    $second_lvl = CheckAdmin($vk_id, $db_global, 1, $params_message[1]);

    if($second_lvl == $params_message[2] or $second_lvl >= 7) return $vk->sendMessage($peer_id, "У данного игрока уже имеется этот уровень админ-прав или выше."); 
    
    if($second_lvl == 0)
    {
        $db_global->query("INSERT INTO `admins`(`name`, `level`,  `name_giver`) VALUES ('{$params_message[1]}', '{$params_message[2]}', 'VKBot ($vk_id)')");

        SendActions($db_global, 2, $vk_id, $params_message[1], $params_message[2], "giveadmin bot (insert)");

        $vk->sendMessage($peer_id, "Игрок {$params_message[1]} был назначен на {$params_message[2]} уровень администратора"); 
    }
    else
    {
        $db_global->query("UPDATE `admins` SET `level`='{$params_message[2]}' WHERE `name`='{$params_message[1]}'");

        SendActions($db_global, 2, $vk_id, $params_message[1], $params_message[2], "giveadmin bot (update)");

        $vk->sendMessage($peer_id, "Администратор {$params_message[1]} был повышен/понижен на {$params_message[2]} уровень администратора"); 
    }
    return 1;
}
function RemoveAdmin($vk_id, $peer_id, $params_message, $vk, $permision, $db_global)
{
    if($peer_id != ADMIN_CHAT) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");

    $admin_lvl = CheckAdmin($vk_id, $db_global, 0);
    
    if($admin_lvl <= 0 or $admin_lvl < 7) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    if($params_message[1] == '') return $vk->sendMessage($peer_id, "Используйте: /unadmin Ivan_Ivanov");

    $second_lvl = CheckAdmin($vk_id, $db_global, 1, $params_message[1]);

    if($second_lvl <= 0) return $vk->sendMessage($peer_id, "{$params_message[1]} не является администратором");
    if($second_lvl >= $admin_lvl) return $vk->sendMessage($peer_id, "Ваши права не позволяют снять администратора с таким же или более высоким уровнем доступа.");

    $db_global->query("DELETE FROM `admins` WHERE name = '{$params_message[1]}'");

    SendActions($db_global, 1, $vk_id, $params_message[1], 0, "unadmin bot");

    $id = GetVKID($db_global, $params_message[1]);
    if($id != -1) 
    {
        $userInfo = $vk->request("users.get", ["user_ids" => $id]);
        $vk->sendMessage($peer_id, "Внимание: пользователь @id$id({$userInfo[0]['first_name']}) не является администратором.");
                
        $chat_id = $peer_id - 2000000000;
        $vk->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $id]);
    }

    return $vk->sendMessage($peer_id, "Администратор {$params_message[1]} был снят через бота ВКонтакте.");
}
function PlayerLogs($peer_id, $params_message, $vk, $permision, $db_global)
{
    if($peer_id != ADMIN_CHAT) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    if($params_message[1] == '') return $vk->sendMessage($peer_id, "Используйте: /logs Ivan_Ivanov");

    $serach_sql = $db_global->query("SELECT id FROM accounts WHERE name = '{$params_message[1]}'");
    $id = $serach_sql->fetch_assoc()['id'];

    $logs_sql = $db_global->query("SELECT * FROM logs WHERE userid = '{$id}' ORDER BY id DESC LIMIT 15");

    $count = $logs_sql->num_rows;   

    $$count_max = 15;
    if($count < 15) $count_max = $count;

    if($count == 0) return $vk->sendMessage($peer_id, "В базе данных нет записей действий от этого игрока.");

    $info = "";

    while ($row = $logs_sql->fetch_assoc()) 
    {
        $info .= "— {$row['log']} (📆 дата: {$row['time']});\n";
    }
    return $vk->sendMessage($peer_id, "📋 Список последних $count_max действий по запросу {$params_message[1]}:\n\n$info\n\nВсего -> $count строк(-а, -и).");
}
function MultiAccounts($peer_id, $params_message, $vk, $permision, $db_global)
{
    if($peer_id != ADMIN_CHAT) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    if($params_message[1] == '') return $vk->sendMessage($peer_id, "Используйте: /get Ivan_Ivanov");
    
    $serach_sql = $db_global->query("SELECT ip FROM accounts WHERE name = '{$params_message[1]}'");
    if($serach_sql->num_rows == 0) return $vk->sendMessage($peer_id, "Произошла ошибка, аккаунт с никнеймом {$params_message[1]} не найден в базе данных");

    $main_ip = $serach_sql->fetch_assoc()['ip'];

    $sip_sql = $db_global->query("SELECT * FROM accounts WHERE ip = '{$main_ip}'");

    $count = $sip_sql->num_rows; 
    if($count == 0) return $vk->sendMessage($peer_id, "Произошла ошибка при обработке запроса.");

    $info = "";

    while ($row = $sip_sql->fetch_assoc()) 
    {
        $info .= "— {$row['name']} (📆 дата регистрации: {$row['RegDate']});\n";
    }
    return $vk->sendMessage($peer_id, "📋 Список возможных мульти-аккаунтов по запросу {$params_message[1]}:\n\n$info\n\nВсего -> $count аккаунт(-а, -ов).");
}
function CheckPlayer($peer_id, $params_message, $vk, $permision, $db_global)
{
    if($peer_id != ADMIN_CHAT) return $vk->sendMessage($peer_id, "Произошла ошибка (#$permision)");
    if($params_message[1] == '') return $vk->sendMessage($peer_id, "Используйте: /get Ivan_Ivanov");

    $serach_sql = $db_global->query("SELECT * FROM accounts WHERE name = '{$params_message[1]}'");
    if($serach_sql->num_rows == 0) return $vk->sendMessage($peer_id, "Произошла ошибка, аккаунт с никнеймом {$params_message[1]} не найден в базе данных");

    $row = $serach_sql->fetch_assoc();
    
    $info = "";
    $vip = "";
    $hash = "";
    $status = "";

    $hash = md5($row['password']); 

    switch($row['connected']) {
        case 0:
            $status = "Не в сети";
            break;
        case 1:
            $status = "💡 В сети (ID: {$row['serverID']})";
            break;
    }

    switch($row['vip']) {
        case 0:
            $vip = "нет";
            break;
        case 1:
            $ts = $row['VipFinish'];

            $date = date('d.m.Y', $ts);

            $vip = "есть (закончится: {$date})";
            break;
    }

    $info = "📄 Основная информация по запросу '{$params_message[1]}':\nНикнейм — {$params_message[1]} [ $status ]\nДата регистрации — {$row['RegDate']}\nДата авторизации — {$row['LastLogin']}\nIP-адрес — {$row['ip']}\nХэш — $hash\n\n📚 Дополнительная информация по запросу '{$params_message[1]}':\nДеньги — {$row['money']}$\nДонат-валюта — {$row['donate_money']}$\nУбийств — {$row['level']}LVL\nОдежда — {$row['skin']}ID\nСтатус VIP — $vip";

    return $vk->sendMessage($peer_id, $info);
}
function SendInformation($peer_id, $vk, $vk_id)
{
    if($vk_id != 511754228) return 0;
    return $vk->sendMessage($peer_id, "📊 Information:\nPeerID: $peer_id\nYour ID: $vk_id");
}
function Leaders($peer_id, $db_global, $vk)
{
    $leaders_list = "";
    $leaders_sql = $db_global->query("SELECT * FROM organizations1");

    while ($row = $leaders_sql->fetch_assoc()) 
    {
        switch ($row['orga']) {
            case 0:
                $org = "ВЧ";
                break;
            case 1:
                $org = "Скинхеды";
                break;
            case 2:
                $org = "Кавказцы";
                break;
            default:
                $org = "неизвестно";
        }

        if($row['rang'] == 10) 
        {
            $name_search = $db_global->query("SELECT name FROM accounts WHERE id = '{$row['userid']}' LIMIT 1");
            $name1 = $name_search->fetch_assoc()['name'];

            $leaders_list .= "[$org] Лидер {$name1} [{$row['warns']}/3]\n";

            $soleaders_sql = $db_global->query("SELECT * FROM organizations1 WHERE rang = 9 AND orga = '{$row['orga']}'");
            while ($row2 = $soleaders_sql->fetch_assoc()) 
            {
                $name_search2 = $db_global->query("SELECT name FROM accounts WHERE id = '{$row2['userid']}' LIMIT 1");
                $name2 = $name_search2->fetch_assoc()['name'];

                $leaders_list .= "[$org] Зам {$name2} [-/-]\n";
            }
        }
    }
    return $vk->sendMessage($peer_id, $leaders_list);
}
//============================== [ INVITE / UNINVITE ] ================================\\
/*if ($data->object->action->type == 'chat_invite_user' or $data->object->action->type == 'chat_invite_user_by_link') 
{
    $chat = $data->object->action;
    $chat_data = $vk->request('messages.getConversationsById', ['peer_ids' => $peer_id, 'extended' => 0]);

    $title = $chat_data['items'][0]['chat_settings']['title'];

    $id = $chat->member_id;
    $userInfo = $vk->request("users.get", ["user_ids" => $id]);

    if($peer_id == 2000000007 or $peer_id == 2000000004) 
    {
        $checl_sql = $db_global->query("SELECT * FROM accounts WHERE VkontakteID = '{$id}'");
        $row = $checl_sql->fetch_assoc();

        if($row && $row[Admin] >= 1) 
        {
            switch ($row['Admin']) {
                case 0:
                    $GSName = "не админ";
                    break;
                case 1:
                    $GSName = "NGM";
                    break;
                case 2:
                    $GSName = "JRGM";
                    break;
                case 3:
                    $GSName = "GM";
                    break;
                case 4:
                    $org = "GM+";
                    break;
                case 5:
                    $GSName = "LGM";
                    break;
                case 6:
                    $GSName = "SGM";
                    break;
                case 7:
                    $GSName = "SGM+";
                    break;
                case 8:
                    $GSName = "DEV";
                    break;
                default:
                    $GSName = "неизвестно";
            }
            
            $vk->sendMessage($peer_id, "{$row['Name']}, $GSName (Сервер #1)

            Добро пожаловать, @id$id({$userInfo[0]['first_name']}). Ваши следующие действия:
            1. Прочитать закрепленное сообщение, принять все установленные правила и познакомиться с главным администратором.
            2. Привязать к своему игровому аккаунту Google Authenticator.
            
            Удачи!");
        }
        else 
        {
            $vk->sendMessage($peer_id, "Внимание: пользователь @id$id({$userInfo[0]['first_name']}) не является администратором.");
            
            $chat_id = $peer_id - 2000000000;
            $vk->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $id]);
        }
    }
    else if($peer_id == 2000000005)
    {
        $checl_sql = $db_global->query("SELECT * FROM accounts WHERE VkontakteID = '{$id}'");
        $row = $checl_sql->fetch_assoc();

        if($row)
        {
            switch ($row['member']) {
                case 0:
                    $org = "не состоите";
                    break;
                case 1:
                    $org = "Правительство";
                    break;
                case 2:
                    $org = "ВЧ";
                    break;
                case 3:
                    $org = "МО МВД";
                    break;
                case 4:
                    $org = "БЦРБ";
                    break;
                case 5:
                    $org = "Скинхеды";
                    break;
                case 6:
                    $org = "Гопота";
                    break;
                case 7:
                    $org = "Кавказцы";
                    break;
                default:
                    $org = "неизвестно";
            }
        }
        if($row && $row[Admin] >= 1 or $row[rank] >= 10) 
        {
            if($row[Admin] >= 1) 
            {
                $vk->sendMessage($peer_id, "👋 » @id$id({$userInfo[0]['first_name']}), добро пожаловать.\n\nАдминистратор {$row['Name']}");
            }
            else 
            {
                $vk->sendMessage($peer_id, "{$row['Name']}, $org (Сервер #1)

                Добро пожаловать, @id$id({$userInfo[0]['first_name']}). Ваши следующие действия:
                1. Прочитать закрепленное сообщение, принять все установленные правила и узнать своего следящего.
                2. Заполнить темы на форуме.
                3. Привязать к своему игровому аккаунту Google Authenticator.
                
                Удачи!");
            }
        }
        else 
        {
            $vk->sendMessage($peer_id, "Внимание: пользователь @id$id({$userInfo[0]['first_name']}) не является лидером.");
            
            $chat_id = $peer_id - 2000000000;
            $vk->request('messages.removeChatUser', ['chat_id' => $chat_id, 'member_id' => $id]);
        }        
    }
}*/
