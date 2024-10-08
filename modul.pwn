#define TIME_TO_UPD 15000

stock vk_OnGameModeInit()
{
    SetTimer("VKActionTimer", TIME_TO_UPD, true);
}

callback: VKActionTimer()
{
    mysql_tquery(mysql_connection_id, "SELECT id,action,player,value,reason FROM `vk_actions` WHERE `status` = '0'", "LoadVKActions", "");
}

callback: LoadVKActions()
{
    new rows;
    cache_get_row_count(rows);

    if(rows)
    {
        new ID, Action, Value;
        new Player[MAX_PLAYER_NAME];
        new Reason[30];

        for (new i = 0; i < rows; i++)
        {
            cache_get_value_name_int(i, "id", ID);
            cache_get_value_name_int(i, "value", Value);
            cache_get_value_name_int(i, "action", Action);

            cache_get_value_name(i, "player", Player, sizeof(Player));
            cache_get_value_name(i, "reason", Reason, sizeof(Reason));

            mysql_tqueryf(mysql_connection_id, "UPDATE `vk_actions` SET `status` = '1' WHERE `id` = '%d'", ID);

            foreach (new j : Player)
            {
                if (!IsPlayerConnected(j)) continue;

                if (pInfo[j][pAdmin] >= 1)
                {
                    if (!strcmp(Player, pInfo[j][pAdminName], false))
                    {
                        if (Action == 1)
                        {
                            SendAdminf(1, "Администратор снял удалённо игрока %s[%d] с должности администратора", pInfo[j][pAdminName], j);
                            SendWarning(j, "Администратор снял Вам удалённо права администратора.");
                            pInfo[j][pAdmin] = 0;
                            pInfo[j][pAgm] = false;
                            SetPlayerHealth(j, 100.0);
                        }
                        else if (Action == 2)
                        {
                            SetPlayerAdmin(0, j, Value);
                        }
                        else if (Action == 3)
                        {
                            if(Value == 0) SendWarning(j, "Администратор кикнул Вас сервера через бота ВКонтакте.");
                            else if(Value == 1) SendWarningf(j, "Администратор кикнул Вас сервера через бота ВКонтакте. Причина: %s", Reason);
                            
                            Kick(j);
                        }
                        break;
                    }
                }
                else
                {
                    if (!strcmp(Player, pInfo[j][pName], false))
                    {
                        if (Action == 1)
                        {
                            SendAdminf(1, "Администратор снял удалённо игрока %s[%d] с должности администратора", pInfo[j][pName], j); // Здесь используй pName, так как это обычный игрок
                            SendWarning(j, "Администратор снял Вам удалённо права администратора.");
                            pInfo[j][pAdmin] = 0;
                            pInfo[j][pAgm] = false;
                            SetPlayerHealth(j, 100.0);
                        }
                        else if (Action == 2)
                        {
                            SetPlayerAdmin(0, j, Value);
                        }
                        else if (Action == 3)
                        {
                            if(Value == 0) SendWarning(j, "Администратор кикнул Вас сервера через бота ВКонтакте.");
                            else if(Value == 1) SendWarningf(j, "Администратор кикнул Вас сервера через бота ВКонтакте. Причина: %s", Reason);

                            Kick(j);
                        }
                        break;
                    }
                }
            }
        }
    }
    return 1;
}