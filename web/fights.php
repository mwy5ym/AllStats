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
$uids = as_get_param_string("uids"); $uidArray = array(); foreach (explode(",", $uids) as $id) { $id = intval($id); if ($id > 0) array_push($uidArray, $id); } $uids = join(",", $uidArray); if ($uid > 0 && !in_array($uid, $uidArray)) array_push($uidArray, $uid); $uidsAll = join(",", $uidArray);

$total = as_get_fight_count($mode, $map, $uidsAll);
$count = as_get_param_number("count"); if ($count <= 0) $count = $iframe ? 10 : 100; if ($count > 100) $count = 100;
$pageTotal = (int)(ceil($total / $count)); if ($pageTotal == 0) $pageTotal = 1;
$page = as_get_param_number("page", 1); if ($page < 1 || $page > $pageTotal) $page = $pageTotal;

$sql = "select * from `as_fights` where `mode` = " . $mode;
if ($map != "All") $sql .= " and `map` = '" . $map . "'";
if (!empty($uidsAll)) $sql .= " and (`uid_a` in (" . $uidsAll . ") or `uid_v` in (" . $uidsAll . "))";
$sql .= " order by `timestamp` desc, `id` desc limit " . (($page - 1) * $count) . ", " . $count;
$fightsResult = mysqli_query($asdb, $sql);
$rows = array(); if ($fightsResult) { while ($row = mysqli_fetch_array($fightsResult)) array_push($rows, $row); mysqli_free_result($fightsResult); }
as_rename_rows($rows, "uid_a", "name_a", "uid_v", "name_v");

$healthMax = 100;
?>
<title><?php echo $uid ? $uid . " " : ""; ?>Fights<?php echo $map == "All" ? "" : (" @" . $map); ?></title>
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
      <a href="fights.php?mode=1&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 1) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">休闲模式</a>
      <a href="fights.php?mode=2&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 2) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">竞技模式</a>
      <a href="fights.php?mode=3&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 3) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">死亡竞赛</a>
      <a href="fights.php?mode=4&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 4) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">僵尸模式</a>
      <a href="fights.php?mode=5&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 5) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义一</a>
      <a href="fights.php?mode=6&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 6) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义二</a>
      <a href="fights.php?mode=7&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 7) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义三</a>
      <a href="fights.php?mode=8&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&_=<?php echo $timestamp; ?>" style="<?php if ($mode != 8) echo "display:none;"; else echo "color:#FFFFFF;"; ?>">自定义四</a>
    </div>
  </span>
  <a href="top.php?mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:#FF6600;">排行</span></a>
  <a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><span style="background-color:#0099FF;">刷新</span></a>
  <a href="fights.php?mode=<?php echo $mode; ?>&map=All&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&_=<?php echo $timestamp; ?>"><span style="background-color:<?php echo ($map == "All" ? "#FF6600;" : "#00CC00;"); ?>">All</span></a>
<?php
$mapsResult = mysqli_query($asdb, "select distinct `map` from `as_fights` where `mode` = " . $mode . " order by `map`;");
if ($mapsResult)
{
    while ($maps = mysqli_fetch_array($mapsResult))
        echo "  <a href=\"fights.php?mode=" . $mode . "&map=" . $maps["map"] . "&uid=" . $uid . "&uids=" . $uids . "&_=" . $timestamp . "\"><span style=\"background-color:" . ($map == $maps["map"] ? "#FF6600" : "#00CC00") . ";\">" . $maps["map"] . "</span></a>\n";
    mysqli_free_result($mapsResult);
}
echo "</div>\n";
}
?>

<div>
  <div>
    <div id="fights">
      <table cellpadding="1" cellspacing="1">
        <tbody>
          <tr>
            <th colspan="9" class="trtitle"><a href="<?php echo basename($_SERVER["REQUEST_URI"]); ?>"><img src="images/icons/fights.gif"><span>实时战况</span></a></th>
          </tr>
          <tr class="colortitle colorbg1">
            <th width="10%">时间</th>
            <th width="10%">地图</th>
            <th width="50%" colspan="3">战况</th>
            <th width="10%">击杀</th>
            <th width="10%">伤害</th>
            <th width="10%">距离</th>
          </tr>
