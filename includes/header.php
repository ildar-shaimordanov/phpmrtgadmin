<?

/*
* phpMyAdmin:
*       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
* Version:
*       1.06
*
* Copyright (C) 2005-2006 Ildar N. Shaimordanov
*
* Licensed under the terms of the GNU General Public License:
* http://opensource.org/licenses/gpl-license.php
*
* File Name: header.php
*       This is the part of phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*/

// {{{

include_once 'includes/MRTG.php';
include_once 'includes/SNMP_Types.php';
include_once 'Pager/Pager.php';
include_once 'Net/IPv4.php';

// }}}
// {{{

/*
*
* phpMrtgAdmin title and version
*
*/
define('PHP_MRTG_ADMIN_TITLE', 'phpMrtgAdmin');
define('PHP_MRTG_ADMIN_VERSION', '1.06');

// }}}
// {{{

/*
*
* New sheet value
*
*/
define('NEW_SHEET_VALUE', -1);

// }}}
// {{{

/*
*
* Full filename of the sheets configuration file
*
*/
define('CONFIG_FILENAME', str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $GLOBALS['setup']['defaults']['configfile']);

// }}}
// {{{

function message_die($error_cond, $message_str='')
{
	if ( ! $error_cond ) {
		return;
	}
	if ( empty($message_str) ) {
		$message_str = 'Error occure!';
	}
	echo '<html><head><title>' . $message_str . '</title></head><body><h1>' . $message_str . '</h1></body></html>';
	exit;
}

// }}}
// {{{

function sheetTitle($sheet_config, $parseEntities=true)
{
	$result = @$sheet_config['sheet_title'] ? $sheet_config['sheet_title'] : 'Interfaces of ' . $sheet_config['if_system'];
	if ($parseEntities) {
		$result = htmlspecialchars($result);
	}
	return $result;
}

// }}}
// {{{

function printNavigateSheets($sheet, $config)
{
	global $template;

	$template->set_filenames(array(
		'navigate_sheets' => 'navigateSheets',
	));

	foreach ($config as $k => $v) {
		$template->assign_block_vars('sheet', array(
			'SHEET_TITLE' => sheetTitle($v),
#			$v['sheet_title'] ? htmlspecialchars($v['sheet_title']) : 'Interfaces of ' . $v['if_system'],
			'SHEET_NUM' => $k,
			'SELECTED' => $k == $sheet ? ' selected="selected" ' : '',
		));
	}

	$template->assign_var_from_handle('NAVIGATE_SHEETS', 'navigate_sheets');
}

// }}}
// {{{

