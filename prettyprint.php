<?php
//это старая версия. новая - pp.php
 //TODO метод вывода (память/страница), парсинг print_r, приватные методы?, уровень рекурсии, прогрессбар(?), 
 //парсинг настроек типа a:2:{s:4:"view";a:2:{s:6:"string";s:23:"Записи блога";s:4:"link";O:10:"moodle_url":9:{s:9:"*scheme";s:4:"http";s:7:"*host";s:13:"81.88.218.114";s:7:"*port";i:8080;s:7:"*user";s:0:"";s:7:"*pass";s:0:"";s:7:"*path";s:23:"/moodle2/blog/index.php";s:16:"*slashargument";s:0:"";s:9:"*anchor";N;s:9:"*params";a:1:{s:6:"userid";s:1:"2";}}}s:3:"add";a:2:{s:6:"string";s:29:"Добавить запись";s:4:"link";O:10:"moodle_url":9:{s:9:"*scheme";s:4:"http";s:7:"*host";s:13:"81.88.218.114";s:7:"*port";i:8080;s:7:"*user";s:0:"";s:7:"*pass";s:0:"";s:7:"*path";s:22:"/moodle2/blog/edit.php";s:16:"*slashargument";s:0:"";s:9:"*anchor";N;s:9:"*params";a:1:{s:6:"action";s:3:"add";}}}}
 //единый массив параметров - класс, свойства, конструктор ну и прочее
 //проверка на представление строки в виде числа
 //тип (DEC/OCT + количество разрядов)
 //при разворачиваниии вертикальная полоса слева
 //по умолчанию верхний класс протектед - виден верхний слой (?)
 //приведение к десятичным числам для недесятичных
 //добавить сброс стиля строки
 //time not timezone, sec/millisec
 //начало и конец работы - если отличаются, время построения
 //рамка - <fieldset>    <legend>Заголовок</legend> текст  </fieldset>
 //прятать все дополнительные варианты информации под "опции", в зависимости от количесвтва (>1)
 

function pretty_print_orig($in = null, $opened = false, $to_file = false, $var_dump = false, $show_all = false)
{
	$string_to_print = '<pre><div style="border: 1px solid red; padding: 5px">';
	if (!isset($in))
	{
		$string_to_print .= '<div><b>-value-</b>: <span style="color:red">-UNSET-</span></div>';
	}
	else
	{
		$opened = $opened?' open':'';

		if (is_object($in))
		{
			$string_to_print .= object_print ($show_all, $in, $opened);
		}
		elseif (is_array($in))
		{
			$string_to_print .= array_print ($show_all, $in, $opened);
		}
		else
		{
			$string_to_print .= value_print (null, $in);
		}
		
		if ($var_dump)
		{
			ob_start();
			var_dump($in);
			$content = ob_get_contents();
			ob_end_clean();
			$string_to_print .= '<details><summary>-var_dump-</summary><div style="margin-left:30px">'.$content.'</div></details>';
		}
	}
	$string_to_print .= '</div></pre>';
  
	if ($to_file)
	{
		$file = 'D:/prj/pretty_print.htm';
		$str_header = '<html><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"></head><body>';
		$str_footer = '</body></html>';
		$string_to_print = $str_header.$string_to_print.$str_footer;
		file_put_contents($file, $string_to_print);
	}
	else
	{
		echo $string_to_print;
	}
}

