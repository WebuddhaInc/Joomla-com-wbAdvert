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
// Configuration Class
//
// ************************************************************************************************

// Required Assets
jimport('joomla.html.parameter');
jimport('joomla.application.component.helper');

// ------------------------------------------------------------------------------------------------------ wbAdvert_config
class wbAdvert_config {

  static  $_instance  = null;             // object instance

  private $_ready     = null;             // ready flag
  private $_id        = null;             // extension id
  private $_appxml    = null;             // extension xml
  private $_extension = null;             // extension object
  private $_params    = null;             // parameter object

  function __construct( $txt=null, $path=null ){
    $this->loadExtension();
    if( !$this->_id ){
      $jApp =& JApplication::getInstance();
      $jApp->redirect(
        'index2.php',
        JText::_('ERR_XMLLOADFAIL'),
        'error'
        );
    }
    self::$_instance = $this;
  }

  function getInstance(){
    if( !is_object(self::$_instance) )
      self::$_instance = new self();
    return self::$_instance;
  }

  function loadExtension(){
    $this->_params = &JComponentHelper::getParams('com_wbadvert');
    if( !$this->_id ){
      $db =& JFactory::getDBO();
      $db->setQuery("
        SELECT `ext`.`extension_id` AS `id`
        FROM `#__extensions` AS `ext`
        WHERE `ext`.`element` = '". WBADVERT_NAME ."'
          AND `ext`.`type` = 'component'
        LIMIT 1
        ");
      $this->_id = $db->loadResult(); echo $db->getErrorMsg();
    }
    if( $this->_id ){
      $this->_extension = & JTable::getInstance( 'extension');
      $this->_extension->load( $this->_id );
      if( !strlen($this->_extension->params) ){
        $this->_ready = false;
      } else {
        $this->_ready = true;
      }
      return $this->_ready;
    }
  }

  function storeExtension(){
    $this->_extension->params = $this->_params->toString();
    return $this->_extension->store();
  }

  function ready(){
    return $this->_ready;
  }

  function render(){
    $config = JComponentHelper::getParams(WBADVERT_NAME);
    $form = new JForm( 'jform' );
    $form->loadFile( WBADVERT_PATH.'config.xml', false, '//config' );
    $form->bind( $config->toArray() );
    $fieldSets = $form->getFieldsets();
    $html[] = '<div class="control-fieldset">';
    foreach( $form->getFieldset('component') as $field ){
      $field->name = 'params['. $field->name .']';
      $html[] = $field->renderField();
    }
    $html[] = '</div>';
    return implode(PHP_EOL, $html);
  }

  function set( $name, $val ){
    return $this->_params->set( $name, $val );
  }

  function get( $name, $def=null ){
    return $this->_params->get( $name, $def );
  }

  function getAdPath( $base=true ){
    return preg_replace('/\\\/','/',($base ? JPATH_ROOT.DS : '').$this->get('ad_path','media/com_wbadvert/adverts/'));
  }

  function getAdSite( $base=true ){
    return ($base ? JURI::root() : '').$this->get('ad_path');
  }

  function getFramePath( $base=true ){
    return preg_replace('/\\\/','/',($base ? JPATH_ROOT.DS : '').'administrator/components/com_wbadvert/templates/framed_template.php');
  }

  function getAppXmlVal( $key, $def=null ){
    if( !$this->_appxml )
      $this->_appxml = JApplicationHelper::parseXMLInstallFile(JPATH_COMPONENT_ADMINISTRATOR.DS.'wbadvert.xml');
    if( $this->_appxml && array_key_exists($key, $this->_appxml) )
      return $this->_appxml[$key];
    return $def;
  }

}
