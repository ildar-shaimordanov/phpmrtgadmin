<?

/**
 * phpMyAdmin:
 *       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
 * Version:
 *       1.05
 *
 * Copyright (C) 2005-2006 Ildar N. Shaimordanov
 *
 * Licensed under the terms of the GNU General Public License:
 * http://opensource.org/licenses/gpl-license.php
 *
 * File Name: MRTG.php
 *       This is the part of phpMrtgAdmin
 *
 * File Authors:
 *       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
 */

// {{{

/**
 *
 * subsidiary package
 * It is neseccary for files and directories reading
 *
 */
include_once 'Utils/File_Find2.php';
include_once 'Core/Utils/PCRE.php';

// }}}
// {{{

/**
 *
 * Predefined constant for estimating time interval (in minutes)
 * between current time and access time to file
 *
 */
define('MRTG_MODIFIED_LAST_5_MINUTES', 5);

// }}}
// {{{

/**
 *
 * General class for reading, sorting and resulting in content of MRTG files
 *
 */
class MRTG extends File_Find2_Helper
{

    // {{{

    /**
     * Absolute or relative path to MRTG storage
     *
     * var     string
     * @access private
     */
    var $_base_path;

    // }}}
    // {{{

    /**
     * List of all systems within the MRTG storage path
     *
     * @var    array
     * @access private
     */
    var $_systems = array();

    // }}}
    // {{{

    /**
     * Internal callback for compare of files
     *
     * @var    string
     * @access private
     */
    var $_compareFilesOrdered = '';

    // }}}
    // {{{

    /**
     * MRTG handling options
     *
     * @var    array
     * @access private
     */
    var $_options = array(
        'outofdate_period' => '',
        'if_type'          => array(),
        'sort_by'          => '',
        'sort_order'       => '',
        'search_by'        => '',
        'search_string'    => array(),
        'search_chr'       => '',
        'search_range'     => array(),
    );

    // }}}
    // {{{

    /**
     * Create instance of MRTG class
     *
     * @param  string $base_path
     *         Valid absolute or relative path to MRTG storage
     *
     * @result MRTG:: class or FALSE if $base_path is not valid
     * @access public
     */
    function & getInstance($base_path='mrtg', $options=array())
    {
        $object =& new MRTG($base_path);
        $object->setOptions($options);

        return $object;
    }

    // }}}
    // {{{

    /**
     * PHP4 constructor
     *
     * @param  string $base_path
     *         Valid absolute or relative path to MRTG storage
     *
     * @result MRTG:: class
     * @access public
     */
    function MRTG($base_path='mrtg')
    {
        $this->__construct($base_path);
    }

    // }}}
    // {{{

