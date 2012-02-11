<?php
/**
 * @package     EasyTable Pro
 * @Copyright   Copyright (C) 2012 Craig Phillips Pty Ltd.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @author      Craig Phillips {@link http://www.seepeoplesoftware.com}
 */
	defined('_JEXEC') or die ('Restricted Access');
	function EasyTableProBuildRoute(&$query)
	{
		$segments = array();
		if(isset($query['view']))
			{
			$segments[] = $query['view'];
			unset($query['view']);
			}
		if(isset($query['id']))
			{
			$segments[] = $query['id'];
			unset($query['id']);
			}
		if(isset($query['rid']))
			{
				$segments[] = $query['rid'];
				unset($query['rid']);
			}
		if(isset($query['rllabel']))
		{
			$segments[] = $query['rllabel'];
			unset($query['rllabel']);
		}
		return $segments;
	}
	
	function EasyTableProParseRoute ($segments)
	{
		$vars = array();
		if (isset($segments[0])) {
			$vars['view'] = $segments[0];
		}
		if (isset($segments[1])) {
			$vars['id'] = $segments[1];
		}
		if (isset($segments[2])) {
			$vars['rid'] = $segments[2];
		}
		if (isset($segments[3])) {
			$vars['rllabel'] = $segments[3];
		}
		return $vars;
	}
?>
