<?php
// ini_set("error_reporting", "E_ALL & ~E_NOTICE"); // 取消注释 或 修改php.ini的error_reporting选项加上 & ~E_NOTICE
if (!isset($_SERVER["REQUEST_URI"]))
{
   $_SERVER["REQUEST_URI"] = substr($_SERVER["PHP_SELF"], 1);
   if (isset($_SERVER["QUERY_STRING"])) $_SERVER["REQUEST_URI"] .= "?" . $_SERVER["QUERY_STRING"];
}

$as_socket_command_s2c = "tcp://127.0.0.1:51203"; // 和as.cfg中一致
$as_socket_command_c2s = "tcp://127.0.0.1:51204"; // 和as.cfg中一致
$asdb = null;
$dzdb = null;

if ($asdb = mysqli_connect("127.0.0.1:3307", "root", "qwer1234", "cstrike")) mysqli_set_charset($asdb, "utf8"); else die("Could not connect to asdb: " . mysqli_connect_error());
// if ($dzdb = mysqli_connect("127.0.0.1:3306", "root", "qwer1234", "discuz")) mysqli_set_charset($dzdb, "utf8"); else die("Could not connect to dzdb: " . mysqli_connect_error());
// 无论坛就注释掉上面这1行; 有论坛就修改上面这1行, 取消注释, 并修改下方5个变量;

