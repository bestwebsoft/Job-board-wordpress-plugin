<?php
/*
Plugin Name: Job Board by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Plugin for adding to site possibility to create job offers page with custom search, send CV and subscribing for similar jobs.
Author: BestWebSoft
Text Domain: job-board
Domain Path: /languages
Version: 1.0.9
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*
	@ Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Add Wordpress page 'bws_plugins' and sub-page of this plugin to admin-panel.
 * @return void
 */
if ( ! function_exists( 'jbbrd_add_admin_menu' ) ) {
	function jbbrd_add_admin_menu() {
		global $submenu;
		bws_general_menu();
		$settings = add_submenu_page( 'bws_plugins', 'Job Board', 'Job Board', 'manage_options', 'job-board.php', 'jbbrd_settings_page' );	
		/* Add custom list page to candidate profile menu. */
		$hook = add_users_page( 'New job offers', 'New job offers', 'job_candidate', 'job_candidate', 'jbbrd_candidate_category_custom_search_page' );
		add_action( "load-$hook", 'jbbrd_screen_options' );

		add_action( 'load-' . $settings, 'jbbrd_add_tabs' );
		add_action( 'load-post.php', 'jbbrd_add_tabs' );
		add_action( 'load-edit.php', 'jbbrd_add_tabs' );
		add_action( 'load-post-new.php', 'jbbrd_add_tabs' );
		add_action( 'load-edit-tags.php', 'jbbrd_add_tabs' );
		
		if ( isset( $submenu['edit.php?post_type=vacancy'] ) )
			$submenu['edit.php?post_type=vacancy'][] = array( __( 'Settings', 'job-board' ), 'manage_options', admin_url( 'admin.php?page=job-board.php' ) );
	}
}

/**
 * Install plugin.
 * @return void
 */
if ( ! function_exists( 'jbbrd_plugin_install' ) ) {
	function jbbrd_plugin_install() {
		/* Set admins capabilities. */
		jbbrd_set_administrators_capabilities();
		/* Create employer & job candidate roles. */
		jbbrd_roles_create();
		/* Check for new shortcode_permalink */
		jbbrd_update_permalink();
	}
}

/**
 * Internationalization
 */
