<?php
namespace TTScripts;

/**
 * Este é um exemplo de como criar um TTScript
 * 
 * @author Fernando H Corrêa fernandohcorrea(TO)gmail.com
 * @version 1.0
 * @license http://creativecommons.org/licenses/GPL/2.0/legalcode.pt
 */
class Math_Quiz extends \Console\Script\Base implements \Console\Script\Schema {

    private $nome;
    private $idade;
    private $numero_perguntas;
    private $array_numeros;
    private $array_operacoes;
    
    /**
     * Pode ser usado para inicar algumas variáveis
     * 
     * @see \Console\Script\Base::__construct()
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * A Documentação das variáver é importante, principamente as obrigatórias
     * 
     * @param string $seu_nome Nome do desafiante
     * @param int $idade Idade do desafiante.
     * @param int $numero_perguntas Número de perguntas
     * @param array $operacoes Operações matemáticas +,-,*,/
     * @return int
     */
    public function startScriptTool($seu_nome, $idade, $numero_perguntas=5, $operacoes=array('+')) {
        
        $this->nome = $seu_nome;
        $this->idade = $idade;
        $this->numero_perguntas = $numero_perguntas;
        $this->array_numeros = range(0, $idade);
        $this->array_operacoes = $operacoes;
        
        $this->interar();
        
        return 0;
    }
    
    /**
     * Interar
     */
    private function interar()
    {
        for( $i =0 ; $i < $this->numero_perguntas; $i++ ){
            $this->calcule();
        }
    }
    
    /**
     * Calcular
     */
    private function calcule()
    {
        $a = array_rand($this->array_numeros);
        $op = array_rand($this->array_operacoes);
        $b = array_rand($this->array_numeros);
        
        $calc = sprintf('%d %s %d', $a, $this->array_operacoes[$op], $b);
        $resposta = $this->pergunta($calc.' = ');
        eval('$calc = ' . $calc . ';');
        if($calc == $resposta){
            \Console\Out::sysOutNl("Certa a resposta", \Console\Color::FG_LIGHT_GREEN);
        } else {
            \Console\Out::sysOutNl("Ops...resposta errada", \Console\Color::FG_LIGHT_RED);
        }
    }
 
    
    

}