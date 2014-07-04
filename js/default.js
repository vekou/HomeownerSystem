$(document).ready(function(){
  $("#notif li a").click(function(){
    $(this).parent().hide(500);
  });
  
  $("input.textamount").change(function(){
      var amttotal=0;
      $("input.textamount").each(function(){
          amttotal += parseFloat($(this).val());
          $(this).val(parseFloat($(this).val()).toFixed(2));
      });
      $("#paymentTotal").text(amttotal.toFixed(2));
      
  });
});

function setAsDataTable(a,url){
    return setAsDataTable(a,url,array());
}

function setAsDataTable(a,url,columnDef,order)
{
    if(!$.fn.dataTable.isDataTable(a))
    {
     //   window.alert($(a));
        return $(a).dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": url,
            "columnDefs":columnDef,
            "order":order
        });
       
        //window.alert($(".dataTables_filter input").data("events"));
    }
     
}

function styleTable()
{
    $("a.paginate_button").attr("data-role","button").attr("data-inline","true");
    $("a.paginate_button.disabled").addClass("ui-state-disabled");
    $(".dataTables_wrapper").enhanceWithin(); 
}