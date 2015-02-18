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
// Section Cross-Reference Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_idx_section
class wbAdvert_idx_section extends JTable {

  var $advert_id = null;
  var $section_id = null;

  function wbAdvert_idx_section( $advert_id = null ) {
    $this->advert_id = $advert_id;
  }

  function _get_id( $advert_id=null ){
    if( !$advert_id && $this->advert_id ){
      return $this->advert_id;
    } else if( !$advert_id ){
      return null;
    }
    return $advert_id;
  }

  function show_ok( $advert_id = null, $set = null ){
    $advert_id = $this->_get_id( $advert_id );
    if( is_array( $set ) ){
      // Check Many
      $db->setQuery("SELECT * FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id' AND `section_id` IN (".join(',',$set).")");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    } else if( $set ) {
      // Check One
      $db->setQuery("SELECT * FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id' AND `section_id`='$set'");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    } else {
      // Set to ALL
      $db->setQuery("SELECT * FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id'");
      $rows = $db->loadObjectList();
      return count( $rows ) ? true : false;
    }
    return false;
  }

  function save( $advert_id = null, $set = null ){
    $db =& JFactory::getDBO();
    $advert_id = $this->_get_id( $advert_id );
    if( is_array( $set ) ){
      foreach( $set AS $id ){
        $db->setQuery("SELECT * FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id' AND `section_id`='$id'");
        $rows = $db->loadObjectList();
        if( !count( $rows ) ){
          $db->setQuery("INSERT INTO #__wbadvert_idx_section VALUES ('$advert_id', '$id')");
          $db->query();
        }
      }
    } else if( $set !== null ) {
      $db->setQuery("INSERT INTO #__wbadvert_idx_section VALUES ('$advert_id', '$set')");
      $db->query();
    } else {
      return false;
    }
    return true;
  }

  function delete( $advert_id = null, $set = null ){
    $db =& JFactory::getDBO();
    $advert_id = $this->_get_id( $advert_id );
    if( is_array( $set ) ){
      foreach( $set AS $id ){
        $db->setQuery("DELETE FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id' AND `section_id`='$id'");
        $db->query();
      }
    } else if( $set ) {
      $db->setQuery("DELETE FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id' AND `section_id`='$set'");
      $db->query();
    } else {
      $db->setQuery("DELETE FROM #__wbadvert_idx_section WHERE `advert_id`='$advert_id'");
      $db->query();
    }

  }

}

