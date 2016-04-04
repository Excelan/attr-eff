<?php


class HtmlCalendar 
{

	public $DD, $MM, $YYYY;

	function __construct ($timestamp) {

		if ($timestamp)	
		{
			$this->DD = substr ($timestamp, 6, 2);
			$this->MM = substr ($timestamp, 4, 2);
			$this->YYYY = substr ($timestamp, 0, 4);
			if (substr ($this->DD, 0, 1) == 0) {
				$this->DD = substr ($this->DD, 1, 1);
			}
			if (substr ($this->MM, 0, 1) == 0) {
				$this->MM = substr ($this->MM, 1, 1);
			}
		}
	}

	function Generate($MM, $YYYY, $activedays)
	{
		if (substr ($MM, 0, 1) == 0) $MM = substr ($MM, 1, 1);
		$firstmdayweekday = $this->WeekDayNum ( date("l", mktime(0,0,0,$MM,1,$YYYY)) );
		$daysinmonth = date("t", mktime(0,0,0,$MM,1,$YYYY));
		$lastmdayweekday = $this->WeekDayNum ( date("l", mktime(0,0,0,$MM,$daysinmonth,$YYYY)) );
		$monthrus = $this->MonthRus ($MM);

		$bc = "<table bgcolor='#FFFFFF' border=0 cellpadding=0 cellspacing=0><tr><td>\n
			<table width='100%' border='0' cellspacing='1' cellpadding='8' class='mycal'>\n<tr bgcolor='#F0F0F0'>\n<td colspan=7><div align=center><b>$monthrus</b></div></td></tr>\n<tr bgcolor='#F8F8F8'>\n<td><font color='#FF0000'>П</font></td><td><font color='#FF0000'>В</font></td><td><font color='#FF0000'>С</font></td><td><font color='#FF0000'>Ч</font></td><td><font color='#FF0000'>П</font></td><td><font color='#FF0000'>С</font></td><td><font color='#FF0000'>Н</font></td></tr>\n";
		$bc .= "<tr bgcolor='#F8F8F8'>\n";

		for ($bday=1; $bday < $firstmdayweekday; $bday++) $bc .= "<td>&nbsp;</td>\n"; // пустышки в начале месяца

		for ($day=$firstmdayweekday; $day<=$daysinmonth+$firstmdayweekday-1; $day++)
		{ // 1

			$cday = $day - $firstmdayweekday + 1;

			if (strlen ($cday) == 1) $dday = "0".$cday; else $dday = $cday;
			if (strlen ($MM) == 1) $dM = "0".$MM; else $dM = $MM;

			$wh = $YYYY.$dM.$dday;
			$hdata = $YYYY.'-'.$dM.'-'.$dday;

			if ($activedays[$cday] > 0) $bold = true; // ($wh)
			else $bold = false;

			if ($bold == true) $bc .= "<td><a class=calact href=/date/$hdata><b>$cday</b></a></td>\n";
			else $bc .= "<td><font color='#727272'>$cday</font></td>\n";

			if (($day % 7) == 0)
			{
				$bc .= "</tr>\n	";
				$bc .= "<tr bgcolor='#F8F8F8'>\n";
			}
		} // 1

		for ($lday=$lastmdayweekday; $lday < 7; $lday++) $bc .= "<td>&nbsp;</td>\n"; // пустышки в конце месяца
		$bc .= "</tr>\n";
		$bc .= "</table>\n</td></tr></table>\n";
		return $bc;
	}











	function BuildCalender ($MM, $YYYY) {

		if (substr ($MM, 0, 1) == 0)
		{
			$MM = substr ($MM, 1, 1);
		}
		$firstmdayweekday = $this->WeekDayNum ( date("l", mktime(0,0,0,$MM,1,$YYYY)) );
		$daysinmonth = date("t", mktime(0,0,0,$MM,1,$YYYY));
		$lastmdayweekday = $this->WeekDayNum ( date("l", mktime(0,0,0,$MM,$daysinmonth,$YYYY)) );
		$monthrus = $this->MonthRus ($MM);

		$bc = "
			<table bgcolor='#FFFFFF' border=0 cellpadding=0 cellspacing=0><tr><td>\n
			<table width='100%' border='0' cellspacing='1' cellpadding='4'>\n
			<tr bgcolor='#F0F0F0'>\n<td colspan=7><div align=center><b>$monthrus</b></div></td></tr>\n
			<tr bgcolor='#F8F8F8'>\n
			<td><font color='#FF0000'>П</font></td>
			<td><font color='#FF0000'>В</font></td>
			<td><font color='#FF0000'>С</font></td>
			<td><font color='#FF0000'>Ч</font></td>
			<td><font color='#FF0000'>П</font></td>
			<td><font color='#FF0000'>С</font></td>
			<td><font color='#FF0000'>В</font></td>
			</tr>\n";

		$bc .= "<tr bgcolor='#F8F8F8'>\n";

		for ($bday=1; $bday < $firstmdayweekday; $bday++) {
			$bc .= "<td>&nbsp;</td>\n";
		}

		for ($day=$firstmdayweekday; $day<=$daysinmonth+$firstmdayweekday-1; $day++) {
			$cday = $day - $firstmdayweekday + 1;
			$bc .= "<td>$cday</td>\n";
			if (($day % 7) == 0) {
				$bc .= "</tr>\n	";
				$bc .= "<tr bgcolor='#F8F8F8'>\n";
			}
		}


		for ($lday=$lastmdayweekday; $lday < 7; $lday++) {
			$bc .= "<td>&nbsp;</td>\n";
		}
		$bc .= "</tr>\n	";

		$bc .= "
			</table>\n
			</td></tr></table>\n
			";

		return $bc;
	}










