$(document).ready(function(){
  $("#notif li a").click(function(){
    $(this).parent().hide(500);
  });
  
  $(document).on( "pagecontainerload", function( event, ui ) {
        $("#tblhomeownerlist").dataTable();
  });
  $("#tblhomeownerlist").dataTable();
});