if ( ! function_exists( 'jbbrd_plugins_loaded' ) ) {
	function jbbrd_plugins_loaded() {
		load_plugin_textdomain( 'job-board', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Initialize plugin.
 * @return void
 */
if ( ! function_exists ( 'jbbrd_init' ) ) {
	function jbbrd_init() {
		global $jbbrd_plugin_info;
		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		
		if ( empty( $jbbrd_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$jbbrd_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $jbbrd_plugin_info, '3.8', '3.5' );

		/* Session start. */
		if ( ! @session_id() )
			@session_start();
		/* Register Vacancy post type. */
		jbbrd_post_type_vacancy();
		/* Register taxonomy for vacancy. */
		jbbrd_taxonomy_vacancy();
		/* Hide logo image when search results displayed. */
		jbbrd_logo_search_hide();
		/* Call register settings function. */
		jbbrd_settings();
		add_action( 'delete_term_taxonomy', 'jbbrd_delete_term', 10, 1 );
	}
}

/**
 * Admin interface init.
 * @return void
 */
if ( ! function_exists ( 'jbbrd_admin_init' ) ) {
	function jbbrd_admin_init() {
		global $bws_plugin_info, $jbbrd_plugin_info, $bws_shortcode_list;
		/* Add variable for bws_menu */
		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '139', 'version' => $jbbrd_plugin_info["Version"] );
		}
		/* Custom meta boxes for the edit job screen. */
		add_meta_box( "list-pers-meta", __( 'Job Information', 'job-board' ), "jbbrd_meta_personal", "vacancy", "normal", "low" );
		/* Check for sender plugin. */
		jbbrd_check_sender();
		/* Show error message on edit.php screen when shortcode error. */
		jbbrd_add_error_to_vacancy_CPT_edit();
		/* add Job Board to global $bws_shortcode_list  */
		$bws_shortcode_list['jbbrd'] = array( 'name' => 'Job Board', 'js_function' => 'jbbrd_shortcode_init' );
	}
}

/**
 * Register "vacancy" custom post type function. Set global array $jbbrd_options.
 * @return void
 */
if ( ! function_exists( 'jbbrd_post_type_vacancy' ) ) {
	function jbbrd_post_type_vacancy() {
		/* Register "vacancy" CPT. */ 
		register_post_type( 'vacancy', array(
			'labels'			=> array(
				'name' 						=> __( 'Jobs', 'job-board' ), 
				'singular_name' 			=> __( 'Job', 'job-board' ),
				'add_new' 					=> __( 'Add job', 'job-board' ),
				'add_new_item'				=> __( 'Add new job', 'job-board' ),
				'edit' 						=> __( 'Edit', 'job-board' ),
				'edit_item' 				=> __( 'Edit job', 'job-board' ),
				'new_item' 					=> __( 'New job', 'job-board' ),
				'view' 						=> __( 'View jobs', 'job-board' ),
				'view_item' 				=> __( 'View job', 'job-board' ),
				'search_items'	 			=> __( 'Search job', 'job-board' ),
				'not_found' 				=> __( 'No jobs found', 'job-board' ),
				'not_found_in_trash' 		=> __( 'No jobs found in Trash', 'job-board' ),
				'parent' 					=> __( 'Parent job', 'job-board' ),
				'filter_items_list'     	=> __( 'Filter jobs list', 'job-board' ),
				'items_list_navigation' 	=> __( 'Jobs list navigation', 'job-board' ),
				'items_list'            	=> __( 'Jobs list', 'job-board' )
			),
			'singular_label' 	=> __( 'Job', 'job-board' ),
			'public' 			=> true,
			'show_ui' 			=> true, /* UI in admin panel. */
			'_builtin' 			=> false, /* It's a custom post type, not built in. */
			'_edit_link' 		=> 'post.php?post=%d',
			'capability_type'	=> 'vacancy',
			'capabilities' 		=> array(
				/* Admins capabilities. */
				'edit_posts'				=> 'edit_vacancies',
				'edit_others_posts'			=> 'edit_others_vacancies',
				'delete_posts'				=> 'delete_vacancies',
				'delete_others_posts'		=> 'delete_others_vacancies',
				'delete_private_posts'		=> 'delete_private_vacancies',
				'edit_private_posts'		=> 'edit_private_vacancies',
				'read_private_posts'		=> 'read_private_vacancies',
				/* Employer capabilities. */
				'edit_published_posts'		=> 'edit_published_vacancies',
				'upload_files'				=> 'upload_files',
				'publish_posts'				=> 'publish_vacancies',
				'delete_published_posts' 	=> 'delete_published_vacancies',
				'edit_post' 				=> 'edit_vacancy',
				'delete_post' 				=> 'delete_vacancy',
				'read_post' 				=> 'read_vacancy',
			),
			'hierarchical' 		=> false,
			'map_meta_cap'    	=> true,
			'rewrite' 			=> array( "slug" => "vacancies" ), /* Permalinks. */
			'query_var' 		=> 'vacancy', /* This goes to the WP_Query schema. */
			'supports' 			=> array( 
				'title', 
				'editor', 
				'jbbrd_businesses',
				'jbbrd_employment',
				'thumbnail', /* Displays a box for featured image. */
			)
		));
	}
}

/**
 * Set "vacancy" custom taxonomies function.
 * @return void
 */
if ( ! function_exists( 'jbbrd_taxonomy_vacancy' ) ) {
	function jbbrd_taxonomy_vacancy() {
		/* Register jbbrd_businesses taxonomy. */
		register_taxonomy( 
			'jbbrd_businesses', 
			array( 'vacancy' ), 
			array(
				'hierarchical' 		=> true, 
				'labels'			=> array(
					'name' 						=> __( 'Job categories', 'job-board' ), 
					'singular_name' 			=> __( 'Job category', 'job-board' ),
					'add_new' 					=> __( 'Add job category', 'job-board' ),
					'add_new_item'				=> __( 'Add new job category', 'job-board' ),
					'edit' 						=> __( 'Edit job categories', 'job-board' ),
					'edit_item' 				=> __( 'Edit job category', 'job-board' ),
					'new_item' 					=> __( 'New job category', 'job-board' ),
					'view' 						=> __( 'View job categories', 'job-board' ),
					'view_item' 				=> __( 'View job category', 'job-board' ),
					'search_items'	 			=> __( 'Search job category', 'job-board' ),
					'not_found' 				=> __( 'No job categories found', 'job-board' ),
					'not_found_in_trash' 		=> __( 'No job categories found in Trash', 'job-board' ),
					'parent' 					=> __( 'Parent job category', 'job-board' ),
					'items_list_navigation' 	=> __( 'Job categories list navigation', 'job-board' ),
					'items_list'            	=> __( 'Job categories list', 'job-board' )
				),
				'rewrite' 			=> true,
				'show_ui'			=> true, /* Show to user. */
				'query_var'			=> true,
				'sort'				=> true,
				'orderby' 			=> 'term_order',
				'map_meta_cap'    	=> true,
				'capabilities' 		=> array(
					'manage_terms' 		=> 'manage_jbbrd_businesses_tags',
					'edit_terms' 		=> 'edit_jbbrd_businesses_tags',
					'delete_terms' 		=> 'delete_jbbrd_businesses_tags',
					'assign_terms' 		=> 'assign_jbbrd_businesses_tags',
				),
			)
		);
		/* Register archive taxonomy. */
		register_taxonomy(
			'archive', 
			array( 'vacancy' ), 
			array( 
				'hierarchical' 		=> true, 
				'labels'			=> array(
					'name' 						=> __( 'Job archive categories', 'job-board' ), 
					'singular_name' 			=> __( 'Job archive category', 'job-board' ),
					'add_new' 					=> __( 'Add job archive category', 'job-board' ),
					'add_new_item'				=> __( 'Add new job archive category', 'job-board' ),
					'edit' 						=> __( 'Edit job archive categories', 'job-board' ),
					'edit_item' 				=> __( 'Edit job archive category', 'job-board' ),
					'new_item' 					=> __( 'New job archive category', 'job-board' ),
					'view' 						=> __( 'View job archive categories', 'job-board' ),
					'view_item' 				=> __( 'View job archive category', 'job-board' ),
					'search_items'	 			=> __( 'Search job category', 'job-board' ),
					'not_found' 				=> __( 'No job archive categories found', 'job-board' ),
					'not_found_in_trash' 		=> __( 'No job archive categories found in Trash', 'job-board' ),
					'parent' 					=> __( 'Parent job archive category', 'job-board' ),
					'items_list_navigation' 	=> __( 'Job archive categories list navigation', 'job-board' ),
					'items_list'            	=> __( 'Job archive categories list', 'job-board' )
				),
				'rewrite' 			=> true,
				'show_ui'			=> false, /* Show to user. */
				'query_var'			=> true,
			)
		);
		/* Register jbbrd_employment taxonomy. */
		register_taxonomy( 
			'jbbrd_employment', 
			array( 'vacancy' ), 
			array( 
				'hierarchical' 		=> true, 
				'labels'			=> array(
					'name' 						=> __( 'Employment', 'job-board' ), 
					'singular_name' 			=> __( 'Employment type', 'job-board' ),
					'add_new' 					=> __( 'Add employment types', 'job-board' ),
					'add_new_item'				=> __( 'Add employment type', 'job-board' ),
					'edit' 						=> __( 'Edit employment types', 'job-board' ),
					'edit_item' 				=> __( 'Edit employment type', 'job-board' ),
					'new_item' 					=> __( 'New employment type', 'job-board' ),
					'view' 						=> __( 'View employment types', 'job-board' ),
					'view_item' 				=> __( 'View employment type', 'job-board' ),
					'search_items'	 			=> __( 'Search employment types', 'job-board' ),
					'not_found' 				=> __( 'No employment types found', 'job-board' ),
					'not_found_in_trash' 		=> __( 'No employment types found in trash', 'job-board' ),
					'parent' 					=> __( 'Parent employment type', 'job-board' ),
					'items_list_navigation' 	=> __( 'Employment types list navigation', 'job-board' ),
					'items_list'            	=> __( 'Employment types list', 'job-board' )
				),
				'rewrite' 			=> true,
				'show_ui'			=> true, /* Show to user. */
				'query_var'			=> true,
				'sort'				=> true,
				'orderby' 			=> 'term_order',
				'map_meta_cap'    	=> true,
				'capabilities' 		=> array(
					'manage_terms' 		=> 'manage_jbbrd_employment_tags',
					'edit_terms' 		=> 'edit_jbbrd_employment_tags',
					'delete_terms' 		=> 'delete_jbbrd_employment_tags',
					'assign_terms' 		=> 'assign_jbbrd_employment_tags',
				),
			)
		);
	}
}

/**
 * Set custom terms for 'archive' taxonomy.
 * @return void
 */
if ( ! function_exists( 'jbbrd_taxonomy_vacancy_terms' ) ) {
	function jbbrd_taxonomy_vacancy_terms() {
		if ( ! term_exists( 'archived', 'archive' ) ) {
			wp_insert_term(
				__( 'Archived', 'job-board' ), /* the term. */ 
				'archive', /* the taxonomy. */
				array(
					'description'	=> __( 'This job is in archive', 'job-board' ),
					'slug' 			=> 'archived',
				)
			);
		}
		if ( ! term_exists( 'posted', 'archive' ) ) {
			wp_insert_term(
				__( 'Posted', 'job-board' ), /* the term. */
				'archive', /* the taxonomy. */
				array(
					'description'	=> __( 'This job is posted', 'job-board' ),
					'slug' 			=> 'posted',
				)
			);
		}
	}
}

/**
 * Set custom terms for 'jbbrd_employment' taxonomy.
 * @return void
 */
if ( ! function_exists( 'jbbrd_taxonomy_employment_terms' ) ) {
	function jbbrd_taxonomy_employment_terms() {
		if ( ! term_exists( 'freelance', 'jbbrd_employment' ) ) {
			wp_insert_term(
				__( 'Freelance', 'job-board' ), /* the term. */ 
				'jbbrd_employment', /* the taxonomy. */
				array( 
					'description'	=> '',
					'slug' 			=> 'freelance', 
				)
			);
		}
		if ( ! term_exists( 'full-time', 'jbbrd_employment' ) ) {
			wp_insert_term(
				__( 'Full Time', 'job-board' ), /* the term. */
				'jbbrd_employment', /* the taxonomy. */
				array( 
					'description'	=> '',
					'slug' 			=> 'full-time',	
				)
			);
		}
		if ( ! term_exists( 'internship', 'jbbrd_employment' ) ) {
			wp_insert_term(
				__( 'Internship', 'job-board' ), /* the term. */ 
				'jbbrd_employment', /* the taxonomy. */
				array( 
					'description'	=> '',
					'slug' 			=> 'internship', 
				)
			);
		}
		if ( ! term_exists( 'part-time', 'jbbrd_employment' ) ) {
			wp_insert_term(
				__( 'Part Time', 'job-board' ), /* the term. */
				'jbbrd_employment', /* the taxonomy. */
				array( 
					'description'	=> '',
					'slug' 			=> 'part-time', 
				)
			);
		}
		if ( ! term_exists( 'temporary', 'jbbrd_employment' ) ) {
			wp_insert_term(
				__( 'Temporary', 'job-board' ), /* the term. */
				'jbbrd_employment', /* the taxonomy. */
				array( 
					'description'	=> '',
					'slug' 			=> 'temporary', 
				)
			);
		}
	}
}

/**
 * Removes the wrong permalinks & buttons on the vacancy post type editor
 * @return void
 */
if ( ! function_exists( 'jbbrd_remove_permalink_line' ) ) {
	function jbbrd_remove_permalink_line() {
        if ( isset( $_GET['post'] ) ) {
			$post_type = get_post_type( $_GET['post'] );
			if ( $post_type == 'vacancy' && $_GET['action'] == 'edit' ) {
				/* Hide permalink line menu, preview button, "Add media" button. */
				echo '<style>#edit-slug-box, #minor-publishing-actions, #wp-content-media-buttons {display:none;}</style>';
			}
		}
		/* If new vacancy create, hide them too. */
		if ( isset( $_GET['post_type'] ) && ( 'vacancy' == $_GET['post_type'] ) ) {
			/* Hide preview button, permalink line menu, "Add media" button. */
			echo '<style>#preview-action, #edit-slug-box, #wp-content-media-buttons{display:none;}</style>';
		}
	}
}

/**
 * Register settings function.
 * @return void
 */
if ( ! function_exists( 'jbbrd_settings' ) ) {
	function jbbrd_settings() {
		global $wpdb, $jbbrd_options, $jbbrd_option_defaults, $jbbrd_plugin_info;
		/* Set defaults array. */
				
		if ( ! $jbbrd_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$jbbrd_plugin_info	=	get_plugin_data( __FILE__, false );
		}
		$jbbrd_option_defaults = array(
			'plugin_option_version' 			=> $jbbrd_plugin_info["Version"],
			'post_per_page'						=> 5,
			'custom_time_hours'					=> 0,
			'custom_time_min'					=> 0,
			'money'								=> array( 'AUD', 'BGN', 'CAD', 'CHF', 'CNY', 'CYP', 'CZK', 'DKK', 
				'EEK', 'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'ISK', 'JPY', 'KPW', 'LTL', 'LVL', 'MTL', 'MYR',
				'NOK', 'NZD', 'PHP', 'PLN', 'RON', 'RUR', 'SEK', 'SGD', 'SIT', 'SKK', 'THB', 'TRY', 'USD', 'ZAR',
			),
			'select_money_unit'					=> 'preset', /* preset or custom */
			'money_choose'						=> 'USD',
			'custom_money_unit'					=> '',
			'time_period'						=> array( 
				__( 'year', 'job-board' ), 
				__( 'month', 'job-board' ), 
				__( 'week', 'job-board' ), 
				__( 'day', 'job-board' ), 
				__( 'hour', 'job-board' ),
			),
			'time_period_choose'				=> __( 'month', 'job-board' ),
			'logo_position'						=> 'left',
			'frontend_form'						=> 1,
			'frontend_form_non_registered'		=> 0,
			'location_select'					=> 0,
			'vacancy_reply_text'				=> __( 'You replied to job offer.', 'job-board' ),
			'archieving_period'					=> 30,
			'shortcode_permalink'				=> '',
			'display_settings_notice'			=>	1
		);
		/* Install the option defaults. */
		if ( ! get_option( 'jbbrd_options' ) ) {
			add_option( 'jbbrd_options', $jbbrd_option_defaults );
			/* Add taxonomy terms during first instalation of plugin */
			/* Register terms for archive. */
			jbbrd_taxonomy_vacancy_terms();
			/* Register terms for employment. */
			jbbrd_taxonomy_employment_terms();
		}
		/* Get options from the database. */
		$jbbrd_options = get_option( 'jbbrd_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $jbbrd_options['plugin_option_version'] ) || $jbbrd_options['plugin_option_version'] != $jbbrd_plugin_info["Version"] ) {
			/* check if shortcode option value exists - added in v.1.0.2 */
			if ( isset( $jbbrd_options['jbbrd_shortcode_permalink'] ) ) {
				$jbbrd_options['shortcode_permalink'] = $jbbrd_options['jbbrd_shortcode_permalink'];
				unset( $jbbrd_options['jbbrd_shortcode_permalink'] );
			}
			$jbbrd_option_defaults['display_settings_notice'] = 0;
			$jbbrd_options = array_merge( $jbbrd_option_defaults, $jbbrd_options );
			$jbbrd_options['plugin_option_version'] = $jbbrd_plugin_info["Version"];
			update_option( 'jbbrd_options', $jbbrd_options );
		}
	}
}

/**
 * Сhecking for the existence of Sender Plugin or Sender Pro Plugin.
 * @return void
 */
if ( ! function_exists( 'jbbrd_check_sender' ) ) {
	function jbbrd_check_sender() {
		global $jbbrd_sender_not_found, $jbbrd_sender_not_active;		
		if ( ! function_exists( 'is_plugin_active' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! ( array_key_exists( 'sender/sender.php', $all_plugins ) || array_key_exists( 'sender-pro/sender-pro.php', $all_plugins ) ) ) {
			$jbbrd_sender_not_found = sprintf( __( '%sSender Plugin%s is not found.%s', 'job-board' ), ( '<a target="_blank" href="' . esc_url( 'http://bestwebsoft.com/products/sender/' ) . '">' ),'</a>', '<br />' );
			$jbbrd_sender_not_found .= sprintf( __( 'If you want to give "send CV possibility" to Job candidates, you need to install and activate Sender plugin.%s', 'job-board' ), '</br>' );
			$jbbrd_sender_not_found .= __( 'You can download Sender Plugin from', 'job-board' ) . ' <a href="' . esc_url( 'http://bestwebsoft.com/products/sender/' ) . '" title="' . __( 'Developers website', 'job-board' ). '"target="_blank">' . __( 'website of plugin Authors', 'job-board' ) . ' </a>';
			$jbbrd_sender_not_found .= __( 'or', 'job-board' ) . ' <a href="' . esc_url( 'http://wordpress.org/plugins/sender/' ) . '" title="Wordpress" target="_blank">' . __( 'Wordpress.', 'job-board' ) . '</a>';
		} else {
			if ( ! ( is_plugin_active( 'sender/sender.php' ) || is_plugin_active( 'sender-pro/sender-pro.php' ) ) ) {
				$jbbrd_sender_not_active = sprintf( __( '%sSender Plugin%s is not active.%sIf you want to give "send CV possibility" to Job candidates, you need to %sactivate Sender plugin.%s', 'job-board' ), ( '<a target="_blank" href="' . esc_url( 'http://bestwebsoft.com/products/sender/' ) . '">' ),'</a>', '<br />', ( '<a href="' . esc_url( 'plugins.php' ) . '">' ), '</a>' );
			}
			/* Old version. */
			if ( is_plugin_active( 'sender/sender.php' ) && isset( $all_plugins['sender/sender.php']['Version'] ) && $all_plugins['sender/sender.php']['Version'] < '0.5' )
				$jbbrd_sender_not_found = __( 'Sender Plugin has old version.', 'job-board' ) . '</br>' . __( 'You need to update this plugin for sendmail function correct work.', 'job-board' );
		}
	}
}

/**
 * Find permalink page.
 * @return $jbbrd_shortcode_page_permalink string page with shortcode links
 */
if ( ! function_exists( 'jbbrd_find_shortcode_page' ) ) {
	function jbbrd_find_shortcode_page() {
		global $wpdb;
		$jbbrd_vacancy = ( method_exists( 'wpdb' , 'esc_like' ) ) ? $wpdb->esc_like( '[jbbrd_vacancy]' ) : like_escape( '[jbbrd_vacancy]' );
		$jbbrd_vacancy = esc_sql( $jbbrd_vacancy );
		$jbbrd_vacancy = '%' . $jbbrd_vacancy . '%';
		$jbbrd_shortcode_page_permalink = $wpdb->get_var( "
			SELECT `ID` 
			FROM `" . $wpdb->prefix . "posts` 
			WHERE `post_content` LIKE '" . $jbbrd_vacancy . "' 
			AND `post_status` = 'publish'"
		);
		if ( empty( $jbbrd_shortcode_page_permalink ) ) {
			return false;
		}
		return get_permalink( $jbbrd_shortcode_page_permalink );
	}
}

/**
 * Function for display job-board settings page in the admin area.
 * @return void
 */
if ( ! function_exists( 'jbbrd_settings_page' ) ) {
	function jbbrd_settings_page() {
		global $wpdb, $jbbrd_options, $jbbrd_option_defaults, $jbbrd_sender_not_found, $jbbrd_sender_not_active, $jbbrd_plugin_info;
		$error = $warning = '';
		$plugin_basename  = plugin_basename( __FILE__ );
		/* Save data for settings page. */
		if ( isset( $_POST['jbbrd_form_submit'] ) && check_admin_referer( $plugin_basename, 'jbbrd_nonce_name' ) ) {
			/* Check if start shedule time changed. */
			if ( ( $_POST['jbbrd_custom_time_hours'] != $jbbrd_options['custom_time_hours'] ) || ( $_POST['jbbrd_custom_time_min'] != $jbbrd_options['custom_time_min'] ) ) 
				$jbbrd_reset_shedule_time = true;
			else
				$jbbrd_reset_shedule_time = false;
			
			if ( ( isset( $_POST['jbbrd_post_per_page'] ) ) && ( '' != $_POST['jbbrd_post_per_page'] ) && ( 0 < $_POST['jbbrd_post_per_page'] ) && ( is_numeric( $_POST['jbbrd_post_per_page'] ) ) ) {
				$jbbrd_options['post_per_page'] = esc_html( ltrim( $_POST['jbbrd_post_per_page'], '0' ) );
			} elseif ( '-1' == $_POST['jbbrd_post_per_page'] ) {
				$jbbrd_options['post_per_page'] = '-1';
			}

			/* Set salary monetary unit. */
			switch ( $_POST['jbbrd_select_money_unit'] ) {
				case 'preset':
					$jbbrd_options['select_money_unit'] = 'preset';
					$jbbrd_options['money_choose'] = $_POST['jbbrd_money_choose'];
					$jbbrd_options['custom_money_unit'] = stripslashes( esc_html( $_POST['jbbrd_custom_money_unit'] ) );
					break;
				case 'custom':
					if ( isset( $_POST['jbbrd_custom_money_unit'] ) && '' != trim( stripslashes( esc_html( $_POST['jbbrd_custom_money_unit'] ) ) ) ) {
						$jbbrd_options['select_money_unit'] = 'custom';
						$jbbrd_options['money_choose'] = $_POST['jbbrd_money_choose'];
						$jbbrd_options['custom_money_unit'] = trim( stripslashes( esc_html( $_POST['jbbrd_custom_money_unit'] ) ) );
					} else {
						$error .= '<strong>' . __( 'Error:', 'job-board' ) . '</strong>&nbsp;' . __( 'Value entered in "Custom monetary unit" field is empty.', 'job-board' ) . '&nbsp;<br />';
					}
					break;
				default:
					break;
			}

			/* Set time period unit. */
			if ( isset( $_POST['jbbrd_time_period_choose'] ) && '' != $_POST['jbbrd_time_period_choose'] )
				$jbbrd_options['time_period_choose'] = esc_html( $_POST['jbbrd_time_period_choose'] );

			/* Set logo position. */
			if ( isset( $_POST['jbbrd_logo_position'] ) && '' != $_POST['jbbrd_logo_position'] )
				$jbbrd_options['logo_position'] = esc_html( $_POST['jbbrd_logo_position'] );

			/* Frontend filter form */
			$jbbrd_options['frontend_form'] = ( isset( $_POST['jbbrd_frontend_form'] ) ) ? 1 : 0;
			$jbbrd_options['frontend_form_non_registered'] = ( isset( $_POST['jbbrd_frontend_form'] ) && isset( $_POST['jbbrd_frontend_form_non_registered'] ) ) ? 1 : 0;

			/* Backend vacancy edit location metafield select show. */
			$jbbrd_options['location_select'] = isset( $_POST['jbbrd_location_select'] ) ? 1 : 0;

			/* Set time period unit. */
			if ( ( isset( $_POST['jbbrd_vacancy_reply_text'] ) ) && ( '' != $_POST['jbbrd_vacancy_reply_text'] ) )
				$jbbrd_options['vacancy_reply_text'] = stripslashes( esc_html( $_POST['jbbrd_vacancy_reply_text'] ) );

			/* Set archieving period. */
			if ( ( isset( $_POST['jbbrd_archieving_period'] ) ) && ( '' != $_POST['jbbrd_archieving_period'] ) && ( is_numeric( $_POST['jbbrd_archieving_period'] ) ) )
				$jbbrd_options['archieving_period'] = esc_html( ltrim( $_POST['jbbrd_archieving_period'], '0' ) );

			/* Set custom time to start move to archive shedule. */
			if ( ( isset( $_POST['jbbrd_custom_time_hours'] ) ) && ( '' != $_POST['jbbrd_custom_time_hours'] ) && ( is_numeric( $_POST['jbbrd_custom_time_hours'] ) ) ) {
				$jbbrd_options['custom_time_hours'] = 23 < $_POST['jbbrd_custom_time_hours'] ? 23 : esc_html( ltrim( $_POST['jbbrd_custom_time_hours'], '0' ) );
			}
			if ( ( isset( $_POST['jbbrd_custom_time_min'] ) ) && ( '' != $_POST['jbbrd_custom_time_min'] ) && ( is_numeric( $_POST['jbbrd_custom_time_min'] ) ) ) {
				$jbbrd_options['custom_time_min'] = 59 < $_POST['jbbrd_custom_time_min'] ? 59 : esc_html( ltrim( $_POST['jbbrd_custom_time_min'], '0' ) );
			}

			if ( '' == $error ) {
				/* Update options in the database. */
				update_option( 'jbbrd_options', $jbbrd_options );
				$message = __( "Settings saved.", 'job-board' );
				/* Set new time for sendmail shedule, if time is changed. */
				if ( true == $jbbrd_reset_shedule_time ) {
					/* Set GMT timezone. */ 
					$jbbrd_GMT = get_option('gmt_offset');
					/* Set new time. */
					$jbbrd_shedule_time = ( $jbbrd_options['custom_time_hours'] * 3600 ) + ( $jbbrd_GMT * 3600 ) + ( $jbbrd_options['custom_time_min'] * 60 );
					/* Clear old schedule hook time set. */
					wp_clear_scheduled_hook( 'jbbrd_move_vacancies_to_archive_dayly_function' );
					/* Set new schedule hook. */
					jbbrd_move_to_archive_schedule( $jbbrd_shedule_time );
				}				
			} else {
				$error .= __( 'Settings are not saved.', 'job-board' );
			}
		}

		/* Warning if not found shortcode. */
		if ( '' == $jbbrd_options['shortcode_permalink'] ) {
			$warning .= '<strong>' . __( 'Warning:', 'job-board' ) . '</strong>&nbsp;' . __( 'Shortcode is not found.', 'job-board' ) . '&nbsp;<br />' . 
				sprintf( __( 'Please add jobs shortcode to your page or post to display content in the frontend using %s button', 'job-board' ), '<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ) . '.<br />';
		}
		/* Warning if not found sender plugin. */
		if ( isset( $jbbrd_sender_not_found ) ) {
			$warning .= '<strong>' . __( 'Warning:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_sender_not_found . '<br />';
		}
		if ( isset( $jbbrd_sender_not_active ) || '' != $jbbrd_sender_not_active ) {
			$warning .= '<strong>' . __( 'Warning:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_sender_not_active . '<br />';
		} 

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$jbbrd_options = $jbbrd_option_defaults;
			update_option( 'jbbrd_options', $jbbrd_options );
			$message = __( 'All plugin settings were restored.', 'job-board' );
		} ?>
		<div class="wrap">
			<h1><?php _e( 'Job Board settings', 'job-board' ); ?></h1>
			<div class="updated fade" <?php if ( ! isset( $message ) ) echo 'style="display:none;" '; ?>>
				<p><strong><?php if ( isset( $message ) ) echo $message; ?></strong></p>
			</div>
			<?php bws_show_settings_notice(); ?>
			<div class="error" <?php if ( '' == $error ) echo 'style="display:none"'; ?>><p><?php echo $error; ?></p></div>
			<?php if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
				bws_form_restore_default_confirm( $plugin_basename );
			} else { ?>
				<div class="update-nag" style="margin: 5px 0px; <?php if ( '' == $warning ) echo ' display:none; '; ?>"><?php echo $warning; ?></div>
				<br/>
				<div><?php printf( 
					__( "If you would like to display jobs or registration form in your page or post, please use %s button", 'job-board' ), 
					'<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ); ?> 
					<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
						<div class="bws_hidden_help_text" style="min-width: 180px;">
							<?php printf( 
								__( "You can add jobs list or registration form to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s to display jobs or the shortcode %s to display registration form", 'job-board' ), 
								'<code><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt="" /></code>',
								'<code>[jbbrd_vacancy]</code>',
								'<code>[jbbrd_registration]</code>'
							); ?>
						</div>
					</div>
				</div>
				<form class="bws_form" method="post" action="">
					<table class="form-table">
						<tr valign="top">
							<th><?php _e( 'Number of jobs on page', 'job-board' ); ?></th>
							<td><input class="small-text" type="number" min="-1" max="99" step="1" name="jbbrd_post_per_page" value="<?php if ( isset( $jbbrd_options['post_per_page'] ) ) echo stripslashes( $jbbrd_options['post_per_page'] ); else echo stripslashes( $jbbrd_option_defaults['post_per_page'] ); ?>"/> <?php _e( 'per page', 'job-board' ); ?><br />
								<span class="bws_info">(<?php _e( 'You can set -1 to to show all jobs', 'job-board' ); ?>)</span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Salary monetary unit', 'job-board' ); ?></th>
							<td>
								<input type="radio" id="preset_money_unit_select" name="jbbrd_select_money_unit" value="preset" <?php if ( 'preset' == $jbbrd_options['select_money_unit'] ) echo 'checked="checked" '; ?>/>
								<select name="jbbrd_money_choose">
									<?php foreach ( $jbbrd_options['money'] as $money_unit ) {
										/* Output each select option line, check against the last $_GET to show the current option selected. */
										echo '<option value="' . $money_unit . '"';
											if ( $money_unit == $jbbrd_options['money_choose'] ) echo ' selected="selected"';
										echo '">' . $money_unit . '</option>';
									} ?>
								</select><br/>
								<input type="radio" id="custom_money_unit_select" name="jbbrd_select_money_unit" value="custom" <?php if ( 'custom' == $jbbrd_options['select_money_unit'] ) echo "checked=\"checked\" "; ?>/> 
								<input type="text" name="jbbrd_custom_money_unit" value="<?php echo $jbbrd_options['custom_money_unit']; ?>" maxlength="100" />
								<span class="bws_info">(<?php _e( "Custom monetary unit", 'job-board' ); ?>)</span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Salary period unit', 'job-board' ); ?></th>
							<td>
								<select name="jbbrd_time_period_choose">
									<?php foreach ( $jbbrd_options['time_period'] as $key => $money_unit ) {
										/* Output each select option line, check against the last $_GET to show the current option selected. */
										echo '<option value="' . $money_unit . '"';
										if ( $money_unit == $jbbrd_options['time_period_choose'] )
											echo ' selected="selected"';
										echo '">' . $money_unit . '</option>';
									} ?>
								</select>
								<span class="bws_info"> (<?php _e( 'Time period for current salary', 'job-board' ); ?>)</span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Frontend logo position', 'job-board' ); ?></th>
							<td><fieldset>
								<label>
									<input type="radio" name="jbbrd_logo_position" value="left" <?php if ( 'left' == $jbbrd_options['logo_position'] ) echo ' checked="checked"'; ?> />
									<?php _e( 'Left', 'job-board' ); ?>
								</label><br />
								<label>
									<input id="jbbrd_logo_position_right" type="radio" name="jbbrd_logo_position" value="right" <?php if ( 'right' == $jbbrd_options['logo_position'] ) echo ' checked="checked"'; ?> />
									<?php _e( 'Right', 'job-board' ); ?>
								</label>
							</fieldset></td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Display frontend form', 'job-board' ); ?></th>
							<td>
								<input type="checkbox" name="jbbrd_frontend_form" value="1" <?php if ( 1 == $jbbrd_options['frontend_form'] ) echo ' checked="checked"'; ?> /> 
								<span class="bws_info"><?php _e( 'Display job filtering form in frontend.' , 'job-board' ); ?></span><br />
								<input type="checkbox" name="jbbrd_frontend_form_non_registered" value="1" <?php if ( 1 == $jbbrd_options['frontend_form_non_registered'] ) echo ' checked="checked"'; ?> /> 
								<span class="bws_info"><?php _e( 'Display job filtering form in frontend for non-registered users' , 'job-board' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Location field type', 'job-board' ); ?></th>
							<td>
								<input type="checkbox" name="jbbrd_location_select" value="1" <?php if ( 1 == $jbbrd_options['location_select'] ) echo ' checked="checked"'; ?> /> 
								<span class="bws_info"><?php _e( 'Change location field in frontens sorting form to select box of all locations which already add to jobs base.' , 'job-board' ); ?></span>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Template text for CV sending', 'job-board' ); ?></th>
							<td>
								<textarea name="jbbrd_vacancy_reply_text"><?php if ( isset( $jbbrd_options['vacancy_reply_text'] ) ) echo stripslashes( $jbbrd_options['vacancy_reply_text'] ); else echo stripslashes( $jbbrd_option_defaults['vacancy_reply_text'] ); ?></textarea>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Job offers default expiry time', 'job-board' ); ?></th>
							<td>
								<input class="small-text" type="number" min="0" max="999" step="1" name="jbbrd_archieving_period" value="<?php if ( isset( $jbbrd_options['archieving_period'] ) ) echo stripslashes( $jbbrd_options['archieving_period'] ); else echo stripslashes( $jbbrd_option_defaults['archieving_period'] ); ?>"/>
								<?php _e( 'days', 'job-board' ); ?>
							</td>
						</tr>
						<tr valign="top">
							<th><?php _e( 'Daily archiving time', 'job-board' ); ?></th>
							<td>
								<input class="small-text" type="number" min="0" max="23" step="1" name="jbbrd_custom_time_hours" value="<?php if ( isset( $jbbrd_options['custom_time_hours'] ) ) echo stripslashes( $jbbrd_options['custom_time_hours'] ); else echo stripslashes( $jbbrd_option_defaults['custom_time_hours'] ); ?>"/>
								<?php _e( 'hours', 'job-board' ); ?>
								<input class="small-text" type="number" min="0" max="59" step="1" name="jbbrd_custom_time_min" value="<?php if ( isset( $jbbrd_options['custom_time_min'] ) ) echo stripslashes( $jbbrd_options['custom_time_min'] ); else echo stripslashes( $jbbrd_option_defaults['custom_time_min'] ); ?>"/>
								<?php _e( 'min', 'job-board' ); ?>
							</td>
						</tr>
					</table>					
					<p class="submit_div">
						<input id="bws-submit-button" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'job-board' ); ?>" />
						<input type="hidden" name="jbbrd_form_submit" value="submit" />
						<?php wp_nonce_field( $plugin_basename, 'jbbrd_nonce_name' ); ?>
					</p>					
				</form>
				<?php bws_form_restore_default_settings( $plugin_basename );				
			} 
			bws_plugin_reviews_block( $jbbrd_plugin_info['Name'], 'job-board' );  ?>
		</div>
	<?php } 
}

/**
 * Add Job Board JS.
 * @return void
 */
if ( ! function_exists ( 'jbbrd_load_scripts' ) ) {
	function jbbrd_load_scripts() {
		/* Add main styles. */
		wp_enqueue_style( 'jbbrd_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );

		wp_enqueue_script( 'jbbrd_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery', 'jquery-ui-slider' ) );
		/* Add datepicker script. */
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}
}

/**
 * Job update messages.
 * @param array $messages Existing post update messages.
 * @return array Amended post update messages with new CPT update messages.
 */
if ( ! function_exists( 'jbbrd_vacancy_updated_messages' ) ) {
	function jbbrd_vacancy_updated_messages( $messages ) {
		global $jbbrd_options;

		$post             = get_post();
		$post_type        = get_post_type( $post );
		$post_type_object = get_post_type_object( $post_type );

		$messages['vacancy'] = array(
			0  => '', /* Unused. Messages start at index 1. */
			1  => __( 'Job updated.', 'job-board' ),
			2  => __( 'Custom field updated.', 'job-board' ),
			3  => __( 'Custom field deleted.', 'job-board' ),
			4  => __( 'Job updated.', 'job-board' ),
			5  => isset( $_GET['revision'] ) ? ( __( 'Job restored to revision from', 'job-board' ) . ' ' . wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6  => __( 'Job published.', 'job-board' ),
			7  => __( 'Job saved.', 'job-board' ),
			8  => __( 'Job submitted.', 'job-board' ),
			9  => __( 'Job scheduled for:', 'job-board' ) . ' <strong>' . date_i18n( 'M j, Y @ G:i', strtotime( $post->post_date ) ) . '</strong>.',
			10 => __( 'Job draft updated.', 'job-board' )
		);

		if ( $post_type_object->publicly_queryable && get_post_type() === 'vacancy' ) {
			/* Create links to current job, if shortcode worked right. */
			if ( '' == $jbbrd_options['shortcode_permalink'] ) {
				$view_link = $preview_link = '';
			} else {
				$vacancy_id_slug	= ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option( 'home' ) ) ) ? '&vacancy_id=' : '?vacancy_id=';
				$view_link 			= sprintf( ' <a href="%s">%s</a>', $jbbrd_options['shortcode_permalink'] . $vacancy_id_slug . get_the_id(), __( 'View job', 'job-board' ) );
				$preview_link 		= sprintf( ' <a target="_blank" href="%s">%s</a>', $jbbrd_options['shortcode_permalink'] . $vacancy_id_slug . get_the_id(), __( 'View job', 'job-board' ) );
			}
			$messages[ $post_type ][1] .= $view_link;
			$messages[ $post_type ][6] .= $view_link;
			$messages[ $post_type ][9] .= $view_link;

			$messages[ $post_type ][8]  .= $preview_link;
			$messages[ $post_type ][10] .= $preview_link;
		}
		return $messages;
	}
}

/**
 * Add capabilities for administrators.
 * @return void
 */
if ( ! function_exists( 'jbbrd_set_administrators_capabilities' ) ) {
	function jbbrd_set_administrators_capabilities() {
		/* Get the role object. */
		$administrator = get_role( 'administrator' );
		/* A list of capabilities to add to administrators. */
		$caps = array(
			'edit_vacancies',
			'edit_others_vacancies',
			'delete_vacancies',
			'delete_others_vacancies',
			'delete_private_vacancies',
			'edit_private_vacancies',
			'read_private_vacancies',
			'edit_published_vacancies',
			'publish_vacancies',
			'delete_published_vacancies',
			'edit_vacancy',
			'delete_vacancy',
			'read_vacancy',
			/* Set capabilities to 'jbbrd_businesses' custom taxonomy. */
			'manage_jbbrd_businesses_tags',
			'edit_jbbrd_businesses_tags',
			'delete_jbbrd_businesses_tags',
			'assign_jbbrd_businesses_tags',
			/* Set capabilities to 'jbbrd_employment' custom taxonomy. */
			'manage_jbbrd_employment_tags',
			'edit_jbbrd_employment_tags',
			'delete_jbbrd_employment_tags',
			'assign_jbbrd_employment_tags',
		);
		foreach ( $caps as $cap ) {
			/* Add the capability. */
			$administrator->add_cap( $cap );
		}
	}
}

/**
 * Remove capabilities from administrators.
 * @return void
 */
if ( ! function_exists( 'jbbrd_remove_administrators_capabilities' ) ) {
	function jbbrd_remove_administrators_capabilities() {
		/* Get the role object. */
		$administrator = get_role( 'administrator' );
		/* A list of capabilities to remove from administrators. */
		$caps = array(
			'edit_vacancies',
			'edit_others_vacancies',
			'delete_vacancies',
			'delete_others_vacancies',
			'delete_private_vacancies',
			'edit_private_vacancies',
			'read_private_vacancies',
			'edit_published_vacancies',
			'publish_vacancies',
			'delete_published_vacancies',
			'edit_vacancy',
			'delete_vacancy',
			'read_vacancy',
			/* Unset capabilities to 'jbbrd_businesses' custom taxonomy. */
			'manage_jbbrd_businesses_tags',
			'edit_jbbrd_businesses_tags',
			'delete_jbbrd_businesses_tags',
			'assign_jbbrd_businesses_tags',
			/* Unset capabilities to 'jbbrd_employment' custom taxonomy. */
			'manage_jbbrd_employment_tags',
			'edit_jbbrd_employment_tags',
			'delete_jbbrd_employment_tags',
			'assign_jbbrd_employment_tags',
		);
		foreach ( $caps as $cap ) {
			/* Remove the capability. */
			$administrator->remove_cap( $cap );
		}
	}
}

/**
  * Create employer and job candidate role.
  * @return void
  */
if ( ! function_exists( 'jbbrd_roles_create' ) ) {
	function jbbrd_roles_create() {
		/* Create employer role. */
		$employer_add = add_role(
			'employer', 'Employer',
			array(
				'edit_vacancies'					=> true,
				'delete_vacancies'					=> true,
				'delete_private_vacancies'			=> true,
				'edit_private_vacancies'			=> true,
				'read_private_vacancies'			=> true,
				'edit_published_vacancies'			=> true,
				'publish_vacancies'					=> true,
				'delete_published_vacancies'		=> true,
				'edit_vacancy'						=> true,
				'delete_vacancy'					=> true,
				'read_vacancy'						=> true,
				'read'								=> true,
				'assign_jbbrd_businesses_tags'		=> true,
				'assign_jbbrd_employment_tags' 		=> true,
				'upload_files'						=> true,
			)
		);
		/* Create job_candidate role. */
		$job_candidate_add = add_role(
			'job_candidate', 'Job Candidate',
			array(
				'read_private_vacancies'			=> true,
				'read_vacancy'						=> true,
				'read'								=> true,
			)
		);
	}
}

/**
 * Add view action to title row on vacancy posts view.
 * @param array $actions Existing post update actions.
 * @return array Amended post update actions with new CPT update actions.
 */
if ( ! function_exists( 'jbbrd_change_title_row_actions' ) ) {
	function jbbrd_change_title_row_actions( $actions ) {
		global $jbbrd_options;		
		if ( get_post_type() === 'vacancy' ) {
			if ( '' == $jbbrd_options['shortcode_permalink'] ) {
				$actions['view'] = __( 'Link is not found!', 'job-board' );
			} else {
				$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
				$actions['view'] = '<a href="' . $jbbrd_options['shortcode_permalink'] . $vacancy_id_slug . get_the_id() . '" title="' . esc_attr( __( 'View this offer', 'job-board' ) ) . '">' . __( 'View', 'job-board' ) . '</a>';
			}
		}
		return $actions;
	}
}

/**
 * Set custom columns to "vacancy" custom post editor menu.
 * @param $wp_admin_bar array() of nodes
 * @return void
 */
if ( ! function_exists( 'jbbrd_add_relative_view' ) ) {
	function jbbrd_add_relative_view( $wp_admin_bar ) {
		global $jbbrd_options, $pagenow;
		if ( get_post_type() === 'vacancy' && 'post.php' == $pagenow ) {
			if ( '' == $jbbrd_options['shortcode_permalink'] ) {
				$args = array(
					'id'    => 'link_not_found',
					'title' => 'Shortcode not found!',
				);
			} else {
				$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
				$args = array(
					'id'    => 'view_vacancy',
					'title' => __( 'View Job', 'job-board' ),
					'href'  => $jbbrd_options['shortcode_permalink'] . $vacancy_id_slug . get_the_id(),
				);
			}
			$wp_admin_bar->add_node( $args );
		}
	}
}

/**
 * Set custom columns to "vacancy" custom post editor menu.
 * @param $wp_admin_bar array() of nodes
 * @return void
 */
if ( ! function_exists( 'jbbrd_remove_view' ) ) {
	function jbbrd_remove_view( $wp_admin_bar ) {
		global $jbbrd_options;
		if ( get_post_type() === 'vacancy' ) {
			$wp_admin_bar->remove_node( 'view' );
		}
	}
}

/**
 * Set custom columns to "vacancy" custom post editor menu.
 * @param $column string vacancy column
 * @return void
 */
if ( ! function_exists( 'jbbrd_custom_columns' ) ) {
	function jbbrd_custom_columns( $column ) {
		global $post, $jbbrd_options, $jbbrd_option_defaults;
		/* get the value of currency */
		if ( 'preset' == $jbbrd_options['select_money_unit'] ) {
			$money_choose = $jbbrd_options['money_choose'];
		} else if ( 'custom' == $jbbrd_options['select_money_unit'] ) {
			$money_choose = $jbbrd_options['custom_money_unit'];
		}
		switch ( $column ) {
			case "jbbrd_title":
				echo '<a href="#">' . get_the_title() . '</a>';
				break;
			case "jbbrd_location":
				$jbbrd_custom = get_post_custom();
				if ( isset ( $jbbrd_custom["jbbrd_location"][0] ) )
					echo $jbbrd_custom["jbbrd_location"][0] . "<br />";
				else echo 'n/a';
				break;
			case "jbbrd_organization":
				$jbbrd_custom = get_post_custom();
				if ( isset ( $jbbrd_custom["jbbrd_organization"][0] ) )
					echo $jbbrd_custom["jbbrd_organization"][0] . "<br />";
				else echo 'n/a';
				break;
			case "jbbrd_employment-categorie":
				$jbbrd_employment_categories = get_the_terms( 0, "jbbrd_employment" );
				$jbbrd_employment_categories_html = array();
				if ( is_array( $jbbrd_employment_categories ) ) {
					if ( '' == $jbbrd_options['shortcode_permalink'] ) {
						foreach ( $jbbrd_employment_categories as $jbbrd_employment_categorie )
							array_push( $jbbrd_employment_categories_html, $jbbrd_employment_categorie->name );
						echo implode( $jbbrd_employment_categories_html, ", " );
					} else {
						foreach ( $jbbrd_employment_categories as $jbbrd_employment_categorie )
							array_push( $jbbrd_employment_categories_html, '<a href="' . $jbbrd_options['shortcode_permalink'] . '&employment_category=' . $jbbrd_employment_categorie->slug . '">' . $jbbrd_employment_categorie->name . '</a>' );
						echo implode( $jbbrd_employment_categories_html, ", " );
					}
				}
				break;
			case "jbbrd_vacancy-categorie":
				$jbbrd_categories = get_the_terms( 0, "jbbrd_businesses" );
				$jbbrd_categories_html = array();
				if ( is_array( $jbbrd_categories ) ) {
					if ( '' == $jbbrd_options['shortcode_permalink'] ) {
						foreach ( $jbbrd_categories as $jbbrd_categorie )
							array_push( $jbbrd_categories_html, $jbbrd_categorie->name );
						echo implode( $jbbrd_categories_html, ", " );
					} else {
						foreach ( $jbbrd_categories as $jbbrd_categorie )
							array_push( $jbbrd_categories_html, '<a href="' . $jbbrd_options['shortcode_permalink'] . '&category=' . $jbbrd_categorie->slug . '">' . $jbbrd_categorie->name . '</a>' );
						echo implode( $jbbrd_categories_html, ", " );
					}
				}
				break;
			case "jbbrd_salary":
				$jbbrd_custom = get_post_custom();
				if ( isset ( $jbbrd_custom["salary"][0] ) ) {
					echo $jbbrd_custom["salary"][0] . ' ' . $money_choose . '/';
					if ( isset( $jbbrd_options['time_period_choose'] ) ) {
						echo $jbbrd_options['time_period_choose'];
					} else {
						echo $jbbrd_option_defaults['time_period_choose'];
					}
					echo "<br />";
				} else
					echo 'n/a';
				break;
			case "jbbrd_archive":
				$jbbrd_custom = get_post_custom();
				if ( isset ( $jbbrd_custom["archive"][0] ) ) {
					echo 'archived';
				}
				break;
		}
	}
}

/**
 * Set vacancy custom post column names.
 * @param array $columns Existing post update columns.
 * @return array post update columns with new CPT update columns.
 */
if ( ! function_exists( 'jbbrd_edit_columns' ) ) {
	function jbbrd_edit_columns( $columns ) {
		$columns = array(
			"cb" 							=> "<input type=\"checkbox\" />",
			"title" 						=> __( "Job offer", 'job-board' ),
			"jbbrd_location" 				=> __( "Location", 'job-board' ),
			"jbbrd_organization" 			=> __( "Organization", 'job-board' ),
			"jbbrd_employment-categorie" 	=> __( "Employment", 'job-board' ),
			"jbbrd_vacancy-categorie"		=> __( "Categories", 'job-board' ),
			"jbbrd_salary" 					=> __( "Salary", 'job-board' ),
			"jbbrd_archive" 				=> __( "Archive", 'job-board' ),
			"date"							=> __( "Date", 'job-board' ),
		);
		return $columns;
	}
}

/**
 * Making content columns.
 * @param array $columns Existing post update columns.
 * @return array post update columns with new CPT update columns.
 */
if ( ! function_exists( 'jbbrd_sortable_columns' ) ) {
	function jbbrd_sortable_columns( $columns ) {
		$columns['jbbrd_location']		= "jbbrd_location";
		$columns['jbbrd_organization']	= "jbbrd_organization";
		$columns['jbbrd_salary']		= "salary";
		$columns['jbbrd_date']			= "date";
		return $columns;
	}
}/* End of making content columns. */

/**
 * Making content columns sortable.
 * @param $query array() query array
 * @return void
 */
if ( ! function_exists( 'jbbrd_sortable_columns_orderby' ) ) {
	function jbbrd_sortable_columns_orderby( $query ) {
		if ( ! is_admin() )
			return;

		$orderby = $query->get( 'orderby' );

		if ( 'jbbrd_location' == $orderby ) {
			$query->set( 'meta_key', 'jbbrd_location' );
		} else if ( 'jbbrd_organization' == $orderby ) {
			$query->set( 'meta_key', 'jbbrd_organization' );
		} else if ( 'salary' == $orderby ) {
			$query->set( 'meta_key', 'salary' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}

/**
 * Add selectbox of vacancy Categories to filter.
 * @return void
 */
if ( ! function_exists( 'jbbrd_restrict_manage_posts' ) ) {
	function jbbrd_restrict_manage_posts() {
		/* Only display these taxonomy filters on desired custom post_type listings. */
		global $typenow;
		if ( $typenow == 'vacancy' ) {
			/* Set "Job categories" taxonomy slugs you want to filter by. */
			$tax_slug = 'jbbrd_businesses';
			print( '<div id="jbbrd_businesses_header" style="float:left; padding:6px 5px 0 5px;">' . __( 'Job categories:', 'job-board' ) . '</div>' );
			/* Retrieve the taxonomy object. */
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			/* Retrieve array of term objects per taxonomy. */
			$terms = get_terms( $tax_slug );
			/* Output html for taxonomy dropdown filter. */
			print( '<select name=' . $tax_slug . ' id=' . $tax_slug . ' class="jbbrd_postform">' );
			print( '<option value="">' . __( 'Show all', 'job-board' ) . '</option>' );
			foreach ( $terms as $term ) {
				/* Output each select option line, check against the last $_GET to show the current option selected. */
				echo '<option value='. $term->slug;
				if ( isset( $_GET[ $tax_slug ] ) ) {
					echo $_GET[ $tax_slug ] == $term->slug ? ' selected="selected"' : '';
				}
				echo '>' . $term->name . ' (' . $term->count . ')</option>';
			}
			echo "</select>";
			/* Set "Archive status" taxonomy slugs you want to filter by. */
			$tax_slug = 'archive';
			print( '<div id="jbbrd_archive_header" style="float:left; padding:6px 5px 0 5px;">' . __( 'Archived jobs:', 'job-board' ) . '</div>' );
			/* Retrieve the taxonomy object. */
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			/* Retrieve array of term objects per taxonomy. */
			$terms = get_terms( $tax_slug );
			/* Output html for taxonomy dropdown filter. */
			print( '<select name=' . $tax_slug . ' class="jbbrd_postform">' );
			print( '<option value="">' . __( 'Show all', 'job-board' ) . '</option>' );
			foreach ( $terms as $term ) {
				/* Output each select option line, check against the last $_GET to show the current option selected. */
				echo '<option value='. $term->slug;
				if ( isset( $_GET[ $tax_slug ] ) ) {
					echo $_GET[ $tax_slug ] == $term->slug ? ' selected="selected"' : '';
				}
				echo '>' . $term->name . ' (' . $term->count . ')</option>';
			}
			echo "</select>";
			/* Set "Archive status" taxonomy slugs you want to filter by. */
			$tax_slug = 'jbbrd_employment';
			print( '<div id="jbbrd_employment_header" style="float:left; padding:6px 5px 0 5px;">' . __( 'Employment type:', 'job-board' ) . '</div>' );
			/* Retrieve the taxonomy object. */
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			/* Retrieve array of term objects per taxonomy. */
			$terms = get_terms( $tax_slug );
			/* Output html for taxonomy dropdown filter. */
			print( '<select name=' . $tax_slug . ' class="jbbrd_postform">' );
			print( '<option value="">' . __( 'Show all', 'job-board' ) . '</option>' );
			foreach ( $terms as $term ) {
				/* Output each select option line, check against the last $_GET to show the current option selected. */
				echo '<option value='. $term->slug;
				if ( isset( $_GET[ $tax_slug ] ) ) {
					echo $_GET[ $tax_slug ] == $term->slug ? ' selected="selected"' : '';
				}
				echo '>' . $term->name . ' (' . $term->count . ')</option>';
			}
			echo "</select>";
		}
	}
}

/**
 * Set post custom metafields values.
 * @return void
 */
if ( ! function_exists( 'jbbrd_meta_personal' ) ) {
	function jbbrd_meta_personal() {
		global $post, $jbbrd_options, $error;
		/* get the value of currency */
		if ( 'preset' == $jbbrd_options['select_money_unit'] ) {
			$money_choose = $jbbrd_options['money_choose'];
		} else if ( 'custom' == $jbbrd_options['select_money_unit'] ) {
			$money_choose = $jbbrd_options['custom_money_unit'];
		}
	
		$jbbrd_custom = get_post_custom( $post->ID );
		/* Set values. */
		$demands = ( isset( $jbbrd_custom["demands"][0] ) ) ? $jbbrd_custom["demands"][0] : '';
		$jbbrd_location = ( isset( $jbbrd_custom["jbbrd_location"][0] ) ) ? $jbbrd_custom["jbbrd_location"][0] : '';
		$jbbrd_organization = ( isset( $jbbrd_custom["jbbrd_organization"][0] ) ) ? $jbbrd_custom["jbbrd_organization"][0] : '';

		/* If ('salary') not numeric = 'salary' = '0' */
		$salary = ( isset( $jbbrd_custom["salary"][0] ) && 0 < $jbbrd_custom["salary"][0] && is_numeric( $jbbrd_custom["salary"][0] ) ) ? $jbbrd_custom["salary"][0] : '0';
		/* Expiry date. */
		if ( isset( $jbbrd_custom["expiry_date"][0] ) ) {
			$format 		= 'm/d/Y';
			$value			= date( $format, strtotime( $jbbrd_custom["expiry_date"][0] ) );
			$expiry_date 	= $value;
		} else {
			$expiry_date 	= '';
		}
		/* Archive status. */
		if ( isset( $jbbrd_custom["archive"][0] ) ) {
			$archive = $jbbrd_custom["archive"][0];
			wp_set_object_terms( $post->ID, 'archived', 'archive', false );
		} else {
			$archive = '';
			wp_set_object_terms( $post->ID, 'posted', 'archive', false );
		}
		/* Use nonce for verification. */
		wp_nonce_field( plugin_basename(__FILE__), 'jbbrd_noncename' );
		echo '<span style="color:red;">' . $error . '</span>'; ?>
		<div class="personal">
			<div id="jbbrd_requirements">
				<table border="0" class="jbbrd_table_inside">
					<tr>
						<td class="jbbrd_demand_field"><label><?php _e( 'Requirements', 'job-board' ); ?></label></td>
						<td class="jbbrd_demand_input"><textarea name="demands"><?php echo $demands; ?></textarea></td>
					</tr>
				</table><!-- .table_inside -->
			</div>
			<div id="jbbrd_metaboxes">
				<table border="0">
					<tr>
						<td class="jbbrd_personal_field"><label id="jbbrd_location_input_label" for="jbbrd_location_input"><?php _e( 'Location', 'job-board' ); ?></label><?php if ( '' == $jbbrd_location ) { ?><span style="color:red">*</span><?php } ?></td>
						<td class="jbbrd_personal_input"><input type="text" maxlength="250" id="jbbrd_location_input" required="required" name="jbbrd_location" value="<?php echo $jbbrd_location; ?>" placeholder="<?php _e( 'Location', 'job-board' ); ?>" /></td>
					</tr>
					<tr>
						<td class="jbbrd_personal_field"><label id="jbbrd_organization_input_label" for="jbbrd_organization_input"><?php _e( 'Organization', 'job-board' ); ?></label><?php if ( '' == $jbbrd_organization ) { ?><span style="color:red">*</span><?php } ?></td>
						<td class="jbbrd_personal_input"><input type="text" maxlength="250" id="jbbrd_organization_input" required="required" name="jbbrd_organization" value="<?php echo $jbbrd_organization; ?>" placeholder="<?php _e( 'Organization name', 'job-board' ); ?>" /></td>
					</tr>
					<tr>
						<td class="jbbrd_personal_field"><label id="jbbrd_salary_input_label" for="jbbrd_salary_input"><?php _e( 'Salary', 'job-board' ); ?></label></td>
						<td class="jbbrd_personal_input"><input type="number" min="0" id="jbbrd_salary_input" name="salary" value="<?php echo $salary; ?>" placeholder="0" /><span class="jbbrd_personal_field"><?php echo $money_choose . '/' . $jbbrd_options['time_period_choose']; ?></span></td>
					</tr>
					<tr>
						<td class="jbbrd_personal_field"><label id="jbbrd_expiry_date_label" for="jbbrd_expiry_date"><?php _e( 'Expiry date', 'job-board' ); ?></label></td>
						<td class="jbbrd_personal_input">
							<input type="text" maxlength="20" id="jbbrd_expiry_date" name="expiry_date" value="<?php echo $expiry_date; ?>" placeholder="MM/DD/YYYY" />
						</td>
					</tr>
					<tr>
						<td class="jbbrd_personal_field"></td>
						<td class="jbbrd_personal_input">
							<input type="checkbox" id="jbbrd_archive_checkbox" name="archive" style="display:none;" value="1" <?php if ( '' != $archive ) echo 'checked="checked"'; ?> />
						</td>
					</tr>
				</table>
			</div>
			<div id="jbbrd_requirements_metaboxes_clear"></div>
		</div><!-- .personal -->
	<?php }
}/* End of set post custom metafields values. */

/**
 * Move to archive expiried jobs.
 * @return array() of location metabox fields
 */
if ( ! function_exists( 'jbbrd_find_location_metabox_fields' ) ) {
	function jbbrd_find_location_metabox_fields() {
		global $wpdb;
		$jbbrd_location_metabox_fields_modified = array();
		/* Set tables names. */
		$postmeta_table = $wpdb->prefix . "postmeta";
		/* Set query. */
		$sql_query = "
			SELECT DISTINCT pm.`meta_value`
			FROM `" . $postmeta_table . "` AS pm
			WHERE pm.`meta_key` = 'jbbrd_location'
		";
		/* Set order. */
		$jbbrd_location_metabox_fields = $wpdb->get_results( $sql_query, ARRAY_A ); 
		if ( is_array( $jbbrd_location_metabox_fields ) ) {
			foreach ( $jbbrd_location_metabox_fields as $key => $value ) {
				foreach ( $value as $key_id => $value_id ) {
					$jbbrd_location_metabox_fields_modified[] = $value_id;
				}
			}
		}
		return $jbbrd_location_metabox_fields_modified;
	}
}

/**
 * Move to archive expiried jobs.
 * @return array() of ID`s of expiried jobs
 */
if ( ! function_exists( 'jbbrd_find_expiry_vacancies' ) ) {
	function jbbrd_find_expiry_vacancies() {
		global $wpdb;
		/* Set tables names. */
		$posts_table				= $wpdb->prefix . "posts";
		$term_relationships_table	= $wpdb->prefix . "term_relationships";
		$term_taxonomy_table 		= $wpdb->prefix . "term_taxonomy";
		$postmeta_table 			= $wpdb->prefix . "postmeta";
		$terms_table 				= $wpdb->prefix . "terms";
		/* Set query. */
		$sql_query = "
			SELECT DISTINCT wpp.`ID`
			FROM `" . $posts_table . "` AS wpp
			JOIN `" . $term_relationships_table . "` AS tr ON wpp.`ID` = tr.`object_id`
			JOIN `" . $term_taxonomy_table . "` AS ttt ON (tr.`term_taxonomy_id` = ttt.`term_taxonomy_id`
				AND ttt.`taxonomy` = 'archive')
			JOIN `" . $terms_table . "` AS tt ON (ttt.`term_id` = tt.`term_id`
				AND tt.`slug` = 'posted' )
			JOIN `" . $postmeta_table . "` AS pm ON (wpp.`ID` = pm.`post_id`
				AND pm.`meta_key` = 'expiry_date'
				AND UNIX_TIMESTAMP(NOW()) > UNIX_TIMESTAMP(pm.`meta_value`))
			AND wpp.`post_status` = 'publish'
			AND wpp.`post_type` = 'vacancy'
		";
		/* Set order. */
		$expiry_date_vacancies = $wpdb->get_results( $sql_query, ARRAY_A );
		return $expiry_date_vacancies;
	}
}

/**
 * Move to archive expiried jobs.
 * @return void
 */
if ( ! function_exists( 'jbbrd_set_archive_status_function' ) ) {
	function jbbrd_set_archive_status_function() {
		$expiry_date_vacancies = jbbrd_find_expiry_vacancies();
		if ( is_array( $expiry_date_vacancies ) ) {
			foreach ( $expiry_date_vacancies as $key => $value ) {
				foreach ( $value as $key_id => $value_id ) {
					update_post_meta( $value_id, "archive", 1 );
					wp_set_object_terms( $value_id, 'archived', 'archive', false );
				}
			}
		}
	}
}

/**
 * Set schedule for daily moving to archive for expirie jobs.
 * @return void
 */
if ( ! function_exists( 'jbbrd_move_to_archive_schedule' ) ) {
	function jbbrd_move_to_archive_schedule( $jbbrd_custom_time ) {
		if ( ! wp_next_scheduled( 'jbbrd_move_vacancies_to_archive_dayly_function' ) ) {
			$jbbrd_absolut_time = floor( time() / 86400 ) * 86400;
			wp_schedule_event( $jbbrd_absolut_time + $jbbrd_custom_time, 'daily', 'jbbrd_move_vacancies_to_archive_dayly_function' );
		}
	}
}

/**
 * Set admin error message on custom metafields error.
 * @return void
 */
if ( ! function_exists( 'jbbrd_metafields_error_admin_notice' ) ) {
	function jbbrd_metafields_error_admin_notice() {
		global $post, $hook_suffix, $jbbrd_plugin_info;
		if ( 'plugins.php' == $hook_suffix && ! is_network_admin() ) {
			bws_plugin_banner_to_settings( $jbbrd_plugin_info, 'jbbrd_options', 'job-board', 'admin.php?page=job-board.php', 'post-new.php?post_type=vacancy',  __( 'Job', 'job-board' ) );
		}
		/* Print the message. */		
		$notice = get_option( 'jbbrd_custom_metafield_error_notice' );
		if ( empty( $notice ) ) {
			return '';
		}
		foreach ( $notice as $id => $message ) {
			print_r( '<div class="error"><p>' . $message . '</p></div>' );
			/* Remove notice after its displayed. */
			unset( $notice[ $id ] );
			update_option( 'jbbrd_custom_metafield_error_notice', $notice );
			break;
		}
	}
}

/**
 * Insert post function.
 * @param $post_id string current post ID
 * @return void
 */
if ( ! function_exists( 'jbbrd_save_post' ) ) {
	function jbbrd_save_post( $post_id, $post = NULL ) {
		global $jbbrd_options;
		/* Set custom meta fields. */
		$jbbrd_meta_fields = array(
			"demands",
			"jbbrd_location",
			"jbbrd_organization",
			"salary",
			"resume-field",
			"archive",
			"expiry_date"
		);
		/* Verify this came from the our screen and with proper authorization. */
		if ( ! isset( $_POST['jbbrd_noncename'] ) ) {
			return $post_id;
		}
		if ( ! wp_verify_nonce( $_POST['jbbrd_noncename'], plugin_basename( __FILE__ ) ) ) {
			return $post_id;
		}
		/* Verify if this is an auto save. */ 
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_id;
		}
		/* Check metafields. */
		if ( ( ! isset( $_POST['jbbrd_location'] ) ) || ( empty( $_POST['jbbrd_location'] ) ) || ( ! isset( $_POST['jbbrd_organization'] ) ) || ( empty( $_POST['jbbrd_organization'] ) ) ) {
			$notice = get_option('jbbrd_custom_metafield_error_notice');
			$notice[ $post_id ] = '';
			if ( ! isset( $_POST['jbbrd_location'] ) || empty( $_POST['jbbrd_location'] )  ) {
				$notice[ $post_id ] .=  '<strong>' . __( 'Error:', 'job-board' ) . '</strong>' . ' ' . __( 'location field should not be empty', 'job-board' ) . '&nbsp;<strong>' . '(Job ID: ' . $post_id . ')' . '</strong>.<br />';
			}
			if ( ! isset( $_POST['jbbrd_organization'] ) || empty( $_POST['jbbrd_organization'] ) ) {
				$notice[ $post_id ] .=  '<strong>' . __( 'Error:', 'job-board' ) . '</strong>' . ' ' . __( 'organization field should not be empty.', 'job-board' ) . '<br />';
			}
			update_option( 'jbbrd_custom_metafield_error_notice', $notice );
			return $post_id;
		}
		/* Add values to metafields table. */
		if ( $post->post_type == "vacancy" ) {
			/* Loop through the POST data. */
			foreach ( $jbbrd_meta_fields as $key ) {
				if ( isset( $_POST[ $key ] ) ) {
					/* Change date format if fild is 'expiry_date'. */
					if ( $key == 'expiry_date' ) {
						$date_compl = stripslashes( esc_html( $_POST[ $key ] ) );
						/* Set 30 days period if date field is empty. */
						if ( '' == $date_compl ) {
							$jbbrd_default_time = $jbbrd_options['archieving_period'];
							$date_compl 		= get_the_date( 'U' );
							$format 			= 'Y-m-d 23:59:59';
							$value				= date( $format, $date_compl + ( 60 * 60 * 24 * $jbbrd_default_time ) );
						} else {
							$date_compl			= explode( "/", $date_compl );
							$format 			= 'Y-m-d 23:59:59';
							$value				= date( $format, strtotime( $date_compl[1] . "-" . $date_compl[0] . '-' . $date_compl[2] ) );
						}
					} else {
						$value = stripslashes( esc_html( ltrim( $_POST[ $key ], '0' ) ) );
					}
				} else {
					$value = '';
				}
				/* Delete meta if empty. */
				if ( '' == $value ) {
					delete_post_meta( $post_id, $key );
					continue;
				}
				/* Update meta. */
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}

if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
	/**
	 * Create class jbbrd_Manager to display list of messages.
	 */
	if ( ! class_exists( 'WP_List_Table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
	}

	if ( ! class_exists( 'jbbrd_Backend_Manager' ) ) {
		class jbbrd_Backend_Manager extends WP_List_Table {

			/**
			 * Constructor of class 
			 */
			function __construct() {
				global $status, $page;
				parent::__construct( array(
					'singular'  => __( 'vacancy', 'job-board' ),
					'plural'    => __( 'vacancies', 'job-board' ),
					'ajax'      => false,
					)
				);
			}

			/**
			 * Function to prepare data before display 
			 * @return void
			 */
			function prepare_items() {
				global $wpdb;
				if ( isset( $_POST['s'] ) ) {
					$search		= isset( $_POST['s'] ) ? htmlspecialchars( stripslashes( $_POST['s'] ) ) : "";
					$search		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $search ) ) );
				}
				$columns				= $this->get_columns();
				$hidden					= array();
				$sortable				= $this->get_sortable_columns();
				$this->_column_headers	= array( $columns, $hidden, $sortable );
				$per_page				= $this->get_items_per_page( 'vacancies_per_page', 30);
				$current_page			= $this->get_pagenum();
				$total_items			= $this->items_count();;
				$this->items			= array_slice( $this->job_list(), ( ( $current_page - 1 ) * $per_page ), $per_page );
				$this->set_pagination_args( array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				) );
			}

			/**
			 * Function to show message if no jobs found
			 * @return void
			 */
			function no_items() { ?>
				<p style="color:red;"><?php _e( 'No jobs found', 'job-board' ); ?></p>
			<?php }

			/**
			 * Get a list of columns.
			 * @return array list of columns and titles
			 */
			function get_columns(){
				$columns = array(
					'title'		=> __( 'Job offer', 'job-board' ),
					'author'	=> __( 'Salary', 'job-board' ),
					'date'		=> __( 'Date', 'job-board' ),
				);
				return $columns;
			}

			/**
			 * Get a list of sortable columns.
			 * @return array list of sortable columns
			 */
			function get_sortable_columns() {
				$sortable_columns = array(
					'title'		=> array( 'title', false ),
					'author'	=> array( 'author', false ),
					'date'		=> array( 'date', false ),
				);
				return $sortable_columns;
			}

			/**
			 * Fires when the default column output is displayed for a single row.
			 * @param string $column_name      The custom column's name.
			 * @param int    $item->comment_ID The custom column's unique ID number.
			 * @return void
			 */
			function column_default( $item, $column_name ) {
				switch( $column_name ) {
					case 'title':
					case 'author':
					case 'date':
						return $item[ $column_name ];
					default:
						return print_r( $item, true ) ; /* Print all array. */
				}
			}

			/**
			 * Function to get report list
			 * @return array() list of jobs
			 */
			function job_list() {
				global $wpdb, $jbbrd_options;
				$i = 0;
				$job_list       = array();  
				$jbbrd_search_category = get_user_meta( get_current_user_id(), 'jbbrd_job_candidate_category_choose', true ); 
				$jbbrd_term_id = term_exists( $jbbrd_search_category, 'jbbrd_businesses' );
				$jbbrd_term_id = $jbbrd_term_id['term_id'];
				/* get the value of currency */
				if ( 'preset' == $jbbrd_options['select_money_unit'] ) {
					$money_choose = $jbbrd_options['money_choose'];
				} else if ( 'custom' == $jbbrd_options['select_money_unit'] ) {
					$money_choose = $jbbrd_options['custom_money_unit'];
				}
				/* Add search. */
				if ( isset( $_POST['s'] ) ) {
					$search		= isset( $_POST['s'] ) ? htmlspecialchars( stripslashes( $_POST['s'] ) ) : "";
					$search		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $search ) ) );
				}
				/* Set tables names. */
				$posts_table					= $wpdb->prefix . "posts";
				$term_relationships_table		= $wpdb->prefix . "term_relationships";
				$term_taxonomy_table 			= $wpdb->prefix . "term_taxonomy";
				$postmeta_table 				= $wpdb->prefix . "postmeta";
				$terms_table 					= $wpdb->prefix . "terms";
				/* Set ordering. */
				if ( ( isset( $_GET['orderby'] ) ) && ( in_array( $_GET['orderby'], array( "title", "author", "date" ) ) ) ) {
					if ( "title" == $_GET['orderby'] )
						$jbbrd_orderby = "post_title";
					else
						$jbbrd_orderby = "post_modified";
				} else
					$jbbrd_orderby = 'post_title';

				if ( ( isset( $_GET['order'] ) ) && ( in_array( $_GET['order'], array( "asc", "desc" ) ) ) )
					$jbbrd_order = $_GET['order'];
				else
					$jbbrd_order = 'desc';
				
				/* Prepare sql_query. */
				$sql_query = "SELECT DISTINCT wpp.`ID`, wpp.`post_title`, wpp.`post_modified`, pm.`meta_value` 
					FROM `" . $posts_table . "` AS wpp
					JOIN `" . $term_relationships_table . "` AS tr ON wpp.`ID` = tr.`object_id` ";
				if ( ! empty( $jbbrd_term_id ) )
					$sql_query .= "JOIN `" . $term_taxonomy_table . "` AS ttt ON (tr.`term_taxonomy_id` = ttt.`term_taxonomy_id`
						AND ttt.`term_id` = '" . $jbbrd_term_id . "'
						AND ttt.`taxonomy` = 'jbbrd_businesses') ";
				$sql_query .= "JOIN `" . $postmeta_table . "` AS pm ON (wpp.`ID` = pm.`post_id`
						AND pm.`meta_key` = 'salary')
					AND wpp.`post_status` = 'publish'
					AND wpp.`post_type` = 'vacancy'
					AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`post_modified`) < '86400'";
				/* Set search order. */
				if ( isset( $_POST['s'] ) ) {
					$sql_query .= " AND wpp.`post_title` LIKE '%" . $search . "%'";
				}
				/* Set output ordering. */
				$sql_query .= " ORDER BY wpp.`" . $jbbrd_orderby . "` " . $jbbrd_order;
				$job_data = $wpdb->get_results( $sql_query, ARRAY_A );
				
				foreach ( $job_data as $job ) {
					$job_list[$i]			= array();
					$job_list[$i]['id']		= $job['ID'];
					$job_list[$i]['title']	= $job['post_title'];
					$job_list[$i]['author']	= $job['meta_value'];
					$job_list[$i]['author'] .= ' ' . $money_choose . '/' . $jbbrd_options['time_period_choose'];
					$job_list[$i]['date']	= $job['post_modified'];
					$i ++;
				}
				return( $job_list );
			}
			/**
			 * Function to get number of all matched jobs
			 * @return sting reports number
			 */
			public function items_count() {
				global $wpdb;
				$jbbrd_search_category = get_user_meta( get_current_user_id(), 'jbbrd_job_candidate_category_choose', true ); 
				$jbbrd_term_id = term_exists( $jbbrd_search_category, 'jbbrd_businesses' );
				$jbbrd_term_id = $jbbrd_term_id['term_id'];
				/* Set tables names. */
				$posts_table				= $wpdb->prefix . "posts";
				$term_relationships_table	= $wpdb->prefix . "term_relationships";
				$term_taxonomy_table 		= $wpdb->prefix . "term_taxonomy";
				$terms_table 				= $wpdb->prefix . "terms";
				/* Prepare sql_query. */
				$sql_query = "SELECT COUNT(wpp.`id`) 
					FROM `" . $posts_table . "` AS wpp
					JOIN `" . $term_relationships_table . "` AS tr ON wpp.`ID` = tr.`object_id` ";
				if ( ! empty( $jbbrd_term_id ) )
					$sql_query .= "JOIN `" . $term_taxonomy_table . "` AS ttt ON (tr.`term_taxonomy_id` = ttt.`term_taxonomy_id`
						AND ttt.`term_id` = '" . $jbbrd_term_id . "'
						AND ttt.`taxonomy` = 'jbbrd_businesses') ";
				$sql_query .= "AND wpp.`post_status` = 'publish'
					AND wpp.`post_type` = 'vacancy'
					AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`post_modified`) < '86400'";
				$items_count  = $wpdb->get_var( $sql_query );
				return $items_count;
			}

			/**
			 * Add link to vacancy below vacansy title.
			 * @param sting current item
			 * @return sting link to vacancy
			 */
			function column_title( $item ) {
				global $jbbrd_options;
				if ( '' == $jbbrd_options['shortcode_permalink'] ) {
					$actions['view'] = __( 'Link is not found!', 'job-board' );
					return sprintf('%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
				} else {
					$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
					$actions = array(
						'view'		=> sprintf( '<a href="' . '%1$s' . $vacancy_id_slug . '%2$s' . '">View</a>', $jbbrd_options['shortcode_permalink'], $item['id'] ),
					);
					return sprintf('%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
				}
			}
		}
	}/* End of class jbbrd_Manager to display list of messages. */
}

