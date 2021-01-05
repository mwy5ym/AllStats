<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<link href="as.css" rel="stylesheet" type="text/css">
<title>Servers</title>
</head>
<body>
<?php
include "cz.php";
include "core.php";
// 该页面仅作为演示使用, 如需在对外发布的服务器上使用, 请自行加上权限认证, 防止被他人滥用
session_start();
$server = as_get_param_string("server");
function convertIP($ip) { return str_replace("192.168.72.1", "123.123.123.123", $ip); } // 内网IP转成外网IP, 请自行修改此行

$servers = array();

$games = as_socket("broadcast", as_gen_msgid(session_id()), "requests", "games", True);
if ($games != "unknown") foreach ($games as $key => $value) { $s = convertIP($key); $servers[$s]["ip"] = $key; $servers[$s]["games"] = $value["games"]; }

$players = as_socket("broadcast", as_gen_msgid(session_id()), "requests", "players", True);
if ($players != "unknown") foreach ($players as $key => $value) { $s = convertIP($key); $servers[$s]["ip"] = $key; $servers[$s]["players"] = $value["players"]; }

$configs = as_socket("broadcast", as_gen_msgid(session_id()), "requests", "configs", True);
if ($configs != "unknown") foreach ($configs as $key => $value) { $s = convertIP($key); $servers[$s]["ip"] = $key; $servers[$s]["configs"] = $value["configs"]; }

ksort($servers); if (count($servers) && (empty($server) || !isset($servers[$server]))) foreach ($servers as $key => $value) { $server = $key; break; }

foreach ($servers as $key => $value)
{
    $servers[$key]["info"] = "";
    if (isset($servers[$key]["games"])) foreach ($servers[$key]["games"] as $value)
    {
        if ($value == "done") break;
        if (strpos($value, "=") === false) continue;
        list($k, $v) = explode("=", $value, 2);
        if ($k == "map") $servers[$key]["info"] .= $v;
        elseif ($k == "players") $servers[$key]["info"] .= " (" . $v . ")";
    }
    $servers[$key]["info"] .= "<br>" . $key;
}

$configs = array();
$filters = array("allow_spectators", "amx_nextmap", "amx_timeleft");
$skips = array("sv_password");

if (isset($servers[$server]["configs"])) foreach ($servers[$server]["configs"] as $value)
{
    if ($value == "done") break;
    if (strpos($value, "=") === false) continue;
    list($k, $v) = explode("=", $value, 2);
    if (strpos($v, "@") === false) continue;
    $vs = explode("@", $v);

    // ARCHIVE         1 // set to cause it to be saved to vars.rc
    // USERINFO        2 // changes the client's info string
    // SERVER          4 // notifies players when changed
    // EXTDLL          8 // defined by external DLL
    // CLIENTDLL      16 // defined by the client dll
    // PROTECTED      32 // It's a server cvar, but we don't send the data since it's a password, etc.  Sends 1 if it's not bland/zero, 0 otherwise as value
    // SPONLY         64 // This cvar cannot be changed by clients connected to a multiplayer server.
    // PRINTABLEONLY 128 // This cvar's string cannot contain unprintable characters ( e.g., used for player name etc ).
    // UNLOGGED      256 // If this is a FCVAR_SERVER, don't log changes to the log file / console if we are creating a log
    $f = array_pop($vs);

    if ($k == "as_mode") $mode = $vs[0];

    if (in_array($k, $skips)) continue; // 过滤列表, 请自行修改
    if (strpos($k, "_sxei") !== 0 && strpos($k, "mp_") !== 0 && strpos($k, "sv_") !== 0 && !in_array($k, $filters)) continue; // 过滤列表, 请自行修改
    $configs[$k] = join("@", $vs);
}
?>
<div class="menu">
  <span style="margin:0;padding:0;">
    <a href="top.php"><div style="margin:4px 4px 0px 4px;padding:0 4px;background-color:#FF6600;">排行</div></a>
    <a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><div style="margin:0px 4px 4px 4px;padding:0 4px;background-color:#0099FF;">刷新</div></a>
  </span>
<?php
foreach ($servers as $key => $value) echo "  <a href=\"servers.php?&server=" . $key . "\"><span style=\"background-color:" . ($key == $server ? "#FF6600" : "#00CC00") . ";\">" . $servers[$key]["info"] . "</span></a>\n";
?>
</div>

<hr>

