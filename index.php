<?php

$token = '87ddb07e8f60e0bef7973cf1a3eefbb513a833c8';

$team_options = array("all"				  => "All",
					  "red-pandas"        => "Red Pandas",
               		  "golden-eagles"     => "Golden Eagles",
               		  "blue-oyster"       => "Blue Oyster",
               		  "black-jaguar"      => "Black Jaguar",
               		  "silver-hippogriff" => "Silver Hippogriff",
               		  "grey-hounds"       => "Grey Hounds",
               		  "green-griffon"     => "Green Griffon");

$PR_state_options = array("all"    => "All",
						  "open"   => "Open",
						  "closed" => "Closed");

$per_page_options = array("5"   => "5",
						  "10"  => "10",
						  "25"	=> "25",
						  "50"  => "50",
						  "100" => "100");

set_options_defaults();
get_PRs();

function set_options_defaults(){
		global $team_options;
		global $PR_state_options;
		global $per_page_options;

		if ( !isset( $_GET["team_name"] ) ){
			$_GET["team_name"] = key($team_options);
		}

		if ( !isset( $_GET["PR_state"] ) ){
			$_GET["PR_state"] = key($PR_state_options);
		}

		if ( !isset( $_GET["per_page"] ) ){
			$_GET["per_page"] = "25";
		}

		if ( !isset( $_GET["page"] ) ){
			$_GET["page"] = "1";
		}
}

function get_PRs(){
	global $token;

	$url = "https://api.github.com/repos";
	$org_name = "mdsol";
	$repo_name = "ctms";
	$PRs_name = "pulls";
	$PR_state = "state=".$_GET["PR_state"];
	$page = "page=".$_GET["page"];
	$per_page = "per_page=".$_GET["per_page"];
	$access_token = "access_token=".$token;

	$team_name = $_GET["team_name"];

	$request_url = $url."/".$org_name."/".$repo_name."/".$PRs_name."?".
					$PR_state."&".
					$page."&".
					$per_page."&".
					$access_token;

	$context = stream_context_create(create_opts('GET'));
	$content = file_get_contents($request_url, false, $context);
	$response = json_decode($content, true);

	$response_for_team = convert_response_for_teams($response);

	draw_PRs($response_for_team);
}

function convert_response_for_teams($response){
	global $team_options;

	$response_for_team = array();

	for ($i = 0; $i < sizeof($response); $i++){
		if ( $_GET["team_name"] == key($team_options) ){
			array_push($response_for_team, $response[$i]);
		}
		else{
			$body = $response[$i]["body"];

			if (strpos($body, $_GET["team_name"]) !== false) {
				array_push($response_for_team, $response[$i]);
			}
		}
	}

	return $response_for_team;
}

// request_type: GET, POST, PUT, PATCH
function create_opts($request_type){
	$opts = [
        'http' => [
                'method' => $request_type,
                'header' => [
                        'User-Agent: PHP'
                ]
        ]
	];

	return $opts;
}