	function BuildCACHEDCalenderActive ($MM, $YYYY, $nocahe = false) {

		if ($nocahe==false)
		{ // 1 если НЕКЕШИРОВАТЬ отключен (сразу выполнится Generate())

			$thisDAY =  date("d"); $thisMONTH =  date("m"); $thisYEAR =  date("Y");
			if ($thisMONTH<$MM && $thisYEAR==$YYYY) return false; // невозможен архив будущего месяца

			if ($thisMONTH == $MM && $thisYEAR == $YYYY)
			{ // 2 запрошен архив для ТЕКУЩЕГО МЕСЯЦА (день не имеет значения)

				if (file_exists ("cache/news_cache_".$YYYY.$MM."_".$thisDAY.".htm") == true)
				{ // 3 если сегодня уже кешировался календарь
					$fd = fopen ("cache/news_cache_".$YYYY.$MM."_".$thisDAY.".htm", "r");
					while (!feof ($fd)) $html .= fgets($fd, 4096); fclose ($fd); // чтение по дню

					$html .= "<!-- возврат по дню (уже кешировался) -->";
					return $html; // - возврат по дню

				}
				else
				{ // 3 если сегодня еще не кешировался календарь

					$datacal = $this->Generate($MM,$YYYY);
					$logfile = "cache/news_cache_".$YYYY.$MM."_".$thisDAY.".htm";
					$fd = fopen( $logfile, "a" ); fwrite($fd, $datacal); fclose( $fd ); // запись по дню

					$html = "<!-- возврат по дню (только что создан) -->";
					return $datacal.$html; // - возврат по дню

				} // 3 кеширование проверено

			}
			else // 2 запрошен архив для ПРОШЛОГО МЕСЯЦА
			{
				#echo "<p>2 запрошен архив для ПРОШЛОГО МЕСЯЦА news_cache_$YYYY$MM.htm</p>";
				if (file_exists ("cache/news_cache_$YYYY$MM.htm") == true)
				{ // 4 существует файл cache_200304.htm, в нем кеш за прошлый месяц
					#echo "<p>4 существует файл cache_200304.htm, в нем кеш за прошлый месяц</p>";
					$fd = fopen ("cache/news_cache_$YYYY$MM.htm", "r");
					while (!feof ($fd)) $html .= fgets($fd, 4096); fclose ($fd); // чтение по месяцу

					$html .= "<!-- возврат по месяцу (уже кешировался) -->";
					return $html; // - возврат по месяцу

				} // 4 кеш за прошлый месяц считан
				else
				{
					#echo "<p>возврат по месяцу (только что создан)</p>";
					$html .= "<!-- возврат по месяцу (только что создан) -->";
					$datacal = $this->Generate($MM,$YYYY); // Генерация
					$logfile = "cache/news_cache_".$YYYY.$MM.".htm";
					$fd = fopen( $logfile, "a" ); fwrite($fd, $datacal); fclose( $fd ); // запись про месяцу
					return $datacal.$html;
				}

			} // 2 запрос архива обработан

		} // прекращено действие директивы НЕКЕШИРОВАТЬ


		// Генерируется календарь (НО НЕ СОХРАНЯЕТСЯ!!!)
		$datacal = $this->Generate($MM,$YYYY); // Генерация
		return $datacal;
	}













	function GetWeekDay ()
	{
		$weekdayen = date("l", mktime(0,0,0,$this->MM,$this->DD,$this->YYYY));
		return $this->WeekDayENtoRU ($weekdayen);
	}

	function WeekDayENtoRU ($wd) {

		switch ($wd) {
		case "Sunday":
			return "Воскресенье";
			break;
		case "Monday":
			return "Понедельник";
			break;
		case "Tuesday":
			return "Вторник";
			break;
		case "Wednesday":
			return "Среда";
			break;
		case "Thursday":
			return "Четверг";
			break;
		case "Friday":
			return "Пятница";
			break;
		case "Saturday":
			return "Суббота";
			break;
		}
		return false;
	}

	function WeekDayNum ($wd) {

		switch ($wd) {
		case "Sunday":
			return 7;
			break;
		case "Monday":
			return 1;
			break;
		case "Tuesday":
			return 2;
			break;
		case "Wednesday":
			return 3;
			break;
		case "Thursday":
			return 4;
			break;
		case "Friday":
			return 5;
			break;
		case "Saturday":
			return 6;
			break;
		}
		return false;
	}

	function MonthRus ($MM) {

		switch ($MM) {
		case 1:
			return "Январь";
			break;
		case 2:
			return "Февраль";
			break;
		case 3:
			return "Март";
			break;
		case 4:
			return "Апрель";
			break;
		case 5:
			return "Май";
			break;
		case 6:
			return "Июнь";
			break;
		case 7:
			return "Июль";
			break;
		case 8:
			return "Август";
			break;
		case 9:
			return "Сентябрь";
			break;
		case 10:
			return "Октябрь";
			break;
		case 11:
			return "Ноябрь";
			break;
		case 12:
			return "Декабрь";
			break;
		}
		return false;
	}

}

?>