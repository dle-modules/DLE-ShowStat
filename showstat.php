<?php
/*=====================================================
ShowStat - Вывод статистики производительности сайта (тестировался на 9.3 - 9.6)
=======================================================
Автор: ПафНутиЙ 
URL: http://pafnuty.name/
ICQ: 817233 
email: pafnuty10@gmail.com
=======================================================
Файл:  showstat.php
-------------------------------------------------------
Версия: 2.2 (16.08.2012)
=======================================================
*/


if( ! defined( 'DATALIFEENGINE' ) ) {
	die( "Hacking attempt!" );
}

if ($user_group[$member_id['user_group']]['allow_admin']) {

	global $config, $Timer, $db, $tpl;


	$size = 40; //максимальный размер файла лога

	$statfile = ROOT_DIR . '/uploads/stat_log.html';
	$dtime = date ('d.m.Y  H:i:s', $_TIME);
	$timer = $Timer->stop();
	$tpl_time = round($tpl->template_parse_time, 5);
	$db_q = $db->query_num;
	$mysql_time = round($db->MySQL_time_taken, 5);

	if ($show_query) {
		$total_time_query = $db->query_list;
		if(is_array($total_time_query)){
			for ($i = 0; $i < count($total_time_query); $i++) { 
			$time_query .= "".$total_time_query[$i][time] > 0.01."" ? "<p><span style='color:red'>".round($total_time_query[$i][time],5)."</span> сек. - [".$total_time_query[$i][query]."]</p>" : "<p><span style='color:green'>".round($total_time_query[$i][time],5)."</span> сек. - [".$total_time_query[$i][query]."]</p>";}
		}
	}

	if(function_exists( "memory_get_peak_usage" )) $mem_usg = round(memory_get_peak_usage()/(1024*1024),2)."Мб";


	if ((file_exists($statfile) && filesize($statfile) > $size*1024) OR $nolog) {
		unlink($statfile);
	}
	if (!$nolog) {
		if (!file_exists($statfile)) {
			$cFile = fopen( $statfile, "wb" );
			$firstText = "
							<!DOCTYPE html><html lang='ru'><head><meta charset='windows-1251'><title>Лог статистики генерации страницы</title></head>
							<style>.stattable{margin: 50px auto;border-collapse:collapse;border:solid 1px #ccc;font:normal 14px Arial,Helvetica,sans-serif;}.stattable th, .stattable td{font-size:12px;border:solid 1px #ccc; padding: 5px;}.stattable tr:hover {background: #f0f0f0; color: #1d1d1d;} b{color:#c00;}p{margin:0 -5px -6px;padding:11px 10px 5px;border-bottom:solid 1px #eee;}</style>
							<body>
							<table width='800' class='stattable'>
							<tr>
								<th scope='col' width='300'>Адрес страницы и запросы в БД (опционально)</th>
								<th scope='col'>Дата</th>
								<th scope='col'>Вемя выполнения скрипта</th>
								<th scope='col'>Время создания шаблона</th>
								<th scope='col'>Кол-во запросов</th>
								<th scope='col'>Время выполнения запросов</th>
								<th scope='col'>Затраты памяти</th>
							</tr>
							\r\n</table></body></html>";
			fwrite( $cFile, $firstText);
			fclose( $cFile );

		} else {
			$cFileArr = file($statfile);
			$lastLine = array_pop($cFileArr);
			$newText = implode("", $cFileArr);

			$newTextAdd = "добавляем строку\r\n";
			$newTextAdd = "	<tr>
								<td><a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."' title='Перейти на страницу' target='_blank'>".$_SERVER['REQUEST_URI']."</a> <br />".$time_query."</td>
								<td>$dtime</td>
								<td><b>".$timer."с</b></td>
								<td>".$tpl_time."с</td>
								<td>".$db_q."</td>
								<td>".$mysql_time."с</td>
								<td>".$mem_usg."</td>
							</tr>\r\n";

			$cFile = fopen( $statfile, "w" );	

			fwrite( $cFile, $newText.$newTextAdd.$lastLine);
			fclose( $cFile );
		}
	}

	$showstat .= "<div class='showstat'><i id='showstat-but' title='Показать статистику (скрыть - Esc)'></i>";
	$showstat .= "
		<div class='base-stat'>
		<p>Скрипт выполнен за: <b>".$timer."с</b></p>
		<p>Шаблон создан за: <b>".$tpl_time."с</b></p>
		<p>Запросы: <b>".$db_q."</b></p>
		<p>Выполнены за: <b>".$mysql_time."с</b></p>";
	if($mem_usg) $showstat .="<p> Расход оперативы <b>".$mem_usg."</b> </p>";
	
	if (!$nolog) {
		$showstat .= "
		<p>
			<a href='".$config['http_home_url']."uploads/stat_log.html' target='_blank' title='Лог удалится при достижении ".$size."Кб'>
				".fgets($statfile)."Посмотреть лог (".round(filesize($statfile)/1024,2)."Кб)
			</a>
		</p>
		";
	}
	
	if ($show_query) {
		$showstat .= "<i id='queries-stat' title='Показать запросы (скрыть - Esc)'></i><div class='queries'>".$time_query."</div>";
	}
	$showstat .= "</div></div>";
	$showstat .="
	<script>
		jQuery(function($) {
			$('#showstat-but').click(function () {
				$('.base-stat').slideToggle(200);
			});
			$('#queries-stat').click(function () {
				$('.queries').slideToggle(200);
			});
			$(document).keyup(function(e) {
				if (e.keyCode == 27) { $('.base-stat, .queries').fadeOut(100); }
			});
		});
	</script>
	";
	echo $showstat;

}

?>