function draw_PRs($response){
	global $team_options;
	global $PR_state_options;
	global $per_page_options;

	$team_name = $_GET["team_name"];
	$PR_state = $_GET["PR_state"];
	$page = $_GET["page"];
	$per_page = $_GET["per_page"];

	echo "<!DOCTYPE html>";
	echo "<html>";
		echo "<head>";
			echo "<title>CTMS PRs</title>";
			echo "<link rel='stylesheet' type='text/css' href='./style.css'>";
			echo "<script src='http://code.jquery.com/jquery-3.1.0.min.js' integrity='sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=' crossorigin='anonymous'></script>";
			echo "<script type='text/javascript' src='./scripts.js'></script>";
		echo "</head>";
		echo "<body>";
		
		echo "<br>";

		// Option for teams
		echo "<select id='select_team' onchange='change_team(\"".$PR_state."\", \"".$page."\", \"".$per_page."\")'>";
		foreach ($team_options as $key => $value) {
			echo "<option value='".$key."'"; if ($_GET["team_name"] == $key) echo "selected"; echo ">".$value."</option>";
		}
		echo "</select>";

		// Option for PR state
		echo "<select id='select_PR_state' onchange='change_PR_state(\"".$team_name."\", \"".$page."\", \"".$per_page."\")'>";
		foreach ($PR_state_options as $key => $value) {
			echo "<option value='".$key."'"; if ($_GET["PR_state"] == $key) echo "selected"; echo ">".$value."</option>";
		}
		echo "</select>";

		// Option for page
		echo "<select id='select_per_page' onchange='change_per_page(\"".$team_name."\", \"".$PR_state."\", \"".$page."\")'>";
		foreach ($per_page_options as $key => $value) {
			echo "<option value='".$key."'"; if ($_GET["per_page"] == $key) echo "selected"; echo ">".$value."</option>";
		}
		echo "</select>";

		echo "<br>";
		echo "<br>";

		echo "<div id='pages_buttons'>";
			echo "<input type='image' src='./images/left_page_button.png' id='previous_page' onclick='previous_page(\"".$team_name."\", \"".$PR_state."\", \"".$per_page."\")'></input>";
			echo "<input id='page_number_field' type='text' value='".$page."' readonly></input>";
			echo "<input type='image' src='./images/right_page_button.png' id='next_page' onclick='next_page(\"".$team_name."\", \"".$PR_state."\", \"".$per_page."\")'></input>";
		echo "</div>";

		echo "<br>";

		echo "<table class='blocks'>";
			echo "<tr class='table_header'>";
				echo "<th class='table_header_title'>TITLE</th>";
				echo "<th class='table_header_github'>GITHUB</th>";
				echo "<th class='table_header_jira'>JIRA</th>";
				echo "<th class='table_header_branch'>BRANCH NAME</th>";
				echo "<th class='table_header_creator'>CREATOR</th>";
				echo "<th class='table_header_status'>STATUS</th>";
			echo "</tr>";

			for ($i = 0; $i < sizeof($response); $i++){
				echo "<tr class='table_row'>";
					// Title
					echo "<td class='table_title' onclick='load_branches(\"".$response[$i]["number"]."\")'>".$response[$i]["title"]."</td>";

					// Github
					echo "<td class='table_github'>";
						echo "<a href='".$response[$i]["html_url"]." 'target='_blank'>";
							echo "<img src='images/github_logo.png' class='github_logo'>";
						echo "</a>";
					echo "</td>";

					// JIRA
					echo "<td class='table_jira'>";
							$body = $response[$i]["body"];
							$number_of_jira_urls = substr_count($body, "https://jira.");

							if ($number_of_jira_urls > 0){
								$jira_link_unfixed = substr( $body, strpos($body, "https://jira.") );
								$jira_link_unfixed_end = strpos($jira_link_unfixed, "####");

								// if ticket url was put last
								if ( !$jira_link_unfixed_end ){
									$jira_link_unfixed_end = strpos($jira_link_unfixed, "\r");
								}

								$jira_link_parts = explode( "\r", substr( $jira_link_unfixed, 0, $jira_link_unfixed_end ) );

								for ($j = 0; $j < sizeof($jira_link_parts); $j++){
									if ( strpos($jira_link_parts[$j], "https://jira.") > -1 ){
										echo "<a href='".$jira_link_parts[$j]." 'target='_blank'>";
											echo "<img src='images/jira_logo.png' class='jira_logo'>";
										echo "</a><br>";
									}
								}
							}
							else{
								echo "-";
							}
					echo "</td>";

					// Branch name
					echo "<td class='table_branch_name'>";
						$head = $response[$i]["head"];
						echo $head["ref"];
					echo "</td>";

					// Creator
					echo "<td class='table_creator'>";
						$user = $response[$i]["user"];
						echo "<img src='".$user["avatar_url"]."' class='creator_logo'>";
					echo "</td>";

					// Open for
					if ($response[$i]["state"] == "open"){
						echo "<td class='table_open_for'>";
							$time_created = $response[$i]["created_at"];

							$time_created = str_replace("T", " ", $time_created);
							$time_created = str_replace("Z", "", $time_created);

							$time_now = date("Y-m-d H:i:s");

							$time_now_converted = new DateTime( $time_now );
							$time_created_converted = new DateTime( $time_created );

							$interval = date_diff($time_now_converted,$time_created_converted);

							echo $interval->format('%mm %dd %hh');
						echo "</td>";
					}
					else if ($response[$i]["state"] == "closed"){
						echo "<td class='table_closed_for'>";
							$time_created = $response[$i]["created_at"];

							$time_created = str_replace("T", " ", $time_created);
							$time_created = str_replace("Z", "", $time_created);

							$time_now = date("Y-m-d H:i:s");

							$time_now_converted = new DateTime( $time_now );
							$time_created_converted = new DateTime( $time_created );

							$interval = date_diff($time_now_converted,$time_created_converted);

							echo $interval->format('%mm %dd %hh');
						echo "</td>";
					}

				echo "</tr>";
			}
		echo "</table>";

		echo "</body>";
	echo "</html>";
}





