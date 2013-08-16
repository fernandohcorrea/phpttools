<?php
/**
 * Pacote de Console Script
 * 
 * @package \Console\Script
 */
namespace Console\Script;

/**
 * Classe Base para ScriptClasses
 * Pré-Codifica alguns método para auxiliar na construção de uma ScriptTool.
 * 
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