<?php
/**
 * @version $Id$
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('=;)');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
jimport('joomla.application.component.controller');
class EasyTableController extends JController
{
	function add()
	{
		JRequest::setVar('view', 'EasyTable');
		$this->display();
	}
	
	/*
	 * The save/apply function has to deal with serveral states, including new a TABLE,
	 * incomplete states (ie. no data table), new csv data files and updated records
	 * from csv files.
	 * The key steps are:
	 * 1. Determine the task
	 *    1.1 Save/Apply steps are done for all tasks
	 *    1.2 createETDTable
	 *    1.3 updateETDTable
	 * 
	*/
	function save()
	{
		JRequest::checkToken() or jexit ( 'Invalid Token' );

		$currentTask = $this->getTask();
		$msg .= '<BR />Current Task = '.$currentTask;
		
		// 1.1 Save/Apply tasks
		$msg = '';
		global $option;

		if($id = $this->saveApplyETdata())
		{
			$msg .= '<BR />Changes applied.';
		}

		// 2 Get a reference to a file if it exists
		$file = JRequest::getVar('tablefile', null, 'files', 'array');

		// 3 Are we creating a new ETTD?
		if($currentTask == 'createETDTable')
		{
			$msg .= '<BR />New data table will be created.';
			$ettd = FALSE;
		}
		else
		{
			// better check one exists...
			$ettd = $this->ettdExists($id);
		}

		// 4 If ETTD exists then update meta & load any new data if required
		if($ettd)
		{	// 1. lets update the meta data
			$this->updateMeta();

			// 2. Check for an update action
			if ($currentTask == 'updateETDTable')
			{
				$msg .= '<BR />Processing '.$currentTask;
				if($file)
				{
				$msg .= '<BR />Data file attached.';
				// 2.1 if a file is attached remove existing data
					if($this->emptyETTD($id))
					{
					$msg .= '<BR />Emptyied existing data rows';
					// Then we parse it and upload the data into the ettd
						$ettdColumnAliass = $this->getMetaFromPost();
						if($ettdColumnAliass)
						{
							if(!($csvRowCount = $this->updateETTDTable($id, $ettdColumnAliass, $file)))
							{
								JError::raiseError(500,"Update of data table failed (Column count mismatch) for table: $id");
							}
						}
						else
						{
							JError::raiseError(500,"Couldn't get the fieldaliass for table: $id");
						}
					}
					else
					{
						$msg .= "<BR />Could not delete any data records from: $id";
					}
				}
				else
				{
				// 2.2 if no file is attached we can go on our merry way.
					$msg .= '<BR />Couldn\'t update the data records as no file was uploaded.';
				}
			}
		}
		// 4.4 Otherwise CREATE the new ETTD for this table if a file was supplied
		elseif($currentTask == 'createETDTable')
		{
			if( isset( $file['name']) && $file['name'] != '' )
			{
				// 1. First we try and get the file
				jimport('joomla.filesystem.file');
				// 1.1 break it's name up into it's components
				$filename = JFile::makeSafe($file['name']);
				$file_basename = substr($filename, 0, strripos($filename, '.')); // file name no extension
				$file_ext      = substr($filename, strripos($filename, '.'));    // here's the extension
				if(!($file_ext == '.csv'))
				{
					$msg .= '<BR />Only CSV files are supported!';
				}
				else
				{
					$ettdColumnAliass =& $this->createMeta($id, $file);  // creates the ETTD and if that works adds the meta records
					if($ettdColumnAliass)
					{
						$csvRowCount = $this->updateETTDTable($id, $ettdColumnAliass, $file);
					}
					else
					{ JError::raiseError(500,"Unable to create ETTD or add Meta records for table: $id"); }
				}
			}
			else
			{
				$msg .= '<BR />No CSV file uploaded - noting to do... ';
			}
		}

		 if ($currentTask == 'apply')
		 {
			$this->setRedirect('index.php?option='.$option.'&task=edit&cid[]='.$id, '<p style="margin-left:35px">'.$msg.'</p>' );
		 }
		 elseif ($currentTask == 'save')
		 {
			 // Now that all the saving is done we can checkIN the table
			 $this->checkInEasyTable();
			 $this->setRedirect('index.php?option='.$option, '<p style="margin-left:35px">'.$msg.'</p>' );
		 }
		 elseif (($currentTask == 'createETDTable') ||($currentTask == 'updateETDTable'))
		 {
		 	$this->setRedirect('index.php?option='.$option.'&task=edit&cid[]='.$id, '<p style="margin-left:35px">'.$msg.'</p>' );
		 }
	}

	function saveApplyETdata()
	{
		// 1.1 Save/Apply tasks - stores the ET record
		$msg = '';
		global $option;

		// 1. Get the TABLE record and check() it.
		$row =& JTable::getInstance('EasyTable', 'Table');
		if (!$row->bind(JRequest::get('post')))
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> '.$row->getError());
		}
		
		if (!$row->check())
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> Table Check() failed... call for help!');
			return;
		}
		
		// 2. Update modified and if necessary created datetime stamps
		if(!$row->id)
		{
			$thisIsANewTable = TRUE;
			$row->created_ = date( 'Y-m-d H:i:s' );
		}
		
		$row->modified_ = date( 'Y-m-d H:i:s' );
		
		$user =& JFactory::getUser();
		if (!$user)
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> '.$user->getError());
		}
		$row->modifiedby_ = $user->id;
		
		// 3. Store the TABLE record
		if (!$row->store())
		{
			JError::raiseError(500, 'Error in saveApplyETdata() -> '.$row->getError());
		}
		
		return $row->id;
	}

	function ettdExists($id)
	{
				 
		// Check for the existence of a matching data table
 		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object checking the existence of data table: $id");
		}

		// Check for ETTD
		return(in_array($db->getPrefix().'easytables_table_data_'.$id, $db->getTableList()));
	}
	
	function uniqueInArray($ettdColumnAliass, $columnAlias, $maxLen= 64)
	{
		// Recursive function to make an URL safe string that isn't in the supplied array.
		// Limited to 64 by default to fit MySQL column limits.
		$columnAlias .= count($ettdColumnAliass);
		if(in_array($columnAlias, $ettdColumnAliass))
		{
			if(strlen($columnAlias) < $maxLen) 
			{
				return $this->uniqueInArray($ettdColumnAliass, $columnAlias);
			}
			return FALSE;
		}
		if(strlen($columnAlias)>$maxLen)
		{
			return FALSE;
		}
		return $columnAlias;
	}
	
	function updateMeta()
	{
		// Now we have to store the meta data
		// 1. Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while setting up for META update: $id");
		}

		// 2. Get the list of mRIds into an array we can use
		$mRIds = split(', ',JRequest::getVar('mRIds'));

		// 3. Get the matching records from the meta table
		// create the sql of the meta record ids
		$etMetaRIdsAsSQL = implode(' OR id =', $mRIds);
		// Get the meta data for this table
		$query = "SELECT * FROM ".$db->nameQuote('#__easytables_table_meta')." WHERE id =".$etMetaRIdsAsSQL." ORDER BY id;";

		$db->setQuery($query);
		
		$easytables_table_meta = $db->loadRowList();
		$ettm_field_count = count($easytables_table_meta);
		$mRIdsCount = count($mRIds);
		if($ettm_field_count != $mRIdsCount) {
			JError::raiseError(500, "META mismatch between form response and data store: $ettm_field_count vs $mRIdsCount <br /> $etMetaRIdAsSQL");
		}

		// Start building the SQL to perform the update
		$etMetaUpdateSQLStart   = 'UPDATE #__easytables_table_meta SET ';
		foreach ($mRIds as $rowValue) {
			// Build the update SQL for each field
			$etMetaUpdateValuesSQL  = '';

			$etMetaUpdateValuesSQL .= '`position` = \''   .JRequest::getVar('position'   .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`label` = \''      .JRequest::getVar('label'      .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`description` = \''.JRequest::getVar('description'.$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`type` = \''       .JRequest::getVar('type'       .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`list_view` = \''  .JRequest::getVar('list_view'  .$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`detail_link` = \''.JRequest::getVar('detail_link'.$rowValue).'\', ';
			$etMetaUpdateValuesSQL .= '`detail_view` = \''.JRequest::getVar('detail_view'.$rowValue).'\' ';
			
			// Build the SQL that selects the record for the right ID
			$etMetaUpdateSQLEnd     = ' WHERE ID =\''.$rowValue.'\'';
			
			// Concatenate all the SQL together
			$etMetaUpdateSQL        = $etMetaUpdateSQLStart.$etMetaUpdateValuesSQL.$etMetaUpdateSQLEnd;

			// Set and run the query
			$db->setQuery($etMetaUpdateSQL);
			$db_result = $db->query();
			
			if(!$db_result) { JERROR::raiseError(500, "Meta data update failed:".$db->explain().'<br /> SQL => '.$etMetaUpdateSQL);}
			
		}
	}
	
	function edit()
	{
		 $this->checkOutEasyTable();
		 // echo 'About to checkoutEasyTable <br />';
		 
		 JRequest::setVar('view', 'EasyTable');
		 $this->display();
	}
	
	function publish()
	{
		// We only publish if the Table is valid, ie. if it has an associated data table
		JRequest::checkToken() or jexit('Invalid Token');
		
		global $option;
		$cid = JRequest::getVar('cid',array());
		$row =& JTable::getInstance('EasyTable','Table');
		
		$msg = '';
		$msg_failures = '';
		$msg_successes = '';
		
		if($this->getTask() =='unpublish')
		{
			//$msg .= 'Task = unpublish; ';
			$publish = 0;
		}
		else
		{
			//$msg .= 'Task = publish; ';
			$publish = 1;
		}
		

		if($publish)
		{
			$f_array = array();  // array to keep id's of failed to publish records
			$s_array = array();  // similar array for successfully published records

			foreach ($cid as $id)
			{
				if($this->ettdExists($id))
				{ $s_array[] = $id;}
				else
				{ $f_array[] = $id;}
			}
			
			// Check for tables we can successfully publish & generate part of the user msg.
			$s = count($s_array);
			if($s)
			{ 
				if($s > 1) {$s = '\'s';} else {$s = '';}
				$msg_successes = 'Table id'.$s.' '.implode(', ',$s_array).' published.';
			}
			// Check for tables we can't publish & generate part of the user msg.
			$f = count($f_array);
			if($f)
			{ 
				if($f > 1) {$f = '\'s';} else {$f = '';}
				$msg_failures = 'Table id'.$f.' '.implode(', ',$f_array).' can\'t be published (no data table). ';
			}
			
			$msg = $msg_failures.$msg_successes;
		}
		else
		{
			$s_array = $cid;
			$msg = 'Table(s) '.implode(', ',$s_array).' unpublished';
		}

		
		if(count($s_array))
		{
			if(!$row->publish($s_array, $publish))
			{
				JError::raiseError(500, $row->getError() );
				
			}
		}

		$this->setRedirect('index.php?option='.$option, $msg);
	}
	
	function checkOutEasyTable()
	{
		 // Check out
		 // Get User ID
		 $user =& JFactory::getUser();
		 
		 $row =& JTable::getInstance('EasyTable', 'Table');
		 $cid = JRequest::getVar( 'cid', array(0), '', 'array');
		 $id = $cid[0];

		 $row->checkout($user->id,$id);
	}
	
	function checkInEasyTable()
	{
		 // Check back in
		 $id = JRequest::getInt('id',0);
		 $row =& JTable::getInstance('EasyTable','Table');
		 //echo 'About to checkin row id:'.$id;
		 $row->checkin($id);
	}
	
	function remove()
	{
		JRequest::checkToken() or jexit('Invalid Token');
		
		global $option;
		$cid = JRequest::getVar('cid',array(0));
		$row =& JTable::getInstance('EasyTable','Table');
		
		foreach ($cid as $id)
		{
			$id = (int) $id;
			$msg = '';
			if(!$this->removeMeta($id))
			{
				JError::raiseError(500, 'Could not remove Meta data for table: '.$id);
			}
			$msg .= '<BR />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(1) Meta data removed. id= '.$id;
			if($this->ettdExists($id))
			{
				if(!$this->removeETTD($id))
				{
					JError::raiseError(500, 'Could not remove ETTD data table: '.$id);
				}
				$msg .= '<BR />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2) ETTD data table removed. id= '.$id;
			}
			else
			{
				$msg .= '<BR />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(2) No ETTD data table found for id ='.$id;
			}
			
			if (!$row->delete($id))
			{
				JError::raiseError(500, $row->getError());
			}
			$msg .= '<BR />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(3) ET Table record removed. id= '.$id;
		}
		$s = '';
		
		$this->setRedirect('index.php?option='.$option, 'Success!'.$msg);
	}

	function getMetaFromPost ()
	{
		// Now we have to store the fieldalias from the post data

		// 1. Get the list of mRIds into an array we can use
		$mRIds = JRequest::getVar('mRIds',0);
		$mRIds = split(', ',$mRIds);

		// 2. Sort the array to ensure it's in the same order as created
		// $msg .= '<BR />Unsorted $mRIds ( '.implode(', ',$mRIds).' )';
		if(!sort($mRIds))
		{
			JError::raiseError(500, 'Failed to sort $mRIds ('.implode(', ',$mRIds).') from table:'.JRequest::getVar('id'));
		}

		// 3. Get fieldalias values and stick them in an array
		$fieldaliass = array();
		
		foreach($mRIds as $rId)
		{
			$fieldaliass[] = JRequest::getVar('fieldalias'.$rId);
		}
		
		// $msg .= '<BR />Sorted $mRIds ( '.implode(', ',$mRIds).' )';
		// $msg .= '<BR />$fieldaliass => '.implode(', ', $fieldaliass);
		
		// JError::raiseError(1000,'Debug of getMetaFromPost: '.$msg);
		
		if(count($fieldaliass))
		{
			return $fieldaliass;
		}
		else
		{
			return FALSE;
		}
	}
	
	function createMeta ($id, $file)
	{
	// Get some basic details about the $file
		$filename = JFile::makeSafe($file['name']);
		$file_basename = substr($filename, 0, strripos($filename, '.')); // file name no extension
		$file_ext      = substr($filename, strripos($filename, '.'));    // here's the extension

	// We Parse the csv file into an array of URL safe Column names 
		$csvData = trim(JFile::read($file['tmp_name']));
		$csvLineArray = explode("\n",$csvData);
		$msg .= '<br />Found '.count($csvLineArray).' rows. | ';
		$csvColumnLabels = explode(',',$csvLineArray[0]);
		$csvColumnCount = count($csvColumnLabels);
		$msg .= 'Found '.$csvColumnCount.' columns in first row. | ';
		$msg .= 'File name => '.$filename.'('.$file_basename.'/'.$file_ext.')'.' | ';
		$msg .= '<br />Meta labels will be: '.implode(', ', $csvColumnLabels).' | ';
		
		$hasHeaders = JRequest::getVar('CSVFileHasHeaders');
		$ettdColumnAliass = array();

		if($hasHeaders)
		{
			foreach($csvColumnLabels as $label)
			{
				$columnAlias = substr( JFilterOutput::stringURLSafe(trim($label)), 0, 64);
				if(in_array($columnAlias, $ettdColumnAliass))
				{
					$columnAlias = $this->uniqueInArray($ettdColumnAliass, $columnAlias);
					if(!$columnAlias)
					{
						JError::raiseError(500,'Duplicate column names in CSV file could not be made unique');
					}
				}
				$ettdColumnAliass[] = $columnAlias;
			}
		}
		else
		{
			$csvColumnLabels = array();
			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				$csvColumnLabels[] = 'Column #'.$colnum;
				$ettdColumnAliass[] = JFilterOutput::stringURLSafe('column'.$colnum);
			}
		}
		reset($ettdColumnAliass);
		echo '<BR /> createMeta() -> ettdColumnAliass = [ '.implode($ettdColumnAliass).' ]';
		
		if($this->createETTD($id, $ettdColumnAliass)) // safe to populate the meta table as we've successfully created the ETTD
		{
			// Construct the SQL
			$insert_Meta_SQL_start = 'INSERT INTO `#__easytables_table_meta` ( `id` , `easytable_id` , `label` , `fieldalias` ) VALUES ';
			// concatenate the values wrapped in SQL for the insert
			for ($colnum = 0; $colnum < $csvColumnCount; $colnum++ )
			{
				if($colnum > 0 )
				{
					$insert_Meta_SQL_row .= ', ';
				}
				$insert_Meta_SQL_row .= "( NULL , '$id', '$csvColumnLabels[$colnum]', '$ettdColumnAliass[$colnum]')";
				
			}
			// better terminate the statement
			$insert_Meta_SQL_end = ';';
			// pull it altogether
			$insert_Meta_SQL = $insert_Meta_SQL_start.$insert_Meta_SQL_row.$insert_Meta_SQL_end;
			// $msg .='<BR />Insert Meta SQL: '.$insert_Meta_SQL;
			echo '<BR />Insert Meta SQL: '.$insert_Meta_SQL;
			
	 		// Get a database object
			$db =& JFactory::getDBO();
			if(!$db){
				JError::raiseError(500,"Couldn't get the database object while doing SAVE() for table: $id");
			}
			// Run the SQL to insert the Meta records
			$db->setQuery($insert_Meta_SQL);
			$insert_Meta_result = $db->query();
			// $msg .='<BR />Insert Meta Result: '.$insert_Meta_result;
			if(!$insert_Meta_result)
			{
				JError::raiseError(500,'Meta insert failed for table: '.$id.'<BR />'.$msg);
			}
		}
		else
		{
			JError::raiseError(500, 'Failed to create the ETTD for Table: '.$id);
		}
		
		return($ettdColumnAliass);
	}
	
	function removeMeta ($id)
	{
 		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to remove META: $id");
		}

		// Build the DELETE SQL
		$query = 'DELETE FROM '.$db->nameQuote('#__easytables_table_meta').' WHERE easytable_id ='.$id.';';

		$db->setQuery($query);
		
		return($theResult=$db->query());
	}
	
	function createETTD ($id, $ettdColumnAliass)
	{
		
	// we turn the arrays of column names into the middle section of the SQL create statement 
		$ettdColumnSQL = implode('` TEXT NOT NULL , `', $ettdColumnAliass);

	// Build the SQL create the ettd
		$create_ETTD_SQL = 'CREATE TABLE `#__easytables_table_data_'.$id.'` (`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT , `';
		// Insert exlpoded
		$create_ETTD_SQL .= $ettdColumnSQL;
		// close the sql with the primary key
		$create_ETTD_SQL .= '` TEXT NOT NULL ,  PRIMARY KEY ( `id` ) )';
		
		echo $create_ETTD_SQL;
		
	// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to create table: $id");
		}
		
	// Set and execute the SQL query
		echo '<BR /> createETTD() -> '.$create_ETTD_SQL;
		$db->setQuery($create_ETTD_SQL);
		$ettd_creation_result = $db->query();
		
		return $this->ettdExists($id);
	}

	function removeETTD ($id)
	{
 		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to remove ETTD: $id");
		}
		// Build the DROP SQL
		$query = 'DROP TABLE '.$db->nameQuote('#__easytables_table_data_'.$id).';';

		$db->setQuery($query);
		
		return($theResult=$db->query());
		
		
	}

	function emptyETTD ($id)
	{
 		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while trying to remove ETTD: $id");
		}
		// Build the DROP SQL
		$query = 'DELETE FROM '.$db->nameQuote('#__easytables_table_data_'.$id).';';

		$db->setQuery($query);
		
		return($theResult=$db->query());
	}
	
	function updateETTDTable ($id, $ettdColumnAliass, $file)
	{
		$hasHeaders = JRequest::getVar('CSVFileHasHeaders');

		jimport('joomla.filesystem.file');

		$csvData = trim(JFile::read($file['tmp_name']));
		$csvLineArray = explode("\n",$csvData);
		
		if( count($ettdColumnAliass) != count(explode(',',$csvLineArray[0])))
		{ return FALSE; } // Our existing column count doesn't match those found in the first line of the CSV
		

		$insert_ettd_data_SQL_start  = 'INSERT INTO `#__easytables_table_data_';
		$insert_ettd_data_SQL_start .= $id.'` ( `id`, `';
		$insert_ettd_data_SQL_start .= implode('` , `', $ettdColumnAliass);
		$insert_ettd_data_SQL_start .= '` ) VALUES ';
		
		
		//$msg .= '<BR />$insert_ettd_data_SQL -> '.$insert_ettd_data_SQL;
		
		$insert_ettd_data_values ='';
		$insertLoopFirstPass = TRUE;
		$csvRowCount = count($csvLineArray);
		
		for($csvRowNum = ($hasHeaders ? 1:0); $csvRowNum <= $csvRowCount; $csvRowNum++)  // If it has headers we skip the 0 row
		{
			if(strlen($csvLineArray[$csvRowNum])>0) // We need to check for empty rows in the CSV file like extra CR or LF's at the EOF
			{
				if($insertLoopFirstPass)
				{
					$insertLoopFirstPass = FALSE;
				}
				else
				{
					$insert_ettd_data_values .= ', ';
				}
				$tempCSVRow = $csvLineArray[$csvRowNum];
				echo $tempCSVRow;
				$tempCSVRowArray = explode(',',$tempCSVRow);
				$tempSQLDataString = implode("' , '", $tempCSVRowArray);
				
				$insert_ettd_data_values .= "( NULL , '". $tempSQLDataString."')";
				echo $insert_ettd_data_values;
			}
		}
		
		if($hasHeaders){$csvRowCount--;}

		$insert_ettd_data_SQL_end = ';';
		
		$insert_ettd_data_SQL = $insert_ettd_data_SQL_start.$insert_ettd_data_values.$insert_ettd_data_SQL_end;
		$msg .='<BR />updateETTDTable() $insert_ettd_data_SQL -> '.$insert_ettd_data_SQL;

 		// Get a database object
		$db =& JFactory::getDBO();
		if(!$db){
			JError::raiseError(500,"Couldn't get the database object while doing SAVE() for table: $id");
		}
		
		// Run the SQL to load the data into the ettd
		$db->setQuery($insert_ettd_data_SQL);
		$insert_ettd_data_result = $db->query();
		$msg .='<BR />Insert Data Result: '.$insert_ettd_data_result;
		
		if(!$insert_ettd_data_result)
		{
			JError::raiseError(500,'Data insert failed for table: '.$id.'<BR />Possibly your CSV file is malformed<BR />'.$db->explain().'<BR />'.$msg);
		}
		else
		{
			return $csvRowCount;
		}
	}
	
	function cancel()
	{
		global $option;
		$this->checkInEasyTable();
		$this->setRedirect('index.php?option='.$option);
	}
	
	function display()
	{
		$view =  JRequest::getVar('view');
		if (!$view) {
			JRequest::setVar('view', 'EasyTables');
		}
		parent::display();
	}
}

// class