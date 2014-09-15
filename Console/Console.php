<?php

namespace Console;

/**
 * Classe de Principais interações do Console
 * 
 * @author Fernando H Corrêa fernandohcorrea(TO)gmail.com
 * @version 1.0
 * @license http://creativecommons.org/licenses/GPL/2.0/legalcode.pt
 * @package \Console
 */
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
     * Vetor com Help de procesimento e scripts
     * @var array
     */
    protected static $helpStorage = array();
    
    /**
     * Vetor de tipo de dados suportados
     * 
     * @var array
     */
    private static $arrayDataTaypes = array(
        'string',
        'bool',
        'boolean',
        'int',
        'integer',
        'array'
    );

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
    
    private static function getHelpToStorage(\ReflectionClass $class, $keyClasse)
    {
        $array_help = array();
        $array_help = array_merge($array_help, self::getHeadHelp($class));
        $array_help = array_merge($array_help, self::getStartScriptToolHelp($class));
        
        self::$helpStorage[$keyClasse] = $array_help;
    }
    
    private static function getHeadHelp(\ReflectionClass $class)
    {
        $array_head_help = array();
        $docComment = $class->getDocComment();
        $docComment = self::trataStrHelp($docComment);
        $array_doc_lines = explode(PHP_EOL, $docComment);
        foreach ($array_doc_lines as $key => $line) {

            if(preg_match('/^(\/\*\*)|(\*\/)$/', $line)){
                continue;
            }
            
            $line = preg_replace('/^( \*)/', '', $line, 1);
            $line = trim($line);
            
            if(empty($line)){
                continue;
            }
            
            $match = array();
            if(preg_match('/^@(?P<key>\w+) (?P<value>.*)/i', $line,$match)){
                $array_head_help['extras'][$match['key']] = $match['value'];
                continue;
            }
            
            $array_head_help['help'][] = $line;
            
        }
        return $array_head_help;
    }
    
    private static function getStartScriptToolHelp(\ReflectionClass $class)
    {
        $array_start_help = array();
        $methodStartScriptTool = $class->getMethod('startScriptTool');
        
        //
        //Params
        //
        $array_method_params = $methodStartScriptTool->getParameters();
        foreach ($array_method_params as $reflectionParam){
            $name_param = $reflectionParam->getName();
            
            
            $array_start_help['startScriptTool']['params'][$name_param] = array(
                'position' => $reflectionParam->getPosition(),
                'is_optional' => $reflectionParam->isOptional()
            );
            
            if($reflectionParam->isOptional()){
                $array_start_help['startScriptTool']['params'][$name_param]['default'] = $reflectionParam->getDefaultValue();
            }
        }
        
        //
        // Docs
        //
        $docComment = $methodStartScriptTool->getDocComment();
        $docComment = self::trataStrHelp($docComment);
        $array_doc_lines = explode(PHP_EOL, $docComment);
        foreach ($array_doc_lines as $key => $line) {
            
            $line = trim($line);
            
            if(preg_match('/^(\/\*\*)|(\*\/)$/', $line)){
                continue;
            }
            
            $line = preg_replace('/^(\*)/', '', $line, 1);
            $line = trim($line);
            
            if(empty($line)){
                continue;
            }
            
            $match = array();
            if(preg_match('/^@(?P<key>param)( ){1,}(\\\){0,}(?P<type>\w+)( ){1,}\$(?P<param>\w+)( ){1,}(?P<desc>.*)/i', $line,$match)){
                
                $type =strtolower($match['type']);
                
                if(!in_array($type, self::$arrayDataTaypes)){
                    throw new \Exception(
                        sprintf('O Parametro %s::%s(%s) possui um tipo(%s) inválido.', $class->getName(), $methodStartScriptTool->getName(), $match['param'], $match['type'])
                    );
                }

                if(isset($array_start_help['startScriptTool']['params'][$match['param']])){
                    $array_start_help['startScriptTool']['params'][$match['param']] = array_merge(
                        $array_start_help['startScriptTool']['params'][$match['param']],
                        array(
                            'type' => $type,
                            'desc' => $match['desc']
                        )
                    );
                }
                continue;
            }
            
            $match = array();
            if(preg_match('/^@(?P<key>\w+) (?P<value>.*)/i', $line,$match)){
                $array_start_help['startScriptTool']['extras'][$match['key']] = $match['value'];
                continue;
            }
            
            $array_start_help['startScriptTool']['help'][] = $line;
        }
        
        //
        // Check required param
        //
        if(isset($array_start_help['startScriptTool']['params']) && count($array_start_help['startScriptTool']['params'])){
            foreach ($array_start_help['startScriptTool']['params'] as $param => $data) {
                if($data['is_optional'] === FALSE && (!isset($data['type']) || !isset($data['desc'])) ){
                    throw new \Exception(
                        sprintf('O Parametro %s::%s(%s) é obrigatório, mas não está com documentação.', $class->getName(), $methodStartScriptTool->getName(), $param)
                    );
                }
            }
        }
        return $array_start_help;
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
                    } 
                    break;
            }
        }

        if ($flgHelp) {
            return self::getHelp($keyClasse);
        } elseif ($keyClasse) {
            return self::runScriptTool($keyClasse, $argv);
        } else {
            throw new \Exception('Erro ao processar parametros.');
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
        $tab = \Console\Config::get('tt_tab');
        
        $helpTexto = array();
        
        $helpTexto[] = sprintf("%s: %s", strtoupper($startFile), \Console\Config::get('tt_about'));
        $helpTexto[] = $tab . "Uso: $startFile <procedimento> [< help | --help | /? >]";
        $helpTexto[] = '';
        
        
        if (is_null($keyClasse)) {
            if (!empty(self::$scriptsTools)) {
                $helpTexto[] = \Console\Color::strColor(
                    $tab . "Lista de <procedimentos> :", \Console\Color::FG_WHITE
                );
            }
            
            $helpTexto[] = '';
            
            foreach (self::$scriptsTools as $keyClasse => $classe) {
                self::buildHelp($keyClasse, $helpTexto);
            }
            $helpTexto[] = '';
        } else {
            if (in_array($keyClasse, array_keys(self::$scriptsTools))) {
                self::buildHelp($keyClasse, $helpTexto);
            }
        }

        \Console\Out::sysOutNl(implode(PHP_EOL, $helpTexto));
        return 0;
    }
    
    private static function buildHelp($keyClasse, &$helpTexto)
    {
        $tab = \Console\Config::get('tt_tab');
        $helpClass = self::$helpStorage[$keyClasse];

        $helpTexto[] = \Console\Color::strColor(
            $tab . $tab . "$keyClasse :", \Console\Color::FG_WHITE
        );

        if(count($helpClass['help'])){
            foreach ($helpClass['help'] as $line){
                $helpTexto[] = str_repeat($tab, 3) . $line;
            }
            $helpTexto[] = '';
        }

        if(isset($helpClass['startScriptTool']['params']) && count($helpClass['startScriptTool']['params'])){
            foreach ($helpClass['startScriptTool']['params'] as $param => $data) {

                $type = '';
                $default = (isset($data['default'])) ? $data['default'] : '';

                switch ($data['type']) {
                    case 'string':
                        $type="='{$data['type']}'";
                    break;

                    case 'array':
                        $type="='param,param ...'";
                        if(is_array($default)){
                            array_map('trim', $default);
                            $default = implode(',', $default);
                        }
                    break;

                    case 'boolean':
                        $type="=<TRUE|FALSE>";
                        $default=($default)?'TRUE':'FALSE';
                    break;

                    case 'int':
                    case 'integer':
                        $type="=[0-9]";
                    break;

                    default:
                        $type="";
                    break;
                }

                if(!empty($default)){
                    $default = 'Padrão: '. $default;
                }

                $helpTexto[] = sprintf(
                    "%s--%s%s %s",
                    str_repeat($tab, 4),
                    $param,
                    $type,
                    $default
                );
                $helpTexto[] = str_repeat($tab, 5) . $data['desc'];
            }
        }
        $helpTexto[] = '';
    }
    
    private static function runScriptTool($keyClasse, $argv=array())
    {
        $scriptTool = self::$scriptsTools[$keyClasse];
        $reflectionClass = new \ReflectionClass($scriptTool);
        $objClass = $reflectionClass->newInstance();
        $methodStartScriptTool = $reflectionClass->getMethod('startScriptTool');
        
        $array_params = self::parseParams($keyClasse, $argv);
        
        return $methodStartScriptTool->invokeArgs($objClass, $array_params);
    }

    private static function parseParams($keyClasse, $argv=array())
    {
        $helpClass = self::$helpStorage[$keyClasse];
        $array_class_params = (isset($helpClass['startScriptTool']['params'])) ? $helpClass['startScriptTool']['params'] : array(); 
        
        $array_params = array();
        $array_check_params = array();
        foreach ($argv as $param) {
            $match = array();
            if( preg_match('/^(?P<arg>--(?P<key>\w+))=(?P<value>.*)$/', $param, $match) ){
                
                $arg = $match['arg'];
                $key = $match['key'];
                $value = $match['value'];
                
                if(array_key_exists($key, $array_class_params)){
                    $array_param_data = $array_class_params[$key];
                    
                    $msg_exception = sprintf('O argumento %s não parece ser do tipo %s.', $arg, $array_param_data['type']);
                    switch ($array_param_data['type']) {
                        
                        case 'string':
                            if(!is_string($value)){
                                throw new \Exception($msg_exception);
                            }
                        break;
                        
                        case 'int':
                        case 'integer':
                            if(!is_numeric($value)){
                                throw new \Exception($msg_exception);
                            }
                        break;
                        
                        case 'bool':
                        case 'boolean':
                            $value = strtoupper($value);
                            if(!in_array($value, array('TRUE','FALSE','0','1'))){
                                throw new \Exception($msg_exception);
                            }
                            
                            if($value === 'TRUE' || $value == '1'){
                                $value = TRUE;
                            } else if($value === 'FALSE' || $value == '0') {
                                $value = FALSE;
                            } else {
                                $value = FALSE;
                            }
                        break;

                        case 'array':
                            if(!is_string($value)){
                                throw new \Exception($msg_exception);
                            }
                            
                            $array_data = explode(',', $value);
                            
                            if(!is_array($array_data)){
                                throw new \Exception($msg_exception);
                            }
                            $value = $array_data;
                        break;

                    }
                    
                    $array_params[$array_param_data['position']] = $value;
                    $array_check_params[$key] = true;
                }
            }
        }
        
        foreach ($array_class_params as $key => $data_param) {
            if( !$data_param['is_optional'] && !isset($array_check_params[$key])){
                throw new \Exception(
                    sprintf("O parametro '%s' obrigatório não foi definido." , $key)
                );
            } else if($data_param['is_optional'] && !isset($array_check_params[$key])){
                $array_params[$data_param['position']] = $data_param['default'];
            }
        }
        
        ksort($array_params);
        return $array_params;
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
        $str = strip_tags($str);
        $str = trim($str);

        return $str;
    }

}