<?php 
if ($_SERVER['REQUEST_METHOD']=='POST') {
	$data = $_POST;
	if (!$data['users']['name']) $data['users']['name']=NULL;
	if (!isset($errors)) {
		try {
			$rowsAffected = DB::update('UPDATE `users` SET `name`=? WHERE `tenant_id` = ? AND `id` = ?', $data['users']['name'], $_SESSION['user']['tenant_id'], $id);
			if ($rowsAffected!==false) {
				Flash::set('success','User saved');
				Router::redirect('users/view/'.$id);
			}
			$error = 'User not saved';
		} catch (DBError $e) {
			$error = 'User not saved: '.$e->getMessage();
		}
	}
} else {
	$data = DB::selectOne('SELECT * from `users` WHERE `tenant_id` = ? AND `id` = ?', $_SESSION['user']['tenant_id'], $id);
}