/**
 * Add screen options of class jbbrd_Backend_Manager
 * @return void 
 */
if ( ! function_exists( 'jbbrd_screen_options' ) ) {
	function jbbrd_screen_options() {
		$option = 'per_page';
		$args = array(
			'label'		=> __( 'Jobs per page', 'job-board' ),
			'default'	=> 30,
			'option'	=> 'vacancies_per_page'
		);
		add_screen_option( $option, $args );
	}
}

/**
 * Function to save and load settings from screen options
 * @return void 
 */
if ( ! function_exists( 'jbbrd_candidate_table_set_option' ) ) {
	function jbbrd_candidate_table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * Create new obect.
 * @return void
 */
if ( ! function_exists( 'jbbrd_candidate_category_custom_search_page' ) ) {
	function jbbrd_candidate_category_custom_search_page() {
		$jbbrd_manager = new jbbrd_Backend_Manager(); ?>
		<div class="wrap"><h2><?php _e( 'Job offers for choosen category by last day', 'job-board' ); ?></h2>
			<form method="post">
				<input type="hidden" name="page" value="job_candidate" />
				<?php if ( isset( $_POST['s'] ) && $_POST['s'] ) {
					print( '<span class="subtitle">' . __( 'Search results for', 'job-board' ) . ' &#8220;' . wp_html_excerpt( esc_html( stripslashes( $_POST['s'] ) ), 50 ) . '&#8221;</span>' );
				}
				$jbbrd_manager->prepare_items();
				$jbbrd_manager->search_box( __( 'Search', 'job-board' ), 'search_id');
				$jbbrd_manager->display(); ?>
			</form>
		</div>
	<?php }
}

/**
 * Add custom upload CV, search categories and saved search capabilities for job_candidate profile page.
 * @return void
 */
if ( ! function_exists( 'jbbrd_add_cv_load_field' ) ) {
	function jbbrd_add_cv_load_field( $user ) { 
		/* Set variables for choosing job  category. */
		global $jbbrd_choose_term_id, $jbbrd_options;
		$tax_slug 	= 'jbbrd_businesses';
		$tax_obj 	= get_taxonomy( $tax_slug );
		$tax_name 	= $tax_obj->labels->name;
		/* Retrieve array of term objects per taxonomy. */
		$terms 		= get_terms( $tax_slug );
		/* Upload CV form. */
		$jbbrd_cv = get_user_meta( $user->ID, 'jbbrd_user_cv', true ); 
		if ( ( true === jbbrd_vacansy_response() ) || ( current_user_can( 'activate_plugins', get_current_user_id() ) ) ) { ?>
			<!-- Job candidate CV. -->
			<h2><?php _e( "Job candidate options", 'job-board' ); ?></h2>
			<?php if ( is_admin() && ( is_plugin_active( 'sender/sender.php' ) || is_plugin_active( 'sender-pro/sender-pro.php' ) ) ) { ?>
				<table class="form-table">
					<tr>
						<th scope="row"><label for="user_meta_cv"><?php _e( 'Upload your CV file', 'job-board' ); ?></label></th>
						<td><input id="user_meta_cv" type="file" name="jbbrd_user_cv" value="" />
							<br />
							<span class="description"><?php _e( 'You can upload only DOC/DOCX, PDF or TXT document.', 'job-board' ); ?></span>
							<br />
							<?php if ( isset( $jbbrd_cv['url'] ) ) {
								echo '<a href=' . $jbbrd_cv['url'] . '>' . pathinfo( $jbbrd_cv['file'], PATHINFO_BASENAME ) . '</a>';
							}
							if ( isset( $jbbrd_cv['url'] ) ) { ?>
								<br /><br />
								<input type="checkbox" id="jbbrd_archive_checkbox" name="jbbrd_cv_clear" />
								<label class="description" for="jbbrd_archive_checkbox"><?php _e( 'Delete current CV', 'job-board' ); ?></label>
							<?php } ?>
						</td>
					</tr>
				</table>
			<?php } ?>
			<!-- Job candidate Search Category. -->
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Set job search category', 'job-board' ); ?></th>
					<td><?php 
						/* Retrieve the current object. */
						$jbbrd_current_user_meta = get_user_meta( $user->ID );
						if ( isset( $jbbrd_current_user_meta['jbbrd_job_candidate_category_choose'] ) )
							$jbbrd_current_user_meta_value = $jbbrd_current_user_meta['jbbrd_job_candidate_category_choose'][0];
						else
							$jbbrd_current_user_meta_value = '';
						/* Output html for taxonomy dropdown filter. */
						print( '<select name="jbbrd_job_candidate_category_choose" id=' . $tax_slug . ' class="jbbrd_postform">' );
						print( '<option value="">' . __( 'Show all', 'job-board' ) . '</option>' );
						foreach ( $terms as $term ) {
							/* Output each select option line, check against the last $_GET to show the current option selected. */
							echo '<option value='. $term->slug;
							if ( $term->slug == $jbbrd_current_user_meta_value ) {
								echo ' selected="selected"';
							}
							echo '>' . $term->name . '</option>';
						}
						print_r( '</select><br />
						<span class="description">' . __( 'Choose job search category', 'job-board' ) . '</span><br /><br />' );
						if ( '' != $jbbrd_current_user_meta_value ) { ?>
						<input type="checkbox" id="jbbrd_candidate_category_clear_checkbox" name="jbbrd_candidate_category_clear" />
						<label class="description" for="jbbrd_candidate_category_clear_checkbox"><?php _e( 'Clear current category search', 'job-board' ); ?></label>
						<?php } ?>
					</td>
				</tr>
			</table>
			<!-- Job candidate Saved Search. -->
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Candidate saved search', 'job-board' ); ?></th>
					<td><?php 
						/* Retrieve the current object. */
						if ( ( isset( $jbbrd_current_user_meta['job_candidate_saved_search'] ) ) && ( '' != ( $jbbrd_current_user_meta['job_candidate_saved_search'][0] ) ) ) {
							$jbbrd_current_user_saved_search = $jbbrd_current_user_meta['job_candidate_saved_search'][0];
							$jbbrd_candidate_saved_search = array();
							$jbbrd_candidate_saved_search = unserialize( $jbbrd_current_user_saved_search );
							unset( $jbbrd_candidate_saved_search[0] );
							$jbbrd_candidate_saved_search_transformed = array(); 
							foreach ( $jbbrd_candidate_saved_search as $key => $value ) {
								$jbbrd_candidate_saved_search_transformed[] = $key . '=' . $value;
							}
							$jbbrd_candidate_saved_search_string = implode( '&', $jbbrd_candidate_saved_search_transformed );
							if ( isset( $jbbrd_options['shortcode_permalink'] ) ) {
								$search_slug = ( ! get_option('permalink_structure') ) ? '&' : '?';
								print_r( '<a href="' . $jbbrd_options['shortcode_permalink'] . $search_slug . $jbbrd_candidate_saved_search_string . '">' . __( 'View saved search results', 'job-board' ) . '</a>' );
							}
							print_r( '<br /><span class="description">' . __( 'Your saved search', 'job-board' ) . '</span><br /><br />' );
						} else {
							$jbbrd_current_user_saved_search = '';
							print_r( '<span class="description">' . __( 'Saved search not found', 'job-board' ) . '</span><br /><br />' );
						}
						/* Checkbox to remove search. */
						if ( '' != $jbbrd_current_user_saved_search ) { ?>
						<input type="checkbox" id="jbbrd_candidate_saved_search_clear_checkbox" name="jbbrd_candidate_saved_search_clear" />
						<label class="description" for="jbbrd_candidate_saved_search_clear_checkbox"><?php _e( 'Clear current saved search', 'job-board' ); ?></label>
						<?php } ?>
					</td>
				</tr>
			</table>
		<?php }
	}
}

/**
 * Saving custom upload CV field to job_candidate profile page.
 * @return void
 */
if ( ! function_exists( 'jbbrd_save_cv_load_field' ) ) {
	function jbbrd_save_cv_load_field( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ( false == jbbrd_vacansy_response() ) ) {
			return false;
		}
		$_POST['action'] = 'wp_handle_upload';
		/* Set array of extention. */
		$jbbrd_cv_extention_array = array( 'txt', 'doc', 'docx', 'pdf' );
		if ( $_FILES['jbbrd_user_cv']['error'] === UPLOAD_ERR_OK ) {
			$jbbrd_cv = wp_handle_upload( $_FILES['jbbrd_user_cv'] );
			if ( $jbbrd_cv['error'] == '' ) {
				if ( in_array( pathinfo( $jbbrd_cv['file'], PATHINFO_EXTENSION ), $jbbrd_cv_extention_array ) ) {
					update_user_meta( $user_id, 'jbbrd_user_cv', $jbbrd_cv );
				} else {
					$notice[ $post_id ] .=  __( 'Failed to load CV: wrong file type. You can upload only DOC/DOCX, PDF or TXT document.', 'job-board' ) . '<br />';
					update_option( 'jbbrd_custom_metafield_error_notice', $notice );
				}
			} else {
				$notice[ $post_id ] .=  $jbbrd_cv['error'] . '<br />';
				update_option( 'jbbrd_custom_metafield_error_notice', $notice );
			}
		}
		/* Add category for search. */
		$jbbrd_job_category = $_POST['jbbrd_job_candidate_category_choose'];
		if ( ! isset( $_POST['jbbrd_candidate_category_clear'] ) ) {
			if ( isset( $jbbrd_job_category ) ) {
				update_user_meta( $user_id, 'jbbrd_job_candidate_category_choose', $jbbrd_job_category );
			}
		} else {
			update_user_meta( $user_id, 'jbbrd_job_candidate_category_choose', '' );
		}
		/* Clear CV if checkbox is checked. */
		if ( isset( $_POST['jbbrd_cv_clear'] ) && ( '' != $_POST['jbbrd_cv_clear'] ) ) {
			$jbbrd_cv = wp_handle_upload( $_FILES['jbbrd_user_cv'] );
			update_user_meta( $user_id, 'jbbrd_user_cv', $jbbrd_cv, get_user_meta( $user_id, 'jbbrd_user_cv', true ) );
		}
		/* Clear candidate saved search if checkbox is checked. */
		if ( isset( $_POST['jbbrd_candidate_saved_search_clear'] ) && ( '' != $_POST['jbbrd_candidate_saved_search_clear'] ) ) {
			update_user_meta( $user_id, 'job_candidate_saved_search', '' );
		}
	}
}

/**
 * Saving custom upload CV field to job_candidate profile page.
 * @return void
 */
if ( ! function_exists( 'jbbrd_form_accept_uploads' ) ) {
	function jbbrd_form_accept_uploads() {
		echo ' enctype="multipart/form-data"';
	}
}

/**
 * Insert message to Sender database function.
 * @param $employer_id string owner of current vacancy ID
 * @param $subject string letter subject to send
 * @param $body string letter body to send
 * @return void
 */
if ( ! function_exists( 'jbbrd_save_letter_to_sender_db' ) ) {
	function jbbrd_save_letter_to_sender_db( $employer_id, $subject, $body ) {
		global $wpdb;
		/* Save mail into database. */
		$prefix = is_multisite() ? $wpdb->base_prefix : $wpdb->prefix;
		$wpdb->insert( $prefix . 'sndr_mail_send', 
			array( 
				'subject'		=> $subject, 
				'body'			=> $body,
				'date_create'	=> time(),
			)
		);
		$last_id = $wpdb->insert_id; /* Get last ID. */
		$wpdb->insert( $prefix . 'sndr_users', 
			array( 
				'id_user'		=> $employer_id, 
				'id_mail'		=> $last_id,
			)
		);
		/*Activation Sendmail cron hook. */
		add_filter( 'cron_schedules', 'sndr_more_reccurences' );
		if ( ! wp_next_scheduled( 'sndr_mail_hook' ) ) {
			$check = wp_schedule_event( time(), 'my_period', 'sndr_mail_hook' );
		}
	}
}

/**
 * Insert message to Sender-pro database function.
 * @param $employer_id string owner of current vacancy ID
 * @param $subject string letter subject to send
 * @param $body string letter body to send
 * @param $candidate_id string of current user ID
 * @return void
 */
if ( ! function_exists( 'jbbrd_save_letter_to_sender_pro_db' ) ) {
	function jbbrd_save_letter_to_sender_pro_db( $employer_id, $subject, $body, $candidate_id ) {
		global $wpdb;
		/* Prepare data. */
		$subject	 	= htmlspecialchars( stripslashes( $subject ) );
		$subject 		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $subject ) ) );
		$body	 		= htmlspecialchars( stripslashes( $body ) );
		$body 			= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $body ) ) );
		$employer_id	= htmlspecialchars( stripslashes( $employer_id ) );
		$employer_id 	= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $employer_id ) ) );
		$candidate_id	= htmlspecialchars( stripslashes( $candidate_id ) );
		$candidate_id 	= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $candidate_id ) ) );
		/* Save mail into database. */
		$prefix = is_multisite() ? $wpdb->base_prefix : $wpdb->prefix;
		$wpdb->insert( $prefix . 'sndr_mail_send', 
			array( 
				'subject'			=> $subject, 
				'body'				=> $body,
				'date_create'		=> date( 'Y-m-d H:i:s', time() + get_option('gmt_offset') * 3600 ),
				'secret_key'		=> MD5(RAND()),
			)
		);
		/* Get last ID. */
		$mail_id = $wpdb->insert_id; 
		/* Get user into database. */
		$user_id = $employer_id; 
		/* Save user into database. */
		$wpdb->insert( $prefix . 'sndr_mailout', 
			array( 
				'mail_id'			=> $mail_id,
				'from_name'			=> get_userdata( $candidate_id )->user_login, 
				'from_email'		=> get_userdata( $candidate_id )->user_email,
				'mailout_create'	=> date( 'Y-m-d H:i:s', time() + get_option('gmt_offset') * 3600 ),
				'mailout_start'		=> date( 'Y-m-d H:i:s', time() + get_option('gmt_offset') * 3600 ),
			)
		);
		/* Get last ID. */
		$mailout_id = $wpdb->insert_id;
		/* Save user into database. */
		$wpdb->insert( $prefix . 'sndr_users', 
			array( 
				'id_user'			=> $user_id,
				'id_mail'			=> $mail_id,
				'id_mailout'		=> $mailout_id,
			)
		);
		/*Activation Sendmail cron hook. */
		add_filter( 'cron_schedules', 'sndrpr_more_reccurences' );
		if ( ! wp_next_scheduled( 'sndrpr_mail_hook' ) ) {
			$check = wp_schedule_event( time(), 'my_period', 'sndrpr_mail_hook' );
		}
	}
}

