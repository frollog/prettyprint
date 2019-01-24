<?php

function pretty_print ($in = null, $opened = true, $param = array())
{
	//определяем имя вызываемой переменой
	$backtrace = debug_backtrace()[0];
    $fh = fopen($backtrace['file'], 'r');
    $line = 0;
    while (++$line <= $backtrace['line']) 
	{
        $code = fgets($fh);
    }
    fclose($fh);
    preg_match('/pretty_print\s*\((.*)\)\s*;/u', $code, $name); //извлекаем параметры данной функции из кода
	$v = trim($name[1]);
	//отсекаем ненужные параметры (TODO: переделать на шаблон)
	$brack_count_op = 0;
	$brack_count_cl = 0;
	$end = 0;
	for ($i = 0; $i<strlen($v); $i ++)
	{
		if ($v[$i] == '(')
		{
			$brack_count_op++;
		}
		elseif ($v[$i] == ')')
		{
			$brack_count_cl++;
		}
		elseif ($v[$i] == ',' && $brack_count_op == $brack_count_cl)
		{
			$end = $i;
			break;
		}
	}
	if ($end > 0)
	{
		$v = substr($v, 0, $end); //получили имя анализируемой переменной
	}
	$param_arr = array('var' => $in, 'name' => $v );
	$obj = new pretty_print_class($param_arr);
	//require_once('prettyprint.php');
	//echo '<br> OBJ:';
	//pretty_print_orig($obj, true, false, true, true);
}

class pretty_print_class 
{
	private $var 			= null;
	private $opened 		= true; //вывод субэлементов
	private $open_props 	= false; //вывод свойств
	private $open_fields 	= false; //вывод полей свойств
	private $max_str_len 	= 100;
	private $time_start 	= 631152000; //1990.01.01
	private $name 			= '';
	private $to_file 		= false;
	private $style_border 	= ' style="border: 1px solid red; padding: 5px"';
	private $style_unset 	= ' style="color:red"';
	private $style_string 	= ' style="color:magenta"';
	private $style_resource	= ' style="color:grey"';
	private $style_int		= ' style="color:green"';
	private $style_double	= ' style="color:LimeGreen"';
	private $style_bool		= ' style="color:blue"';
	private $style_margin 	= ' style="margin-left:30px"';
	private $style_td1	 	= ' width=10';
	private $style_td2	 	= ' width=10';
	private $style_td3	 	= ' style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"';
	private $style_table	= ' border="1" cellpadding="5"';
	private $style_text		= ' style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all"';
	private $res 			= array();
	private $filestream 	= null;
	private $func_id 		= 1;
	
	public function __construct($params = array())
	{
		$this->func_id  	= rand();
		$this->var 			= $params['var'];
		$this->name 		= $params['name'];
		if (isset($params['to_file'])) 		$this->to_file 		= $params['to_file'];
		if (isset($params['opened']))		$this->opened 		= $params['opened'];
		if (isset($params['open_props']))	$this->open_props	= $params['open_props'];
		if (isset($params['open_fields']))	$this->open_fields	= $params['open_fields'];		
		
		$this->pp();
	}
	public function pp ()
	{
		if ($this->to_file)
		{
			//TODO filestream start
		}
		$this->add
		('<script>'.
			'function hideShowRaw'.$this->func_id.'(id)'.
			'{'.
				'var x = document.getElementById(id);'.
				'if (x.style.display === "none")'.
				'{'.
					'x.style.display = "table-row";'.
				'}'.
				'else'.
				'{'.
					'x.style.display = "none";'.
				'}'.
			'}'.
		'</script>');
 //';
		$this->add ('<pre>'); //<div'.$this->style_border.'>
		if (!isset($this->var))
		{
			$this->add ('<div'.$this->style_border.'><b>'.$this->name.'</b>: <span'.$this->style_unset.'>-UNSET-</span></div>');
		}
		else
		{
			$this->add('<table'.$this->style_table.'>');
			if (is_object($this->var))
			{
				$this->object_print ($this->var, $this->name, true);
			}
			elseif (is_array($this->var))
			{
				$this->array_print ($this->var, $this->name);
			}
			else
			{
				$this->value_print ($this->var, $this->name);
			}
			$this->add('</table>');
		}
		$this->add('</pre>'); //</div>
		if (!$this->to_file)
		{
			$this->out ();	
		}
		else
		{
			//TODO filestream end
		}
	}
	
