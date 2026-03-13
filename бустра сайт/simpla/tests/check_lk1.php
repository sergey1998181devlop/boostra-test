<?PHP

	require_once('../../api/Simpla.php');
	$simpla = new Simpla();

	$user = $simpla->users->get_user(807);

	print_r($user);

	print_r('<br/><br/>');

	print_r($simpla->settings->api_password);

	print_r('<br/><br/>');

	