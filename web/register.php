<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link name="shortcut" rel="icon shortcut" href="favicon.ico" />
<style type="text/css">
a,
span{color:#FF6600;}
div{text-align:center;}
td{font-size:14px;}
p{position:absolute;right:50px;bottom:30px;color:#FF6600;border:1px solid #FF6600;font-size:24px;transform:rotate(-30deg);}
input{height:22px;}
</style>
<title>Register</title>
<body style="margin:0;padding:0;color:#CCBB99;background-color:#000000;">
<table>
  <tbody>
<?php
include "core.php";
$server = as_get_param_string("server");
$index = as_get_param_number("index");
if ($dzdb)
{
?>
    <tr>
      <td colspan="2">
        <h3>点击<a href="<?php echo $dz_url . "member.php?mod=register"; ?>">这里</a>注册后，在游戏中按 <span>`</span> 设置相关信息即可登录</h3>
      </td>
    </tr>
    <tr>
      <td>
        <pre>
方案A: 最<span>简单</span>的登录方式
  步骤1: 设置名称, 游戏选项中设置
  步骤2: 设置密码，控制台输入 setinfo pass 123456
  步骤3: say /login

方案B: 最<span>常用</span>的登录方式
  步骤1: 设置账号，控制台输入 setinfo user admin
  步骤2: 设置密码，控制台输入 setinfo pass 123456
  步骤3: say /login

方案C: 最<span>可靠</span>的登录方式<span>(推荐)</span>
  步骤1: 设置编号，控制台输入 setinfo uid 9527
  步骤2: 设置密码，控制台输入 setinfo pass 123456
  步骤3: say /login</pre>
<?php
}
else
{
?>
    <tr>
      <td colspan="2">
<?php
    session_start();
    $success = false;
    do
    {
        if (empty($_REQUEST["submit"])) break;
        if (!$_SESSION["captcha"]) { echo "请刷新验证码!\n"; break; }
        $captcha = strtolower(trim($_REQUEST["captcha"]));
        if (empty($captcha)) { echo "请输入验证码!\n"; break; }
        if ($captcha != $_SESSION["captcha"]) { echo "验证码错误!\n"; break; }
        $name = as_get_param_string("name");
        if (empty($name)) { echo "名称不可为空!\n"; break; }
        $password = trim($_REQUEST["password"]);
        $password2 = trim($_REQUEST["password2"]);
        if (strlen($password) < 6) { echo "密码长度不可少于6位!\n"; break; }
        if ($password != $password2) { echo "两次密码输入不一致!\n"; break; }
        if (strpos($password, '^') !== false) { echo "密码不可含有非法字符: ^\n"; break; }
        if (strpos($password, '"') !== false) { echo "密码不可含有非法字符: \"\n"; break; }
        if (strpos($password, '\\') !== false) { echo "密码不可含有非法字符: \\\n"; break; }
        if (strpos($password, '/') !== false) { echo "密码不可含有非法字符: /\n"; break; }
        if (strpos($password, '`') !== false) { echo "密码不可含有非法字符: `\n"; break; }
        if (strpos($password, '~') !== false) { echo "密码不可含有非法字符: ~\n"; break; }
        $signature = as_get_param_string("signature");
        $salt = as_rand_string(6);
        $password = md5(md5($password) . $salt);

        $sql = "select count(1) as count from `as_users` where `name` = '" . $name . "';";
        $result = mysqli_query($asdb, $sql); if (!$result) { echo "查询用户失败: " . mysqli_error($asdb) . "\n"; break; }
        $count = mysqli_fetch_array($result)["count"]; mysqli_free_result($result); if ($count) { echo "该游戏名已被使用!\n"; break; }

        $sql = "insert into `as_users` (`uid`, `name`, `signature`, `password`, `salt`, `regtimestamp`, `lasttimestamp`, `lastipaddress`) select ifnull(max(uid), 0) + 1, '" . $name . "', '" . $signature . "', '" . $password . "', '" . $salt . "', now(), now(), '" . as_get_user_ipaddress() . "' from `as_users` where `uid` < 100000000;";
        $result = mysqli_query($asdb, $sql); if (!$result) { echo "新增用户失败: " . mysqli_error($asdb) . "\n"; break; }

        $sql = "select `uid` from `as_users` where `name` = '" . $name . "';";
        $result = mysqli_query($asdb, $sql); if (!$result) { echo "查询用户失败: " . mysqli_error($asdb) . "\n"; break; }
        $uid = mysqli_fetch_array($result)["uid"]; mysqli_free_result($result);

        $success = true;
        if ($server && $index && $uid) as_send_message($server, $index . " " . $uid . " register #" . $password2);
    } while (0);

    if (!$success)
    {
?>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <form method="post" action="register.php?server=<?php echo $server; ?>&index=<?php echo $index; ?>">
          <table cellpadding="1" cellspacing="1">
            <tbody>
              <tr>
                <td>名称:</td>
                <td><input name="name" type="text" size="10" maxlength="31" style="width:120px;"></td>
                <td></td>
                <td></td>
                <td>密码:</td>
                <td><input name="password" type="password" size="10" maxlength="16" style="width:120px;"></td>
                <td><input name="password2" type="password" size="10" maxlength="16" style="width:120px;"></td>
                <td rowspan="2"><input name="submit" type="submit" value="注册" style="height:47px;"></td>
              </tr>
              <tr>
                <td>签名:</td>
                <td colspan="3"><input name="signature" type="text" size="30" maxlength="63", style="width:243px;"></td>
                <td>验证:</td>
                <td><input name="captcha" type="text" size="10" maxlength="16" style="width:120px;"></td>
                <td><img src="captcha.php" style="width:116px;height:18px;border:2px inset #fff;" onclick="this.src='captcha.php?_=' + Math.random()"></td>
              </tr>
            </tbody>
          </table>
        </form>
<?php
    }
    else
    {
        echo "        <pre>注册成功! 你的编号: " . $uid . " 你的名称: " . $_REQUEST["name"] . " 你的签名: " . $_REQUEST["signature"] . "</pre>\n";
    }
?>
      </td>
    </tr>
    <tr>
      <td>
        <h3>注册: 在游戏中按 <span>Y</span> 输入<span>#密码</span>即可快速注册</h3>
        <pre>需根据提示重复操作一次</pre>
        <h3>登录: 在游戏中按 <span>`</span> 设置相关信息即可登录</h3>
        <pre>
方案A: 最<span>简单</span>的登录方式
  步骤1: 设置名称, 游戏选项中设置
  步骤2: 设置密码，控制台输入 setinfo pass 123456
  步骤3: say /login

方案B: STEAM正版用户自动注册和登录<span>(优先)</span>

方案C: 最<span>可靠</span>的登录方式<span>(推荐)</span>
  步骤1: 设置编号，控制台输入 setinfo uid 9527
  步骤2: 设置密码，控制台输入 setinfo pass 123456
  步骤3: say /login</pre>
<?php
}
?>
        <h3>登录成功后，在游戏中按 <span>Y</span> 输入:</h3>
        <pre>
  top  查看排行榜
   as  查看当前实时战况
   ms  查看我的数据统计
   hs  查看被观察玩家的数据统计
<span>*密码</span>  修改密码(需根据提示重复操作一次)
<span>!签名</span>  修改签名
        </pre>
      </td>
      <td rowspan="2" style="position:relative;vertical-align:bottom;">
        <div style="padding-top:<?php echo $dzdb ? "100px" : "60px"; ?>;"><a href="stats.php?uid=1">数据统计</a></div>
        <img src="images/demo_stats.jpg">
        <p>DEMO</p>
      </td>
    </tr>
    <tr>
      <td style="position:relative;vertical-align:bottom;">
        <div><a href="top.php">排行榜</a></div>
        <img src="images/demo_top.jpg">
        <p>DEMO</p>
      </td>
    </tr>
  </tbody>
</table>
</body>
</html>
