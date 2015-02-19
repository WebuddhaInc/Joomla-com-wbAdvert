<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/07
  Last Modified:  2013-03-28
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
// Advertisement Class
//
// ************************************************************************************************

// ------------------------------------------------------------------------------------------------------ wbAdvert
class wbAdvert_advert extends JTable {

  var $id = null;
  var $client_id = null;
  var $name = null;
  var $caption = null;
  var $imptotal = null;
  var $impmade = null;
  var $target = null;
  var $clicks = null;
  var $file_type = null;
  var $width = null;
  var $height = null;
  var $url = null;
  var $code = null;
  var $date_start = null;
  var $date_stop = null;
  var $weekdays = null;   // + v147
  var $time_start = null; // + v147
  var $time_stop = null;  // + v147
  var $created = null;
  var $modified = null;
  var $published = null;
  var $checked_out = null;
  var $checked_out_time = null;

  // ************************************************************************************************************************************************************
  function __construct( &$_db ){
    parent::__construct( '#__wbadvert_advert', 'id', $_db );
    $this->set( 'modified', JFactory::getDate()->toSql() );
  }

  // ************************************************************************************************************************************************************
  function reorder( $group_id ) {
    $this->_db->setQuery( "SELECT `advert_id` FROM `#__wbadvert_idx_group` WHERE `group_id` = '$group_id' ORDER BY `ordering` ASC" );
    $rows = $this->_db->loadObjectList();
    $count = 1;
    foreach( $rows AS $row ){
      $this->_db->setQuery( "UPDATE `#__wbadvert_idx_group` SET `ordering` = '". $count++ ."' WHERE `advert_id` = '$row->advert_id' AND `group_id` = '$group_id'" );
      $this->_db->query();
    }
  }

  // ************************************************************************************************************************************************************
  function move( $inc, $group_id ) {
    $this->_db->setQuery( "SELECT * FROM `#__wbadvert_idx_group` WHERE `advert_id` = '$this->id' AND `group_id` = '$group_id'" );
    $row = array_shift($this->_db->loadObjectList());
    if( $row->advert_id ){
      $lastOrder = $row->ordering;
      $nextOrder = $lastOrder + ($inc > 0 ? 1 : -1);
      $this->_db->setQuery( "UPDATE `#__wbadvert_idx_group` SET `ordering` = '$lastOrder' WHERE `group_id` = '$group_id' AND `ordering` = '$nextOrder'" );
      $this->_db->query();
      $this->_db->setQuery( "UPDATE `#__wbadvert_idx_group` SET `ordering` = '$nextOrder' WHERE `advert_id` = '$this->id' AND `group_id` = '$group_id'" );
      $this->_db->query();
      $this->reorder( $group_id );
    }
  }

  // ************************************************************************************************************************************************************
  function impression( $id = null ) {
    if( !$id ) $id = $this->id;
    $this->_db->setQuery( "UPDATE #__wbadvert_advert SET impmade=(impmade+1) WHERE id=$id" );
    $this->_db->query();
  }

  // ************************************************************************************************************************************************************
  function click( $id = null ) {
    if( !$id ) $id = $this->id;
    $this->_db->setQuery( "UPDATE #__wbadvert_advert SET clicks=(clicks+1) WHERE id=$id" );
    $this->_db->query();
  }

  // ************************************************************************************************************************************************************
  function check() {
    // check for valid client id
    if (is_null($this->client_id) || $this->client_id == 0) {
      $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Advertisement Client")) );
      return false;
    }
    if(trim($this->name) == "") {
      $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Advertisement Name")) );
      return false;
    }
    if( $this->id ){
      if( (trim($this->file_type) == "") && (trim($this->code) == "") ) {
        $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Advertisement File Type")) );
        return false;
      }
      if( (trim($this->code) == "") && !in_array( $this->file_type, Array('gif','jpg','png','bmp','swf') ) ) {
        $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Advertisement Image or Custom Code")) );
        return false;
      }
    } else {
      $this->published = 0;
    }
    if(strlen($this->url)){
      $this->url = trim($this->url);
      if( !preg_match('/^\w+\:/',$this->url)
        && !preg_match('/^\//',$this->url)
        && !preg_match('/^index/',$this->url)
        )
          $this->url = 'http://'.$this->url;
    }
    return true;
  }

