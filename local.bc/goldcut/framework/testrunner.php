<?php
/**

TODO yellow pluses if no assers and no errors. GREEN PLUSES if test has asserst

TODO chain actions in testcase - f1() {return 1} f2(a) {a == 1}
if next action has no (attributes) then echo or log that return

TODO dont run any test in production env (load database etc)!
TODO WIN TERMINAL IS CP866
$t = iconv('CP1251', 'CP866//IGNORE', $r['title']);

TODO use protected as OK for Exceptions


TODO !!! autodelete .danger file if modified timestamp 1hout older

*/
class TestRunner
{

	private static $testclasses_count = 0;

	private static $testCases = array();
	private static $testUnits = array();

	private static $report = array();
	private static $reportp = array();
	private static $outbuffers = array();
	private static $timings = array();

	/**
	use comments above function names as testcase description
	*/
	private static function filterdoc($doc)
	{
		$lines = explode("\n", $doc);
		for ($n=0; $n<=count($lines); $n++)
		{
			$lines[$n] = str_replace("/**", "", $lines[$n]);
			$lines[$n] = str_replace("*/", "", $lines[$n]);
			$lines[$n] = ltrim($lines[$n]);
			$lines[$n] = rtrim($lines[$n]);
			if (empty($lines[$n])) { unset($lines[$n]); }
		}
		return implode("\n", $lines);
	}

