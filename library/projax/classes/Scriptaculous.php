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

class Scriptaculous extends Prototype   {
	
	var $TOGGLE_EFFECTS = array('toggle_appear', 'toggle_slide','toggle_blind');

	function Scriptaculous(){
	}

	function dragable_element($element_id,$options=null)
	{
		return $this->tag($this->_dragable_element_js($element_id,$options));
	}

	function drop_receiving_element($element_id,$options=null)
	{
		return $this->tag($this->_drop_receiving_element($element_id,$options));
	}

	function visual_effect($name,$element_id=false,$js_options=null) {

		$element=($element_id)?"'$element_id'":'element';

		$js_queue ='';
		if(isset($js_options) && is_array($js_options['queue'])){

		} elseif (isset($js_options)) {
			$js_queue="'$js_options'";
		}

		if(in_array($name,$this->TOGGLE_EFFECTS)){
			return  "Effect.toggle($element,'".str_replace('toggle_','',$name)."',".$this->_options_for_javascript($js_options).')';
		}else {
			return  "new Effect.".ucwords($name)."($element,".$this->_options_for_javascript($js_options).')';
		}

	}

	function sortabe_element($element_id,$options=null)
	{
		return $this->tag($this->_sortabe_element($element_id,$options));
	}

	/////////////////////////////////////////////////////////////////////////////////////
	//                             Private functions
	/////////////////////////////////////////////////////////////////////////////////////

	function _sortabe_element($element_id,$options)
	{
		//if(isset($options['with']))
		{
			$options['with'] ="Sortable.serialize('$element_id')";
		}

		//if (isset($option['onUpdate']))
		{
			$options['onUpdate'] ="function(){". $this->remote_function($options) ."}";
		}

		foreach ($options as $var=>$val)if(in_array($var,$this->AJAX_OPTIONS))unset($options[$var]);

		$arr = array('tag','overlap','contraint','handle');

		foreach ($arr as $var){
			if (isset($options[$var])) {
				$options[$var]	= "'".$options[$var]."'";
			}
		}

		if (isset($options['containment'])) {
			$options['containment'] =$this->_array_or_string_for_javascript($options['containment']);
		}

		if (isset($options['only'])) {
			$options['only'] =$this->_array_or_string_for_javascript($options['only']);
		}

		return "Sortable.create('$element_id',".$this->_options_for_javascript($options).')';

	}

	function  _dragable_element_js($element_id,$options)
	{
		return 'new Draggable(\''.$element_id.'\','.$this->_options_for_javascript($options).')';
	}


	function _drop_receiving_element($element_id,$options)
	{


		//if(isset($options['with']))
		{
			$options['with'] = '\'id=\' + encodeURIComponent(element.id)';
		}
		//if (isset($option['onDrop']))

		{
			$options['onDrop'] ="function(element){". $this->remote_function($options) ."}";
		}

		if (is_array($options)) {
			foreach ($options as $var=>$val)if(in_array($var,$this->AJAX_OPTIONS))unset($options[$var]);
		}


		if (isset($options['accept'])) {
			$options['accept'] =$this->_array_or_string_for_javascript($options['accept']);
		}

		if (isset($options['hoverclass'])) {
			$options['hoverclass'] ="'".$options['hoverclass']."'";
		}

		return  'Droppables.add(\''.$element_id.'\','. $this->_options_for_javascript($options).')';
	}

	/////////////////////////////////////////////////////////////////////////////////////
	//                            Merged Javascript macro 
	/////////////////////////////////////////////////////////////////////////////////////

function in_place_editor($field_id,$options,$tag=true) {
	     $function  =  "new Ajax.InPlaceEditor(";
         $function .= "'$field_id', ";
	     $function .= "'".$options['url']."'";
	     
	      $js_options=array();
	     if (isset($options['cancel_text']))$js_options['cancelText']=$options['cancel_text'];
	     if (isset($options['save_text']))$js_options['okText']=$options['save_text'];
	     if (isset($options['loading_text']))$js_options['loadingText']=$options['loading_text'];
	     if (isset($options['rows']))$js_options['rows']=$options['rows'];
	     if (isset($options['cols']))$js_options['cols']=$options['cols'];
	     if (isset($options['size']))$js_options['size']=$options['size'];
	     if (isset($options['external_control']))$js_options['externalControl']="'".$options['external_control']."'";
	     if (isset($options['load_text_url']))$js_options['loadTextURL']="'".$options['load_text_url']."'";
	     if (isset($options['options']))$js_options['ajaxOptions']=$options['options'];
	     if (isset($options['script']))$js_options['evalScripts']=$options['script'];
	     if (isset($options['with']))$js_options['callback']="function(form) { return ".$options['with']." }";

	     $function.= ', '.$this->_options_for_javascript($js_options).' )';
	      if($tag)return $this->tag($function);	else 
	      return  $function;
	}
	
