<?php

/**
*
* Byte suffixes manipulation
*
* PHP versions 4 and 5
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license/3_0.txt.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @category   PHP
* @package    Utils_Bytes
* @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    $Id: Bytes.php,v 1.1 2007/06/08 12:42:24 phpmrtgadmin Exp $
*
*/

// {{{

$GLOBALS['BYTE_SUFFIXES'] = array(
    'T' => 1099511627776,
    'G' => 1073741824,
    'M' => 1048576,
    'k' => 1024,
);

// }}}
// {{{

class Utils_Bytes
{

    // {{{

    function bytes($value)
    {
        $suffixes = implode('|', array_keys($GLOBALS['BYTE_SUFFIXES']));
        preg_match("/^(\d+(?:\.?\d*)?)(" . $suffixes . ")?$/", $value, $matches);
        if ( empty($matches[1]) ) {
            return 0;
        }
        $result = (float)$matches[1];
        if ( isset($matches[2]) ) {
            $result *= $GLOBALS['BYTE_SUFFIXES'][$matches[2]];
        }
        return $result;
    }

    // }}}
    // {{{

    function multibytes($value, $precision=2)
    {
        foreach ($GLOBALS['BYTE_SUFFIXES'] as $k => $v) {
            if ($value >= $v) {
            	$value /= $v;
                return ($precision ? round($value, $precision) : $value) . $k;
            }
        }
        return $value;
    }

    // }}}

}

// }}}

?>