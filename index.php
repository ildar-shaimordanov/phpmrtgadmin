<?

/*
* phpMyAdmin:
*       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
* Version:
*       1.04
*
* Copyright (C) 2005-2006 Ildar N. Shaimordanov
*
* Licensed under the terms of the GNU General Public License:
* http://opensource.org/licenses/gpl-license.php
*
* File Name: index.php
*       This is the main file for phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*/

// {{{

include_once 'Core.php';
include_once 'includes/header.php';

// }}}
// {{{

checkSetup();

switch (@$_GET['action']) {
case 'construct':

	// Validate an ability to edit
	$result = isAvailableEdit();
	message_die(!$result, 'You cannot view this page. <br />Please contact with administrator for details.');

	/*
	*
	* BEGIN OF CONSTRUCTOR
	*
	*/

	// read config from config-file
	// translate sheet-number from the GET request
	// customize actual sheet_config
	prepareSheetData();

	// Create MRTG handle and iterface types
	$mrtg =& MRTG::getInstance($setup['defaults']['mrtgpath']);
	$systems = $mrtg->readAllSystems();

	switch (@$_GET['panel']) {
	case 'left':

		/*
		*
		* BEGIN OF LEFT PANEL
		*
		*/

		$types =& new SNMP_Types;

		// Create template list
		$template_names = File_Find2::maptree($template->root, array(
			'mode'		=> FILE_FIND_MODE_FILES,
			'fullname'	=> false,
			'pattern_file'	=> $templatePattern,
		));

		// Prepare sheet
		$template->set_filenames(array(
			'body' => 'navigate',
		));
		$template->assign_vars(array(
			'U_INTERFACE' => $_SERVER['REQUEST_URI'],
			'U_NAVIGATE' => $_SERVER['REQUEST_URI'],

			'U_HELP_SHEET' => 'doc/index.html#SHEET',
			'U_HELP_SYSTEM' => 'doc/index.html#SYSTEM',
			'U_HELP_TYPE' => 'doc/index.html#TYPE',
			'U_HELP_SORT' => 'doc/index.html#SORT',
			'U_HELP_SEARCH' => 'doc/index.html#SEARCH',
			'U_HELP_DISPLAY' => 'doc/index.html#DISPLAY',
			'U_HELP_OPTIONS' => 'doc/index.html#OPTIONS',

			'SHEET' => $sheet,
		));

		// Sheets:
		// Enable/Disable "Save" and "Delete" buttons
		if ( count($config['sheet']) < $GLOBALS['setup']['defaults']['maxsheets'] ) {
			$template->assign_block_vars('enable_add_option', array(
				'NEW_SHEET' => NEW_SHEET_VALUE,
			));
		}
		if ( $sheet <= count($config['sheet']) ) {
			$template->assign_block_vars('enable_add_button', array(
			));
		}
		if ( $sheet >= 0 && $sheet < count($config['sheet']) ) {
			$template->assign_block_vars('enable_del_button', array(
			));
		}

		// Nagivation through sheets
		$template->assign_block_vars('constructor', array(
		));
		printNavigateSheets($sheet, $config['sheet'], true);

		// Systems
		foreach ($systems as $k => $v) {
			$template->assign_block_vars('if_system', array(
				'IF_SYSTEM' => $v['name'],
				'SELECTED' => $v['name'] == $sheet_config['if_system'] ? ' selected="selected" ' : '',
			));
		}

		// Types of interfaces
		foreach ($types->if_grouped as $if_type_num => $v) {
			$template->assign_block_vars('ifTypeGroup', array(
				'IF_TYPE_NUM' => $if_type_num,
				'IF_TYPE_DESC' => $v['desc'],
				'CHECKED' => in_array($if_type_num, $sheet_config['if_type']) ? ' checked="checked" ' : '',
			));
			$template->assign_block_vars('ifTypeGroupJs', array(
				'IF_TYPE_NUM' => $if_type_num,
				'IF_TYPE_DESC' => $v['desc'],
				'IF_TYPE_VALUES' => implode(', ', $v['list']),
			));
		}
		foreach ($types->if_type as $if_type_num => $if_type_desc) {
			$template->assign_block_vars('ifType', array(
				'IF_TYPE_NUM' => $if_type_num,
				'IF_TYPE_DESC' => htmlspecialchars($if_type_desc),
				'SELECTED' => in_array($if_type_num, $sheet_config['if_type']) ? ' selected="selected" ' : '',
			));
		}

		// Sorting
		foreach ($sortCriteria as $k => $v) {
			$template->assign_block_vars('sort_by', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => $k == $sheet_config['sort_by'] ? ' selected="selected" ' : '',
			));
		}

		foreach ($availSortOrder as $k => $v) {
			$template->assign_block_vars('sort_order', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => $k == $sheet_config['sort_order'] ? ' selected="selected" ' : '',
			));
		}

		// Searching
		foreach ($availCriteria as $k => $v) {
			$template->assign_block_vars('search_by', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => $k == $sheet_config['search_by'] ? ' selected="selected" ' : '',
			));
		}
		$template->assign_vars(array(
			'SEARCH_STRING' => $sheet_config['search_string'],
		));

		// Displayable information
		foreach ($displayCriteria as $k => $v) {
			$template->assign_block_vars('display_info', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => in_array($k, $sheet_config['display_info']) ? ' selected="selected" ' : '',
			));
		}

		// Options
		$template->assign_vars(array(
			'SHEET_TITLE' => $sheet_config['sheet_title'],
		));
		foreach ($template_names as $k => $v) {
			$v = preg_replace($templatePattern, '\1', $v);
			$template->assign_block_vars('template_name', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == $sheet_config['template_name'] ? ' selected="selected" ' : '',
			));
		}

		foreach ($updatePeriods as $k => $v) {
			$template->assign_block_vars('update_period', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == $sheet_config['update_period'] ? ' selected="selected" ' : '',
			));
		}

		foreach ($outofdatePeriods as $k => $v) {
			$template->assign_block_vars('outofdate_period', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == @$sheet_config['outofdate_period'] ? ' selected="selected" ' : '',
			));
		}

		/*
		*
		* END OF LEFT PANEL
		*
		*/

		break;

	case 'right':

		/*
		*
		* BEGIN OF RIGHT PANEL
		*
		*/

		$types =& new SNMP_Types;

		// Create template list
		$template_names = File_Find2::maptree($template->root, array(
			'mode'		=> FILE_FIND_MODE_FILES,
			'fullname'	=> false,
			'pattern_file'	=> $templatePattern,
		));

		/*
		*
		* Validate request
		*
		*/

		$sheet_config = array();

		// Available value of system
		$sheet_config['if_system'] = false;
		foreach ($systems as $k => $v) {
			if ( $v['name'] == @$_GET['if_system'] ) {
				$sheet_config['if_system'] = $_GET['if_system'];
				break;
			}
		}
		if (false === $sheet_config['if_system']) {
			$template->set_filenames(array(
				'body' => 'systems',
			));
			$template->assign_vars(array(
				'U_INTERFACE' => $_SERVER['REQUEST_URI'],
			));
			foreach ($systems as $k => $v) {
				$template->assign_block_vars('if_system', array(
					'IF_SYSTEM' => $v['name'],
				));
			}

			include_once 'includes/footer.php';
			exit;
		}

		// Available SNMP types
		if ( empty($_GET['if_type']) || array_diff(@$_GET['if_type'], array_keys($types->if_type), array_keys($types->if_grouped)) ) {
			$sheet_config['if_type'] = array();
		} else {
			$sheet_config['if_type'] = @$_GET['if_type'];
		}

		// Sorting
		$sheet_config['sort_by'] = isset($sortCriteria[@$_GET['sort_by']]) ? $_GET['sort_by'] : 'html_if_title_desc';
		$sheet_config['sort_order'] = isset($availSortOrder[@$_GET['sort_order']]) ? $_GET['sort_order'] : 'ASC';

		// Searching
		$sheet_config['search_by'] = isset($availCriteria[@$_GET['search_by']]) ? $_GET['search_by'] : 'html_if_title_desc';
		$sheet_config['search_string'] = @$_GET['search_string'] ? $_GET['search_string'] : '';

		// Displayable information
		if ( empty($_GET['display_info']) || ! is_array($_GET['display_info']) ) {
			$_GET['display_info'] = array();
		}
		$sheet_config['display_info'] = array_intersect(array_keys($displayCriteria), array_merge($_GET['display_info']));
		if ( empty($sheet_config['display_info']) ) {
			$sheet_config['display_info'] = array_keys($availCriteria);
		}

		// Options
		$sheet_config['sheet_title'] = @$_GET['sheet_title'];
		$sheet_config['template_name'] = '';
		foreach ($template_names as $k => $v) {
			if ( 0 == strpos(preg_replace($templatePattern, '\1', $v), @$_GET['template_name']) ) {
				$sheet_config['template_name'] = @$_GET['template_name'];
				break;
			}
		}
		$sheet_config['update_period'] = in_array((int)@$_GET['update_period'], $updatePeriods) ? $_GET['update_period'] : $updatePeriods[1];
		$sheet_config['outofdate_period'] = in_array((int)@$_GET['outofdate_period'], $outofdatePeriods) ? @$_GET['outofdate_period'] : $outofdatePeriods[0];


		/*
		*
		* Save data and redirect
		*
		*/

		if ( @$_GET['save'] ) {

			// Saving
			if ( $sheet < 0 || $sheet >= count($config['sheet']) ) {
				$sheet = count($config['sheet']);
			}

			$config['sheet'][$sheet] = $sheet_config;
			$config['sheet'][$sheet]['if_type'] = implode(' ', $config['sheet'][$sheet]['if_type']);
			$config['sheet'][$sheet]['display_info'] = implode(' ', $config['sheet'][$sheet]['display_info']);

                } elseif ( @$_GET['delete'] ) {

			// Deleting:
			// 1. Delete all links from combo list
			$result = array();
			foreach ($config['combo'] as $k => $v) {
				if ( ! in_array($sheet, $v) ) {
					$result[] = $v;
				}
			}
                	$config['combo'] = $result;

                	// 2. Delete this sheet
                	unset($config['sheet'][$sheet]);
                	$result = array();
                	foreach ($config['sheet'] as $k => $v) {
                		$result[] = $v;
                	}
			$config['sheet'] = $result;

		}
		if ( @$_GET['save'] || @$_GET['delete'] ) {

                	function sorter($a, $b) { return strnatcasecmp(sheetTitle($a, false), sheetTitle($b, false)); }
                	usort($config['sheet'], 'sorter');
                	IniFile::write(CONFIG_FILENAME, $config);

                }


                /*
                *
                * Prepare sheet info
                *
                */

		// Prepare MRTG options
		$ifs_options = array();
		$ifs_options['outofdate_period'] = $sheet_config['outofdate_period'];
		$ifs_options['if_type'] = $sheet_config['if_type'];
		if ($sheet_config['sort_by'] != 'unsorted') {
			$ifs_options['sort_by'] = $sheet_config['sort_by'];
			$ifs_options['sort_order'] = $sheet_config['sort_order'];
		}
		if ($sheet_config['search_string']) {
			$ifs_options['search_by'] = $sheet_config['search_by'];
			$ifs_options['search_string'] = $sheet_config['search_string'];
		}
		$ifs_options['search_chr'] = @$_GET['chr'];

		// MRTG running
		$mrtg->setOptions($ifs_options);
		$ifs = $mrtg->readInterfaces($sheet_config['if_system']);


		/*
		*
		* Parse and prepare sheet
		*
		*/

		printInterfaces($ifs, $sheet_config);

		if ($sheet >= 0) {
			$template->assign_block_vars('action', array(
				'U_ACTION' => $_SERVER['SCRIPT_NAME'] . '?&sheet=' . $sheet,
				'ACTION' => 'View this sheet',
			));
		}


		/*
		*
		* END OF RIGHT PANEL
		*
		*/

		break;

	default:

		/*
		*
		* BEGIN OF FRAME INDEX
		*
		*/

		$template->set_filenames(array(
			'body' => 'frameset',
		));

		$template->assign_vars(array(
			'PHP_MRTG_ADMIN_VERSION' => PHP_MRTG_ADMIN_VERSION,
			'U_LEFTPANEL'  => $_SERVER['SCRIPT_NAME'] . '?action=construct&panel=left&sheet=' . $sheet,
			'U_RIGHTPANEL' => $_SERVER['SCRIPT_NAME'] . '?action=construct&panel=right&sheet=' . $sheet,
		));

		/*
		*
		* END OF FRAME INDEX
		*
		*/

		break;

	}

	/*
	*
	* END OF CONSTRUCTOR
	*
	*/

	break;

