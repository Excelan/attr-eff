<?php 
/**
TODO PRIMARY NAMES FIND
TODO phonetic search http://habrahabr.ru/blogs/php/28752/
TODO pymorphy
http://code.google.com/p/php-text-words-parser/ (OWN UTF8!)
TODO use spell for variants
TODO synonims

lang detect

@@@
per paragraph text dirrefernce
paragraph, li order change
store as simple html, edit as plain text
link between p
conversation
mixed timeline
@@@

Timestamp->date
Timestamp->time
Timestamp->is_future
Timestamp->daysfromnow
Timestamp->difference(Ts2)
Timestamp->is_oneday(Ts2)

SimpleText
Document

Text->lang
Text->mblen
Text->paragpahs
Text->internalLinks
Text->externalLinks
Text->history
Text->version
Text->edittime
Text->images
Text->tables
Text->dataproviders

*/
class Text
{
	/**
	частотность
	*/
	public static function langDetect($text)
	{
		if (strlen($text) > 500) $text = substr($text, 0, 500);
		$t = iconv('UTF-8', 'cp1251//IGNORE', $text);
		for ($i=0; $i<strlen($t); $i++)
		{
			$char = $t[$i];
			$ukrspec = array("є","ї","і","ґ",'Є','Ї','І','Ґ');
			$russpec = array("Ъ","Ы","Ё","Э",'ъ','ы','э','ё');
			if (in_array($char, $ukrspec)) $ukr++;
			if (in_array($char, $russpec)) $rus++;
			$c = ord($char);
			if ($c >= 224 and $c <=255) 
				$shi++;
			else 
				$slo++;
		}
		//printlnd($shi);
		//printlnd($slo);
		if ($shi >= $slo) return 'local';
		if ($shi < $slo) return 'english';
	}
	
	/**
	TODO add ru digits
	*/
	public static function rod($text)
	{
		//$t = iconv('UTF-8', 'cp1251//IGNORE', $text);
		$lastchar = mb_substr($text, -1, 1);
		$f = array("а","и","у","ы","э","ю","я");
		$s = array("е","о",);
		if (in_array($lastchar, $f)) return 'F';
		else if (in_array($lastchar, $s)) return 'S';
		else return 'M';
	}
		
