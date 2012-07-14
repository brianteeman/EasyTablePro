<?php
/**
 * @package    EasyTables
 * @author     Craig Phillips {@link http://www.seepeoplesoftware.com}
 * @author     Created on 13-Jul-2009
 */

//--No direct access
	defined('_JEXEC') or die('Restricted Access');
	JHTML::_('behavior.tooltip');
?>
<form action="#" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<div class="col100">
	<table width="100%">
		<tr>
			<td>
			<fieldset>
				<div class="configuration"><?php echo JText::_('COM_EASYTABLEPRO');?> - <?php echo JText::_('COM_EASYTABLEPRO_LINK_EXISTING_TABLE');?></div>
			</fieldset>
			<fieldset class="adminform">
			<legend><?php echo $this->legend; ?></legend>
			<table class="adminlist" id="et_linkTable">
				<tr class="row0">
					<td>
						<p style="text-align: center"><button type="button" onclick="com_EasyTablePro.Link.editTable()"><?php echo JText::sprintf('COM_EASYTABLEPRO_EDIT_LINKED_TABLE',$this->let); ?></button></p>
					</td>
				</tr>
				<tr class="row1">
					<td>
						<span style="font-size: 1.5em;font-weight: bold;"><label for="easytablename"><?php echo JText::_('COM_EASYTABLEPRO_MGR_NOTES'); ?>:</label></span>
						<?php echo $this->note; ?>
					</td>
				</tr>
			</table>
			</fieldset>
			</td>
		</tr>
	</table>
</div>
<div class="clr"></div>

<input type="hidden" name="id" id="id" value="<?php echo $this->id; ?>" />
<?php echo JHTML::_('form.token'); ?>
</form>