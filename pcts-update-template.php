<?php

global $wpdb;

$template_id = (isset($_GET['pcts_template_id'])&&is_numeric($_GET['pcts_template_id'])) ? $_GET['pcts_template_id'] : "";

$pcts_options_result = array();

if(!empty($template_id)){
	$pcts_options_result = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM ".PCTS_TEMPLATES_TBL." WHERE ID = %d",
			$template_id
		),ARRAY_A
	);
}

// unserialize
$pcts_options_array = isset($pcts_options_result[0]) ? unserialize($pcts_options_result[0]['template_options']) : array();


if(empty($pcts_options_result)) {
	
	echo pcts_admin_notice__error('Unable to load the data!');
	
} else {

	// data
	$data_template_name = !empty($pcts_options_result[0]['template_name']) ? $pcts_options_result[0]['template_name'] : "";
	$data_cats = !empty($pcts_options_array['pcts_cats'])?$pcts_options_array['pcts_cats']:array();
	$data_attrs = !empty($pcts_options_array['pcts_attrs'])?$pcts_options_array['pcts_attrs']:array();
	$data_tags = !empty($pcts_options_array['pcts_tags'])?$pcts_options_array['pcts_tags']:array();
	$data_virtual = !empty($pcts_options_array['pcts_virtual'])?$pcts_options_array['pcts_virtual']:"";
	$data_product_type = !empty($pcts_options_array['pcts_product_type'])?$pcts_options_array['pcts_product_type']:"";
	$data_downloadable = !empty($pcts_options_array['pcts_downloadable'])?$pcts_options_array['pcts_downloadable']:"";
	$data_short_description = !empty($pcts_options_array['pcts_short_description'])?$pcts_options_array['pcts_short_description']:"";
	$data_purchase_note = !empty($pcts_options_array['pcts_purchase_note'])?$pcts_options_array['pcts_purchase_note']:"";

	if(isset($_POST['submit'])){

		$pcts_options = array();
			
		// post data
		$pcts_template_name = !empty($_POST['pcts_template_name']) ? sanitize_text_field($_POST['pcts_template_name']) : "";
		$pcts_options['pcts_attrs'] = (!empty($_POST['pcts_attrs']) && is_array($_POST['pcts_attrs'])) ? sanitize_post($_POST['pcts_attrs']) : "";
		$pcts_options['pcts_cats'] = (!empty($_POST['pcts_cats']) && is_array($_POST['pcts_cats']))  ? sanitize_post($_POST['pcts_cats']): "";
		$pcts_options['pcts_tags'] = (!empty($_POST['pcts_tags']) && is_array($_POST['pcts_tags'])) ? sanitize_post($_POST['pcts_tags']) : "";
		$pcts_options['pcts_virtual'] = isset($_POST['pcts_virtual'])  ? filter_var($_POST['pcts_virtual'],FILTER_SANITIZE_NUMBER_INT): 0;
		$pcts_options['pcts_product_type'] = !empty($_POST['pcts_product_type']) ? sanitize_text_field($_POST['pcts_product_type']) : "";
		$pcts_options['pcts_downloadable'] = isset($_POST['pcts_downloadable']) ? filter_var($_POST['pcts_downloadable'],FILTER_SANITIZE_NUMBER_INT) : 0;
		$pcts_options['pcts_short_description'] = !empty($_POST['pcts_short_description']) ? sanitize_textarea_field($_POST['pcts_short_description']):"";
		$pcts_options['pcts_purchase_note'] = !empty($_POST['pcts_purchase_note']) ? sanitize_textarea_field($_POST['pcts_purchase_note']):"";
		
		// unset
		foreach($pcts_options as $k=>$opt){
			if(is_array($opt)){
				unset($pcts_options[$k]['ID']);
				unset($pcts_options[$k]['filter']);
			}			
		}
		
		
		// serialize
		$pcts_options_serialized = serialize($pcts_options);
		
		// if template name is empty
		if(empty($pcts_template_name)){
			
			echo pcts_admin_notice__error('Template name is required!');
			
		} else{
			
			$wpdb->update(
				PCTS_TEMPLATES_TBL,
				array(
					'template_name' => $pcts_template_name,
					'template_options' => $pcts_options_serialized
				),
				array(
					'ID' => $template_id
				)
			);

				
			wp_redirect(admin_url("edit.php?post_type=product&page=product-creation-time-saver-for-woocommerce&sub_page=pcts-update-template&pcts_status=success&pcts_template_id=$template_id"));
			exit();
			
		}
	}	
}

// success message
if(isset($_GET['pcts_status']) && $_GET['pcts_status'] == 'success'){
	echo pcts_admin_notice__success('Template successfully saved!');
}


echo "<div class=\"wrap\">";
echo "<h1 class=\"wp-heading-inline\">".esc_html(__('Setup Template','product-creation-time-saver-for-woocommerce'))."</h1>";
echo "<form method=\"POST\">";

// template name
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Template Name','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<input type=\"text\" name=\"pcts_template_name\" class=\"pcts_template_name\" value=\"".(!empty($data_template_name)? esc_attr($data_template_name):"")."\" placeholder=\"".esc_attr(__('Enter Template Name','product-creation-time-saver-for-woocommerce'))."\"/>";
echo "</div>";		

