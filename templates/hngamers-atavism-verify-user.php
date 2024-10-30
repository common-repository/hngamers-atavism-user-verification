<?php /* Template Name: Atavism Verify User */

$username = get_query_var('user');
$password = get_query_var('password');

hngamers_atavism_user_verify_check_subscription_requirements($username, $password);
function hngamers_atavism_user_verify_check_subscription_requirements($usernamePost, $userPassword)
{
	$options = get_option('hngamers_atavism_user_verify_plugin_options');
	$allowlist = preg_split ("/\,/", $options['atavism_loginserver_ip']); 
	
	if (!in_array($_SERVER['REMOTE_ADDR'], $allowlist)) {
		return;
	}
	
	$subscribers_only = $options['subscribers_only'];
	if($subscribers_only == 2)
	{
		hngamers_pmpro_integration($usernamePost, $userPassword);	
	}
	else 
	{
		hngamers_atavism_user_verify_check_wordpress_user($usernamePost, $userPassword);	
	}		
}

function VerifyWordPressUser($usernamePost)
{
	$user = null;
	$options = get_option('hngamers_atavism_user_verify_plugin_options');
	
	if($options['email_login'] == 2)
	{
		if (email_exists($usernamePost)) {	
			$user = get_user_by( 'email', $usernamePost );
		}
	}
	else {	
		if (username_exists($usernamePost)) {	
			$user = get_user_by( 'login', $usernamePost );
		}
	}
	
	if($user)
	{
		return true;
	}

	return false;
}

function ReturnWordPressUser($usernamePost)
{
//https://developer.wordpress.org/reference/functions/get_user_by/
//https://developer.wordpress.org/reference/functions/email_exists/
//https://developer.wordpress.org/reference/functions/username_exists/
	$user = null;	
	$options = get_option('hngamers_atavism_user_verify_plugin_options');
	
	if($options['email_login'] == 2)
	{
		if (email_exists($usernamePost)) {	
			$user = get_user_by( 'email', $usernamePost );
		}
	}
	else {	
		if (username_exists($usernamePost)) {	
			$user = get_user_by( 'login', $usernamePost );
		}
	}
	
	if($user)
	{
		return $user;
	}

	return null;
}

function hngamers_atavism_user_verify_check_mysql_error($str) {
    die("ERROR: ".$str);
}

function hngamers_atavism_user_verify_check_wordpress_user($usernamePost, $userPassword) {
	//https://developer.wordpress.org/reference/functions/get_option/
	$options = get_option('hngamers_core_options');
	$mysqli_conn = new mysqli(
		$options[ 'hngamers_atavism_master_db_hostname_string' ],
		$options[ 'hngamers_atavism_master_db_user_string' ],
		$options[ 'hngamers_atavism_master_db_pass_string' ],
		$options[ 'hngamers_atavism_master_db_schema_string' ],
		$options[ 'hngamers_atavism_master_db_port_string' ]
	) or hngamers_atavism_user_verify_check_mysql_error(mysqli_error($mysqli_conn));

	if (VerifyWordPressUser($usernamePost)) {	
		$user = ReturnWordPressUser($usernamePost);
		if ($user) {
			$id = strval($user->ID);
			if (wp_check_password($userPassword, $user->data->user_pass, $id))
			{				
				$sql = "SELECT status FROM account WHERE id = '$id'";
				$result = $mysqli_conn->query( $sql );

				if(mysqli_num_rows($result) >= 1 ) {
					foreach ($result as $data) {
						if ( empty( $data['status'] ) ) {
							// banned
							echo(esc_html( '-2' ));
						} else {
							// return the users ID
							echo(esc_html(trim($user->ID)));
						}
					}
				} else
				{
					// return the users ID
					echo(esc_html(trim($user->ID)));
				}	
			}
			else {
				echo(esc_html( '-1' ));
			} 
		}
		else
		{
			echo(esc_html( '-3' ));
		}
	}
	else
	{
		echo(esc_html( '-3' ));
	}
}

function hngamers_pmpro_integration($usernamePost, $userPassword) {
	$options = get_option('hngamers_atavism_user_verify_plugin_options');
	$required_pmpro_level = $options['pmp_subscription_id'];
	$pieces = explode(",", $required_pmpro_level);

	if ( function_exists( 'pmpro_getMembershipLevelForUser' ))
	{
		$user = get_user_by( 'login', $usernamePost );
		$id = strval($user->ID);
		if ( empty( $id ) ) {
			echo(esc_html( '-1, user not found' ));
		}
		if (wp_check_password($userPassword, $user->data->user_pass, $id))
		{				
			$membership_level = pmpro_getMembershipLevelForUser( $user->ID );
			if ( empty( $membership_level ) ) {
				echo(esc_html( '-1,  no subscription' ));
			} else {
				$subscription = 0;
				foreach ($pieces as &$value) {
					if ( $membership_level->ID == $value)
					{
						hngamers_atavism_user_verify_check_wordpress_user($usernamePost, $userPassword);
						$subscription = 1;
						break;
					}
				}
				if ($subscription == 0)
				{
					echo(esc_html( '-1,  no subscription.' ));		
				}
			}
		}
		else 
		{
			echo(esc_html( '-1,  wrong pass' ));
		} 
	}
	else
	{
		echo(esc_html( '-1, required plugin not found' ));
	}
}