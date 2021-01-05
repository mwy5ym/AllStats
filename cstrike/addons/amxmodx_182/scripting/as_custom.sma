#include <amxmodx>
#include <cstrike>
#include <engine>
#include <fun>
#include <fakemeta>
#include <ipseeker>
#include <sqlx>
#include <as>

#define BOT_ID_LIMIT 100000000
new g_bot_random_id;

// 取消下行注释开启演示: 从网页到游戏的数据传输, 对应console.php的功能
// #define MESSAGE_DEMO

// 未登录:
// 按Y输入 login 或 /login 进行登录
// 按Y输入 井号密码, 例如 #abcdefg 使用当前游戏名注册新用户 <--- 仅适用于无论坛模式

// 登录后:
// 按Y输入 星号密码, 例如 *abcdefg 设置密码
// 按Y输入 叹号签名, 例如 !hahaha  设置签名

// 改成 0: 无论坛模式
// 改成 1: 有论坛模式, 并修改下方4个变量 <---------------------- 重要! 重要! 重要! 先看这里! 先看这里! 先看这里!
#if 0
#define USE_DISCUZ
#define TABLE_PRE       "pre_"
#define FIELD_NAME      "field1"
#define FIELD_FLAGS     "field2"
#define FIELD_SIGNATURE "field3"
#define FIELD_STEAMID   "field4"
#else
#define AS_TASK_SHOW_USAGE 11111
#endif

// 按实际情况修改成as数据库或论坛数据库的连接方式 <------------- 别忘了还有这里!
#define DB_HOST         "127.0.0.1:3307"
#define DB_USER         "root"
#define DB_PASS         "qwer1234"
#define DB_NAME         "cstrike"

// 按实际情况修改用户登录选项字段, 比如可以改成 _uid _user _pass, as.txt中的提示信息记得也修改下哦
#define SETINFO_UID     "uid"
#define SETINFO_USER    "user"
#define SETINFO_PASS    "pass"

#define AS_SALT_LENGTH      6
#define AS_PASSWORD_LENGTH  16
#define AS_LOCATION_LENGTH  127
#define AS_SIGNATURE_LENGTH 63

enum
{
    EXECUTE_TYPE_REGISTER = 0,
    EXECUTE_TYPE_RENAME,
    EXECUTE_TYPE_PASSWORD,
    EXECUTE_TYPE_SIGNATURE
};

enum
{
    WEAPON_AMMO_TYPE = 0,
    WEAPON_AMMO_MAX,
    WEAPON_CLIP_MAX,
    WEAPON_CLIP_SIZE,
    WEAPON_AMMO_CLIP_ENUM_COUNT
};

new const g_weapon_info[AS_MAX_WEAPONS + 1][WEAPON_AMMO_CLIP_ENUM_COUNT] =
{
    { 0,  0,   0, 0   }, // CSW_NONE
    { 9,  52,  4, 13  }, // CSW_P228
    { 0,  0,   0, 0   }, // CSW_GLOCK
    { 2,  90,  3, 10  }, // CSW_SCOUT
    { 12, 1,   0, 0   }, // CSW_HEGRENADE
    { 5,  32,  4, 7   }, // CSW_XM1014
    { 14, 1,   0, 0   }, // CSW_C4
    { 6,  100, 9, 30  }, // CSW_MAC10
    { 4,  90,  3, 30  }, // CSW_AUG
    { 13, 1,   0, 0   }, // CSW_SMOKEGRENADE
    { 10, 120, 4, 30  }, // CSW_ELITE
    { 7,  100, 2, 20  }, // CSW_FIVESEVEN
    { 6,  100, 9, 25  }, // CSW_UMP45
    { 4,  90,  3, 30  }, // CSW_SG550
    { 4,  90,  3, 35  }, // CSW_GALIL
    { 4,  90,  3, 25  }, // CSW_FAMAS
    { 6,  100, 9, 12  }, // CSW_USP
    { 10, 120, 4, 20  }, // CSW_GLOCK18
    { 1,  30,  3, 10  }, // CSW_AWP
    { 10, 120, 4, 30  }, // CSW_MP5NAVY
    { 3,  200, 7, 100 }, // CSW_M249
    { 5,  21,  4, 8   }, // CSW_M3
    { 4,  90,  3, 30  }, // CSW_M4A1
    { 10, 120, 4, 30  }, // CSW_TMP
    { 2,  90,  3, 20  }, // CSW_G3SG1
    { 11, 2,   0, 0   }, // CSW_FLASHBANG
    { 8,  35,  5, 7   }, // CSW_DEAGLE
    { 4,  90,  3, 30  }, // CSW_SG552
    { 2,  90,  3, 30  }, // CSW_AK47
    { 0,  0,   0, 0   }, // CSW_KNIFE
    { 7,  100, 2, 50  }, // CSW_P90
    { 0,  0,   0, 0   }, // CSW_VEST
    { 0,  0,   0, 0   }  // CSW_VESTHELM
};

new g_ammo_name[15][] =
{
    "",
    "ammo_338magnum",
    "ammo_762nato",
    "ammo_556natobox",
    "ammo_556nato",
    "ammo_buckshot",
    "ammo_45acp",
    "ammo_57mm",
    "ammo_50ae",
    "ammo_357sig",
    "ammo_9mm",
    "",
    "",
    "",
    ""
};

new Handle:dbt;
new Handle:dbc;

new AS_MODE_TYPE:g_mode;

new g_msg_SayText;

new g_player_password_check[AS_MAX_PLAYERS + 1];   // 状态
new g_player_login_check[AS_MAX_PLAYERS + 1][2];   // 时间 次数
new g_player_execute_check[AS_MAX_PLAYERS + 1][2]; // 状态 次数

new g_player_salt[AS_MAX_PLAYERS + 1][AS_SALT_LENGTH + 1];
new g_player_name[AS_MAX_PLAYERS + 1][AS_NAME_LENGTH + 1];
new g_player_name_new[AS_MAX_PLAYERS + 1][AS_NAME_LENGTH + 1];
new g_player_password_new[AS_MAX_PLAYERS + 1][AS_PASSWORD_LENGTH + 1];
new g_player_location[AS_MAX_PLAYERS + 1][AS_LOCATION_LENGTH + 1];
new g_player_signature[AS_MAX_PLAYERS + 1][AS_SIGNATURE_LENGTH + 1];
new g_player_signature_new[AS_MAX_PLAYERS + 1][AS_SIGNATURE_LENGTH + 1];
new g_player_timestamp[AS_MAX_PLAYERS + 1];

public Float:apb(a, b, m)
{
    if (b == 0) return 0.0;
    return 1.0 * a / b * m;
}

public plugin_natives()
{
    register_native("as_calc_score", "native_calc_score");
    register_native("as_calc_rating", "native_calc_rating");
    register_native("as_calc_rws", "native_calc_rws");
    register_native("as_set_kill_sound", "native_set_kill_sound");
    register_native("as_set_kill_badge", "native_set_kill_badge");
    register_native("as_set_hud_l", "native_set_hud_l");
    register_native("as_set_hud_s", "native_set_hud_s");
    register_native("as_set_tutor_stats", "native_set_tutor_stats");
    register_native("as_player_login", "native_player_login");
    register_native("as_get_player_signature", "native_get_player_signature");
}

public plugin_precache()
{
    if (get_cvar_num("as_mk_sound_flag"))
    {
        // 和 as_set_kill_sound 对应
        as_precache_sound("sound/as/v1/t/kill_revenge.wav");
        as_precache_sound("sound/as/v1/t/kill_hegrenade.wav");

        as_precache_sound("sound/as/v1/t/kill_knife.wav");
        as_precache_sound("sound/as/v1/t/kill_headshot.wav");

        as_precache_sound("sound/as/v1/t/multi_kill_1.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_2.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_3.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_4.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_5.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_6.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_7.wav");
        as_precache_sound("sound/as/v1/t/multi_kill_8.wav");

        as_precache_sound("sound/as/v1/ct/kill_revenge.wav");
        as_precache_sound("sound/as/v1/ct/kill_hegrenade.wav");

        as_precache_sound("sound/as/v1/ct/kill_knife.wav");
        as_precache_sound("sound/as/v1/ct/kill_headshot.wav");

        as_precache_sound("sound/as/v1/ct/multi_kill_1.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_2.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_3.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_4.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_5.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_6.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_7.wav");
        as_precache_sound("sound/as/v1/ct/multi_kill_8.wav");
    }

    if (get_cvar_num("as_mk_badge_flag"))
    {
        // 和 as_set_kill_badge 对应
        as_precache_sprite("sprites/as/v1/kill_revenge.spr");
        as_precache_sprite("sprites/as/v1/kill_hegrenade.spr");

        as_precache_sprite("sprites/as/v1/kill_headshot.spr");
        as_precache_sprite("sprites/as/v1/kill_headwallshot.spr");
        as_precache_sprite("sprites/as/v1/kill_wallshot.spr");

        as_precache_sprite("sprites/as/v1/multi_kill_headshot.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_headwallshot.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_wallshot.spr");

        as_precache_sprite("sprites/as/v1/multi_kill_1.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_2.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_3.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_4.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_5.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_6.spr");

        as_precache_sprite("sprites/as/v1/multi_kill_knife_1.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_knife_2.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_knife_3.spr");
        as_precache_sprite("sprites/as/v1/multi_kill_knife_4.spr");
    }
}

