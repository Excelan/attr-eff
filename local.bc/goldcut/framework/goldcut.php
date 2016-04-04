<?php
/**
Boot
grab all places from apps to inc
inc 1 joined src file for framework
+ apps, managers, mq separete?

PHP OPT - http://phplens.com/lens/php-book/optimizing-debugging-php.php
http://raditha.com/wiki/Readfile_vs_include

*/
class Goldcut
{
	function __construct($dir)
	{
		// base speed on c2d 1.3 = 250 r/s (without opcode cache)
		$this->init($dir); // >fast
		$this->autoloadRegister(); // > 200
		//require(BASE_DIR.'/goldcut/framework/utils.php'); // > 230 IS FASTER THEN autoloadRegister (it has >200)
		////Utils::startTimer('init'); // >150(autoload)|>200(mrequire) // first call with resolve class by autoload register
		//$this->webbase(); // fast
		define('BASE_URI', '/');
		define('WEB_BASE_DIR', $_SERVER['DOCUMENT_ROOT']);
		// primary util functions - scan files for conf files
		$this->loadLoaders(); // >97 just require 3 files gets 30% of speed after this line
		if (ENV === 'DEVELOPMENT' or TEST_ENV === true) $this->check_base_consistency();
		$this->loadConfigs(); // very slow (>27 r/s) - slowest is php unserilize on 100k file (reason in classes with interlinks. arrays is fast) (7 earlier was in preload, mailer)
		$this->loadNamedQueries();
		$this->loadMessageQueuePublishersListeners(); // >20
		if (ENV === 'DEVELOPMENT' || TEST_ENV === true) $this->check_consistency();
		if (ADMIN_AREA === true) $this->loadFormWidgets();
		include BASE_DIR.'/goldcut/helpers/item_renderers.php';
    //include BASE_DIR.'/helpers/global_functions.php';
    require BASE_DIR.'/goldcut/ent/datamodel.php';
		$this->load_divided('helpers');
		if (ENV === 'DEVELOPMENT') $this->load_divided('migrations');
		$this->load_divided('FormDataStorage');
		$procdir = 'BCJava/src/main/resources/process';
		if (file_exists(BASE_DIR.DIRECTORY_SEPARATOR.$procdir))
			$this->load_managedprocesses_xml($procdir);
		else Log::debug("NO MPE PROCESSES DIR $procdir");
		// load render functions
		////$ctime = Utils::reportTimer('init');
		////Log::info("@ INIT CTIME: [{$ctime['time']}]",'main');
	}

	private function init($dir)
	{
		$basedir = realpath($dir.'/..');
		define('GOLDCUT_DIR', $dir);
		define('BASE_DIR', $basedir);
        define('TMP_DIR', BASE_DIR.'/tmp');
		define('CLASS_DIR', $dir.'/framework');
		define('CLASS_ENT_DIR', $dir.'/ent');
		define('FORMWIDGETS_LOCAL_DIR', BASE_DIR.'/formwidgets');
		define('GATES_DIR', BASE_DIR.'/gates');
		define('FORMWIDGETS_DIR', BASE_DIR.'/goldcut/formwidgets');
		define('MANAGERS_DIR', BASE_DIR.'/goldcut/managers');
		define('MANAGERS_LOCAL_DIR', BASE_DIR.'/managers');
		define('APPS_DIR', BASE_DIR.'/apps/');
		define('SYSTEM_APPS_DIR', BASE_DIR.'/goldcut/apps/');
		define('PLUGINS_DIR', BASE_DIR.'/goldcut/plugins/');
		define('PLUGINS_LOCAL_DIR', BASE_DIR.'/data-plugins/');
		define('FIXTURES_DIR', BASE_DIR.'/importexport');
	}

