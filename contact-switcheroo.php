<?php
/*
Plugin Name: Contact Switcheroo
Plugin URI: https://wordpress.org/plugins/contact-switcheroo/
Description: This plugin will allow you to change the contact number frequently. For a specific time duration contact number is changed.
Author: Hiren Wadhiya
Author URI: https://en.gravatar.com/hirenwadhiya
Version: 1.0
 */

$user_agent = $_SERVER['HTTP_USER_AGENT'];

function change_con_num_init(){
    register_setting('change_con_num_options','change_con_num');
}
add_action('admin_init','change_con_num_init');

function change_con_num_option_page(){
    ?>
        <div class="change_con_num">
            <h2>Contact Switcheroo Option</h2><br/>
            <form action="" method="post">
                <h4>Set time duration for Business Hours</h4>
                <span>FROM</span> <input type="time" id="fromtime" name="fromtime" >
                <span>TO</span> <input type="time" id="totime" name="totime" >

                <h4>Contact Number during Business Hours</h4>
                <input type="tel" id="number1" name="number1" placeholder="Number A" />

                <h4>Contact Number during Normal Hours</h4>
                <input type="tel" id="number2" name="number2" placeholder="Number B" />

                <h4>Select your Timezone</h4>
                <?php include("timezone.php"); ?>

                <?php submit_button( 'Submit' ); ?>
            </form>
        </div>
    <?php
}

function insert_into_database($atts){
    global $wpdb;
    $table_name = $wpdb->prefix . "con_num";
    if($wpdb->get_var('SHOW TABLES LIKE '.$table_name) != $table_name){

        $query = 'CREATE TABLE ' .$table_name.' (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fromtime TIME NOT NULL,
        totime TIME NOT NULL,
        business BIGINT(20) NOT NULL,
        normal BIGINT(20) NOT NULL,
        timezone VARCHAR(100) );';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $query );

        if (!defined('CHANGE_CON_NUM_VERSION_KEY'))
        define('CHANGE_CON_NUM_VERSION_KEY', 'change_con_num_version');

        if (!defined('CHANGE_CON_NUM_VERSION_NUM'))
        define('CHANGE_CON_NUM_VERSION_NUM', '1.0.0');

        update_option('CHANGE_CON_NUM_VERSION_KEY', 'CHANGE_CON_NUM_VERSION_NUM');

        $new_version = '1.0.0';

        if (get_option('CHANGE_CON_NUM_VERSION_KEY') != $new_version) {
            // Execute your upgrade logic here

            // Then update the version value
            update_option('CHANGE_CON_NUM_VERSION_KEY', $new_version);
        }
    }

    $business = (INT) $_REQUEST['number1'];
    $normal = (INT) $_REQUEST['number2'];
    if(($business > 0) and ($normal > 0)){
        $wpdb->insert( $table_name, array(
        'fromtime' => $_REQUEST['fromtime'] ,
        'totime' => $_REQUEST['totime'] ,
        'business' => $_REQUEST['number1'] ,
        'normal' => $_REQUEST['number2'] ,
        'timezone' => $_REQUEST['timezone']), array( '%s','%s','%s','%s','%s' ) );
    }

    $last = $wpdb->get_row('SHOW TABLE STATUS LIKE "'.$table_name.'"');
    $lastid = $last->Auto_increment-1;

    $business_number = $wpdb->get_var('SELECT business FROM ' .$table_name. ' WHERE id = ' .$lastid);
    $normal_number = $wpdb->get_var('SELECT normal FROM ' .$table_name. ' WHERE id = ' .$lastid);

    $number = shortcode_atts( array(
        'business' => $business_number,
        'normal' => $normal_number,
    ), $atts );

    $last_fromtime = $wpdb->get_var('SELECT fromtime FROM '.$table_name.' WHERE id = ' .$lastid);
    $last_totime = $wpdb->get_var('SELECT totime FROM '.$table_name.' WHERE id = ' .$lastid);
    $get_curr_tz = $wpdb->get_var('SELECT timezone FROM '.$table_name.' WHERE id = ' .$lastid);
    $curr_tz = date_default_timezone_set($get_curr_tz);
    $current_time = date('H:i:s');

    if ($current_time >= $last_fromtime && $current_time <= $last_totime) {
        return "Contact No: {$number['business']}";
    }else {
        return "Contact No: {$number['normal']}";
    }
}
add_action('admin_menu','insert_into_database');
add_shortcode('contactnumber','insert_into_database');

function change_con_num_plugin_menu(){
    add_options_page('Contact Switcheroo Settings','Contact Switcheroo','manage_options','change_con_num_plugin','change_con_num_option_page');
}
add_action('admin_menu','change_con_num_plugin_menu');
