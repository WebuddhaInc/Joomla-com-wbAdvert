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
// Config Core
//
// ************************************************************************************************

// ------------------------------------------------------------------------ config_edit
function config_edit( $option ) {
  $wbAdvert_config = wbAdvert_config::getInstance();
  $config = null;
  if( $fh = fopen( $wbAdvert_config->getFramePath(), 'r' ) ){
    while( !feof( $fh ) )
      $config->framed_template .= fread( $fh, 1024 );
    fclose( $fh );
  }
  HTML_wbAdvert_config::config_edit( $wbAdvert_config, $config, $option );
}

// ------------------------------------------------------------------------ config_save
function config_save( $option ) {
  $wbAdvert_config = wbAdvert_config::getInstance();

  $errors = array();
  $params = JRequest::getVar( 'params', Array(), 'method', 'array' );
  if(count($params)) {
    $txt = array();

    // Build Advertisement Path
    $ad_path = preg_replace('/\\\/','/',trim($params['ad_path']));
    if( !preg_match('/\/$/',$ad_path) ) $ad_path .= '/';
    if( preg_match('/^\//',$ad_path) ) $ad_path = substr($ad_path,1);
    $params['ad_path'] = $ad_path;
    $ad_path = preg_replace('/\\\/','/',JPATH_ROOT.DS.$params['ad_path']);

    // Check Advertisement Path
    if( !file_exists($ad_path) )
      $errors[] = JText::_('ERR_ADPATHINVALID');
    if( !is_writable($ad_path) )
      $errors[] = JText::_('ERR_ADPATHNOWRITE');

    // Check Wrapper Exists
    if( $params['wrap_swf'] == 1 )
      if( !file_exists($ad_path.'wbadvert_wrapper.swf') ){
        $params['wrap_swf'] = 0;
        $errors[] = JText::sprintf('ERR_FILENOTFOUND', $ad_path.'wbadvert_wrapper.swf');
      }

    // Build SWFObject Library Path
    $swf_jsloaderpath = preg_replace('/\\\/','/',trim($params['swf_jsloaderpath']));
    if( !preg_match('/\/$/',$swf_jsloaderpath) ) $swf_jsloaderpath .= '/';
    if( preg_match('/^\//',$swf_jsloaderpath) ) $swf_jsloaderpath = substr($swf_jsloaderpath,1);
    $params['swf_jsloaderpath'] = $swf_jsloaderpath;
    $swf_jsloaderpath = preg_replace('/\\\/','/',JPATH_ROOT.DS.$params['swf_jsloaderpath']);

    // Check SWFObject Exists
    if( $params['swf_jsloader'] == 1 )
      if( !file_exists($swf_jsloaderpath.'swfobject.js') ){
        $params['swf_jsloader'] = 0;
        $errors[] = JText::sprintf('ERR_FILENOTFOUND', $swf_jsloaderpath.'swfobject.js');
      }

    // Collect Values
    foreach($params AS $key => $val)
      $wbAdvert_config->set( $key, $val );

  }
  if (!$wbAdvert_config->storeExtension()) {
    echo "<script type=\"text/javascript\"> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
    exit();
  }
  $framed_template  = JRequest::getVar('framed_template', '', 'post', 'string', JREQUEST_ALLOWRAW);
  if( $framed_template !== null ){
    if( $fh = fopen( $wbAdvert_config->getFramePath(), 'w' ) ){
      fwrite( $fh, $framed_template );
      fclose( $fh );
    } else
      $errors[] = JText::sprintf('ERR_FILEWRITEFAIL', $wbAdvert_config->getFramePath(false));
  }
  // Create Advertistment Folder
  $ad_path = $wbAdvert_config->getAdPath();
  if( !is_dir($ad_path) )
    if( !wbAdvert_Common::create_dir($ad_path) )
      $errors[] = JText::_('ERR_CREATEDIRFAIL', $ad_path);
  // Redirect
  $app = JFactory::getApplication();
  $app->redirect(
    'index.php?option='.WBADVERT_NAME.'&task=config',
    ( count($errors) ? implode('<br>', $errors) : 'Settings successfully Saved' ),
    ( count($errors) ? 'error' : 'message' )
    );
}

// ************************************************************************************************
//
// Class
//
// ************************************************************************************************

class HTML_wbAdvert_config {

  function config_edit( &$params, &$config, $option ) {

    $wbAdvert_config = wbAdvert_config::getInstance();
    $app = JFactory::getApplication();
    $db =& JFactory::getDBO();
    JHTML::_('behavior.tooltip');

    ?>
    <form action="index.php" method="post" name="adminForm">
      <?php
        if( !$wbAdvert_config->ready() )
          $app->enqueueMessage( 'You Must Save the Configuration before Continuing to use wbAdvert', 'error' );
      ?>
      <style>
        table.adminlist tr.inactive td {
          background:#FFCCCC;
        }
      </style>
      <table width="100%">
        <tr height="300">
          <td width="30%" valign="top">
            <table width="100%" class="adminform">
              <tr><th><?php echo WBADVERT_TITLE.' '.JText::_('LBL_CONFIG'); ?></th></tr>
              <tr><td><?php echo $params->render() ?></td></tr>
              <tr><th><?php echo WBADVERT_TITLE.' '.JText::_('LBL_MODINSTALLED'); ?></th></tr>
              <tr><td>
                <table width="100%" cellpadding="2" cellspacing="0" class="adminlist">
                  <tr>
                    <th><?php echo JText::_('TH_MODNME') ?></th>
                    <th><?php echo JText::_('TH_MODPOS') ?></th>
                    <th><?php echo JText::_('TH_MODORD') ?></th>
                    <th><?php echo JText::_('TH_MODPUB') ?></th>
                  </tr>
                <?php
                  $db->setQuery("
                    SELECT *
                    FROM #__modules
                    WHERE `module` LIKE 'mod_wbadvert%'
                    ORDER BY position, ordering
                    ");
                  $modList = $db->loadObjectList();
                  if( !count( $modList ) ){
                    $errMsg = JText::_('ERR_MODREQUIRED');
                    $app->enqueueMessage( $errMsg, 'error' );
                    echo '<tr bgcolor="#FF0000"><td colspan="4"><h1 class="alert_msg" style="text-align:center;">' . $errMsg . '</h1></td></tr>';
                  } else {
                    foreach($modList AS $mod){
                      $link = 'index.php?option=com_modules&view=module&layout=edit&id='.$mod->id;
                      echo '<tr '.($mod->published?'':'class="inactive"').'>';
                      echo '<td><a href="'.$link.'" target="_blank">'.$mod->title.'</a></td>';
                      echo '<td>'.$mod->position.'</td>';
                      echo '<td>'.$mod->ordering.'</td>';
                      echo '<td>'.($mod->published?'Yes':'No').'</td>';
                      echo '</tr>';
                    }
                  }
                ?>
                </table>
              </td></tr>
            </table>
          </td>
          <td valign="top" height="100%">
            <table width="100%" height="100%" class="adminform">
              <tr height="1%">
                <th><?php
                  echo JText::_('LBL_FRAMEDTMPL');
                  echo ': <i>' . $wbAdvert_config->getFramePath(false) . '</i>';
                  ?></th>
              </tr>
              <tr><td valign="top" colspan="2">
                <textarea style="width: 100%;height:500px" name="framed_template"><?php echo $config->framed_template ?></textarea>
              </td></tr>
            </table>
          </td>
        </tr>
      </table>
      <input type="hidden" name="option" value="<?php echo $option ?>" />
      <input type="hidden" name="task" value="" />
    </form>
    <?php
  }

}
