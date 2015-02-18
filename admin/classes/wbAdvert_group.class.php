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
// Group Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_client
class wbAdvert_group extends JTable {

  var $id = null;
  var $name = null;
  var $description = null;
  var $module_id = null;
  var $count = null;
  var $order = null;
  var $published = 1;
  var $ordering = null;
  var $checked_out = null;
  var $checked_out_time = null;

  function __construct( &$_db ){
    parent::__construct( '#__wbadvert_group', 'id', $_db );
    // $now =& JFactory::getDate();
    // $this->set( 'date', $now->toMySQL() );
  }

  function check() {
    // check for name
    if (trim($this->name == "")) {
      $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Group Name")) );
      return false;
    }
    // check for show count
    if (!(int)$this->count)
      $this->count = 1;
    return true;
  }
}

