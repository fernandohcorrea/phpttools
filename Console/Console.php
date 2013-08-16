<?php

namespace Console;

class Console {

    /**
     * Definição interna de diretório padrão de scripts.
     * @var String Constânte de Diretório interno de ScriptTools padrão.
     */
    const SCRIPT_TOOLS_DIR = '/ScriptTools';

    /**
     * Nome do script php de start do console.
     * 
     * @access protected
     * @var String Nome do script php de start do console.
     */
    protected static $startFile = NULL;

    /**
     * Flag de Checagem de Dependências.
     * 
     * @var Boolean Flag de checagem completa de dependências
     */
    protected static $flagDependen = FALSE;

    /**
     * ScriptsTools encontrados para procedimentos
     * 
     * @var Array
     */
    protected static $scriptsTools = Array();

    /**
     * Construtor
     * 
     * Inicia processo de Checar dependencias. 
     */
    public function __construct() {
        self::checaDependencias();
    }

    /**
     * 
     * 
     * @param Array $argv
     * @return Int Retorna zero(0) para execução correta. Outros valores dependem do retorno de cada ScriptTool.
     */
    public static function iniciar($startFile, $argv = array()) {

        self::$startFile = $startFile;

        self::checaDependencias();

        self::obterScripts();

        return self::iniciaProcessamento($argv);
    }

    /**
     * Checa as Dependencias para o ShareSvn
     * 
     * @throws Exception 
     */
    protected static function checaDependencias() {
        if (!self::$flagDependen) {

            if (!self::isCli()) {
                throw new \Exception('Execução só é permitida em um console');
            }

            if (!self::chkSpl()) {
                throw new \Exception('Necessário a extenção SPL do php.');
            }

            self::$flagDependen = TRUE;
        }
    }

    /**
     * Verifica se a classe esta sendo chamada por um Terminal.
     * 
     * @return boolean 
     */
    private static function isCli() {
        return strtolower(PHP_SAPI) == 'cli';
    }

    private static function chkSpl() {
        return extension_loaded('SPL');
    }

    /**
     * Monta listagem de Scripts
     * Obtem todas as classes declaradas e verifica se sao Scripts válidos
     * 
     * @throws Exception 
     */
    private static function obterScripts() {
        self::declareScripts();
        $arrayClasses = get_declared_classes();

        $scriptsNamespace = preg_quote(\Console\Config::get('scripts_namespace'));

        $arrayClassesScript = preg_grep("/^$scriptsNamespace/", $arrayClasses);
        if (count($arrayClassesScript) > 0) {
            foreach ($arrayClassesScript as $classeNome) {
                
                $replacedClassName = preg_replace("/^$scriptsNamespace\\\/i", '', $classeNome);
                $keyClasse = strtolower($replacedClassName);
                
                $reflecClass = new \ReflectionClass($classeNome);
                self::chkImplementsInterface($reflecClass);
                self::chkIsSubclassOf($reflecClass);
                self::chkIsInstantiable($reflecClass);
                self::chkHasStartScriptTool($reflecClass);
                self::getHelpToStorage($reflecClass, $keyClasse);

                self::$scriptsTools[$keyClasse] = $classeNome;
            }
        }

        if (empty(self::$scriptsTools)) {
            throw new \Exception('Não foram encotradas classes de TTScripts compativeis.');
        }
    }

    private static function declareScripts() {
        $scriptsDir = self::getScripsDir();
        $directoryInterator = new \DirectoryIterator($scriptsDir);
        foreach ($directoryInterator as $fileInfo) {
            if ($fileInfo->isDot())
                continue;
            if (preg_match('/\.script\.php$/i', $fileInfo->getFilename())) {
                require_once $fileInfo->getFileInfo();
            }
        }
    }

    private static function getScripsDir() {
        $scriptsDir = \Console\Config::get('scripts_dir');

        if (empty($scriptsDir)) {
            $fileInfo = new \SplFileInfo(__FILE__);
            $scriptsDir = realpath($fileInfo->getPath() . '/..' . self::SCRIPT_TOOLS_DIR);
        } else if (is_dir($scriptsDir)) {
            \Console\Bootstrap::addIncludePath($scriptsDir);
        } else {
            throw new \Exception('Não foi encontrado nenhum diretório contendo .scripts.php');
        }

        return $scriptsDir;
    }

