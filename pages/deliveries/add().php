<?php 
$projects = DB::select('select `id`,`name`,`customer_id` from `projects` WHERE `tenant_id` = ? and `active` ORDER BY name', $_SESSION['user']['tenant_id']);
$customers = DB::selectPairs('select `id`,`name` from `customers` WHERE `tenant_id` = ? ORDER BY name', $_SESSION['user']['tenant_id']);
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;

	if ($data['deliveries']['add_customer']) {
		$data['deliveries']['customer_id'] = DB::insert('INSERT INTO `customers` (`tenant_id`, `name`) VALUES (?, ?)', $_SESSION['user']['tenant_id'], $data['deliveries']['add_customer']);
		$data['deliveries']['add_customer'] = null;
		$customers = DB::selectPairs('select `id`,`name` from `customers`  WHERE `tenant_id` = ?', $_SESSION['user']['tenant_id']);
	}
	
	//set vat_percentage to NULL if the customer has vat_reverse_charge
	$vat_reverse_charge = DB::selectValue('select `vat_reverse_charge` from `customers` WHERE `tenant_id` = ? AND `id` = ?', $_SESSION['user']['tenant_id'], $data['deliveries']['customer_id']);
	if ($vat_reverse_charge) $data['deliveries']['vat_percentage']=NULL;

	if (!$data['deliveries']['project_id']) $data['deliveries']['project_id']=NULL;
	if (!$data['deliveries']['comment']) $data['deliveries']['comment']=NULL;
	if (!$data['deliveries']['date']) $errors['deliveries[date]']='Date not set';	
	if (!$data['deliveries']['subtotal']) $errors['deliveries[subtotal]']='Subtotal not set';	
	if (!$data['deliveries']['vat_percentage'] && !$vat_reverse_charge) $errors['deliveries[vat_percentage]']='VAT percentage not set';	
	if (!$data['deliveries']['customer_id']) $errors['deliveries[customer_id]']='Customer not set';	

	if (!isset($errors)) {
		try {
			if($data['deliveries']['vat_percentage']) $total = $data['deliveries']['subtotal']*((100+$data['deliveries']['vat_percentage'])/100); 
			else $total = $data['deliveries']['subtotal'];

			$template = DB::selectValue('select `invoiceline_template` from `tenants` WHERE `tenant_id` = ?', $_SESSION['user']['tenant_id']);
			$name = InvoiceTemplate::render($template, array('type'=>'delivery', 'delivery'=>$data['deliveries']));
			$invoiceline_id = DB::insert('INSERT INTO `invoicelines` (`tenant_id`, `customer_id`, `type`, `name`, `subtotal`, `vat`, `vat_percentage`, `total`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['user']['tenant_id'], $data['deliveries']['customer_id'], 'delivery', $name, $data['deliveries']['subtotal'], ($total - $data['deliveries']['subtotal']), $data['deliveries']['vat_percentage'], $total);
			$delivery_id = DB::insert('INSERT INTO `deliveries` (`tenant_id`, `customer_id`, `project_id`, `date`, `name`,`subtotal`, `vat_percentage`, `comment`, `invoiceline_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $_SESSION['user']['tenant_id'], $data['deliveries']['customer_id'], $data['deliveries']['project_id'], $data['deliveries']['date'], $data['deliveries']['name'], $data['deliveries']['subtotal'], $data['deliveries']['vat_percentage'], $data['deliveries']['comment'], $invoiceline_id);

			if ($invoiceline_id && $delivery_id) {
				Flash::set('success','deliveries saved');
				Router::redirect('deliveries/index');
			}
			$error = 'deliveries not saved';
		} catch (DBError $e) {
			$error = 'deliveries not saved: '.$e->getMessage();
		}
	}	
} else {
	$data = array('deliveries'=>array(
		'customer_id'=>NULL, 
		'project_id'=>NULL, 
		'date'=>Date("Y-m-d"), 
		'name'=>NULL, 
		'subtotal'=>NULL, 
		'vat_percentage'=>$tenant['tenants']['default_vat_percentage'], 
		'type'=>NULL, 
		'comment'=>NULL,
		'invoiceline_id'=>NULL));
}