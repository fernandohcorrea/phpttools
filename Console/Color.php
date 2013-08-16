<?php

namespace Console;

class Color
{

    /**
     * Contentes de Cor de Fonte.
     */
    const FG_BLACK = '0;30';
    const FG_DARK_GRAY = '1;30';
    const FG_BLUE = '0;34';
    const FG_LIGHT_BLUE = '1;34';
    const FG_GREEN = '0;32';
    const FG_LIGHT_GREEN = '1;32';
    const FG_CYAN = '0;36';
    const FG_LIGHT_CYAN = '1;36';
    const FG_RED = '0;31';
    const FG_LIGHT_RED = '1;31';
    const FG_PURPLE = '0;35';
    const FG_LIGHT_PURPLE = '1;35';
    const FG_BROWN = '0;33';
    const FG_YELLOW = '1;33';
    const FG_LIGHT_GRAY = '0;37';
    const FG_WHITE = '1;37';
    
    /**
     * Contantes de Cor de Fundo. 
     */
    const BG_BLACK = '40';
    const BG_RED = '41';
    const BG_GREEN = '42';
    const BG_YELLOW = '43';
    const BG_BLUE = '44';
    const BG_MAGENTA = '45';
    const BG_CYAN = '46';
    const BG_LIGHT_GRAY = '47';

    /**
     * Grupo de Cor de Fonte
     * @var String
     */
    private static $grupoFG = 'FG_';
    
    /**
     *Grupo de Cor de Fundo
     * @var type 
     */
    private static $grupoBG = 'BG_';

    /**
     * Responde por dar cor a uma String.
     *
     * @param string $string
     * @param string $fgColor
     * @param string $bgColor
     * @return string 
     */
    public static function strColor($string, $fgColor = null, $bgColor = null)
    {
        $colored_string = "";

        $fgColor = self::getFgColor($fgColor);
        if (!is_null($fgColor)) {
            $colored_string .= "\033[" . $fgColor . "m";
        }

        $bgColor = self::getBgColor($bgColor);
        if (!is_null($bgColor)) {
            $colored_string .= "\033[" . $bgColor . "m";
        }

        if (is_null($fgColor) && is_null($bgColor)) {
            $colored_string = $string;
        } else {
            $colored_string .= $string . "\033[0m";
        }
        return $colored_string;
    }

    /**
     * Obter Cor do Grupo
     *
     * @param string $constCor
     * @param string $grupo
     * @return null | String
     */
    private static function getColor($constCor = NULL, $grupo = NULL)
    {
        $tt_color = \Console\Config::get('tt_color');
        
        if (is_null($constCor) || !$tt_color || is_null($grupo))
            return NULL;

        $reflectionClass = new \ReflectionClass(__CLASS__);
        $constants = $reflectionClass->getConstants();

        $arrayKeys = array_keys($constants);
        foreach ($arrayKeys as $valor) {
            if (preg_match('/' . $grupo . '/', $valor)) {
                if ($reflectionClass->getConstant($valor) == $constCor) {
                    return $constCor;
                }
            }
        }
        
        return NULL;
    }

    /**
     * Obter Cor de Fundo
     *
     * @param String $constCor
     * @return null | String 
     */
    private static function getBgColor($constCor = NULL)
    {
        return self::getColor($constCor, self::$grupoBG);
    }

    /**
     * Obter Cor de Fonte
     * 
     * @param String $constCor
     * @return null | String 
     */
    private static function getFgColor($constCor = NULL)
    {
        return self::getColor($constCor, self::$grupoFG);
    }

}