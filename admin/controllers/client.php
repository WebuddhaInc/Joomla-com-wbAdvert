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
// Operations
//
// ************************************************************************************************

// ------------------------------------------------------------------------ client_list
function client_list( $option, $task ) {

  // System
    $app = JFactory::getApplication();

  // Get List Limits
    $limit = $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg('list_limit') );
    $limitstart = $app->getUserStateFromRequest( "wbadvert{$task}limitstart", 'limitstart', 0 );

  // List Filters
    $filters = array(
      'search'      => $app->getUserStateFromRequest( "wbadvert{$task}fsearch", 'filter_search', '' ),
      'order'       => $app->getUserStateFromRequest( "wbadvert{$task}order", 'filter_order', 'c.name', 'cmd' ),
      'order_Dir'   => $app->getUserStateFromRequest( "wbadvert{$task}order_Dir", 'filter_order_Dir', 'asc', 'word' )
      );

  // Get Total Records
    $db =& JFactory::getDBO();
    $db->setQuery( "
      SELECT COUNT(c.id)
      FROM #__wbadvert_client AS `c`
      ".($filters['search']?" WHERE c.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%' ":'')."
      " );
    $total = $db->loadResult(); echo $db->getErrorMsg();

  // Load PageNavigation
    jimport('joomla.html.pagination');
    $pageNav = new JPagination( $total, $limitstart, $limit );

  // Load Records
    $ordering = $filters['order'].' '.$filters['order_Dir'];
    $db->setQuery("
      SELECT c.*
        , COUNT(a.id) AS total_banners
        , SUM(a.impmade) AS total_impmade
        , SUM(a.clicks) AS total_clicks
        , u.name AS editor
      FROM #__wbadvert_client AS `c`
      LEFT JOIN #__wbadvert_advert AS `a` ON a.client_id = c.id
      LEFT JOIN #__users AS `u` ON u.id = c.checked_out
      ".($filters['search']?" WHERE c.name LIKE '%".preg_replace('/\s+/','%',trim($filters['search']))."%' ":'')."
      GROUP BY c.id
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
    HTML_wbAdvert_client::client_list( $rows, $pageNav, $option, $lists );
}

// ------------------------------------------------------------------------ client_edit
function client_edit( $id, $option, $task ) {
  $app = JFactory::getApplication();
  $my = jFactory::getUser();

  // Load Record
    $db =& JFactory::getDBO();
    $row = new wbAdvert_client($db);
    $row->load($id);

  // Client Report
    $db->setQuery("
      SELECT COUNT(a.id) AS total_banners
        , SUM(a.impmade) AS total_impmade
        , SUM(a.clicks) AS total_clicks
      FROM #__wbadvert_client AS `c`
      LEFT JOIN #__wbadvert_advert AS `a` ON a.client_id = c.id
      WHERE c.id = '$row->id'
      ");
    $res = $db->loadObjectList(); echo $db->getErrorMsg();
    $row->_report = array_shift($res);

  // Checkout Processing
    if ($row->checked_out && $row->checked_out <> $my->id)
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=client',
        JText::sprintf('MSD_CHECKEDOUT', JText::_('Client'), $row->name),
        'error'
        );
    if($row->id) $row->checkout( $my->id );

  // Build Published Select List
    $lists = array();
    $yesno = Array();
    $yesno[] = JHTML::_('select.option', '1', JText::_('Yes') );
    $yesno[] = JHTML::_('select.option', '0', JText::_('No') );
    $lists['published'] = JHTML::_('select.radiolist', $yesno, 'published', 'class="inputbox" size="1"' , 'value', 'text', $row->published );

  // Load View
    HTML_wbAdvert_client::client_edit( $row, $lists, $option );
}

// ------------------------------------------------------------------------ client_save
function client_save( $id, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  $row = new wbAdvert_client( $db );
  if (!$row->bind( $_POST )) {
    echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  if (!$row->check() || !$row->store())
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client.edit&id='.$row->id, $row->getError(), 'error' );
  // Update Session
  $session =& JFactory::getSession();
  $session->set('wbadvert_cache_clients', null);
  // Checkin
  $row->checkin();
  // Redirect
  $msg = JText::sprintf('MSG_SAVED', JText::_('Client'));
  if( in_array($task, Array('client.apply')) )
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client.edit&hidemainmenu=1&id='.$row->id, $msg );
  else
    $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client', $msg );
}

// ------------------------------------------------------------------------ client_cancel
function client_cancel( $id, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  $row = new wbAdvert_client( $db );
  if ( $row->load( $id ) )
    $row->checkin();
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client', JText::sprintf('MSG_CANCELLED', JText::_('Client')) );
}

// ------------------------------------------------------------------------ client_delete
function client_delete( $cid, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  for ($i = 0; $i < count($cid); $i++) {
    $db->setQuery("SELECT COUNT(id) FROM #__wbadvert_advert WHERE client_id='".$cid[$i]."'");
    if(($count = $db->loadResult()) == null) {
      echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
      exit();
    }
    if ($count != 0) {
      $app->redirect(
        'index.php?option='.WBADVERT_NAME.'&task=client',
        'You Cannot Delete a Client with Active Banners',
        'error'
        );
    } else {
      $db->setQuery("DELETE FROM #__wbadvert_client WHERE `id`='".$cid[$i]."'");
      $db->query();
    }
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client', JText::sprintf('MSG_DELETED', JText::_('Client')) );
}

// ------------------------------------------------------------------------ client_publish
function client_publish( $cid, $publish=1, $option, $task ) {
  $app = JFactory::getApplication();
  $db =& JFactory::getDBO();
  if (!is_array( $cid ) || count( $cid ) < 1) {
    $action = $publish ? 'publish' : 'unpublish';
    echo "<script> alert('Select an item to $action'); window.history.go(-1);</script>\n";
    exit;
  }
  $cids = implode( ',', $cid );
  $db->setQuery( "UPDATE #__wbadvert_client SET published='$publish' WHERE id IN ($cids)");
  if (!$db->query()) {
    echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
    exit();
  }
  $app->redirect( 'index.php?option='.WBADVERT_NAME.'&task=client', JText::sprintf('MSG_UPDATED', JText::_('Client')) );
}

// ************************************************************************************************
//
// Class
//
// ************************************************************************************************

class HTML_wbAdvert_client {

  // **************************************************************************
  function client_list( &$rows, &$pageNav, $option, &$lists ) {
    $my = jFactory::getUser();
    JHTML::_('behavior.tooltip');
    ?>
    <style><!--
      .adminlist thead tr th { text-align:left; }
      .adminlist thead th:nth-child(6),
      .adminlist thead th:nth-child(7),
      .adminlist thead th:nth-child(8),
      .adminlist thead th:last-child { text-align:center!important; }
      .adminlist tbody td:nth-child(6),
      .adminlist tbody td:nth-child(7),
      .adminlist tbody td:nth-child(8),
      .adminlist tbody td:last-child { text-align:center!important; }
    //--></style>
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=client'); ?>" method="post" name="adminForm" id="adminForm">
      <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
      <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />
      <table class="adminList table table-striped" id="wbadvertList">
        <thead>
          <tr>
            <th width="20">#</th>
            <th width="20"><input type="checkbox" name="toggle" value="" onClick="checkAll(<?php echo count( $rows ); ?>);" /></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTNAME', 'c.name', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTCONTACT', 'c.contact', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTEMAIL', 'c.email', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTADVERTS', 'total_banners', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTIMPRESSIONS', 'total_impmade', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'TH_CLIENTCLICKS', 'total_clicks', @$lists['order_Dir'], @$lists['order'] ); ?></th>
            <th nowrap><?php echo JHTML::_('grid.sort', 'Published', 'a.published', @$lists['order_Dir'], @$lists['order'] ); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
            if( !count($rows) )
              echo '<tr><td colspan="9"><h1 class="alert_msg">'.JText::_('LIST_NOCLIENTS').'</h1></td></tr>';
            $k = 0;
            for ($i=0, $n=count( $rows ); $i < $n; $i++) {
              $row = &$rows[$i];
              $link     = 'index.php?option=com_wbadvert&task=client.edit&hidemainmenu=1&id='. $row->id;
              $task     = $row->published ? 'client.unpublish' : 'client.publish';
              $state    = $row->published ? 'publish' : 'unpublish';
              $img      = $row->published ? 'publish_g.png' : 'publish_x.png';
              $alt      = $row->published ? JText::_('Published') : JText::_('Unpublished');
              $checked  = JHTML::_('grid.checkedout', $row, $i );
              ?>
              <tr class="<?php echo "row$k"; ?>">
                <td width="1%"><?php echo $pageNav->getRowOffset( $i ); ?></td>
                <td width="1%"><?php echo $checked; ?></td>
                <td>
                <?php
                  if ( $row->checked_out && ( $row->checked_out != $my->id ) )
                    echo $row->name;
                  else
                    echo '<a href="'.$link.'" title="'.JText::sprintf('BTN_EDIT',JText::_('Client')).'">'.$row->name.'</a>';
                ?>
                </td>
                <td><?php echo $row->contact ?></td>
                <td><a href="mailto:<?php echo $row->email ?>" title="<?php echo JText::sprintf('BTN_EMAIL',JText::_('Client')) ?>"><?php echo $row->email ?></a></td>
                <td><?php echo $row->total_banners ?></td>
                <td><?php echo $row->total_impmade ?></td>
                <td><?php echo $row->total_clicks ?></td>
                <td>
                  <a class="btn btn-micro hasTooltip" title="" onclick="return listItemTask('cb<?php echo $i;?>','<?php echo $task;?>')" title="<?php echo $alt; ?>" href="javascript:void(0);">
                    <i class="icon-<?php echo $state ?>"></i>
                  </a>
                </td>
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
      <input type="hidden" name="task" value="client">
      <input type="hidden" name="boxchecked" value="0">
      <?php echo JHtml::_('form.token'); ?>
    </form>
    <?php
  }

  // **************************************************************************
  function client_edit( &$row, &$lists, $option ) {
    JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES, 'extrainfo' );
    JHTML::_('behavior.tooltip');
    ?>
    <script language="javascript">
    <!--
    function submitbutton(pressbutton) {
      var form = document.adminForm;
      if (pressbutton == 'client.cancel') {
        submitform( pressbutton );
        return;
      }
      // do field validation
      if (form.name.value == "") {
        alert( "<?php echo JText::_('FLD_VAL_CLIENTNAME'); ?>" );
      } else {
        submitform( pressbutton );
      }
    }
    //-->
    </script>
    <form action="<?php echo JRoute::_('index.php?option=com_wbadvert&task=client.edit'); ?>" method="post" name="adminForm" id="adminForm">
      <table class="adminHeading" width="100%">
        <tr><th class="icon-48-client">
          <?php echo $row->id ? JText::sprintf('HEAD_CLIENTEDIT',$row->name) : JText::_('HEAD_CLIENTNEW');?><br/>
          <font size="-1"><?php
            if(is_object($row->_report)){
              echo '<a href="index.php?option='.$option.'&task=advert&filter_client_id='.$row->id.'">'.JText::_('BTN_VIEWADVERTLIST').'</a> ';
              foreach($row->_report AS $k => $v)
                echo ' | <span>'.JText::_('FLD_'.strtoupper($k)).': '.(int)$v.'</span>';
            }
            ?></font>
        </th></tr>
      </table>
      <div class="col100">
        <fieldset class="adminform">
          <legend><?php echo JText::_( 'Details' ); ?></legend>
          <table class="admintable">
            <tbody>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CLIENTNAME'), JText::_('FLD_CLIENTNAME_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="name" size="30" maxlength="60" valign="top" value="<?php echo $row->name; ?>"></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_PUBLISHED'), JText::_('FLD_PUBLISHED_TIP') ); ?></td>
                <td>
                  <fieldset id="jform_type" class="radio inputbox">
                  <?php echo $lists['published']; ?>
                  </fieldset>
                </td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CLIENTCONTACT'), JText::_('FLD_CLIENTCONTACT_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="contact" size="30" maxlength="60" value="<?php echo $row->contact; ?>"></td>
              </tr>
              <tr>
                <td><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CLIENTEMAIL'), JText::_('FLD_CLIENTEMAIL_TIP') ); ?></td>
                <td><input class="inputbox" type="text" name="email" size="30" maxlength="60" value="<?php echo $row->email; ?>"></td>
              </tr>
              <tr>
                <td valign="top"><?php echo wbAdvert_Common::getFormLabel( JText::_('FLD_CLIENTEXTRA'), JText::_('FLD_CLIENTEXTRA_TIP') ); ?></td>
                <td><textarea class="inputbox" name="extrainfo" cols="60" rows="10"><?php echo str_replace('&','&amp;',$row->extrainfo);?></textarea></td>
              </tr>
            </tbody>
          </table>
        </fieldset>
      </div>
      <div class="clr"></div>
      <input type="hidden" name="option" value="<?php echo $option; ?>">
      <input type="hidden" name="id" value="<?php echo $row->id; ?>">
      <input type="hidden" name="task" value="client.apply">
      <?php echo JHTML::_( 'form.token' ); ?>
    </form>
    <?php
  }
}
