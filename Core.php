<?php

/*
* phpMyAdmin:
*       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
* Version:
*       1.03
*
* Copyright (C) 2005-2006 Ildar N. Shaimordanov
*
* Licensed under the terms of the GNU General Public License:
* http://opensource.org/licenses/gpl-license.php
*
* File Name: Core.php
*       This is the part of phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*/

// {{{

/*
*
* Include and turn on the timer
*
*/
include_once 'Core/Timer.php';

$timer =& new Timer();
$timer->start();

// }}}
// {{{

/*
*
* OS depended path separator
*
*/
if (!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR', (preg_match('/WIN/i', PHP_OS)) ? ';' : ':');
}

if (!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR', (preg_match('/WIN/i', PHP_OS)) ? '\\' : '/');
}

// }}}
// {{{

/*
*
* Include paths definitions 
*
*/
ini_set('include_path', ini_get('include_path')
    . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Core'
    . PATH_SEPARATOR . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PEAR'
);

// }}}
// {{{

/*
*
* Common core (kernel) includes
*
*/
include_once 'Core/Debug.php';
Debug::useNice();

include_once 'Core/Utils.php';

// }}}
// {{{

/*
*
* Load setup
*
*/
include_once 'Core/IniFile.php';

$GLOBALS['setup'] = IniFile::read(dirname(__FILE__) . '/Core/Setup/Setup.ini');
#$GLOBALS['setup'] = IniFile::read($_SERVER['DOCUMENT_ROOT'] . '/Core/Setup/Setup.ini');

// }}}
// {{{

/*
*
* Template initialization
*
*/
include_once 'Core/Template.php';

$GLOBALS['template'] =& new Template(dirname(__FILE__)
    . DIRECTORY_SEPARATOR . 'Core'
    . DIRECTORY_SEPARATOR . 'Template'
    . DIRECTORY_SEPARATOR . $GLOBALS['setup']['defaults']['template']
);

// }}}

?>