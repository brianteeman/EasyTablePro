<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */

//--No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controller');

/**
 * EasyTables Controller
 *
 * @package    EasyTables
 * @subpackage Controllers
 */
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/general.php';
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

class EasyTableProControllerRecord extends JController
{
	protected $default_view = 'record';
	protected $option;
	protected $context;


	public function __construct($config = array())

	{
		parent::__construct($config);

		$jInput = JFactory::getApplication()->input;
		$jInput->set('view', $this->default_view);

		// Set our 'option' & 'context'
		$this->option = 'com_easytablepro';
		$this->context = 'record';

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('save2copy', 'save');
	}

	public function cancel()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$trid = ET_Helper::getTableRecordID();
		// So that we go back to the correct location
		$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	public function save($key = null, $urlVar = null)

	{

		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the Table & Record id
		$trid = ET_Helper::getTableRecordID();
		$task = $this->getTask();

		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$data = JRequest::getVar('et_fld', array(), 'post', 'array');
		$rid = $data['id'];
		$context = "$this->option.edit.$this->context.$rid";
		$task = $this->getTask();
		$easyTable = ET_Helper::getEasytableMetaItem($trid[0]);
		
		// Handle save2copy differently
		if ($task == 'save2copy')
		{
			// Reset the ID and then treat the request as for Apply.
			$data['id'] = 0;
			$task = 'apply';
		}

		// Tell the virtual model to Save the record
		if($model->save($data))
		{
			$trid[1] = $model->getState($this->context . '.id');
			$tridstr = implode('.', $trid);
			$app->enqueueMessage(JText::sprintf('Record %s saved successfully to table: "%s".', $trid[1], $easyTable->easytablename ));
		} else {
			$app->enqueueMessage(JText::sprintf('Unable to save changes to record (%s). %s', implode('.', $trid), implode('</br>\n',  $model->errors())));
		}

		// So that we go back to the correct location
		switch ($task) {
		case 'apply':
		case 'save2new':
			$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=record.edit&view=record&id=$tridstr");
			break;
		default:
			$this->setRedirect("/administrator/index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
			break;
		}
	}


	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))

	{
		$model = parent::getModel($name, $prefix, $config);
		$params = JComponentHelper::getParams('com_easytablepro');
		$model->setState('params',$params);
		return $model;
	}

}

// class
