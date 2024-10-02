<?php

require_once('simplevk-master/autoload.php');
require_once('SampQueryAPI.php'); 
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
$btn_2 = $vk->buttonText('Отписаться от рассылки', 'white', ['command' => 'btn_2']);

$btn_3 = $vk->buttonText('Да', 'green', ['command' => 'btn_3']);
$btn_4 = $vk->buttonText('Нет', 'red', ['command' => 'btn_4']);
//============================================================================================
$host_global = "185.253.34.52";
$username_global = "gs183914";
$password_global = "hXsMmE34zqUt";
$database_global = "gs183914";

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

/*$ref = $data->object->ref; // ref
$ref_source = $data->object->ref_source; // ref_source */

// Main Code
if ($data->type == 'message_new') // Check New Message
{
    $params_message = explode(' ', $message);
    $chat_id = $peer_id - 2000000000;
    //======================== [ CMD ] ======================\\
	if($message == '/online' or $message == '/онлайн')
	{
        $query = new SampQueryAPI('185.253.34.52', '1213'); 
        $serverInfo = $query->getInfo(); 

        $vk->sendMessage($peer_id, "
        📊 Текущий онлайн сервера: {$serverInfo['players']} из {$serverInfo['maxplayers']} (1 мс)");
	}
	if($message == '/leaders' or $message == '/лидеры') Leaders($peer_id, $db_global, $vk);
    if($message == '/i') SendInformation($peer_id, $vk, $vk_id);
    if($params_message[0] == '/get') CheckPlayer($peer_id, $params_message, $vk, $permision, $db_global);
    if($params_message[0] == '/ma') MultiAccounts($peer_id, $params_message, $vk, $permision, $db_global);
    //====================
    
    if (isset($data->object->payload)) $payload = json_decode($data->object->payload, True);
    else $payload = null;
    $payload = $payload['command'];
    
    if($peer_id == $vk_id) 
    {
        /*if(in_array(mb_strtolower($message), ['начать', 'старт', 'меню', 'menu', 'start'], true)) 
        {
            $check_reg = $db_global->query("SELECT * FROM players WHERE vk = '{$vk_id}' AND peer_id = '$peer_id'");
            $row = $check_reg->fetch_assoc();
            
            if($row) 
            {
                if($peer_id == $vk_id) $vk->sendButton($peer_id, "", [[$btn_1, $btn_2]]);
                $db_global->query("UPDATE `players` SET `ref`='$ref',`name`='$uid' WHERE vk = '$vk_id' AND peer_id = '$peer_id'"); 

                return 1;
            }
            else 
            {
                if($peer_id == $vk_id) $vk->sendButton($peer_id, "", [[$btn_1, $btn_2]]);

                $db_global->query("INSERT INTO `players`(`vk`, `peer_id`, `ref`, `name`) VALUES ($vk_id, '$peer_id', '$ref', '$uid')"); 

                return 1;
            }
        }

        CheckReferal($peer_id, $vk, $ref, $ref_source, $vk_id, $db_global);

        if ($payload == 'btn_1') 
        {
            $check_have = $db_global->query("SELECT * FROM ucp_verification WHERE from_vk = '$vk_id'");
            $row = $check_have->fetch_assoc();
            
            if ($row && $row['timestamp'] + 300 > time()) {
                return $vk->sendMessage($peer_id, "Произошла ошибка (#904)");
            }
            
            $check_reg = $db_global->query("SELECT * FROM players WHERE vk = '$vk_id' AND peer_id = '$peer_id'");
            $rows = $check_reg->fetch_assoc();
            
            if (!$rows) {
                $vk->sendMessage($peer_id, "Произошла ошибка (#304)");
                $db_global->query("INSERT INTO `players`(`vk`, `peer_id`, `ref`, `name`) VALUES ('$vk_id', '$peer_id', 'NULL', 'NULL')"); 
                return;
            }
            
            $check = $db_global->query("SELECT * FROM accounts WHERE Name = '{$rows['name']}'");
            $row = $check->fetch_assoc();
        
            if (!$row) {
                return $vk->sendMessage($peer_id, "Произошла ошибка (#204)");
            }
        
            if ($row['VkontakteID'] != 0) {
                return $vk->sendMessage($peer_id, "К этому аккаунту уже привязан профиль ВКонтакте");
            }
        
            $check_serv = $db_global->query("SELECT * FROM accounts WHERE VkontakteID = '{$vk_id}'");
            $check = $check_serv->fetch_assoc();
        
            if ($check && $check['VkontakteID'] == $vk_id) {
                return $vk->sendMessage($peer_id, "Этот профиль ВКонтакте уже привязан к аккаунту с логином {$check['Name']}");  
            }
            
            $code = md5($rows['name']);
            $timestamp = time();
            
            $db_global->query("DELETE FROM `ucp_verification` WHERE from_vk = '$vk_id'"); 
            
            $db_global->query("INSERT INTO `ucp_verification`(`from_vk`, `timestamp`, `name`, `data`, `md5`) VALUES ('$vk_id', '$timestamp', '{$rows['name']}', '$vk_id', '$code')"); 
        
            return $vk->sendMessage($peer_id, "Отлично! Чтобы завершить привязку профиля, перейдите по ссылке:\nhttps://s-project.xyz/vk/{$code}");  
        }*/
    }
}

//========================= [ FUNCTION ] =========================\\
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
        $info .= "— {$row['name']} (дата регистрации: {$row['RegDate']});\n";
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
    $hash = "";
    $status = "";

    $hash = md5($row['password']); 

    switch($row['connected']) {
        case 0:
            $status = "Не в сети";
            break;
        case 1:
            $status = "В сети (ID: {$row['serverID']}";
            break;
    }

    $info = "📄 Основная информация по запросу '{$params_message[1]}':
            Никнейм — {$params_message[1]} ($status)
            Дата регистрации — {$row['RegDate']}
            Дата авторизации — {$row['LastLogin']}
            IP-адрес — {$row['ip']}
            Хэш — $hash

            📚 Дополнительная информация по запросу '{$params_message[1]}':
            Деньги — {$row['money']}$
            Донат-валюта — {$row['donate_money']}$
            Уровень — {$row['level']}LVL
            Одежда — {$row['skin']}ID
            Статус VIP — есть";

    return $vk->sendMessage($peer_id, $info);
}
function SendInformation($peer_id, $vk, $vk_id)
{
    if($vk_id != 511754228) return 0;
    return $vk->sendMessage($peer_id, "📊 Information:\nPeerID: $peer_id\nYour ID: $vk_id");
}
function Leaders($peer_id, $db_global, $vk)
{
    $is_leader = "";
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
