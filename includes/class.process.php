<?php


class process_certificate_pattern{

	var $format;
	var $certificate_code;
	function __construct(){
		$this->certificate_code = '';
		$this->fetch_format();
		add_action('bp_activity_add',array($this,'grab_certificate'),10,2);
		add_action('wp_ajax_process_code_all',array($this,'process_code_all'));
	}

	function fetch_format(){
		$values = get_option(WPLMS_CERTIFICATE_CODES);
		$this->format = $values['certificate_pattern'];
	}

	function grab_certificate($args,$activity_id){
		if(empty($args['id'])){
            $args['id']=$activity_id;
        }
		switch($args['type']){
			case 'student_certificate':
				$this->process_format($args);
			break;
			case 'bulk_action':
				$string = __('Instructor assigned/removed Certificate/Badges','vibe');

				if($args['action'] === $string){ 
					$this->process_format($args);
				}
			break;
		}
	}

	function process_format($args){

		$course_id = $args['item_id'];
		$user_id = $args['user_id'];
		$activity_id = $args['id'];

		if(!is_numeric($course_id) || !is_numeric($user_id))
		 	return;

		$code = '';
		$this->certificate_code ='';
		global $wpdb,$bp;
		
		$format = $this->format;
		if(!isset($activity_id) && !is_numeric($activity_id)){
		$activity_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$bp->activity->table_name} WHERE component = %s  AND item_id = %d
							AND user_id = %d ORDER BY id DESC LIMIT 0,1",'course', $course_id,$user_id));
		}

		preg_match_all("/{{([a-z]+)}}/", $this->format, $matched);

		if(is_array($matched) && count($matched)){
			foreach($matched[1] as $key=>$match){
				switch($match){
					case 'site':
						$val = get_option('blogname');
					break;
					case 'userid':
						$val = $user_id;
					break;
					case 'courseid':
						$val = $course_id;
					break;
					case 'courseslug':
						$val = get_post_field('post_name', $course_id);
					break;
					case 'instructorid':
						$val = get_post_field('post_author', $course_id);
					break;
					case 'month':
						if(!isset($args['recorded_time']) && isset($activity_id) && is_numeric($activity_id)){
						 $recorded_time = $wpdb->get_var($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE id = %d",$activity_id));
						}else{
							$recorded_time = $args['recorded_time'];
						}
						$date =date_parse($recorded_time);
						$val = sprintf("%'.02d",$date['month']);
					break;
					case 'year':
						if(!isset($args['recorded_time']) && isset($activity_id) && is_numeric($activity_id)){
						 	$recorded_time =  $wpdb->get_var($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE id = %d",$activity_id));
						}else{
							$recorded_time = $args['recorded_time'];
						}
						$date =date_parse($recorded_time);
						$val = $date['year'];
					break;
					case 'n':
						$val = $activity_id;
					break;
				}
				
				$format = str_replace($matched[0][$key], $val, $format);
			}
			
			$this->certificate_code = $format;
		}

		$certificate_template = get_post_meta($course_id,'vibe_certificate_template',true);
		if(!isset($certificate_template) || !is_numeric($certificate_template)){
			$certificate_template = vibe_get_option('certificate_page');
		}
		$code = $certificate_template.'-'.$course_id.'-'.$user_id;
		bp_activity_update_meta($activity_id,$code,$this->certificate_code);
	}

	function process_code_all(){
		if ( !isset($_POST['security_nonce']) || !wp_verify_nonce($_POST['security_nonce'],'save_settings') || !current_user_can('manage_options') ){
		    _e('Security check Failed. Contact Administrator.','wplms_custom_certificate_codes');
		    die();
		}

		global $wpdb,$bp;
		$certificate_codes_array = array();
		
		$certificate_codes = $wpdb->get_results($wpdb->prepare("SELECT activity.id as id, activity.user_id as user_id ,activity.item_id as course_id
																FROM {$bp->activity->table_name} as activity  
																WHERE component = %s 
																AND type = %s 
																ORDER BY activity.id DESC",'course','student_certificate'));	

		$codes = $wpdb->get_Results($wpdb->prepare("SELECT activity.id as id, meta.meta_value as user_id ,activity.item_id as course_id
													FROM {$bp->activity->table_name} as activity  LEFT JOIN {$bp->activity->table_name_meta} as meta ON activity.id = meta.activity_id
													WHERE activity.component = %s 
													AND activity.type = %s 
													AND meta.meta_key = %s
													ORDER BY activity.id DESC",'course','bulk_action','add_certificate'));

		if(is_array($certificate_codes) && count($certificate_codes)){
			foreach($certificate_codes as $code){
				$certificate_codes_array[$code->id]=array('id'=>$code->id,'user_id'=>$code->user_id,'course_id'=>$code->course_id);
			}
		}
		if(is_array($codes) && count($codes)){
			foreach($codes as $code){
				$certificate_codes_array[$code->id]=array('id'=>$code->id,'user_id'=>$code->user_id,'course_id'=>$code->course_id);
			}
		}

		if(is_array($certificate_codes_array) && count($certificate_codes_array)){
			foreach($certificate_codes_array as $code){
				$args = array(
					'item_id'=>$code['course_id'],
					'user_id'=>$code['user_id'],
					'id'=>$code['id']
					);
				$this->process_format($args);
			}
			_e('Successfuly updated',PLUGIN_DOMAIN);
			die();
		}
		_e('Unable to Process',PLUGIN_DOMAIN);
		die();
	}
}

new process_certificate_pattern;
