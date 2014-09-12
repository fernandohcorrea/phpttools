<?php
namespace Console\Script;

/**
 * Classe Base para ScriptClasses
 * Pre-Codifica alguns método para auxiliar na construção de uma ScriptTool.
 * 
 * @author Fernando H Corrêa fernandohcorrea(TO)gmail.com
 * @version 1.0
 * @license http://creativecommons.org/licenses/GPL/2.0/legalcode.pt
 * @package \Console\Script
 * @abstract
 */
abstract class Base
{
  
    /**
     *  Contrutor Abstrato.
     * Limpa Tela ao iniciar.
     */
    public function __construct()
    {
        $this->limpaTela();
    }

    /**
     * Limpa tela.
     * Pode ser invocado a qualquer momento
     * 
     * @access protected
     */
    protected function limpaTela()
    {
        passthru('clear');
    }
    
    /**
     * Pegunta algo para o usuário do ScriptTool.
     * Usado para interagir com o usuário.
     * 
     * @access protected
     * @param String $pergunta
     * @return String
     */
    protected function pergunta($pergunta)
    {
        \Console\Out::sysOut($pergunta);
        $resposta = fgets(STDIN);
        $retorno = trim($resposta);
        return $retorno;
    }
    
}