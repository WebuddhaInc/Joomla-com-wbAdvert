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
// Filter Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_filter
class wbAdvert_filter {

  var $menu_id = 0;
  var $content_id = 0;
  var $category_id = 0;
  var $category_chain = 0;

  function wbAdvert_filter(){
    global $Itemid, $option, $task, $view, $id;

    $menu = &JSite::getMenu();
    $menuActive = $menu->getActive();
    if( $menuActive->id ){
      $this->menu_id  = $menuActive->id;
      $my_id          = preg_replace('/^(\d+)\:.*/','$1',JRequest::getInt('id', $menuActive->query['id']));
      $my_task        = JRequest::getCmd( 'task', JRequest::getCmd('view', $menuActive->query['view']) );
      $my_option      = JRequest::getCmd( 'option', $menuActive->query['option'] );
     } else {
      $this->menu_id  = JRequest::getInt( 'Itemid', $Itemid );
      $my_id          = preg_replace('/^(\d+)\:.*/','$1',JRequest::getInt('id', $id));
      $my_task        = JRequest::getCmd( 'task', JRequest::getCmd('view', $task) );
      $my_option      = JRequest::getCmd( 'option', $option );
    }

    $db =& JFactory::getDBO();

    if( $my_option == 'com_content' ){
      if( preg_match('/category/',$my_task) && $my_id ){
        $db->setQuery("SELECT * FROM `#__categories` WHERE `id` = '". (int)$my_id ."'");
        $category = array_shift($db->loadObjectList()); echo $db->getErrorMsg();
        $this->category_id    = $category->id;
      }
      else if( $my_id ){
        $db->setQuery("SELECT * FROM `#__content` WHERE `id` = '". (int)$my_id ."'");
        $content = array_shift($db->loadObjectList()); echo $db->getErrorMsg();
        $this->content_id     = $content->id;
        $this->category_id    = $content->catid;
      }
      $this->category_chain = array();
      if( $this->category_id ){
        $db->setQuery("SELECT `id`, `lft`, `rgt` FROM `#__categories` WHERE `id` = '". (int)$this->category_id ."'");
        $catRow = array_shift($db->loadObjectList()); echo $db->getErrorMsg();
        $db->setQuery("SELECT `id` FROM `#__categories` WHERE `lft` < '". (int)$catRow->lft ."' AND `rgt` > '". (int)$catRow->rgt ."'");
        $catRows = $db->loadObjectList(); echo $db->getErrorMsg();
        if(count($catRows))
          foreach($catRows AS $tmp)
            $this->category_chain[] = $tmp->id;
        $this->category_chain[] = $this->category_id;
      }
    }

    $this->menu_id        = (int)$this->menu_id;
    $this->category_chain = (array)$this->category_chain;
    $this->category_id    = (int)$this->category_id;
    $this->content_id     = (int)$this->content_id;

  }

}

