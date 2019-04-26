<?php 
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;
	if (!isset($errors)) {
		if(!$data['customers']['tax_reverse_charge']) $data['customers']['tax_reverse_charge'] = 0;
		try {
			$id = DB::insert('INSERT INTO `customers` (`tenant_id`, `name`, `email`, `contact`, `address`, `tax_reverse_charge`, `language_id`) VALUES (?, ?, ?, ?, ?, ?, ?)', $_SESSION['user']['tenant_id'], $data['customers']['name'], $data['customers']['email'], $data['customers']['contact'], $data['customers']['address'], $data['customers']['tax_reverse_charge'], $data['customers']['language_id']);
			if ($id) {
				Flash::set('success','Customer saved');
				Router::redirect('customers/index');
			}
			$error = 'Customer not saved';
		} catch (DBError $e) {
			$error = 'Customer not saved: '.$e->getMessage();
		}
	}	
} else {
	$data = array('customers'=>array('name'=>NULL, 'email'=>NULL, 'contact'=>NULL, 'address'=>NULL, 'tax_reverse_charge'=>NULL, 'language_id'=>NULL));
	$languages = DB::selectPairs('SELECT `id`,`name` FROM `languages`'); // tenant_id not required
}