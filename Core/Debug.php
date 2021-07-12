<?php

/**
*
* Debugging wrapper for standard PHP functions
* such as var_dump(), print_r() and debug_backtrace()
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
* @package    Debug
* @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    $Id: Debug.php,v 1.1 2007/06/08 12:43:17 phpmrtgadmin Exp $
*
*/

// {{{

class Debug
{

    // {{{

    /*
    * Nice wrapper for PHP print_r() and var_dump().
    * Its behavior is equals to Debug::dump() but it makes windowable output.
    *
    * @param   mixed   $var
    * @param   boolean $return
    * @param   integer $nest
    *
    * @return  boolean
    *
    * @access  public
    *
    * @see     For detail see Debug::dump()
    */
    function display($var, $return=false, $nest=0xFFFFFFFF)
    {
        $result = Debug::dump($var, true);
        $result = '<div class="dbg_var_window"><div class="dbg_var_frame"><div class="dbg_var_view">' . $result . '</div></div></div>';

        // Return or output immediately
        if ($return) {
            return $result;
        }
        echo $result;
    }

    // }}}
    // {{{

    /*
    * Nice wrapper for PHP print_r() and var_dump().
    * Prints human-readable information about variables.
    *
    * @param   mixed   $var      Variable to be outputted
    * @param   boolean $return   If this parameter is set to TRUE, the method will return
    *          its output, instead of printing it (which it does by default)
    * @param   integer $nest     Available nesting level (by default, all nesting)
    *
    * @return  boolean If $return argument is set to TRUE, method will return its output
    *
    * @access  public
    */
    function dump($var, $return=false, $nest=0xFFFFFFFF)
    {
        Debug::nice();

        $result = Debug::var_dump(null, $var, $nest);

        // Return or output immediately
        if ($return) {
            return $result;
        }
        echo $result;
    }

    function var_dump($name, $value, $nest=0xFFFFFFFF)
    {
        $v_type = gettype($value);
        switch ($v_type) {
        case 'string':
            $v_str = $value;
            $v_str = str_replace(array("\n", "\t"), array('\\n', '\\t'), $v_str);
            $v_str = htmlspecialchars($v_str);
            $v_str = '&quot;' . $v_str . '&quot;';
            break;
        case 'integer':
        case 'float':
        case 'double':
            $v_str = $value;
            break;
        case 'boolean':
            $v_str = $value ? 'TRUE' : 'FALSE';
            break;
        case 'array':
            $var_is_complex = true;
            $v_str = 'ARRAY[' . count($value) . ']';
            break;
        case 'object':
            $var_is_complex = true;
            $v_str = 'OBJECT ' . get_class($value);
            break;
        case 'NULL':
            $v_str = 'NULL';
            break;
        case 'resource':
            $v_str = get_resource_type($value) . preg_replace('/Resource id (#\d+)/', ' [\1]', $value);
            break;
        }
        $name = (null === $name ? '' : '<span class="dbg_var_name">' . $name . '</span> = ');
        $v_str = $name
            . '<span class="dbg_var dbg_var_' . strtolower($v_type) . '" title="' . $v_type . '">' . $v_str . '</span>';

        // "Simple" variables - scalars, null, and resources
        if (empty($var_is_complex)) {
            return $v_str;
        }

        // Complex variables - arrays and objects
        $result = '';
        if ($nest > 0) {
            $nest--;
            foreach ($value as $k => $v) {
                if (is_array($v) && $k === 'GLOBALS') {
                    continue;
                }
                $result .= "\n" . '<li>' . Debug::var_dump($k, $v, $nest) . '</li>';
            }
        }

        if ($result) {
            return "\n" . '<div class="dbg_var_complex">' . $v_str . '<ul>' . $result . '</ul></div>';
        }

        return $v_str;
    }

    // }}}
    // {{{

    /*
    * Nice wrapper for PHP debug_backtrace().
    * Prints human-readable information about a backtrace.
    *
    * @param   array   $trace    If the $trace argument is sets it will be returned
    * @param   boolean $return   If this parameter is set to TRUE, the method will return
    *          its output, instead of printing it (which it does by default)
    *
    * @return  boolean If $return argument is set to TRUE, method will return its output
    *
    * @access  public
    */
    function backtrace($trace=false, $return=false)
    {
        Debug::nice();

        if (empty($trace)) {
            $trace = debug_backtrace();
            array_shift($trace);
        }

        $result = '';
        $i = count($trace);
        while ($i--) {
            $func = '<span class="dbg_function">';
            if (isset($trace[$i]['class'])) {
                $func .= $trace[$i]['class'] . $trace[$i]['type'];
            }
            $func .= $trace[$i]['function'] . '</span>';

            $args = array();
            foreach ($trace[$i]['args'] as $arg) {
                $args[] = '<span class="dbg_arg">' . Debug::var_dump(null, $arg, 0) . '</span>';
            }
            if ($args) {
                $func .= '<span class="dbg_args_left">(</span>';
                $func .= '<span class="dbg_args">' . implode('<span class="dbg_args_separator">,</span>' . "\n", $args) . '</span>';
                $func .= '<span class="dbg_args_right">)</span>';
            } else {
                $func .= '<span class="dbg_args_empty">()</span>';
            }

            $result .= '<tr class="dbg_row">' . "\n"
                . '<td class="dbg_td1">'
                . '<span class="dbg_file">' . @$trace[$i]['file'] . '</span>'
                . '<span class="dbg_line">' . @$trace[$i]['line'] . '</span>'
                . '</td>' . "\n"
                . '<td class="dbg_td2">' . $func . '</td>' . "\n"
                . '</tr>' . "\n";
        }

        // Non-empty backtrace
        if ($result) {
            $result = '<table class="dbg_table">' . $result . '</table>';
        }

        // Return or output immediately
        if ($return) {
            return $result;
        }
        echo $result;
    }

    // }}}
    // {{{

    /*
    * Print out predefined CSS styles and JavaScript for Debug::backtrace() and Debug::dump()
    *
    * @param   void
    *
    * @retrun  void
    *
    * @access  public
    */
    function nice()
    {
        $useNice =& Debug::_getUseNice();
        if ($useNice <= 0) {
            return;
        }
        $useNice = -$useNice;

        static $use = array(
            'styles.css' => array(
                '<style type="text/css"><!--',
                '//--></style>',
            ),
            'scripts.js'  => array(
                '<script type="text/javascript"><!--',
                '//--></script>',
            ),
        );
        foreach ($use as $k => $v) {
            $file = @file_get_contents(dirname(__FILE__) . '/Debug/' . $k);
            if (trim($file)) {
                echo $v[0] . "\n" . $file . $v[1] . "\n";
            }
        }
    }

    // }}}
    // {{{

    /*
    * Turn on and print out (if it is necessary) predefined
    * CSS styles and JavaScript for Debug::backtrace() and Debug::dump()
    *
    * @param   boolean $here   If it is TRUE, print out predefined ones
    *
    * @retrun  void
    *
    * @access  public
    */
    function useNice($here=false)
    {
        $useNice =& Debug::_getUseNice();
        $useNice = 1;
        if ($here) {
            Debug::nice();
        }
    }

    // }}}
    // {{{

    function & _getUseNice()
    {
        // Available values:
        //      0 - turn off
        //    > 0 - turn on
        //    < 0 - already loaded
        static $useNice = 0;
        return $useNice;
    }

    // }}}

}

// }}}

?>