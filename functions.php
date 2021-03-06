<?php
add_filter( 'comments_open', '__return_false' );

//////////////////////////////////////////////////////
// Setup Theme
function miyazaki_en_setup() {

	register_default_headers( array(
		'birdfield_child'		=> array(
		'url'			=> '%2$s/images/header.jpg',
		'thumbnail_url'		=> '%2$s/images/header-thumbnail.jpg',
		'description_child'	=> 'birdfield'
		)
	) );
}
add_action( 'after_setup_theme', 'miyazaki_en_setup' );

//////////////////////////////////////////////////////
// Child Theme Initialize
function miyazaki_en_init() {

 	// add tags at page
	register_taxonomy_for_object_type('post_tag', 'page');

	// add post type fruits
	$labels = array(
		'name'		=> 'くだもの・野菜',
		'all_items'	=> 'くだもの・野菜一覧',
		);

	$args = array(
		'labels'			=> $labels,
		'supports'			=> array( 'title','editor', 'thumbnail', 'custom-fields' ),
		'public'			=> true,	// 公開するかどうが
		'show_ui'			=> true,	// メニューに表示するかどうか
		'menu_position'		=> 5,		// メニューの表示位置
		'has_archive'		=> true,	// アーカイブページの作成
		);

	register_post_type( 'fruits', $args );

	// add post type sweets
	$labels = array(
		'name'		=> '焼き菓子',
		'all_items'	=> '焼き菓子の一覧',
		);

	$args = array(
		'labels'			=> $labels,
		'supports'			=> array( 'title','editor', 'thumbnail', 'custom-fields' ),
		'public'			=> true,	// 公開するかどうが
		'show_ui'			=> true,	// メニューに表示するかどうか
		'menu_position'		=> 5,		// メニューの表示位置
		'has_archive'		=> true,	// アーカイブページの作成
		);

	register_post_type( 'sweets', $args );

	// add post type news
	$labels = array(
		'name'		=> '更新マニュアル',
		'all_items'	=> '更新マニュアルの一覧',
		);
	$args = array(
		'labels'			=> $labels,
		'supports'			=> array( 'title','editor', 'thumbnail' ),
		'public'			=> true,	// 公開するかどうが
		'show_ui'			=> true,	// メニューに表示するかどうか
		'menu_position'		=> 5,		// メニューの表示位置
		'has_archive'		=> true,	// アーカイブページの作成
		);
	register_post_type( 'manual', $args );

}
add_action( 'init', 'miyazaki_en_init', 0 );

//////////////////////////////////////////////////////
// Filter at main query
function miyazaki_en_query( $query ) {

 	if ( $query->is_home() && $query->is_main_query() ) {
		// toppage news
		$query->set( 'cat', get_cat_ID( 'お知らせ' ));
		$query->set( 'posts_per_page', 3 );
	}

	if ($query->is_main_query() && is_post_type_archive('fruits')) {
		// fruits
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', 'rand' );
	}
}
add_action( 'pre_get_posts', 'miyazaki_en_query' );

//////////////////////////////////////////////////////
// Enqueue Scripts
function miyazaki_en_scripts() {

	// css
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );

	if ( is_page() || is_home() ) {
		wp_enqueue_script( 'googlemaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyBqRX-Yuy2t26Sj3EHaheWV8eHRqnv3Hns' );
	}

	$deps =  array( 'jquery' , 'birdfield' );

	// zipcode js
	if( is_page()){
		wp_enqueue_script( 'ajaxzip3', '//ajaxzip3.github.io/ajaxzip3.js', array( 'jquery' ));
		$deps[] = 'ajaxzip3';

		wp_enqueue_style( 'magnific-popup', get_stylesheet_directory_uri().'/js/Magnific-Popup/magnific-popup.css' );
		wp_enqueue_script( 'magnific-popup', get_stylesheet_directory_uri() .'/js/Magnific-Popup/jquery.magnific-popup.min.js', array( 'jquery' ), '3.3.0');
		$deps[] = 'magnific-popup';
	}

	// miyazaki-en js
	wp_enqueue_script( 'miyazaki_en', get_stylesheet_directory_uri() .'/js/script.js', $deps, '1.10' );
}
add_action( 'wp_enqueue_scripts', 'miyazaki_en_scripts' );

