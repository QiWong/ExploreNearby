var app = angular.module('searchApp', ['ngAnimate', 'ngStorage']);

app.value('user', {current_lat : 0, current_lon : 0});
app.value("next_page_token", null);
app.constant('server_url', "http://akihellobeehw7.us-west-1.elasticbeanstalk.com");

app.controller('searchCtrl', function($scope,$localStorage,$http, $sce, user, next_page_token, server_url) {
	//Initialize the category list
    $scope.categoryList = ["Default","cafe","bakery","restaurant","beauty salon","casino","movie theater","lodging","airport","train station","subway station","bus station"];
    $scope.from = "here";
    $scope.distance = 10;
    $scope.current_page = 0;
    $scope.is_last_page = false;
    $scope.all_places = [];
    $scope.photo_urls = [];
    $scope.yelp_reviews = [];
    $scope.is_in_favorites = [];
    $scope.$storage = $localStorage.$default({
          favorite_place_ids: [],
          favorite_places: [],
    });
    var map = null;
    var marker = null;
    var directionsDisplay = null;
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

        $scope.current_page = 0;
        $scope.selected_row = -1;
        $scope.selected_page = -1;
        $scope.is_in_favorites = [];
        $scope.all_places.length = 0;

        if ($scope.from == "other") {
            //get the lat and lon through geocoding API
            $http({
                method : "GET",
                url : server_url+"/getNearybyPlaces/getLoc?address="+$('#enter_location').val()
                //url : "http://localhost:8080/getNearybyPlaces/getLoc?address="+$('#enter_location').val()
            }).then(function(response) {
                console.log("the location user input is");
                user.current_lat = response.data.lat;
                user.current_lon = response.data.lng;
                
                console.log(user.current_lat);
                console.log(user.current_lon);
            });
        };

        var search_url = server_url+"/getNearybyPlaces?keyword="+$scope.keyword;
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

            if (response.data.status == "OK") {
                places = response.data.results;
                let tmp_in_favorites = [];
                for (var i = places.length - 1; i >= 0; i--) {
                    tmp_in_favorites.push(false);
                };

                $scope.all_places.push(places);
                $scope.is_in_favorites.push(tmp_in_favorites);
            }else {
                /*===========Error, can't return any place===========*/
            }
            
        });

        $scope.searchPlaces=true;
    }

    $scope.changeFavorite = function(place_id, index){
        
        if (!$scope.$storage.favorite_place_ids.includes(place_id)) {
            /*=============If this place is not in the list=========*/
            /*============Add this place to the favorite list============*/
            $scope.$storage.favorite_place_ids.push(place_id);
            $scope.$storage.favorite_places.push($scope.all_places[$scope.current_page][index]);
            console.log($scope.$storage.favorite_places);
        }else{
            /*============Remove this place from the favorite list============*/
            let index = -1;
            for (var i = $scope.$storage.favorite_place_ids.length - 1; i >= 0; i--) {
                if ($scope.$storage.favorite_place_ids[i] == place_id) {
                    index = i;
                    break;
                };
            };
            if (index > -1) {
                $scope.$storage.favorite_place_ids.splice(index, 1);
            };
            index = -1;
            for (var i = $scope.$storage.favorite_places.length - 1; i >= 0; i--) {
                if ($scope.$storage.favorite_places[i].place_id == place_id) {
                    index = i;
                    break;
                };
            };
            if (index > -1) {
                $scope.$storage.favorite_places.splice(index, 1);
            };
        }
    }

    $scope.nextSearchResults = function() {
        var next_page_url = server_url+"/getNearybyPlaces/getNextPageResults?token="+next_page_token;
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

                if (response.data.status == "OK") {
                    places = response.data.results;
                    
                    let tmp_in_favorites = [];
                    for (var i = places.length - 1; i >= 0; i--) {
                        tmp_in_favorites.push(false);
                    };

                    $scope.is_in_favorites.push(tmp_in_favorites);
                    $scope.all_places.push(places);
                    $scope.current_page +=1;
                }else {
                    /*===========Request failed===========*/

                }

                places = response.data.results;
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

    function timeConverter(UNIX_timestamp){
        var a = new Date(UNIX_timestamp * 1000);
        
        var year = a.getFullYear();
        var month = a.getMonth();
        if (month<12) {
            month="0"+month;
        };
        var date = a.getDate();
        if (date<10) {
            date="0"+date;
        };
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        var time = year+"-"+month + '-' +date+ ' ' + hour + ':' + min + ':' + sec ;
        return time;
    }

    $scope.showDetails = function(row_index,place_id) {
        
        var current_local_date = 0;
        
        if ($scope.selected_row != row_index || $scope.selected_page != $scope.current_page) {
            $http({
                method : "GET",
                url : server_url+"/getPlaceDetails?placeid="+place_id
            }).then(function(response) {
                    console.log(response.data);
                    $scope.current_place_details = response.data.result;
                    if ($scope.current_place_details.hasOwnProperty("rating")) {
                        $scope.current_place_details.rating = $scope.current_place_details.rating*100/5;
                        $scope.current_place_details.rating +='%';
                    };
                    //rewrite the time
                    //from timestamp 1518035369 to time string like 2018-01-07 12:29:29
                    //and also change the rating
                    if ($scope.current_place_details.hasOwnProperty("reviews")) {
                        for (var i = $scope.current_place_details.reviews.length - 1; i >= 0; i--) {
                            $scope.current_place_details.reviews[i].time = timeConverter($scope.current_place_details.reviews[i].time);
                            /*====================add a property called order=============*/
                            $scope.current_place_details.reviews[i].order = i;                        
                            if ($scope.current_place_details.reviews[i].hasOwnProperty("rating")) {
                                $scope.current_place_details.reviews[i].rating = $scope.current_place_details.reviews[i].rating*100/5;
                                $scope.current_place_details.reviews[i].rating +='%';
                            };
                        };
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

                    //get yelp reviews
                    $scope.yelp_reviews = [];
                    var get_yelp_review_url = server_url+"/getYelpReviews/match/best?name="+$scope.all_places[$scope.current_page][row_index]['name'];
                    get_yelp_review_url+="&country=US";

                    var addr = $scope.current_place_details.formatted_address.split(", ");
                    var tmp_len = addr.length;
                    var city = "";
                    var state="";
                    var addr1="";
                    if (tmp_len >= 3) {
                        state = addr[tmp_len-2].split(" ")[0];
                        city = addr[tmp_len-3];
                        if (tmp_len >= 4) {
                            addr1 = addr[tmp_len-4];
                        };
                    };

                    get_yelp_review_url+="&city="+city+"&state="+state+"&addr="+addr1;
                    console.log("yelp");
                    console.log(get_yelp_review_url);

                    var yelp_business_id;
                    $http({
                        method : "GET",
                        url : get_yelp_review_url
                    }).then(function(response) {
                        console.log(response.data);
                        if (response.data.businesses.length > 0) {
                            yelp_business_id = response.data.businesses[0].id;
                            $http({
                                method : "GET",
                                url :  server_url+"/getYelpReviews/reviews?id="+yelp_business_id
                            }).then(function(response) {
                                console.log(response.data);
                                if (response.data.total > 0) {
                                    $scope.yelp_reviews = response.data.reviews;
                                    console.log($scope.yelp_reviews);
                                    for (var i = $scope.yelp_reviews.length - 1; i >= 0; i--) {
                                        $scope.yelp_reviews[i].rating = $scope.yelp_reviews[i].rating*100/5;
                                        $scope.yelp_reviews[i].rating +='%';
                                        $scope.yelp_reviews[i].order = i;
                                    };
                                }else {
                                    //Cannot find a matched place
                                    
                                }
                            });
                        }else {
                            //Cannot find a matched place
                            
                        }
                    });
            });
            
            //get all the photo urls
            $scope.photo_urls=[];
            var details_container =document.getElementById("uselessMapContainer");
            var service = new google.maps.places.PlacesService(details_container);
            service.getDetails({
              placeId: place_id
            }, function(place, status) {
                if (place!=null && place.hasOwnProperty("geometry")) {

                    map = new google.maps.Map(document.getElementById("map-div"),{
                        zoom: 15,
                        center: place.geometry.location
                    });

                    marker = new google.maps.Marker({
                        position: place.geometry.location,
                        map: map
                    });

                    document.getElementById("route-details-panel").innerHTML = '';
                    directionsDisplay = new google.maps.DirectionsRenderer({
                        draggable: true,
                        map: map,
                        panel: document.getElementById('route-details-panel')
                    });

                    directionsDisplay.setMap(map);
                };

                if (place.hasOwnProperty("photos")) {
                    console.log("phootoooooo");

                    for (var i = 0; i < place.photos.length; i++) {
                        $scope.photo_urls.push(place.photos[i].getUrl({'maxWidth': 750, 'maxHeight': 1500}));
                    }
                }

            });

           
        };


        $scope.selected_row = row_index;
        $scope.selected_page = $scope.current_page;
        /* get details through google js api to get photos urls*/
        
        $scope.hide_details = false;
    }


    //Comparer Function  
    function GetSortOrder(prop) {  
        return function(a, b) {  
            if (a[prop] > b[prop]) {  
                return 1;  
            } else if (a[prop] < b[prop]) {  
                return -1;  
            }  
            return 0;  
        }  
    }

    $scope.sortReviews = function(option) {
        switch (option) {
            case 1:
                if ($scope.current_place_details.hasOwnProperty("reviews")) {
                    $scope.current_place_details.reviews.sort(GetSortOrder("order"));
                }
                if ($scope.yelp_reviews.length>0) {
                    $scope.yelp_reviews.sort(GetSortOrder("order"));
                };
                $scope.reviews_option_text2="Default Order";
                break;
            case 2:
                if ($scope.current_place_details.hasOwnProperty("reviews")) {
                    $scope.current_place_details.reviews.sort(GetSortOrder("rating"));
                }
                if ($scope.yelp_reviews.length>0) {
                    $scope.yelp_reviews.sort(GetSortOrder("rating"));
                };
                $scope.reviews_option_text2="Highest Rating";
                break;
            case 3:
                if ($scope.current_place_details.hasOwnProperty("reviews")) {
                    $scope.current_place_details.reviews.sort(GetSortOrder("rating"));
                    $scope.current_place_details.reviews.reverse();
                }
                if ($scope.yelp_reviews.length>0) {
                    $scope.yelp_reviews.sort(GetSortOrder("rating"));
                    $scope.yelp_reviews.reverse();
                };
                $scope.reviews_option_text2="Lowest Rating";
                break;
            case 4:
                if ($scope.current_place_details.hasOwnProperty("reviews")) {
                    $scope.current_place_details.reviews.sort(GetSortOrder("time"));
                    $scope.current_place_details.reviews.reverse();
                }
                if ($scope.yelp_reviews.length>0) {
                    $scope.yelp_reviews.sort(GetSortOrder("time_created"));
                    $scope.yelp_reviews.reverse();
                };             
                $scope.reviews_option_text2="Most Recent";
                break;
            case 5:
                if ($scope.current_place_details.hasOwnProperty("reviews")) {
                    $scope.current_place_details.reviews.sort(GetSortOrder("time"));
                }
                if ($scope.yelp_reviews.length>0) {
                    $scope.yelp_reviews.sort(GetSortOrder("time_created"));
                };
                $scope.reviews_option_text2="Least Recent";
                break;
        }
    }

    $scope.switchReviews = function(reviews_option){
        
        if (reviews_option =='G') {
            $scope.reviews_option_text = "Google Reviews";
            $scope.show_google_reviews = true;
        }else {
            $scope.reviews_option_text = "Yelp Reviews";
            $scope.show_google_reviews = false;
        }
    }

    $scope.showRoutes = function(){
        marker.setMap(null);

        var directionsService = new google.maps.DirectionsService;
        let origin = angular.element('#map_location_input')[0].value;
        if (origin == "") {
            origin = {
                lat: user.current_lat,
                lng: user.current_lon
            };
        };
        let travelMode;
        console.log($scope.travel_mode);
        switch($scope.travel_mode) {
          case 'Driving':
            travelMode = google.maps.TravelMode.DRIVING;
            break;
          case 'Bicycling':
            travelMode = google.maps.TravelMode.BICYCLING;
            break;
          case 'Transit':
            travelMode = google.maps.TravelMode.TRANSIT;
            break;
          case 'Walking':
            travelMode = google.maps.TravelMode.WALKING;
            break;
        }

        directionsService.route({
            origin: origin,
            destination: $scope.all_places[$scope.current_page][$scope.selected_row]['geometry']['location'],
            provideRouteAlternatives: true,
            travelMode: travelMode
        }, (response, status) => {
            if (status === google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
            }   else {
                alert('Could not display directions due to: ' + status);
            }
        });

      }

      $scope.showMapPanel = function(){
        var panorama = new google.maps.StreetViewPanorama(
            document.getElementById('street-view'),{
                position: $scope.all_places[$scope.current_page][$scope.selected_row].geometry.location,
                pov: {heading: 165, pitch: 0},
                zoom: 1
        });

        panorama.setVisible(true);  
      }
});