/**
 * Display an array of category taxonomy slugs you want to filter in frontend.
 * @return $terms array() objects jbbrd_businesses per taxonomy.
 */
if ( ! function_exists( 'jbbrd_restrict_manage_posts_frontend' ) ) {
	function jbbrd_restrict_manage_posts_frontend() { 
		$filters = array( 'jbbrd_businesses' );
		foreach ( $filters as $tax_slug ) {
			/* Retrieve the taxonomy object. */
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			/* Retrieve array of term objects per taxonomy. */
			$terms = get_terms( $tax_slug );
			return $terms;
		}
	}
}

/**
 * Display an array of employment category taxonomy slugs you want to filter in frontend.
 * @return $terms array() objects jbbrd_employment per taxonomy.
 */
if ( ! function_exists( 'jbbrd_employment_restrict_manage_posts_frontend' ) ) {
	function jbbrd_employment_restrict_manage_posts_frontend() { 
		$filters = array( 'jbbrd_employment' );
		foreach ( $filters as $tax_slug ) {
			/* Retrieve the taxonomy object. */
			$tax_obj = get_taxonomy( $tax_slug );
			$tax_name = $tax_obj->labels->name;
			/* Retrieve array of term objects per taxonomy. */
			$terms = get_terms( $tax_slug );
			return $terms;
		}
	}
}


