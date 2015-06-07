<?php

	class custom_certificate_code{
		public function __construct(){
			
		add_filter('wplms_certificate_code',array($this,'my_custom_certificate_code'),10,3);
		//Remove Certificate validating codes
		remove_filter('wplms_certificate_code_template_id','wplms_get_template_id_from_certificate_code',10);
		remove_filter('wplms_certificate_code_user_id','wplms_get_user_id_from_certificate_code',15);
		remove_filter('wplms_certificate_code_course_id','wplms_get_course_id_from_certificate_code',20);
		//Add your custom codes
		add_filter('wplms_certificate_code_template_id',array($this,'mywplms_get_template_id_from_certificate_code'),40);
		add_filter('wplms_certificate_code_user_id',array($this,'mywplms_get_user_id_from_certificate_code'),40);
		add_filter('wplms_certificate_code_course_id',array($this,'mywplms_get_course_id_from_certificate_code'),40);
		}

	
			function my_custom_certificate_code($code,$course_id,$user_id){
			 			$uid=$_GET['u'];
			            $cid=$_GET['c'];
			            $blog_title = get_bloginfo('name');
			            if(isset($uid) && is_numeric($uid) && isset($cid) && is_numeric($cid) && get_post_type($cid) == 'course'){
			            	$ctemplate=get_post_meta($cid,'vibe_certificate_template',true);
			            	if(isset($ctemplate) && $ctemplate){
			            		$code =  $blog_title.'|'.$uid.'|'.$cid.'|'.$ctemplate;
			            	}else{
			            		$code =  $blog_title .'|'.$uid.'|'.$cid.'|'.get_the_ID();
			            	}
			            	return $code;
			            }
			            else{
			        		return '[certificate_code]';
			            }
			}
 


 
			function mywplms_get_template_id_from_certificate_code($code){ 
				$code=urldecode($code);
			$codes = explode('|',$code);
			  $template = intval($codes[3]);
			  return $template;
			}
			 
			function mywplms_get_user_id_from_certificate_code($code){
			   $code=urldecode($code);
			  $codes = explode('|',$code);
			  if (isset($codes[1])&& is_numeric($codes[1])){
			  $user_id = intval($codes[1]);
			     return $user_id;
				}
			}
			 
			function mywplms_get_course_id_from_certificate_code($code){
				$code=urldecode($code);
			  $codes = explode('|',$code);
			  if (isset($codes[2])&& is_numeric($codes[2])){
			  $course_id = intval($codes[2]);
			     return $course_id;
				}
			}
			
}
add_action('init','call_custom_certificate_class');
function call_custom_certificate_class(){
	$obj= new custom_certificate_code();
}
