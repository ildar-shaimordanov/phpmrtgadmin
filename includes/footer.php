<?

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
* File Name: footer.php
*       This is the part of phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*/

function correctExternalLinks($html)
{
    global $config;

    $subst = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) . '/Core/Template/' . $GLOBALS['setup']['defaults']['template'];
    $subst = str_replace('//', '/', $subst);

    $html = preg_replace(
        array(
            '/(<link\s+.*?\s*href="(?!\/|\w+:\/\/))([^"]+)("[^>]*>)/',
            '/(<img\s+.*?\s*src="(?!\/|\w+:\/\/))([^"]+)("[^>]*>)/',
            '/(<script\s+.*?\s*src="(?!\/|\w+:\/\/))([^"]+)("[^>]*><\/script>)/',
#            '/(<link\s+.*?\s*href=")([^"]+)("[^>]*>)/',
#            '/(<img\s+.*?\s*src="[^\/])([^"]+)("[^>]*>)/',
#            '/(<script\s+.*?\s*src=")([^"]+)("[^>]*><\/script>)/',
        ),
        '\1' . $subst . '/\2' . '\3',
        $html
    );

    return $html;
}


/*
* 
* Capture of output full page
* 
*/
ob_start();

$template->assign_vars(array(
    'H_CONTENT_DIRECTION' => @$lang['Common']['Metas']['Direction'],
    'H_CONTENT_ENCODING'  => @$lang['Common']['Metas']['Charset'],

    'GENERATION_TIME' => round($timer->stop(), 3),
));

foreach (array('head', 'body', 'tail') as $handle) {
    if ($template->handle_assigned($handle)) {
        $template->parse($handle);
    }
}

$html_compress = $html_uncompress = correctExternalLinks(ob_get_contents());

ob_end_clean();


/*
* 
* Bypass empty output
* 
*/
if (!strlen($html_uncompress)) {
    exit;
}


/*
* 
* Use GZip if it is enabled and available
* 
*/
if ( strstr(@$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') ) {
#    $html_compress = ob_gzhandler($html_uncompress, 9);
}


/*
*
* Output finally
*
*/
echo $html_compress;


?>