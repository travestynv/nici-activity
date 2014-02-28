<?php
/*Config for NICI Activity report
--NSHE Canvas Integration v.2.0.201211
--author: Travis Souza (tsouza@tmcc.edu)
--Last Modified: 2013-Oct-02
*/
	define("CFG_USE_DB", false);  // ignore DB functions and assign values listed below

  /* used in complete NICI framework
    $mydb_host="";
	$mydb_user="";
	$mydb_pass="";
	$mydb_db="";
	
	$oraservice="";  //used for Peoplesoft integration
  */
	
	// variables and constants
	$cv_api_token=""; // api token with admin rights
	$cv_prd_url=""; //Your Canvas production URL
	$cv_account_id="";  //Your Canvas account id

	$ltiapp_key="";	//LTI application key
	$ltiapp_secret=""; //LTI shared secret
	$ltiapp_inst="TMCC"; // institution identifier used as index, also used for mySQL lookups if DB is used
		
	//development values
	#$cv_course_id="";  // this will come in the LTI POST array
	
	
	


?>