<?php
/*
 * REQUIRE: <core><ns>path</ns><section.. - root element needed in any config file
 * Class :Config
 * Class :ConfigSection
 * Class :ValueTypes
 * Config::value(/ns/path/key.path) - по пути, но нужно знать NS отдельно от пути ключа
 * Config::get(/ns/path, key.path) - по NS и ключу
 * SIMPLE PATH - /NS.PATH/KEY.PATH (WORKS) - only 1/2 slash? or /NS/PATH/KEY.PATH
 * TODO 'strings/ru','base.home' | 'app.Specialoffer.ru', 'strings.more'
 * TODO why :selected - array, :string - $s->string (but with lang), :struct -
 * TODO ->structs(filter[]) - struct - struct.string = $s != :string - $s->string
 * TODO endpoint struct -> to php Object {k:v,k:v}, of force cast for struct with inline structs
 * any section can hace sub section
 * struct is endpoint section.
 * /global/path (www/goldcut/config), local/path (www/config)
 * /manager/oauth/providers - ConfigSection with foreach (name => provider)
 * /manager/oauth/providers.facebook - 1 concrete section (struct) by name path
 * /manager/oauth/providers.facebook.url !error - no path to struct value. get facebook struct, then do $fbstruct->url
 * /manager/oauth/providers->structs(array(facebook,twitter))
 * struct(array) can be <selected> value type
 * site/oauth.providers - if selected/char blank the now its exception. should be blank array
 * TODO ns app/News - search in apps/News/config.xml
 * TODO _option('app.News/counts.perpage'), _option('/global.usesome')
 * TODO _str(RU, 'app.news.readmore')
 * TODO <html><h1 data-sk="othersection.readmore">Read more stub text</h1> >> str(RU, 'app.news.othersection.readmore') || str(RU, 'base.othersection.readmore') || stub
 * <a data-sk="" data-sd="title" title="int title">
global strings
per app strings
1 lang - 1 file
TODO dict valuetype. by m/f, single/many, plural number suffix etc. Script to calc dict key from word or number. Russian endings 3-4 different for "N word_SFX"
TODO <home><string> - speed, <value name="home" type="string"> - ясность?
<home>На главную</home>
<home><string>На главную</string></home>
<value name="home" type="string">На главную</value>
REQUIRE: NO app.Specialoffer.passport.apptitle, ONLY app/Specialoffer/passport.apptitle - how to know where is NS and where is KEYPATH?
 */
class Config
{
	private $xml;
	public $sections;
	private $concrete;
	private static $instance = array();

    /**
     * singleton per $ns
     */
	function __construct($ns=null)
	{
		$ns = join('/',explode('.',$ns)); // path.name > path/name

		if (substr($ns,0,1) == '/') {
            $path = BASE_DIR . "/goldcut/config";
            $xmlfile = $path.$ns.'.xml';
        }
        else if (substr($ns,0,3) == 'app') // app.* > path=/apps/*
        {
            $nsa = explode('/',$ns);
            $appname = array_shift($nsa);
            $ns = join('/', $nsa);
            $path = BASE_DIR."/apps/".$ns;
            $xmlfile = $path.'/config.xml';
        }
        else {
            $path = BASE_DIR . "/config/";
            $xmlfile = $path.$ns.'.xml';
        }

		if (!file_exists($xmlfile)) throw new Exception("Config file $xmlfile not exists");	
		$this->xml = simplexml_load_file($xmlfile);
		//var_dump('LOADED', $this->xml);
		if ($name) $this->concrete = true;
		self::drill($this->xml, 0, $this->sections);
		//print_r($this->sections);  // DEBUG
	}

    /**
     */
    public static function get($ns='core', $strkey)
	{
		if (!self::$instance[$ns]) self::$instance[$ns] = new Config($ns);
		return self::$instance[$ns]->getValue($strkey);
	}

    /**
     */
	public static function drill($el, $l=0, &$c)
	{
		foreach($el as $k=>$v)
		{
			if ($k == 'section')
			{
				//dprintln('@'.$k.$l.$v->name);
				$k = (string) $v->name;
				$c->$k = new ConfigSection($v);
			}
			else
			{
				//dprintln('#'.$k.$l.$v);
				$c->$k = (string) $v;
				self::drill($v, $l+1, $c);
			}
		}
	}

    /**
     * Config::value("/site/oembed/providers.twitter") => Config::get('/site/oembed', 'providers.twitter')
     * ? Config::value("/manager/oauth/providers[facebook,vk]") = Config::value("/manager/oauth/providers")->structs(array('facebook','vk'))
     * ? Config::value("/manager/oauth/providers(facebook)") = Config::value("/manager/oauth/providers")->structs(array('facebook'))
     */
	public static function value($fullpath)
	{
        //println($fullpath);
        //$fullpath = join('/',explode('.',$fullpath));
		$sides = explode('/',$fullpath);
		$path = array_pop($sides);
		$ns = join('/',$sides);
        //println("$ns, $path");
		return Config::get($ns, $path);
	}
    /**
     */
	private function getValue($strkey)
	{
		$k = explode('.',$strkey);
        $deep = array();
		foreach ($k as $key)
		{
			if (!$p) $p = $this->sections;
			$p = $p->$key;
            array_push($deep, $key);
            array_push($deep, get_class($p));
		}
        //dprintln($deep,1,TERM_YELLOW);
		if ($p !== null)
			return $p;
		else {
            //dprintln($deep,1,TERM_YELLOW);
            throw new Exception("$strkey not found");
        }
	}
}	
?>