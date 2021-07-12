/**
*
* This is the part of the PEAR Debug package
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
* @version    $Id: scripts.js,v 1.1 2007/06/08 12:42:23 phpmrtgadmin Exp $
*
*/

var document_onmousedown_saved_from_php_debug = null;

if (document.onmousedown) {
    document_onmousedown_saved_from_php_debug = document.onmousedown;
}

document.onmousedown = function(e)
{
    e = e || event;
    var target = e.srcElement || e.target;

    if (target.title == 'array' || target.title == 'object') {
        var collapsed = target;
        do {
            collapsed = collapsed.nextSibling;
        } while (collapsed && !(collapsed.tagName && collapsed.tagName.toUpperCase() == 'UL'));
        if (collapsed) {
            collapsed.style.display = collapsed.style.display ? '' : 'none';
        }
    }
    if (document_onmousedown_saved_from_php_debug) {
        document_onmousedown_saved_from_php_debug(e);
    }
}

