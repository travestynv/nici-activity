<?php
##########
# Canvas Course-Section Activity Report Rev 3.a.0
# Purpose: to produce an LTI tool summarize and drill-down activity 
#		   data for all students enrolled in sections 
#		   contained in the course.
#
# Requires custom nici classes, mySQL table for LTI information 
#
# Author: Travis Souza
# Design Date: 2013-09-11
# Revision Date: 2013-09-24
##########

// include files
	// LTI support (provides app authorization and configuration)
		require_once 'lib/ims-blti/blti.php';
	
	// NICI API classes (provides php classes for Canvas api objects)
		include 'lib/nici/nici-cvapi.classes.php';
		include 'lib/nici/nici-db.classes.php';
		include 'lib/nici/nici-core.classes.php';
			
// variables and constants
		//define global constants from config.php values for NO DB installs. ltiapp_key,ltiapp_secret,ltiapp_inst 
		include 'lib/config.php';
		define ("LTIAPP_SECRET", "$ltiapp_secret");  //
		define ("LTIAPP_KEY", "$ltiapp_key");  //<strong></strong>
		define ("LTIAPP_INST", "$ltiapp_inst");  //
		define ("LTIAPP_TOKEN", "$cv_api_token");  //
		define ("LTIAPP_URL", "$cv_prd_url");  //
		define ("LTIAPP_ACCT_ID", "$cv_account_id");  //

		
		//css include location
		$css_include="css/rpt_user_analytics.css";
		//sets css styling of last_activity to none
		$alertLogin_style="";
		$alertPart_style="";	
		
		$detail_div_id="'activity_detail'";
		
		$image_path="images/";

	
// local functions

  //checks for time since last login or submission and styles date string with color coding red, yellow, green, blue
	  function checkDateException($str_date) {
		  $today = new DateTime("now");
		  $last = new DateTime($str_date);
		  $interval = $today->diff($last);	
		  $days_since = $interval->Format("%a");
		  if($days_since < 4) {
			  // set css styling to codeBlue 
			  $style='class="codeBlue"';
		  }
		  elseif(($days_since > 3) && ($days_since < 8)) {
			  // set css styling to codeGreen 
			  $style='class="codeGreen"';
		  }
		  		  elseif(($days_since > 7) && ($days_since < 11)) {
			  // set css styling to codeYellow 
			  $style='class="codeYellow"';
		  }
		  elseif($days_since > 10) {
			  // set css styling to codeRed 
			  $style='class="codeRed"';
		  }
		  
		  return $style;
	  }

function buildPlotArray($views) {
	$activity=array();
	for ($chart_days = 0; $chart_days < 14; $chart_days++){
		$interval = 14 - $chart_days;
		$now = new DateTime("now");
		$date = $now->sub(new DateInterval("P".$interval."D"));
		$activity[$chart_days]=array($date->format("m/d/y"),0,0);	
		foreach ($views as $day=>$data) {
			if ($day == $date->format("m/d/y")) {
				$activity[$chart_days]=array($data[0],$data[1],$data[2]*5);
			}
		
	}
	}

	return $plot_array=array_values($activity);
}


//start session,  and check oauth
session_start();
header('Content-Type: text/html; charset=utf-8'); 

$app=new niciLTI($ltiapp_key);


