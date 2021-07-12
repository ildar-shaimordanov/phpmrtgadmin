<?

/**
 *
 * Directories working
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
 * @package    File
 * @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */

// {{{

if ( ! defined('DIRECTORY_SEPARATOR') ) {
    define('DIRECTORY_SEPARATOR', (preg_match('/WIN/i', PHP_OS)) ? '\\' : '/');
}

if ( ! defined('PATH_SEPARATOR') ) {
    define('PATH_SEPARATOR', (preg_match('/WIN/i', PHP_OS)) ? ';' : ':');
}

// }}}
// {{{

define('FILE_FIND_MODE_DIRS',     -1);
define('FILE_FIND_MODE_BOTH',      0);
define('FILE_FIND_MODE_FILES',    +1);

define('FILE_FIND_SORT_USER',     -2);
define('FILE_FIND_SORT_DESC',     -1);
define('FILE_FIND_SORT_NOSORT',    0);
define('FILE_FIND_SORT_ASC',      +1);
define('FILE_FIND_SORT_NATURAL',  +2);

// }}}
// {{{

/**
 * A Class the facilitates the search of filesystems
 *
 * @access  public
 * @package File
 * @author  Ildar N. Shaimordanov <ildar-sh@mail.ru>
 */
class File_Find2
{

    // {{{

    /**
     * Create a view map for the specified path
     *
     * @param  string  $path    Contains the directory path that you want to map
     * @param  array   $options Various options which controls mapping
     * This is an assoc.array, where keys are:
     * mode         - (integer) mapping mode of path (dirs only, files only, or both)
     * fullname     - (boolean) return filename only as result or path to entry too
     * recursive    - (boolean) lookup directories recursively
     * pattern_dir  - (regexp) pattern for matching of dirnames
     * pattern_file - (regexp) pattern for matching of filenames
     * parse        - (boolean) parse or not file content
     * sort         - (integer) sort mode (ascending, descending, natural, user-defined, or no-sorting)
     * helper       - (mixed) the instance or descendant name of the 'File_Find2_Helper'
     *
     * @result array  List of dirs and files or user-defined items as result of the callback function.
     * In the common case this is a multidimensional array containing all subdirectories
     * and their files. For example:
     * <code>
     * Array
     * (
     *    [0] => file_1.php
     *    [1] => file_2.php
     *    [subdirname] => Array
     *       (
     *          [0] => file_1.php
     *       )
     * )
     * </code>
     *
     * @access public
     */
    function maptree($path, $options=array())
    {
        static $defOptions = array(
            'mode'         => FILE_FIND_MODE_BOTH,
            'fullname'     => true,
            'recursive'    => false,
            'pattern_dir'  => false,
            'pattern_file' => false,
            'parse'        => false,
            'sort'         => FILE_FIND_SORT_NOSORT,
            'helper'       => null,
        );

        $options = (array)$options;
        foreach ($defOptions as $k => $v) {
            if ( ! isset($options[$k]) ) {
                $options[$k] = $v;
            }
        }

        $path = preg_replace('/[\\\\\/]+$/', '', $path);
        return File_Find2::_maptree($path, $options);
    }

    // }}}
    // {{{

    function _maptree($path, $options)
    {
        // Check directory to be readable ...
        if ( ! is_readable($path) ) {
            return false;
        }

        $dh = opendir($path);

        // ... and the path to be valid directory
        if ( ! $dh ) {
            return false;
        }

        $f = array();
        $d = array();

        // Processing ...
        while ( false !== ($entry = readdir($dh)) ) {
            // Skip current/upper directories pointers
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }

            $name = $path . '/' . $entry;
            $value = $options['fullname'] ? $name : $entry;

            // Files
            if ( 
                is_file($name) 
                && 
                $options['mode'] >= FILE_FIND_MODE_BOTH 
                && 
                ( ! $options['pattern_file'] || preg_match($options['pattern_file'], $entry) ) 
            ) {
                $result = $options['parse'] ? call_user_func(array($options['helper'], 'parseFile'), $value) : $value;
                if ( $result ) {
                    $f[] = $result;
                }
            }

            // Directories
            if ( 
                is_dir($name) 
                && 
                $options['mode'] <= FILE_FIND_MODE_BOTH 
                && 
                ( ! $options['pattern_dir'] || preg_match($options['pattern_dir'], $entry) ) 
            ) {
                $d[$value] = $options['recursive'] ? File_Find2::_maptree($name, $options) : array();
            }
        }

        closedir($dh);

        // Sorting ...
        switch ($options['sort']) {
        case FILE_FIND_SORT_ASC:
            asort($f);
            ksort($d);
            break;
        case FILE_FIND_SORT_DESC:
            arsort($f);
            krsort($d);
            break;
        case FILE_FIND_SORT_NATURAL:
            natsort($f);
            uksort($d, 'strnatcmp');
            break;
        case FILE_FIND_SORT_USER:
            if ( $options['sort'] ) {
                uasort($f, array($options['helper'], 'compareFiles'));
                uksort($d, array($options['helper'], 'compareDirs'));
            }
        }

        return array_merge($d, $f);
    }

    // }}}

}

// }}}
// {{{

/**
 * Class provides interface of the helper callback functions for sorting of entries and parsing of files
 *
 * @access  public
 * @package File_Find
 * @author  Ildar N. Shaimordanov <ildar-sh@mail.ru>
 */
class File_Find2_Helper
{

    // {{{

    /**
     * Compare of directories
     *
     * @param  string  $item1 The name of a directory
     * @param  string  $item2 The name of another directory
     *
     * @result integer Returns < 0 if $item1 is less than $item2; > 0 if $item1 is greater than $item2, and 0 if they are equal
     *
     * @access public
     */
    function compareDirs($item1, $item2)
    {
        return strnatcmp($item1, $item2);
    }

    // }}}
    // {{{

    /**
     * Compare of files
     *
     * @param  string  $item1 The name of a file
     * @param  string  $item2 The name of another file
     *
     * @result integer Returns < 0 if $item1 is less than $item2; > 0 if $item1 is greater than $item2, and 0 if they are equal
     *
     * @access public
     */
    function compareFiles($item1, $item2)
    {
        return strnatcmp($item1, $item2);
    }

    // }}}
    // {{{

    /**
     * Parse of file
     *
     * @param  string  $item The name of a file
     *
     * @result mixed   Result of handling of a file
     *
     * @access public
     */
    function parseFile($item)
    {
        return $item;
    }

    // }}}

}

// }}}

?>