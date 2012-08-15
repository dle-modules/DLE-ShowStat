ShowStat
========

ShowStat - Модуль показа статистики нагрузки для DLE 9.3-9.6
---------------------------------------------------- 
Модуль предназначен для отладки сайта и удобного отображения отладочной информации (время выполнения скрипта, затраты памяти, количество запросов и сами запросы), а так же ведения лога с отладочной информацией.



УСТАНОВКА
=========
1. Залить файл showstat.php в папку engine/modules 
	Не забудьте перекодировать файл модуля если сайт на windows-1251

2. В конец main.tpl перед закрывающим тегом body прописать строку: 
{include file="engine/modules/showstat.php"}

3. В конец любого CSS-файла (например engine.css) дописать:

/**Showstat**/
.base-stat { display: none; }

.queries {
	display: none;
	text-align: left;
	max-height: 500px;
	overflow: auto;
}

.queries p { padding: 5px; border-top: solid 1px #ddd; }

.queries p:hover { background: #ddd; }

.showstat {
	position: fixed;
	bottom: 10px;
	left: 10px;
	background: #ffffff;
	background: -webkit-linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%);
	background: -moz-linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%);
	background: -o-linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%);
	background: -ms-linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%);
	background: linear-gradient(to bottom,  #ffffff 0%,#e5e5e5 100%);
	font: normal 12px/18px Arial, Helvetica, sans-serif;
	color: #323232;
	padding: 20px 20px 10px;
	text-align: left;
	border: solid 1px #fff;
	-webkit-border-radius: 10px;
	border-radius: 10px;
	text-shadow: 1px 1px 0 #fff;
	-webkit-box-shadow: 1px 1px 5px 1px rgba(0, 0, 0, 0.3);
	box-shadow: 1px 1px 5px 1px rgba(0, 0, 0, 0.3);
}

.showstat p { margin: 0; }

#queries-stat {
	display: inline-block;
	padding: 5px 5px 5px 0;
	width: 20px;
	height: 20px;
	cursor: pointer;
	background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAuVJREFUeNqMU0tIVFEY/s655955+RihsnRkVHotCknBRWEhGqUggSFEGyOiVu2iyCBoEeWmRaGQ4CJatUgJDaKIitTURk1NE0sdH6Q4jjOSOo87c2//uSYJuejCx+Wc8z++//vOYU+et8EwjCLTNOsN0yiDiX8/BnDG3zLGbnDO+za3TToQlPxYVdXLud5cZKS7YXc4QMX+5jKGaCSC0Eq4zD/t9+m63sQ4v2JCQZKpYM3PWs0jBYWyHIgBzG0YsA0GFpOBoQEwRWMJ7kBl8C647JaXkwW3OxWcM4oxqNgW0JpoI92dhlyvBwlTgc4cqAjeQ5w5IRgldfv6sDc/F1m7d8JptxMZ0xqDWcNzRGNJhH5F8al/DDp3oUomczsMJqgA8ZPB/uk5aKoNms0GO8HldCISjSEaTyAZ9mPXQB0OcBtBIK7YqLpizSZkF4W6CEWBonALkrJhmOC0xymOR35CSWPIPn0GUjxG4k20tqNnPHSbh5aDL0fHxrEQCGB1bc1K3gpGHVnoO+yZHoiMCuiB1wj42pA0kusXHnQ2CFXVYudravBjcgrz84uYmZ6GEBoUoVgs4qaAZ3kMrkOF0OefQo+5EPYH8ap/8dpIQ/mycKWkVPu+DFoi5ud74ZAi/rGTSetUwN83DFdmERJrYcRWBfRIHKcKdjRSRKOQQieTSUxOzcKmaZaINs2+IWI8jqjO4TCjmGxvh0YXzXP0MDIL9mGucxSTw7PvLectNaWApJicW4oqnVFoX0EC4aouLDgqke7NoVgnvrV04PPH8aYTN7su8VAw2LIUXMJqZJ2EMaAKFUIlf4Ww/go5gdgK9hgdSN9/EONt7/Chd+ZWdf3AneGG0glqD++52oslJaXlVx1OZ7HsvPkWTHlHyPvs0BvkGd201lDXPFL5onepZ+jR8WVGjJl8jbVnq5wU7yGkbvMWMXQ/zxflrlDx9a8naTk8+PBYnNFFkgX+9ysiFGx38FuAAQBygywrLfBIegAAAABJRU5ErkJggg==') no-repeat 50% 50%;
}

