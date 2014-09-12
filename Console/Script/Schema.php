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

}
