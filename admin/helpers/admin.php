<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/07
  Last Modified:  2013-03-28
  Developer:      David Hunt
  Copyright:      2007-2010 Webuddha.com, The Holodyn Corporation
  License:        GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
  Source:         http://software.webuddha.com/

  - Description --------------------------------------------------------------------------

  ----------------------------------------------------------------------------------------
*/

// Block Direct Access
defined( '_JEXEC' ) or die('Access Denied');

// ************************************************************************************************
//
// Common Functions
//
// ************************************************************************************************


class wbAdvert_AdminHelper {

  // ------------------------------------------------------------------------ adminHeader
  function htmlHeader(){
    global $task, $option;
    $document =& JFactory::getDocument();
    $document->addStyleSheet(WBADVERT_SITE_LOCAL . 'administrator/components/com_wbadvert/inc/admin.css','text/css',"screen");
    echo '<div id="com_wbadvert">';
  }

  // ------------------------------------------------------------------------ adminHeader
  function htmlFooter(){
    echo '</div>';
  }

}