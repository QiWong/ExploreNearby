var app = angular.module('searchApp', ['ngAnimate']);

app.value('user', {current_lat : 0, current_lon : 0});
app.value("next_page_token", null);

app.controller('searchCtrl', function($scope,$http, $sce, user, next_page_token) {
	//Initialize the category list
    $scope.categoryList = ["Default","cafe","bakery","restaurant","beauty salon","casino","movie theater","lodging","airport","train station","subway station","bus station"];
    $scope.from = "here";
    $scope.distance = 10;
    $scope.current_page = 0;
    $scope.is_last_page = false;
    $scope.all_places = [];
    var trustedUrl = $sce.trustAsResourceUrl('http://ip-api.com/json/');
    // $http.get("http://ip-api.com/json/", {jsonpCallbackParam: 'callback'}).then(function(data){
    // 	//enable the search button
    // 	$scope.get_ip_location = true;
    // 	user.current_lat = data.data.lat;
    //     user.current_lon = data.data.lon;
    //     console.log(user.current_lat);
    //     console.log(user.current_lon);
    // });

    $scope.get_ip_location = true;

    user.current_lat = 34.0266;
    user.current_lon = -118.2831;


    //When user click the search button, this function will be called.
    $scope.searchFunc = function() {
        console.log(user.current_lat);
        console.log(user.current_lon);
        if ($scope.from == "other") {
            //get the lat and lon through geocoding API
            $http({
                method : "GET",
                url : "http://localhost:8080/getNearybyPlaces/getLoc?address="+$('#enter_location').val()
            }).then(function(response) {
                console.log("the location user input is");
                user.current_lat = response.data.lat;
                user.current_lon = response.data.lng;
                
                console.log(user.current_lat);
                console.log(user.current_lon);
            });
        };

        var search_url = "http://localhost:8080/getNearybyPlaces?keyword="+$scope.keyword;
        search_url+="&location="+user.current_lat+","+user.current_lon;
        search_url+="&distance="+$scope.distance * 1609;
        search_url+="&category="+$scope.category.replace(/ /gi, "_");
        console.log(search_url);
        var places;
        $http({
            method : "GET",
            url : search_url
        }).then(function(response) {
            console.log(response.data);
            if (response.data.hasOwnProperty("next_page_token")) {
                next_page_token = response.data.next_page_token;
                $scope.is_last_page = false;
            }else {
                next_page_token = null;
                $scope.is_last_page = true;
            }
            places = response.data.results;
            $scope.all_places.length = 0;
            $scope.all_places.push(places);
        });

        $scope.searchPlaces=true;
    }

    $scope.nextSearchResults = function() {
        var next_page_url = "http://localhost:8080/getNearybyPlaces/getNextPageResults?token="+next_page_token;
        var places = null;
        $scope.is_last_page = true;
        if ($scope.current_page == $scope.all_places.length-1) {
            $http({
                method : "GET",
                url : next_page_url
            }).then(function(response) {
                console.log(response.data);
                if (response.data.hasOwnProperty("next_page_token")) {
                    next_page_token = response.data.next_page_token;
                    $scope.is_last_page = false;
                }else {
                    next_page_token = null;
                    $scope.is_last_page = true;
                }
                places = response.data.results;
                $scope.all_places.push(places);
                $scope.current_page +=1;
            });

        }else {
            $scope.current_page +=1;
            if ($scope.current_page == $scope.all_places.length-1) {
                $scope.is_last_page = true;
            };
        }
    }

    $scope.prevSearchResults = function() {
        $scope.current_page -= 1;
        $scope.is_last_page = false;
    }

    $scope.showDetails = function(row_index) {
        
        var place_id = $scope.all_places[$scope.current_page][row_index]['place_id'];
        console.log(place_id);

        // var details_container =document.getElementById("place_details_div");
        // var service = new google.maps.places.PlacesService(details_container);
        // service.getDetails({
        //   placeId: place_id
        // }, function(place, status) {
        //     console.log("hiiiii");

        //     console.log(place);
        //     $scope.place_details = place;
        // });
        var current_local_date = 0;
        
        if ($scope.selected_row != row_index) {
            $http({
                method : "GET",
                url : "http://localhost:8080/getPlaceDetails?placeid="+place_id
            }).then(function(response) {
                    console.log(response.data);
                    $scope.current_place_details = response.data.result;
                    if ($scope.current_place_details.hasOwnProperty("rating")) {
                        $scope.current_place_details.rating = $scope.current_place_details.rating*100/5;
                        $scope.current_place_details.rating +='%';
                    };

                    //check the if it's openning
                    if ($scope.current_place_details.hasOwnProperty("opening_hours")) {
                        current_local_date = parseInt($scope.current_place_details.local_date);
                        if (current_local_date ==0) {
                            current_local_date = 6;
                        }else {
                            current_local_date -=1;
                        }

                        if ($scope.current_place_details.opening_hours.open_now) {
                            $scope.openning_status="Open now: "+$scope.current_place_details.opening_hours.weekday_text[current_local_date].split(": ")[1];
                        }else {
                            $scope.openning_status="Closed";
                        }

                        $scope.weekly_openning_hours = [];
                        for (var i = 0; i < 7; i++) {
                            $scope.weekly_openning_hours.push($scope.current_place_details.opening_hours.weekday_text[(current_local_date+i)%7].split(": "));
                        };
                    };
            });
        };
        $scope.selected_row = row_index;
        $scope.hide_details = false;
    }
});
