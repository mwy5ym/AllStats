<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<link href="as.css" rel="stylesheet" type="text/css">
<?php
include "core.php";
$timestamp = time();
$iframe = as_get_param_number("iframe");
$mode = as_get_param_string("mode"); if (empty($mode)) $mode = "1";
$map = as_get_param_string("map"); if (empty($map)) $map = "All";
$uid = as_get_param_number("uid");
$order = as_get_param_string("order"); if (empty($order)) $order = $iframe ? "shot" : "wid";

$columns = $uid ? "*, `kill` / `death` as `kd_rate`, `kill_hs` / `kill` as `kill_hs_rate`, `kill_ws` / `kill` as `kill_ws_rate`, `hit_ws` / `hit` as `hit_ws_rate`, `hit` / `shot` as `hit_rate`" : "`wid`, sum(`kill`) AS `kill`, sum(`kill_hs`) AS `kill_hs`, sum(`kill_hs`) / sum(`kill`) as `kill_hs_rate`, sum(`kill_ws`) AS `kill_ws`, sum(`kill_ws`) / sum(`kill`) as `kill_ws_rate`, sum(`kill_hws`) AS `kill_hws`, sum(`killed`) AS `killed`, sum(`killed_hs`) AS `killed_hs`, sum(`killed_ws`) AS `killed_ws`, sum(`killed_hws`) AS `killed_hws`, sum(`death`) AS `death`, sum(`kill`) / sum(`death`) as `kd_rate`, sum(`shot`) AS `shot`, sum(`hit`) AS `hit`, sum(`hit`) / sum(`shot`) as `hit_rate`, sum(`hit_hs`) AS `hit_hs`, sum(`hit_ws`) AS `hit_ws`, sum(`hit_ws`) / sum(`hit`) as `hit_ws_rate`, sum(`hit_hws`) as `hit_hws`, sum(`damage`) as `damage`";
$sql = ($map == "All" ?
    "select " . $columns . " from `as_all_weapons` where `mode` = " . $mode . " and `tflag` = '0'" :
    "select " . $columns . " from `as_map_weapons` where `mode` = " . $mode . " and `map` = '" . $map . "' and `tflag` = '0'");
