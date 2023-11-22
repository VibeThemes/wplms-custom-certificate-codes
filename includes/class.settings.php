<?php

class wplms_custom_certificate_codes_settings{

	var $settings;

	public function __construct(){
		$this->settings = get_option(WPLMS_CERTIFICATE_CODES);
		add_options_page(__('Certificate Codes Settings','wplms_custom_certificate_codes'),__('Certificate Codes','wplms_custom_certificate_codes'),'manage_options','wplms_custom_certificate_codes',array($this,'settings'));
		add_action('admin_enqueue_scripts',array($this,'enqueue_admin_scripts'));	
	}

	function enqueue_admin_scripts($hook){
		if ( 'settings_page_wplms_custom_certificate_codes' != $hook ) {
        	return;
    	}
    	wp_enqueue_style( 'wplms_custom_certificate_codes_admin_style', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css' );
    	wp_enqueue_script( 'wplms_custom_certificate_codes_admin_style', plugin_dir_url( __FILE__ ) . '../assets/js/admin.js',array('jquery'),'1.0',true);
	}


	
	function settings(){
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		$this->settings_tabs($tab);
		$this->$tab();
	}
   
	function settings_tabs( $current = 'general' ) {
	    $tabs = array( 
	    		'general' => __('General','wplms_custom_certificate_codes'), 
	    		'codes' => __('Certificate Codes','wplms_custom_certificate_codes'), 
	    		);
	    echo '<div id="icon-themes" class="icon32"><br></div>';
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab$class' href='?page=wplms_custom_certificate_codes&tab=$tab'>$name</a>";

	    }
	    echo '</h2>';
	    if(isset($_POST['save'])){
	    	$this->save();
	    }
	}

