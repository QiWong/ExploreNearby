<?php
	/**
	* User's information
	*/
	class User
	{
		//The current location of a user.
		public $lat;
		public $lon;
        public $keyword;
        public $category;
        public $distance;
        public $base_loc;
        public $customized_loc;
		
        function getUserInput(){
            $this->keyword = $_POST['keyword'];
            $this->category = str_replace(' ', '_', $_POST['category']);
            $this->distance = floatval($_POST['distance'])*1609.34;
            $this->base_loc = $_POST['base_loc'];
            $this->customized_loc = $_POST['customized_loc'];
            $this->lat = $_POST['lat'];
            $this->lon = $_POST['lon'];
        }
	}

	$user = new User;
    if (!empty($_POST)) {
        //The user search places nearby.
        $user->getUserInput();
        $res = array("places"=>$user->lat);
        $res = json_encode($res);
        $placesAPIURL = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?";
        $PlacesAPIKey = "AIzaSyD6MyH7xppln3pf8K4IXgLjjZvarCwcSE0";
        $param = "key=".$PlacesAPIKey."&radius=".$user->distance."&location=".$user->lat.",".$user->lon."&keyword=".$user->keyword;
        if ($user->category != "default") {
            $param = $param."&type=".$user->category;
        }
        $googleAPIResp = file_get_contents($placesAPIURL.$param);
        echo $googleAPIResp;
        //delete the saved image files on the server to save space
        foreach (glob("*.jpg") as $image){
            unlink($image);
        }
        return;
    }
    if (!empty($_GET)) {
        $PlacesAPIKey = "AIzaSyD6MyH7xppln3pf8K4IXgLjjZvarCwcSE0";
        $placesAPIURL = "https://maps.googleapis.com/maps/api/place/details/json?";
        $param = "key=".$PlacesAPIKey."&placeid=".$_GET["placeid"];
        $googleAPIResp = file_get_contents($placesAPIURL.$param);
        $placesDetailsResp = json_decode($googleAPIResp, true);
        
        $placeName = $placesDetailsResp['result']['name'];
        $minLength = min(5, sizeof($placesDetailsResp['result']['photos']));
        $photoNames = array();
        for ($x = 0; $x < $minLength; $x++) {
            $photoUrl = "https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference=".$placesDetailsResp['result']['photos'][$x]['photo_reference']."&key=".$PlacesAPIKey;
            $photoName = $_GET["placeid"].$x.".jpg";
            file_put_contents($photoName, file_get_contents($photoUrl));
            array_push($photoNames, $photoName);

        }

        $minLength = min(5, sizeof($placesDetailsResp['result']['reviews']));
        $reviews = array();
        for ($x = 0; $x < $minLength; $x++) {
            $authorName = $placesDetailsResp['result']['reviews'][$x]["author_name"];
            $authorProfile = $placesDetailsResp['result']['reviews'][$x]["profile_photo_url"];
            $textReview = $placesDetailsResp['result']['reviews'][$x]["text"];
        
            array_push($reviews, array('authorName' => $authorName, 'profilePhoto'=>$authorProfile, 'text'=>$textReview));
        }
        $reviewsAndPhotos = array('photos' => $photoNames, 'reviews'=>$reviews, 'name'=>$placeName);
        echo json_encode($reviewsAndPhotos);
        return;
    }
    
?>

<html lang="en">
<head>
	<title>Travel And Entertainment Search</title>
    <meta charset="utf-8"/>
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
        #placesNearbyResults{
            width: 90%;
            margin: auto;
            text-align: center;
            position: relative;
            top: 20px;
        }

    </style>

    <script type="text/javascript">
    function disableLocInput() {
        document.getElementById("locationValue").disabled = true;
    }
    function enableLocInput() {
        document.getElementById("locationValue").disabled = false;
    }
    </script>
</head>

