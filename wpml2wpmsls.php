<?php
/*
Plugin Name: WPML 2 WPMSLS
Plugin URI: wordpress.org/plugins/wpml2wpmsls
Description: Convert posts from an existing WPML multilingual site via WP Import/Export to a WPMS (Network) with Language Switcher so easily it feels like magic!
Author: Jamie Oastler
Version: 0.2.0
Author URI: http://idealienstudios.com/
GitHub Plugin URI: idealien/wpml2wpmsls
*/


define( 'IDEALIEN_ML2MSLS_PATH', WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) );

if ( ! defined( 'RG_CURRENT_PAGE' ) ) {
	define( 'RG_CURRENT_PAGE', basename( $_SERVER['PHP_SELF'] ) );
}

class Idealien_ML2MSLS {

	//Ensure plugin functions are accessible based on version of WordPress
	function requires_wordpress_version() {
		global $wp_version;
		$plugin = plugin_basename( __FILE__ );
		$plugin_data = get_plugin_data( __FILE__, false );

		if ( version_compare( $wp_version, "3.1", "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "'"  . $plugin_data[ 'Name' ] . "' requires WordPress 3.1 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
			}
		}
	}

	//Clean up after the plugin is deleted
	static function delete_plugin_options() {
		delete_option( 'idealien_ml2msls_options' );
	}

	//Setup plugin options
	static function add_plugin_defaults() {

		//Check that this is initial activation (or default option reset requires reset)
		$tmp = get_option('idealien_ml2msls_options');
		if ( ( $tmp['chk_default_options_db'] == '1' ) || ( ! is_array( $tmp ) ) ) {
			//Start from a clean slate
			delete_option( 'idealien_ml2msls_options' );
			//Create array of default options
			$arr = array(	"main_mode" => "OFF", //"WPMS, WPML, EXPORT"
							"main_post_type" => "",						
							"chk_default_options_db" => "1"
						);
			//Store them into the DB
			update_option( 'idealien_ml2msls_options', $arr ); 
		}
	}

	function __construct() {
		//Confirm WordPress version requirements - 3.1
		add_action( 'admin_init', array( &$this, 'requires_wordpress_version' ) );
		// Setup Options page with custom post type
		add_action( 'admin_menu', array( &$this, 'admin_add_page' ) );
		// Add settings for options page
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		// Setup Settings Link on Plugins Page
		add_action( 'plugin_action_links', array( &$this, 'action_link'), 10, 2 );

		//End Idealien_ML2MSLS constructor function
	}

	// Display a Settings link on the Plugins page
	function action_link( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$idealien_ml2msls_options_link = '<a href="' . get_admin_url() . 'options-general.php?page=wpml2wpmsls">' . __( 'Settings', 'ml2msls' ) . '</a>';
			//Settings at the front, Support at the back
			array_unshift( $links, $idealien_ml2msls_options_link );
		}

