<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        2.5.0
  Release Date:   05/01/07
  Last Modified:  2013-03-28
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
// Group Manager
//
// ************************************************************************************************

// ------------------------------------------------------------------------ group_list
function group_list( $option, $task ){

  // System
    $app = JFactory::getApplication();

  // Get List Limits
    $limit = $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg('list_limit') );
    $limitstart = $app->getUserStateFromRequest( "wbadvert{$task}limitstart", 'limitstart', 0 );

  // List Filters
    $filters = array(
      'search'      => $app->getUserStateFromRequest( "wbadvert{$task}fsearch", 'filter_search', '' ),
      'order'       => $app->getUserStateFromRequest( "wbadvert{$task}order", 'filter_order', 'g.name', 'cmd' ),
      'order_Dir'   => $app->getUserStateFromRequest( "wbadvert{$task}order_Dir", 'filter_order_Dir', 'asc', 'word' )
      );

  // Get Total Records
    $db =& JFactory::getDBO();
    $db->setQuery( "
      SELECT COUNT(g.id)
      FROM #__wbadvert_group AS `g`
      LEFT JOIN #__wbadvert_idx_group AS `idx_group` ON idx_group.group_id = g.id
      LEFT JOIN #__wbadvert_advert AS `a` ON a.id = idx_group.advert_id
      LEFT JOIN #__modules AS `m` ON m.id = g.module_id
      LEFT JOIN #__users AS `u` ON u.id = g.checked_out
      ".($filters['search']?" WHERE g.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%' ":'')."
      " );
    $total = $db->loadResult(); echo $db->getErrorMsg();

  // Load PageNavigation
    jimport('joomla.html.pagination');
    $pageNav = new JPagination( $total, $limitstart, $limit );

  // Load Records
    if( in_array($filters['order'],array('m.title')) )
      $ordering = 'm.title '.$filters['order_Dir'].', g.ordering, g.name';
    else if( in_array($filters['order'],array('g.ordering')) )
      $ordering = 'm.title '.$filters['order_Dir'].', g.ordering '.$filters['order_Dir'].', g.name '.$filters['order_Dir'].'';
    else
      $ordering = $filters['order'].' '.$filters['order_Dir'];
    $db->setQuery("
      SELECT g.*
        , COUNT(a.id) AS num_adverts
        , m.title AS module_title
        , m.position AS module_position
        , m.published AS module_published
        , u.name AS editor
      FROM #__wbadvert_group AS g
      LEFT JOIN #__wbadvert_idx_group AS `idx_group` ON idx_group.group_id = g.id
      LEFT JOIN #__wbadvert_advert AS `a` ON a.id = idx_group.advert_id
      LEFT JOIN #__modules AS `m` ON m.id = g.module_id
      LEFT JOIN #__users AS `u` ON u.id = g.checked_out
      ".($filters['search']?" WHERE g.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%' ":'')."
      GROUP BY g.id
      ORDER BY $ordering
      LIMIT ".(int)$pageNav->limitstart.", ".(int)$pageNav->limit."
      ");
    $rows = $db->loadObjectList(); echo $db->getErrorMsg();

  // Lists
    $lists = array(
      'search'    => $filters['search'],
      'order'     => $filters['order'],
      'order_Dir' => $filters['order_Dir']
      );

  // Load View
    HTML_wbAdvert_group::group_list( $rows, $pageNav, $option, $lists );
}

// ------------------------------------------------------------------------ group_edit
function group_edit( $id, $option, $task ){

  $my = jFactory::getUser();
  $lists = array();

  // Load Group Object
    $db =& JFactory::getDBO();
    $row = new wbAdvert_group( $db );
    $row->load( $id );

  // Client Report
    $db->setQuery("
      SELECT COUNT(a.id) AS total_banners
        , SUM(a.impmade) AS total_impmade
        , SUM(a.clicks) AS total_clicks
      FROM #__wbadvert_group AS `g`
      LEFT JOIN #__wbadvert_idx_group AS `idx_group` ON idx_group.group_id = g.id
      LEFT JOIN #__wbadvert_advert AS `a` ON a.id = idx_group.advert_id
      WHERE g.id = '$row->id'
      ");
    $res = $db->loadObjectList(); echo $db->getErrorMsg();
    $row->_report = array_shift($res);

  // Build Module List
    $db->setQuery("
      SELECT id AS value,
        CONCAT(position,' [',ordering,'] ',title) AS text
      FROM #__modules
      WHERE `module`
      LIKE 'mod_wbadvert%'
      ");
    $mod_list = array_merge(
      array(JHTML::_('select.option', '0', 'No Module...' )),
      $db->loadObjectList()
      );
    $lists['module_id'] = JHTML::_('select.genericlist', $mod_list, 'module_id', 'class="inputbox" size="1"','value', 'text', $row->module_id);

  // Build Order List
    $order_list = Array();
    $order_list[] = JHTML::_('select.option', 'random', 'Random' );
    $order_list[] = JHTML::_('select.option', 'ordering', 'Ordering' );
    $order_list[] = JHTML::_('select.option', 'name', 'Name' );
    $lists['order'] = JHTML::_('select.genericlist', $order_list, 'order', 'class="inputbox" size="1"','value', 'text', $row->order);

  // Checkout
    if($id) $row->checkout( $my->id );
      else $row->published = 0;

  // Load View
    HTML_wbAdvert_group::group_edit( $row, $lists, $option );
}

// ------------------------------------------------------------------------ group_save
function group_save( $id, $option, $task ){

  // System
  $my = jFactory::getUser();
  $app = JFactory::getApplication();

  $db =& JFactory::getDBO();
  $row = new wbAdvert_group($db);
  if (!$row->bind( $_POST ) || !$row->check() || !$row->store())
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group.edit&id='.$row->id, $row->getError(), 'error' );
  // Update Session
  $session =& JFactory::getSession();
  $session->set('wbadvert_cache_groups', null);
  // Checkin
  $row->checkin();
  $row->reorder('module_id = '.$row->module_id);
  // Redirect
  $msg = JText::sprintf('MSG_SAVED', JText::_('Group'));
  if( in_array($task, Array('group.apply')) )
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group.edit&hidemainmenu=1&id='.$row->id, $msg );
  else
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group', $msg );
}

// ------------------------------------------------------------------------ group_cancel
function group_cancel( $id, $option, $task ){
  $my = jFactory::getUser();
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  $row = new wbAdvert_group($db);
  if ( $row->load( $id ) )
    $row->checkin();
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group', JText::sprintf('MSG_CANCELLED', JText::_('Group')) );
}

// ------------------------------------------------------------------------ group_delete
function group_delete( $cid, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  for ($i = 0; $i < count($cid); $i++) {
    $db->setQuery("
      SELECT COUNT(`advert_id`)
      FROM `#__wbadvert_idx_group`
      WHERE `group_id` = '".$cid[$i]."'
      ");
    if(($count = $db->loadResult()) == null){
      echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
      exit();
    }
    if ($count != 0) {
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=group',
        'You Cannot Delete a Group with Active Banners',
        'error'
        );
      exit();
    } else {
      $db->setQuery("DELETE FROM #__wbadvert_group WHERE `id`='".$cid[$i]."'");
      $db->query();
    }
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group', JText::sprintf('MSG_DELETED', JText::_('Group')) );
}

// ------------------------------------------------------------------------ group_order
function group_order( $cid, $inc, $option ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  $row = new wbAdvert_group( $db );
  if( is_array($cid) ){
    $modules = array();
    $order = JRequest::getVar( 'order', array(), 'method', 'array' );
    if( count($order) && count($order) == count($cid) ){
      for($i=0;$i<count($cid);$i++){
        $row->load( $cid[$i] );
        $row->ordering = $order[$i];
        $row->store();
        $modules[$row->module_id]=0;
      }
      foreach($modules AS $key => $val){
        $row->reorder("module_id = ".(int)$key);
      }
    }
  } else {
    $row->load( (int)$cid );
    $row->move( $inc, "module_id = " . (int)$row->module_id );
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group', JText::sprintf('MSG_UPDATED', JText::_('Group')) );
}

// ------------------------------------------------------------------------ group_publish
function group_publish( $cid, $publish=1 ) {
  $my = jFactory::getUser();
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  if( !is_array( $cid ) || !count( $cid ) )
    $app->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=group',
      JText::sprintf('ERR_NOCID', strtoupper($publish ? 'publish' : 'unpublish')),
      'error'
      );
  $cids = implode( ',', $cid );
  $db->setQuery("UPDATE #__wbadvert_group SET `published`='$publish' WHERE id IN ($cids)");
  if (!$db->query())
    $app->redirect(
      'index.php?option='.WBADVERT_NAME.'&task=group',
      JText::sprintf('ERR_DBERR', $db->getErrorMsg()),
      'error'
      );
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=group', JText::sprintf('MSG_UPDATED', JText::_('Group')) );
}

// ************************************************************************************************
//
// Class
//
// ************************************************************************************************

class HTML_wbAdvert_group {

  function group_list( &$rows, &$pageNav, $option, &$lists ) {
    $my = jFactory::getUser();

    $canDo = wbAdvert_Common::userGetPermissions();
    $saveOrder = $lists['order'] == 'g.ordering';

    // JHTML::_('behavior.tooltip');
    JHtml::_('bootstrap.tooltip');
    JHtml::_('behavior.multiselect');
    JHtml::_('formbehavior.chosen', 'select');

    if ($saveOrder){
      $saveOrderingUrl = 'index.php?option=com_wbadvert&task=group.order&tmpl=component';
      JHtml::_('sortablelist.sortable', 'wbadvertList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
    }

    $ordering = ($lists['order'] == 'm.title' || $lists['order'] == 'g.ordering');
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
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=group'); ?>" method="post" name="adminForm" id="adminForm">
      <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
      <table class="adminList table table-striped" id="wbadvertList">
        <thead>
          <tr>
            <th width="1%" class="nowrap center hidden-phone">
              <?php echo JHTML::_('grid.sort', '<i class="icon-menu-2"></i>', 'g.ordering', @$lists['order_Dir'], @$lists['order'], 'group', 'asc', 'JGRID_HEADING_ORDERING' ); ?>
            </th>
            <th width="20">#</th>
            <th width="20"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" /></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPNAME', 'g.name', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPMODNAME', 'm.title', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPMODPOS', 'm.position', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPMODPUB', 'm.published', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPCOUNT', 'g.count', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPADVERTS', 'num_adverts', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_GROUPORDER', 'g.order', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'Published', 'g.published', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'ID', 'g.id', $lists['order_Dir'], $lists['order'], 'group' ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          if( !count($rows) )
            echo '<tr><td colspan="20"><h1 class="alert_msg">'.JText::_('LIST_NOGROUPS').'</h1></td></tr>';

          $k = 0;
          for ($i=0, $n=count( $rows ); $i < $n; $i++) {
            $row = &$rows[$i];
            $row->id  = $row->id;
            $link     = 'index.php?option=com_wbadvert&task=group.edit&hidemainmenu=1&id='. $row->id;
            $task     = $row->published ? 'group.unpublish' : 'group.publish';
            $state    = $row->published ? 'publish' : 'unpublish';
            $img      = $row->published ? 'publish_g.png' : 'publish_x.png';
            $alt      = $row->published ? 'Published' : 'Unpublished';
            $checked  = JHTML::_('grid.checkedout', $row, $i );
            $modLink  = 'index.php?option=com_modules&client=0&task=edit&cid[]='.$row->module_id;
            ?>
            <tr class="<?php echo "row$k"; ?>" sortable-group-id="<?php echo (int)$row->module_id ?>">
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
              <td><?php echo $pageNav->getRowOffset( $i ); ?></td>
              <td><?php echo $checked; ?></td>
              <td><a href="<?php echo $link ?>"><?php echo $row->name ?></a></td>
              <?php if( !$row->module_id ){ ?>
              <td style="background:#FCC;text-align:center;">Module Undefined</td>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
              <?php } else { ?>
              <td><a href="<?php echo $modLink ?>" target="_blank"><?php echo $row->module_title ? $row->module_title.' #'.$row->module_id : '-' ?></a></td>
              <td><?php echo $row->module_position ? $row->module_position : '-' ?></td>
              <td <?php echo $row->module_published?'':'style="background:#FFCCCC;"' ?>><?php echo $row->module_published?'Yes':($row->module_id?'No':'Undefined') ?></td>
              <?php } ?>
              <td><?php echo $row->count ?></td>
              <td><?php echo $row->num_adverts ?></td>
              <td><?php echo ucwords($row->order) ?></td>
              <td>
                <a class="btn btn-micro hasTooltip" title="" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')" title="<?php echo $alt; ?>" href="javascript:void(0);">
                  <i class="icon-<?php echo $state ?>"></i>
                </a>
              </td>
              <td><?php echo $row->id ?></td>
            </tr>
            <?php
            $k = 1 - $k;
          }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="13"><?php echo $pageNav->getListFooter(); ?></td>
          </tr>
        </tfoot>
      </table>
      <input type="hidden" name="task" value="group">
      <input type="hidden" name="boxchecked" value="0">
      <?php echo JHtml::_('form.token'); ?>
    </form>
    <?php
  }

  function group_edit( &$row, &$lists, $option ){
    JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES );
    JHTML::_('behavior.tooltip');
    ?>
    <script language="javascript">
    Joomla.submitbutton = function(pressbutton) {
      var form = document.adminForm;
      if (pressbutton == 'group.cancel') {
        submitform( pressbutton );
        return;
      }
      // do field validation
      if (form.name.value == "") {
        alert( "You must provide a banner name." );
      } else {
        submitform( pressbutton );
      }
    }
    </script>
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=group.edit'); ?>" method="post" name="adminForm" id="adminForm">
      <table class="adminHeading" width="100%">
        <tr><th class="icon-48-group">
          <?php echo $row->id ? JText::sprintf('HEAD_GROUPEDIT',$row->name) : JText::_('HEAD_GROUPNEW');?><br/>
          <font size="-1"><?php
            if(is_object($row->_report)){
              echo '<a href="index.php?option='.$option.'&task=advert&filter_group_id='.$row->id.'">'.JText::_('BTN_VIEWADVERTLIST').'</a> ';
              foreach($row->_report AS $k => $v)
                echo ' | <span>'.JText::_('FLD_'.strtoupper($k)).': '.(int)$v.'</span>';
            }
            ?></font>
        </th></tr>
      </table>
      <div class="col100">
        <fieldset class="adminForm">
          <legend><?php echo JText::_( 'Details' ); ?></legend>
          <table class="adminTable" width="100%">
            <tbody>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_GROUPNAME'), JText::_('FLD_GROUPNAME_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="name" value="<?php echo $row->name ?>"></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_DESCRIPTION'), JText::_('FLD_DESCRIPTION_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="description" value="<?php echo $row->description ?>"></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_MODULEPOS'), JText::_('FLD_MODULEPOS_TIP') ); ?></td>
                <td><?php echo $lists['module_id'] ?></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_SHOWCOUNT'), JText::_('FLD_SHOWCOUNT_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="count" value="<?php echo $row->count ?>"></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_DISPLAYORD'), JText::_('FLD_DISPLAYORD_TIP') ); ?></td>
                <td><?php echo $lists['order'] ?></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
      <div class="clr"></div>
      <input type="hidden" name="option" value="<?php echo $option; ?>">
      <input type="hidden" name="task" value="group.apply">
      <input type="hidden" name="id" value="<?php echo $row->id; ?>">
      <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <?php
  }
}
