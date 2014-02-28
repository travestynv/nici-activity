<?php 

class niciLTI {
	var $cv_account_id,$cv_api_token,$cv_prd_url, $ltiapp_key, $ltiapp_secret ;
	
	function niciLTI($app_key) {
		
		if (!CFG_USE_DB) {

			$this->ltiapp_secret = LTIAPP_SECRET;
			$this->ltiapp_key = LTIAPP_KEY;
			$this->ltiapp_inst= LTIAPP_INST;
			
		}
		else {
		  //echo $app_key;
		  $db=new myDB();
		  $conn=$db->connect('ps2cv');
		  
		  $query=sprintf("SELECT ltiapp_id,ltiapp_name,ltiapp_key,ltiapp_secret,ltiapp_inst,ltiapp_launch_url,ltiapp_last_nonce from PS2CV_LTI where ltiapp_key='%s'",$app_key);
		  //echo $query;
		  $result=$db->query($query);
	
		  while ($row = mysql_fetch_assoc($result)) {
				  foreach ($row as $k => $v) {
					  $this->$k=$v; 
					  //printf("<br>%s->%s<br>",$k,$v); //remove for production
				  }
		  }
		  if ($this->ltiapp_last_nonce==$_REQUEST['oauth_nonce']) {
			  printf("<h1>BAD NONCE</h1>");
			  die;
		  }
		  else	{
			  //printf("Storing nonce!: %s",$_REQUEST['oauth_nonce']);  
			  $db2=new myDB();
		  	  $conn2=$db->connect('ps2cv');
			  $query=sprintf("update PS2CV_LTI set ltiapp_last_nonce='%s' where ltiapp_key='%s'",$_REQUEST['oauth_nonce'],$app_key);
			  $update=$db->query($query);
		  }
		  
		}	
	
	}
		
}

class institution {
	var $inst_id,$institution,$inst_abbv,$cv_account_id,$cv_api_token,$cv_prd_url,$cv_tst_url,$admin_email,$app_home,$file_location;
	var $activity_rpt_tbl,$rpt_log_tbl,$sis_log_tbl,$error_log_tbl,$default_mode,$default_term;
	private $orauser,$orapass,$class_vw,$enrl_vw;
	
	public function institution($inst) {
		
		if (!CFG_USE_DB) {
			
			$this->cv_account_id = LTIAPP_ACCT_ID;
			$this->cv_api_token = LTIAPP_TOKEN;
			$this->cv_prd_url = LTIAPP_URL;
			$this->ltiapp_inst= LTIAPP_INST;

		}
		else {
		  $db=new myDB();
		  $conn=$db->connect('ps2cv');
		  #$inst=$app->ltiapp_inst;
		  $query=sprintf("Select * from PS2CV_INST where institution='%s';",
			   mysql_real_escape_string($inst));
		   
		  $result=mysql_query($query);
		 
		  if (!$result) {
			  $message  = 'Invalid query: ' . mysql_error() . "\n";
			  $message .= 'Whole query: ' . $query;
			  die($message);
		  }
		  
			  while ($row = mysql_fetch_assoc($result)) {
				  foreach ($row as $k => $v) {
					  $this->$k=$v; 
					  //printf("<br>%s->%s<br>",$k,$v); //remove for production
			  }
  
		  }
		}
		
	}
	
	function editConfig() {
		
	}
	
		
	
}

class task extends institution {
	
	var $inst,$type,$sis_term_id;
	

	public function task($app) {
		parent::__construct($app);
   		if ($_SERVER['PHP_SELF']=="/cjob.php"){
			$params=array();
			$params=$_REQUEST;
			#printf("USING $_REQUEST");
		}
		else {
			$params=array();
			$params=$_POST;
			#printf("USING $_post");
		}
   		foreach ($params as $k => $v) {
	  	$this->$k=$v; 
	  	//printf("<br>%s->%s<br>",$k,$this->$k); //remove for production
		}
	}
	
}

class niciReport extends task {
	
	public function niciReport($app) {
		parent::__construct($app);
	}
	
	
}