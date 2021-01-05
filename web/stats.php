<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<link href="as.css" rel="stylesheet" type="text/css">
<?php
include "cz.php";
include "core.php";
$timestamp = time();
$mode = as_get_param_string("mode"); if (empty($mode)) $mode = "1";
$map = as_get_param_string("map"); if (empty($map)) $map = "All";
$order = strtolower(as_get_param_string("order")); if ($order != "score_ranking" && $order != "rating_ranking" && $order != "rws_ranking") $order = "score_ranking";
$uid = as_get_param_number("uid");

$userResult = mysqli_query($asdb, "select * from `as_users` where `uid` = " . $uid . " limit 1;");
$user = mysqli_fetch_array($userResult);
mysqli_free_result($userResult);
if ($dzdb) if ($dzname = as_get_user_name($uid)) $user["name"] = $dzname;
if ($dzdb) if ($dzflags = as_get_user_flags($uid)) $user["flags"] = $dzflags;
if ($dzdb) if ($dzsignature = as_get_user_signature($uid)) $user["signature"] = $dzsignature;
if ($dzdb) if ($dzsteamid = as_get_user_steamid($uid)) $user["authid"] = $dzsteamid;
if ($dzdb) if ($dzregtimestamp = as_get_user_regtimestamp($uid)) $user["regtimestamp"] = $dzregtimestamp;

$rankingResult = ($map == "All" ?
    mysqli_query($asdb, "select * from `as_all_rankings` where `mode` = " . $mode . " and `uid` = " . $uid . " limit 1;") :
    mysqli_query($asdb, "select * from `as_map_rankings` where `mode` = " . $mode . " and `map` = '" . $map . "' and `uid` = " . $uid . " limit 1;"));
$ranking = mysqli_fetch_array($rankingResult);
mysqli_free_result($rankingResult);

$rankingsCount = $dzdb ? 23 : 21;
$rankingsCountHalf = (int)($rankingsCount / 2);
$min = 1;
$max = as_get_user_count($mode, $map);
$rankingMin = $min;
$rankingMax = $max;
if ($rankingMin < $ranking[str_replace("_r", "R", $order)] - $rankingsCountHalf) $rankingMin = $ranking[str_replace("_r", "R", $order)] - $rankingsCountHalf;
if ($rankingMax > $ranking[str_replace("_r", "R", $order)] + $rankingsCountHalf) $rankingMax = $ranking[str_replace("_r", "R", $order)] + $rankingsCountHalf;
if ($ranking[str_replace("_r", "R", $order)] - $rankingsCountHalf < $rankingMin) $rankingMax += $rankingsCountHalf - ($ranking[str_replace("_r", "R", $order)] - $rankingMin);
if ($ranking[str_replace("_r", "R", $order)] + $rankingsCountHalf > $rankingMax) $rankingMin -= $rankingsCountHalf + ($ranking[str_replace("_r", "R", $order)] - $rankingMax);
if ($rankingMin < $min) $rankingMin = $min;
if ($rankingMax > $max) $rankingMax = $max;

$rankingsResult = ($map == "All" ?
    mysqli_query($asdb, "select `r`.*, `u`.`name` from `as_all_rankings` as `r` join `as_users` as `u` on `r`.`uid` = `u`.`uid` where `r`.`mode` = " . $mode . " and " . $rankingMin . " <= `r`.`" . (str_replace("_r", "R", $order)) . "` and `r`.`" . (str_replace("_r", "R", $order)) . "` <= " . $rankingMax . " order by `r`.`" . (str_replace("_r", "R", $order)) . "`;") :
    mysqli_query($asdb, "select `r`.*, `u`.`name` from `as_map_rankings` as `r` join `as_users` as `u` on `r`.`uid` = `u`.`uid` where `r`.`mode` = " . $mode . " and `r`.`map` = '" . $map . "' and " . $rankingMin . " <= `r`.`" . (str_replace("_r", "R", $order)) . "` and `r`.`" . (str_replace("_r", "R", $order)) . "` <= " . $rankingMax . " order by `r`.`" . (str_replace("_r", "R", $order)) . "`;"));
