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
// Advertisement Group Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_idx_group
class wbAdvert_idx_group extends JTable {

  var $advert_id    = null;
  var $campaign_id  = null;
  var $group_id     = null;
  var $ordering     = null;

  function __construct( $db = null ) {
    $db = $db ? $db : JFactory::getDBO();
    parent::__construct( '#__wbadvert_idx_group', null, $db );
  }

  function _getAdvertId( $advert_id = null ){
    if( !$advert_id && $this->advert_id ){
      return $this->advert_id;
    } else if( !$advert_id ){
      return null;
    }
    return $advert_id;
  }

  function _getCampaignId( $campaign_id = null ){
    if( !$campaign_id && $this->campaign_id ){
      return $this->campaign_id;
    } else if( !$campaign_id ){
      return null;
    }
    return $campaign_id;
  }

  function _getGroupId( $group_id = null ){
    if( !$group_id && $this->group_id ){
      return $this->group_id;
    } else if( !$group_id ){
      return null;
    }
    return $group_id;
  }

  function loadRow( $advert_id, $campaign_id, $group_id ){
    $this->_db->setQuery("
      SELECT *
      FROM #__wbadvert_idx_group
      WHERE `advert_id` = '". (int)$advert_id ."'
        AND `campaign_id` = '". (int)$campaign_id ."'
        AND `group_id` = '". (int)$group_id ."'
      LIMIT 1
      ");
    $this->bind( $this->_db->loadObject() );
  }

  function moveRow( $inc ){
    $rows = $this->_db->setQuery("
      SELECT *
      FROM `#__wbadvert_idx_group`
      WHERE `advert_id` = '". (int)$this->_getAdvertId() ."'
        AND `campaign_id` = '". (int)$this->_getCampaignId() ."'
        AND `group_id` = '". (int)$this->_getGroupId() ."'
      ORDER BY `ordering` ASC
      ")
      ->loadObjectList();
    for( $i=0; $i < count($rows); $i++ ){
      $newOrdering = ($inc > 0 ? $rows[$i]->ordering+1 : $rows[$i]->ordering-1);
      $this->_db->setQuery("
        UPDATE `#__wbadvert_idx_group`
        SET `ordering` = '". $rows[$i]->ordering ."'
        WHERE `ordering` = '". $newOrdering ."'
          AND `advert_id` != '". (int)$rows[$i]->advert_id ."'
          AND `campaign_id` = '". (int)$rows[$i]->campaign_id ."'
          AND `group_id` = '". (int)$rows[$i]->group_id ."'
        ")
        ->query();
      $this->_db->setQuery("
        UPDATE `#__wbadvert_idx_group`
        SET `ordering` = '". $newOrdering ."'
        WHERE `ordering` = '". $rows[$i]->ordering ."'
          AND `advert_id` = '". (int)$rows[$i]->advert_id ."'
          AND `campaign_id` = '". (int)$rows[$i]->campaign_id ."'
          AND `group_id` = '". (int)$rows[$i]->group_id ."'
        ")
        ->query();
    }
  }

  function reorderRows( $campaign_id, $group_id ){
    $rows = $this->_db->setQuery("
      SELECT *
      FROM `#__wbadvert_idx_group`
      WHERE `campaign_id` = '". (int)$campaign_id ."'
        AND `group_id` = '". (int)$group_id ."'
      ORDER BY `ordering` ASC
      ")
      ->loadObjectList();
    for( $i=0; $i < count($rows); $i++ ){
      $this->_db->setQuery("
        UPDATE `#__wbadvert_idx_group`
        SET `ordering` = '". (int)($i+1) ."'
        WHERE `advert_id` = '". (int)$rows[$i]->advert_id ."'
          AND `campaign_id` = '". (int)$rows[$i]->campaign_id ."'
          AND `group_id` = '". (int)$rows[$i]->group_id ."'
        ")
        ->query();
    }
  }

  function save( $advert_id = null, $campaign_id = null, $set = null, $order = 0 ){
    $advert_id    = (int)$this->_getAdvertId( $advert_id );
    $campaign_id  = (int)$this->_getCampaignId( $campaign_id );
    if( is_array( $set ) ){
      foreach( $set AS $id ){
        $this->_db->setQuery("
          SELECT *
          FROM #__wbadvert_idx_group
          WHERE `advert_id` = '". (int)$advert_id ."'
            AND `campaign_id` = '". (int)$campaign_id ."'
            AND `group_id` = '". (int)$id ."'
          LIMIT 1
          ");
        $rows = $this->_db->loadObjectList();
        if( !count( $rows ) ){
          $this->_db->setQuery("
            INSERT INTO #__wbadvert_idx_group
            (`advert_id`, `campaign_id`, `group_id`, `ordering`)
            VALUES
            ('". (int)$advert_id ."', '". (int)$campaign_id ."', '". (int)$id ."', '". $this->_db->escape($order) ."')
            ");
          $this->_db->query();
        }
      }
    }
    elseif( !is_null($set) ) {
      $this->_db->setQuery("
        SELECT *
        FROM #__wbadvert_idx_group
        WHERE `advert_id` = '". (int)$advert_id ."'
          AND `campaign_id` = '". (int)$campaign_id ."'
          AND `group_id` = '". (int)$set ."'
        LIMIT 1
        ");
      $rows = $this->_db->loadObjectList();
      if( count($rows) ){
        $this->_db->setQuery("
          UPDATE #__wbadvert_idx_group
          SET `ordering` = '$order'
          WHERE `advert_id` = '". (int)$advert_id ."'
            AND `campaign_id` = '". (int)$campaign_id ."'
            AND `group_id` = '". (int)$set ."'
          ");
        $this->_db->query();
      }
      else {
        $this->_db->setQuery("
          INSERT INTO #__wbadvert_idx_group
          (`advert_id`, `campaign_id`, `group_id`, `ordering`)
          VALUES
          ('". (int)$advert_id ."', '". (int)$campaign_id ."', '". (int)$set ."', '". $this->_db->escape($order) ."')
          ");
        $this->_db->query();
      }
    }
    else {
      return false;
    }
    return true;
  }

  function delete( $advert_id = null, $campaign_id = null, $set = null ){
    $advert_id    = $this->_getAdvertId( $advert_id );
    $campaign_id  = $this->_getCampaignId( $campaign_id );
    $where = array();
    if( !is_null($advert_id) )
      $where[] = "`advert_id` = '". (int)$advert_id ."'";
    if( !is_null($campaign_id) )
      $where[] = "`campaign_id` = '". (int)$campaign_id ."'";
    if( !is_null( $set ) )
      if( is_array($set) )
        $where[] = "`group_id` IN ('". implode("','",$set) ."')";
      else
        $where[] = "`group_id` = '". (int)$set ."'";
    if( count($where) ) {
      $this->_db->setQuery("
        DELETE FROM `#__wbadvert_idx_group`
        WHERE ". implode(' AND ', $where) ."
        ");
      return $this->_db->query();
    }
    return false;
  }

  function show_ok( $advert_id = null, $set = null ){
    die(' -- defunct --');
    $advert_id    = (int)$this->_getAdvertId( $advert_id );
    $campaign_id  = (int)$this->_getCampaignId( $campaign_id );
    if( is_array( $set ) ){
      // Check Many
      $this->_db->setQuery("SELECT * FROM #__wbadvert_idx_group WHERE `advert_id`='$advert_id' AND `group_id` IN (".join(',',$set).")");
      $rows = $this->_db->loadObjectList();
      return count( $rows ) ? true : false;
    } else if( $set ) {
      // Check One
      $this->_db->setQuery("SELECT * FROM #__wbadvert_idx_group WHERE `advert_id`='$advert_id' AND `group_id`='$set'");
      $rows = $this->_db->loadObjectList();
      return count( $rows ) ? true : false;
    } else {
      // Set to ALL
      $this->_db->setQuery("SELECT * FROM #__wbadvert_idx_group WHERE `advert_id`='$advert_id'");
      $rows = $this->_db->loadObjectList();
      return count( $rows ) ? true : false;
    }
    return false;
  }

}

