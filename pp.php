<?php

function pretty_print ($in = null, $opened = true, $param = array())
{
	//определяем имя вызываемой переменой
	$backtrace = debug_backtrace()[0];
    $fh = fopen($backtrace['file'], 'r');
    $line = 0;
    while (++$line <= $backtrace['line']) {
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
}

class pretty_print_class 
{
	private $var = null;
	private $opened = true;
	private $name = '';
	private $to_file = false;
	private $style_border 	= ' style="border: 1px solid red; padding: 5px"';
	private $style_unset 	= ' style="color:red"';
	private $style_margin 	= ' style="margin-left:30px"';
	private $style_td	 	= ' width=10';
	private $res = array();
	private $filestream = null;
	
	public function __construct($params = array())
	{
		$this->var 		= $params['var'];
		$this->name 	= $params['name'];
		$this->to_file 	= $params['to_file'];
		
	}
	public function pp ()
	{
		$this->add ('<pre><div'.$this->style_border.'>');
		if (!isset($this->var))
		{
			$this->add ('<div><b>-value-</b>: <span'.$style_unset.'>-UNSET-</span></div>');
		}
		else
		{
			if (is_object($this->var))
			{
				$this->object_print ($this->var, $this->name);
			}
			elseif (is_array($this->var))
			{
				$this->array_print ($this->var, $this->name);
			}
			else
			{
				$this->value_print ($this->var, $this->name);
			}
		}
		$this->add('</div></pre>');
	}
	
	private function pp_rec ($in)
	{
		if(!is_object($in) && !is_array($in)) 
		{
			return;
		}
		$this->add ('<div'.$this->style_margin.'>');
		foreach($in as $key => $value) 
		{
			if (is_object($value))
			{
				$this->object_print ($value, $key);
			}
			elseif (is_array($value))
			{
				$this->array_print ($value, $key);
			}
			else
			{
				$this->value_print ($value, $key);			
			}
		}
		$this->add ('</div>');
	}
	
	private function value_print ($value, $key = null)
	{
		if ($key === null)
		{
			$key = '-scalar value-';
		}
		switch(gettype($value))
		{
			case 'string':
				$this->print_string ($value, $key);
				break;
			case 'integer':
				$this->print_int ($value, $key);
				break;
			case 'double':
				$this->print_double ($value, $key);
				break;
			case 'NULL':
				$this->print_null ($key);
				break;
			case 'boolean':
				$this->print_bool ($value, $key);
				break;
			case 'resource':
				$this->print_resource ($value, $key);
				break;
			default:
				$this->print_default ($value, $key);
		}
	}
	
	function print_the_string ($key, $value)
	{
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
	
	private function detail_ins()
	{
		
	}
	
	private function table_ins($name, $type, $value, $data = '')
	{
		$res = 
			'<tr>'.
				'<td'.$this->style_td.'>'.$name.'</td>'.
				'<td'.$this->style_td.'>'.$type.'</td>'.
				'<td'.$this->style_td.'>'.$value.'</td>'.
			'</tr>';
		if ($data != '')
		{
			$res .= '<tr><td colspan=3>'.$data.'</td></tr>';
		}
		return $res;
	}
	
	private function add ($str)
	{
		if (!$this->to_file)
		{			
			$this->res[] = $str;
		}
		else
		{
			//TODO
		}
	}
	
	private function out ()
	{
		if (!$this->to_file)
		{			
			$this->res[] = $str;
		}
		else
		{
			//TODO
		}
	}
	
}

