<?php
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.application.component.view');
JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easytable'.DS.'tables');

class EasyTableViewEasyTable extends JView
{
	function display ($tp = null)
	{
		$id = (int) JRequest::getVar('id',0);
		$easytable =& JTable::getInstance('EasyTable','Table');
		$easytable->load($id);
		if($easytable->published == 0) {
			JError::raiseError(404,"The table you requested is not published or doesn't exist<br />Record id: $id");
		}
		
		$imageDir = $easytable->defaultimagedir;

		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while getting EasyTable id: $id");
		}
		// Get the meta data for this table
		$query = "SELECT label, fieldalias, type, detail_link FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE easytable_id =".$id." AND list_view = '1' ORDER BY position;";
		$db->setQuery($query);
		
		$easytables_table_meta = $db->loadRowList();
		$etmCount = count($easytables_table_meta);
		

		$fields = array();
		$fields[] = 'id'; //put the id in first for accessing detail view of a table row
		foreach($easytables_table_meta as $aRow)
		{
			$fields[] .= $aRow[1]; // for the fieldalias
		}
		
		$fields = implode('`, `',$fields);
				
		// Create a backlink
		$backlink = JRoute::_('index.php?option=com_easytable');
		
		// Get paginated user table
		$paginatedRecords =& $this->get('data');
		// echo('<BR />Paginated Records Array = '.print_r($paginatedRecords));
		
		// Get pagination object
		$pagination =& $this->get('pagination');
		// echo('<BR />Pagination Array = '.print_r($pagination));
		
		//Get form link
		$paginationLink = JRoute::_('index.php?option=com_easytable&id='.$id.'&view=easytable');
		
		// Search
		// echo '<BR />About to do a get(\'search\')';
		$search = $this->get('search');
		// echo '<BR />Just did a get(\'search\')';
		
		// Get Params
		global $mainframe;

		$params =& $mainframe->getParams(); // Component wide & menu based params
		
		$params->merge( new JParameter( &$easytable->params ) );

		$show_description = $params->get('show_description',0);
		$show_created_date = $params->get('show_created_date',0);
		$show_modified_date = $params->get('show_modified_date',0);
		
		// Assing these items for use in the tmpl
		$this->assign('show_description', $show_description);
		$this->assign('show_created_date', $show_created_date);
		$this->assign('show_modified_date', $show_modified_date);

		$this->assign('tableId', $id);
		$this->assign('imageDir', $imageDir);
		$this->assign('backlink', $backlink);
		$this->assignRef('easytable', $easytable);
		$this->assignRef('easytables_table_meta', $easytables_table_meta);
		$this->assignRef('pagination', $pagination);
		$this->assign('paginationLink', $paginationLink);
		$this->assignRef('paginatedRecords', $paginatedRecords);
		$this->assign('search',$search);
		$this->assign('etmCount', $etmCount);
		parent::display($tpl);
	}
}
?>