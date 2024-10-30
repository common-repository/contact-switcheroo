<?php
	
		if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
		    exit();

		$option_name = 'change_con_num_init';

		delete_option( $option_name );

		// For site options in multisite
		delete_site_option( $option_name );

		//if uninstall not called from WordPress exit

	 	//drop a custom db table
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}con_num" );

		//note in multisite looping through blogs to delete options on each blog does not scale. You'll just have to leave them.	
?>