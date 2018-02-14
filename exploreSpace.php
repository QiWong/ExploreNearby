<?php
	/**
	* User's information
	*/
	class User
	{
		//The current location of a user.
		public $lat = null;
		public $lon = null;

		function getUserLoc() {
			//Get user's location based on user IP
			$locResp = file_get_contents("http://ip-api.com/json");
			if ($locResp != FALSE) {
				echo $locResp;
			}else {
				echo "Error";
			}
		}
	}

	$user = new User;

	if ($_GET["reqType"]=="1") {
		$user->getUserLoc();
		return;
	}
?>

<html lang="en">
<head>
	<title>Travel And Entertainment Search</title>
    <style type="text/css">
    	.header{
    		font-family: serif;
    		font-size: 30px;
    		font-style:oblique;
    		margin:5px;
    	}
    	.container {
    		width: 600px;
    		border: solid gray;
    		text-align: center;
    		margin: auto;
    	}

    	hr {
    		color: gray;
    		margin-left: 5px;
    		margin-right: 5px;
    	}
    	table {
    		width: 580px;
 			margin: auto;
    	}
    	td {
    		width: 290px;
    		text-align: left;
    	}
    	span {
    		margin-right: 5px;
    		font-weight: bold;
    	}
    </style>

</head>
<body>
	<div class="container">
		<form>
		<p class="header">Travel And Entertainment Search</p>
		<hr/>
		<table>
		<tr>
			<td>
			<span>Keyword</span><input type="text" id="keyword" oninvalid="this.setCustomValidity('Please enter your keyword')" oninput="setCustomValidity('')" required>
 			</td>
 			<td>
 			</td>
 		</tr>
			<tr>
				<td>
				<span>Category</span>
				<select id="categoryList">
				<option>Default</option>
				<script type="text/javascript">
				var categories = ["accounting","airport","amusement_park","aquarium","art_gallery","atm","bakery",
				"bank","bar","beauty_salon","bicycle_store","book_store","bowling_alley","bus_station","cafe","campground",
				"car_dealer","car_rental","car_repair","car_wash","casino","cemetery","church","city_hall","clothing_store",
				"convenience_store","courthouse","dentist","department_store","doctor","electrician","electronics_store","embassy",
				"fire_station","florist","funeral_home","furniture_store","gas_station","gym","hair_care","hardware_store","hindu_temple",
				"home_goods_store","hospital","insurance_agency","jewelry_store","laundry","lawyer","library","liquor_store","local_government_office",
				"locksmith","lodging","meal_delivery","meal_takeaway","mosque","movie_rental","movie_theater","moving_company","museum",
				"night_club","painter","park","parking","pet_store","pharmacy","physiotherapist","plumber",
				"police","post_office","real_estate_agency","restaurant","roofing_contractor","rv_park",
				"school","shoe_store","shopping_mall","spa","stadium","storage","store","subway_station","supermarket",
				"synagogue","taxi_stand","train_station","transit_station","travel_agency","veterinary_care","zoo"];
				//console.log(categories);
				for (var i = 0; i <= categories.length - 1; i++) {
					document.write("<option>"+categories[i]+"</option>");
				};
				</script>
				</select>
				</td>
				<td></td>
			</tr>
			<tr>
				<td style="vertical-align: text-top;">
				<span>Distance (miles)</span><input type="number" id="distance">
				</td>
				<td>
				<span>from</span><input type="radio" name="from" value="here" checked> Here<br>
  				<input type="radio" name="from" value="location" style="margin-left:45px;"><input type="text" id="locationValue" placeholder="Location"><br><br>
				</td>
			</tr>
		<br>
		
		</table>
		<input type="submit" value="Search" class="btn" onclick="searchPlaces()" id="searchBtn" disabled>
		<input type="button" value="Clear" class="btn">
		</form>
	</div>
</body>
</html>

<script type="text/javascript">
 	class User{
    	constructor() {
    		// User's longitude and latitude
    		this.lat = 0;
    		this.lon = 0;
    	}

    	getUserLoc() {
    		//Get user's location using user's ip.
    		var resp = null;
    		var xhttp = new XMLHttpRequest();

    		xhttp.onreadystatechange = function(){
    			if (this.readyState==4 && this.status==200) {
    				resp = this.responseText;
    			}
    		};
    		xhttp.open("GET","exploreSpace.php?reqType=1",false);
    		xhttp.send();
    		if (resp != "Error") {
    			resp = JSON.parse(resp);
    			this.lat = resp.lat;
    			this.lon = resp.lon;
    			return true;
    		}else {
    			return false;
    		}
    	}

    	getAndValidateInput() {
    		//Check whether keyword input box and location box is empty
    		var keyword = document.getElementById("keyword").value;
    		if (keyword == '') {return false};
    		var baseLoc = document.querySelector('input[name="from"]:checked').value;
	    	if (baseLoc == "location") {
	    		var inputLocForm = document.getElementById("locationValue");
	    		var inputLoc = inputLocForm.value;
	    		if (inputLoc == '') {
	    			inputLocForm.setCustomValidity('please enter a valid location');
	    			return false;
	    		}else {
	    			inputLocForm.setCustomValidity('');
	    		}
	    	}
	    	var category = document.getElementById("categoryList").value;
	    	//"Default" means all types
	    	if (category == "Default") {

	    	}else {

	    	}
	    	
	    	var dis = document.getElementById("distance").value;

    	}
    }
    	
    var user = new User();
    if (user.getUserLoc()) {
    	document.getElementById("searchBtn").disabled = false;
    }else {
    	alert("Sorry, cannot determine your location");
    }

    function searchPlaces() {
    	user.getAndValidateInput();
    }
    //Check User input
</script>
