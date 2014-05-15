<?php
/**
 * template_lite replace modifier plugin
 *
 * Type:     modifier
 * Name:     replace
 * Purpose:  Wrapper for the PHP 'str_replace' function
 * Credit:   Taken from the original Smarty
 *           http://smarty.php.net
 * ADDED: { $text|replace:",,,,":",,,," }
 * @modifier ¿ÝÄ¾ <www.idreamsoft.com> 17:38 2007-11-13
 */
function tpl_modifier_imgsize($string, $width, $height)
{
	$string	= str_replace('<img src=', '<img width="'.$width.'" src=', $string);
	$string	= str_replace('http://www.ladyband.com/tag', 'http://m.ladyband.com/tag', $string);
	return $string;
}
?>