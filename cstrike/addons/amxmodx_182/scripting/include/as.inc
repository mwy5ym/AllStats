#if defined _as_included
  #endinput
#endif
#define _as_included

#if AMXX_VERSION_NUM >= 175
    #pragma reqlib as
    #if !defined AMXMODX_NOAUTOLOAD
        #pragma loadlib as
    #endif
#else
    #pragma library as
#endif

#define AS_VERSION              "2020.11"
#define AS_AUTHOR               "mwy5ym@qq.com"

#define AS_MAX_PLAYERS          32
#define AS_MAX_WEAPONS          32
#define AS_MAX_RANKING          100000000

#define AS_IP_LENGTH            15
#define AS_NAME_LENGTH          31

// 数据模式
enum AS_MODE_TYPE
{
    AS_MODE_NONE = 0,
    AS_MODE_CASUAL,             // 休闲模式
    AS_MODE_COMPETITIVE,        // 竞技模式
    AS_MODE_DEATHMATCH,         // 死亡竞赛
    AS_MODE_ZOMBIEPLAGUE,       // 僵尸模式
    AS_MODE_CUSTOM_1,           // 自定义1
    AS_MODE_CUSTOM_2,           // 自定义2
    AS_MODE_CUSTOM_3,           // 自定义3
    AS_MODE_CUSTOM_4,           // 自定义4
    AS_MODE_ENUM_COUNT
};

// 数据范围
enum AS_RANGE_TYPE
{
    AS_RANGE_NOW = 0,           // 本场
    AS_RANGE_MAP,               // 地图
    AS_RANGE_ALL,               // 全图
    AS_RANGE_ENUM_COUNT
};

// 排名方式
enum AS_RANKING_TYPE
{
    AS_RANKING_MAP_SCORE = 0,   // 地图得分
    AS_RANKING_MAP_RATING,      // 地图评级
    AS_RANKING_MAP_RWS,         // 地图战力
    AS_RANKING_ALL_SCORE,       // 全图得分
    AS_RANKING_ALL_RATING,      // 全图评级
    AS_RANKING_ALL_RWS,         // 全图战力
    AS_RANKING_ENUM_COUNT
};

enum AS_KAST_TYPE
{
    AS_KAST_KILL = 1,           // 杀敌
    AS_KAST_ASSIST = 2,         // 助攻
    AS_KAST_SURVIVED = 4,       // 存活
    AS_KAST_TRADED = 8          // 换人
};

// 目标类型
enum AS_TARGET_TYPE
{
    AS_TARGET_ENEMY = 0,        // 敌人
    AS_TARGET_FRIEND,           // 队友
    AS_TARGET_ENUM_COUNT
};

// 炸弹数据统计
enum
{
    AS_BOMB_PLANTING = 0,       // 尝试安包的次数
    AS_BOMB_PLANTED,            // 成功安包的次数
    AS_BOMB_EXPLODE,            // 成功爆炸的次数
    AS_BOMB_DEFUSING,           // 尝试拆包的次数
    AS_BOMB_DEFUSED,            // 成功拆包的次数
    AS_BOMB_ENUM_COUNT
};

// 回合数据统计
enum
{
    AS_ROUND_UNASSIGNED = 0,       // 未选择
    AS_ROUND_T,                    // 作为恐怖分子的次数
    AS_ROUND_CT,                   // 作为反恐精英的次数
    AS_ROUND_SPECTATOR,            // 作为观察者的次数
    AS_ROUND_WIN_T,                // 作为恐怖分子并获得胜利的次数
    AS_ROUND_WIN_CT,               // 作为反恐精英并获得胜利的次数
    AS_ROUND_WIN_SHARES,           // 战力
    AS_ROUND_FIRST_KILL,           // 最先击杀的次数
    AS_ROUND_FIRST_DEATH,          // 最先阵亡的次数
    AS_ROUND_LAST_KILL,            // 最后击杀的次数
    AS_ROUND_LAST_DEATH,           // 最后阵亡的次数
    AS_ROUND_MVP,                  // 获得MVP的次数
    AS_ROUND_KAST,                 // 非白给的次数, Kill、Assist、Survived、Traded, 做到其中一样即可
    AS_ROUND_ASSIST,               // 助攻的次数
    AS_ROUND_ASSIST_BY_DAMAGE,     // 助攻的次数(伤害)
    AS_ROUND_ASSIST_BY_FLASHBANG,  // 助攻的次数(闪光弹)
    AS_ROUND_KILL_1,               // 每局1杀的次数
    AS_ROUND_KILL_2,               // 每局2杀的次数
    AS_ROUND_KILL_3,               // 每局3杀的次数
    AS_ROUND_KILL_4,               // 每局4杀的次数
    AS_ROUND_KILL_5,               // 每局5杀的次数
    AS_ROUND_1_VS_1,               // 残局1vs1获胜的次数
    AS_ROUND_1_VS_2,               // 残局1vs2获胜的次数
    AS_ROUND_1_VS_3,               // 残局1vs3获胜的次数
    AS_ROUND_1_VS_4,               // 残局1vs4获胜的次数
    AS_ROUND_1_VS_5,               // 残局1vs5获胜的次数
    AS_ROUND_TIME,                 // 在线时间
    AS_ROUND_ENUM_COUNT
};

