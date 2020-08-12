<?
header('Content-Type: text/html; charset=gbk');

$msg = '';

if($_POST['action'] == 'save') {

	if(is_writeable('mail_config.inc.php')) {

		$_POST['sendmail_silent_new'] = intval($_POST['sendmail_silent_new']);
		$_POST['mailsend_new'] = intval($_POST['mailsend_new']);
		$_POST['maildelimiter_new'] = intval($_POST['maildelimiter_new']);
		$_POST['mailusername_new'] = intval($_POST['mailusername_new']);
		$_POST['mailcfg_new']['server'] = addslashes($_POST['mailcfg_new']['server']);
		$_POST['mailcfg_new']['port'] = intval($_POST['mailcfg_new']['port']);
		$_POST['mailcfg_new']['auth'] = intval($_POST['mailcfg_new']['auth']);
		$_POST['mailcfg_new']['from'] = addslashes($_POST['mailcfg_new']['from']);
		$_POST['mailcfg_new']['auth_username'] = addslashes($_POST['mailcfg_new']['auth_username']);
		$_POST['mailcfg_new']['auth_password'] = addslashes($_POST['mailcfg_new']['auth_password']);

$savedata = <<<EOF
<?php

\$sendmail_silent = $_POST[sendmail_silent_new];
\$maildelimiter = $_POST[maildelimiter_new];
\$mailusername = $_POST[mailusername_new];
\$mailsend = $_POST[mailsend_new];

EOF;

		if($_POST['mailsend_new'] == 2) {

$savedata .= <<<EOF

\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
\$mailcfg['port'] = {$_POST[mailcfg_new][port]};
\$mailcfg['auth'] = {$_POST[mailcfg_new][auth]};
\$mailcfg['from'] = '{$_POST[mailcfg_new][from]}';
\$mailcfg['auth_username'] = '{$_POST[mailcfg_new][auth_username]}';
\$mailcfg['auth_password'] = '{$_POST[mailcfg_new][auth_password]}';

EOF;

		} elseif($_POST['mailsend_new'] == 3) {

$savedata .= <<<EOF

\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
\$mailcfg['port'] = '{$_POST[mailcfg_new][port]}';

EOF;

		}

		setcookie('mail_cfg', base64_encode(serialize($_POST['mailcfg_new'])), time() + 86400);

$savedata .= <<<EOF

?>
EOF;

		$fp = fopen('mail_config.inc.php', 'w');
		fwrite($fp, $savedata);
		fclose($fp);

		$msg = '设置保存完毕！';

		if($_POST['sendtest']) {

			define('IN_DISCUZ', true);

			define('DISCUZ_ROOT', './');
			define('TPLDIR', './templates/default');
			require './include/global.func.php';

			$test_tos = explode(',', $_POST['mailcfg_new']['test_to']);
			$date = date('Y-m-d H:i:s');

			switch($_POST['mailsend_new']) {
				case 1:
					$title = '标准方式发送 Email';
					$message = "通过 PHP 函数及 UNIX sendmail 发送\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
					break;
				case 2:
					$title = '通过 SMTP 服务器(SOCKET)发送 Email';
					$message = "通过 SOCKET 连接 SMTP 服务器发送\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
					break;
				case 3:
					$title = '通过 PHP 函数 SMTP 发送 Email';
					$message = "通过 PHP 函数 SMTP 发送 Email\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
					break;
			}

			$bbname = '邮件单发测试';
			sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);
			$bbname = '邮件群发测试';
			sendmail($_POST['mailcfg_new']['test_to'], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);

			$msg = '设置保存完毕！<br>标题为“'.$title.' @ '.$date.'”的测试邮件已经发出！';

		}

	} else {

		$msg = '无法写入邮件配置文件 mail_config.inc.php，要使用本工具请设置此文件的可写入权限。';

	}

}

include './mail_config.inc.php';

