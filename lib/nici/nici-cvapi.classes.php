<?php
########################################
# NICI Canvas API Classes
# Author: Travis Souza (tsouza@tmcc.edu)
#
# Revision Date:9/12/13
########################################
# Changes:
# separated api classes from nici-core  
#
#
#
########################################


//Base class for CURL methods

class niciCURL {
	private $domain, $uri;
	
	
	public function niciCURL() {
		
	}

/*	
	public function curlGET($url,$token) {
		#printf("<p>Executing curl GET request to:%s</p>", $url);
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $url);	
		$api_json=curl_exec($ch);
		
		#printf("FUNCTION CURL RESPONSE:<p>%s</p>",$api_json);
		curl_close($ch);
		$response=json_decode($api_json,true);
		#$response['raw response']=$api_json;
		return $response;
	}
*/

	public function curlGET($url,$token, $return_header) {
		#printf("<p>Executing curl GET request to:%s</p>", $url);
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_HEADER, $return_header);
  		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $url);	
		$api_json=curl_exec($ch);
		
		if ( $return_header==1 ) {
		  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		  $header = substr($api_json, 0, $header_size);
		  $body = substr($api_json, $header_size);
		}
		else {
		  $body = $api_json;
		  $header = "";
		  #echo $body;	
		  
		}
		
		#printf("RESPONSE HEADER:<p>%s</p>",$header);
		curl_close($ch);
		$response=json_decode($body,true);
		$response['header']=$header;
		return $response;
	}
	
	public function curlPOST($url,$token,$postfields,$attach) {
		#printf("<p>Executing curl POST request to:%s</p>", $url);
		print_r($postfields);
		
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $url);	
		curl_setopt($ch, CURLOPT_POST, true);
		
		if (!empty($attach)) {
			//insert file attachment code
			if (!empty($postfields)) {
				$postfields['attachment']="@".$attach; // same as <input type="file" name="canvas_csv">	
			}
			elseif (empty($postfields)) {
				$postfields=array("attachment"=>"@".$attach); // same as <input type="file" name="canvas_csv">
			}
		}
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		$api_json=curl_exec($ch);
		printf("FUNCTION CURL RESPONSE:<p>%s</p>",$api_json);
		curl_close($ch);
		$response=json_decode($api_json,true);
		$response['raw response']=$api_json;
		return $response;
	}
	
	public function curlDELETE() {
		#printf("<p>Executing curl DELETE request to:%s</p>", $url);
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
  		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		$response=curl_exec($ch);;
		//printf("FUNCTION CURL RESPONSE:<p>%s</p>",$response);
		curl_close($ch);
		return $response;
	}
	
	function curlExecute($url,$token,$method,$attach, $postfields) {
		$ch = curl_init();	
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($method=="post") {
			curl_setopt($ch, CURLOPT_POST, true);
			if (!empty($attach)) {
				//insert file attachment code
				if (!empty($postfields)) {
					$postfields['attachment']="@".$attach; // same as <input type="file" name="canvas_csv">
				//	
				}
				elseif (empty($attach)) {
					$postfields=array("attachment"=>"@".$attach); // same as <input type="file" name="canvas_csv">
				}
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		}
		$response=curl_exec($ch);
		echo $response;
		curl_close($ch);
		return $response;
	
	}

	
}

// extended base class to include  URL method.  Parent for all API endpoint classes

class niciCanvasApi extends niciCURL {
	var $endpoint,$params,$response,$decode,$url,$ch,$domain,$uri,$token;
	private $acct_id; 
	
	public function niciCanvasApi() {
		parent::__construct();
	}
	
	public function makeURL($domain,$uri,$url_params) {
			$url=sprintf("%s%s",$domain,$uri);
			if (!empty($url_params)) {
				$url=$url."?";
				$i=0;
				foreach ($url_params as $k=>$v) {
					if ($i==0) {
						$this->url=$this->url.$k."=".$v;
					}
					else {
						$this->url=$this->url."&".$k."=".$v;
					}
				}
			}
			return $url;
	}
	
	
}


// Canvas API endpoint: Account Reports 

class apiCanvasRpt extends niciCanvasApi {
	var $api_rpt_id,$rpt_name,$rpt_file_url;
	