<body>

	<div class="container">
		<form name="searchPlaceBox" onsubmit="return false;">
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
				<option>default</option>
				<script type="text/javascript">
				// var categories = ["accounting","airport","amusement_park","aquarium","art_gallery","atm","bakery",
				// "bank","bar","beauty_salon","bicycle_store","book_store","bowling_alley","bus_station","cafe","campground",
				// "car_dealer","car_rental","car_repair","car_wash","casino","cemetery","church","city_hall","clothing_store",
				// "convenience_store","courthouse","dentist","department_store","doctor","electrician","electronics_store","embassy",
				// "fire_station","florist","funeral_home","furniture_store","gas_station","gym","hair_care","hardware_store","hindu_temple",
				// "home_goods_store","hospital","insurance_agency","jewelry_store","laundry","lawyer","library","liquor_store","local_government_office",
				// "locksmith","lodging","meal_delivery","meal_takeaway","mosque","movie_rental","movie_theater","moving_company","museum",
				// "night_club","painter","park","parking","pet_store","pharmacy","physiotherapist","plumber",
				// "police","post_office","real_estate_agency","restaurant","roofing_contractor","rv_park",
				// "school","shoe_store","shopping_mall","spa","stadium","storage","store","subway_station","supermarket",
				// "synagogue","taxi_stand","train_station","transit_station","travel_agency","veterinary_care","zoo"];

                var categories = ["cafe","bakery","restaurant","beauty salon","casino","movie theater","lodging","airport","train station","subway station","bus station"];
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
				<span>Distance (miles)</span><input type="number" id="distance" value="10" placeholder="10">
				</td>
				<td>
				<span>from</span><input type="radio" name="from" value="here" onclick="disableLocInput()" checked> Here<br>
  				<input type="radio" name="from" value="location" style="margin-left:45px;" onclick="enableLocInput()"><input type="text" id="locationValue" placeholder="Location" disabled><br><br>
				</td>
			</tr>
		<br>
		
		</table>
		<input type="submit" value="Search" class="btn" onclick="searchPlaces()" id="searchBtn" disabled>
		<input type="button" value="Clear" onclick="clearUserInput()" class="btn">
		</form>
	</div>
    <div id="placesNearbyResults">
        
    </div>
</body>
</html>

