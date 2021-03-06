<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2012-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

// No direct access
defined('_JEXEC') or die ('Restricted Access');

jimport('joomla.application.component.controlleradmin');

require_once '' . JPATH_COMPONENT_ADMINISTRATOR . '/helpers/general.php';

/**
 * EasyTables Records Controller
 *
 * @package     EasyTables
 * @subpackage  Controllers
 *
 * @since       1.0
 */
class EasyTableProControllerRecords extends JControllerAdmin
{
	/**
	 * @var		string	The default view.
	 *
	 */
	protected $default_view = 'records';

	/**
	 * getModel()
	 *
	 * @param   string  $name    Name of the model file.
	 *
	 * @param   string  $prefix  Component Model class.
	 *
	 * @param   array   $config  Optional configuration parameters.
	 *
	 * @return  EasyTableProModelRecords
	 *
	 * @since   1.0
	 */
	public function getModel($name = 'Record', $prefix = 'EasyTableProModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * delete()
	 *
	 * @return void
	 *
	 * @since  1.0
	 */
	public function delete()
	{
		// Get app and input
		$jAp = JFactory::getApplication();
		$jInput = $jAp->input;

		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Some precautionary steps
		$trid = ET_General_Helper::getTableRecordID();

		// Get items to remove from the request.
		$cid = $jInput->get('cid', array(), 'ARRAY');

		if (!is_array($cid) || count($cid) < 1)
		{
			$jAp->enqueueMessage(JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'ERROR');
		}
		else
		{
			// Get the model.
			// @var EasyTableProModelRecords $model
			$model = $this->getModel();

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		// So that we go back to the correct location
		$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	/**
	 * listAll()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function listAll()
	{
		$trid = ET_General_Helper::getTableRecordID();
		$this->setRedirect("index.php?option=com_easytablepro&task=records&view=records&id=$trid[0]");
	}

	/**
	 * cancel()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		// So that we go back to the correct location
		$this->setRedirect("index.php?option=com_easytablepro&task=tables");
	}
}
