<div id="smio-realtime-gmap"></div>
<script>
var SMIOrealmap;
var SMIOlastactive = 0;
function SMIOinitializeRealMap() {
  var latlng = new google.maps.LatLng(26.820553, 30.802498000000014);
  var mapOptions = {
    zoom: 2,
    center: latlng
  }
  SMIOrealmap = new google.maps.Map(document.getElementById('smio-realtime-gmap'), mapOptions);
  SMIOloadNewDevies();
}

function SMIOloadNewDevies(){
  $.get("<?php echo $page_url;?>", {"smpushlive": "1", "lastupdate":SMIOlastactive}
  ,function(data){
    try{
      var devices = JSON.parse(data);
      for(var i=0;i<devices.length;i++){
        var myLatlng = new google.maps.LatLng(devices[i]["latidude"], devices[i]["longitude"]);
        new google.maps.Marker({
            map: SMIOrealmap,
            label: devices[i]["devicetype"].substring(0, 1),
            title: devices[i]["deviceinfo"],
            position: myLatlng
        });
      }
      SMIOlastactive = devices[0]["lastupdate"];
    } catch(e){
      SMIOlastactive = data;
    }
    setTimeout(function(){SMIOloadNewDevies();}, 5000);
  });
}

SMIOinitializeRealMap();
</script>