	function check_base_consistency()
	{
		/**
		TODO
		get js/settings.js from goldcut/defaults/js/settings.js, watermark
		777 anf option default mask in config
		*/
		$syspath = array();
		array_push($syspath, BASE_DIR.'/log');
		array_push($syspath, BASE_DIR.'/tmp');
		array_push($syspath, BASE_DIR.'/config/entityoverlay');
		array_push($syspath, BASE_DIR.'/mq_rpc/listeners');
		array_push($syspath, BASE_DIR.'/original');
		array_push($syspath, BASE_DIR.'/media');
		array_push($syspath, BASE_DIR.'/thumb');
		array_push($syspath, BASE_DIR.'/helpers/render');
		array_push($syspath, BASE_DIR.'/data-plugins');
		//array_push($syspath, BASE_DIR.'/views/layout');
		//array_push($syspath, BASE_DIR.'/widgets');
		$this->dirsSetup($syspath);
	}

	function check_consistency() // dev, test
	{
		$syspath = array();
		$reportOnConsistency = array();
		foreach (Entity::each_entity() as $e )
		{
			$report = $e->checkConsistency();
			if (count($report)) $reportOnConsistency[$e->name] = $report;
			if ($e->class != 'Photo' or $e->name == 'photo') continue;
			array_push($syspath, BASE_DIR.'/media/'.$e->getFolderName());
		}
		foreach($reportOnConsistency as $ename => $reports)
		{
			foreach($reports as $report)
			{
				Log::error($report, 'inconsistency');
				println($report,1,TERM_RED);
			}
		}
		if (count($reportOnConsistency)) throw new Exception('Inconsistencies in entities config');
		$this->dirsSetup($syspath);

		$this->checkSystemDirsAccesibility();
	}

	private function dirsSetup($dirs)
	{
		foreach($dirs as $path)
		{
			clearstatcache(true, $path);
			if (!file_exists($path))
			{
				mkdir($path, octdec(FS_MODE_DIR), true);
				println("create folder $path",1,TERM_VIOLET);
			}
			if (!is_writable($path))
			{
				if (!chmod($path, octdec(FS_MODE_DIR))) throw new Exception("Can't chmod $path");
				else dprintln("make ug writable $path",1,TERM_YELLOW);
			}
		}
	}

	private function checkSystemDirsAccesibility()
	{
		$syspath = array();
		array_push($syspath, BASE_DIR.'/log');
		array_push($syspath, BASE_DIR.'/original');
		array_push($syspath, BASE_DIR.'/media');
		array_push($syspath, BASE_DIR.'/thumb');
		foreach($syspath as $path)
		{
			clearstatcache(true, $path);
			if (!file_exists($path))
			{
				throw new Exception("Not exists folder $path");
			}
			if (!is_writable($path))
			{
				throw new Exception("Not writable folder $path");
			}
		}
	}