	public static function primaryNames($text)
	{
		$stemmer = new Stemming('ru');
		$namecounts = array();
		$namevariations = array();
		$namecountsint = array();
		$words = array();
		
		$t = iconv('UTF-8', 'cp1251//IGNORE', $text);
		//$tterm = iconv('UTF-8', 'cp866//IGNORE', $text);
		$tterm = $t;
		$qs = array();
		$struct = array();
		$states = array();
		$xstates = array();
		$st = 0;
		$rst = 0;
		for ($i=0;$i<strlen($t);$i++)
		{
			$c = ord($t[$i]);
			/** 
			.    слово
			U_.  предлог "С слова"
			U.   Слово
			/U./ «Слово»
			_U._U._ Имя Фамилия в предожении TODO А.И.Фам ??
			E    конец предложения
			+    конец абзаца
			TODO «More Words»
			*/
			if ($c >= 192 and $c <=223) $case = 'U';
			else if (in_array($c,array(129,165,175,178))) $case = 'U'; // ukr Caps
			else if ($c >= 224 and $c <=255) $case = '.'; // ru letter
			else if (in_array($c,array(131,180,191,179))) $case = '.'; // ukr letter
			else if ($c == 32 or $c == 44 or $c == 58 or $c == 45) $case = '_'; // _space_ , : -
			else if ($c == 10 or $c == 13) $case = '+'; // \r\n
			else if (in_array($c, array(46,63,33))) $case = 'E'; // .?!
			else if (in_array($c, array(171,187,34,39))) { $case = '/'; $qs[] = $i; } // «»\"'  
			else if ($c >= 65 and $c <=90) $case = 'Z';
			else if ($c >= 97 and $c <=122) $case = 'z';
			else if ($c >= 224 and $c <=255) $case = 'l';
			else $case = '?';
			//$dt = iconv('cp1251', 'cp866//IGNORE', $t[$i]);
			//$dt = $t[$i];
			//println("$case\t$i\t$c\t$dt");
			$struct[] = $case;
			if ($st>0) $rst = $st-1;
			if ($states[$rst][2] != $case)
			{
				$xstates[] = $case;
				$states[$st] = array($i,0,$case);
				$states[$rst][1] = $i-$states[$rst][0];
				//println($states[$rst]);
				//println($states[$st]);
				$st++;
			}
		}
		$p = 1;
		$current_p_word_count = 0;
		
		//println(join('',$struct));
		//println(join('',$xstates));
		//println($states);
		
		for ($pos=0; $pos<count($states);$pos++)
		{
			$state = $states[$pos];
			
			/**
			if (($state[2] == 'U' or $state[2] == 'Z') and $states[$pos-1][2]=='/') print('"'); // "Имена" в кавычкам
			if (($state[2] == 'U' or $state[2] == 'Z') and $current_p_word_count > 0) print('['); // в тексте Имена
			*/
			
			//if ($state[2] == 'U') printlnd($states[$pos-1][2]);
			//printlnd($state[2]);
			//printlnd($states[$pos-1][2]);
			
			// TODO U, U,
			// TODO apPle
			// ALLCAPS NAME
			// Ру Имя /(En Name)
			// end text (text)
			// Лиге Европы - работает только вместе
			// БОРУССИЯ М – ШТУТГАРТ – 1:1 Голы: Дэмс (67, с пенальти) – Какау (71). Боруссия: тер Штеген, Дэмс, Данте, Броуверс, Янчке, Нойштедтер, Нордтвайт, Ройс (Маркс, 83), Аранго, Ханке (Леки, 77), Бобадилья (Рупп, 70). Штутгарт: Ульрайх, Молинаро, Маса, Таски, Буларуз, Кузманович, Квист, Гентнер (Погребняк, 77), Хайнал (Траоре, 72), Харник, Какау. Предупреждения: Бобадилья (26), Броуверс (36), Янчке (69) – Молинаро (24), Квист (66).
			// Старого Света.
			//  будь то Бандера или Шухевич, Ленин или Сталин, Ватутин или Оберлендер, о
			//  «Днепр-Арене»
			// «Донбасс Арене»
			
			// TODO ! DIFFERENTIAITE _-_ and .-. - слово через дефис и тире в предложении
			
			// English names
			if ($state[2] == 'Z' and $states[$pos+1][1]>1) $Z = substr($tterm,$state[0],$state[1]);
			if ($state[2] == 'z' and $state[1] > 1)
			{
				$word = substr($tterm,$state[0],$state[1]);
				$saveName = false;
				if ($states[$pos-1][2]=='Z') 
				{
					$word = $Z.$word;
					$saveName = true;
				}
				//println($word . ' <eng> ');
				if ($saveName) // TODO !!!!!!! && !in_array() STOP_WORDS
				{
					$namecountsint[$word]++;
				}
				$current_p_word_count++;
			}
			
			// Russian names
			if ($state[2] == 'U' and $states[$pos+1][1]>1) $U = substr($tterm,$state[0],$state[1]); // ?
			if ($state[2] == '.' and $state[1] > 2) 
			{
				$word = substr($tterm,$state[0],$state[1]);
				$saveName = false;
				if ($states[$pos-1][2]=='U') 
				{
					
					//print($states[$pos-2][2]);
					//print($states[$pos-3][2]);
					//print($states[$pos-4][2]);
					//print cp1251toUT8($U.$word);
					//print($states[$pos+1][2]);
					//print($states[$pos+2][2]);
					//print "<br>";
					$word = $U.$word;   // захватить первую букву
					if ($states[$pos+2][2] != 'U') // Не брать имена перед Фамилиями
						$saveName = true;
				}
				
				$utf8word = iconv('cp1251', 'UTF-8//IGNORE', $word);
				$stemmed8 = $stemmer->stem($utf8word);
				$stemmed1251 = iconv('UTF-8', 'cp1251//IGNORE', $stemmed8);
				if ($saveName)
				{
					if ($current_p_word_count > 0)
					{
						$namecounts[$stemmed1251]++;
						//$namevariations[$stemmed1251][]=$word;
						$namevariations[$stemmed1251][$word]++;
					}
				}
				//println($utf8word . ' > ' . $stemmed8);
				$current_p_word_count++;
			}
			if ($state[2] == 'E' or $state[2] == '+') $current_p_word_count = 0;
		}
		//print_r($states);
		//print $tterm;
		//print_r($namecounts);
		//print_r($namevariations);
		return array('namecounts'=>$namecounts,'namevariations'=>$namevariations,'namecountsint'=>$namecountsint);
	}
}
?>