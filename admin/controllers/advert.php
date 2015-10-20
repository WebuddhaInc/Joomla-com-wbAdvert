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
// Advertisements
//
// ************************************************************************************************

// ------------------------------------------------------------------------ advert_list
function advert_list( $option, $task ) {
  $app = JFactory::getApplication();

  // Check for Groups
    $db =& JFactory::getDBO();
    $db->setQuery("SELECT COUNT(*) FROM `#__wbadvert_group`");
    if( !$db->loadResult() ){
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=group',
        JText::_('ERR_GROUPREQUIRED'),
        'error'
        );
      return false;
    }

  // Check for Clients
    $db->setQuery("SELECT COUNT(*) FROM `#__wbadvert_client`");
    if( !$db->loadResult() ){
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=client',
        JText::_('ERR_CLIENTREQUIRED'),
        'error'
        );
      return false;
    }

  // Set Limiters
    $limit      = $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg('list_limit') );
    $limitstart = $app->getUserStateFromRequest( "wbadvert{$task}limitstart", 'limitstart', 0 );

  // List Filters
    $filters = array(
      'search'      => $app->getUserStateFromRequest( "wbadvert{$task}fsearch", 'filter_search', '' ),
      'client_id'   => $app->getUserStateFromRequest( "wbadvert{$task}fclientid", 'filter_client_id', 0 ),
      'group_id'    => $app->getUserStateFromRequest( "wbadvert{$task}fgroupid", 'filter_group_id', 0 ),
      'ad_size'     => $app->getUserStateFromRequest( "wbadvert{$task}fadsize", 'filter_ad_size', '' ),
      'showall'     => $app->getUserStateFromRequest( "wbadvert{$task}fshowall", 'filter_showall', 0 ),
      'order'       => $app->getUserStateFromRequest( "wbadvert{$task}order", 'filter_order', 'idx_group.ordering', 'cmd' ),
      'order_Dir'   => $app->getUserStateFromRequest( "wbadvert{$task}order_Dir", 'filter_order_Dir', 'asc', 'word' )
      );
    // print '<pre>' . print_r($filters,true) . '</pre>';

  // Get Total Records
    $db->setQuery("
      SELECT COUNT(DISTINCT a.id)
      FROM `#__wbadvert_advert` AS `a`
      INNER JOIN `#__wbadvert_idx_group` AS `idx_group` ON idx_group.advert_id = a.id
      WHERE a.id > '0'
        ".($filters['search']?" AND a.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%'":'')."
        ".($filters['client_id']?" AND a.client_id = '".(int)$filters['client_id']."'":'')."
        ".($filters['group_id']?" AND idx_group.group_id = '".(int)$filters['group_id']."'":'')."
        ".($filters['ad_size']?" AND (a.width = '".(int)array_shift(explode(',',$filters['ad_size']))."' AND a.height = '".(int)array_pop(explode(',',$filters['ad_size']))."')":'')."
      ");
    $total = $db->loadResult(); echo $db->getErrorMsg();

  // Load PageNavigation
    jimport('joomla.html.pagination');
    $pageNav = new JPagination( $total, $limitstart, $limit );

  // Load Records
    if( in_array($filters['order'],array('g.name')) )
      $ordering = 'g.name '.$filters['order_Dir'].', idx_group.ordering '.$filters['order_Dir'].', a.name '.$filters['order_Dir'].'';
    else if( in_array($filters['order'],array('idx_group.ordering')) )
      $ordering = 'g.name '.$filters['order_Dir'].', idx_group.ordering '.$filters['order_Dir'].', a.name '.$filters['order_Dir'].'';
    else
      $ordering = $filters['order'].' '.$filters['order_Dir'];
    $db->setQuery( "
        SELECT a.*
          , ROUND((100 * a.clicks/a.impmade),3) AS percent_clicks
          , u.name as editor
          , c.name AS client_name
          ".($filters['group_id'] ? ", idx_group.ordering AS ordering" : '')."
          , GROUP_CONCAT(idx_group.group_id) AS idx_groups
        ".(
          $filters['showall']
            ? "
              , 'ok' AS `filters`
              , (SELECT COUNT(idx_menu.advert_id) FROM `#__wbadvert_idx_menu` AS `idx_menu` WHERE (idx_menu.advert_id = a.id AND idx_menu.menu_id != '0')) AS num_menu
              , (SELECT COUNT(idx_category.advert_id) FROM `#__wbadvert_idx_category` AS `idx_category` WHERE (idx_category.advert_id = a.id AND idx_category.category_id != '0')) AS num_category
              , (SELECT COUNT(idx_content.advert_id) FROM `#__wbadvert_idx_content` AS `idx_content` WHERE (idx_content.advert_id = a.id AND idx_content.content_id != '0')) AS num_content
            "
            : "
              , 'na' AS `filters`
            "
            )."
        FROM `#__wbadvert_advert` AS `a`
        LEFT JOIN `#__wbadvert_client` AS `c` ON c.id = a.client_id
        LEFT JOIN `#__users` AS `u` ON u.id = a.checked_out
        LEFT JOIN `#__wbadvert_idx_group` AS `idx_group` ON idx_group.advert_id = a.id
        LEFT JOIN `#__wbadvert_group` AS `g` ON g.id = idx_group.group_id
        WHERE a.id > 0
          ".($filters['search']?" AND a.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%'":'')."
          ".($filters['client_id']?" AND a.client_id = '".(int)$filters['client_id']."'":'')."
          ".($filters['group_id']?" AND idx_group.group_id = '".(int)$filters['group_id']."'":'')."
          ".($filters['ad_size']?" AND (a.width = '".(int)array_shift(explode(',',$filters['ad_size']))."' AND a.height = '".(int)array_pop(explode(',',$filters['ad_size']))."')":'')."
        GROUP BY a.id
        ORDER BY $ordering
        LIMIT ".(int)$pageNav->limitstart.", ".(int)$pageNav->limit."
      " );
    $rows = $db->loadObjectList(); echo $db->getErrorMsg();
    // echo '<pre>'; print_r($rows); die();

  // Load Session
    $session =& JFactory::getSession();

  // Lists
    $lists = array(
      'search'    => $filters['search'],
      'showall'   => $filters['showall'],
      'client_id' => null,
      'group_id'  => null,
      'ad_size'   => null,
      'order'     => $filters['order'],
      'order_Dir' => $filters['order_Dir']
      );

  // List of Clients
    $cList = $session->get('wbadvert_cache_clients');
    if( !is_array($cList) ){
      $db->setQuery("SELECT id, name FROM `#__wbadvert_client` ORDER BY name ASC");
      $cListRows = $db->loadObjectList(); echo $db->getErrorMsg();
      $cList = Array( JHTML::_('select.option', '0', JText::_('LIST_CLIENTFILTER'), 'id', 'name' ) );
      $cList = array_merge($cList,$cListRows);
      $session->set('wbadvert_cache_clients', $cList);
    }
    $lists['client_id'] = JHTML::_('select.genericlist', $cList, 'filter_client_id', 'onChange="submitbutton(\'advert\');"', 'id', 'name', $filters['client_id']);

  // List of Groups
    $gList = $session->get('wbadvert_cache_groups');
    if( !is_array($gList) ){
      $db->setQuery("SELECT id, name FROM `#__wbadvert_group` ORDER BY name ASC");
      $gListRows = $db->loadObjectList(); echo $db->getErrorMsg();
      $gList = Array( JHTML::_('select.option', '0', JText::_('LIST_GROUPFILTER'), 'id', 'name' ) );
      $gList = array_merge($gList,$gListRows);
      $session->set('wbadvert_cache_groups', $gList);
    }
    $lists['group_rows'] = $gList;
    $lists['group_id'] = JHTML::_('select.genericlist', $gList, 'filter_group_id', 'onChange="submitbutton(\'advert\');"', 'id', 'name', $filters['group_id']);

  // List of Ad Sizes
    $sList = $session->get('wbadvert_cache_adsizes');
    if( !is_array($sList) ){
      $db->setQuery("SELECT CONCAT(`width`,',',`height`) AS size FROM `#__wbadvert_advert` as advert GROUP BY size");
      $sListRows = $db->loadObjectList(); echo $db->getErrorMsg(); $sListCount = count($sListRows);
      $sList = Array( JHTML::_('select.option', '', JText::_('LIST_SIZEFILTER'), 'id', 'name' ) );
      for($i=0;$i<$sListCount;$i++) $sList[] = JHTML::_('select.option', $sListRows[$i]->size, implode(' x ',explode(',',$sListRows[$i]->size)), 'id', 'name' );
      $session->set('wbadvert_cache_adsizes', $sList);
    }
    $lists['ad_size'] = JHTML::_('select.genericlist', $sList, 'filter_ad_size', 'onChange="submitbutton(\'advert\');"', 'id', 'name', $filters['ad_size']);

  // Show Filters Option
    $lists['showall'] = JHTML::_('select.genericlist', array(
      JHTML::_('select.option', '0', JText::_('Hide'), 'id', 'name' ),
      JHTML::_('select.option', '1', JText::_('Show'), 'id', 'name' )
      ), 'filter_showall', 'onChange="submitbutton(\'advert\');"', 'id', 'name', $filters['showall']);

  // Load View
    HTML_wbAdvert::advert_list( $rows, $pageNav, $option, $lists, $filters );
}