public plugin_init()
{
    register_plugin("All Stats Custom", AS_VERSION, AS_AUTHOR);

    register_srvcmd("as_db_connect", "as_db_connect");
    register_srvcmd("as_db_disconnect", "as_db_disconnect");
    register_srvcmd("as_message", "as_message");

    register_clcmd("say", "as_cmd_say");
    register_clcmd("say_team", "as_cmd_say");

    register_forward(FM_ClientUserInfoChanged, "as_set_player_name");

    g_msg_SayText = get_user_msgid("SayText");

    as_log("[AS] database config: host[%s] user[%s] pass[%s] name[%s]", DB_HOST, DB_USER, DB_PASS, DB_NAME);
    dbt = SQL_MakeDbTuple(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    as_db_connect();

#if defined USE_DISCUZ
    // 有论坛模式则不带此使用说明, 对应as.txt中的 USAGE_USER
#else
    taskShowUsage(AS_TASK_SHOW_USAGE);
#endif
}

public plugin_cfg()
{
    g_mode = AS_MODE_TYPE:get_cvar_num("as_mode");
}

public plugin_end()
{
#if defined USE_DISCUZ
    // 有论坛模式则不带此使用说明, 对应as.txt中的 USAGE_USER
#else
    if (task_exists(AS_TASK_SHOW_USAGE)) remove_task(AS_TASK_SHOW_USAGE);
#endif
    as_db_disconnect();
    SQL_FreeHandle(dbt);
}

public as_db_connect()
{
    if (dbc) return;
    new errcode, errinfo[128];
    dbc = SQL_Connect(dbt, errcode, errinfo, charsmax(errinfo));
    if (dbc) as_log("[AS] database connected");
    else as_log("[AS] failed to connect to database: [%d] [%s]", errcode, errinfo);
}

public as_db_disconnect()
{
    if (dbc)
    {
        SQL_FreeHandle(dbc);
        as_log("[AS] database disconnected");
    }
}

public as_message()
{
    new buffer[1024];
    read_args(buffer, charsmax(buffer));
    as_send_message(buffer);
}

public as_cmd_say(index)
{
    new temp[256];
    read_args(temp, charsmax(temp));
    remove_quotes(temp);
    for (new i = 0; temp[i]; ++i) { if (temp[i] == -17 && temp[i + 1] == -93 && temp[i + 2] == -75) temp[i] = temp[i + 1] = temp[i + 2] = 0; } // 过滤特殊字符

    if ((strcmp(temp, "login", true) == 0) || (strcmp(temp, "/login", true) == 0))
    {
        if (as_get_player_id(index) > 0) { sendMessageLang(index, "LOGIN_MSG_E"); return PLUGIN_HANDLED; }
        if (g_player_login_check[index][0] + 10 > get_systime()) { sendMessageLang(index, "LOGIN_MSG_T"); return PLUGIN_HANDLED; }
        if (g_player_login_check[index][1] > 2) { sendMessageLang(index, "LOGIN_MSG_O"); return PLUGIN_HANDLED; }
        g_player_login_check[index][0] = get_systime();
        g_player_login_check[index][1]++;
        sendMessageLang(index, "LOGIN_MSG_W");
        as_player_login(index);
        return PLUGIN_HANDLED;
    }

#if defined USE_DISCUZ
    if (temp[0] == '@' || temp[0] == '*' || temp[0] == '!') // 将要进行数据库更新
#else
    if (temp[0] == '#' || temp[0] == '@' || temp[0] == '*' || temp[0] == '!') // 将要进行数据库更新
#endif
    {
        if (!dbc) { sendMessageLang(index, "ERROR_MSG_N"); return PLUGIN_HANDLED; }
        if (g_player_execute_check[index][0]) { sendMessageLang(index, "EXECUTE_MSG_E"); return PLUGIN_HANDLED; }
        if (g_player_execute_check[index][1] > 4) { sendMessageLang(index, "EXECUTE_MSG_O"); return PLUGIN_HANDLED; }
#if defined USE_DISCUZ
        if (as_get_player_id(index) <= 0) { sendMessageLang(index, "LOGIN_MSG_N"); return PLUGIN_HANDLED; }
#else
        if (temp[0] != '#' && as_get_player_id(index) <= 0) { sendMessageLang(index, "LOGIN_MSG_N"); return PLUGIN_HANDLED; }
#endif
    }

#if defined USE_DISCUZ
    // 有论坛就不带注册功能
#else
    if (temp[0] == '#') // say的内容以 # 开头表示设置密码以注册新用户
    {
        if (as_get_player_id(index) > 0) { sendMessageLang(index, "LOGIN_MSG_E"); return PLUGIN_HANDLED; }
        temp[0] = ' '; trim(temp);
        if (!temp[0]) { sendMessageLang(index, "ERROR_MSG_PASSWORD_E"); return PLUGIN_HANDLED; }
        if (strlen(temp) < 6 || 16 < strlen(temp)) { sendMessageLang(index, "ERROR_MSG_PASSWORD_L"); return PLUGIN_HANDLED; } // 长度6~16位, 且不可包含以下字符: ^"\/`~
        for (new i = 0; temp[i]; ++i) if (temp[i] < 0 || temp[i] == 0x5E || temp[i] == '"' || temp[i] == 92 || temp[i] == '/' || temp[i] == '`' || temp[i] == '~') { sendMessageLang(index, "ERROR_MSG_PASSWORD_B"); return PLUGIN_HANDLED; }
        if (!g_player_password_check[index])
        {
            g_player_password_check[index] = 1;
            copy(g_player_password_new[index], AS_PASSWORD_LENGTH, temp);
            sendMessageLang(index, "EXECUTE_REGISTER_R");
            return PLUGIN_HANDLED;
        }
        g_player_password_check[index] = 0;
        if (!equal(g_player_password_new[index], temp))
        {
            sendMessageLang(index, "ERROR_MSG_PASSWORD_R");
            return PLUGIN_HANDLED;
        }
        new ipaddress[16]; get_user_ip(index, ipaddress, charsmax(ipaddress), 1);
        for (new i = 0; i < AS_SALT_LENGTH; ++i) g_player_salt[index][i] = random_num(0, 1) ? (random_num(0, 9) + 48) : (random_num(0, 25) + 97);
#if AMXX_VERSION_NUM < 190
        new md5_new[34]; md5(temp, md5_new); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); md5(temp, md5_new); // 将密码明文转化成密码密文
#else
        new md5_new[34]; hash_string(temp, Hash_Md5, md5_new, 34); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); hash_string(temp, Hash_Md5, md5_new, 34); // 将密码明文转化成密码密文
#endif
        new name[AS_NAME_LENGTH + 1]; get_user_info(index, "name", name, charsmax(name)); as_str_to_hex(name, temp, charsmax(temp));
        new sql[1024]; formatex(sql, charsmax(sql), "insert into `as_users` (`uid`, `name`, `password`, `salt`, `regtimestamp`, `lasttimestamp`, `lastipaddress`) select ifnull(max(uid), 0) + 1, unhex('%s'), '%s', '%s', now(), now(), '%s' from `as_users` where `uid` < %u;", temp, md5_new, g_player_salt[index], ipaddress, BOT_ID_LIMIT);
        as_db_execute(sql, EXECUTE_TYPE_REGISTER, index);
        g_player_execute_check[index][0] = 1;
        sendMessageLang(index, "EXECUTE_MSG_W");
        return PLUGIN_HANDLED;
    }
#endif

#if defined USE_DISCUZ
    // 有论坛就不带修改游戏名功能
#else
    if (temp[0] == '@') // say的内容以 @ 开头表示修改游戏名
    {
        temp[0] = ' '; trim(temp);
        if (!temp[0]) { sendMessageLang(index, "ERROR_MSG_RENAME_E"); return PLUGIN_HANDLED; }
        if (31 < strlen(temp)) { sendMessageLang(index, "ERROR_MSG_RENAME_L"); return PLUGIN_HANDLED; } // 长度最多31位, 且不可包含以下字符: ^"\/
        for (new i = 0; temp[i]; ++i) if (temp[i] < 0 || temp[i] == 0x5E || temp[i] == '"' || temp[i] == 92 || temp[i] == '/') { sendMessageLang(index, "ERROR_MSG_RENAME_B"); return PLUGIN_HANDLED; }
        copy(g_player_name_new[index], AS_NAME_LENGTH, temp);
        as_str_to_hex(g_player_name_new[index], temp, charsmax(temp));
        new sql[1024]; formatex(sql, charsmax(sql), "update `as_users` set `name` = unhex('%s') where `uid` = %d;", temp, as_get_player_id(index));
        as_db_execute(sql, EXECUTE_TYPE_RENAME, index);
        g_player_execute_check[index][0] = 1;
        sendMessageLang(index, "EXECUTE_MSG_W");
        return PLUGIN_HANDLED;
    }
