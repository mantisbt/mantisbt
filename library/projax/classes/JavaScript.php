<?php  

/**
 * Projax
 *
 * An open source set of php helper classes for prototype and script.aculo.us.
 *
 * @package		Projax
 * @author		Vikas Patial
 * @copyright	Copyright (c) 2006, ngcoders.
 * @license		http://www.gnu.org/copyleft/gpl.html 
 * @link		http://www.ngcoders.com
 * @since		Version 0.2
 * @filesource
 */

class JavaScript {
	
	function button_to_function($name,$function=null)
	{
		return '<input type="button" value="'.$name.'" onclick="'.$function.'" />';
		
	}
	

	function escape($javascript)
	{
		$javascript=addslashes($javascript);
		$javascript=str_replace(array("\r\n","\n","\r"),array("\n"),$javascript);
		return $javascript;
		
	}
	
	
	function tag($content)
	{
		return '<script type="text/javascript">'.$content.'</script>';
	}
	
		
	function link_to_function($name,$function,$html_options=null)
	{
		return '<a href="'.((isset($html_options['href']))?$html_options['href']:'#').'" onclick="'.((isset($html_options['onclick']))?$html_options['onclick'].';':'').$function.'; return false;" />'.$name.'</a>';
	}
		
	/////////////////////////////////////////////////////////////////////////////////////
	//                             Private functions 
	/////////////////////////////////////////////////////////////////////////////////////
	
	function _array_or_string_for_javascript($option)
	{
		$return_val='';
		if(is_array($option))
		{
			foreach ($option as $value) {
				if(!empty($return_val))$ret_val.=', ';
				$return_val.=$value;
			}
			return '['.$return_val.']';
		} 
			return "'$option'";	
	}
	
	
	function _options_for_javascript($options)
	{
		$return_val='';
		
		if (is_array($options)) {
			
		foreach ($options as $var=>$val)
		{
			if (!empty($return_val)) $return_val.=', ';
			$return_val.="$var:$val";
		}
		}		
		return '{'.$return_val.'}';
	}
}


?>
