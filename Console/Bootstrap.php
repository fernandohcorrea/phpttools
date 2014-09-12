<?php
namespace Console;

/**
 * Bootstrap do Console phpttools
 * 
 * @author Fernando H Corrêa fernandohcorrea(TO)gmail.com
 * @version 1.0
 * @license http://creativecommons.org/licenses/GPL/2.0/legalcode.pt
 * @package \Console
 */
class Bootstrap {

    private static $instance;
    private static $includePaths;
    private static $iniFile;

    public static function bootStart(Array $includePath = array(), $iniFile = 'Config/console.ini') {
        if (!self::$instance) {

            self::$includePaths = $includePath;
            self::$iniFile = $iniFile;

            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    private function __construct() {
        if (count(self::$includePaths)) {
            self::addIncludePaths();
        }

        if (self::$iniFile) {
            Config::load(self::$iniFile);
        }
    }
    
    private static function addIncludePaths() {
        if (count(self::$includePaths)) {
            foreach (self::$includePaths as $vPath) {
                self::addIncludePath($vPath);
            }
        }
    }
    
    public static function addIncludePath($path){
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        if (!in_array($path, $includePaths) && is_dir($path)) {
            set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        }
    }

    public static function consoleAutoLoad($className){
        $className = str_replace('\\', '/', $className) . '.php';
        require_once "$className";
    }
    
    public static function scriptsAutoLoad($className){
        $className = str_replace('\\', '/', $className) . '.script.php';
        require_once "$className";
    }
    
}

spl_autoload_extensions(".php, .script.php");
spl_autoload_register('\Console\Bootstrap::consoleAutoLoad');
spl_autoload_register('\Console\Bootstrap::scriptsAutoLoad');