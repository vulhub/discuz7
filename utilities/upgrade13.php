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

$version['old'] = 'Discuz! 7.1 正式版';
$version['new'] = 'Discuz! 7.2';
$version['newnumber'] = '7.2';
$lock_file = DISCUZ_ROOT.'./forumdata/upgrade13.lock';

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
#帖子位置信息存储;
DROP TABLE IF EXISTS cdb_postposition;
CREATE TABLE cdb_postposition (
	`tid` int(10) unsigned NOT NULL,
        `position` int(10) unsigned NOT NULL,
        `pid` int(10) unsigned NOT NULL,
         PRIMARY KEY (`tid`,`position`)
) ENGINE=MyISAM;


# 后台是否在页面上显示帖子评分记录;
REPLACE INTO cdb_settings (variable, value) VALUES ('ratelogon', '0');

#主题列表分割带;
REPLACE INTO cdb_settings (variable, value) VALUES ('forumseparator', '1');

# 附件 URL 即可直接参与媒体播放功能开关;
REPLACE INTO cdb_settings (variable, value) VALUES ('allowattachurl', '0');

# 个人资料页可自由查看他人的帖子;
REPLACE INTO cdb_settings (variable, value) VALUES ('allowviewuserthread', '');

#不受审核限制的 IP 列表;
REPLACE INTO cdb_settings (variable, value) VALUES ('ipverifywhite', '');

EOT;