#showstat-but {
	display: inline-block;
	width: 20px;
	height: 20px;
	padding: 0 0 5px 0;
	cursor: pointer;
	background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAp5JREFUeNpkU01IVFEU/t6972fG1JHGrLBso2WRSAshonIjFrZQ27QoEQza6K6gXYS0KKhWuijIjbZw0xhRoEagUhRWUIJSWuL4A44OzDjhjOO89zrnzjwZ68Jh5pzvO+d857x7NfxzSq+Fem3b7XBd1+eSr7FpWkpKrW99oLUzn0schauzr22o0dC04ca6cpyrKUMw4CMGJwPr8RQmpiIYmVzGtuteWOtvGdlVgJP3B6zhW1dqEEvZeDcTxczKH2xuOQj4JY4dLER99V6U+CQeDk5hNb6linABwQV06tzdfgqf5hN4Nr6EudUkuluqsPS4Hjap+L2WRN/EssKZx3xPuc4zd1ysxNivOL6E4yj060hnHAiRnc5nSewxpfrPuGkKMP+JE+qlUKdwHOd6+YEAvi4kiGjAZ+jK7r2ex8k7H+E3dVjk8y/jzGM+52XVQ7PC8TT8hlBdaSxkSPf3u6dV17P3P8OUWhYj33BcMJ/zGBe85XgqA8vSYRoSBhtJHpyMqAJCl9BzccaZx3wt9/1YAQVski1Ud2pAi3OhS7VfmLpQJnM7yTa0WYFXwE07tmNaNB/NlS1AoJdg5jp7vhACmQwpoDzlO6no0MpKDAW0bR+TVUdtRyLPSLdQxRlnHvM5D7mbWlF69cVCU8MJFBVZ2KZP2FQdQHNtcOfKtg/MqmSDiiQSW3jzdhrrzy8foYsU1gkPZxbfd42OyZ5LDcdRTEVG5xJ4NR1Tl4iao9hv0BgCG5Q8OvYDzOc8TwGf4pLzN9vkoTM9dbWHcbSyDEUFJrzHlNhM4+dcBJPfFmEvfeiKjT/qp/DGrsfERciqgq1Pb2v+YDMt0/QA2l/aTUZfRkM3HpA7y8n/vca8U0HGCxB5MYcs6snOf85/BRgAU2QTxLlxCL0AAAAASUVORK5CYII=') no-repeat 50% 50%;
}
/**Showstat**/

ВСЁ!

-------------------------------------------------------
Для изменения максимального размера лог-файла можно передавать параметр &size=XX где XX максимальный размер лог-файла в килобайтах 

-------------------------------------------------------
Для мониторинга запросов к БД в файлах:

engine/classes/mysqli.class.php
engine/classes/mysql.class.php

раскомментировать строки:
============================================================================================
//			$this->query_list[] = array( 'time'  => ($this->get_real_time() - $time_before), 
//										 'query' => $query,
//										 'num'   => (count($this->query_list) + 1));
============================================================================================
в строку подключения добавить &show_query=y

-------------------------------------------------------
Для отключения логирования статистики в строку подключения прописать &nolog=y

-------------------------------------------------------
Если прописать все параметры, получится вот такая строка подключения:
{include file="engine/modules/showstat.php?&size=15&show_query=y&nolog=y"}