  // ************************************************************************************************************************************************************
  function getAdvertCode( $advert_id = null ){
    $advert = $this->getAdvertObject( $advert_id );
    return is_object($advert) ? $advert->code : false;
  }

  // ************************************************************************************************************************************************************
  function getAdvertObject( $advert_id = null ){
    $app = JFactory::getApplication();

    // Load DB
    $db =& JFactory::getDBO();
    $wbAdvert_config = wbAdvert_config::getInstance();

    // Check and Load...
    if( !is_object( $advert_id ) ){
      if( !$advert_id && $this->id )
        $advert_id = $this->id;
      if( !$advert_id )
        return false;
      $db->setQuery("SELECT * FROM `#__wbadvert_advert` WHERE `id`='$advert_id' LIMIT 1");
      $advert = array_shift( $db->loadObjectList() );
    } else
      $advert =& $advert_id;

    // Check Record is Valid..
    if( !$advert->id )
      return false;
    if( $advert->code != '' )
      return $advert;
    if( !$advert->file_type )
      return false;

    // Determine Click Target
    if( !strlen($advert->url) && !in_array($advert->file_type,Array('swf')) ){
      $advert->link = null;
    } elseif( $wbAdvert_config->get('track_clicks') || $wbAdvert_config->get('load_frame') ){
      $advert->link = JURI::root().'index.php?option=com_wbadvert&task=load&id='.$advert->id;
      if( function_exists('sefRelToAbs') )
        $advert->link = sefRelToAbs($advert->link);
    } else {
      $advert->link = $advert->url;
    }

    // Determine SWF Parameter
    $forceVal = $wbAdvert_config->get('ad_force',1) ? substr(md5(time().session_id()),0,10) : null;
    $clickTag = $wbAdvert_config->get('swf_clicktag', 'clickTAG');
    $targeTag = $wbAdvert_config->get('swf_targetag', 'targetTAG');

    // IMG Advertisement
    if( in_array( $advert->file_type, Array('gif','jpg','png','bmp') ) ){
      // $advert->src = JURI::root().$wbAdvert_config->get('ad_path').$advert->id.'.'.$advert->file_type.($forceVal ? '?z='.$forceVal : '');
      $advert->src = $wbAdvert_config->getAdSite().$advert->id.'.'.$advert->file_type.($forceVal ? '?z='.$forceVal : '');
      if( is_null($advert->link) )
        $advert->code = '<img src="'.$advert->src.'" border="0" '.($advert->width ? 'width="'.$advert->width.'"' : '').' '
                      . ($advert->height ? 'height="'.$advert->height.'"' : '').' />';
      else
        $advert->code = '<a href="'.$advert->link.'" title="'.($advert->caption ? $advert->caption : JText::_('COM_WBADVERT_LINK_DEFAULTCAPTION')).'" target="'.$advert->target.'"><img src="'
                      . $advert->src.'" border="0" '.($advert->width ? 'width="'.$advert->width.'"' : '').' '.($advert->height ? 'height="'.$advert->height.'"' : '').' /></a>';
    }

    // SWF Advertisement
    else if( in_array( $advert->file_type, Array('swf') ) ){

      // SWF Wrapper
      if( $wbAdvert_config->get('wrap_swf',0) )
        // $advert->src = $wbAdvert_config->getAdSite().'wbadvert_wrapper.swf?i='.$advert->id.'&t='.htmlspecialchars($advert->target).($forceVal ? '&z='.$forceVal : '');
        $advert->src = $wbAdvert_config->getAdSite().'wbadvert_wrapper.swf?cfg='.base64_encode($advert->id.'|'.$advert->target.'|'.WBADVERT_SITE.'|'.$wbAdvert_config->get('ad_path')).($forceVal ? '&z='.$forceVal : '');
      elseif( $wbAdvert_config->get('swf_base64ct',0) )
        $advert->src = $wbAdvert_config->getAdSite().$advert->id.'.'.$advert->file_type.'?'.$clickTag.'='.base64_encode($advert->link).'&'.$targeTag.'='.htmlspecialchars($advert->target).($forceVal ? '&z='.$forceVal : '');
      else
        $advert->src = $wbAdvert_config->getAdSite().$advert->id.'.'.$advert->file_type.'?'.$clickTag.'='.urlencode(utf8_encode($advert->link)).'&'.$targeTag.'='.htmlspecialchars($advert->target).($forceVal ? '&z='.$forceVal : '');

      // SWFOBJECT Javascript
      if( $wbAdvert_config->get('swf_jsloader',0) ){
        /*
          swfobject.js
          http://code.google.com/p/swfobject/wiki/documentation
        */
        $advert->swf_cell = 'wbadvert_swf_'.rand($advert->id,time());
        $advert->code = '<div id="'.$advert->swf_cell.'">'
                      . ( !empty($advert->code) ? $advert->code : '<p><b>'. JText::_('ERR_FLASH_REQUIRED') .'</b> <a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>' )
                      . '</div>'
                      . '<script type="text/javascript">swfobject.embedSWF("'.$advert->src.'","'.$advert->swf_cell.'","'.$advert->width.'","'.$advert->height.'","9.0.0","expressInstall.swf",{},{wmode:\'transparent\'},{});</script>';
      }

      // EMBED Tag
      else {
        /* embed tag */
        $advert->code = '<embed wmode="transparent" src="'.$advert->src.'"'
                      . ($advert->width ? ' width="'.$advert->width.'"' : '')
                      . ($advert->height ? ' height="'.$advert->height.'"' : '')
                      . ' bgcolor="#FFFFFF" play="true" loop="false" quality="high"'
                      . ' menu="false" type="application/x-shockwave-flash"'
                      . ' pluginspage="http://www.macromedia.com/go/flashplayer/"></embed>';
        /* wrap in object tag */
        if( $wbAdvert_config->get('swf_objectag',1) )
          $advert->code
            = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'
            . ' codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"'
            . ($advert->width ? ' width="'.$advert->width.'"' : '')
            . ($advert->height ? ' height="'.$advert->height.'"' : '')
            . '>'
            . '<param name=movie value="'.$advert->src.'">'
            . '<param name=wmode value=transparent>'
            . '<param name=quality value=high>'
            . '<param name=bgcolor value=#ffffff>'
            . $advert->code
            . '</object>';
      }
    }

    // Return Object
    return $advert;
  }

