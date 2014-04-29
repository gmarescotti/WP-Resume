<?php

require_once dirname( __FILE__ ) . '/linkedin_parser.php';

// wp shell --path=/usr/share/wordpress

class WordpressHResumeWriter extends HResumeWriter {

   public function __construct() {
      parent::__construct();
      $this->delete_all_positions();
   }

   public function added_hresume($resume_name) { }

   public function added_vcalendar($resume_name, $calendar_name) { }

   public function added_experience($resume_name, $calendar_name, Experience &$experience_class) { }

   public function upload() {
   }

   private wrapped_insert_post($vcalendar_name], $experience_class) {
      $table_section = array();
      $table_section['profile-experience']='experiences';
      if (!array_key_exists($vcalendar_name, $table_section)) {
         exit ('Manca section: '.$vcalendar_name);
      }
      $section = $table_section[$vcalendar_name];
      $org_name = $experience_class->orgName;
      $org_location = $experience_class->location;
      $org_link = $experience_class->href;
      $from = $experience_class->dtstart;
      $to = $experience_class->dtend;
      $title = $experience_class->title;
      $details = $experience_class->details;

      print('================ new position ===================<br/>');

      $section_term = get_term_by ('slug', $section, 'wp_resume_section', 'ARRAY_A');

      if (!$section_term) {
	 exit('no section named '.$section.' found!<br/>');
      }

      $org_term = get_term_by ('name', $org_name, 'wp_resume_organization', 'ARRAY_A');

      if (!$org_term) {
	 $org_term = $this->store_new_organization($org_name, $org_location, $org_link);
      }

      global $_POST;
      $_POST=array(
	    'post_title'   	=> $title,
	    'post_content' 	=> $details,
	    'post_status'  	=> 'publish',
	    'post_type'   		=> 'wp_resume_position',
	    'post_excerpt' 	=> '',
	    'comment_status' 	=> 'closed',
	    'ping_status' 		=> 'closed',
	    'from'			=> $from,
	    'menu_order' 		=> $this->experience_index,
	    'to'			=> $to,
	    'wp_resume_section' 	=> (int)$section_term['term_id'],
	    'wp_resume_organization' => (int)$org_term['term_id'],
	    'wp_resume_nonce' 	=> wp_create_nonce('wp_resume_taxonomy' , 'wp_resume_nonce'),
	    );

      $postid = wp_insert_post( $_POST, true );
      var_dump($postid);
      print "<br/>";
      var_dump($_POST);
      print "<br/>";
   }

   private function store_new_organization($org, $location, $website) {
      global $_REQUEST;
      $_REQUEST = array(
	    'org_link' => $website,
	    'description'=> $location,
	    'wp_resume_nonce' => wp_create_nonce('wp_resume_org_link' , 'wp_resume_nonce'),
	    );

      $ret = wp_insert_term(
	    $org,
	    'wp_resume_organization',
	    $_REQUEST
	    );
      if (!$ret) {
	 print('Organization failed ['.$org.']</br>');
      } else {
	 print('Organization added ['.$org.']</br>');
      }
      var_dump($ret);
      print "<br/>";
      return $ret;
   }

   private function delete_all_positions() {
      //loop through posts
      $all_posts = get_posts(
	 array(
	    'post_type'         => 'wp_resume_position',
	    'orderby'           => 'menu_order',
	    'order'             => 'ASC',
	    'numberposts'       => -1,
	    'wp_resume_section' => 'experiences',
	 )
      );

      if (sizeof($all_posts) <= 0) {
	 print ("No posts found with wp_resume_section=experiences<br/>");
      }

      print ("================ removing all position =================<br/>");
      foreach ($all_posts as $post) {
	 wp_delete_post( $post->ID, true );
	 print ("Removed post $post->post_title<br/>");
      }

      foreach (get_terms('wp_resume_organization', 'hide_empty=0') as $term) {
	 $ret = wp_delete_term( $term->term_id, 'wp_resume_organization' );
      }

   }
}; // class

?>