// ------------------------------------------------------------------------ advert_edit
function advert_edit( $id, $option ) {
  $my = jFactory::getUser();
  $app = JFactory::getApplication();
  $lists = array();

  // Load Record
    $db =& JFactory::getDBO();
    $row = new wbAdvert_advert($db);
    if($id) $row->load( $id );

  // Checkout Processing
    if ($row->checked_out && $row->checked_out <> $my->id)
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=client',
        JText::sprintf('MSD_CHECKEDOUT', JText::_('Advertisement'), $row->name),
        'error'
        );
    if($row->id) $row->checkout( $my->id );

  // Build Client Select List
    $db->setQuery("SELECT id as value, name as text FROM `#__wbadvert_client` ORDER BY name");
    if(!$db->query()){echo $db->stderr();return;};
    $client_list = Array( JHTML::_('select.option', '0', JText::_('LIST_CLIENTSELECT') ) );
    $client_list = array_merge( $client_list, $db->loadObjectList() );
    $lists['client_id'] = JHTML::_('select.genericlist', $client_list, 'client_id', 'class="inputbox" size="1" required="true"','value', 'text', $row->client_id);

  // Query Groups
    $idx_groups = array();
    $db->setQuery("SELECT `group_id` FROM `#__wbadvert_idx_group` WHERE `advert_id` = '$row->id'");
    $tmpRows = $db->loadObjectList(); echo $db->getErrorMsg();
    if( $tmpRows ){
      foreach( $tmpRows AS $tmpResult ){
        $idx_groups[] = $tmpResult->group_id;
      }
    }

  // Build Group Select List
    $db->setQuery("SELECT id AS value, name AS text FROM `#__wbadvert_group` ORDER BY module_id, ordering, name");
    if(!$db->query()){echo $db->stderr();return;};
    $group_list = $db->loadObjectList();
    $lists['idx_group'] = JHTML::_('select.genericlist', $group_list, 'idx_group[]', 'class="inputbox idx_group" multiple="true" required="true"','value', 'text', $idx_groups);

  // Query Categories
    $idx_categories = array();
    $db->setQuery("SELECT `category_id` FROM `#__wbadvert_idx_category` WHERE `advert_id` = '$row->id'");
    $tmpRows = $db->loadObjectList(); echo $db->getErrorMsg();
    if( $tmpRows ){
      foreach( $tmpRows AS $tmpResult ){
        $idx_categories[] = $tmpResult->category_id;
      }
    }
    if(!count($idx_categories)) $idx_categories = array('0');

  // Build Category Select List
    $db->setQuery("
      SELECT c.id, c.title, c.parent_id, c.level
      FROM #__categories AS c
      WHERE `extension` = 'com_content'
      ORDER BY c.lft ASC
      ");
    $categories = $db->loadObjectList(); echo $db->getErrorMsg();
    $category_list = array( JHTML::_('select.option', '0', JText::_('LIST_ALLCATEGORIES')) );
    $lastParentId = 1;
    for($i=0;$i<count($categories);$i++){
      $category =& $categories[$i];
      $category_list[] = JHTML::_('select.option', $category->id, ($category->level-1?str_pad('',($category->level-1)*2,'-').' ':'').$category->title.' .. '.$category->id);
    }
    $lists['idx_category'] = JHTML::_('select.genericlist', $category_list, 'idx_category[]', 'class="inputbox idx_category" multiple="true"','value', 'text', $idx_categories);

  // Query Menus
    $idx_menus = array();
    $db->setQuery("SELECT `menu_id` FROM `#__wbadvert_idx_menu` WHERE `advert_id` = '$row->id'");
    $tmpRows = $db->loadObjectList(); echo $db->getErrorMsg();
    if( $tmpRows ){
      foreach( $tmpRows AS $tmpResult ){
        $idx_menus[] = $tmpResult->menu_id;
      }
    }
    if(!count($idx_menus)) $idx_menus = array('0');

  // Build Menu Select List
    $db->setQuery("SELECT * FROM #__menu WHERE `published` = '1' AND `menutype` != '' ORDER BY `menutype`, `title`");
    $menus = $db->loadObjectList(); echo $db->getErrorMsg();
    $menu_list = array( JHTML::_('select.option', '0', JText::_('LIST_ALLMENUS')) );
    $lastType = null;
    foreach( $menus AS $menu ){
      if( $menu->menutype !== $lastType )
        $menu_list[] = JHTML::_('select.option', '', '------------------------------'); $lastType = $menu->menutype;
      $menu_list[] = JHTML::_('select.option', $menu->id, $menu->menutype.' | '.$menu->title.' .. '.$menu->id);
    }
    $menu_list[] = JHTML::_('select.option', '', '------------------------------');
    $lists['idx_menu'] = JHTML::_('select.genericlist', $menu_list, 'idx_menu[]', 'class="inputbox idx_menu" multiple="true"','value', 'text', $idx_menus);

  // Build Content Select List
    $idx_content = array();
    $db->setQuery("SELECT `content_id` FROM `#__wbadvert_idx_content` WHERE `advert_id` = '$row->id'");
    $tmpRows = $db->loadObjectList(); echo $db->getErrorMsg();
    if( $tmpRows ){
      foreach( $tmpRows AS $tmpResult ){
        $idx_content[] = $tmpResult->content_id;
      }
    }
    $row->idx_content = join(',',$idx_content);

  // Build Target Select List
    $target = Array();
    $target[] = JHTML::_('select.option', '_top',     JText::_('OPT_TARGET_TOP') );
    $target[] = JHTML::_('select.option', '_self',    JText::_('OPT_TARGET_SELF') );
    $target[] = JHTML::_('select.option', '_blank',   JText::_('OPT_TARGET_BLANK') );
    $target[] = JHTML::_('select.option', '_parent',  JText::_('OPT_TARGET_PARENT') );
    $lists['target'] = JHTML::_('select.genericlist', $target, 'target', 'class="inputbox" size="1"' , 'value', 'text', $row->target );

  // Build Published Select List
    $yesno = Array();
    $yesno[] = JHTML::_('select.option', '1', JText::_('Yes') );
    $yesno[] = JHTML::_('select.option', '0', JText::_('No') );
    $lists['published'] = JHTML::_('select.radiolist', $yesno, 'published', 'class="inputbox" size="1"' , 'value', 'text', (int)$row->published );

  // Format Dates
    $nullDate = $db->getNullDate();

  // Start Date
    if (trim( $row->date_start ) == $nullDate || trim( $row->date_start ) == '' || trim( $row->date_start ) == '-' )
      $row->date_start = date('Y-m-d', time() + ( $app->getCfg('offset') * 60 * 60 ));
    else {
      $row->date_start = JHTML::_('date',$row->date_start,'Y-m-d H:i:s');
      if ( $row->date_start != $nullDate )
        $row->date_start = JHTML::_('date',$row->date_start,'Y-m-d');
    }

  // Stop Date
    if (trim( $row->date_stop ) == $nullDate || trim( $row->date_stop ) == '' || trim( $row->date_stop ) == '-' )
      $row->date_stop = 'Never';
    else {
      $row->date_stop = JHTML::_('date',$row->date_stop,'Y-m-d H:i:s');
      if ( $row->date_stop != $nullDate )
        $row->date_stop = JHTML::_('date',$row->date_stop,'Y-m-d');
    }

  // Build Published Select List
    $weekdays = Array();
    $weekdays[] = JHTML::_('select.option', 'sun', JText::_('Sun') );
    $weekdays[] = JHTML::_('select.option', 'mon', JText::_('Mon') );
    $weekdays[] = JHTML::_('select.option', 'tue', JText::_('Tue') );
    $weekdays[] = JHTML::_('select.option', 'wed', JText::_('Wed') );
    $weekdays[] = JHTML::_('select.option', 'thu', JText::_('Thu') );
    $weekdays[] = JHTML::_('select.option', 'fri', JText::_('Fri') );
    $weekdays[] = JHTML::_('select.option', 'sat', JText::_('Sat') );
    // All Days MUST BE NULL Value in the database
    $isAllDays = !$row->id || is_null($row->weekdays);
    $lists['weekdays'] = "\n\t<input type=\"checkbox\" name=\"weekdays[]\" value=\"all\" ".( $isAllDays ? 'checked=checked' : '' )." onclick=\"toggleAllDays(this);\" />"
                       . "\n\t<label><b>Every Day</b></label>";
    // NO Days MUST BE '' Blank Value in the database
    foreach( $weekdays AS $weekday )
      $lists['weekdays'] .= "\n\t<input type=\"checkbox\" name=\"weekdays[]\" value=\"".$weekday->value."\" ".( $isAllDays ? 'disabled=disabled' : '' )." ".( !$isAllDays && strpos($row->weekdays, $weekday->value)!==false ? 'checked=checked' : '' )."/>"
                          . "\n\t<label>".$weekday->text."</label>";

  // Time Start
    $time_scale = Array();
    $time_scale[] = JHTML::_('select.option', null, JText::_('OPT_TIMESTART_NULL') );
    for($h=0;$h<2400;$h+=100)
      for($m=0;$m<60;$m+=15)
        $time_scale[] = JHTML::_('select.option', $h+$m, (str_pad($h/100,2,'0',STR_PAD_LEFT).':'.str_pad($m,2,'0',STR_PAD_LEFT)));
    $lists['time_start'] = JHTML::_('select.genericlist', $time_scale, 'time_start', 'class="inputbox" size="1"' , 'value', 'text', $row->time_start );
    $lists['time_stop']  = JHTML::_('select.genericlist', $time_scale, 'time_stop', 'class="inputbox" size="1"' , 'value', 'text', $row->time_stop );

  // Load View
    HTML_wbAdvert::advert_edit( $row, $lists, $option );
}

