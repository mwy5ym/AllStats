<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<link href="as.css" rel="stylesheet" type="text/css">
<?php
include "core.php";
$timestamp = time();
$mode = as_get_param_string("mode"); if (empty($mode)) $mode = "1";
$map = as_get_param_string("map"); if (empty($map)) $map = "All";
$order = strtolower(as_get_param_string("order")); if (empty($order)) $order = "score_ranking";
$uid = as_get_param_number("uid");
$uids = as_get_param_string("uids"); $uidArray = array(); foreach (explode(",", $uids) as $id) { $id = intval($id); if ($id > 0) array_push($uidArray, $id); } $uids = join(",", $uidArray);
$name = as_get_param_string("name");

if (empty($uids))
{
    $total = as_get_user_count($mode, $map, $dzdb ? "" : $name);
    $count = as_get_param_number("count"); if ($count <= 0) $count = 32; if ($count > 100) $count = 100;
    $pageTotal = (int)(ceil($total / $count)); if ($pageTotal == 0) $pageTotal = 1;
    $pageDefault = 1; if (empty($name) && ($order == "score_ranking" || $order == "rating_ranking" || $order == "rws_ranking")) { $ranking = as_get_user_ranking($mode, $map, $order, $uid); if (0 < $ranking && $ranking <= $total) $pageDefault = (int)(ceil($ranking / $count)); }
    $page = as_get_param_number("page", $pageDefault); if ($page < 1 || $page > $pageTotal) $page = $pageTotal;
}
else
{
    $count = 32;
    $pageTotal = 1;
    $page = 1;
}
$columns = "`a`.*, `a`.`kill` / `a`.`death` as `kd_rate`, `a`.`kill_hs` / `a`.`kill` as `kill_hs_rate`, `a`.`kill_ws` / `a`.`kill` as `kill_ws_rate`, `a`.`hit_ws` / `a`.`hit` as `hit_ws_rate`, `a`.`hit` / `a`.`shot` as `hit_rate`";
$sql = ($map == "All" ?
    "select " . $columns . ", `u`.`name` as `name`, `r`.`score`, `r`.`scoreRanking`, `r`.`rating`, `r`.`ratingRanking`, `r`.`rws`, `r`.`rwsRanking` from `as_all_stats` as `a` inner join `as_users` as `u` on (`a`.`uid` = `u`.`uid`) inner join `as_all_rankings` as `r` on (`a`.`mode` = `r`.`mode` and `a`.`uid` = `r`.`uid`) where `a`.`mode` = " . $mode . "" :
    "select " . $columns . ", `u`.`name` as `name`, `r`.`score`, `r`.`scoreRanking`, `r`.`rating`, `r`.`ratingRanking`, `r`.`rws`, `r`.`rwsRanking` from `as_map_stats` as `a` inner join `as_users` as `u` on (`a`.`uid` = `u`.`uid`) inner join `as_map_rankings` as `r` on (`a`.`mode` = `r`.`mode` and `a`.`map` = `r`.`map` and `a`.`uid` = `r`.`uid`) where `a`.`mode` = " . $mode . " and `a`.`map` = '" . $map . "'");