#endif

    if (temp[0] == '*') // say的内容以 * 开头表示修改密码
    {
        temp[0] = ' '; trim(temp);
        if (!temp[0]) { sendMessageLang(index, "ERROR_MSG_PASSWORD_E"); return PLUGIN_HANDLED; }
        if (strlen(temp) < 6 || 16 < strlen(temp)) { sendMessageLang(index, "ERROR_MSG_PASSWORD_L"); return PLUGIN_HANDLED; } // 长度6~16位, 且不可包含以下字符: ^"\/`~
        for (new i = 0; temp[i]; ++i) if (temp[i] < 0 || temp[i] == 0x5E || temp[i] == '"' || temp[i] == 92 || temp[i] == '/' || temp[i] == '`' || temp[i] == '~') { sendMessageLang(index, "ERROR_MSG_PASSWORD_B"); return PLUGIN_HANDLED; }
        if (!g_player_password_check[index])
        {
            g_player_password_check[index] = 1;
            copy(g_player_password_new[index], AS_PASSWORD_LENGTH, temp);
            sendMessageLang(index, "EXECUTE_PASSWORD_R");
            return PLUGIN_HANDLED;
        }
        g_player_password_check[index] = 0;
        if (!equal(g_player_password_new[index], temp))
        {
            sendMessageLang(index, "ERROR_MSG_PASSWORD_R");
            return PLUGIN_HANDLED;
        }
#if AMXX_VERSION_NUM < 190
        new md5_new[34]; md5(temp, md5_new); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); md5(temp, md5_new); // 将密码明文转化成密码密文
#else
        new md5_new[34]; hash_string(temp, Hash_Md5, md5_new, 34); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); hash_string(temp, Hash_Md5, md5_new, 34); // 将密码明文转化成密码密文
#endif
#if defined USE_DISCUZ
        new sql[1024]; formatex(sql, charsmax(sql), "update `%sucenter_members` set `password` = '%s' where `uid` = %d;", TABLE_PRE, md5_new, as_get_player_id(index));
#else
        new sql[1024]; formatex(sql, charsmax(sql), "update `as_users` set `password` = '%s' where `uid` = %d;", md5_new, as_get_player_id(index));
#endif
        as_db_execute(sql, EXECUTE_TYPE_PASSWORD, index);
        g_player_execute_check[index][0] = 1;
        sendMessageLang(index, "EXECUTE_MSG_W");
        return PLUGIN_HANDLED;
    }

    if (temp[0] == '!') // say的内容以 ! 开头表示修改签名
    {
        temp[0] = ' '; trim(temp);
        copy(g_player_signature_new[index], AS_SIGNATURE_LENGTH, temp);
        as_str_to_hex(g_player_signature_new[index], temp, charsmax(temp));
#if defined USE_DISCUZ
        new sql[1024]; formatex(sql, charsmax(sql), "update `%scommon_member_profile` set `%s` = unhex('%s') where `uid` = %d;", TABLE_PRE, FIELD_SIGNATURE, temp, as_get_player_id(index));
#else
        new sql[1024]; formatex(sql, charsmax(sql), "update `as_users` set `signature` = unhex('%s') where `uid` = %d;", temp, as_get_player_id(index));
#endif
        as_db_execute(sql, EXECUTE_TYPE_SIGNATURE, index);
        g_player_execute_check[index][0] = 1;
        sendMessageLang(index, "EXECUTE_MSG_W");
        return PLUGIN_HANDLED;
    }

#if defined MESSAGE_DEMO
    if (strcmp(temp, "console", true) == 0 || strcmp(temp, "/console", true) == 0) // 演示从网页到游戏的数据传输
    {
        new title[32], motd[1024];
        get_user_name(index, title, charsmax(title));
        new url[256];
        get_cvar_string("as_motd_url", url, charsmax(url));
        new server[32];
        get_user_ip(0, server, charsmax(server));
        new id = as_get_player_id(index);
        if (id)
            formatex(motd, charsmax(motd), "<html><meta http-equiv='refresh' content='0;url=%sconsole.php?server=%s&index=%d&uid=%d'></html>", url, server, index, id);
        else
            formatex(motd, charsmax(motd), "<html><meta http-equiv='refresh' content='0;url=%sregister.php?server=%s&index=%d'></html>", url, server, index);
        show_motd(index, motd, title);
        return PLUGIN_HANDLED;
    }
#endif
    return PLUGIN_CONTINUE;
}

public as_set_player_name(index, buffer)
{
    if (is_user_connected(index))
    {
        new name[AS_NAME_LENGTH + 1];
        get_user_info(index, "name", name, charsmax(name));
        if (!equal(name, g_player_name[index]))
        {
            set_user_info(index, "name", g_player_name[index]);
            sendMessageLang(index, "ERROR_MSG_C");
            return FMRES_SUPERCEDE; // 禁止玩家改名
        }
    }
    return FMRES_IGNORED;
}