$rankingsRows = array(); if ($rankingsResult) { while ($row = mysqli_fetch_array($rankingsResult)) array_push($rankingsRows, $row); mysqli_free_result($rankingsResult); }
if ($dzdb) as_rename_rows($rankingsRows, "uid", "name");

$rankingsStatsRows = array();
$uids = array_map(function($e) { return $e["uid"]; }, $rankingsRows);
if (!empty($uids))
{
    $rankingsStatsResult = mysqli_query($asdb, "select * from `as_all_stats` where `mode` = " . $mode . " and `uid` in (" . join(",", $uids) . ");");
    if ($rankingsStatsResult) { while ($row = mysqli_fetch_array($rankingsStatsResult)) $rankingsStatsRows[$row["uid"]] = $row; mysqli_free_result($rankingsStatsResult); }
}

$statsResult = ($map == "All" ?
    mysqli_query($asdb, "select * from `as_all_stats` where `mode` = " . $mode . " and `uid` = " . $uid . " limit 1;") :
    mysqli_query($asdb, "select * from `as_map_stats` where `mode` = " . $mode . " and `map` = '" . $map . "' and `uid` = " . $uid . " limit 1;"));
$stats = mysqli_fetch_array($statsResult);
mysqli_free_result($statsResult);

$bodyhitsResult = ($map == "All" ?
    mysqli_query($asdb, "select * from `as_all_bodyhits_sum` where `mode` = " . $mode . " and `uid` = " . $uid . " and `tflag` = '0' limit 1;") :
    mysqli_query($asdb, "select * from `as_map_bodyhits_sum` where `mode` = " . $mode . " and `map` = '" . $map . "' and `uid` = " . $uid . " and `tflag` = '0' limit 1;"));
$bodyhits = mysqli_fetch_array($bodyhitsResult);
mysqli_free_result($bodyhitsResult);
$bodyhitsTotal = $bodyhits["generic"] + $bodyhits["head"] + $bodyhits["chest"] + $bodyhits["stomach"] + $bodyhits["leftarm"] + $bodyhits["rightarm"] + $bodyhits["leftleg"] + $bodyhits["rightleg"];
$bodyhitsHead = (int)($bodyhits["head"]);
$bodyhitsBody = $bodyhits["generic"] + $bodyhits["chest"] + $bodyhits["stomach"];
$bodyhitsArms = $bodyhits["leftarm"] + $bodyhits["rightarm"];
$bodyhitsLegs = $bodyhits["leftleg"] + $bodyhits["rightleg"];
$bodyhitsOrders = as_get_bodyhits_orders([$bodyhitsHead, $bodyhitsBody, $bodyhitsArms, $bodyhitsLegs]);

$assistBoth = (int)($stats["assist_by_damage"]) + (int)($stats["assist_by_flashbang"]) - (int)($stats["assist"]);
$roundTotal = (int)($stats["t"]) + (int)($stats["ct"]);
$time = (int)($stats["time"]);
?>
<title><?php echo $user["name"]; ?> Stats<?php echo $map == "All" ? "" : (" @" . $map); ?></title>
</head>
<body>

<div class="menu">
  <span class="select">
    <a>选择模式</a>
    <div>
      <a href="stats.php?mode=1&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 1) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">休闲模式</a>
      <a href="stats.php?mode=2&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 2) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">竞技模式</a>
      <a href="stats.php?mode=3&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 3) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">死亡竞赛</a>
      <a href="stats.php?mode=4&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 4) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">僵尸模式</a>
      <a href="stats.php?mode=5&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 5) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义一</a>
      <a href="stats.php?mode=6&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 6) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义二</a>
      <a href="stats.php?mode=7&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 7) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义三</a>
      <a href="stats.php?mode=8&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 8) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义四</a>
    </div>
  </span>
  <a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:#FF6600;">排行</span></a>
  <a href="servers.php?_=<?php echo $timestamp; ?>"><span style="background-color:#00CC00;">服务</span></a>
  <a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><span style="background-color:#0099FF;">刷新</span></a>
  <a href="stats.php?mode=<?php echo $mode; ?>&map=All&order=<?php echo $order; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:<?php echo ($map == "All" ? "#FF6600;" : "#00CC00;"); ?>">All</span></a>