if (!empty($uids)) $sql .= " and `a`.`uid` in (" . $uids . ")";
if (!empty($name))
{
    if ($dzdb)
    {
        $userSql = "select `uid` from `" . $dz_table_pre . "common_member_profile` where `" . $dz_field_name . "` like '%" . $name . "%' limit 100;";
        $userResult = mysqli_query($dzdb, $userSql);
        $userIds = array(); if ($userResult) { while ($row = mysqli_fetch_array($userResult)) array_push($userIds, $row["uid"]); mysqli_free_result($userResult); }
        $sql .= " and `u`.`uid` in (" . join(",", $userIds) . ")";
        $count = count($userIds);
        $pageTotal = 1;
        $page = 1;
    }
    else
    {
        $sql .= " and `u`.`name` like '%" . $name . "%'";
    }
}
switch ($order)
{
    case "score_ranking": $sql .= " order by `r`.`scoreRanking`"; break;
    case "rating_ranking": $sql .= " order by `r`.`ratingRanking`"; break;
    case "rws_ranking": $sql .= " order by `r`.`rwsRanking`"; break;
    case "level": $sql .= " order by `r`.`scoreRanking`"; break;
    case "kast": $sql .= " order by if(`a`.`t` + `a`.`ct` = 0, 0, `a`.`kast` / (`a`.`t` + `a`.`ct`)) desc"; break;
    case "round": $sql .= " order by (`a`.`t` + `a`.`ct`) desc"; break;
    default: $sql .= " order by `" . $order . "` desc"; break;
}
if (empty($uids)) $sql .= " limit " . (($page - 1) * $count) . ", " . $count;
$result = mysqli_query($asdb, $sql);
$rows = array(); if ($result) { while ($row = mysqli_fetch_array($result)) array_push($rows, $row); mysqli_free_result($result); }
if ($dzdb) as_rename_rows($rows, "uid", "name");
?>
<title>Top<?php echo $map == "All" ? "" : (" @" . $map); ?></title>
</head>
<body>
<?php
if (empty($uids))
{
?>

<div class="menu">
  <span class="select">
    <a>选择模式</a>
    <div>
      <a href="top.php?mode=1&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 1) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">休闲模式</a>
      <a href="top.php?mode=2&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 2) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">竞技模式</a>
      <a href="top.php?mode=3&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 3) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">死亡竞赛</a>
      <a href="top.php?mode=4&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 4) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">僵尸模式</a>
      <a href="top.php?mode=5&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 5) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义一</a>
      <a href="top.php?mode=6&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 6) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义二</a>
      <a href="top.php?mode=7&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 7) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义三</a>
      <a href="top.php?mode=8&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 8) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义四</a>
    </div>
  </span>
<?php
if (empty($uid)) echo "  <a href=\"register.php?_=" . $timestamp . "\"><span style=\"background-color:#FF6600;\">注册</span></a>\n";
?>
  <a href="servers.php?_=<?php echo $timestamp; ?>"><span style="background-color:#00CC00;">服务</span></a>
  <a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><span style="background-color:#0099FF;">刷新</span></a>
  <a href="top.php?mode=<?php echo $mode; ?>&map=All&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:<?php echo ($map == "All" ? "#FF6600;" : "#00CC00;"); ?>">All</span></a>
<?php
$mapsResult = mysqli_query($asdb, "select distinct `map` from `as_map_rounds` where `mode` = " . $mode . " order by `map`;");
if ($mapsResult)
{
    while ($maps = mysqli_fetch_array($mapsResult))
        echo "  <a href=\"top.php?mode=" . $mode . "&map=" . $maps["map"] . "&order=" . $order . "&uid=" . $uid . "&_=" . $timestamp . "\"><span style=\"background-color:" . ($map == $maps["map"] ? "#FF6600" : "#00CC00") . ";\">" . $maps["map"] . "</span></a>\n";
    mysqli_free_result($mapsResult);
}
?>
</div>
<?php
}
?>