	private function pp_rec ($in, $st_arr = null)
	{
		if(!is_object($in) && !is_array($in)) 
		{
			return;
		}
		//$this->add ('<div'.$this->style_margin.'>');
		foreach($in as $key => $value) 
		{
			$status = $st_arr?$st_arr[$key]:'';
			$show_private = false; //можно переопределить
			if (is_object($value))
			{
				$this->object_print ($value, $key, $show_private, $status);
			}
			elseif (is_array($value))
			{
				$this->array_print ($value, $key, $status);
			}
			else
			{
				$this->value_print ($value, $key, $status);			
			}
		}
		//$this->add ('</div>');
	}
	
	private function object_print ($obj, $name = '-object-', $show_all = false, $status = null)
	{
		if (!is_object($obj))
		{
			return;
		}
		$all_f = count((array)$obj); //все поля объекта
		$open_f = 0; //открытые поля
		foreach ($obj as $key => $value)
		{
			$open_f++;
		}
		$methods = $this->get_methods ($obj);
		$var_array = array();
		$status_array = null;
		if ($show_all) //$open_f < $all_f &&  - static будет отображаться для public
		{ //приватные поля
			$status_array = array();
			$reflection = new ReflectionClass($obj);
			$properties = $reflection->getProperties();
			foreach ($properties as $property)
			{
				$property->setAccessible(true);
				$status = '';
				if ($property->isStatic())
				{
					$status = 'static';
				}
				if ($property->isPrivate())
				{
					$status = ($status == '')?'private':$status.' private';
				}
				elseif ($property->isProtected())
				{
					$status = ($status == '')?'protected':$status.' protected';
				}
				else
				{
					$status = ($status == '')?'public':$status.' public';
				}
				$status = ($status == '')?'':''.$status.' ';
				$var_array[$property->getName()] = $property->getValue($obj);
				$status_array[$property->getName()] = $status;
				if (!$property->isPublic())
				{
					$property->setAccessible(false);
				}
			}
			unset($reflection);
		}
		
		$type = '(['.$open_f.'/'.$all_f.']<i><b>'.$status.'</b>object of <u>'.get_class($obj).'</u></i>)';
		$value_det = '<i>-empty-</i>';
		if ($all_f > 0 || $methods)
		{
			if ($var_array)
			{
				$obj = $var_array;
			}
			$this->detail_ins_begin($name, $type, '', $this->opened);
			if ($methods)
			{
				$this->field_detail_begin('class methods', '', true);
				$this->pp_rec($methods);
				$this->field_detail_end('', true);
			}
			$this->pp_rec($obj, $status_array);
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($name, $type, $value_det);
		}
	}
	
	private function array_print ($arr, $name = '-array-', $status = '')
	{
		if (!is_array($arr))
		{
			return;
		}
		$type = '(['.count($arr).']<b>'.$status.'</b><i>array</i>)';
		$value_det = '<i>-empty-</i>';
		if ($arr)
		{
			$this->detail_ins_begin($name, $type, '', $this->opened);
			$this->pp_rec($arr);
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($name, $type, $value_det);
		}
	}
	
	
	private function get_methods ($obj)
	{
		if (is_object($obj))
		{
			$methods = get_class_methods(get_class($obj));
			if (count($methods) > 0)
			{
				return $methods;
			}
		}
		return null;
	}
	
	private function value_print ($value, $key = null, $status = '')
	{
		if ($key === null)
		{
			$key = '-scalar value-';
		}
		switch(gettype($value))
		{
			case 'string':
				$this->print_string ($value, $key, $status);
				break;
			case 'integer':
				$this->print_int ($value, $key, $status);
				break;
			case 'double':
				$this->print_double ($value, $key, $status);
				break;
			case 'NULL':
				$this->print_null ($key, $status);
				break;
			case 'boolean':
				$this->print_bool ($value, $key, $status);
				break;
			case 'resource':
				$this->print_resource ($value, $key, $status);
				break;
			default:
				$this->print_default ($value, $key, $status);
		}
	}
	