<?php
$mapsResult = mysqli_query($asdb, "select distinct `map` from `as_map_rounds` where `mode` = " . $mode . " and `uid` = " . $uid . " order by `map`;");
if ($mapsResult)
{
    while ($maps = mysqli_fetch_array($mapsResult))
        echo "  <a href=\"stats.php?mode=" . $mode . "&map=" . $maps["map"] . "&order=" . $order . "&uid=" . $uid . "&_=" . $timestamp . "\"><span style=\"background-color:" . ($map == $maps["map"] ? "#FF6600" : "#00CC00") . ";\">" . $maps["map"] . "</span></a>\n";
    mysqli_free_result($mapsResult);
}
?>
</div>

<iframe src="fights.php?iframe=1&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" frameborder="0" width="100%" height="400" scrolling="no"></iframe>

<table>
  <tbody>
    <tr>
      <th style="vertical-align:top;">
        <div id="user">
          <table cellpadding="1" cellspacing="1">
            <tbody>
              <tr><?php $i = 1; ?>
                <th colspan="2" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/user.gif"><span>玩家资料</span></a></th>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <th class="colortitle">玩家编号</th>
                <th><?php echo $uid; ?></th>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">玩家头像</td>
                <td style="height:125px;"><img src="<?php echo as_get_user_image($uid); ?>" style="width:120px;height:120px;"></td>
              </tr>
<?php
if ($dzdb)
{
?>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">用户账号</td>
                <td><a href="<?php echo $dz_url . "home.php?mod=space&uid=" . $uid; ?>" style="color:#99CCFF;"><?php echo as_fixHtmlString(as_get_user_dzname($uid)); ?></a></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">用户分组</td>
                <td><div style="color:#FF9900;"><?php echo as_fixHtmlString(as_get_user_dzgroup($uid)); ?></div></td>
              </tr>
<?php
}
?>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">SteamID</td>
                <td class="tdcompact"><a href="<?php echo as_get_user_steam_profile($user["authid"]); ?>" style="color:#99CCFF;"><?php echo $user["authid"]; ?></a></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">玩家名称</td>
                <td class="tdnick tdcompact"><?php echo as_fixHtmlString($user["name"]); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">玩家权限</td>
                <td><div style="color:#FF6600;"><?php echo as_formatFlags($user["flags"]); ?></div></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">玩家排名</td>
                <td><b style="color:#FF6600;"><?php echo $ranking["scoreRanking"]; ?></b> | <b style="color:#00CC00;"><?php echo $ranking["ratingRanking"]; ?></b> | <b style="color:#0099FF;"><?php echo $ranking["rwsRanking"]; ?></b></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">综合得分</td>
                <td style="color:#FF6600;"><?php echo number_format($ranking["score"], 2, ".", ""); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">技术评级</td>
                <td style="color:#00CC00;"><?php echo number_format($ranking["rating"], 2, ".", ""); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">回合战力</td>
                <td style="color:#0099FF;"><?php echo number_format($ranking["rws"], 2, ".", ""); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">军衔等级</td>
                <td><img src="images/levels/<?php echo as_get_level_id($ranking["score"]); ?>.jpg" height="100%"><span><?php echo $LevelName[as_get_level_id($ranking["score"])]; ?></span></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">升级还需</td>
                <td><?php echo number_format($LevelScore[as_get_level_id2($ranking["score"])] - $ranking["score"], 2, ".", ""); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">下一等级</td>
                <td><img src="images/levels/<?php echo as_get_level_id2($ranking["score"]); ?>.jpg" height="100%"><span><?php echo $LevelName[as_get_level_id2($ranking["score"])]; ?></span></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">注册时间</td>
                <td><?php echo $user["regtimestamp"]; ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">最后在线</td>
                <td><?php echo $user["lasttimestamp"]; ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">最后登录</td>
                <td><?php echo Look_IP($user["lastipaddress"]); ?></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">游戏道具</td>
                <td><div style="color:#FF6600;"><?php echo "暂未开放:)"; ?></div></td>
              </tr>
              <tr class="colorbg<?php echo ++$i % 2 + 1; ?>">
                <td class="colortitle">个性签名</td>
                <td class="tdcompact"><div style="color:#00CC00;"><?php echo as_fixHtmlString($user["signature"]); ?></div></td>
              </tr>
            </tbody>
          </table>
        </div>
      </th>
      <th style="width:70%;vertical-align:top;">
        <div id="rankings">
          <table cellpadding="1" cellspacing="1">
            <tbody>
              <tr>
                <th colspan="9" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/rankings.gif"><span>玩家排名</span></a></th>
              </tr>
              <tr class="colortitle colorbg2">
                <th width="10%"><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=score_ranking&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>">得分</a></th>
                <th width="10%"><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rating_ranking&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>">评级</a></th>
                <th width="10%"><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rws_ranking&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>">战力</a></th>
                <th width="30%">玩家</th>
                <th width="5%">KD</th>
                <th width="5%" style="text-align:right;">[军衔]</th>
                <th width="10%" style="text-align:left;">得分</th>
                <th width="10%">评级</th>
                <th width="10%">战力</th>
              </tr>