<div style="<?php if (!empty($uids)) echo "margin-bottom:20px;"; ?>">
  <div>
    <div id="top">
      <table cellpadding="1" cellspacing="1">
        <tr style="color:#FFFFFF;background-color:#FF6600;">
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=score_ranking&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "score_ranking") echo " style=\"color:#000000;\""; ?>>得分</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rating_ranking&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "rating_ranking") echo " style=\"color:#000000;\""; ?>>评级</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rws_ranking&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "rws_ranking") echo " style=\"color:#000000;\""; ?>>战力</a></th>
          <th style="width:30%;" class="invalid"><a href="javascript:void(0);">玩家</a></th>
          <th style="text-align:right;"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=level&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "level") echo " style=\"color:#000000;\""; ?>>[军衔]</a></th>
          <th style="text-align:left;"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=score&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "score") echo " style=\"color:#000000;\""; ?>>得分</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rating&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "rating") echo " style=\"color:#000000;\""; ?>>评级</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rws&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "rws") echo " style=\"color:#000000;\""; ?>>战力</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=kd_rate&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "kd_rate") echo " style=\"color:#000000;\""; ?>>阵亡比</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=kill&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "kill") echo " style=\"color:#000000;\""; ?>>杀敌</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=death&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "death") echo " style=\"color:#000000;\""; ?>>阵亡</a></th>
          <th title="伤害助攻+闪光弹助攻"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=assist&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "assist") echo " style=\"color:#000000;\""; ?>>助攻</a></th>
          <th title="残局1vs1获胜"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=1_vs_1&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "1_vs_1") echo " style=\"color:#000000;\""; ?>>1vs1</a></th>
          <th title="残局1vs2获胜"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=1_vs_2&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "1_vs_2") echo " style=\"color:#000000;\""; ?>>1vs2</a></th>
          <th title="残局1vs3获胜"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=1_vs_3&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "1_vs_3") echo " style=\"color:#000000;\""; ?>>1vs3</a></th>
          <th title="残局1vs4获胜"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=1_vs_4&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "1_vs_4") echo " style=\"color:#000000;\""; ?>>1vs4</a></th>
          <th title="残局1vs5获胜"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=1_vs_5&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "1_vs_5") echo " style=\"color:#000000;\""; ?>>1vs5</a></th>
          <th title="非白给率"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=kast&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "kast") echo " style=\"color:#000000;\""; ?>>KAST</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=mvp&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "mvp") echo " style=\"color:#000000;\""; ?>>MVP</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=kill_hs_rate&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "kill_hs_rate") echo " style=\"color:#000000;\""; ?>>爆头率</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=hit_rate&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "hit_rate") echo " style=\"color:#000000;\""; ?>>命中率</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=round&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "round") echo " style=\"color:#000000;\""; ?>>局数</a></th>
          <th><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=time&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"<?php if ($order == "time") echo " style=\"color:#000000;\""; ?>>时长</a></th>
        </tr>
