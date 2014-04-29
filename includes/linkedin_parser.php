<?php
/*
///// come copiare i contenuti da LINKEDIN.xml a WORDPRESS.xml
Struttura LINKEDIN.xml:

<div class="section" id="profile-summary">
<div class="section" id="profile-experience">
<div class="section" id="profile-languages">
<div class="section" id="profile-skills">
<div class="section" id="profile-education">
<div class="section" id="profile-additional">
<div class="section" id="profile-contact">


	hresume -> vcalendar -> vcard...

	vcard:
		#title: 	ROLE
		#org:   	COMPANY NAME
		#orgstats:	COMPANY DETAILS (solo linkedin!)
		#dtstart: 	DATA INIZIO (mese anno)
		#dtstamp/dtend:	DATA FINE / Presente (mese anno)
		#duration: 	DURATA (mesi e anni)
		#location:	CITTA (PROVINCIA)
		#description:	DESCRIZIONE ATTIVITA'
		
VINCOLI NODI:
prima di vcalendar controllare #id=profile-experience

Struttura WORDPRESS:

	hresume -> vcalendar -> vevent.. -> vcard...

	vevent:
		#orgName/a: 	<COMPANY NAME>
		#orgName/a[@href]: <COMPANY WEB SITE> (solo wordpress!)
		#location:	<LOCATION>

	vcard:
		#title:		<ROLE>
		#dtstart[1]:	<DATA INIZIO> (mese anno)
		#dtstart[2]:	<DATA FINE> (mese anno)
		#details/p:	<DESCRIZIONE ATTIVITA>
		

Dati della struttura WORDPRESS da replicare nel template vevent:
	#vevent[@id= "minuscolo(<COMPANY NAME>)"]
	#orgName[@id= "minuscolo(<COMPANY NAME>)-name"]
	#orgName/a[@title= "<COMPANY NAME>"]

Dati della struttura WORDPRESS da replicare nel template vcard:

	a[2][@href="minuscolo(<COMPANY NAME>)-name" @title="<COMPANY NAME>"]
	#dtstart[1][@datetime="dataformatoaaaa-mm-01(<DATA INIZIO>)" @title="stesso"]
	#dtstart[2][@datetime="dataformatoaaaa-mm-01(<DATA INIZIO>)" @title="stesso"]
	
*/

assert_options(ASSERT_BAIL, 1); // terminate execution on failed assertions

class Experience { 
   public $id;
   public $language;

   public function __construct($id, $language) {
      $this->id = $id;
      $this->language = $language;
   }

   // VEVENT
   public $orgName;	// COMPANY NAME
   public $href; 	// SOLO WORDPRESS
   public $location; // CITTA, NAZIONE
   public $orgstats; // COMPANY DETAILS (solo linkedin!)

   // VCARD
   public $title;   // <ROLE>
   public $dtstart; // <DATA INIZIO> (mese anno)
   public $dtend;   // <DATA FINE> (mese anno)
   public $details; // <DESCRIZIONE ATTIVITA>

   public $next = null;
   public function &last() {
      $ret = &$this;
      while ($ret->next) { $ret = &$ret->next; }
      return $ret->next;
   }

   public function __toString() {
      return 
	 "COMPANY NAME: ".$this->orgName."\n".
	 "href(SOLO WORDPRESS): ".$this->href."\n".
	 "CITTA, NAZIONE: ".$this->location."\n".
	 "COMPANY DETAILS (solo linkedin!): ".$this->orgstats."\n".
	 "ROLE: ".$this->title."\n".
	 "DATA INIZIO (mese anno) :".$this->dtstart."\n".
	 "DATA FINE (mese anno) :".$this->dtend."\n".
	 "DESCRIZIONE ATTIVITA: "."\n";
	 // "DESCRIZIONE ATTIVITA: ".$this->details."\n";
   }
};

abstract class HResumeWriter {

   abstract protected function added_hresume($resume_name);
   abstract protected function added_vcalendar($resume_name, $calendar_name);
   abstract protected function added_experience($resume_name, $calendar_name, Experience &$experience);

   abstract protected function upload();

   public $resumes = array();

   function add_hresume($resume_name) {
      // if present same CV (mame surname) => it is another language!
      // we will merge nested info
      if (!array_key_exists($resume_name, $this->resumes)) {
	 $this->resumes[$resume_name] = array();
      }
      $this->added_hresume($resume_name);
      return $this->resumes[$resume_name];
   }

