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

   public function added_experience($resume_name, $calendar_name, Experience &$experience) { }

   private function translate_string($i18n_experience, $param) {
      if (count($i18n_experience) == 1) {
	 foreach($i18n_experience as $x) {print ")))))))) $x<br/>";};
	 print "<br/>";
	 reset($i18n_experience); // GET FIRST EXPERIENE FROM ARRAY
	 return key($i18n_experience);
      }
      $ret = '';
      foreach ($i18n_experience as $exp) {
	 $ret .= '<!--:'.$exp->language.'-->'.$exp->$param.'<!--:-->';
      }
      return $ret;
   }

   // public function merge_i18n_experience(Experience &$dest, Experience $src) {
   //    foreach (array('title') as $key) {
   //       $dest->$key .= translate_string($src->getLang(), $src->$key); 
   //    }
   // }

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

      reset($i18n_experience); // GET FIRST EXPERIENE FROM ARRAY
      $experience = key($i18n_experience);

      if (!array_key_exists($vcalendar_name, $table_section)) {
         exit ('Manca section: '.$vcalendar_name);
      }
      $section = $table_section[$vcalendar_name];
      // $org_name = translate_string($i18n_experience, 'orgName');
      // $org_location = $experience->location;
      // $org_link = $experience->href;
      $from = $experience->dtstart;
      $to = $experience->dtend;
print "IIIIII\n";
      $title = $this->translate_string($i18n_experience, 'title');
      $details = $this->translate_string($i18n_experience, 'details');

      print("================ new position in $org_name ===================<br/>");

      $section_term = get_term_by ('slug', $section, 'wp_resume_section', 'ARRAY_A');

      if (!$section_term) {
	 exit('no section named '.$section.' found!<br/>');
      }

      $org_term = get_term_by ('name', $org_name, 'wp_resume_organization', 'ARRAY_A');

      if (!$org_term) {
	 $org_term = $this->store_new_organization($i18n_experience);
      }

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
	    'menu_order' 		=> $this->experience_index,
	    'to'			=> $to,
	    'wp_resume_section' 	=> (int)$section_term['term_id'],
	    'wp_resume_organization' 	=> (int)$org_term['term_id'],
	    'wp_resume_nonce' 		=> wp_create_nonce('wp_resume_taxonomy' , 'wp_resume_nonce'),
	    );

      $postid = wp_insert_post( $_POST, true );
      var_dump($postid);
      print "<br/>";
      var_dump($_POST);
      print "<br/>";
   }

   private function store_new_organization($i18n_experience) {

      // $org, $location, $website
      reset($i18n_experience);
      $experience = key($i18n_experience);
  
      $website = $experience->href; // ONLY 1 SITE!
      $org = $experience->orgName; // FIRST ORG USED AS SLUG AND NAME
      $location = $experience->location; // ONLY 1 LOCATION in wordpress 2 in linkein :(

      global $_REQUEST;
      $_REQUEST = array(
	    'description'=> $location,
	    'wp_resume_nonce' => wp_create_nonce('wp_resume_org_link' , 'wp_resume_nonce'),
	    );
      // 'org_link' => $website,

      foreach ($i18n_experience as $experience) {
	 $_REQUEST['qtrans_term_'.$experience->language] = $experience->name;
      }

      $ret = wp_insert_term(
	    $org,
	    'wp_resume_organization',
	    $_REQUEST
	    );
      if (!$ret) {
	 error("Organization failed [$org]</br>");
      } else {
	 print("Organization succesfully added [$org]</br>");
      }

      set_org_link( $ret, $website );

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
	 print ("Removed organization $term->term_id<br/>");
      }

   }
}; // class

?>