###bLTI code TURN on for validation
// Initialize, all secrets are 'secret', do not set session, and do not redirect
$context = new BLTI($ltiapp_secret, false, false);
if ($context->valid) {
	//echo $_REQUEST['custom_canvas_course_id'];
	
// parse parse lti vars and create institution object
	$inst=new niciReport($app->ltiapp_inst);
	$cv_prd_url=$inst->cv_prd_url;
	$cv_api_token=$inst->cv_api_token;
	$cv_course_id=$inst->custom_canvas_course_id;
	
// main code block
	// start output buffering
	ob_start();

	// initialize app configuration 
		//option 1 - Basic LTI with OAUTH  : option 2 same steps just ignore oauth authorization and values
			// lookup or hard-code app config data in nici db using oauth_consumer_key (from post array) as search term
			// req. values: ltiapp_secret, ltiapp_inst (institution), ltiapp_last_nonce
			
			// authorize LTI request using oauth_signature, oauth_consumer_key and post array 
				// compare oauth_nonce to previous stored nonce
				// authorize LTI oauth signature using BLTI/oauth libs

			// lookup or hard-code institution config
			// req. values: cv_account_id (canvas institution level account), cv_api_token, cv_prd_url, cv_tst_url 
            
            // parse additional  LTI launch parameters from post array (even without security and config Canvas will send the LTI post data)
			// key data: resource_link_title (LTI App Title), custom_canvas_course_id, custom_canvas_api_domain
	
	// get course information. needed for Course Title and SIS ID if not extracted from post array.  
		// instantiate nici course object 'apiCanvasCourse' (class is not necessary but has builtin properties and methods for following steps)
		// Canvas API call: GET /api/v1/courses/:course_id/sections
			// construct URL
			// send request using CURL get 
			// parse response into array
		$course = new apiCanvasCourse();
		$crs_info = $course->getCourseInfo($cv_prd_url,$cv_api_token,$cv_course_id);
		
		//output html headers
		printf('<html>
					<head>
						<title>NICI Canvas Class Participation</title>

						<link href="%s" rel="stylesheet" type="text/css">
					
						<!-- <style>
							.tborder {border-top:thin dotted green;}
							
							.cborder {border-left:thin dotted;}
							

							.codeBlue {color: #0066FF};}
							
							.codeGreen {color:#0C6;}
							
							.codeYellow {background-color: #FF0;}
							
							.codeRed {background-color: #F30;}
						</style>
					  -->
					  <script type="text/javascript">
					  <!--
    						function toggle_visibility(id) {
							
       						var e = document.getElementById(id);
       						if(e.style.display == "table-row")
          						e.style.display = "none";
       						else
         						 e.style.display = "table-row";
    						}
							
					//-->
					</script>
					</head>
				<body>',$css_include);
				
				
		
		// display course report headers (TITLE etc)
		printf('<h3 align="center">Class Participation Report</h3>');
		#printf("Course Title: <b>%s</b><br />SIS course id: <b>%s</b>", $crs_info['name'], $crs_info['sis_course_id']);
		#printf("<br>");
		
		// get course sections.  if enrollments are at the course level not section, 
		// the enrollments will be another array contained in the $crs_info array.
		
		$course->getSections($cv_prd_url,$cv_api_token,$cv_course_id);
		#print_r($course->sections);
		
		//sort section array
		asort($course->sections);
		
	//instantiate section object	
		$section=new apiCanvasSection();
		
	// iterate sections
		foreach($course->sections as $sec) {

			if (isset($sec['id']) && $sec['id']!=="[" && $sec['id']!==""){	//hack to work around weird JSON/API quirk on nested arrays
			
			  	$sec_info=$section->getSectionInfo($cv_prd_url,$cv_api_token,$sec['id']);  //verify section info and grab title and SIS id
			  
			  

			  
		  	  	// create table header for sections with student enrollments
			  		// display table caption (Section name)
				printf('<table width="800" border="1" cellpadding="1" align="center" >
							<tr>
								<th align="center">Section:%s<br />%s<br />
								<p align="center">Activity Legend: 
								<span class="codeBlue">0-3 days</span>, <span class="codeGreen">4-7 days</span>, 
								<span class="codeYellow">8-10</span>, <span class="codeRed">10+ days</span> </p>
								</th>
							</tr>
							<tr>
								<td>
							',$sec['name'],$sec['sis_section_id']); // don't forget to close this cell
  
				// nest table and display column headers (e.g. avatar, sortable name, views, participations
				printf('<table width="%s" >
							<tr>
								<th align="center" width="320" >Student</th>
								<th align="center" width="240" style="border-left:thin dotted;">Views</th>
								<th align="center" width="240" style="border-left:thin dotted;">Submissions</th>
							</tr>
							<tr>
								<td colspan="3">',
							"100%");
								
		  
		  		// get section roster (check for pagination after 50 records)
			  		// Canvas API call: GET /api/v1/sections/:section_id/enrollments?per_page=50
						// construct URL
						// send request using CURL get 
						// parse response into array
						// filter empty sections	
			 	
				$roster=$section->getSectionRoster($cv_prd_url,$cv_api_token,$sec['id']);
			  	#print_r($roster[1]['user']);						
				
				// instantiate analytics api object
				$analytics = new apiCanvasAnalytics;	
						 		
				// iterate roster to query each student 
				foreach($roster as $enrollment) {
			  
				  // get user analytics 
					// Canvas API call: GET /api/v1/courses/:course_id/analytics/users/:student_id/activity
					  // construct URL
					  // send request using CURL get 
					  // parse response into array
					  // filter Teacher and TestStudent enrollments  ****
				
				
				  if($enrollment['role']=="StudentEnrollment") {
					$user=$enrollment['user'];
					$user_activity = $analytics->getUserCourseDetails($cv_prd_url,$cv_api_token,$cv_course_id,$user['id']);
					#print_r($user_activity);
					
					$view_dates=array();
					$total_views=array();
					$last_date="";
					$min_view_date="";
					$max_view_date="";
					$detail_div_id=$user['sis_user_id'];
					$profile_url=sprintf("%s/courses/%s/users/%s", $cv_prd_url , $cv_course_id , $user['id']);
					foreach($user_activity['page_views'] as $timestamp=>$count) {
						//split timestamp to date and time and rebuild array
						$temp_datetime=explode("T",$timestamp);
						$date=strftime("%D",strtotime($temp_datetime[0]));
						$time=$temp_datetime[1];
						#echo $date;
						if ($date==$last_date) {
							$total_views[$date] += $count;
						}
						else {
							$total_views[$date] = $count;							
						}
						
						$view_dates[$date]=array($date , $total_views[$date] , 0 );
						#array_push($view_dates,array($date , $total_views[$date]));
												
						//check for min and max dates
						if (($date<$last_date)||($last_date=="")) {
							$min_view_date = $date;
						}
						elseif ($date>$last_date) {
							$max_view_date = $date;	
						}
							
					
						//set last date and loop back
						$last_date=$date;
					}
					
					#print_r($view_dates);
						//flag login exceptions by adding inline css 
							//function: checkDateException
							$alertLogin_style=checkDateException($max_view_date);
								
					//check for no participations
					if (!empty($user_activity['participations'])) {
					  $participation_dates=array();
					  
					  //iterate through participations for dates
					  foreach($user_activity['participations'] as $participation) {
						  $date=strftime("%D",strtotime($participation['created_at']));
						  $view_dates[$date][2] += 1;
						  array_push($participation_dates,$date);
						  $min_part_date=min($participation_dates);
						  $max_part_date=max($participation_dates);
						  //flag exceptions by adding inline css 
						  	//function: checkDateException
							$alertPart_style=checkDateException($max_part_date);
					  }
					}
					else {
					  $min_part_date="none";
					  $max_part_date="none";
					  $alert_style='style="codeRed"';	
					}
					
					
					
					
					
					
					//print student summary role as Another nested table,  --need to add collapsable detail view 
					// --replace with student avatar if available (doubles api calls)
					printf('<table width="%s" style="border-top:thin dotted;" cellpadding="0" cellspacing="0">
							  <tr >
								  <td width="50"><img src="%sdefault_avatar.png" /></td> 
								  <td width="270"> <!--Student info-->
								  		<div style="float:left;"><a href="%s" target="_blank">%s</a>&nbsp;<br />%s<br></div>
      									<div class="emailLink" style="float:right;" >
											<img src="%semail.png" style="display:none"> <!-- link to Canvas conversation -->
											<br />
											<a href="javascript:void(0)" onclick="toggle_visibility(%s);">Show/Hide chart +</a>
											</div> 
    									
								  </td>	
								  <td width="80" align="center" style="border-left:thin dotted;"><b>First:</b><br /> %s</td> 	<!--First View info-->
								  <td width="80" align="center" %s ><b>Last:</b><br />%s</td>									<!--Last View-->
								  <td width="80" align="center" ><b>Total:</b><br />%s</td>										<!--Total Views-->
								  <td width="80" align="center" style="border-left:thin dotted;"><b>First:</b><br />%s</td>		<!--First Submit-->
								  <td width="80" align="center" %s ><b>Last:</b><br />%s</td>									<!--Last Submit-->
								  <td width="80" align="center" ><b>Total:</b><br />%s</td>										<!--Total Submits-->
													  
							  </tr>
							  <tr style="display:none;" id="%s">
							  	  <td colspan="8" align="center"  >',
								  
							"100%", $image_path , $profile_url , $user['name'] , $user['sis_user_id'] , $image_path , $detail_div_id , $min_view_date ,
							$alertLogin_style , $max_view_date , array_sum($user_activity['page_views']) , $min_part_date , $alertPart_style , 
							$max_part_date , count($user_activity['participations']) ,
							$user['sis_user_id']); //display student record and open collapsable detail cell
							
							include 'rpt_user_expand_detail.php';
							
							printf('</td>
							  </tr>
							</table>');  //close detail and student record
							
							// flush output buffer
							ob_flush();
					
					#printf("Name: %s<br />Views: %s,  First: %s,  Last: %s <br />Participations: %s, First: %s,  Last: %s<br />",
					#		$userA['name'],array_sum($user_activity['page_views']),$min_view_date,$max_view_date,
					#		count($user_activity['participations']), min($participation_dates), max($participation_dates));
					
					
					
	
					  
	  
						
					  
					  // display activity summary with drill-down
						// create table row
						// create table cells with user data from analytics array
						// create hidden row with spanned cells to display detail
						// write detail data to hidden row
						// provide link to show/hide detail
				  }
					
				  #exit();
				} //end iterate roster
				
					//close table
					printf('				</td>
										</tr>
									</table>
									');
				
				
			} // end JSON nested array hack check
			  
		} // end iterate sections
		
					  
	// display report footer
		// report version number
		// support link

	//close html 
	printf('	</body>
			</html>');
			
}  //end oauth check
			
	
		