<?php 
$customers = DB::selectPairs('select `id`,`name` from `customers` WHERE `tenant_id` = ? order by name', $_SESSION['user']['tenant_id']);
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;
	
	if (isset($data['projects']['add_customer']) && $data['projects']['add_customer']) {
		$customer_id = DB::insert('INSERT INTO `customers` (`tenant_id`, `name`) VALUES (?, ?)', $_SESSION['user']['tenant_id'], $data['projects']['add_customer']);
		$customers = DB::selectPairs('select `id`,`name` from `customers`  WHERE `tenant_id` = ?', $_SESSION['user']['tenant_id']);
	} else {
		$customer_id = $data['projects']['customer_id'];
	}

	if (!isset($customers[$customer_id])) $errors['projects[customer_id]']='Customer not found';
	if (!isset($data['projects']['active']) || !$data['projects']['active']) $data['projects']['active'] = 0;

	if (!isset($errors)) {
		try {
			$id = DB::insert('INSERT INTO `projects` (`tenant_id`, `name`, `customer_id`, `default_hourly_fee`, `active`) VALUES (?, ?, ?, ?, ?)', $_SESSION['user']['tenant_id'], $data['projects']['name'], $customer_id, $data['projects']['default_hourly_fee'], $data['projects']['active']);
			if ($id) {
				Flash::set('success','Project saved');
				Router::redirect('projects/index');
			}
			$error = 'Project not saved';
		} catch (DBError $e) {
			$error = 'Project not saved: '.$e->getMessage();
		}
	}
} else {
	$data = array('projects'=>array('name'=>NULL, 'customer_id'=>NULL, 'add_customer'=>NULL, 'default_hourly_fee'=>NULL, 'active'=>1));
}