<?php
set_time_limit(60 * 30);

require dirname(__FILE__) . '/../../goldcut/boot.php';

$ADMIN_COLOR = (defined('ADMIN_COLOR')) ? ADMIN_COLOR : '#000';

$STRING_ADD = array('ru' => 'добавить', 'en' => 'add');
$STRING_ANY = array('ru' => 'Любой', 'en' => 'Any');
$STRING_NO = array('ru' => 'Не', 'en' => 'No');
$STRING_NOT = array('ru' => 'Нет', 'en' => 'Not');
$STRING_YES = array('ru' => 'Да', 'en' => 'Yes');
$STRING_SEARCH = array('ru' => 'Поиск', 'en' => 'Search');
$STRING_LOADTIME = array('ru' => 'Время загрузки', 'en' => 'Load time');
$STRING_FOUND = array('ru' => 'Найдено', 'en' => 'Found');
$STRING_TOTALWITHFILTERS = array('ru' => 'всего материалов с учетом фильтров', 'en' => 'Total data found');
$STRING_ENABLEPAGING = array('ru' => 'Включить постраничность', 'en' => 'Enable paging');
$STRING_SHOWALL = array('ru' => 'Показать все', 'en' => 'Show all');

/**
 * TODO add check for is in ADMIN_AREA for all included files
 */

$root_login = 'root';
$root_password = ROOT_PASS;
if ($_COOKIE['login'])
{
	if (md5($root_login . $root_password) == $_COOKIE['login'])
	{
		$username = 'root';
	} else
	{
		setcookie("login", "", time() - 36000, '/');
		die("Old login. Refresh page.");
	}
} else
{
	header('Location: /goldcut/admin/aauth.php');
	exit(0);
}

define('ADMIN_AREA', true);

if ($_GET['phpinfo'])
{
	phpinfo();
	exit;
}

/**
 * http://nimbupani.com/declaring-languages-in-html-5.html
 * Content-Type: text/html; charset=utf-8
 * Content-Language: en
 * As lang attribute on a HTML element e.g. <div lang="fr">, or a xml:lang attribute on XML documents like MathML and SVG.
 * Identify the default language of a document This concept is new in HTML 5. If you specify only one language using the above two methods (i.e.<meta http-equiv="content-language" content="en"> instead of <meta http-equiv="content-language" content="en, fr"> ), then the text of the entire document is processed as that language (except for the text that is contained in an element which has a lang attribute, which is processed as the language tag value in lang attribute).
 * The last method is to explicitly declare a language to be used for text processing by the user agent. Use the lang attribute if you want the browser to process the text in that HTML element in a specific language.
 * IANA codes http://www.w3.org/TR/i18n-html-tech-lang/#ri20030218.131140352
 */


?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="content-language" content="ru">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title>@ <?php echo SITE_NAME; ?> / GOLDCUT CMS</title>
	<?php
	require("jscss.head.html");
	?>

	<script type="text/javascript">
	</script>

</head>
<body>

<div style="width:100%; background: <?= $ADMIN_COLOR ?>; color: #ccc;">
	<div style="padding: 10px; float: left;" id='adminlogo'>
		<?php echo '<a href="' . BASEURL . '">' . SITE_NAME . '</a>'; ?>
	</div>
	<div style="padding: 10px; float: right; font-size: 8pt; line-height: 200%; color: #aaa;">
		<input type=hidden id='serverts' name='serverts' value='<?php echo time(); ?>'>
		<input type=hidden id='clientts' name='clientts' value=''>

		<div id='serverclienttime'>
			<span id='timeCorr'></span>
			<span id='clientTime'></span> 
			<span id='GMTinfo'>
				<span id='clientTimeZone'></span>/<span id='serverTimeZone'><?php echo date('Z') / 3600 ?></span>
			</span>
		</div>
	</div>
	<br style='clear: both;'>
</div>

<?php
require("admin.menu.php");
?>


<div id="frame">

	<div id="maincenter">
		<div style='height: 10px;'></div>

		<?php

		try
		{
			if ($plugin = $_GET['plugin'])
			{
				include 'plugins/' . $plugin . '.php';
			}
			else if ($plugin = $_GET['localplugin'])
			{
				$local = BASE_DIR . '/adminutils/' . $plugin . '.php';
				$system = BASE_DIR . '/goldcut/adminutils/' . $plugin . '.php';
				if (file_exists($local))
					include $local;
				else
					include $system;
			}
			else if ($_GET['action'])
			{
				include 'crud.php';
			}
			else if ($_GET['app'])
			{
				WebRequest::dispatch($_GET['app']);
			}
			else
			{
				if (ENV === 'DEVELOPMENT') include 'dashboard-admin.php';
				else include 'dashboard.php';
			}
		}
		catch (Exception $e)
		{
			println($e, 1, TERM_RED);
		}

		?>

	</div>
	<!--
		<div id="mainright">
			<div class="pad">


			</div>
		</div>
	-->
	<div id="pane"></div>
	<div style="clear: both;">
	</div>
</div>
</body>
</html>