// 武器数据统计(HS: HEADSHOT爆头, WS: WALLSHOT穿墙, HWS: HEADWALLSHOT穿墙爆头)
enum
{
    AS_WEAPON_KILL = 0,         // 击杀的次数(含HS、WS、HWS的情况)
    AS_WEAPON_KILL_HS,          // 爆头击杀的次数(含HWS的情况)
    AS_WEAPON_KILL_WS,          // 穿墙击杀的次数(含HWS的情况)
    AS_WEAPON_KILL_HWS,         // 爆头穿墙击杀的次数
    AS_WEAPON_KILLED,           // (被)击杀的次数(含HS、WS、HWS的情况)
    AS_WEAPON_KILLED_HS,        // (被)爆头击杀的次数(含HWS的情况)
    AS_WEAPON_KILLED_WS,        // (被)穿墙击杀的次数(含HWS的情况)
    AS_WEAPON_KILLED_HWS,       // (被)爆头穿墙击杀的次数
    AS_WEAPON_DEATH,            // 阵亡的次数
    AS_WEAPON_SHOT,             // 使用的次数
    AS_WEAPON_HIT,              // 命中的次数(含HS、WS、HWS的情况)
    AS_WEAPON_HIT_HS,           // 爆头命中的次数(含HWS的情况)
    AS_WEAPON_HIT_WS,           // 穿墙命中的次数(含HWS的情况)
    AS_WEAPON_HIT_HWS,          // 穿墙爆头命中的次数
    AS_WEAPON_DAMAGE,           // 造成的伤害
    AS_WEAPON_DAMAGE_REAL,      // 造成的伤害(实际)
    AS_WEAPON_ENUM_COUNT
};

// 命中部位数据统计
enum
{
    AS_BODYHIT_GENERIC = 0,     // 命中全身的次数
    AS_BODYHIT_HEAD,            // 命中头部的次数
    AS_BODYHIT_CHEST,           // 命中胸部的次数
    AS_BODYHIT_STOMACH,         // 命中胃部的次数
    AS_BODYHIT_LEFT_ARM,        // 命中左肩的次数
    AS_BODYHIT_RIGHT_ARM,       // 命中右肩的次数
    AS_BODYHIT_LEFT_LEG,        // 命中左腿的次数
    AS_BODYHIT_RIGHT_LEG,       // 命中右腿的次数
    AS_BODYHIT_ENUM_COUNT
};

// RWS计算参数
enum
{
    AS_RWS_RESULT = 0,           // 回合结果(1:恐怖分子 2:反恐精英 4:平局 5:VIP被杀了 9:VIP未能逃离 10:VIP成功逃离 17:人质未能解救 18:人质成功解救 33:C4成功爆破 34:C4成功拆除 66:C4未能爆破)
    AS_RWS_T_ALIVE,              // 恐怖分子存活数
    AS_RWS_T_TOTAL,              // 恐怖分子总人数
    AS_RWS_T_DAMAGES,            // 恐怖分子总伤害
    AS_RWS_CT_ALIVE,             // 反恐精英存活数
    AS_RWS_CT_TOTAL,             // 反恐精英总人数
    AS_RWS_CT_DAMAGES,           // 反恐精英总伤害
    AS_RWS_FIRST_KILL,           // 最先杀敌index
    AS_RWS_FIRST_DEATH,          // 最先阵亡index
    AS_RWS_LAST_KILL,            // 最后杀敌index
    AS_RWS_LAST_DEATH,           // 最后阵亡index
    AS_RWS_MVP,                  // MVP的index
    AS_RWS_PLANTER,              // C4安包员index
    AS_RWS_DEFUSER,              // C4拆包员index
    AS_RWS_VIP,                  // VIP的index
    AS_RWS_VIP_KILLER,           // VIP杀手的index
    AS_RWS_LAST_HOSTAGE_RESCUER, // 最后人质拯救者的index
    AS_RWS_ENUM_COUNT
};

//////////////////////////////////////////////////////////////// 以下内容由模块 as_amxx.dll 提供