/**
 * Action plugin links function.
 * @param $links string
 * @param $file string
 * @return list of links
 */
if ( ! function_exists( 'jbbrd_plugin_action_links' ) ) {
	function jbbrd_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=job-board.php">' . __( 'Settings', 'job-board' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/**
 * Register plugin links function.
 * @return $links array().
 */
if ( ! function_exists( 'jbbrd_register_plugin_links' ) ) {
	function jbbrd_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=job-board.php">' . __( 'Settings', 'job-board' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/job-board/faq/" target="_blank">' . __( 'FAQ', 'job-board' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'job-board' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Function to check is current user are 'job_candidate'.
 * @return bool
 */
if ( ! function_exists( 'jbbrd_vacansy_response' ) ) {
	function jbbrd_vacansy_response() { 
		global $wpdb;
		$user = get_current_user_id();
		if ( method_exists( 'wpdb' , 'esc_like' ) ) {
			$job_candidate = $wpdb->esc_like( 'job_candidate' );
		} else {
			$job_candidate = like_escape( 'job_candidate' );
		}
		$job_candidate = esc_sql( $job_candidate );
		$job_candidate = '%' . $job_candidate . '%';
		/* Get subscribers list. */
		$prefix = is_multisite() ? $wpdb->base_prefix : $wpdb->prefix;
		$jbbrd_user_id_find = $wpdb->get_results( "SELECT umt.`user_id`
			FROM `" . $prefix . "usermeta` as umt
			WHERE umt.`meta_key` = 'wp_capabilities'
			AND umt.`meta_value` LIKE '%job_candidate%'", ARRAY_N);
		/* If current user is 'job_candidate' return true */
		foreach ( $jbbrd_user_id_find as $key => $value ) {
			if ( ( $user == $value[0] ) || is_admin() ) {
				return true;
			}
		}
		return false;
	}
}

/**
 * Get salary array.
 * @return $jbbrd_salary_find_modified array() salary array.
 */
if ( ! function_exists( 'jbbrd_salary_find' ) ) {
	function jbbrd_salary_find() { 
		global $wpdb;
		$postmeta_table 			= $wpdb->prefix . "postmeta" ;
		$post_table 				= $wpdb->prefix . "posts" ;
		$term_relationships_table	= $wpdb->prefix . "term_relationships";
		$term_taxonomy_table 		= $wpdb->prefix . "term_taxonomy";
		$terms_table 				= $wpdb->prefix . "terms";
		/* Get subscribers list. */
		$jbbrd_salary_find = $wpdb->get_results( "
			SELECT DISTINCT `meta_value`
			FROM `" . $postmeta_table . "` as pmt
			JOIN `" . $post_table . "` AS pt ON (pmt.`post_id` = pt.`ID`
				AND pt.`post_type` = 'vacancy'
				AND pt.`post_status` = 'publish')
			JOIN `" . $term_relationships_table . "` AS trt ON pt.`ID` = trt.`object_id`
			JOIN `" . $term_taxonomy_table . "` AS ttt ON trt.`term_taxonomy_id` = ttt.`term_taxonomy_id`
			JOIN `" . $terms_table . "` AS tt ON (ttt.`term_id` = tt.`term_id`
				AND tt.`slug` = 'posted' )
			AND pmt.`meta_key` = 'salary'
		", ARRAY_A);
		$jbbrd_salary_find_modified = array();
		foreach ( $jbbrd_salary_find as $array_key => $array_value ) {
			foreach ( $array_value as $key => $value ) {
				$jbbrd_salary_find_modified[] = $value;
			}
		}
		return $jbbrd_salary_find_modified;
	}
}

/**
 * Change vacancy CPT link returned by CPTsearch BWS Plugin.
 * @return $url string modified url.
 */
function jbbrd_append_vacancy_permalink( $url, $post ) {
	global $jbbrd_options;
	if ( ! ( is_admin() ) && ( 'vacancy' == get_post_type( $post ) ) ) {
		$jbbrd_link = esc_url( $jbbrd_options['shortcode_permalink'] );
		$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
		$url = $jbbrd_link . $vacancy_id_slug . get_the_ID();
	}
	return $url;
}

/**
 * Hide logo image when search results displayed.
 * @return void
 */
function jbbrd_logo_search_hide() {
	global $jbbrd_options;
	if ( ! ( is_admin() ) && ( isset( $_GET['s'] ) ) ) { ?>
		<style type="text/css">
			.vacancy img { /* Set custom header background */
				display: none !important; 
			}
		</style>
	<?php }
}

/**
 * Function get salary from mixed string.
 * @param mixed salary string
 * @return string maximum digit in salary
 */
if ( ! function_exists( 'jbbrd_get_salary' ) ) {
	function jbbrd_get_salary( $jbbrd_salary_string ) { 
		$jbbrd_salary_string = esc_html( $jbbrd_salary_string );
		$pattern = '[\D+]';
		$jbbrd_nums_array = preg_split( $pattern , $jbbrd_salary_string );
		return max( $jbbrd_nums_array );
	}
}

if ( ! function_exists( 'jbbrd_sendmail_session_check' ) ) {
	function jbbrd_sendmail_session_check() { 
		if ( isset( $_POST['jbbrd_frontend_sendmail_submit'] ) ) {
			$_SESSION['jbbrd_send_cv'] = 1;
			return 1;
		} else {
			$_SESSION['jbbrd_send_cv'] = 0;
			return 0;
		}
	}
}

/**
 * Function to display frontend by shortcode [jbbrd_vacancy].
 * @return shortcode frontend content string
 */
if ( ! function_exists( 'jbbrd_vacancy_shortcode' ) ) {
	function jbbrd_vacancy_shortcode() { 
		global $wp_query, $jbbrd_options, $jbbrd_sender_not_found, $jbbrd_sender_not_active;
		/* Get sender-pro plugin options. */
		$sndrpr_options = get_option( 'sndrpr_options' );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$jbbrd_location_array = jbbrd_find_location_metabox_fields();
		/* Find maximum and minimum salary. */
		$jbbrd_salary_array = jbbrd_salary_find();
		if ( isset( $jbbrd_salary_array[0] ) ) {
			$jbbrd_salary_min = min( jbbrd_salary_find() );
			$jbbrd_salary_max = max( jbbrd_salary_find() );
		} else {
			$jbbrd_salary_min = $jbbrd_salary_max = 0;
		}
		/* Clear posible vulnerabilities of variable input. */
		$jbbrd_get_search_period 		= isset( $_GET['search_period'] ) ? htmlspecialchars( stripslashes( $_GET['search_period'] ) ) : "";
		$jbbrd_get_category 			= isset( $_GET['category'] ) ? htmlspecialchars( stripslashes( $_GET['category'] ) ) : "";
		$jbbrd_get_employment_category 	= isset( $_GET['employment_category'] ) ? htmlspecialchars( stripslashes( $_GET['employment_category'] ) ) : "";
		$jbbrd_get_location	 			= isset( $_GET['jbbrd_location'] ) ? htmlspecialchars( stripslashes( $_GET['jbbrd_location'] ) ) : "";
		$jbbrd_get_organization	 		= isset( $_GET['jbbrd_organization'] ) ? htmlspecialchars( stripslashes( $_GET['jbbrd_organization'] ) ) : "";
		$jbbrd_get_salary_from	 		= isset( $_GET['jbbrd_salary_from'] ) ? htmlspecialchars( stripslashes( $_GET['jbbrd_salary_from'] ) ) : "";
		$jbbrd_get_salary_to	 		= isset( $_GET['jbbrd_salary_to'] ) ? htmlspecialchars( stripslashes( $_GET['jbbrd_salary_to'] ) ) : "";
		$jbbrd_get_vacancy_id	 		= isset( $_GET['vacancy_id'] ) ? htmlspecialchars( stripslashes( $_GET['vacancy_id'] ) ) : "";

		$jbbrd_get_search_period 		= $_GET['search_period'] 		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_search_period ) ) );
		$jbbrd_get_category 			= $_GET['category']				= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_category ) ) );
		$jbbrd_get_employment_category	= $_GET['employment_category'] 	= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_employment_category ) ) );
		$jbbrd_get_location				= $_GET['jbbrd_location'] 		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_location ) ) );
		$jbbrd_get_organization			= $_GET['jbbrd_organization']	= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_organization ) ) );
		$jbbrd_get_salary_from			= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_salary_from ) ) );
		$jbbrd_get_salary_to			= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_salary_to ) ) );
		$jbbrd_get_vacancy_id			= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $jbbrd_get_vacancy_id ) ) );
		/* Set array of search time select. */
		$jbbrd_vacancy_search_period = array( 
			3		=> __( 'Last 3 days', 'job-board' ),
			7 		=> __( 'Last week', 'job-board' ),
			30		=> __( 'Last month', 'job-board' ),
		);
		/* If time period exist set time period query. */
		if ( isset( $jbbrd_get_search_period ) && ( '' != $jbbrd_get_search_period ) ) {
			$jbbrd_search_period_cond = array(
				'column' => 'post_modified_gmt',
				'after'  => $jbbrd_get_search_period . ' day ago',
			);	
		} else {
			$jbbrd_search_period_cond = '';
		}
		/* If category exist set search by this category. */
		if ( isset( $jbbrd_get_category ) && ( '' != $jbbrd_get_category ) ) {
			$jbbrd_businesses_search_categories = array(
				'taxonomy'	=> 'jbbrd_businesses',
				'field'		=> 'slug',
				'terms'		=> $jbbrd_get_category,
			);
		} else {
			$jbbrd_businesses_search_categories = '';
		}
		/* If employment category exist set search by this category. */
		if ( isset( $jbbrd_get_employment_category ) && ( '' != $jbbrd_get_employment_category ) ) {
			$jbbrd_employment_search_categories = array(
				'taxonomy'	=> 'jbbrd_employment',
				'field'		=> 'slug',
				'terms'		=> $jbbrd_get_employment_category,
			);
		} else
			$jbbrd_employment_search_categories = '';
		
		$jbbrd_search_categories = array(
			$jbbrd_businesses_search_categories,
			$jbbrd_employment_search_categories,
		);
		/* Set wp_query conditions for frontend form search. */
		if ( isset( $jbbrd_get_location ) && ( '' != $jbbrd_get_location ) ) 
			$jbbrd_location_cond = array( 
				'key'		=> 'jbbrd_location', 
				'value'		=> $jbbrd_get_location, 
				'compare'	=> 'LIKE', 
			);
		else
			$jbbrd_location_cond = '';
		
		if ( isset( $jbbrd_get_organization ) && ( '' != $jbbrd_get_organization ) ) 
			$jbbrd_organization_cond = array( 
				'key'		=> 'jbbrd_organization', 
				'value'		=> $jbbrd_get_organization,
				'compare'	=> 'LIKE', 
			);
		else
			$jbbrd_organization_cond = '';
		
		if ( isset( $jbbrd_get_salary_from ) && ( '' != $jbbrd_get_salary_from ) ) 
			$jbbrd_salary_from_cond = array( 
				'key' 		=> 'salary', 
				'value' 	=> jbbrd_get_salary( $jbbrd_get_salary_from ),
				'compare' 	=> '>=', 
				'type' 		=> 'numeric',);
		else
			$jbbrd_salary_from_cond = '';

		if ( isset( $jbbrd_get_salary_to ) && ( '' != $jbbrd_get_salary_to ) ) 
			$jbbrd_salary_to_cond = array( 
				'key' 		=> 'salary', 
				'value' 	=> jbbrd_get_salary( $jbbrd_get_salary_to ), 
				'compare' 	=> '<=', 
				'type' 		=> 'numeric',);
		else
			$jbbrd_salary_to_cond = '';

		/* If get ID - set id search condition. */
		$jbbrd_title_search_cond = ( isset( $jbbrd_get_vacancy_id ) && '' != $jbbrd_get_vacancy_id ) ? $jbbrd_get_vacancy_id : '';
		/* Add parameters for output posts. */
		$args = array(
			'post_type'				=> 'vacancy',
			'p'						=> $jbbrd_title_search_cond,
			'tax_query' 			=> $jbbrd_search_categories,
			'ignore_sticky_posts'	=> true,
			'paged' 				=> get_query_var( 'paged' ), 
			'posts_per_page'		=> $jbbrd_options['post_per_page'],
			'archive' 				=> 'Posted',
			'orderby'				=> 'post_date',
			'order'					=> 'DESC',
			/* Forming meta querry. */
			'meta_query'			=>	array(
				'relation' 	=> 'AND', 
				$jbbrd_location_cond,
				$jbbrd_organization_cond,
				$jbbrd_salary_from_cond,
				$jbbrd_salary_to_cond,
			),
			'date_query' => array(
				$jbbrd_search_period_cond,
			),
		);
		$wp_query = new WP_Query( $args );
		$jbbrd_content = '';
		/* Print form fo sorting jobs in frontend if user logged. */
		if ( is_user_logged_in() && ( current_user_can( 'read_private_vacancies', get_current_user_id() ) ) ) { 
			/* Add to table of response if submit. */
			if ( isset( $_POST['jbbrd_frontend_sendmail_submit'] ) ) {
				/* Move mail from job candidate to Sender plugin DB. */
				$jbbrd_sendmail_error = '';
				$jbbrd_current_employer_id = esc_html( $_POST['jbbrd_frontend_submit_post_email'] );
				if ( empty( $jbbrd_current_employer_id ) ) {
					$jbbrd_sendmail_error = __( 'Error: Vacancy author not found. The message has not been sent.', 'job-board' );
				}
				if ( '' == $jbbrd_sendmail_error ) {
					$jbbrd_current_employer_email 	= get_userdata( $jbbrd_current_employer_id );
					$jbbrd_current_employer_email 	= $jbbrd_current_employer_email->user_email; 
					$jbbrd_current_vacancy_id		= esc_html( $_POST['jbbrd_frontend_submit_post_id'] );
					$jbbrd_current_candidate 		= get_current_user_id();
					$jbbrd_current_candidate_cv 	= get_user_meta( $jbbrd_current_candidate, 'jbbrd_user_cv', true ); 
					if ( isset( $jbbrd_current_candidate_cv['url'] ) ) {
						$jbbrd_current_candidate_cv = $jbbrd_current_candidate_cv['url'];
					} 
					/* Forming message. */
					if ( is_plugin_active( 'sender-pro/sender-pro.php' ) ) {
						if ( ( isset( $sndrpr_options['html_email'] ) ) && ( 1 == $sndrpr_options['html_email'] ) ) {
							$jbbrd_message = '<div style="width:600px;padding:20px 30px;border:1px solid #e0e0e0;background:#fff;"><h2 style="color:#6495ED;line-height:11px;">' . __( 'Hello,', 'job-board' ) . '&nbsp;' . get_userdata( $jbbrd_current_employer_id )->user_login . '</h2>'; 
							$jbbrd_message .= '<h4 style="font-weight:normal;">' . __( 'You receive message for your job offer:', 'job-board' ) . '&nbsp;<strong>' . get_the_title( $jbbrd_current_vacancy_id ) . '</strong><br />';
							$jbbrd_message .= __ ( 'from candidate:', 'job-board' ) . '&nbsp;<strong>' . get_userdata( $jbbrd_current_candidate )->user_login . '</strong></h4>';
							if ( isset( $jbbrd_current_candidate_cv ) && ( '' != $jbbrd_current_candidate_cv ) ) {
								$jbbrd_current_candidate_cv_filename = pathinfo( $jbbrd_current_candidate_cv );
								$jbbrd_current_candidate_cv_filename = $jbbrd_current_candidate_cv_filename['basename'];
							} else {
								$jbbrd_current_candidate_cv = '';
							}
							$jbbrd_message .= sprintf( '<h4 style="font-weight:normal;">' . __( 'Candidate CV:', 'job-board' ) . '&nbsp;' . ( ( '' != $jbbrd_current_candidate_cv ) ? '<a style="color:#6495ED;text-decoration:none;line-height:11px;" href="' . $jbbrd_current_candidate_cv . '">' . $jbbrd_current_candidate_cv_filename . '</a>' : __( '...sorry, CV not found.', 'job-board' ) ) . '</h4>');
							$jbbrd_message .= '<hr style="border-width:0;border-bottom:solid 1px #e0e0e0;" />';
							$jbbrd_message .= sprintf( __( 'This mail was sent by %sJob Board%s plugin by %sBestWebSoft%s from', 'job-board' ) . '&nbsp;<a  href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . get_bloginfo( 'name' ) . '</a>', ( '<a href="' . esc_url( 'http://bestwebsoft.com/products/job-board/' ) . '">' ),'</a>', ( '<a href="' . esc_url( 'http://bestwebsoft.com/' ) . '">' ),'</a>' );
							$jbbrd_message .= '</div>';
							$jbbrd_subject = __( 'Reply for', 'job-board' ) . ' ' . get_the_title( $jbbrd_current_vacancy_id ) . ' ' . __( 'job offer.', 'job-board' );
						} else {
							$jbbrd_message = __( 'Hello,', 'job-board' ) . ' ' . get_userdata( $jbbrd_current_employer_id )->user_login . "\n";
							$jbbrd_message .= __( 'You receive message for your job offer:', 'job-board' ) . ' ' . get_the_title( $jbbrd_current_vacancy_id ) . "\n";
							$jbbrd_message .= __ ( 'from candidate:', 'job-board' ) . ' ' . get_userdata( $jbbrd_current_candidate )->user_login . "\n";
							$jbbrd_message .= __( 'Candidate CV:', 'job-board' ) . ' ' . $jbbrd_current_candidate_cv . "\n";
							$jbbrd_message .= __( 'This mail was sent by Job Board plugin by BestWebSoft from', 'job-board' ) . ' ' . get_bloginfo( 'name' ) . '.';
							$jbbrd_subject = __( 'Reply for', 'job-board' ) . ' ' . get_the_title( $jbbrd_current_vacancy_id ) . ' ' . __( 'job offer.', 'job-board' );
						}
					} else {
						$jbbrd_message = '<div style="width:600px;padding:20px 30px;border:1px solid #e0e0e0;background:#fff;"><h2 style="color:#6495ED;line-height:11px;">' . __( 'Hello,', 'job-board' ) . '&nbsp;' . get_userdata( $jbbrd_current_employer_id )->user_login . '</h2>'; 
						$jbbrd_message .= '<h4 style="font-weight:normal;">' . __( 'You receive message for your job offer:', 'job-board' ) . '&nbsp;<strong>' . get_the_title( $jbbrd_current_vacancy_id ) . '</strong><br />';
						$jbbrd_message .= __ ( 'from candidate:', 'job-board' ) . '&nbsp;<strong>' . get_userdata( $jbbrd_current_candidate )->user_login . '</strong></h4>';
						if ( isset( $jbbrd_current_candidate_cv ) && ( '' != $jbbrd_current_candidate_cv ) ) {
							$jbbrd_current_candidate_cv_filename = pathinfo( $jbbrd_current_candidate_cv );
							$jbbrd_current_candidate_cv_filename = $jbbrd_current_candidate_cv_filename['basename'];
						} else {
							$jbbrd_current_candidate_cv = '';
						}
						$jbbrd_message .= sprintf( '<h4 style="font-weight:normal;">' . __( 'Candidate CV:', 'job-board' ) . '&nbsp;' . ( ( '' != $jbbrd_current_candidate_cv ) ? '<a style="color:#6495ED;text-decoration:none;line-height:11px;" href="' . $jbbrd_current_candidate_cv . '">' . $jbbrd_current_candidate_cv_filename . '</a>' : __( '...sorry, CV not found.', 'job-board' ) ) . '</h4>');
						$jbbrd_message .= '<hr style="border-width:0;border-bottom:solid 1px #e0e0e0;" />';
						$jbbrd_message .= sprintf( __( 'This mail was sent by %sJob Board%s plugin by %sBestWebSoft%s from', 'job-board' ) . '&nbsp;<a  href="' . esc_url( get_bloginfo( 'url' ) ) . '">' . get_bloginfo( 'name' ) . '</a>', ( '<a href="' . esc_url( 'http://bestwebsoft.com/products/job-board/' ) . '">' ),'</a>', ( '<a href="' . esc_url( 'http://bestwebsoft.com/' ) . '">' ),'</a>' );
						$jbbrd_message .= '</div>';
						$jbbrd_subject = __( 'Reply for', 'job-board' ) . ' ' . get_the_title( $jbbrd_current_vacancy_id ) . ' ' . __( 'job offer.', 'job-board' );
					}
					/* Sendmail by Email-queue if active. Else use sendmail function due Sender. */
					if ( 0 == $_SESSION['jbbrd_send_cv'] ) {
						/* Set session var for submit only once. */
						$_SESSION['jbbrd_send_cv'] = 1;
						if ( is_plugin_active( 'email-queue/email-queue.php' ) || is_plugin_active( 'email-queue-pro/email-queue-pro.php' ) ) {
							mlq_add_extra_plugin_to_mail_queue( array(
								'plugin_name'			=> 'Job Board', 
								'plugin_slug' 			=> 'job-board',
								'plugin_link'			=> plugin_basename( __FILE__ ),
								'install_link'			=> '/wp-admin/plugin-install.php?tab=search&s=Job+Board+Bestwebsoft&plugin-search-input=Search+Plugins',
							) );
						}
						if ( ( is_plugin_active( 'email-queue/email-queue.php' ) || is_plugin_active( 'email-queue-pro/email-queue-pro.php' ) ) && mlq_if_mail_plugin_is_in_queue( plugin_basename( __FILE__ ) ) ) {
							mlq_get_mail_data_for_email_queue_and_save( plugin_basename( __FILE__ ), $jbbrd_current_employer_email, $jbbrd_subject, $jbbrd_message );
						} elseif ( is_plugin_active( 'sender-pro/sender-pro.php' ) ) {
							jbbrd_save_letter_to_sender_pro_db( $jbbrd_current_employer_id, $jbbrd_subject, $jbbrd_message, $jbbrd_current_candidate );
						} else {
							jbbrd_save_letter_to_sender_db( $jbbrd_current_employer_id, $jbbrd_subject, $jbbrd_message );
						}
					}
					$jbbrd_content .= '<hr />
						<h3>' . stripslashes( $jbbrd_options['vacancy_reply_text'] ) . '</h3>';
					$jbbrd_content .= '
					<form method="post" action="">
						<input type="hidden" name="jbbrd_frontend_sendmail_form_back" value="submit" />
						<div>
							<p id="jbbrd_frontend_sendmail_form_back" class="submit_div">
								<input type="submit" class="button-primary" value="' . __( 'Back to jobs', 'job-board' ) . '" />
							</p>
						</div>
					</form>';
				} else {
					$jbbrd_content .= '<hr />
						<h3>' . $jbbrd_sendmail_error . '</h3>';
					$jbbrd_content .= '
					<form method="post" action="">
						<input type="hidden" name="jbbrd_frontend_sendmail_form_back" value="submit" />
						<div>
							<p id="jbbrd_frontend_sendmail_form_back" class="submit_div">
								<input type="submit" class="button-primary" value="' . __( 'Back to jobs', 'job-board' ) . '" />
							</p>
						</div>
					</form>';
				}
			}
			/* Check $_SESSION status. */
			$_SESSION['jbbrd_send_cv'] = jbbrd_sendmail_session_check();
		}
		/* If no send CV button. */
		if ( ! isset( $_POST['jbbrd_frontend_sendmail_submit'] ) ) {
			if ( ( ! isset( $_GET['vacancy_id'] ) ) || ( isset( $_GET['vacancy_id'] ) && ( ! ( $wp_query->have_posts() ) ) ) ) {
			/* Print form fo sorting jobs in frontend. */
				if ( ( isset( $jbbrd_options['frontend_form'] ) && 1 == $jbbrd_options['frontend_form'] && is_user_logged_in() && current_user_can( 'read_private_vacancies', get_current_user_id() ) ) ||
					( isset( $jbbrd_options['frontend_form_non_registered'] ) && 1 == $jbbrd_options['frontend_form_non_registered'] ) ) { 
					$jbbrd_content .= '<hr />
					<h3>' . __( 'Add parameters to sorting jobs', 'job-board' ) . '</h3>';
				}
				/* Save query search results form button. */
				if ( isset( $_GET['jbbrd_frontend_form'] ) && is_user_logged_in() && ( false != jbbrd_vacansy_response() || current_user_can( 'activate_plugins', get_current_user_id() ) ) ) {
					$jbbrd_content .= '<form id="jbbrd_frontend_save_query_results" method="post" action="">
						<table id="jbbrd_frontend_table_savesearch_box">
							<tr>
								<td>';
									if ( isset( $_POST['jbbrd_frontend_save_query_results'] ) ) {
										$jbbrd_content .= '<h4>' . __( 'Search results saved.', 'job-board' ) . '</h4>';
										/* Save query results to candidate profile. */
										$user_id = get_current_user_id();
										$jbbrd_search_args_array = $_GET;
										update_user_meta( $user_id, 'job_candidate_saved_search', $jbbrd_search_args_array );
									} else {
										$jbbrd_content .= '
										<input type="hidden" name="jbbrd_frontend_save_query_results" value="submit" />
										<div style="float:left;margin:0 5px 0 0;">
											<span id="jbbrd_frontend_submit" class="submit_div">
												<input type="submit" class="button-primary" value="' . __( 'Save search results', 'job-board' ) . '" />
											</span>
										</div>';
									}
								$jbbrd_content .= '</td>
							</tr>
						</table>
					</form>';
				}
				/* Frontend sorting form. */
				if ( ( isset( $jbbrd_options['frontend_form'] ) && 1 == $jbbrd_options['frontend_form'] && is_user_logged_in() && current_user_can( 'read_private_vacancies', get_current_user_id() ) ) ||
					( isset( $jbbrd_options['frontend_form_non_registered'] ) && 1 == $jbbrd_options['frontend_form_non_registered'] ) ) { 
				
					$jbbrd_content .= '<form id="jbbrd_frontend_form" method="get" action="';
					if ( isset(	$jbbrd_options['shortcode_permalink'] ) )
						$jbbrd_content .= $jbbrd_options['shortcode_permalink'];

					$jbbrd_content .= '">
						<input type="hidden" name="page_id" value="' . get_the_ID() . '" />
						<div>
							<div class="jbbrd_frontend_table_div">
								<table class="jbbrd_frontend_table_sendmail">
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Location:', 'job-board' ) . '</label>';
											if ( 1 == $jbbrd_options['location_select'] ) {
												$jbbrd_content .= '<select name="jbbrd_location">
																<option value="">' . __( 'Show all locations', 'job-board' ) . '</option>';
													foreach ( $jbbrd_location_array as $key => $current_location ) {
														$jbbrd_content .= '<option value="' . $current_location . '"';
															if ( isset( $jbbrd_get_location ) ) {
																if ( $current_location == $jbbrd_get_location ) {
																	$jbbrd_content .= ' selected="selected"';
																} 
															}
														$jbbrd_content .= '">' . $current_location . '</option>';
													}
												$jbbrd_content .= '</select>';
											} else {
												$jbbrd_content .=	'<input type="text" class="jbbrd_frontend_input" name="jbbrd_location" value="';
													if ( isset( $jbbrd_get_location ) ) {
														$jbbrd_content .= $jbbrd_get_location; 
													}
												$jbbrd_content .= '" />';
											}
											$jbbrd_content .= '
										</td>
									</tr>
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Select category:', 'job-board' ) . '</label>
											<div>';
												/* Output html for taxonomy dropdown filter. */
												$jbbrd_content .= '<select name="category" class="jbbrd_frontend_input">
																	<option value="">' . __( 'Show all categories', 'job-board' ) . '</option>';
												foreach ( jbbrd_restrict_manage_posts_frontend() as $term ) {
													/* Output each select option line, check against the last $_POST to show the current option selected. */
													$jbbrd_content .= '<option class="jbbrd_frontend_input" value='. $term->slug;
													if ( isset( $jbbrd_get_category ) ) {
														$jbbrd_content .= $jbbrd_get_category == $term->slug ? ' selected="selected"' : '';
													}
													$jbbrd_content .= '>' . $term->name . '</option>';
												}
												$jbbrd_content .= '</select>
											</div>
										</td>
									</tr>
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Employment:', 'job-board' ) . '</label>
											<div>';
												/* Output html for taxonomy dropdown filter. */
												$jbbrd_content .= '<select name="employment_category" class="jbbrd_frontend_input">
																	<option value="">' . __( 'Show all employments', 'job-board' ) . '</option>';
												foreach ( jbbrd_employment_restrict_manage_posts_frontend() as $term ) {
													/* Output each select option line, check against the last $_GET to show the current option selected. */
													$jbbrd_content .= '<option class="jbbrd_frontend_input" value='. $term->slug;
													if ( isset( $jbbrd_get_employment_category ) ) {
														$jbbrd_content .= $jbbrd_get_employment_category == $term->slug ? ' selected="selected"' : '';
													}
													$jbbrd_content .= '>' . $term->name . '</option>';
												}
												$jbbrd_content .= '</select>
											</div>
										</td>
									</tr>
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Searching period:', 'job-board' ) . '</label>
											<div>';
												/* Output html for time period for modified vacancy date dropdown filter. */
												$jbbrd_content .= '<select name="search_period" class="jbbrd_frontend_input">
																	<option value="">' . __( 'All time', 'job-board' ) . '</option>';
												foreach ( $jbbrd_vacancy_search_period as $key => $value ) {
													/* Output each select option line, check against the last $_GET to show the current option selected. */
													$jbbrd_content .= '<option class="jbbrd_frontend_input" value='. $key;
													if ( isset( $_GET['search_period'] ) ) {
														$jbbrd_content .= $_GET['search_period'] == $key ? ' selected="selected"' : '';
													}
													$jbbrd_content .= '>' . $value . '</option>';
												}
												$jbbrd_content .= '</select>
											</div>
										</td>
									</tr>
								</table><!-- #jbbrd_frontend_table -->
							</div><!-- .jbbrd_frontend_table_div -->
							<div class="jbbrd_frontend_table_div">
								<table class="jbbrd_frontend_table_sendmail">
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Organization:', 'job-board' ) . '</label>
											<input type="text" class="jbbrd_frontend_input" name="jbbrd_organization" value="';
												if ( isset( $jbbrd_get_organization ) ) {
													$jbbrd_content .= $jbbrd_get_organization;
												}
											$jbbrd_content .= '" />
										</td>
									</tr>
									<tr>
										<td class="jbbrd_frontend_field">
											<div><label>' . __( 'Salary:', 'job-board' ) . '</label></div>
											<div id="jbbrd_frontend_salary">
												<div class="left">
													<input type="text" id="jbbrd_frontend_input_salary_from" class="jbbrd_frontend_input" name="jbbrd_salary_from" value="';
														if ( isset( $_GET['jbbrd_salary_from'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_from );
														} else {
															if ( isset( $jbbrd_salary_min ) ) {
																$jbbrd_content .= $jbbrd_salary_min;
															}
														}
													$jbbrd_content .= '" />
												</div>
												<div class="left">
													<span style="position:relative;top:10px;">' . __( 'to', 'job-board' ) . '</span>
												</div>
												<div class="left">
													<input type="text" id="jbbrd_frontend_input_salary_to" class="jbbrd_frontend_input" name="jbbrd_salary_to" value="';
														if ( isset( $_GET['jbbrd_salary_to'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_to );
														} else {
															if ( isset( $jbbrd_salary_max ) ) {
																$jbbrd_content .= $jbbrd_salary_max;
															}
														}
													$jbbrd_content .= '" />
												</div>
												<div class="clear"></div>';
												if ( $jbbrd_salary_min != $jbbrd_salary_max ) {
													$jbbrd_content .= '<div id="jbbrd_slider" class="ui-corner-all"></div>
													<script type="text/javascript">
													jQuery("#jbbrd_slider").slider({
														min: ';
														if ( isset( $_GET['jbbrd_salary_from'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_from );
														} else {
															$jbbrd_content .= $jbbrd_salary_min;
														}
														$jbbrd_content .= ',
														max: ';
														if ( isset( $_GET['jbbrd_salary_to'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_to );
														} else {
															$jbbrd_content .= $jbbrd_salary_max;
														}
														$jbbrd_content .= ',
														values: [';
														if ( isset( $_GET['jbbrd_salary_from'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_from );
														} else {
															$jbbrd_content .= $jbbrd_salary_min;
														}
														$jbbrd_content .= ',';
														if ( isset( $_GET['jbbrd_salary_to'] ) ) {
															$jbbrd_content .= jbbrd_get_salary( $jbbrd_get_salary_to );
														} else {
															$jbbrd_content .= $jbbrd_salary_max;
														}
														$jbbrd_content .= '],
														range: true,
														stop: function(event, ui) {
															jQuery("input#jbbrd_frontend_input_salary_from").val(jQuery("#jbbrd_slider").slider("values",0));
															jQuery("input#jbbrd_frontend_input_salary_to").val(jQuery("#jbbrd_slider").slider("values",1));
														},
														slide: function(event, ui){
															jQuery("input#jbbrd_frontend_input_salary_from").val(jQuery("#jbbrd_slider").slider("values",0));
															jQuery("input#jbbrd_frontend_input_salary_to").val(jQuery("#jbbrd_slider").slider("values",1));
														}
													});
													</script>';
												}
											$jbbrd_content .= '</div>
										</td><!-- .jbbrd_frontend_field -->
									</tr>
									<tr>
										<td class="jbbrd_frontend_field" style="padding-top:20px;">
										</td><!-- .jbbrd_frontend_field -->
									</tr>
								</table><!-- #jbbrd_frontend_table -->
							</div><!-- .jbbrd_frontend_table_div -->
							<div style="clear:both;"></div>
						</div><!-- -->
						<input type="hidden" name="jbbrd_frontend_form" value="submit" />
						<div style="">
							<p id="jbbrd_frontend_submit" class="submit_div">
								<input type="reset" value="' . __( 'Reset', 'job-board' ) . '" />
								<input type="submit" class="button-primary" value="' . __( 'Search', 'job-board' ) . '" />
							</p>
						</div>
					</form><!-- #jbbrd_frontend_form -->';
				}
			}
			/* Print alert if no posts found. */
			if ( ! ( $wp_query->have_posts() ) ) {
				$jbbrd_content .= '<br /><h3>' . __( 'Nothing found.', 'job-board' ) . '</h3>';
			}
		}		
		if ( ( $wp_query->have_posts() ) && ( ! isset( $_POST['jbbrd_frontend_sendmail_submit'] ) ) ) : /* check for existing posts with specified parameters*/ 
			$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
			/* get the value of currency */
			if ( 'preset' == $jbbrd_options['select_money_unit'] ) {
				$money_choose = $jbbrd_options['money_choose'];
			} else if ( 'custom' == $jbbrd_options['select_money_unit'] ) {
				$money_choose = $jbbrd_options['custom_money_unit'];
			}
	
			while ( $wp_query->have_posts() ) : $wp_query->the_post(); 
				$jbbrd_custom = get_post_custom();
				/* Common vacation page view.  */
				$jbbrd_content .= '
				<div class="jbbrd_vacancy">
					<hr style="margin:15px 0 15px 0;" />';
					/* Logo on left side. */
					if ( 'left' == $jbbrd_options['logo_position'] ) {
						$jbbrd_content .= '
						<h2 style="margin-bottom:0px;">
							<a style="" href="';
							if ( isset(	$jbbrd_options['shortcode_permalink'] ) ) {
								$jbbrd_content .= $jbbrd_options['shortcode_permalink'];
							}
							$jbbrd_content .= $vacancy_id_slug . get_the_ID() . '">';
								$jbbrd_content .= get_the_title() . '
							</a>
						</h2>
						<span style="font-size:0.8em;margin-top:0;margin-bottom:0;"><strong>' . __( 'Posted:', 'job-board' ) . '</strong>&nbsp;' . get_the_date( 'j F, Y' ) . '</span>
						<div class="jbbrd_logo" style="float:left;width:100%;">
							<div style="padding:0;">';
								if ( isset( $_GET['vacancy_id'] ) && ( has_post_thumbnail() ) ) $jbbrd_content .= '<div style="margin:5px 10px 10px 0;float:left;">' . get_the_post_thumbnail() . '</div>';
								$jbbrd_content .= '</div>
							<div style="padding:0;">';
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["jbbrd_organization"][0] ) && ( '' != $jbbrd_custom["jbbrd_organization"][0] ) ) {
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Organization:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["jbbrd_organization"][0] . '</p>';
								}
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["jbbrd_location"][0] ) && ( '' != $jbbrd_custom["jbbrd_location"][0] ) ) {
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Location:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["jbbrd_location"][0] . '</p>';
								}
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["salary"][0] ) && ( '' != $jbbrd_custom["salary"][0] ) ) { 
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Salary:', 'job-board') . '</strong>&nbsp;' . $jbbrd_custom["salary"][0] . '&nbsp;' . $money_choose . '/' . $jbbrd_options['time_period_choose'] . '</p>';
								}
								$jbbrd_content .= '</div>
							<div class="clear"></div>
						</div><!-- .jbbrd_logo -->';
					}
					/* Logo on right side. */
					if ( 'right' == $jbbrd_options['logo_position'] ) { 
						$jbbrd_content .= '
						<div class="jbbrd_logo" style="float:left;width:100%;">
							<div class="jbbrd_logo" style="float:left;">
								<h2 style="margin-bottom:0px;">
									<a style="" href="' . $jbbrd_options['shortcode_permalink'] . $vacancy_id_slug . get_the_ID() . '">';
										$jbbrd_content .= get_the_title()  . '
									</a>
								</h2>
								<p style="font-size:0.8em;margin-top:0;margin-bottom:0;"><strong>' . __( 'Posted:', 'job-board' ) . '</strong>&nbsp;' . get_the_date( 'j F, Y' ) . '</p>';
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["jbbrd_organization"][0] ) && ( '' != $jbbrd_custom["jbbrd_organization"][0] ) ) {
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Organization:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["jbbrd_organization"][0] . '</p>';
								}
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["jbbrd_location"][0] ) && ( '' != $jbbrd_custom["jbbrd_location"][0] ) ) {
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Location:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["jbbrd_location"][0] . '</p>';
								}
								if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["salary"][0] ) && ( '' != $jbbrd_custom["salary"][0] ) ) { 
									$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Salary:', 'job-board') . '</strong>&nbsp;' . $jbbrd_custom["salary"][0] . '&nbsp;' . $money_choose . '/' . $jbbrd_options['time_period_choose'] . '</p>'; 
								}
								$jbbrd_content .= '
							</div><!-- .jbbrd_logo -->';
							if ( isset( $_GET['vacancy_id'] ) && ( has_post_thumbnail() ) ) $jbbrd_content .= '<div style="margin:5px 0 10px 10px;float:right;">' . get_the_post_thumbnail() . '</div>';
							$jbbrd_content .= '
							<div class="clear"></div>
						</div><!-- .jbbrd_logo -->';
					}
					/* Common vacation page view.  */
					$jbbrd_content .= '<div id="jbbrd_custom_post_' . get_the_ID() . '" class="jbbrd_content">';
					/* Show vacancy content if get vacancy ID, else show exerpts. */
					if ( isset( $_GET['vacancy_id'] ) ) 
						$jbbrd_content .= '<p>' . get_the_content() . '</p>';
					else { 
						/* Replaces the excerpt "more" text by a link. */
						add_filter( 'excerpt_more', 'jbbrd_excerpt_more_link' );
						$jbbrd_content .= '<p>' . get_the_excerpt() . '</p>';
					} 
					$jbbrd_content .= '</div><!-- .jbbrd_content -->';
					if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["employment"][0] ) && ( '' != $jbbrd_custom["employment"][0] ) ) {
						$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Employment:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["employment"][0] . '</p>';
					}
					if ( isset( $_GET['vacancy_id'] ) && isset( $jbbrd_custom["demands"][0] ) && ( '' != $jbbrd_custom["demands"][0] ) ) {
						$jbbrd_content .= '<p style="margin-top:0;margin-bottom:0;"><strong>' . __( 'Requirements:', 'job-board' ) . '</strong>&nbsp;' . $jbbrd_custom["demands"][0] . '</p>';
					}
					if ( is_user_logged_in() && ( ( false != jbbrd_vacansy_response() ) || ( current_user_can( 'activate_plugins', get_current_user_id() ) ) ) && ( is_plugin_active( 'sender/sender.php' ) || is_plugin_active( 'sender-pro/sender-pro.php' ) ) ) { 
						if ( '' != $jbbrd_options['shortcode_permalink'] ) {
							$jbbrd_content .= '
							<form method="post" action="">
								<input type="hidden" name="jbbrd_frontend_sendmail_submit" value="submit" />
								<div>
									<p style="margin:10px 0;" class="submit_div">
										<input type="submit" name="send" value="' . __( 'Send CV', 'job-board' ) . '" />
										<input type="hidden" name="jbbrd_frontend_submit_post_email" value="';
										$jbbrd_id = 'ID';
										$jbbrd_content .= get_the_author_meta( $jbbrd_id );
										$jbbrd_content .= '" />
										<input type="hidden" name="jbbrd_frontend_submit_post_id" value="' . get_the_ID() . '" />
									</p>
								</div>
							</form>';
						}
					}
					/* Get link to edit vacancy if current user is admin or vacancy author (employer). */
					if ( ( is_user_logged_in() ) && ( current_user_can( 'activate_plugins', get_current_user_id() ) || ( get_the_author_meta('ID') == get_current_user_id() ) ) ) {
						$jbbrd_content .= '<a href="' . get_edit_post_link( get_the_ID() ) . '">' . __( 'Edit job', 'job-board' ) . '</a>';
					}
					$jbbrd_content .= '</div><!-- .jbbrd_vacancy -->';
			endwhile;
			wp_link_pages();
			/* Add pagination. */
			$jbbrd_content .= jbbrd_vacancy_page_pagination();
		endif;
		wp_reset_postdata();
		wp_reset_query();
		return $jbbrd_content;
	}
}