//////////////////////////////////////////////////////
// Shortcode Goole Maps
function miyazaki_en_map ( $atts ) {

	$output = '<div id="map-canvas">地図はいります </div>';
	$output .= '<input type="hidden" id="map_icon_path" value="' .get_stylesheet_directory_uri() .'/images">';
	return $output;
}
add_shortcode( 'miyazaki_en_map', 'miyazaki_en_map' );

//////////////////////////////////////////////////////
// Shortcode Fruits Calendar Link
function miyazaki_en_fruits_calendar_link ( $atts ) {

	$html = '';
	if ( wp_is_mobile() ){
		$page = get_page_by_path( 'calendar' );
		$html = '<p><a href="' .get_the_permalink( $page->ID) .'">&raquo;' .$page->post_title .'</a></p>';
	}
	else{
		$html = do_shortcode( '[miyazaki_en_fruits_calendar]' );
	}

	return $html;
}
add_shortcode( 'miyazaki_en_fruits_calendar_link', 'miyazaki_en_fruits_calendar_link' );

//////////////////////////////////////////////////////
// Shortcode Fruits Calendar
function miyazaki_en_fruits_calendar ( $atts ) {

	extract( shortcode_atts( array(
		'title' => 'no'
		), $atts ) );

	$html_table_header = '<table class="fruits-calendar"><tbody><tr><th class="title">&nbsp;</th><th class="data"><span>4月</span><span>5月</span><span>6月</span><span>7月</span><span>8月</span><span>9月</span><span>10月</span><span>11月</span><span>12月</span><span>1月</span><span>2月</span><span>3月</span></th></tr>';
	$html_table_footer = '</tbody></table>';
	$html = '';

	$args = array(
		'posts_per_page' => -1,
		'post_type'	=> 'fruits',
		'post_status'	=> 'publish',
		'meta_key'		=> 'type',
		'orderby'		=> 'meta_value',
	);

	$the_query = new WP_Query($args);
	$type_current = '';
	if ( $the_query->have_posts() ) :
		while ( $the_query->have_posts() ) : $the_query->the_post();

		$type = get_field( 'type' );
		if( $type && ( $type != $type_current ) ){
			if( !empty( $html )){
				$html .= $html_table_footer;
			}

			$html .= '<div class="fruits-meta">' .miyazaki_en_get_type_label( $type ) .'</div>';
			$type_current = $type;
			$html .= $html_table_header;
		}

		// 収穫カレンダー
		$selected = get_field( 'calendar' );
		$html .= '<tr>';
		$html .= '<td class="title"><a href="' .get_permalink() .'">' .get_the_title() .'</a></td>';
		$html .= '<td class="data">';
		for( $i = 1; $i <= 12; $i++ ){

			$month = $i +3;
			if( 12 < $month ){
				$month -= 12;
			}

			if( $selected && in_array( $month, $selected ) ) {
				$html .= '<span class="best">' .$month .'</span>';
			}
			else{
				$html .= '<span>' .$month .'</span>';
			}
		}

		$html .= '</td>';
		$html .= '</tr>';

		endwhile;

		wp_reset_postdata();
	endif;

	if( !empty( $html )){
		$html .= $html_table_footer;
	}

	if( 'yes' === $title ){
		$html = '<h2>野菜収穫カレンダー</h2>' .$html;
	}

	return $html;
}
add_shortcode( 'miyazaki_en_fruits_calendar', 'miyazaki_en_fruits_calendar' );

