$(document).ready(function(){
  $("#notif li a").click(function(){
    $(this).parent().hide(500);
  });
  
  $(document).on( "pagecontainerbeforeshow", function( event, ui ) {
        setAsDataTable("#tblhomeownerlist","./homeownerlistss");
        setAsDataTable("#tbluserlist","./userlistss");
  });
  setAsDataTable("#tblhomeownerlist","./homeownerlistss");
  setAsDataTable("#tbluserlist","./userlistss");
  //$("#tblhomeownerlist").on("draw.dt",function(){$("#dataTables_wrapper").enhanceWithin();});
  $(document).on( "stateLoaded.dt", function( e, settings, data ) {
      window.alert("t");
       $("a.paginate_button").prop("data-role","button");
       $(".dataTables_wrapper").enhanceWithin(); 
  });
});

function setAsDataTable(a,url)
{
    if(!$.fn.dataTable.isDataTable(a))
    {
     //   window.alert($(a));
        $(a).dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": url
        });
       
        //window.alert($(".dataTables_filter input").data("events"));
    }
     
}