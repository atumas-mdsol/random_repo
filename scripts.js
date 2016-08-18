var team_name = "";
var PR_state = "";
var page = "";
var per_page = "";

function change_team(PR_state_GET, page_GET, per_page_GET) {
	team_name = document.getElementById("select_team").value;
	PR_state = PR_state_GET;
	page = page_GET;
	per_page = per_page_GET;

	reload_page();
}
function change_PR_state(team_name_GET, page_GET, per_page_GET) {
	team_name = team_name_GET;
	PR_state = document.getElementById("select_PR_state").value;
	page = page_GET;
	per_page = per_page_GET;

	reload_page();
}
function change_per_page(team_name_GET, PR_state_GET, page_GET) {
	team_name = team_name_GET;
	PR_state = PR_state_GET;
	page = page_GET;
	per_page = document.getElementById("select_per_page").value;

	reload_page();
}
function previous_page(team_name_GET, PR_state_GET, per_page_GET){
	team_name = team_name_GET;
	PR_state = PR_state_GET;
	if ( parseInt(document.getElementById("page_number_field").value) > 1 ){
		page = parseInt(document.getElementById("page_number_field").value) - 1;
	}
	else{
		page = 0;
	}
	per_page = per_page_GET;

	reload_page();
}
function next_page(team_name_GET, PR_state_GET, per_page_GET){
	team_name = team_name_GET;
	PR_state = PR_state_GET;
	page = parseInt(document.getElementById("page_number_field").value) + 1;
	per_page = per_page_GET;

	reload_page();
}

function reload_page(){
	var current_url = window.location.href;
	var page_url = current_url.split("?");

	window.location.href = page_url[0]
							+"?team_name="+team_name
							+"&PR_state="+PR_state
							+"&page="+page
							+"&per_page="+per_page;
}

function load_branches(PR_number){
	window.location.href = "./branches.php?number="+PR_number;
}