    private static function chkImplementsInterface(\ReflectionClass $reflecClass) {
        if (!$reflecClass->implementsInterface('\Console\Script\Schema')) {
            throw new \Exception('É necessário a implementação da Interface "Schema" para a classe de Script ' . $reflecClass->getName());
        }
    }

    private static function chkIsSubclassOf(\ReflectionClass $reflecClass) {
        if (!$reflecClass->isSubclassOf('\Console\Script\Base')) {
            throw new \Exception('É necessário a extensão da "Base" para o classe de Script ' . $reflecClass->getName());
        }
    }

    private static function chkIsInstantiable(\ReflectionClass $reflecClass) {
        if (!$reflecClass->isInstantiable()) {
            throw new \Exception('Classe de  Script ' . $reflecClass->getName() . ' não pode ser instânciada');
        }
    }

    private static function chkHasStartScriptTool(\ReflectionClass $class) {
        if (!$class->hasMethod('startScriptTool')) {
            throw new \Exception('Classe de  Script ' . $class->getName() . ' não tem o método startScriptTool');
        }
    }
    
    private static function getHelpToStorage(\ReflectionClass $class){
        
    }

    private static function iniciaProcessamento($argv = array()) {
        if (count($argv) <= 1) {
            return self::getHelp();
        }

        $flgHelp = FALSE;
        $keyClasse = NULL;
        array_shift($argv);
        foreach ($argv as $key => $param) {
            $param = strtolower($param);
            switch ($param) {
                case '--help':
                case 'help':
                case '?':
                case '/?':
                    $flgHelp = TRUE;
                break;

                default:
                    if (is_null($keyClasse) && in_array($param, array_keys(self::$scriptsTools))) {
                        $keyClasse = $param;
                    } elseif (!in_array($param, array_keys(self::$scriptsSvn))) {
                        $flgHelp = TRUE;
                    }
                    break;
            }
        }

        if ($flgHelp) {
            return self::getHelp($keyClasse);
        } elseif ($keyClasse) {
            $nomeClasse = self::$scriptsTools[$keyClasse];
            $scriptObj = new $nomeClasse();
            return $scriptObj->startScript();
        } else {
            throw new Exception('Erro ao processar parametros.');
        }
    }

    /**
     * Obter Help de um Script o de Todos os Scripts.
     * 
     * @param type $keyClasse
     * @return int 
     */
    private static function getHelp($keyClasse = NULL) {
        $startFile = self::$startFile;
        $helpTexto = strtoupper($startFile) . ": " . \Console\Config::get('tt_about') . "\n" .
                "    Uso: $startFile <procedimento> [< help | --help | /? >]  \n\n";
        if (is_null($keyClasse)) {
            if (!empty(self::$scriptsTools)) {
                $helpTexto .= \Console\Color::strColor("    Lista de <procedimentos>: \n\n", \Console\Color::FG_WHITE);
            }

            foreach (self::$scriptsTools as $keyClasse => $nomeClasse) {
                $help = call_user_func(array($nomeClasse, 'getHelp'));

                $helpTexto .= \Console\Color::strColor("     $keyClasse :   \n        ", \Console\Color::FG_WHITE);
                $helpTexto .= self::trataStrHelp($help) . "\n\n";
            }
        } else {
            if (in_array($keyClasse, array_keys(self::$scriptsTools))) {
                $nomeClasse = self::$scriptsTools[$keyClasse];

                $help = call_user_func(array($nomeClasse, 'getHelp'));

                $helpTexto .= \Console\Color::strColor("    $keyClasse:   \n        ", \Console\Color::FG_WHITE);
                $helpTexto .= self::trataStrHelp($help) . "\n";
            }
        }

        \Console\Out::sysOutNl($helpTexto);
        return 0;
    }

    /**
     * Trada Texto de Help.
     * 
     * @param String $str
     * @return String 
     */
    private static function trataStrHelp($str) {
        $tr = array(
            "\r\n" => "\n",
            "\r" => "\n",
            "\t" => ' '
        );
        $str = strtr($str, $tr);
        $str = preg_replace('/\n{1,}/', "\n        ", $str);
        $str = strip_tags($str);
        $str = trim($str);

        return $str;
    }

}