case 'detail':

	/*
	*
	* BEGIN OF DETAILS
	*
	*/

	$availWhat = array('log', 'calc');

	if (in_array(@$_GET['what'], $availWhat)) {
		$mrtg =& new MRTG($setup['defaults']['mrtgpath']);
		$systems = $mrtg->readAllSystems();

		// Available value of system
		$if_system = false;
		foreach ($systems as $k => $v) {
			if ( $v['name'] == @$_GET['if_system'] ) {
				$if_system = $v['name'];
				break;
			}
		}

		if ($if_system) {
			$mrtg->setOptions(array(
				'search_by' => 'html_if_title_desc',
				'search_string' => @$_GET['html_if_title_desc'],
			));
			$ifs = $mrtg->readInterfaces($if_system);
		}
	}

	message_die(empty($ifs) || ! count($ifs), 'Bad request');

	$if = $ifs[0];

	// read log file
	$log = @file($if['files']['log']);
	message_die(empty($log), 'Error log-file');

	if ($_GET['what'] == 'log') {

		/*
		*
		* BEGIN OF LOG DETAIL
		*
		*/

		// parse last update time and incoming/outgoing bytes
		list($date, $incoming, $outgoing) = explode(' ', $log[0]);

		// output header
		$template->set_filenames(array(
			'body' => 'detail_log',
		));
		$template->assign_vars(array(
			'TITLE' => $if['system']['html_if_snmp_descr'] . ' ' . $if['system']['html_if_snmp_alias'],
			'DATE' => date('Y-m-d H:i', $date),
			'INCOMING' => $incoming,
			'OUTGOING' => $outgoing,
		));

		// output log
		for ($i = 1; $i < count($log); $i++) {
			list($date, $cuin, $cuout, $maxin, $maxout) = explode(' ', $log[$i]);
			if ( ! ($cuin + $cuout + $maxin + $maxout) ) {
				continue;
			}
			$template->assign_block_vars('log_row', array(
				'ZEBRA' => $i % 2 ? 'zebra_dark' : 'zebra_light',

				'DATE' => date('Y-m-d H:i', $date),
				'IN' => $cuin,
				'OUT' => $cuout,
				'MAXIN' => $maxin,
				'MAXOUT' => $maxout,
			));
		}

		/*
		*
		* END OF LOG DETAIL
		*
		*/

	} else {

		/*
		*
		* BEGIN OF CALC DETAIL
		*
		*/

		// start/stop
		preg_match('/^(\d+)/', $log[count($log) - 1], $matches);
		$time_start = (int)@$matches[1];
		preg_match('/^(\d+)/', $log[1], $matches);
		$time_stop = (int)@$matches[1];

		// output
		$template->set_filenames(array(
			'body' => 'detail_calc',
		));
		$template->assign_vars(array(
			'TITLE' => $if['system']['html_if_snmp_descr'] . ' ' . $if['system']['html_if_snmp_alias'],
			'U_DETAIL_CALC' => $_SERVER['SCRIPT_NAME'],
			'U_HELP_CALC' => 'doc/index.html#CALC',

			'IF_SYSTEM' => $_GET['if_system'],
			'HTML_IF_TITLE_DESC' => $_GET['html_if_title_desc'],
		));

		// calculate
		if (isset($_GET['time_start']) && isset($_GET['time_stop'])) {

			function xmktime($time)
			{
				$result = mktime(@$time['hour'], @$time['minute'], @$time['second'], @$time['month'], @$time['day'], @$time['year']);
				return $result;
			}

			// get time from request
			$request_time_start = xmktime($_GET['time_start']);
			if ( $request_time_start >= $time_start && $request_time_start <= $time_stop ) {
				$time_start = $request_time_start;
			}
			$request_time_stop = xmktime($_GET['time_stop']);
			if ( $request_time_stop >= $time_start && $request_time_stop <= $time_stop ) {
				$time_stop = $request_time_stop;
			}

			// extract data from the start and the stop time
			$data = array();
			for ($i = 1; $i < count($log); $i++) {
				$x = explode(' ', $log[$i]);
				$x['time'] = date('Y-m-d H:i:s', $x[0]);
				if ( (int)$x[0] >= $time_start && (int)$x[0] <= $time_stop && ($x[1] || $x[2]) ) {
					$data[] = $x;
				}
				if ($x[0] >= $time_start) {
					$i_start = $x[0];
				}
				if ($x[0] >= $time_stop) {
					$i_stop  = $x[0];
				}
			}

			if ( ! isset($i_stop) ) {
				$i_stop = @$data[0][0];
			}
			if ( ! isset($i_start) ) {
				$i_start = @$data[count($data) - 1][0];
			}
			if ($i_start || $i_stop) {
				// calculate a sum of incoming and outgoing bytes
				$sum_i = $sum_o = 0;
				for ($i = 0; $i < count($data) - 1; $i++) {
					$delta = $data[$i][0] - $data[$i + 1][0];
					$sum_i += $data[$i][1] * $delta;
					$sum_o += $data[$i][2] * $delta;
				}
				$template->assign_block_vars('result', array(
					'TIME_START' => date('Y-m-d H:i', $time_start),
					'TIME_STOP' => date('Y-m-d H:i', $time_stop),
					'BYTES_INCOMING' => $sum_i,
					'MULTI_INCOMING' => Utils_Bytes::multibytes($sum_i),
					'BYTES_OUTGOING' => $sum_o,
					'MULTI_OUTGOING' => Utils_Bytes::multibytes($sum_o),
				));
			} else {
				$template->assign_block_vars('no_result', array(
					'TIME_START' => date('Y-m-d H:i', $time_start),
					'TIME_STOP' => date('Y-m-d H:i', $time_stop),
				));
			}
		}

		// output continue
		foreach (range(date('Y', $time_start), date('Y', $time_stop)) as $k => $v) {
			$template->assign_block_vars('time_start_year', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('Y', $time_start) ? ' selected="selected" ' : '',
			));
			$template->assign_block_vars('time_stop_year', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('Y', $time_stop) ? ' selected="selected" ' : '',
			));
		}

		$months = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		foreach ($months as $k => $v) {
			$template->assign_block_vars('time_start_month', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => $k == date('m', $time_start) ? ' selected="selected" ' : '',
			));
			$template->assign_block_vars('time_stop_month', array(
				'VALUE' => $k,
				'TEXT' => $v,
				'SELECTED' => $k == date('m', $time_stop) ? ' selected="selected" ' : '',
			));
		}

		foreach (range(1, 31) as $k => $v) {
			$template->assign_block_vars('time_start_day', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('d', $time_start) ? ' selected="selected" ' : '',
			));
			$template->assign_block_vars('time_stop_day', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('d', $time_stop) ? ' selected="selected" ' : '',
			));
		}

		foreach (range(0, 23) as $k => $v) {
			$template->assign_block_vars('time_start_hour', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('H', $time_start) ? ' selected="selected" ' : '',
			));
			$template->assign_block_vars('time_stop_hour', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('H', $time_stop) ? ' selected="selected" ' : '',
			));
		}

		foreach (range(0, 59) as $k => $v) {
			$template->assign_block_vars('time_start_minute', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('i', $time_start) ? ' selected="selected" ' : '',
			));
			$template->assign_block_vars('time_stop_minute', array(
				'VALUE' => $v,
				'TEXT' => $v,
				'SELECTED' => $v == date('i', $time_stop) ? ' selected="selected" ' : '',
			));
		}

		/*
		*
		* END OF CALC DETAIL
		*
		*/

	}

	/*
	*
	* END OF DETAILS
	*
	*/

	break;

