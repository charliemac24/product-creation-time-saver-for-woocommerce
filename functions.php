<?php

if( !function_exists('pcts_register_submenu_page') ){

	function pcts_register_submenu_page(){
		add_submenu_page(
            'edit.php?post_type=product',
            'Product Creation Time Saver Template',
            'Product Creation Time Saver Template',
            'manage_options',
            'product-creation-time-saver-for-woocommerce',
            'pcts_load_template'
    	);
	}
	add_action( 'admin_menu', 'pcts_register_submenu_page' );
}

if( !function_exists('pcts_load_template') ){

	function pcts_load_template(){
		
		// allowed sub pages
		$sub_pages = array(
			'pcts-create-template',
			'pcts-update-template'
		);
		
		// check if sub page is set and in allowed sub pages
		$sub_page = (isset($_GET['sub_page']) && in_array($_GET['sub_page'],$sub_pages) )?$_GET['sub_page']:"";
		
		// if empty, check if has template or not , then redirect 
		if(empty($sub_page)) {

			pcts_redirect_to_update_if_has_template();

			include_once(PCTS_PLUGIN_DIR.'/pcts-create-template.php');

		}else{ // if sub page is set
	
			if($sub_page == 'pcts-create-template'){
				pcts_redirect_to_update_if_has_template();				
			}elseif($sub_page == 'pcts-update-template'){
				pcts_redirect_to_create_if_has_no_template();
			}
			
	
			include_once(PCTS_PLUGIN_DIR.'/'.$sub_page.'.php');
		}
		
	}

}

if( !function_exists('pcts_redirect_to_update_if_has_template') ){
	function pcts_redirect_to_update_if_has_template(){

		global $wpdb;
		if(pcts_count_template() > 0){

			// need to get the id before redirect
			$results = $wpdb->get_results("SELECT ID FROM ".PCTS_TEMPLATES_TBL,ARRAY_A);			
			wp_redirect(admin_url("edit.php?post_type=product&page=product-creation-time-saver-for-woocommerce&sub_page=pcts-update-template&pcts_template_id=".$results[0]['ID']));
			exit();
		}
	}
}

if( !function_exists('pcts_redirect_to_create_if_has_no_template') ){
	function pcts_redirect_to_create_if_has_no_template(){

		if(pcts_count_template() == 0){
			//redirect		
			wp_redirect(admin_url("edit.php?post_type=product&page=product-creation-time-saver-for-woocommerce&sub_page=pcts-create-template"));
			exit();
		}
	}
}

if( !function_exists('pcts_redirect_to_create') ){
	function pcts_redirect_to_create(){

		wp_redirect(admin_url("edit.php?post_type=product&page=product-creation-time-saver-for-woocommerce&sub_page=pcts-create-template"));
		exit();
	}
}

if( !function_exists('pcts_count_template') ){

	function pcts_count_template(){

		global $wpdb;

		$count = $wpdb->get_var("SELECT COUNT(*) FROM ".PCTS_TEMPLATES_TBL);

		return $count;
	}
}


if( !function_exists('pcts_admin_notice__error') ){

	function pcts_admin_notice__error($error) {

	    $class = 'notice notice-error is-dismissible';
	    $message = __( $error, 'product-creation-time-saver-for-woocommerce' );	 	
	 	
	 	return '<div class="notice notice-error is-dismissible"><p>'.esc_html( $message ).'</p></div>';
	    
	}
	add_action( 'admin_notices', 'pcts_admin_notice__error' );
}

if( !function_exists('pcts_admin_notice__success') ){

	function pcts_admin_notice__success($success) {

	    $message = __( $success, 'product-creation-time-saver-for-woocommerce' );
	 	
	 	return '<div class="notice notice-success is-dismissible"><p>'.esc_html( $message ).'</p></div>';
	    
	}
	add_action( 'admin_notices', 'pcts_admin_notice__success' );
}

add_action( 'admin_head', function(){
	echo "
	<style type='text/css'>
		.pcts-field,.pcts-field-group {
			display:block;
		}
		.pcts-field-group{
			margin-top: 15px;
		}
		.pcts-field{
			margin-bottom:20px;
		}
		.pcts_template_name{
			padding: 6px;
    		width: 30%;
		}
		.pcts-field label{
			font-size:16px;
			font-weight:500;
		}
		.pcts-field p{
			margin:5px;
		}
		.pcts_purchase_note,.pcts_short_description{
			height: 140px;
    		width: 30%;
		}
	</style>
	";
});