if ($uid) $sql .= " and `uid` = " . $uid; else $sql .= " group by `wid`";
$sql .= " order by `" . $order . "` " . ($order == "wid" ? "asc" : "desc");
$weaponsResult = mysqli_query($asdb, $sql);
?>
<title><?php echo $uid ? $uid . " " : ""; ?> Weapons<?php echo $map == "All" ? "" : (" @" . $map); ?></title>
</head>
<body>
<?php
if (!$iframe)
{
    echo "\n<div class=\"menu\">\n";
?>
  <span class="select">
    <a>选择模式</a>
    <div>
      <a href="weapons.php?mode=1&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 1) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">休闲模式</a>
      <a href="weapons.php?mode=2&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 2) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">竞技模式</a>
      <a href="weapons.php?mode=3&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 3) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">死亡竞赛</a>
      <a href="weapons.php?mode=4&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 4) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">僵尸模式</a>
      <a href="weapons.php?mode=5&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 5) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义一</a>
      <a href="weapons.php?mode=6&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 6) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义二</a>
      <a href="weapons.php?mode=7&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 7) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义三</a>
      <a href="weapons.php?mode=8&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 8) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义四</a>
    </div>
  </span>
  <a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:#FF6600;">排行</span></a>
  <a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><span style="background-color:#0099FF;">刷新</span></a>
  <a href="weapons.php?mode=<?php echo $mode; ?>&map=All&uid=<?php echo $uid; ?>&order=<?php echo $order; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:<?php echo ($map == "All" ? "#FF6600;" : "#00CC00;"); ?>">All</span></a>
<?php
$mapsResult = mysqli_query($asdb, "select distinct `map` from `as_map_rounds` where `mode` = " . $mode . ($uid ? " and `uid` = " . $uid : "") . " order by `map`;");
if ($mapsResult)
{
    while ($maps = mysqli_fetch_array($mapsResult))
        echo "  <span style=\"background-color:" . ($map == $maps["map"] ? "#FF6600" : "#00CC00") . ";\"><a href=\"weapons.php?mode=" . $mode . "&map=" . $maps["map"] . "&uid=" . $uid . "&order=" . $order . "&_=" . $timestamp . "\">" . $maps["map"] . "</a></span>\n";
    mysqli_free_result($mapsResult);
}
echo "</div>\n";
echo "\n<div>\n";
}
else
{
echo "\n<div style=\"margin-top:20px;\">\n";
}
?>
  <div>
    <div id="weapons">
      <table cellpadding="1" cellspacing="1">
        <tbody>
          <tr>
            <th colspan="12" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/weapons.gif"><span>武器数据</span></a></th>
          </tr>
          <tr class="colortitle colorbg1">
            <th width="12%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=wid&_=<?php echo $timestamp; ?>"<?php if ($order == "wid") echo " style=\"color:#FF6600;\""; ?>>武器</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=kill&_=<?php echo $timestamp; ?>"<?php if ($order == "kill") echo " style=\"color:#FF6600;\""; ?>>杀敌</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=death&_=<?php echo $timestamp; ?>"<?php if ($order == "death") echo " style=\"color:#FF6600;\""; ?>>阵亡</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=kd_rate&_=<?php echo $timestamp; ?>"<?php if ($order == "kd_rate") echo " style=\"color:#FF6600;\""; ?>>阵亡比</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=killed&_=<?php echo $timestamp; ?>"<?php if ($order == "killed") echo " style=\"color:#FF6600;\""; ?>>被杀</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=kill_hs&_=<?php echo $timestamp; ?>"<?php if ($order == "kill_hs") echo " style=\"color:#FF6600;\""; ?>>爆头</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=kill_hs_rate&_=<?php echo $timestamp; ?>"<?php if ($order == "kill_hs_rate") echo " style=\"color:#FF6600;\""; ?>>爆头率</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=hit&_=<?php echo $timestamp; ?>"<?php if ($order == "hit") echo " style=\"color:#FF6600;\""; ?>>命中</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=shot&_=<?php echo $timestamp; ?>"<?php if ($order == "shot") echo " style=\"color:#FF6600;\""; ?>>开火</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=hit_rate&_=<?php echo $timestamp; ?>"<?php if ($order == "hit_rate") echo " style=\"color:#FF6600;\""; ?>>命中率</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=hit_ws&_=<?php echo $timestamp; ?>"<?php if ($order == "hit_ws") echo " style=\"color:#FF6600;\""; ?>>穿墙</a></th>
            <th width="8%"><a href="weapons.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&order=hit_ws_rate&_=<?php echo $timestamp; ?>"<?php if ($order == "hit_ws_rate") echo " style=\"color:#FF6600;\""; ?>>穿墙率</a></th>
          </tr>
<?php
if ($weaponsResult)
{
    $i = 0;
    while ($weapons = mysqli_fetch_array($weaponsResult))
    {
        if ($weapons["wid"] == 2 || $weapons["wid"] == 9 || $weapons["wid"] == 25 || $weapons["wid"] == 31 || $weapons["wid"] == 32) continue;
?>
          <tr class="colorbg<?php echo $i++ % 2 ? 1: 2; ?>">
            <td><img src="images/items/<?php echo $weapons["wid"]; ?>.png" title="<?php echo $WeaponName[$weapons["wid"]]; ?>"></td>
            <td><?php echo $weapons["kill"]; ?></td>
            <td><?php echo $weapons["death"]; ?></td>
            <td><?php echo number_format(as_apb($weapons["kill"], $weapons["death"], 1), 2, ".", ""); ?></td>
            <td><?php echo $weapons["killed"]; ?></td>
            <td><?php echo $weapons["kill_hs"]; ?></td>
            <td><?php echo number_format(as_apb($weapons["kill_hs"], $weapons["kill"], 100), 2, ".", ""); ?>%</td>
            <td><?php echo $weapons["hit"]; ?></td>
            <td><?php echo $weapons["shot"]; ?></td>
            <td><?php echo number_format(as_apb($weapons["hit"], $weapons["shot"], 100), 2, ".", ""); ?>%</td>
            <td><?php echo $weapons["hit_ws"]; ?></td>
            <td><?php echo number_format(as_apb($weapons["hit_ws"], $weapons["hit"], 100), 2, ".", ""); ?>%</td>
          </tr>
<?php
    }
    mysqli_free_result($weaponsResult);
}
?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
