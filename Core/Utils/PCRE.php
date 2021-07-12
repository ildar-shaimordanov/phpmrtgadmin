<?php

/**
*
* PCRE regular expressions simplifier and manipulator
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
* @package    PCRE
* @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    $Id: PCRE.php,v 1.1 2007/06/08 12:42:24 phpmrtgadmin Exp $
*
*/

$GLOBALS['__PCRE__regexp__'] = array(

    // Escaped characters
    '/\(/' => '\(', '/\)/' => '\)',
    '/\[/' => '\[', '/\]/' => '\]',
    '/\{/' => '\{', '/\}/' => '\}',
    '/\</' => '\<', '/\>/' => '\>',
    '/\^/' => '\^', '/\$/' => '\$',
    '/\./' => '\.', '/\+/' => '\+',
    '/\|/' => '\|', '/\//' => '\/', "/\\\\/" => '\\',
    '/\#/' => '\#', '/\-/' => '\-',
    '/\=/' => '\=', '/\!/' => '\!', '/\:/' => '\:', '/\&/' => '\&', '/\@/' => '\@',

    // Patterns
    '/%d/' => '\d',
    '/%a/' => '[a-zA-Z]',
    '/%s/' => '\s',
    '/%%/' => '%',
    '/\?/' => '.?',
    '/\*/' => '.+?',

);

class PCRE
{

    function pattern2regexp($input)
    {
        $input = preg_replace(
            array_keys($GLOBALS['__PCRE__regexp__']),
            array_values($GLOBALS['__PCRE__regexp__']),
            $input
        );
        $input = '/^' . $input . '$/';
        return $input;
    }

    function regexp2pattern($input)
    {
    }

    function pattern2sqllike($input)
    {
    }

    function sqllike2pattern($input)
    {
    }

}

?>