<?php

	// Get Location IDs and set EFA Baseurl
  $dep = htmlspecialchars($_GET['dep']);
  $arr = htmlspecialchars($_GET['arr']);
	$baseurl = 'http://bsvg.efa.de/bsvagstd/XML_TRIP_REQUEST2?language=de&type_origin=stopID&name_origin='.$dep.'&type_destination=stopID&name_destination='.$arr.'&sessionID=0';
	/*****************************************/

	// Run the helper function with the desired URL and echo the contents.
  //start XML Read to get sessionID for mor than 4 EFA Results (each call of the URL2 will respond 3 more Results)
  $file = get_url($baseurl);
  $xml = simplexml_load_string($file);
  $result = $xml->xpath("//itdRequest");
  //save sessionID
  $sid = $result[0]["sessionID"];
  //define URL for additional calls
  $url2 ='http://bsvg.efa.de/bsvagstd/XML_TRIP_REQUEST2?language=de&command=tripNext&type_origin=stopID&name_origin='.$dep.'&type_destination=stopID&name_destination='.$arr.'&sessionID='.$sid.'&requestID=1';
  $dump = get_url($url2);

  //print the final XML data
   print_r(utf8_encode(get_url($url2)));

	// Define the helper function that retrieved the data and decodes the content.
	function get_url($url)
	{
	    //user agent is very necessary, otherwise some websites like google.com wont give zipped content
	    $opts = array(
	        'http'=>array(
	            'method'=>"GET",
	            'header'=>"Accept-Language: en-US,en;q=0.8rn" .
	                        "Accept-Encoding: gzip,deflate,sdchrn" .
	                        "Accept-Charset:UTF-8,*;q=0.5rn" .
	                        "User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 FirePHP/0.4rn",
				"ignore_errors" => true	 //Fix problems getting data
	        ),
	        //Fixes problems in ssl
		"ssl" => array(
			"verify_peer"=>false,
			"verify_peer_name"=>false
		)
	    );

	    $context = stream_context_create($opts);
	    $content = file_get_contents($url ,false,$context);

	    //If http response header mentions that content is gzipped, then uncompress it
	    foreach($http_response_header as $c => $h)
	    {
	        if(stristr($h, 'content-encoding') and stristr($h, 'gzip'))
	        {
	            //Now lets uncompress the compressed data
	            $content = gzinflate( substr($content,10,-8) );
	        }
	    }

	    return $content;
	}
?>