	function general(){
		echo '<h3>'.__('WPLMS Certificate Code Settings','wplms_custom_certificate_codes').'</h3>';
	
		$settings=array(
				array(
					'label' => __('Set Global Certificate Code pattern','wplms_custom_certificate_codes'),
					'name' =>'certificate_pattern',
					'type' => 'text',
					'std'=> '',
					'desc' => __('Set Global Certificate Code pattern<br />
						<strong>{{site}}</strong> : Use this to display Site Name<br />
						<strong>{{userid}}</strong> : Use this to display User ID<br />
						<strong>{{courseid}}</strong> : Use this to display Course ID<br />
						<strong>{{courseslug}}</strong> : Use this to display Course Slug<br />
						<strong>{{instructorid}}</strong> : Use this to display Course ID<br />
						<strong>{{month}}</strong> : Use to display month in numeric MM<br />
						<strong>{{year}}</strong> : Use to display year in numeric YYYY<br />
						<strong>{{n}}</strong> : Use to display unique certificate ID<br />
						','wplms_custom_certificate_codes')
				),
				array(
					'label' => __('Execute for Previous Certificates','wplms_custom_certificate_codes'),
					'name' =>'execute_pattern',
					'type' => 'button',
					'std'=> __('Apply to all Certificates','wplms_custom_certificate_codes'),
					'desc' => __('Clicking this button would set the above Certificate Pattern for all previous generated Certificate Code. 
						Executing this would put load on server depending upon number of previously generated certficates. Max-Limit 999 Certificates.
						','wplms_custom_certificate_codes')
				),
			);

		$this->generate_form('general',$settings);
	}



	function codes(){
		echo '<h3>'.__('Certificate Codes','wplms_custom_certificate_codes').'</h3>';
		global $wpdb,$bp;
		$generated_certificate_codes =array();
		$certificate_codes_array = array();	
		$start = 0;
		$num=100;
		if(isset($_GET['p']) && is_numeric($_GET['p'])){
			$start = $_GET['p']*$num;
		}
		
		if(isset($_GET['course']) && is_numeric($_GET['course'])){
			$course_id = intval($_GET['course']);
			$certificate_codes = $wpdb->get_results($wpdb->prepare("SELECT activity.id as id, activity.user_id as user_id ,activity.item_id as course_id
																FROM {$bp->activity->table_name} as activity
																WHERE component = %s 
																AND item_id = %d
																AND type = %s
																ORDER BY activity.id DESC
																LIMIT %d,%d",'course',$course_id,'student_certificate',$start,$num));
			$codes = $wpdb->get_Results($wpdb->prepare("SELECT activity.id as id, meta.meta_value as user_id ,activity.item_id as course_id
														FROM {$bp->activity->table_name} as activity  LEFT JOIN {$bp->activity->table_name_meta} as meta ON activity.id = meta.activity_id
														WHERE component = %s 
														AND item_id = %d
														AND type = %s 
														AND meta.meta_key = %s
														ORDER BY activity.id DESC
														LIMIT %d,%d",'course',$course_id,'add_certificate',$start,$num));
		}else{
			$certificate_codes = $wpdb->get_results($wpdb->prepare("SELECT activity.id as id, activity.user_id as user_id ,activity.item_id as course_id
																FROM {$bp->activity->table_name} as activity  
																WHERE component = %s 
																AND type = %s 
																ORDER BY activity.id DESC
																LIMIT %d,%d",'course','student_certificate',$start,$num));	
			$codes = $wpdb->get_Results($wpdb->prepare("SELECT activity.id as id, meta.meta_value as user_id ,activity.item_id as course_id
														FROM {$bp->activity->table_name} as activity  LEFT JOIN {$bp->activity->table_name_meta} as meta ON activity.id = meta.activity_id
														WHERE activity.component = %s 
														AND activity.type = %s 
														AND meta.meta_key = %s
														ORDER BY activity.id DESC
														LIMIT %d,%d",'course','bulk_action','add_certificate',$start,$num));	
			
			
		}
		
		
		
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


		$unique_codes = array();

		if(is_array($certificate_codes_array) && count($certificate_codes_array)){
			foreach($certificate_codes_array as $code){
				

				$q = $wpdb->prepare("SELECT meta_key,meta_value FROM {$bp->activity->table_name_meta} WHERE activity_id = %d AND meta_key LIKE %s",$code['id'],'%-%');
				$certificate_code = $wpdb->get_row($q);				

				if(isset($certificate_code)){
					if($this->verify_certificate($code['course_id'],$code['user_id'])){
						if(!in_array($certificate_code->meta_key,$unique_codes)){
							$unique_codes[]=$certificate_code->meta_key;
							$generated_certificate_codes[$code['id']] = array($certificate_code->meta_key => $certificate_code->meta_value);
						}
					}
				}else{

					$certificate_template = get_post_meta($code['course_id'],'vibe_certificate_template',true);
					if(!isset($certificate_template) || !is_numeric($certificate_template)){
						$certificate_template = vibe_get_option('certificate_page');
					}
					if($this->verify_certificate($code['course_id'],$code['user_id'])){
						$c_code = $certificate_template.'-'.$code['course_id'].'-'.$code['user_id'];
						if(!in_array($c_code,$unique_codes)){
							$unique_codes[]=$c_code;
							$generated_certificate_codes[$code['id']]=array($c_code => '');
						}
					}
				}
				$code='';
			}	
		}

		$settings=array(
				array(
					'label' => __('Manage Certificate Codes','wplms_custom_certificate_codes'),
					'name' => 'wplms_certificate_codes',
					'type' => 'certificate_codes',
					'std'=> $generated_certificate_codes,
					'desc' => __('some description','wplms_custom_certificate_codes')
				),
			);

		$this->generate_form('general',$settings);
	}


	function verify_certificate($course_id,$user_id){
		$certificates = vibe_sanitize(get_user_meta($user_id,'certificates',false));
		if(is_array($certificates) && count($certificates)){
			if(in_array($course_id,$certificates)){
				return true;
			}
		}
		return false;
	}
	function generate_form($tab,$settings=array()){
		echo '<form method="post">
				<table class="form-table">';
		wp_nonce_field('save_settings','_wpnonce');   

		foreach($settings as $setting ){
			echo '<tr valign="top">';
			global $wpdb,$bp;
			switch($setting['type']){
				case 'textarea':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'" class="chzn-select">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'button':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><a class="button" id="'.$setting['name'].'">'.$setting['std'].'</a><br />
					<span>'.$setting['desc'].'</span></td>';
				break;
				case 'hidden':
					echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
				break;
				case 'certificate_codes':

					$option =  get_option($setting['name']);
					if(!isset($option) || !is_array($option)){
						$option = $setting['std'];
					}
					echo '<input type="text" id="course_id" name="course" placeholder="'.__('Search Course ID',PLUGIN_DOMAIN).'" value="'.(isset($_GET['course'])?$_GET['course']:'').'" />
					<a id="search_course" class="button button-primary">'.__('SEARCH',PLUGIN_DOMAIN).'</a>';
					if (is_array($option) && count($option)){
						foreach($option as $key => $value){

							foreach((array)$value as $k=>$v){
								echo '</tr><tr valign="top"><th scope="row" class="titledesc">'.$k.'</th>
								<td class="forminp"><input type="text" id="'.$key.'" data-key="'.$k.'" value="'.$v.'" />
								<a class="button button-primary update_code" data-key="'.$key.'">Update</a>&nbsp;<a data-key="'.$key.'" class="button delete_code">Delete</a>
								</td>';
							}
						}

						echo '<tr>';
						if(isset($_GET['p']) && $_GET['p']){
							echo '<th><a href="?page=wplms_custom_certificate_codes&tab=codes&p='.($_GET['p']-1).'" class="button">'.__('Previous Page',PLUGIN_DOMAIN).'</a></th>';
							echo '<td><a href="?page=wplms_custom_certificate_codes&tab=codes&p='.($_GET['p']+1).'" class="button">'.__('Next Page',PLUGIN_DOMAIN).'</a><td></tr>';	
						}
						
					}else{
						echo '<div class="error"><p>'.__('No Certificate Codes found',PLUGIN_DOMAIN).'</p></div>';
					}
				break;
				default:
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input update_codet type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		echo '<input type="submit" name="save" '.((!empty($_GET['tab']) && $_GET['tab'] == 'codes')?'style="display:none"':'').' value="'.__('Save Settings','wplms_custom_certificate_codes').'" class="button button-primary" /></form>';
	}


	function save(){
		$none = $_POST['save_settings'];
		if ( !isset($_POST['save']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'save_settings') ){
		    _e('Security check Failed. Contact Administrator.','wplms_custom_certificate_codes');
		    die();
		}
		unset($_POST['_wpnonce']);
		unset($_POST['_wp_http_referer']);
		unset($_POST['save']);

		foreach($_POST as $key => $value){
			$this->settings[$key]=$value;
		}
		update_option(WPLMS_CERTIFICATE_CODES,$this->settings);
	}
}


