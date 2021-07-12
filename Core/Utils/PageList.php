<?php

/*
* PagesList
*
* Copyright (C) 2005 Dmitry Koteroff
*
* File Authors:
*       Dmitry Koteroff (http://forum.dklab.ru/users/DmitryKoteroff/)
*/

class PagesList
{

    function make($pageSize, $nElts, $curElt=null, $url=false, $arg='p')
    {
        $pages = array();
        $pageSize = intval($pageSize);
        if ($pageSize <= 0) {
            $pageSize = 10;
        }
        if ($url === false) {
            $url = $_SERVER['REQUEST_URI'];
        }
        if ($curElt === null) {
            $curElt = isset($_GET[$arg])? $_GET[$arg] : 0;
        }
        for ($n = 1, $i = 0; $i < $nElts; $i += $pageSize, $n++) {
            if (preg_match("/([?&]$arg=)(\d+|all)/s", $url)) {
                $purl = preg_replace("/([?&]$arg=)(\d+|all)/si", '${1}' . $i, $url);
            } else {
                $div = strpos($url, '?')? '&' : '?';
                $purl = $url . $div . $arg . '=' . $i;
            }
            $pages[] = array(
                'n'       => $n,
                'pos'     => $i,
                'url'     => $purl,
                'isfirst' => false,
                'iscur'   => @$_GET[$arg] != 'all' && $curElt >= $i && $curElt < $i + $pageSize,
                'islast'  => false,
            );
        }
        if (count($pages)) {
            $pages[0]['isfirst'] = 1;           
            $pages[count($pages)-1]['islast'] = 1;
            if ($curElt >= $nElts) {
                $pages[count($pages) - 1]['iscur'] = true;
            }
        }
        return $pages;
    }
       
    function frame($frameSize, $pageSize, $nElts, $curElt=null, $url=false, $arg='p')
    {
        $pages = PagesList::make($pageSize, $nElts, $curElt, $url, $arg);
        for ($i = 0; $i < count($pages); $i++) {
            if ($pages[$i]['iscur']) {
                break;
            }
        }
        $cur = $i;
        $start = 0;
        if ($i > $frameSize / 2) {
            $start = intval($i - $frameSize / 2);
        }
        if (count($pages) - $start < $frameSize) {
            $start = count($pages) - $frameSize;
        }
        $start = max($start, 0);
        $framePages = array_slice($pages, $start, $frameSize);
           
        $frame = array();
        if ($start != 0) {
            $prev = max($cur - $frameSize, 0);
            $frame['prev'] = $pages[$prev];
        }
        if ($start + $frameSize < count($pages)) {
            $next = min($cur + $frameSize, count($pages) - 1);
            $frame['next'] = $pages[$next];
        }
        $frame['pages'] = $framePages;
	if (count($frame['pages'])) {
	    $frame['all'] = array(
		'url' => preg_replace("/([?&]$arg=)(\d+|all)/s", '${1}all', $frame['pages'][0]['url']),
		'isfirst' => false,
		'iscur' => @$_GET[$arg] == 'all',
		'islast' => false,
	    );
	}
           
        return $frame;
    }

    function paginate($pages_per_frame, $items_per_page, $total)
    {
	$pages = PagesList::frame($pages_per_frame, $items_per_page, $total);
	$s = '';
	if (!empty($pages['all'])) {
	    if ($pages['all']['iscur']) {
		$s .= '<span class="current">all</span>';
	    } else {
		$s .= '&nbsp;<a href="' . $pages['all']['url'] . '">all</a>&nbsp;';
	    }
	}
	if (!empty($pages['prev'])) {
	    $s .= '<a href="' . $pages['prev']['url'] . '">...</a>&nbsp;';
	}
	foreach ($pages['pages'] as $p) {
	    if ($p['iscur']) {
		$s .= '<span class="current">' . $p['n'] . '</span>';
	    } else {
		$s .= '&nbsp;<a href="' . $p['url'] . '">' . $p['n'] . '</a>&nbsp;';
	    }
	}
	if (!empty($pages['next'])) {
	    $s .= '&nbsp;<a href="' . $pages['next']['url'] . '">...</a>';
	}
	$s = '<table class="paginate"><tr><td class="pagesWord">Pages: </td><td>' . $s . '</td></tr></table>';
	return $s;
    }

}

?>