?>
<html>
<head>
<title>Discuz! Board Mail Config and Test Tools</title>
<style>
body,table,td	{COLOR: #3A4273; FONT-FAMILY: Tahoma, Verdana, Arial; FONT-SIZE: 12px; LINE-HEIGHT: 20px; scrollbar-base-color: #E3E3EA; scrollbar-arrow-color: #5C5C8D}
tr		{background-color: #E3E3EA}
th		{background-color: #3A4273; color: #FFFFFF}
input		{border: 1px solid #CCCCCC}
.button 	{background-color: #3A4273; color: #FFFFFF}
.text		{width: 100%}
.checkbox,.radio{border: 0px}
</style>
<script>
function $(id) {
	return document.getElementById(id);
}
</script>
</head>

<body>
<?

if($msg) {
	echo '<center><font color="#FF0000">'.$msg.'</font></center>';
}

?>
<table width="60%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<form method="post">
<input type="hidden" name="action" value="save"><input type="hidden" name="sendtest" value="0">
<tr><th colspan="2">邮件配置/测试工具</th></tr>
<?

$saved_mailcfg = empty($_COOKIE['mail_cfg']) ? array(
	'server' => 'smtp.21cn.com',
	'port' => '25',
	'auth' => 1,
	'from' => 'Discuz <username@21cn.com>',
	'auth_username' => 'username@21cn.com',
	'auth_password' => 'password',
	'test_from' => 'user <my@mydomain.com>',
	'test_to' => 'user1 <test1@test1.com>, user2 <test2@test2.net>'
) : unserialize(base64_decode($_COOKIE['mail_cfg']));

echo '<tr><td width="40%">屏蔽邮件发送中的全部错误提示</td><td>';
echo ' <input class="checkbox" type="checkbox" name="sendmail_silent_new" value="1"'.($sendmail_silent ? ' checked' : '').'><br>';
echo '</tr>';
echo '<tr><td>邮件头的分隔符</td><td>';
echo ' <input class="radio" type="radio" name="maildelimiter_new" value="1"'.($maildelimiter ? ' checked' : '').'> 使用 CRLF 作为分隔符<br>';
echo ' <input class="radio" type="radio" name="maildelimiter_new" value="0"'.(!$maildelimiter ? ' checked' : '').'> 使用 LF 作为分隔符<br>';
echo '</tr>';
echo '<tr><td>收件人中包含用户名</td><td>';
echo ' <input class="checkbox" type="checkbox" name="mailusername_new" value="1"'.($mailusername ? ' checked' : '').'><br>';
echo '</tr>';

echo '<tr><td>邮件发送方式</td><td>';
echo ' <input class="radio" type="radio" name="mailsend_new" value="1"'.($mailsend == 1 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'none\';$(\'hidden2\').style.display=\'none\'"> 通过 PHP 函数及 UNIX sendmail 发送(推荐此方式)<br>';
echo ' <input class="radio" type="radio" name="mailsend_new" value="2"'.($mailsend == 2 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'\'"> 通过 SOCKET 连接 SMTP 服务器发送(支持 ESMTP 验证)<br>';
echo ' <input class="radio" type="radio" name="mailsend_new" value="3"'.($mailsend == 3 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'none\'"> 通过 PHP 函数 SMTP 发送 Email(仅 win32 下有效, 不支持 ESMTP)<br>';
echo '</tr>';

$mailcfg['server'] = $mailcfg['server'] == '' ? $saved_mailcfg['server'] : $mailcfg['server'];
$mailcfg['port'] = $mailcfg['port'] == '' ? $saved_mailcfg['port'] : $mailcfg['port'];
$mailcfg['auth'] = $mailcfg['auth'] == '' ? $saved_mailcfg['auth'] : $mailcfg['auth'];
$mailcfg['from'] = $mailcfg['from'] == '' ? $saved_mailcfg['from'] : $mailcfg['from'];
$mailcfg['auth_username'] = $mailcfg['auth_username'] == '' ? $saved_mailcfg['auth_username'] : $mailcfg['auth_username'];
$mailcfg['auth_password'] = $mailcfg['auth_password'] == '' ? $saved_mailcfg['auth_password'] : $mailcfg['auth_password'];

echo '<tbody id="hidden1" style="display:'.($mailsend == 1 ? ' none' : '').'">';
echo '<tr><td>SMTP 服务器</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[server]" value="'.$mailcfg['server'].'"><br>';
echo '</tr>';
echo '<tr><td>SMTP 端口, 默认不需修改</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[port]" value="'.$mailcfg['port'].'"><br>';
echo '</tr>';
echo '</tbody>';
echo '<tbody id="hidden2" style="display:'.($mailsend != 2 ? ' none' : '').'">';
echo '<tr><td>是否需要 AUTH LOGIN 验证</td><td>';
echo ' <input class="checkbox" type="checkbox" name="mailcfg_new[auth]" value="1"'.($mailcfg['auth'] ? ' checked' : '').'><br>';
echo '</tr>';
echo '<tr><td>发信人地址 (如果需要验证,必须为本服务器地址)</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[from]" value="'.$mailcfg['from'].'"><br>';
echo '</tr>';
echo '<tr><td>验证用户名</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[auth_username]" value="'.$mailcfg['auth_username'].'"><br>';
echo '</tr>';
echo '<tr><td>验证密码</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[auth_password]" value="'.$mailcfg['auth_password'].'"><br>';
echo '</tr>';
echo '</tbody>';

?>
<tr><td colspan="2" align="center">
<input class="button" type="submit" name="submit" value="保存设置">
</td></tr>
<?

echo '<tr><td>测试发件人</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[test_from]" value="'.$saved_mailcfg['test_from'].'"><br>';
echo '</tr>';
echo '<tr><td>测试收件人</td><td>';
echo ' <input class="text" type="text" name="mailcfg_new[test_to]" value="'.$saved_mailcfg['test_to'].'"><br>';
echo '</tr>';

?>
<tr><td colspan="2" align="center">
<input class="button" type="submit" name="submit" onclick="this.form.sendtest.value = 1" value="保存设置并测试发送">
</td></tr>
</form>
</table>

</body>