//////////////////////////////////////////////////////
// Shortcode Fruit List
function miyazaki_en_fruits_list ( $atts ) {

	ob_start();

	$args = array(
		'post_type' => 'fruits',
		'post_status' => 'publish',
		'orderby'	=> 'rand',
	);

	if( is_home()){
		$args[ 'posts_per_page' ] = 6;
		$args[ 'meta_key' ] = '_thumbnail_id';
	}
	else{
		$args[ 'posts_per_page' ] = -1;
	}

	$the_query = new WP_Query($args);
	if ( $the_query->have_posts() ) :
		?> <div class="tile"><?php

		while ( $the_query->have_posts() ) : $the_query->the_post();
			get_template_part( 'content', 'fruits' );
		endwhile;

		?></div><?php

		wp_reset_postdata();
	endif;

	return ob_get_clean();
}
add_shortcode( 'miyazaki_en_fruits_list', 'miyazaki_en_fruits_list' );

//////////////////////////////////////////////////////
// Shortcode Swieets List
function miyazaki_en_sweets_list ( $atts ) {

	if( is_home()){
		return '';
	}

	ob_start();

	$args = array(
		'post_type' => 'sweets',
		'post_status' => 'publish',
		'orderby'	=> 'rand',
		'posts_per_page' => -1,
	);

	$the_query = new WP_Query($args);
	if ( $the_query->have_posts() ) :
		?> <div class="tile"><?php

		while ( $the_query->have_posts() ) : $the_query->the_post();
			get_template_part( 'content', 'sweets' );
		endwhile;

		?></div><?php

		wp_reset_postdata();
	endif;

	return ob_get_clean();
}
add_shortcode( 'miyazaki_en_sweets_list', 'miyazaki_en_sweets_list' );

//////////////////////////////////////////////////////
// Shortcode link button
function miyazaki_en_link ( $atts ) {

	$atts = shortcode_atts( array( 'title' => '', 'url' => '#' ), $atts );
	$title = $atts['title'];
	$url = $atts['url'];

	if( !strcmp( '#' ,$url )){
		return '';
	}

	if( '' === $title ){
		$title = $url;
	}

	$html = '<a href="' .esc_html( $url ) .'" class="miyazaki_en_link">' .$title .'</a>';

	return $html;
}
add_shortcode( 'miyazaki_en_link', 'miyazaki_en_link' );

//////////////////////////////////////////////////////
// Shortcode popup link
function miyazaki_en_popuplink ( $atts ) {

	$atts = shortcode_atts( array( 'title' => '', 'pagetitle' => '' ), $atts );
	$title = $atts['title'];
	$pagetitle = $atts['pagetitle'];

	if( !strcmp( '' ,$pagetitle )){
		return '';
	}

	if( '' === $title ){
		$title = $pagetitle;
	}

	$html = '<a href="#" class="miyazaki_en_link popup" pagetitle="' .$pagetitle .'">' .$title .'</a>';

	return $html;
}
add_shortcode( 'miyazaki_en_popuplink', 'miyazaki_en_popuplink' );

//////////////////////////////////////////////////////
// Display the Featured Image at fruit page
function miyazaki_en_post_image_html( $html, $post_id, $post_image_id ) {

	if( !( false === strpos( $html, 'anchor' ) ) ){
		$html = '<a href="' .get_permalink() .'" class="thumbnail">' .$html .'</a>';
	}

	return $html;
}
add_filter( 'post_thumbnail_html', 'miyazaki_en_post_image_html', 10, 3 );

/////////////////////////////////////////////////////
// get type label in fruits
function miyazaki_en_get_type_label( $value, $anchor = TRUE ) {
	$label ='';
	$fields = get_field_object( 'type' );
	$url = get_post_type_archive_link( 'fruits' );

	if( array_key_exists( 'choices' , $fields ) ){
		$label .= '<span>';
		if( $anchor ){
//			$label .= '<a href="' .$url .'type/' .$value .'">';
		}
		$label .= $fields[ 'choices' ][ $value ];
		if( $anchor ){
//			$label .= '</a>';
		}
		$label .= '</span>';
	}

	return $label;
}

