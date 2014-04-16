<?php

// wp shell --path=/usr/share/wordpress

$_POST=array(
   "wp_resume_section" => 2, 
   "wp_resume_organization"=>3,
   "from"=>"August 1968",
   "to"=>"August 2014",
   "menu_order"=>0,
   "post_title"=>"ciao mamma",
   "post_content"=>"qesta volta no, non devi amarmi troppo",
   "post_type"=>"wp_resume_position",
   "post_status"=>"publish",
   "post_status" => "publish",
   "comment_status"=>"closed",
   "ping_status"=>"closed",
   "page_template"=>"resume.php"
);

print ("HAS55: ".has_action('55')."\n");

print ("wip:".wp_insert_post( $_POST, 1)."\n");



//
//do_action('save_posta', 57, $_POST);
//
//has_action('save_post')
//
//   did_action('save_post')
//

// print ("HA: ". has_action('save_post'). "\n");



// $args = array(
//       'post_type'         => 'wp_resume_position',
//       'orderby'           => 'menu_order',
//       'order'             => 'ASC',
//       'numberposts'       => -1,
//       'wp_resume_section' => $section->slug,
//       'exclude'           => $post_id
//       );
// $posts = get_posts( $args );
// 
// foreach ($posts as $post) {
//    print_r ($post);
// }
// 
// tipo: WP_Post: 3 instanze
// 
// $ID: 4 - int
// $post_content: blabla ita eng blabla - string
// $post_title: Sviluppatore,Developer - string
// $post_excerpt: "" - string
// $post-name: sviluppatore-software - string
// $guid: http://lh/blog/?post_type=wp_resume_position$#038;p=4
// $menu_order: 1 - int
// $post_type: wp_resume_position - string
// 


?>
