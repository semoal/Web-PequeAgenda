jQuery(document).ready(function() {
  SMIOinitialize();
  $('.smio_gmap_input').on('keypress', function (event) {
    if(event.which === 13){
      event.preventDefault();
      SMIOcodeAddress();
    }
  });
});

var SMIOgeocoder;
var SMIOmap;
var SMIOcircle = 0;
var SMIOmarker = 0;
function SMIOinitialize() {
  SMIOgeocoder = new google.maps.Geocoder();
  var latlng = new google.maps.LatLng(26.820553, 30.802498000000014);
  var mapOptions = {
    zoom: 3,
    center: latlng
  }
  SMIOmap = new google.maps.Map(document.getElementById('smio-gmap'), mapOptions);
}

function SMIOdrawCircle() {
  if(SMIOcircle != 0){
    SMIOcircle.setMap(null);
  }
  SMIOcircle = new google.maps.Circle({
    map: SMIOmap,
    radius: (1609.34 * $('#smio_gmap_radius').val()),
    fillColor: '#00AA00',
    strokeColor: '#fff',
    strokeOpacity: '.5',
    strokeWeight: '2'
  });
  SMIOcircle.bindTo('center', SMIOmarker, 'position');
}

function SMIOcodeAddress() {
  var address = $('#smio_gmap_address').val();
  SMIOgeocoder.geocode( { 'address': address}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      SMIOmap.setCenter(results[0].geometry.location);
      if(SMIOmarker != 0){
        SMIOmarker.setMap(null);
      }
      SMIOmarker = new google.maps.Marker({
          map: SMIOmap,
          draggable:true,
          position: results[0].geometry.location
      });
      $("#smio_latidude").val(results[0].geometry.location.lat());
      $("#smio_longitude").val(results[0].geometry.location.lng());
      SMIOdrawCircle();
      
      google.maps.event.addListener(SMIOmarker,'dragend',function(event){
        $('#smio_latidude').val(event.latLng.lat());
        $('#smio_longitude').val(event.latLng.lng());
        SMIOdrawCircle();
        SMIOgeocodePosition(SMIOmarker.getPosition());
      });
    } else {
      alert('Geocode was not successful for the following reason: ' + status);
    }
  });
}

function SMIOgeocodePosition(pos) {
  SMIOgeocoder.geocode({
    latLng: pos
  }, function(responses) {
    if (responses && responses.length > 0) {
      $('#smio_gmap_address').val(responses[0].formatted_address);
    } else {
      alert('Cannot determine address at this location.');
    }
  });
}