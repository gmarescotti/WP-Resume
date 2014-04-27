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

   abstract protected function add_hresume($hresume);
   abstract protected function add_vcalendar($hresume, $vcalendar);
   abstract protected function add_experience($hresume, $vcalendar, $experience);
};

abstract class HResumeReader {
   public $xmlfile_base;

   public $dom;
   public $xpath;

   public function __construct() {
      $this->xmlfile = dirname( __FILE__ ).'/linkedin.xml';
   }

   // public function __construct($xmlfile) {
      // $this->xmlfile = $xmlfile;
      // import_xml_file($xmlfile);
   // }

   public function import_xml_file() {
      $ext = pathinfo($this->xmlfile);
      if (!array_key_exists('extension', $ext) || $ext['extension']!='xml') {
         print ("Solo file xml prego: $this->xmlfile\n");
      }
      if (!file_exists($this->xmlfile))  {
         print ("File does not exists: $this->xmlfile\n");
      }
      
      $this->dom = new DOMDocument();

      $save_rep = error_reporting();
      error_reporting(E_ERROR);
      // $this->dom->import_xml_file($this->xmlfile);
      $this->dom->loadHTML(file_get_contents($this->xmlfile));
      $this->dom->loadXML($this->dom->saveXML());
      error_reporting($save_rep);

      $this->xpath = new DOMXpath($this->dom);
   }

   abstract protected function get_all_hresumes();
   abstract protected function get_all_vcalendars($hresume);
   abstract protected function get_all_vcards($vcalendar);

   abstract protected function get_info_for_hresume($hresume);
   abstract protected function get_info_for_vcalendar($vcalendar);
   // abstract protected function get_info_for_vcard($vcalendar);

   abstract protected function read_data(DOMNode $vcard, Experience &$experience);
   // abstract protected function write_data(DOMNode &$vcalendar, Experience $experience);

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
	    // if (!$this->is_valid_vcalendar($vcalendar)) continue;
	    foreach ($this->get_all_vcards($vcalendar) as $vcard) {
	       // print ($vcard->getNodePath()."\n");
	       $experience = new Experience;
	       $this->read_data($vcard, $experience);
	       // $dom_dst->write_data($vcalendar, $experience);
	       $hrs->add_experience($hresume_name, $vcalendar_name, $experience);

	       // print $experience."<br/>";
	    }
	 }
      }
   }

};

class DOM_LINKEDIN extends HResumeReader {

   public $public_profile_url;

   public function download_xml_from_internet($public_profile_url) {
      $this->public_profile_url = $public_profile_url;
      $html = file_get_contents($public_profile_url);
      file_put_contents($this->xmlfile, $html);
   }

   // #hresume/#vcalendar/#vcard
   public function get_all_hresumes() {
      $hresumes=$this->xpath->query("//*[contains(@class,'hresume')]");
      if ($hresumes->length<=0) {
         print ("Non sono stati trovati hresume!");
      }
      return $hresumes;
   }

   public function get_all_vcalendars($hresume) {
      $tmp_vcalendars = $this->xpath->query(".//*[contains(@class,'vcalendar')]", $hresume);
      $vcalendars = array();
      if ($tmp_vcalendars->length<=0) {
         print ("Non non stati trovati vcalendars!");
      }
      foreach ($tmp_vcalendars as $vcal) {
         if ($this->is_valid_vcalendar($vcal))
            $vcalendars[]=$vcal;
      }
      if (count($vcalendars)<=0) {
         print ("Non non stati trovati vcalendars validi!");
      }
      return $vcalendars;
   }

   public function get_all_vcards($vcalendar) {
      $vcards = $this->xpath->query(".//*[contains(@class, 'vcard')]", $vcalendar);
      if ($vcards->length <=0) {
         print ("Non sono stati trovate vcards!");
      }
      return $vcards;
   }

   // ../div#id="experiences"
   public function is_valid_vcalendar($vcalendar) {
      return $vcalendar->parentNode->getAttribute('id')=='profile-experience';
   }

   public function read_data(DOMNode $vcard, Experience &$experience) {
      // string $href; 	// SOLO WORDPRESS

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
	    $node = $this->xpath->query(".//*[contains(concat(' ',@class,' '), ' $key ')]", $vcard);
	    if ($node->length>0) break;
	 }
	 if ($node->length<=0) {
        print ("vcard non contiene div# $key!");
     }

	 // print ("NNN:".$node->item(0)->nodeName."\n");
	 $ref = trim($node->item(0)->textContent);
      }
   }

   public function get_info_for_hresume($hresume) {
      $vcard = $this->xpath->query(".//*[contains(concat(' ',@class,' '), ' contact ')]", $hresume);
      if ($vcard->length<=0) {
         print ("hresume non contiene <contact>!");
      }
      $fullname = $this->xpath->query('.//*[@class="full-name"]', $vcard->item(0));
      if ($fullname->length<=0) { 
         print ("vcard non contiene <full-name>!");
      }
      return $fullname->item(0)->textContent;
   }

   public function get_info_for_vcalendar($vcalendar) {
      return $vcalendar->parentNode->getAttribute('id');
   }
};

if (__FILE__ == realpath($argv[0])) {

   class TestHResumeWriter extends HResumeWriter {
      // public $experiences = array();
      // public $vcalendars = array();
      
      function add_hresume($hresume_name) {
         // unset ($this->experiences); $this->experiences = array();
         // unset ($this->vcalendar); $this->vcalendar = array();
         print ("HRESUME ".$hresume_name."\n");
      }
      function add_vcalendar($hresume_name, $vcalendar_name) {
	 // array_push($this->vcalendars, $vcalendar);
         print ("VCALENDAR ".$vcalendar_name."\n");
      }
      function add_experience($hresume_name, $vcalendar_name, $experience) {
	 // array_push($this->experiences, $experience);
         print ("EXPERIENCE ".$experience."\n");
      }

   };

   $hresume = new DOM_LINKEDIN();
   $writer = new TestHResumeWriter();

   // $hresume->download_xml_from_internet('https://www.linkedin.com/pub/giulio-marescotti/5/235/380');
   $hresume->import_xml_file();
   $hresume->parse_hresume($writer);
}

?>

