<?

/*
* phpMyAdmin:
*       the flexible PHP-based web-tool for monitoring of MRTG-created graphs
* Version:
*       1.00
*
* Copyright (C) 2005-2006 Ildar N. Shaimordanov
*
* Licensed under the terms of the GNU Lesser General Public License:
* http://opensource.org/licenses/gpl-license.php
*
* File Name: frames.php
*       This is the subsidiary file for phpMrtgAdmin
*
* File Authors:
*       Ildar N. Shaimordanov (phpmrtgadmin@users.sourceforge.net)
*
* Description:
*       The main purpose of this file to grant synchronous access for different sheets
*/

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Frames :: phpMrtgAdmin</title>
</head>

<frameset rows="50%,50%" id="mainFrameset" border="1" frameborder="1" framespacing="0">
	<frame src="http://<?=$_SERVER['HTTP_HOST']?>" frameborder="1" marginwidth="0" marginheight="0" scrolling="auto" />
	<frame src="http://<?=$_SERVER['HTTP_HOST']?>" frameborder="1" marginwidth="0" marginheight="0" scrolling="auto" />
</frameset>

<noframes>
<body>
Frames are not supported by Your web-browser
</body>
</noframes>
</html>

