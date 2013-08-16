<?php
namespace TTScripts;

/**
 * Pode ser que esse seja um help ideal
 */
class Math_Quiz extends \Console\Script\Base implements \Console\Script\Schema {

    /**
     * @see \Console\Script\Base::__construct()
     */
    function __construct() {
        parent::__construct();
    }

    
    public static function getHelp() {
        return "Jogo de Perguntas sobre Matemática";
    }

    /**
     *  Inicia processamento da Classe Script Math_Quiz
     * 
     * @param String $asd asd asd asd 
     * @param String $qwe qwe qwe qwe 
     * @return int
     */
    public function startScriptTool($asd, $qwe) {
        $resposta = $this->pergunta('asdasd ?');
        \Console\Out::sysOutNl($resposta);
        return 1;
    }

}