<script type="text/javascript">
    /*-----------------------------------------------*/
 	class User{
    	constructor() {
    		// User's longitude and latitude
    		this.lat = 0;
    		this.lon = 0;
    		this.keyword = '';
    		this.base_loc = '';
    		this.customized_loc = '';
    		this.category = '';
    		this.distance = '10';
    	}

    	getAndValidateInput() {
    		//Check whether keyword input box and location box is empty
    		var keyword = document.getElementById("keyword").value;
    		if (keyword == '') {
    			return false;
    		}else {
    			this.keyword = keyword;
    		}
    		this.base_loc = document.querySelector('input[name="from"]:checked').value;
	    	if (this.base_loc == "location") {
	    		var inputLocForm = document.getElementById("locationValue");
	    		var inputLoc = inputLocForm.value;
	    		this.customized_loc = inputLoc;
	    		if (inputLoc == '') {
	    			inputLocForm.setCustomValidity('please enter a valid location');
	    			return false;
	    		}else {
	    			inputLocForm.setCustomValidity('');
	    		}
	    	}
	    	this.category = document.getElementById("categoryList").value;
	    	//"Default" means all types
	    	this.distance = document.getElementById("distance").value;
	    	if (this.distance == '') {
	    		this.distance = '10';
	    	};

	    	return true;
    	}

    	searchPlacesNearBy() {
            var resp = null;
            var xhttp = new XMLHttpRequest();
            var url = "exploreSpace.php";
            var params = "keyword="+this.keyword+"&category="+this.category+"&distance="+this.distance+"&base_loc="+this.base_loc+"&customized_loc="+this.customized_loc+"&lat="+this.lat+"&lon="+this.lon;
            //console.log("params "+params);
            xhttp.open("POST",url,false);
            xhttp.onreadystatechange = function(){
                 if (this.readyState==4 && this.status==200) {
                    resp = JSON.parse(this.responseText);
                    console.log(resp);
                 }
            };
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(params);
            return resp;
            // ----------------------------------------
            // var fileUrl="places.json";
            // xhttp.open("GET", fileUrl,false);

            // xhttp.onreadystatechange = function(){
            //     if (this.readyState==4 && this.status==200) {
            //         resp = JSON.parse(this.responseText);
            //     }
            // };
            // xhttp.send();
            // return resp;
    	}

        showPlacesInTable(tableID, jsonResp) {
            var tb = document.getElementById(tableID);
            var tableContent = "<table style=\"margin: 0 auto; width:90%;border: 1px solid gray;border-collapse: collapse;\"><tr><th height=\"10\" width=\"5%\" style=\"border: 1px solid gray;\">Category</th><th width=\"45%\" style=\"border: 1px solid gray;\">Name</th><th style=\"border: 1px solid gray;\" width=\"45%\">Address</th></tr>";
            for (var i = 0; i < jsonResp.results.length; i++) {
                tableContent+="<tr>";
                tableContent+="<td style=\"border: 1px solid gray;\"> <img style=\"height:30px;\" src=\""+encodeURI(jsonResp.results[i].icon)+"\"/></td>";
                tableContent+="<td style=\"border: 1px solid gray;\"><a style=\"cursor:pointer;\" onclick=\"showPlaceDetails('"+jsonResp.results[i].place_id+"')\">"+jsonResp.results[i].name+"</a></td>";
                tableContent+="<td style=\"border: 1px solid gray;\"><a style=\"cursor:pointer;\" onclick=\"showGoogleMap("+jsonResp.results[i].geometry.location.lat+","+jsonResp.results[i].geometry.location.lng+","+i+")\">"+jsonResp.results[i].vicinity+"</a></td>";
                tableContent+="</tr>";
            };
            tableContent+="</table>";
            tb.innerHTML = tableContent;
        }

    }
    /*-----------------------------------------------*/
    //Disable the location input box when "here" is selected
    if (document.getElementsByName("from")[0].checked == false) {
        document.getElementById("locationValue").disabled = false;
    }

    var user = new User();
    //get User's location by their IP using ip-api.
    var script = document.createElement("script");
    script.src = "http://ip-api.com/json/?callback=getUserLoc";
    document.getElementsByTagName("head")[0].appendChild(script);

    function getUserLoc(response) {
        if (response.status=="success") {
            document.getElementById("searchBtn").disabled = false;
            user.lat = response.lat;
            user.lon = response.lon;
        }else {
            alert("Sorry, cannot determine your location. Please refresh the page");
        }
    }
    
    function searchPlaces() {
    	//Check if user's input is valid. Some information are required
    	if (user.getAndValidateInput()) {
    		//if user's input are valid, then we can search palces by calling google API
    		var places = user.searchPlacesNearBy();
            if (places != null) {
                user.showPlacesInTable("placesNearbyResults",places);
            }else {
                document.getElementById("placesNearbyResults").innerHTML = "<p>No Record has been found</p>";
            }
    	}

    }


    function showPlaceDetails(place_id) {
        //---------This function will show the reviews and photos of this place
        var resp = null;
        var photoReferences = [];
        var xhttp = new XMLHttpRequest();
        var url = "exploreSpace.php";
        var params = "placeid="+place_id;
        xhttp.open("GET",url+"?+"+params,false);
        //------------------------------------------
        // var url = "placesDetails.json";
        // xhttp.open("GET",url,false);
        xhttp.onreadystatechange = function(){
            if (this.readyState==4 && this.status==200) {
                resp = JSON.parse(this.responseText);
                //---------------------show the details of the reviews
                var details = "<h3>"+resp['name']+"</h3>";
                details += "<div style=\"text-align: center\"><p style=\"text-align: center;margin-bottom: 0;\">click to show reviews</p><img id=\"reviewsTrigger\" style=\"margin-bottom: 10px;cursor:pointer;\" width=25 src=\"http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png\"/><table id=\"reviewsTable\" style=\"width:80%; margin-left:auto; margin-right: auto; border-collapse: collapse;display: none;\">";
                var authorName;
                var profilePhoto;
                var reviewText;
                if (resp == null || resp['reviews']==false || resp['reviews'].length == 0) {
                    details+="<p>No Reviews Found</p>";
                };
                for (var i = 0; i < resp['reviews'].length; i++) {
                    profilePhoto = resp['reviews'][i]['profilePhoto'];
                    authorName = resp['reviews'][i]['authorName'];
                    reviewText = resp['reviews'][i]['text'];
                    details+="<tr><td style=\"text-align: center;border: 1px solid gray;\"><img width=30 src=\""+encodeURI(profilePhoto)+"\"><span style=\"font-weight: bold\">"+authorName+"</span></td></tr>";
                    details+="<tr><td style=\"border: 1px solid gray;\"><p>"+reviewText+"</p></td></tr>";
                };
                details+="</table>";

                //------------------------show all the photos
                details+="<p style=\"text-align: center;margin-bottom: 0;\">click to show photos</p><img id=\"photosTrigger\" width=25 style=\"margin-left: auto;margin-right: auto;margin-bottom: 10px;cursor:pointer;\" src=\"http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png\"/><table id=\"photosTable\" style=\"width:80%; margin:auto;border-collapse: collapse;display: none;\">";
                if (resp == null || resp['photos']==false || resp['photos'].length == 0) {
                    details+="<p>No Photos Found</p>";
                };
                var photoSrc;
                for (var i = 0; i < resp['photos'].length; i++) {
                    photoSrc="http://cs-server.usc.edu:25637/"+resp['photos'][i];
                    details+="<tr><td style=\"text-align: center;border: 1px solid gray;\"><img style=\"width:60%\" id=\"photo"+i+"\" src=\""+photoSrc+"\"></td></tr>";
                };
                details+="</table></div>";

                document.getElementById("placesNearbyResults").innerHTML = details;
                //---------------------------add click function-------------
                for (var i = 0; i < resp['photos'].length; i++) {
                    document.getElementById("photo"+i).addEventListener("click", function(){
                        window.open(this.src, '_blank');
                    });
                };
                document.getElementById("reviewsTrigger").addEventListener("click", function(){
                    if (document.getElementById("reviewsTable").style.display =='none') {
                        document.getElementById("reviewsTable").style.display = 'table';
                        document.getElementById("reviewsTrigger").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                    }else{
                        document.getElementById("reviewsTable").style.display = 'none';
                        document.getElementById("reviewsTrigger").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                    }
                });
                document.getElementById("photosTrigger").addEventListener("click", function(){
                    if (document.getElementById("photosTable").style.display =='none') {
                        document.getElementById("photosTable").style.display = 'table';
                        document.getElementById("photosTrigger").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                    }else{
                        //console.log("hiiii");
                        document.getElementById("photosTable").style.display = 'none';
                        document.getElementById("photosTrigger").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                    }
                });
            }
        };
        xhttp.send();
        
    }

    var latValue;
    var lonValue;
    var mapDivIndex;
    function initMap() {
        var addr = {lat: latValue, lng: lonValue};
        var directionsDisplay = new google.maps.DirectionsRenderer;
        var directionsService = new google.maps.DirectionsService;
        var map = new google.maps.Map(document.getElementById("mapDiv"+mapDivIndex), {
            zoom: 15,
            center: addr
        });
        var marker = new google.maps.Marker({
            position: addr,
            map: map
        });

        directionsDisplay.setMap(map);

        document.getElementById("DRIVING"+mapDivIndex).addEventListener('click', function() {
            calculateAndDisplayRoute(directionsService, directionsDisplay, "DRIVING", addr);
        });

        document.getElementById("WALKING"+mapDivIndex).addEventListener('click', function() {
            calculateAndDisplayRoute(directionsService, directionsDisplay, "WALKING", addr);
        });

        document.getElementById("BICYCLING"+mapDivIndex).addEventListener('click', function() {
            calculateAndDisplayRoute(directionsService, directionsDisplay, "BICYCLING", addr);
        });
    }

    function calculateAndDisplayRoute(directionsService, directionsDisplay, travelMode, destination) {
        directionsService.route({
            origin: {lat: user.lat, lng: user.lon},  // Haight.
            destination: destination,  // Ocean Beach.
            travelMode: google.maps.TravelMode[travelMode]
        }, function(response, status) {
            if (status == 'OK') {
                directionsDisplay.setDirections(response);
            } else {
                window.alert('Directions request failed due to ' + status);
            }
        });
      }

    function showGoogleMap(lat, lon, index) {
        latValue = lat;
        lonValue = lon;
        mapDivIndex = index;
        if (document.getElementById("mapContainer"+index)) {
            if (document.getElementById("mapContainer"+index).style.display == 'none') {
                document.getElementById("mapContainer"+index).style.display = 'block';
            }else {
                document.getElementById("mapContainer"+index).style.display = 'none';
            }
            return;
        }else {
            var mapdiv = document.createElement("div");
            mapdiv.id = "mapDiv"+index;
            mapdiv.style.width = "300px";
            mapdiv.style.height = "300px";
            mapdiv.style.position = "absolute";
            // mapdiv.style.zIndex = "100";
            // mapdiv.style.top = index*33 +60+"px";
            // mapdiv.style.left = "55%";
            mapdiv.style.top = "0px";
            mapdiv.style.left = "0px";

            var mapContainerDiv = document.createElement("div");
            mapContainerDiv.id = "mapContainer"+index;
            mapContainerDiv.style.width = "300px";
            mapContainerDiv.style.height = "300px";
            mapContainerDiv.style.position = "absolute";
            mapContainerDiv.style.zIndex = "20";
            mapContainerDiv.style.top = index*33 +60+"px";
            mapContainerDiv.style.left = "55%";
            var travelModeList = "<div style=\"position: absolute;top: 0px;left: 0px;z-index: 30;background-color: white;\"><ul style=\"list-style-type:none; background-color: white;padding:0px;margin:0;\"> <li id=\"WALKING"+index+"\" style=\"padding: 5px;\"onMouseover=\"this.style.backgroundColor= 'gray'\" onmouseleave=\"this.style.backgroundColor= 'white'\">Walk There</li><li id=\"BICYCLING"+index+"\"+ onMouseover=\"this.style.backgroundColor = 'gray'\" onmouseleave=\"this.style.backgroundColor= 'white'\" style=\"padding: 5px;\">Bike There</li><li id=\"DRIVING"+index+"\" onMouseover=\"this.style.backgroundColor = 'gray'\" onmouseleave=\"this.style.backgroundColor= 'white'\" style=\"padding: 5px;\">Drive There</li></ul></div>";
            mapContainerDiv.innerHTML = travelModeList;
            mapContainerDiv.appendChild(mapdiv);

            var container = document.getElementById("placesNearbyResults");
            container.appendChild(mapContainerDiv);
            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDyaGMUW8neSd9IDx3GFrMGhyXMOYfqFIs&callback=initMap';
            document.getElementsByTagName('head')[0].appendChild(script);
        }
        
    }
    
    function clearUserInput() {
    	//clear all the user input and set all the fields to default values
    	//reset all the input fields
    	document.getElementById("keyword").value = "";
    	document.getElementById("distance").value = "10";
    	document.getElementById("categoryList").value = "default";
		document.getElementsByName("from")[0].checked = true;
        document.getElementById("locationValue").disabled = true;
		document.getElementById("locationValue").value = "";
        document.getElementById("placesNearbyResults").innerHTML = "";
    }

</script>