	function autoloadRegister()
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . CLASS_ENT_DIR . PATH_SEPARATOR. MANAGERS_LOCAL_DIR . PATH_SEPARATOR. MANAGERS_DIR . PATH_SEPARATOR .  PLUGINS_LOCAL_DIR . PATH_SEPARATOR . PLUGINS_DIR . PATH_SEPARATOR . FORMWIDGETS_LOCAL_DIR . PATH_SEPARATOR . FORMWIDGETS_DIR);  //    PATH_SEPARATOR . CLASS_DIR  - already defined in boot.php
		spl_autoload_register();
		require "assert.php";
	}

	function loadFormWidgets()
	{
		$this->load_divided('goldcut/formwidgets');
	}

	function loadNamedQueries()
	{
		include BASE_DIR.'/config/namedquery/namedquery.php';
	}

	function loadLoaders()
	{
		$SCF = parse_ini_file(BASE_DIR . "/config/system.ini", true);
		if ($SCF !== false)
		{
			define('INICONFIGS', true);
            $CF = parse_ini_file(BASE_DIR."/config/project.ini", true);
            if ($CF === false) throw new Exception('project.ini error');

            //error_reporting(E_ALL ^ E_NOTICE); // warnings included
            mb_internal_encoding("UTF-8");
            $MCF = parse_ini_file(BASE_DIR."/config/mail/mail.ini", true);
            if ($MCF === false) throw new Exception('mail.ini error');

            if (!file_exists(BASE_DIR."/config/host.ini")) throw new Exception('host.ini not found');
            $HCF = parse_ini_file(BASE_DIR."/config/host.ini", true);
            if ($HCF === false) throw new Exception('host.ini error');
            $GLOBALS['CONFIG'] = array_merge($SCF, $CF, $MCF, $HCF);

			define('ENV', $GLOBALS['CONFIG']['ENVIRONMENT']['ENV'] ? $GLOBALS['CONFIG']['ENVIRONMENT']['ENV'] : 'DEVELOPMENT');
			define('LOG_ENV', $GLOBALS['CONFIG']['LOGGING']['LOG_ENV']);

			if (isset($SCF['SOFTWARESTACK']) && $SCF['SOFTWARESTACK']['DISPLAY_PHP_ERRORS'] == 1) ini_set('display_errors', 1);
			error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED);

			$DBCF = parse_ini_file(BASE_DIR."/config/db/".strtolower(ENV)."db.ini", true);
			if ($DBCF === false) throw new Exception("db/".strtolower(ENV)."db.ini error");
			$ECF = parse_ini_file(BASE_DIR."/../env".strtolower(ENV).".ini", true);
			if ($ECF === false) throw new Exception(BASE_DIR."/../env".strtolower(ENV).'.ini error');
			$GLOBALS['CONFIG'] = array_merge($GLOBALS['CONFIG'], $DBCF, $ECF);

			setlocale(LC_ALL, $GLOBALS['CONFIG']['LOCALE']['SYSTEM_LOCALE']);
			define('DEFAULT_LANG', $GLOBALS['CONFIG']['INTERNATIONAL']['DEFAULT_LANG']);
			SystemLocale::setall(explode(',',$GLOBALS['CONFIG']['INTERNATIONAL']['AVAIL_LANGS']));
			// TODO AVAIL_LANGS, ADMIN_LANGS, PUBLISH_LANGS
			Options::set('admin_filter_entities', explode(',',$GLOBALS['CONFIG']['PERSONALIZATION']['ADMIN_HIDE_ENTITIES']));
			date_default_timezone_set($GLOBALS['CONFIG']['LOCALE']['Timezone']);

			define('NEWUSERMODEL', true);
			if ($GLOBALS['CONFIG']['LEGACY']['LEGACY_CONFIG_FIELDS_ASPHP'] == 1) define('LEGACY_CONFIG_FIELDS_ASPHP', true);

			define('AUTO_JSON_FIELD_DECODE_TO_OBJECT', true);

			if ($GLOBALS['CONFIG']['PRECISION']['FORCE32BIT'] == 1) define('FORCE32BIT', true);
			if ($GLOBALS['CONFIG']['PRECISION']['FORCEBIGINTS'] == 1) define('FORCEBIGINTS', true);
			if ($GLOBALS['CONFIG']['PRECISION']['STRICT_FLOATS'] == 1) define('STRICT_FLOATS', true);
			define('SECURITY_SALT_STATIC', $GLOBALS['CONFIG']['ACCESS']['SECURITY_SALT_STATIC']);
			if ($GLOBALS['CONFIG']['OTHER']['GLOBAL_PER_PAGE']) define('GLOBAL_PER_PAGE', (int)$GLOBALS['CONFIG']['OTHER']['GLOBAL_PER_PAGE']);
			define('HOST', $GLOBALS['CONFIG']['PROJECT']['HOST']);
			if (!$GLOBALS['CONFIG']['PROJECT']['HOST']) {
				throw new Exception("Define in config/project.ini [PROJECT]\nHOST = ".$_SERVER['SERVER_NAME']."\n\n");
			}
			$scheme = $GLOBALS['CONFIG']['SOFTWARESTACK']['USE_HTTPS'] ? 'https://' : 'http://';
			define('BASEURL', $scheme.$GLOBALS['CONFIG']['PROJECT']['HOST']);
			define('SITE_NAME', $GLOBALS['CONFIG']['PROJECT']['SITE_NAME']);
			if ($GLOBALS['CONFIG']['PERSONALIZATION']['ADMIN_COLOR']) define('ADMIN_COLOR', '#'.$GLOBALS['CONFIG']['PERSONALIZATION']['ADMIN_COLOR']);
			if ($GLOBALS['CONFIG']['MEDIA']['IMAGE_CONVERTER'] == 'gm') define('IMAGE_CONVERTER', 'gm');
			if ($GLOBALS['CONFIG']['CACHE']['ENABLE_CACHE'] == 1) define('ENABLE_CACHE', true); else define('ENABLE_CACHE', false);
			if ($GLOBALS['CONFIG']['CACHE']['ENABLE_QUERY_CACHE'] == 1) define('ENABLE_QUERY_CACHE', true); else define('ENABLE_QUERY_CACHE', false);
			if ($GLOBALS['CONFIG']['CACHE']['SKIP_XCACHE'] == 1) define('SKIP_XCACHE', true);
			if ($GLOBALS['CONFIG']['CACHE']['SKIP_APC'] == 1) define('SKIP_APC', true);
			if ($GLOBALS['CONFIG']['CACHE']['SKIP_MEMCACHE'] == 1) define('SKIP_MEMCACHE', true);
			if ($GLOBALS['CONFIG']['CACHE']['USE_REAL_EXTERNAL_DATA_IN_TEST'] == 1) define('USE_REAL_EXTERNAL_DATA_IN_TEST', true);
			if ($GLOBALS['CONFIG']['CACHE']['ENABLE_CONFIGS_CACHE'] == 1) define('ENABLE_CONFIGS_CACHE', true);
			if ($GLOBALS['CONFIG']['CACHE']['cacheAdminSelectors'] == 1) define('cacheAdminSelectors', true);
			if ($GLOBALS['CONFIG']['CACHE']['DATAHISTORYTOCACHE']) define('DATAHISTORYTOCACHE', (int)$GLOBALS['CONFIG']['CACHE']['DATAHISTORYTOCACHE']);
			if ($GLOBALS['CONFIG']['GATES']['GATES_ENABLED'] == 1) define('GATES_ENABLED', true);
			if ($GLOBALS['CONFIG']['GATES']['EXTERNAL_GATES_ENABLED'] == 1) define('EXTERNAL_GATES_ENABLED', true);
			if ($GLOBALS['CONFIG']['']['ENABLE_LISTDB'] == 1) define('ENABLE_LISTDB', true);
			define('INITIAL_DEPOSIT', (int)$GLOBALS['CONFIG']['SERVICE']['INITIAL_DEPOSIT']);
			define('INITIAL_BONUS', (int)$GLOBALS['CONFIG']['SERVICE']['INITIAL_BONUS']);

//			if ($GLOBALS['CONFIG']['LOGGING']['DEBUG_SQL'] == 1)
//				define('DEBUG_SQL', true);

			if ($GLOBALS['CONFIG']['VERSIONCONTROL']['GITUSED'] == 1) define('GITUSED', true);

			if ($GLOBALS['CONFIG']['FILESYSTEM']['FS_MODE_DIR']) define('FS_MODE_DIR', '0755');
			if ($GLOBALS['CONFIG']['FILESYSTEM']['FS_MODE_FILE']) define('FS_MODE_FILE', '0644');
			define('ROOT_PASS', $GLOBALS['CONFIG']['ACCESS']['ROOT_PASS']);

			if ($GLOBALS['CONFIG']['APIKEYS']['POSTMARKAPI']) define('POSTMARKAPI', $GLOBALS['CONFIG']['APIKEYS']['POSTMARKAPI']);
			if ($GLOBALS['CONFIG']['RECEIVERS']['EMAILBUGSTO']) define('EMAILBUGSTO', $GLOBALS['CONFIG']['RECEIVERS']['EMAILBUGSTO']);
			if ($GLOBALS['CONFIG']['RECEIVERS']['EMAILORDERSTO']) define('EMAILORDERSTO', $GLOBALS['CONFIG']['RECEIVERS']['EMAILORDERSTO']);
			if ($GLOBALS['CONFIG']['MAIL']['SEND_MAIL'] == 1) define('SEND_MAIL', true);
			if ($GLOBALS['CONFIG']['MAIL']['SENDMAILINENVDEV'] == 1) define('SENDMAILINENVDEV',true);
			if ($GLOBALS['CONFIG']['APPMAIL']['USEDUMBWELCOME'] == 1) define('USEDUMBWELCOME',true);

			if ($GLOBALS['CONFIG']['DATABASES']['RDBMS'] == 'PostgreSQL') define('USEPOSTGRESQL',true);

			$GLOBALS['CONFIG'][ENV]['DB']['HOST'] = $GLOBALS['CONFIG']['DATABASE']['HOST'];
			$GLOBALS['CONFIG'][ENV]['DB']['USER'] = $GLOBALS['CONFIG']['DATABASE']['USER'];
			$GLOBALS['CONFIG'][ENV]['DB']['PASSWORD'] = $GLOBALS['CONFIG']['DATABASE']['PASSWORD'];
			$GLOBALS['CONFIG'][ENV]['DB']['DBNAME'] = $GLOBALS['CONFIG']['DATABASE']['DBNAME'];
			if (ENV === 'DEVELOPMENT')
			{
				$GLOBALS['CONFIG']['TEST']['DB']['HOST'] = $GLOBALS['CONFIG']['TESTDATABASE']['HOST'];
				$GLOBALS['CONFIG']['TEST']['DB']['USER'] = $GLOBALS['CONFIG']['TESTDATABASE']['USER'];
				$GLOBALS['CONFIG']['TEST']['DB']['PASSWORD'] = $GLOBALS['CONFIG']['TESTDATABASE']['PASSWORD'];
				$GLOBALS['CONFIG']['TEST']['DB']['DBNAME'] = $GLOBALS['CONFIG']['TESTDATABASE']['DBNAME'];
			}

			$GLOBALS['CONFIG'][ENV]['DB']['REDISDBID'] = $GLOBALS['CONFIG']['DATABASE']['REDISDBID'];

			if (USEPOSTGRESQL === true) { // pgsql
				define('SQLQT', '"');
			}
			else { // mysql
				define('SQLQT', '`');
				$GLOBALS['CONFIG'][ENV]['DB']['CHARSET'] = 'utf8';
				$GLOBALS['CONFIG'][ENV]['DB']['COLLATE'] = 'utf8_general_ci';
			}
		}
		else
		{
			require BASE_DIR.'/config/core.php';
			require BASE_DIR.'/config/local.php';
		}
		require CLASS_DIR.'/coreutils.php';
		require CLASS_DIR.'/gctemplates.php';
	}

	function loadConfigs()
	{
		// umask for created firs, files
        if (!defined('FS_MODE_DIR')) define('FS_MODE_DIR', '0750');
        if (!defined('FS_MODE_FILE')) define('FS_MODE_FILE', '0640');

		// is config in cache?
        if (ENABLE_CONFIGS_CACHE === true && $sysConfig = Cache::get('sys:config'))
		{
			$GLOBALS['CONFIG'] = ($sysConfig);
		}
		else
		{
			// load fields config
			if (LEGACY_CONFIG_FIELDS_ASPHP === true)
			{
				$this->load_divided('goldcut/systemfield');
				$this->load_divided('config/field');
			}
			// else fields config is in xml of entities

			// load entity configs .php
			if (defined('LEGACY_ENTITY_CONFIGS_ASPHPSRC') && LEGACY_ENTITY_CONFIGS_ASPHPSRC === true) // legacy goldcut php configs
			{
				$this->load_divided('goldcut/systementity');
				$this->load_divided('config/entity');
				$this->check_configs();
			}
			else // .xml configs
			{
				$this->load_divided_xml('goldcut/systementity', true);
				$this->load_divided_xml('config/entity');
				$this->load_overlay_xml('config/entityoverlay');
				$this->load_overlay_xml('config/entitygroupoverlay');
				Field::manager()->reinit();
				Status::manager()->reinit();
			}
			// routes
			$this->load_divided('goldcut/config/route');
			$this->load_divided('config/route');
			//$GLOBALS['CONFIG']['GATEROUTING']['WMS/Warehouse/ParcelInRegister'] = json_decode('{"gate": "WMS/Warehouse/ParcelInRegister", "type": "internal"}', true);
			$internalGates = json_decode(read_data_from_file(BASE_DIR.'/config/route/gates.json'));
			// \Log::info($internalGates, "RRR");
			foreach ($internalGates as $g)
			{
				// \Log::info($g, "RRR");
				$GLOBALS['CONFIG']['GATEROUTING'][$g] = array('gate' => $g, 'type' => 'internal');
				if (ENV == 'DEVELOPMENT' || ENV == 'TEST')
				{
					$m = new Message();
					$m->gate = $g;
					$m->registerGate();
				}
			}
			// db
			if (INICONFIGS !== true) $this->load_divided('config/db');
			// mail
			if (INICONFIGS !== true) $this->load_divided('config/mail');
			// cache config
			if (ENABLE_CONFIGS_CACHE === true) Cache::put('sys:config', $GLOBALS['CONFIG']);
		}
		// require preloaders
		require BASE_DIR.'/goldcut/preload.php';
		require BASE_DIR.'/config/preload/preload.php';
		if (file_exists(BASE_DIR.'/../postloadoverlay.php')) require BASE_DIR.'/../postloadoverlay.php';
	}

	// check for deprecated stuff
	private function check_configs()
	{
		$em = Entity::each_managed_entity('Photo');
		foreach($em['Photo'] as $manager)
		{
			if ($manager->name == 'photo') continue;
			if(is_string($manager->mediaoptions)) throw new Exception("Photo entity:{$manager->name} mediaoptions {$manager->mediaoptions} in legacy string format. Migrate to array('image'=>'XxY', 'preview'=>'XxY:crop')");
		}
	}

	function loadMessageQueuePublishersListeners()
	{
		//$this->load_divided('mq_rpc/publishers');
		$broker = Broker::instance();
		$broker->exchange_declare ("MANAGERS", DURABLE, ROUTED);
		$broker->exchange_declare ("ENTITY", DURABLE, ROUTED);
		$broker->exchange_declare ("EVENTS", DURABLE, ROUTED);
		$broker->exchange_declare ("GATES", DURABLE, ROUTED);
		$broker->exchange_declare ("SCHEDULE", DURABLE, ROUTED);

		$this->load_divided('goldcut/mq');
		$this->load_divided('goldcut/ent/mq');
		$this->load_divided('mq_rpc/listeners');
	}


	private function load_divided($dir)
	{
		$directory = BASE_DIR.DIRECTORY_SEPARATOR.$dir;
		//$iterator = new DirectoryIterator($directory); // plain
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
		$objects->setMaxDepth(5);
		foreach ($objects as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$fname = $fileinfo->getFilename();
				$fpath = $fileinfo->getPath();
				if (substr($fname,-4,4) == '.php')
				{
					require($fpath.'/'.$fname);
				}
			}
		}
	}

	private function load_divided_xml($dir, $system=false)
	{
		$directory = BASE_DIR.DIRECTORY_SEPARATOR.$dir;
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
		$objects->setMaxDepth(5);
		foreach ($objects as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$fname = $fileinfo->getFilename();
				$fpath = $fileinfo->getPath();
				if (substr($fname,-4,4) == '.xml')
				{
					$filepath = $fpath.'/'.$fname;
					XMLConfigLoader::load($filepath, 'entity', $system);
				}
			}
		}
	}

    private function load_overlay_xml($dir)
    {
        $directory = BASE_DIR.DIRECTORY_SEPARATOR.$dir;
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
        $objects->setMaxDepth(5);
        foreach ($objects as $fileinfo)
        {
            if ($fileinfo->isFile())
            {
                $fname = $fileinfo->getFilename();
                $fpath = $fileinfo->getPath();
                if (substr($fname,-4,4) == '.xml')
                {
                    $filepath = $fpath.'/'.$fname;
                    XMLConfigLoader::loadoverlay($filepath, 'entity');
                }
            }
        }
    }

	private function load_managedprocesses_xml($dir)
	{
		$directory = BASE_DIR.DIRECTORY_SEPARATOR.$dir;
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS), RecursiveIteratorIterator::SELF_FIRST);
		$objects->setMaxDepth(5);
		foreach ($objects as $fileinfo)
		{
			if ($fileinfo->isFile())
			{
				$fname = $fileinfo->getFilename();
				$fpath = $fileinfo->getPath();
				if (substr($fname,-4,4) == '.xml')
				{
					$filepath = $fpath.'/'.$fname;
					XMLManagedProcessLoader::load($filepath);
				}
			}
		}
	}

}
?>
