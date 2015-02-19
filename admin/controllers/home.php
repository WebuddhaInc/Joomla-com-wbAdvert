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
// Home Display
//
// ************************************************************************************************

// ------------------------------------------------------------------------ home_display
function home_display( $option, $task ){
  global $mainframe;

  $wbAdvert_config = wbAdvert_config::getInstance();

  ?>
    <script type="text/javascript">
    <!--
      Joomla.submitbutton = function(btn){
        switch(btn){
          case 'link.ticket':
            window.open('https://billing.holodyn.com/submitticket.php?step=2&deptid=2','wbAdvertTicket');
            break;
          case 'link.forum':
            window.open('http://forum.webuddha.com/','wbAdvertForum');
            break;
        }
        return false;
      }
      <?php if( $task == 'support' ){ ?>
      if( confirm('You are being redirected to the Holodyn Support system.') )
        submitbutton('link.ticket');
      <?php } ?>
    //-->
    </script>
    <style>
      div.wbHome { text-align:left; }
      div.wbHome p { font-size:10pt; }
      div.wbHome ul { margin:5px 0; padding:0 0 0 20px; }
      div.wbHome ul ul { padding:0 0 0 10px; }
      div.wbToolBar div.icon { float:left; margin:5px; width:200px; }
      div.wbToolBar div.icon a { padding:10px; display:block; background:#efefef; border:1px solid #999; }
      div.wbToolBar div.icon img { float:left; margin:0 10px 0 0; border:0px; }
      div.wbToolBar div.icon h2 { font-size:16px; text-decoration:none; }
      div.wbHome div.features { float:right; width:320px; padding:5px 10px; border:1px dashed #ccc; background:#f6f6f6; margin:0 0 0 15px; }
      div.wbHome div.features ul { padding:0 0 0 20px; margin:0; }
      div.wbHome a { text-decoration:none; }
      table.adUnits { width:90%; }
      table.adUnits tr th { border-bottom:1px solid #999; padding:2px 5px; }
      table.adUnits tr td { border-bottom:1px dashed #ccc; padding:2px 5px; }
      table.adUnits tr td:first-child, table.adUnits tr td:first-child + td { text-align:center; }
      table.adUnits tr.custom td { background:#ECC; }
    </style>
    <div class="wbHome">
      <div class="features">
        <h2><?php echo WBADVERT_TITLE.' '.$wbAdvert_config->getAppXmlVal('version') ?></h2>

        <h3>Standard Banner Sizes:<br/>
          <small>Standards: <a href="http://www.google.com/search?q=iab+ad+unit+guidelines">IAB Ad Unit Guidelines</a></small></h3>
        <table class="adUnits">
          <tr><th>Width</th><th>Height</th><th>Common Name</th><th># Ads</th></tr>
          <?php

            // Update
              $adUnits = array(
                '300x250' => 'Medium Rectangle',
                '180x150' => 'Rectangle',
                '728x90'  => 'Leaderboard',
                '160x600' => 'Wide Skyscraper',
                '300x600' => 'Half Page Ad',
                '120x60'  => 'Button #2',
                '88x31'   => 'Micro Bar'
                );

            // Collect Banner Size Count
              $db =& JFactory::getDBO();
              $db->setQuery("
                SELECT CONCAT(`advert`.`width`,'x',`advert`.`height`) AS `dim`
                  , COUNT(`advert`.`id`) AS `total`
                FROM #__wbadvert_advert AS `advert`
                GROUP BY `dim`
                ");
              $adCount = $db->loadRowList(0);
              if( count($adCount) )
                foreach($adCount AS $dim => $res)
                  $adUnits[ $dim ] = array('label' => (array_key_exists($dim,$adUnits) ? $adUnits[$dim] : null), 'total' => $res[1]);

            // Report
              foreach($adUnits AS $dim => $res){
                $wh = explode('x',$dim);
                if(!is_array($res))
                  $res = array('label' => $res, 'total' => 0);
                echo '<tr'.($res['label']?'':' class="custom"').'>'
                   . '<td>'.$wh[0].'</td>'
                   . '<td>'.$wh[1].'</td>'
                   . '<td>'.($res['label']?$res['label']:'Custom Size').'</td>'
                   . '<td>'.($res['total'] ? '<a href="index.php?option=com_wbadvert&task=advert&filter_ad_size='.$wh[0].','.$wh[1].'"> '.$res['total'].' - view</a>' : '-').'</td>'
                   . '</tr>';
              }
          ?>
        </table>

        <h3>Resources:</h3>
        <ul>
          <li><a href="https://billing.holodyn.com/submitticket.php" target="_blank">Submit Support Ticket</a>
          <li><a href="http://software.webuddha.com/" target="_blank">Webuddha Software Repository</a>
        </ul>

        <h3>Assets / Folders:</h3>
        <?php
          $paths = Array(
            $wbAdvert_config->getAdPath(),
            JPATH_ROOT.DS.$wbAdvert_config->get('swf_jsloaderpath','media/com_wbadvert/swfobject/') . 'swfobject.js',
            $wbAdvert_config->getAdPath() . '/wbadvert_wrapper.swf',
          );
          echo '<table border="0" cellpadding="2" cellspacing="0">';
          foreach( $paths AS $path ){
            echo '<tr><td><b>'.substr( $path, strlen(JPATH_ROOT.DS) ).'</b> .. <b>';
            if( is_dir($path) )
              echo (is_writable($path)
                ? '<font color="green">Is Writeable</font>'
                : '<font color="red">Is NOT Writeable</font>');
            else
              echo (file_exists($path)
                ? '<font color="green">Installed</font>'
                : '<font color="red">NOT Installed</font>');
            echo '</b></td></tr>';
          }
          echo '</table>';
        ?>

        <h3>wbAdvert System Highlights:</h3>
        <ul>
          <li>Advanced Rotation Control
          <li>Visitor Hit Tracking
          <li>Link Target Branding
          <li>Menu Relationships
          <li>Category Relationships
          <li>Content Relationships
          <li>JPG, GIF, PNG Support
          <li>Flash SWF Support
          <li>Media Dimensions Management
        </ul>

        <?php
          /*
          <p>For updates, please visit the home of the Webuddha wbAdvert at <a href="http://wbadvert.webuddha.com/" target="_blank">http://wbadvert.webuddha.com/</a>
          */
        ?>
        <br/>
      </div>
      <h1>wbAdvert Advanced Advertisement Management System<br/><small>Version <?php echo $wbAdvert_config->getAppXmlVal('version') ?>, <?php echo $wbAdvert_config->getAppXmlVal('copyright') ?></small></h1>
      <div class="wbToolBar">
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=advert">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_media.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_ADVERT'); ?></h2>
          <div style="clear:both;"></div>
          </a>
        </div>
        <?php /*
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=campaign">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_campaign.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_CAMPAIGN'); ?></h2>
          <div style="clear:both;"></div></a>
        </div>
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=keyword">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_keyword.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_KEYWORD'); ?></h2>
          <div style="clear:both;"></div></a>
        </div>
        */ ?>
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=group">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_category.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_GROUP'); ?></h2>
          <div style="clear:both;"></div></a>
        </div>
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=client">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_client.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_CLIENT'); ?></h2>
          <div style="clear:both;"></div></a>
        </div>
        <div class="icon">
          <a href="index.php?option=com_wbadvert&task=config">
          <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/icon_config.png" />
          <h2><?php echo JText::_('COM_WBADVERT_MENU_CONFIG'); ?></h2>
          <div style="clear:both;"></div></a>
        </div>
      </div>

      <h1 style="clear:left;padding:20px 0 0 0;">wbAdvert System Logic</h1>
      <p>The wbAdvert system hooks the wbAdvert display modules into the wbAdvert groups, which are assigned to the advertisements you create.
        Each advertisement has control parameters that allow you to relate the advertisement with particular Menus, Categories, or
        a list of Article Content items. The logic structure is listed below:</p>
      <ul><li>Module Position
        <ul>
          <li>Advert Groups<br/>
          <i>Multiple Groups are tied to a particular Instance of the wbAdvert Module</i>
          <ul>
            <li>Advertisements
            <i>Multiple Advertisements are tied to a particular Advert Group</i><br/>
            <i>Each Advertisement is optionally configured to display in relation to particular site content.</i>
          </ul>
        </ul>
      </ul>

      <h1 style="clear:left;padding:20px 0 0 0;">About the wbAdvert Component</h1>
      <p>
        The wbAdvert by Webuddha.com is a Joomla! Banner Management Component that provides a dynamic system for managing your advertisements
        using relationships between Menus, Categories, and Content items.
      </p>
      <p>
        The wbAdvert is provided to the community FREE through the GNU/GPL license.
        We have benefitted greatly from the Joomla community, and will be releasing a wealth of codes that have been developed for use with our clients over the years.
        This product will be one of many to come for media, product, collaboration, and catalog management. All will be integrated, all will be
        GPL compliant and ready for your use!
      </p>
      <p>
        Your comments and suggestions are what help us improve, so please take the time to visit our site and contact us with questions.
        If you have questions, comments, or suggestions, please take a moment to visit our forum and let us know your thoughts.
      </p>
      <p>Go Joomla! Open-Source, and Collaboration! <grin></p>
      <p><a href="http://www.webuddha.com/" target="_blank" title="Visit Webuddha in a New Window">
        <img src="<?php echo JURI::root() ?>administrator/components/com_wbadvert/inc/img/webuddha_logo.jpg" border="0" /></a></p>
    </div>
  <?php
}