// 玩家有3+1种登录方式可以选择(论坛再多1种), 最简单的方式是仅设置密码就可以登录了!
public as_query_player_id(index)
{
    if (!is_user_connected(index))
        return 0;

    if (is_user_bot(index)) // 如果是机器人, 自动登录
    {
        if (!g_bot_random_id) g_bot_random_id = BOT_ID_LIMIT + random_num(1, 9968); // 当前设置最多保存 9968 + 32 = 10000 个机器人的数据, 请根据需要自行修改68
        return g_bot_random_id + index; // 如不保存机器人的数据改成 return 0;
    }

    if (!dbc) return 0;

    new authid[32]; get_user_authid(index, authid, charsmax(authid));       // 3选1 -> 获取 用户STEAM账号, STEAM用户自动登录
    new uid[32]; get_user_info(index, SETINFO_UID,  uid, charsmax(uid));    // 3选1 -> 获取 用户编号, 玩家可以设置用户编号进行登录(setinfo uid 1)
    new user[32]; get_user_info(index, SETINFO_USER, user, charsmax(user)); // 再+1 -> 获取 论坛账号, 玩家可以设置论坛账号进行登录(setinfo user admin)
    new name[32]; get_user_info(index, "name", name, charsmax(name));       // 3选1 -> 获取 游戏名称, 如果玩家上面2个都没有设置的话, 但设置了下面的登录密码, 就使用游戏名登录
    new pass[32]; get_user_info(index, SETINFO_PASS, pass, charsmax(pass)); // 密码 -> 获取 密码明文, 玩家设置自己的登录密码(setinfo pass 123456)
    for (new i = 0; name[i]; ++i) { if (name[i] == -17 && name[i + 1] == -93 && name[i + 2] == -75) name[i] = name[i + 1] = name[i + 2] = 0; } // 过滤玩家游戏名中的特殊字符
    as_log("[AS] index[%d] authid[%s] uid[%s] user[%s] name[%s] pass[%s]", index, authid, uid, user, name, pass);

    // replace_all(authid, charsmax(authid), "VALVE_", "STEAM_"); // 强行让所有玩家标记为正版玩家, 自动注册, 自动登录, 不推荐
    if (!pass[0] && !(contain(authid, "STEAM_") == 0 && !equal(authid, "STEAM_ID_LAN"))) // 如果玩家未设置登录密码且不是STEAM正版用户, 则视为未注册玩家, 不予登录
    {
        as_log("[AS] Player login: index[%d] authid[%s] null password", index, authid);
        sendMessageLang(index, "LOGIN_MSG_U");
        return 0;
    }

    new temp[128], sql[1024];
    if (contain(authid, "STEAM_") == 0 && !equal(authid, "STEAM_ID_LAN")) // 如果玩家是STEAM正版用户, 则自动登录
    {
#if defined USE_DISCUZ
        formatex(sql, charsmax(sql), "select `a`.`uid`, hex(`a`.`username`) as `user`, `a`.`password`, `a`.`salt`, hex(`b`.`%s`) as `name`, `b`.`%s` as `flags`, hex(`b`.`%s`) as `signature` from `%sucenter_members` as `a` join `%scommon_member_profile` as `b` on (`a`.`uid` = `b`.`uid`) where `b`.`%s` = '%s';", FIELD_NAME, FIELD_FLAGS, FIELD_SIGNATURE, TABLE_PRE, TABLE_PRE, FIELD_STEAMID, authid);
#else
        formatex(sql, charsmax(sql), "select `uid`, '' as `user`, `password`, `salt`, hex(`name`) as `name`, `flags`, hex(`signature`) as `signature` from `as_users` where `authid` = '%s';", authid);
#endif
    }
    else if (uid[0]) // 如果玩家设置了用户编号, 则通过用户编号和密码登录
    {
#if defined USE_DISCUZ
        formatex(sql, charsmax(sql), "select `a`.`uid`, hex(`a`.`username`) as `user`, `a`.`password`, `a`.`salt`, hex(`b`.`%s`) as `name`, `b`.`%s` as `flags`, hex(`b`.`%s`) as `signature` from `%sucenter_members` as `a` join `%scommon_member_profile` as `b` on (`a`.`uid` = `b`.`uid`) where `a`.`uid` = %s and `b`.`uid` = %s;", FIELD_NAME, FIELD_FLAGS, FIELD_SIGNATURE, TABLE_PRE, TABLE_PRE, uid, uid);
#else
        formatex(sql, charsmax(sql), "select `uid`, '' as `user`, `password`, `salt`, hex(`name`) as `name`, `flags`, hex(`signature`) as `signature` from `as_users` where `uid` = %s;", uid);
#endif
    }
#if defined USE_DISCUZ
    else if(user[0]) // 如果玩家设置了论坛账号, 则通过论坛账号和密码登录
    {
        as_str_to_hex(user, temp, charsmax(temp));
        formatex(sql, charsmax(sql), "select `a`.`uid`, hex(`a`.`username`) as `user`, `a`.`password`, `a`.`salt`, hex(`b`.`%s`) as `name`, `b`.`%s` as `flags`, hex(`b`.`%s`) as `signature` from `%sucenter_members` as `a` join `%scommon_member_profile` as `b` on (`a`.`uid` = `b`.`uid`) where `a`.`username` = unhex('%s');", FIELD_NAME, FIELD_FLAGS, FIELD_SIGNATURE, TABLE_PRE, TABLE_PRE, temp);
    }
#endif
    else // if(!uid[0] && !user[0]) // 如果玩家没有设置用户编号或者论坛账号, 则使用游戏名和密码登录
    {
        as_str_to_hex(name, temp, charsmax(temp));
#if defined USE_DISCUZ
        formatex(sql, charsmax(sql), "select `a`.`uid`, hex(`a`.`username`) as `user`, `a`.`password`, `a`.`salt`, hex(`b`.`%s`) as `name`, `b`.`%s` as `flags`, hex(`b`.`%s`) as `signature` from `%sucenter_members` as `a` join `%scommon_member_profile` as `b` on (`a`.`uid` = `b`.`uid`) where `b`.`%s` = unhex('%s');", FIELD_NAME, FIELD_FLAGS, FIELD_SIGNATURE, TABLE_PRE, TABLE_PRE, FIELD_NAME, temp);
#else
        formatex(sql, charsmax(sql), "select `uid`, '' as `user`, `password`, `salt`, hex(`name`) as `name`, `flags`, hex(`signature`) as `signature` from `as_users` where `name` = unhex('%s');", temp);
#endif
    }

    new Handle:query;
    for (new i = 0; i < 2; ++i)
    {
        query = SQL_PrepareQuery(dbc, sql);
        if (!SQL_Execute(query))
        {
            SQL_QueryError(query, temp, charsmax(temp));
            SQL_FreeHandle(query);
            if (i == 0 && (contain(temp, "Lost connection") == 0 || contain(temp, "Can't connect") == 0))
            {
                new errcode, errinfo[128];
                SQL_FreeHandle(dbc);
                dbc = SQL_Connect(dbt, errcode, errinfo, charsmax(errinfo));
                if (dbc)
                {
                    as_log("[AS] database reconnected");
                    continue;
                }
                else
                {
                    as_log("[AS] failed to reconnect to database: [%d] [%s]^n%s", errcode, errinfo, sql);
                    return 0;
                }
            }
            as_log("[AS] query user failed: [%d] [%s]^n%s", index, temp, sql);
            return 0;
        }
        break;
    }

    if (SQL_MoreResults(query))
    {
        new flags_db[26 + 1], md5_db[34], md5_local[34];
        new id = SQL_ReadResult(query, SQL_FieldNameToNum(query, "uid"));                                                                                         // 获取玩家用户编号
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "user"), temp, charsmax(temp)); as_hex_to_str(temp, user, charsmax(user));                                // 获取玩家论坛账号
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "password"), md5_db, charsmax(md5_db));                                                                   // 获取玩家密码密文
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "salt"), g_player_salt[index], AS_SALT_LENGTH);                                                           // 获取加密参数
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "name"), temp, charsmax(temp)); temp[AS_NAME_LENGTH * 2] = 0; as_hex_to_str(temp, g_player_name[index], AS_NAME_LENGTH);                // 获取玩家游戏名称
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "flags"), flags_db, 26);                                                                                  // 获取玩家游戏权限
        SQL_ReadResult(query, SQL_FieldNameToNum(query, "signature"), temp, charsmax(temp)); temp[AS_SIGNATURE_LENGTH * 2] = 0; as_hex_to_str(temp, g_player_signature[index], AS_SIGNATURE_LENGTH); // 获取玩家游戏签名
        SQL_FreeHandle(query);

        user[as_valid_utf8_length(user)] = 0;
        g_player_name[index][as_valid_utf8_length(g_player_name[index])] = 0;
        g_player_signature[index][as_valid_utf8_length(g_player_signature[index])] = 0;

        if (contain(authid, "STEAM_") == 0 && !equal(authid, "STEAM_ID_LAN")) // 如果玩家是STEAM正版用户
        {
            if (g_player_name[index][0] && !equal(name, g_player_name[index])) set_user_info(index, "name", ""); // 清空名字让回调触发更名操作
            as_log("[AS] player login: ok!^n     index[%d] authid[%s] uid[%d] user[%s] name[%s]->[%s] flags[%s] signature[%s]", index, authid, id, user, name, g_player_name[index], flags_db, g_player_signature[index]);
            sendMessageLang(index, "LOGIN_MSG_S");
            remove_user_flags(index); set_user_flags(index, read_flags(flags_db)); // 设置玩家游戏权限
            return id;
        }
        else // 校验密码
        {
#if AMXX_VERSION_NUM < 190
            md5(pass, md5_local); formatex(temp, charsmax(temp), "%s%s", md5_local, g_player_salt[index]); md5(temp, md5_local); // 将密码明文转化成密码密文
#else
            hash_string(pass, Hash_Md5, md5_local, 34); formatex(temp, charsmax(temp), "%s%s", md5_local, g_player_salt[index]); hash_string(temp, Hash_Md5, md5_local, 34); // 将密码明文转化成密码密文
#endif
            if(equali(md5_db, md5_local)) // 比较密码密文
            {
                if (!uid[0]) client_cmd(index, "setinfo %s %d", SETINFO_UID, id); // 设置uid, 下次采用更可靠的该方式登录
                if (g_player_name[index][0] && !equal(name, g_player_name[index])) set_user_info(index, "name", ""); // 清空名字让回调触发更名操作
                as_log("[AS] player login: ok!^n     index[%d] authid[%s] uid[%d] user[%s] name[%s]->[%s] flags[%s] signature[%s]", index, authid, id, user, name, g_player_name[index], flags_db, g_player_signature[index]);
                sendMessageLang(index, "LOGIN_MSG_L");
                remove_user_flags(index); set_user_flags(index, read_flags(flags_db)); // 设置玩家游戏权限
                return id;
            }
            else
            {
                server_cmd("kick #%d %L", get_user_userid(index), LANG_PLAYER, "ERROR_MSG_PASSWORD_W");
                as_log("[AS] player login: kicked!^n     index[%d] authid[%s] uid[%d] user[%s] name[%s] password error", index, authid, id, user, name);
            }
        }
    }
    else
    {
        SQL_FreeHandle(query);

#if defined USE_DISCUZ
        as_log("[AS] Player login: index[%d] unregistered", index); // 有论坛就不带STEAM正版用户自动注册功能, 需注册时手动关联STEAMID
#else
        if (contain(authid, "STEAM_") == 0 && !equal(authid, "STEAM_ID_LAN")) // 如果玩家是STEAM正版用户, 自动注册
        {
            new ipaddress[16]; get_user_ip(index, ipaddress, charsmax(ipaddress), 1);
            for (new i = 0; i < AS_SALT_LENGTH; ++i) g_player_salt[index][i] = random_num(0, 1) ? (random_num(0, 9) + 48) : (random_num(0, 25) + 97);
            for (new i = 0; i < 16; ++i) temp[i] = random_num(0, 1) ? (random_num(0, 9) + 48) : (random_num(0, 25) + 97); temp[16] = 0; // 默认随机密码
#if AMXX_VERSION_NUM < 190
            new md5_new[34]; md5(temp, md5_new); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); md5(temp, md5_new);
#else
            new md5_new[34]; hash_string(temp, Hash_Md5, md5_new, 34); formatex(temp, charsmax(temp), "%s%s", md5_new, g_player_salt[index]); hash_string(temp, Hash_Md5, md5_new, 34);
#endif
            as_str_to_hex(name, temp, charsmax(temp));
            new sql[1024]; formatex(sql, charsmax(sql), "insert into `as_users` (`uid`, `name`, `password`, `salt`, `authid`, `regtimestamp`, `lasttimestamp`, `lastipaddress`) select ifnull(max(uid), 0) + 1, unhex('%s'), '%s', '%s', '%s', now(), now(), '%s' from `as_users` where `uid` < %u;", temp, md5_new, g_player_salt[index], authid, ipaddress, BOT_ID_LIMIT);
            as_db_execute(sql, EXECUTE_TYPE_REGISTER, index);
            g_player_execute_check[index][0] = 1;
            sendMessageLang(index, "EXECUTE_MSG_S");
        }
        else
        {
            as_log("[AS] Player login: index[%d] unregistered", index);
        }
