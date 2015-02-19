<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/07
  Last Modified:  04/29/10
  Developer:      David Hunt
  Copyright:      2007-2009 Webuddha.com, The Holodyn Corporation
  License:        GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
  Source:         http://software.webuddha.com/

  - Description --------------------------------------------------------------------------

  ----------------------------------------------------------------------------------------
*/

// Block Direct Access
defined( '_JEXEC' ) or die('Access Denied');

// ************************************************************************************************
//
// Toolbar
//
// ************************************************************************************************

class wbAdvert_ToolbarHelper {
  function submenu( $task ) {
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_HOME'),
      'index.php?option=com_wbadvert',
      in_array($task,array('home'))
    );
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_ADVERT'),
      'index.php?option=com_wbadvert&task=advert',
      in_array($task,array('advert', 'advert.edit'))
    );
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_GROUP'),
      'index.php?option=com_wbadvert&task=group',
      in_array($task,array('group', 'group.edit'))
    );
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_CLIENT'),
      'index.php?option=com_wbadvert&task=client',
      in_array($task,array('client', 'client.edit'))
    );
    /*
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_CAMPAIGN'),
      'index.php?option=com_wbadvert&task=campaign',
      in_array($task,array('campaign'))
    );
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_KEYWORD'),
      'index.php?option=com_wbadvert&task=keyword',
      in_array($task,array('keyword'))
    );
    */
    JSubMenuHelper::addEntry(
      JText::_('COM_WBADVERT_MENU_CONFIG'),
      'index.php?option=com_wbadvert&task=config',
      in_array($task,array('config'))
    );
  }
  function home() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_HOME'), 'systeminfo.png');
    // JToolBarHelper::custom('link.forum','html','',JText::_('BTN_FORUM'),false,false);
    JToolBarHelper::custom('link.ticket','help','',JText::_('BTN_TICKETS'),false,false);
  }
  function advert_list() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_ADVERTLIST'), 'mediamanager.png');
    JToolBarHelper::publishList( 'advert.publish' );
    JToolBarHelper::unpublishList( 'advert.unpublish' );
    JToolBarHelper::addNew( 'advert.edit' );
    JToolBarHelper::editList( 'advert.edit' );
    JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'),'advert.delete');
  }
  function advert_edit() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_ADVERTEDIT'), 'addedit.png');
    JToolBarHelper::apply( 'advert.apply' );
    JToolBarHelper::save( 'advert.save' );
    JToolBarHelper::cancel( 'advert.cancel', 'Cancel' );
  }
  function campaign_list() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_CAMPAIGNLIST'), 'user.png');
    JToolBarHelper::publishList('campaign.publish');
    JToolBarHelper::unpublishList('campaign.unpublish');
    JToolBarHelper::addNew('campaign.edit');
    JToolBarHelper::editList('campaign.edit');
    JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'),'campaign.delete');
  }
  function campaign_edit() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_CAMPAIGNEDIT'), 'addedit.png');
    JToolBarHelper::apply('campaign.apply');
    JToolBarHelper::save('campaign.save');
    JToolBarHelper::cancel('campaign.cancel');
  }
  function client_list() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_CLIENTLIST'), 'user.png');
    JToolBarHelper::publishList( 'client.publish' );
    JToolBarHelper::unpublishList( 'client.unpublish' );
    JToolBarHelper::addNew('client.edit');
    JToolBarHelper::editList('client.edit');
    JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'),'client.delete');
  }
  function client_edit() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_CLIENTEDIT'), 'addedit.png');
    JToolBarHelper::apply( 'client.apply' );
    JToolBarHelper::save('client.save');
    JToolBarHelper::cancel('client.cancel');
  }
  function group_list() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_GROUPLIST'), 'categories.png');
    JToolBarHelper::addNew('group.edit');
    JToolBarHelper::editList('group.edit');
    JToolBarHelper::publishList( 'group.publish' );
    JToolBarHelper::unpublishList( 'group.unpublish' );
    JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'),'group.delete');
    $canDo = wbAdvert_Common::userGetPermissions();
    if( $canDo->get('core.admin') )
      JToolbarHelper::preferences('com_weblinks');
  }
  function group_edit() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_GROUPEDIT'), 'addedit.png');
    JToolBarHelper::apply('group.apply');
    JToolBarHelper::save('group.save');
    JToolBarHelper::cancel( 'group.cancel', 'Cancel' );
  }
  function keyword_list() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_KEYWORDLIST'), 'user.png');
    JToolBarHelper::addNew('keyword.edit');
    JToolBarHelper::editList('keyword.edit');
    JToolBarHelper::deleteList(JText::_('VALIDDELETEITEMS'),'keyword.delete');
  }
  function keyword_edit() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_KEYWORDEDIT'), 'addedit.png');
    JToolBarHelper::apply('keyword.apply');
    JToolBarHelper::save('keyword.save');
    JToolBarHelper::cancel('keyword.cancel');
  }
  function config() {
    JToolBarHelper::title(WBADVERT_TITLE.' '.JText::_('TB_CONFIG'), 'config.png');
    JToolBarHelper::apply('config.save');
    JToolBarHelper::cancel('config.cancel');
  }
}

?>