	private function print_string ($value, $key, $status = '')
	{
		//$this->raw_ins($name, $type, $value);
		$name = $key;
		$type = '(['.mb_strlen($value).']<i><b>'.$status.'</b>string</i>)';
		$value_det = '&#171<span'.$this->style_string.'>'.strip_tags($value).'</span>&#187';
		$need_long 		= (mb_strlen($value) > $this->max_str_len);
		$need_strip 	= (strip_tags($value) !== $value);
		$need_urldec 	= (urldecode($value) !== $value);
		$need_json 		= (strlen($value) > 2 && $value{0} === '{' && $value{strlen($value) - 1} === '}');
		$need_url 		= filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED);
		$need_ip 		= filter_var($value, FILTER_VALIDATE_IP);
		$need_time 		= $this->get_time($value);
		if ($need_long || $need_strip || $need_urldec || $need_json || $need_url || $need_ip || $need_time)
		{//вывод с деталировкой
			$this->detail_ins_begin($name, $type, $value_det, $this->open_props);
			if ($need_long)//длинные строки
			{
				$this->field_detail('all string', $value);
			}
			if ($need_strip)//кодировки
			{
				if (mb_strlen(htmlspecialchars($value)) > 0)
				{
					$this->field_detail('string with tags (utf-8)', htmlspecialchars($value));
				}
				elseif (mb_strlen(htmlspecialchars($value, null, "windows-1251")) > 0)
				{
					$this->field_detail('string with tags(windows-1251)', htmlspecialchars($value, null, "windows-1251"));
				}
				else
				{
					$this->field_detail('string with tags', '<i>Unrecognized encoding!</i>');
				}
				$this->field_detail('string with decoded tags', htmlspecialchars_decode($value, ENT_QUOTES));
			} 
			if ($need_urldec)//URL декод
			{
				$temp = $value;
				while (urldecode($temp) !== $temp)
				{
					$temp = urldecode($temp);
				}
				$this->field_detail('url decode', $temp);
			}	
			if ($need_json)//json
			{
				$res = json_decode($value, true);
				if ($res !== NULL)
				{
					$this->field_detail_begin('json decode', '', true);
					$this->pp_rec ($res);
					$this->field_detail_end('', true);
				}
				else
				{
					$this->field_detail('json decode', '<i>Error while encoding!</i>'); //TODO
				}
			}
			if ($need_url)
			{
				$this->field_detail('URL parsing', '<a href="'.$value.'" target="_blank">-open url-</a>');
			}
			if ($need_ip) 
			{
				$this->field_detail('IP parsing', '<a href="https://whatismyipaddress.com/ip/'.$value.'" target="_blank">-open whois-</a>');
			}
			if ($need_time) 
			{
				$this->field_detail('time parsing', $need_time);
			}
			$this->detail_ins_end();
		}
		else
		{//вывод без деталей
			$this->raw_ins($name, $type, $value_det);
		}
	}
	
	private function print_resource ($value, $key, $status='')
	{
		$res_type = get_resource_type($value);
		$type = '(<i><b>'.$status.'</b>resource:'.$res_type.'</i>):';
		$text_resource = '<span'.$this->style_resource.'>'.$value.'</span>';
		$curl_info = false;
		$key_info = false;
		if ($res_type == 'curl')
		{
			$curl_info = curl_getinfo ($value);
		} 
		elseif ($res_type == 'OpenSSL key')
		{
			$key_info = openssl_pkey_get_details ($value);
		}
		if ($curl_info || $key_info)
		{
			$this->detail_ins_begin($key, $type, $text_resource, $this->open_props);
			if ($curl_info)
			{
				$this->field_detail_begin('curl info', '', true);
				$this->pp_rec ($curl_info);
				$this->field_detail_end('', true);
			}
			if ($key_info)
			{
				$this->field_detail_begin('key info', '', true);
				$this->pp_rec ($key_info);
				$this->field_detail_end('', true);
			}
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($key, $type, $text_resource);
		}
	}
	
	private function print_int ($value, $key, $status='')
	{
		$name = $key;
		$type = '(['.floor(log10(abs($value))).'DEC]<i><b>'.$status.'</b>integer</i>)';
		$value_det = '<span'.$this->style_int.'>'.$value.'</span>';
		$need_time = $this->get_time($value);
		if ($need_time) //свойства
		{
			$this->detail_ins_begin($name, $type, $value_det, $this->open_props);
			$this->field_detail('time parsing', $need_time);
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($name, $type, $value_det);
		}
	}
	
	private function print_double ($value, $key, $status='')
	{
		$name = $key;
		$type = '(['.floor(log10(abs($value))).'DEC]<i><b>'.$status.'</b>double</i>)';
		$value_det = '<span'.$this->style_double.'>'.$value.'</span>';
		$need_time = $this->get_time($value);
		if ($need_time) //свойства
		{
			$this->detail_ins_begin($name, $type, $value_det, $this->open_props);
			$this->field_detail('time parsing', $need_time);
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($name, $type, $value_det);
		}
	}
	
	private function print_null ($key, $status='')
	{
		$this->raw_ins($key, '(<i><b>'.$status.'</b>NULL</i>)', '<span'.$this->style_unset.'>-NULL-</span>');
	}
	
	private function print_bool ($value, $key, $status='')
	{
		$this->raw_ins($key, '(<i><b>'.$status.'</b>bool</i>)', '<span'.$this->style_bool.'>'.($value?'TRUE':'FALSE').'</span>');
	}
	
	private function print_default ($value, $key, $status='')
	{
		$this->raw_ins($key, '(<i><b>'.$status.'</b>undefined</i>)', '<span'.$this->style_unset.'>'.$value.'</span>');
	}
	
	
	private function get_time($value)
	{
		$res = false;
		$num = intval($value);
		if ($num > $this->time_start)
		{
			$res = date("Y.m.d H:i:s", $num);
		}
		return $res;
	}
	
	
	private function field_detail($name, $value)
	{
		$this->field_detail_begin($name, $value);
		$this->add($value);
		$this->field_detail_end($value);
	}
	
	
	private function field_detail_begin($name, $value, $full_form = false)
	{
		if(mb_strlen($value) > $this->max_str_len || $full_form)
		{
			$id = rand();
			$disp = $this->open_fields?'': ' style="display:none;"';
			$this->add(
				'<tr onclick="hideShowRaw'.$this->func_id.'('.$id.')">'.
					'<td'.$this->style_td1.'>'.'-'.$name.'-'.'</td>'.
					'<td'.$this->style_td3.'>'.$value.'</td>'.
				'</tr>'.
				'<tr id="'.$id.'"'.$disp.'>'.
					'<td colspan=2>'.
						'<table'.$this->style_table.'>'.
							'<tr>'.
								'<td'.$this->style_text.'>');
		}
		else
		{
			$this->add(
				'<tr>'.
					'<td'.$this->style_td1.'>'.
						'-'.$name.'-'.
					'</td>'.
					'<td>');
		}
	}
	
	private function field_detail_end($value, $full_form = false)
	{
		if(mb_strlen($value) > $this->max_str_len || $full_form)
		{
			$this->add(
								'</td>'.
							'</tr>'.
						'</table>'.
					'</td>'.
				'</tr>');
		}
		else
		{
			$this->add(
					'</td>'.
				'</tr>');
		}
	}
	
	
	private function raw_detail_ins($name, $type, $value, $details)
	{
		if ($details)
		{
			$this->detail_ins_begin($name, $type, $value, $this->open_props);
			$this->add($details);
			$this->detail_ins_end();
		}
		else
		{
			$this->raw_ins($name, $type, $value);
		}
	}
	
	private function raw_ins($name, $type, $value)
	{
		$this->add ('<tr>');
			$this->add('<td'.$this->style_td1.'><b>'.$name.'</b></td>');
			$this->add('<td'.$this->style_td2.'>'.$type.'</td>');
			$this->add('<td'.$this->style_td3.'>'.$value.'</td>');
		$this->add ('</tr>');
	}
	
	private function detail_ins_begin($name, $type, $value='', $open=true)
	{
		$id = rand();
		$disp = $open?'': ' style="display:none;"';
		$this->add ('<tr onclick="hideShowRaw'.$this->func_id.'('.$id.')">');
			$this->add ('<td'.$this->style_td1.'>'.($value?'<b>':'').$name.($value?'</b>':'').'</td>');
			$this->add ('<td'.$this->style_td2.'>'.$type.'</td>');
			$this->add ('<td'.$this->style_td3.'>'.$value.'</td>');
		$this->add ('</tr>');
		$this->add ('<tr id="'.$id.'"'.$disp.'>');//table-row
			$this->add ('<td colspan=3>');
				$this->add ('<table'.$this->style_table.'>');
	}
	
	private function detail_ins_end()
	{
				$this->add ('</table>');
			$this->add ('</td>');
		$this->add ('</tr>');
	}
	
	private function add ($str)
	{
		if (!$this->to_file)
		{			
			$this->res[] = $str;
		}
		else
		{
			//TODO to file
		}
	}
	
	private function out ()
	{
		if (!$this->to_file)
		{			
			foreach ($this->res as $str)
			{
				echo $str;
			}
		}
		else
		{
			//TODO to file
		}
	}
}