    /**
     * PHP5 constructor
     *
     * @see    MRTG::MRTG
     */
    function __construct($base_path='mrtg')
    {
        $base_path = realpath($base_path);
        $this->_base_path = $base_path;
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
     * @access public
     */
    function compareFiles($item1, $item2)
    {
        return call_user_func(
            array(&$this, $this->_compareFilesOrdered), 
            $item1, 
            $item2
        );
    }

    // }}}
    // {{{

    /**
     * Reads appropriate HTML-file and parse for detailed info about interface
     *
     * @param  string   $system    Name of a interface
     *
     * @result array    Info collected from HTML-file
     * @access public
     */
    function parseFile($file)
    {
        // Skip out-of-dated files
        if ( $this->_options['outofdate_period'] && $this->_options['outofdate_period'] != 'never' && $this->_ago($file, $this->_options['outofdate_period']) ) {
            return false;
        }

        $html = file_get_contents($file);

        $r = array();

        $r = array (
            'system' => array (
                'html_if_title_desc' => '',
                'sysname' => '',
                'html_syslocation' => '',
                'html_syscontact' => '',
                'html_if_snmp_descr' => '',
                'html_if_snmp_alias' => '',
                'html_if_cisco_descr' => '',
                'html_if_type_desc' => '',
                'if_type_num' => '',
                'html_if_snmp_name' => '',
                'if_port_name' => '',
                'if_speed_str' => '',
                'if_ip' => '',
                'if_dns_name' => '',
            ),
            'update' => '',
            'periods' => array (
                'd' => array (
                    'maxin' => '',
                    'maxout' => '',
                    'avin' => '',
                    'avout' => '',
                    'cuin' => '',
                    'cuout' => '',
                    'avmxin' => '',
                    'avmxout' => '',
                ),
                'w' => array (
                    'maxin' => '',
                    'maxout' => '',
                    'avin' => '',
                    'avout' => '',
                    'cuin' => '',
                    'cuout' => '',
                    'avmxin' => '',
                    'avmxout' => '',
                ),
                'm' => array (
                    'maxin' => '',
                    'maxout' => '',
                    'avin' => '',
                    'avout' => '',
                    'cuin' => '',
                    'cuout' => '',
                    'avmxin' => '',
                    'avmxout' => '',
                ),
                'y' => array (
                    'maxin' => '',
                    'maxout' => '',
                    'avin' => '',
                    'avout' => '',
                    'cuin' => '',
                    'cuout' => '',
                    'avmxin' => '',
                    'avmxout' => '',
                ),
            ),
            'files' => array (
                'html' => '',
                'png' => '',
                'log' => '',
            ),
            'URLs' => array (
                'html' => '',
                'png' => '',
                'log' => '',
            ),
        );

        // read system info
        // <H1>$html_desc_prefix$html_if_title_desc -- $sysname</H1>
        // <H1>Traffic analysis for $html_if_title_desc -- $sysname</H1>
        preg_match('/<h1>Traffic\s+analysis\s+for\s+(.*?)?\s*--\s*.*?<\/h1>/i', $html, $matches);
	/**
	 * SEARCH BY
	 */
        $r['system']['html_if_title_desc'] = @$matches[1] ? $matches[1] : '';

        // <TR><TD>System:</TD>     <TD>$sysname in $html_syslocation</TD></TR>
        preg_match('/<td>\s*System\s*:\s*<\/td>\s*<td>\s*(.*?)?\s+in\s+(.*?)?\s*<\/td>/i', $html, $matches);
        $r['system']['sysname'] = @$matches[1] ? $matches[1] : '';
        $r['system']['html_syslocation'] = @$matches[2] ? $matches[2] : '';

        // <TR><TD>Maintainer:</TD> <TD>$html_syscontact</TD></TR>
        preg_match('/<td>\s*Maintainer\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*<\/td>/i', $html, $matches);
        $r['system']['html_syscontact'] = @$matches[1] ? $matches[1] : '';

        // <TR><TD>Description:</TD><TD><span>$html_if_snmp_descr</span><span>$html_if_snmp_alias</span><span>$html_if_cisco_descr</span></TD></TR>
        /*
        preg_match('/<td>\s*Description\s*:\s*<\/td>\s*<td><span>(.*?)?</span><span>(.*?)?</span><span>(.*?)?</span><\/td>/i', $html, $matches);
        $r['system']['html_if_snmp_descr'] = @$matches[1] ? $matches[1] : '';
        $r['system']['html_if_snmp_alias'] = @$matches[2] ? $matches[2] : '';
        $r['system']['html_if_cisco_descr'] = @$matches[3] ? $matches[3] : '';
        */

        // <TR><TD>Description:</TD><TD>$html_if_description</TD></TR>
        preg_match('/<td>\s*Description\s*:\s*<\/td>\s*<td>\s*(.*?)?\s+(.*?)?\s*<\/td>/i', $html, $matches);
	/**
	 * SEARCH BY
	 */
        $r['system']['html_if_snmp_descr'] = @$matches[1] ? $matches[1] : '';
	/**
	 * SEARCH BY
	 */
        $r['system']['html_if_snmp_alias'] = @$matches[2] ? $matches[2] : '';
        $r['system']['html_if_cisco_descr'] = '';

        // <TR><TD>ifType:</TD>     <TD>$html_if_type_desc ($if_type_num)</TD></TR>
        preg_match('/<td>\s*ifType\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*\(\s*(.*?)?\s*\)\s*<\/td>/i', $html, $matches);
        $r['system']['html_if_type_desc'] = @$matches[1] ? $matches[1] : '';
	/**
	 * SEARCH BY
	 */
        $r['system']['if_type_num'] = @$matches[2] ? $matches[2] : '';

        // <TR><TD>ifName:</TD>     <TD>$html_if_snmp_name</TD></TR>
        preg_match('/<td>\s*ifName\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*<\/td>/i', $html, $matches);
	/**
	 * SEARCH BY
	 */
        $r['system']['html_if_snmp_name'] = @$matches[1] ? $matches[1] : '';

        // <TR><TD>Port Name:</TD>  <TD>$if_port_name</TD></TR>
        preg_match('/<td>\s*Port\s+Name\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*<\/td>/i', $html, $matches);
        $r['system']['if_port_name'] = @$matches[1] ? $matches[1] : '';

        // <TR><TD>Max Speed:</TD>  <TD>$if_speed_str</TD></TR>
        preg_match('/<td>\s*Max\s+Speed\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*<\/td>/i', $html, $matches);
        $r['system']['if_speed_str'] = @$matches[1] ? $matches[1] : '';

        // <TR><TD>Ip:</TD>         <TD>$if_ip ($if_dns_name)</TD>
        preg_match('/<td>\s*Ip\s*:\s*<\/td>\s*<td>\s*(.*?)?\s*\(\s*(.*?)?\s*\)\s*<\/td>/i', $html, $matches);
	/**
	 * SEARCH BY
	 */
        $r['system']['if_ip'] = $matches ? $matches[1] : '';
        $r['system']['if_dns_name'] = $matches ? $matches[2] : '';

        // last update time
        preg_match_all('/<b>\s*([^<]*)\s*<\/b>/i', $html, $matches, PREG_SET_ORDER);
        $r['update'] = $matches ? $matches[0][1] : '';

        // read statistics info for all periods presented within the file such as
        // <!-- name period value -->
        preg_match_all('/<\!--\s+(\w+)\s+(\w+)\s+(\d+)\s+-->/', $html, $matches, PREG_SET_ORDER);
        if ( ! empty($matches) ) {
            for ($i = 0; $i < count($matches); $i++) {
                $name   = $matches[$i][1];
                $period = $matches[$i][2];
                $value  = $matches[$i][3];
                $r['periods'][$period][$name] = $value;
            }
        }

        // set names of files
        $r['files'] = array(
            'html' => $file,
            'png'  => preg_replace('/\.html$/', '-day.png', $file),
            'log'  => preg_replace('/\.html$/', '.log', $file),
        );

        // set URLs
        $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $r['URLs'] = array(
            'html' => str_replace($doc_root, '', str_replace('\\', '/', $r['files']['html'])),
            'png'  => str_replace($doc_root, '', str_replace('\\', '/', $r['files']['png'])),
            'log'  => str_replace($doc_root, '', str_replace('\\', '/', $r['files']['log'])),
        );

        return $r;

/*
        // actual interface only
        if ( $this->_options['if_type'] && ! in_array($r['system']['if_type_num'], $this->_options['if_type']) ) {
            return false;
        }

        // specified interface marked by search_string
        if ( $this->_options['search_by'] && $this->_options['search_string'] ) {
            $found_match = false;
            foreach ($this->_options['search_string'] as $search_string) {
                if ( preg_match($search_string, $r['system'][$this->_options['search_by']]) ) {
                    $found_match = true;
                    continue;
                }
            }
            if ( ! $found_match ) {
                return false;
            }
        }
*/

        return $r;
    }

    // }}}
    // {{{

    /**
     * Validate and scans MRTG::_base_path for list of directories
     *
     * @param  none
     *
     * @result array  If path is valid and is not empty
     *         then method will returns list of directories
     * @access piblic
     */
    function readAllSystems()
    {
        $dirs = File_Find2::maptree($this->_base_path, array(
            'mode' => FILE_FIND_MODE_DIRS,
            'sort' => FILE_FIND_SORT_NATURAL,
        ));

        if ( empty($dirs) ) {
            return false;
        }

        $this->_systems = array();
        foreach (array_keys($dirs) as $system) {
            preg_match('/([^\/\\\\]+)$/', $system, $matches);
            $this->_systems[] = array(
                'name' => $matches[1],
                'path' => $system,
            );
        }

        return $this->_systems;
    }

    // }}}
    // {{{

    /**
     * Reads appropriate HTML-file and parse for detailed info about interface
     *
     * @param  string $system    Name of a interface
     *
     * @result array  Info collected from HTML-file
     * @access public
     */
    function readInterfaces($system)
    {
        $path = '';
        foreach ($this->_systems as $k => $v) {
            if ( $system == $v['name'] ) {
                $path = $v['path'];
                break;
            }
        }

        if ( empty($path) ) {
            return false;
        }

        $sort_mode = $this->_options['sort_by'] && strnatcasecmp($this->_options['sort_by'], 'unsorted')
            ? FILE_FIND_SORT_USER
            : FILE_FIND_SORT_NOSORT;

        $result = File_Find2::maptree($path, array(
            'mode'         => FILE_FIND_MODE_FILES,
            'pattern_file' => '/\.html$/',
            'parse'        => true,
            'sort'         => $sort_mode,
            'helper'       => &$this,
        ));

        // actual interface only
        if ( $this->_options['if_type'] ) {
            $result = $this->_searchByIftype($result);
        }

        // specified interface marked by search_string
        if ( $this->_options['search_by'] && $this->_options['search_string'] ) {
            $result = $this->_searchBy($result);
        }

        // specified interface marked by search_letters
        if ( $this->_options['search_chr'] ) {
            $result = $this->_searchByChr($result);
        }

        return $result;
    }

    // }}}
    // {{{

    function setOptions($options)
    {
        $options = (array)$options;
        foreach ($this->_options as $k => $v) {
            if ( isset($options[$k]) ) {
                $this->_options[$k] = $options[$k];
            }
        }

        if ( ! is_array($this->_options['if_type']) ) {
            $this->_options['if_type'] = preg_split('/\s+/', $this->_options['if_type']);
        }

        if ( $this->_options['search_string'] && ! is_array($this->_options['search_string']) ) {
            $search_strings = preg_split('/\s+/', $this->_options['search_string']);
            $this->_options['search_string'] = array();
            foreach ($search_strings as $search_string) {
                $this->_options['search_string'][] = PCRE::pattern2regexp($search_string);
            }
        }

#        $this->_options['search_range'] = array_merge(array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), range('A', 'Z'));
        $this->_options['search_range'] = array_merge(range('0', '9'), range('A', 'Z'));
        if ( ! in_array($this->_options['search_chr'], $this->_options['search_range']) ) {
            $this->_options['search_letters'] = '';
        }

        if ( 0 == strnatcasecmp($this->_options['sort_order'], 'ASC') ) {
            $this->_compareFilesOrdered = '_cmpAsc';
        }
        if ( 0 == strnatcasecmp($this->_options['sort_order'], 'DESC') ) {
            $this->_compareFilesOrdered = '_cmpDesc';
        }

        return;
    }

    // }}}
    // {{{

    function _ago($file, $minutes)
    {
        clearstatcache();
        return time() - filemtime($file) >= $minutes * 60;
    }

    function _cmpAsc($a, $b)
    {
        return strnatcmp(
            $a['system'][$this->_options['sort_by']], 
            $b['system'][$this->_options['sort_by']]
        );
    }

    function _cmpDesc($a, $b)
    {
        return -strnatcmp(
            $a['system'][$this->_options['sort_by']], 
            $b['system'][$this->_options['sort_by']]
        );
    }

    // }}}
    // {{{

    function _searchByIftype($ifs)
    {
        $result = array();

        foreach ($ifs as $k => $r) {
            if ( ! in_array($r['system']['if_type_num'], $this->_options['if_type']) ) {
                continue;
            }
            $result[] = $ifs[$k];
        }

        return $result;
    }

    // }}}
    // {{{

    function _searchBy($ifs)
    {
        $result = array();

        foreach ($ifs as $k => $r) {
            $found_match = false;
            foreach ($this->_options['search_string'] as $search_string) {
                if ( preg_match($search_string, $r['system'][$this->_options['search_by']]) ) {
                    $found_match = true;
                    continue;
                }
            }
            if ( ! $found_match ) {
                continue;
            }
            $result[] = $ifs[$k];
        }

        return $result;
    }

    // }}}
    // {{{

    function _searchByChr($ifs)
    {
        $result = array();

        foreach ($ifs as $k => $r) {
            $str = preg_replace('/^[^0-9A-Z]+/i', '', $r['system']['html_if_snmp_alias']);
            if ( 0 !== strpos($str, $this->_options['search_chr']) ) {
                continue;
            }
            $result[] = $ifs[$k];
        }

        return $result;
    }

    // }}}

}

// }}}

?>