   function add_vcalendar($resume_name, $calendar_name) {
      $resume = &$this->resumes[$resume_name];
      if (!array_key_exists($calendar_name, $resume)) {
	 $resume[$calendar_name] = array();
      }
      $this->added_vcalendar($resume_name, $calendar_name);
      return $resume[$calendar_name];
   }

   function add_experience($resume_name, $calendar_name, Experience &$experience) {
      $resume = &$this->resumes[$resume_name];
      $calendar = &$resume[$calendar_name];
      if (!array_key_exists($experience->id, $calendar)) {
	 $calendar[$experience->id] = $experience;
      } else {
	 $last = &$calendar[$experience->id]->last();
	 $last = $experience;
      }
      $this->added_experience($resume_name, $calendar_name, $experience);
   }

   public function __toString() {
      $ret = "";
      foreach ($this->resumes as $name=>$resume) {
	 $ret .= "NEW RESUME OF $name\n";
	 foreach ($resume as $name=>$calendar) {
	    $ret .= "  CALENDAR FOR $name\n";
	    foreach ($calendar as $experience) {
	       while ($experience) {
		  $ret.= "    -------------$experience->language-------------------\n";
		  foreach (split("\n", $experience->__toString()) as $exp_line) {
		     $ret.= "    ".$exp_line."\n";
		  }
		  $experience = $experience->next;
	       }
	    }
	 }
      }
      return $ret;
   }

};

abstract class HResumeReader {
   public $xmlfile_base;

   public $doms = array(); // array of doms indexed by languages (i.e. 'en')

   public function __construct() {
      // $this->xmlfile = dirname( __FILE__ ).'/linkedin.xml';
   }

   public function import_xml_file($xmlfile) {
      $ext = pathinfo($xmlfile);

      if (!file_exists($xmlfile))  {
         exit ("File does not exists: $xmlfile\n");
      }
      
      $dom = new DOMDocument();

      $save_rep = error_reporting();
      error_reporting(E_ERROR);

      $dom->loadHTML(file_get_contents($xmlfile));
      $dom->loadXML($dom->saveXML());
      error_reporting($save_rep);

      array_push($this->doms, $dom);
   }

   abstract protected function get_all_hresumes();
   abstract protected function get_all_vcalendars($hresume);
   abstract protected function get_all_vcards($vcalendar);

   abstract protected function get_info_for_hresume($hresume);
   abstract protected function get_info_for_vcalendar($vcalendar);
   abstract protected function get_info_for_experience($hresume);

   abstract protected function read_data(DOMNode $vcard, Experience &$experience);

   public function parse_hresume(HResumeWriter &$hrs) {

      // ----------------------------------------------------------------------------------------------
      // PROCEDURE DI MERGE LINKEDIN IN WORDPRESS OF RESUME DATA:		
      // 
      // 1) CARICARE HResumeReader LINKEDIN E WORDPRESS (QUELLO VECCHIO CON DATI VECCHI - ! ALMENO UN ESPERIENZA DEVE ESSERE PRESENTE DA USARE COME TEMPLATE)
      // 2) CANCELLARE #vevent[1]/#vcard[2-*] da WORDPRESS
      // .) e SALVARE UN NODO WORDPRESS:#vevent[1] in TEMPLATEVEVENT e SALVARE NODO PADRE IN WORDPRESS-BASE
      // .) e CANCELLARE WORDPRESS:#vevent[*]
      // 3) PER OGNI NODO=LINKEDIN:#hresume.#vcalendar.#vcard :
      // 	.) applicare filtri a sorgente linkedin (se non applicabile->continue)
      // 	.) se #org non è presente in #orgname/a
      // 		.) aggiungere un nuovo TEMPLATEVEVENT a WORDPRESS-BASE 
      // 		.) riempire dati vevent
      // 		.) replicare i dati doppi
      // 	.) altrimenti
      // 		.) aggiungere un nuovo TEMPLATEVENT/#vcard[1] all'elemento presente
      // 	.) riempire i dati vcard
      // 	.) replicare i dati doppi

      // 1)
      // $dom_src = new DOM_LINKEDIN("linkedin.xml");
      // $dom_dst = new DOM_WORDPRESS("wordpress.xml");

      // 2) TODO

      // 3)
      foreach ($this->get_all_hresumes() as $hresume) {
         $hresume_name = $this->get_info_for_hresume($hresume);
         $hrs->add_hresume($hresume_name);
	 foreach ($this->get_all_vcalendars($hresume) as $vcalendar) {
	    $vcalendar_name = $this->get_info_for_vcalendar($vcalendar);
	    $hrs->add_vcalendar($hresume_name, $vcalendar_name);
	    $id = 1;
	    foreach ($this->get_all_vcards($vcalendar) as $vcard) {
	       $experience = new Experience($id++, $this->get_info_for_experience($hresume));
	       $this->read_data($vcard, $experience);
	       $hrs->add_experience($hresume_name, $vcalendar_name, $experience);
	    }
	 }
      }

      $hrs->upload();
   }

};