	public function apiCanvasRpt($domain,$token) {
		parent::__construct();
		$this->endpoint="reports";
		$this->uri="/api/v1/accounts/self/reports";
		$this->domain=$domain;
		$this->token=$token;	
		$this->rpt_name="s";
		$this->apt_rpt_id="";
		$this->rpt_file_url="";
		$this->url=$this->makeURL($this->domain,$this->uri,NULL);
		//Create db entry on new report run
	}
	
	public function apiRptListAvail() {  // show reports available at account level
		
		//echo $this->domain.$this->uri;
		printf("<h3>Constructed URL</h3><P>%s</p>",$this->url);
		$rpt_list=$this->curlGET($this->url,$this->token,0);
		return $rpt_list;
	}
	
	public function apiRptIndex($rpt_name) { // show list of individua report instances 
		$this->rpt_name=$rpt_name;
		$url=sprintf("%s/%s",$this->url,$this->rpt_name);
		printf("<h3>Constructed URL</h3><P>%s</p>",$url);
		$rpt_index=$this->curlGET($url,$this->token,1);
		return $rpt_index;
	}
	
	public function apiRptStatus($rpt_name,$rpt_id) { // 
		$this->rpt_name=$rpt_name;
		$this->api_rpt_id=$rpt_id;
		$url=sprintf("%s/%s/%s",$this->url,$rpt_name,$rpt_id);
		printf("<h3>Constructed URL</h3><P>%s</p>",$url);
		$rpt_status=$this->curlGET($url,$this->token,0);
		$this->rpt_file_url=$rpt_status['file_url'];
		printf("<h3>File URL without auth token</h3><P><a href='%s'>%s</a></p>",$this->rpt_file_url,$this->rpt_file_url);
		return $rpt_status;
	}
	
	public function newReport($rpt_name,$parameters) {
		$this->rpt_name=$rpt_name;
		$url=sprintf("%s/%s",$this->url,$this->rpt_name);
		$rpt_request=$this->curlPOST($url,$this->token,$parameters,null);
		printf("<h3>Report Submitted!</h3><P>id: %s  |  params: %s  </p>",$rpt_request['id'],$rpt_request['parameters']);
		return $rpt_request;
	}
	
	
	
}


// Canvas API endpoint: Files 
class apiCanvasFile extends niciCanvasApi {
	var $api_file_id,$api_file_url,$api_file_auth_url,$api_file_size,$api_file_created,$api_file_name;
	
	public function apiCanvasFile($domain,$token) {
		parent::__construct();
		$this->api_file_id="";
		$this->api_file_url="";
		$this->api_file_auth_url="";
		$this->api_file_size="";
		$this->api_file_created="";
		$this->api_file_name="";
		$this->api_file_type=="";
		$this->token=$token;
		$this->domain=$domain;
		$this->uri="/api/v1/files";
		$this->url=$this->makeURL($this->domain,$this->uri,NULL);
		//Create db entry on new report run
	}
	
	public function apiRptGetFileID($api_file_url) {
		$this->api_file_url=$api_file_url;
		$this->api_file_id=preg_replace(array("^h.*s\/^","&\/download&"),"",$this->api_file_url);

		printf("<h3>file id</h3><P>%s</p>",$this->api_file_id);
		
	}
	
	public function apiGetFileObj($file_id) {
		if($this->api_file_id=="") {
			$this->api_file_id=$file_id;
		}
		$url=sprintf("%s/%s",$this->url,$this->api_file_id);
		#printf("<h3>Constructed URL</h3><P>%s</p>",$url);
		$file_obj=$this->curlGET($url,$token,0);
		print_r($file_obj);
		$this->api_file_id=$file_obj['id'];
		$this->api_file_auth_url=$file_obj['url'];
		$this->api_file_size=$file_obj['size'];
		$this->api_file_created=$file_obj['created_at'];
		$this->api_file_name=$file_obj['filename'];
		$this->api_file_type==$file_obj['content-type'];
		$this->apt_rpt_id="";
		#printf("<h3>File size: %s bytes</h3><h3>Created at: %s</h3>",$this->api_file_size,$this->api_file_created);
		#printf("<h3>File URL including auth token</h3><P><a href='%s'>%s</a></p>",$this->api_file_auth_url,$this->api_file_auth_url);
	}

	
}