#endif
    }

    return 0;
}

public as_db_execute(const sql[], type, index)
{
    if (!dbc) return;
    new data[3];
    data[0] = type;
    data[1] = index;
    data[2] = as_get_player_id(index);
    SQL_ThreadQuery(dbt, "as_db_execute_handle", sql, data, sizeof(data));
}

public as_db_execute_handle(failstate, Handle:query, error[], errnum, data[], size, Float:queuetime)
{
    new type = data[0];
    new index = data[1];
    new id = data[2];
    if (!is_user_connected(index) || as_get_player_id(index) != id) return;

    g_player_execute_check[index][0] = 0;
    if (failstate)
    {
        new temp[1024];
        SQL_GetQueryString(query, temp, charsmax(temp));
        as_log("[AS] execute failed: [%d] [%s]^n%s", errnum, error, temp);
    }
    else
    {
        g_player_execute_check[index][1]++;
    }
    switch (type)
    {
        case EXECUTE_TYPE_REGISTER:
        {
            sendMessageLang(index, failstate ? (errnum == 1062 ? "EXECUTE_REGISTER_D" : "EXECUTE_REGISTER_E") : "EXECUTE_REGISTER_S");
            if (!failstate)
            {
                client_cmd(index, "setinfo %s ^"^"", SETINFO_UID); // 清空原uid(不管之前有无设置), 在 login 后将设置回来
                client_cmd(index, "setinfo %s ^"%s^"", SETINFO_PASS, g_player_password_new[index]);
                client_cmd(index, "say /login");
            }
        }
        case EXECUTE_TYPE_RENAME:
        {
            sendMessageLang(index, failstate ? (errnum == 1062 ? "EXECUTE_RENAME_D" : "EXECUTE_RENAME_E") : "EXECUTE_RENAME_S");
            if (!failstate)
            {
                copy(g_player_name[index], AS_NAME_LENGTH, g_player_name_new[index]);
                set_user_info(index, "name", ""); // 清空名字让回调触发更名操作
            }
        }
        case EXECUTE_TYPE_PASSWORD:
        {
            sendMessageLang(index, failstate ? "EXECUTE_PASSWORD_E" : "EXECUTE_PASSWORD_S");
            if (!failstate) client_cmd(index, "setinfo %s ^"%s^"", SETINFO_PASS, g_player_password_new[index]);
        }
        case EXECUTE_TYPE_SIGNATURE:
        {
            sendMessageLang(index, failstate ? "EXECUTE_SIGNATURE_E" : "EXECUTE_SIGNATURE_S");
            if (!failstate) copy(g_player_signature[index], AS_SIGNATURE_LENGTH, g_player_signature_new[index]);
        }
    }
}

public as_give_player_weapon(index, wpnindex, bool:clip, bool:ammo)
{
    if (!(1 <= index && index <= AS_MAX_PLAYERS)) return;
    if (!(1 <= wpnindex && wpnindex <= AS_MAX_WEAPONS)) return;
    if (!is_user_connected(index)) return;
    if (wpnindex == 2 || wpnindex == 31 || wpnindex == 32) return;

    new weaponName[32];
    get_weaponname(wpnindex, weaponName, charsmax(weaponName));

    if (wpnindex == CSW_HEGRENADE || wpnindex == CSW_SMOKEGRENADE || wpnindex == CSW_FLASHBANG)
    {
        give_item(index, weaponName);
        if (wpnindex == CSW_FLASHBANG) give_item(index, weaponName);
        return;
    }

    new entity = find_ent_by_owner(-1, weaponName, index);
    if (entity)
    {
        new clipSize = g_weapon_info[wpnindex][WEAPON_CLIP_SIZE];
        if (clipSize) cs_set_weapon_ammo(entity, clipSize);
    }
    else
    {
        give_item(index, weaponName);
    }

    new clipMax = g_weapon_info[wpnindex][WEAPON_CLIP_MAX];
    for (new i = 1; i <= clipMax; ++i)
        give_item(index, g_ammo_name[g_weapon_info[wpnindex][WEAPON_AMMO_TYPE]]);
}
//////////////////////////////// 从这里开始, 以下部分是as模块提供的forward, 具体见as.inc
public as_recv_message(const message[])
{
    // server_print("   message: [%s]", message);
    new indexstr[128], idstr[128], type[128], argv[10][128];
    new argc = parse(message, indexstr, charsmax(indexstr), idstr, charsmax(idstr), type, charsmax(type), argv[0], 127, argv[1], 127, argv[2], 127, argv[3], 127, argv[4], 127, argv[5], 127, argv[6], 127, argv[7], 127, argv[8], 127, argv[9], 127);
    if (argc < 3) return; argc -= 3;

    new index = str_to_num(indexstr);
    new id = str_to_num(idstr);

    if (equal(type, "kick")) // 他服过来的踢人请求
    {
        if (!id || id > BOT_ID_LIMIT) return;
        for (new i = 1; i <= AS_MAX_PLAYERS; ++i)
            if (is_user_connected(i) && as_get_player_id(i) == id && g_player_timestamp[i] < index)
                server_cmd("kick #%d %s %s %s %s %s %s %s %s %s %s", get_user_userid(i), argv[0], argv[1], argv[2], argv[3], argv[4], argv[5], argv[6], argv[7], argv[8], argv[9]);
        return;
    }

    if (equal(type, "register")) // 页面过来的注册请求
    {
        if (!(1 <= index && index <= AS_MAX_PLAYERS) || !id) return; // 检查index和id是否有效
        if (!is_user_connected(index)) return;
        new password[AS_PASSWORD_LENGTH + 1];
        for (new i = 0; message[i]; ++i) if (message[i] == '#') { copy(password, AS_PASSWORD_LENGTH, message[i + 1]); break; }
        if (!password[0]) return;
        client_cmd(index, "setinfo %s %d", SETINFO_UID, id);
        client_cmd(index, "setinfo %s ^"%s^"", SETINFO_PASS, password);
        client_cmd(index, "say /login");
        return;
    }

#if defined MESSAGE_DEMO
    // 演示从网页到游戏的数据传输
    if (!(1 <= index && index <= AS_MAX_PLAYERS) || !id) return; // 检查index和id是否有效
    if (as_get_player_id(index) != id) return; // 检查是否同一个玩家
    if (!is_user_connected(index)) return;

    if (equal(type, "health") && argc == 1)
    {
        new health = clamp(str_to_num(argv[0]), 0, 100);
        set_user_health(index, health);
    }

    if (equal(type, "money") && argc == 1)
    {
        new money = str_to_num(argv[0]);
        if (!money) return;
        new oldmoney = cs_get_user_money(index);
        new newmoney = clamp(oldmoney + money, 0, 16000);
        if (oldmoney != newmoney) cs_set_user_money(index, newmoney);
    }

    if (equal(type, "ammo") && argc == 0)
    {
        new wpnindex, weapons[AS_MAX_WEAPONS], count = 0, weaponName[64];
        get_user_weapons(index, weapons, count);
        for (new i = 0; i < count; ++i)
        {
            wpnindex = weapons[i];
            get_weaponname(wpnindex, weaponName, charsmax(weaponName));
            new entity = find_ent_by_owner(-1, weaponName, index);
            if (entity)
            {
                new clipSize = g_weapon_info[wpnindex][WEAPON_CLIP_SIZE];
                if (clipSize) cs_set_weapon_ammo(entity, clipSize);
            }
            new clipMax = g_weapon_info[wpnindex][WEAPON_CLIP_MAX];
            for (new j = 1; j <= clipMax; ++j)
                give_item(index, g_ammo_name[g_weapon_info[wpnindex][WEAPON_AMMO_TYPE]]);
        }
    }

    if (equal(type, "weapon") && argc == 1)
    {
        new wpnindex = str_to_num(argv[0]);
        if (!(1 <= wpnindex && wpnindex <= AS_MAX_WEAPONS)) return;
        as_give_player_weapon(index, wpnindex, true, true);
    }

    if (equal(type, "armor") && argc == 2)
    {
        new armor = clamp(str_to_num(argv[0]), 0, 100);
        new armorType = clamp(str_to_num(argv[1]), 0, 2);
        cs_set_user_armor(index, armor, CsArmorType:armorType);
    }

    if (equal(type, "defuse") && argc == 0)
    {
        if (!cs_get_user_defuse(index)) cs_set_user_defuse(index);
    }

    if (equal(type, "nvg") && argc == 0)
    {
        if (!cs_get_user_nvg(index)) cs_set_user_nvg(index);
    }

    if (equal(type, "shield") && argc == 0)
    {
        if (!cs_get_user_shield(index) && !cs_get_user_hasprim(index))
            give_item(index, "weapon_shield");
    }
#endif
}