  // ************************************************************************************************************************************************************
  function upload( $field_id = 'advert_file' ){
    $app = JFactory::getApplication();
    $wbAdvert_config = wbAdvert_config::getInstance();

    // Pre-Processing Checks
    if( !$this->id ){
      if( !$this->store() ){
        $this->setError( JText::sprintf('ERR_INVALIDX', JText::_("Advertisement ID")) );
        return false;
      }
    }

    // Check File Permissions
    if(!is_writable(JPATH_ROOT.'/media/')) {
      $this->setError( JText::sprintf('ERR_FILEWRITEFAIL', "/media") );
      return false;
    }

    // Check File Permissions
    $ad_path = $wbAdvert_config->getAdPath();
    if( !wbAdvert_Common::isWritable($ad_path) ){
      $this->setError( JText::sprintf('ERR_FILEWRITEFAIL', $wbAdvert_config->getAdPath(false)) );
      return false;
    }

    // Pre-Processing Checks
    if ( !isset($_FILES[$field_id]) || !is_array($_FILES[$field_id]) || ($_FILES[$field_id]['tmp_name'] == '') ){
      return true;
    }

    // Check Upload File
    if (!is_uploaded_file($_FILES[$field_id]['tmp_name'])) {
      $this->setError( JText::_('No Upload File Provided') );
      return false;
    }

    // Get File Type
    $this->file_type = strtolower( preg_replace("/^.*\.(\w+)$/","$1",$_FILES[$field_id]['name']) );

    // Get File Dimensions
    if ( in_array( $this->file_type, Array('gif','jpg','png','bmp') ) ) {
      $imginfo = getimagesize($_FILES[$field_id]['tmp_name']);
      if ($imginfo == null) {
        $this->setError( JText::_('Error Pulling File Dimensions') );
        return false;
      }
      $this->width  = $imginfo[0];
      $this->height = $imginfo[1];
    } else if ( in_array( $this->file_type, Array('swf') ) ) {
      $swf = new wbAdvert_swfHeader(false);
      $swf->loadswf($_FILES[$field_id]['tmp_name']);
      if ( !$swf->size ) {
        $this->setError( JText::_('Error Pulling File Dimensions') );
        return false;
      }
      $this->width  = $swf->width;
      $this->height = $swf->height;
    } else {
      $this->width  = 0;
      $this->height = 0;
    }

    // Delete Existing File
    // if( $this->file_type && file_exists(JPATH_ROOT.'/'.$wbAdvert_config->get('ad_path').$this->id.'.'.$this->file_type) && !unlink(JPATH_ROOT.'/'.$wbAdvert_config->get('ad_path').$this->id.'.'.$this->file_type) ){
    if( $this->file_type && file_exists($wbAdvert_config->getAdPath().$this->id.'.'.$this->file_type) && !unlink($wbAdvert_config->getAdPath().$this->id.'.'.$this->file_type) ){
      $this->setError( JText::_('Error Deleting Existing File') );
      return false;
    }

    // Verify Formats
    if ( !in_array( $this->target, Array('_blank','_self','_top','_parent') ) ) {
      $this->setError( JText::sprintf('ERR_INVALIDX', JText::_('Advertisement Link Target')) );
      return false;
    }

    // Verify Formats
    if ( !in_array( $this->file_type, Array('gif','jpg','png','bmp','swf') ) ) {
      $this->setError( JText::_('ERR_INVALIDFORMAT') );
      return false;
    }

    // Copy or Overwrite
    // if( !copy($_FILES[$field_id]['tmp_name'], JPATH_ROOT.'/'.$wbAdvert_config->get('ad_path').$this->id.'.'.$this->file_type) ) {
    if( !copy($_FILES[$field_id]['tmp_name'], $wbAdvert_config->getAdPath().$this->id.'.'.$this->file_type) ) {
      $this->setError(  JText::_('Error Saving Record') );
      return false;
    }

    return true;
  } // -- upload --

