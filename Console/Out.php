<?php

namespace Console;

/**
 * Description of Console
 *
 * @author Fernando H Corrêa fernandohcorrea(TO)gmail.com
 * @version 1.0
 * @license http://creativecommons.org/licenses/GPL/2.0/legalcode.pt
 */
class Out {

    /**
     * Retorno de Texto Formatado com Cores.
     *
     * @param String $string
     * @param String $fgColor
     * @param String $bgColor
     * @return String 
     */
    public static function strColor($string, $fgColor = NULL, $bgColor = NULL) {
        return \Console\Color::strColor($string, $fgColor, $bgColor);
    }

    /**
     * Saida de Texto Formatado com Cores.
     *
     * @param String $string
     * @param String $fgColor
     * @param String $bgColor 
     */
    public static function sysOut($string, $fgColor = null, $bgColor = null) {
        $saida = \Console\Color::strColor($string, $fgColor, $bgColor);
        fwrite(STDOUT, $saida);
    }

    /**
     * Saida de Erro Formatado com Cores.
     *
     * @param String $string
     * @param String $fgColor
     * @param String $bgColor 
     */
    public static function sysErr($string, $fgColor = null, $bgColor = null) {
        $saida = \Console\Color::strColor($string, $fgColor, $bgColor);
        fwrite(STDERR, $saida);
    }

    /**
     * Saida de Texto Formatado com Cores e Nova Linha
     * 
     * @param type $string
     * @param type $fgColor
     * @param type $bgColor 
     */
    public static function sysOutNl($string, $fgColor = null, $bgColor = null) {
        self::sysOut($string, $fgColor, $bgColor);
        fwrite(STDOUT, PHP_EOL);
    }

    /**
     * Saida de Erro Formatado com Cores e Nova Linha
     * 
     * @param type $string
     * @param type $fgColor
     * @param type $bgColor 
     */
    public static function sysErrNl($string, $fgColor = null, $bgColor = null) {
        self::sysErr($string, $fgColor, $bgColor);
        fwrite(STDERR, PHP_EOL);
    }

}