case 'help':

	/*
	*
	* BEGIN OF HELP
	*
	*/

        message_die(true, 'The help page is in the prepare stage yet');

	/*
	*
	* END OF HELP
	*
	*/

	break;

default:

	/*
	*
	* BEGIN OF MAIN SHEET
	*
	*/

	// read config from config-file
	// translate sheet-number from the GET request
	// customize actual sheet_config
	prepareSheetData();

	// Create MRTG handle and iterface types
	$mrtg =& MRTG::getInstance($setup['defaults']['mrtgpath']);
	$systems = $mrtg->readAllSystems();

	// MRTG running
	$mrtg->setOptions(array(
		'outofdate_period' => @$sheet_config['outofdate_period'],
		'if_type' => $sheet_config['if_type'],
		'sort_by' => $sheet_config['sort_by'] == 'unsorted' ? '' : $sheet_config['sort_by'],
		'sort_order' => $sheet_config['sort_order'],
		'search_by' => $sheet_config['search_by'],
		'search_string' => $sheet_config['search_string'],
		'search_chr' => @$_GET['chr'],
	));
	$ifs = $mrtg->readInterfaces($sheet_config['if_system']);

	printInterfaces($ifs, $sheet_config);
	$template->assign_vars(array(
		'U_NAVIGATE' => $_SERVER['SCRIPT_NAME'],
		'U_HELP_SHEET' => 'doc/index.html#SHEET',
	));
	printNavigateSheets($sheet, $config['sheet']);

	// Show link for available to edit only
	if (isAvailableEdit()) {
		$template->assign_block_vars('action', array(
			'U_ACTION' => $_SERVER['SCRIPT_NAME'] . '?action=construct&sheet=' . $sheet,
			'ACTION' => 'Edit this sheet',
		));
	}

	/*
	*
	* END OF MAIN SHEET
	*
	*/

	break;

}


include_once 'includes/footer.php';


?>