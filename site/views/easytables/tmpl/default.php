<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');
echo '<div class="contentpaneopen'.$this->pageclass_sfx.'" id="et_list_page">';

if($this->show_page_title) {
    echo '<div class="componentheading">'.$this->page_title.'</div>';
    }
?>
<ul class="et_tables_list">
<?php
	foreach ($this->rows as $row )
	{
		$tableParams = new JParameter( $row->params );
		/* Check the user against table access */
		// Create a user $access object for the current $user
		$user = JFactory::getUser();
		$access = new stdClass();
		// Check to see if the user has access to view the table
		$aid	= $user->get('aid');

		if ($tableParams->get('access') > $aid)
		{
			$lockImage = ' <img class="etTableListLockElement" src="/administrator/images/checked_out.png" title="'.JText::_('COM_EASYTABLEPRO_SITE_RESTRICTED_TABLE').'" alt="'.JText::_('COM_EASYTABLEPRO_SITE_CLICK_TO_LOGIN').'" />';
		}
		else
		{
			$lockImage ='';
		}
		$link = JRoute::_('index.php?option=com_easytablepro&amp;view=easytable&amp;id='.$row->id.':'.$row->easytablealias);
		echo '<li class="et_list_table_'.$row->easytablealias.'"><a href="'.$link.'">'.$row->easytablename.$lockImage.'</a>';
		if($this->show_description)
		{
			echo '<br /><div class="et_description '.$row->easytablealias.'">'.$row->description.'</div>';
		}
		echo '</li>';
   }
?>
</ul>
</div>