// template values for add screen
add_action('admin_footer',function(){

	global $wpdb,$pagenow;

	$template_id = (isset($_GET['pcts_template_id'])&&is_numeric($_GET['pcts_template_id'])) ? $_GET['pcts_template_id'] : "";

	$pcts_templates = $wpdb->get_results("SELECT * FROM ".PCTS_TEMPLATES_TBL,ARRAY_A);

	if(empty($pcts_templates) || empty($template_id))
		return;
	
	if($pagenow == 'post-new.php' && get_post_type() == 'product'){
		
		$sql = "SELECT * FROM ".PCTS_TEMPLATES_TBL." WHERE ID = %d";
		$pcts_options = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				$template_id
			),ARRAY_A
		);
		if(!empty($pcts_options[0]['template_options'])){

			$pcts_options_array = unserialize($pcts_options[0]['template_options']);

			// data
			$cats = $pcts_options_array['pcts_cats'];
			$tags = $pcts_options_array['pcts_tags'];
			$type = $pcts_options_array['pcts_product_type'];
			$virtual = $pcts_options_array['pcts_virtual'];
			$downloadable = $pcts_options_array['pcts_downloadable'];
			$short_description = $pcts_options_array['pcts_short_description'];
			$purchase_note = $pcts_options_array['pcts_purchase_note'];

			$output = "";

			// categories
			$product_cats_html = "";
			foreach($cats as $cat){
				$product_cats_html .= "$('#in-product_cat-".$cat."').prop('checked', true);\n";
			}

			// tags
			$product_tags_html = "";
			if(!empty($tags)){
				$product_tags_html .= "$('#new-tag-product_tag').val('".implode(',',$tags)."');\n";
				$product_tags_html .= "setTimeout(function(){
					$('.tagadd').trigger('click');
				},1000);";
			}
			
			// type
			$product_type_html = "";
			if(!empty($type)){
				$product_type_html .= "$('#product-type').val('".$type."').trigger('change');\n";
			}
			
			// virtual
			$virtual_html = "";
			if($virtual) {
				$virtual_html .= "$('#_virtual').trigger('click');\n";
			}

			// downloadable
			$downloadable_html = "";
			if($downloadable) {
				$downloadable_html .= "$('#_downloadable').trigger('click');\n";
			}

			// short description
			$short_description_html = "";
			if($short_description){
				//$short_description_html .= "tinymce.activeEditor.setContent('".$short_description."');"; : this was included in version 1.0
				$short_description_html .= "$('#tinymce[data-id=excerpt]').html('".$short_description."');\n";
				$short_description_html .= "$('#excerpt').html('".$short_description."');\n";
			}

			// purchase_note
			$purchase_note_html = "";
			if($purchase_note){
				$purchase_note_html .= "$('#_purchase_note').val('".$purchase_note."');\n";
			}

			// javascript
			$output .= "<script>
				jQuery(document).ready(function($){";	
			$output .= $product_cats_html;
			$output .= $product_tags_html;
			$output .= $product_type_html;
			$output .= $virtual_html;
			$output .= $downloadable_html;
			$output .= $short_description_html;
			$output .= $purchase_note_html;
			$output .= "});
			</script>";

			echo $output;

		}

		
	}

},10);


// the attributes
add_action( 'get_post_metadata', function( $value, $object_id, $meta_key ) {

	global $wpdb,$pagenow;

	$template_id = (isset($_GET['pcts_template_id'])&&is_numeric($_GET['pcts_template_id'])) ? $_GET['pcts_template_id'] : "";

	$pcts_templates = $wpdb->get_results("SELECT * FROM ".PCTS_TEMPLATES_TBL,ARRAY_A);

	if(empty($pcts_templates) || empty($template_id))
		return;
	
	if($pagenow == 'post-new.php' && get_post_type() == 'product'){

		$sql = "SELECT * FROM ".PCTS_TEMPLATES_TBL." WHERE ID = %d";
		$pcts_options = $wpdb->get_results(
			$wpdb->prepare(
				$sql,
				$template_id
			),ARRAY_A
		);
		
		if(!empty($pcts_options[0]['template_options'])){

			$pcts_options_array = unserialize($pcts_options[0]['template_options']);

			if ( '_product_attributes' == $meta_key) {

				$options = array();

				if(!empty( $pcts_options_array['pcts_attrs'])){
					foreach ( $pcts_options_array['pcts_attrs'] as  $idx => $attr_name ) {

						$options[ $attr_name ] = array(
							'name'	=> $attr_name,
							'value'	=> "",
							'position'	=> $idx,
							'is_visible'=> 1,
							'is_variation'	=> 0,
							'is_taxonomy'=> 1,
							);
					}

					$value = array($options);
				}		
				
			}
		}
		
	}

	return $value;

}, 10, 3 );