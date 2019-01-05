<?php
echo 'kek';
echo '<br>';
require_once('pp.php');
$v = 'ololo ололо ololo (), (f ase, 8asdf ()) ';
echo $v,'<br>';

pretty_print($v);

echo 
'<table border="1" cellpadding="5">', //width="100%"
	'<tr>',
		'<td width=10>11</td><td width=10>12</td><td>13</td>',
	'</tr>',
	'<tr>',
		'<td colspan=3>',
			'<details>',
				'<summary>',
					'21',
				'</summary>',
					'<table border="1" cellpadding="5">',
						'<tr>',
							'<td>11</td><td>12</td><td>13</td>',
						'</tr>',
						'<tr>',
							'<td colspan=3>',
								'<details>',
									'<summary>',
										'21',
									'</summary>',
										'211 212',
								'</details>',
							'</td>',
						'</tr>',
					'</table>',
			'</details>',
		'</td><td width=10>22</td><td>23</td>',
	'</tr>',
'</table>'


?>
