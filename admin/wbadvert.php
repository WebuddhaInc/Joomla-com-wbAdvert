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

  This is the administration kernel. The logic for this file was developed for the
  j10x series and should not be used as a model for j15x development.

  ----------------------------------------------------------------------------------------
*/

// Block Direct Access
  defined( '_JEXEC' ) or die('Access Denied');

// ************************************************************************************************
//
// Admin Kernel
//
// ************************************************************************************************

// Load Gallery Objects
  define('WBADVERT_ADMIN',1);

// Include Classes
  $db =& JFactory::getDBO();
  require_once( JPATH_ROOT.'/administrator/components/com_wbadvert/load.php' );

// Pull IDs
  $option = JRequest::getCmd( 'option', WBADVERT_NAME );
  $task   = JRequest::getCmd( 'task', 'home' );
  $cid    = JRequest::getVar( 'cid', array($id), 'method', 'array' );
  $id     = JRequest::getInt( 'id', (count($cid)?$cid[0]:0) );

// Check Settings
  if( !$wbAdvert_config->ready() && !preg_match('/^config|^home|^support/',$task) )
    JFactory::getApplication()->redirect(
      'index2.php?option='.WBADVERT_NAME.'&task=config',
      'Please Save Configuration before Using wbAdvert',
      'error'
      );

// Check Modules
  $db->setQuery("
    SELECT COUNT(*)
    FROM #__modules
    WHERE module = 'mod_wbadvert'
    ");
  $total = $db->loadResult(); echo $db->getErrorMsg();
  if( !$total && !preg_match('/^config|^home|^support/',$task) )
    JFactory::getApplication()->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=config',
      'No wbAdvert Modules have been installed',
      'error'
      );

// Administrator Heading

  /**
   * TODO: Move to view
   */
  wbAdvert_AdminHelper::htmlHeader();

// Menu Switch

  /**
   * TODO: Move this into the view handler
   */
  wbAdvert_ToolbarHelper::submenu( $task );
  switch ($task) {
    case 'config':
    case 'config.edit':
      wbAdvert_ToolbarHelper::config();
      break;
    case 'campaign.edit':
      wbAdvert_ToolbarHelper::campaign_edit();
      break;
    case 'campaign':
    case 'campaign.list':
      wbAdvert_ToolbarHelper::campaign_list();
      break;
    case 'client.edit':
      wbAdvert_ToolbarHelper::client_edit();
      break;
    case 'client':
    case 'client.list':
      wbAdvert_ToolbarHelper::client_list();
      break;
    case 'group.edit':
    case 'group.delete':
      wbAdvert_ToolbarHelper::group_edit();
      break;
    case 'group':
    case 'group.list':
    case 'group.cancel':
      wbAdvert_ToolbarHelper::group_list();
      break;
    case 'advert.edit':
      wbAdvert_ToolbarHelper::advert_edit();
      break;
    case 'advert':
    case 'advert.list':
      wbAdvert_ToolbarHelper::advert_list();
      break;
    case 'keyword.edit':
      wbAdvert_ToolbarHelper::keyword_edit();
      break;
    case 'keyword':
    case 'keyword.list':
      wbAdvert_ToolbarHelper::keyword_list();
      break;
    default:
    case 'config':
    case 'support':
      wbAdvert_ToolbarHelper::home();
      break;
  }

// Main Task Switch
  /**
   * TODO: This routing should be automatic
   */
  switch ($task) {

    // ------------------------------------ Advertisements
    case 'advert.edit':
      advert_edit( $id, $option, $task );
      break;

    case 'advert.save':
    case 'advert.apply':
    case 'advert.reset':
      advert_save( $id, $option, $task );
      break;

    case 'advert.cancel':
      advert_cancel( $id, $option, $task );
      break;

    case 'advert.delete':
      advert_delete( $cid, $option, $task  );
      break;

    case 'advert.order':
      advert_order( $cid, 0, $option, $task );
      break;

    case 'advert.orderup':
      advert_order( intval( $cid[0] ), -1, $option, $task );
      break;

    case 'advert.orderdn':
      advert_order( intval( $cid[0] ), 1, $option, $task );
      break;

    case 'advert.publish':
      advert_publish( $cid, 1, $option, $task );
      break;

    case 'advert.unpublish':
      advert_publish( $cid, 0, $option, $task );
      break;

    case 'advert':
    case 'advert.list':
      advert_list( $option, $task );
      break;

    // ------------------------------------ Campaigns
    case 'campaign.edit':
      campaign_edit( $id, $option, $task );
      break;

    case 'campaign.save':
    case 'campaign.apply':
      campaign_save( $id, $option, $task );
      break;

    case 'campaign.cancel':
      campaign_cancel( $id, $option, $task );
      break;

    case 'campaign.delete':
      campaign_delete( $cid, $option, $task );
      break;

    case 'campaign.publish':
      campaign_publish( $cid, 1, $option, $task );
      break;

    case 'campaign.unpublish':
      campaign_publish( $cid, 0, $option, $task );
      break;

    case 'campaign':
    case 'campaign.list':
      campaign_list( $option, $task );
      break;

    // ------------------------------------ Clients
    case 'client.edit':
      client_edit( $id, $option, $task );
      break;

    case 'client.save':
    case 'client.apply':
      client_save( $id, $option, $task );
      break;

    case 'client.cancel':
      client_cancel( $id, $option, $task );
      break;

    case 'client.delete':
      client_delete( $cid, $option, $task );
      break;

    case 'client.publish':
      client_publish( $cid, 1, $option, $task );
      break;

    case 'client.unpublish':
      client_publish( $cid, 0, $option, $task );
      break;

    case 'client':
    case 'client.list':
      client_list( $option, $task );
      break;

    // ------------------------------------ Groups
    case 'group.edit':
      group_edit( $id, $option, $task );
      break;

    case 'group.save':
    case 'group.apply':
      group_save( $id, $option, $task );
      break;

    case 'group.cancel':
      group_cancel( $id, $option, $task );
      break;

    case 'group.delete':
      group_delete( $cid, $option, $task );
      break;

    case 'group.order':
      group_order( $cid, 0, $option, $task );
      break;

    case 'group.orderup':
      group_order( intval( $cid[0] ), -1, $option, $task );
      break;

    case 'group.orderdn':
      group_order( intval( $cid[0] ), 1, $option, $task );
      break;

    case 'group.publish':
      group_publish( $cid, 1, $option, $task );
      break;

    case 'group.unpublish':
      group_publish( $cid, 0, $option, $task );
      break;

    case 'group':
    case 'group.list':
      group_list( $option, $task );
      break;

    // ------------------------------------ Keywords
    case 'keyword.edit':
      keyword_edit( $id, $option, $task );
      break;

    case 'keyword.save':
    case 'keyword.apply':
      keyword_save( $id, $option, $task );
      break;

    case 'keyword.cancel':
      keyword_cancel( $id, $option, $task );
      break;

    case 'keyword.delete':
      keyword_delete( $cid, $option, $task );
      break;

    case 'keyword':
    case 'keyword.list':
      keyword_list( $option, $task );
      break;

    // ------------------------------------ Config
    case 'config':
    case 'config.edit':
      config_edit( $option );
      break;

    case 'config.save':
      config_save( $option );
      break;

    // ------------------------------------ Default / Home
    default:
    case 'home':
    case 'support':
      home_display( $option, $task );
      break;

  }

// Administrator Heading

  /**
   * TODO: Move to view
   */
  wbAdvert_AdminHelper::htmlFooter();
