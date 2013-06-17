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
Версия: 2.6 (17.06.2013)
=======================================================
*/

if(! defined('DATALIFEENGINE')) {
	die('Fucking attempt');
}

if ($user_group[$member_id['user_group']]['allow_admin']) {

	global $config, $Timer, $db, $tpl;

	if(!is_numeric($size)) $size = 40; //максимальный размер файла лога

	$statfile = ROOT_DIR . '/uploads/stat_log.html';
	$dtime = date ('d.m.Y  H:i:s', $_TIME);
	$timer = ($config['version_id']>= '10.0') ? $Timer->get() : $Timer->stop();
	$tpl_time = round($tpl->template_parse_time, 5);
	$db_q = $db->query_num;
	$mysql_time = round($db->MySQL_time_taken, 5);

	if ($show_query) {
		$total_time_query = $db->query_list;
		if(is_array($total_time_query)){
			for ($i = 0; $i < count($total_time_query); $i++) 
			{ 
			$color = ($total_time_query[$i][time] > 0.01) ? 'red' : 'green';
			$rounted_time = sprintf("%.8f", $total_time_query[$i][time]);
			$time_query .= "<p><span style='color:".$color."'>".$rounted_time."</span> сек. - [ ".htmlspecialchars($total_time_query[$i][query])." ]</p>";
			}
		}
	}

	if(function_exists("memory_get_peak_usage")) $mem_usg = round(memory_get_peak_usage()/(1024*1024),2)."Мб";


	if ((file_exists($statfile) && filesize($statfile) > $size*1024) OR $nolog) {
		unlink($statfile);
	}
	if (!$nolog) {
		if (!file_exists($statfile)) {
			$cFile = fopen($statfile, "wb");
			$firstText = "
<!DOCTYPE html>
<html lang='ru'>
<head>
	<meta charset='".$config['charset']."'>
	<title>Лог статистики генерации страницы</title>
	<style>
	a { display: inline-block; margin-bottom: 5px; }
	b { color: #c00; }
	p {
		margin: 0 -5px;
		padding: 10px 5px;
		border-bottom: solid 1px #ddd;
	}
	p:hover { background: #fcfcfc; }

	p:last-child { margin-bottom: -6px; }
	.stattable {
		margin: 50px;
		border-collapse: collapse;
		border: solid 1px #ccc;
		font: normal 14px Arial,Helvetica,sans-serif;
	}
	.stattable th b {
		cursor: help;	
	}
	.stattable td { text-align: right; }
	.stattable th, .stattable td {
		font-size: 12px;
		border: solid 1px #ccc;
		padding: 5px 8px;
	}
	.stattable th:first-child, .stattable td:first-child { width: 80%; text-align: left; }
	.stattable tr:hover { background: #f0f0f0; color: #1d1d1d; }
	</style>
	<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js'></script>
	<script>
		$.fn.getZnach = function(prop) {
			var options = $.extend({
				source: 'с',
				ins: '',
				quant: '5'
			}, prop);

			var summ=0;
			this.each(function (i) {
				summ+=+($(this).text().replace(/,/,'.').replace(options.source,''));			
			});

			$(options.ins).append('<br /><b title=\"Cреднее значение\">'+(summ/this.length).toFixed(options.quant)+options.source+'</b>');
		}
		//инициализация
		jQuery(function($) {
			$('td.timer').getZnach({ins:'th.timer'});
			$('td.tpl_time').getZnach({ins:'th.tpl_time'});
			$('td.db_q').getZnach({ins:'th.db_q', source: '', quant: '0'});
			$('td.mysql_time').getZnach({ins:'th.mysql_time'});
			$('td.mem_usg').getZnach({source: 'Мб', ins:'th.mem_usg', quant: '2'});
		});
	</script>
</head>
<body>
	<table class='stattable'>
		<tr>
			<th scope='col' class='queries'>Адрес страницы и запросы в БД (опционально)</th>
			<th scope='col' class='dtime'>Дата</th>
			<th scope='col' class='timer'>Вемя выполнения скрипта</th>
			<th scope='col' class='tpl_time'>Время создания шаблона</th>
			<th scope='col' class='db_q'>Кол-во запросов</th>
			<th scope='col' class='mysql_time'>Время выполнения запросов</th>
			<th scope='col' class='mem_usg'>Затраты памяти</th>
		</tr>
	\r\n</table></body></html>";
			fwrite($cFile, $firstText);
			fclose($cFile);

		} else {
			$cFileArr = file($statfile);
			$lastLine = array_pop($cFileArr);
			$newText = implode("", $cFileArr);

			$newTextAdd = "добавляем строку\r\n";
			$newTextAdd = "	
		<tr>
			<td class='queries'><a href='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."' title='Перейти на страницу' target='_blank'>".$_SERVER['REQUEST_URI']."</a> <br />".$time_query."</td>
			<td class='dtime'>$dtime</td>
			<td class='timer'><b>".$timer."с</b></td>
			<td class='tpl_time'>".$tpl_time."с</td>
			<td class='db_q'>".$db_q."</td>
			<td class='mysql_time'>".$mysql_time."с</td>
			<td class='mem_usg'>".$mem_usg."</td>
		</tr>\r\n";

			$cFile = fopen($statfile, "w");	

			fwrite($cFile, $newText.$newTextAdd.$lastLine);
			fclose($cFile);
		}
	}

	$showstat .= "<div class='showstat'><i id='showstat-but' title='Показать статистику (скрыть - Esc)'></i>";

	if ($show_query) {
		$showstat .= "<i id='queries-stat' title='Показать запросы (скрыть - Esc)'></i>";
	}
	if (!$nolog) {
		$showstat .= "<a id='log-link' href='".$config['http_home_url']."uploads/stat_log.html' target='_blank' title='Смотреть лог. Лимит ".$size."Кб,  сейчас: ".fgets($statfile).round(filesize($statfile)/1024,2)."Кб'></a>";
	}
	if ($member_id['user_group'] == 1) {
		$showstat .= "
		<i id='clearbutton' title='Очистить кеш'></i>
		<i id='cache-info'></i>
		";
	}
	$showstat .= "
		<div class='base-stat'>
		<p>Скрипт выполнен за: <b>".$timer."с</b></p>
		<p>Шаблон создан за: <b>".$tpl_time."с</b></p>
		<p>Запросы: <b>".$db_q."</b></p>
		<p>Выполнены за: <b>".$mysql_time."с</b></p>";
	if($mem_usg) $showstat .="<p> Расход оперативы <b>".$mem_usg."</b> </p>";
	
	$showstat .= "</div>";
	if ($show_query) {
		$showstat .= "<div class='queries'>".$time_query."</div>";
	}
	$showstat .="
	</div>
	<script>
		jQuery(function($) {
			$('#showstat-but').click(function () {
				$(this).toggleClass('active');
				$('.base-stat').slideToggle(200);
			});
			$('#queries-stat').click(function () {
				$(this).toggleClass('active');
				$('.queries').slideToggle(200);
			});
			$(document).keyup(function(e) {
				if (e.keyCode == 27) { $('.base-stat, .queries').fadeOut(100); $('#queries-stat, #showstat-but').removeClass('active'); }
			});";			
		if ($member_id['user_group'] == 1) {
			$showstat .="
			$('#clearbutton').click(function() {
				$('#cache-info').html('В процессе ...');
				$.get('".$config['http_home_url']."engine/ajax/adminfunction.php?action=clearcache', function(data){
					$('#cache-info').html(data);
				});
				return false;
			});";
		}
	$showstat .="
		});
	</script>
	";
	echo $showstat;

}

?>