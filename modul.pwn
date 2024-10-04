#define TIME_TO_UPD 15000 // MS

//=============[ OnGameModeInit ]================
SetTimer("VKActionTimer", TIME_TO_UPD, true);
//=============================

callback: VKActionTimer()
{
    mysql_tquery(mysql, "SELECT id,action,player,value,reason FROM `vk_actions` WHERE `status` = '0'", "LoadVKActions", "");
}

callback: LoadVKActions()
{
    new rows = cache_num_rows();

    if (rows)
    {
        new ID, Action, Value;
        new Player[MAX_PLAYER_NAME];
        new Reason[30];

        for (new i = 0; i < rows; i++)
        {
            ID = cache_get_field_content_int(i, "id");
            Value = cache_get_field_content_int(i, "value");
            Action = cache_get_field_content_int(i, "action");

            cache_get_field_content(i, "player", Player, sizeof(Player));
            cache_get_field_content(i, "reason", Reason, sizeof(Reason));

            foreach (new j : Player)
            {
                new nName[MAX_PLAYER_NAME];
                if (!IsPlayerConnected(j)) continue;
                GetPlayerName(j, nName, MAX_PLAYER_NAME);

                if (!strcmp(Player, nName, false))
                {
                    if (Action == 1)
                    {
                        // unadmin system
                    }
                    else if (Action == 2)
                    {
                        // giveadmin system
                    }

                    mysql_queryf(mysql, "UPDATE `vk_actions` SET `status` = '1' WHERE `id` = '%d'", ID);
                    break;
                }
            }

            // if action are undef
            if (Action != 1 && Action != 2)
            {
                mysql_queryf(mysql, "UPDATE `vk_actions` SET `status` = '1' WHERE `id` = '%d'", ID);
            }
        }
    }
    return 1;
}