/////////////////////////////////////////////////////
// get season label in fruits
function miyazaki_en_get_season_label( $value, $anchor = TRUE ) {
	$label ='';
	$fields = get_field_object( 'season' );
	$url = get_post_type_archive_link( 'fruits' );

	if( is_array($value)){
		foreach ( $value as $key => $v ) {
			if( array_key_exists( 'choices', $fields) ) {
				$label .= '<span>';
				if( $anchor ){
					$label .= '<a href="' .$url .'season/' .$v .'">';
				}
				$label .= ( $fields[ 'choices' ][ $v ] );
				if( $anchor ){
					$label .= '</a>';
				}
				$label .= '</span>';
			}
		}
	}
	else{
		if( array_key_exists( 'choices', $fields) ) {
			$label .= '<span>'. $fields[ 'choices' ][ $value ] .'</span>';
		}
	}

	return $label;
}

/////////////////////////////////////////////////////
// add permalink parameters for fruits
function miyazaki_en_query_vars( $vars ){
	$vars[] = "type";
	$vars[] = "season";
	return $vars;
}
add_filter( 'query_vars', 'miyazaki_en_query_vars' );

/////////////////////////////////////////////////////
// Add WP REST API Endpoints
function miyazaki_en_rest_api_init() {
	register_rest_route( 'get_page', '/(?P<pagetitle>.*)', array(
		'methods' => 'GET',
		'callback' => 'miyazaki_en_get_page',
		) );
}
add_action( 'rest_api_init', 'miyazaki_en_rest_api_init' );

function miyazaki_en_get_page( $params ) {
/*
	$page = get_page_by_title( urldecode( $params['pagetitle'] ));
	if( $page ) {
		return new WP_REST_Response( array(
			'id'		=> $page->ID,
			'title'		=> get_the_title( $page->ID ),
			'content'	=> apply_filters( 'the_content', $page->post_content )
		) );
	}
	else{
		$response = new WP_Error('error_code', 'Sorry, no posts matched your criteria.' );
		return $response;
	}
*/
	$find = FALSE;
	$id = 0;
	$title = '';
	$content = '';

	$args = array(
		'title'			=> urldecode( $params[ 'pagetitle' ] ),
		'posts_per_page'	=> 1,
		'post_type'		=> 'page',
		'post_status'		=> 'publish',
	);

	$the_query = new WP_Query($args);
	if ( $the_query->have_posts() ) :
		$find = TRUE;
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$id = get_the_ID();
			$title = get_the_title( );
			$content = apply_filters('the_content', get_the_content() );
			break;
		endwhile;

		wp_reset_postdata();
	endif;

	if($find) {
		return new WP_REST_Response( array(
			'id'		=> $id,
			'title'		=> $title,
			'content'	=> $content,
		) );
	}
	else{
		$response = new WP_Error('error_code', 'Sorry, no posts matched your criteria.' );
		return $response;
	}
}

/////////////////////////////////////////////////////
// show catchcopy at fruits tile
function miyazaki_en_get_catchcopy() {

	$catchcopy = get_field( 'catchcopy' );
	if( $catchcopy ){
		return '<p class="catchcopy">' .$catchcopy .'</p>';
	}

	return NULL;
}

/////////////////////////////////////////////////////
// show sweets price
function miyazaki_en_get_sweets_price() {

	$price = get_field( 'price' );
	if( $price ){
		return '<p class="price">' .$price .' 円</p>';
	}

	return NULL;
}

//////////////////////////////////////////////////////
// bread crumb
function miyazaki_en_content_header( $arg ){

	$html = '';

	if( !is_home()){
		if ( class_exists( 'WP_SiteManager_bread_crumb' ) ) {
			$html .= '<div class="bread_crumb_wrapper">';
			$html .= WP_SiteManager_bread_crumb::bread_crumb( array( 'echo'=>'false', 'home_label' => 'ホーム', 'elm_class' => 'bread_crumb container' ));
			$html .= '</div>';
		}
	}

	return $html;

}
add_action( 'birdfield_content_header', 'miyazaki_en_content_header' );

