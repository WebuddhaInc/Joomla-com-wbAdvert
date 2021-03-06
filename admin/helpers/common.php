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
// Common Functions
//
// ************************************************************************************************


class wbAdvert_Common {

  // ------------------------------------------------------------------------ initSwfObject
  static private $_initSWFObject_loaded = false;
  static function initSWFObject(){
    if( !self::$_initSWFObject_loaded ){
      $document =& JFactory::getDocument();
      $document->addScript(WBADVERT_SITE_LOCAL . wbAdvert_config::getInstance()->get('swf_jsloaderpath','media/com_wbadvert/swfobject/') . 'swfobject.js');
    }
  }

  // ------------------------------------------------------------------------ getFormLabel
  function getFormLabel( $label, $helpText=null ){
    if( $helpText )
      return '<label><span class="hasTip" title="'.preg_replace('/^(.*?)\<.*$/','$1',$label).'::'.$helpText.'">'.$label.'</span>'.(preg_match('/\<\w/',$label)?'':':').'</label>';
    else
      return '<label><span>'.$label.'</span>'.(preg_match('/\<\w/',$label)?'':':').'</label>';
  }

  // ------------------------------------------------------------------------ stripFromArray
  function stripFromArray( &$set, $val ){
    if( !is_array($set) )
      return;
    for($i=0;$i<count($set);$i++)
      if( $set[$i] == $val )
        array_splice( $set, $i--, 1 );
    return $set;
  }

  // ------------------------------------------------------------------------ sortCategoryTree
  function sortCategoryTree(&$cats,$id=0,$level=0){
    $retree = Array();
    for($i=0;$i<count($cats);$i++){
      $cat =& $cats[$i];
      if($id == $cat->parent_id){
        $cat->_level = $level;
        $retree[] = $cat;
        $retree = array_merge($retree,self::sortCategoryTree($cats,($cat->id),($level+1)));
      }
    }
    return $retree;
  }

  // ------------------------------------------------------------------------ create_dir
  function create_dir( $directory ){
    $directory = preg_replace('/\\\/','/',$directory);
    $fullPath  = preg_replace('/\\\/','/',JPATH_ROOT);
    $dirs = explode('/',$directory);
    foreach($dirs AS $dir){
      $fullPath .= '/'.$dir;
      if(!is_dir($fullPath)){
        if(!mkdir($fullPath, 0755))
          return false;
        if(!is_writable($fullPath))
          mosChmod($fullPath, 0755);
      }
    }
    return true;
  }

  // ------------------------------------------------------------------------ isWritable
  function isWritable($path) {
    // http://us.php.net/is_writable
    // will work in despite of Windows ACLs bug
    // NOTE: use a trailing slash for folders!!!
    // see http://bugs.php.net/bug.php?id=27609
    // see http://bugs.php.net/bug.php?id=30931
    if($path{strlen($path)-1}=='/') // recursively return a temporary file path
      return self::isWritable($path.uniqid(mt_rand()).'.tmp');
    else if(is_dir($path))
      return self::isWritable($path.'/'.uniqid(mt_rand()).'.tmp');
    // check tmp file for read/write capabilities
    $rm = file_exists($path);
    $f = @fopen($path, 'a');
    if($f===false)
      return false;
    fclose($f);
    if(!$rm)
      unlink($path);
    return true;
  }

  // ------------------------------------------------------------------------ userGetPermissions
  function userGetPermissions( $asset='' ){
    $user = JFactory::getUser();
    $perm = new JObject;
    $actions = JAccess::getActions('com_wbadvert', 'component');
    foreach ($actions as $action)
      $perm->set($action->name, $user->authorise($action->name, 'com_wbadvert'.(strlen($asset)?'.'.$asset:'')));
    return $perm;
  }

}