<?php
if (isset($servers[$server]))
{
?>
<div class="menu invalid">
<?php
$maps = array();
$games = array("map" => "", "time" => "0/0");
$scores = "<span style=\"color:#FF6600;\">0</span><span style=\"color:#CCBB99;\"> : </span><span style=\"color:#0099FF;\">0</span>";
if (isset($servers[$server]["games"])) foreach ($servers[$server]["games"] as $value)
{
    if ($value == "done") break;
    if (strpos($value, "=") === false) continue;
    list($k, $v) = explode("=", $value, 2);
    if ($k == "maps") array_push($maps, $v);
    if ($k == "scores")
    {
        if (strpos($v, ":") === false) continue;
        list($t, $ct) = explode(":", $v, 2);
        $scores = "<span style=\"color:#FF6600;\">" . $t . "</span><span style=\"color:#CCBB99;\"> : </span><span style=\"color:#0099FF;\">" . $ct . "</span>";
    }
    else $games[$k] = $v;
}
echo "  <span style=\"background-color:#0099FF;\"><a href=\"#\">地图</a></span>\n";
if (!in_array($games["map"], $maps)) echo "  <span style=\"background-color:#FF6600;\"><a href=\"javascript:void(0);\" title=\"地图时间: " . $games["time"] . "分钟\">" . $games["map"] . "</a></span>\n";
foreach ($maps as $m) echo "  <span style=\"background-color:" . ($m == $games["map"] ? "#FF6600" : "#00CC00") . ";\"" . ($m == $games["map"] ? (" title=\"地图时间: " . $games["time"] . "分钟\"") : "") . "><a href=\"javascript:void(0);\">" . $m . "</a></span>\n";
?>
</div>

<hr>
<?php
$uids = array();
if (isset($servers[$server]["players"])) foreach ($servers[$server]["players"] as $value)
{
    if ($value == "done") break;
    if (strpos($value, "=") === false) continue;
    list($k, $v) = explode("=", $value, 2);
    if (strpos($v, ",") === false) continue;
    $vs = explode(",", $v);
    if (intval($vs[1] > 0)) array_push($uids, $vs[1]);
}
$uids = join(",", $uids);
if (empty($uids)) $uids = "999999999";
?>
<iframe src="fights.php?iframe=1&mode=<?php echo $mode; ?>&map=<?php echo $games["map"]; ?>&uids=<?php echo $uids; ?>" frameborder="0" width="100%" height="400" scrolling="no"></iframe>

<div>
  <table cellpadding="1" cellspacing="1">
    <tr>
      <th colspan="9" class="trtitle" style="position:relative;"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/user.gif"><span>玩家信息</span></a><span style="position:absolute;top:0;left:0;right:0;bottom:0;text-align:center;"><?php echo $scores; ?></span></th>
    </tr>
    <tr style="color:#FFBB00;background-color:#444444;">
      <th>序号</th>
      <th>权限</th>
      <th style="width:30%;">玩家</th>
      <th>得分</th>
      <th>阵亡</th>
      <th>局数</th>
      <th>时长</th>
      <th>来自</th>
      <th>STEAMID</th>
    </tr>
<?php
$players = array();
if (isset($servers[$server]["players"])) foreach ($servers[$server]["players"] as $value)
{
    if ($value == "done") break;
    if (strpos($value, "=") === false) continue;
    list($k, $v) = explode("=", $value, 2);
    if (strpos($v, ",") === false) continue;
    $vs = explode(",", $v);
    if (intval($vs[2]) == 0) $vs[2] = "3";
    array_push($players, $vs);
}
usort($players, function($a, $b) {
    if ($a[2] != $b[2]) return $a[2] - $b[2];
    if ($a[5] != $b[5]) return $b[5] - $a[5];
    if ($a[6] != $b[6]) return $a[6] - $b[6];
    return 0;
});

$colors = array("#CCBB99", "#FF6600", "#0099FF", "#CCBB99");

$i = 0;
foreach ($players as $player)
{
    echo "    <tr class=\"colorbg" . ($i++ % 2 ? 1 : 2) . "\" style=\"color:" . $colors[intval($player[2])] . ";\">\n";
    echo "      <td>" . $player[0] . "</td>\n";
    echo "      <td>" . as_formatFlags($player[3]) . "</td>\n";
    echo intval($player[1]) == 0 ? ("      <td>" . as_fixHtmlString(as_hex2string($player[4])) . "</td>\n") : ("      <td><a href=\"stats.php?mode=" . $mode . "&uid=" . $player[1] . "\" style=\"color:" . $colors[intval($player[2])] . ";\">" . as_hex2string($player[4]) . "</a></td>\n");
    echo "      <td>" . $player[5] . "</td>\n";
    echo "      <td>" . $player[6] . "</td>\n";
    echo "      <td>" . $player[7] . "</td>\n";
    echo "      <td>" . as_formatTime($player[8]) . "</td>\n";
    echo "      <td>" . Look_IP($player[9]) . "</td>\n";
    echo "      <td>" . $player[10] . "</td>\n";
    echo "    </tr>\n";
}
?>
  </table>
</div>

<div style="margin-top:20px;">
  <table cellpadding="1" cellspacing="1">
    <tr>
      <th colspan="4" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/stats.gif"><span>服务器参数</span></a></th>
    </tr>
    <tr style="color:#FFBB00;background-color:#444444;">
      <th>参数</th>
      <th>内容</th>
      <th>参数</th>
      <th>内容</th>
    </tr>
<?php
$i = 0;
foreach ($configs as $key => $value)
{
    if ($i && $i % 2 == 0) echo "    </tr>\n";
    if ($i % 2 == 0) echo "    <tr class=\"colorbg" . ($i % 4 ? 1 : 2) . "\">\n";
    echo "      <td style=\"color:#0099FF;\">" . $key . "</td><td style=\"color:#FF6600;\">" . $value . "</td>\n";
    $i++;
}

if ($i)
{
    if ($i % 2) echo "      <td></td>\n      <td></td>\n";
    echo  "    </tr>\n";
}
?>
  </table>
</div>

<?php
}
?>
</body>
</html>