forward as_recv_message(const message[]);   // 接收自定义消息

forward as_player_enter(index); // 玩家进入游戏后触发该回调, 建议在此时进行登录操作
forward as_player_leave(index); // 玩家离开游戏后触发该回调

forward as_mode_changed(AS_MODE_TYPE:oldmode, AS_MODE_TYPE:newmode); // 游戏模式改变, 告知当前的游戏模式(请勿直接使用get_cvar_num("as_mode"), 这样并不准确, 因为as_mode仅在下一回合开始时生效)

forward as_player_stats_synced(AS_MODE_TYPE:mode, index);    // 玩家登录会自动同步历史数据, 同步完成后触发该回调
forward as_player_rankings_synced(AS_MODE_TYPE:mode, index); // 玩家登录、回合结束后会自动同步最新排名, 同步完成后触发该回调

forward as_player_shoot(attacker, wpnindex, clip, ammo); // 玩家挥刀、开火、扔手雷、扔烟雾弹、扔闪光弹时触发该回调
forward as_player_attack(attacker, awpnindex, victim, vwpnindex, aiming, damage, damage_real, kflag, wflag, AS_TARGET_TYPE:tflag, Float:distance); // 玩家造成伤害时触发该回调
forward as_player_assist(killer, victim, assistant, assist_damage, assist_flashbang); // 玩家产生助攻时触发该回调

forward as_bomb_planting(planter); // 玩家正在安放C4时触发该回调
forward as_bomb_planted(planter);  // 玩家成功安放C4时触发该回调
forward as_bomb_explode(planter);  // C4爆炸时触发该回调
forward as_bomb_defusing(defuser); // 玩家正在拆除C4时触发该回调
forward as_bomb_defused(defuser);  // 玩家成功拆除C4时触发该回调

forward as_round_first_blood(killer, victim); // 每回合最先杀敌时触发该回调, 与as_round_last_blood不会同时触发
forward as_round_last_blood(killer, victim);  // 每回合最后杀敌时触发该回调, 与as_round_first_blood不会同时触发

forward as_round_result(result, mvp); // 回合结束时触发该回调, 参数表示回合结果(1:恐怖分子 2:反恐精英 4:平局 5:VIP被杀了 9:VIP未能逃离 10:VIP成功逃离 17:人质未能解救 18:人质成功解救 33:C4成功爆破 34:C4成功拆除 66:C4未能爆破)

//////////////////////////////////////////////////////////////// 以下内容由模块 as_amxx.dll 提供

native as_log(const message[], any:...); // 输出日志
native as_log_debug(const message[], any:...); // 输出调试日志
native as_send_message(const message[], any:...); // 发送自定义消息

native as_valid_ansi_length(const ipt[]); // 计算有效的ansi字符长度
native as_valid_utf8_length(const ipt[]); // 计算有效的utf8字符长度

native as_ansi_to_utf8(const ipt[], opt[], size); // 字符转换
native as_utf8_to_ansi(const ipt[], opt[], size); // 字符转换

native as_str_to_hex(const ipt[], opt[], size); // 字符转换
native as_hex_to_str(const ipt[], opt[], size); // 字符转换

native as_set_player_id(index, id); // 设置玩家(index)的id
native as_get_player_id(index);     // 获取玩家(index)的id

native as_sync_player_stats(index);    // 请求同步玩家历史数据, 异步同步非实时, 同步完后触发as_player_stats_synced
native as_sync_player_rankings(index); // 请求同步玩家最新排名, 异步同步非实时, 同步完后触发as_player_rankings_synced

native as_set_player_ranking(index, AS_MODE_TYPE:mode, AS_RANKING_TYPE:type, ranking); // 设置玩家(index)的排名(index设0表示设置总数), 会被系统同步刷新, 仅在未开启自动保存数据时使用
native as_get_player_ranking(index, AS_MODE_TYPE:mode, AS_RANKING_TYPE:type);          // 获取玩家(index)的排名(index设0表示获取总数), 每回合会自动同步, 同步完后触发as_player_rankings_synced