<?php
for ($i = 0; $i < count($rows); ++$i)
{
    $row = $rows[$i];
?>
        <tr class="colorbg<?php echo $row["uid"] == $uid ? 4 : ($i % 2 ? 1 : 2); ?>">
          <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=score_ranking&uid=<?php echo $row["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#FF6600;"><?php echo $row["scoreRanking"]; ?></a></td>
          <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rating_ranking&uid=<?php echo $row["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#00CC00;"><?php echo $row["ratingRanking"]; ?></a></td>
          <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rws_ranking&uid=<?php echo $row["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#0099FF;"><?php echo $row["rwsRanking"]; ?></a></td>
          <td class="tdnick tdcompact"><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $row["uid"]; ?>&_=<?php echo $timestamp; ?>"><?php echo as_fixHtmlString($row["name"]); ?></a></td>
          <td class="levelleft"><img src="images/levels/<?php echo as_get_level_id($row["score"]); ?>.jpg" title="<?php echo $LevelName[as_get_level_id($row["score"])]; ?>"></td>
          <td class="levelright"><span><?php echo number_format($row["score"], 2, ".", ""); ?></span></td>
          <td><?php echo number_format($row["rating"], 2, ".", ""); ?></td>
          <td><?php echo number_format($row["rws"], 2, ".", ""); ?></td>
          <td><?php echo number_format(as_apb($row["kill"], $row["death"], 1), 2, ".", ""); ?></td>
          <td><?php echo $row["kill"]; ?></td>
          <td><?php echo $row["death"]; ?></td>
          <td title="<?php echo $row["assist_by_damage"]; ?>+<?php echo $row["assist_by_flashbang"]; ?>"><?php echo $row["assist"]; ?></td>
          <td><?php echo $row["1_vs_1"]; ?></td>
          <td><?php echo $row["1_vs_2"]; ?></td>
          <td><?php echo $row["1_vs_3"]; ?></td>
          <td><?php echo $row["1_vs_4"]; ?></td>
          <td><?php echo $row["1_vs_5"]; ?></td>
          <td><?php echo number_format(as_apb($row["kast"], $row["t"] + $row["ct"], 100), 2, ".", "") . "%"; ?></td>
          <td><?php echo $row["mvp"]; ?></td>
          <td><?php echo number_format(as_apb($row["kill_hs"], $row["kill"], 100), 2, ".", "") . "%"; ?></td>
          <td><?php echo number_format(as_apb($row["hit"], $row["shot"], 100), 2, ".", "") . "%"; ?></td>
          <td><?php echo $row["t"] + $row["ct"]; ?></td>
          <td><?php echo as_formatTime((int)($row["time"]), true); ?></td>
        </tr>
<?php
}
if (!$i)
{
    $hint = "";
    if (!empty($uids)) $hint .= " uids[" . $uids . "]";
    if (!empty($name)) $hint .= " name[" . $name . "]";
    echo "        <tr><td colspan=\"14\">无记录" . $hint . "</td></tr>\n";
}
?>
      </table>
    </div>
<?php
if (empty($uids))
{
?>
    <div class="page">
      <table>
        <form name="search" action="top.php?map=<?php echo $map; ?>&order=<?php echo $order; ?>">
        <tr>
          <td style="width:200px;">
            <input name="name" type="text" size="10" maxlength="32" class="input_text">
            <input type="submit" value="查找玩家" class="input_button">
          </td>
          <td class="out"><div class="in"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&page=<?php echo $page - 1; ?>&count=<?php echo $count; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>"><img src="images/icons/left.gif">上一页</a></div></td>
<?php
$pageMin = 1;
$pageMax = $pageTotal;
if ($pageMin < $page - 5) $pageMin = $page - 5;
if ($pageMax > $page + 5) $pageMax = $page + 5;
if ($pageMin > 1)
    echo "          <td class=\"o\"><a href=\"top.php?mode=" . $mode . "&map=" . $map . "&order=" . $order . "&uid=" . $uid . "&page=1&count=" . $count . "&uids=" . $uids . "&name=" . $name . "&_=" . $timestamp . "\">" . ($pageMin == 1 + 1 ? "1" : "1...") . "</a></td>\n";
for ($pageIndex = $pageMin; $pageIndex <= $pageMax; ++$pageIndex)
    echo "          <td class=\"" . ($pageIndex == $page ? "n" : "o") . "\"><a href=\"top.php?mode=" . $mode . "&map=" . $map . "&order=" . $order . "&uid=" . $uid . "&page=" . $pageIndex . "&count=" . $count . "&uids=" . $uids . "&name=" . $name . "&_=" . $timestamp . "\">" . $pageIndex . "</a></td>\n";
if ($pageMax < $pageTotal)
    echo "          <td class=\"o\"><a href=\"top.php?mode=" . $mode . "&map=" . $map . "&order=" . $order . "&uid=" . $uid . "&page=" . $pageTotal . "&count=" . $count . "&uids=" . $uids . "&name=" . $name . "&_=" . $timestamp . "\">" . ($pageMax == $pageTotal - 1 ? "" : "...") . $pageTotal . "</a></td>\n";
?>
          <td class="out"><div class="in"><a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&page=<?php echo $page + 1; ?>&count=<?php echo $count; ?>&uids=<?php echo $uids; ?>&name=<?php echo $name; ?>&_=<?php echo $timestamp; ?>">下一页<img src="images/icons/right.gif"></a></div></td>
        </tr>
        </form>
      </table>
    </div>
<?php
}
?>
  </div>
</div>
<?php
if (!empty($uids))
{
echo "\n";
?>
<iframe src="fights.php?iframe=1&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&_=<?php echo $timestamp; ?>" frameborder="0" width="100%" height="400" scrolling="no"></iframe>
<?php
}
echo "\n";
?>
</body>
</html>