public as_player_enter(index)
{
    // server_print("     enter: %d", index);
    g_player_password_check[index] = 0;
    g_player_login_check[index][0] = g_player_login_check[index][1] = 0;
    g_player_execute_check[index][0] = g_player_execute_check[index][1] = 0;

    g_player_salt[index][0] = 0;
    get_user_info(index, "name", g_player_name[index], AS_NAME_LENGTH);
    g_player_name_new[index][0] = 0;
    g_player_password_new[index][0] = 0;
    g_player_location[index][0] = 0;
    g_player_signature[index][0] = 0;
    g_player_signature_new[index][0] = 0;
    g_player_timestamp[index] = 0;

    new sIPAddress[16];
    new sCountry[64];
    new sArea[256];
    get_user_ip(index, sIPAddress, charsmax(sIPAddress), 1);
    if (!sIPAddress[0]) copy(sIPAddress, charsmax(sIPAddress), "127.0.0.1");
    ipseeker2(sIPAddress, sCountry, charsmax(sCountry), 1, sArea, charsmax(sArea), 1);
    if (containi(sCountry, "CZ88.NET") != -1) copy(sCountry, charsmax(sCountry), "未知地址");
    if (containi(sArea, "CZ88.NET") != -1) copy(sArea, charsmax(sArea), "");
    formatex(g_player_location[index], AS_LOCATION_LENGTH, "%s%s", sCountry, sArea);

    // as_player_login(index);
    set_task(1.0, "task_player_login", index);
}

public task_player_login(index)
{
    as_player_login(index);
}

public as_player_leave(index)
{
    // server_print("     leave: %d", index);
}

public as_mode_changed(AS_MODE_TYPE:oldmode, AS_MODE_TYPE:newmode)
{
    // server_print("      mode: %d -> %d", oldmode, newmode);
    g_mode = newmode;
}

public as_player_stats_synced(AS_MODE_TYPE:mode, index)
{
    // server_print("     stats synced: %d %d", mode, index);
    if (!index) return;
    new modestr[8]; formatex(modestr, charsmax(modestr), "MODE_%d", mode);
    new modename[128]; formatex(modename, charsmax(modename), "%L", LANG_PLAYER, modestr); trim(modename);
    new buffer[1024]; formatex(buffer, charsmax(buffer), "%L", LANG_PLAYER, "SYNC_MSG_STATS"); trim(buffer);
    if (buffer[0] == '-') return;
    if (contain(buffer, "!MODENAME") != -1) replace_all(buffer, charsmax(buffer), "!MODENAME", modename);
    sendMessage(index, buffer, charsmax(buffer));
}

public as_player_rankings_synced(AS_MODE_TYPE:mode, index)
{
    // server_print("     ranking synced: %d %d -> %d %d %d %d %d %d", mode, index,
    //     as_get_player_ranking(index, mode, AS_RANKING_MAP_SCORE),
    //     as_get_player_ranking(index, mode, AS_RANKING_MAP_RATING),
    //     as_get_player_ranking(index, mode, AS_RANKING_MAP_RWS),
    //     as_get_player_ranking(index, mode, AS_RANKING_ALL_SCORE),
    //     as_get_player_ranking(index, mode, AS_RANKING_ALL_RATING),
    //     as_get_player_ranking(index, mode, AS_RANKING_ALL_RWS));
    if (!index) return;
    new modestr[8]; formatex(modestr, charsmax(modestr), "MODE_%d", mode);
    new modename[128]; formatex(modename, charsmax(modename), "%L", LANG_PLAYER, modestr); trim(modename);
    new buffer[1024]; formatex(buffer, charsmax(buffer), "%L", LANG_PLAYER, "SYNC_MSG_RANKINGS"); trim(buffer);
    if (buffer[0] == '-') return;
    if (contain(buffer, "!MODENAME") != -1) replace_all(buffer, charsmax(buffer), "!MODENAME", modename);
    sendMessage(index, buffer, charsmax(buffer));
}

public as_player_shoot(attacker, wpnindex, clip, ammo)
{
    // server_print("     shoot: %d %d %d %d", attacker, wpnindex, clip, ammo);
}

public as_player_attack(attacker, awpnindex, victim, vwpnindex, aiming, damage, damage_real, kflag, wflag, AS_TARGET_TYPE:tflag, Float:distance)
{
    // if (kflag) server_print("    attack: %d %d %d %d %d %d %d %s %s %s %f", attacker, awpnindex, victim, vwpnindex, aiming, damage, damage_real, (kflag ? "K" : "_"), (wflag ? "W" : "_"), (tflag ? "T" : "_"), distance);
}

public as_player_assist(killer, victim, assistant, assist_damage, assist_flashbang)
{
    // server_print("    assist: %d %d %d %d %d", killer, victim, assistant, assist_damage, assist_flashbang);
}

public as_bomb_planting(planter)
{
    // server_print("  planting: %d", planter);
}

public as_bomb_planted(planter)
{
    // server_print("   planted: %d", planter);
}

public as_bomb_explode(planter)
{
    // server_print("   explode: %d", planter);
}

public as_bomb_defusing(defuser)
{
    // server_print("  defusing: %d", defuser);
}

public as_bomb_defused(defuser)
{
    // server_print("   defused: %d", defuser);
}

public as_round_first_blood(killer, victim)
{
    // server_print("firstblood: %d %d", killer, victim);
}

public as_round_last_blood(killer, victim)
{
    // server_print(" lastblood: %d %d", killer, victim);
}

public as_round_result(result, mvp)
{
    // server_print("    result: %d %d", result, mvp);
}
//////////////////////////////// 到这里结束, 以上部分是as模块提供的forward, 具体见as.inc
public Float:native_calc_score(plugin_id, argc)
{
    if (argc < 3) return 1.0;
    new ibombs[AS_BOMB_ENUM_COUNT], irounds[AS_ROUND_ENUM_COUNT], iweapons[AS_WEAPON_ENUM_COUNT];
    get_array(1, ibombs, AS_BOMB_ENUM_COUNT);
    get_array(2, irounds, AS_ROUND_ENUM_COUNT);
    get_array(3, iweapons, AS_WEAPON_ENUM_COUNT);
    return ibombs[AS_BOMB_PLANTED] * 3.0 + ibombs[AS_BOMB_DEFUSED] * 5.0 + irounds[AS_ROUND_FIRST_KILL] * 3.0 + irounds[AS_ROUND_TIME] * 0.001 + iweapons[AS_WEAPON_KILL] + iweapons[AS_WEAPON_KILL_HS] + iweapons[AS_WEAPON_KILL_WS] * 0.5 + iweapons[AS_WEAPON_KILL_HWS] * 0.5;
}

public Float:native_calc_rating(plugin_id, argc)
{
    if (argc < 3) return 1.0;
    new ibombs[AS_BOMB_ENUM_COUNT], irounds[AS_ROUND_ENUM_COUNT], iweapons[AS_WEAPON_ENUM_COUNT];
    get_array(1, ibombs, AS_BOMB_ENUM_COUNT);
    get_array(2, irounds, AS_ROUND_ENUM_COUNT);
    get_array(3, iweapons, AS_WEAPON_ENUM_COUNT);
    if ((irounds[AS_ROUND_T] + irounds[AS_ROUND_CT]) == 0 || (irounds[AS_ROUND_T] + irounds[AS_ROUND_CT]) < iweapons[AS_WEAPON_DEATH]) return 1.0;
    return (iweapons[AS_WEAPON_KILL] / 0.679 + 0.7 * (irounds[AS_ROUND_T] + irounds[AS_ROUND_CT] - iweapons[AS_WEAPON_DEATH]) / 0.317 + (irounds[AS_ROUND_KILL_1] + 4 * irounds[AS_ROUND_KILL_2] + 9 * irounds[AS_ROUND_KILL_3] + 16 * irounds[AS_ROUND_KILL_4] + 25 * irounds[AS_ROUND_KILL_5]) / 1.277) / (irounds[AS_ROUND_T] + irounds[AS_ROUND_CT]) / 2.7;
}