/**
 * Function to display frontend by shortcode [jbbrd_registration].
 * @return shortcode login/registration form content string
 */
if ( ! function_exists( 'jbbrd_registration_shortcode' ) ) {
	function jbbrd_registration_shortcode() {
		global $wpdb, $jbbrd_options;
		$jbbrd_register_form_content = $jbbrd_register_error = '';
		/* If user not logged or not admin/employer/job_candidate show login form */
		if ( ! is_user_logged_in() ) {
			if ( ! isset( $_POST['jbbrd_registration_request'] ) ) {
				/* Arguments for login form. */
				$args = array(
					'echo'				=> false,
					'redirect'			=> site_url( $_SERVER['REQUEST_URI'] ), 
					'form_id'			=> 'jbbrd_loginform',
					'label_username'	=> __( 'Username', 'job-board' ),
					'label_password'	=> __( 'Password', 'job-board' ),
					'label_remember'	=> __( 'Remember Me', 'job-board' ),
					'label_log_in'		=> __( 'Log In', 'job-board' ),
					'id_username' 		=> 'user_login',
					'id_password'		=> 'user_pass',
					'id_remember'		=> 'rememberme',
					'id_submit'			=> 'wp-submit',
					'remember'			=> true,
					'value_username'	=> NULL,
					'value_remember'	=> false
				);
				$jbbrd_register_form_content .= wp_login_form( $args );
				$jbbrd_register_form_content .= 
				'<form id="jbbrd_registration_request" method="POST">
					<input type="hidden" name="jbbrd_registration_request" value="submit" />
					<input type="submit" class="button-primary" value="' . __( 'Registration', 'job-board' ) . '" />
				</form>';
			} else {
				if ( ! isset( $_POST['jbbrd_frontend_registration_form'] ) ) {
					$jbbrd_register_form_content .= '<form id="jbbrd_registration_form" method="POST">
						<div>
							<div class="jbbrd_frontend_table_div">
								<table class="jbbrd_frontend_table_sendmail">
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'Name:', 'job-board' ) . '</label>
											<input type="text" class="jbbrd_frontend_input" name="jbbrd_user_login" value="" />
										</td>
									</tr>
									<tr>
										<td class="jbbrd_frontend_field">
											<label>' . __( 'E-mail:', 'job-board' ) . '</label>
											<input type="text" class="jbbrd_frontend_input" name="jbbrd_user_email" value="" />
										</td>
									</tr>
								</table><!-- #jbbrd_frontend_table -->
							</div><!-- .jbbrd_frontend_table_div -->
							<div class="jbbrd_frontend_table_div">
								<table class="jbbrd_frontend_table_sendmail">
									<tr>
										<td class="jbbrd_frontend_field" style="min-width:200px;">
											<label>' . __( 'User role:', 'job-board' ) . '</label><br />
											<label><input type="radio" name="jbbrd_user_role" value="employer" /> ' . __( 'Employer', 'job-board' ) . '</label><br />
											<label><input type="radio" name="jbbrd_user_role" value="job_candidate" /> ' . __( 'Job candidate', 'job-board' ) . '</label>
										</td>
									</tr>
								</table><!-- #jbbrd_frontend_table -->
							</div><!-- .jbbrd_frontend_table_div -->
							<div style="clear:both;"></div>
						</div><!-- -->
						<div style="">
							<p id="jbbrd_frontend_submit" class="submit_div">
								<input type="hidden" name="jbbrd_frontend_registration_form" value="submit" />
								<input type="button" onclick="clear_frontend_input()" value="' . __( 'Clear', 'job-board' ) . '" />
								<input type="submit" class="button-primary" value="' . __( 'Register', 'job-board' ) . '" />
								<input type="hidden" name="jbbrd_registration_request" value="1" />
							</p>
						</div>
					</form>';
				} else {
					/* Preparing user name and user email fields. */
					$user_name	 	= isset( $_POST['jbbrd_user_login'] ) ? htmlspecialchars( stripslashes( trim( $_POST['jbbrd_user_login'] ) ) ) : "";
					$user_email 	= isset( $_POST['jbbrd_user_email'] ) ? trim( $_POST['jbbrd_user_email'] ) : "";
					$user_role	 	= isset( $_POST['jbbrd_user_role'] ) ? htmlspecialchars( stripslashes( trim( $_POST['jbbrd_user_role'] ) ) ) : "";

					$user_name 		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $user_name ) ) );
					$user_email	 	= ( is_email( $user_email ) ) ? $user_email : "";
					$user_role 		= strip_tags( preg_replace( '/<[^>]*>/', '', preg_replace( '/<script.*<\/[^>]*>/', '', $user_role ) ) );

					$jbbrd_register_error .= ( '' == $user_name ) ? __( 'Wrong user name.', 'job-board' ) . '&nbsp;' : '';
					$jbbrd_register_error .= ( '' == $user_email ) ? __( 'Wrong e-mail adress.', 'job-board' ) . '&nbsp;' : '';
					$jbbrd_register_error .= ( '' == $user_role ) ? __( 'Role was not selected.', 'job-board' ) . '&nbsp;' : '';
					/* Set errors if conditions are wrong. */
					if ( ( ! $jbbrd_register_error ) || ( '' == $jbbrd_register_error ) ) {
						if ( ( false == username_exists( $user_name ) ) && ( false == email_exists( $user_email ) ) ) {
							$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );
							/* Registered user args. */
							$userdata = array(
								'user_login'	=> $user_name,
								'user_email'	=> $user_email,
								'user_pass'		=> $random_password, /* When creating an user, `user_pass` is expected. */
								'role'			=> $user_role,
							);
							$user_id = wp_insert_user( $userdata );
							/* Send mail notification. */
							wp_new_user_notification( $user_id, $random_password );
							$jbbrd_register_form_content .= __( "Thank you for registering. Please check your email for password.", 'job-board' );
						} else {
							$jbbrd_register_form_content .= __( 'User already exists.  Password inherited.', 'job-board' );
						}
					} else {
						$jbbrd_register_form_content .= $jbbrd_register_error . __( 'New user registration failed...', 'job-board' );
					}
				}
			}
		} else {
			if ( ! current_user_can( 'read_private_vacancies', get_current_user_id() ) ) {
				$jbbrd_register_form_content .= sprintf( __( 'Please %slogout%s and register/login as Employer or Job candidate for possibility to sort jobs and sending CV.', 'job-board' ), '<a href="' . wp_logout_url( get_permalink() ) . '" title="' . __( 'Logout', 'job-board' ) . '">', '</a>' );
			} else {
				$current_user = wp_get_current_user();
				$jbbrd_register_form_content .= sprintf( __( 'You are logged as %s.', 'job-board' ), '<strong>' . $current_user->user_login . '</strong>' );
			}
		}
	return $jbbrd_register_form_content;
	}
}

