<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Contains the Pager_Jumping class
 *
 * PHP versions 4 and 5
 *
 * LICENSE: Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    Pager
 * @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>,
 * @copyright  2006 Ildar N. Shaimordanov
 * @license    http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version    CVS: $Id: Alphanumeric.php,v 1.1 2007/06/08 12:46:03 phpmrtgadmin Exp $
 * @link       http://pear.php.net/package/Pager
 */

/**
 * require PEAR::Pager_Common base class
 */
require_once 'Pager/Common.php';

/**
 * Pager_Jumping - Generic data paging class  ("jumping window" style)
 * Handles paging a set of data. For usage see the example.php provided.
 *
 * @category   HTML
 * @package    Pager
 * @author     Ildar N. Shaimordanov <ildar-sh@mail.ru>,
 * @copyright  2006 Ildar N. Shaimordanov
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link       http://pear.php.net/package/Pager
 */
class Pager_Alphanumeric extends Pager_Common
{

    // {{{

    var $_currentID = '';

    // }}}
    // {{{ Pager_Alphanumeric()

    function Pager_Alphanumeric($options = array())
    {
        $this->_itemData = range('A', 'Z');
        $this->_urlVar = 'chr';
        $this->_spacesBeforeSeparator = 0;
        $this->_spacesAfterSeparator = 1;
        $this->_curPageSpanPre = '<b>';
        $this->_curPageSpanPost = '</b>';

        $err = $this->setOptions($options);
        if ($err !== PAGER_OK) {
            return $this->raiseError($this->errorMessage($err), $err);
        }

        $this->build();
    }

    // }}}
    // {{{ build()

    function build()
    {
        $this->_pageData = array();
        $this->links = '';

        $this->_generatePageData();
        $this->_setFirstLastText();

        $this->links .= $this->_getPageLinks();
    }

    // }}}
    // {{{ getCurrentPageID()

    function getCurrentPageID()
    {
        return $this->_itemData[$this->_currentPage - 1];
    }

    // }}}
    // {{{ getLinks()

    function getLinks($pageID=null, $next_html='')
    {
        if (!empty($next_html)) {
            $back_html = $pageID;
            $pageID    = null;
        } else {
            $back_html = '';
        }

        if (!is_null($pageID)) {
            $_sav = $this->_currentPage;
            $this->_currentPage = $pageID;

            $this->links = $this->_getPageLinks();
        }

        $back = '';
        $next = '';
        $pages = '';
        $first = '';
        $last = '';
        $all = $this->links;
        $linkTags = $this->linkTags;

        if (!is_null($pageID)) {
            $this->_currentPage = $_sav;
        }

        return array(
            $back,
            $pages,
            $next,
            $first,
            $last,
            $all,
            $linkTags,
            'back' => $back,
            'pages' => $pages,
            'next' => $next,
            'first' => $first,
            'last' => $last,
            'all' => $all,
            'linktags' => $linkTags
        );
    }

    // }}}
    // {{{ getNextPageID()

    function getNextPageID()
    {
        return $this->isLastPage() ? false : $this->_itemData[$this->_currentPage];
    }

    // }}}
    // {{{ getOffsetByPageId()

    function getOffsetByPageId($pageid = null)
    {
        return false;
    }

    // }}}
    // {{{ getPageData()

    function getPageData($pageID = null)
    {
        return $this->_itemData;
    }

    // }}}
    // {{{ getPageIdByOffset()

    function getPageIdByOffset($index)
    {
        return false;
    }

    // }}}
    // {{{ getPageRangeByPageId()

    function getPageRangeByPageId($pageid = null)
    {
        return false;
    }

    // }}}
    // {{{ getPreviousPageID()

    function getPreviousPageID()
    {
        return $this->isFirstPage() ? false : $this->_itemData[$this->_currentPage - 2];
    }

    // }}}
    // {{{ setOptions()

    function setOptions($options)
    {
        $result = parent::setOptions($options);

        $this->_perPage = 1;
        $this->_delta = count($this->_itemData);
        $this->_prevImg = '';
        $this->_nextImg = '';

        if ( isset($_REQUEST[$this->_urlVar]) && in_array($_REQUEST[$this->_urlVar], $this->_itemData) ) {
            $this->_currentID = $_REQUEST[$this->_urlVar];
            $this->_currentPage += array_search($_REQUEST[$this->_urlVar], $this->_itemData);
        } else {
            $this->_currentID = reset($this->_itemData);
        }

        return $result;
    }

    // }}}
    // {{{ _generatePageData()

    function _generatePageData()
    {
        if (!is_null($this->_itemData)) {
            $this->_totalItems = count($this->_itemData);
        }
        $this->_totalPages = ceil((float)$this->_totalItems / (float)$this->_perPage);
        $this->_pageData = array();

        $this->_currentPage = min($this->_currentPage, $this->_totalPages);
    }

    // }}}
    // {{{ _getPageLinks()

    function _getPageLinks($url = '')
    {
        $result = '';

        $countData = count($this->_itemData);
        for ($i = 0; $i < $countData; $i++) {
            $result .= $this->_spacesBefore . $this->_separator;
#            if ( $this->_currentPage == $i + 1 ) {
#                $this->range[$i] = true;
#                $result .= $this->_curPageSpanPre . $this->_itemData[$i] . $this->_curPageSpanPost;
#            } else {
                $this->range[$i] = false;
                $this->_linkData[$this->_urlVar] = $this->_itemData[$i];
                $result .= $this->_renderLink($this->_itemData[$i], $this->_itemData[$i]);
#            }
            $result .= $this->_separator . $this->_spacesAfter;
        }

        return $result;
    }

    // }}}
}
?>