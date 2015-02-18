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
// Content Cross-Reference Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_idx_content
class wbAdvert_idx_content extends JTable {

  var $advert_id = null;
  var $campaign_id  = null;
  var $content_id = null;

  function __construct( $advert_id = null, $campaign_id = null ) {
    $this->advert_id = $advert_id;
    $this->campaign_id = $campaign_id;
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

  function save( $advert_id = null, $campaign_id = null, $set = null ){
    $db =& JFactory::getDBO();
    $advert_id    = (int)$this->_getAdvertId( $advert_id );
    $campaign_id  = (int)$this->_getCampaignId( $campaign_id );
    if( is_array( $set ) ){
      foreach( $set AS $id ){
        $db->setQuery("
          SELECT *
          FROM #__wbadvert_idx_content
          WHERE `advert_id` = '$advert_id'
            AND `campaign_id` = '$campaign_id'
            AND `content_id` = '$id'
          ");
        $rows = $db->loadObjectList();
        if( !count( $rows ) ){
          $db->setQuery("
            INSERT INTO #__wbadvert_idx_content
            (`advert_id`, `campaign_id`, `content_id`)
            VALUES
            ('$advert_id', '$campaign_id', '$id')
            ");
          $db->query();
        }
      }
    } else if( !is_null($set) ) {
      $db->setQuery("
        SELECT *
        FROM #__wbadvert_idx_content
        WHERE `advert_id` = '$advert_id'
          AND `campaign_id` = '$campaign_id'
          AND `content_id` = '". (int)$set ."'
        ");
      $rows = $db->loadObjectList();
      if( !count( $rows ) ){
        $db->setQuery("
          INSERT INTO #__wbadvert_idx_content
          (`advert_id`, `campaign_id`, `content_id`)
          VALUES
          ('$advert_id', '$campaign_id', '". (int)$set ."')
          ");
        $db->query();
      }
    } else {
      return false;
    }
    return true;
  }

  function delete( $advert_id = null, $campaign_id = null, $set = null ){
    $db =& JFactory::getDBO();
    $advert_id   = $this->_getAdvertId( $advert_id );
    $campaign_id = $this->_getCampaignId( $campaign_id );
    $where = array();
    if( !is_null($advert_id) )
      $where[] = "`advert_id` = '". (int)$advert_id ."'";
    if( !is_null($campaign_id) )
      $where[] = "`campaign_id` = '". (int)$campaign_id ."'";
    if( !is_null( $set ) )
      if( is_array($set) )
        $where[] = "`content_id` IN ('". implode("','",$set) ."')";
      else
        $where[] = "`content_id` = '". (int)$set ."'";
    if( count($where) ) {
      $db->setQuery("
        DELETE FROM `#__wbadvert_idx_content`
        WHERE ". implode(' AND ', $where) ."
        ");
      return $db->query();
    }
  }

  function show_ok( $advert_id = null, $campaign_id = null, $set = null ){
    $advert_id = $this->_get_id( $advert_id );
    if( is_array( $set ) ){
      // Check Many
      $db->setQuery("SELECT * FROM #__wbadvert_idx_content WHERE `advert_id`='$advert_id' AND `content_id` IN (".join(',',$set).")");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    } else if( $set ) {
      // Check One
      $db->setQuery("SELECT * FROM #__wbadvert_idx_content WHERE `advert_id`='$advert_id' AND `content_id`='$set'");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    } else {
      // Set to ALL
      $db->setQuery("SELECT * FROM #__wbadvert_idx_content WHERE `advert_id`='$advert_id'");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    }
    return false;
  }

}
