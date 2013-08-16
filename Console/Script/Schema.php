<?php
namespace Console\Script;

/**
 * Interface obrigatória para ScriptTools.
 * Nela obrigamos alguns método de implementação.
 */
interface Schema
{

    /**
     * Definir diretivas de pré-execução.
     */
    public function __construct();

    /**
     * Obter o help de descrição da ScriptTools.
     * 
     * @return string
     */
    public static function getHelp();

    
    
}