//////////////////////////////////////////////////////
// show eyecarch on dashboard
function miyazaki_en_manage_posts_columns( $columns ) {
	$columns[ 'thumbnail' ] = __( 'Thumbnail' );
	return $columns;
}
add_filter( 'manage_posts_columns', 'miyazaki_en_manage_posts_columns' );
add_filter( 'manage_pages_columns', 'miyazaki_en_manage_posts_columns' );

function miyazaki_en_manage_posts_custom_column( $column_name, $post_id ) {
	if ( 'thumbnail' == $column_name ) {
		$thum = get_the_post_thumbnail( $post_id, 'small', array( 'style'=>'width:100px;height:auto;' ));
	} if ( isset( $thum ) && $thum ) {
		echo $thum;
	} else {
		echo __( 'None' );
	}
}
add_action( 'manage_posts_custom_column', 'miyazaki_en_manage_posts_custom_column', 10, 2 );
add_action( 'manage_pages_custom_column', 'miyazaki_en_manage_posts_custom_column', 10, 2 );

//////////////////////////////////////////////////////
// add body class
function miyazaki_en_body_class( $classes ) {
	if ( is_page() ) {
		$page = get_post( get_the_ID() );
		$classes[] = $page->post_name;
	}

	return $classes;
}
add_filter( 'body_class', 'miyazaki_en_body_class' );

//////////////////////////////////////////////////////
// login logo
function miyazaki_en_login_head() {

	$url = get_stylesheet_directory_uri() .'/images/login.png';
	echo '<style type="text/css">.login h1 a { background-image:url(' .$url .'); height: 117px; width: 151px; background-size: 100% 100%;}</style>';
}
add_action('login_head', 'miyazaki_en_login_head');

//////////////////////////////////////////////////////
// remove emoji
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles', 10 );

//////////////////////////////////////////////////////
// set favicon
function miyazaki_en_favicon() {
	echo '<link rel="shortcut icon" type="image/x-icon" href="' .get_stylesheet_directory_uri() .'/images/favicon.ico" />'. "\n";
	echo '<link rel="apple-touch-icon" href="' .get_stylesheet_directory_uri() .'/images/webclip.png" />'. "\n";
}
add_action( 'wp_head', 'miyazaki_en_favicon' );

//////////////////////////////////////////////////////
// remove theme customize
function miyazaki_en_customize_register( $wp_customize ) {
	$wp_customize->remove_control( 'header_image' );
	$wp_customize->remove_section( 'static_front_page' );
	$wp_customize->remove_section( 'background_image' );
	$wp_customize->remove_section( 'custom_css' );
}
add_action( 'customize_register', 'miyazaki_en_customize_register' );

//////////////////////////////////////////////////////
// Google Analytics
function miyazaki_en_wp_head() {
	if ( !is_user_logged_in() ) {
		get_template_part( 'google-analytics' );
	}
}
add_action( 'wp_head', 'miyazaki_en_wp_head' );


//////////////////////////////////////////////////////
// image optimize
function miyazaki_en_handle_upload( $file )
{
	if( $file['type'] == 'image/jpeg' ) {
		$image = wp_get_image_editor( $file[ 'file' ] );

		if (! is_wp_error($image)) {
			$exif = exif_read_data( $file[ 'file' ] );
			$orientation = $exif[ 'Orientation' ];
			$max_width = 930;
			$max_height = 930;
			$size = $image->get_size();
			$width = $size[ 'width' ];
			$height = $size[ 'height' ];

			if ( $width > $max_width || $height > $max_height ) {
				$image->resize( $max_width, $max_height, false );
			}

			if (! empty($orientation)) {
				switch ($orientation) {
					case 8:
						$image->rotate( 90 );
						break;

					case 3:
						$image->rotate( 180 );
						break;

					case 6:
						$image->rotate( -90 );
						break;
				}
			}
			$image->save( $file[ 'file' ]) ;
		}
	}

	return $file;
}
add_action( 'wp_handle_upload', 'miyazaki_en_handle_upload' );