public Float:native_calc_rws_0(index, team, kast, kills, deaths, assists, assists_by_damage, assists_by_flashbang, damages, infos[AS_RWS_ENUM_COUNT])
{
    new result = infos[AS_RWS_RESULT];                             // 1:恐怖分子 2:反恐精英 4:平局 5:VIP被杀了 9:VIP未能逃离 10:VIP成功逃离 17:人质未能解救 18:人质成功解救 33:C4成功爆破 34:C4成功拆除 66:C4未能爆破
    // new t_alive = infos[AS_RWS_T_ALIVE];                           // 恐怖分子存活数
    new t_total = infos[AS_RWS_T_TOTAL];                           // 恐怖分子总人数
    new t_damages = infos[AS_RWS_T_DAMAGES];                       // 恐怖分子总伤害
    // new ct_alive = infos[AS_RWS_CT_ALIVE];                         // 反恐精英存活数
    new ct_total = infos[AS_RWS_CT_TOTAL];                         // 反恐精英总人数
    new ct_damages = infos[AS_RWS_CT_DAMAGES];                     // 反恐精英总伤害
    // new firstkill = infos[AS_RWS_FIRST_KILL];                      // 最先杀敌index
    // new firstdeath = infos[AS_RWS_FIRST_DEATH];                    // 最先阵亡index
    // new lastkill = infos[AS_RWS_LAST_KILL];                        // 最后杀敌index
    // new lastdeath = infos[AS_RWS_LAST_DEATH];                      // 最后阵亡index
    // new mvp = infos[AS_RWS_MVP];                                   // MVP的index
    new planter = infos[AS_RWS_PLANTER];                           // C4安包员index
    new defuser = infos[AS_RWS_DEFUSER];                           // C4拆包员index
    // new vip = infos[AS_RWS_VIP];                                   // VIP的index
    // new vip_killer = infos[AS_RWS_VIP_KILLER];                     // VIP杀手的index
    // new last_hostage_rescuer = infos[AS_RWS_LAST_HOSTAGE_RESCUER]; // 最后人质拯救者的index
    // as_log("native_calc_rws: %d %d %d %d %d %d %d %d %d", index, team, kast, kills, deaths, assists, assists_by_damage, assists_by_flashbang, damages);
    // as_log("native_calc_rws: %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d %d", result, t_alive, t_total, t_damages, ct_alive, ct_total, ct_damages, firstkill, firstdeath, lastkill, lastdeath, mvp, planter, defuser, vip, vip_killer, last_hostage_rescuer);

    new Float:win_shares = 100.0;
    new win_team = result & 3;
    if (team != win_team) return 0.0; // 失败队伍无得分
    if (g_mode != AS_MODE_CASUAL && g_mode != AS_MODE_COMPETITIVE) return 0.0; // 暂时只支持休闲模式和竞技模式

    new Float:scale = 1.0; // 缩放比例, 让较多人数的混战服数据更合理, 比赛服是5人分配100点, 混战服的话按对方人数适当缩放下
    if (g_mode == AS_MODE_CASUAL) scale = ((win_team == 1 ? ct_total : t_total) / 5.0);

    new win_team_damages = (win_team == 1 ? t_damages : ct_damages);
    if (win_team_damages == 0)
    {
        if (result == 33) return t_total ? (((index == planter ? 30.0 : 0.0) + (win_shares - 30.0) / t_total) * scale) : 0.0;
        if (result == 34) return ct_total ? (((index == defuser ? 30.0 : 0.0) + (win_shares - 30.0) / ct_total) * scale) : 0.0;
        return ((win_shares / (win_team == 1 ? t_total : ct_total)) * scale);
    }

    if (result == 33) return (((index == planter ? 30.0 : 0.0) + (win_shares - 30.0) * damages / win_team_damages) * scale);
    if (result == 34) return (((index == defuser ? 30.0 : 0.0) + (win_shares - 30.0) * damages / win_team_damages) * scale);
    return ((win_shares * damages / win_team_damages) * scale);
}

public Float:native_calc_rws(plugin_id, argc)
{
    if (argc < 10) return 0.0;

    new index = get_param(1);                // 玩家index
    new team = get_param(2);                 // 玩家队伍
    new kast = get_param(3);                 // 玩家非白给(Kill、Assist、Survived、Traded)
    new kills = get_param(4);                // 本回合杀敌数
    new deaths = get_param(5);               // 本回合阵亡数
    new assists = get_param(6);              // 本回合助攻数
    new assists_by_damage = get_param(7);    // 本回合助攻数(伤害)
    new assists_by_flashbang = get_param(8); // 本回合助攻数(闪光弹)
    new damages = get_param(9);              // 本回合伤害

    new infos[AS_RWS_ENUM_COUNT];
    get_array(10, infos, AS_RWS_ENUM_COUNT);

    new Float:rws = native_calc_rws_0(index, team, kast, kills, deaths, assists, assists_by_damage, assists_by_flashbang, damages, infos);
    // as_log("native_calc_rws: %d %s %f", g_mode, g_player_name[index], rws);
    return rws;
}

public native_set_kill_sound(plugin_id, argc) // 和 plugin_precache 对应
{
    if (argc < 11) return 0;
    new len = 0;
    new filename[1024];
    new maxlen = get_param(2);
    if (maxlen <= 0) return 0;
    // new index = get_param(3);             // 玩家index
    new team = get_param(4);              // 玩家队伍
    new weapon = get_param(5);            // 当前武器
    // new weapon_kill_count = get_param(6); // 当前武器杀敌数
    // new all_kill_count = get_param(7);    // 所有武器杀敌数
    new multi_kill_count = get_param(8);  // 连续杀敌数
    new headshot = get_param(9);          // 是否爆头
    // new wallshot = get_param(10);         // 是否穿墙
    new revenge = get_param(11);          // 是否复仇

    if (revenge)
        len = formatex(filename, charsmax(filename), "sound/as/v1/%s/kill_revenge.wav", (team == 1 ? "t" : "ct"));
    else if (weapon == CSW_HEGRENADE)
        len = formatex(filename, charsmax(filename), "sound/as/v1/%s/kill_hegrenade.wav", (team == 1 ? "t" : "ct"));
    else if (weapon == CSW_KNIFE)
        len = formatex(filename, charsmax(filename), "sound/as/v1/%s/kill_knife.wav", (team == 1 ? "t" : "ct"));
    else if (headshot)
        len = formatex(filename, charsmax(filename), "sound/as/v1/%s/kill_headshot.wav", (team == 1 ? "t" : "ct"));
    else
        len = formatex(filename, charsmax(filename), "sound/as/v1/%s/multi_kill_%d.wav", (team == 1 ? "t" : "ct"), min(multi_kill_count, 8));

    return set_string(1, filename, min(len, maxlen));
}

public native_set_kill_badge(plugin_id, argc) // 和 plugin_precache 对应
{
    if (argc < 11) return 0;
    new len = 0;
    new filename[1024];
    new maxlen = get_param(2);
    if (maxlen <= 0) return 0;
    // new index = get_param(3);             // 玩家index
    // new team = get_param(4);              // 玩家队伍
    new weapon = get_param(5);            // 当前武器
    new weapon_kill_count = get_param(6); // 当前武器杀敌数
    // new all_kill_count = get_param(7);    // 所有武器杀敌数
    new multi_kill_count = get_param(8);  // 连续杀敌数
    new headshot = get_param(9);          // 是否爆头
    new wallshot = get_param(10);         // 是否穿墙
    new revenge = get_param(11);          // 是否复仇

    if (revenge)
        len = formatex(filename, charsmax(filename), "sprites/as/v1/kill_revenge.spr");
    else if (weapon == CSW_HEGRENADE)
        len = formatex(filename, charsmax(filename), "sprites/as/v1/kill_hegrenade.spr");
    else if (weapon == CSW_KNIFE)
        len = formatex(filename, charsmax(filename), "sprites/as/v1/multi_kill_knife_%d.spr", min(weapon_kill_count, 4));
    else
    {
        if (headshot && wallshot)
            len = formatex(filename, charsmax(filename), "sprites/as/v1/%s.spr", (multi_kill_count == 1 ? "kill_headwallshot" : "multi_kill_headwallshot"));
        else if (headshot)
            len = formatex(filename, charsmax(filename), "sprites/as/v1/%s.spr", (multi_kill_count == 1 ? "kill_headshot" : "multi_kill_headshot"));
        else if (wallshot)
            len = formatex(filename, charsmax(filename), "sprites/as/v1/%s.spr", (multi_kill_count == 1 ? "kill_wallshot" : "multi_kill_wallshot"));
        else
            len = formatex(filename, charsmax(filename), "sprites/as/v1/multi_kill_%d.spr", min(multi_kill_count, 6));
    }

    return set_string(1, filename, min(len, maxlen));
}

