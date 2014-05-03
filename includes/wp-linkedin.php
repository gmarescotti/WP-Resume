<?php

require_once dirname( __FILE__ ) . '/linkedin_parser.php';

// wp shell --path=/usr/share/wordpress

define('WP_DEBUG', true);

class WordpressHResumeWriter extends HResumeWriter {

   public function __construct() {
      parent::__construct();
      $this->delete_all_positions();
   }

   public function added_hresume($resume_name) { }

   public function added_vcalendar($resume_name, $calendar_name) { }

   public function added_experience($resume_name, $calendar_name, Experience &$experience) { }

   private function translate_string($i18n_experience, $param) {
      if (count($i18n_experience) == 1) {
	 return reset($i18n_experience); // GET FIRST EXPERIENE FROM ARRAY
      }
      $ret = '';
      foreach ($i18n_experience as $exp) {
	 $ret .= '<!--:'.$exp->getLang().'-->'.$exp->$param.'<!--:-->';
      }
      return $ret;
   }

   // merge_languages_for_qtranslator_plugin
   public function upload() {
      foreach ($this->resumes as $name=>$resume) {
	 foreach ($resume as $calendar_name=>$calendar) {
	    foreach ($calendar as $i18n_experience) {
	       $this->resume_insert_post($calendar_name, $i18n_experience);
	    }
	 }
      }
   }

   private function resume_insert_post($vcalendar_name, $i18n_experience) {
      $table_section = array();
      $table_section['profile-experience']='experiences';

      global $q_config;
      if (isset($q_config)) {
	 $experience = $i18n_experience[$q_config['default_language']];
      } else {
	 $experience = reset($i18n_experience);
      }

      if (!array_key_exists($vcalendar_name, $table_section)) {
         exit ('Manca section: '.$vcalendar_name);
      }
      $section = $table_section[$vcalendar_name];
      // $org_name = translate_string($i18n_experience, 'orgName');
      // $org_location = $experience->location;
      // $org_link = $experience->href;
      $from = $experience->dtstart;
      $to = $experience->dtend;
      $title = $this->translate_string($i18n_experience, 'title');
      $details = $this->translate_string($i18n_experience, 'details');

      print("================ new position ===================<br/>");
      $section_term = get_term_by ('slug', $section, 'wp_resume_section', 'ARRAY_A');

      if (!$section_term) {
	 exit('no section named '.$section.' found!<br/>');
      }

      $org_term = $this->store_new_organization($i18n_experience);

      global $_POST;
      $_POST=array(
	    'post_title'   		=> $title,
	    'post_content' 		=> $details,
	    'post_status'  		=> 'publish',
	    'post_type'   		=> 'wp_resume_position',
	    'post_excerpt' 		=> '',
	    'comment_status' 		=> 'closed',
	    'ping_status' 		=> 'closed',
	    'from'			=> $from,
	    'menu_order' 		=> $experience->getID(),
	    'to'			=> $to,
	    'wp_resume_section' 	=> (int)$section_term['term_id'],
	    'wp_resume_organization' 	=> (int)$org_term['term_id'],
	    'wp_resume_nonce' 		=> wp_create_nonce('wp_resume_taxonomy' , 'wp_resume_nonce'),
	    );

      $postid = wp_insert_post( $_POST, true );

   }

   private function store_new_organization($i18n_experience) {

      global $q_config;
      if (isset($q_config)) {
	 $experience = $i18n_experience[$q_config['default_language']];
      } else {
	 $experience = reset($i18n_experience);
      }

      $website = $experience->href; // ONLY 1 SITE!
      $org = $experience->orgName; // FIRST ORG USED AS SLUG AND NAME
      $location = $experience->location; // ONLY 1 LOCATION in wordpress 2 in linkein :(

      if (!$website) { $website = 'www.google.com'; }
      $org_term = get_term_by ('name', $org, 'wp_resume_organization', 'ARRAY_A');
      if ($org_term) {
	 return $org_term;
      }

      $_POST = array(
	    'description'=> $location,
	    'wp_resume_nonce' => wp_create_nonce('wp_resume_org_link' , 'wp_resume_nonce'),
	    'org_link' => $website,
	    'action' => 'add-tag',
	    'screen' => 'edit-wp_resume_organization',
	    'taxonomy' => 'wp_resume_organization',
	    'post_type' => 'wp_resume_position',
	    '_wpnonce_add-tag' => wp_create_nonce('wp_resume_org_link' , 'wp_resume_nonce'),
	    '_wp_http_referer' => '/blog/wp-admin/edit-tags.php?taxonomy=wp_resume_organization&post_type=wp_resume_position',
	    'tag-name' => $org,
	    'slug' => '',
	    'parent' => '-1',
	    );

      foreach ($i18n_experience as $experience) {
	 $_POST['qtrans_term_'.$experience->getLang()] = $experience->orgName;
      }

      $ret = wp_insert_term(
	    $org,
	    'wp_resume_organization',
	    $_POST
	 );

      if (!$ret) {
	 var_dump($ret);
	 print "<br/>";
	 error("Organization failed [$org]: $ret</br>");
      } else {
	 print("Organization succesfully added [$org] ".$ret[0]."</br>");
      }

      qtrans_updateTermLibrary();

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
	 print ("Removed organization $term->term_id<br/>");
      }

   }
}; // class

?>

