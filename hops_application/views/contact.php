<input type='text' id='start'/>
<input type='text' id='end'/>

<div id="map" style="width:100%; height:500px;"></div>
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>


<script type="text/javascript">
	$(document).ready(function () {
    initializeLocation();
});

// This example displays an address form, using the autocomplete feature
// of the Google Places API to help users fill in the information.

var start, end,pointA,pointB;



function initializeLocation() {

	var directionsService = new google.maps.DirectionsService;
    var directionsDisplay = new google.maps.DirectionsRenderer;

	var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 7,
          center: {lat: 41.85, lng: -87.65}
        });
        directionsDisplay.setMap(map);


    // Create the autocomplete object, restricting the search
    // to geographical location types.
    start = new google.maps.places.Autocomplete((document.getElementById('start')),{});
    end = new google.maps.places.Autocomplete((document.getElementById('end')),{});


    

	google.maps.event.addListener(start, 'place_changed', function () {
      	var results = start.getPlace();
      	var pickup_lat = results.geometry.location.lat();
       	var pickup_long = results.geometry.location.lng();
       	pointA = new google.maps.LatLng(pickup_lat, pickup_long);
        calculateAndDisplayRoute1(directionsService, directionsDisplay);
    });

    google.maps.event.addListener(end, 'place_changed', function () {
        var results = end.getPlace();
      	var drop_lat = results.geometry.location.lat();
       	var drop_long = results.geometry.location.lng();
       	pointB = new google.maps.LatLng(drop_lat, drop_long);
        calculateAndDisplayRoute1(directionsService, directionsDisplay);
    });


   
    // var onChangeHandler = function() {
    //  	calculateAndDisplayRoute1(directionsService, directionsDisplay);
    // };
    // document.getElementById('start').addEventListener('change', onChangeHandler);
    // document.getElementById('end').addEventListener('change', onChangeHandler);
}




function calculateAndDisplayRoute(directionsService, directionsDisplay) {

	directionsService.route({
	  origin: document.getElementById('start').value,
	  destination: document.getElementById('end').value,
	  travelMode: 'DRIVING'
	}, function(response, status) {



	  if (status === 'OK') {
	    directionsDisplay.setDirections(response);
	  } else {
	    window.alert('Directions request failed due to ' + status);
	  }
	});
}

function calculateAndDisplayRoute1(directionsService, directionsDisplay) {

	if(pointA!='' && pointB!='' && $('#start').val()!='' && $('#end').val()) {
		directionsService.route({
	        origin: pointA,
	        destination: pointB,
	        avoidTolls: true,
	        avoidHighways: false,
	        travelMode: google.maps.TravelMode.DRIVING
	    }, function (response, status) {
	        if (status == google.maps.DirectionsStatus.OK) {
	            directionsDisplay.setDirections(response);
	        } else {
	            window.alert('Directions request failed due to ' + status);
                directionsDisplay.setDirections({ routes: [] });
	        }
	    });
	}    
}



</script>

  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBMdeyofANUfzrounhUMtZGakvFwpwjfqU&libraries=places" async defer></script>
