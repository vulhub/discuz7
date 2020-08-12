<?php

// Upgrade Discuz! Board from 3.0F beta3 to 4.0.0

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
DROP TABLE IF EXISTS cdb_adminactions;
CREATE TABLE cdb_adminactions (
  admingid smallint(6) unsigned NOT NULL default '0',
  disabledactions text NOT NULL,
  PRIMARY KEY  (admingid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_advertisements;
CREATE TABLE cdb_advertisements (
  advid mediumint(8) unsigned NOT NULL auto_increment,
  available tinyint(1) NOT NULL default '0',
  type varchar(50) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  title varchar(50) NOT NULL default '',
  targets varchar(255) NOT NULL default '',
  parameters text NOT NULL,
  code text NOT NULL,
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (advid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_orders;
CREATE TABLE cdb_orders (
  orderid char(32) NOT NULL default '',
  status char(3) NOT NULL default '',
  buyer char(50) NOT NULL default '',
  admin char(15) NOT NULL default '',
  uid mediumint(8) unsigned NOT NULL default '0',
  amount smallint(6) unsigned NOT NULL default '0',
  price float(7,2) unsigned NOT NULL default '0.00',
  submitdate int(10) unsigned NOT NULL default '0',
  confirmdate int(10) unsigned NOT NULL default '0',
  UNIQUE KEY orderid (orderid),
  KEY submitdate (submitdate),
  KEY uid (uid,submitdate)
) TYPE=MyISAM;


DROP TABLE IF EXISTS cdb_rsscaches;
CREATE TABLE cdb_rsscaches (
  lastupdate int(10) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  forum char(50) NOT NULL default '',
  author char(15) NOT NULL default '',
  subject char(80) NOT NULL default '',
  description char(255) NOT NULL default '',
  KEY fid (fid,dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_sessions;
CREATE TABLE cdb_sessions (
  sid char(6) binary NOT NULL default '',
  ip1 tinyint(3) unsigned NOT NULL default '0',
  ip2 tinyint(3) unsigned NOT NULL default '0',
  ip3 tinyint(3) unsigned NOT NULL default '0',
  ip4 tinyint(3) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  username char(15) NOT NULL default '',
  groupid smallint(6) unsigned NOT NULL default '0',
  styleid smallint(6) unsigned NOT NULL default '0',
  invisible tinyint(1) NOT NULL default '0',
  action tinyint(1) unsigned NOT NULL default '0',
  lastactivity int(10) unsigned NOT NULL default '0',
  lastolupdate int(10) unsigned NOT NULL default '0',
  pageviews smallint(6) unsigned NOT NULL default '0',
  seccode smallint(6) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  bloguid mediumint(8) unsigned NOT NULL default '0',
  UNIQUE KEY sid (sid),
  KEY uid (uid),
  KEY bloguid (bloguid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_validating;
CREATE TABLE cdb_validating (
  uid mediumint(8) unsigned NOT NULL default '0',
  submitdate int(10) unsigned NOT NULL default '0',
  moddate int(10) unsigned NOT NULL default '0',
  admin varchar(15) NOT NULL default '',
  submittimes tinyint(3) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  message text NOT NULL,
  remark text NOT NULL,
  PRIMARY KEY  (uid),
  KEY status (status)
) TYPE=MyISAM;
EOT;

$upgrade2 = <<<EOT
ALTER TABLE cdb_admingroups  DROP allowhighlight,  DROP allowdigestthread,  DROP allowclose,  DROP allowmove,  DROP allowmerge,  DROP allowsplit,  DROP allowadmincp,  DROP acpactions,  DROP allowrepair;

ALTER TABLE cdb_admingroups ADD allowmodpost TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowstickthread , ADD allowmassprune TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowdelpost ,ADD allowrefund TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowmassprune ,ADD allowcensorword TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowrefund , ADD allowbanip TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowviewip ,ADD allowedituser TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowbanip ,
ADD allowmoduser TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowedituser , ADD allowbanuser TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowmoduser , ADD allowpostannounce TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowbanuser , ADD allowviewlog TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowpostannounce ;

ALTER TABLE cdb_announcements DROP extlink ;

ALTER TABLE cdb_attachments DROP permid ,
DROP displayorder ;
ALTER TABLE cdb_attachments CHANGE readperm readperm TINYINT( 3 ) UNSIGNED NOT NULL 
DEFAULT '0' ;
UPDATE cdb_attachments SET readperm=0;
ALTER TABLE cdb_medals DROP description ;
ALTER TABLE cdb_buddys ADD dateline INT( 10 ) UNSIGNED DEFAULT '0' NOT NULL ,
ADD description CHAR( 255 ) NOT NULL DEFAULT '';
ALTER TABLE cdb_forumfields DROP threadperms ;
ALTER TABLE cdb_forums ADD alloweditrules TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowtrade ;
ALTER TABLE cdb_members ADD extgroupids CHAR( 60 ) DEFAULT '' NOT NULL AFTER 
groupexpiry ;
ALTER TABLE cdb_members ADD pageviews MEDIUMINT( 8 ) unsigned DEFAULT '0' NOT NULL 
AFTER oltime ;
ALTER TABLE cdb_members ADD pmsound TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
timeformat ;
ALTER TABLE cdb_members DROP disablesigviews ;
DROP TABLE IF EXISTS cdb_modworks;
ALTER TABLE cdb_onlinetime CHANGE lastolupdate lastupdate INT( 10 ) UNSIGNED NOT NULL 
DEFAULT '0' ;
ALTER TABLE cdb_paymentlog DROP pid ,
DROP aid ,
DROP permid ;
ALTER TABLE cdb_ranks DROP INDEX postshigher ;
ALTER TABLE cdb_ratelog DROP authorid ;
ALTER TABLE cdb_smilies ADD displayorder TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER id ;
ALTER TABLE cdb_templates DROP charset ;
ALTER TABLE cdb_threads DROP permid ;
ALTER TABLE cdb_threads CHANGE readperm readperm TINYINT( 3 ) UNSIGNED NOT NULL 
DEFAULT '0';
ALTER TABLE cdb_threads ADD price SMALLINT( 6 ) DEFAULT '0' NOT NULL AFTER readperm ;
UPDATE cdb_threads SET readperm=0;
ALTER TABLE cdb_usergroups CHANGE raterange raterange CHAR( 150 ) NOT NULL ;
ALTER TABLE cdb_usergroups ADD allowmultigroups TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowvote ;
ALTER TABLE cdb_usergroups CHANGE allowsetpostperm allowsetreadperm TINYINT( 1 ) NOT 
NULL DEFAULT '0' ;
ALTER TABLE cdb_usergroups DROP allowsetpostprice ,
DROP allowsetattachprice ;
ALTER TABLE cdb_usergroups ADD allowcusbbcode TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER 
allowhtml ,
ADD allownickname TINYINT( 1 ) DEFAULT '0' NOT NULL AFTER allowcusbbcode ;

ALTER TABLE cdb_usergroups ADD maxprice SMALLINT( 6 ) UNSIGNED DEFAULT '0' NOT NULL 
AFTER reasonpm ;
ALTER TABLE cdb_usergroups ADD system char(8) NOT NULL default 'private' AFTER type ;
ALTER TABLE cdb_memberfields DROP origgroup ;
ALTER TABLE cdb_memberfields ADD nickname VARCHAR( 30 ) NOT NULL  default '' AFTER 
uid ;
ALTER TABLE cdb_memberfields ADD taobao VARCHAR( 40 ) NOT NULL default '' AFTER msn ;
ALTER TABLE cdb_memberfields ADD groupterms TEXT NOT NULL AFTER ignorepm ;

EOT;

$upgrade3 = <<<EOT
DELETE FROM cdb_settings Where variable in ('admincpbanperiods','attachnumperpost', 
'avatarshow_lastreg', 'avatarshow_license', 'ecommercestatus', 'cachelifespan', 
'creditstitle', 'creditsunit', 'exchangebalance', 'exchangemax', 'exchangemin', 
'exchangestatus', 'exchangetax', 'seccodestatus', 'forcesecques', 'karmaratelimituser', 
'maxthreadspan', 'maxincperattach', 'maxattachspan', 'memberonlinestatus', 'modworkslist', 
'modworkstatus', 'regfloodctrl', 'regmodperiods', 'regusersperday', 'stylejumpstatus', 
'fastloginform', 'attachpricetax', 'custombackup', 'threadperms', 'threadpricetax', 
'transferstatus', 'transfertax', 'transferbalance', 
'transfermax','regbanperiods','transfermin');

REPLACE INTO cdb_settings VALUES ('adminipaccess', '');
REPLACE INTO cdb_settings VALUES ('bannedmessages', '1');
REPLACE INTO cdb_settings VALUES ('censoremail', '');
REPLACE INTO cdb_settings VALUES ('creditstax', '0.2');
REPLACE INTO cdb_settings VALUES ('ec_account', '');
REPLACE INTO cdb_settings VALUES ('ec_maxcredits', '1000');
REPLACE INTO cdb_settings VALUES ('ec_maxcreditspermonth', '0');
REPLACE INTO cdb_settings VALUES ('ec_mincredits', '0');
REPLACE INTO cdb_settings VALUES ('ec_ratio', '0');
REPLACE INTO cdb_settings VALUES ('ec_securitycode', '');
REPLACE INTO cdb_settings VALUES ('wapcharset', '2');
REPLACE INTO cdb_settings VALUES ('wapdateformat', 'n/j');
REPLACE INTO cdb_settings VALUES ('wapmps', '500');
REPLACE INTO cdb_settings VALUES ('wapppp', '5');
REPLACE INTO cdb_settings VALUES ('wapstatus', '1');
REPLACE INTO cdb_settings VALUES ('waptpp', '10');
REPLACE INTO cdb_settings VALUES ('exchangemincredits', '100');
REPLACE INTO cdb_settings VALUES ('fullmytopics', '1');
REPLACE INTO cdb_settings VALUES ('ipaccess', '');
REPLACE INTO cdb_settings VALUES ('ipregctrl', '');
REPLACE INTO cdb_settings VALUES ('jscachelife', '1800');
REPLACE INTO cdb_settings VALUES ('jsrefdomains', '');
REPLACE INTO cdb_settings VALUES ('jsstatus', '0');
REPLACE INTO cdb_settings VALUES ('maxsigrows', '20');
REPLACE INTO cdb_settings VALUES ('maxsmilies', '3');
REPLACE INTO cdb_settings VALUES ('maxthreadads', '0');
REPLACE INTO cdb_settings VALUES ('visitedforums', '10');
REPLACE INTO cdb_settings VALUES ('searchbanperiods', '');
REPLACE INTO cdb_settings VALUES ('searchbox', '0');
REPLACE INTO cdb_settings VALUES ('stylejump', '0');
REPLACE INTO cdb_settings VALUES ('postbanperiods', '');
REPLACE INTO cdb_settings VALUES ('postmodperiods', '');
REPLACE INTO cdb_settings VALUES ('pvfrequence', '60');
REPLACE INTO cdb_settings VALUES ('qihoostatus', '0');
REPLACE INTO cdb_settings VALUES ('transfermincredits', '1000');
REPLACE INTO cdb_settings VALUES ('seccodestatus', '0');
EOT;


if(!$action) {
	echo"本程序用于升级 Discuz! 3.0F beta3 到 Discuz! 4.0.0,请确认之前已经顺利安装 Discuz! 3.0F beta3<br><br><br>";
	echo"<b><font color=\"red\">本升级程序只能从 3.0F beta3 升级到 4.0.0,运行之前,请确认已经上传 3.0F beta3 的全部文件和目录</font></b><br>";
	echo"<b><font color=\"red\">升级前请打开浏览器 JavaScript 支持,整个过程是自动完成的,不需人工点击和干预.<br>升级之前务必备份数据库资料,否则可能产生无法恢复的后果!<br></font></b><br><br>";
	echo"正确的升级方法为:<br>1. 关闭原有论坛,上传 Discuz! 4.0.0 版的全部文件和目录,覆盖服务器上的 3.0F beta3<br>2. 上传本程序到 Discuz! 目录中;<br>4. 运行本程序,直到出现升级完成的提示;<br><br>";
	echo"<a href=\"$PHP_SELF?action=upgrade&step=1\">如果您已确认完成上面的步骤,请点这里升级</a>";
} else {

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	unset($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	if($step == 1) {

		dir_clear('./forumdata/cache');
		dir_clear('./forumdata/templates');

		runquery($upgrade1);

		echo "第 1 步升级成功<br><br>";
		redirect("?action=upgrade&step=2");

	} elseif($step == 2 ) {
		runquery($upgrade2);
		echo "第 2 步升级成功<br><br>";
		redirect("?action=upgrade&step=3");
		
	} elseif($step == 3) {

		runquery($upgrade3);
		echo "第 3 步升级成功<br><br>";
		redirect("?action=upgrade&step=4");

	} elseif($step == 4) {

		@unlink('./forumdata/cache/cache_settings.php');
		echo "恭喜您升级成功,请务必删除本程序. <br>完成全部升级请务必进行以下操作：<li>以管理员身份登录论坛，重新设置管理组权限以及各种特殊会员组<li>更新论坛基本设置。<li>更新论坛缓存";
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

function checkbbcodes($message, $bbcodeoff) {
	return !$bbcodeoff && !preg_match("/\[.+\].*\[\/.+\]/s", $message) ? -1 : $bbcodeoff;
}

function checksmilies($message, $smileyoff) {
	global $smilies;
	return !$smileyoff && !preg_match('/'.implode('|', $smilies).'/', $message) ? -1 : $smileyoff;
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

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	mt_srand((double)microtime() * 1000000);
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

?>