/**
 * Replaces the excerpt "more" text by a link function.
 * @param $more string
 * @return string link to current vacancy post
 */
if ( ! function_exists( 'jbbrd_excerpt_more_link' ) ) {
	function jbbrd_excerpt_more_link( $more ) {
		global $jbbrd_options;
		$jbbrd_exerpt_more = '...&nbsp;' . '<a style="" href="';
		if ( isset(	$jbbrd_options['shortcode_permalink'] ) ) {
			$jbbrd_exerpt_more .= $jbbrd_options['shortcode_permalink'];
		}
		$vacancy_id_slug = ( ! get_option('permalink_structure') && ( trim( $jbbrd_options['shortcode_permalink'], '/' ) != get_option('home') ) ) ? '&vacancy_id=' : '?vacancy_id=';
		$jbbrd_exerpt_more .= $vacancy_id_slug . get_the_ID() . '">' . __( 'Read more...', 'job-board' ) . '</a>'; 
		return $jbbrd_exerpt_more;
	}
}

/**
 * Set pagination on vacancy page function.
 * @return string of pagination links
 */
if ( ! function_exists( 'jbbrd_vacancy_page_pagination' ) ) {
	function jbbrd_vacancy_page_pagination() {
		global $wp_query;
		$max = $wp_query->max_num_pages;
		if ( ! $pagecurrent = get_query_var( 'paged' ) )
			$pagecurrent = 1;
		$pages_array = array(
			'base' 		=> str_replace( 999999, '%#%', get_pagenum_link( 999999 ) ),
			'total' 	=> $max,
			'current' 	=> $pagecurrent,
			/* How many pages before and after current page. */
			'mid_size' 	=> 2,
			/* How many pages at start and at the end. */
			'end_size' 	=> 0, 
			'prev_text' => '&laquo;', 
			'next_text' => '&raquo;',
		);
		if ( $max > 1 )
			return '<div style="margin:20px 0 -20px 0;" class="page-navigation">' . paginate_links( $pages_array ) . '</div>';
	}
}

