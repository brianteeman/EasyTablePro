<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
defined('_JEXEC') or die('Restricted Access');

jimport( 'joomla.application.component.view');

$pvf = ''.JPATH_COMPONENT_ADMINISTRATOR.'/views/viewfunctions.php';
require_once $pvf;

/**
 * HTML View class for the EasyTables Component
 *
 * @package    EasyTables
 * @subpackage Views
 */

class EasyTableViewEasyTables extends JView
{
	function getEditorLink ($locked, $rowId, $tableName)
	{
		$link_text = JText::_( 'EDIT_PROPERTIES_AND_STRUCTURE_OF' ).' \''.$tableName.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.$tableName.'</span>';

		if( !$locked )
		{
			$theEditLink = '<span class="hasTip" title="'.$link_text.'" style="margin-left:10px;" >'.'<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId.'\',\'edit\');" title="'.$link_text.'" >'.$tableName.'</a></span>';
		}

		return($theEditLink);
	}

	function publishedIcon ($locked, $row, $i)
	{
		$btn_text = JText::_( ( $row->published ? 'PUBLISHED_BTN':'UNPUBLISHED_BTN') ).' \''.$row->easytablename.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( $locked ? 'disabled_' : '' ).'publish_g.png';
		$theBtn = '<span  class="hasTip" title="'.$btn_text.'" style="margin-left:15px;" ><img src="'.$theImageURL.'" border="0" ></span>';

		if( !$locked )
		{
			$theBtn = "<span class=\"hasTip\" title=\"$btn_text\" style=\"margin-left:15px;\" >".JHTML::_( 'grid.published',  $row, $i, '../'.$theImageURL ).'</span>';
		}

		return $theBtn;
	}


	function getDataEditorIcon ($locked, $i, $rowId, $tableName, $extTable)
	{
		if($extTable)
		{
			$btn_text = JText::sprintf ( 'LINKED_TABLE_NO_DATA_EDITING' , $tableName);
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/disabled_edit.png';
		}
		else
		{
			$btn_text = JText::_( 'EDIT_TABLE_DATA_IN_' ).' \''.$tableName.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( $locked ? 'disabled_' : '' ).'edit.png';
		}

		$theEditBtn = '<span class="hasTip" title="'.JText::_( 'EDIT_RECORDS' ).'::'.$btn_text.'" style="margin-left:4px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" />';

		if( !$locked && !$extTable)
		{
			$theEditBtn = '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\'editData\');" title="'.$btn_text.'" >'.$theEditBtn.'</a>';
		}

		return($theEditBtn);
	}

	function getDataUploadIcon ($locked, $i, $rowId, $tableName, $extTable)
	{
		if($extTable)
		{
			$btn_text = JText::sprintf ( 'LINKED_TABLE_NO_UPLOAD' , $tableName);
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/disabled_upload.png';
		}
		else
		{
			$btn_text = JText::_( 'UPLOAD_NEW_DESC' ).' \''.$tableName.'\' '.($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
			$theImageURL = 'components/com_'._cppl_this_com_name.'/assets/images/'.( $locked ? 'disabled_' : '' ).'upload.png';
		}

		$theBtn = '<span class="hasTip" title="'.JText::_( 'UPLOAD_DATA' ).'::'.$btn_text.'" style="margin-left:10px;" ><img src="'.$theImageURL.'" style="text-decoration: none; color: #333;" />';

		if( !$locked && !$extTable)
		{
			$theBtn = '<a href="/administrator/index.php?option=com_easytablepro&task=presentUploadScreen&view=easytableupload&cid='.$rowId.'&tmpl=component" class="modal" title="'.$btn_text.'" rel="{handler: \'iframe\', size: {x: 700, y: 495}}">'.$theBtn.'</a>';
		}

		return($theBtn);
	}

	function getSearchableTick ($rowId, $flag, $locked=true)
	{
		$btn_text = '';
		$theImageString = 'components/com_'._cppl_this_com_name.'/assets/images/'.( $locked ? 'disabled_' : '' );
		if( $flag == '' )
		{
			$theImageString .= 'GlobalIcon16x16.png';
			$btn_text = JText::_( "CLICK_HERE_TO_ALLOW_ACCESS_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}
		else if($flag)
		{
			$theImageString .= 'tick.png';
			$btn_text = JText::_( "CLICK_HERE_TO_PREVENT_ACCESS_TO_THIS_TABLE_BY_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}
		else
		{
			$theImageString .= 'publish_x.png';
			$btn_text = JText::_( "CLICK_HERE_TO_USE_THE_GLOBAL_PREFERENCES_TO_CONTROL_JOOMLA_S_BUILT_IN_SEARCH_FUNCTION_TO_ACCESS_THIS_TABLE_" ).($locked ? JText::_( 'DISABLED_BECAUSE_THE_TABLE_IS_LOCKED_' ) : '');
		}

		$theSearchableImage = '<img src="'.$theImageString.'" name="'.$rowId.'_img" border="0" />';
		$theSearchableButton = '<span class="hasTip" title="'.$btn_text.'" style="margin-left:20px;" ><a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$rowId
								.'\',\'toggleSearch\');" title="'.$btn_text.'" >'.$theSearchableImage.'</a></span>';
		
		return($theSearchableButton);
	}

	/**
	 * EasyTables view display method
	 * @return void
	 **/
	function display($tpl = null)
	{
		//get the document and load the js support file
		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.css');
		$doc->addScript(JURI::base().'components/com_'._cppl_this_com_name.'/'._cppl_base_com_name.'.js');
		
		// Get the current user
		$user =& JFactory::getUser();

		// Get the settings meta record
		$settings = ET_MgrHelpers::getSettings();
		// Allow Access settings
		$aaSettings = explode(',', $settings->get('allowAccess'));
		// Allow Linking Access settings
		$alaSettings = explode(',', $settings->get('allowLinkingAccess'));

		/*
			Setup the Toolbar
		*/
		JToolBarHelper::title(JText::_( 'EASYTABLEPRO' ), 'easytables');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_( 'ARE_YOU_SURE_YOU_TO_DELETE_THE_TABLE_S__' ));
		JToolBarHelper::addNew();
		if(in_array($user->usertype, $alaSettings))
		{
			$toolbar=& JToolBar::getInstance( 'toolbar' );
			$toolbar->appendButton( 'Popup', 'linkTable', 'Link Table', 'index.php?option=com_easytablepro&view=easytablelink&tmpl=component', 500, 280 );
		}
		JToolBarHelper::preferences( 'com_'._cppl_this_com_name, 420 );
		if(in_array($user->usertype, $aaSettings))
		{
			JToolBarHelper::custom( 'settings','Gear_Icon_48x48.png','',JText::_('SETTINGS'), FALSE );
		}

		/**
		 *
		 * Let's do a version check - it's always good to use the newest version.
		 *
		**/

		// Get data from the model
		$subscriber_ver_array = ET_VHelpers::et_version('subscriber');
		$rows =& $this->get('data');
		$this->assignRef('rows',$rows);
		$this->assign('et_current_version',ET_VHelpers::current_version());
		$this->assign('et_subscriber_version',$subscriber_ver_array["version"]);
		$this->assign('et_subscriber_tip',$subscriber_ver_array["tip"]);
		parent::display($tpl);
	}
}