		return $links;
	}

	// Add menu page
	function admin_add_page() {
		//add_management_page( "Convert WPML to WPMSLS", "WPML 2 WPMSLS", "manage_options", "wpml2wpmsls", array($this, 'options_page') );
		global $wpml2wpmsls;
		$wpml2wpmsls = add_options_page( __( 'Convert WPML to WPMSLS', 'ml2msls' ), __( 'WPML 2 WPMSLS', 'ml2msls' ), 'manage_options', 'wpml2wpmsls', array( &$this, 'options_page' ) );
		add_action( 'load-' . $wpml2wpmsls, array( &$this, 'contextual_help_tab' ) );
	}

	// Options Form
	function options_page() {
		?>
		<div class="wrap">
			<!-- Display Plugin Icon, Header, and Description -->
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php _e( 'WPML 2 WPMSLS', 'ml2msls' ); ?></h2>
			<p><?php _e( 'Conversion from WPML to WPMS so simple it feels like magic.', 'ml2msls' ); ?></p>
			<!-- Beginning of the Plugin Options Form -->
			<form method="post" action="options.php">	
				<?php settings_fields( 'ml2msls_options' ); ?>
				<?php do_settings_sections( 'ml2msls' ); ?>
				<p class="submit">
				<input name="Submit" type="submit" class="button-primary"  value="<?php esc_attr_e( 'Update', 'ml2msls' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	function admin_init() {
		//Enable options to be saved on menu page.
		register_setting( 'ml2msls_options', 'ml2msls_options', array( &$this, 'validate_options' ) );
		add_settings_section( 'ml2msls_main', __('', 'ml2msls' ), array( &$this, 'options_section_main' ), 'ml2msls' );
		add_settings_field( 'ml2msls_main_mode', __( 'Conversion Mode', 'ml2msls' ), array( $this, 'options_radio_mode' ), 'ml2msls', 'ml2msls_main' );
		add_settings_field( 'ml2msls_main_postType', __('Post Type(s)', 'ml2msls '), array( &$this, 'options_select_post_type' ), 'ml2msls', 'ml2msls_main' );
	}

	function options_section_main() {
		echo '<p></p>';
	}

	function options_radio_mode() {
		$options = get_option('ml2msls_options');
		echo '<label><input name="ml2msls_options[main_mode]" type="radio" value="OFF" ' . checked( 'OFF', $options['main_mode'], false ) . '/> ' . __('Off', 'ml2msls') . '</label><br />';
		echo '<label><input name="ml2msls_options[main_mode]" type="radio" value="WPML" ' . checked( 'WPML', $options['main_mode'], false );
		if ( !function_exists('icl_get_languages') ) {
			echo " disabled='true' ";
		}
		echo  '/> ' . __( 'WPML', 'ml2msls' ) . '</label><br />';
		echo '<label><input name="ml2msls_options[main_mode]" type="radio" value="EXPORT" ' . checked( 'EXPORT', $options['main_mode'], false ) . ' disabled="true" /> ' . __('Export', 'ml2msls') . '</label><br />';
		echo '<label><input name="ml2msls_options[main_mode]" type="radio" value="WPMS" ' . checked( 'WPMS', $options['main_mode'], false );
		if ( ! function_exists( 'the_msls' ) ) { 
			echo " disabled='true' ";
		}
		echo  '/> ' . __( 'WPMS', 'ml2msls' ) . '</label><br />';
	}

	function options_select_post_type() {
		$options = get_option( 'ml2msls_options' );

		$post_types = get_post_types( array( 'public'   => true ), 'names', 'and' );
		echo "<select name='ml2msls_options[main_post_type]'>";
		echo "<option value='' " . selected( "", $options["main_post_type"], false ) . "></option>";
		echo "<option value='all' " . selected( "all", $options["main_post_type"], false ) . ">All</option>";

		foreach ( $post_types  as $post_type ) {
			echo '<option value="' . $post_type . '" ' . selected( $post_type, $options["main_post_type"], false ) . '>'. ucfirst( $post_type ) . '</option>';
		}
		echo '</select>';
	}

	// Sanitize and validate input. Accepts an array, return a sanitized array.
	function validate_options( $input ) {
		$updateMessage = "";
		$errorMessage = "";

		$validatedInput['main_mode']      =  wp_filter_nohtml_kses( $input['main_mode'] );
		$validatedInput['main_post_type'] =  wp_filter_nohtml_kses( $input['main_post_type'] );

		//Setup query params for processing on both ML and MS usage
		switch ( $validatedInput[ 'main_post_type' ] ) {
			case '':
				$errorMessage  .= __( 'You did not select a Post Type. ', 'ml2msls' );
				$updateMessage .= __( 'No changes made to your site content. ', 'ml2msls' );
				break;

			case 'all':
				$queryParams = array( 
					'posts_per_page' => -1,
					'post_type'      => get_post_types( array( 'public' => true ), 'names', 'and' ),
				);
				break;

			default:
				$queryParams = array( 
					'posts_per_page' => -1,
					'post_type'      => array( $validatedInput['main_post_type'] ),
				);
				break;
		}

		switch ( $validatedInput['main_mode'] ) {
			case 'WPML':
				if ( $validatedInput['main_post_type'] != '' ) :
					$conversionData = new WP_Query();
					$conversionData->query( $queryParams );

					if ( $conversionData->have_posts() ) : 
						$count_posts = 0;
						$count_translations = 0;

						while ( $conversionData->have_posts() ) : $conversionData->the_post(); 
							$ID = get_the_ID();
							$postType = get_post_type( $ID );

							$translations = icl_get_languages('skip_missing=1');
							$translateLanguages = array();
							foreach ( $translations  as $translation ) {
								$translate_ID = icl_object_id( $ID, $postType, false, $translation['language_code'] );
								//Filter out languages that do not have translations
								if ( $translate_ID != "" ) {
									$translateLanguages[] = $translation['language_code'];
									update_post_meta( $ID, "_wpml2wpms_" . $translation['language_code'], $translate_ID );
									update_post_meta( $translate_ID, "_wpml2wpms_" . $translation['language_code'], $translate_ID );
								}

								//Identify primary language of the current post
								if( $ID == $translate_ID ) {
									update_post_meta( $ID, "_wpml2wpms_baseLanguage",  $translation['language_code'] );
								} else {
									update_post_meta( $translate_ID, "_wpml2wpms_baseLanguage",  $translation['language_code'] );
								}

								$count_translations++;
							}
							update_post_meta($ID, "_wpml2wpms_transLanguages", $translateLanguages);
							$count_posts++;
						endwhile;

						$updateMessage .= $count_posts . __(' entries had meta data updated with details for ', 'ml2msls') . $count_translations . __(' translations. ', 'ml2msls');
					else: 					
						$updateMessage .= __( ' None of those Post Type(s) existed for update. ', 'ml2msls' );
					endif;
				endif;
				$updateMessage .= "<br/>" . __('Settings saved. ', 'ml2msls');
				break;
			case 'EXPORT':
				$updateMessage .= __("This feature is not yet implemented.", 'ml2msls');
				break;
			case 'WPMS':
				if( $validatedInput['main_post_type'] != '' ):
					
					$main_blog_ID     = get_current_blog_id();
					$main_lang_iso 	  = get_option('WPLANG');
					$main_lang_short  =  substr($main_lang_iso, 0, 2);
					
					$updateMessage .= "<br/>" . "Initiating Blog: " . $main_blog_ID . ": " . get_bloginfo('name') . " has language: " . $main_lang_iso . "<br/>";

				    $sites = array();
				    
					// Query all blogs from multi-site install for language details based on current user access
				    $current_user = wp_get_current_user();
					$blogs = get_blogs_of_user($current_user->ID);
            	
            		foreach ( $blogs as $blog ) {						
                    	switch_to_blog( $blog->userblog_id );
							//Store Blog ID and multiple versions of language code
							$language = get_option('WPLANG');
							$sites[] = array( "ID" => $blog->userblog_id,"ISO" => $language, "LANG" => substr($language, 0, 2));
						
					}
					//Return to original blog
					switch_to_blog( $main_blog_ID );
					
					//Find posts to begin conversion
					$conversionData = new WP_Query();
					$conversionData->query($queryParams);
					
					if ( $conversionData->have_posts() ) : 

						$count_posts = 0;
						$count_translations = 0;
						$count_errors = 0;
						
						while ( $conversionData->have_posts() ) : $conversionData->the_post(); 
							$count_posts++;
							$ID = get_the_ID();
							$postType = get_post_type( $ID );
							$native_new_id = $ID;
							$native_language = get_post_meta($ID, "_wpml2wpms_baseLanguage", true);
							$native_old_id = get_post_meta($ID, "_wpml2wpms_" . $native_language, true);
						
							//Loop through every translation language looking for matches.
							$translations = get_post_meta($ID, "_wpml2wpms_transLanguages", true);

							if ( is_array( $translations ) && ! empty( $translations ) ) {
								foreach ( $translations as $translation ) { 
								
									$trans_old_id = get_post_meta($ID, "_wpml2wpms_" . $translation, true);
									
									//Find every site which has matching language (2 char)
									foreach($sites as $site) {
										if($site['LANG'] == $translation) {
											
											switch_to_blog($site['ID']);
												
											//Find matching posts based on old post ID / language combo 
											$transQueryParams = array(
												'post_type' => $postType,
												'posts_per_page' => '-1',
												'meta_query' => array(
													array(
														'key' => "_wpml2wpms_" . $translation,
														'value' => $trans_old_id,
														'compare' => '='
													)
												),
												'order' => 'ASC'
											);
																							
											$translatePosts = new WP_Query();
											$translatePosts->query($transQueryParams);
			
											if ( $translatePosts->have_posts() ) : 
												while ( $translatePosts->have_posts() ) : $translatePosts->the_post();  
													$trans_new_id = get_the_ID();
												endwhile; 
											else:
												$trans_new_id = 0;
											endif;
												
											//Matching post was found. Make MSLS entry for current site back to native language post
											if($trans_new_id != 0) {
												$count_translations++;
												delete_option( "msls_" . $trans_new_id);
												add_option( "msls_" . $trans_new_id, array( $main_lang_iso => $native_new_id), "", "no");
												$trans_language = get_option('WPLANG');
											}
							
											wp_reset_postdata();
								
											restore_current_blog();
											
											//Make MSLS entry for native language site to translation language post 
											if($trans_new_id != 0) {
												delete_option( "msls_" . $native_new_id);
												add_option( "msls_" . $native_new_id, array( $trans_language => $trans_new_id), "", "no");
											}
										}
									}
								}
							} else {
								$count_errors++;
								error_log('WPML2WPMSLS - ' . $postType . ' #' . $ID . ' had no meta info to complete attempted translation.');
							}

						 endwhile;
						$updateMessage .= $count_posts . __(' entries had meta data updated with details for ', 'ml2msls') . $count_translations . __(' translations ', 'ml2msls') . ". ";
						$updateMessage .= $count_errors . __(' entries did not have sufficient meta to complete translation. See error log for entry IDs.', 'ml2msls');
					else:
						$updateMessage .= __(' None of those Post Type(s) existed for update. ', 'ml2msls');
					endif; 
					
            	endif;
				break;
				
			case 'OFF':
				$updateMessage .= "<br/>" . __('Settings saved. ', 'ml2msls');
				$updateMessage .= __('No changes made to your site content. ', 'ml2msls');
				break;
		}
		
		if( $errorMessage != "") {
			add_settings_error( 'wpml2wpmsls', 'error-messages', $errorMessage, 'error');	
		}
		
		if( $updateMessage != "") {
			add_settings_error( 'wpml2wpmsls', 'update-messages', $updateMessage, 'updated');
		}
		
		return $validatedInput;		
	}

	
	
	// Add menu page
	function export_language_select() {
		echo "<strong>" . "Language" . "</strong><br/>";
		$languages = icl_get_languages('skip_missing=0');
		echo "<select name='ml2msls_language_filter'>";
		echo "<option value=''></option>";		
		foreach ($languages  as $language ) {
    		echo '<option value="' . $language['language_code'] . '" >'. $language['language_code'] . '</option>';
  		}
		echo "</select>";
	}
	
	function export_language_filter_args( $args) {
		if ( $_REQUEST['ml2msls_language_filter'] )
			$args['ml2msls_language_filter'] = $_REQUEST['ml2msls_language_filter'];
			
		return $args;
	}
			
	function export_language_filter_join( $join, $args) {
		$join .= " INNER JOIN {$wpdb->wp_postmeta} ON ({$wpdb->posts}.ID = {$wpdb->wp_postmeta}.post_id) ";
		return $join;
	}	
	
	function export_language_filter_where( $where, $args) {
		$where .= $wpdb->prepare( " AND {$wpdb->wp_postmeta}._wpml2wpms_baseLanguage = %s ", "en" );
		
		return $where;
	}

	function export_language_query_filter($query) {
		
		global $wpdb;
  		
		//$query .= $wpdb->prepare(" WHERE ID IN (SELECT post_id FROM {$wpdb->wp_postmeta} WHERE meta_key = %s AND meta_value = %s) ", "_wpml2wpms_baseLanguage", "en");
		//$query .= $wpdb->prepare(" INNER JOIN {$wpdb->wp_postmeta} ON ({$wpdb->posts}.ID = {$wpdb->wp_postmeta}.post_id) WHERE ($wpdb->wp_postmeta.meta_key} = %s AND ($wpdb->wp_postmeta.meta_value} = %s) ", "_wpml2wpms_baseLanguage", "en");

		//var_export($query);
  		return $query;
	}
	

	function contextual_help_tab() {
		global $wpml2wpmsls;
		
    	$screen = get_current_screen();
		
		if ( $screen->id != $wpml2wpmsls )
        return;

    	// Add help tab to page
		$helpTitle = '<h3>' . __('WPML2WPMS Help', 'ml2msls') . '</h3>';
		
		$defaultContent  =  '<strong>' . __( 'Note: ', 'ml2msls') . '</strong>';
		$defaultContent .= __('This plugin only converts the actual posts or post types. You will need to handle conversion of any strings, .po files or other language elements separately.', 'ml2msls' );
		
		$content  = '<br/><br/>' . __('To convert your WPML translation details for export: ', 'ml2msls');
		$content .=  '<ol><li>' . __( 'You must have WPML installed and activated.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'Set the Conversion Mode to WPML and select which post type(s) you want to convert.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'Press Update. All of the selected post types will have their translations associated together via meta data.', 'ml2msls' ) . '</li></ol>';
		
    	$screen->add_help_tab( array(
			'id'	=> 'ml2msls_tab1',
        	'title'	=> __('Step 1 - Convert WPML', 'ml2msls'),
        	'content'	=>  $helpTitle . $defaultContent . $content,
   		) );
		
		$content  = '<br/><br/>' . __('To export your WPML translation details: ', 'ml2msls');
		$content .=  '<ol><li>' . __( 'Use the standard WordPress Export tool.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'A future version of this plugin will enable you to filter posts for export by language.', 'ml2msls' ) . '</li></ol>';
		
		// Add help tab to page
    	$screen->add_help_tab( array(
			'id'	=> 'ml2msls_tab2',
        	'title'	=> __('Step 2 - Export Data', 'ml2msls'),
        	'content'	=> $helpTitle . $defaultContent . $content,
   		) );
		
		$content =  '<ol><li>' . __( 'Use the standard WordPress Import tool.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'Manually delete any posts in each site that are not of the correct language.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'When the export enhancements are complete your export files will be language specific so deletes not be required.', 'ml2msls' ) . '</li></ol>';
		
		// Add help tab to page
    	$screen->add_help_tab( array(
			'id'	=> 'ml2msls_tab3',
        	'title'	=> __('Step 3 - Import Data', 'ml2msls'),
        	'content'	=> $helpTitle . $defaultContent . $content,
   		) );
		
		$content  = '<br/><br/>' . __('To finish converting your translations: ', 'ml2msls');
		$content .=  '<ol><li>' . __( 'You must have MSLS installed and activated.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'Set the Conversion Mode to WPMS and select which post type(s) you want to convert.', 'ml2msls' ) . '</li>';
		$content .=  '<li>' . __( 'Press Update. All of the selected post types will have their associated translations restored.', 'ml2msls' ) . '</li></ol>';
		
		// Add help tab to page
    	$screen->add_help_tab( array(
			'id'	=> 'ml2msls_tab4',
        	'title'	=> __('Step 4 - Update WPMS', 'ml2msls'),
        	'content'	=> $helpTitle . $defaultContent . $content,
   		) );
		
	}
}


// Initiate the plugin
add_action("init", "Idealien_ml2mslsInit");
register_activation_hook(__FILE__, array('Idealien_ml2msls', 'add_plugin_defaults'));
register_deactivation_hook(__FILE__, array('Idealien_ml2msls', 'delete_plugin_options'));

function Idealien_ml2mslsInit() {
	global $Idealien_ml2msls;
	$Idealien_ml2msls = new Idealien_ml2msls();
}


?>