native as_set_player_bombs(index, ibombs[AS_BOMB_ENUM_COUNT], AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 设置玩家(index)的炸弹数据, 可选数据模式(mode)、范围(range)
native as_get_player_bombs(index, ibombs[AS_BOMB_ENUM_COUNT], AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 获取玩家(index)的炸弹数据, 可选数据模式(mode)、范围(range)

native as_set_player_rounds(index, irounds[AS_ROUND_ENUM_COUNT], AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 设置玩家(index)的回合数据, 可选数据模式(mode)、范围(range)
native as_get_player_rounds(index, irounds[AS_ROUND_ENUM_COUNT], AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 获取玩家(index)的回合数据, 可选数据模式(mode)、范围(range)

native as_set_player_weapons(index, iwn, iweapons[AS_WEAPON_ENUM_COUNT], AS_TARGET_TYPE:tflag = AS_TARGET_ENEMY, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 设置玩家(index)武器(iwn)的数据, 可选目标类型(tflag: 敌人、队友), 可选数据模式(mode)、范围(range)
native as_get_player_weapons(index, iwn, iweapons[AS_WEAPON_ENUM_COUNT], AS_TARGET_TYPE:tflag = AS_TARGET_ENEMY, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 获取玩家(index)武器(iwn)的数据, 可选目标类型(tflag: 敌人、队友), 可选数据模式(mode)、范围(range)

native as_set_player_bodyhits(index, iwn, ibodyhits[AS_BODYHIT_ENUM_COUNT], AS_TARGET_TYPE:tflag = AS_TARGET_ENEMY, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 设置玩家(index)武器(iwn)的命中部位数据, 可选目标类型(tflag: 敌人、队友), 可选数据模式(mode)、范围(range)
native as_get_player_bodyhits(index, iwn, ibodyhits[AS_BODYHIT_ENUM_COUNT], AS_TARGET_TYPE:tflag = AS_TARGET_ENEMY, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 获取玩家(index)武器(iwn)的命中部位数据, 可选目标类型(tflag: 敌人、队友), 可选数据模式(mode)、范围(range)

//////////////////////////////////////////////////////////////// 以下内容由插件 as.amxx 提供

native as_precache_generic(const filename[]); //缓冲普通文件, 在plugin_precache()中执行
native as_precache_sound(const filename[]);   //缓冲声音文件, 在plugin_precache()中执行
native as_precache_sprite(const filename[]);  //缓冲模型文件, 在plugin_precache()中执行

native as_get_player_spectator(index); // 获取当前玩家正在观察的玩家(index), 如果不是第一人称观察模式返回-index

native Float:as_get_player_score(index, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL);  // 获取玩家(index)的得分, 可选数据模式(mode)、范围(range)
native Float:as_get_player_rating(index, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL); // 获取玩家(index)的评级, 可选数据模式(mode)、范围(range)
native Float:as_get_player_rws(index, AS_MODE_TYPE:mode = AS_MODE_CASUAL, AS_RANGE_TYPE:range = AS_RANGE_ALL);    // 获取玩家(index)的战力, 可选数据模式(mode)、范围(range)

native as_get_level(Float:score);                           // 获取得分(score)的等级
native as_get_level_name(level, lname[AS_NAME_LENGTH + 1]); // 获取等级(level)的名称
native Float:as_get_level_score(level);                     // 获取等级(level)的得分

//////////////////////////////////////////////////////////////// 以下内容由插件 as_custom.amxx 提供

native Float:as_calc_score(ibombs[AS_BOMB_ENUM_COUNT], irounds[AS_ROUND_ENUM_COUNT], iweapons[AS_WEAPON_ENUM_COUNT]);  // 自定义计算得分, 用于被插件 as.amxx 调用
native Float:as_calc_rating(ibombs[AS_BOMB_ENUM_COUNT], irounds[AS_ROUND_ENUM_COUNT], iweapons[AS_WEAPON_ENUM_COUNT]); // 自定义计算评级, 用于被插件 as.amxx 调用
native Float:as_calc_rws(index, team, kast, kills, deaths, assists, assists_by_damage, assists_by_flashbang, damages, infos[AS_RWS_ENUM_COUNT]); // 自定义计算战力, 用于被插件 as.amxx 调用

native as_set_kill_sound(filename[], maxlen, index, team, weapon, weapon_kill_count, all_kill_count, multi_kill_count, headshot, wallshot, revenge); // 自定义设置声音特效, 用于被插件 as.amxx 调用
native as_set_kill_badge(filename[], maxlen, index, team, weapon, weapon_kill_count, all_kill_count, multi_kill_count, headshot, wallshot, revenge); // 自定义设置徽章特效, 用于被插件 as.amxx 调用

native as_set_hud_l(index, buffer[], maxlen);         // 设置自定义等级HUD信息(左下角), 用于被插件 as.amxx 调用
native as_set_hud_s(index, target, buffer[], maxlen); // 设置自定义观察者HUD信息(右下角), 用于被插件 as.amxx 调用
native as_set_tutor_stats(index, buffer[], maxlen);   // 设置自定义向导(右上角)统计信息, 用于被插件 as.amxx 调用

native as_player_login(index);                           // 玩家登录
native as_get_player_signature(index, buffer[], maxlen); // 获取玩家签名