/**
 * Print alert to edit.php page when shortcode error
 * @return void
 */
if ( ! function_exists( 'jbbrd_add_error_to_vacancy_CPT_edit' ) ) {
	function jbbrd_add_error_to_vacancy_CPT_edit() {
		global $pagenow, $jbbrd_options;
		/* if we on vacancy edit page */
		if ( 'edit.php' == $pagenow && isset( $_GET['post_type'] ) && 'vacancy' == $_GET['post_type'] ) {
			/* if no shortcode found*/
			if ( '' == $jbbrd_options['shortcode_permalink'] ) {
				$error = '<strong>' . __( 'WARNING:', 'job-board' ) . '</strong>&nbsp;' . __( 'Shortcode is not found.', 'job-board' ) . '&nbsp;<br />' .
								sprintf( __( 'Please add jobs shortcode to your page or post to display content in the frontend using %s button', 'job-board' ), '<span class="bws_code"><img style="vertical-align: sub;" src="' . plugins_url( 'bws_menu/images/shortcode-icon.png', __FILE__ ) . '" alt=""/></span>' ) . '.'; ?>
				<div class="error" ><p><?php echo $error; ?></p></div>
			<?php }
		}
	}
}


/**
 * Update 'shortcode_permalink' plugin options value on plugin activation or if permalink structure has changed.
 * @return void
 */
if ( ! function_exists( 'jbbrd_update_permalink' ) ) {
	function jbbrd_update_permalink() {
		global $jbbrd_options;
		$jbbrd_shortcode_permalink = jbbrd_find_shortcode_page();
		if ( ! $jbbrd_options )
			$jbbrd_options = get_option( 'jbbrd_options' );
		if ( false == $jbbrd_shortcode_permalink ) {
			/* if no shortcode */
			if ( '' != $jbbrd_options['shortcode_permalink'] ) {
				$jbbrd_options['shortcode_permalink'] = '';
				update_option( 'jbbrd_options', $jbbrd_options );
			}
		} else {
			/* if shortcode exists */
			if ( $jbbrd_options['shortcode_permalink'] != $jbbrd_shortcode_permalink ) {
				$jbbrd_options['shortcode_permalink'] = $jbbrd_shortcode_permalink;
				update_option( 'jbbrd_options', $jbbrd_options );
			}
		}
	}
}

/**
 * Remove association with term, which was deleted
 * @return void
 */
if ( ! function_exists( 'jbbrd_delete_term' ) ) {
	function jbbrd_delete_term( $term_id ) {
		global $wpdb;
		$args = array(
			'post_type'     => 'vacancy',
			'posts_per_page'=> -1,
			'tax_query'     => array(
				array(
					'taxonomy' => 'jbbrd_employment',
					'field'    => 'id',
					'terms'    => $term_id
				)
			)
		);
		$new_query = new WP_Query( $args );
		if ( $new_query->have_posts() ) {
			foreach ( $new_query->posts as $post ) {
				do_action( 'delete_term_relationships', $post->ID, $term_id );
				$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $post->ID, $term_id ) );
				do_action( 'deleted_term_relationships', $post->ID, $term_id );
			}
			wp_update_term_count( $term_id, 'jbbrd_employment' );
		}
		wp_reset_query();
	}
}

/**
 * Update 'shortcode_permalink' plugin options value on post update.
 * @return void
 */
if ( ! function_exists( 'jbbrd_update_permalink_on_post_update' ) ) {
	function jbbrd_update_permalink_on_post_update( $post_id ) {
		/* check if post updated is not just a revision */
		if ( ! wp_is_post_revision( $post_id ) ) {
			global $jbbrd_options;
			$jbbrd_shortcode_permalink = jbbrd_find_shortcode_page();
			if ( ! $jbbrd_options )
				$jbbrd_options = get_option( 'jbbrd_options' );
			if ( false == $jbbrd_shortcode_permalink ) {
				/* if no shortcode */
				if ( '' != $jbbrd_options['shortcode_permalink'] ) {
					$jbbrd_options['shortcode_permalink'] = '';
					update_option( 'jbbrd_options', $jbbrd_options );
				}
			} else {
				/* if shortcode exists */
				if ( $jbbrd_options['shortcode_permalink'] != $jbbrd_shortcode_permalink ) {
					$jbbrd_options['shortcode_permalink'] = $jbbrd_shortcode_permalink;
					update_option( 'jbbrd_options', $jbbrd_options );
				}
			}
		}
	}
}

/* add shortcode content  */
if ( ! function_exists( 'jbbrd_shortcode_button_content' ) ) {
	function jbbrd_shortcode_button_content( $content ) {
		global $wp_version; ?>
		<div id="jbbrd" style="display:none;">
			<fieldset>
				<label>
					<input checked="checked" type="radio" name="jbbrd_select" value="jbbrd_vacancy" /> 
					<span class="checkbox-title">	
						<?php _e( 'Display jobs', 'job-board' ); ?>
					</span>	
				</label><br/>
				<label>
					<input type="radio" name="jbbrd_select" value="jbbrd_registration" /> 
					<span class="checkbox-title">	
						<?php _e( 'Display registration form', 'job-board' ); ?>
					</span>	
				</label>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[jbbrd_vacancy]" />
			<script type="text/javascript">
				function jbbrd_shortcode_init() {
					(function($) {	
						<?php if ( $wp_version < '3.9' ) { ?>	
							var current_object = '#TB_ajaxContent';
						<?php } else { ?>
							var current_object = '.mce-reset';
						<?php } ?>			

						$( current_object + ' input[name="jbbrd_select"]' ).on( 'change', function() {
							var shortcode = $( current_object + ' input[name="jbbrd_select"]:checked' ).val();
							$( current_object + ' #bws_shortcode_display' ).text( '[' + shortcode + ']' );
						});         
					})(jQuery);
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/* add help tab  */
if ( ! function_exists( 'jbbrd_add_tabs' ) ) {
	function jbbrd_add_tabs() {
		$screen = get_current_screen();
		if ( ( ! empty( $screen->post_type ) && 'vacancy' == $screen->post_type ) ||
			( ! empty( $screen->taxonomy ) && 'jbbrd_businesses' == $screen->taxonomy ) ||
			( ! empty( $screen->taxonomy ) && 'jbbrd_employment' == $screen->taxonomy ) ||
			( isset( $_GET['page'] ) && $_GET['page'] == 'job-board.php' ) ) {
			$args = array(
				'id' 			=> 'jbbrd',
				'section' 		=> '200538769'
			);
			bws_help_tab( $screen, $args );
		}
	}
}

/**
 * Uninstall the Vacancy post type function.
 * @return void
 */
if ( ! function_exists( 'jbbrd_plugin_uninstall' ) ) {
	function jbbrd_plugin_uninstall() {
		global $wpdb;
		/* Delete options */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'jbbrd_options' );
				$wpdb->delete( $wpdb->prefix . "posts", array( 'post_type' => 'vacancy' ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'jbbrd_businesses' ), array( '%s' ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'jbbrd_employment' ), array( '%s' ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'archive' ), array( '%s' ) );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'jbbrd_options' );
			$wpdb->delete( $wpdb->prefix . "posts", array( 'post_type' => 'vacancy' ) );
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'jbbrd_businesses' ), array( '%s' ) );
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'jbbrd_employment' ), array( '%s' ) );
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'archive' ), array( '%s' ) );
		}
		/* Removing plugin roles. */
		remove_role( 'employer' );
		remove_role( 'job_candidate' );
		/* Remove Vacancy CPT admins capabilities. */
		jbbrd_remove_administrators_capabilities();
	}
}

/* Activate plugin. */
register_activation_hook( __FILE__, 'jbbrd_plugin_install' );
/* Activate Job board settings page in admin menu. */
add_action( 'admin_menu', 'jbbrd_add_admin_menu' );
/* Initiate the Vacancy post type. */
add_action( 'init', 'jbbrd_init' );
/* Admin interface init. */
add_action( 'admin_init', 'jbbrd_admin_init' );
add_action( 'plugins_loaded', 'jbbrd_plugins_loaded' );
/* Set admin error message on custom metafields error. */
add_action( 'admin_notices', 'jbbrd_metafields_error_admin_notice', 0 );
/* Set JS scripts. */
add_action( 'admin_enqueue_scripts', 'jbbrd_load_scripts' );
add_action( 'wp_enqueue_scripts', 'jbbrd_load_scripts' );
/* Removes the permalinks display on the vacancy post type. */
add_action( 'admin_head', 'jbbrd_remove_permalink_line' );
/* Update messages for vacancy CPT. */
add_filter( 'post_updated_messages', 'jbbrd_vacancy_updated_messages' );
/* Set custom columns to "vacancy" custom post editor menu. */
add_action( 'manage_posts_custom_column', 'jbbrd_custom_columns' );
add_filter( 'manage_edit-vacancy_columns', 'jbbrd_edit_columns' );
/* Making content columns. */
add_filter( 'manage_edit-vacancy_sortable_columns',  'jbbrd_sortable_columns' );
/* Making content columns sortable. */
add_action( 'pre_get_posts', 'jbbrd_sortable_columns_orderby' );
/* Add view action to title row on vacancy posts view. */
add_filter( 'post_row_actions', 'jbbrd_change_title_row_actions', 10, 1 );
/* Add relative "View" node to admin menu. */
add_action( 'admin_bar_menu', 'jbbrd_add_relative_view', 999 );
/* Remove standart "View" node from admin menu. */
add_action( 'admin_bar_menu', 'jbbrd_remove_view', 999 );
/* Add filter for vacancy categories. */
add_action( 'restrict_manage_posts', 'jbbrd_restrict_manage_posts' );
/* Add filter to enable job_candidate vacancy list in profile screen option */
add_filter( 'set-screen-option', 'jbbrd_candidate_table_set_option', 10, 3 );
/* Add custom upload CV field to job_candidate profile page. */
add_action( 'show_user_profile', 'jbbrd_add_cv_load_field' );
add_action( 'edit_user_profile', 'jbbrd_add_cv_load_field' );
/* Saving custom upload CV field to job_candidate profile page. */
add_action( 'personal_options_update', 'jbbrd_save_cv_load_field' );
add_action( 'edit_user_profile_update', 'jbbrd_save_cv_load_field' );
/* Saving custom upload CV field to job_candidate profile page. */
add_action( 'user_edit_form_tag', 'jbbrd_form_accept_uploads' );
/* Add shortcode for print vacancy posts. */
add_shortcode( 'jbbrd_vacancy', 'jbbrd_vacancy_shortcode' );
/* Add shortcode for registration form. */
add_shortcode( 'jbbrd_registration', 'jbbrd_registration_shortcode' );
/* Add filter for shortcode in text widget. */
add_filter( 'widget_text', 'do_shortcode' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'jbbrd_shortcode_button_content' );
/* Insert post hook. */
add_action( 'save_post', 'jbbrd_save_post', 10, 2 );
/* update shortcode_permalink if any post is updated */
add_action( 'save_post', 'jbbrd_update_permalink_on_post_update' );
add_action( 'trashed_post ', 'jbbrd_update_permalink_on_post_update' );
/* update permalink to shortcode in plugin option if permalink structure has changed */
add_action( 'permalink_structure_changed', 'jbbrd_update_permalink' );
add_action( 'load-options-reading.php', 'jbbrd_update_permalink' );
/* Set schedule hook for move to archieve function */
add_action( 'jbbrd_move_vacancies_to_archive_dayly_function', 'jbbrd_set_archive_status_function' );
/* Add filter to vacancy CPT links. */
add_filter( 'post_type_link', 'jbbrd_append_vacancy_permalink', 10, 2 );
/* Additional links on the plugin page. */
add_filter( 'plugin_action_links', 'jbbrd_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'jbbrd_register_plugin_links', 10, 2 );
/* Uninstall plugin. Drop tables, delete options. */
register_uninstall_hook( __FILE__, 'jbbrd_plugin_uninstall' ); 