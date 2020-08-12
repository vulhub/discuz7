<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);
@set_time_limit(0);

define('DISCUZ_ROOT', getcwd().'/');
define('IN_DISCUZ', TRUE);

$lang = array(
	'error_message' => '错误信息',
	'message_return' => '返回',
	'old_step' => '上一步',
	'new_step' => '下一步',
	'uc_appname' => '论坛',
	'uc_appreg' => '注册',
	'uc_appreg_succeed' => '到 UCenter 成功，',
	'uc_continue' => '点击这里继续',
	'uc_setup' => '<font color="red">如果没有安装过，点击这里安装 UCenter</font>',
	'uc_title_ucenter' => '请填写 UCenter 的相关信息',
	'uc_url' => 'UCenter 的 URL',
	'uc_ip' => 'UCenter 的 IP',
	'uc_admin' => 'UCenter 的管理员帐号',
	'uc_adminpw' => 'UCenter 的管理员密码',
	'uc_title_app' => '相关信息',
	'uc_app_name' => '的名称',
	'uc_app_url' => '的 URL',
	'uc_app_ip' => '的 IP',
	'uc_app_ip_comment' => '当主机 DNS 有问题时需要设置，默认请保留为空',
	'uc_connent_invalid1' => '连接服务器',
	'uc_connent_invalid2' => ' 失败，请返回检查。',
	'error_message' => '提示信息',
	'error_return' => '返回',

	'tagtemplates_subject' => '标题',
	'tagtemplates_uid' => '用户 ID',
	'tagtemplates_username' => '发帖者',
	'tagtemplates_dateline' => '日期',
	'tagtemplates_url' => '主题地址',
);

$msglang = array(
	'redirect_msg' => '浏览器会自动跳转页面，无需人工干预。除非当您的浏览器长时间没有自动跳转时，请点击这里',
	'uc_url_empty' => '您没有填写 UCenter 的 URL，请返回填写。',
	'uc_url_invalid' => 'UCenter 的 URL 格式不合法，正常的格式为： http://www.domain.com ，请返回检查。',
	'uc_ip_invalid' => '<font color="red">无法连接 UCenter 所在的 Web 服务器，请填写 UCenter 服务器的IP，如果 UCenter 与论坛在同一台服务器，可以尝试填写：127.0.0.1。</font>',
	'uc_admin_invalid' => '<font color="red">登录 UCenter 的管理员帐号密码错误。</font>',
	'uc_data_invalid' => 'UCenter 获取数据失败，请返回检查 UCenter URL、管理员帐号、密码。 ',
);

require DISCUZ_ROOT.'./include/db_mysql.class.php';
@include DISCUZ_ROOT.'./config.inc.php';

$version['old'] = 'Discuz! 6.0.0 正式版';
$version['new'] = 'Discuz! 6.1.0 正式版';

instheader();
if(!$dbhost || !$dbname || !$dbuser) {
	instmsg('论坛数据库的主机，数据库名，用户名为空。');
}

$db = new dbstuff();
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true, $dbcharset);
function get_charset($tablename) {
	global $db;
	$tablestruct = $db->fetch_first("show create table $tablename");
	preg_match("/CHARSET=(\w+)/", $tablestruct['Create Table'], $m);
	return $m[1];
}

if($db->version() > '4.1.0') {
	$tablethreadcharset = get_charset($tablepre.'threads');
	$dbcharset = strtolower($dbcharset);
	$tablethreadcharset = strtolower($tablethreadcharset);
	if($dbcharset && $dbcharset !=  $tablethreadcharset) {
		instmsg("您的配置文件 (./config.inc.php) 中的字符集 ($dbcharset) 与表的字符集 ($tablethreadcharset) 不匹配。");
	}
}

