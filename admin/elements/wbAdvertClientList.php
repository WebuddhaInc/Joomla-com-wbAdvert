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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

class JElementwbAdvertClientList extends JElement
{
  var $_name = 'wbAdvertClientList';
  function fetchElement($name, $value, &$node, $control_name)
  {
    // Filters
    $filter   = $node->attributes('filter');
    $exclude  = $node->attributes('exclude');
    // Load List
    $db =& JFactory::getDBO();
    $db->setQuery("
      SELECT c.id AS value
        , CONCAT(c.id,': ',c.name) AS text
      FROM #__wbadvert_client AS c
      GROUP BY c.id
      ORDER BY c.name ASC
      ");
    $options = $db->loadObjectList();
    // array_unshift( $options, JHTML::_('select.option', '', JText::_('No Group Filters').'...') );
    return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]', 'class="inputbox" multiple="true" size="4" style="width:120px;"', 'value', 'text', $value, "param$name");
  }
}