function pretty_print_rec($show_all = false, $in, $opened = true) //, $rec_lim = 20
{
	// if ($rec_lim > 0)
	// {
		// $rec_lim --;
	// }
	// else
	// {
		// return '<div style="margin-left:30px"><span style="color:red">-RECURSION LIMIT-</span></div>';
	// }
	$str_to_print = '';
	if(!is_object($in) && !is_array($in)) 
	{
		return '';
	}
	$str_to_print .= '<div style="margin-left:30px">';
	foreach($in as $key => $value) //TODO подчёркивать названия разворачиваемых элементов. унифицировать разворачивание. 
	{
		if (is_object($value))
		{
			$str_to_print .= object_print ($show_all, $value, $opened, $key);
		}
		elseif (is_array($value))
		{
			$str_to_print .= array_print ($show_all, $value, $opened, $key);
		}
		else
		{
			$str_to_print .= value_print ($key, $value);			
		}
	}
	$str_to_print .= '</div>';
	return $str_to_print;
}
function value_print ($key = null, $value)
{
	$print_string = '';
	if ($key === null)
	{
		$key = '-scalar value-';
	}
	switch(gettype($value))
	{
		case 'string':
		
			$print_string .= print_the_string ($key, $value);
			break;
		case 'integer':
			$print_string .= '<div><b>'.$key.'</b> (<i>integer</i>): <span style="color:green">'.$value.'</span></div>';
			$print_string .= get_time ($key, $value);
			break;
		case 'double':
			$print_string .= '<div><b>'.$key.'</b> (<i>double</i>): <span style="color:LimeGreen">'.$value.'</span></div>';
			$print_string .= get_time ($key, $value);
			break;
		case 'NULL':
			$print_string .= '<div><b>'.$key.'</b> (<i>NULL</i>): <span style="color:red">-NULL-</span></div>';
			break;
		case 'boolean':
			$print_string .= '<div><b>'.$key.'</b> (<i>boolean</i>): <span style="color:blue">'.(($value === true)?('True'):('False')).'</span></div>';
			break;
		case 'resource':
			$print_string .= print_resource ($key, $value);
			break;
		default:
			$print_string .= '<div><b>'.$key.'</b> (<i>'.gettype($value).'</i>): <span style="color:black">' .$value.'</span></div>';
  }
	return $print_string;
}

function methods_print ($obj)
{
	if (is_object($obj))
	{
		$methods = get_class_methods(get_class($obj));
		$c_m = count($methods);
		if ($c_m > 0)
		{
			return '<div style="margin-left:30px"><details><summary>-class methods- ('.$c_m.')</summary>'.pretty_print_rec(false,$methods).'</details></div>';
		}
	}
	return '';
}

function get_time ($key, $value) //TODO: не только если есть time, но и для диапазона дат от 2000 до текущ+ 10 лет
{
	if (strpos(mb_strtolower($key), 'time')=== false)
		return '';
	$time = false;
	try 
	{
		$time = date("Y.m.d H:i:s", (int)$value); 
	}
	catch (Exception $e)
	{
		return '';
	}
	if ($time !== false)
	{
		return '<div style="margin-left:30px">time: '.$time.'</div>';
	}
	else
	{
		return '';
	}
}

function object_print ($show_all = false, $obj, $opened, $name = '-object-')
{
	if (is_object($obj))
	{
		$text_obj = '';
		$all_f = count((array)$obj);
		$open_f = 0;
		foreach ($obj as $key => $value)
		{
			$open_f++;
		}
		$methods = methods_print ($obj);
		$details = ($all_f > 0 || $methods != '')?true:false;
				
		$an_array = array();
		if ($open_f < $all_f && $show_all)
		{
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
				$status = ($status == '')?'':' {'.$status.'}';
				$an_array[$property->getName().$status] = $property->getValue($obj);
				if (!$property->isPublic())
					$property->setAccessible(false);
			}
			unset($reflection);
		}
		$text_obj .= $details? '<details'.$opened.'><summary>':'';
		$text_obj .= '<b>'.$name.'</b>'.'(['.$open_f.'/'.$all_f.']<i>object of <u>'.get_class($obj).'</u></i>)';
		$text_obj .= $details? '</summary>':'';
		$text_obj .= $methods;

		if (count($an_array)>0)
		{
			$text_obj .= pretty_print_rec($show_all, $an_array, $opened);
		}
		else
		{
			$text_obj .= pretty_print_rec($show_all, $obj, $opened);
		}
		$text_obj .= $details? '</details>':'';
		return $text_obj;
	}
	else
	{
		return '';
	}
}
function array_print ($show_all = false, $arr, $opened, $name = '-array-')
{
	if (is_array($arr))
	{
		$text_arr = '';
		$all_f = count($arr);
		$details = ($all_f > 0)?true:false;
		
		$text_arr .= $details? '<details'.$opened.'><summary>':'';
		$text_arr .= '<b>'.$name.'</b>'.'(['.$all_f.']<i>array</i>)';
		$text_arr .= $details? '</summary>':'';
		$text_arr .= pretty_print_rec($show_all, $arr, $opened);
		$text_arr .= $details? '</details>':'';
		return $text_arr;
	}
	else
	{
		return '';
	}
}