public native_set_hud_l(plugin_id, argc) // 设置等级HUD信息(左下角), 用于被插件 as.amxx 调用
{
    if (argc < 3) return 0;
    new index = get_param(1);
    if (!(1 <= index && index <= AS_MAX_PLAYERS)) return 0;
    new maxlen = get_param(3);
    if (maxlen <= 0) return 0;

    new len = 0;
    new buffer[1024];

#if 1 // 默认使用内置的等级HUD信息, 改成0使用自定义等级HUD信息
    len = get_string(2, buffer, charsmax(buffer));
    // server_print("hud_l: %d %d [%s]", index, maxlen, buffer); // 显示内置信息

    // 前面追加得分、评级、战力信息
    new newlen = 0;
    new newbuffer[1024];
    newlen += formatex(newbuffer[newlen], charsmax(newbuffer) - newlen,  "%L: %.2f", LANG_PLAYER, "SCORE", as_get_player_score(index) + 0.005);
    newlen += formatex(newbuffer[newlen], charsmax(newbuffer) - newlen, " %L: %.2f", LANG_PLAYER, "RATING", as_get_player_rating(index) + 0.005);
    newlen += formatex(newbuffer[newlen], charsmax(newbuffer) - newlen, " %L: %.2f", LANG_PLAYER, "RWS", as_get_player_rws(index) + 0.005);
    newlen += formatex(newbuffer[newlen], charsmax(newbuffer) - newlen, "^n");
    copy(newbuffer[newlen], min(charsmax(newbuffer) - newlen, len), buffer);
#else
    // 自定义等级HUD信息
#endif

    return set_string(2, newbuffer, min(newlen + len, maxlen));
}

public native_set_hud_s(plugin_id, argc) // 设置观察者HUD信息(右下角), 用于被插件 as.amxx 调用
{
    if (argc < 4) return 0;
    // new index = get_param(1);
    // if (!(1 <= index && index <= AS_MAX_PLAYERS)) return 0;
    new target = get_param(2);
    if (!target) return 0;
    new maxlen = get_param(4);
    if (maxlen <= 0) return 0;

    new len = 0;
    new buffer[1024];

#if 1 // 默认使用内置的观察者HUD信息, 改成0使用自定义的观察者HUD信息
    len = get_string(3, buffer, charsmax(buffer));
    // server_print("hud_s: %d %d %d [%s]", index, target, maxlen, buffer); // 显示内置信息

    // 后面追加位置、签名信息
    len += formatex(buffer[len], charsmax(buffer) - len, "[%L] %s^n", LANG_PLAYER, "LOCATION", g_player_location[target]);
    len += formatex(buffer[len], charsmax(buffer) - len, "[%L] %s^n", LANG_PLAYER, "SIGNATURE", g_player_signature[target]);
#else
    // 自定义观察者HUD信息
    new ibombs[AS_BOMB_ENUM_COUNT], irounds[AS_ROUND_ENUM_COUNT], iweapons[AS_WEAPON_ENUM_COUNT];
    as_get_player_bombs(target, ibombs, g_mode, AS_RANGE_ALL);
    as_get_player_rounds(target, irounds, g_mode, AS_RANGE_ALL);
    as_get_player_weapons(target, 0, iweapons, AS_TARGET_ENEMY, g_mode, AS_RANGE_ALL);

    new cName[AS_NAME_LENGTH + 1]; get_user_name(target, cName, AS_NAME_LENGTH);
    len += formatex(buffer[len], charsmax(buffer) - len, "%L: %s^n", LANG_PLAYER, "PLAYER", cName);

    new flags[26 + 1]; get_flags(get_user_flags(target), flags, charsmax(flags));
    len += formatex(buffer[len], charsmax(buffer) - len, "权限: %s^n", flags);

    new Float:score = as_get_player_score(target, g_mode, AS_RANGE_ALL);
    // new Float:rating = as_get_player_rating(target, g_mode, AS_RANGE_ALL);
    // new Float:rws = as_get_player_rws(target, g_mode, AS_RANGE_ALL);

    new scoreRanking = as_get_player_ranking(target, g_mode, AS_RANKING_ALL_SCORE); if (scoreRanking == AS_MAX_RANKING) scoreRanking = 0;
    // new ratingRanking = as_get_player_ranking(target, g_mode, AS_RANKING_ALL_RATING); if (ratingRanking == AS_MAX_RANKING) ratingRanking = 0;
    // new rwsRanking = as_get_player_ranking(target, g_mode, AS_RANKING_ALL_RWS); if (rwsRanking == AS_MAX_RANKING) rwsRanking = 0;

    new level = as_get_level(score);
    new lName[AS_NAME_LENGTH + 1]
    as_get_level_name(level, lName);

    len += formatex(buffer[len], charsmax(buffer) - len, "%L: %d %L: %s^n", LANG_PLAYER, "RANKING", scoreRanking, LANG_PLAYER, "LEVEL", lName);
    len += formatex(buffer[len], charsmax(buffer) - len, "%L: %d %L: %d KD: %.2f^n", LANG_PLAYER, "KILL", iweapons[AS_WEAPON_KILL], LANG_PLAYER, "DEATH", iweapons[AS_WEAPON_DEATH], apb(iweapons[AS_WEAPON_KILL], iweapons[AS_WEAPON_DEATH], 1) + 0.005);

    len += formatex(buffer[len], charsmax(buffer) - len, "%L: %s^n", LANG_PLAYER, "LOCATION", g_player_location[target]);
    len += formatex(buffer[len], charsmax(buffer) - len, "%L: %s^n", LANG_PLAYER, "SIGNATURE", g_player_signature[target]);
#endif

    return set_string(3, buffer, min(len, maxlen));
}

public native_set_tutor_stats(plugin_id, argc) // 设置向导(右上角)统计信息, 用于被插件 as.amxx 调用
{
    if (argc < 3) return 0;
    new index = get_param(1);
    if (!(1 <= index && index <= AS_MAX_PLAYERS)) return 0;
    new maxlen = get_param(3);
    if (maxlen <= 0) return 0;

    new len = 0;
    new buffer[1024];

#if 1 // 默认使用内置的向导信息, 改成0使用自定义的向导信息
    len = get_string(2, buffer, charsmax(buffer));
    // server_print("tutor_stats: %d %d [%s]", index, maxlen, buffer); // 显示内置信息

    // 后面追加签名信息
    len += formatex(buffer[len], maxlen - len, " %L:%s", LANG_PLAYER, "SIGNATURE", g_player_signature[index]);
#else
    // 自定义的向导信息
#endif

    return set_string(2, buffer, min(len, maxlen));
}

public native_player_login(plugin_id, argc)
{
    if (argc < 1) return 0;
    new index = get_param(1);
    new id = as_query_player_id(index);
    as_set_player_id(index, id);
    g_player_timestamp[index] = get_systime();
    if (id && id < BOT_ID_LIMIT) // 防止一个用户同时登录多处, 机器人除外
    {
        // 踢本服
        for (new i = 1; i <= AS_MAX_PLAYERS; ++i)
        {
            if (!is_user_connected(i)) continue;
            if (i == index) continue;
            if (as_get_player_id(i) == id)
                server_cmd("kick #%d %L", get_user_userid(i), LANG_PLAYER, "ERROR_MSG_K");
        }

        // 踢他服, 向服务器发送踢人消息, 本服不会收到该消息
        as_send_message("kick,%d,%d,%L", id, g_player_timestamp[index], LANG_PLAYER, "ERROR_MSG_K");
    }
    return id;
}

public native_get_player_signature(plugin_id, argc)
{
    if (argc < 3) return 0;
    new index = get_param(1);
    new maxlen = get_param(3);
    if (maxlen <= 0) return 0;
    return set_string(2, g_player_signature[index], maxlen);
}
////////////////////////////////////////////////////////////////
public sendMessage(index, msg[], maxlen)
{
    if (msg[0] && maxlen > 0)
    {
        if (index)
        {
            if (!is_user_connected(index)) return;
            replace_all(msg, maxlen, "!Y", "^x01");
            replace_all(msg, maxlen, "!T", "^x03");
            replace_all(msg, maxlen, "!G", "^x04");
            message_begin(MSG_ONE_UNRELIABLE, g_msg_SayText, _, index); msg[min(190, maxlen)] = 0;
            write_byte(index);
            write_string(msg);
            message_end();
        }
        else
        {
            for (new i = 1; i <= AS_MAX_PLAYERS; ++i) if (is_user_connected(i)) sendMessage(i, msg, maxlen);
        }
    }
}

public sendMessageLang(index, const lang[])
{
    new buffer[1024]; formatex(buffer, charsmax(buffer), "%L", LANG_PLAYER, lang); trim(buffer);
    if (buffer[0] == '-') return;
    sendMessage(index, buffer, charsmax(buffer));
}
#if defined USE_DISCUZ
// 有论坛模式则不带此使用说明, 对应as.txt中的 USAGE_USER
#else
public taskShowUsage(tid)
{
    if (task_exists(tid)) remove_task(tid);
    if (!get_cvar_num("as_usage_flag")) return;
    set_task(floatclamp(get_cvar_float("as_usage_interval"), 5.0, 180.0) + 0.1, "taskShowUsage", tid);
    sendMessageLang(0, "USAGE_USER");
}
#endif
