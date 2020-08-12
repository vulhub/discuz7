<?php

// Upgrade Discuz! Board from 4.0.0 to 4.1.0

@set_time_limit(1000);

define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

$version_old = 'Discuz! 4.0.0';
$version_new = 'Discuz! 4.1.0';

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("请先上传所有新版本的程序文件后再运行本升级程序");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

if(empty($dbcharset) && in_array(strtolower($charset), array('gbk', 'big5', 'utf-8'))) {
	$dbcharset = str_replace('-', '', $charset);
}

if(PHP_VERSION < '4.1.0') {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
}

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = $_GET['start'];

$upgrade1 = <<<EOT
DELETE FROM cdb_sessions;
ALTER TABLE cdb_sessions TYPE=HEAP;

DELETE FROM cdb_settings WHERE variable='avatarext';
REPLACE INTO cdb_settings (variable, value) VALUES ('bdaystatus', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('maxspm', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('rewritestatus', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('watermarkquality', 80);
REPLACE INTO cdb_settings (variable, value) VALUES ('boardlicensed', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('regfloodctrl', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('modworkstatus', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('maxmodworksmonths', 3);

DROP TABLE IF EXISTS cdb_crons;
CREATE TABLE cdb_crons (
  cronid smallint(6) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  type enum('user','system') NOT NULL default 'user',
  name char(50) NOT NULL default '',
  filename char(50) NOT NULL default '',
  lastrun int(10) unsigned NOT NULL default '0',
  nextrun int(10) unsigned NOT NULL default '0',
  weekday tinyint(1) NOT NULL default '0',
  day tinyint(2) NOT NULL default '0',
  hour tinyint(2) NOT NULL default '0',
  minute char(36) NOT NULL default '',
  PRIMARY KEY  (cronid),
  KEY nextrun (available,nextrun)
) Type=MyISAM;

INSERT INTO cdb_crons VALUES (1, 1, 'system', '清空今日发帖数', 'todayposts_daily.inc.php', 1130139337, 1130169600, -1, -1, 0, '0');
INSERT INTO cdb_crons VALUES (2, 1, 'system', '清空本月在线时间', 'onlinetime_monthly.inc.php', 1130139557, 1130774400, -1, 1, 0, '0');
INSERT INTO cdb_crons VALUES (3, 1, 'system', '每日数据清理', 'cleanup_daily.inc.php', 1130142545, 1130189400, -1, -1, 5, '30');
INSERT INTO cdb_crons VALUES (4, 1, 'system', '生日统计与邮件祝福', 'birthdays_daily.inc.php', 1130139241, 1130169600, -1, -1, 0, '0');
INSERT INTO cdb_crons VALUES (5, 1, 'system', '主题回复通知', 'notify_daily.inc.php', 1130142545, 1130189400, -1, -1, 5, '00');
INSERT INTO cdb_crons VALUES (6, 1, 'system', '每日公告清理', 'announcements_daily.inc.php', 0, 1131284204, -1, -1, 0, '0');
INSERT INTO cdb_crons VALUES (7, 1, 'system', '限时操作清理', 'threadexpiries_daily.inc.php',0,1138464000, -1, -1, 5, 0);
INSERT INTO cdb_crons VALUES (8, 1, 'system', '论坛推广清理', 'promotions_hourly.inc.php', 0,1130169600, -1, -1, 0, '00');

DROP TABLE IF EXISTS cdb_pluginhooks;
CREATE TABLE cdb_pluginhooks (
  pluginhookid mediumint(8) unsigned NOT NULL auto_increment,
  pluginid smallint(6) unsigned NOT NULL default '0',
  available tinyint(1) NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  description mediumtext NOT NULL,
  code mediumtext NOT NULL,
  PRIMARY KEY  (pluginhookid),
  KEY pluginid (pluginid),
  KEY available (available)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_pmsearchindex;
CREATE TABLE cdb_pmsearchindex (
  searchid int(10) unsigned NOT NULL auto_increment,
  keywords varchar(255) NOT NULL default '',
  searchstring varchar(255) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  expiration int(10) unsigned NOT NULL default '0',
  pms smallint(6) unsigned NOT NULL default '0',
  pmids text NOT NULL,
  PRIMARY KEY  (searchid)
) TYPE=MyISAM;
EOT;

$upgrade2 = <<<EOT
UPDATE cdb_settings SET variable='qihoo_status' WHERE variable='qihoostatus';
UPDATE cdb_settings SET variable='qihoo_searchbox' WHERE variable='searchbox';
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_summary', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_keywords', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_relatedthreads', '5');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_topics', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_maxtopics', '10');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_relatedthreads', '5');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_validity', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('qihoo_adminemail', '');

DROP TABLE IF EXISTS cdb_relatedthreads;
CREATE TABLE cdb_relatedthreads (
  tid mediumint(8) NOT NULL default '0',
  expiration int(10) NOT NULL default '0',
  keywords varchar(255) NOT NULL default '',
  relatedthreads text NOT NULL,
  PRIMARY KEY  (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_promotions;
CREATE TABLE cdb_promotions (
  ip char(15) NOT NULL default '',
  uid mediumint(8) NOT NULL default '0',
  username char(15) NOT NULL default '',
  PRIMARY KEY  (ip)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_modworks;
CREATE TABLE cdb_modworks (
  uid mediumint(8) unsigned NOT NULL default '0',
  modaction char(3) NOT NULL default '',
  dateline date NOT NULL default '2006-1-1',
  count smallint(6) unsigned NOT NULL default '0',
  posts smallint(6) unsigned NOT NULL default '0',
  KEY uid (uid,dateline)
) TYPE=MyISAM;

ALTER TABLE cdb_announcements ADD INDEX timespan (starttime, endtime);
ALTER TABLE cdb_threads ADD subscribed TINYINT(1) NOT NULL AFTER attachment;
DELETE FROM cdb_subscriptions;
ALTER TABLE cdb_subscriptions DROP PRIMARY KEY, ADD PRIMARY KEY (tid, uid), DROP email, ADD lastpost int(10) UNSIGNED NOT NULL AFTER tid;
EOT;

$upgrade3 = <<<EOT
ALTER TABLE cdb_usergroups ADD allowanonymous TINYINT(1) NOT NULL DEFAULT '0' AFTER allowcusbbcode;
ALTER TABLE cdb_forums ADD allowanonymous TINYINT(1) NOT NULL DEFAULT '0' AFTER allowimgcode;
ALTER TABLE cdb_advertisements CHANGE targets targets TEXT NOT NULL;
ALTER TABLE cdb_orders CHANGE amount amount int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE cdb_paymentlog CHANGE amount amount INT(10) UNSIGNED NOT NULL DEFAULT '0', CHANGE netamount netamount INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE cdb_ratelog CHANGE reason reason CHAR(40) NOT NULL;

ALTER TABLE cdb_threadsmod ADD expiration int(10) unsigned NOT NULL default '0' AFTER dateline, ADD status tinyint(1) NOT NULL default '0' AFTER action;
ALTER TABLE cdb_threadsmod DROP PRIMARY KEY, ADD INDEX (tid , dateline), ADD INDEX (expiration ,status);
DELETE FROM cdb_stylevars WHERE variable='maintablespace';
UPDATE cdb_stylevars SET substitute='98%' WHERE styleid=1 AND variable='tablewidth';

EOT;

if(!$action) {
	echo "本程序用于升级 $version_old 到 $version_new,请确认之前已经顺利安装 $version_old <br><br><br>",
		"<b><font color=\"red\">本升级程序只能从 $version_old 升级到 $version_new ,运行之前,请确认已经上传 $version_new 的全部文件和目录</font></b><br>",
		"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>",
		"正确的升级方法为:<br><ol><li>关闭原有论坛,上传 $version_new 的全部文件和目录,覆盖服务器上的 $version_old <li>上传本程序到 Discuz! 目录中，并确认已经重新配置好 config.inc.php <li>运行本程序,直到出现升级完成的提示</ol><br>",
		"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		runquery($upgrade2);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);
		$db->query("UPDATE {$tablepre}crons SET lastrun='0', nextrun='".(time() + 3600)."'");

		echo "第 $step 步升级成功<br><br>下一步升级需要占用较大服务器资源，如果程序长时间没有提示升级完成，您可以不必理会，也无需重复进行升级，稍后直接访问论坛即可。";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		$db->query("UPDATE {$tablepre}crons SET lastrun='0', nextrun='".(time() + 3600)."'");
		$db->query("ALTER TABLE {$tablepre}posts ADD anonymous TINYINT(1) NOT NULL default '0' AFTER invisible", 'UNBUFFERD');

		echo "恭喜您升级成功,请务必删除本程序.";

	}
}

function dir_clear($dir) {
	$directory = dir($dir);
	while($entry = $directory->read()) {
		$filename = $dir.'/'.$entry;
		if(is_file($filename)) {
			@unlink($filename);
		}
	}
	$directory->close();
}

function daddslashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = daddslashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function loginit($log) {
	global $lang;

	$fp = @fopen('./forumdata/'.$log.'.php');
	@fwrite($fp, "<?PHP exit(\"Access Denied\"); ?>\n");
	@fclose($fp);
}

function runquery($query) {
	global $db, $tablepre, $dbcharset;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql == '' || $sql[0] == '#') continue;

		$sql = str_replace(' cdb_', ' '.$tablepre, $sql);
		if(strtoupper(substr($sql, 0, 12)) == 'CREATE TABLE') {
			$db->query(createtable($sql, $dbcharset));
		} else {
			$db->query($sql);
		}
	}
}

function redirect($url) {

	echo "<script>",
		"function redirect() {window.location.replace('$url');}\n",
		"setTimeout('redirect();', 500);\n",
		"</script>",
		"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(mysql_get_server_info() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

?>