// ------------------------------------------------------------------------ advert_save
function advert_save( $id, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();

  // Load Record Object
  $row = new wbAdvert_advert($db);
  if( $id )
    $row->load( $id );
  $row->bind( $_POST );

  // Reset Impressions Counter
  if( $task == 'advert.reset' ){
    $row->clicks  = 0;
    $row->impmade = 0;
  }

  // Code
  if(array_key_exists('code',$_POST) )
    $row->code = stripslashes(trim($_POST['code']));

  // Dates
  $nullDate = $db->getNullDate();
  if (strlen(trim($row->date_start)) < 10)
    $row->date_start = date('Y-m-d', time() + ( $app->getCfg('offset') * 60 * 60 ));
  if (strlen(trim($row->date_stop)) < 10)
    $row->date_stop = $nullDate;

  // Times
  if( !strlen($row->time_start) )
    $row->time_start = null;
  if( !strlen($row->time_stop) )
    $row->time_stop = null;

  // Weekdays
  $weekdays = JRequest::getVar( 'weekdays', array(), 'method', 'array' );
  if( count($weekdays) )
    if( in_array('all',$weekdays) )
      $row->weekdays = null;
    else
      $row->weekdays = implode(',',$weekdays);
  else
    $row->weekdays = '';

  // Upload Image
  if (!$row->upload()) {
    echo "<script> alert('".JText::sprintf('MSG_DBERR',$row->getError())."'); window.history.go(-1); </script>\n";
    exit();
  }
  // Save Record
  if (!$row->store(true)) {
    echo "<script> alert('".JText::sprintf('MSG_DBERR',$row->getError())."'); window.history.go(-1); </script>\n";
    exit();
  }
  // Update Session
  $session =& JFactory::getSession();
  $session->set('wbadvert_cache_adsizes', null);

  // Save Group Index
  $idx_group = wbAdvert_Common::stripFromArray( JRequest::getVar( 'idx_group', array(0), 'method', 'array' ), 0 );
  $wbAdvert_idx_group = new wbAdvert_idx_group();
  $wbAdvert_idx_group->delete( $row->id, 0 );
  if( count($idx_group) )
    $wbAdvert_idx_group->save( $row->id, 0, $idx_group );
  else
    $wbAdvert_idx_group->save( $row->id, 0, 0 );

  // Save Category Index
  $idx_category = wbAdvert_Common::stripFromArray( JRequest::getVar( 'idx_category', array(0), 'method', 'array' ), 0 );
  $wbAdvert_idx_category = new wbAdvert_idx_category();
  $wbAdvert_idx_category->delete( $row->id, 0 );
  if( count($idx_category) )
    $wbAdvert_idx_category->save( $row->id, 0, $idx_category );
  else
    $wbAdvert_idx_category->save( $row->id, 0, 0 );

  // Save Menu Index
  $idx_menu = wbAdvert_Common::stripFromArray( JRequest::getVar( 'idx_menu', array(0), 'method', 'array' ), 0 );
  $wbAdvert_idx_menu = new wbAdvert_idx_menu();
  $wbAdvert_idx_menu->delete( $row->id, 0 );
  if( count($idx_menu) )
    $wbAdvert_idx_menu->save( $row->id, 0, $idx_menu );
  else
    $wbAdvert_idx_menu->save( $row->id, 0, 0 );

  // Save Content Index
  $idx_content = Array();
  $idx_temps = explode( ',', preg_replace('/[\r\n\t]/',',',JRequest::getVar( 'idx_content', '' )) );
  foreach( $idx_temps AS $idx_temp )
    if( (int)$idx_temp > 0 )
      $idx_content[] = (int)$idx_temp;
  $wbAdvert_idx_content = new wbAdvert_idx_content();
  $wbAdvert_idx_content->delete( $row->id, 0 );
  if( count( $idx_content ) )
    $wbAdvert_idx_content->save( $row->id, 0, $idx_content );
  else
    $wbAdvert_idx_content->save( $row->id, 0, 0 );

  // Check Required
  if (!$row->check()) {
    $app->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=advert.edit&hidemainmenu=1&id='.$row->id,
      $row->getError(),
      'error'
      );
    exit();
  } else
    $row->store();

  // Checkin
  $row->checkin();
  foreach( $idx_group AS $group_id )
    $row->reorder( $group_id );

  // Redirect
  $msg = JText::sprintf('MSG_SAVED', JText::_('Advertisement'));
  if( in_array($task, Array('advert.apply', 'advert.reset')) )
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert.edit&hidemainmenu=1&id='.$row->id, $msg );
  else
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert', $msg );
}

