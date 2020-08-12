<?php

/*
	[DISCUZ!] utilities/repair.php - generally repair of database
	This is NOT a freeware, use is subject to license terms

	Version: 2.0.0
	Author: Crossday (info@discuz.net)
	Copyright: Crossday Studio (www.crossday.com)
	Last Modified: 2002/12/6 17:00
*/

error_reporting(7);
require './config.inc.php';

mysql_connect($dbhost, $dbuser, $dbpw);
mysql_select_db($dbname);

if(!get_cfg_var("register_globals")) {
	foreach($HTTP_GET_VARS as $key => $val) {
		$$key = $val;
	}
}

function checktable($table, $loops = 0) {
  global $db, $nohtml;

   $result = mysql_query("CHECK TABLE $table");
   if(!$nohtml) {
     echo "<tr bgcolor='#CCCCCC'><td colspan=4 align='center'>检查表 $table</td></tr>";
     echo "<tr><td>表</td><td>操作</td><td>类型</td><td>信息</td></tr>";
   } else {
     echo "\n>>>>>>>>>>>>>检查表 $table\n";
     echo "---------------------------------<br>\n";
   }
   $error = 0;
   while($r = mysql_fetch_row($result)) {
     if($r[2] == 'error') {
       if($r[3] == "The handler for the table doesn't support check/repair") {
         $r[2] = 'status';
         $r[3] = '此表不支持检查/修复/优化';
         unset($bgcolor);
         $nooptimize = 1;
       } else {
         $error = 1;
         $bgcolor = 'red';
         unset($nooptimize);
       }
     } else {
       unset($bgcolor);
       unset($nooptimize);
     }
     if(!$nohtml) {
       echo "<tr><td>$r[0]</td><td>$r[1]</td><td bgcolor='$bgcolor'>$r[2]</td><td>$r[3]</td></tr>";
     } else {
       echo "$r[0] | $r[1] | $r[2] | $r[3]<br>\n";
     }
   }
   if($error) {
     if(!$nohtml) {
       echo "<tr><td colspan=4 align='center'>修复表 $table</td></tr>";
     } else {
       echo ">>>>>>>>>>>>>修复表 $table<br>\n";
     }
     $result2=mysql_query("REPAIR TABLE $table");
     if($result2[3]!='OK')
       $bgcolor='red';
     else
       unset($bgcolor);
     if(!$nohtml) {
       echo "<tr><td>$result2[0]</td><td>$result2[1]</td><td>$result2[2]</td><td bgcolor='$bgcolor'>$result2[3]</td></tr>";
     } else {
       echo "$result2[0] | $result[1] | $result2[2] | $result2[3]<br>\n";
     }
   }
   if(($result2[3]=='OK'||!$error)&&!$nooptimize) {
     if(!$nohtml) {
       echo "<tr><td colspan=4 align='center'>优化表 $table</td></tr>";
     } else {
       echo ">>>>>>>>>>>>>优化表 $table<br>\n";
     }
     $result3=mysql_query("OPTIMIZE TABLE $table");
     $error=0;
     while($r3=mysql_fetch_row($result3)) {
       if($r3[2]=='error') {
         $error=1;
         $bgcolor='red';
       } else {
         unset($bgcolor);
       }
       if(!$nohtml) {
         echo "<tr><td>$r3[0]</td><td>$r3[1]</td><td bgcolor='$bgcolor'>$r3[2]</td><td>$r3[3]</td></tr>";
       } else {
         echo "$r3[0] | $r3[1] | $r3[2] | $r3[3]<br><br>\n";
       }
     }
   }
   if($error&&$loops) {
     checktable($table,($loops-1));
   }
}

if($check) {

  $tables=mysql_query("SHOW TABLES");

  if(!$nohtml) {
    echo "<HTML><HEAD></HEAD><BODY><table border=1 cellspacing=0 cellpadding=4 STYLE=\"font-family: Tahoma, Verdana; font-size: 11px\">";
  }

  if($iterations) {
    $iterations--;
  }
  while($table=mysql_fetch_row($tables)) {

     if(substr($table[0], -8) != 'sessions') {
       $answer=checktable($table[0],$iterations);
       if(!$nohtml) {
         echo "<tr><td colspan=4>&nbsp;</td></tr>";
       }
       flush();
     }
  }

  if(!$nohtml) {
    echo "</table></BODY></HTML>";
  }
} else {
  echo "<HTML><HEAD></HEAD><BODY STYLE=\"font-family: Tahoma, Verdana; font-size: 11px\"><b>Discuz!数据库修复工具 (MySQL 版本要求：3.23+)</b><br><br>".
       "本程序将尝试修复损坏的数据库，它能修复绝大部分常见错误。<br>".
       "同时，它还将对您的数据表进行优化。<br>".
       "This script was copyrighted by Jelsoft and modified by Crossday Studio to make it apply for Discuz!.<br><br>".
       "用法：<br><br>".
       "<b>iterations=x</b> (x 表示您希望本程序尝试修复损坏数据表的次数)<br>".
       "<b>nohtml=1</b> (仅输出纯文本信息，常用于计划任务，只希望收到纯文本信息)<br>".
       "<b>check=1</b> (对论坛数据库进行检测，没有此项您将停留在当前页面)<br><br>".
       "用法举例：<br><br>".
       "<b><a href=\"repair.php?check=1&iterations=5\">repair.php?html=1&check=1</a></b> (最常见用法，将检查论坛数据库并以 HTML 方式返回检查结果)<br>".
       "<b><a href=\"repair.php?check=1&iterations=5\">repair.php?check=1&iterations=5</a></b> (尝试修复损坏数据表 5 次)".
       "</BODY></HTML>";
}
?>