class DOM_LINKEDIN extends HResumeReader {

   public $public_profile_url;

   public function __construct() {
      parent::__construct();
      // $this->download_xml_from_internet('https://www.linkedin.com/pub/giulio-marescotti/5/235/380');
      
      $profiles_filename = glob(dirname( __FILE__ ) . "/*LinkedIn.html"); // i.e. <Giulio Marescotti ita LinkedIn.html>
      foreach ($profiles_filename as $profile_filename) {
	 print("opening file $profile_filename ...<br/>");
	 $this->import_xml_file($profile_filename);
      }
      if (count($profiles_filename) == 0) {
	 exit("No filename *LinkedIn.html found!<br/>");
      }
   }

   private function alter_file_get_contents($url) {

      $curl = curl_init();

      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($curl, CURLOPT_HEADER, false);
   
      $data = curl_exec($curl);
      curl_close($curl);

      return $data;
   }

   private function oo_alter_file_get_contents($url) {

      if( ini_get('safe_mode') ){
	 // Do it the safe mode way
	 print ("SAFE MODE!<br/>");
      }else{
	 // Do it the regular way
	 print ("UN-SAFE MODE!<br/>");
      }

      // phpinfo();
      $curl = curl_init();

      // $url = "https://www.linkedin.com/secure/login";
      // $POSTFIELDS = 'session_key=balasun08@gmail.com&session_password=balasundar&session_login=&session_rikey=invalid key';
      // $reffer = "https://www.linkedin.com";
      $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
      // $cookie_file_path = "tmp/cookie.txt"; 


      curl_setopt($curl, CURLOPT_URL, $url);
      // curl_setopt($curl, CURLOPT_USERAGENT, $agent);
      // curl_setopt($curl, CURLOPT_POST, 1);
      // curl_setopt($curl, CURLOPT_POSTFIELDS,$POSTFIELDS);

      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
      // curl_setopt($curl, CURLOPT_REFERER, $reffer);
      // curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie_file_path);
      // curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie_file_path);
      curl_setopt($curl, CURLOPT_HEADER, true);
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

      // curl_setopt($curl, CURLOPT_URL, $url);
      // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      // curl_setopt($curl, CURLOPT_HEADER, false);
   
      $data = curl_exec($curl);
      curl_close($curl);

      return $data;
   }

   public function download_xml_from_internet($public_profile_url, $xmlfile) {
      print ("================ downloading $public_profile_url =============<br/>");
      $this->public_profile_url = $public_profile_url;
      try {
	 $html = $this->alter_file_get_contents($public_profile_url);
      } catch (Exception $e) {
	 exit ('Caught exception: '.  $e->getMessage(). "<br/>");
      }

      if (strlen($html) <= 0) {
	 exit ("Error reading url $public_profile_url<br/>");
      }

      $number_of_bytes_written = file_put_contents($xmlfile, $html);
      if ($number_of_bytes_written == FALSE) {
	 exit ("Error writing linkedin file $xmlfile!<br/>");
      } else {
	 print ("Wrote $number_of_bytes_written bytes in $xmlfile<br/>");
      }
   }

   // #hresume/#vcalendar/#vcard
   public function get_all_hresumes() {
      $hresumes = array();

      foreach ($this->doms as $dom) {
	 $xpath = new DOMXpath($dom);
	 // $language_str = $this->get_language_from_profile_dom($xpath);

	 foreach ($xpath->query("//*[contains(@class,'hresume')]") as $hresume) {
	    array_push($hresumes, $hresume);
	 }
      }
      if (count($hresumes)<=0) {
	 exit ("Non sono stati trovati hresume!");
      }
      return $hresumes;
   }

