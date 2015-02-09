monthnames=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

$(document).ready(function(){
  $("#notif li a").click(function(){
    $(this).parent().parent().hide(500);
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
        return $(a).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": url,
            "columnDefs":columnDef,
            "order":order,
            "retrieve":true
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

function getInterest(amt,m,i){
    ir=0;
    for(j=0;j<m;j++){
        ir += Math.pow(i,j)*amt;
        //window.alert(i+"^"+j+"*"+amt+"="+Math.pow(i,j)*amt);
    }
    return ir;
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function reCenter(a) {
    a.find('.ui-dialog-contain').css("position","absolute");
    a.find('.ui-dialog-contain').css("top", Math.max(0, (($(window).height()/2 - $('#box').find('.ui-dialog-contain').outerHeight()) / 2)) + "px");
    a.find('.ui-dialog-contain').css("left", Math.max(0, (($(window).width() - $('#box').find('.ui-dialog-contain').outerWidth()) / 2) + $(window).scrollLeft()) + "px");
}