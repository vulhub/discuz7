<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);
@set_time_limit(0);

define('DISCUZ_ROOT', getcwd().'/');
define('IN_DISCUZ', TRUE);
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);

if(!function_exists('file_put_contents')) {
	define('FILE_APPEND', 'FILE_APPEND');
	if(!defined('LOCK_EX')) {
		define('LOCK_EX', 'LOCK_EX');
	}

	function file_put_contents($file, $data, $flags = '') {
		$contents = (is_array($data)) ? implode('', $data) : $data;

		$mode = ($flags == 'FILE_APPEND') ? 'ab+' : 'wb';

		if(($fp = @fopen($file, $mode)) === false) {
			return false;
		} else {
			$bytes = fwrite($fp, $contents);
			fclose($fp);
			return $bytes;
		}
	}
}

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

$version['old'] = 'Discuz! 6.1.0 正式版';
$version['new'] = 'Discuz! 7.0.0 正式版';
$lock_file = DISCUZ_ROOT.'./forumdata/upgrade11.lock';

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

DROP TABLE IF EXISTS cdb_tasks;
CREATE TABLE cdb_tasks (
  taskid smallint(6) unsigned NOT NULL auto_increment,
  relatedtaskid smallint(6) unsigned NOT NULL default '0',
  available tinyint(1) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  description text NOT NULL,
  icon varchar(150) NOT NULL default '',
  applicants mediumint(8) unsigned NOT NULL default '0',
  achievers mediumint(8) unsigned NOT NULL default '0',
  tasklimits mediumint(8) unsigned NOT NULL default '0',
  applyperm text NOT NULL,
  scriptname varchar(50) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  period int(10) unsigned NOT NULL default '0',
  reward enum('credit','magic','medal','invite','group') NOT NULL default 'credit',
  prize varchar(15) NOT NULL default '',
  bonus int(10) NOT NULL default '0',
  displayorder smallint(6) unsigned NOT NULL default '0',
  version varchar(15) NOT NULL default '',
  PRIMARY KEY  (taskid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_taskvars;
CREATE TABLE cdb_taskvars (
  taskvarid mediumint(8) unsigned NOT NULL auto_increment,
  taskid smallint(6) unsigned NOT NULL default '0',
  sort enum('apply','complete','setting') NOT NULL DEFAULT 'complete',
  `name` varchar(100) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  variable varchar(40) NOT NULL default '',
  `type` varchar(20) NOT NULL default 'text',
  `value` text NOT NULL,
  extra text NOT NULL,
  PRIMARY KEY  (taskvarid),
  KEY taskid (taskid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_mytasks;
CREATE TABLE cdb_mytasks (
  uid mediumint(8) unsigned NOT NULL,
  username char(15) NOT NULL default '',
  taskid smallint(6) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  csc char(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (uid,taskid),
  KEY parter (taskid, dateline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS cdb_navs;
CREATE TABLE cdb_navs (
  id smallint(6) unsigned NOT NULL auto_increment,
  parentid smallint(6) unsigned NOT NULL default '0',
  name char(50) NOT NULL,
  title char(255) NOT NULL,
  url char(255) NOT NULL,
  target tinyint(1) NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  available tinyint(1) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL,
  highlight tinyint(1) NOT NULL DEFAULT '0',
  level tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

EOT;

$upgradetable = array(

	array('forums', 'ADD', 'allowfeed', "TINYINT(1) NOT NULL DEFAULT '1' AFTER alloweditrules"),
	array('forums', 'CHANGE', 'displayorder', "displayorder SMALLINT(6) NOT NULL DEFAULT '0'"),

	array('threads', 'DROP', 'blog', ""),
	array('threads', 'INDEX', '', "DROP INDEX blog"),
	array('threads', 'ADD', 'sortid', "SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0' AFTER typeid"),
	array('threads', 'INDEX', '', "ADD INDEX (sortid)"),

	array('forumfields', 'ADD', 'threadsorts', "TEXT NOT NULL AFTER threadtypes"),

	array('threadtypes', 'CHANGE', 'displayorder', "displayorder smallint(6) NOT NULL DEFAULT '0'"),

	array('typeoptionvars', 'CHANGE', 'typeid', "sortid SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'"),
	array('typeoptionvars', 'INDEX', '', "ADD INDEX sortid (sortid)"),
	array('typeoptionvars', 'INDEX', '', "DROP INDEX typeid"),

	array('typevars', 'CHANGE', 'typeid', "sortid SMALLINT(6) NOT NULL DEFAULT '0'"),
	array('typevars', 'INDEX', '', "DROP INDEX typeid"),
	array('typevars', 'INDEX', '', "ADD INDEX sortid (sortid)"),
	array('typevars', 'INDEX', '', "DROP INDEX optionid"),
	array('typevars', 'INDEX', '', "ADD UNIQUE optionid (sortid,optionid)"),
	array('attachments', 'INDEX', '', "ADD INDEX dateline (dateline, isimage, downloads)"),
	array('warnings', 'INDEX', '', "ADD INDEX authorid (authorid)"),

	array('searchindex', 'CHANGE', 'threadtypeid', "threadsortid SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'"),

	array('tradeoptionvars', 'CHANGE', 'typeid', "sortid SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'"),
	array('tradeoptionvars', 'INDEX', '', "ADD INDEX sortid (sortid)"),
	array('tradeoptionvars', 'INDEX', '', "DROP INDEX typeid"),

	array('polls', 'ADD', 'overt', "tinyint(1) NOT NULL DEFAULT '0'"),

	array('forumlinks', 'CHANGE', 'logo', "logo VARCHAR(255) NOT NULL"),
	array('forumlinks', 'CHANGE', 'url', "url VARCHAR(255) NOT NULL"),

	array('admincustom', 'CHANGE', 'title', "title VARCHAR(255) NOT NULL"),

	array('advertisements', 'CHANGE', 'title', "title VARCHAR(255) NOT NULL"),

	array('announcements', 'CHANGE', 'subject', "subject VARCHAR(255) NOT NULL"),

	array('itempool', 'CHANGE', 'answer', "answer VARCHAR(255) NOT NULL"),

	array('medals', 'CHANGE', 'image', "image VARCHAR(255) NOT NULL"),

	array('members', 'CHANGE', 'newpm', "prompt tinyint(1) NOT NULL default '0'"),
	array('memberfields', 'CHANGE', 'msn', "msn VARCHAR(100) NOT NULL default ''"),

	array('attachments', 'ADD', 'width', "SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0'"),

	array('bbcodes', 'ADD', 'type', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER available"),
	array('bbcodes', 'ADD', 'displayorder', "TINYINT(3) NOT NULL DEFAULT '0'"),

	array('forums', 'ADD', 'allowglobalstick', "TINYINT(1) NOT NULL default '1'"),
	array('usergroups', 'ADD', 'allowsendpm', "TINYINT(1) NOT NULL DEFAULT '1'"),
	array('imagetypes', 'ADD', 'available', "TINYINT(1) NOT NULL DEFAULT '0' AFTER typeid"),
);

$upgrade3 = <<<EOT
DROP TABLE IF EXISTS cdb_buddys;
DROP TABLE IF EXISTS cdb_pms;
DROP TABLE IF EXISTS cdb_pmsearchindex;
DELETE FROM cdb_settings WHERE variable IN ('jsmenustatus', 'pluginjsmenu', 'regadvance', 'hottags', 'maxbiotradesize', 'tradeimagewidth', 'tradeimageheight', 'bbinsert', 'smileyinsert');
REPLACE INTO cdb_settings (variable, value) VALUES ('sigviewcond', 0);
REPLACE INTO cdb_settings (variable, value) VALUES ('swfupload', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('sitemessage', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('magicdiscount', '85');
REPLACE INTO cdb_settings (variable, value) VALUES ('ucactivation', '1');
REPLACE INTO cdb_stats (type, variable, `count`) VALUES ('browser', 'Firefox', 0);
REPLACE INTO cdb_stats (type, variable, `count`) VALUES ('browser', 'Safari', 0);
REPLACE INTO cdb_navs VALUES ('1', '0', '论坛', '', '#', '0', '0', '1', '1', '0', '0');
REPLACE INTO cdb_navs VALUES ('2', '0', '搜索', '', 'search.php', '0', '0', '1', '2', '0', '0');
REPLACE INTO cdb_navs VALUES ('3', '0', '插件', '', '#', '0', '0', '1', '4', '0', '0');
REPLACE INTO cdb_navs VALUES ('4', '0', '帮助', '', 'faq.php', '0', '0', '1', '5', '0', '0');
REPLACE INTO cdb_navs VALUES ('5', '0', '导航', '', '#', '0', '0', '1', '6', '0', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('allowfloatwin', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('creditnotice', 1);
REPLACE INTO cdb_settings (variable, value) VALUES ('pwdsafety', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('dateconvert', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('authoronleft', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('statcode', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('google', '1');
REPLACE INTO cdb_settings (variable, value) VALUES ('avatarmethod', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('newbietask', '');
REPLACE INTO cdb_settings (variable, value) VALUES ('taskon', '0');
REPLACE INTO cdb_settings (variable, value) VALUES ('msn', '');
UPDATE cdb_imagetypes SET available='1';
DELETE FROM cdb_admincustom WHERE sort='0';
EOT;

$upgradesql_bbcodes = <<<EOT
INSERT INTO cdb_bbcodes VALUES ('1', '2','1','b i u', 'popup_simple', '', '', '粗体 斜体 下划线', '1', '', '1', '0');
INSERT INTO cdb_bbcodes VALUES ('2', '1','1','font', 'popup_fontname', '', '', '字体', '1', '', '1', '1');
INSERT INTO cdb_bbcodes VALUES ('3', '2','1','size', 'popup_fontsize', '', '', '大小', '1', '', '1', '2');
INSERT INTO cdb_bbcodes VALUES ('4', '2','1','color', 'popup_forecolor', '', '', '颜色', '1', '', '1', '3');
INSERT INTO cdb_bbcodes VALUES ('5', '2','1','align', 'popup_justify', '', '', '对齐', '1', '', '1', '4');
INSERT INTO cdb_bbcodes VALUES ('6', '2','1','url', 'cmd_createlink', '', '', '链接', '1', '', '1', '5');
INSERT INTO cdb_bbcodes VALUES ('7', '1','1','email', 'cmd_email', '', '', 'Email', '1', '', '1', '6');
INSERT INTO cdb_bbcodes VALUES ('8', '2','1','img', 'cmd_insertimage', '', '', '图片', '1', '', '1', '7');
INSERT INTO cdb_bbcodes VALUES ('9', '2','1','media', 'popup_media', '', '', '多媒体', '1', '', '1', '8');
INSERT INTO cdb_bbcodes VALUES ('10', '2','1','quote', 'cmd_quote', '', '', '引用', '1', '', '1', '9');
INSERT INTO cdb_bbcodes VALUES ('11', '2','1','code', 'cmd_code', '', '', '代码', '1', '', '1', '10');
INSERT INTO cdb_bbcodes VALUES ('12', '2','1','list', 'popup_list', '', '', '列表', '1', '', '1', '11');
INSERT INTO cdb_bbcodes VALUES ('13', '2','1','indent outdent', 'popup_dent', '', '', '缩进', '1', '', '1', '12');
INSERT INTO cdb_bbcodes VALUES ('14', '1','1','float', 'popup_float', '', '', '浮动', '1', '', '1', '13');
INSERT INTO cdb_bbcodes VALUES ('15', '2','1','table', 'cmd_table', '', '', '表格', '1', '', '1', '14');
INSERT INTO cdb_bbcodes VALUES ('16', '1','1','free', 'cmd_free', '', '', '免费信息', '1', '', '1', '15');
INSERT INTO cdb_bbcodes VALUES ('17', '2','1','hide', 'cmd_hide', '', '', '隐藏内容', '1', '', '1', '16');
INSERT INTO cdb_bbcodes VALUES ('18', '2','1','smilies', 'popup_smilies', '', '', '表情', '1', '', '1', '17');
INSERT INTO cdb_bbcodes VALUES ('19', '2','1','tools', 'popup_tools', '', '', '工具', '1', '', '1', '99');
EOT;

$upgradesql_smiles = <<<EOT
INSERT INTO cdb_imagetypes VALUES ('{typeid,1}', '1','酷猴','smiley','2','coolmonkey');
INSERT INTO cdb_imagetypes VALUES ('{typeid,2}', '1','呆呆男','smiley','3','grapeman');

INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','1','smiley','[m:01]','01.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','2','smiley','[m:02]','02.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','3','smiley','[m:03]','03.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','4','smiley','[m:04]','04.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','5','smiley','[m:05]','05.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','6','smiley','[m:06]','06.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','7','smiley','[m:07]','07.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','8','smiley','[m:08]','08.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','9','smiley','[m:09]','09.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','10','smiley','[m:10]','10.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','11','smiley','[m:11]','11.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','12','smiley','[m:12]','12.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','13','smiley','[m:13]','13.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','14','smiley','[m:14]','14.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','15','smiley','[m:15]','15.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,1}','16','smiley','[m:16]','16.gif');

INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','1','smiley','[g:01]','01.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','2','smiley','[g:02]','02.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','3','smiley','[g:03]','03.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','4','smiley','[g:04]','04.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','5','smiley','[g:05]','05.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','6','smiley','[g:06]','06.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','7','smiley','[g:07]','07.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','8','smiley','[g:08]','08.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','9','smiley','[g:09]','09.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','10','smiley','[g:10]','10.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','11','smiley','[g:11]','11.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','12','smiley','[g:12]','12.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','13','smiley','[g:13]','13.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','14','smiley','[g:14]','14.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','15','smiley','[g:15]','15.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','16','smiley','[g:16]','16.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','17','smiley','[g:17]','17.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','18','smiley','[g:18]','18.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','19','smiley','[g:19]','19.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','20','smiley','[g:20]','20.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','21','smiley','[g:21]','21.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','22','smiley','[g:22]','22.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','23','smiley','[g:23]','23.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('{typeid,2}','24','smiley','[g:24]','24.gif');

INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('1','0','smiley',':curse:','curse.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('1','0','smiley',':dizzy:','dizzy.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('1','0','smiley',':shutup:','shutup.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('1','0','smiley',':sleepy:','sleepy.gif');
EOT;

$upgradesql_icons = <<<EOT
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','10','icon','','icon10.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','11','icon','','icon11.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','12','icon','','icon12.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','13','icon','','icon13.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','14','icon','','icon14.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','15','icon','','icon15.gif');
INSERT INTO cdb_smilies (typeid, displayorder, type, code, url) VALUES ('0','16','icon','','icon16.gif');
EOT;

$step = getgpc('step');
$step = empty($step) ? 1 : $step;
instheader();
if(!isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
}

if(file_exists($lock_file)) {
	instmsg('升级被锁定，应该是已经升级过了，如果已经恢复数据请手动删除<br />'.str_replace(DISCUZ_ROOT, '', $lock_file).'<br />之后再来刷新页面');
}

if($step == 1) {

	$msg = '<a href="'.$PHP_SELF.'?step=check"><font size="2"><b>&gt;&gt;&nbsp;如果您已确认完成上面的步骤,请点这里升级</b></font></a>';
	

echo <<<EOT
<h4>本升级程序只能从 $version[old] 升级到 $version[new]<br /></h4>
升级之前<b>务必备份数据库资料</b>，否则升级失败无法恢复<br /><br />
正确的升级方法为:
<ol>
	<li>关闭原有论坛，上传 $version[new] 的全部文件和目录，覆盖服务器上的 $version[old]
	<li>上传升级程序到论坛目录中，重新配置好 config.inc.php
	<li>运行本程序，直到出现升级完成的提示
	<li>如果中途失败，请使用Discuz!工具箱（./utilities/tools.php）里面的数据恢复工具恢复备份，去除错误后重新运行本程序
</ol>
$msg

EOT;

	instfooter();

} elseif($step == 'check') {

	@touch(DISCUZ_ROOT.'./forumdata/install.lock');
	@unlink(DISCUZ_ROOT.'./install/index.php');

	echo "<h4>Ucenter版本检测</h4>";

	if(!defined('UC_CONNECT')) {
		instmsg('您的config.inc.php文件被覆盖，请恢复备份好的config.inc.php文件，之后再尝试升级');
	}
	
	include_once DISCUZ_ROOT.'./discuz_version.php';
	if(!defined('DISCUZ_VERSION') || DISCUZ_VERSION != '7.0.0') {
		instmsg('您还没有上传(或者上传不完全)最新的Discuz!7.0.0的程序文件，请先上传之后再尝试升级');
	}
	
	include_once DISCUZ_ROOT.'./uc_client/client.php';
	if(!defined('UC_CLIENT_VERSION') || UC_CLIENT_VERSION != '1.5.0') {
		instmsg('请将Discuz!7.0.0程序包中的 ./upload/uc_client 上传至论坛根目录，之后再尝试升级');
	}

	if(!function_exists('uc_check_version')) {
		instmsg('您论坛下的uc_client不是最新的版本，请下载最新版本上传上去，之后再尝试升级');
	}

	$uc_root = get_uc_root();

	$return = uc_check_version();
	if(empty($return)) {
		$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=2';
		$uc_upgrade_url = UC_API."/upgrade/upgrade2.php?action=db&forward=".urlencode($upgrade_url);
		instmsg('无法检测到您的uc目前的版本，请将最新的 UCenter程序上传至<br /> '.($uc_root ? $uc_root : UC_API).'<br /> 目录下之后<a href="'.$uc_upgrade_url.'">点击这里进行升级</a>');
	} elseif(is_string($return)) {
		instmsg('在确保 UCenter 和 Discuz! 通信成功的前提下，请将最新版的 UCenter 程序文件上传至 <br />  '.($uc_root ? $uc_root : UC_API).'<br />  目录下之后在回到这里刷新页面');
	} elseif(is_array($return)) {
		if($return['db'] == '1.5.0') {
			instmsg('UCenter已经升级。', 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=2');
		}
	}

	$upgrade_url = 'http://'.$_SERVER['HTTP_HOST'].$PHP_SELF.'?step=check';
	instmsg('开始升级Ucenter。', UC_API."/upgrade/upgrade2.php?action=db&forward=".urlencode($upgrade_url));

} elseif($step == 2) {

	echo "<h4>新增数据表</h4>";

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	runquery($upgrade1);
	instmsg("新增数据表处理完毕。", '?step=3');
	instfooter();

} elseif($step == 3) {

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
		instmsg("请等待 ...", "?step=3&start=$start");
	}
	instmsg("论坛数据表结构调整完毕。", "?step=4");
	instfooter();

} elseif($step == 4) {

	echo "<h4>更新部分数据</h4>";
	runquery($upgrade3);
	upg_bbcodes();
	upg_smiles();
	upg_icons();
	upg_js();
	$typeids = $comma = '';
	$query = $db->query("SELECT typeid FROM {$tablepre}imagetypes WHERE type='smiley' AND (directory='coolmonkey' OR directory='grapeman')");
	while($type = $db->fetch_array($query)) {
		$typeids .= $comma.$type['typeid'];
		$comma = ',';
	}
	if($typeids) {
		$db->query("UPDATE {$tablepre}smilies SET code=CONCAT('{:', typeid, '_', id, ':}') WHERE typeid IN ($typeids)");
	}
	$tasktypes = array(
	  'promotion' => 
	  array (
	    'name' => '论坛推广任务',
	    'version' => '1.0',
	  ),
	  'gift' => 
	  array (
	    'name' => '红包类任务',
	    'version' => '1.0',
	  ),
	  'avatar' => 
	  array (
	    'name' => '头像类任务',
	    'version' => '1.0',
	  )
	);
	$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('tasktypes', '".addslashes(serialize($tasktypes))."')");
	instmsg("部分数据更新完毕。", "?step=5");
	instfooter();

} elseif($step == 5) {

	echo "<h4>处理图片附件</h4>";
	$total = getgpc('total');
	$start = intval(getgpc('start'));
	$limit = 100;
	if(!$total) {
		$total = $db->result_first("SELECT count(*) FROM {$tablepre}attachments WHERE isimage=1");
	}

	if(!$total || $total <= $start) {
		instmsg("图片附件处理完毕。", "?step=6");
	}

	$query = $db->query("SELECT attachment,aid FROM {$tablepre}attachments WHERE isimage=1 LIMIT $start, $limit");
	$attachdir = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='attachdir'");
	while($attachments = $db->fetch_array($query)) {
		$target		= $attachdir.'/'.$attachments['attachment'];
		$attachinfo	= @getimagesize($target);
		$img_w		= $attachinfo[0];
		if($img_w) {
			$db->query("UPDATE {$tablepre}attachments SET width='$img_w' WHERE aid='$attachments[aid]'", 'UNBUFFERED');
		}
	}
	$end = $start + $limit;
	instmsg("图片附件已处理 $start / $total ...", "?step=5&start=$end&total=$total");

} elseif($step == 6) {

	echo "<h4>处理安全提问</h4>";
	$total = getgpc('total');
	$start = intval(getgpc('start'));
	$limit = 1000;
	if(!$total) {
		$total = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members");
		if(is_dir(DISCUZ_ROOT.'./forumdata/upgsecques/')) {
			dir_clear(DISCUZ_ROOT.'./forumdata/upgsecques');
		}
	}

	if(!$total || $total <= $start) {
		instmsg("安全提问处理完毕。", "?step=7");
	}

	$urladd = '';
	$query = $db->query("SELECT uid, secques FROM {$tablepre}members WHERE secques<>'' ORDER BY uid LIMIT $start, $limit");
	if(defined('UC_CONNECT') && UC_CONNECT == 'mysql') {
		$uc_db = new dbstuff();
		$uc_db->connect(UC_DBHOST, UC_DBUSER, UC_DBPW, UC_DBNAME, UC_DBCHARSET);
		while($member = $db->fetch_array($query)) {
			$uid = $member['uid'];
			$secques = $member['secques'];
			$uc_db->query("UPDATE ".UC_DBTABLEPRE."members SET secques='$secques' WHERE uid='$uid'", 'UNBUFFERED');
		}
	} else {
		$random = getgpc('random');
		if(empty($random)) {
			$random = random(8);
		}
		$num = getgpc('num');
		$num = $num ? intval($num) : 1;
		$sqldump = '';
		while($member = $db->fetch_array($query)) {
			$uid = $member['uid'];
			$secques = $member['secques'];
			$sql = "UPDATE uc_members SET secques='$secques' WHERE uid='$uid';\n";
			$sqldump .= $sql;
		}
		if($sqldump) {
			if(!is_dir(DISCUZ_ROOT.'./forumdata/upgsecques/')) {
				if(mkdir(DISCUZ_ROOT.'./forumdata/upgsecques/')) {
					file_put_contents(DISCUZ_ROOT.'./forumdata/upgsecques/index.htm', time());
				} else {
					instmsg('请登陆服务器将 '.DISCUZ_ROOT.'./forumdata/ 目录属性设置为可写(777)');
				}
			}
			file_put_contents(DISCUZ_ROOT.'./forumdata/upgsecques/secques_'.$random.'_'.$num.'.sql', $sqldump, FILE_APPEND);
		}
		$num++;
		$urladd = '&random='.$random.'&num='.$num;
	}
	$end = $start + $limit;
	instmsg("安全提问已处理 $start / $total ...", "?step=6&start=$end&total=$total".$urladd);

} elseif($step == 7) {

	echo "<h4>处理分类信息数据转换</h4>";
	$total = getgpc('total');
	$start = intval(getgpc('start'));
	$limit = 1000;

	if(!$total) {
		$total = $db->result_first("SELECT COUNT(DISTINCT(tid)) FROM {$tablepre}typeoptionvars");
	}

	if(!$total || $total <= $start) {
		instmsg("分类信息处理完毕。", "?step=8");
	}

	$query = $db->query("SELECT DISTINCT(tid), sortid FROM {$tablepre}typeoptionvars LIMIT $start, $limit");
	while($sort = $db->fetch_array($query)) {
		$tid = $sort['tid'];
		$sortid = $sort['sortid'];
		$db->query("UPDATE {$tablepre}threads SET sortid='$sortid', typeid='0' WHERE tid='$tid'", 'UNBUFFERED');
	}

	$end = $start + $limit;
	instmsg("分类信息已处理 $start / $total ...", "?step=7&start=$end&total=$total");

} elseif($step == 8) {

	echo "<h4>处理分类信息版块数据转换</h4>";
	$threadtypes = $threadsorts = array();
	$query = $db->query("SELECT fid, threadtypes FROM {$tablepre}forumfields");
	while($thread = $db->fetch_array($query)) {
		if($thread['threadtypes']) {
			$threadtypes[$thread['fid']] = unserialize($thread['threadtypes']);
		}
	}

	if($threadtypes) {
		foreach($threadtypes as $fid => $thread) {
			if($thread['types']) {
				foreach($thread['types'] as $typeid => $name) {
					if($sortname = $db->result_first("SELECT name FROM {$tablepre}threadtypes WHERE typeid='$typeid' AND special='1'")) {
						$threadsorts[$fid]['types'][$typeid] = $threadsorts[$fid]['flat'][$typeid] = $name;
					}
				}
			}
			$threadsorts[$fid]['listable'] = $threadsorts[$fid]['prefix'] = 1;
			$threadsorts[$fid]['required'] = $threadsorts[$fid]['selectbox'] = '';
		}
	}

	if($threadsorts) {
		foreach($threadsorts as $fid => $data) {
			$db->query("UPDATE {$tablepre}forumfields SET threadsorts='".addslashes(serialize($data))."' WHERE fid='$fid'");
		}
	}

	instmsg("分类信息处理完毕。", "?step=9");
	instfooter();

} elseif($step == 9) {

	echo "<h4>处理论坛风格</h4>";
	$db->query("REPLACE INTO {$tablepre}styles VALUES ('1','默认风格','1','1')");
	$db->query("DELETE FROM {$tablepre}stylevars WHERE styleid='1'");
	$db->query("REPLACE INTO {$tablepre}stylevars (styleid, variable, substitute) VALUES
		('1', 'stypeid', '1'),
		('1', 'available', ''),
		('1', 'boardimg', 'logo.gif'),
		('1', 'imgdir', ''),
		('1', 'styleimgdir', ''),
		('1', 'font', 'Verdana, Helvetica, Arial, sans-serif'),
		('1', 'fontsize', '12px'),
		('1', 'smfont', 'Verdana, Helvetica, Arial, sans-serif'),
		('1', 'smfontsize', '0.83em'),
		('1', 'tabletext', '#444'),
		('1', 'midtext', '#666'),
		('1', 'lighttext', '#999'),
		('1', 'link', '#000'),
		('1', 'highlightlink', '#09C'),
		('1', 'noticetext', '#F60'),
		('1', 'msgfontsize', '14px'),
		('1', 'msgbigsize', '16px'),
		('1', 'bgcolor', '#0D2345 bodybg.gif repeat-x 0 90px'),
		('1', 'sidebgcolor', '#FFF sidebg.gif repeat-y 100% 0'),
		('1', 'headerborder', '1px'),
		('1', 'headerbordercolor', '#00B2E8'),
		('1', 'headerbgcolor', '#00A2D2 header.gif repeat-x 0 100%'),
		('1', 'headertext', '#97F2FF'),
		('1', 'footertext', '#8691A2'),
		('1', 'menuborder', '#B0E4EF'),
		('1', 'menubgcolor', '#EBF4FD mtabbg.gif repeat-x 0 100%'),
		('1', 'menutext', '#666'),
		('1', 'menuhover', '#1E4B7E'),
		('1', 'menuhovertext', '#C3D3E4'),
		('1', 'wrapwidth', '960px'),
		('1', 'wrapbg', '#FFF'),
		('1', 'wrapborder', '0'),
		('1', 'wrapbordercolor', ''),
		('1', 'contentwidth', '600px'),
		('1', 'contentseparate', '#D3E8F2'),
		('1', 'inputborder', '#CCC'),
		('1', 'inputborderdarkcolor', '#999'),
		('1', 'inputbg', '#FFF'),
		('1', 'commonborder', '#E6E7E1'),
		('1', 'commonbg', '#F7F7F7'),
		('1', 'specialborder', '#E3EDF5'),
		('1', 'specialbg', '#EBF2F8'),
		('1', 'interleavecolor', '#F5F5F5'),
		('1', 'dropmenuborder', '#7FCAE2'),
		('1', 'dropmenubgcolor', '#FEFEFE'),
		('1', 'floatmaskbgcolor', '#7FCAE2'),
		('1', 'floatbgcolor', '#F1F5FA');");
	$db->query("UPDATE {$tablepre}settings SET value='1' WHERE variable='styleid'");
	$db->query("UPDATE {$tablepre}members SET styleid='0'");
	$db->query("UPDATE {$tablepre}styles SET available='0' WHERE styleid<>'$newstyleid'");
	$db->query("UPDATE {$tablepre}settings SET value='8' WHERE variable='smcols' AND value<'8'");
	$db->query("UPDATE {$tablepre}settings SET value='5' WHERE variable='smrows' AND value<'5'");
	instmsg("论坛风格处理完毕。", "?step=10");
	instfooter();

} else {

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$uc_input = uc_api_input("action=updatecache");

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');
	dir_clear('./uc_client/data/cache');
	@touch($lock_file);
	if(!@unlink('upgrade11.php')) {
		echo '<br />恭喜您论坛数据升级成功，接下来请您：<ol><li><b>必删除本程序</b>';
	} else {
		echo '<br />恭喜您论坛数据升级成功，接下来请您：<ol>';
	}
	if(!defined('UC_CONNECT') || UC_CONNECT != 'mysql') {
		if(is_dir(DISCUZ_ROOT.'./forumdata/upgsecques/')) {
			echo '<li><b><font color="red">请将目录 : '.DISCUZ_ROOT.'./forumdata/ 下 upgsecques 目录上传至 '.UC_API.'/data/下，之后<a href="'.UC_API.'/upgrade/upgrade2.php?action=upgsecques" target="_blank">点击这里</a><br />或者浏览器直接访问一下地址：'.UC_API.'/upgrade/upgrade2.php?action=upgsecques<br />此操作会将论坛设定的安全提问升级到 UCenter ，如果没有执行此步骤，所有设定了安全提问的会员在登陆的时候均无需输入安全提问即可登陆。</font></b></li>';
		}	
	}
	echo '<li><b><font color="red">如果您开启过远程附件那么请参考这个帖子对远程附件进行升级：<a href="http://www.discuz.net/thread-1107842-1-1.html" target="_blank">http://www.discuz.net/thread-1107842-1-1.html</a></font></b>';
	echo '<li>使用管理员身份登录论坛，进入后台，更新缓存'.
		'<li>进行论坛注册、登录、发贴等常规测试，看看运行是否正常<br /><br />'.
		'<b>感谢您选用我们的产品！</b><a href="index.php" target="_blank">您现在可以访问论坛，查看升级情况</a><iframe width="0" height="0" src="index.php"></iframe>';
	instfooter();

}

function send_sql_to_uc($sql) {
	$url = UC_API.'/accept_sql.php?appid='.UC_APPID.'&uckey='.UC_KEY.'&sql='.urlencode($sql);
	return file_get_contents($url);
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
	if($directory = dir($dir)) {
		while($entry = $directory->read()) {
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			}
		}
		@touch($dir.'/index.htm');
		$directory->close();
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

function upg_bbcodes() {
	global $db, $tablepre, $upgradesql_bbcodes;

	$bbcodes = array();
	$query = $db->query("SELECT * FROM {$tablepre}bbcodes WHERE type='0'");
	while($bbcode = $db->fetch_array($query)) {
		$bbcode['available'] = $bbcode['available'] && $bbcode['icon'] ? 2 : $bbcode['available'];
		$bbcodes[] = daddslashes($bbcode, 1);
	}
	$db->query("TRUNCATE {$tablepre}bbcodes");
	runquery($upgradesql_bbcodes);
	$i = 18;
	foreach($bbcodes as $bbcode) {
		$db->query("INSERT INTO {$tablepre}bbcodes (available, tag, icon, replacement, example, explanation, params, prompt, nest, displayorder) VALUES ('$bbcode[available]', '$bbcode[tag]', '$bbcode[icon]', '$bbcode[replacement]', '$bbcode[example]', '$bbcode[explanation]', '$bbcode[params]', '$bbcode[prompt]', '$bbcode[nest]', '$i');");
		$i++;
	}
}

function daddslashes($string, $force = 0) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force);
			}
		} else {
			$string = addslashes($string);
		}
	}
	return $string;
}

function upg_js() {
	global $db, $tablepre;

	$request_data = array (
  '边栏模块_版块树形列表' =>
  array (
    'url' => 'function=module&module=forumtree.inc.php&settings=N%3B&jscharset=0&cachelife=864000',
    'parameter' =>
    array (
      'module' => 'forumtree.inc.php',
      'cachelife' => '864000',
      'jscharset' => '0',
    ),
    'comment' => '边栏版块树形列表模块',
    'type' => '5',
  ),
  '边栏模块_版主排行' =>
  array (
    'url' => 'function=module&module=modlist.inc.php&settings=N%3B&jscharset=0&cachelife=3600',
    'parameter' =>
    array (
      'module' => 'modlist.inc.php',
      'cachelife' => '3600',
      'jscharset' => '0',
    ),
    'comment' => '边栏版主排行模块',
    'type' => '5',
  ),
  '聚合模块_版块列表' =>
  array (
    'url' => 'function=module&module=rowcombine.inc.php&settings=a%3A1%3A%7Bs%3A4%3A%22data%22%3Bs%3A58%3A%22%B1%DF%C0%B8%C4%A3%BF%E9_%B0%E6%BF%E9%C5%C5%D0%D0%2C%B0%E6%BF%E9%C5%C5%D0%D0%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%B0%E6%BF%E9%CA%F7%D0%CE%C1%D0%B1%ED%2C%B0%E6%BF%E9%C1%D0%B1%ED%22%3B%7D&jscharset=0&cachelife=864000',
    'parameter' =>
    array (
      'module' => 'rowcombine.inc.php',
      'cachelife' => '864000',
      'settings' =>
      array (
        'data' => '边栏模块_版块排行,版块排行
边栏模块_版块树形列表,版块列表',
      ),
      'jscharset' => '0',
    ),
    'comment' => '热门版块、版块树形聚合模块',
    'type' => '5',
  ),
  '边栏模块_版块排行' =>
  array (
    'url' => 'function=forums&startrow=0&items=0&newwindow=1&orderby=posts&jscharset=0&cachelife=43200&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%B0%E6%BF%E9%C5%C5%D0%D0%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%3Cimg%20style%3D%5C%22vertical-align%3Amiddle%5C%22%20src%3D%5C%22images%2Fdefault%2Ftree_file.gif%5C%22%20%2F%3E%20%7Bforumname%7D%28%7Bposts%7D%29%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>版块排行</h4>
<ul class=\\"textinfolist\\">
[node]<li><img style=\\"vertical-align:middle\\" src=\\"images/default/tree_file.gif\\" /> {forumname}({posts})</li>[/node]
</ul>
</div>',
      'cachelife' => '43200',
      'startrow' => '0',
      'items' => '0',
      'newwindow' => 1,
      'orderby' => 'posts',
      'jscharset' => '0',
    ),
    'comment' => '边栏版块排行模块',
    'type' => '1',
  ),
  '聚合模块_热门主题' => 
  array (
    'url' => 'function=module&module=rowcombine.inc.php&settings=a%3A2%3A%7Bs%3A5%3A%22title%22%3Bs%3A8%3A%22%C8%C8%C3%C5%D6%F7%CC%E2%22%3Bs%3A4%3A%22data%22%3Bs%3A79%3A%22%B1%DF%C0%B8%C4%A3%BF%E9_%C8%C8%C3%C5%D6%F7%CC%E2_%BD%F1%C8%D5%2C%C8%D5%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%C8%C8%C3%C5%D6%F7%CC%E2_%B1%BE%D6%DC%2C%D6%DC%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%C8%C8%C3%C5%D6%F7%CC%E2_%B1%BE%D4%C2%2C%D4%C2%22%3B%7D&jscharset=0&cachelife=1800',
    'parameter' => 
    array (
      'module' => 'rowcombine.inc.php',
      'cachelife' => '1800',
      'settings' => 
      array (
        'title' => '热门主题',
        'data' => '边栏模块_热门主题_今日,日
边栏模块_热门主题_本周,周
边栏模块_热门主题_本月,月',
      ),
      'jscharset' => '0',
    ),
    'comment' => '今日、本周、本月热门主题聚合模块',
    'type' => '5',
  ),
  '边栏模块_热门主题_本月' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=hourviews&hours=720&jscharset=0&cachelife=86400&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%D4%C2%C8%C8%C3%C5%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>本月热门</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '86400',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
      'tids' => '',
      'keyword' => '',
      'tag' => '',
      'threadtype' => '0',
      'highlight' => '0',
      'recommend' => '0',
      'newwindow' => 1,
      'orderby' => 'hourviews',
      'hours' => '720',
      'jscharset' => '0',
    ),
    'comment' => '边栏本月热门主题模块',
    'type' => '0',
  ),
  '聚合模块_会员排行' => 
  array (
    'url' => 'function=module&module=rowcombine.inc.php&settings=a%3A2%3A%7Bs%3A5%3A%22title%22%3Bs%3A8%3A%22%BB%E1%D4%B1%C5%C5%D0%D0%22%3Bs%3A4%3A%22data%22%3Bs%3A79%3A%22%B1%DF%C0%B8%C4%A3%BF%E9_%BB%E1%D4%B1%C5%C5%D0%D0_%BD%F1%C8%D5%2C%C8%D5%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%BB%E1%D4%B1%C5%C5%D0%D0_%B1%BE%D6%DC%2C%D6%DC%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%BB%E1%D4%B1%C5%C5%D0%D0_%B1%BE%D4%C2%2C%D4%C2%22%3B%7D&jscharset=0&cachelife=3600',
    'parameter' => 
    array (
      'module' => 'rowcombine.inc.php',
      'cachelife' => '3600',
      'settings' => 
      array (
        'title' => '会员排行',
        'data' => '边栏模块_会员排行_今日,日
边栏模块_会员排行_本周,周
边栏模块_会员排行_本月,月',
      ),
      'jscharset' => '0',
    ),
    'comment' => '今日、本周、本月会员排行聚合模块',
    'type' => '5',
  ),
  '边栏模块_推荐主题' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=1&newwindow=1&threadtype=0&highlight=0&orderby=lastpost&hours=48&jscharset=0&cachelife=3600&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%CD%C6%BC%F6%D6%F7%CC%E2%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>推荐主题</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '3600',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
      'tids' => '',
      'keyword' => '',
      'tag' => '',
      'threadtype' => '0',
      'highlight' => '0',
      'recommend' => '1',
      'newwindow' => 1,
      'orderby' => 'lastpost',
      'hours' => '48',
      'jscharset' => '0',
    ),
    'comment' => '边栏推荐主题模块',
    'type' => '0',
  ),
  '边栏模块_最新图片' => 
  array (
    'url' => 'function=images&sidestatus=0&isimage=1&threadmethod=1&maxwidth=140&maxheight=140&startrow=0&items=5&orderby=dateline&hours=0&digest=0&newwindow=1&jscharset=0&jstemplate=%3Cdiv%20%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%D7%EE%D0%C2%CD%BC%C6%AC%3C%2Fh4%3E%0D%0A%3Cscript%20type%3D%5C%22text%2Fjavascript%5C%22%3E%0D%0Avar%20slideSpeed%20%3D%202500%3B%0D%0Avar%20slideImgsize%20%3D%20%5B140%2C140%5D%3B%0D%0Avar%20slideTextBar%20%3D%200%3B%0D%0Avar%20slideBorderColor%20%3D%20%5C%27%23C8DCEC%5C%27%3B%0D%0Avar%20slideBgColor%20%3D%20%5C%27%23FFF%5C%27%3B%0D%0Avar%20slideImgs%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideImgLinks%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideImgTexts%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideSwitchBar%20%3D%201%3B%0D%0Avar%20slideSwitchColor%20%3D%20%5C%27black%5C%27%3B%0D%0Avar%20slideSwitchbgColor%20%3D%20%5C%27white%5C%27%3B%0D%0Avar%20slideSwitchHiColor%20%3D%20%5C%27%23C8DCEC%5C%27%3B%0D%0A%5Bnode%5D%0D%0AslideImgs%5B%7Border%7D%5D%20%3D%20%5C%22%7Bimgfile%7D%5C%22%3B%0D%0AslideImgLinks%5B%7Border%7D%5D%20%3D%20%5C%22%7Blink%7D%5C%22%3B%0D%0AslideImgTexts%5B%7Border%7D%5D%20%3D%20%5C%22%7Bsubject%7D%5C%22%3B%0D%0A%5B%2Fnode%5D%0D%0A%3C%2Fscript%3E%0D%0A%3Cscript%20language%3D%5C%22javascript%5C%22%20type%3D%5C%22text%2Fjavascript%5C%22%20src%3D%5C%22include%2Fjs%2Fslide.js%5C%22%3E%3C%2Fscript%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' => 
    array (
      'jstemplate' => '<div  class=\\"sidebox\\">
<h4>最新图片</h4>
<script type=\\"text/javascript\\">
var slideSpeed = 2500;
var slideImgsize = [140,140];
var slideTextBar = 0;
var slideBorderColor = \\\'#C8DCEC\\\';
var slideBgColor = \\\'#FFF\\\';
var slideImgs = new Array();
var slideImgLinks = new Array();
var slideImgTexts = new Array();
var slideSwitchBar = 1;
var slideSwitchColor = \\\'black\\\';
var slideSwitchbgColor = \\\'white\\\';
var slideSwitchHiColor = \\\'#C8DCEC\\\';
[node]
slideImgs[{order}] = \\"{imgfile}\\";
slideImgLinks[{order}] = \\"{link}\\";
slideImgTexts[{order}] = \\"{subject}\\";
[/node]
</script>
<script language=\\"javascript\\" type=\\"text/javascript\\" src=\\"include/js/slide.js\\"></script>
</div>',
      'cachelife' => '',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'isimage' => '1',
      'maxwidth' => '140',
      'maxheight' => '140',
      'threadmethod' => '1',
      'newwindow' => 1,
      'orderby' => 'dateline',
      'hours' => '',
      'jscharset' => '0',
    ),
    'comment' => '边栏最新图片展示模块',
    'type' => '4',
  ),
  '边栏模块_最新主题' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=dateline&hours=0&jscharset=0&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%D7%EE%D0%C2%D6%F7%CC%E2%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>最新主题</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
      'tids' => '',
      'keyword' => '',
      'tag' => '',
      'threadtype' => '0',
      'highlight' => '0',
      'recommend' => '0',
      'newwindow' => 1,
      'orderby' => 'dateline',
      'hours' => '',
      'jscharset' => '0',
    ),
    'comment' => '边栏最新主题模块',
    'type' => '0',
  ),
  '边栏模块_活跃会员' =>
  array (
    'url' => 'function=memberrank&startrow=0&items=12&newwindow=1&extcredit=1&orderby=posts&hours=0&jscharset=0&cachelife=43200&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%BB%EE%D4%BE%BB%E1%D4%B1%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22avt_list%20s_clear%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bavatarsmall%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>活跃会员</h4>
<ul class=\\"avt_list s_clear\\">
[node]<li>{avatarsmall}</li>[/node]
</ul>
</div>',
      'cachelife' => '43200',
      'startrow' => '0',
      'items' => '12',
      'newwindow' => 1,
      'extcredit' => '1',
      'orderby' => 'posts',
      'hours' => '',
      'jscharset' => '0',
    ),
    'comment' => '边栏活跃会员模块',
    'type' => '2',
  ),
  '边栏模块_热门主题_本版' =>
  array (
    'url' => 'function=threads&sidestatus=1&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=replies&hours=0&jscharset=0&cachelife=1800&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%B0%E6%C8%C8%C3%C5%D6%F7%CC%E2%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>本版热门主题</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '1800',
      'sidestatus' => '1',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
      'tids' => '',
      'keyword' => '',
      'tag' => '',
      'threadtype' => '0',
      'highlight' => '0',
      'recommend' => '0',
      'newwindow' => 1,
      'orderby' => 'replies',
      'hours' => '',
      'jscharset' => '0',
    ),
    'comment' => '边栏本版热门主题模块',
    'type' => '0',
  ),
  '边栏模块_热门主题_今日' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=hourviews&hours=24&jscharset=0&cachelife=1800&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%BD%F1%C8%D5%C8%C8%C3%C5%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>今日热门</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '1800',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
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
    'comment' => '边栏今日热门主题模块',
    'type' => '0',
  ),
  '边栏模块_最新回复' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=lastpost&hours=0&jscharset=0&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%D7%EE%D0%C2%BB%D8%B8%B4%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>最新回复</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
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
    'comment' => '边栏最新回复模块',
    'type' => '0',
  ),
  '边栏模块_最新图片_本版' => 
  array (
    'url' => 'function=images&sidestatus=1&isimage=1&threadmethod=1&maxwidth=140&maxheight=140&startrow=0&items=5&orderby=dateline&hours=0&digest=0&newwindow=1&jscharset=0&jstemplate=%3Cdiv%20%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%D7%EE%D0%C2%CD%BC%C6%AC%3C%2Fh4%3E%0D%0A%3Cscript%20type%3D%5C%22text%2Fjavascript%5C%22%3E%0D%0Avar%20slideSpeed%20%3D%202500%3B%0D%0Avar%20slideImgsize%20%3D%20%5B140%2C140%5D%3B%0D%0Avar%20slideTextBar%20%3D%200%3B%0D%0Avar%20slideBorderColor%20%3D%20%5C%27%23C8DCEC%5C%27%3B%0D%0Avar%20slideBgColor%20%3D%20%5C%27%23FFF%5C%27%3B%0D%0Avar%20slideImgs%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideImgLinks%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideImgTexts%20%3D%20new%20Array%28%29%3B%0D%0Avar%20slideSwitchBar%20%3D%201%3B%0D%0Avar%20slideSwitchColor%20%3D%20%5C%27black%5C%27%3B%0D%0Avar%20slideSwitchbgColor%20%3D%20%5C%27white%5C%27%3B%0D%0Avar%20slideSwitchHiColor%20%3D%20%5C%27%23C8DCEC%5C%27%3B%0D%0A%5Bnode%5D%0D%0AslideImgs%5B%7Border%7D%5D%20%3D%20%5C%22%7Bimgfile%7D%5C%22%3B%0D%0AslideImgLinks%5B%7Border%7D%5D%20%3D%20%5C%22%7Blink%7D%5C%22%3B%0D%0AslideImgTexts%5B%7Border%7D%5D%20%3D%20%5C%22%7Bsubject%7D%5C%22%3B%0D%0A%5B%2Fnode%5D%0D%0A%3C%2Fscript%3E%0D%0A%3Cscript%20language%3D%5C%22javascript%5C%22%20type%3D%5C%22text%2Fjavascript%5C%22%20src%3D%5C%22include%2Fjs%2Fslide.js%5C%22%3E%3C%2Fscript%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' => 
    array (
      'jstemplate' => '<div  class=\\"sidebox\\">
<h4>最新图片</h4>
<script type=\\"text/javascript\\">
var slideSpeed = 2500;
var slideImgsize = [140,140];
var slideTextBar = 0;
var slideBorderColor = \\\'#C8DCEC\\\';
var slideBgColor = \\\'#FFF\\\';
var slideImgs = new Array();
var slideImgLinks = new Array();
var slideImgTexts = new Array();
var slideSwitchBar = 1;
var slideSwitchColor = \\\'black\\\';
var slideSwitchbgColor = \\\'white\\\';
var slideSwitchHiColor = \\\'#C8DCEC\\\';
[node]
slideImgs[{order}] = \\"{imgfile}\\";
slideImgLinks[{order}] = \\"{link}\\";
slideImgTexts[{order}] = \\"{subject}\\";
[/node]
</script>
<script language=\\"javascript\\" type=\\"text/javascript\\" src=\\"include/js/slide.js\\"></script>
</div>',
      'cachelife' => '',
      'sidestatus' => '1',
      'startrow' => '0',
      'items' => '5',
      'isimage' => '1',
      'maxwidth' => '140',
      'maxheight' => '140',
      'threadmethod' => '1',
      'newwindow' => 1,
      'orderby' => 'dateline',
      'hours' => '',
      'jscharset' => '0',
    ),
    'comment' => '边栏本版最新图片展示模块',
    'type' => '4',
  ),
  '边栏模块_标签' =>
  array (
    'url' => 'function=module&module=tag.inc.php&settings=a%3A1%3A%7Bs%3A5%3A%22limit%22%3Bs%3A2%3A%2220%22%3B%7D&jscharset=0&cachelife=900',
    'parameter' =>
    array (
      'module' => 'tag.inc.php',
      'cachelife' => '900',
      'settings' =>
      array (
        'limit' => '20',
      ),
      'jscharset' => '0',
    ),
    'comment' => '边栏标签模块',
    'type' => '5',
  ),
  '边栏模块_会员排行_本月' =>
  array (
    'url' => 'function=memberrank&startrow=0&items=5&newwindow=1&extcredit=1&orderby=hourposts&hours=720&jscharset=0&cachelife=86400&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%20s_clear%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%D4%C2%C5%C5%D0%D0%3C%2Fh4%3E%0D%0A%5Bnode%5D%3Cdiv%20style%3D%5C%22clear%3Aboth%5C%22%3E%3Cdiv%20style%3D%5C%22float%3Aleft%3Bmargin%3A%200%2016px%205px%200%5C%22%3E%7Bavatarsmall%7D%3C%2Fdiv%3E%7Bmember%7D%3Cbr%20%2F%3E%B7%A2%CC%FB%20%7Bvalue%7D%20%C6%AA%3C%2Fdiv%3E%5B%2Fnode%5D%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox s_clear\\">
<h4>本月排行</h4>
[node]<div style=\\"clear:both\\"><div style=\\"float:left;margin: 0 16px 5px 0\\">{avatarsmall}</div>{member}<br />发帖 {value} 篇</div>[/node]
</div>',
      'cachelife' => '86400',
      'startrow' => '0',
      'items' => '5',
      'newwindow' => 1,
      'extcredit' => '1',
      'orderby' => 'hourposts',
      'hours' => '720',
      'jscharset' => '0',
    ),
    'comment' => '边栏会员本月发帖排行模块',
    'type' => '2',
  ),
  '边栏模块_会员排行_本周' =>
  array (
    'url' => 'function=memberrank&startrow=0&items=5&newwindow=1&extcredit=1&orderby=hourposts&hours=168&jscharset=0&cachelife=43200&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%20s_clear%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%D6%DC%C5%C5%D0%D0%3C%2Fh4%3E%0D%0A%5Bnode%5D%3Cdiv%20style%3D%5C%22clear%3Aboth%5C%22%3E%3Cdiv%20style%3D%5C%22float%3Aleft%3Bmargin%3A%200%2016px%205px%200%5C%22%3E%7Bavatarsmall%7D%3C%2Fdiv%3E%7Bmember%7D%3Cbr%20%2F%3E%B7%A2%CC%FB%20%7Bvalue%7D%20%C6%AA%3C%2Fdiv%3E%5B%2Fnode%5D%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox s_clear\\">
<h4>本周排行</h4>
[node]<div style=\\"clear:both\\"><div style=\\"float:left;margin: 0 16px 5px 0\\">{avatarsmall}</div>{member}<br />发帖 {value} 篇</div>[/node]
</div>',
      'cachelife' => '43200',
      'startrow' => '0',
      'items' => '5',
      'newwindow' => 1,
      'extcredit' => '1',
      'orderby' => 'hourposts',
      'hours' => '168',
      'jscharset' => '0',
    ),
    'comment' => '边栏会员本周发帖排行模块',
    'type' => '2',
  ),
   '边栏方案_主题列表页默认' =>
  array (
    'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5D%B1%DF%C0%B8%C4%A3%BF%E9_%CE%D2%B5%C4%D6%FA%CA%D6%5B%2Fmodule%5D%3Chr%20class%3D%22shadowline%22%2F%3E%5Bmodule%5D%B1%DF%C0%B8%C4%A3%BF%E9_%C8%C8%C3%C5%D6%F7%CC%E2_%B1%BE%B0%E6%5B%2Fmodule%5D%3Chr%20class%3D%22shadowline%22%2F%3E%5Bmodule%5D%B1%DF%C0%B8%C4%A3%BF%E9_%B0%E6%BF%E9%C5%C5%D0%D0%5B%2Fmodule%5D',
    'parameter' =>
    array (
      'selectmodule' =>
      array (
        1 => '边栏模块_我的助手',
        2 => '边栏模块_热门主题_本版',
        3 => '边栏模块_版块排行',
      ),
      'cachelife' => 0,
      'jstemplate' => '[module]边栏模块_我的助手[/module]<hr class="shadowline"/>[module]边栏模块_热门主题_本版[/module]<hr class="shadowline"/>[module]边栏模块_版块排行[/module]',
    ),
    'comment' => NULL,
    'type' => '-2',
  ),
  '边栏方案_首页默认' =>
  array (
    'url' => 'function=side&jscharset=&jstemplate=%5Bmodule%5D%B1%DF%C0%B8%C4%A3%BF%E9_%CE%D2%B5%C4%D6%FA%CA%D6%5B%2Fmodule%5D%3Chr%20class%3D%22shadowline%22%2F%3E%5Bmodule%5D%BE%DB%BA%CF%C4%A3%BF%E9_%D0%C2%CC%FB%5B%2Fmodule%5D%3Chr%20class%3D%22shadowline%22%2F%3E%5Bmodule%5D%BE%DB%BA%CF%C4%A3%BF%E9_%C8%C8%C3%C5%D6%F7%CC%E2%5B%2Fmodule%5D%3Chr%20class%3D%22shadowline%22%2F%3E%5Bmodule%5D%B1%DF%C0%B8%C4%A3%BF%E9_%BB%EE%D4%BE%BB%E1%D4%B1%5B%2Fmodule%5D',
    'parameter' =>
    array (
      'selectmodule' =>
      array (
        1 => '边栏模块_我的助手',
        2 => '聚合模块_新帖',
        3 => '聚合模块_热门主题',
        4 => '边栏模块_活跃会员',
      ),
      'cachelife' => 0,
      'jstemplate' => '[module]边栏模块_我的助手[/module]<hr class="shadowline"/>[module]聚合模块_新帖[/module]<hr class="shadowline"/>[module]聚合模块_热门主题[/module]<hr class="shadowline"/>[module]边栏模块_活跃会员[/module]',
    ),
    'comment' => NULL,
    'type' => '-2',
  ),
  '聚合模块_新帖' => 
  array (
    'url' => 'function=module&module=rowcombine.inc.php&settings=a%3A2%3A%7Bs%3A5%3A%22title%22%3Bs%3A8%3A%22%D7%EE%D0%C2%CC%FB%D7%D3%22%3Bs%3A4%3A%22data%22%3Bs%3A46%3A%22%B1%DF%C0%B8%C4%A3%BF%E9_%D7%EE%D0%C2%D6%F7%CC%E2%2C%D6%F7%CC%E2%0D%0A%B1%DF%C0%B8%C4%A3%BF%E9_%D7%EE%D0%C2%BB%D8%B8%B4%2C%BB%D8%B8%B4%22%3B%7D&jscharset=0',
    'parameter' => 
    array (
      'module' => 'rowcombine.inc.php',
      'cachelife' => '',
      'settings' => 
      array (
        'title' => '最新帖子',
        'data' => '边栏模块_最新主题,主题
边栏模块_最新回复,回复',
      ),
      'jscharset' => '0',
    ),
    'comment' => '最新主题、最新回复聚合模块',
    'type' => '5',
  ),
  '边栏模块_热门主题_本周' =>
  array (
    'url' => 'function=threads&sidestatus=0&maxlength=20&fnamelength=0&messagelength=&startrow=0&picpre=images%2Fcommon%2Fslisticon.gif&items=5&tag=&tids=&special=0&rewardstatus=&digest=0&stick=0&recommend=0&newwindow=1&threadtype=0&highlight=0&orderby=hourviews&hours=168&jscharset=0&cachelife=43200&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%D6%DC%C8%C8%C3%C5%3C%2Fh4%3E%0D%0A%3Cul%20class%3D%5C%22textinfolist%5C%22%3E%0D%0A%5Bnode%5D%3Cli%3E%7Bprefix%7D%7Bsubject%7D%3C%2Fli%3E%5B%2Fnode%5D%0D%0A%3C%2Ful%3E%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox\\">
<h4>本周热门</h4>
<ul class=\\"textinfolist\\">
[node]<li>{prefix}{subject}</li>[/node]
</ul>
</div>',
      'cachelife' => '43200',
      'sidestatus' => '0',
      'startrow' => '0',
      'items' => '5',
      'maxlength' => '20',
      'fnamelength' => '0',
      'messagelength' => '',
      'picpre' => 'images/common/slisticon.gif',
      'tids' => '',
      'keyword' => '',
      'tag' => '',
      'threadtype' => '0',
      'highlight' => '0',
      'recommend' => '0',
      'newwindow' => 1,
      'orderby' => 'hourviews',
      'hours' => '168',
      'jscharset' => '0',
    ),
    'comment' => '边栏本周热门主题模块',
    'type' => '0',
  ),
  '边栏模块_会员排行_今日' =>
  array (
    'url' => 'function=memberrank&startrow=0&items=5&newwindow=1&extcredit=1&orderby=hourposts&hours=24&jscharset=0&cachelife=3600&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%20s_clear%5C%22%3E%0D%0A%3Ch4%3E%BD%F1%C8%D5%C5%C5%D0%D0%3C%2Fh4%3E%0D%0A%5Bnode%5D%3Cdiv%20style%3D%5C%22clear%3Aboth%5C%22%3E%3Cdiv%20style%3D%5C%22float%3Aleft%3Bmargin%3A%200%2016px%205px%200%5C%22%3E%7Bavatarsmall%7D%3C%2Fdiv%3E%7Bmember%7D%3Cbr%20%2F%3E%B7%A2%CC%FB%20%7Bvalue%7D%20%C6%AA%3C%2Fdiv%3E%5B%2Fnode%5D%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox s_clear\\">
<h4>今日排行</h4>
[node]<div style=\\"clear:both\\"><div style=\\"float:left;margin: 0 16px 5px 0\\">{avatarsmall}</div>{member}<br />发帖 {value} 篇</div>[/node]
</div>',
      'cachelife' => '3600',
      'startrow' => '0',
      'items' => '5',
      'newwindow' => 1,
      'extcredit' => '1',
      'orderby' => 'hourposts',
      'hours' => '24',
      'jscharset' => '0',
    ),
    'comment' => '边栏会员今日发帖排行模块',
    'type' => '2',
  ),
  '边栏模块_论坛之星' =>
  array (
    'url' => 'function=memberrank&startrow=0&items=3&newwindow=1&extcredit=1&orderby=hourposts&hours=168&jscharset=0&cachelife=43200&jstemplate=%3Cdiv%20class%3D%5C%22sidebox%20s_clear%5C%22%3E%0D%0A%3Ch4%3E%B1%BE%D6%DC%D6%AE%D0%C7%3C%2Fh4%3E%0D%0A%5Bnode%5D%0D%0A%5Bshow%3D1%5D%3Cdiv%20style%3D%5C%22clear%3Aboth%5C%22%3E%3Cdiv%20style%3D%5C%22float%3Aleft%3B%20margin-right%3A%2016px%3B%5C%22%3E%7Bavatarsmall%7D%3C%2Fdiv%3E%5B%2Fshow%5D%7Bmember%7D%20%5Bshow%3D1%5D%3Cbr%20%2F%3E%B7%A2%CC%FB%20%7Bvalue%7D%20%C6%AA%3C%2Fdiv%3E%3Cdiv%20style%3D%5C%22clear%3Aboth%3Bmargin-top%3A2px%5C%22%20%2F%3E%3C%2Fdiv%3E%5B%2Fshow%5D%0D%0A%5B%2Fnode%5D%0D%0A%3C%2Fdiv%3E',
    'parameter' =>
    array (
      'jstemplate' => '<div class=\\"sidebox s_clear\\">
<h4>本周之星</h4>
[node]
[show=1]<div style=\\"clear:both\\"><div style=\\"float:left; margin-right: 16px;\\">{avatarsmall}</div>[/show]{member} [show=1]<br />发帖 {value} 篇</div><div style=\\"clear:both;margin-top:2px\\" /></div>[/show]
[/node]
</div>',
      'cachelife' => '43200',
      'startrow' => '0',
      'items' => '3',
      'newwindow' => 1,
      'extcredit' => '1',
      'orderby' => 'hourposts',
      'hours' => '168',
      'jscharset' => '0',
    ),
    'comment' => '边栏论坛之星模块',
    'type' => '2',
  ),
  '边栏模块_我的助手' =>
  array (
    'url' => 'function=module&module=assistant.inc.php&settings=N%3B&jscharset=0&cachelife=0',
    'parameter' =>
    array (
      'module' => 'assistant.inc.php',
      'cachelife' => '0',
      'jscharset' => '0',
    ),
    'comment' => '边栏我的助手模块',
    'type' => '5',
  ),
  '边栏模块_Google搜索' =>
  array (
    'url' => 'function=module&module=google.inc.php&settings=a%3A2%3A%7Bs%3A4%3A%22lang%22%3Bs%3A0%3A%22%22%3Bs%3A7%3A%22default%22%3Bs%3A1%3A%221%22%3B%7D&jscharset=0&cachelife=864000',
    'parameter' =>
    array (
      'module' => 'google.inc.php',
      'cachelife' => '864000',
      'settings' =>
      array (
        'lang' => '',
        'default' => '1',
      ),
      'jscharset' => '0',
    ),
    'comment' => '边栏 Google 搜索模块',
    'type' => '5',
  ),
  'UCHome_最新动态' => 
  array (
    'url' => 'function=module&module=feed.inc.php&settings=a%3A6%3A%7Bs%3A5%3A%22title%22%3Bs%3A8%3A%22%D7%EE%D0%C2%B6%AF%CC%AC%22%3Bs%3A4%3A%22uids%22%3Bs%3A0%3A%22%22%3Bs%3A6%3A%22friend%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22start%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22limit%22%3Bs%3A2%3A%2210%22%3Bs%3A8%3A%22template%22%3Bs%3A54%3A%22%3Cdiv%20style%3D%5C%22padding-left%3A2px%5C%22%3E%7Btitle_template%7D%3C%2Fdiv%3E%22%3B%7D&jscharset=0&cachelife=0',
    'parameter' => 
    array (
      'module' => 'feed.inc.php',
      'cachelife' => '0',
      'settings' => 
      array (
        'title' => '最新动态',
        'uids' => '',
        'friend' => '0',
        'start' => '0',
        'limit' => '10',
        'template' => '<div style=\\"padding-left:2px\\">{title_template}</div>',
      ),
      'jscharset' => '0',
    ),
    'comment' => '获取UCHome的最新动态',
    'type' => '5',
  ),
  'UCHome_最新更新空间' => 
  array (
    'url' => 'function=module&module=space.inc.php&settings=a%3A17%3A%7Bs%3A5%3A%22title%22%3Bs%3A12%3A%22%D7%EE%D0%C2%B8%FC%D0%C2%BF%D5%BC%E4%22%3Bs%3A3%3A%22uid%22%3Bs%3A0%3A%22%22%3Bs%3A14%3A%22startfriendnum%22%3Bs%3A0%3A%22%22%3Bs%3A12%3A%22endfriendnum%22%3Bs%3A0%3A%22%22%3Bs%3A12%3A%22startviewnum%22%3Bs%3A0%3A%22%22%3Bs%3A10%3A%22endviewnum%22%3Bs%3A0%3A%22%22%3Bs%3A11%3A%22startcredit%22%3Bs%3A0%3A%22%22%3Bs%3A9%3A%22endcredit%22%3Bs%3A0%3A%22%22%3Bs%3A6%3A%22avatar%22%3Bs%3A2%3A%22-1%22%3Bs%3A10%3A%22namestatus%22%3Bs%3A2%3A%22-1%22%3Bs%3A8%3A%22dateline%22%3Bs%3A1%3A%220%22%3Bs%3A10%3A%22updatetime%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22order%22%3Bs%3A10%3A%22updatetime%22%3Bs%3A2%3A%22sc%22%3Bs%3A4%3A%22DESC%22%3Bs%3A5%3A%22start%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22limit%22%3Bs%3A2%3A%2210%22%3Bs%3A8%3A%22template%22%3Bs%3A267%3A%22%3Ctable%3E%0D%0A%3Ctr%3E%0D%0A%3Ctd%20width%3D%5C%2250%5C%22%20rowspan%3D%5C%222%5C%22%3E%3Ca%20href%3D%5C%22%7Buserlink%7D%5C%22%20target%3D%5C%22_blank%5C%22%3E%3Cimg%20src%3D%5C%22%7Bphoto%7D%5C%22%20%2F%3E%3C%2Fa%3E%3C%2Ftd%3E%0D%0A%3Ctd%3E%3Ca%20href%3D%5C%22%7Buserlink%7D%5C%22%20%20target%3D%5C%22_blank%5C%22%20style%3D%5C%22text-decoration%3Anone%3B%5C%22%3E%7Busername%7D%3C%2Fa%3E%3C%2Ftd%3E%0D%0A%3C%2Ftr%3E%0D%0A%3Ctr%3E%3Ctd%3E%7Bupdatetime%7D%3C%2Ftd%3E%3C%2Ftr%3E%0D%0A%3C%2Ftable%3E%22%3B%7D&jscharset=0&cachelife=0',
    'parameter' => 
    array (
      'module' => 'space.inc.php',
      'cachelife' => '0',
      'settings' => 
      array (
        'title' => '最新更新空间',
        'uid' => '',
        'startfriendnum' => '',
        'endfriendnum' => '',
        'startviewnum' => '',
        'endviewnum' => '',
        'startcredit' => '',
        'endcredit' => '',
        'avatar' => '-1',
        'namestatus' => '-1',
        'dateline' => '0',
        'updatetime' => '0',
        'order' => 'updatetime',
        'sc' => 'DESC',
        'start' => '0',
        'limit' => '10',
        'template' => '<table>
<tr>
<td width=\\"50\\" rowspan=\\"2\\"><a href=\\"{userlink}\\" target=\\"_blank\\"><img src=\\"{photo}\\" /></a></td>
<td><a href=\\"{userlink}\\"  target=\\"_blank\\" style=\\"text-decoration:none;\\">{username}</a></td>
</tr>
<tr><td>{updatetime}</td></tr>
</table>',
      ),
      'jscharset' => '0',
    ),
    'comment' => '获取UCHome最新更新会员空间',
    'type' => '5',
  ),
  'UCHome_最新记录' => 
  array (
    'url' => 'function=module&module=doing.inc.php&settings=a%3A6%3A%7Bs%3A5%3A%22title%22%3Bs%3A8%3A%22%D7%EE%D0%C2%BC%C7%C2%BC%22%3Bs%3A3%3A%22uid%22%3Bs%3A0%3A%22%22%3Bs%3A4%3A%22mood%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22start%22%3Bs%3A1%3A%220%22%3Bs%3A5%3A%22limit%22%3Bs%3A2%3A%2210%22%3Bs%3A8%3A%22template%22%3Bs%3A360%3A%22%0D%0A%3Cdiv%20style%3D%5C%22padding%3A0%200%205px%200%3B%5C%22%3E%0D%0A%3Ca%20href%3D%5C%22%7Buserlink%7D%5C%22%20target%3D%5C%22_blank%5C%22%3E%3Cimg%20src%3D%5C%22%7Bphoto%7D%5C%22%20width%3D%5C%2218%5C%22%20height%3D%5C%2218%5C%22%20align%3D%5C%22absmiddle%5C%22%3E%3C%2Fa%3E%20%3Ca%20href%3D%5C%22%7Buserlink%7D%5C%22%20%20target%3D%5C%22_blank%5C%22%3E%7Busername%7D%3C%2Fa%3E%A3%BA%0D%0A%3C%2Fdiv%3E%0D%0A%3Cdiv%20style%3D%5C%22padding%3A0%200%205px%2020px%3B%5C%22%3E%0D%0A%3Ca%20href%3D%5C%22%7Blink%7D%5C%22%20style%3D%5C%22color%3A%23333%3Btext-decoration%3Anone%3B%5C%22%20target%3D%5C%22_blank%5C%22%3E%7Bmessage%7D%3C%2Fa%3E%0D%0A%3C%2Fdiv%3E%22%3B%7D&jscharset=0&cachelife=0',
    'parameter' => 
    array (
      'module' => 'doing.inc.php',
      'cachelife' => '0',
      'settings' => 
      array (
        'title' => '最新记录',
        'uid' => '',
        'mood' => '0',
        'start' => '0',
        'limit' => '10',
        'template' => '
<div style=\\"padding:0 0 5px 0;\\">
<a href=\\"{userlink}\\" target=\\"_blank\\"><img src=\\"{photo}\\" width=\\"18\\" height=\\"18\\" align=\\"absmiddle\\"></a> <a href=\\"{userlink}\\"  target=\\"_blank\\">{username}</a>：
</div>
<div style=\\"padding:0 0 5px 20px;\\">
<a href=\\"{link}\\" style=\\"color:#333;text-decoration:none;\\" target=\\"_blank\\">{message}</a>
</div>',
      ),
      'jscharset' => '0',
    ),
    'comment' => '获取UCHome的最新记录',
    'type' => '5',
  ),
  'UCHome_竞价排名' => 
  array (
    'url' => 'function=module&module=html.inc.php&settings=a%3A3%3A%7Bs%3A4%3A%22type%22%3Bs%3A1%3A%220%22%3Bs%3A4%3A%22code%22%3Bs%3A27%3A%22%3Cdiv%20id%3D%5C%22sidefeed%5C%22%3E%3C%2Fdiv%3E%22%3Bs%3A4%3A%22side%22%3Bs%3A1%3A%220%22%3B%7D&jscharset=0&cachelife=864000',
    'parameter' => 
    array (
      'module' => 'html.inc.php',
      'cachelife' => '864000',
      'settings' => 
      array (
        'type' => '0',
        'code' => '<div id=\\"sidefeed\\"></div>',
        'side' => '0',
      ),
      'jscharset' => '0',
    ),
    'comment' => '获取UCHome的竞价排名信息',
    'type' => '5',
  ),
);

	foreach($request_data as $k => $v) {
		$variable = addslashes($k);
		$type = $v['type'];
		if(isset($v['parameter']['settings'])) {
			$v_settings = rawurlencode(serialize($v['parameter']['settings']));
			$v['url'] = preg_replace('/&settings=.+?([&|$])/', '&settings='.$v_settings.'\1', $v['url'].'&');
		}
		if(isset($v['parameter']['jstemplate'])) {
			$v_jstemplate = rawurlencode($v['parameter']['jstemplate']);
			$v['url'] = preg_replace('/&jstemplate=.+?([&|$])/', '&jstemplate='.$v_jstemplate.'\1', $v['url'].'&');
		}

		$value = addslashes(serialize($v));
		$db->query("REPLACE INTO {$tablepre}request (variable, value, type) VALUES ('$variable', '$value', '$type')");
	}
}



function upg_smiles() {
	global $db, $tablepre, $upgradesql_smiles;
	$upglogfile = DISCUZ_ROOT.'./forumdata/upgradesmile70.log';
	if(!file_exists($upglogfile)) {
		$lastid = $db->result_first("SELECT typeid FROM {$tablepre}imagetypes ORDER BY typeid DESC");
		$upgradesql_smiles = preg_replace('/\{typeid,(\d)\}/e', "\$lastid + \\1", $upgradesql_smiles);
		runquery($upgradesql_smiles);
		@touch($upglogfile);
	}
}

function upg_icons() {
	global $db, $tablepre, $upgradesql_icons;
	$upglogfile = DISCUZ_ROOT.'./forumdata/upgradeicon70.log';
	if(!file_exists($upglogfile)) {
		runquery($upgradesql_icons);
		@touch($upglogfile);
	}
}

function get_uc_root() {
	$uc_root = '';
	$uc = parse_url(UC_API);
	if($uc['host'] == $_SERVER['HTTP_HOST']) {
		$php_self_len = strlen($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
		$uc_root = substr(__FILE__, 0, -$php_self_len).$uc['path'];
	}
	return $uc_root;
}

function random($length, $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}
?>