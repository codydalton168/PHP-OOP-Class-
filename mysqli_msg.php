<?php
!function_exists('checkpathdir') && exit('Forbidden');

Class DBERROR {
	function DBERROR($debugmsg) {
		global $default_name,$onlineip,$sql,$default_obstart,$default_host,$REQUEST_URI,$default_charset;

		include_once security::escapePath(DIR.'require/sql_error_msg.php');


		/*
		*  判斷 IP  是否顯示資料詳細錯誤訊息
		*
		*   列如 一組 IP  114.35.22.14  或  網段 114.35.22
		*/

		$checkerrormsg='0';

		$default_selectip="122.117.14.39,192.168.0,114.35.21.159,192.168.0,127.0.0";
		
		if($default_selectip){
			$baniparray = explode(',', $default_selectip);

			$ip = explode(".",$onlineip);

			if(in_array($ip[0],$baniparray) || in_array($ip[0].'.'.$ip[1],$baniparray) || in_array($ip[0].'.'.$ip[1].'.'.$ip[2],$baniparray) || in_array($ip[0].'.'.$ip[1].'.'.$ip[2].'.'.$ip[3],$baniparray)) {
				$checkerrormsg='1';
			}
			//判斷 IP  是否顯示資料詳細錯誤訊息

		}



		$sqlerror = mysqli_error($sql->sql);

		$sqlerrno = mysqli_errno($sql->sql);

		$sqlerror = str_replace($default_host,'default_host',$sqlerror);
	
		ob_end_clean();

		$default_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		
		echo"<html>\n<head>\n<meta http-equiv='Content-Type' content='text/html; charset=big5' />\n";
		echo"<title>MySQL Server Message</title>\n";
		echo"<style type='text/css'>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:11px;}\n";
		echo"a{ TEXT-DECORATION: none;}a:hover{ text-decoration: underline;}\n";
		echo"table{TABLE-LAYOUT:fixed;WORD-WRAP: break-word}\n";
		echo"td{ BORDER-RIGHT: 1px; BORDER-TOP: 0px; FONT-SIZE: 16pt; COLOR: #000000;}</style><body>\n";
		echo"<table>\n<tr>\n\t<td>";
		echo"<b>SQL Server Error Message</b>:<br /><br />";
		if($checkerrormsg){
			echo "$debugmsg<br /><br />";
		}

		if($sqlerror && $sqlerrno){

			if($checkerrormsg){
				echo"$sqlerror<br /><br />";
			}
			echo"錯誤訊息代碼 : $sqlerrno";

			if($checkerrormsg){

			echo"<br /><br />描述訊息 : <b>".($errorData[$sqlerrno] ? $errorData[$sqlerrno] : '')."</b>";
			}
		}

		echo"\n\t</td>\n</tr>\n</table>\n</body>\n</html>";


		exit;
	}
}
?>