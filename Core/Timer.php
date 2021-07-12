<?php

/**
*
* Simple timer based on Structure class
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
* @package    Timer
* @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>
* @license    http://www.php.net/license/3_0.txt  PHP License 3.0
* @version    $Id: Timer.php,v 1.1 2007/06/08 12:43:17 phpmrtgadmin Exp $
*
*/

include_once 'Structure.php';

class Timer extends Stack
{

    function start()
    {
        list($msec, $sec) = explode(' ', microtime());
        return $this->push($sec + $msec);
    }

    function stop()
    {
        $start = $this->pop();
        if ($start === null) {
            return null;
        }
        list($msec, $sec) = explode(' ', microtime());
        return $sec + $msec - $start;
    }

}

?>