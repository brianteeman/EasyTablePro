<?php
/**
 * @package    EasyTable_Pro
 * @author     Craig Phillips <craig@craigphillips.biz>
 * @copyright  Copyright (C) 2009-2014 Craig Phillips Pty Ltd.
 * @license    GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @url        http://www.seepeoplesoftware.com
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
?>

<form action="index.php?option=com_easytablepro" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div style="float: right">
					<button type="button" onclick="<?php echo $this->closeURL; ?>"><?php echo JText::_('COM_EASYTABLEPRO_LABEL_CLOSE'); ?></button>
				</div>
				<div class="configuration"><?php echo JText::_('COM_EASYTABLEPRO');?> - <?php echo $this->stepLabel; ?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo $this->stepLegend; ?></legend>
			<?php switch ($this->step) {
				case 'new':
					echo $this->loadTemplate('new');
					break;

				case 'uploadCompleted':
					echo $this->loadTemplate('completed');
					break;

				default:
					echo $this->loadTemplate('form');
					break;
			} ?>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="task" value="" />
<input type="hidden" name="id" value="<?php echo $this->form->getValue('id'); ?>" />
<input type="hidden" name="jform[id]" value="<?php echo $this->form->getValue('id'); ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>
<?php if (($this->step == 'uploadCompleted') && ($this->prevStep == 'new')) {?>
<script type="text/javascript">
<!--
sbx = parent.document.getElementById('sbox-content');
sbx.setStyle('height','335px');
sbx.firstChild.setStyle('height','330px');
parent.SqueezeBox.resize({x: 700, y: 330});
parent.document.getElementById('sbox-btn-close').toggle();
//-->
</script><?php } ?>
