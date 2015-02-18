<?php

/*
  - wbAdvert for Joomla! -----------------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/2007
  Last Modified:  03/28/2013
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
// Public Kernel
//
// ************************************************************************************************

$_inc = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_wbadvert'.DS.'load.php';
if( file_exists($_inc) ){

  // Include Classes
  require_once($_inc);

  // Load DB Class
  $db =& JFactory::getDBO();
  $wbAdvert = new wbAdvert_advert( $db );

  $_id = JRequest::getInt( 'id', 0 );
  $_task = JRequest::getCmd( 'task', 'click' );
  $_func = array_shift( explode('\.', $_task) );

  switch( $_func ){

    case 'system':
      $control = new wbAdvert_System();
      $control->process( $_task );
      break;

    case 'code':
      // Override SWF Loader Option
      $wbAdvert_config->set('swf_jsloader',0);
      // Load & Return Code
      $code = $wbAdvert->getAdvertCode((int)$_id);
      if(!strlen($code))
        die('<h1 alert="alert">'.JText::_('ERR_NOTFOUND').'</h1>');
      if( JRequest::getInt('track',0) )
        $wbAdvert->impression((int)$_id);
      echo $code;
      exit();
      break;

    default:
      // Load Record
      if($_id)
        $wbAdvert->load( $_id );
      if(!(int)$wbAdvert->id)
        die('<h1 alert="alert">'.JText::_('ERR_NOTFOUND').'</h1>');
      // Confirm Available
      if( !$wbAdvert->url )
        die ('
          <h1 alert="alert">'.JText::_('ERR_URLNOTFOUND').'</h1>
          <a href="'.WBADVERT_SITE.'">'.JText::_('ERR_RETURNLINK').'</a>
          ');
      if( !$wbAdvert->published )
        die ('<h1 alert="alert">'.JText::_('ERR_NOTPUBLISHED').'</h1>');
      // Track Clicks
      if( $wbAdvert_config->get('track_clicks') )
        $wbAdvert->click();
      // Load URL
      if( $wbAdvert_config->get('load_frame') )
        include($wbAdvert_config->getFramePath());
      else
        JFactory::getApplication()->redirect( $wbAdvert->url );
     break;
  }

} else {

  $msg = '<h1 alert="alert">'.JText::_('ERR_LOADFAILED').'</h1>';
  die( $msg );

}

exit();
