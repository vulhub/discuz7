<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_magic_quotes_runtime(0);
@set_time_limit(0);

define('DISCUZ_ROOT', getcwd().'/');
define('IN_DISCUZ', TRUE);
$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
$newfunction_set = array("<h3>【新手任务】（扩展 -> 论坛任务）</h3>
		此功能用来帮助新手掌握论坛生存必备的基本技能。建议站长在进行该功能的设置时认真考虑任务奖励类型和具体的奖励量值。一般来讲，同时使用多种奖励形式(论坛设定开启【<a href='./admincp.php?frames=yes&action=magics&operation=config' target='_blank'>道具</a>】和【<a href='./admincp.php?frames=yes&action=medals' target='_blank'>勋章</a>】功能)更能激励新手们把所有新手任务做完。对积分的设置也要拉开层次，不要所有任务的奖励都奖励相同的积分量值。站长也可以修改任务描述，用更友好，更具吸引力的语言来描述任务，提高用户对完成任务的兴趣。

		下面是一个示例：
		任务一的任务名可以写“学习回帖” ，奖励10个金钱 。任务二的任务名写成“开始我的第一次”，奖励一种道具。 任务三的任务名写成“与众不同”，奖励一枚勋章。

		<a href='./admincp.php?frames=yes&action=tasks' target='_blank'><b>现在就进入后台设置</b></a>",
"<h3>【主题推荐】（版块 -> 编辑版块）</h3>
		此功能通过自动或手动方式从论坛数据中提取一些主题作为系统推荐的主题，这些主题一般为论坛里内容精彩，用户参与度高的话题。

		推荐主题的数量应设置合理，太多则让人眼花缭乱，太少则不美观。

		数据缓存时间也要设置得当，该值设置太大则数据长时间不更新，造成吸引力下降，设置太小频繁更新缓存又会增加服务器负担。

		<a href='./admincp.php?frames=yes&action=forums' target='_blank'><b>现在就进入后台设置</b></a>",
"<h3>【主题热度】（全局 -> 论坛功能）</h3>
		此功能会影响主题在主题列表显示时标题后图标的显示，主题的热度根据回复数、评价值等参量根据一定算法计算得到。当热度值达到设定的显示级别如50，100，200 时 ，在主题列表中主题的标题后会显示对应级别的图标，来表示该主题的热门程度。

		站长应该根据站点当前运营情况来设定这些值，一般推荐的方案是保证主题列表中，热门主题和普通主题的比例在 1:7 左右。

		<a href='./admincp.php?frames=yes&action=settings&operation=functions' target='_blank'><b>现在就进入后台设置</b></a>
","<h3>【主题评价】（全局 -> 论坛功能）</h3>
		此功能通过收集用户对主题的评价，来计算评价图标的显示级别，当达到设定的级别阈值时，在主题列表中显示主题标题后的对应级别的推荐图标。

		<a href='./admincp.php?frames=yes&action=settings&operation=functions' target='_blank'><b>现在就进入后台设置</b></a>
","<h3>【论坛动态】（全局 -> 论坛动态设置）</h3>
		此功能通过指定条件产生论坛动态消息，促进会员之间互动的产生。各项目的值应该根据当前论坛运营状况仔细斟酌而定。

		例如：论坛日发帖量在100左右的，设置【主题回复数达到一定值发送动态】时可以如下设置 “10, 30, 80” ， 这样当主题被回复了10次，30次，80次的时候都在论坛动态页产生一个动态消息。日发帖量在1000左右的论坛，就可以设置“30，100，200”。

		总结起来论坛小，活跃用户少， 日发帖量不大，那么应该将各项目的阈值调低，这样让论坛动态更容易产生。相反，论坛大，活跃用户多，日发帖量很大，那么应该将各项目的阈值调高，避免论坛动态泛滥，影响用户体验。

		<a href='./admincp.php?frames=yes&action=settings&operation=dzfeed' target='_blank'><b>现在就进入后台设置</b></a>");


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

$version['old'] = 'Discuz! 7.0 正式版';
$version['new'] = 'Discuz! 7.1';
$lock_file = DISCUZ_ROOT.'./forumdata/upgrade12.lock';

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

#主题关注;
DROP TABLE IF EXISTS cdb_favoritethreads;
CREATE TABLE cdb_favoritethreads (
	`tid` mediumint(8) NOT NULL DEFAULT '0',
	`uid` mediumint(8) NOT NULL DEFAULT '0',
	`dateline` int(10) NOT NULL DEFAULT '0',
	`newreplies` smallint(6) NOT NULL DEFAULT '0',
	PRIMARY KEY (tid, uid)
) TYPE=MYISAM;

#版块关注;
DROP TABLE IF EXISTS cdb_favoriteforums;
CREATE TABLE cdb_favoriteforums (
	`fid` smallint(6) NOT NULL DEFAULT '0',
	`uid` mediumint(8) NOT NULL DEFAULT '0',
	`dateline` int(10) NOT NULL DEFAULT '0',
	`newthreads` mediumint(8) NOT NULL DEFAULT '0',
	PRIMARY KEY (fid, uid)
) TYPE=MYISAM;

#消息通知提示系统;
DROP TABLE IF EXISTS cdb_prompt;
CREATE TABLE cdb_prompt (
  `uid` MEDIUMINT(8) UNSIGNED NOT NULL,
  `typeid` SMALLINT(6) UNSIGNED NOT NULL,
  `number` SMALLINT(6) UNSIGNED NOT NULL,
  PRIMARY KEY (`uid`, `typeid`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS cdb_prompttype;
CREATE TABLE cdb_prompttype (
  `id` SMALLINT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `script` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) TYPE=MyISAM;
DROP TABLE IF EXISTS cdb_promptmsgs;
CREATE TABLE cdb_promptmsgs (
  `id` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `typeid` SMALLINT(6) UNSIGNED NOT NULL DEFAULT '0',
  `uid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
  `extraid` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0',
  `new` TINYINT(1) NOT NULL DEFAULT '0',
  `dateline` INTEGER(10) UNSIGNED NOT NULL DEFAULT '0',
  `message` TEXT NOT NULL,
  `actor` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`, `typeid`),
  KEY `new` (`new`),
  KEY `dateline` (`dateline`),
  KEY `extraid` (`extraid`)
) TYPE=MyISAM;

#dz内部feed;
DROP TABLE IF EXISTS cdb_feeds;
CREATE TABLE cdb_feeds (
  feed_id mediumint(8) unsigned NOT NULL auto_increment,
  type varchar(255) NOT NULL DEFAULT 'default',
  fid smallint(6) unsigned NOT NULL DEFAULT '0',
  typeid smallint(6) unsigned NOT NULL DEFAULT '0',
  sortid smallint(6) unsigned NOT NULL DEFAULT '0',
  appid varchar(30) NOT NULL DEFAULT '',
  uid mediumint(8) unsigned NOT NULL DEFAULT '0',
  username varchar(15) NOT NULL DEFAULT '',
  data text NOT NULL DEFAULT '',
  template text NOT NULL DEFAULT '',
  dateline int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (feed_id),
  KEY type(type),
  KEY dateline (dateline),
  KEY uid(uid),
  KEY appid(appid)
) TYPE=MyISAM;

#评价;
DROP TABLE IF EXISTS cdb_memberrecommend;
CREATE TABLE cdb_memberrecommend (
  `tid` mediumint(8) unsigned NOT NULL,
  `recommenduid` mediumint(8) unsigned NOT NULL,
  `dateline` int(10) unsigned NOT NULL,
  KEY `tid` (`tid`),
  KEY `uid` (`recommenduid`)
) TYPE=MyISAM;

#扩展中心;
DROP TABLE IF EXISTS cdb_addons;
CREATE TABLE cdb_addons (
  `key` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `sitename` varchar(255) NOT NULL DEFAULT '',
  `siteurl` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `contact` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `system` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`)
) TYPE=MyISAM;

EOT;

$upgradetable = array(

	#用户组的每天最大附件数量限制
	array('usergroups', 'ADD', 'maxattachnum', "smallint( 6 ) NOT NULL DEFAULT '0'"),

	#词语过滤增强
	array('words', 'ADD', 'extra', "varchar(255) NOT NULL DEFAULT ''"),

	#商品主题用积分购买
	array('trades', 'ADD', 'credit', "int(10) unsigned NOT NULL DEFAULT '0'"),
	array('trades', 'ADD', 'costcredit', "int(10) unsigned NOT NULL DEFAULT '0'"),
	array('trades', 'ADD', 'credittradesum', "int(10) unsigned NOT NULL DEFAULT '0'"),
	array('trades', 'INDEX', '', "ADD INDEX(`credittradesum`)"),
	array('tradelog', 'ADD', 'credit', "int(10) unsigned NOT NULL DEFAULT '0'"),
	array('tradelog', 'ADD', 'basecredit', "int(10) unsigned NOT NULL DEFAULT '0'"),

	#特殊主题插件
	array('forumfields', 'ADD', 'threadplugin', "text NOT NULL"),

	#是否新手任务  0 不是   1 针对新注册用户的任务   2 针对不熟悉新功能会员的新手任务
	array('tasks', 'ADD', 'newbietask', "TINYINT(1) NOT NULL DEFAULT '0' AFTER relatedtaskid"),

	#当前正在进行的新手任务
	array('members', 'ADD', 'newbietaskid', "smallint(6) unsigned NOT NULL DEFAULT '0'"),

	#任务显示顺序可以是负数
	array('tasks', 'CHANGE', 'displayorder', "`displayorder` SMALLINT(6) NOT NULL DEFAULT '0'"),
	array('taskvars', 'CHANGE', 'sort', "`sort` enum('apply','complete','setting') NOT NULL DEFAULT 'complete'"),

	#isimage可以是负数 通过附件上传的图片
	array('attachments', 'CHANGE', 'isimage', "`isimage` TINYINT( 1 ) NOT NULL DEFAULT '0'"),

	#插件版本
	array('plugins', 'ADD', 'version', "varchar(20) NOT NULL default ''"),

	#用户主题数
	array('members', 'ADD', 'threads', "MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0' AFTER `posts`"),

	#主题推荐（暂定）
	array('forumrecommend', 'ADD', 'typeid', "SMALLINT(6) NOT NULL AFTER `tid`"),
	array('forumrecommend', 'ADD', 'position', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('forumrecommend', 'ADD', 'highlight', "tinyint(1) NULL NOT NULL DEFAULT '0'"),
	array('forumrecommend', 'ADD', 'aid', "mediumint(8) unsigned NOT NULL DEFAULT '0'"),
	array('forumrecommend', 'ADD', 'filename', "CHAR(100) NOT NULL DEFAULT ''"),
	array('forumrecommend', 'INDEX', '', "ADD INDEX (`position`)"),
	array('threads', 'ADD', 'recommends', "SMALLINT(6) NOT NULL"),
	array('threads', 'ADD', 'recommend_add', "SMALLINT(6) NOT NULL"),
	array('threads', 'ADD', 'recommend_sub', "SMALLINT(6) NOT NULL"),
	array('threads', 'INDEX', '', "ADD INDEX (`recommends`)"),

	#用户组发表URL控制
	array('usergroups', 'ADD', 'allowposturl', "TINYINT(1) NOT NULL DEFAULT '3'"),

	#用户评价主题
	array('usergroups', 'ADD', 'allowrecommend', "TINYINT(1) unsigned NOT NULL DEFAULT '1'"),
	array('usergroups', 'DROP', 'allowavatar', ""),

	#删除订阅相关
	array('threads', 'DROP', 'subscribed', ""),

	#删除video相关功能
	array('usergroups', 'DROP', 'allowpostvideo', ""),

	#道具功能改进
	array('magics', 'ADD', 'recommend', "TINYINT(1) NOT NULL AFTER `weight`"),

	#删除内置的bbcodes
	array('bbcodes', 'DROP', 'type', ""),

	#帖子热度
	array('threads', 'ADD', 'heats', "INT(10) unsigned NOT NULL DEFAULT '0'"),
	array('threads', 'INDEX', '', "ADD INDEX (`heats`)"),

	#去除 mythreads myposts
	array('threads', 'INDEX', '', "ADD INDEX (`authorid`)"),

	# 分离cdb_attachments.description
	array('attachments', 'DROP', 'description', ""),
	array('attachmentfields', 'INDEX', '', "ADD INDEX (`tid`)"),
	array('attachmentfields', 'INDEX', '', "ADD INDEX (`pid`)"),
	array('attachmentfields', 'INDEX', '', "ADD INDEX (`uid`)"),

	#数据调用扩展
	array('request', 'ADD', 'system', "tinyint(1) NOT NULL DEFAULT '0'"),
);

$upgrade3 = <<<EOT
#特殊主题插件;
REPLACE INTO cdb_settings (variable, value) VALUES ('allowthreadplugin', '');

#编辑器改进;
DELETE FROM cdb_bbcodes WHERE `id` = 13 ;
UPDATE cdb_bbcodes SET `tag` = 'p', `icon` = 'cmd_paragraph', `explanation` = '段落' WHERE `id` =5 ;

#新手任务列表;
UPDATE cdb_settings SET variable='newbietasks' WHERE variable='newbietask';

#新手任务更新时间;
REPLACE INTO cdb_settings (variable, value) VALUES ('newbietaskupdate', '');

#消息系统类型;
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (1,'pm','私人消息','pm.php?filter=newpm');
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (2,'announcepm','公共消息','pm.php?filter=announcepm');
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (3,'task','论坛任务','task.php?item=doing');
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (4,'systempm','系统消息','');
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (5,'friend','好友消息','');
REPLACE INTO cdb_prompttype (`id`, `key`, `name`, `script`) VALUES (6,'threads','帖子消息','');

#JS文件缓存;
REPLACE INTO cdb_settings (variable, value) VALUES ('jspath', 'forumdata/cache/');

#支付宝签约用户兼容;
REPLACE INTO cdb_settings (variable, value) VALUES ('ec_contract', '');

#财付通;
REPLACE INTO cdb_settings (`variable`, `value`) VALUES ('ec_tenpay_bargainor', '');
REPLACE INTO cdb_settings (`variable`, `value`) VALUES ('ec_tenpay_key', '');

#用户推荐主题;
REPLACE INTO cdb_settings (`variable`, `value`) VALUES ('recommendthread', '');

#删除Insenz相关功能;
DELETE FROM cdb_settings WHERE variable='insenz';
DROP TABLE IF EXISTS cdb_advcaches;
DROP TABLE IF EXISTS cdb_virtualforums;
DROP TABLE IF EXISTS cdb_campaigns;

#删除订阅相关功能
DELETE FROM cdb_settings WHERE variable='maxsubscriptions';
DROP TABLE IF EXISTS cdb_subscriptions;

#删除video相关功能;
DELETE FROM cdb_settings WHERE variable='videoinfo';
DROP TABLE IF EXISTS cdb_videos;
DROP TABLE IF EXISTS cdb_videotags;

#删除内置的bbcodes;
DELETE FROM cdb_bbcodes WHERE tag='flash';

#浮动窗口开关改进;
REPLACE INTO cdb_settings (variable, value) VALUES ('allowfloatwin', 'a:17:{i:0;s:5:"login";i:1;s:8:"register";i:2;s:6:"sendpm";i:3;s:9:"newthread";i:4;s:5:"reply";i:5;s:9:"attachpay";i:6;s:3:"pay";i:7;s:11:"viewratings";i:8;s:11:"viewwarning";i:9;s:13:"viewthreadmod";i:10;s:8:"viewvote";i:11;s:10:"tradeorder";i:12;s:8:"activity";i:13;s:6:"debate";i:14;s:3:"nav";i:15;s:10:"usergroups";i:16;s:4:"task";}');

#首页显示风格;
REPLACE INTO cdb_settings (variable, value) VALUES ('indextype', 'classics');

#帖子热度;
REPLACE INTO cdb_settings (variable, value) VALUES ('heatthread', 'a:3:{s:5:"reply";i:5;s:9:"recommend";i:3;s:8:"hottopic";s:10:"50,100,200";}');

#去除 mythreads myposts 留到下一个版本去做;
DELETE FROM cdb_settings WHERE variable='myrecorddays';

#增加发送论坛内部feed的设置;
REPLACE INTO cdb_settings (variable, value) VALUES ('dzfeed_limit', 'a:9:{s:14:"thread_replies";a:4:{i:0;s:3:"100";i:1;s:4:"1000";i:2;s:4:"2000";i:3;s:5:"10000";}s:12:"thread_views";a:3:{i:0;s:3:"500";i:1;s:4:"5000";i:2;s:5:"10000";}s:11:"thread_rate";a:3:{i:0;s:2:"50";i:1;s:3:"200";i:2;s:3:"500";}s:9:"post_rate";a:3:{i:0;s:2:"20";i:1;s:3:"100";i:2;s:3:"300";}s:14:"user_usergroup";a:4:{i:0;s:2:"12";i:1;s:2:"13";i:2;s:2:"14";i:3;s:2:"15";}s:11:"user_credit";a:3:{i:0;s:4:"1000";i:1;s:5:"10000";i:2;s:6:"100000";}s:12:"user_threads";a:5:{i:0;s:3:"100";i:1;s:3:"500";i:2;s:4:"1000";i:3;s:4:"5000";i:4;s:5:"10000";}s:10:"user_posts";a:4:{i:0;s:3:"500";i:1;s:4:"1000";i:2;s:4:"5000";i:3;s:5:"10000";}s:11:"user_digest";a:4:{i:0;s:2:"50";i:1;s:3:"100";i:2;s:3:"500";i:3;s:4:"1000";}}');

#首页热点;
REPLACE INTO cdb_settings (variable, value) VALUES ('indexhot', '');

#浮窗改进;
DELETE FROM cdb_settings WHERE variable='allowfloatwin';
REPLACE INTO cdb_settings (variable, value) VALUES ('disallowfloat', '');

#扩展中心默认的资源提供商;
TRUNCATE TABLE cdb_addons;
INSERT INTO cdb_addons (`key`, `title`, `sitename`, `siteurl`, `description`, `contact`, `logo`, `system`) VALUES ('25z5wh0o00', 'Comsenz', 'Comsenz官方网站', 'http://www.comsenz.com', 'Comsenz官方网站推荐的论坛模板与插件', 'ts@comsenz.com', 'http://www.comsenz.com/addon/logo.gif', 1);
INSERT INTO cdb_addons (`key`, `title`, `sitename`, `siteurl`, `description`, `contact`, `logo`, `system`) VALUES ('R051uc9D1i', 'DPS', 'DPS 插件中心', 'http://bbs.7dps.com', '提供 Discuz!7.1 新核(NC)插件，享受一键安装/升级/卸载带来的快感，还提供少量风格。', 'http://bbs.7dps.com/thread-1646-1-1.html', 'http://api.7dps.com/addons/logo.gif', 0);

#删除回复通知的计划任务;
DELETE FROM cdb_crons WHERE filename='notify_daily.inc.php';

#发帖域名白名单;
REPLACE INTO cdb_settings (variable, value) VALUES ('domainwhitelist', '');

EOT;

$newfunc = getgpc('newfunc');
$newfunc = empty($newfunc) ? 0 : $newfunc;
$step = getgpc('step');
$step = empty($step) ? 1 : $step;
instheader();
if(!isset($cookiepre)) {
	instmsg('config_nonexistence');
} elseif(!ini_get('short_open_tag')) {
	instmsg('short_open_tag_invalid');
}

if(file_exists($lock_file)) {
	instmsg('升级被锁定，应该是已经升级过了，如果已经恢复数据请手动删除<br />'.str_replace(DISCUZ_ROOT, '', $lock_file).'<br />之后再来刷新页面。');
}

if($step == 1) {

	$msg = '<div class="btnbox marginbot">
			<form method="get">
			<input type="hidden" name="step" value="check" />
				<input type="submit" style="padding: 2px;" value="开始升级" name="submit" />
			</form>
		</div>';

echo <<<EOT
		<div class="licenseblock">
		<div class="license">
	<h1>本升级程序只能从 $version[old] 升级到 $version[new]</h1>
	升级之前<b>务必备份数据库资料</b>，否则升级失败无法恢复<br /><br />
		正确的升级方法为:
	<ol>
		<li>关闭原有论坛，上传 $version[new] 的全部文件和目录（除install目录和config.inc.php文件），覆盖服务器上的 $version[old]
		<li>上传升级程序到论坛目录中。
		<li>运行本程序，直到出现升级完成的提示
		<li>如果中途失败，请使用Discuz!工具箱（./utilities/tools.php）里面的数据恢复工具恢复备份，去除错误后重新运行本程序
	</ol>
</div></div>
	$msg

EOT;

	instfooter();

} elseif($step == 'check') {

	@touch(DISCUZ_ROOT.'./forumdata/install.lock');
	@unlink(DISCUZ_ROOT.'./install/index.php');

//	echo "<h4>Discuz!程序版本检测</h4>";

	if(!defined('UC_CONNECT')) {
		instmsg('您的config.inc.php文件被覆盖，请恢复备份好的config.inc.php文件，之后再尝试升级。');
	}

	include_once DISCUZ_ROOT.'./discuz_version.php';
	if(!defined('DISCUZ_VERSION') || DISCUZ_VERSION != '7.1') {
		instmsg('您还没有上传(或者上传不完全)最新的Discuz!7.1的程序文件，请先上传之后再尝试升级。');
	}

	instmsg("Discuz!程序版本检测通过，自动执行下一步。", '?step=2');

} elseif($step == 2) {

//	echo "<h4>新增数据表</h4>";

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');

	runquery($upgrade1);

	instmsg("新增数据表处理完毕。", '?step=3');
	instfooter();

} elseif($step == 3) {

	//echo "<h4>转移附件描述</h4>";

	upg_attach_description();

	$recommendthread = array (
		'status' => '1',
		'addtext' => '支持',
		'subtracttext' => '反对',
		'defaultshow' => '0',
		'daycount' => '0',
		'ownthread' => '1',
		'iconlevels' => '10,50,100',
	);
	$db->query("REPLACE INTO {$tablepre}settings (`variable`, `value`) VALUES ('recommendthread', '".addslashes(serialize($recommendthread))."')");

	$db->query("DELETE FROM {$tablepre}bbcodes WHERE type='1'", "SILENT");

	instmsg("转移附件描述成功。", '?step=4');

	instfooter();

} elseif($step == 4) {

	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	//echo "<h4></h4>";

	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

		//echo "升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
		$successed = upgradetable($upgradetable[$start]);

		if($successed === TRUE) {
			$start ++;
			if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
				instmsg("升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:<span class='w'>OK</span>", "?step=4&start=$start");
			}
		} elseif($successed === FALSE) {
			instmsg("调整数据表结构失败：{$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}");
		} elseif($successed == 'TABLE NOT EXISTS') {
			instmsg("<span class=red>数据表：{$tablepre}{$upgradetable[$start][0]}不存在，升级无法继续，请确认您的论坛版本是否正确!</span>");
		}
	}

	instmsg("论坛数据表结构调整完毕。", "?step=5");
	instfooter();

} elseif($step == 5) {

//	echo "<h4>更新部分数据</h4>";
	runquery($upgrade3);
	upg_newbietask();

	@include_once DISCUZ_ROOT.'./forumdata/cache/cache_settings.php';
	$timestamp = time();
	$data = array('title' => array(
		'bbname' => $_DCACHE['settings']['bbname'],
		'time' => gmdate($_DCACHE['settings']['dateformat'], $timestamp + $_DCACHE['settings']['timeoffset'] * 3600),
		'version' => $version['new'],
		)
	);
	$template = array('title' => '{bbname} 于 {time} 升级到 {version}');
	$db->query("INSERT INTO {$tablepre}feeds (type, fid, typeid, sortid, appid, uid, username, data, template, dateline)
		VALUES ('feed_announce', '0', '0', '0', '0', '0', '', '".addslashes(serialize($data))."', '".addslashes(serialize($template))."', '$timestamp')");

	instmsg("部分数据更新完毕。", "?step=6");
	instfooter();
} elseif($step == 6) {
	if(getgpc('addfounder_contact','P')) {
		$email = strip_tags(getgpc('email', 'P'));
		$msn = strip_tags(getgpc('msn', 'P'));
		$qq = strip_tags(getgpc('qq', 'P'));
		if(!preg_match("/^[\d]+$/", $qq)) $qq = '';
		if(strlen($email) < 6 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) $email = '';
		if(strlen($msn) < 6 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $msn)) $msn = '';

		$contact = serialize(array('qq' => $qq, 'msn' => $msn, 'email' => $email));
		$db->query("REPLACE {$tablepre}settings (variable, value) VALUES ('founder_contact', '$contact')");
		instmsg("进入新功能提示。","?step=7");
	} else {
		$contact = array();
		$contact = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='founder_contact'"));
		$founder_contact = str_replace(array("\n","\t"), array('<br>','&nbsp;&nbsp;&nbsp;&nbsp;'), $founder_contact);
echo <<<EOD
 		<div class="licenseblock">
		<div class="license">
	<h1>关于《康盛改善计划》的说明</h1>
	<ol>
		<li>为了不断改进产品质量，改善用户体验，Discuz!7.1版内置了统计系统。</li>
		<li>该统计系统有利于我们分析用户在论坛的操作习惯，进而帮助我们在未来的版本中对产品进行改进，设计出更符合用户需求的新功能。</li>
		<li>该统计系统不会收集站点敏感信息，不收集用户资料，不存在安全风险，并且经过实际测试不会影响论坛的运行效率。</li>
		<li>您安装使用本版本表示您同意加入《康盛改善计划》，Discuz!运营部门会通过对站点数据的分析为您提供运营指导建议，我们将提示您如何根据站点运行情况开启论坛功能，如何进行合理的功能配置，以及提供其他的一些运营经验等。</li>
		<li>为了方便我们和您沟通运营策略，请您留下常用的网络联系方式。</li>
	</ol>
</div></div>
<div class="desc">
	<h4>填写联系方式</h4>
	<p>正确的联系方式有助于我们给你提供最新的信息和安全方面的报告</p>
</div>

<form action="$url_forward" method="post" id="postform">
	<table class="tb2">
		<tr>
			<th class="tbopt">QQ：</th>
			<td><input type="text" value="$contact[qq]" name="qq" size="35" class="txt" /></td>
			<td>请正确填写QQ号码</td>
		</tr>
		<tr>

			<th class="tbopt">MSN：</th>
			<td><input type="text" value="$contact[msn]" name="msn" size="35" class="txt" /></td>
			<td>MSN账号</td>
		</tr>
		<tr>
			<th class="tbopt">E-mail：</th>
			<td><input type="text" value="$contact[email]" name="email" size="35" class="txt" /></td>

			<td>邮箱地址</td>
		</tr>
		<tr>
			<th class="tbopt"></th>
			<td><input type="submit" class="btn" name="addfounder_contact" value="下一步" /> &nbsp; &nbsp;<a href='?step=7'>跳过</a>
			<td></td>
		</tr>
	</table>
</form>

EOD;

	}

} elseif($step == 7) {
	if($newfunction_set[$newfunc]) {
		$newfunction_set[$newfunc] = str_replace(array("\n","\t"), array('<br>','&nbsp;&nbsp;&nbsp;&nbsp;'), $newfunction_set[$newfunc]);
$msg = $newfunction_set[$newfunc];
$nextfunc = $newfunc + 1;
$newfunction_set_count = count($newfunction_set);
echo <<<EOD
	<div class="licenseblock">
		<div class="license">
			<h1>重要新功能设置</h1>
			$msg
		</div>
	</div>
	<div class="btnbox marginbot">
		<form method="get">
			<input type="hidden" name="step" value="7" />
			<input type="hidden" name="newfunc" value="$nextfunc" />
			共【{$newfunction_set_count}】个重要新功能，第【{$nextfunc}】个。
			<input type="submit" style="padding: 2px;" value="继续下一个功能" name="submit" />&nbsp;<a href='?step=8'>跳过</a>
		</form>
	</div>
EOD;
	} else {
		instmsg("新功能查看完毕。", "?step=8");
	}

	instfooter();
} else {

	$settings = array();
	$query = $db->query("SELECT value, variable FROM {$tablepre}settings WHERE variable IN('statid', 'statkey', 'bbname')");
	while($row = $db->fetch_array($query)) {
		$settings[$row['variable']] = $row['value'];
	}
	getstatinfo($settings['statid'], $settings['statkey']);

	dir_clear('./forumdata/cache');
	dir_clear('./forumdata/templates');
	dir_clear('./uc_client/data/cache');
	@touch($lock_file);
	if(!@unlink('upgrade12.php')) {
		$msg = '<li><b>必删除本程序</b></li>';
	} else {
		$msg = '';
	}
echo <<<EOT
		<div class="licenseblock">
		<div class="license">
	<h1>恭喜您论坛数据升级成功</h1>
	<h3>接下来请您：</h3>
	<ol>
		$msg
		<li>使用管理员身份登录论坛，进入后台，更新缓存</li>
		<li>进行论坛注册、登录、发贴等常规测试，看看运行是否正常</li>
	</ol>
</div></div>

EOT;
echo '<div class="btnbox marginbot">
			<form method="get" action="index.php">
				<b>感谢您选用我们的产品！</b><input type="submit" style="padding: 2px;" value="您现在可以访问论坛，查看升级情况" name="submit" />
			</form>
		</div><iframe width="0" height="0" src="index.php" style="display:none;"></iframe>';
		
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Discuz! 升级向导</title>
<style type="text/css">
/*
(C) 2001-2009 Comsenz Inc.
*/

/* common */
*{ word-wrap:break-word; }
body{ padding:5px 0; background:#FFF; text-align:center; }
body, td, input, textarea, select, button{ color:#666; font:12px Verdana, Tahoma, Arial, sans-serif; }
ul, dl, dd, p, h1, h2, h3, h4, h5, h6, form, fieldset { margin:0; padding:0; }
h1, h2, h3, h4, h5, h6{ font-size:12px; }
a{ color:#2366A8; text-decoration:none; }
	a:hover { text-decoration:underline; }
	a img{ border:none; }
em, cite, strong, th{ font-style:normal; font-weight:normal; }
table{ border-collapse:collapse; }

/* box */
.container{ overflow:hidden; margin:0 auto; width:700px; height:auto !important;text-align:left; border:1px solid #B5CFD9; }
.header{ height:71px; background:url(images/upgrade/bg_repx.gif) repeat-x; }
	.header h1{ text-indent:-9999px; width:270px; height:48px; background:url(images/upgrade/bg_repno.gif) no-repeat 26px 22px; }
	.header span { float: right; padding-right: 10px; }
.main{ padding:20px 20px 0; background:#F7FBFE url(images/upgrade/bg_repx.gif) repeat-x 0 -194px; }
	.main h3{ margin:10px auto; width:75%; color:#6CA1B4; font-weight:700; }
.desc{ margin:0 auto; width:537px; line-height:180%; clear:both; }
	.desc ul{ margin-left:20px; }
.desc1{ margin:10px 0; width:100%; }
	.desc1 ul{ margin-left:25px; }
	.desc1 li{ margin:3px 0; }
.tb2{ margin:15px 0 15px 67px; }
	.tb2 th, .tb2 td{ padding:3px 5px; }
	.tbopt{ width:120px; text-align: left; }
.btnbox{ text-align:center; }
	.btnbox input{ margin:0 2px; }
	.btnbox textarea{ margin-bottom:10px; height:150px; }
.btn{ margin-top:10px; }
.footer{ line-height:40px; text-align:center; background:url(images/upgrade/bg_footer.gif) repeat-x; font-size:11px; }

/* form */
.txt{ width:200px; }

/* file status */
.w{ margin-left: 8px; padding-left: 16px; background:url(images/upgrade/bg_repno.gif) no-repeat 0 -149px;  }
.nw{ margin-left: 8px; padding-left: 16px; background:url(images/upgrade/bg_repno.gif) no-repeat 0 -198px; }

/* space */
.marginbot{ margin-bottom:20px; }
.margintop{ margin-top:20px; }
.red{ color:red; }

.licenseblock{ margin-bottom:15px; padding:8px; border:1px solid #EEE; background:#FFF; overflow:scroll; overflow-x:hidden; }
.license{}
	.license h1{ padding-bottom:10px; font-size:14px; text-align:center; }
	.license h3{ margin:0; color:#666; }
	.license p{ line-height:150%; margin:10px 0; text-indent:25px; }
	.license li{ line-height:150%; margin:5px 0; }
.title{ margin:5px 0 -15px 58px; }
.showmessage { margin: 0 0 20px; line-height: 160%; }
	.showmessage h2 { margin-bottom: 10px;font-size: 14px; }
	.showmessage .btnbox { margin-top: 20px;}
</style>
<meta name="copyright" content="Comsenz Inc." />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<script type="text/javascript">
function redirect(url) {
	window.location=url;
}
function $(id) {
	return document.getElementById(id);
}
</script>
</head>
<div class="container">
	<div class="header">
		<h1>Discuz! 升级向导</h1>
		<span>从 Discuz! 7.0 升级到 7.1</span>
	</div>
	<div class="main">

<?php
}

function instfooter() {
	global $version;
?>
		<div class="footer">&copy;2001 - 2009 <a href="http://www.comsenz.com/">Comsenz</a> Inc.</div>
	</div>
</div>
</body>
</html>
<?php
}

function instmsg($message, $url_forward = '', $postdata = '') {
	global $lang, $msglang;
	$message = $msglang[$message] ? $msglang[$message] : $message;
	if($url_forward) {
		$message .= "<p><a href=\"$url_forward\">$msglang[redirect_msg]</a></p>";
		$message .= "<script>setTimeout(\"redirect('$url_forward');\", 1250);</script>";
	} elseif(strpos($message, $lang['return'])) {
		$message .= "<p><a href=\"javascript:history.go(-1);\" class=\"mediumtxt\">$lang[message_return]</a></p>";
	}

echo <<<EOD
	<div class="showmessage">
	<h2>{$lang[error_message]}</h2>
	<p>$message</p>
	<!--<div class="btnbox"><input type="button" class="btn" value="按钮文字" /></div>-->
	</div>
EOD;
		
	instfooter();
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

function upg_attach_description() {
	global $db, $tablepre;
	if($db->fetch_first("SHOW TABLE STATUS LIKE '{$tablepre}attachmentfields'")) {
		return;
	}
$create_table_sql = "
CREATE TABLE cdb_attachmentfields (
  aid mediumint(8) UNSIGNED NOT NULL ,
  tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0' ,
  pid int(10) UNSIGNED NOT NULL DEFAULT '0' ,
  uid mediumint(8) UNSIGNED NOT NULL DEFAULT '0' ,
  description varchar(255) NOT NULL ,
  PRIMARY KEY (`aid`),
  KEY tid (tid),
  KEY pid (pid,aid),
  KEY uid (uid)
) TYPE=MyISAM;
";
	runquery($create_table_sql);
	$db->query("INSERT INTO {$tablepre}attachmentfields (`aid`, `tid`, `pid`, `uid`, `description`) SELECT `aid`, `tid`, `pid`, `uid`, `description` FROM {$tablepre}attachments WHERE `description`<>''");
}

function getstatinfo($siteid = 0, $key = '') {
	global $db, $tablepre, $dbcharset, $settings;
	if($siteid && $key) {
		return;
	} else {
		$siteid = $key = '';
	}
	$version = '7.1';
	$onlineip = '';
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$onlineip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$onlineip = $_SERVER['REMOTE_ADDR'];
	}
	$members = $db->result_first("SELECT COUNT(*) FROM {$tablepre}members");
	$funcurl = 'http://stat.discuz.com/stat_ins.php';
	$bbname = $settings['bbname'];
	$PHP_SELF = htmlspecialchars($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
	$url = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))));
	$posts = $db->result($db->query("SELECT count(*) FROM {$tablepre}posts"), 0);
	$domain = $_SERVER['HTTP_HOST'];
	$hash = $bbname.$url.$mark.$version.$posts;
	$threads = $db->result($db->query("SELECT count(*) FROM {$tablepre}threads"), 0);
	$hash = md5($hash.$members.$threads.$email.$siteid.md5($key).'install');
	$q = "bbname=$bbname&url=$url&domain=$domain&mark=$mark&version=$version&posts=$posts&members=$members&threads=$threads&email=$email&siteid=$siteid&key=".md5($key)."&ip=$onlineip&time=".time()."&hash=$hash";
	$q=rawurlencode(base64_encode($q));
	$siteinfo = dfopen($funcurl."?action=install&q=$q");
	if(empty($siteinfo)) {
		$siteinfo = dfopen($funcurl."?action=install&q=$q");
	}
	if($siteinfo && preg_match("/^[a-zA-Z0-9_]+,[A-Z]+$/i", $siteinfo)) {
		$siteinfo = explode(',', $siteinfo);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('statid', '$siteinfo[0]')");
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('statkey', '$siteinfo[1]')");
	}
}

function upg_newbietask() {
	global $db, $tablepre;

	$newbietask = array(
		1 => array(
			'name' => '回帖是一种美德',
			'task' => "1, 0, '回帖是一种美德', '学习回帖，看帖回帖是一种美德，BS看帖不回帖的', '', 0, 0, 0, 'all', 'newbie_post_reply', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '回复指定主题', '".addslashes('设置会员只有回复该主题才能完成任务，请填写主题的 tid(比如一个主题的地址是 http://localhost/viewthread.php?tid=8 那么该主题的 tid 就是 8)，留空为不限制')."', 'threadid', 'text', '0', ''",
				"'setting', '', '', 'entrance', 'text', 'viewthread', ''"
			)
		),
		2 => array(
			'name' => '我的第一次',
			'task' => "1, 0, '我的第一次', '学会发主题帖，成为社区的焦点', '', 0, 0, 0, 'all', 'newbie_post_newthread', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '在指定版块发表新主题', '".addslashes('设置会员必须在某个版块发表至少一篇新主题才能完成任务')."', 'forumid', 'text', '', ''",
	
				"'setting', '', '', 'entrance', 'text', 'forumdisplay', ''"
			)
		),
		3 => array(
			'name' => '与众不同',
			'task' => "1, 0, '与众不同', '修改个人资料，让你和别人与众不同', '', 0, 0, 0, 'all', 'newbie_modifyprofile', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '完善个人资料', '".addslashes('申请任务后只要把自己的个人资料填写完整即可完成任务')."', '', '', '', ''",
				"'setting', '', '', 'entrance', 'text', 'memcp', ''"
			)
		),
		4 => array(
			'name' => '我型我秀',
			'task' => "1, 0, '我型我秀', '上传头像，让大家认识一个全新的你', '', 0, 0, 0, 'all', 'newbie_uploadavatar', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '上传头像', '".addslashes('申请任务后只要成功上传头像即可完成任务')."', '', '', '', ''",
				"'setting', '', '', 'entrance', 'text', 'memcp', ''"
			)
		),
		5 => array(
			'name' => '联络感情',
			'task' => "1, 0, '联络感情', '给其他用户发个发短消息，大家联络一下感情', '', 0, 0, 0, 'all', 'newbie_sendpm', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '给指定会员发送短消息', '".addslashes('只有给该会员成功发送短消息才能完成任务，请填写该会员的用户名')."', 'authorid', 'text', '', ''",
				"'setting', '', '', 'entrance', 'text', 'space', ''"
			)
		),
		6 => array(
			'name' => '一个好汉三个帮',
			'task' => "1, 0, '一个好汉三个帮', '出来混的，没几个好友怎么行，加个好友吧', '', 0, 0, 0, 'all', 'newbie_addbuddy', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '将指定会员加为好友', '".addslashes('只有将该会员加为好友才能完成任务，请填写该会员的用户名')."', 'authorid', 'text', '', ''",
				"'setting', '', '', 'entrance', 'text', 'space', ''"
			)
		),
		7 => array(
			'name' => '信息时代',
			'task' => "1, 0, '信息时代', '信息时代最缺的什么？搜索', '', 0, 0, 0, 'all', 'newbie_search', 0, 0, 0, 'credit', '2', 10, -1, ''",
			'vars' => array(
				"'complete', '学会搜索', '".addslashes('申请任务后只要成功使用论坛搜索功能即可完成任务')."', '', '', '', ''",
				"'setting', '', '', 'entrance', 'text', 'search', ''"
			)
		)
	);
	if($db->result($db->query("SELECT count(*) FROM `{$tablepre}tasks` WHERE newbietask=1"), 0)) {
		return;
	}
	foreach($newbietask as $k => $sqlarray) {
		$db->query("INSERT INTO `{$tablepre}tasks` (`newbietask`, `available`, `name`, `description`, `icon`, `applicants`, `achievers`, `tasklimits`, `applyperm`, `scriptname`, `starttime`, `endtime`, `period`, `reward`, `prize`, `bonus`, `displayorder`, `version`) VALUES ($sqlarray[task]);");
		$currentid = $db->insert_id();
		foreach($sqlarray['vars'] as $taskvars) {
			$db->query("INSERT INTO `{$tablepre}taskvars` (`taskid`, `sort`, `name`, `description`, `variable`, `type`, `value`, `extra`) VALUES ($currentid, $taskvars);");
		}
	}
}

function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE) {
	$return = '';
	$matches = parse_url($url);
	$host = $matches['host'];
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
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
?>