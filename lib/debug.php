<?php
if ( function_exists('dump') ) {
	/**
	 * Do not allow WordPress to process emojis when symfony's VarDumper is included due
	 * to incompitability between both libraries. 
	 */
	remove_action( 'wp_head', 'print_emoji_detection_script', 7);
}

if (!function_exists('dump')) :
/**
 * This will be used only when symfony's var-dumper package is not loaded. 
 */
function dump() {
	$args = func_get_args();
	echo "\n<pre>\n";
	if (is_scalar($args[0])) {
		call_user_func_array('var_dump', $args);
	} else {
		foreach ($args as $arg) {
			print_r($arg);
		}
	}

	echo "\n</pre>";
}
endif;

if (!function_exists('dd')) :
/**
 * dump-and-die(dd): helper function that dumps the arguments and terminates the script execution. 
 */
function dd() {
	$args = func_get_args();

	call_user_func_array('dump', $args);
	exit;
}
endif;