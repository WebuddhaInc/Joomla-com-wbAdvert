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
// Client Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert_client
class wbAdvert_client extends JTable {

  var $id = null;
  var $name = null;
  var $contact = null;
  var $email = null;
  var $extrainfo = null;
  var $published = 1;
  var $ordering = null;
  var $checked_out = null;
  var $checked_out_time = null;

  function __construct( &$_db ){
    parent::__construct( '#__wbadvert_client', 'id', $_db );
  }

  function check() {
    // check for valid client name
    if (trim($this->name == "")) {
      $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Client Name")) );
      return false;
    }
    // check for valid client contact
    // if (trim($this->contact == "")) {
    //   $this->_error = "Invalid Client Contact";
    //   return false;
    // }
    // check for valid client email
    // if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
    //   $this->_error = "Invalid Contact Email Address";
    //   return false;
    // }
    return true;
  }
}

