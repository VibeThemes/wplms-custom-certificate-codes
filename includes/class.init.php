<?php

//add_action('init','add_garbage_data');
function add_garbage_data(){
echo'@@@';
	add_option('2060-1139-1','ABC1');
	add_option('2060-1260-1','ABC2');
	add_option('wplms_certificate_codes',array('2060-1139-1','2060-1260-1'));
}