$upgrade1 = <<<EOT
DROP TABLE IF EXISTS cdb_request;
CREATE TABLE cdb_request (
  variable varchar(32) NOT NULL DEFAULT '',
  value mediumtext NOT NULL,
  type tinyint(1) NOT NULL,
  PRIMARY KEY (variable),
  KEY type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_reportlog;
CREATE TABLE cdb_reportlog (
  id int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL,
  pid int(10) unsigned NOT NULL,
  uid mediumint(8) unsigned NOT NULL,
  username char(15) NOT NULL,
  status tinyint(1) unsigned NOT NULL default '1',
  type tinyint(1) NOT NULL,
  reason char(40) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY pid (pid,uid),
  KEY dateline (fid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_warnings;
CREATE TABLE cdb_warnings (
  wid smallint(6) unsigned NOT NULL auto_increment,
  pid int(10) unsigned NOT NULL,
  operatorid mediumint(8) unsigned NOT NULL,
  operator char(15) NOT NULL,
  authorid mediumint(8) unsigned NOT NULL,
  author char(15) NOT NULL,
  dateline int(10) unsigned NOT NULL,
  reason char(40) NOT NULL,
  PRIMARY KEY  (wid),
  UNIQUE KEY pid (pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_medallog;
CREATE TABLE cdb_medallog (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid mediumint(8) unsigned NOT NULL default '0',
  medalid smallint(6) unsigned NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY type (type),
  KEY status (status,expiration),
  KEY uid (uid,medalid,type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_admincustom;
CREATE TABLE cdb_admincustom (
  id smallint(6) unsigned NOT NULL auto_increment,
  title varchar(50) NOT NULL,
  url varchar(255) NOT NULL,
  sort tinyint(1) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL,
  clicks smallint(6) unsigned NOT NULL default '1',
  uid mediumint(8) unsigned NOT NULL,
  dateline int(10) unsigned NOT NULL,
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY displayorder (displayorder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_virtualforums;
CREATE TABLE cdb_virtualforums (
  fid smallint(6) unsigned NOT NULL auto_increment,
  cid mediumint(8) unsigned NOT NULL,
  fup smallint(6) unsigned NOT NULL,
  `type` enum('group','forum') NOT NULL default 'forum',
  `name` varchar(255) NOT NULL,
  description text NOT NULL,
  logo varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  threads mediumint(8) unsigned NOT NULL DEFAULT '0',
  posts mediumint(8) unsigned NOT NULL DEFAULT '0',
  lastpost varchar(255) NOT NULL DEFAULT '',
  displayorder tinyint(3) NOT NULL,
  PRIMARY KEY  (fid),
  KEY forum (`status`,`type`,displayorder),
  KEY fup (fup)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_advcaches;
CREATE TABLE cdb_advcaches (
  advid mediumint(8) unsigned NOT NULL auto_increment,
  `type` varchar(50) NOT NULL default '0',
  target smallint(6) NOT NULL,
  `code` mediumtext NOT NULL,
  PRIMARY KEY  (advid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS cdb_adminsessions;
CREATE TABLE cdb_adminsessions (
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  adminid smallint(6) unsigned NOT NULL DEFAULT '0',
  panel tinyint(1) NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  errorcount tinyint(1) NOT NULL DEFAULT '0',
  `storage` mediumtext NOT NULL,
  PRIMARY KEY (uid,panel)
) TYPE=MyISAM;

EOT;

$upgradetable = array(

	array('forums', 'ADD', 'allowtag', "TINYINT(1) NOT NULL DEFAULT '1'"),
	array('forums', 'ADD', 'modworks', "TINYINT(1) UNSIGNED NOT NULL"),
	array('forums', 'DROP', 'allowpaytoauthor', ""),

	array('medals', 'ADD', 'type', "TINYINT( 1 ) NOT NULL DEFAULT '0'"),
	array('medals', 'ADD', 'displayorder', "TINYINT( 3 ) NOT NULL DEFAULT '0'"),
	array('medals', 'INDEX', '', "ADD INDEX displayorder (displayorder)"),
	array('medals', 'ADD', 'description', "VARCHAR( 255 ) NOT NULL"),
	array('medals', 'ADD', 'expiration', "SMALLINT( 6 ) unsigned NOT NULL DEFAULT '0'"),
	array('medals', 'ADD', 'permission', "MEDIUMTEXT NOT NULL"),

	array('memberfields', 'CHANGE', 'medals', "medals TEXT NOT NULL"),

	array('usergroups', 'ADD', 'exempt', "TINYINT(1) unsigned NOT NULL"),

	array('members', 'ADD', 'customaddfeed', "TINYINT( 1 ) NOT NULL DEFAULT '0'"),

	array('campaigns', 'ADD', 'url', "CHAR(255) NOT NULL"),
	array('campaigns', 'ADD', 'autoupdate', "TINYINT(1) unsigned NOT NULL"),
	array('campaigns', 'ADD', 'lastupdated', "INT(10) unsigned NOT NULL"),

	array('usergroups', 'DROP', 'maxpmnum', ""),

	array('access', 'ADD', 'adminuser', "MEDIUMINT(8) unsigned NOT NULL DEFAULT '0'"),
	array('access', 'ADD', 'dateline', "INT(10) unsigned NOT NULL DEFAULT '0'"),
	array('access', 'INDEX', '', "ADD INDEX listorder (fid,dateline)"),

	array('videos', 'ADD', 'displayorder', "TINYINT(3) NOT NULL DEFAULT '0' AFTER dateline"),
	array('videos', 'INDEX', '', "ADD INDEX displayorder (displayorder)"),

);

$upgrade3 = <<<EOT

REPLACE INTO cdb_settings (variable, value) VALUES ('attachexpire', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('admode', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('infosidestatus', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('seclevel', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('warninglimit', '3');
REPLACE INTO cdb_settings (variable, value) VALUES ('warningexpiration', '30');
REPLACE INTO cdb_settings (variable, value) VALUES ('thumbquality', '100');
REPLACE INTO cdb_settings (variable, value) VALUES ('relatedtag', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('outextcredits', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('uc', 'a:1:{s:7:"addfeed";i:1;}');
DELETE FROM cdb_settings WHERE variable='allowcsscache';
DELETE FROM cdb_settings WHERE variable='seccodeanimator';
DELETE FROM cdb_settings WHERE variable='maxavatarsize';
DELETE FROM cdb_settings WHERE variable='maxavatarpixel';
DELETE FROM cdb_settings WHERE variable like 'passport_%';
DELETE FROM cdb_settings WHERE variable like 'supe_%';

DELETE FROM cdb_crons WHERE filename='supe_daily.inc.php';

INSERT INTO cdb_crons VALUES (NULL,'1','system','每日勋章更新','medals_daily.inc.php','0','1170600452','-1','-1','0','0');

UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 1;
UPDATE cdb_usergroups SET exempt=255 WHERE radminid = 2;
UPDATE cdb_usergroups SET exempt=224 WHERE radminid = 3;

EOT;

$uchidden = '<input type="hidden" name="ucdbhost" value="'.$_POST['ucdbhost'].'">';
$uchidden .= '<input type="hidden" name="ucdbname" value="'.$_POST['ucdbname'].'">';
$uchidden .= '<input type="hidden" name="ucdbuser" value="'.$_POST['ucdbuser'].'">';
$uchidden .= '<input type="hidden" name="ucdbpw" value="'.$_POST['ucdbpw'].'">';
$uchidden .= '<input type="hidden" name="uctablepre" value="'.$_POST['uctablepre'].'">';
$uchidden .= '<input type="hidden" name="ucdbcharset" value="'.$_POST['ucdbcharset'].'">';
$uchidden .= '<input type="hidden" name="ucapi" value="'.$_POST['ucapi'].'">';
$uchidden .= '<input type="hidden" name="ucip" value="'.$_POST['ucip'].'">';
$uchidden .= '<input type="hidden" name="uccharset" value="'.$_POST['uccharset'].'">';
$uchidden .= '<input type="hidden" name="appid" value="'.$_POST['appid'].'">';
$uchidden .= '<input type="hidden" name="appauthkey" value="'.$_POST['appauthkey'].'">';

$step = getgpc('step');
$step = empty($step) ? 1 : $step;

if(!isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
}



if($step == 1) {

$msg = '';
if(file_exists(DISCUZ_ROOT.'forumdata/upgrademaxuid.log')) {
	$msg = '<b><font color="red">升级程序检测到您执行过本程序，请点击一下链接，选择操作：</font></b><br />
	<li><a href="'.$PHP_SELF.'?step=uc&restart=yes"><font size="2">我已恢复旧版数据, 请点击这里重新升级</font></a><br />
	<li><a href="'.$PHP_SELF.'?step=uc"><font size="2">继续以前的升级</font></a><br />';
} else {
	$msg = '<a href="'.$PHP_SELF.'?step=uc"><font size="2"><b>&gt;&gt;&nbsp;如果您已确认完成上面的步骤,请点这里升级</b></font></a>';
}

echo <<<EOT
<h4>本升级程序只能从 $version[old] 升级到 $version[new]<br /></h4>
升级之前<b>务必备份数据库资料</b>，否则升级失败无法恢复<br /><br />
正确的升级方法为:
<ol>
	<li>请确认已经安装了 UCenter
	<li>关闭原有论坛，上传 $version[new] 的全部文件和目录，覆盖服务器上的 $version[old]
	<li>上传升级程序到论坛目录中，重新配置好 config.inc.php
	<li>运行本程序，直到出现升级完成的提示
	<li>如果中途失败，请使用Discuz!工具箱（./utilities/tools.php）里面的数据恢复工具恢复备份，去除错误后重新运行本程序
</ol>
$msg

EOT;

	instfooter();

} elseif($step == 'uc') {

	if(!empty($_GET['restart'])) {
		@unlink(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
		@unlink(DISCUZ_ROOT.'forumdata/upgrade.log');
		@unlink(DISCUZ_ROOT.'forumdata/repeatuser.log');
	}

	define('APP_NAME', $_POST['appname'] ? $_POST['appname'] : $lang['uc_appname']);
	define('APP_TYPE', 'DISCUZ');
	define('APP_CHARSET', $charset);
	define('APP_DBCHARSET', $dbcharset ? $dbcharset : (in_array(strtolower($charset), array('gbk', 'big5', 'utf-8')) ? str_replace('-', '', $charset) : 'gbk'));
	define('APP_URL', strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
	define('APP_NEXTSTEP', $_SERVER['PHP_SELF'].'?step=2');

	$ucip = $ucapi = $uciperror = '';
	if(!empty($_POST['ucsubmit'])) {

		$ucapi = getgpc('ucapi', 'P');
		$ucip = getgpc('ucip', 'P');
		$ucfounderpw = getgpc('ucfounderpw', 'P');

		$appip = getgpc('appip', 'P');

		$hidden .= var_to_hidden('ucapi', $ucapi);
		$hidden .= var_to_hidden('ucfounderpw', $ucfounderpw);

		$ucapi = preg_replace("/\/$/", '', trim($ucapi));
		if(empty($ucapi)) {
			instmsg('uc_url_empty');
		} elseif(!preg_match("/^(http:\/\/)/i", $ucapi)) {
			instmsg('uc_url_invalid');
		}

		if(!$ucip) {
			parse_url($ucapi);
			$matches = parse_url($ucapi);
			$host = $matches['host'];
			$port = !empty($matches['port']) ? $matches['port'] : 80;
			if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $host)) {
				$ucip = gethostbyname($host);
				$ucip = $ucip == $host ? '' : $ucip;
			} else {
				$ucip = $host;
			}
		}
		$connect_error = false;
		$pw_error = false;
		if(!$ucip) {
			$uciperror = $msglang['uc_ip_invalid'];
		} else {
			$app_tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
				'apptagtemplates[fields][subject]='.urlencode($lang['tagtemplates_subject']).'&'.
				'apptagtemplates[fields][uid]='.urlencode($lang['tagtemplates_uid']).'&'.
				'apptagtemplates[fields][username]='.urlencode($lang['tagtemplates_username']).'&'.
				'apptagtemplates[fields][dateline]='.urlencode($lang['tagtemplates_dateline']).'&'.
				'apptagtemplates[fields][url]='.urlencode($lang['tagtemplates_url']);

			$postdata = "m=app&a=add&ucfounderpw=".urlencode($ucfounderpw)."&apptype=".urlencode(APP_TYPE)."&appname=".urlencode(APP_NAME)."&appurl=".urlencode(APP_URL)."&appip=&appcharset=".APP_CHARSET.'&appdbcharset='.APP_DBCHARSET.'&'.$app_tagtemplates;
			$s = dfopen($ucapi.'/index.php', 0, $postdata, '', 1, $ucip);
			if(empty($s)) {
				$connect_error = true;
				$uciperror = $msglang['uc_ip_invalid'];
				//instmsg($lang['uc_connent_invalid1'].$ucapi.' ('.$ucip.')'.$lang['uc_connent_invalid2']);
			} elseif($s == '-1') {
				$pw_error = $msglang['uc_admin_invalid'];
			} else {
				list($appauthkey, $appid, $ucdbhost, $ucdbname, $ucdbuser, $ucdbpw, $ucdbcharset, $uctablepre, $uccharset) = explode('|', $s);
				if(empty($appauthkey) || empty($appid)) {
					$connect_error = true;
					$uciperror = $msglang['uc_ip_invalid'];
					//instmsg('uc_data_invalid');
				} else {
					$apphidden = var_to_hidden('ucdbhost', $ucdbhost);
					$apphidden .= var_to_hidden('ucdbname', $ucdbname);
					$apphidden .= var_to_hidden('ucdbuser', $ucdbuser);
					$apphidden .= var_to_hidden('ucdbpw', $ucdbpw);
					$apphidden .= var_to_hidden('uctablepre', $uctablepre);
					$apphidden .= var_to_hidden('ucdbcharset', $ucdbcharset);

					$apphidden .= var_to_hidden('ucapi', $ucapi);
					$apphidden .= var_to_hidden('ucip', $ucip);
					$apphidden .= var_to_hidden('uccharset', $uccharset);
					$apphidden .= var_to_hidden('appid', $appid);
					$apphidden .= var_to_hidden('appauthkey', $appauthkey);

					instmsg($lang['uc_appreg'].APP_NAME.$lang['uc_appreg_succeed'].'<form action="'.APP_NEXTSTEP.'" method="post">'.$apphidden.'</form><br /><a href="javascript:document.forms[0].submit();">'.$lang['uc_continue'].'</a><script type="text/javascript">setTimeout("document.forms[0].submit()", 1000);</script>');
				}
			}
		}
	}

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>?step=uc">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" colspan="2"><?=$lang['uc_title_ucenter']?></td></tr>
<tr>
<td class="altbg1"><?=$lang['uc_url']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucapi" id="ucapi" value="<?=$ucapi?>" size="60"></td>
</tr>
<?

	if($uciperror || $connect_error) {

?>
<tr>
<td class="altbg1"><?=$lang['uc_ip']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucip" value="<?=$ucip?>" size="60"><?=$uciperror?></td>
</tr>
<?

	}

?>
<tr>
<td class="altbg1"><?=$lang['uc_admin']?>:</td>
<td class="altbg2"><input class="txt" type="text" name="ucfounder" value="UCenterAdministrator" disabled="disabled" size="30" id="ucfounder"></td>
</tr>
<tr>
<td class="altbg1"><?=$lang['uc_adminpw']?>:</td>
<td class="altbg2"><input class="txt" type="password" name="ucfounderpw" id="ucfounderpw" size="30"><?=$pw_error?></td>
</tr>
</table>
</div>
<br />
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273">
<td colspan="2" style="color: #FFFFFF; padding-left: 10px" colspan="2"><?=APP_NAME.$lang['uc_title_app']?></td>
</tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_name']?>:</td>
<td class="altbg2"><input type="text" name="appname" value="<?=APP_NAME?>" size="30"></td>
</tr>
<tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_url']?>:</td>
<td class="altbg2"><input type="text" name="appurl" value="<?=APP_URL?>" size="60"></td>
</tr>
<tbody style="display: none;" id="appip">
<tr>
<td class="altbg1"><?=APP_NAME.$lang['uc_app_ip']?>:</td>
<td class="altbg2"><input type="text" name="appip" value="" size="30"> <?=$lang['uc_app_ip_comment']?></td>
</tr>
</tbody>
</table>
<input type="hidden" name="apptype" value="<?=APP_TYPE?>">
<center>
<input type="button" name="ucsubmit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="history.back()">&nbsp;
<input type="submit" name="ucsubmit" value=" <?=$lang['new_step']?> " style="height: 25"></center>
</center>
</form>
<?

	instfooter();
	exit;

} elseif($step == 2) {

	$dirs = array('config.inc.php', 'uc_client/data', 'uc_client/data/cache');

	echo "<h4>检查目录权限</h4>";
	echo '<form action="?step=3" method="post">'.$uchidden;
	echo '<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">';
        echo '<tr class="header"><td>目录文件</td><td>所需状态</td><td>当前状态</td></tr>';
        $pass = TRUE;
	foreach($dirs as $dir) {
		$iswritable = is_writable(DISCUZ_ROOT.'./'.$dir);
		$pass == TRUE && !$iswritable && $pass = FALSE;
		echo '<tr align="center"><td class="altbg1">'.$dir.'</td><td class="altbg2">可写</td><td class="altbg1">'.($iswritable ? '<font color="green">可写</font>' : '<font color="red">不可写</font>').'</td></tr>';
	}
	if($pass) {
		$nextstep = ' <input type="submit" value="下一步" style="height: 25">';
	} else {
		$nextstep = ' <input type="button" disabled value="请将以上目录权限全部设置为 777，然后进行下一步安装。" style="height: 25">';
	}
	echo '</table>';
	echo '<p align="center"><input type="button" onclick="history.back()" value="上一步" style="height: 25"> '.$nextstep.'</p></form>';
	instfooter();

} elseif($step == 3) {

	echo "<h4>创建配置文件</h4>";
	$discuzconfig = DISCUZ_ROOT.'./config.inc.php';
	$ucdbhost = $_POST['ucdbhost'];
	$ucdbuser = $_POST['ucdbuser'];
	$ucdbpw = $_POST['ucdbpw'];
	$ucdbname = $_POST['ucdbname'];
	$ucdbcharset = $_POST['ucdbcharset'];
	$uctablepre = $_POST['uctablepre'];
	$appauthkey = $_POST['appauthkey'];
	$ucapi = $_POST['ucapi'];
	$appid = $_POST['appid'];
	$uccharset = $_POST['uccharset'];
	$ucip = $_POST['ucip'];
	$samelink = ($dbhost == $ucdbhost && $dbuser == $ucdbuser && $dbpw == $ucdbpw);
	$s = file_get_contents($discuzconfig);
	$s = trim($s);
	$s = substr($s, -2) == '?>' ? substr($s, 0, -2) : $s;

	$link = mysql_connect($ucdbhost, $ucdbuser, $ucdbpw, 1);
	$uc_connnect = $link && mysql_select_db($ucdbname, $link) ? 'mysql' : '';
	$s = insertconfig($s, "/define\('UC_CONNECT',\s*'.*?'\);/i", "define('UC_CONNECT', '$uc_connnect');");
	$s = insertconfig($s, "/define\('UC_DBHOST',\s*'.*?'\);/i", "define('UC_DBHOST', '$ucdbhost');");
	$s = insertconfig($s, "/define\('UC_DBUSER',\s*'.*?'\);/i", "define('UC_DBUSER', '$ucdbuser');");
	$s = insertconfig($s, "/define\('UC_DBPW',\s*'.*?'\);/i", "define('UC_DBPW', '$ucdbpw');");
	$s = insertconfig($s, "/define\('UC_DBNAME',\s*'.*?'\);/i", "define('UC_DBNAME', '$ucdbname');");
	$s = insertconfig($s, "/define\('UC_DBCHARSET',\s*'.*?'\);/i", "define('UC_DBCHARSET', '$ucdbcharset');");
	$uctablepre = preg_replace("/(.+?\.)/", '', $uctablepre);
	$s = insertconfig($s, "/define\('UC_DBTABLEPRE',\s*'.*?'\);/i", "define('UC_DBTABLEPRE', '`$ucdbname`.$uctablepre');");
	$s = insertconfig($s, "/define\('UC_DBCONNECT',\s*'.*?'\);/i", "define('UC_DBCONNECT', '0');");
	$s = insertconfig($s, "/define\('UC_KEY',\s*'.*?'\);/i", "define('UC_KEY', '$appauthkey');");
	$s = insertconfig($s, "/define\('UC_API',\s*'.*?'\);/i", "define('UC_API', '$ucapi');");
	$s = insertconfig($s, "/define\('UC_CHARSET',\s*'.*?'\);/i", "define('UC_CHARSET', '$uccharset');");
	$s = insertconfig($s, "/define\('UC_IP',\s*'.*?'\);/i", "define('UC_IP', '$ucip');");
	$s = insertconfig($s, "/define\('UC_APPID',\s*'?.*?'?\);/i", "define('UC_APPID', '$appid');");
	$s = insertconfig($s, "/define\('UC_PPP',\s*'?.*?'?\);/i", "define('UC_PPP', '20');");
	//$s = insertconfig($s, "/define\('UC_LINK',\s*'?.*?'?\);/i", "define('UC_LINK', ".($samelink ? 'TRUE' : 'FALSE').");");

	if(!($fp = @fopen($discuzconfig, 'w'))) {
		instmsg('配置文件写入失败，请返回检查 ./config.inc.php 的权限是否为0777 ');
	}

	@fwrite($fp, $s);
	@fclose($fp);
	instmsg("创建配置文件完毕", '?step=4&urladd='.$urladd, $uchidden);

} elseif($step == 4) {

	echo "<h4>用户数据导入到 UCenter</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);
	if(empty($_POST['ucsubmit']) && getgpc('start') === NULL) {

		if(file_exists(DISCUZ_ROOT.'forumdata/upgrademaxuid.log')) {
			$maxuid = file(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
			$maxuid = $maxuid[0];
			instmsg('用户数据导入完毕。', '?step='.($maxuid > 0 ? 'merge' : '5').'&urladd='.$urladd.'&maxuid='.$maxuid, $uchidden);
		}

		if(!($maxuid = getmaxuid())) {
			instmsg('准备导入用户数据 ...', '?step=4&start=0&urladd='.$urladd.'&maxuid='.$maxuid, $uchidden);
		}

?>
<form method="post" action="<?=$_SERVER['PHP_SELF']?>?step=4">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" colspan="2">导入方法</td></tr>
<tr>
<td class="altbg1" width="100"><input name="method" onclick="$('maxuidtable').style.display=''" style="background:none" type="radio" value="0" checked> 标准方法</td>
<td class="altbg2">当 UCenter 中已存在用户数据，本论坛的用户 ID 会按照“用户 ID 起始值”进行提升追加在 UCenter 最大用户 ID 后。</td></tr>
<td class="altbg1" width="100"><input name="method" onclick="$('maxuidtable').style.display='none'" style="background:none" type="radio" value="1"> ECShop 方法</td>
<td class="altbg2">当 UCenter 中存在有 ECShop 用户数据，本论坛的用户 ID 不发生变化并将按照一一对应的方式导入关联。</td></tr>
<td class="altbg1" width="100"><input name="mergerepeat" style="background:none" type="checkbox" value="1"> 合并重复用户</td>
<td class="altbg2">当需要导入的用户在 UCenter 中已存在且密码相同，用户将会进行合并，使用已存在的用户 ID。（此操作会延长升级时间）</td></tr>
</table><br />
<span id="maxuidtable">
<table width="80%" cellspacing="1" bgcolor="#000000" border="0" align="center">
<tr bgcolor="#3A4273"><td style="color: #FFFFFF; padding-left: 10px" colspan="2">用户 ID 起始值</td></tr>
<tr>
<td class="altbg1">用户 ID 起始值:</td>
<td class="altbg2"><input onclick="alert('除非您非常了解此数值的作用，否则不建议您修改此默认值');this.onclick=null" class="txt" type="text" name="maxuidset" value="<?=$maxuid?>" size="10">
起始用户 ID 必须大于等于 <?=$maxuid?>。如填写 10000 那么，原 ID 为 888 的用户将变为 10888。</td>
</tr>
</table><br /></span>
<center>
<input type="button" name="ucsubmit" value=" <?=$lang['old_step']?> " style="height: 25" onclick="history.back()">&nbsp;
<input type="submit" name="ucsubmit" value=" <?=$lang['new_step']?> " style="height: 25"></center>
</center>
</form>
<?

	instfooter();
	exit;
	}

	$method = intval(isset($_POST['method']) ? $_POST['method'] : $_GET['method']);
	$start = intval(getgpc('start'));
	$mergerepeat = intval(isset($_POST['mergerepeat']) ? $_POST['mergerepeat'] : $_GET['mergerepeat']);
	$limit = 5000;
	$total = intval(getgpc('total'));
	$maxuid = !$method ? intval(getgpc('maxuid')) : 0;
	$lastuid = intval(getgpc('lastuid'));

	if(!$total) {
		if(!$method) {
			$maxuid = getmaxuid();
			$maxuidset = intval($_POST['maxuidset']);
			if($maxuidset < $maxuid) {
				@unlink(DISCUZ_ROOT.'forumdata/upgrademaxuid.log');
				instmsg('起始用户 ID 必须大于等于 '.$maxuid.'，请返回重新填写。', '?step=4&urladd='.$urladd, $uchidden);
			} else {
				$maxuid = $maxuidset;
			}
		} else {
			$maxuid = getmaxuid();
		}
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}members");
		$total = $db->result($query, 0);
		$fp = @fopen(DISCUZ_ROOT.'forumdata/upgrademaxuid.log', 'w');
		@fwrite($fp, $maxuid."\r\n此文件提供给论坛插件作者升级插件之用，以上数值为本论坛由 Discuz! 6.0.0 升级到 Discuz! 6.1.0 后 UID 的偏移量");
		@fclose($fp);

		if(!empty($forumfounders)) {
			$discuzconfig = DISCUZ_ROOT.'./config.inc.php';
			$s = file_get_contents($discuzconfig);
			$s = trim($s);
			$s = substr($s, -2) == '?>' ? substr($s, 0, -2) : $s;

			$forumfounderarray = explode(',', $forumfounders);
			foreach($forumfounderarray as $k => $u) {
				$forumfounderarray[$k] = is_numeric($u) ? $u + $maxuid : $u;
			}
			$forumfounders = implode(',', $forumfounderarray);

			$s = insertconfig($s, "/[$]forumfounders\s*\=\s*[\"'].*?[\"'];/is", "\$forumfounders = '$forumfounders';");

			if(!($fp = @fopen($discuzconfig, 'w'))) {
				instmsg('配置文件写入失败，请返回检查 ./config.inc.php 的权限是否为0777 ');
			}

			@fwrite($fp, $s);
			@fclose($fp);
		}
	}
	if($total == 0 || $total <= $start) {
		$ucdb->query("ALTER TABLE ".UC_DBTABLEPRE."members AUTO_INCREMENT=".($lastuid + 1));
		instmsg('用户数据导入完毕。', '?step='.($maxuid > 0 ? 'merge' : '5').'&urladd='.$urladd.'&maxuid='.$maxuid.'&mergerepeat='.$mergerepeat, $uchidden);
	}

	$query = $db->query("SELECT * FROM {$tablepre}members LIMIT $start, $limit");
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	$repeatusers = array();
	while($data = $db->fetch_array($query)) {
		$salt = rand(100000, 999999);
		$password = md5($data['password'].$salt);
		$data['username'] = addslashes($data['username']);
		$lastuid = $data['uid'] += $maxuid;
		$queryuc = $ucdb->query("SELECT uid, salt, password FROM ".UC_DBTABLEPRE."members WHERE username='$data[username]'");
		$ucdata = $ucdb->fetch_array($queryuc);
		if(!$ucdata) {
			$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."members SET uid='$data[uid]', username='$data[username]', password='$password',
				email='$data[email]', regip='$data[regip]', regdate='$data[regdate]', salt='$salt'", 'SILENT');
			$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."memberfields SET uid='$data[uid]'",'SILENT');
		} else {
			if($mergerepeat) {
				if(md5($data['password'].$ucdata['salt']) == $ucdata['password']) {
					$repeatusers[] = $data['uid']."\t".$ucdata['uid'];
				} else {
					$ucdb->query("REPLACE INTO ".UC_DBTABLEPRE."mergemembers SET appid='".UC_APPID."', username='$data[username]'", 'SILENT');
				}
			} elseif(!$method) {
				$ucdb->query("REPLACE INTO ".UC_DBTABLEPRE."mergemembers SET appid='".UC_APPID."', username='$data[username]'", 'SILENT');
			}
		}
	}

	if(!empty($repeatusers)) {
		$fp = fopen(DISCUZ_ROOT.'forumdata/repeatuser.log', 'a+');
		fwrite($fp, implode("\n", $repeatusers));
		fclose($fp);
	}

	$end = $start + $limit;
	instmsg("用户数据导入到 UCenter $start / $total ...", '?step=4&'.$urladd.'&start='.$end.'&total='.$total.'&maxuid='.$maxuid.'&lastuid='.$lastuid.'&method='.$method.'&mergerepeat='.$mergerepeat, $uchidden);

	instfooter();

} elseif($step == 'merge') {

	echo "<h4>合并用户数据</h4>";
	$maxuid = intval(getgpc('maxuid'));
	$mergerepeat = intval(getgpc('mergerepeat'));

	$uidfields = getuidfields();

	$start = intval(getgpc('start'));
	$end = $start + 1;
	$total = count($uidfields);
	if($total == 0 || $total <= $start) {
		if($mergerepeat) {
			instmsg('用户数据合并完毕。', '?step=mergerepeat&urladd='.$urladd, $uchidden);
		} else {
			instmsg('用户数据合并完毕。', '?step=5&urladd='.$urladd, $uchidden);
		}
	}

	$value = $uidfields[$start];
	list($table, $field, $stepfield) = explode('|', $value);
	$logs = array();
	$logs = explode('|', @file_get_contents(DISCUZ_ROOT.'forumdata/upgrade.log'));
	if(!in_array($table, $logs)) {
		$fields = !$field ? array('uid') : explode(',', $field);
		if($stepfield) {
			$mlimit = 5000;
			$mstart = intval(getgpc('mstart'));
			$mtotal = intval(getgpc('total'));
			if(!$mtotal) {
				$query = $db->query("SELECT `$stepfield` FROM `{$tablepre}$table` ORDER BY `$stepfield` DESC LIMIT 1");
				$mtotal = $db->result($query, 0);
			}

			if($mtotal != 0 && $mtotal > $mstart) {
				$mend = $mstart + $mlimit;
				$urladd = 'mstart='.$mend;

				foreach($fields as $field) {
					$db->query("UPDATE `{$tablepre}$table` SET `$field`=`$field`+$maxuid WHERE `$stepfield` >= $mstart AND `$stepfield` < $mend ORDER BY `$field` DESC");
				}

				instmsg("正在处理用户合并数据 {$tablepre}$table $mstart / $mtotal ...", '?step=merge&'.$urladd.'&start='.$start.'&maxuid='.$maxuid.'&mergerepeat='.$mergerepeat, $uchidden);
			} else {
				$fp = fopen(DISCUZ_ROOT.'forumdata/upgrade.log', 'a+');
				fwrite($fp, $table."|");
				fclose($fp);
			}

		} else {
			foreach($fields as $field) {
				$db->query("UPDATE `{$tablepre}$table` SET `$field`=`$field`+$maxuid ORDER BY `$field` DESC");
			}
			$fp = fopen(DISCUZ_ROOT.'forumdata/upgrade.log', 'a+');
			fwrite($fp, $table."|");
			fclose($fp);
		}
	}
	instmsg("{$tablepre}$table 表合并完毕。", '?step=merge&'.$urladd.'&start='.$end.'&maxuid='.$maxuid.'&mergerepeat='.$mergerepeat, $uchidden);

} elseif($step == 'mergerepeat') {

	echo "<h4>合并重复用户</h4>";
	$start = intval(getgpc('start'));
	$end = $start + 1;
	$uids = @file(DISCUZ_ROOT.'forumdata/repeatuser.log');
	$total = count($uids);
	if($total == 0 || $total <= $start) {
		@unlink(DISCUZ_ROOT.'forumdata/repeatuser.log');
		instmsg('重复用户合并完毕。', '?step=5&urladd='.$urladd, $uchidden);
	}

	for($i = $start;$i < $end;$i++) {
		if(empty($uids[$i])) {
			break;
		}
		list($olduid, $newuid) = explode("\t", $uids[$i]);
		$uidfields = getuidfields();
		foreach($uidfields as $value) {
			list($table, $field, $stepfield) = explode('|', $value);
			$fields = !$field ? array('uid') : explode(',', $field);
			foreach($fields as $field) {
				$db->query("UPDATE `{$tablepre}$table` SET `$field`='$newuid' WHERE `$field`='$olduid'");
			}
		}
	}

	instmsg("正在处理重复用户的合并 $start / $total ...", '?step=mergerepeat&'.$urladd.'&start='.$end, $uchidden);

} elseif($step == 5) {

	echo "<h4>导入好友数据</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	$start = intval(getgpc('start'));
	$limit = 5000;
	$total = intval(getgpc('total'));
	if(!$total) {
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}buddys");
		$total = $db->result($query, 0);
	}
	if($total == 0 || $total <= $start) {
		instmsg('导入好友数据完毕。', '?step=6&urladd='.$urladd, $uchidden);
	}

	$query = $db->query("SELECT * FROM {$tablepre}buddys LIMIT $start, $limit");
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	while($data = $db->fetch_array($query)) {
		$ucdb->query("INSERT LOW_PRIORITY INTO ".UC_DBTABLEPRE."friends SET uid='$data[uid]', friendid='$data[buddyid]', direction='1',
			version='0', delstatus='0', comment='$data[description]'", 'SILENT');
	}
	$end = $start + $limit;
	instmsg("正在导入好友数据 $start / $total ...", '?step=5&'.$urladd.'&start='.$end.'&total='.$total, $uchidden);

	instfooter();

} elseif($step == 6) {

	$ucdbhost = $_POST['ucdbhost'];
	$ucdbuser = $_POST['ucdbuser'];
	$ucdbpw = $_POST['ucdbpw'];
	$ucdbname = $_POST['ucdbname'];
	$ucdbcharset = $_POST['ucdbcharset'];
	$uctablepre = $_POST['uctablepre'];

	echo "<h4>处理短消息数据</h4>";

	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	$commonpm = getgpc('commonpm');
	if(!$commonpm) {
		$arr = $db->fetch_first("SELECT uid, username FROM {$tablepre}members WHERE adminid='1' LIMIT 1");
		$query = $db->query("SELECT * FROM {$tablepre}announcements WHERE type='2'");
		while($data = $db->fetch_array($query)) {
			$data['subject'] = addslashes($data['subject']);
			$data['message'] = addslashes($data['message']);
			$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."pms SET msgfrom='$arr[username]', msgfromid='$arr[uid]', msgtoid='0', folder='inbox', subject='$data[subject]', message='$data[message]', dateline='$data[dateline]'");
		}
	}

	$start = intval(getgpc('start'));
	$limit = 5000;
	$total = intval(getgpc('total'));
	if(!$total) {
		$total = $db->result_first("SELECT COUNT(*) FROM {$tablepre}pms");
	}
	$ucdb = new dbstuff();
	$ucdb->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, 0, FALSE, UC_DBCHARSET);

	//mysql_query("SET character_set_connection=latin1, character_set_results=binary, character_set_client=latin1", $ucdb->link);

	$query = $db->query("SELECT * FROM {$tablepre}pms LIMIT $start, $limit");
	if($total == 0 || $total <= $start || $db->errno() == 1146) {
		instmsg(' 处理短消息完毕。', '?step=7');
	}
	if($ucdb->version() > '4.1' && $ucdb == $db && $dbname != UC_DBNAME) {
		$ucdb->query("SET NAMES ".UC_DBCHARSET);
	}
	while($data = $db->fetch_array($query)) {
		$data['subject'] = addslashes($data['subject']);
		$data['message'] = addslashes($data['message']);
		$ucdb->query("INSERT INTO ".UC_DBTABLEPRE."pms SET msgfrom='$data[msgfrom]',
			msgfromid='$data[msgfromid]',msgtoid='$data[msgtoid]',folder='$data[folder]',new='$data[new]',subject='$data[subject]',
			dateline='$data[dateline]',message='$data[message]',delstatus='$data[delstatus]',related='0'", 'SILENT');
	}
	$end = $start + $limit;
	instmsg("正在处理短消息 $start / $total", '?step=6&commonpm=1&start='.$end.'&total='.$total, $uchidden);
	instfooter();

} elseif($step == 7) {

	echo "<h4>新增数据表</h4>";
	$sql = str_replace("\r\n", "\n", $sql);

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	runquery($upgrade1);
	instmsg("新增数据表处理完毕。", '?step=8');
	instfooter();

} elseif($step == 8) {

	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	echo "<h4>调整论坛数据表结构</h4>";
	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

		echo "升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
		$successed = upgradetable($upgradetable[$start]);

		if($successed === TRUE) {
			echo ' <font color=green>OK</font><br />';
		} elseif($successed === FALSE) {
			//echo ' <font color=red>ERROR</font><br />';
		} elseif($successed == 'TABLE NOT EXISTS') {
			echo '<span class=red>数据表不存在</span>升级无法继续，请确认您的论坛版本是否正确!</font><br />';
			instfooter();
			exit;
		}
	}

	$start ++;
	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
		instmsg("请等待 ...", "?step=8&start=$start");
	}
	instmsg("论坛数据表结构调整完毕。", "?step=9");
	instfooter();

} elseif($step == 9) {

	echo "<h4>更新部分数据</h4>";
	runquery($upgrade3);
	upg_adminactions();
	upg_insenz();
	upg_js();
	instmsg("部分数据更新完毕。", "?step=10");
	instfooter();

} else {

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$uc_input = uc_api_input("action=updatecache");

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	echo '<br />恭喜您论坛数据升级成功，接下来请您：<ol><li><b>必删除本程序</b>'.
		'<li>使用管理员身份登录论坛，进入后台，更新缓存'.
		'<li>进行论坛注册、登录、发贴等常规测试，看看运行是否正常'.
		'<li>如果您觉得论坛升级后没有任何问题可以自行删除 forumdata 目录下的 upgrade.log 文件<br /><br />'.
		'<b>感谢您选用我们的产品！</b><a href="index.php" target="_blank">您现在可以访问论坛，查看升级情况</a><iframe width="0" height="0" src="index.php"></iframe>';
	instfooter();

}

function insertconfig($s, $find, $replace) {
	if(preg_match($find, $s)) {
		$s = preg_replace($find, $replace, $s);
	} else {
		// 插入到最后一行
		$s .= "\r\n".$replace;
	}
	return $s;
}

function instheader() {
	global $charset, $version;

	echo "<html><head>".
		"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">".
		"<title>$version[old] &gt;&gt; $version[new] 升级向导</title>".
		"<style type=\"text/css\">
		a {
			color: #3A4273;
			text-decoration: none
		}

		a:hover {
			color: #3A4273;
			text-decoration: underline
		}

		body, table, td {
			color: #3A4273;
			font-family: Tahoma, Verdana, Arial;
			font-size: 12px;
			line-height: 20px;
			scrollbar-base-color: #E3E3EA;
			scrollbar-arrow-color: #5C5C8D
		}

		input {
			color: #085878;
			font-family: Tahoma, Verdana, Arial;
			font-size: 12px;
			background-color: #3A4273;
			color: #FFFFFF;
			scrollbar-base-color: #E3E3EA;
			scrollbar-arrow-color: #5C5C8D
		}

		.install {
			font-family: Arial, Verdana;
			font-size: 20px;
			font-weight: bold;
			color: #000000
		}

		.message {
			background: #E3E3EA;
			padding: 20px;
		}

		.altbg1 {
			background: #E3E3EA;
		}

		.altbg2 {
			background: #EEEEF6;
		}

		.header td {
			color: #FFFFFF;
			background-color: #3A4273;
			text-align: center;
		}

		.option td {
			text-align: center;
		}

		.redfont {
			color: #FF0000;
		}
		</style>
		<script type=\"text/javascript\">
		function redirect(url) {
			window.location=url;
		}
		function $(id) {
			return document.getElementById(id);
		}
		</script>
		</head>".
		"<body bgcolor=\"#3A4273\" text=\"#000000\"><div id=\"append_parent\"></div>".
		"<table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#FFFFFF\" align=\"center\"><tr><td>".
      		"<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr>".
          	"<td class=\"install\" height=\"30\" valign=\"bottom\"><font color=\"#FF0000\">&gt;&gt;</font> $version[old] &gt;&gt; $version[new] 升级向导 ".
          	"</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr><tr><td colspan=\"2\">";
}

function instfooter() {
	global $version;

	echo "</td></tr><tr><td><hr noshade align=\"center\" width=\"100%\" size=\"1\"></td></tr>".
        	"<tr><td align=\"center\">".
            	"<b style=\"font-size: 11px\">Powered by <a href=\"http://discuz.net\" target=\"_blank\">Discuz!".
          	"</a> &nbsp; Copyright &copy; <a href=\"http://www.comsenz.com\" target=\"_blank\">Comsenz Inc.</a> 2001-2009</b><br /><br />".
          	"</td></tr></table></td></tr></table>".
		"</body></html>";
}

function instmsg($message, $url_forward = '', $postdata = '') {
	global $lang, $msglang;
	$message = $msglang[$message] ? $msglang[$message] : $message;
	if($postdata) {
		$message .= "<br /><br /><br /><a href=\"###\" onclick=\"document.getElementById('postform').submit();\">$msglang[redirect_msg]</a>";
		echo '<form action="'.$url_forward.'" method="post" id="postform">';
		echo $postdata;
		echo	"<tr><td style=\"padding-top:50px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
			"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
	  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></td></tr>";
		echo '</form><script>setTimeout("document.getElementById(\'postform\').submit()", 1250);</script>';
		instfooter();
	} else {
		if($url_forward) {
			$message .= "<br /><br /><br /><a href=\"$url_forward\">$msglang[redirect_msg]</a>";
			$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
		} elseif(strpos($message, $lang['return'])) {
			$message .= "<br /><br /><br /><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a>";
		}

		echo 	"<tr><td style=\"padding-top:50px; padding-bottom:100px\"><table width=\"560\" cellspacing=\"1\" bgcolor=\"#000000\" border=\"0\" align=\"center\">".
			"<tr bgcolor=\"#3A4273\"><td width=\"20%\" style=\"color: #FFFFFF; padding-left: 10px\">$lang[error_message]</td></tr>".
	  		"<tr align=\"center\" bgcolor=\"#E3E3EA\"><td class=\"message\">$message</td></tr></table></td></tr>";
		instfooter();
	}
	exit;
}


function getgpc($k, $var='G') {
	switch($var) {
		case 'G': $var = &$_GET; break;
		case 'P': $var = &$_POST; break;
		case 'C': $var = &$_COOKIE; break;
		case 'R': $var = &$_REQUEST; break;
	}
	return isset($var[$k]) ? $var[$k] : NULL;
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	@touch($dir.'/index.htm');
	$directory->close();
}

function var_to_hidden($k, $v) {
	return "<input type=\"hidden\" name=\"$k\" value=\"$v\" />";
}

function getmaxuid() {
	global $ucdb;

	$query = $ucdb->query("SHOW TABLE STATUS LIKE '".substr(UC_DBTABLEPRE, strpos(UC_DBTABLEPRE, ".")+1)."members'");
	$data = $ucdb->fetch_array($query);
	if($data["Auto_increment"]) {
		return $data["Auto_increment"] - 1;
	} else {
		return 0;
	}
}

function getuidfields() {
	return array(
		'members',
		'memberfields',
		'access',
		'activities',
		'activityapplies',
		'attachments',
		'attachpaymentlog',
		'buddys|uid,buddyid',
		'creditslog',
		'debateposts',
		'debates',
		'favorites',
		'forumrecommend|authorid,moderatorid',
		'invites',
		'magiclog',
		'magicmarket',
		'membermagics',
		'memberspaces',
		'moderators',
		'modworks',
		'myposts',
		'mythreads',
		'onlinetime',
		'orders',
		'paymentlog|uid,authorid',
		'pms|msgtoid,msgfromid|pmid',
		'posts|authorid|pid',
		'promotions',
		'ratelog',
		'rewardlog|authorid,answererid',
		'searchindex|uid',
		'spacecaches',
		'subscriptions',
		'threads|authorid|tid',
		'threadsmod',
		'tradecomments|raterid,rateeid',
		'tradelog|sellerid,buyerid',
		'trades|sellerid',
		'validating',
		'videos',
	);
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].'?'.$matches['query'].'#'.$matches['fragment'] : '/';
	$port = !empty($matches['port']) ? $matches['port'] : 80;

	if($post) {
		$out = "POST $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= 'Content-Length: '.strlen($post)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cache-Control: no-cache\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
		$out .= $post;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$out .= "Accept: */*\r\n";
		//$out .= "Referer: $boardurl\r\n";
		$out .= "Accept-Language: zh-cn\r\n";
		$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$out .= "Host: $host\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Cookie: $cookie\r\n\r\n";
	}
	$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp)) {
				if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}

			$stop = false;
			while(!feof($fp) && !$stop) {
				$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
				$return .= $data;
				if($limit) {
					$limit -= strlen($data);
					$stop = $limit <= 0;
				}
			}
		}
		@fclose($fp);
		return $return;
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;

	$query = str_replace("\r", "\n", str_replace(' cdb_', ' '.$tablepre, $query));
	$expquery = explode(";\n", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function upgradetable($updatesql) {
	global $db, $tablepre, $dbcharset;

	$successed = TRUE;

	if(is_array($updatesql) && !empty($updatesql[0])) {

		list($table, $action, $field, $sql) = $updatesql;

		if(empty($field) && !empty($sql)) {

			$query = "ALTER TABLE {$tablepre}{$table} ";
			if($action == 'INDEX') {
				$successed = $db->query("$query $sql", "SILENT");
			} elseif ($action == 'UPDATE') {
				$successed = $db->query("UPDATE {$tablepre}{$table} SET $sql", 'SILENT');
			}

		} elseif($tableinfo = loadtable($table)) {

			$fieldexist = isset($tableinfo[$field]) ? 1 : 0;

			$query = "ALTER TABLE {$tablepre}{$table} ";

			if($action == 'MODIFY') {

				$query .= $fieldexist ? "MODIFY $field $sql" : "ADD $field $sql";
				$successed = $db->query($query, 'SILENT');

			} elseif($action == 'CHANGE') {

				$field2 = trim(substr($sql, 0, strpos($sql, ' ')));
				$field2exist = isset($tableinfo[$field2]);

				if($fieldexist && ($field == $field2 || !$field2exist)) {
					$query .= "CHANGE $field $sql";
				} elseif($fieldexist && $field2exist) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "CHANGE $field $sql";
				} elseif(!$fieldexist && $fieldexist2) {
					$db->query("ALTER TABLE {$tablepre}{$table} DROP $field2", 'SILENT');
					$query .= "ADD $sql";
				} elseif(!$fieldexist && !$field2exist) {
					$query .= "ADD $sql";
				}
				$successed = $db->query($query);

			} elseif($action == 'ADD') {

				$query .= $fieldexist ? "CHANGE $field $field $sql" :  "ADD $field $sql";
				$successed = $db->query($query);

			} elseif($action == 'DROP') {
				if($fieldexist) {
					$successed = $db->query("$query DROP $field", "SILENT");
				}
				$successed = TRUE;
			}

		} else {

			$successed = 'TABLE NOT EXISTS';

		}
	}
	return $successed;
}

function loadtable($table, $force = 0) {
	global $db, $tablepre, $dbcharset;
	static $tables = array();

	if(!isset($tables[$table]) || $force) {
		if($db->version() > '4.1') {
			$query = $db->query("SHOW FULL COLUMNS FROM {$tablepre}$table", 'SILENT');
		} else {
			$query = $db->query("SHOW COLUMNS FROM {$tablepre}$table", 'SILENT');
		}
		while($field = @$db->fetch_array($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function upg_adminactions() {
	global $db, $tablepre;

	$actionarray = array(
		'settings' => 'settings',
		'forumsedit' => 'forums',
		'forumdetail' => 'forums_edit',
		'moderators' => 'forums_moderators',
		'forumdelete' => 'forums_delete',
		'forumsmerge' => 'forums_merge',
		'forumcopy' => 'forums_copy',
		'threadtypes' => 'threadtypes',
		'members' => 'members',
		'forumadd' => 'members_add',
		'editgroups' => 'members_editgroups',
		'access' => 'members_access',
		'editcredits' => 'members_editcredits',
		'editmedals' => 'members_editmedals',
		'memberprofile' => 'members_profile',
		'profilefields' => 'members_profilefields',
		'ipban' => 'members_ipban',
		'membersmerge' => 'members_merge',
		'usergroups' => 'groups_user',
		'admingroups' => 'groups_admin',
		'ranks' => 'groups_ranks',
		'styles' => 'styles',
		'templates' => 'templates',
		'tpladd' => 'templates_add',
		'tpledit' => 'templates_edit',
		'modmembers' => 'moderate_members',
		'modthreads' => 'moderate_threads',
		'modreplies' => 'moderate_replies',
		'threads' => 'threads',
		'prune' => 'prune',
		'recyclebin' => 'recyclebin',
		'announcements' => 'announcements',
		'forumlinks' => 'misc_forumlinks',
		'onlinelist' => 'misc_onlinelist',
		'censor' => 'misc_censor',
		'discuzcodes' => 'misc_discuzcodes',
		'tags' => 'misc_tags',
		'smilies' => 'smilies',
		'icons' => 'misc_icons',
		'attachtypes' => 'misc_attachtypes',
		'crons' => 'misc_crons',
		'adv' => 'advertisements',
		'advadd' => 'advertisements_add',
		'advedit' => 'advertisements_edit',
		'runquery' => 'database_runquery',
		'optimize' => 'database_optimize',
		'export' => 'database_export',
		'import' => 'database_import',
		'updatecache' => 'tools_updatecache',
		'fileperms' => 'tools_fileperms',
		'relatedtag' => 'tools_relatedtag',
		'attachments' => 'attachments',
		'counter' => 'counter',
		'jswizard' => 'jswizard',
		'creditwizard' => 'creditwizard',
		'google_config' => 'google_config',
		'qihoo_config' => 'qihoo_config',
		'qihoo_topics' => 'qihoo_topics',
		'alipay' => 'ecommerce_alipay',
		'orders' => 'ecommerce_orders',
		'medals' => 'medals',
		'plugins' => 'plugins',
		'pluginsconfig' => 'plugins_config',
		'pluginsedit' => 'plugins_edit',
		'pluginhooks' => 'plugins_hooks',
		'pluginvars' => 'plugins_vars',
		'illegallog' => 'logs_illegal',
		'ratelog' => 'logs_rate',
		'modslog' => 'logs_mods',
		'medalslog' => 'logs_medals',
		'banlog' => 'logs_ban',
		'cplog' => 'logs_cp',
		'creditslog' => 'logs_credits',
		'errorlog' => 'logs_error'
	);

	$da = array();
	$query = $db->query("SELECT * FROM {$tablepre}adminactions");
	while($a = $db->fetch_array($query)) {
		if($a['disabledactions']) {
			$da = @unserialize($a['disabledactions']);
			if(is_array($da) && $da) {
				foreach($da as $k => $v) {
					if(isset($actionarray[$v])) {
						$da[$k] = $actionarray[$v];
					} else {
						unset($da[$k]);
					}
				}
			} else {
				$da = array();
			}
			$db->query("UPDATE {$tablepre}adminactions SET disabledactions='".addslashes(serialize($da))."' WHERE admingid='$a[admingid]'");
		}
	}
}

function upg_insenz() {
	global $db, $tablepre;

	$insenz = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='insenz'");
	$insenz = $insenz ? unserialize($insenz) : array();
	if($insenz) {
		if($insenz['admin_masks'] && is_array($insenz['admin_masks'])) {
			$insenz['admin_masks'] = array_keys($insenz['admin_masks']);
		}
		if($insenz['member_masks'] && is_array($insenz['member_masks'])) {
			$insenz['member_masks'] = array_keys($insenz['member_masks']);
		}
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('insenz', '".addslashes(serialize($insenz))."')");
	}
}

function upg_js() {
	global $db, $tablepre;

$request_tpl = array(
	0 => '<div class=\\"box\\">
<h4>本版热门主题</h4>
<ul class=\\"textinfolist\\">
[node]<li>[{author}]{subject}</li>[/node]
</ul>
</div>',
	1 => '<div class=\\"box\\">
<h4>今日热门主题</h4>
<ul class=\\"textinfolist\\">
[node]<li>[{author}]{subject}</li>[/node]
</ul>
</div>',
	2 => '<div class=\\"box\\">
<h4>本版最新回复</h4>
<ul class=\\"textinfolist\\">
[node]<li>{subject} ({replies}/{views})</li>[/node]
</ul>
</div>',
	3 => '<div class=\\"box\\">
<h4>活跃用户</h4>
<ul class=\\"imginfolist\\">
[node]<li>{avatarsmall}<p>{member}</p></li>[/node]
</ul>
</div>',
);
	$request_data = array (
		'default_hotthreads' => array (
			'url' => 'function=threads&sidestatus=1&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=views&hours=0&jscharset=0&cachelife=900&jstemplate='.rawurlencode($request_tpl[0]),
			'parameter' => array (
					'jstemplate' => $request_tpl[0],
					'cachelife' => '900',
					'sidestatus' => '1',
					'startrow' => '0',
					'items' => '10',
					'maxlength' => '50',
					'fnamelength' => '0',
					'messagelength' => '',
					'picpre' => '',
					'tids' => '',
					'keyword' => '',
					'tag' => '',
					'threadtype' => '0',
					'highlight' => '0',
					'recommend' => '0',
					'newwindow' => 1,
					'orderby' => 'views',
					'hours' => '',
					'jscharset' => '0',
			    ),
			    'comment' => '本版热门主题',
			    'type' => '0',
	  	),
		'default_hotthreads24hrs' => array (
			'url' => 'function=threads&sidestatus=0&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=hourviews&hours=24&jscharset=0&jstemplate='.rawurlencode($request_tpl[1]),
			'parameter' => array (
				'jstemplate' => $request_tpl[1],
				'cachelife' => '',
				'sidestatus' => '0',
				'startrow' => '0',
				'items' => '10',
				'maxlength' => '50',
				'fnamelength' => '0',
				'messagelength' => '',
				'picpre' => '',
				'tids' => '',
				'keyword' => '',
				'tag' => '',
				'threadtype' => '0',
				'highlight' => '0',
				'recommend' => '0',
				'newwindow' => 1,
				'orderby' => 'hourviews',
				'hours' => '24',
				'jscharset' => '0',
			),
			'comment' => '今日热门主题',
			'type' => '0',
		),
		'default_newreplies' => array (
			'url' => 'function=threads&sidestatus=1&maxlength=50&fnamelength=0&messagelength=&startrow=0&picpre=&items=10&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=lastpost&hours=0&jscharset=0&cachelife=900&jstemplate='.rawurlencode($request_tpl[2]),
			'parameter' => array (
				'jstemplate' => $request_tpl[2],
				'cachelife' => '900',
				'sidestatus' => '1',
				'startrow' => '0',
				'items' => '10',
				'maxlength' => '50',
				'fnamelength' => '0',
				'messagelength' => '',
				'picpre' => '',
				'tids' => '',
				'keyword' => '',
				'tag' => '',
				'threadtype' => '0',
				'highlight' => '0',
				'recommend' => '0',
				'newwindow' => 1,
				'orderby' => 'lastpost',
				'hours' => '',
				'jscharset' => '0',
			),
			'comment' => '本版最新回复',
			'type' => '0',
		),
		'default_hotmembers' => array (
			'url' => 'function=memberrank&startrow=0&items=9&newwindow=1&extcredit=1&orderby=posts&hours=0&jscharset=0&cachelife=1800&jstemplate='.rawurlencode($request_tpl[3]),
			'parameter' => array (
				'jstemplate' => $request_tpl[3],
				'cachelife' => '1800',
				'startrow' => '0',
				'items' => '9',
				'newwindow' => 1,
				'extcredit' => '1',
				'orderby' => 'posts',
				'hours' => '',
				'jscharset' => '0',
			),
			'comment' => '活跃用户',
			'type' => '2',
		),
		'边栏1' => array (
			'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5Ddefault_hotthreads%5B%2Fmodule%5D%5Bmodule%5Ddefault_hotmembers%5B%2Fmodule%5D',
			'parameter' => array (
				'selectmodule' =>
				array (
					0 => 'default_hotthreads',
					1 => 'default_hotmembers',
				),
				'cachelife' => '',
				'jstemplate' => '[module]default_hotthreads[/module][module]default_hotmembers[/module]',
			),
			'comment' => NULL,
			'type' => '-2',
		),
		'边栏2' => array (
			'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5Ddefault_newreplies%5B%2Fmodule%5D%5Bmodule%5Ddefault_hotthreads24hrs%5B%2Fmodule%5D',
			'parameter' =>
			array (
				'selectmodule' =>
				array (
						0 => 'default_newreplies',
						1 => 'default_hotthreads24hrs',
				),
				'cachelife' => '',
				'jstemplate' => '[module]default_newreplies[/module][module]default_hotthreads24hrs[/module]',
			),
			'comment' => NULL,
			'type' => '-2',
		),
	);

	$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable like 'jswizard_%'");
	while($data = $db->fetch_array($query)) {
		$variable = substr($data['variable'], 9);
		$value = unserialize($data['value']);
		$type = $value['type'];
		$value = addslashes(serialize($value));

		$db->query("INSERT INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}

	foreach($request_data as $k => $v) {
		$variable = $k;
		$type = $v['type'];
		$value = addslashes(serialize($v));

		$db->query("REPLACE INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}

	$query = $db->query("DELETE FROM {$tablepre}settings WHERE variable like 'jswizard_%'");
}

?>