<?php
for ($i = 0; $i < $rankingsCount; ++$i)
{
    $rankings = $i < count($rankingsRows) ? $rankingsRows[$i] : array("uid" => "", "name" => "", "score" => 0.0, "scoreRanking" => "", "rating" => 1.0, "ratingRanking" => "", "rws" => 0.0, "rwsRanking" => "");
    $rankingsStats = ($rankings["uid"] && array_key_exists($rankings["uid"], $rankingsStatsRows)) ? $rankingsStatsRows[$rankings["uid"]] : array();
?>
              <tr class="colorbg<?php echo $uid && $rankings["uid"] == $uid ? 4 : ($i % 2 ? 2 : 1); ?>">
                <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=score_ranking&uid=<?php echo $rankings["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#FF6600;"><?php echo $rankings["scoreRanking"]; ?></a></td>
                <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rating_ranking&uid=<?php echo $rankings["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#00CC00;"><?php echo $rankings["ratingRanking"]; ?></a></td>
                <td><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=rws_ranking&uid=<?php echo $rankings["uid"]; ?>&_=<?php echo $timestamp; ?>" style="color:#0099FF;"><?php echo $rankings["rwsRanking"]; ?></a></td>
                <td class="tdnick tdcompact"><a href="stats.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&order=<?php echo $order; ?>&uid=<?php echo $rankings["uid"]; ?>&_=<?php echo $timestamp; ?>"><?php echo as_fixHtmlString($rankings["name"]); ?></a></td>
                <td><?php echo number_format(as_apb($rankingsStats["kill"], $rankingsStats["death"], 1), 2, ".", ""); ?></td>
                <td class="levelleft"><img src="images/levels/<?php echo as_get_level_id($rankings["score"]); ?>.jpg" title="<?php echo $LevelName[as_get_level_id($rankings["score"])]; ?>"></td>
                <td class="levelright"><span><?php echo number_format($rankings["score"], 2, ".", ""); ?></span></td>
                <td><?php echo number_format($rankings["rating"], 2, ".", ""); ?></td>
                <td><?php echo number_format($rankings["rws"], 2, ".", ""); ?></td>
              </tr>
<?php
}
?>
            </tbody>
          </table>
        </div>
      </th>
    </tr>
  </tbody>
</table>

<div style="margin-top:20px;">
  <div>
    <div id="stats">
      <table cellpadding="1" cellspacing="1">
        <tbody>
          <tr>
            <th colspan="9" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/stats.gif"><span>数据总览</span></a></th>
          </tr>
          <tr class="colorbg0">
            <td colspan="4" width="50%">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg1">
                    <td width="25%" class="tdhead"><img src="images/icons/ranking.gif"><span>排名</span></td>
                    <td width="25%" class="tdhead"><b style="color:#FF6600;"><?php echo $ranking["scoreRanking"]; ?></b> | <b style="color:#00CC00;"><?php echo $ranking["ratingRanking"]; ?></b> | <b style="color:#0099FF;"><?php echo $ranking["rwsRanking"]; ?></b></td>
                    <td width="25%" class="tdhead"><img src="images/icons/score.gif"><span>得分</span></td>
                    <td width="25%" class="tdhead" style="color:#FF6600;"><?php echo number_format($ranking["score"], 2, ".", ""); ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4" width="50%">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg1">
                    <td width="25%" class="tdhead"><img src="images/icons/rating.gif"><span>评级</span></td>
                    <td width="25%" class="tdhead" style="color:#00CC00;"><?php echo number_format($ranking["rating"], 2, ".", ""); ?></td>
                    <td width="25%" class="tdhead"><img src="images/icons/rws.gif"><span>战力</span></td>
                    <td width="25%" class="tdhead" style="color:#0099FF;"><?php echo number_format($ranking["rws"], 2, ".", ""); ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan="9" class="tdspace"></td>
          </tr>
          <tr class="colorbg0">
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">杀敌</td>
                    <td width="50%"><b>杀敌 : 阵亡<?php echo number_format(as_apb($stats["kill"], $stats["death"], 1), 2, ".", ""); ?></b></td>
                    <td width="25%">阵亡</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["kill"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["kill"], $stats["death"] + $stats["kill"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["death"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">KAST</td>
                    <td width="50%"><b>非白给率<?php echo number_format(as_apb($stats["kast"], $roundTotal, 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">局数</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["kast"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["kast"], $roundTotal, 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $roundTotal; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan="9" class="tdspace"></td>
          </tr>
          <tr class="colorbg0">
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">伤害助攻</td>
                    <td width="50%"><b>助攻<?php echo $stats["assist"]; ?></b></td>
                    <td width="25%">闪光弹助攻</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["assist_by_damage"]; ?></td>
                    <td class="percents">
                      <span class="orange">
                        <span class="red progress" style="float:left;width:<?php echo number_format(as_apb($stats["assist_by_damage"] - $assistBoth, $stats["assist"], 100), 2, ".", ""); ?>%;"></span>
                        <span class="white progress" style="float:right;width:<?php echo number_format(as_apb($stats["assist_by_flashbang"] - $assistBoth, $stats["assist"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["assist_by_flashbang"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">命中</td>
                    <td width="50%"><b>命中率<?php echo number_format(as_apb($stats["hit"], $stats["shot"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">开火</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["hit"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["hit"], $stats["shot"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["shot"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan="9" class="tdspace"></td>
          </tr>
          <tr class="colorbg0">
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">爆头</td>
                    <td width="50%"><b>爆头率<?php echo number_format(as_apb($stats["kill_hs"], $stats["kill"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">杀敌</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["kill_hs"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["kill_hs"], $stats["kill"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["kill"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">穿墙</td>
                    <td width="50%"><b>穿墙率<?php echo number_format(as_apb($stats["hit_ws"], $stats["hit"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">命中</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["hit_ws"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["hit_ws"], $stats["hit"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["hit"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan="9" class="tdspace"></td>
          </tr>
          <tr class="colorbg0">
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">爆破</td>
                    <td width="50%"><b>爆破率<?php echo number_format(as_apb($stats["explode"], $stats["planted"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">埋包</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["explode"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["explode"], $stats["planted"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["planted"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">拆除</td>
                    <td width="50%"><b>拆除率<?php echo number_format(as_apb($stats["defused"], $stats["defusing"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">拆包</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["defused"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["defused"], $stats["defusing"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["defusing"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td colspan="9" class="tdspace"></td>
          </tr>
          <tr class="colorbg0">
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">获胜</td>
                    <td width="50%"><b>获胜率<?php echo number_format(as_apb($stats["win_t"], $stats["t"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">恐怖分子</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["win_t"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["win_t"], $stats["t"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["t"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
            <td class="tdspace"></td>
            <td colspan="4">
              <table cellpadding="1" cellspacing="1">
                <tbody>
                  <tr class="colorbg3">
                    <td width="25%">获胜</td>
                    <td width="50%"><b>获胜率<?php echo number_format(as_apb($stats["win_ct"], $stats["ct"], 100), 2, ".", ""); ?>%</b></td>
                    <td width="25%">反恐精英</td>
                  </tr>
                  <tr class="colorbg1">
                    <td><?php echo $stats["win_ct"]; ?></td>
                    <td class="percents">
                      <span class="blue">
                        <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["win_ct"], $stats["ct"], 100), 2, ".", ""); ?>%;"></span>
                      </span>
                    </td>
                    <td><?php echo $stats["ct"]; ?></td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</div>

<table style="margin-top:20px;">
  <tbody>
    <tr>
      <th style="vertical-align:top;">
        <div id="bodyhits" style="height:100%;padding-bottom:1px;">
          <table cellpadding="1" cellspacing="1" style="height:100%;">
            <tbody>
              <tr>
                <th class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/bodyhits.gif"><span>命中部位</a></th>
              </tr>
              <tr>
                <td class="colorbg1">
                  <div style="width:432px;height:375px;margin:auto;">
                    <div style="width:432px;height:70px;">
                      <div style="width:130px;height:50px;float:left;margin-left:38px;margin-top:20px">
                        <div class="dialog" style="width:120px;height:30px;">
                          <span>范围命中: <?php echo $bodyhits["generic"]; ?></span><br/>
                          <span>百分比: <?php echo number_format(as_apb($bodyhits["generic"], $bodyhitsTotal, 100), 2, ".", ""); ?>%</span>
                        </div>
                      </div>
                      <div style="width:98px;height:70px;float:left;">
                        <img width="98" height="70" src="images/bodyhits/head_<?php echo $bodyhitsOrders[0]; ?>.png">
                      </div>
                      <div style="width:166px;height:50px;float:left;margin-top:20px;">
                        <div class="dialog" style="width:120px;height:30px;margin-right:46px;">
                          <span>头部命中: <?php echo $bodyhitsHead; ?></span><br/>
                          <span>百分比: <?php echo number_format(as_apb($bodyhitsHead, $bodyhitsTotal, 100), 2, ".", ""); ?>%</span>
                        </div>
                      </div>
                    </div>
                    <div style="width:432px;height:123px;">
                      <div style="width:168px;height:123px;float:left;">
                        <div style="width:168px;height:56px;">
                          <img width="168" height="56" src="images/bodyhits/left_arm_<?php echo $bodyhitsOrders[2]; ?>.png">
                        </div>
                        <div style="width:130px;height:50px;margin-top:17px;margin-left:38px;">
                          <div class="dialog" style="width:120px;height:30px;">
                            <span>躯干命中: <?php echo $bodyhitsBody; ?></span><br/>
                            <span>百分比: <?php echo number_format(as_apb($bodyhitsBody, $bodyhitsTotal, 100), 2, ".", ""); ?>%</span>
                          </div>
                        </div>
                      </div>
                      <div style="width:96px;height:123px;float:left;">
                        <div style="width:96px;height:123px;">
                          <img width="96" height="123" src="images/bodyhits/chest_<?php echo $bodyhitsOrders[1]; ?>.png">
                        </div>
                      </div>
                      <div style="width:168px;height:123px;float:left;">
                        <div style="width:168px;height:56px;">
                          <img width="168" height="56" src="images/bodyhits/right_arm_<?php echo $bodyhitsOrders[2]; ?>.png">
                        </div>
                        <div style="width:166;height:67px;margin-left:2px;">
                          <div class="dialog" style="width:120px;height:30px;margin-right:46px;">
                            <span>手部命中: <?php echo $bodyhitsArms; ?></span><br/>
                            <span>百分比: <?php echo number_format(as_apb($bodyhitsArms, $bodyhitsTotal, 100), 2, ".", ""); ?>%</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div style="width:432px;height:182px;">
                      <div style="width:160px;height:182px;float:left;text-align:left;">
                        <div class="dialog" style="width:120px;height:15px;margin-top:85px;margin-left:20px;">
                            <span>命中数: <?php echo $stats["hit"]; ?></span>
                        </div>
                        <div class="dialog" style="width:120px;height:15px;margin-top:2px;margin-left:20px;">
                            <span>开火数: <?php echo $stats["shot"]; ?></span>
                        </div>
                        <div class="dialog" style="width:120px;height:15px;margin-top:2px;margin-left:20px;">
                            <span>命中率: <?php echo number_format(as_apb($stats["hit"], $stats["shot"], 100), 2, ".", ""); ?>%</span>
                        </div>
                      </div>
                      <div style="width:106px;height:182px;float:left;">
                        <img width="106" height="182" src="images/bodyhits/legs_<?php echo $bodyhitsOrders[3]; ?>.png">
                      </div>
                      <div style="width:166px;height:132px;float:left;margin-top:50px;">
                        <div class="dialog" style="width:120px;height:30px;margin-right:46px;">
                          <span>腿部命中: <?php echo $bodyhitsLegs; ?></span><br/>
                          <span>百分比: <?php echo number_format(as_apb($bodyhitsLegs, $bodyhitsTotal, 100), 2, ".", ""); ?>%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </th>
      <th style="width:100%;vertical-align:top;">
        <div id="rounds">
          <table cellpadding="1" cellspacing="1">
            <tbody>
              <tr>
                <th class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/rounds.gif"><span>游戏局数&nbsp;<?php echo $roundTotal; ?></span></a></th>
              </tr>
              <tr>
                <td>
                  <table cellpadding="1" cellspacing="1">
                    <tbody>
                      <tr>
                        <td><span style="color:#FF9900;">恐怖分子<?php echo $stats["t"]; ?>(<?php echo number_format(as_apb($stats["t"], $roundTotal, 100), 2, ".", ""); ?>%)</span></td>
                        <td><span style="color:#99CCFF;">反恐精英<?php echo $stats["ct"]; ?>(<?php echo number_format(as_apb($stats["ct"], $roundTotal, 100), 2, ".", ""); ?>%)</span></td>
                      </tr>
                      <tr>
                        <td colspan="2" class="percents">
                          <span class="gray">
                            <span class="orange progress" style="float:left;width:<?php echo number_format(as_apb($stats["t"], $roundTotal, 100), 2, ".", ""); ?>%;"></span>
                            <span class="blue progress" style="float:right;width:<?php echo number_format(as_apb($stats["ct"], $roundTotal, 100), 2, ".", ""); ?>%;"></span>
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
              <tr class="colorbg0">
                <td>
                  <table cellpadding="1" cellspacing="1">
                    <tbody>
                      <tr>
                        <td width="16%" class="colorbg1">每局杀敌</td>
                        <td width="16%" class="colorbg2"><?php echo number_format(as_apb($stats["kill"], $roundTotal, 1), 2, ".", ""); ?></td>
                        <td class="tdspace tdwide"></td>
                        <td width="16%" class="colorbg1">每局阵亡</td>
                        <td width="16%" class="colorbg2"><?php echo number_format(as_apb($stats["death"], $roundTotal, 1), 2, ".", ""); ?></td>
                        <td class="tdspace tdwide"></td>
                        <td width="16%" class="colorbg1">每局助攻</td>
                        <td width="16%"  class="colorbg2"><?php echo number_format(as_apb($stats["assist"], $roundTotal, 1), 2, ".", ""); ?></td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每局一杀</td>
                        <td class="colorbg2"><?php echo $stats["kill_1"]; ?>(<?php echo number_format(as_apb($stats["kill_1"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">残局1vs1</td>
                        <td class="colorbg2"><?php echo $stats["1_vs_1"]; ?>(<?php echo number_format(as_apb($stats["1_vs_1"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">最先杀敌</td>
                        <td class="colorbg2"><?php echo $stats["first_kill"]; ?>(<?php echo number_format(as_apb($stats["first_kill"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每局二杀</td>
                        <td class="colorbg2"><?php echo $stats["kill_2"]; ?>(<?php echo number_format(as_apb($stats["kill_2"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">残局1vs2</td>
                        <td class="colorbg2"><?php echo $stats["1_vs_2"]; ?>(<?php echo number_format(as_apb($stats["1_vs_2"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">最先阵亡</td>
                        <td class="colorbg2"><?php echo $stats["first_death"]; ?>(<?php echo number_format(as_apb($stats["first_kill"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每局三杀</td>
                        <td class="colorbg2"><?php echo $stats["kill_3"]; ?>(<?php echo number_format(as_apb($stats["kill_3"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">残局1vs3</td>
                        <td class="colorbg2"><?php echo $stats["1_vs_3"]; ?>(<?php echo number_format(as_apb($stats["1_vs_3"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">最后杀敌</td>
                        <td class="colorbg2"><?php echo $stats["last_kill"]; ?>(<?php echo number_format(as_apb($stats["last_kill"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每局四杀</td>
                        <td class="colorbg2"><?php echo $stats["kill_4"]; ?>(<?php echo number_format(as_apb($stats["kill_4"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">残局1vs4</td>
                        <td class="colorbg2"><?php echo $stats["1_vs_4"]; ?>(<?php echo number_format(as_apb($stats["1_vs_4"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">最后阵亡</td>
                        <td class="colorbg2"><?php echo $stats["last_death"]; ?>(<?php echo number_format(as_apb($stats["last_death"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每局五杀</td>
                        <td class="colorbg2"><?php echo $stats["kill_5"]; ?>(<?php echo number_format(as_apb($stats["kill_5"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">残局1vs5</td>
                        <td class="colorbg2"><?php echo $stats["1_vs_5"]; ?>(<?php echo number_format(as_apb($stats["1_vs_5"], $roundTotal, 100), 2, ".", ""); ?>%)</td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">MVP</td>
                        <td class="colorbg2"><?php echo $stats["mvp"]; ?></td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div id="time" style="margin-top:20px;">
          <table cellpadding="1" cellspacing="1">
            <tbody>
              <tr>
                <th colspan="5" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/time.gif"><span>游戏时长&nbsp;<?php echo as_formatTime($time); ?></span></a></th>
              </tr>
              <tr class="colorbg0">
                <td>
                  <table cellpadding="1" cellspacing="1">
                    <tbody>
                      <tr>
                        <td width="25%" class="colorbg1">总计伤害</td>
                        <td width="25%" class="colorbg2"><?php echo $stats["damage_real"]; ?></td>
                        <td class="tdspace tdwide"></td>
                        <td width="25%" class="colorbg1">每局伤害</td>
                        <td width="25%" class="colorbg2"><?php echo number_format(as_apb($stats["damage_real"], $roundTotal, 1), 2, ".", ""); ?></td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每小时伤害</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 3600) * as_apb($stats["damage_real"], $time, 1), 2, ".", ""); ?></td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">每分钟伤害</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 60) * as_apb($stats["damage_real"], $time, 1), 2, ".", ""); ?></td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每小时杀敌</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 3600) * as_apb($stats["kill"], $time, 1), 2, ".", ""); ?></td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">每分钟杀敌</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 60) * as_apb($stats["kill"], $time, 1), 2, ".", ""); ?></td>
                      </tr>
                      <tr>
                        <td class="colorbg1">每小时阵亡</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 3600) * as_apb($stats["death"], $time, 1), 2, ".", ""); ?></td>
                        <td class="tdspace tdwide"></td>
                        <td class="colorbg1">每分钟阵亡</td>
                        <td class="colorbg2"><?php echo number_format(min($time, 60) * as_apb($stats["death"], $time, 1), 2, ".", ""); ?></td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </th>
    </tr>
  </tbody>
</table>

<iframe src="weapons.php?iframe=1&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" frameborder="0" width="100%" height="950" scrolling="no"></iframe>

<script type="text/javascript">
var spans = document.getElementsByTagName("span")
function progress()
{
    var classname, change, width0, width1, flag = false;
    for (var i = 0; i < spans.length; ++i)
    {
        classname = spans[i].className;
        if (!classname || classname.indexOf("progress") == -1) continue;
        if (!spans[i].getAttribute("change"))
        {
            spans[i].setAttribute("width0", 0.0);
            spans[i].setAttribute("width1", parseFloat(spans[i].style.width.replace(/%/, "")));
            spans[i].setAttribute("change", parseFloat(spans[i].style.width.replace(/%/, "")) / 50);
        }
        width0 = parseFloat(spans[i].getAttribute("width0"));
        width1 = parseFloat(spans[i].getAttribute("width1"));
        change = parseFloat(spans[i].getAttribute("change"));
        if (width0 == width1) continue;
        width0 = width0 + change;
        if (width0 > width1) width0 = width1;
        spans[i].setAttribute("width0", width0);
        spans[i].style.width = width0 + "%";
        flag = true;
    }
    if (flag) setTimeout(progress, 20.0);
    else
    {
        for (var i = 0; i < spans.length; ++i) spans[i].removeAttribute("change");
        setTimeout(progress, 2000.0);
    }
}
progress();
</script>

</body>
</html>
