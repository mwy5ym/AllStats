<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<style type="text/css">
html,body,table,tr {margin:0;padding:0;width:100%;text-align:center;font-size:12px;font-weight:bold;}
body               {-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;user-select:none;}
body               {font-family:'Helvetica Neue',Helvetica,'PingFang SC','Hiragino Sans GB','Microsoft YaHei','微软雅黑',Arial,sans-serif;}
body               {color:#FFFFFF;background-color:#000000;}
img                {border:none;height:24px;}
tr                 {height:24px;}
td                 {border:1px solid #000000;}
a                  {display:block;cursor:pointer;}
a:link,a:visited   {color:#FFFFFF;text-decoration:none;}
span               {display:block;line-height:24px;white-space:nowrap;}
.buy               {display:inline-block;margin-right:4px;color:#000000;}
.bt                {border-top:none;}
.bl                {border-left:none;}
.br                {border-right:none;}
.bb                {border-bottom:none;}
</style>
<title>Console</title>
</head>
<body>
<?php
include "core.php";
// 该页面仅作为演示使用, 如需在对外发布的服务器上使用, 请自行加上权限认证, 防止被他人滥用
// 演示从网页到游戏的数据传输, 请编辑as_custom.sma, 取消注释#define MESSAGE_DEMO
$server = as_get_param_string("server");
$index = as_get_param_number("index");
$uid = as_get_param_number("uid");
$cmd = as_get_param_string("cmd");

function href()
{
    global $server, $index, $uid;
    return "console.php?server=" . $server . "&index=" . $index . "&uid=" . $uid . "&cmd=" . $index . " " . $uid . " " . join(" ", func_get_args());
}

if ($server && $index && $uid)
{
    if ($cmd)
    {
        session_start();
        $result = as_socket($server, as_gen_msgid(session_id()), "messages", $cmd);
        if ($result != 'unknown') $result = $result[$server];
        echo "<span style=\"position:absolute;top:0;right:0;\">" . $result . "</span>";
    }
?>

<table cellpadding="0" cellspacing="0" style="table-layout:fixed;">
  <tbody>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#00AA00;">
        <a href="<?php echo href("health", 100); ?>">
          <img src="images/items/hp.png" title="health">
          <span><?php echo "满生命"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#00AA00;">
        <a href="<?php echo href("money", 16000); ?>">
          <img src="images/items/money.png" title="money">
          <span><?php echo "满金钱"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#00AA00;">
        <a href="<?php echo href("ammo"); ?>">
          <img src="images/items/ammo.png" title="ammo">
          <span><?php echo "满弹药"; ?></span>
        </a>
      </td>
      <td colspan="2" class="bb br"></td>
      <td colspan="2" class="bl br"></td>
      <td colspan="2" class="bl bb"></td>
    </tr>
    <tr>
      <td colspan="2" class="bt"></td>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 10); ?>">
          <img src="images/items/10.png" title="b15">
          <span><span class="buy">b15</span><?php echo $WeaponName[10]; ?></span>
        </a>
      </td>
      <td rowspan="2" class="bt bb" colspan="2"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 17); ?>">
          <img src="images/items/17.png" title="b11">
          <span><span class="buy">b11</span><?php echo $WeaponName[17]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 16); ?>">
          <img src="images/items/16.png" title="b12">
          <span><span class="buy">b12</span><?php echo $WeaponName[16]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 1); ?>">
          <img src="images/items/1.png" title="b13">
          <span><span class="buy">b13</span><?php echo $WeaponName[1]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 26); ?>">
          <img src="images/items/26.png" title="b14">
          <span><span class="buy">b14</span><?php echo $WeaponName[26]; ?></span>
        </a>
      </td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 11); ?>">
          <img src="images/items/11.png" title="b15">
          <span><span class="buy">b15</span><?php echo $WeaponName[11]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" class="bt bb"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 21); ?>">
          <img src="images/items/21.png" title="b21">
          <span><span class="buy">b21</span><?php echo $WeaponName[21]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 5); ?>">
          <img src="images/items/5.png" title="b22">
          <span><span class="buy">b22</span><?php echo $WeaponName[5]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" class="br bb"></td>
      <td colspan="2" class="bl bb"></td>
    </tr>
    <tr>
      <td colspan="2" class="bt bl br bb"></td>
      <td colspan="2" class="bl br bb"></td>
      <td colspan="2" class="bt bl bb"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 7); ?>">
          <img src="images/items/7.png" title="b31">
          <span><span class="buy">b31</span><?php echo $WeaponName[7]; ?></span>
        </a>
      </td>
      <td colspan="2" class="br"></td>
      <td colspan="4" class="bt bl br"></td>
      <td colspan="4" class="bt bl bb"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 19); ?>">
          <img src="images/items/19.png" title="b32">
          <span><span class="buy">b32</span><?php echo $WeaponName[19]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 12); ?>">
          <img src="images/items/12.png" title="b33">
          <span><span class="buy">b33</span><?php echo $WeaponName[12]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 30); ?>">
          <img src="images/items/30.png" title="b34">
          <span><span class="buy">b34</span><?php echo $WeaponName[30]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" class="bt br bb"></td>
      <td rowspan="2" colspan="2" class="bt bl"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 23); ?>">
          <img src="images/items/23.png" title="b31">
          <span><span class="buy">b31</span><?php echo $WeaponName[23]; ?></span>
        </a>
      </td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 28); ?>">
          <img src="images/items/28.png" title="b42">
          <span><span class="buy">b42</span><?php echo $WeaponName[28]; ?></span>
        </a>
      </td>
      <td colspan="2" class="bb br"></td>
      <td colspan="2" class="bl br"></td>
      <td colspan="2" class="bt bl bb"></td>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 24); ?>">
          <img src="images/items/24.png" title="b46">
          <span><span class="buy">b46</span><?php echo $WeaponName[24]; ?></span>
        </a>
      </td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 14); ?>">
          <img src="images/items/14.png" title="b41">
          <span><span class="buy">b41</span><?php echo $WeaponName[14]; ?></span>
        </a>
      </td>
      <td class="bt br"></td>
      <td class="bt bl bb"></td>
      <td rowspan="2" colspan="2" style="background-color:#FF6600;">
        <a href="<?php echo href("weapon", 27); ?>">
          <img src="images/items/27.png" title="b44">
          <span><span class="buy">b44</span><?php echo $WeaponName[27]; ?></span>
        </a>
      </td>
      <td class="bt br bb"></td>
      <td class="bt bl"></td>
    </tr>
    <tr>
      <td class="bb"></td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 3); ?>">
          <img src="images/items/3.png" title="b42,b43">
          <span><span class="buy" style="color:#0099FF;">b42</span><span class="buy" style="color:#FF6600;">b43</span><?php echo $WeaponName[3]; ?></span>
        </a>
      </td>
      <td class="bt bb"></td>
      <td class="bt bb"></td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 18); ?>">
          <img src="images/items/18.png" title="b45,b46">
          <span><span class="buy" style="color:#FF6600;">b45</span><span class="buy" style="color:#0099FF;">b46</span><?php echo $WeaponName[18]; ?></span>
        </a>
      </td>
      <td class="bb"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 15); ?>">
          <img src="images/items/15.png" title="b41">
          <span><span class="buy">b41</span><?php echo $WeaponName[15]; ?></span>
        </a>
      </td>
      <td class="bt bb"></td>
      <td class="bt"></td>
      <td rowspan="2" colspan="2" class="bb" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 8); ?>">
          <img src="images/items/8.png" title="b44">
          <span><span class="buy">b44</span><?php echo $WeaponName[8]; ?></span>
        </a>
      </td>
      <td class="bt"></td>
      <td class="bt bb"></td>
    </tr>
    <tr>
      <td class="bt br bb"></td>
      <td class="bl bb"></td>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 22); ?>">
          <img src="images/items/22.png" title="b43">
          <span><span class="buy">b43</span><?php echo $WeaponName[22]; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("weapon", 13); ?>">
          <img src="images/items/13.png" title="b45">
          <span><span class="buy">b45</span><?php echo $WeaponName[13]; ?></span>
        </a>
      </td>
      <td class="br bb"></td>
      <td class="bt bl bb"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 20); ?>">
          <img src="images/items/20.png" title="b51">
          <span><span class="buy">b51</span><?php echo $WeaponName[20]; ?></span>
        </a>
      </td>
      <td colspan="2" class="bt bb"></td>
      <td colspan="2" class="bt bb"></td>
      <td colspan="2" class="bt bb"></td>
    </tr>
    <tr>
      <td colspan="2" class="bt br"></td>
      <td colspan="2" class="bl br"></td>
      <td colspan="2" class="bt bl br"></td>
      <td colspan="2" class="bl br"></td>
      <td colspan="2" class="bt bl"></td>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("armor", 100, 1); ?>">
          <img src="images/items/31.png" title="b81">
          <span><span class="buy">b81</span><?php echo "防弹衣"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("armor", 100, 2); ?>">
          <img src="images/items/32.png" title="b82">
          <span><span class="buy">b82</span><?php echo "防弹衣 + 头盔"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 25); ?>">
          <img src="images/items/25.png" title="b83">
          <span><span class="buy">b83</span><?php echo "闪光弹"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 4); ?>">
          <img src="images/items/4.png" title="b84">
          <span><span class="buy">b84</span><?php echo "手榴弹"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("weapon", 9); ?>">
          <img src="images/items/9.png" title="b85">
          <span><span class="buy">b85</span><?php echo "烟雾弹"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#888888;">
        <a href="<?php echo href("nvg"); ?>">
          <img src="images/items/nightvisiongoggles.png" title="b86,b87">
          <span><span class="buy" style="color:#FF6600;">b86</span><span class="buy" style="color:#0099FF;">b87</span><?php echo "夜视镜"; ?></span>
        </a>
      </td>
    </tr>
    <tr>
    </tr>
    <tr>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("defuse"); ?>">
          <img src="images/items/defusekit.png" title="b86">
          <span><span class="buy">b86</span><?php echo "拆包工具"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="2" style="background-color:#0099FF;">
        <a href="<?php echo href("shield"); ?>">
          <img src="images/items/tacticalshield.png" title="b88">
          <span><span class="buy">b88</span><?php echo "盾牌"; ?></span>
        </a>
      </td>
      <td rowspan="2" colspan="8"></td>
    </tr>
    <tr>
    </tr>
  </tbody>
</table>

<?php
}
?>
</body>
</html>