function printInterfaces($ifs, $sheet_config)
{
	global $template, $displayCriteria, $templatePrefix;

	// Prepare sheet
	$template->set_filenames(array(
		'body' => $templatePrefix,
		'interface' => $templatePrefix . $sheet_config['template_name'],
	));

	if ( empty($ifs) ) {
		$template->assign_vars(array(
			'TITLE' => 'There is no data',
		));
		return;
	}

	$gmt = gmdate('D, d M Y H:i:s') . ' GMT';
	$refresh = $sheet_config['update_period'] * 60;

	header('Refresh: ' . $refresh);
	header('Expires: ' . $gmt);
	header('Last-Modified: ' . $gmt);
	header('Cache-Control: no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0');
	header('Cache-Control: max-age=0');
	header('Pragma: no-cache');

	$template->assign_vars(array(
		'U_INTERFACE' => $_SERVER['REQUEST_URI'],
		'TITLE' => sheetTitle($sheet_config),
		'PHP_MRTG_ADMIN_VERSION' => PHP_MRTG_ADMIN_VERSION,
		'IF_SYSTEM' => $sheet_config['if_system'],

		'META_REFRESH' => $refresh,
		'META_EXPIRES' => $gmt,
		'META_LAST_MODIFIED' => $gmt,
	));

	foreach ($displayCriteria as $k => $v) {
		if ( ! in_array($k, $sheet_config['display_info']) ) {
			continue;
		}
		$template->assign_block_vars($k, array(
			'DISPLAY_NAME' => $v,
		));
	}

	// Display multipage pager
	$pager_mode = ( @$GLOBALS['setup']['Pager']['mode'] && ( 0 == strnatcasecmp($GLOBALS['setup']['Pager']['mode'], 'jumping') || 0 == strnatcasecmp($GLOBALS['setup']['Pager']['mode'], 'sliding') ) ) ? $GLOBALS['setup']['Pager']['mode'] : 'Sliding';
	$pager_perPage = ( (int)@$GLOBALS['setup']['Pager']['perPage'] ) ? $GLOBALS['setup']['Pager']['perPage'] : 10;
	$pager_delta = ( 0 == strnatcasecmp($pager_mode, 'Sliding') ) ? $pager_perPage >> 1 : $pager_perPage;
	$pager =& Pager::factory(array(
		'mode'		=> $pager_mode,
		'perPage'	=> $pager_perPage,
		'delta'		=> $pager_delta,
		'expanded'	=> true,
		'itemData'	=> $ifs,
		'urlVar'	=> 'p',
	));

	$pager_links = $pager->getLinks();
	$all_pages = $pager->numPages() + 1;
	$template->assign_block_vars('pagination', array(
		'PAGINATION'	=> $pager_links['all'],
		'ALL_LINK'	=> $_SERVER['SCRIPT_NAME'] . '?sheet=' . ( @$_GET['sheet'] ) . '&p=' . $all_pages,
		'ALL_TEXT'	=> 'All',
	));
	if ( $pager->numPages() > 1 && @$_GET['p'] != $all_pages ) {
		$template->assign_block_vars('pagination.all_pages', array(
			'LINK'	=> $_SERVER['SCRIPT_NAME'] . '?sheet=' . ( @$_GET['sheet'] ) . '&p=' . $all_pages,
			'TEXT'	=> 'All',
		));
	}

	$pager_data = @$_GET['p'] == $all_pages ? $ifs : $pager->getPageData();
	foreach ($pager_data as $k => $if) {
		$template->assign_block_vars('interface', array(
			'ZEBRA' => $k % 2 ? 'zebra_dark' : 'zebra_light',

			'U_LOG'  => $_SERVER['SCRIPT_NAME'] . '?action=detail&what=log&if_system='  . $sheet_config['if_system'] . '&html_if_title_desc=' . $if['system']['html_if_title_desc'],
			'U_CALC' => $_SERVER['SCRIPT_NAME'] . '?action=detail&what=calc&if_system=' . $sheet_config['if_system'] . '&html_if_title_desc=' . $if['system']['html_if_title_desc'],
			'U_HTML' => $if['URLs']['html'],
			'U_IMG' => $if['URLs']['png'],
		));
		if (in_array('html_if_title_desc', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.html_if_title_desc', array(
				'DISPLAY_NAME' => $displayCriteria['html_if_title_desc'],
				'HTML_IF_TITLE_DESC' => $if['system']['html_if_title_desc'],
			));
		}
		if (in_array('html_if_snmp_name', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.html_if_snmp_name', array(
				'DISPLAY_NAME' => $displayCriteria['html_if_snmp_name'],
				'HTML_IF_SNMP_NAME' => $if['system']['html_if_snmp_name'],
			));
		}
		if (in_array('html_if_snmp_descr', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.html_if_snmp_descr', array(
				'DISPLAY_NAME' => $displayCriteria['html_if_snmp_descr'],
				'HTML_IF_SNMP_DESCR' => $if['system']['html_if_snmp_descr'],
			));
		}
		if (in_array('html_if_snmp_alias', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.html_if_snmp_alias', array(
				'DISPLAY_NAME' => $displayCriteria['html_if_snmp_alias'],
				'HTML_IF_SNMP_ALIAS' => $if['system']['html_if_snmp_alias'],
			));
		}
		if (in_array('if_type_num', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.if_type_num', array(
				'DISPLAY_NAME' => $displayCriteria['if_type_num'],
				'HTML_IF_TYPE_DESC' => $if['system']['html_if_type_desc'],
				'IF_TYPE_NUM' => $if['system']['if_type_num'],
			));
		}
		if (in_array('if_ip', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.if_ip', array(
				'DISPLAY_NAME' => $displayCriteria['if_ip'],
				'IF_IP' => $if['system']['if_ip'],
				'IF_DNS_NAME' => $if['system']['if_dns_name'],
			));
		}
		if (in_array('images', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.images', array(
				'U_HTML' => $if['URLs']['html'],
				'U_IMG' => $if['URLs']['png'],
			));
		}
		if (in_array('actual_info', $sheet_config['display_info'])) {
			$template->assign_block_vars('interface.actual_info', array(
				'DISPLAY_NAME' => @$displayCriteria['actual_info'],
				'IF_SPEED_STR' => @$if['system']['if_speed_str'],
				'LAST_UPDATE' => @$if['update'],
				'MAXIN' => @$if['periods']['d']['maxin'],
				'AVIN' => @$if['periods']['d']['avin'],
				'CUIN' => @$if['periods']['d']['cuin'],
				'MAXOUT' => @$if['periods']['d']['maxout'],
				'AVOUT' => @$if['periods']['d']['avout'],
				'CUOUT' => @$if['periods']['d']['cuout'],
			));
		}
	}

	// Display alphanumeric pager