	/**
	find all classes implemented TestCase in loaded (current file only in autoload case)
	*/
	private static function findTestCases()
	{
		foreach (get_declared_classes() as $class)
		{
			$reflectionClass = new ReflectionClass($class);
			if ($reflectionClass->implementsInterface('TestCase'))
			{
				self::$testclasses_count++;
				self::$testCases[ $reflectionClass->name ] = $reflectionClass;
				foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method)
				{
					if ( $method->name != '__construct' && $method->name != '__destruct')
					{
						self::$testUnits[ $reflectionClass->name ][ $method->name ] = $method;
					}
				}
			}
		}
	}

	private static function prepareDB()
	{
		// migrate db (data unchanged)
		if (NOMIGRATE !== true)
		{
			OPTIONS::set('pause_DEBUG_SQL',true);
			Migrate::full();
			OPTIONS::set('pause_DEBUG_SQL',false);
		}
		// clear data (from prev run)
		if (NOCLEARDB !== true)
		{
			OPTIONS::set('pause_DEBUG_SQL',true);
			Migrate::clearAll();
			OPTIONS::set('pause_DEBUG_SQL',false);
			// clean all *.jpg in originals, preview, media, thumb
			self::clean_media_files();
			if (NOCLEARCACHE !== true)
				Cache::flush();
		}
	}

	private static function loadFixtures($testClass)
	{
		$import=null;
		// load fixtures
		try
		{
			OPTIONS::set('pause_DEBUG_SQL',true);

			if ($testClass->import)
			{
				$import = $testClass->import;
				$imported_callback_each = function($created) {};
				$imported_callback_before = function($entity) {};
				$imported_callback_after = function($entity) {};
				XMLData::iterateXMLfolders($testClass->import, null, null, $imported_callback_each, $imported_callback_before, $imported_callback_after);
			}
			elseif ($testClass->fixtures)
			{
				throw new Exception("Legacy test->fixtures. Use test->snapshot");
				/**
				foreach (Entity::each_entity() as $E)
				{
					if (is_array($testClass->fixtures) && count($testClass->fixtures))
					{
						if (in_array($E->name, $testClass->fixtures))
						{
							$need[] = $E->name;
						}
						else
							continue;
					}
					else
					{
						$need[] = $E->name;
					}
				}
				//println("INDIVIDUAL FIXTURES: ".json_encode($need));
				$imported_callback_each = function($created) {
					//println($created->urn,1,TERM_GRAY);
					print(".");
				};
				$imported_callback_before = function($entity) {
					printColor("urn-".$entity->name." ", TERM_BLUE);
				};
				$imported_callback_after = function($entity) {
					if (is_web_request()) echo '<br>';
					print "\n";
				};
				XMLData::iterateXMLfolders($testClass->snapshot, null, $need, $imported_callback_each, $imported_callback_before, $imported_callback_after);
				 */
				// load post fixtures actions
				self::loadPostFixtureMessages($testClass);
			}
			else
			{
				println('ALL FIXTURES DISABLED');
			}
			OPTIONS::set('pause_DEBUG_SQL',false);
		}
		catch (Exception $e)
		{
			$trace = $e->getTrace(); // ???
			$message = $e->getMessage();
			println($message, 1, TERM_RED);
		}
		return $import;
	}

	/**
	[
		{"action":"link","variator":"urn-variator-1001","urn":"urn-category-1"},
		{"action":"link","variator":"urn-variator-1003","urn":"urn-category-1"}
	]
	*/
	private static function loadPostFixtureMessages($testClass)
	{
		$file = FIXTURES_DIR."messages.json";
		if ($testClass->messages && file_exists($file))
		{
			$json = file_get_contents($file);
			printColor("messages: ", TERM_BLUE);
			$allj = json_decode($json,true);
			foreach ($allj as $mj)
			{
				println($mj);
				try
				{
					$m = new Message($mj);
					$m->deliver();
				}
				catch(Exception $e) {println($e,1,TERM_RED);}
			}

		}
	}

	/**
	find testcases, migrate db, load fixtures,  run test functions, report results
	*/
	public static function run()
	{
		define('INTEST',true);
		if (ENV !== 'DEVELOPMENT' && PRODUCTION_DB_IN_TEST_ENV !== true)
		{
			print "Tests can be runned only in DEVELOPMENT ENV (in production even test emails will be really sent)";
			die(1);
		}

		if (PRODUCTION_DB_IN_TEST_ENV === true)
		{
			if (!file_exists(BASE_DIR.'/ALLOW_PRODUCTION_TESTS.danger'))
			{
				die('Place file named ALLOW_PRODUCTION_TESTS.danger in site root to allow PRODUCTION_DB_IN_TEST_ENV');
			}
			else
			{
				// delete danger file if older then 1 hour
				if (time() - filemtime(BASE_DIR.'/ALLOW_PRODUCTION_TESTS.danger') > 3600*24*7)
					unlink(BASE_DIR.'/ALLOW_PRODUCTION_TESTS.danger');
			}
		}

		// max time for test
		set_time_limit(0); // unlimited

		// load all test cases
		self::findTestCases();

		if (is_web_request())
		{
			foreach ( self::$report as $testCaseName => $caseReport)
				$ht .= $testCaseName.' ';
			print "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /><title>Test case {$ht}</title><style>body {color: white; background-color: black;} body, pre {font-size: 85%; font-family: Consolas, Courier}</style></head>";
		}

		// short test on db connect ability
		try
		{
			// try to just connect with db
			$test_db_connect = DB::link();
			// check and migrate structure
			self::prepareDB();
		}
		catch (Exception $e)
		{
			$message = $e->getMessage();
			println($message, 1, TERM_RED);
			exit(1);
		}

		/**
		TODO catch FieldNotExists, EntityNotExists
		TODO Entity::ref(), Field::ref(), DataRow->unexistent in test nv throws Exeption if not exists and return null in production env
		*/
		//printlnd($GLOBALS['CONFIG']['LOGGING']['CONSOLELOGPHP']); exit();
		if ($_GET['screenlog'] || $GLOBALS['CONFIG']['LOGGING']['CONSOLELOGPHP'] == '1')
			define('SCREENLOG',TRUE);

		$lastTestClassRunned = null;

		$dblink = DB::link();

		// INSTANTIANTE TETSTCASE, RUN EVEREY METHOD
		foreach (self::$testCases as $testCaseName => $class)
		{
			$testClass = $class->newInstance();
			//$testClass = $class->newInstanceArgs([]);
			$import = self::loadFixtures($testClass);

			foreach (self::$testUnits[ $testCaseName ] as $unitName => $testUnit)
			{
				Utils::startTimer('test_run');

				ob_start();
				try
				{
					//Log::buffer_clear();
					Log::info("run [ $testCaseName ][ $unitName ]", 'test');
					OPTIONS::set('pause_DEBUG_SQL',true);
					$dblink->begin();
					OPTIONS::set('pause_DEBUG_SQL',false);

					$testUnit->invoke($testClass);

					OPTIONS::set('pause_DEBUG_SQL',true);
					$dblink->commit();
					OPTIONS::set('pause_DEBUG_SQL',false);
					//$testUnit->invokeArgs($testClass, []);
					self::$report[ $testCaseName ][ $unitName ] = true;
				}
				catch (PendingException $e)
				{
					$dblink->rollback();
					self::$report[ $testCaseName ][ $unitName ] = null;
					self::$reportp[ $testCaseName ][] = $unitName;
				}
				catch (AssertException $e)
				{
					$dblink->commit(); // COMMIT ON AssertException!
					$trace = $e->getTrace();
					$message = $e->getMessage();
					self::$report[ $testCaseName ][ $unitName ] = "!ASSERT: [" . $trace[1]['function'] . "] " . '[' .  $message . "] ". $trace[0]['file'].":".$trace[0]['line']."\n".Log::buffer_clear();
				}
				catch (Exception $e)
				{
					$dblink->rollback();
					$trace = $e->getTrace();
					$message = $e->getMessage();
                    $extxt = "!EXCEPTION: [".$message."] \n";
                    for ($i=0;$i<count($trace)-4;$i++) {
                        $argsj = array();
                        foreach ($trace[$i]['args'] as $arg) {
                            if ($arg instanceof Message) $argsj[] = (string)$arg;
                            else $argsj[] = print_r($arg, true);
                        }

                        $lines = file($trace[$i]['file']);
                        $lineum = $trace[$i]['line'] - 1;
                        $codeline = $lines[$lineum];
                        if (count($argsj)) $argstxt = join("; ",$argsj);
                        else $argstxt = 'NO ARGS';
                        $et = array($trace[$i]['class'].$trace[$i]['type'].$trace[$i]['function'], $argstxt, substr($trace[$i]['file'],strlen(BASE_DIR)).':'.$trace[$i]['line'].' '.trim($codeline));
                        $extxt .= join(" \n", $et)."\n\n";
                    }
                    //$extxt .= Log::buffer_clear();
					self::$report[ $testCaseName ][ $unitName ] = $extxt;
				}
				$outbuffer = ob_get_clean();
				if (!empty($outbuffer)) self::$outbuffers[ $testCaseName ][ $unitName ] = $outbuffer;
				self::$timings[ $testCaseName ][ $unitName ] = Utils::reportTimer('test_run');
			}
			ob_end_clean();
			$lastTestClassRunned = $testClass;
			unset($testClass);
		}

		//
		OPTIONS::set('pause_DEBUG_SQL',true);
		if ($lastTestClassRunned->export)
			XMLExport::exportData($lastTestClassRunned->export);
		OPTIONS::set('pause_DEBUG_SQL',false);

		//
		self::report_header($import, $lastTestClassRunned->export);

		//
		if (self::$testclasses_count < 2) // был запущен только один класс тесты
		{
			self::report_output_and_errors();
		}

	}



	public static function report_header($import=null, $export=null)
	{
		foreach ( self::$report as $testCaseName => $caseReport)
		{
			if (is_web_request())
				print "<html><head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" /><title>Test case {$testCaseName}</title><style>body {color: white; background-color: black; margin-top: 40px;} body, pre {font-size: 85%; font-family: Consolas, Courier} #nav {line-height: 34px; width: 100%; position: fixed; top:0; background-color: black} .arraykey {font-weight: bold; color: gold}</style></head>";

			$errors_count = 0;
			$pending_count = 0;
			$reports = 0;
			$totalTime = 0.00;

			$is64 = is_64bit() ? '64bit' : '32bit';

			foreach ( self::$timings[ $testCaseName ] as $unitName => $unitReport )
				$totalTime += (float)$unitReport['time'];

			if (is_web_request()) print '<div id="nav">';
            $db = 'MySQL';
            if (defined('USEPOSTGRESQL') && USEPOSTGRESQL === true) $db = 'PostgreSQL';
            if (defined('ENABLE_LISTDB') && ENABLE_LISTDB === true) $db .= ' Redis';
            if (Cache::is_enabled()) $db .= ' Cache: '.Cache::backend();
			printColor('Host: ' . HOST . ' ' . $is64 . ' ' . $db . ' Memory peak: ' . Utils::formatBytes(memory_get_peak_usage(),2) . ' Time: ' . $totalTime . '  Test: '.$testCaseName . "", TERM_YELLOW);

			for ($i=30;$i>strlen($testCaseName);$i--)
				print " ";
			foreach ( $caseReport as $unitReport )
			{
				$reports++;
				if ($unitReport === true)
					printColor( "+", TERM_GREEN);
				elseif ($unitReport === null)
				{
					printColor( ".", TERM_VIOLET);
					$pending_count++;
				}
				else
				{
					printColor( "-", TERM_RED);
					$errors_count++;
				}
			}

			for ($i=20;$i>$reports;$i--)
				print " ";

			if ($errors_count > 0) {
				printColor("FAIL", TERM_RED);
				// notify fail
			}

			if ($errors_count == 0)
			{

				if ($pending_count > 0)
					printColor("OK, some pending tests", TERM_GREEN);
				else
					printColor("OK", TERM_GREEN);
				// notify success
			}
			if ($import) printColor(" IMPORT {$import}", TERM_GRAY);
			if ($export) printColor(" EXPORT {$export}", TERM_GRAY);

			print "\n";
			if (PRODUCTION_DB_IN_TEST_ENV === true)
			{
				$productionenv = 'PRODUCTION ENV USED';
				if (NOMIGRATE !== true) $productionenv .= '. DB MIGRATED';
				if (NOCLEARDB !== true) $productionenv .= '. DB CLEARED';
				println($productionenv,1,TERM_YELLOW, false);
			}
			print "\n";
			if (is_web_request()) print '</div>';

		}
		foreach ( self::$reportp as $testCaseName => $caseReport)
		{
			foreach ($caseReport as $pendingTestName)
			{
				Log::debug("Pending $pendingTestName",'test');
				println('Pending '.$pendingTestName,1,TERM_VIOLET,false);
			}
		}

	}


	public static function report_output_and_errors()
	{
		foreach ( self::$report as $testCaseName => $caseReport)
		{
			if (count(self::$report)>1)
				printH($testCaseName);

			foreach ( $caseReport as $unitReportName => $unitReport )
			{
				$outbuffer = self::$outbuffers[ $testCaseName ][ $unitReportName ];
				$timing = self::$timings[ $testCaseName ][ $unitReportName ];
				$doc = self::$testUnits[ $testCaseName ][ $unitReportName ]->getDocComment();

				if ( $unitReport === true)
				{
					printLine();
					printColor ($unitReportName." ", TERM_YELLOW);
					if ( !empty($doc) )
						printColor (self::filterdoc( $doc )." ", TERM_BLUE);
					println(" ~".$timing['time'],1,TERM_BLUE,false);
				}
				elseif ($unitReport !== true && $unitReport !== null)
				{
					printLine();
					printColor ($unitReportName." ", TERM_YELLOW);
					if ( !empty($doc) )
						printColor (self::filterdoc( $doc )." ", TERM_BLUE);
					println(" ~".$timing['time'],1,TERM_BLUE,false);
					println ($unitReport, 1, TERM_RED,false);

					/* method code */
					/*
					$testfile = file( self::$testUnits[ $testCaseName ][ $unitReportName ]->getFileName() );
					for ($ln=self::$testUnits[ $testCaseName ][ $unitReportName ]->getStartLine()-1; $ln<self::$testUnits[ $testCaseName ][ $unitReportName ]->getEndLine();$ln++)
					{
						// if (($ln-1) == ERROR-LINE) RED IT
						println($ln.$testfile[$ln], 2);
					}
					 */

				}
				elseif ($unitReport === null)
				{
				}

				/* buffered output */
				if (!empty($outbuffer))
					print $outbuffer;
			}
		}
		print "\n";
	}


	private static function clean_media_files()
	{
		if (CLEAN_MEDIA_FILES_IN_TESTS !== true) return false;

		if (detect_platform() == 'WIN')
		{
			foreach (Entity::each_entity() as $e )
			{
				if ($e->class != 'Photo' or $e->name == 'photo') continue;
				$cmd = 'echo Y | del '.BASE_DIR.'\\media\\'.$e->name.'\\'.'*.*';
				system($cmd);
				if (MEDIASERVERS > 1)
				{
					$cmd = 'echo Y | del '.BASE_DIR.'\\thumb\\'.$e->name.'\\*.*';
					system($cmd);
				}
			}
			$cmd = 'del '.BASE_DIR.'\preview\*.jpg';
			system($cmd);
			$cmd = 'del '.BASE_DIR.'\original\*.jpg';
			system($cmd);
		}
		else
		{
			foreach (Entity::each_entity() as $e )
			{
				if ($e->class != 'Photo' or $e->name == 'photo') continue;
				$path = 'del '.BASE_DIR.'/media/'.$e->name.'/*.*';
				system($cmd);
				if (MEDIASERVERS > 1)
				{
					$path = 'del '.BASE_DIR.'/thumb/'.$e->name.'/*.*';
					system($cmd);
				}
			}
			$cmd = 'rm -rf '.BASE_DIR.'/preview/*.jpg';
			system($cmd);
			$cmd = 'rm -rf '.BASE_DIR.'/original/*.jpg';
			system($cmd);
		}
	}

}

?>
