<?php

class wplms_custom_certificate_codes_settings{

	var $settings;

	public function __construct(){
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
					'label' => __('Field','wplms_custom_certificate_codes'),
					'name' =>'security',
					'type' => 'text',
					'std'=> '',
					'desc' => __('some description','wplms_custom_certificate_codes')
				),
			);

		$this->generate_form('general',$settings);
	}

	function codes(){
		echo '<h3>'.__('Certificate Codes','wplms_custom_certificate_codes').'</h3>';
	
		$settings=array(
				array(
					'label' => __('Manage Certificate Codes','wplms_custom_certificate_codes'),
					'name' => 'wplms_certificate_codes',
					'type' => 'certificate_codes',
					'std'=> array(
						'2060-1139-1' => 'ABC1'
						),
					'desc' => __('some description','wplms_custom_certificate_codes')
				),
			);

		$this->generate_form('general',$settings);
	}

	function generate_form($tab,$settings=array()){
		echo '<form method="post">
				<table class="form-table">';
		wp_nonce_field('save_settings','_wpnonce');   
		echo '<ul class="save-settings">';

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
				case 'hidden':
					echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
				break;
				case 'certificate_codes':
					$option =  get_option($setting['name']);
					if(!isset($option) || !is_array($option)){
						$option = $setting['std'];
					}
					foreach($option as $key => $value){
						echo '<label>'.$key.'</label><input type="text" id="'.$key.'" value="'.$value.'" />
						<a class="button update_code" data-key="'.$key.'">Update</a><a data-key="'.$key.'" class="button delete_code">Delete</a>';
					}
				break;
				default:
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><inpu update_codet type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		echo '<input type="submit" name="save" value="'.__('Save Settings','wplms_custom_certificate_codes').'" class="button button-primary" /></form>';
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

		$this->put($this->settings);
	}
}

add_action('admin_menu','init_wplms_custom_certificate_codes_settings',100);
function init_wplms_custom_certificate_codes_settings(){
	new wplms_custom_certificate_codes_settings;	
}
