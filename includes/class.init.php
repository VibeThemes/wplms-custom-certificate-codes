<?php


class wplms_custom_certificate_codes{

	var $version;

	function __construct(){
		add_action('wp_ajax_update_certificate_code_meta',array($this,'update_certificate_code_meta'));
		add_filter('wplms_certificate_code',array($this,'my_custom_certificate_code'),10,3);
		//Add your custom codes
		add_filter('wplms_certificate_code_template_id',array($this,'mywplms_get_template_id_from_certificate_code'),5);
		add_filter('wplms_certificate_code_user_id',array($this,'mywplms_get_user_id_from_certificate_code'),5);
		add_filter('wplms_certificate_code_course_id',array($this,'mywplms_get_course_id_from_certificate_code'),5);
	}




	function update_certificate_code_meta(){
		if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'save_settings') ){
		    _e('Security check Failed. Contact Administrator.','wplms_custom_certificate_codes');
		    die();
		}
		if(is_numeric($_POST['activity_id'])){
			bp_activity_update_meta($_POST['activity_id'],$_POST['meta_key'],$_POST['meta_value']);
		}
		die();
	}

	function my_custom_certificate_code($code,$course_id,$user_id){
	 
	 global $wpdb,$bp;
	 $activity_meta_table = $wpdb->prefix.'bp_activity_meta';
	 $certificate_code = $wpdb->get_var($wpdb->prepare("SELECT meta.meta_value 
														FROM {$bp->activity->table_name} as activity LEFT JOIN  {$activity_meta_table} as meta
														ON activity.id = meta.activity_id
														WHERE activity.component = %s 
														AND activity.type = %s
														AND activity.item_id = %d
														AND activity.user_id = %d",'course','student_certificate',$course_id,$user_id));
	
	if(isset($certificate_code)) 
		return $certificate_code;

	return $code;
	}

	function mywplms_get_template_id_from_certificate_code($code){
		global $wpdb;
		$activity_meta_table = $wpdb->prefix.'bp_activity_meta';
		$certificate_code = $wpdb->get_var($wpdb->prepare("SELECT meta_key
																FROM {$activity_meta_table}
																WHERE meta_value = %s",$code));		
		if(isset($certificate_code))
			return $certificate_code;
		else
			return $code;
	}
	 
	function mywplms_get_user_id_from_certificate_code($code){
		global $wpdb;
		$activity_meta_table = $wpdb->prefix.'bp_activity_meta';
		$certificate_code = $wpdb->get_var($wpdb->prepare("SELECT meta_key
																FROM {$activity_meta_table}
																WHERE meta_value = %s",$code));	

	    if(isset($certificate_code))
			return $certificate_code;
		else
			return $code;
	}
	 
	function mywplms_get_course_id_from_certificate_code($code){
		global $wpdb;
		$activity_meta_table = $wpdb->prefix.'bp_activity_meta';
		$certificate_code = $wpdb->get_var($wpdb->prepare("SELECT meta_key
																FROM {$activity_meta_table}
																WHERE meta_value = %s",$code));	

	    if(isset($certificate_code))
			return $certificate_code;
		else
			return $code;
	}

}


add_action('init','define_wplms_custom_certificate_codes');
function define_wplms_custom_certificate_codes(){
	new wplms_custom_certificate_codes;
}