$upgradetable = array(

	# 编辑帖子时间按照用户组设置
	array('usergroups', 'ADD', 'edittimelimit', "smallint(6) UNSIGNED NOT NULL DEFAULT '0'"),

	# 增加主题状态标示
	array('threads', 'ADD', 'status', "smallint (6)UNSIGNED  DEFAULT '0' NOT NULL"),

	#主题图章
	array('smilies', 'MODIFY', 'type', "ENUM('smiley','icon','stamp') NOT NULL DEFAULT 'smiley'"),
	array('threadsmod', 'ADD', 'stamp', "TINYINT(3) NOT NULL"),

	#是否允许发表抢楼贴
	array('usergroups', 'ADD', 'allowpostrushreply', "TINYINT(1) NOT NULL DEFAULT '0'"),
	# 分类信息加强
	array('typevars', 'ADD', 'subjectshow', "TINYINT(1) NOT NULL DEFAULT '0'"),
	array('threadtypes', 'ADD', 'stemplate', "TEXT NOT NULL"),
	array('typeoptions', 'ADD', 'unit', "VARCHAR(40) NOT NULL AFTER `type`"),
	# 前台权限细化
	array('admingroups', 'ADD', 'allowhighlightthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowdigestthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowrecommendthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowbumpthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowclosethread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowmovethread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowedittypethread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowstampthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowcopythread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowmergethread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowsplitthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowrepairthread', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowwarnpost', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowviewreport', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'alloweditforum', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowremovereward', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'allowedittrade', "tinyint(1) NOT NULL DEFAULT '0'"),
	array('admingroups', 'ADD', 'alloweditactivity', "tinyint(1) NOT NULL DEFAULT '0'"),

	#有关版块的其他信息
	array('forumfields', 'ADD', 'extra', "TEXT NOT NULL DEFAULT ''"),
);

$upgrade3 = <<<EOT
#主题图章;
DELETE FROM cdb_smilies WHERE `typeid` = 0 AND `type` = 'stamp';
INSERT INTO cdb_smilies (`typeid`, `displayorder`, `type`, `code`, `url`) VALUES
  (0, 0, 'stamp', '精华', '001.gif'),
  (0, 1, 'stamp', '热帖', '002.gif'),
  (0, 2, 'stamp', '美图', '003.gif'),
  (0, 3, 'stamp', '优秀', '004.gif'),
  (0, 4, 'stamp', '置顶', '005.gif'),
  (0, 5, 'stamp', '推荐', '006.gif'),
  (0, 6, 'stamp', '原创', '007.gif'),
  (0, 7, 'stamp', '版主推荐', '008.gif'),
  (0, 8, 'stamp', '爆料', '009.gif');

#权限细化;
UPDATE cdb_admingroups SET allowhighlightthread='1',allowdigestthread='3',allowrecommendthread='1',allowbumpthread='1',allowclosethread='1',allowmovethread='1',allowedittypethread='1',allowstampthread='1',allowcopythread='1',allowmergethread='1',allowsplitthread='1',allowrepairthread='1',allowwarnpost='1',allowviewreport='1',alloweditforum='1',allowviewlog='1',allowremovereward='1',allowedittrade='0',alloweditactivity='0';

UPDATE cdb_admingroups SET allowedittrade='1',alloweditactivity='1' WHERE admingid='1';

UPDATE cdb_usergroups SET allowpostrushreply='1' WHERE groupid='1' AND radminid='1';

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
	if(!defined('DISCUZ_VERSION') || DISCUZ_VERSION != $version['newnumber']) {
		instmsg('您还没有上传(或者上传不完全)最新的'.$version['new'].'的程序文件，请先上传之后再尝试升级。');
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

	$start = isset($_GET['start']) ? intval($_GET['start']) : 0;

	//echo "<h4></h4>";

	if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {

		//echo "升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:";
		$successed = upgradetable($upgradetable[$start]);

		if($successed === TRUE) {
			$start ++;
			if(isset($upgradetable[$start]) && $upgradetable[$start][0]) {
				instmsg("升级数据表 [ $start ] {$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}:<span class='w'>OK</span>", "?step=3&start=$start");
			}
		} elseif($successed === FALSE) {
			instmsg("调整数据表结构失败：{$tablepre}{$upgradetable[$start][0]} {$upgradetable[$start][3]}");
		} elseif($successed == 'TABLE NOT EXISTS') {
			instmsg("<span class=red>数据表：{$tablepre}{$upgradetable[$start][0]}不存在，升级无法继续，请确认您的论坛版本是否正确!</span>");
		}
	}

	instmsg("论坛数据表结构调整完毕。", "?step=4");
	instfooter();

} elseif($step == 4) {

//	echo "<h4>更新部分数据</h4>";
	runquery($upgrade3);

	$query = $db->query("SELECT value, variable FROM {$tablepre}settings WHERE variable IN('bbname', 'dateformat', 'timeoffset')");
	while($row = $db->fetch_array($query)) {
		$settings[$row['variable']] = $row['value'];
	}
	$timestamp = time();
	$data = array('title' => array(
		'bbname' => $settings['bbname'],
		'time' => gmdate($settings['dateformat'], $timestamp + $settings['timeoffset'] * 3600),
		'version' => $version['new'],
		)
	);
	$template = array('title' => '{bbname} 于 {time} 升级到 {version}');
	$db->query("INSERT INTO {$tablepre}feeds (type, fid, typeid, sortid, appid, uid, username, data, template, dateline)
		VALUES ('feed_announce', '0', '0', '0', '0', '0', '', '".addslashes(serialize($data))."', '".addslashes(serialize($template))."', '$timestamp')");
	$edittimelimit = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='edittimelimit'");
	$db->query("UPDATE {$tablepre}usergroups SET edittimelimit='$edittimelimit'");
	updatespecial();
	instmsg("部分数据更新完毕。", "?step=5");
	instfooter();
} elseif($step == 5) {
	if(getgpc('addfounder_contact','P')) {
		$email = strip_tags(getgpc('email', 'P'));
		$msn = strip_tags(getgpc('msn', 'P'));
		$qq = strip_tags(getgpc('qq', 'P'));
		if(!preg_match("/^[\d]+$/", $qq)) $qq = '';
		if(strlen($email) < 6 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email)) $email = '';
		if(strlen($msn) < 6 || !preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $msn)) $msn = '';

		$contact = serialize(array('qq' => $qq, 'msn' => $msn, 'email' => $email));
		$db->query("REPLACE {$tablepre}settings (variable, value) VALUES ('founder_contact', '$contact')");
		instmsg("进入下一步。","?step=7");
	} else {
		$contact = array();
		$contact = unserialize($db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='founder_contact'"));
		$founder_contact = str_replace(array("\n","\t"), array('<br>','&nbsp;&nbsp;&nbsp;&nbsp;'), $founder_contact);
echo <<<EOD
 		<div class="licenseblock">
		<div class="license">
	<h1>关于《康盛改善计划》的说明</h1>
	<ol>
		<li>为了不断改进产品质量，改善用户体验，$version[new]版内置了统计系统。</li>
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
			<td><input type="submit" class="btn" name="addfounder_contact" value="下一步" /> &nbsp; &nbsp;<a href='?step=6'>跳过</a>
			<td></td>
		</tr>
	</table>
</form>

EOD;

	}

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
	if(!@unlink('upgrade13.php')) {
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
		<span>从 Discuz! 7.1 升级到 7.2</span>
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

function getstatinfo($siteid = 0, $key = '') {
	global $db, $tablepre, $dbcharset, $settings, $version;
	if($siteid && $key) {
		return;
	} else {
		$siteid = $key = '';
	}
	$versionnumber = $version['newnumber'];
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
	$hash = $bbname.$url.$mark.$versionnumber.$posts;
	$threads = $db->result($db->query("SELECT count(*) FROM {$tablepre}threads"), 0);
	$hash = md5($hash.$members.$threads.$email.$siteid.md5($key).'install');
	$q = "bbname=$bbname&url=$url&domain=$domain&mark=$mark&version=$versionnumber&posts=$posts&members=$members&threads=$threads&email=$email&siteid=$siteid&key=".md5($key)."&ip=$onlineip&time=".time()."&hash=$hash";
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

function updatespecial() {
	global $db, $tablepre, $dbcharset;
	$fieldtypes = array('number' => 'bigint(20)', 'text' => 'mediumtext', 'radio' => 'smallint(6)', 'checkbox' => 'mediumtext', 'textarea' => 'mediumtext', 'select' => 'smallint(6)', 'calendar' => 'mediumtext', 'email' => 'mediumtext', 'url' => 'mediumtext', 'image' => 'mediumtext');

	$optionvalues = array();

	$query = $db->query("SELECT v.*, p.identifier, p.type FROM {$tablepre}typevars v LEFT JOIN {$tablepre}typeoptions p ON p.optionid=v.optionid WHERE search='1' OR p.type IN('radio','select','number')");
	$optionvalues = array();
	while($row = $db->fetch_array($query)) {
		$optionvalues[$row['sortid']][$row['identifier']] = $row['type'];
		$optionids[$row['sortid']][$row['optionid']] = $row['identifier'];
	}
	foreach($optionvalues as $sortid => $options) {
		$keys = array('KEY tid (tid)', 'KEY fid (fid)');
		$tablename = "{$tablepre}optionvalue{$sortid}";
		$db->query("DROP TABLE IF EXISTS $tablename");
		$sql = "CREATE TABLE $tablename (\n tid int(10) NOT NULL DEFAULT '0',\n fid int(10) NOT NULL DEFAULT '0',";
		foreach($options as $fieldname => $type) {
			$unsigned = $default = '';
			if(strstr($fieldtypes[$type], 'int')) {
				$unsigned = 'unsigned';
				$default = 0;
				$keys[] = "KEY `$fieldname` (`$fieldname`)";
			}
			$sql .= "\n`$fieldname` $fieldtypes[$type] $unsigned NOT NULL DEFAULT '$default',";
		}
		if($keys) {
			$sql .= implode(",\n", $keys);
		}
		$sql .= "\n) TYPE=MyISAM;";
		$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
		$db->query($sql);
		$opids = array_keys($optionids[$sortid]);
		$query = $db->query("SELECT t.*, th.fid FROM {$tablepre}typeoptionvars t left join {$tablepre}threads th ON th.tid=t.tid WHERE t.sortid='$sortid' AND t.optionid IN ('".implode("','", $opids)."')");
		$inserts = array();
		while($row = $db->fetch_array($query)) {
			$opname = $optionids[$sortid][$row['optionid']];
			if(empty($inserts[$row[tid]])) {
				$inserts[$row['tid']]['tid'] = $row['tid'];
				$inserts[$row['tid']]['fid'] = $row['fid'];
			}
			$inserts[$row['tid']][$opname] = addslashes($row['value']);
		}
		if($inserts) {
			foreach($inserts as $tid => $fieldval) {
				$ikeys = $ivals = array();
				foreach($fieldval as $ikey => $ival) {
					$ikeys[] = $ikey;
					$ivals[] = $ival;
				}
				$db->query("INSERT INTO $tablename (`".implode("`,`", $ikeys)."`) VALUES ('".implode("','", $ivals)."')");
			}
		}
	}
}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}
?>