<?php
echo 'kek';
echo '<br>';
require_once('pp.php');
$heh = array('lala' => 'ночная бабушка Гриша');
$kekich = null;
$v = 'ololo ололо ololo (), (f ase, 8asdf ()) ';
//echo $v,'<br>';

$bibib = new Stdclass();
$bibib->poop = '<p>Абзац</p>';
class nanayna
{
	public $azaza = 1111111111;
	private $zupu = '{1:"1452000000"}';
}


class lalayla 
{
	static private $var = '123.123.32.32';
	static protected $mao = '2e45';
}	


pretty_print($bibib);
pretty_print(new nanayna());


pretty_print($kekich);
pretty_print($heh);
// echo '<pre>';
// var_dump($heh);
// echo '</pre>';
pretty_print($v);
pretty_print(2000000000);
// $st = ' width=10';

// echo 
// '<table border="1" cellpadding="5">', //width="100%"
	// '<tr>',
		// '<td'.$st.'>11</td><td'.$st.'>12</td><td>13</td>',
	// '</tr>',
	// '<tr>',
		// '<td'.$st.'>',
			// '21',
		// '</td>',
		// '<td'.$st.'>',
			// '22',
		// '</td>',
		// '<td>',
			// '23',
		// '</td>',
	// '</tr>',
		// '<td colspan=3>',
			// '<details>',
				// '<summary>',
					// '',
				// '</summary>',
					// '<table border="1" cellpadding="5">',
						// '<tr>',
							// '<td>11</td><td>12</td><td>13</td>',
						// '</tr>',
						// '<tr>',
							// '<td colspan=3>',
								// '<details>',
									// '<summary>',
										// '21',
									// '</summary>',
										// '211 212',
								// '</details>',
							// '</td>',
						// '</tr>',
					// '</table>',
			// '</details>',
		// '</td>',
	// '<tr>',
		// '<td'.$st.'>31</td><td'.$st.'>32</td><td>33</td>',
	// '</tr>',
	// '<tr>',
	// '</tr>',
// '</table>';
// echo 
// '<br><table border="1" cellpadding="5">', //width="100%"
	// '<tr>',
		// '<td'.$st.'>11</td><td'.$st.'>12</td><td>13</td>',
	// '</tr>',
	// '<tr>',
		// '<td'.$st.'>',
			// '21',
		// '</td>',
		// '<td'.$st.'>',
			// '22',
		// '</td>',
		// '<td>',
			// '23',
		// '</td>',
	// '</tr>',
		// '<td colspan=3>',
			// '<details>',
				// '<summary>',
					// '<div style="display:inline;">21<table border="1" cellpadding="5"  style="display:inline;"><tr><td>22</td><td>23</td></tr></table></div>',
				// '</summary>',
					// '<table border="1" cellpadding="5">',
						// '<tr>',
							// '<td'.$st.'>11</td><td'.$st.'>12</td><td>13</td>',
						// '</tr>',
						// '<tr>',
							// '<td colspan=3>',
								// '<details>',
									// '<summary>',
										// '21',
									// '</summary>',
										// '211 212',
								// '</details>',
							// '</td>',
						// '</tr>',
					// '</table>',
			// '</details>',
		// '</td>',
	// '<tr>',
		// '<td'.$st.'>31</td><td'.$st.'>32</td><td>33</td>',
	// '</tr>',
	// '<tr>',
	// '</tr>',
// '</table>';

// echo 
// '<table border="1" cellpadding="5">',
	// '<tr>',
		// '<th'.$st.'>',
			// '1',
		// '</th>',
		// '<th'.$st.'>',
			// '2',
		// '</th>',
		// '<th>',
			// '3',
		// '</th>',
	// '</tr>',
	// '<tr>',
		// '<td'.$st.'>',
			// '11',
		// '</td>',
		// '<td'.$st.'>',
			// '12',
		// '</td>',
		// '<td>',
			// '13',
		// '</td>',
	// '</tr>',
	// '<tr>',
		// '<td'.$st.'>',
			// '21',
		// '</td>',
		// '<td'.$st.'>',
			// '22',
		// '</td>',
		// '<td>',
			// '23',
		// '</td>',
	// '</tr>',
	// '<tr>',
		// '<td  colspan=3>',
			// '31',
		// '</td>',
	// '</tr>';
	// $id = rand();
	// echo
	// '<tr onclick="hideShowRaw('.$id.')">',
		// '<td'.$st.'>',
			// '41',
		// '</td>',
		// '<td'.$st.'>',
			// '42',
		// '</td>',
		// '<td>',
			// '43',
		// '</td>',
	// '</tr>',
	// '<tr id="'.$id.'" style="display:none;">',
		// '<td  colspan=3>',
			// '<table border="1" cellpadding="5">',
				// '<tr>',
					// '<td'.$st.'>',
						// '511',
					// '</td>',
					// '<td'.$st.'>',
						// '512',
					// '</td>',
					// '<td>',
						// '513',
					// '</td>',
				// '</tr>',
			// '</table>',
		// '</td>',
	// '</tr>',
	// '<tr>',
		// '<td'.$st.'>',
			// '61',
		// '</td>',
		// '<td'.$st.'>',
			// '62',
		// '</td>',
		// '<td>',
			// '63',
		// '</td>',
	// '</tr>',
// '</table>';

// echo
// '
// <script>
	// function hideShowRaw(id) {
	  // var x = document.getElementById(id);
	  // if (x.style.display === "none") {
		// x.style.display = "table-row";
	  // } else {
		// x.style.display = "none";
	  // }
	// }
// </script>
 // ';

?>
