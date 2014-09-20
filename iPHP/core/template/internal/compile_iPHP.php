<?php
/**
 * Template Lite iPHP compile plugin ADD iDreamSoft
 *
 * Type:	 compile
 * Name:	 compile_iPHP
 */

function compile_iPHP($arguments, &$object){
	$attrs   = $object->_parse_arguments($arguments);
	$hash    = substr(uniqid(rand()), -4);
	$props   = "\$_iPHP_{$hash}";
	$props_a = "\$_iPHP_{$hash}_a";
	$output  = "<?php if (isset($props)) unset($props);\n";
	foreach ($attrs as $attr_name => $attr_value){
		switch ($attr_name){
			case 'app':
				$output .= "{$props}['total'] = count($attr_value);\n";
				$output .= "{$props_a}={$attr_value};unset($attr_value);\n";
				break;
			case 'name':
				$output .= "{$props}['$attr_name'] = '$attr_value';\n";
				break;
			case 'max':
			case 'start':
				$output .= "{$props}['$attr_name'] = (int)$attr_value;\n";
				break;
			case 'step':
				$output .= "{$props}['$attr_name'] = ((int)$attr_value) == 0 ? 1 : (int)$attr_value;\n";
				break;
		}
	}

	if (isset($attrs['max'])){
		$output .= "{$props}['max'] < 0 && {$props}['max'] = {$props}['total'];\n";
	}else{
		$output .= "{$props}['max'] = {$props}['total'];\n";
	}

	isset($attrs['step']) OR $output .= "{$props}['step'] = 1;\n";

	if (!isset($attrs['start'])){
		$output .= "{$props}['start'] = {$props}['step'] > 0 ? 0 : {$props}['total']-1;\n";
	}else{
		$output .= "if ({$props}['start'] < 0){\n" .
				   "	{$props}['start'] = max({$props}['step'] > 0 ? 0 : -1, {$props}['total'] + {$props}['start']);\n" .
				   "}else{\n" .
				   "	{$props}['start'] = min({$props}['start'], {$props}['step'] > 0 ? {$props}['total'] : {$props}['total']-1);\n}\n";
	}
	if (isset($attrs['start'])||isset($attrs['step'])||isset($attrs['max'])){
		$output .= "{$props}['total'] = min(ceil(({$props}['step'] > 0 ? {$props}['total'] - {$props}['start'] : {$props}['start']+1)/abs({$props}['step'])), {$props}['max']);\n";
	}

	$output .= "if({$props}['max']){";
	$output .= "for ({$props}['index'] = {$props}['start'], {$props}['rownum'] = 1;{$props}['rownum'] <= {$props}['total'];{$props}['index'] += {$props}['step'], {$props}['rownum']++){\n";
	$output .= "{$props}['prev'] = {$props}['index'] - {$props}['step'];\n";
	$output .= "{$props}['next'] = {$props}['index'] + {$props}['step'];\n";
	$output .= "{$props}['first'] = ({$props}['rownum'] == 1);\n";
	$output .= "{$props}['last'] = ({$props}['rownum'] == {$props}['total']);\n";
	$output .= "{$attrs['app']} = array_merge((array){$props_a}[{$props}['index']],(array){$props});\n";
	$output .= "?>";
//print_r($output);
	return $output;
}
?>