function print_the_string ($key, $value)
{
	$text_string = '';
	$text_string .= '<div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><b>'.$key.'</b> (['.mb_strlen($value).']<i>string</i>): &#171<span style="color:magenta">'.strip_tags($value).'</span>&#187 </div>';
	if (mb_strlen($value) > 100)
	{
		$text_string .= '<div style="margin-left:15px"><details><summary>-all string-</summary><div style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all">'.$value.'</div></details></div>';
	}
	if (strip_tags($value) !== $value)
	{
		if (mb_strlen(htmlspecialchars($value)) > 0)
		{
			$text_string .= '<div style="margin-left:15px"><details><summary>-string with tags (utf-8)-</summary><div style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all">'.htmlspecialchars($value).'</div></details></div>';
		}
		elseif (mb_strlen(htmlspecialchars($value, null, "windows-1251")) > 0)
		{
			$text_string .= '<div style="margin-left:15px"><details><summary>-string with tags(windows-1251)-</summary><div style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all">'.htmlspecialchars($value, null, "windows-1251").'</div></details></div>';
		}
		else
		{
			$text_string .= '<div style="margin-left:30px"><i>Unrecognized encoding!</i></div>';
		}
		$text_string .= '<div style="margin-left:15px"><details><summary>-string with decoded tags-</summary><div style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all">'.htmlspecialchars_decode($value, ENT_QUOTES).'</div></details></div>';
	} 
	if (urldecode($value) !== $value)
	{
		$temp = $value;
		while (urldecode($temp) !== $temp)
		{
			$temp = urldecode($temp);
		}
		$text_string .= '<div style="margin-left:15px"><details><summary>-url decode-</summary><div style="margin-left:30px; white-space:pre-wrap; text-indent: 20px; word-break:break-all">'.$temp.'</div></details></div>';
	}
	if (strlen($value) > 2 && $value{0} === '{' && $value{strlen($value) - 1} === '}')
	{
		$text_string .= '<div style="margin-left:15px"><details><summary>json decode</summary>'.pretty_print_rec(false, json_decode($value, true), false).'</details></div>';//json_decode($value, true);
	}
	if (filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED))
	{
		$text_string .= '<div style="margin-left:15px"><a href="'.$value.'" target="_blank">-open url-</a></div>';
	}
	if (filter_var($value, FILTER_VALIDATE_IP)) 
	{
		$text_string .= '<div style="margin-left:15px"><a href="https://whatismyipaddress.com/ip/'.$value.'" target="_blank">-open whois-</a></div>';
	}
	$text_string .= get_time ($key, $value);

	return $text_string;
}

function print_resource ($key, $value)
{
	$text_resource = '';
	$res_type = get_resource_type($value);
	$text_resource .= '<div><b>'.$key.'</b> (<i>resource:'.$res_type.'</i>): <span style="color:grey">'.$value.'</span></div>';
	if ($res_type == 'curl')
	{
		$curl_info = curl_getinfo ($value);
		if ($curl_info)
		{
			$text_resource .= '<div style="margin-left:15px"><details><summary>-curl info-</summary>'.pretty_print_rec(false, $curl_info, false).'</details></div>';
		}
	} else if ($res_type == 'OpenSSL key')
	{
		$key_info = openssl_pkey_get_details ($value);
		if ($key_info)
		{
			$text_resource .= '<div style="margin-left:15px"><details><summary>-key info-</summary>'.pretty_print_rec(false, $key_info, false).'</details></div>';
		}
	}
	return $text_resource;
}