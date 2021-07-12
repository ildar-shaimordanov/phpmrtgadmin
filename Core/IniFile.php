<?php

/**
*
* Windows-like ini-files
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
* @package    Structure
* @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    $Id: IniFile.php,v 1.1 2007/06/08 12:43:17 phpmrtgadmin Exp $
*
*/

// {{{

#include_once 'Utils/file_put_contents.php';
include_once 'PHP/Compat/Function/file_put_contents.php';

// }}}
// {{{

/**
*
* Example 1:
* <code>
*   ; Contents of ini-file
*   [Section/Subsection1]
*   "Item1"="value 1"
*   "Item2"="value 2"
*   [Section/Subsection2]
*   "Item1"="value 1"
*   "Item2"="value 2"
*   "Item3"="value 3"
* </code>
*
* Example 2:
* <code>
*   $result = IniFile::read($file_name);
*   print_r($result);
* </code>
*
* Example 3:
* <code>
*   Array(
*       [Section] => Array(
*           [Subsection1] => Array(
*               [Item1] => value 1,
*               [Item2] => value 2,
*           ),
*           [Subsection2] => Array(
*               [Item1] => value 1,
*               [Item2] => value 2,
*               [Item3] => value 3,
*           ),
*       )
*   )
* </code>
*/
class IniFile
{

    // {{{

    function & _getOptions()
    {
        static $properties;
        return $properties;
    }

    // }}}
    // {{{

    function parse($ini, $options)
    {
        $delim = $options['delimiter'];
        $comment = $options['comments'];
        $heredoc = $options['heredoc'];

        $result = array();
        foreach ($ini as $line) {
            $line = trim($line);
            // Empty lines and comments
            if (empty($line) || 0 === strpos($comment, $line[0])) {
                continue;
            }
            // Sections
            if (preg_match('/^\s*\[\s*(.+)\s*\]\s*$/', $line, $matches)) {
                $keys = explode($delim, $matches[1]);
                $ref =& $result;
                foreach ($keys as $k => $v) {
#                    if (empty($v)) {
#                        continue;
#                    }
                    if (empty($ref[$v])) {
                        $ref[$v] = array();
                    }
                    $ref =& $ref[$v];
                }
                continue;
            }
            // Keys and values
            if (preg_match('/^(["\']|\b)(@|\w+)\1\s*=\s*([\'"]|\b)([^\3]*)\3$/', $line, $matches) && @$matches[2]) {
                $ref[$matches[2]] = $matches[4];
            }
        }
        return $result;
    }

    // }}}
    // {{{

    function process($ini)
    {
        $options =& IniFile::_getOptions();
        return IniFile::parse($ini, $options);
    }

    // }}}
    // {{{

    function read($fileName)
    {
        $ini = @file($fileName);
        return $ini ? IniFile::process($ini) : false;
    }

    // }}}
    // {{{

    function write($fileName, $array)
    {
        if ( ! is_array($array) ) {
            return false;
        }
        $options =& IniFile::_getOptions();
        $string = IniFile::writePrepare($array, '', $options);
        return file_put_contents($fileName, $string, LOCK_EX);
    }

    // }}}
    // {{{

    function writePrepare($array, $str, $options)
    {
        if ($str) {
            $str .= $options['delimiter'];
        }
        $result = '';
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result .= '[' . $str . $k . ']' . "\n" . IniFile::writePrepare($v, $str . $k, $options) . "\n";
            } else {
                $result .= '"' . $k . '"="' . $v . '"' . "\n";
            }
        }
        return $result;
    }

    // }}}
    // {{{

    function setOptions($newOptions)
    {
        static $defaultOptions = array('delimiter' => '/', 'comments' => ';#*', 'heredoc' => '<<<');
        $newOptions = array_merge($defaultOptions, $newOptions);

        if (!is_array($newOptions)) {
            return;
        }
        $options =& IniFile::_getOptions();
        if (!empty($newOptions['delimiter'])) {
            $options['delimiter'] = substr($newOptions['delimiter'], 0, 1);
        }
        if (!empty($newOptions['comments'])) {
            $options['comments'] = $newOptions['comments'];
        }
        if (!empty($newOptions['heredoc'])) {
            $options['heredoc'] = $newOptions['heredoc'];
        }
    }

    // }}}

}

// }}}
// {{{

/*
*
* Sets default values for delimiter, comments and heredoc signatures
*
*/
IniFile::setOptions(array(
    'delimiter' => '/',
    'comments'  => ';#*',
    'heredoc'   => '<<<', // 'heredoc' is not supported yet
));

// }}}

?>