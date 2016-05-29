<?php 
add_action( 'admin_enqueue_scripts', 'import_epanel_javascript' );
function import_epanel_javascript( $hook_suffix ) {
	if ( 'admin.php' == $hook_suffix && isset( $_GET['import'] ) && isset( $_GET['step'] ) && 'wordpress' == $_GET['import'] && '1' == $_GET['step'] )
		add_action( 'admin_head', 'admin_headhook' );
}

function admin_headhook(){ ?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			$("p.submit").before("<p><input type='checkbox' id='importepanel' name='importepanel' value='1' style='margin-right: 5px;'><label for='importepanel'>Replace ePanel settings with sample data values</label></p>");
		});
	</script>
<?php }

add_action('import_end','importend');
function importend(){
	global $wpdb, $shortname;
	
	#make custom fields image paths point to sampledata/sample_images folder
	$sample_images_postmeta = $wpdb->get_results(
		$wpdb->prepare( "SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE meta_value REGEXP %s", 'http://et_sample_images.com' )
	);
	if ( $sample_images_postmeta ) {
		foreach ( $sample_images_postmeta as $postmeta ){
			$template_dir = get_template_directory_uri();
			if ( is_multisite() ){
				switch_to_blog(1);
				$main_siteurl = site_url();
				restore_current_blog();
				
				$template_dir = $main_siteurl . '/wp-content/themes/' . get_template();
			}
			preg_match( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $postmeta->meta_value, $matches );
			$image_path = $matches[1];
			
			$local_image = preg_replace( '/http:\/\/et_sample_images.com\/([^.]+).jpg/', $template_dir . '/sampledata/sample_images/$1.jpg', $postmeta->meta_value );
			
			$local_image = preg_replace( '/s:55:/', 's:' . strlen( $template_dir . '/sampledata/sample_images/' . $image_path . '.jpg' ) . ':', $local_image );
			
			$wpdb->update( $wpdb->postmeta, array( 'meta_value' => esc_url_raw( $local_image ) ), array( 'meta_id' => $postmeta->meta_id ), array( '%s' ) );
		}
	}

	if ( !isset($_POST['importepanel']) )
		return;
	
	$importOptions = 'YToxMDA6e3M6MDoiIjtOO3M6MTY6InNpbXBsZXByZXNzX2xvZ28iO3M6MDoiIjtzOjE5OiJzaW1wbGVwcmVzc19mYXZpY29uIjtzOjA6IiI7czoyNDoic2ltcGxlcHJlc3NfY29sb3Jfc2NoZW1lIjtzOjc6IkRlZmF1bHQiO3M6MjI6InNpbXBsZXByZXNzX2Jsb2dfc3R5bGUiO047czoyMjoic2ltcGxlcHJlc3NfZ3JhYl9pbWFnZSI7TjtzOjI0OiJzaW1wbGVwcmVzc19jYXRudW1fcG9zdHMiO3M6MToiNiI7czoyODoic2ltcGxlcHJlc3NfYXJjaGl2ZW51bV9wb3N0cyI7czoxOiI1IjtzOjI3OiJzaW1wbGVwcmVzc19zZWFyY2hudW1fcG9zdHMiO3M6MToiNSI7czoyNDoic2ltcGxlcHJlc3NfdGFnbnVtX3Bvc3RzIjtzOjE6IjUiO3M6MjU6InNpbXBsZXByZXNzX2dhbGxlcnlfcG9zdHMiO3M6MjoiMTIiO3M6MjM6InNpbXBsZXByZXNzX2RhdGVfZm9ybWF0IjtzOjY6Ik0gaiwgWSI7czoxOToic2ltcGxlcHJlc3NfZ2FsbGVyeSI7czo5OiJQb3J0Zm9saW8iO3M6MjM6InNpbXBsZXByZXNzX3VzZV9leGNlcnB0IjtOO3M6MjY6InNpbXBsZXByZXNzX2dhbGxlcnlfZW5hYmxlIjtzOjI6Im9uIjtzOjIxOiJzaW1wbGVwcmVzc19zZXJ2aWNlXzEiO3M6ODoiV2hvIEkgQW0iO3M6MjE6InNpbXBsZXByZXNzX3NlcnZpY2VfMiI7czo5OiJXaGF0IEkgRG8iO3M6MjE6InNpbXBsZXByZXNzX3NlcnZpY2VfMyI7czo4OiJXaG8gSSBBbSI7czoyNjoic2ltcGxlcHJlc3NfaG9tZXBhZ2VfcG9zdHMiO3M6MToiNyI7czoyNjoic2ltcGxlcHJlc3NfZXhsY2F0c19yZWNlbnQiO047czoxNzoic2ltcGxlcHJlc3NfcXVvdGUiO3M6Mjoib24iO3M6MjE6InNpbXBsZXByZXNzX3F1b3RlX29uZSI7czo1MDoiUFJPVklESU5HIFRIRSBCRVNUIERFU0lHTiBTRVJWSUNFUyBPTiBUSEUgSU5URVJORVQiO3M6MjE6InNpbXBsZXByZXNzX3F1b3RlX3R3byI7czo4MToid2UgY3JlYXRlIGFtYXppbmdseSBiZWF1dGlmdWwgZGVzaWducyBhdCBhIHByaWNlIHRoYXQgaXMgYWJzb2x1dGVseSB1bmJlbGlldmVhYmxlIjtzOjE3OiJzaW1wbGVwcmVzc19zdHJpcCI7czo1NToiSXRzIHRpbWUgdGhhdCB5b3UgdG9vayB5b3VyIHdlYnNpdGUgdG8gdGhlIG5leHQgbGV2ZWwuICI7czoyMDoic2ltcGxlcHJlc3NfZmVhdHVyZWQiO3M6Mjoib24iO3M6MjE6InNpbXBsZXByZXNzX2R1cGxpY2F0ZSI7TjtzOjIwOiJzaW1wbGVwcmVzc19mZWF0X2NhdCI7czo4OiJGZWF0dXJlZCI7czoyNDoic2ltcGxlcHJlc3NfZmVhdHVyZWRfbnVtIjtzOjE6IjMiO3M6MjE6InNpbXBsZXByZXNzX3VzZV9wYWdlcyI7TjtzOjIyOiJzaW1wbGVwcmVzc19mZWF0X3BhZ2VzIjtOO3M6MjM6InNpbXBsZXByZXNzX3NsaWRlcl9hdXRvIjtOO3M6Mjg6InNpbXBsZXByZXNzX3NsaWRlcl9hdXRvc3BlZWQiO3M6NDoiNDAwMCI7czoyMToic2ltcGxlcHJlc3NfbWVudXBhZ2VzIjtOO3M6Mjg6InNpbXBsZXByZXNzX2VuYWJsZV9kcm9wZG93bnMiO3M6Mjoib24iO3M6MjE6InNpbXBsZXByZXNzX2hvbWVfbGluayI7czoyOiJvbiI7czoyMjoic2ltcGxlcHJlc3Nfc29ydF9wYWdlcyI7czoxMDoicG9zdF90aXRsZSI7czoyMjoic2ltcGxlcHJlc3Nfb3JkZXJfcGFnZSI7czozOiJhc2MiO3M6Mjk6InNpbXBsZXByZXNzX3RpZXJzX3Nob3duX3BhZ2VzIjtzOjE6IjMiO3M6MjA6InNpbXBsZXByZXNzX21lbnVjYXRzIjtOO3M6Mzk6InNpbXBsZXByZXNzX2VuYWJsZV9kcm9wZG93bnNfY2F0ZWdvcmllcyI7czoyOiJvbiI7czoyODoic2ltcGxlcHJlc3NfY2F0ZWdvcmllc19lbXB0eSI7czoyOiJvbiI7czozNDoic2ltcGxlcHJlc3NfdGllcnNfc2hvd25fY2F0ZWdvcmllcyI7czoxOiIzIjtzOjIwOiJzaW1wbGVwcmVzc19zb3J0X2NhdCI7czo0OiJuYW1lIjtzOjIxOiJzaW1wbGVwcmVzc19vcmRlcl9jYXQiO3M6MzoiYXNjIjtzOjI3OiJzaW1wbGVwcmVzc19kaXNhYmxlX3RvcHRpZXIiO047czoyMToic2ltcGxlcHJlc3NfcG9zdGluZm8yIjthOjQ6e2k6MDtzOjY6ImF1dGhvciI7aToxO3M6NDoiZGF0ZSI7aToyO3M6MTA6ImNhdGVnb3JpZXMiO2k6MztzOjg6ImNvbW1lbnRzIjt9czoyMjoic2ltcGxlcHJlc3NfdGh1bWJuYWlscyI7czoyOiJvbiI7czoyOToic2ltcGxlcHJlc3Nfc2hvd19wb3N0Y29tbWVudHMiO3M6Mjoib24iO3M6Mjc6InNpbXBsZXByZXNzX3BhZ2VfdGh1bWJuYWlscyI7TjtzOjMwOiJzaW1wbGVwcmVzc19zaG93X3BhZ2VzY29tbWVudHMiO047czoyMToic2ltcGxlcHJlc3NfcG9zdGluZm8xIjthOjQ6e2k6MDtzOjY6ImF1dGhvciI7aToxO3M6NDoiZGF0ZSI7aToyO3M6MTA6ImNhdGVnb3JpZXMiO2k6MztzOjg6ImNvbW1lbnRzIjt9czoyODoic2ltcGxlcHJlc3NfdGh1bWJuYWlsc19pbmRleCI7czoyOiJvbiI7czoyNToic2ltcGxlcHJlc3NfY3VzdG9tX2NvbG9ycyI7TjtzOjIxOiJzaW1wbGVwcmVzc19jaGlsZF9jc3MiO047czoyNDoic2ltcGxlcHJlc3NfY2hpbGRfY3NzdXJsIjtzOjA6IiI7czoyNToic2ltcGxlcHJlc3NfY29sb3JfYmdjb2xvciI7czowOiIiO3M6MjY6InNpbXBsZXByZXNzX2NvbG9yX21haW5mb250IjtzOjA6IiI7czoyNjoic2ltcGxlcHJlc3NfY29sb3JfbWFpbmxpbmsiO3M6MDoiIjtzOjMyOiJzaW1wbGVwcmVzc19jb2xvcl9tYWlubGlua19ob3ZlciI7czowOiIiO3M6MjY6InNpbXBsZXByZXNzX2NvbG9yX3BhZ2VsaW5rIjtzOjA6IiI7czozMjoic2ltcGxlcHJlc3NfY29sb3Jfc2lkZWJhcl90aXRsZXMiO3M6MDoiIjtzOjMxOiJzaW1wbGVwcmVzc19jb2xvcl9zaWRlYmFyX2xpbmtzIjtzOjA6IiI7czozMDoic2ltcGxlcHJlc3NfY29sb3Jfc2lkZWJhcl90ZXh0IjtzOjA6IiI7czoyNDoic2ltcGxlcHJlc3NfY29sb3JfZm9vdGVyIjtzOjA6IiI7czozMDoic2ltcGxlcHJlc3NfY29sb3JfZm9vdGVyX2xpbmtzIjtzOjA6IiI7czoyNjoic2ltcGxlcHJlc3NfY29sb3JfcG9zdGluZm8iO3M6MDoiIjtzOjMyOiJzaW1wbGVwcmVzc19jb2xvcl9wb3N0aW5mb19saW5rcyI7czowOiIiO3M6MjY6InNpbXBsZXByZXNzX3Nlb19ob21lX3RpdGxlIjtOO3M6MzI6InNpbXBsZXByZXNzX3Nlb19ob21lX2Rlc2NyaXB0aW9uIjtOO3M6Mjk6InNpbXBsZXByZXNzX3Nlb19ob21lX2tleXdvcmRzIjtOO3M6MzA6InNpbXBsZXByZXNzX3Nlb19ob21lX2Nhbm9uaWNhbCI7TjtzOjMwOiJzaW1wbGVwcmVzc19zZW9faG9tZV90aXRsZXRleHQiO3M6MDoiIjtzOjM2OiJzaW1wbGVwcmVzc19zZW9faG9tZV9kZXNjcmlwdGlvbnRleHQiO3M6MDoiIjtzOjMzOiJzaW1wbGVwcmVzc19zZW9faG9tZV9rZXl3b3Jkc3RleHQiO3M6MDoiIjtzOjI1OiJzaW1wbGVwcmVzc19zZW9faG9tZV90eXBlIjtzOjI3OiJCbG9nTmFtZSB8IEJsb2cgZGVzY3JpcHRpb24iO3M6Mjk6InNpbXBsZXByZXNzX3Nlb19ob21lX3NlcGFyYXRlIjtzOjM6IiB8ICI7czoyODoic2ltcGxlcHJlc3Nfc2VvX3NpbmdsZV90aXRsZSI7TjtzOjM0OiJzaW1wbGVwcmVzc19zZW9fc2luZ2xlX2Rlc2NyaXB0aW9uIjtOO3M6MzE6InNpbXBsZXByZXNzX3Nlb19zaW5nbGVfa2V5d29yZHMiO047czozMjoic2ltcGxlcHJlc3Nfc2VvX3NpbmdsZV9jYW5vbmljYWwiO047czozNDoic2ltcGxlcHJlc3Nfc2VvX3NpbmdsZV9maWVsZF90aXRsZSI7czo5OiJzZW9fdGl0bGUiO3M6NDA6InNpbXBsZXByZXNzX3Nlb19zaW5nbGVfZmllbGRfZGVzY3JpcHRpb24iO3M6MTU6InNlb19kZXNjcmlwdGlvbiI7czozNzoic2ltcGxlcHJlc3Nfc2VvX3NpbmdsZV9maWVsZF9rZXl3b3JkcyI7czoxMjoic2VvX2tleXdvcmRzIjtzOjI3OiJzaW1wbGVwcmVzc19zZW9fc2luZ2xlX3R5cGUiO3M6MjE6IlBvc3QgdGl0bGUgfCBCbG9nTmFtZSI7czozMToic2ltcGxlcHJlc3Nfc2VvX3NpbmdsZV9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MzE6InNpbXBsZXByZXNzX3Nlb19pbmRleF9jYW5vbmljYWwiO047czozMzoic2ltcGxlcHJlc3Nfc2VvX2luZGV4X2Rlc2NyaXB0aW9uIjtOO3M6MjY6InNpbXBsZXByZXNzX3Nlb19pbmRleF90eXBlIjtzOjI0OiJDYXRlZ29yeSBuYW1lIHwgQmxvZ05hbWUiO3M6MzA6InNpbXBsZXByZXNzX3Nlb19pbmRleF9zZXBhcmF0ZSI7czozOiIgfCAiO3M6MzU6InNpbXBsZXByZXNzX2ludGVncmF0ZV9oZWFkZXJfZW5hYmxlIjtzOjI6Im9uIjtzOjMzOiJzaW1wbGVwcmVzc19pbnRlZ3JhdGVfYm9keV9lbmFibGUiO3M6Mjoib24iO3M6Mzg6InNpbXBsZXByZXNzX2ludGVncmF0ZV9zaW5nbGV0b3BfZW5hYmxlIjtzOjI6Im9uIjtzOjQxOiJzaW1wbGVwcmVzc19pbnRlZ3JhdGVfc2luZ2xlYm90dG9tX2VuYWJsZSI7czoyOiJvbiI7czoyODoic2ltcGxlcHJlc3NfaW50ZWdyYXRpb25faGVhZCI7czowOiIiO3M6Mjg6InNpbXBsZXByZXNzX2ludGVncmF0aW9uX2JvZHkiO3M6MDoiIjtzOjM0OiJzaW1wbGVwcmVzc19pbnRlZ3JhdGlvbl9zaW5nbGVfdG9wIjtzOjA6IiI7czozNzoic2ltcGxlcHJlc3NfaW50ZWdyYXRpb25fc2luZ2xlX2JvdHRvbSI7czowOiIiO3M6MjI6InNpbXBsZXByZXNzXzQ2OF9lbmFibGUiO047czoyMToic2ltcGxlcHJlc3NfNDY4X2ltYWdlIjtzOjA6IiI7czoxOToic2ltcGxlcHJlc3NfNDY4X3VybCI7czowOiIiO30=';
	
	/*global $options;
	
	foreach ($options as $value) {
		if( isset( $value['id'] ) ) { 
			update_option( $value['id'], $value['std'] );
		}
	}*/
	
	$importedOptions = unserialize(base64_decode($importOptions));
	
	foreach ($importedOptions as $key=>$value) {
		if ($value != '') update_option( $key, $value );
	}
	
	update_option( $shortname . '_use_pages', 'false' );
} ?>