//PreProceso de carga de la web
setTimeout(function() {
    $('.se-pre-con ').fadeOut('slow');
}, 3500); // <-- time in milliseconds

//Rotate rangoLocalizacion when click
$(".rotate").click(function(){
 $(this).toggleClass("down")  ; 
})

//Show or hide UL
$(function () {
    var searchresults = $('.search-results');
    $('input:text').on('click', function (e) {
        e.stopPropagation();
        searchresults.toggle();
    });
    $(document).on('click', function (e) {
        searchresults.hide();

    });
});

//Modal open and HIDE
function show(target) {
    document.getElementById(target).style.display = 'block';
}

function hide(target) {
    document.getElementById(target).style.display = 'none';
}

//For geolocating user
$('#geolocating-container').hide(this);
  $('#button-geolocate').on('click', function(e) {
  e.preventDefault();
       $('#geolocating-container').show(this);
  $('#geolocating-container').show(0).delay(3000).hide(this);
});
