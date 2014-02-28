<?php

class myDB {
	public $db,$conn;
	protected $host, $user, $pass, $query;
	function myDB() {
		include '/var/www/includes/config.php'; ////Replace in production
		#require_once '../lib/config.php';
		$this->host=$mydb_host;
		$this->user=$mydb_user;
		$this->pass=$mydb_pass;
		$this->conn;
		
	}
	
	function connect($d) {
		$this->conn = mysql_connect($this->host, $this->user, $this->pass) or trigger_error(mysql_error(),E_USER_ERROR);
		$this->db=mysql_select_db($d,$this->conn);
		//return $this->db;
		return $this->conn;
	}
	
	function query($q) {
		$result=mysql_query($q);
	
	// Check result
	// This shows the actual query sent to MySQL, and the error. Useful for debugging.
	  if (!$result) {
		  $message  = 'Invalid query: ' . mysql_error() . "\n";
		  $message .= 'Whole query: ' . $q;
		  die($message);
	  }
	  
	  //mysql_close($this->conn);
	  return $result;
	}
	
	
}

class niciDB extends myDB {
	var $tbl, $query, $results, $columns, $row_count, $inserts, $action;
	
	public function nicDB() {
		parent::__construct();
		$this->tbl="";
		$this->query="";
		$this->results="";
		$this->columns="";
		$this->row_count="";
		$this->inserts="";
		$this->action="";
	
		
	}
		
	public function niciStdtSectActRpt($tbl,$cv_user_id,$cv_section_id,$start_at,$end_at) {
		//$tbl=sprintf("_%s_Participation",$inst);
		$this->tbl=$tbl;
		### Simple activity summary, uses the totals from the most recent record of a particular content_access_id (unique user<->content id
		$qSQL=sprintf("SELECT a.content_access_id as content_id,a.section_id, a.section_sis_id,a.course_name,
					   a.course_id,a.course_sis_id,a.term_sis_id,a.user_id,a.user_sis_id,a.content_type,
        			   a.content,a.times_viewed,a.times_participated,max(b.last_viewed) as last_access,min(b.last_viewed) as first_access,
					   b.content_access_id, count(b.content_access_id)as viewcount
				FROM %s a
				JOIN %s b
				ON 	a.last_viewed=b.last_viewed AND a.content_access_id=b.content_access_id
				WHERE	a.user_id =  '%s' AND a.section_id = '%s'
				GROUP BY b.content_access_id 
				HAVING MAX(b.last_viewed)
				ORDER BY viewcount DESC",
				$tbl,$tbl,$cv_user_id,$cv_section_id);	
		//printf("<h4>%s</h4>",$qSQL);
		
		$result=$this->query($qSQL);
		$activity_summary=mysql_fetch_assoc($this->query($qSQL));
		$times_viewed=0;
		$times_participate=0;
		$last_view="";
		$first_view="";
		$count=0;
		while ($row = mysql_fetch_assoc($result)) {
				  $times_viewed=$times_viewed+$row['times_viewed'];
				  $times_participate=$times_participate+$row['times_participated'];
				  if (($last_view=="")||($row['last_access']>$last_view)) {
						$last_view=$row['last_access']; 
				  }
				   if (($first_view=="")||($row['first_access']<$first_view)) {
						$first_view=$row['first_access']; 
				  }
				  
		  }
		## printf("last: %s, first: %s, viewed: %s, participated: %s", $last_view,$first_view,$times_viewed,$times_participate);
		## Return simple summary
		$summary=array("last_view"=>$last_view,"first_view"=>$first_view,"total_views"=>$times_viewed,"total_part"=>$times_participate);
		return $summary;
	
	}
		
		
}