// ------------------------------------------------------------------------ advert_save
function advert_cancel( $id, $option ) {
  $app = JFactory::getApplication();
  if( $id ){
    $row = new wbAdvert_advert( JFactory::getDBO() );
    $row->load( $id );
    $row->checkin();
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert', JText::sprintf('MSG_CANCELLED', JText::_('Advertisement')) );
}

// ------------------------------------------------------------------------ advert_delete
function advert_delete( $cid ) {
  $app = JFactory::getApplication();
  $wbAdvert_config = wbAdvert_config::getInstance();
  $db =& JFactory::getDBO();
  if(count( $cid )) {
    foreach($cid AS $id){
      $row = new wbAdvert_advert($db);
      $row->load( $id );
      if( $row->id ){
        if( $row->file_type ){
          // $filePath = JPATH_ROOT.'/'.$wbAdvert_config->get('ad_path').$row->id.'.'.$row->file_type;
          $filePath = $wbAdvert_config->getAdPath().$row->id.'.'.$row->file_type;
          if( file_exists($filePath) && !unlink($filePath) ){
            echo "<script> alert('Failed to Remove ".$filePath."'); window.history.go(-1); </script>\n";
            exit();
          }
        }
        if(!$row->delete()) {
          echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
          exit();
        }
      }
    }
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert', JText::sprintf('MSG_DELETED', JText::_('Advertisement')) );
}

// ------------------------------------------------------------------------ advert_order
function advert_order( $cid, $inc, $option ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  $row = new wbAdvert_advert( $db );
  $idx_row = new wbAdvert_idx_group( $db );
  $group_id = JRequest::getInt('filter_group_id',0);
  $campaign_id = JRequest::getInt('filter_campaign_id',0);
  if( $group_id ){
    $order = JRequest::getVar( 'order', array(), 'method', 'array' );
    if( is_array($cid) ){
      $groups = array();
      if( count($order) && count($order) == count($cid) ){
        for($i=0;$i<count($cid);$i++){
          $idx_row->save( $cid[$i], $campaign_id, $group_id, $order[$i] );
        }
      }
    }
    else {
      $idx_row->loadRow( $cid, $campaign_id, $group_id );
      $idx_row->moveRow( $inc );
    }
    $idx_row->reorderRows( $campaign_id, $group_id );
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert', JText::sprintf('MSG_UPDATED', JText::_('Advertisement')) );
}

// ------------------------------------------------------------------------ advert_publish
function advert_publish( $cid, $publish=1 ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  if( !is_array( $cid ) || !count( $cid ) )
    $app->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=advert',
      JText::sprintf('ERR_NOCID', strtoupper($publish ? 'publish' : 'unpublish')),
      'error'
      );
  $cids = implode( ',', $cid );
  $db->setQuery("UPDATE `#__wbadvert_advert` SET `published`='$publish' WHERE id IN ($cids)");
  if (!$db->query())
    $app->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=advert',
      JText::sprintf('ERR_DBERR', $db->getErrorMsg()),
      'error'
      );
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=advert', JText::sprintf('MSG_UPDATED', JText::_('Advertisement')) );
}

// ************************************************************************************************
//
// Class
//
// ************************************************************************************************

class HTML_wbAdvert {

  // **************************************************************************
  function advert_list( &$rows, &$pageNav, $option, &$lists, &$filters ) {
    $my = jFactory::getUser();
    $db =& JFactory::getDBO();

    $canDo = wbAdvert_Common::userGetPermissions();
    $saveOrder = $filters['group_id'] && $lists['order'] == 'idx_group.ordering';

    JHtml::_('bootstrap.tooltip');
    JHtml::_('behavior.multiselect');
    JHtml::_('formbehavior.chosen', 'select');

    if ($saveOrder){
      $saveOrderingUrl = 'index.php?option=com_wbadvert&task=advert.order&tmpl=component';
      JHtml::_('sortablelist.sortable', 'wbadvertList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
    }

    JHTML::_('behavior.tooltip');
    $ordering = ($lists['order'] == 'g.name' || $lists['order'] == 'idx_group.ordering');
    ?>
    <script type="text/javascript">
      Joomla.orderTable = function(){
        table = document.getElementById("sortTable");
        direction = document.getElementById("directionTable");
        order = table.options[table.selectedIndex].value;
        if (order != '<?php echo $lists['order']; ?>'){
          dirn = 'asc';
        } else {
          dirn = direction.options[direction.selectedIndex].value;
        }
        Joomla.tableOrdering(order, dirn, '');
      }
    </script>
    <script>
      function submitResetFilters(){
        $('filter_search').value='';
        $('filter_ad_size').options[0].selected=true;
        $('filter_client_id').options[0].selected=true;
        $('filter_group_id').options[0].selected=true;
        submitbutton('advert');
      }
    </script>
    <style>
      .adminlist thead tr th { text-align:left; }
      .adminlist thead th:nth-child(1) { width:20px; }
      <?php if($filters['group_id']){ ?>
      .adminlist thead th:nth-child(12) a:first-child { float:left; }
      .adminlist thead th:nth-child(12) a:last-child { float:right; }
      .adminlist tbody td:nth-child(12) { width:80px; }
      .adminlist tbody td:nth-child(10),
      .adminlist tbody td:nth-child(11),
      .adminlist tbody td:nth-child(12),
      .adminlist tbody td:nth-child(13),
      .adminlist tbody td:nth-child(14),
      .adminlist tbody td:nth-child(15) { text-align:center!important; }
      <?php } else { ?>
      .adminlist tbody td:nth-child(10),
      .adminlist tbody td:nth-child(11),
      .adminlist tbody td:nth-child(12),
      .adminlist tbody td:nth-child(13),
      .adminlist tbody td:nth-child(14) { text-align:center!important; }
      <?php } ?>
      .adminlist tbody td { vertical-align:top; }
    </style>
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=advert'); ?>" method="post" name="adminForm" id="adminForm">
      <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
      <table width="100%" class="adminheading">
        <tr>
          <td style="text-align:left;">
            <?php echo JText::_( 'Filter' ); ?>:
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $lists['search'];?>" class="text_area" onchange="submitbutton('advert');" />
            <button onclick="submitbutton('advert');"><?php echo JText::_('Go'); ?></button>
            <button onclick="submitResetFilters();"><?php echo JText::_('Reset'); ?></button>
          </td>
          <td style="text-align:right;">
            <?php echo $lists['ad_size'] ?>
            <?php echo $lists['client_id'] ?>
            <?php echo $lists['group_id'] ?>
          </td>
        </tr>
      </table>
      <table class="adminList table table-striped" id="wbadvertList">
        <thead>
          <tr>
            <?php if($filters['group_id']){ ?>
            <th width="1%" class="nowrap center hidden-phone">
              <?php echo JHTML::_('grid.sort', '<i class="icon-menu-2"></i>', 'idx_group.ordering', @$lists['order_Dir'], @$lists['order'], 'advert', 'asc', 'JGRID_HEADING_ORDERING' ); ?>
            </th>
            <?php } ?>
            <th nowrap><?php echo JText::_('#') ?></th>
            <th width="1%"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" /></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTNAME', 'a.name', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTNAME', 'c.name', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPNAMES', 'g.name', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTTYPE', 'a.file_type', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTSTART', 'a.date_start', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTSTOP', 'a.date_stop', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTIMPRESSIONS', 'a.impmade', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTCLICKS', 'a.clicks', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_ADVERTCLICKRATIO', 'percent_clicks', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <?php /* if($filters['group_id']){ ?>
            <th nowrap><?php echo JHTML::_('grid.sort', 'Order', 'idx_group.ordering', @$lists['order_Dir'], @$lists['order'] ); ?>
              <?php if($ordering) echo JHTML::_('grid.order',  $rows, 'filesave.png', 'advert.order' ); ?></th>
            <?php } */ ?>
            <th nowrap><?php echo JHTML::_('grid.sort', 'Published', 'a.published', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JText::_('TH_ADVERTFILTERS') ?> <?php echo $lists['showall'] ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'ID', 'a.id', @$lists['order_Dir'], @$lists['order'] ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
            if( !count($rows) )
              echo '<tr><td colspan="16"><h1 class="alert_msg">'.JText::_('LIST_NOADVERTS').'</h1></td></tr>';
            $k = 0;
            for ($i=0, $n=count( $rows ); $i < $n; $i++) {
              $row = &$rows[$i];
              $link = 'index.php?option='.$option.'&task=advert.edit&hidemainmenu=1&id='. $row->id;
              $link_group = 'index.php?option='.$option.'&task=group.edit&hidemainmenu=1&id='. $row->group_id;
              $link_client = 'index.php?option='.$option.'&task=client.edit&hidemainmenu=1&id='. $row->client_id;
              $task   = $row->published ? 'advert.unpublish' : 'advert.publish';
              $state  = $row->published ? 'publish' : 'unpublish';
              $alt    = $row->published ? JText::_('Published') : JText::_('Unpublished');
              if( $row->code ){
                $type_name = 'Code';
              } else if( $row->file_type ) {
                $type_name = strtoupper( $row->file_type ).'('.$row->width.'x'.$row->height.')';
              } else {
                $type_name = '-';
              }
              $nullDate = $db->getNullDate();
              $checked  = JHTML::_('grid.checkedout', $row, $i );
              ?>
              <tr class="<?php echo "row$k"; ?>" sortable-group-id="<?php echo (int)$filters['group_id'] ?>">
                <?php if($filters['group_id']){ ?>
                  <td class="order nowrap center hidden-phone">
                    <?php
                      if( $canDo->get('core.edit.state') ){
                        $disableClassName = '';
                        $disabledLabel    = '';
                        if( !$saveOrder ){
                          $disabledLabel    = JText::_('JORDERINGDISABLED');
                          $disableClassName = 'inactive tip-top';
                        }
                        ?>
                        <span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
                          <i class="icon-menu"></i>
                        </span>
                        <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering;?>" class="width-20 text-area-order " />
                        <?php
                      } else {
                        ?>
                        <span class="sortable-handler inactive" >
                          <i class="icon-menu"></i>
                        </span>
                        <?php
                      }
                    ?>
                  </td>
                <?php } ?>
                <td><?php echo $pageNav->getRowOffset( $i ); ?></td>
                <td><?php echo $checked; ?></td>
                <td align="left"><?php
                  if ( $row->checked_out && ( $row->checked_out != $my->id ) )
                    echo $row->name;
                  else
                    echo '<a href="'.$link.'" title="'.JText::sprintf('BTN_EDIT',JText::_('Advertisement')).'">'.$row->name.'</a>';
                  ?></td>
                <td><a href="<?php echo $link_client; ?>" title="<?php echo JText::sprintf('BTN_EDIT',JText::_('Client')); ?>"><?php echo $row->client_name ?></a></td>
                <td><?php
                  $idx_group_links = array();
                  if( strlen($row->idx_groups) ){
                    $idx_groups = explode(',',$row->idx_groups);
                    foreach($lists['group_rows'] AS $group_row){
                      if($group_row->id && in_array( $group_row->id, $idx_groups )){
                        $idx_group_links[] = '<a href="index.php?option='.$option.'&task=group.edit&hidemainmenu=1&id='.$group_row->id.'" title="'.JText::sprintf('BTN_EDIT',JText::_('Group')).'">'.$group_row->name .'</a>';
                      }
                    }
                  }
                  if( count($idx_group_links) ){
                    echo implode(', ',$idx_group_links);
                  }
                  else {
                    echo JText::_('FT_NONE');
                  }
                  ?></td>
                <td><?php echo $type_name ?></td>
                <td><?php echo ($row->date_start==$nullDate?'Never':JHTML::_('date',$row->date_start,JText::_('DATE_FORMAT_LC4'))) ?></td>
                <td><?php echo ($row->date_stop==$nullDate?'Never':JHTML::_('date',$row->date_stop,JText::_('DATE_FORMAT_LC4'))) ?></td>
                <td><?php echo $row->impmade .' of '. ($row->imptotal ? $row->imptotal : 'unlimited') ?></td>
                <td><?php echo $row->clicks ?></td>
                <td><?php echo $row->percent_clicks ?></td>
                <?php /* if($filters['group_id']){ ?>
                <td class="order">
                  <span><?php echo $pageNav->orderUpIcon( $i, ($row->group_id == @$rows[$i-1]->group_id), 'advert.orderup', $ordering ); ?></span>
                  <span><?php echo $pageNav->orderDownIcon( $i, $n, ($row->group_id == @$rows[$i+1]->group_id), 'advert.orderdn', $ordering ); ?></span>
                  <input type="text" name="order[]" size="5" value="<?php echo $row->ordering; ?>" <?php echo $ordering ? '' : 'disabled="disabled'; ?> class="text_area" style="text-align: center" />
                </td>
                <?php } */ ?>
                <td>
                  <a class="btn btn-micro hasTooltip" title="" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')" title="<?php echo $alt; ?>" href="javascript:void(0);">
                    <i class="icon-<?php echo $state ?>"></i>
                  </a>
                </td>
                <td><?php
                  if( $row->filters == 'na' )
                    echo '-';
                  elseif( !$row->num_menu && !$row->num_category && !$row->num_content )
                    echo JText::_('FT_NONE');
                  else {
                    if( $row->num_menu )
                      echo $row->num_menu.' '.JText::_('FT_MENUS').'<br/>';
                    if( $row->num_category )
                      echo $row->num_category.' '.JText::_('FT_CATEGORIES').'<br/>';
                    if( $row->num_content )
                      echo $row->num_content.' '.JText::_('FT_ARTICLES').'<br/>';
                  }
                ?></td>
                <td><?php echo $row->id ?></td>
              </tr>
              <?php
              $k = 1 - $k;
            }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="16"><?php echo $pageNav->getListFooter(); ?></td>
          </tr>
        </tfoot>
      </table>
      <input type="hidden" name="task" value="advert">
      <input type="hidden" name="boxchecked" value="0">
      <?php echo JHtml::_('form.token'); ?>
    </form>

    <?php
  }

  // **************************************************************************
  function advert_edit( &$row, &$lists, $option ) {
    $app = JFactory::getApplication();
    JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'custombannercode' );
    JHTML::_('behavior.tooltip');
    JHTML::_('behavior.calendar');
    $db =& JFactory::getDBO();
    $wbAdvert = new wbAdvert_advert( $db );
    ?>
    <script>
      var dimRatio = '<?php echo ($row->width?($row->height / $row->width):0) ?>';
      function dimCheck( myObj ){
        if( !dimRatio )return;
        var form = document.adminForm;
        if( form.constrain.checked == true ){
          if( myObj.name == 'height' )
            form.width.value = Math.round(myObj.value / dimRatio);
          else
            form.height.value = Math.round(myObj.value * dimRatio);
        }
      }
      function getSelectedValue(formName,fieldName){
        var form = document[formName];
        if( form[fieldName].selectedIndex >= 0 )
          return form[fieldName].options[form[fieldName].selectedIndex].value;
        return 0;
      }
      Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton == 'advert.cancel') {
          submitform( pressbutton );
          return;
        }
        // do field validation
        if (form.name.value == "") {
          alert( "<?php echo JText::_('FLD_VAL_ADVERTNAME'); ?>" );
        }
        else if (getSelectedValue('adminForm','client_id') < 1) {
          alert( "<?php echo JText::_('FLD_VAL_ADVERTCLIENT'); ?>" );
        }
        else if (getSelectedValue('adminForm','idx_group') < 1) {
          alert( "<?php echo JText::_('FLD_VAL_ADVERTGROUP'); ?>" );
        }
        else if (form.id.value == '' && form.code.value == '' && form.advert_file.value == ''){
          alert( "<?php echo JText::_('FLD_VAL_ADVERTADMEDIA'); ?>" );
        }
        else {
          submitform( pressbutton );
        }
      }
      function toggleAllDays(el){
        var isAll = el.checked;
        var fields = document.adminForm['weekdays[]'];
        for(var i=1;i<fields.length;i++){
          fields[i].checked = isAll?false:true;
          fields[i].disabled = isAll?true:false;
        }
      }
    </script>
    <style>
      .adminform select.idx_menu { width:400px; height:190px; }
      .adminform select.idx_category { width:400px; height:190px; }
      .adminform textarea.idx_content { width:400px;height:80px; }
      .adminform textarea.code { width:360px;height:140px; }
    </style>
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=advert.edit'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
      <table class="adminHeading" width="100%">
        <tr><th class="icon-48-advert">
          <?php echo $row->id ? JText::sprintf('HEAD_ADVERTEDIT',$row->name) : JText::_('HEAD_ADVERTNEW');?><br/>
          <font size="-1"><?php echo ($row->id ? JText::_('LBL_ADVERTLINK').': <a href="'.JURI::root().'index.php?option='.$option.'&task=load&id='.$row->id.'" target="_blank">index.php?option='.$option.'&task=load&id='.$row->id.'</a>' : '&nbsp;') ?></font>
        </th></tr>
      </table>
      <div class="col100">
        <table class="adminTable" width="100%">
          <tr><td valign="top" width="50%">
            <fieldset class="adminForm">
              <legend><?php echo JText::_('SET_ADVERTDETAIL') ?></legend>
              <table class="adminTable" width="100%">
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_ADVERTNAME'), JText::_('FLD_ADVERTNAME_TIP') ); ?></td>
                  <td><input class="inputbox" type="text" name="name" size="30" value="<?php echo $row->name ?>" required="true"></td>
                </tr>
                <tr>
                  <td valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_ADVERTGROUPIDX'), JText::_('FLD_ADVERTGROUPIDX_TIP') ); ?></td>
                  <td><?php echo $lists['idx_group']; ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_ADVERTCLIENT'), JText::_('FLD_ADVERTCLIENT_TIP') ); ?></td>
                  <td><?php echo $lists['client_id']; ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_PUBLISHED'), JText::_('FLD_PUBLISHED_TIP') ); ?></td>
                  <td><?php echo $lists['published']; ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_IMPTOTAL'), JText::_('FLD_IMPTOTAL_TIP') ); ?></td>
                  <td><input class="inputbox" type="text" name="imptotal" size="12" maxlength="11" value="<?php echo $row->imptotal ? $row->imptotal : null ?>">
                    <span><?php echo JText::_('FLD_IMPTOTAL_NOTE') ?></span></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_IMPMADE'), JText::_('FLD_IMPMADE_TIP') ); ?></td>
                  <td>
                    <?php
                      echo '<b> '.(int)$row->impmade.' </b>';
                      if( $row->id )
                        echo '&nbsp;&nbsp;&nbsp;<input type="button" onClick="submitbutton(\'advert.reset\');" value="'.JText::_('BTN_RESETCOUNTER').'" />';
                    ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_DATESTART'), JText::_('FLD_DATESTART_TIP') ); ?></td>
                  <td><?php echo JHTML::_('calendar', $row->date_start, 'date_start', 'date_start_cal', '%Y-%m-%d', array('class' => 'inputbox')) ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_DATESTOP'), JText::_('FLD_DATESTOP_TIP') ); ?></td>
                  <td><?php echo JHTML::_('calendar', ($row->date_stop == 'Never' ? null : $row->date_stop), 'date_stop', 'date_stop_cal', '%Y-%m-%d', array('class' => 'inputbox')) ?></td>
                </tr>
              </table>
            </fieldset>
            <fieldset class="adminForm">
              <legend><?php echo JText::_('SET_ADVERTMEDIA') ?></legend>
              <table class="adminTable" width="100%">
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_URL'), JText::_('FLD_URL_TIP') ); ?></td>
                  <td><input class="inputbox" type="text" name="url" size="30" maxlength="200" value="<?php echo $row->url ?>"></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_TARGET'), JText::_('FLD_TARGET_TIP') ); ?></td>
                  <td><?php echo $lists['target'] ?></td>
                </tr>
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CAPTION'), JText::_('FLD_CAPTION_TIP') ); ?></td>
                  <td><input class="inputbox" type="text" name="caption" size="30" maxlength="200" value="<?php echo $row->caption ?>"></td>
                </tr>
                <tr>
                  <td nowrap valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_UPLOADFILE'), JText::_('FLD_UPLOADFILE_TIP') ); ?></td>
                  <td>
                    <input class="inputbox" type="file" name="advert_file" size="30" /><br/>
                    <span><?php echo JText::_('FLD_UPLOADFILE_NOTE'); ?></span>
                  </td>
                </tr>
                <tr>
                  <td nowrap valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_DIMENSIONS'), JText::_('FLD_DIMENSIONS_TIP') ); ?></td>
                  <td>
                    <input class="inputbox" type="text" name="width" size="5" value="<?php echo $row->width ?>" onkeypress="dimCheck(this);" onchange="dimCheck(this);" /> x
                    <input class="inputbox" type="text" name="height" size="5" value="<?php echo $row->height ?>" onkeypress="dimCheck(this);" onchange="dimCheck(this);" /><br/>
                    <input type="checkbox" id="constrain" name="constrain" <?php echo $row->height && $row->width ? 'checked' : '' ?>> <label for="constrain"><?php echo JText::_('CHK_CONSTRAIN') ?></label>
                  </td>
                </tr>
                <tr><th colspan="2"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CODE'), JText::_('FLD_CODE_TIP') ); ?></th></tr>
                <tr><td colspan="2"><textarea class="inputbox code" name="code"><?php echo $row->code ?></textarea></td></tr>
              </table>
            </fieldset>
          </td>
          <td valign="top" width="50%">
            <fieldset class="adminForm">
              <legend><?php echo JText::_('SET_ADVERTFILTERS') ?></legend>
              <table class="adminTable" width="100%">
                <tr>
                  <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_WEEKDAYS'), JText::_('FLD_WEEKDAYS_TIP') ); ?></td>
                  <td><?php echo $lists['weekdays']; ?></td>
                </tr>
                <tr>
                  <td>
                    <?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_TIMESTART'), JText::_('FLD_TIMESTART_TIP') ); ?> /
                    <?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_TIMESTOP'), JText::_('FLD_TIMESTOP_TIP') ); ?>
                    </td>
                  <td>
                    <?php echo $lists['time_start']; ?> /
                    <?php echo $lists['time_stop']; ?>
                    </td>
                </tr>
                <tr>
                  <td valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_IDXMENU'), JText::_('FLD_IDXMENU_TIP') ); ?></td>
                  <td><?php echo $lists['idx_menu']; ?></td>
                </tr>
                <tr>
                  <td valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_IDXCATEGORY'), JText::_('FLD_IDXCATEGORY_TIP') ); ?></td>
                  <td><?php echo $lists['idx_category']; ?></td>
                </tr>
                <tr>
                  <td valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_IDXCONTENT'), JText::_('FLD_IDXCONTENT_TIP') ); ?></td>
                  <td><textarea class="inputbox idx_content" name="idx_content"><?php echo $row->idx_content ?></textarea></td>
                </tr>
              </table>
            </fieldset>
          </td></tr>
        </table>
      </div>
      <input type="hidden" name="option" value="<?php echo $option ?>">
      <input type="hidden" name="id" value="<?php echo $row->id ?>">
      <input type="hidden" name="task" value="advert.apply">
      <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <?php if( $row->id ){ ?>
    <fieldset class="adminForm">
      <legend><?php echo JText::_('SET_ADVERTMEDIA') ?></legend>
      <table class="adminTable report" width="100%">
        <tbody>
          <tr>
            <td><label><?php echo JText::_('SET_ADVERTSAMPLE'); ?> - <?php echo $row->file_type ?></label></td>
            <td><?php echo $wbAdvert->getAdvertCode( $row->id ) ?></td>
          </tr>
          <?php if( !$row->code ){ ?>
          <tr>
            <td><label><?php echo JText::_('SET_ADVERTCODE'); ?></label></td>
            <td><textarea class="inputbox" style="width:100%;height:100px;"><?php echo $wbAdvert->getAdvertCode( $row->id ) ?></textarea></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </fieldset>
    <?php } ?>
    <?php
  }
}