  // ************************************************************************************************************************************************************
  function getAdvertListParamDefault( $key ){
    $app = JFactory::getApplication();
    $val = null;
    switch( $key ){
      case 'date':
        $val = date( 'Y-m-d', time() + ($app->getCfg('offset') * 60 * 60));
        break;
      case 'weekdays':
        $val = array( strtolower(  date( 'D', time() + ($app->getCfg('offset') * 60 * 60)) ) );
        break;
      case 'time':
        $val = strtolower(  date( 'Hi', time() + ($app->getCfg('offset') * 60 * 60)) );
        break;
    }
    return $val;
  }

  // ************************************************************************************************************************************************************
  function &getAdvertList( $params=array() ){

    // Prepare Data
    $qPars = array(
      'advert_id'       => ( !isset($params['advert_id']) ? self::getAdvertListParamDefault('advert_id') : (is_array($params['advert_id']) ? $params['advert_id'] : array($params['advert_id'])) ),
      'not_advert_id'   => ( !isset($params['not_advert_id']) ? self::getAdvertListParamDefault('not_advert_id') : (is_array($params['not_advert_id']) ? $params['not_advert_id'] : array($params['not_advert_id'])) ),
      'date'            => ( !isset($params['date']) ? self::getAdvertListParamDefault('date') : $params['date'] ),
      'date_start'      => ( !isset($params['date_start']) ? self::getAdvertListParamDefault('date_start') : $params['date_start'] ),
      'date_stop'       => ( !isset($params['date_stop']) ? self::getAdvertListParamDefault('date_stop') : $params['date_stop'] ),
      'weekdays'        => ( !isset($params['weekdays']) ? self::getAdvertListParamDefault('weekdays') : (is_array($params['weekdays']) ? $params['weekdays'] : array($params['weekdays'])) ),
      'time'            => ( !isset($params['time']) ? self::getAdvertListParamDefault('time') : $params['time'] ),
      'time_start'      => ( !isset($params['time_start']) ? self::getAdvertListParamDefault('time_start') : $params['time_start'] ),
      'time_stop'       => ( !isset($params['time_stop']) ? self::getAdvertListParamDefault('time_stop') : $params['time_stop'] ),
      'not_group_id'    => ( !isset($params['not_group_id']) ? self::getAdvertListParamDefault('not_group_id') : (is_array($params['not_group_id']) ? $params['not_group_id'] : array($params['not_group_id'])) ),
      'group_id'        => ( !isset($params['group_id']) ? self::getAdvertListParamDefault('group_id') : (is_array($params['group_id']) ? $params['group_id'] : array($params['group_id'])) ),
      'not_group_id'    => ( !isset($params['not_group_id']) ? self::getAdvertListParamDefault('not_group_id') : (is_array($params['not_group_id']) ? $params['not_group_id'] : array($params['not_group_id'])) ),
      'client_id'       => ( !isset($params['client_id']) ? self::getAdvertListParamDefault('client_id') : (is_array($params['client_id']) ? $params['client_id'] : array($params['client_id'])) ),
      'not_client_id'   => ( !isset($params['not_client_id']) ? self::getAdvertListParamDefault('not_client_id') : (is_array($params['not_client_id']) ? $params['not_client_id'] : array($params['not_client_id'])) ),
      'menu_id'         => ( !isset($params['menu_id']) ? self::getAdvertListParamDefault('menu_id') : (is_array($params['menu_id']) ? $params['menu_id'] : array($params['menu_id'])) ),
      'not_menu_id'     => ( !isset($params['not_menu_id']) ? self::getAdvertListParamDefault('not_menu_id') : (is_array($params['not_menu_id']) ? $params['not_menu_id'] : array($params['not_menu_id'])) ),
      'category_id'     => ( !isset($params['category_id']) ? self::getAdvertListParamDefault('category_id') : (is_array($params['category_id']) ? $params['category_id'] : array($params['category_id'])) ),
      'not_category_id' => ( !isset($params['not_category_id']) ? self::getAdvertListParamDefault('not_category_id') : (is_array($params['not_category_id']) ? $params['not_category_id'] : array($params['not_category_id'])) ),
      'content_id'      => ( !isset($params['content_id']) ? self::getAdvertListParamDefault('content_id') : (is_array($params['content_id']) ? $params['content_id'] : array($params['content_id'])) ),
      'not_content_id'  => ( !isset($params['not_content_id']) ? self::getAdvertListParamDefault('not_content_id') : (is_array($params['not_content_id']) ? $params['not_content_id'] : array($params['not_content_id'])) ),
      'ordering'        => ( !isset($params['ordering']) ? self::getAdvertListParamDefault('ordering') : (is_array($params['ordering']) ? $params['ordering'] : array($params['ordering'])) ),
      'limit'           => ( !isset($params['limit']) ? self::getAdvertListParamDefault('limit') : $params['limit'] ),
      'file_type'       => ( !isset($params['file_type']) ? self::getAdvertListParamDefault('file_type') : (is_array($params['file_type']) ? $params['file_type'] : array($params['file_type'])) ),
      'not_file_type'   => ( !isset($params['not_file_type']) ? self::getAdvertListParamDefault('not_file_type') : (is_array($params['not_file_type']) ? $params['not_file_type'] : array($params['not_file_type'])) ),
      'width'           => ( !isset($params['width']) ? self::getAdvertListParamDefault('width') : (is_array($params['width']) ? (int)$params['width'] : array($params['width'])) ),
      'min_width'       => ( !isset($params['min_width']) ? self::getAdvertListParamDefault('min_width') : (int)$params['min_width'] ),
      'max_width'       => ( !isset($params['max_width']) ? self::getAdvertListParamDefault('max_width') : (int)$params['max_width'] ),
      'height'          => ( !isset($params['height']) ? self::getAdvertListParamDefault('height') : (is_array($params['height']) ? (int)$params['height'] : array($params['height'])) ),
      'min_height'      => ( !isset($params['min_height']) ? self::getAdvertListParamDefault('min_height') : (int)$params['min_height'] ),
      'max_height'      => ( !isset($params['max_height']) ? self::getAdvertListParamDefault('max_height') : (int)$params['max_height'] )
      );

    // Ordering
    if( !isset($qPars['ordering']) )
      $qPars['ordering'] = array('RAND()');

    // Where
    $where = array();
    $where[] = "`advert`.`published` = '1'";
    $where[] = "`group`.`published` = '1'";
    $where[] = "`client`.`published` = '1'";
    $where[] = "((`advert`.`imptotal` = 0) OR (`advert`.`imptotal` > `advert`.`impmade`))";

    if( $qPars['date'] ){
      $where[] = "((`advert`.`date_start` = '0000-00-00 00:00:00') OR (`advert`.`date_start` <= '". $qPars['date'] ."'))";
      $where[] = "((`advert`.`date_stop` = '0000-00-00 00:00:00') OR (`advert`.`date_stop` >= '". $qPars['date'] ."'))";
    }

    if( $qPars['time'] ){
      $where[] = "((`advert`.`time_start` IS NULL) OR (`advert`.`time_start` <= '". $qPars['time'] ."'))";
      $where[] = "((`advert`.`time_stop` IS NULL) OR (`advert`.`time_stop` >= '". $qPars['time'] ."'))";
    }

    if( is_array($qPars['weekdays']) ){
      $tmp = array();
      $tmp[] = "`advert`.`weekdays` IS NULL";
      foreach($qPars['weekdays'] AS $day)
        $tmp[] = "`advert`.`weekdays` LIKE '%". $day ."%'";
      $where[] = "(". implode(' OR ',$tmp) .")";
    }

    if( is_array($qPars['advert_id']) && count($qPars['advert_id']) )
      $where[] = "`advert`.`id` IN ('". implode("','",$qPars['advert_id']) ."')";
    if( is_array($qPars['not_advert_id']) && count($qPars['not_advert_id']) )
      $where[] = "`advert`.`id` NOT IN ('". implode("','",$qPars['not_advert_id']) ."')";

    if( is_array($qPars['group_id']) && count($qPars['group_id']) )
      $where[] = "((`idx_group`.`group_id` IN ('". implode("','",$qPars['group_id']) ."')) OR (`idx_group`.`group_id` = 0))";
    if( is_array($qPars['not_group_id']) && count($qPars['not_group_id']) )
      $where[] = "((`idx_group`.`group_id` NOT IN ('". implode("','",$qPars['not_group_id']) ."')) OR (`idx_group`.`group_id` = 0))";

    if( is_array($qPars['client_id']) && count($qPars['client_id']) )
      $where[] = "`advert`.`client_id` IN ('". implode("','",$qPars['client_id']) ."')";
    if( is_array($qPars['not_client_id']) && count($qPars['not_client_id']) )
      $where[] = "`advert`.`client_id` NOT IN ('". implode("','",$qPars['not_client_id']) ."')";

    if( is_array($qPars['menu_id']) && count($qPars['menu_id']) )
      $where[] = "((`idx_menu`.`menu_id` IN ('". implode("','",$qPars['menu_id']) ."')) OR (`idx_menu`.`menu_id` = 0))";
    if( is_array($qPars['not_menu_id']) && count($qPars['not_menu_id']) )
      $where[] = "((`idx_menu`.`menu_id` NOT IN ('". implode("','",$qPars['not_menu_id']) ."')) OR (`idx_menu`.`menu_id` = 0))";

    if( is_array($qPars['category_id']) && count($qPars['category_id']) )
      $where[] = "((`idx_category`.`category_id` IN ('". implode("','",$qPars['category_id']) ."')) OR (`idx_category`.`category_id` = 0))";
    if( is_array($qPars['not_category_id']) && count($qPars['not_category_id']) )
      $where[] = "((`idx_category`.`category_id` NOT IN ('". implode("','",$qPars['not_category_id']) ."')) OR (`idx_category`.`category_id` = 0))";

    if( is_array($qPars['content_id']) && count($qPars['content_id']) )
      $where[] = "((`idx_content`.`content_id` IN ('". implode("','",$qPars['content_id']) ."')) OR (`idx_content`.`content_id` = 0))";
    if( is_array($qPars['not_content_id']) && count($qPars['not_content_id']) )
      $where[] = "((`idx_content`.`content_id` NOT IN ('". implode("','",$qPars['not_content_id']) ."')) OR (`idx_content`.`content_id` = 0))";

    if( is_array($qPars['file_type']) && count($qPars['file_type']) )
      $where[] = "`advert`.`file_type` IN ('". implode("','",$qPars['file_type']) ."')";
    if( is_array($qPars['not_type']) && count($qPars['not_type']) )
      $where[] = "`advert`.`file_type` NOT IN ('". implode("','",$qPars['file_type']) ."')";

    if( is_array($qPars['width']) && count($qPars['width']) )
      $where[] = "`advert`.`width` IN ('". implode("','",$qPars['width']) ."')";
    if( isset($qPars['min_width']) && !is_null($qPars['min_width']) )
      $where[] = "`advert`.`width` >= '". $qPars['min_width'] ."'";
    if( isset($qPars['max_width']) && !is_null($qPars['max_width']) )
      $where[] = "`advert`.`width` <= '". $qPars['max_width'] ."'";

    if( is_array($qPars['height']) && count($qPars['height']) )
      $where[] = "`advert`.`height` IN ('". implode("','",$qPars['height']) ."')";
    if( isset($qPars['min_height']) && !is_null($qPars['min_height']) )
      $where[] = "`advert`.`height` >= '". $qPars['min_height'] ."'";
    if( isset($qPars['max_height']) && !is_null($qPars['max_height']) )
      $where[] = "`advert`.`height` <= '". $qPars['max_height'] ."'";

    // echo '<pre>'; print_r($where); print_r($qPars); die();

    // Get DBO
    $db =& JFactory::getDBO();

    // Pull Advertisements
    $db->setQuery("
      SELECT advert.* FROM #__wbadvert_advert AS advert
      LEFT JOIN #__wbadvert_client AS `client` ON `client`.`id` = `advert`.`client_id`
      LEFT JOIN #__wbadvert_idx_group AS `idx_group` ON `idx_group`.`advert_id` = `advert`.`id`
      LEFT JOIN #__wbadvert_group AS `group` ON `group`.`id` = `idx_group`.`group_id`
      LEFT JOIN #__wbadvert_idx_menu AS `idx_menu` ON `idx_menu`.`advert_id` = `advert`.`id`
      LEFT JOIN #__wbadvert_idx_category AS `idx_category` ON `idx_category`.`advert_id` = `advert`.`id`
      LEFT JOIN #__wbadvert_idx_content AS `idx_content` ON `idx_content`.`advert_id` = `advert`.`id`
      WHERE ". implode("\n AND ", $where) ."
      GROUP BY `advert`.`id`
      ORDER BY ".  implode(', ', $qPars['ordering']) ."
      LIMIT ". $qPars['limit'] ."
      ");

    $adverts = $db->loadObjectList();
    if( $db->getErrorNum() ){
      echo '<p><font color=red>'. $db->getErrorMsg() .'</font></p>';
      echo '<pre>'.$db->_sql.'</pre>';
    }

    // Return
    return $adverts;

  }

}

