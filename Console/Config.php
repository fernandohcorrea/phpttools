<?php

namespace Console;

class Config {

    private static $instance;
    private static $cfgData;
    private static $iniFile;
    private static $iniPath;
    private static $iniFileCfg;

    public static function load($iniFile = 'Config/console.ini') {
        if (!self::$instance) {

            self::$iniFile = (is_string($iniFile) and strlen($iniFile) > 4) ? $iniFile : null;

            $class = __CLASS__;
            self::$instance = new $class;
        }

        return self::$instance;
    }

    private function __construct() {
        if (self::$iniFile) {
            self::parseIniFile();
        }
    }
    
    private static function parseIniFile() {
        self::parsePathIniFile();

        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        $iniFileCfg = null;
        if (count($includePaths)) {
            foreach ($includePaths as $incPath) {
                $lastBar = (substr($incPath, strlen($incPath) - 1) != "/") ? "/" : null;
                if (is_file($incPath . $lastBar . self::$iniPath . self::$iniFileCfg)) {
                    $iniFileCfg = $incPath . $lastBar . self::$iniPath . self::$iniFileCfg;
                    break;
                }
            }
        }

        if (!is_null($iniFileCfg)) {
            self::$cfgData = parse_ini_file($iniFileCfg, true);
            if (!count(self::$cfgData)) {
                throw new \Exception('Arquivo de configuração INI ' . self::$iniFileCfg . ' contém configurações', 0);
            }
        } else {
            throw new \Exception('Arquivo de configuração INI ' . self::$iniFileCfg . ' não encontrado', 0);
        }
    }

    private static function parsePathIniFile() {
        $matches = array();
        if (preg_match('@^(/)?(.*/)?(([\-\.\w\d]+).ini)$@im', self::$iniFile, $matches)) {
            list($iniFile, $bar, $iniPath, $iniFileCfg, $cacheName) = $matches;
            self::$iniPath = ($iniPath != "/") ? $iniPath : null;
            self::$iniFileCfg = $iniFileCfg;
        } else {
            throw new \Exception('Arquivo de configuração ' . self::$iniFile . ' não é um arquivo INI', 0);
        }
    }

    public static function get($cfgName) {
        $cfgOut = NULL;
        if (strpos($cfgName, '*')) {
            $cfgOut = self::getCfgGroup($cfgName);
        } else {
            $cfgOut = self::getCfg($cfgName);
        }

        if (is_null($cfgOut)) {
            throw new \Exception('Parametro de configuração não encontrado. [' . $cfgName . ']', 0);
        }

        return $cfgOut;
    }
    
    private static function getCfgGroup($cfgName){
        $cfgOut = NULL;
        $cfgName = '/^'.str_replace('*', '.*', $cfgName).'$/im';

        foreach (self::$cfgData as $key => $value) {
            if(preg_match($cfgName, $key)){
                $cfgOut[$key] = $value;
            }
        }

        return $cfgOut;
    }
    
    private static function getCfg($cfgName){
        $cfgOut = NULL;
        if (isset(self::$cfgData[$cfgName])) {
            $cfgOut =  self::$cfgData[$cfgName];
        } 
        return $cfgOut;
    }
}