<?php
for ($i = 0; $i < $count ; ++$i)
{
    $fights = $i < count($rows) ? $rows[$i] : array("timestamp" => "", "map" => "", "uid_a" => 0, "name_a" => "", "team_a" => "", "health_a" => 0, "wid_a" => 0, "uid_v" => 0, "name_v" => "", "team_v" => "", "health_v" => 0, "wid_v" => 0, "aiming" => 0, "damage" => 0, "kflag" => "", "wflag" => "", "tflag" => "", "distance" => 0.0);
    echo "          <tr class=\"colorbg" . ($fights["kflag"] && $uid && $fights["uid_v"] == $uid ? 4 : ($i % 2 ? 1 : 2)) . "\">\n";
    echo "            <td>" . $fights["timestamp"] . "</td>\n";
    echo "            <td>" . $fights["map"] . "</td>\n";

    echo "            <td class=\"tdcompact\" style=\"width:25%;text-align:right;\">\n";
    echo "              <a" . ($fights["uid_a"] ? (" href=\"stats.php?mode=" . $mode . "&map=" . $map . "&uid=" . $fights["uid_a"] . "&_=" . $timestamp . "\" target=\"_parent\"") : "") . " style=\"color:" . ($fights["team_a"] == 2 ? "#99CCFF" : "#FF9900" ). ";height:30px;line-height:30px;". ($fights["uid_a"] ? "" : "cursor:not-allowed;") . "\">";
    if ($uid && $fights["uid_a"] == $uid)
        echo $fights["team_a"] == 1 ? "<img src=\"images/events/t.gif\" title=\"" . as_fixHtmlString($fights["name_a"]) . "\" style=\"margin:7px auto;\">" : "<img src=\"images/events/ct.gif\" title=\"" . as_fixHtmlString($fights["name_a"]) . "\" style=\"margin:7px auto;\">";
    else
        echo as_fixHtmlString($fights["name_a"]);
    echo "</a>\n";
    echo "            </td>\n";

    echo "            <td style=\"height:30px;\">\n";
    echo "              <table>\n";
    echo "                <tbody>\n";
    echo "                  <tr>\n";
    if ($fights["uid_a"] || $fights["uid_v"] ? $fights["uid_a"] == $fights["uid_v"] : $fights["name_a"] == $fights["name_v"])
    {
        echo "                    <td class=\"percents\" style=\"text-align:left;\">\n";
        echo "                      <span class=\"" . ($fights["team_v"] == 2 ? "blue": "orange") . "\" title=\"" . max($fights["health_v"], 0) . "\" style=\"width:80px;\">\n";
        if ($fights["health_v"] < 0)
        {
            echo "                      <span class=\"gray\" title=\"\" style=\"float:left;width:" . number_format(as_apb($healthMax - ($fights["damage"] + $fights["health_v"]), $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
            echo "                      <span class=\"red progress\" title=\"" . ($fights["damage"] + $fights["health_v"]) . "\" style=\"float:left;width:" . number_format(as_apb(($fights["damage"] + $fights["health_v"]), $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
        }
        else
        {
            echo "                      <span class=\"gray\" title=\"\" style=\"float:left;width:" . number_format(as_apb($healthMax - $fights["health_v"] - $fights["damage"], $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
            echo "                      <span class=\"red progress\" title=\"" . $fights["damage"] . "\" style=\"float:left;width:" . number_format(as_apb($fights["damage"], $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
        }
        echo "                    </td>\n";
    }
    else
    {
        echo "                    <td class=\"percents\" style=\"text-align:left;\">\n";
        echo "                      <span class=\"" . ($fights["team_a"] == 1 ? "orange": "blue") . "\" title=\"" . max($fights["health_a"], 0) . "\" style=\"width:80px;\">\n";
        echo "                        <span class=\"gray\" title=\"\" style=\"float:left;width:" . number_format(as_apb($healthMax - max($fights["health_a"], 0), $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
        echo "                      </span>\n";
        echo "                    </td>\n";
    }

    echo "                    <td><img src=\"images/events/" . (int)($fights["wid_a"]) . ".gif\" title=\"" . $WeaponName[$fights["wid_a"]] . "\"></td>\n";

    echo "                    <td class=\"percents\" style=\"text-align:right;\">\n";
    echo "                      <span class=\"" . ($fights["team_v"] == 2 ? "blue": "orange") . "\" title=\"" . max($fights["health_v"], 0) . "\" style=\"width:80px;\">\n";
    if ($fights["health_v"] < 0)
    {
        echo "                      <span class=\"gray\" title=\"\" style=\"float:right;width:" . number_format(as_apb($healthMax - ($fights["damage"] + $fights["health_v"]), $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
        echo "                      <span class=\"red progress\" title=\"" . ($fights["damage"] + $fights["health_v"]) . "\" style=\"float:right;width:" . number_format(as_apb(($fights["damage"] + $fights["health_v"]), $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
    }
    else
    {
        echo "                      <span class=\"gray\" title=\"\" style=\"float:right;width:" . number_format(as_apb($healthMax - $fights["health_v"] - $fights["damage"], $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
        echo "                      <span class=\"red progress\" title=\"" . $fights["damage"] . "\" style=\"float:right;width:" . number_format(as_apb($fights["damage"], $healthMax, 100), 2, ".", "") . "%;\"></span>\n";
    }
    echo "                    </td>\n";
    echo "                  </tr>\n";
    echo "                </tbody>\n";
    echo "              </table>\n";
    echo "            </td>\n";

    echo "            <td class=\"tdcompact\" style=\"width:25%;text-align:left;\">\n";
    echo "              <a" . ($fights["uid_v"] ? (" href=\"stats.php?mode=" . $mode . "&map=" . $map . "&uid=" . $fights["uid_v"] . "&_=" . $timestamp . "\" target=\"_parent\"") : "") . " style=\"color:" . ($fights["team_v"] == 1 ? "#FF9900" : "#99CCFF" ). ";height:30px;line-height:30px;". ($fights["uid_v"] ? "" : "cursor:not-allowed;") . "\">";
    if ($uid && $fights["uid_v"] == $uid)
        echo $fights["team_v"] == 2 ? "<img src=\"images/events/ct.gif\" title=\"" . as_fixHtmlString($fights["name_v"]) . "\" style=\"margin:7px auto;\">" : "<img src=\"images/events/t.gif\" title=\"" . as_fixHtmlString($fights["name_v"]) . "\" style=\"margin:7px auto;\">";
    else
        echo as_fixHtmlString($fights["name_v"]);
    echo "</a>\n";
    echo "            </td>\n";

    echo "            <td>";
    if ($fights["aiming"] == 1 && $fights["wflag"] == 1)
        echo "<img src=\"images/events/headwallshot.gif\" title=\"headwallshot\">";
    else if ($fights["aiming"] == 1)
        echo "<img src=\"images/events/headshot.gif\" title=\"headshot\">";
    else if ($fights["wflag"] == 1)
        echo "<img src=\"images/events/wallshot.gif\" title=\"wallshot\">";
    if ($fights["kflag"])
    {
        if ($fights["aiming"] == 1 || $fights["wflag"] == 1)
            echo "&nbsp;";
        echo "<img src=\"images/events/0.gif\" title=\"dead\">";
    }
    echo "</td>\n";
    echo "            <td>" . $fights["damage"] . "</td>\n";
    echo "            <td>" . number_format($fights["distance"], 2, ".", "") . "</td>\n";
    echo "          </tr>\n";
}
?>
        </tbody>
      </table>
    </div>
    <div class="page">
      <table>
        <tr>
          <td class="colorbg1"><a href="fights.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&page=<?php echo $page - 1; ?>&count=<?php echo $count; ?>&_=<?php echo $timestamp; ?>"><img src="images/icons/left.gif">上一页</a></td>
<?php
$pageMin = 1;
$pageMax = $pageTotal;
if ($pageMin < $page - 5) $pageMin = $page - 5;
if ($pageMax > $page + 5) $pageMax = $page + 5;
if ($pageMin > 1)
    echo "          <td class=\"o\"><a href=\"fights.php?iframe=" . $iframe . "&mode=" . $mode . "&map=" . $map . "&uid=" . $uid . "&uids=" . $uids . "&page=1&count=" . $count . "&_=" . $timestamp . "\">" . ($pageMin == 1 + 1 ? "1" : "1...") . "</a></td>\n";
for ($pageIndex = $pageMin; $pageIndex <= $pageMax; ++$pageIndex)
    echo "          <td class=\"" . ($pageIndex == $page ? "n" : "o") . "\"><a href=\"fights.php?iframe=" . $iframe . "&mode=" . $mode . "&map=" . $map . "&uid=" . $uid . "&uids=" . $uids . "&page=" . $pageIndex . "&count=" . $count . "&_=" . $timestamp . "\">" . $pageIndex . "</a></td>\n";
if ($pageMax < $pageTotal)
    echo "          <td class=\"o\"><a href=\"fights.php?iframe=" . $iframe . "&mode=" . $mode . "&map=" . $map . "&uid=" . $uid . "&uids=" . $uids . "&page=" . $pageTotal . "&count=" . $count . "&_=" . $timestamp . "\">" . ($pageMax == $pageTotal - 1 ? "" : "...") . $pageTotal . "</a></td>\n";
?>
          <td class="colorbg1"><a href="fights.php?iframe=<?php echo $iframe; ?>&mode=<?php echo $mode; ?>&map=<?php echo $map; ?>&uid=<?php echo $uid; ?>&uids=<?php echo $uids; ?>&page=<?php echo $page + 1; ?>&count=<?php echo $count; ?>&_=<?php echo $timestamp; ?>">下一页<img src="images/icons/right.gif"></a></td>
        </tr>
      </table>
    </div>
  </div>
</div>

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