// categories
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Pre-select the categories you want on this template','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<div class=\"pcts-field-group\">";
		$cats = get_categories( 
			array(
				'taxonomy' => 'product_cat', 
				'hide_empty' => false,
				'parent'=>0
			) 
		);		
		foreach($cats as $cat){
			$checked = in_array($cat->term_id, $data_cats) ? "checked":"";
			echo "<p><input type=\"checkbox\" name=\"pcts_cats[]\" value=\"".esc_attr($cat->term_id)."\" ".esc_attr($checked).">&nbsp;".esc_html($cat->name)."</p>";
			$cat_childs = get_categories( 
				array('taxonomy' => 'product_cat', 
					'hide_empty' => false,
					'parent'=>$cat->term_id
				) 
			);
			foreach($cat_childs as $cc){
				$cc_checked = in_array($cc->term_id, $data_cats) ? "checked":"";
				echo "<p style=\"margin-left:20px;\"><input type=\"checkbox\" name=\"pcts_cats[]\" value=\"".esc_attr($cc->term_id)."\" ".esc_attr($cc_checked).">&nbsp;".esc_html($cc->name)."</p>";
			}
		}
echo "</div>";
echo "</div>";


// attributes
$attrs = wc_get_attribute_taxonomies();	
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Pre-select attributes included on this template','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<div class=\"pcts-field-group\">";
		if(empty($attrs)){
			echo "<p>There are no attributes found!</p>";
		} else{
			foreach($attrs as $attr){
				$checked = in_array('pa_'.$attr->attribute_name, $data_attrs) ? "checked":"";
				echo "<p><input type=\"checkbox\" name=\"pcts_attrs[]\" value=\"pa_".esc_attr($attr->attribute_name)."\" ".esc_attr($checked).">&nbsp;".esc_html($attr->attribute_label)."</p>";
			}
		}	
		
echo "</div>";
echo "</div>";

// tags
$tags = get_terms( 
			array(
				'taxonomy' => 'product_tag', 
				'hide_empty' => false
			) 
		);
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Pre-select the tags you want on this template','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<div class=\"pcts-field-group\">";
		if(empty($tags)){
			echo "<p>There are no tags found!</p>";
		} else {
			foreach($tags as $tag){
				$checked = in_array($tag->slug, $data_tags) ? "checked":"";
				echo "<p><input type=\"checkbox\" name=\"pcts_tags[]\" value=\"".esc_attr($tag->slug)."\" ".esc_attr($checked).">&nbsp;".esc_html($tag->name)."</p>";
			}
		}
				
echo "</div>";
echo "</div>";

// product type
$selected = "";
$selected_product_type = "";
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Pre-select the product type','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<div class=\"pcts-field-group\">";
echo "<select name=\"pcts_product_type\" class=\"pcts_product_type\">";
		$product_types = array(
			'simple' => 'Simple product',
			'grouped' => 'Grouped product',
			'external' => 'External/Affiliate product',
			'variable'=>'Variable product'
		);

		foreach($product_types as $slug=>$product_type){
			if($slug==$data_product_type){
				$selected = "selected";
				$selected_product_type = $slug;
			}					
			echo "<option value=\"".esc_attr($slug)."\" ".esc_attr($selected).">&nbsp;".esc_html($product_type)."</option>";
		}
echo "</select>";
echo "</div>";
echo "</div>"; 

// other info


echo "<div class=\"pcts-field pcts-other-info\" style=\"".($selected_product_type == 'simple' ? 'display:block;' : 'display:none;')."\">";
echo "<label>".esc_html(__('Other product info','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<div class=\"pcts-field-group\">";
echo "<p><input type=\"checkbox\" name=\"pcts_virtual\" value=\"1\" ".($data_virtual ? "checked":"").">&nbsp;".__('Virtual','product-creation-time-saver-for-woocommerce')."</p>";
echo "<p><input type=\"checkbox\" name=\"pcts_downloadable\" value=\"1\" ".($data_downloadable ? "checked":"").">&nbsp;".__('Downloadable','product-creation-time-saver-for-woocommerce')."</p>";
echo "</div>";
echo "</div>";

// short description
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Enter Product Short Description','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<p><i>".esc_html(__('Create generic description and just change or inject the keywords when creating product to save time','product-creation-time-saver-for-woocommerce'))."</i></p>";
echo "<div class=\"pcts-field-group\">";
echo "<textarea name=\"pcts_short_description\" class=\"pcts_short_description\">".(!empty($data_short_description)? esc_html($data_short_description):"")."</textarea>";
echo "</div>";

// purchase note
echo "</div>";
echo "<div class=\"pcts-field\">";
echo "<label>".esc_html(__('Enter Purchase Note','product-creation-time-saver-for-woocommerce'))."</label>";
echo "<p><i>". esc_html(__('Create generic note and just change or inject the keywords when creating product to save time','product-creation-time-saver-for-woocommerce'))."</i></p>";
echo "<div class=\"pcts-field-group\">";
echo "<textarea name=\"pcts_purchase_note\" class=\"pcts_purchase_note\">".(!empty($data_purchase_note)?esc_html($data_purchase_note):"")."</textarea>";
echo "</div>";
echo "</div>";

echo "<div class=\"pcts-field\">";
echo "<input type=\"submit\" name=\"submit\" class=\"button-primary\" value=\"".esc_attr(__('Save Template','product-creation-time-saver-for-woocommerce'))."\"/>";
echo "</div>";
echo "</form>";
echo "</div>";

echo "<script>
	jQuery(document).ready(function(){
		jQuery('.pcts_product_type').change(function(){
			var ptype = jQuery(this).val();
			console.log(ptype);
			if(ptype !== 'simple'){
				jQuery('.pcts_virtual').prop('checked', false);
				jQuery('.pcts_downloadable').prop('checked', false);
				jQuery('.pcts-other-info').hide();
			}else{
				jQuery('.pcts-other-info').show();
			}
		});
	});
</script>";
