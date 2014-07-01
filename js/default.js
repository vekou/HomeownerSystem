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