// Canvas API endpoint: Course
class apiCanvasCourse  extends niciCanvasApi {
	var $id, $sis_couse_id, $sections ;
	
	public function apiCanvasCourse() {
		parent::__construct();
		$this->uri=sprintf("/api/v1/courses");
		$this->domain="";
		$this->course_code="";
		$this->sis_couse_id="";
		$this->account_id="";
		$this->name="";
		$this->id="";
		$this->start_at="";
		$this->end_at="";
		$this->publc_syllabus="";
		$this->calendar=array();;
		$this->enrollments=array();
		$this->sections=array();
		$this->hide_final_grades="";
	}
	
	public function getCourseInfo($domain,$token,$crs_id) {
		$this->uri=sprintf("/api/v1/courses/%s",$crs_id);
		$this->url=$this->makeURL($domain,$this->uri,null);
		$this->id=$crs_id;
		//printf("<h3>Constructed URL</h3><P>%s</p>",$this->url);
		//printf("<h3>Token</h3><P>%s</p>",$token);
		
		$course=$this->curlGET($this->url,$token,0);
		//$this->sections
		foreach ($course as $k=>$v) {
			$this->$k=$v;	
		}
		
		return $course;
	}
	
	public function getSections($domain,$token,$crs_id) {
		$this->uri=sprintf("/api/v1/courses/%s/sections",$crs_id);
		$this->url=$this->makeURL($domain,$this->uri,null);
		$this->id=$crs_id;
		//printf("<h3>Constructed URL</h3><P>%s</p>",$this->url);
		//printf("<h3>Token</h3><P>%s</p>",$token);
		
		$sections=$this->curlGET($this->url,$token,0);
		//print_r($sections);
		$this->sections=$sections;
		
		
		return $sections;
	}
	
	
}

// Canvas API endpoint: Account Section 
class apiCanvasSection  extends niciCanvasApi {
	var $sis_section_id, $roster, $course_id;
	public function apiCanvasSection() {
		parent::__construct();
		$this->uri=sprintf("/api/v1/courses");
		$this->domain="";
		$this->course_id="";
		$this->name="";
		$this->id="";
		$this->sis_section_id="";
		$this->start_at="";
		$this->end_at="";
		$this->nonxlist_course_id="";
		$this->enrollment=array();
	}
	
		public function getSectionInfo($domain,$token,$sec_id) {
		$this->uri=sprintf("/api/v1/sections/%s",$sec_id);
		$this->url=$this->makeURL($domain,$this->uri,null);
		$this->id=$sec_id;
		$section=$this->curlGET($this->url,$token,0);
		//print_r($section);
		foreach ($section as $k=>$v) {
			if (!empty($v['sis_section_id'])) {
			$this->$k=$v;	
			return $section;
			}
		}
		
	}
	 
		public function getSectionRoster($domain,$token,$sec_id) {
			$page="?per_page=50";
			$page_num=1;
			$more_pages=TRUE;
			
			$this->uri=sprintf("/api/v1/sections/%s/enrollments%s",$sec_id,$page);
			$this->url=$this->makeURL($domain,$this->uri,null);
			$this->id=$sec_id;
			$roster=$this->curlGET($this->url,$token,1);
			#print_r($roster);
			
			while ( $more_pages ) {
			  if (preg_match('/.*?rel.*?(rel)(=)("next")/', $roster['header'])) {
				  #echo "Hurray! more pages";
				  $page_num ++;
				  $page=sprintf("?page=%s&per_page=50",$page_num);
				  
				  $this->uri=sprintf("/api/v1/sections/%s/enrollments%s",$sec_id,$page);
				  $this->url=$this->makeURL($domain,$this->uri,null);
				  #echo $this->url;
				  $roster2=$this->curlGET($this->url,$token,1);
				  #printf("<p>Full Response2:");
				  unset($roster['header']);
				  $roster=array_merge($roster,$roster2)	;
				  #print_r($roster2);
			  }
			  else {
			  	  $more_pages = FALSE;
				  #printf("<p>COMPLETE:");
			  }
			}

			
			return $roster;
			
		}
		
}

