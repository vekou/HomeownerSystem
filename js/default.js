$(document).ready(function(){
  $("#notif li a").click(function(){
    $(this).parent().hide(500);
  });
  
  $(document).on( "pagecontainerbeforeshow", function( event, ui ) {
        //setAsDataTable("#tblhomeownerlist","./homeownerlistss");
        //setAsDataTable("#tbluserlist","./userlistss");
  });
  //setAsDataTable("#tblhomeownerlist","./homeownerlistss");
//  setAsDataTable("#tbluserlist","./userlistss");
  //$("#tblhomeownerlist").on("draw.dt",function(){$("#dataTables_wrapper").enhanceWithin();});
//  $("#tblhomeownerlist").on( "draw.dt", function( e, settings, data ) {
//        //styleTable();
//  });
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

function generateHomeownerList()
{
    
}

function styleTable()
{
    $("a.paginate_button").attr("data-role","button").attr("data-inline","true");
    $("a.paginate_button.disabled").addClass("ui-state-disabled");
    $(".dataTables_wrapper").enhanceWithin(); 
}