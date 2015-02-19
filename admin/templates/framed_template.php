<?php

/*
  - wbAdvert Component for Joomla! -------------------------------------------------------

  Version:        3.0.0
  Release Date:   05/01/07
  Last Modified:  2013-03-28
  Developer:      David Hunt
  Copyright:      2006-2011 Webuddha.com, The Holodyn Corporation
  License:        GNU/GPL (http://www.gnu.org/copyleft/gpl.html)
  Source:         http://software.webuddha.com/

  - Description --------------------------------------------------------------------------

  This is the template used with the "Load Framed" option to your left. With the Load
  framed option, the link will trigger this page to execute and the iframe within this page
  will point to the advertisements destination url.

  - wbAdvert Fields ----------------------------------------------------------------------

  $wbAdvert->name = the name of the advertisement
  $wbAdvert->caption = the link caption
  $wbAdvert->imptotal = total impressions allowed
  $wbAdvert->impmade = total impressions made
  $wbAdvert->target = target window for links
  $wbAdvert->clicks = total clicks recorded
  $wbAdvert->file_type = type of advertisement file
  $wbAdvert->width = width of ad
  $wbAdvert->height = height of ad
  $wbAdvert->url = destination url
  $wbAdvert->code = custom code
  $wbAdvert->date_start = run start date
  $wbAdvert->date_stop = run stop date
  $wbAdvert->created = date created
  $wbAdvert->modified = date last modified
  $wbAdvert->published = published status
  $wbAdvert->ordering = ordering value

  ----------------------------------------------------------------------------------------
*/

// Block Direct Access
defined( '_JEXEC' ) or die('Access Denied');

// ************************************************************************************************
//
// Frame Template
//
// ************************************************************************************************

$app = JFactory::getApplication();

?>
<html>
  <head>
    <title><?php echo $app->getCfg('sitename') ?></title>
    <style>
      td.bar {
        border-bottom:2px solid #999;
        padding:10px;
        background:#333;
      }
      td.bar a,
      td.bar a:visited {
        color: #FFF;
        text-decoration: none;
        font-weight: bold;
        font-size: 12px;
        font-family: tahoma;
      }
      td.bar a:hover {
        color: #efefef;
      }
      td.bar a.return {
        font-size:16px;
      }
    </style>
  </head>
  <body style="margin:0px;">
    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="bar"><a href="<?php echo JRoute::_('index.php') ?>" title="Return to <?php echo $app->getCfg('sitename') ?>" class="return">
          Return to <?php echo $app->getCfg('sitename') ?></a></td>
        <td class="bar" style="text-align:right;"><a href="<?php echo $wbAdvert->url ?>" target="_top" title="Close the Header">Close Header</a>
          <br/><a href="#" onClick="window.close();" title="Close">Close Window</a></td>
      </tr>
      <tr height="100%"><td colspan="2"><iframe style="border:0px;width:100%;height:100%;" src="<?php echo $wbAdvert->url ?>"></iframe></td></tr>
    </table>
  </body>
</html>