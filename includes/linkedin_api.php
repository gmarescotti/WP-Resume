<?php

function linkedin_api_test() {
   print("ciao<br/>");
   // Fill the keys and secrets you retrieved after registering your app
   try {
      print ("JKJK");
   $oauth = new OAuth("77zlffapyyv3l9", "Pcvux4niVvWkfxV5");
   } catch(Exception $e) {
      print ("Messaggio: $e->getMessage()");
   }
   $oauth->setToken("59ee5d2d-bf43-498e-a737-6709df900f3a", "c0e0112a-c230-4948-b057-79264545f675");
print("AA<br/>");
   $params = array();
   $headers = array();
print("AA<br/>");
   $method = OAUTH_HTTP_METHOD_GET;
print("AA<br/>");
   return;

   // Specify LinkedIn API endpoint to retrieve your own profile
   $url = "http://api.linkedin.com/v1/people/~";

print("AA<br/>");
   // By default, the LinkedIn API responses are in XML format. If you prefer JSON, simply specify the format in your call
   // $url = "http://api.linkedin.com/v1/people/~?format=json";

   // Make call to LinkedIn to retrieve your own profile
   $oauth->fetch($url, $params, $method, $headers);

print("AA<br/>");
   $out = $oauth->getLastResponse();
print("AA<br/>");
   var_dump($out);
print("AA<br/>");
}

?>