   public function get_all_vcalendars($hresume) {
      $xpath = new DOMXpath($hresume->ownerDocument);

      $tmp_vcalendars = $xpath->query(".//*[contains(@class,'vcalendar')]", $hresume);
      $vcalendars = array();
      if ($tmp_vcalendars->length<=0) {
         exit ("Non non stati trovati vcalendars!<br/>");
      }
      foreach ($tmp_vcalendars as $vcal) {
         if ($this->is_valid_vcalendar($vcal))
            $vcalendars[]=$vcal;
      }
      if (count($vcalendars)<=0) {
         exit ("Non non stati trovati vcalendars validi!<br/>");
      }
      return $vcalendars;
   }

   public function get_all_vcards($vcalendar) {
      $xpath = new DOMXpath($vcalendar->ownerDocument);

      $vcards = $xpath->query(".//*[contains(@class, 'vcard')]", $vcalendar);
      if ($vcards->length <=0) {
         exit ("Non sono stati trovate vcards!");
      }
      return $vcards;
   }

   // ../div#id="experiences"
   public function is_valid_vcalendar($vcalendar) {
      return $vcalendar->parentNode->getAttribute('id')=='profile-experience';
   }

   public function read_data(DOMNode $vcard, Experience &$experience) {
      // string $href; 	// SOLO WORDPRESS
      $xpath = new DOMXpath($vcard->ownerDocument);

      // "#dtstamp/dtend" => 	DATA FINE / Presente (mese anno)
      $table_experience=array(
	 "title" 	=> &$experience->title, 	// ROLE
	 "org"   	=> &$experience->orgName,     	// COMPANY NAME
	 "orgstats" 	=> &$experience->orgstats, 	// COMPANY DETAILS (solo linkedin!)
	 "dtstart" 	=> &$experience->dtstart,	// DATA INIZIO (mese anno)
	 "dtend,dtstamp"=> &$experience->dtend,		// DATA FINE / Presente (mese anno)
	 "location" 	=> &$experience->location,	// CITTA (PROVINCIA)
	 "description"  => &$experience->details	// DESCRIZIONE ATTIVITA'
      );

      foreach ($table_experience as $keys => &$ref) {
	 // print ("NNN:".$vcard->nodeName."\n");
	 foreach (split(",", $keys) as $key) {
	    $node = $xpath->query(".//*[contains(concat(' ',@class,' '), ' $key ')]", $vcard);
	    if ($node->length>0) break;
	 }
	 if ($node->length<=0) {
	    exit ("vcard non contiene div# $key!");
	 }

	 // print ("NNN:".$node->item(0)->nodeName."\n");
	 $ref = trim($node->item(0)->textContent);
      }
   }

   public function get_info_for_hresume($hresume) {
      $xpath = new DOMXpath($hresume->ownerDocument);

      $vcard = $xpath->query(".//*[contains(concat(' ',@class,' '), ' contact ')]", $hresume);
      if ($vcard->length<=0) {
         exit ("hresume non contiene <contact>!<br/>");
      }
      $fullname = $xpath->query('.//*[@class="full-name"]', $vcard->item(0));
      if ($fullname->length<=0) { 
         exit ("vcard non contiene <full-name>!<br/>");
      }
      // return $fullname->item(0)->textContent.'-'.$this->get_language_from_profile_dom($xpath);
      // If this value is the same for multiple resume => it will be merged to a different language
      return $fullname->item(0)->textContent;
   }

   public function get_info_for_vcalendar($vcalendar) {
      return $vcalendar->parentNode->getAttribute('id');
   }

   public function get_info_for_experience($hresume) {
      static $languages_code=array(
	 'Italiano' => 'it',
	 'Inglese'  => 'en'
      );
      $xpath = new DOMXpath($hresume->ownerDocument);

      $node = $xpath->query("//*[@id='current-locale']");
      $lang = $node->item(0)->textContent;
      $lang = $languages_code($lang);
      return $lang;
   }
};

if (__FILE__ == realpath($argv[0])) {

   class TestHResumeWriter extends HResumeWriter {
      public function added_hresume($hresume) {}
      public function added_vcalendar($hresume, $vcalendar) {}
      public function added_experience($hresume, $vcalendar, Experience &$experience) {}

      public function upload() {}
   };

   $hresume = new DOM_LINKEDIN(); // load profiles
   $writer = new TestHResumeWriter();

   $hresume->parse_hresume($writer);

   print ($writer);
}

?>

