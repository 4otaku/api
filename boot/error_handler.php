<?php

if (!DEBUG) {
	function exception_handler($exception) {
// @TODO: notification
	}

	set_exception_handler('exception_handler');

	function shutdown_handler() {
		$error = error_get_last();
		if ($error && ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR)) {
			if (strpos($error['message'], 'Allowed memory size') === 0) {
				ob_end_clean();
// @TODO: notification
			} else {
				ob_end_clean();
// @TODO: notification
			}
		}
	}

	register_shutdown_function('shutdown_handler');
} else {
	if (isset($_GET['phpinfo'])) {
		phpinfo();
		die;
	}
}
