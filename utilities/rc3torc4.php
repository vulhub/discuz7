<?php

// Upgrade Discuz! Board from 4.0.0RC3 to 4.0.0RC4

@set_time_limit(1000);
define('IN_DISCUZ', TRUE);
define('DISCUZ_ROOT', './');

if(@(!include("./config.inc.php")) || @(!include("./include/db_mysql.class.php"))) {
	exit("请先上传所有新版本的程序文件后再运行本升级程序");
}

header("Content-Type: text/html; charset=$charset");

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);

$action = ($_POST['action']) ? $_POST['action'] : $_GET['action'];
$step = $_GET['step'];
$start = $_GET['start'];

$upgrade1 = <<<EOT
ALTER TABLE cdb_posts ADD first tinyint(1) NOT NULL AFTER tid, ADD INDEX first (tid, first), DROP INDEX dotfolder;
EOT;

$upgrade2 = <<<EOT
DROP TABLE IF EXISTS cdb_paymentlog;
CREATE TABLE cdb_paymentlog (
  uid mediumint(8) UNSIGNED NOT NULL,
  tid mediumint(8) UNSIGNED NOT NULL,
  authorid mediumint(8) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  amount smallint(6) NOT NULL,
  netamount smallint(6) NOT NULL,
  PRIMARY KEY (tid, uid),
  INDEX (uid),
  INDEX (authorid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_creditslog;
CREATE TABLE cdb_creditslog (
  uid mediumint(8) UNSIGNED NOT NULL,
  fromto char(15) NOT NULL,
  sendcredits tinyint(1) NOT NULL,
  receivecredits tinyint(1) NOT NULL,
  send int(10) UNSIGNED NOT NULL,
  receive int(10) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  operation char(3) NOT NULL,
  INDEX (uid, dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_rsscaches;
CREATE TABLE cdb_rsscaches (
  lastupdate int(10) UNSIGNED NOT NULL,
  fid smallint(6) UNSIGNED NOT NULL,
  tid mediumint(8) UNSIGNED NOT NULL,
  dateline int(10) UNSIGNED NOT NULL,
  forum char(50) NOT NULL,
  author char(15) NOT NULL,
  subject char(80) NOT NULL,
  description char(255) NOT NULL,
  INDEX (fid, dateline)
) TYPE=MyISAM;

REPLACE INTO cdb_settings VALUES ('wapstatus', '1');
REPLACE INTO cdb_settings VALUES ('waptpp', '10');
REPLACE INTO cdb_settings VALUES ('wapppp', '5');
REPLACE INTO cdb_settings VALUES ('wapdateformat', 'n/j');
REPLACE INTO cdb_settings VALUES ('wapmps', '500');
REPLACE INTO cdb_settings VALUES ('wapcharset', '2');
REPLACE INTO cdb_settings VALUES ('avatarshowstatus', 1);
REPLACE INTO cdb_settings VALUES ('avatarshowwidth', 135);
REPLACE INTO cdb_settings VALUES ('avatarshowheight', 200);
REPLACE INTO cdb_settings VALUES ('avatarshowpos', 3);
REPLACE INTO cdb_settings VALUES ('avatarshowdefault', 0);
REPLACE INTO cdb_settings VALUES ('avatarshowlink', 1);
REPLACE INTO cdb_settings VALUES ('maxsigrows', 20);
REPLACE INTO cdb_settings VALUES ('bannedmessages', 1);
REPLACE INTO cdb_settings VALUES ('creditsformula', 'extcredits1');
REPLACE INTO cdb_settings VALUES ('ipaccess', '');
REPLACE INTO cdb_settings VALUES ('pvfrequence', '60');
REPLACE INTO cdb_settings VALUES ('adminipaccess', '');
REPLACE INTO cdb_settings VALUES ('stylejump', '0');
REPLACE INTO cdb_settings VALUES ('jsstatus', '0');
REPLACE INTO cdb_settings VALUES ('jscachelife', '1800');
REPLACE INTO cdb_settings VALUES ('jsrefdomains', '');
REPLACE INTO cdb_settings VALUES ('extcredits', '');
REPLACE INTO cdb_settings VALUES ('creditspolicy', '');

ALTER TABLE cdb_members ADD extgroupids char(60) NOT NULL AFTER groupexpiry, CHANGE email email char(50) NOT NULL, ADD avatarshowid int(10) UNSIGNED NOT NULL default '0' AFTER extcredits5, ADD extcredits8 int(10) NOT NULL AFTER extcredits5, ADD extcredits7 int(10) NOT NULL AFTER extcredits5, ADD extcredits6 int(10) NOT NULL AFTER extcredits5, ADD pageviews mediumint(8) UNSIGNED NOT NULL AFTER digestposts, ADD oltime smallint(6) UNSIGNED NOT NULL AFTER digestposts;
EOT;

$upgrade3 = <<<EOT
ALTER TABLE cdb_usergroups ADD public tinyint(1) NOT NULL AFTER radminid, ADD allowmultigroups tinyint(1) NOT NULL AFTER allowvote, CHANGE allowcstatus allowcstatus1 tinyint(1) NOT NULL, CHANGE allowavatar allowavatar1 tinyint(1) NOT NULL, ADD allowcstatus tinyint(1) NOT NULL AFTER allowsearch, ADD allowavatar tinyint(1) NOT NULL AFTER allowsearch, ADD allowreply tinyint(1) NOT NULL AFTER allowpost;
UPDATE cdb_usergroups SET allowcstatus=allowcstatus1, allowavatar=allowavatar1, allowreply=allowpost;
ALTER TABLE cdb_usergroups DROP allowcstatus1, DROP allowavatar1;
UPDATE cdb_usergroups SET allowmultigroups=1 WHERE groupid NOT IN (4,5,6,7,8) AND (type<>'member' OR (type='member' AND creditshigher>=500));

ALTER TABLE cdb_forums ADD todayposts MEDIUMINT(8) UNSIGNED NOT NULL AFTER posts, ADD INDEX (fup);
ALTER TABLE cdb_sessions ADD pageviews smallint(6) UNSIGNED NOT NULL AFTER lastolupdate;
ALTER TABLE cdb_forumfields ADD description text NOT NULL AFTER fid, ADD postcredits varchar(255) NOT NULL AFTER icon, ADD replycredits varchar(255) NOT NULL AFTER postcredits;
ALTER TABLE cdb_attachments ADD dateline int(10) UNSIGNED NOT NULL AFTER pid;
ALTER TABLE cdb_buddys ADD dateline int(10) UNSIGNED NOT NULL, ADD description CHAR(255) NOT NULL;
UPDATE cdb_buddys SET dateline=UNIX_TIMESTAMP();
ALTER TABLE cdb_smilies ADD displayorder tinyint(1) NOT NULL AFTER id;
ALTER TABLE cdb_profilefields ADD unchangeable TINYINT(1) NOT NULL AFTER required;
ALTER TABLE cdb_ratelog ADD INDEX (dateline), ADD extcredits tinyint(1) UNSIGNED NOT NULL AFTER username;
UPDATE cdb_ratelog SET extcredits=1;

ALTER TABLE cdb_usergroups ADD raterange char(120) NOT NULL AFTER attachextensions;
UPDATE cdb_usergroups SET raterange=CONCAT_WS('\t', allowkarma, minkarmarate, maxkarmarate, maxrateperday);
ALTER TABLE cdb_usergroups DROP allowkarma, DROP minkarmarate, DROP maxkarmarate, DROP maxrateperday, CHANGE attachextensions attachextensions CHAR(100) NOT NULL;
UPDATE cdb_usergroups SET raterange='' WHERE raterange LIKE '0\t0\t0\t%';
UPDATE cdb_members SET extcredits1=credits;

ALTER TABLE cdb_sessions DROP INDEX bloguid, ADD INDEX (uid), ADD INDEX (bloguid);
ALTER TABLE cdb_attachments DROP INDEX pid, ADD INDEX pid (pid, aid);
ALTER TABLE cdb_failedlogins ADD PRIMARY KEY (ip);

UPDATE cdb_stats SET variable='0' WHERE type='week' AND variable='';
DELETE FROM cdb_statvars WHERE type IN ('threads', 'forums', 'members', 'posts');
ALTER TABLE cdb_statvars CHANGE type type VARCHAR(20) NOT NULL;

REPLACE INTO cdb_settings VALUES ('transfermincredits', '1000');
REPLACE INTO cdb_settings VALUES ('exchangemincredits', '100');
REPLACE INTO cdb_settings VALUES ('creditsformulaexp', '');
REPLACE INTO cdb_settings VALUES ('passport_status', '');
REPLACE INTO cdb_settings VALUES ('passport_key', '');
REPLACE INTO cdb_settings VALUES ('passport_expire', '3600');
REPLACE INTO cdb_settings VALUES ('passport_extcredits', '0');
REPLACE INTO cdb_settings VALUES ('passport_url', '');
REPLACE INTO cdb_settings VALUES ('passport_register_url', '');
REPLACE INTO cdb_settings VALUES ('passport_login_url', '');
REPLACE INTO cdb_settings VALUES ('passport_logout_url', '');
REPLACE INTO cdb_settings VALUES ('creditstrans', '0');
REPLACE INTO cdb_settings VALUES ('creditstax', '0.2');
REPLACE INTO cdb_settings VALUES ('initcredits', '0,0,0,0,0,0,0,0,0');
REPLACE INTO cdb_settings VALUES ('rssttl', '60');

UPDATE cdb_settings SET variable='postbanperiods', value=REPLACE(value, ',', '\r\n') WHERE variable='maintspans';
REPLACE INTO cdb_settings VALUES ('visitbanperiods', '');
REPLACE INTO cdb_settings VALUES ('searchbanperiods', '');
REPLACE INTO cdb_settings VALUES ('postmodperiods', '');

ALTER TABLE cdb_usergroups ADD maxprice smallint(6) UNSIGNED NOT NULL NOT NULL AFTER reasonpm, ADD allowtransfer tinyint(1) NOT NULL AFTER allowinvisible, ADD disableperiodctrl tinyint(1) NOT NULL AFTER allowviewstats;
UPDATE cdb_usergroups SET allowtransfer=1, disableperiodctrl=1, maxprice=(4-groupid)*10 WHERE groupid IN (1,2,3);

ALTER TABLE cdb_admingroups ADD allowrefund tinyint(1) NOT NULL AFTER allowmassprune;
UPDATE cdb_admingroups SET allowrefund=1 WHERE admingid=1 OR admingid=2;
ALTER TABLE cdb_threads ADD price smallint(6) NOT NULL AFTER readperm;
DELETE FROM cdb_settings WHERE variable IN ('maintspans');
ALTER TABLE cdb_settings ORDER BY variable;
EOT;

if(!$action) {
	echo"本程序用于升级 Discuz! 4.0.0RC3 到 Discuz! 4.0.0RC4,请确认之前已经顺利安装 Discuz! 4.0.0RC3<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 4.0.0RC3 升级到 4.0.0RC4,运行之前,请确认已经上传 4.0.0RC4 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 4.0.0RC4 版的全部文件和目录,覆盖服务器上的 4.0.0RC3<br>2. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		/*
		$query = $db->query("SELECT value FROM {$tablepre}settings WHERE variable='version'");
		if(!in_array(($db->result($query, 0)), array('4.0.0RC3'))) {
			exit('您当前数据库数据版本不是4.0.0RC3，无法升级');
		}
		*/

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		$db->query("ALTER TABLE {$tablepre}pms DROP INDEX folder", 'SILENT');
		$db->query("ALTER TABLE {$tablepre}styles DROP INDEX themename", 'SILENT');
		$db->query("ALTER TABLE {$tablepre}buddys DROP description", 'SILENT');

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 2) {

		runquery($upgrade2);

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 3) {

		runquery($upgrade3);

		$query = $db->query("SELECT fid, description, postcredits, replycredits FROM {$tablepre}forums");
		while($forum = $db->fetch_array($query)) {
			$postcredits = $forum['postcredits'] == -1 ? '' : addslashes(serialize(array(1 => $forum['postcredits'])));
			$replycredits = $forum['replycredits'] == -1 ? '' : addslashes(serialize(array(1 => $forum['replycredits'])));
			$db->query("UPDATE {$tablepre}forumfields SET description='".addslashes($forum['description'])."', postcredits='$postcredits', replycredits='$replycredits' WHERE fid='$forum[fid]'");
		}
		$db->query("ALTER TABLE {$tablepre}forums DROP description, DROP postcredits, DROP replycredits");

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 4) {

		$query = $db->query("SELECT pid, dateline FROM {$tablepre}posts WHERE attachment>0");
		while($post = $db->fetch_array($query)) {
			$db->query("UPDATE {$tablepre}attachments SET dateline='$post[dateline]' WHERE pid='$post[pid]'");
		}

		echo "第 $step 步升级成功<br><br>";
		redirect("?action=upgrade&step=".($step+1));

	} elseif($step == 5) {

		$num = 10000; // parameter
		$start = intval($start);

		$pids = 0;
		$query = $db->query("SELECT pid FROM {$tablepre}threads t, {$tablepre}posts p
			WHERE p.tid=t.tid AND p.dateline=t.dateline AND p.authorid=t.authorid
			LIMIT $start, $num");
		while($post = $db->fetch_array($query)) {
			$pids .= ','.$post['pid'];
		}

		$db->query("UPDATE {$tablepre}posts SET first='1' WHERE pid IN ($pids)");

		if($db->num_rows($query)) {
			echo "正在进行第 $step 步升级 起始列数: $start<br><br>";
			redirect("?action=upgrade&step=$step&start=".($start+$num));
		} else {
			echo "第 $step 步升级成功<br><br>";
			redirect("?action=upgrade&step=".($step+1));
		}

	} elseif($step == 6) {

		$query = $db->query("SELECT uid, buddyid, count(*) AS count FROM {$tablepre}buddys GROUP BY uid, buddyid HAVING count>1");
		while($buddy = $db->fetch_array($query)) {
			$db->query("DELETE FROM {$tablepre}buddys WHERE uid='$buddy[uid]' AND buddyid='$buddy[buddyid]' LIMIT ".($buddy['count'] - 1));
		}

		$credits = array();
		$query = $db->query("SELECT * FROM {$tablepre}settings WHERE variable LIKE '%credits%'");
		while($c = $db->fetch_array($query)) {
			$credits[$c['variable']] = $c['value'];
		}

		$extcredits = Array
		(
		1 => Array
			(
			'title' => '威望',
			'showinthread' => '',
			'available' => 1
			),
		2 => Array
			(
			'title' => '金钱',
			'showinthread' => '',
			'available' => 1
			)
		);
		$creditspolicy = Array
		(
		'post' => Array
			(
			1 => $credits['postcredits']
			),
		'reply' => Array
			(
			1 => $credits['replycredits']
			),
		'digest' => Array
			(
			1 => $credits['digestcredits']
			),
		'postattach' => Array
			(
			),
		'getattach' => Array
			(

			),
		'pm' => Array
			(
			),
		'search' => Array
			(
			)
		);

		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('creditspolicy', '".addslashes(serialize($creditspolicy))."')");

		$db->query("REPLACE INTO {$tablepre}settings (variable, value)
				VALUES ('extcredits', '".addslashes(serialize($extcredits))."')");

		$db->query("DELETE FROM {$tablepre}settings WHERE variable IN ('deletedcredits', 'digestcredits', 'postcredits', 'replycredits')");
		@unlink('./forumdata/cache/cache_settings.php');
		echo "恭喜您升级成功,请务必删除本程序. 进行以下操作后才能最后完成:<br>";

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
	global $db, $tablepre;
	$expquery = explode(";", $query);
	foreach($expquery as $sql) {
		$sql = trim($sql);
		if($sql != "" && $sql[0] != "#") {
			$db->query(str_replace("cdb_", $tablepre, $sql));
		}
	}
}

function redirect($url) {

	echo"<script>";
	echo"function redirect() {window.location.replace('$url');}\n";
	echo"setTimeout('redirect();', 500);\n";
	echo"</script>";
	echo"<br><br><a href=\"$url\">浏览器会自动跳转页面，无需人工干预。<br>除非当您的浏览器没有自动跳转时，请点击这里</a>";

}

?>