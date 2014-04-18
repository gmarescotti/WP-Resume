<?php

// wp shell --path=/usr/share/wordpress

function store_experience_in_post2( $section, $org, $from, $to, $title, $details) {
   $_POST=array(
      "wp_resume_section" => $section, 	// 2, 
      "wp_resume_organization"=>$org, 	// 3,
      "from"=>$from,			// "August 1968",
      "to"=>$to,			// "August 2014",

      "menu_order"=>0,
      "post_title"=>$title,		// "ciao mamma",
      "post_content"=>$details,		// "qesta volta no, non devi amarmi troppo",
      "post_type"=>"wp_resume_position",
      "post_status"=>"publish",
      "post_date" => "0000-00-00 00:00:00",
      "comment_status"=>"closed",
      "ping_status"=>"closed",
      "page_template"=>"resume.php",
      "post_author" => 1
   );

   // // print ("HAS55: ".has_action('55')."\n");

   wp_insert_post( $_POST, 1);
   // print ($section.",".$org.",".$from.",".$to.",".$title.",".$details."\n");
 
}

function store_experience_in_post33( $section, $org, $from, $to, $title, $details) {
   global $user_ID;
   $new_post = array(
      'post_title' => 'My New Post',
      'post_content' => 'Lorem ipsum dolor sit amet...',
      'post_status' => 'publish',
      'post_date' => date('Y-m-d H:i:s'),
      'post_author' => $user_ID,
      'post_type' => 'post',
      'post_category' => array(0),
      'wp_resume_section' => $section,
      'wp_resume_organization'=>$org,
      'from'=>$from,
      'to'=>$to,
      'page_template'=>'resume.php',
      'comment_status'=>'closed',
      'ping_status'=>'closed',
      'post_name' => 'experience'
   );
   $post_id = wp_insert_post($new_post);

}

function store_experience_in_post( $section, $org, $from, $to, $title, $details) {
   wp_insert_post(
      array(
	 'post_name'   => 'experience-12-1',
	 'post_title'    => 'mancuso svedese',
	 'post_content'  => 'revidere ridere derireder',
	 'post_status'   => 'publish',
	 'post_type'   => 'wp_resume_position',
	 'post_date'   => '2014-04-18 14:39:24',
	 'post_excerpt' => ''
      )
   );
}


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