#	if ( $pager->numPages() > 1 ) {
		$pager =& Pager::factory(array(
			'mode'		=> 'Alphanumeric',
			'itemData'	=> $GLOBALS['mrtg']->_options['search_range'],
			'excludeVars'	=> array('p'),
		));
		$links = $pager->getLinks();
		$template->assign_block_vars('search_letters', array(
			'SEARCH_LETTERS'	=> $links['all'],
		));
#	}

	$template->assign_var_from_handle('INTERFACE', 'interface');
}

// }}}
// {{{

// Available values of sorting and searching
$availCriteria = array(
	'html_if_title_desc' => '#',
	'html_if_snmp_name' => 'SNMP name',
	'html_if_snmp_descr' => 'SNMP decription',
	'html_if_snmp_alias' => 'SNMP alias',
	'if_type_num' => 'Interface type',
	'if_ip' => 'IP',
);
$sortCriteria = array_merge(array('unsorted' => 'Unsorted'), $availCriteria);
$displayCriteria = array_merge(array('images' => 'Images', 'actual_info' => 'Actual info'), $availCriteria);
$availSortOrder = array(
	'ASC' => 'Ascending',
	'DESC' => 'Descending',
);

$templatePrefix = 'interface';
$templateSuffix = $template->extension;
$templatePattern = '/^' . $templatePrefix . '([A-Z][^\.]+)\\' . $templateSuffix . '$/';

$updatePeriods = array(1, 5, 7, 10, 15, 20, 30);
$outofdatePeriods = array(5, 10, 15, 20, 30, 60, 120, 'never');

// }}}
// {{{

function prepareSheetData()
{
        global $availCriteria, $outofdatePeriods;

	global $config, $sheet, $sheet_config;

	// Read config
	// Validate all data and count of sheets
	$config = IniFile::read(CONFIG_FILENAME);

	if ( empty($config) || empty($config['sheet']) || count($config['sheet']) > $GLOBALS['setup']['defaults']['maxsheets']) {
		$config['sheet'] = array();
	}

	$config = array(
		'sheet' => @$config['sheet'] && count($config['sheet']) <= $GLOBALS['setup']['defaults']['maxsheets'] ? $config['sheet'] : array(),
		'combo' => @$config['combo'] ? $config['combo'] : array(),
	);

	// Validate out-of-date periods of each seet 
	// and set to default value if not exists
	foreach ($config['sheet'] as $k => $v) {
		if ( empty($v['outofdate_period']) || !in_array($v['outofdate_period'], $outofdatePeriods) ) {
			$config['sheet'][$k]['outofdate_period'] = $outofdatePeriods[0];
		}
	}

	// Validate sheet number
	$_GET['sheet'] = (int)@$_GET['sheet'];
	if ( $_GET['sheet'] < 0 || $_GET['sheet'] >= count($config['sheet']) ) {
#		$sheet = count($config['sheet']) - 1;
		$sheet = NEW_SHEET_VALUE;
		$sheet_config = array(
			'if_system' => '',
			'if_type' => array(),
			'sort_by' => '',
			'sort_order' => '',
			'search_by' => '',
			'search_string' => '',
			'display_info' => array_keys($availCriteria),
			'sheet_title' => '',
			'template_name' => 'Default',
			'update_period' => 5,
		);
	} else {
		$sheet = $_GET['sheet'];
		$sheet_config = $config['sheet'][$sheet];
		$sheet_config['if_type'] = empty($sheet_config['if_type']) ? array() : preg_split('/\s+/', $sheet_config['if_type']);
		$sheet_config['display_info'] = empty($sheet_config['display_info']) ? array_keys($availCriteria) : preg_split('/\s+/', $sheet_config['display_info']);
	}
}

// }}}
// {{{

function checkSetup()
{
	global $setup;

	if (empty($setup['IP']['availedit'])) {
		$setup['IP']['availedit'] = '127.0.0.1';
	}

	$setup['IP']['availedit'] = trim($setup['IP']['availedit']);
        $setup['nets'] = array();

	$ips = preg_split('/\s+/', $setup['IP']['availedit']);
	foreach ($ips as $k => $ip) {
		$setup['nets']['availedit'][$k] =& Net_IPv4::parseAddress($ip);
		if (PEAR::isError($setup['nets']['availedit'][$k])) {
		        unset($setup['nets']['availedit'][$k]);
		}
	}
}

// }}}
// {{{

function isAvailableEdit()
{
	global $setup;

	foreach ($setup['nets']['availedit'] as $net) {
		if (($net->network && $net->broadcast 
		&& strnatcasecmp($_SERVER['REMOTE_ADDR'], $net->network) >= 0 
		&& strnatcasecmp($_SERVER['REMOTE_ADDR'], $net->broadcast) <= 0)
		|| strnatcasecmp($_SERVER['REMOTE_ADDR'], $net->ip) == 0) {
			return true;
		}
	}
	return false;
}

// }}}

?>