if ($dzdb)
{
    // discuz表前缀
    $dz_table_pre = "pre_";
    // 游戏名称的自定义字段
    $dz_field_name = "field1";
    // 游戏权限的自定义字段
    $dz_field_flags = "field2";
    // 游戏签名的自定义字段
    $dz_field_signature = "field3";
    // STEAMID的自定义字段
    $dz_field_steamid = "field4";
    // discuz主页地址, 填写时请去掉末尾的index.php
    $dz_url = "http://127.0.0.1/bbs/";

    function as_get_user_image($uid)
    {
        global $dz_url;
        return $dz_url . "uc_server/avatar.php?uid=" . $uid . "&size=middle";
    }

    function as_get_user_dzname($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        $result = mysqli_query($dzdb, "select `username` from `" . $dz_table_pre . "common_member` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["username"];
    }

    function as_get_user_dzgroup($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        $result = mysqli_query($dzdb, "select `grouptitle` from `" . $dz_table_pre . "common_usergroup` where `groupid` = (select `groupid` from `" . $dz_table_pre . "common_member` where `uid` = " . $uid . ") limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["grouptitle"];
    }

    function as_get_user_name($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        global $dz_field_name;
        $result = mysqli_query($dzdb, "select `" . $dz_field_name . "` as `name` from `" . $dz_table_pre . "common_member_profile` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["name"];
    }

    function as_get_user_flags($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        global $dz_field_flags;
        $result = mysqli_query($dzdb, "select `" . $dz_field_flags . "` as `flags` from `" . $dz_table_pre . "common_member_profile` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["flags"];
    }

    function as_get_user_signature($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        global $dz_field_signature;
        $result = mysqli_query($dzdb, "select `" . $dz_field_signature . "` as `signature` from `" . $dz_table_pre . "common_member_profile` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["signature"];
    }

    function as_get_user_steamid($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        global $dz_field_steamid;
        $result = mysqli_query($dzdb, "select `" . $dz_field_steamid . "` as `steamid` from `" . $dz_table_pre . "common_member_profile` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["steamid"];
    }

    function as_get_user_regtimestamp($uid)
    {
        global $dzdb;
        global $dz_table_pre;
        $result = mysqli_query($dzdb, "select from_unixtime(`regdate`) as `regtimestamp` from `" . $dz_table_pre . "common_member` where `uid` = " . $uid . " limit 1;");
        if (!$result) return "";
        $row = mysqli_fetch_array($result);
        mysqli_free_result($result);
        return $row["regtimestamp"];
    }

    function as_rename_rows(&$rows, $col_uid, $col_name, $col_uid2 = "", $col_name2 = "")
    {
        global $dzdb;
        global $dz_table_pre;
        global $dz_field_name;
        if (!$rows) return;
        $names = array();
        foreach($rows as $row)
        {
            if (!empty($col_uid) && !empty($col_name) && $row[$col_uid]) $names[$row[$col_uid]] = $row[$col_name];
            if (!empty($col_uid2) && !empty($col_name2) && $row[$col_uid2]) $names[$row[$col_uid2]] = $row[$col_name2];
        }
        $result = mysqli_query($dzdb, "select `uid`, `" . $dz_field_name . "` as `name` from `" . $dz_table_pre . "common_member_profile` where `uid` in (" . join(",", array_keys($names)) . ");");
        if ($result)
        {
            while ($row = mysqli_fetch_array($result)) $names[$row["uid"]] = $row["name"];
            mysqli_free_result($result);
        }
        foreach($rows as &$row)
        {
            if (!empty($col_uid) && !empty($col_name) && $row[$col_uid]) $row[$col_name] = $names[$row[$col_uid]];
            if (!empty($col_uid2) && !empty($col_name2) && $row[$col_uid2]) $row[$col_name2] = $names[$row[$col_uid2]];
        }
    }
}
else
{
    function as_get_user_image($uid)
    {
        return "images/heads/" . (!$uid ? 0 : (($uid % 8) + 1)) . ".png";
    }

    function as_rename_rows(&$rows, $col_uid, $col_name, $col_uid2 = "", $col_name2 = "")
    {
        global $asdb;
        if (!$rows) return;
        $names = array();
        foreach($rows as $row)
        {
            if (!empty($col_uid) && !empty($col_name) && $row[$col_uid]) $names[$row[$col_uid]] = $row[$col_name];
            if (!empty($col_uid2) && !empty($col_name2) && $row[$col_uid2]) $names[$row[$col_uid2]] = $row[$col_name2];
        }
        $result = mysqli_query($asdb, "select `uid`, `name` from `as_users` where `uid` in (" . join(",", array_keys($names)) . ");");
        if ($result)
        {
            while ($row = mysqli_fetch_array($result)) $names[$row["uid"]] = $row["name"];
            mysqli_free_result($result);
        }
        foreach($rows as &$row)
        {
            if (!empty($col_uid) && !empty($col_name) && $row[$col_uid]) $row[$col_name] = $names[$row[$col_uid]];
            if (!empty($col_uid2) && !empty($col_name2) && $row[$col_uid2]) $row[$col_name2] = $names[$row[$col_uid2]];
        }
    }
}

function as_get_user_steam_profile($authid)
{
    $as = explode(":", $authid); if (count($as) == 3) return "http://steamcommunity.com/profiles/7656" . (intval($as[2]) * 2 + intval($as[1]) + 1197960265728);
    return "";
}

function as_get_param_number($name, $default = 0)
{
    if (!isset($_REQUEST[$name])) return $default;
    if (empty(trim($_REQUEST[$name]))) return $default;
    return intval(trim($_REQUEST[$name]));
}

function as_get_param_string($name, $default = "")
{
    global $asdb;
    if (!isset($_REQUEST[$name])) return $default;
    if (empty(trim($_REQUEST[$name]))) return $default;
    return mysqli_escape_string($asdb, trim($_REQUEST[$name]));
}

function as_apb($a, $b, $m)
{
    return $b ? $m * $a / $b : 0.0;
}

function as_rand_string($len)
{
    $str = ""; for ($i = 0; $i < $len; ++$i) $str .= str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
    return substr(str_shuffle($str), 0, $len);
}

function as_get_user_ipaddress()
{
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
    {
        $_SERVER["HTTP_CLIENT_IP"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER["REMOTE_ADDR"] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client = @$_SERVER["HTTP_CLIENT_IP"];
    $forward = @$_SERVER["HTTP_X_FORWARDED_FOR"];
    $remote = $_SERVER["REMOTE_ADDR"];
    if (filter_var($client, FILTER_VALIDATE_IP))
        return $client;
    if (filter_var($forward, FILTER_VALIDATE_IP))
        return $forward;
    return $remote;
}

function as_hex2string($hex)
{
    $str = "";
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    return $str;
}

function as_fixHtmlString($str)
{
    $str = str_replace("&", "&amp;", $str);
    $str = str_replace("<", "&lt;", $str);
    $str = str_replace(">", "&gt;", $str);
    $str = str_replace('"', "&quot;", $str);
    $str = str_replace("'", "&#39;", $str);
    return $str;
}

function as_formatTime($time, $simple = false)
{
    $day = (int)($time / 86400);
    $hour = (int)($time % 86400 / 3600);
    $minute = (int)($time % 3600 / 60);
    $second = (int)($time % 60);
    $str = "";
    if ($simple)
    {
        if ($time >= 360000) $str .= $day . "天"; else $hour += $day * 24;
        if ($hour > 0) $str .= $hour . "时";
        else if ($minute > 0) $str .= $minute . "分";
        else $str .= $second . "秒";
    }
    else
    {
        $day = (int)($time / 86400);
        $hour = (int)($time % 86400 / 3600);
        $minute = (int)($time % 3600 / 60);
        $second = (int)($time % 60);
        if ($day > 0) $str .= $day . "天";
        if ($hour > 0) $str .= $hour . "小时";
        if ($minute > 0) $str .= $minute . "分钟";
        if ($second > 0) $str .= $second . "秒";
    }
    return $str;
}

function as_formatFlags($flags) // 自定义权限名称, 也可以使用图标更清楚
{
    if ($flags == "abcdefghijklmnopqrstu") return "顶级OP";
    if ($flags == "abcdefjmru") return "高级OP";
    if ($flags == "abcdru") return "普通OP";
    if ($flags == "bcdru") return "高级VIP";
    if ($flags == "bcru") return "中级VIP";
    if ($flags == "bru") return "普通VIP";
    return "普通玩家";
}

function as_get_bodyhits_orders($values)
{
    $ret = array(0, 0, 0, 0);
    $left = array(0 => true, 1 => true, 2 => true, 3 => true);

    $min = min($values);
    $max = max($values);
    if ($min == $max) return [2, 2, 2, 2];
    for ($i = 0; $i < 4; ++$i) if ($values[$i] == $min) { $ret[$i] = 1; $left[$i] = false; } else if ($values[$i] == $max) { $ret[$i] = 4; $left[$i] = false; }

    $one = -1;
    $two = -1;
    for ($i = 0; $i < 4; ++$i) if ($left[$i]) { $one = $i; $left[$i] = false; break; }
    for ($i = 0; $i < 4; ++$i) if ($left[$i]) { $two = $i; $left[$i] = false; break; }

    if ($one != -1 && $two != -1)
    {
        if ($values[$one] < $values[$two]) { $ret[$one] = 2; $ret[$two] = 3; }
        else if ($values[$one] > $values[$two]) { $ret[$one] = 3; $ret[$two] = 2; }
        else $ret[$one] = $ret[$two] = 2;
    }
    else if ($one != -1)
    {
        $ret[$one] = 2;
    }
    return $ret;
}

function as_get_user_count($mode, $map, $name = "")
{
    global $asdb;
    $sql = ($map == "All" ?
        (empty($name) ?
            ("select count(1) as `total` from `as_all_rankings` where `mode` = " . $mode . " limit 1;") :
            ("select count(1) as `total` from `as_all_rankings` as `r` inner join `as_users` as `u` on (`r`.`uid` = `u`.`uid`) where `r`.`mode` = " . $mode . " and `u`.`name` like '%" . $name . "%' limit 1;")) :
        (empty($name) ?
            ("select count(1) as `total` from `as_map_rankings` where `mode` = " . $mode . " and `map` = '" . $map . "' limit 1;") :
            ("select count(1) as `total` from `as_map_rankings` as `r` inner join `as_users` as `u` on (`r`.`uid` = `u`.`uid`) where `r`.`mode` = " . $mode . " and `r`.`map` = " . $map . " and `u`.`name` like '%" . $name . "%' limit 1;")));
    $result = mysqli_query($asdb, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);
    return (int)($row["total"]);
}

function as_get_user_ranking($mode, $map, $order, $uid)
{
    global $asdb;
    if ($uid <= 0) return 0;
    $sql = ($map == "All" ?
        ("select `" . str_replace("_r", "R", $order) . "` as `ranking` from `as_all_rankings` where `mode` = " . $mode . " and `uid` = " . $uid . " limit 1;") :
        ("select `" . str_replace("_r", "R", $order) . "` as `ranking` from `as_map_rankings` where `mode` = " . $mode . " and `map` = '" . $map .  "' and `uid` = " . $uid . " limit 1;"));
    $result = mysqli_query($asdb, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);
    return (int)($row["ranking"]);
}

function as_get_fight_count($mode, $map, $uids)
{
    global $asdb;
    $sql = ($map == "All" ?
        (empty($uids) ?
            ("select count(1) as `total` from `as_fights` where `mode` = " . $mode . " limit 1;") :
            ("select count(1) as `total` from `as_fights` where `mode` = " . $mode . " and (`uid_a` in (" . $uids . ") or `uid_v` in (" . $uids . ")) limit 1;")) :
        (empty($uids) ?
            ("select count(1) as `total` from `as_fights` where `mode` = " . $mode . " and `map` = '" . $map . "' limit 1;") :
            ("select count(1) as `total` from `as_fights` where `mode` = " . $mode . " and `map` = '" . $map . "' and (`uid_a` in (" . $uids . ") or `uid_v` in (" . $uids . ")) limit 1;")));
    $result = mysqli_query($asdb, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);
    return (int)($row["total"]);
}

function as_get_level_id($score)
{
    global $LevelScore;
    if (!$score) return 0;
    for ($i = 0; $i <= 100; ++$i) if ($LevelScore[$i] > $score) return $i - 1;
    return 100;
}

function as_get_level_id2($score)
{
    global $LevelScore;
    if (!$score) return 0;
    for ($i = 0; $i <= 100; ++$i) if ($LevelScore[$i] > $score) return $i;
    return 100;
}

function as_gen_msgid($prefix)
{
    list($msec, $sec) = explode(' ', microtime());
    return $prefix . sprintf("%08d", (int)((floatval(substr($sec, -5)) + floatval($msec)) * 1000.0));
}

function as_socket($server, $msgid, $head, $body, $most = False)
{
    global $as_socket_command_s2c, $as_socket_command_c2s;
    $ret = array();
    $context = new ZMQContext();

    $recv = new ZMQSocket($context, ZMQ::SOCKET_SUB);
    $recv->setSockOpt(ZMQ::SOCKOPT_RCVTIMEO, 500);
    $recv->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $msgid);
    $recv->connect($as_socket_command_c2s);

    $send = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
    $send->connect($as_socket_command_s2c);
    $send->send("@" . $server, ZMQ::MODE_SNDMORE);
    $send->send($msgid, ZMQ::MODE_SNDMORE);
    $send->send($head, ZMQ::MODE_SNDMORE);
    $send->send(is_array($body) ? " ".join($body) : $body);
    $send->disconnect($as_socket_command_s2c);

    $valid = False;
    while ($recv->recv() !== False) // rspid == msgid
    {
        if (!$recv->getSockOpt(ZMQ::SOCKOPT_RCVMORE)) continue;
        $server = $recv->recv(ZMQ::MODE_DONTWAIT); // server

        if (!$recv->getSockOpt(ZMQ::SOCKOPT_RCVMORE)) continue;
        $recv->recv(ZMQ::MODE_DONTWAIT); // head == "responses"

        if (!$recv->getSockOpt(ZMQ::SOCKOPT_RCVMORE)) continue;
        $body = $recv->recv(ZMQ::MODE_DONTWAIT); // body == body

        if (!$recv->getSockOpt(ZMQ::SOCKOPT_RCVMORE)) $ret[$server] = $body;
        else $ret[$server][$body] = $recv->recvMulti(ZMQ::MODE_DONTWAIT);

        $valid = True;
        if (!$most) break;
    }
    $recv->disconnect($as_socket_command_c2s);
    return $valid ? $ret : "unknown";
}

$LevelScore[0] = -100000000.0;
$LevelScore[1] = 0.0;
$LevelScore[2] = 457.0;
$LevelScore[3] = 913.0;
$LevelScore[4] = 1825.0;
$LevelScore[5] = 3193.0;
$LevelScore[6] = 5017.0;
$LevelScore[7] = 7297.0;
$LevelScore[8] = 10033.0;
$LevelScore[9] = 13225.0;
$LevelScore[10] = 17785.0;
$LevelScore[11] = 23941.0;
$LevelScore[12] = 33061.0;
$LevelScore[13] = 43093.0;
$LevelScore[14] = 54037.0;
$LevelScore[15] = 65893.0;
$LevelScore[16] = 78661.0;
$LevelScore[17] = 92341.0;
$LevelScore[18] = 106933.0;
$LevelScore[19] = 122437.0;
$LevelScore[20] = 138853.0;
$LevelScore[21] = 156181.0;
$LevelScore[22] = 174421.0;
$LevelScore[23] = 193573.0;
$LevelScore[24] = 213637.0;
$LevelScore[25] = 234613.0;
$LevelScore[26] = 256501.0;
$LevelScore[27] = 279301.0;
$LevelScore[28] = 326725.0;
$LevelScore[29] = 375973.0;
$LevelScore[30] = 427045.0;
$LevelScore[31] = 479941.0;
$LevelScore[32] = 534661.0;
$LevelScore[33] = 591205.0;
$LevelScore[34] = 649573.0;
$LevelScore[35] = 709765.0;
$LevelScore[36] = 771781.0;
$LevelScore[37] = 835621.0;
$LevelScore[38] = 901285.0;
$LevelScore[39] = 968773.0;
$LevelScore[40] = 1038085.0;
$LevelScore[41] = 1109221.0;
$LevelScore[42] = 1182181.0;
$LevelScore[43] = 1256965.0;
$LevelScore[44] = 1333573.0;
$LevelScore[45] = 1412005.0;
$LevelScore[46] = 1492261.0;
$LevelScore[47] = 1574341.0;
$LevelScore[48] = 1658245.0;
$LevelScore[49] = 1743973.0;
$LevelScore[50] = 1831525.0;
$LevelScore[51] = 1920901.0;
$LevelScore[52] = 2057701.0;
$LevelScore[53] = 2197237.0;
$LevelScore[54] = 2339509.0;
$LevelScore[55] = 2484517.0;
$LevelScore[56] = 2632261.0;
$LevelScore[57] = 2782741.0;
$LevelScore[58] = 2935957.0;
$LevelScore[59] = 3091909.0;
$LevelScore[60] = 3277045.0;
$LevelScore[61] = 3465373.0;
$LevelScore[62] = 3673537.0;
$LevelScore[63] = 3885178.0;
$LevelScore[64] = 4100296.0;
$LevelScore[65] = 4318891.0;
$LevelScore[66] = 4540963.0;
$LevelScore[67] = 4766512.0;
$LevelScore[68] = 5028199.0;
$LevelScore[69] = 5319184.0;
$LevelScore[70] = 5614501.0;
$LevelScore[71] = 5914150.0;
$LevelScore[72] = 6218131.0;
$LevelScore[73] = 6526501.0;
$LevelScore[74] = 6839203.0;
$LevelScore[75] = 7156237.0;
$LevelScore[76] = 7578037.0;
$LevelScore[77] = 8026912.0;
$LevelScore[78] = 8481772.0;
$LevelScore[79] = 8964562.0;
$LevelScore[80] = 9475852.0;
$LevelScore[81] = 10016212.0;
$LevelScore[82] = 10586212.0;
$LevelScore[83] = 11186422.0;
$LevelScore[84] = 11817412.0;
$LevelScore[85] = 12479752.0;
$LevelScore[86] = 13174012.0;
$LevelScore[87] = 13900762.0;
$LevelScore[88] = 14660572.0;
$LevelScore[89] = 15454012.0;
$LevelScore[90] = 16281652.0;
$LevelScore[91] = 17144062.0;
$LevelScore[92] = 18041812.0;
$LevelScore[93] = 18975472.0;
$LevelScore[94] = 19945612.0;
$LevelScore[95] = 20952802.0;
$LevelScore[96] = 21997612.0;
$LevelScore[97] = 23080612.0;
$LevelScore[98] = 24202372.0;
$LevelScore[99] = 25363462.0;
$LevelScore[100] = 26564451.0;

$LevelName[0] = "无";
$LevelName[1] = "列兵1";
$LevelName[2] = "列兵2";
$LevelName[3] = "三等兵";
$LevelName[4] = "二等兵";
$LevelName[5] = "一等兵";
$LevelName[6] = "上等兵1";
$LevelName[7] = "上等兵2";
$LevelName[8] = "上等兵3";
$LevelName[9] = "上等兵4";
$LevelName[10] = "下士1";
$LevelName[11] = "下士2";
$LevelName[12] = "下士3";
$LevelName[13] = "下士4";
$LevelName[14] = "下士5";
$LevelName[15] = "下士6";
$LevelName[16] = "中士1";
$LevelName[17] = "中士2";
$LevelName[18] = "中士3";
$LevelName[19] = "中士4";
$LevelName[20] = "中士5";
$LevelName[21] = "中士6";
$LevelName[22] = "上士1";
$LevelName[23] = "上士2";
$LevelName[24] = "上士3";
$LevelName[25] = "上士4";
$LevelName[26] = "上士5";
$LevelName[27] = "上士6";
$LevelName[28] = "少尉1";
$LevelName[29] = "少尉2";
$LevelName[30] = "少尉3";
$LevelName[31] = "少尉4";
$LevelName[32] = "少尉5";
$LevelName[33] = "少尉6";
$LevelName[34] = "少尉7";
$LevelName[35] = "少尉8";
$LevelName[36] = "中尉1";
$LevelName[37] = "中尉2";
$LevelName[38] = "中尉3";
$LevelName[39] = "中尉4";
$LevelName[40] = "中尉5";
$LevelName[41] = "中尉6";
$LevelName[42] = "中尉7";
$LevelName[43] = "中尉8";
$LevelName[44] = "上尉1";
$LevelName[45] = "上尉2";
$LevelName[46] = "上尉3";
$LevelName[47] = "上尉4";
$LevelName[48] = "上尉5";
$LevelName[49] = "上尉6";
$LevelName[50] = "上尉7";
$LevelName[51] = "上尉8";
$LevelName[52] = "少校1";
$LevelName[53] = "少校2";
$LevelName[54] = "少校3";
$LevelName[55] = "少校4";
$LevelName[56] = "少校5";
$LevelName[57] = "少校6";
$LevelName[58] = "少校7";
$LevelName[59] = "少校8";
$LevelName[60] = "中校1";
$LevelName[61] = "中校2";
$LevelName[62] = "中校3";
$LevelName[63] = "中校4";
$LevelName[64] = "中校5";
$LevelName[65] = "中校6";
$LevelName[66] = "中校7";
$LevelName[67] = "中校8";
$LevelName[68] = "上校1";
$LevelName[69] = "上校2";
$LevelName[70] = "上校3";
$LevelName[71] = "上校4";
$LevelName[72] = "上校5";
$LevelName[73] = "上校6";
$LevelName[74] = "上校7";
$LevelName[75] = "上校8";
$LevelName[76] = "大校1";
$LevelName[77] = "大校2";
$LevelName[78] = "大校3";
$LevelName[79] = "大校4";
$LevelName[80] = "大校5";
$LevelName[81] = "大校6";
$LevelName[82] = "少将1";
$LevelName[83] = "少将2";
$LevelName[84] = "少将3";
$LevelName[85] = "少将4";
$LevelName[86] = "少将5";
$LevelName[87] = "少将6";
$LevelName[88] = "中将1";
$LevelName[89] = "中将2";
$LevelName[90] = "中将3";
$LevelName[91] = "中将4";
$LevelName[92] = "中将5";
$LevelName[93] = "中将6";
$LevelName[94] = "上将1";
$LevelName[95] = "上将2";
$LevelName[96] = "上将3";
$LevelName[97] = "上将4";
$LevelName[98] = "上将5";
$LevelName[99] = "上将6";
$LevelName[100] = "元帅";

$WeaponName[0] = "GENERIC";
$WeaponName[1] = "P228";
$WeaponName[2] = "GLOCK";
$WeaponName[3] = "SCOUT";
$WeaponName[4] = "HEGRENADE";
$WeaponName[5] = "XM1014";
$WeaponName[6] = "C4";
$WeaponName[7] = "MAC10";
$WeaponName[8] = "AUG";
$WeaponName[9] = "SMOKEGRENADE";
$WeaponName[10] = "ELITES";
$WeaponName[11] = "FIVESEVEN";
$WeaponName[12] = "UMP45";
$WeaponName[13] = "SG550";
$WeaponName[14] = "GALIL";
$WeaponName[15] = "FAMAS";
$WeaponName[16] = "USP";
$WeaponName[17] = "GLOCK18";
$WeaponName[18] = "AWP";
$WeaponName[19] = "MP5NAVY";
$WeaponName[20] = "M249";
$WeaponName[21] = "M3";
$WeaponName[22] = "M4A1";
$WeaponName[23] = "TMP";
$WeaponName[24] = "G3SG1";
$WeaponName[25] = "FLASHBANG";
$WeaponName[26] = "DEAGLE";
$WeaponName[27] = "SG552";
$WeaponName[28] = "AK47";
$WeaponName[29] = "KNIFE";
$WeaponName[30] = "P90";
$WeaponName[31] = "VEST";
$WeaponName[32] = "VESTHELM";
?>