	function in_place_editor_field($object,$tag_options=null,$in_place_editor_options=null)
	{
		$ret_val='';
		$ret_val.='<span id="'.$object.'" class="in_place_editor_field">'.(isset($tag_options['value'])?$tag_options['value']:'').'</span>';
		$ret_val.=$this->in_place_editor($object,$in_place_editor_options);
		return $ret_val;
	}
	
	function auto_complete_field($field_id,$options){
		$function = "var $field_id"."_auto_completer = new Ajax.Autocompleter(";
		$function.= "'$field_id', ";
		$function.= "'".(isset($options['update'])?$options['update']:$field_id.'_auto_complete')."', ";
		$function.= "'".$options['url']."'";
		
		$js_options=array();
		if (isset($options['tokens']))$js_options['tokens']=$this->javascript->_array_or_string_for_javascript($options['tokens']);
		if (isset($options['with']))$js_options['callback']="function(element, value) { return ".$options['with']." }";
		if (isset($options['indicator']))$js_options['indicator']="'".$options['indicator']."'";
		if (isset($options['select']))$js_options['select']="'".$options['select']."'";
		
		foreach (array('on_show'=>'onShow','on_hide'=>'onHide','min_chars'=>'min_chars') as $var=>$val) {
			if (isset($options[$var])) $js_options['$val']=$options['var'];
		}
		
		$function.= ', '.$this->_options_for_javascript($js_options).' )';
		return $this->tag($function);
	}
	
	function auto_complete_results($entries,$field,$phrase=null){
		if(!is_array($entries))return;
		$ret_val='<ul>';
	//	Complete this function
	}
	
	function text_field_with_auto_complete($object,$tag_options=null,$completion_options=null)
	{
		$ret_val=(isset($completion_options['skip_style']))?'':$this->_auto_complete_stylesheet();
		
		# @@@ Fixed a bug in the next like where the dumping of the value tag used to check if size is set!
		# @@@ Added maxlength and tabindex attributes
		$t_tabindex = isset( $tag_options['tabindex'] ) ? ( ' tabindex="' . $tag_options['tabindex'] . '"' ) : '';
		$t_maxlength = isset( $tag_options['maxlength'] ) ?( ' maxlength="' . $tag_options['maxlength'] . '"' ) : '';
		$ret_val.='<input autocomplete="off" id="'.$object.'" name="'.$object.'"'. $t_tabindex . $t_maxlength . ' size="'.(isset($tag_options['size'])?$tag_options['size']:30).'" type="text" value="'.(isset($tag_options['value'])?$tag_options['value']:'').'" '.(isset($tag_options['class'])?'class = "'.$tag_options['class'].'" ':'').'/>';
		
		$ret_val.='<div id="'.$object.'_auto_complete" class="auto_complete"></div>';
		$ret_val.=$this->auto_complete_field($object,$completion_options);
		return $ret_val;
	}
	
	function _auto_complete_stylesheet()
	{
		return '<style> div.auto_complete {
	              width: 350px;
	              background: #fff;
 	            }
	            div.auto_complete ul {
 	              border:1px solid #888;
 	              margin:0;
 	              padding:0;
 	              width:100%;
 	              list-style-type:none;
 	            }
 	            div.auto_complete ul li {
 	              margin:0;
 	              padding:3px;
 	            }
 	            div.auto_complete ul li.selected {
 	              background-color: #ffb;
 	            }
 	            div.auto_complete ul strong.highlight {
 	              color: #800;
 	              margin:0;
 	              padding:0;
 	            }
 	            </style>';
	}

}