// Canvas API endpoint: User 
class apiCanvasUser extends niciCanvasApi {
		var $id, $sortable_name,$sis_user_id,$sis_login_id,$login_id,$avatar_url,$title,$bio,$primary_email;
		
		public function apiCanvasUser() {
			parent::__construct();
			$this->uri="/api/v1/users";
			$this->id="";
			$this->sortable_name="";
			$this->sis_user_id="";
			$this->sis_login_id="";
			$this->login_id="";
			$this->avatar_url="";
			$this->title="";
			$this->bio="";
			$this->primary_email="";
			
	   }			
			
		public function getUserProfile($domain,$token,$user_id) {
			$this->uri=sprintf("%s/%s/profile",$this->uri,$user_id);

			$this->url=$this->makeURL($domain,$this->uri,null);

			$this->id=$user_id;
			$profile=$this->curlGET($this->url,$token,0);
			//print_r($profile);
			foreach ($profile as $k=>$v) {
				$this->$k=$v;
				//printf("<h5>%s->%s</h5>",$k,$v);
			}
			return $profile;
			
			
		}
		

		
		public function createUser($domain,$token,$user_parms) {
			$this->uri=sprintf("/api/v1/accounts/self/users");
			$this->url=$this->makeURL($domain,$this->uri,null);
			$response=$this->curlPOST($this->url,$token,$user_parms,null);
			return $response;
			
		}
		
}

// Canvas API endpoint: Analytics
class apiCanvasAnalytics extends niciCanvasApi {
	var $page_views, $participations,$url,$uri;
	
	public function apiCanvasAnalytics() {
		parent::__construct();
		$this->page_views=array();
		$this->participations=array();
		$this->url="";
		$this->uri="";
	}	
	
	public function getUserCourseDetails($domain,$token,$cv_course_id,$cv_user_id) {
		$this->uri=sprintf("/api/v1/courses/%s/analytics/users/%s/activity",$cv_course_id,$cv_user_id);

		$this->url=$this->makeURL($domain,$this->uri,null);

		$analytics=$this->curlGET($this->url,$token,0);
		//print_r($analytics);
		#foreach ($analytics as $k=>$v) {
			#$this->$k=$v;
			//printf("<h5>%s->%s</h5>",$k,$v);
			#print_r($k);
		#}
		return $analytics;
		
	}
	
	public function getSessionViews($raw_views) {
		$i=1;
		$session_views=array();
		ksort($raw_views);
		foreach($raw_views as $k=>$v) {
		  $timestamp=strtotime($k);
		  $date=strftime("%m-%d-%Y",$timestamp);
		  $hour=strftime("%H",$timestamp);
		  $dow=strftime("%a",$timestamp);
		  #printf("Session:%s |Date:%s - Hour:%s - DOW:%s - Views:%s<br>",$i,$date,$hour,$dow,$v);
		  $session_views[$i]=array("date"=>$date,"hour"=>$hour,"dow"=>$dow,"views"=>$v,"submissions"=>"");
		  $i++;
		}
		return $session_views;
	}
	
	public function getSubmissions($participations) {
		$j=1;
		$submissions=array();
		ksort($participations);
		foreach($participations as $k=>$v) {
		 #printf("<h5>%s->%s</h5>",$k,$v);
		 #print_r($v);
		 $timestamp=strtotime($v['created_at']);
		 $date=strftime("%m-%d-%Y",$timestamp);
		 $hour=strftime("%H",$timestamp);
		 $dow=strftime("%a",$timestamp);
		 $type=$v['asset_category'];
		 $asset_id=$v['asset_code'];
		 if ($type=="topics") {
			 $temp=explode("_",$asset_id);
			 $asset_id=$temp[2];
		 }
		 else {
			 $temp=explode("_",$asset_id);
			 $asset_id=$temp[1];
		 }
		 #printf("Submission:%s |Date:%s - Hour:%s - DOW:%s - type:%s - id:%s <br>",$j,$date,$hour,$dow,$v['asset_category'],$asset_id);
		 $submissions[$j]=array("date"=>$date,"hour"=>$hour,"dow"=>$dow,"type"=>$type,"asset_id"=>$asset_id);
		 $j++;
		}
		return $submissions;
	}
	
}