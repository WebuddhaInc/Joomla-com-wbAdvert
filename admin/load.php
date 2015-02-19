<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/07
  Last Modified:  2013-03-28
  Developer:      David Hunt
  Copyright:      2007-2013 Webuddha.com, The Holodyn Corporation
  License:        GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
  Source:         http://software.webuddha.com/

  - Description --------------------------------------------------------------------------

  ----------------------------------------------------------------------------------------
*/

// Block Direct Access
defined( '_JEXEC' ) or die('Access Denied');

// ************************************************************************************************
//
// Define System Paths
//
// ************************************************************************************************

  defined('DS') or define('DS', DIRECTORY_SEPARATOR);
  define('WBADVERT_TITLE', 'wbAdvert');
  define('WBADVERT_NAME', 'com_wbadvert');
  define('WBADVERT_SITE', JURI::root());
  define('WBADVERT_BASE', JPATH_ROOT);
  define('WBADVERT_PATH', JPATH_ROOT.DS.'administrator'.DS.'components'.DS.WBADVERT_NAME.DS);
  define('WBADVERT_PATH_PUBLIC', JPATH_ROOT.DS.'components'.DS.WBADVERT_NAME.DS);

// ************************************************************************************************
//
// Load System
//
// ************************************************************************************************

// Administration Access
  if( defined('WBADVERT_ADMIN') ){
    $user =& JFactory::getUser();
    if( !JFactory::getUser()->authorise('core.manage', WBADVERT_NAME) ){
      $app = JFactory::getApplication();
      $app->redirect( 'index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error' );
    }
  }

// Load Compatability
  require_once( WBADVERT_PATH.'helpers/compat.php' );

// Load Configuration
  global $wbAdvert_config;
  require_once(WBADVERT_PATH.'classes/wbAdvert_config.class.php');
  $wbAdvert_config = wbAdvert_config::getInstance();

// Load Classes
  require_once( WBADVERT_PATH.'classes/wbAdvert_advert.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_client.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_filter.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_group.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_idx_category.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_idx_content.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_idx_group.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_idx_menu.class.php' );
  require_once( WBADVERT_PATH.'classes/wbAdvert_swfHeader.class.php' );

// Load Includes
  if( defined('WBADVERT_ADMIN') ){
    require_once( WBADVERT_PATH.'helpers/common.php' );
    require_once( WBADVERT_PATH.'helpers/toolbar.php' );
    require_once( WBADVERT_PATH.'controllers/advert.php' );
    require_once( WBADVERT_PATH.'controllers/campaign.php' );
    require_once( WBADVERT_PATH.'controllers/client.php' );
    require_once( WBADVERT_PATH.'controllers/group.php' );
    require_once( WBADVERT_PATH.'controllers/keyword.php' );
    require_once( WBADVERT_PATH.'controllers/config.php' );
    require_once( WBADVERT_PATH.'controllers/home.php' );
  }
  else {
  }

// Load SWF JS Loader
  JHTML::script(
    /* file */      'swfobject.js',
    /* path */      $wbAdvert_config->get('swf_jsloaderpath','media/com_wbadvert/swfobject/'),
    /* mootools */  false
    );

