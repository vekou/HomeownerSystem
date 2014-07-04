<?php
//Initialize script 
require_once 'functions.php';
session_start();
global $systempage;
$systempage=(is_null(filter_input(INPUT_GET, "page"))?"dashboard":filter_input(INPUT_GET, "page"));

if(!is_null($systempage))
{
    switch($systempage)
    {
        case "login":
            global $conn;
            dbConnect();
            $stmt=$conn->prepare("SELECT id, fullname, username, permission FROM user WHERE username=? AND password=?");
            if($stmt === false) {
                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
            }
            $postusername=filter_input(INPUT_POST, "uid");
            $postpassword=md5(filter_input(INPUT_POST, "password"));
            $stmt->bind_param('ss',$postusername,$postpassword);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows==1)
            {
                $stmt->bind_result($_SESSION['uid'],$_SESSION['fullname'],$_SESSION['username'], $_SESSION['permission']);
                while($stmt->fetch()){}
                $_SESSION['permlist']=  parsePermission($_SESSION['permission']);
                //writeLog($_SESSION["fullname"]."(".$_SESSION["uid"].") logged in to the system.");
            }
            else
            {
                setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
            }
            $stmt->close();
            dbClose();
            header("Location: ./");
            break;
        case "logout":
            session_destroy();
            setNotification("Successfully logged out.");
            header("Location: ./");
            break;
        case "addhomeowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO homeowner(lastname,firstname,middlename,contactno,email,user) VALUES(?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $plastname=filter_input(INPUT_POST, "plastname");
                $pfirstname=filter_input(INPUT_POST, "pfirstname");
                $pmiddlename=filter_input(INPUT_POST, "pmiddlename");
                $pcontactno=filter_input(INPUT_POST, "pcontactno");
                $pemail=filter_input(INPUT_POST, "pemail");
                $stmt->bind_param('sssssi',$plastname,$pfirstname,$pmiddlename,$pcontactno,$pemail,$userid);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("$plastname, $pfirstname ".substr($pmiddlename, 0, 1).". has been added.");
                dbClose();
                header("Location: ./homeowners");
            }
            else{header("Location: ./");}
            break;
        case "updatehomeowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE homeowner SET `lastname`=?,`firstname`=?,`middlename`=?,`contactno`=?,`email`=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=filter_input(INPUT_POST, "uid");
                $plastname=filter_input(INPUT_POST, "plastname");
                $pfirstname=filter_input(INPUT_POST, "pfirstname");
                $pmiddlename=filter_input(INPUT_POST, "pmiddlename");
                $pcontactno=filter_input(INPUT_POST, "pcontactno");
                $pemail=filter_input(INPUT_POST, "pemail");
                $stmt->bind_param('sssssi',$plastname,$pfirstname,$pmiddlename,$pcontactno,$pemail,$userid);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("$plastname, $pfirstname ".substr($pmiddlename, 0, 1).". has been updated.");
                dbClose();
                header("Location: ./homeowner?id=".$userid);
            }
            else{header("Location: ./");}
            break;
        case "users":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <a href="#addUserForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add User</a>
                <div data-role="popup" id="addUserForm" data-dismissible="false" data-overlay-theme="b">
                  <header data-role="header">
                    <h1>Add User</h1>
                    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                  </header>
                  <div role="main" class="ui-content">
                    <form action="adduser" method="post" data-ajax="false">
                        <label for="plastname">Last Name</label>
                        <input id="plastname" name="plastname" type="text"/>
                        <label for="pfirstname">First Name</label>
                        <input id="pfirstname" name="pfirstname" type="text"/>
                        <label for="pmiddlename">Middle Name</label>
                        <input id="pmiddlename" name="pmiddlename" type="text"/>
                        <label for="pcontactno">Contact Number</label>
                        <input id="pcontactno" name="pcontactno" type="tel"/>
                        <label for="pemail">Email Address</label>
                        <input id="pemail" name="pemail" type="email"/>
                      <input type="submit" value="Receive" data-icon="arrow-d"/>
                    </form>
                  </div>
                </div>
                <hr/>
                <table id="tbluserlist" class="table table-striped table-bordered dt stripe">
                    <thead>
                        <tr>
<!--                            <th>ID</th>-->
                            <th>Username</th>
                            <th>Fullname</th>
<!--                            <th>Options</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <script type="text/javascript">
                    $(document).ready(function(){setAsDataTable("*#tbluserlist","./userlistss");});
                    
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "homeowner":
            if((!is_null(filter_input(INPUT_GET, "id")))&&(isLoggedIn()))
            {
                displayHTMLPageHeader();

                global $conn;
                dbConnect();
                $uid = filter_input(INPUT_GET, "id");

                $stmt=$conn->prepare("SELECT * FROM homeowner WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }

                $postusername=filter_input(INPUT_POST, "uid");
                $postpassword=md5(filter_input(INPUT_POST, "password"));
                $stmt->bind_param('i',$uid);
                $stmt->execute();
                $stmt->store_result();

                if($stmt->num_rows > 0)
                {
                    $stmt->bind_result($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded);
                    while($stmt->fetch()){ ?>
                        <a href="#addHomeowner" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Homeowner</a>
                        <h1><?php echo "$lastname, $firstname " . substr($middlename, 0, 1) . "."; ?></h1>
                        <hr/>
                        
                        <?php displayHomeownerForm("./updatehomeowner",$lastname,$firstname,$middlename,$contactno,$email,$id); ?>
                        
                        <ul data-role="listview" data-inset="true" id="homeownercontactinfo">
                            <li data-role="list-divider">Contact Information</li>
                            <li data-icon="phone"><a href="tel:<?php echo $contactno; ?>"><?php echo $contactno; ?></a></li>
                            <li data-icon="mail"><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
                        </ul>                        
                        
                        <div>
                            <ul data-role="listview" data-inset="true">
                                <li id="paymentsTab" data-role="collapsible" data-inset="false" data-theme="d">
                                    <h2 class="ui-body-d">Payments</h2>
                                    <div>
                                        <a href="#paymentform" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" id="addPaymentBtns" data-theme="a">Add Payment</a>
                                        <table id="tblpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                            <thead>
                                                <tr>
                                                    <th data-priority="1">Date</th>
                                                    <th data-priority="3">OR Number</th>
                                                    <th data-priority="4">Paid by</th>
                                                    <th data-priority="2">Amount</th>
                                                    <th data-priority="2">ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <div data-role="popup" id="popupReceipt" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                                            <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
<!--                                            <iframe id="paymentdetailsframe" src="" width="640" height="480" seamless=""></iframe>-->
                                        </div>
                                        <script type="text/javascript">
                                            $(document).on("pagecreate",function(){
                                                try{
                                                    pl = setAsDataTable("#tblpaymentlist","./paymentlistss?id=<?php echo $uid; ?>",[{"targets":[4],"visible":false,"searchable":false}],[[0,"desc"]]);
                                                    
                                                    
                                                    var plapi=pl.api();

                                                    $("#tblpaymentlist").on( "draw.dt", function() {
                                                        $("a.paymentdetailslink").click(function(){
                                                            changeIFrameSrc($(this)[0].dataset.ledgerid);
                                                        });
                                                    });

                                                    $("#tblpaymentlist").on( "init.dt", function() {
                                                        $("#tblpaymentlist_wrapper").enhanceWithin();
                                                        $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                                        $("#tblpaymentlist_filter input").on("change",function(){
                                                            plapi.search($(this).val()).draw();
                                                        });
                                                    });
                                                }catch(e){}
                                                $("#popupReceipt").on({popupafterclose:function(){
                                                  $("#paymentdetailsframe").remove();
                                                }});
                                                $("#popupLot").on({popupafterclose:function(){
                                                  $("#lotdetailsframe").remove();
                                                }});

                                                function changeIFrameSrc(lid){
                                                    //$("#paymentdetailsframe").attr("src","./paymentdetails?id=" + lid);
                                                    $("#popupReceipt").append('<iframe id="paymentdetailsframe" src="./paymentdetails?id='+lid+'" width="640" height="480" seamless=""></iframe>');
                                                }
                                            });
                                        </script>
                                    </div>
                                </li>
                                <li id="lotsTab" data-role="collapsible" data-inset="false" data-theme="d">
                                    <h2>Registered Lots</h2>
                                    <a href="#addLotForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add Lot</a>
                                    <?php displayLotForm($uid); ?>
                                    <div data-role="popup" id="popupLot" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                                        <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
<!--                                            <iframe id="paymentdetailsframe" src="" width="640" height="480" seamless=""></iframe>-->
                                    </div>
                                    <ul data-role="listview" data-inset="true" data-theme="a">
                                    <?php
                                        $lotlist=array();
                                        $stmt2=$conn->prepare("SELECT * FROM lot WHERE homeowner=?");
                                        if($stmt2 === false) {
                                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                        }
                                        $householdid=filter_input(INPUT_GET, "id");
                                        $stmt2->bind_param('i',$householdid);
                                        $stmt2->execute();
                                        $stmt2->store_result();
                                        $lotcount=$stmt2->num_rows;

                                        if($lotcount>0)
                                        {

                                            $stmt2->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $caretaker, $dateadded, $userid, $active);

                                            while($stmt2->fetch()){
                                                echo "<li data-role='collapsible' data-collapsed-icon='carat-r' data-expanded-icon='carat-u' data-inset='false'>"
                                                    . "<h2>$code ($housenumber Lot $lot Block $block $street Phase $phase)</h2>"
                                                    . "<table class='tbldata'><tr><th>Address</th><td>$housenumber Lot $lot Block $block $street Phase $phase</td></tr>"
                                                    . "<tr><th>Acquisition Date</th><td>$dateacquired</td></tr>"
                                                    . "<tr><th>Lot Size</th><td>$lotsize sq. m</td></tr>"
                                                    . "<tr><th>Household size</th><td>$numberinhousehold</td></tr></table>"
                                                    . "<div data-role='controlgroup' data-type='horizontal' data-mini='true'><a href='#' data-role='button' data-icon='edit' data-iconpos='notext'>Edit Lot</a><a href='#' data-role='button' data-icon='delete' data-iconpos='notext'>Delete Lot</a></div>"
                                                    . "</li>";
                                                $lotinfo=array();
                                                $lotinfo["id"]=$id;
                                                $lotinfo["lotsize"]=$lotsize;
                                                $lotinfo["lotcode"]=$code;
                                                $lotinfo["address"]=$housenumber." Lot ".$lot." Block ".$block." ".$street." Phase ".$phase;
                                                $lotlist[]=$lotinfo;
                                            }
                                        }
                                        else
                                        {
                                            echo "<li><em>No lot registered.</em></li>";
                                        }
                                        $stmt2->free_result();
                                        $stmt2->close();
                                    ?>
                                    </ul>
                                </li>
                            </ul>
                            <div data-role="popup" data-dismissible="false" id="paymentform" data-overlay-theme="b">
                                <header data-role="header">
                                <h1>Add Payment</h1>
                                <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <section class="ui-content">
                                    <?php if($lotcount>0): ?>
                                        <form method="post" action="./addpayment" data-ajax="false">
                                            <label for="paymentdate">Payment Date</label>
                                            <input type="date" id="paymentdate" name="paymentdate" data-role="date" value="<?php echo date("m/d/Y"); ?>" <?php echo ($lotcount<=0)?"disabled='true'":""; ?> />
                                            <label for="ornumber">OR Number</label>
                                            <input type="text" id="ornumber" name="ornumber" <?php echo ($lotcount<=0)?"disabled='true'":""; ?>/>
                                            <label for="payee">Paid by</label>
                                            <input type="text" id="payee" name="payee" <?php echo ($lotcount<=0)?"disabled='true'":""; ?>/>
                                            <input type="hidden" name="homeowner" value="<?php echo $uid; ?>"/>
                                        
                                            <div class="ui-bar ui-bar-a">
                                            <h4>Amount</h4>
                                            </div>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>Lot Code</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($lotlist as $loti): ?>
                                                    <tr>
                                                        <th><label for="lotamt<?php echo $loti["id"]; ?>" title="<?php echo $loti["address"]; ?>"><?php echo $loti["lotcode"]; ?></label></th>
                                                        <td><input type="month" name="amt[<?php echo $loti["id"]; ?>][start]" id="lotstart<?php echo $loti["id"]; ?>"/></td>
                                                        <td><input type="month" name="amt[<?php echo $loti["id"]; ?>][end]" id="lotend<?php echo $loti["id"]; ?>"/></td>
                                                        <td><input type="number" step="0.01" name="amt[<?php echo $loti["id"]; ?>][amount]" id="lotamt<?php echo $loti["id"]; ?>" value="0.00" class="textamount"/></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                    <tr>
                                                        <th>Total</th>
                                                        <th id="paymentTotal" class="textamount" colspan="3">0.00</th>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="submit" value="Submit" <?php echo ($lotcount<=0)?"disabled='true'":""; ?>/>
                                        </form>
                                    <?php else: ?>
                                        <div>There is no lot registered to this homeowner.</div>
                                    <?php endif; ?>
                                </section>
                            </div>
                        </div>

                    <?php }
                }
                else
                {
                    setNotification("No such user exists.",DT_NOTIF_ERROR);
                    $stmt->free_result();
                    $stmt->close();
                    dbClose();
                    header("Location: ./homeowners");
                }

                $stmt->free_result();
                dbClose();

                displayHTMLPageFooter();
            }
            else{
            header("Location: ./homeowners");
            }
            break;
        case "homeowners":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <a href="#addHomeowner" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Homeowner</a>
                <form action="addhomeowner" method="post" data-ajax="false">
                    <?php displayHomeownerForm(); ?>
                </form>
                <hr/>
                <table id="tblhomeownerlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                    <thead>
                        <tr>
                            <th >Name</th>
                            <th data-priority="3">Contact Number</th>
                            <th data-priority="4">Email Address</th>
                            <th data-priority="1">Month Paid</th>
                            <th data-priority="2">Payment Date</th>
                            <th data-priority="2">Amount</th>
                            <th data-priority="2">Option</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <script type="text/javascript">
                    //var hol;
                    $(document).on("pagecreate",function(event, ui){
                        try{
                            hol = setAsDataTable("#tblhomeownerlist","./homeownerlistss",[{"targets":[6],"visible":false,"searchable":false}],[[0,"asc"]]);
                            var holapi=hol.api();

                            $("#tblhomeownerlist").on( "draw.dt", function() {
                                $("a.tblhomeownerlistbtn").button();
                            });

                            $("#tblhomeownerlist").on( "init.dt", function() {
                                $("#tblhomeownerlist_wrapper").enhanceWithin();
                                $("#tblhomeownerlist_filter input").on("change",function(){
                                    holapi.search($(this).val()).draw();
                                });
                            });
                        }catch(e){}
                    });
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "homeownerlistss":
            if(isLoggedIn())
            {
                $table = 'homeowner a LEFT JOIN ledger b ON a.id=b.homeowner LEFT JOIN ledgeritem c ON b.id=c.id ';
                $primaryKey = 'id';
                $columns = array(
                    //array('db'=>'id','dt'=>0),
                    array('db'=>'CONCAT(a.lastname,", ",a.firstname," ",SUBSTR(a.middlename,1,1),".")','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"name","aliascols"=>"a.lastname,a.firstname,a.middlename"),
                    array('db'=>'a.contactno','dt'=>1,"alias"=>"contactno", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.email','dt'=>2,"alias"=>"email", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'MAX(c.enddate)','dt'=>3,"alias"=>"latestpayment","aliascols"=>"c.enddate" ,'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".(($d=="")?"":date('M-Y',strtotime($d)))."</a>";}),
                    array('db'=>'b.paymentdate','dt'=>4,"alias"=>"paymentdate", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'c.amount','dt'=>5,"alias"=>"amount", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".(($d>0)?number_format($d,2):"")."</a>";}),
                    array('db'=>'a.id','dt'=>6,"alias"=>"uid","aliascols"=>"a.id", 'formatter'=>function($d,$row){return "<a href='#' class='tblhomeownerlistbtn' data-role='button' data-iconpos='notext' data-icon='edit'>Edit</a>";})
                );
                $addwhere="";
                $group="GROUP BY a.id";
                $counttable="homeowner";
                $countwhere="";
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere));
// <editor-fold defaultstate="collapsed" desc="Manual generation of json">


//                global $conn;
//                dbConnect();
//                $get_length = filter_input(INPUT_GET, "length");
//                $get_start = filter_input(INPUT_GET, "start");
//                $get_draw = filter_input(INPUT_GET, "draw");
//                $get_search = filter_input(INPUT_GET, "searc[value]");
//                
//                $stmt=$conn->prepare("SELECT * FROM homeowner LIMIT ?,?");
//                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                $postusername=filter_input(INPUT_POST, "uid");
//                $postpassword=md5(filter_input(INPUT_POST, "password"));
//                $stmt->bind_param('ii',$get_start,$get_length);
//                $stmt->execute();
//                $stmt->store_result();
//                $json = array(
//                    "draw" => $get_draw,
//                    "recordsFiltered" => $stmt->num_rows
//                );
//                $jsondata = array();
//                
//                if($stmt->num_rows > 0)
//                {
//                    $stmt->bind_result($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded);
//                    while($stmt->fetch()){
//                        $jsondata[]=array($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded);
////                        $jsondata[] = array(
////                            "id"=>$id,
////                            "lastname"=>$lastname,
////                            "firstname"=>$firstname,
////                            "middlename"=>$middlename,
////                            "contactno"=>$contactno,
////                            "email"=>$email
////                        );
//                    }
//                    $json["data"]=$jsondata;
//                }
//                else
//                {
//                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
//                }
//                
//                $stmt->free_result();
//                $stmt=$conn->prepare("SELECT COUNT(*) FROM homeowner");
//                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                $stmt->execute();
//                $stmt->bind_result($cnttotal);
//                while($stmt->fetch()){
//                    $json["recordsTotal"]=$cnttotal;
//                }
//                
//                $stmt->free_result();
//                $stmt->close();
//                dbClose();
//                echo json_encode($json);
                // </editor-fold>
            }
            break;
        case "userlistss":
            if(isLoggedIn())
            {
                $table = 'user';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'username','dt'=>0, 'formatter'=>function($d,$row){return "<a href='#' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'fullname','dt'=>1, 'formatter'=>function($d,$row){return "<a href='#' class='tablecelllink'>".$d."</a>";})
                );
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns));
            }
            break;
        case "lots":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <a href="#addLotForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Lot</a>
                <form action="./addlot" method="post" data-ajax="false">
                    <?php displayLotForm(); ?>
                </form>
                <hr/>
                <table id="tbllotlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                    <thead>
                        <tr>
                            <th data-priority="5">ID</th>
                            <th data-priority="1">Lot Code</th>
                            <th data-priority="2">Address</th>
                            <th data-priority="4">Lot Size</th>
                            <th data-priority="3">Owner</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
                <script type="text/javascript">
                    //var hol;
                    $(document).on("pagecreate",function(event, ui){
                        try{
                            hol = setAsDataTable("#tbllotlist","./lotlistss",[{"targets":[0],"visible":false,"searchable":false}],[[0,"asc"]]);
                            var holapi=hol.api();

                            $("#tbllotlist").on( "draw.dt", function() {
                                $("a.tblhomeownerlistbtn").button();
                            });

                            $("#tbllotlist").on( "init.dt", function() {
                                $("#tbllotlist_wrapper").enhanceWithin();
                                $("#tbllotlist_filter input").on("change",function(){
                                    holapi.search($(this).val()).draw();
                                });
                            });
                        }catch(e){}
                    });
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "addlot":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO lot(code, homeowner, dateacquired, lotsize, housenumber, street, lot, block, phase, numberinhousehold, user) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $homeowner=filter_input(INPUT_POST, "homeowner");
                $code=filter_input(INPUT_POST, "code");
                $dateacquired=filter_input(INPUT_POST, "dateacquired");
                $lotsize=filter_input(INPUT_POST, "lotsize");
                $housenumber=filter_input(INPUT_POST, "housenumber");
                $street=filter_input(INPUT_POST, "street");
                $lot=filter_input(INPUT_POST, "lot");
                $block=filter_input(INPUT_POST, "block");
                $phase=filter_input(INPUT_POST, "phase");
                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
                
                $stmt->bind_param('sisdsssssii',$code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $userid);
                $stmt->execute();
                $stmt->close();
                
                setNotification("Lot $code has been added.");
                dbClose();
                header("Location: ./homeowner?id=".$homeowner);
            }
            else{header("Location: ./");}
            break;
        case "lotlistss":
            if(isLoggedIn())
            {
                $table = 'lot a LEFT JOIN homeowner b ON a.homeowner=b.id';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"uid", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.code','dt'=>1,"alias"=>"code", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'CONCAT(a.housenumber," Lot",a.lot," Block",a.block," ",a.street," Phase",a.phase)','dt'=>2, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"address","aliascols"=>"a.housenumber,a.lot,a.block,a.street,a.phase"),
                    array('db'=>'a.lotsize','dt'=>3,"alias"=>"lotsize", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'CONCAT(b.lastname,", ",b.firstname," ",SUBSTR(b.middlename,1,1),".")','dt'=>4, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"homeowner","aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'a.active','dt'=>6,"alias"=>"active", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";})
                );
                $addwhere="a.active=1";
                $group="";
                $counttable="lot";
                $countwhere="active=1";
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere));
// <editor-fold defaultstate="collapsed" desc="Manual generation of json">


//                global $conn;
//                dbConnect();
//                $get_length = filter_input(INPUT_GET, "length");
//                $get_start = filter_input(INPUT_GET, "start");
//                $get_draw = filter_input(INPUT_GET, "draw");
//                $get_search = filter_input(INPUT_GET, "searc[value]");
//                
//                $stmt=$conn->prepare("SELECT * FROM homeowner LIMIT ?,?");
//                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                $postusername=filter_input(INPUT_POST, "uid");
//                $postpassword=md5(filter_input(INPUT_POST, "password"));
//                $stmt->bind_param('ii',$get_start,$get_length);
//                $stmt->execute();
//                $stmt->store_result();
//                $json = array(
//                    "draw" => $get_draw,
//                    "recordsFiltered" => $stmt->num_rows
//                );
//                $jsondata = array();
//                
//                if($stmt->num_rows > 0)
//                {
//                    $stmt->bind_result($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded);
//                    while($stmt->fetch()){
//                        $jsondata[]=array($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded);
////                        $jsondata[] = array(
////                            "id"=>$id,
////                            "lastname"=>$lastname,
////                            "firstname"=>$firstname,
////                            "middlename"=>$middlename,
////                            "contactno"=>$contactno,
////                            "email"=>$email
////                        );
//                    }
//                    $json["data"]=$jsondata;
//                }
//                else
//                {
//                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
//                }
//                
//                $stmt->free_result();
//                $stmt=$conn->prepare("SELECT COUNT(*) FROM homeowner");
//                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                $stmt->execute();
//                $stmt->bind_result($cnttotal);
//                while($stmt->fetch()){
//                    $json["recordsTotal"]=$cnttotal;
//                }
//                
//                $stmt->free_result();
//                $stmt->close();
//                dbClose();
//                echo json_encode($json);
                // </editor-fold>
            }
            break;
        case "lot":
            if(isLoggedIn())
            {
                if(!is_null($lid=filter_input(INPUT_GET, "id")))
                {
                    global $conn;
                    dbConnect();
                    $stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername FROM lot a, homeowner b WHERE a.homeowner=b.id AND a.id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$lid);
                    $stmt->execute();
                    $stmt->store_result();

                    if($stmt->num_rows==1)
                    {
                        displayHTMLPageHeader();
                        $stmt->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $active, $homeownername);
                        while($stmt->fetch()){ ?>
                        <h1>Lot Code: <?php echo $code; ?></h1>
                        
                        <ul data-role="listview" data-inset="true">
                            <li data-role="list-divider">Address</li>
                            <li><span class="infoheader">House Number</span> <?php echo $housenumber; ?></li>
                            <li><span class="infoheader">Lot</span> <?php echo $lot; ?></li>
                            <li><span class="infoheader">Block</span> <?php echo $block; ?></li>
                            <li><span class="infoheader">Street</span> <?php echo $street; ?></li>
                            <li><span class="infoheader">Phase</span> <?php echo $phase; ?></li>
                            <li><span class="infoheader">Lot Size</span> <?php echo $lotsize; ?> sq. m.</li>
                            <li data-role="list-divider">Ownership</li>
                            <li><span class="infoheader">Name</span> <?php echo $homeownername; ?></li>
                            <li><span class="infoheader">Date Acquired</span> <?php echo $dateacquired; ?></li>
                            <li><span class="infoheader">Household Size</span> <?php echo $numberinhousehold; ?></li>
                        </ul>
                        
                        <table id="lotpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                            <thead>
                                <tr>
                                    <th data-priority="1">Date</th>
                                    <th data-priority="3">OR Number</th>
                                    <th data-priority="1">Start Date</th>
                                    <th data-priority="1">End Date</th>
                                    <th data-priority="4">Paid by</th>
                                    <th data-priority="2">Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        
                        
                        <script type="text/javascript">
                            $(document).on("pagecreate",function(){
                                try{
                                    pl = $("#lotpaymentlist").dataTable({
                                        ajax:"./lotpaymentss?id=<?php echo $lid; ?>",
                                        columns:[
                                            {data:"paymentdate"},
                                            {data:"ornumber"},
                                            {data:"startdate"},
                                            {data:"enddate"},
                                            {data:"payee"},
                                            {data:"amount"},
                                            {data:"id"}
                                        ],
                                        columnDefs:[
                                            {
                                                "render":function(data,type,row){
                                                    return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink textamount" data-ledgerid="'+row.id+'">'+data.toFixed(2)+'</a>';
                                                },
                                                "targets":[5]
                                            },
                                            {
                                                "render":function(data,type,row){
                                                    return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'+row.id+'">'+data+'</a>';
                                                },
                                                "targets":[0,1,2,3,4]
                                            },
                                            {
                                                "visible":false,
                                                "targets":[6]
                                            }
                                        ],
                                        order:[[0,"desc"]]
                                    });

                                    var plapi=pl.api();

                                    $("#lotpaymentlist").on( "draw.dt", function() {
                                        $("a.paymentdetailslink").click(function(){
                                            changeIFrameSrc($(this)[0].dataset.ledgerid);
                                        });
                                    });

                                    $("#lotpaymentlist").on( "init.dt", function() {
                                        $("#lotpaymentlist_wrapper").enhanceWithin();
                                        $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                        $("#lotpaymentlist_filter input").on("change",function(){
                                            plapi.search($(this).val()).draw();
                                        });
                                    });
                                    
                                    $("#popupReceipt").on({popupafterclose:function(){
                                        $("#paymentdetailsframe").remove();
                                    }});
                                    $("#popupLot").on({popupafterclose:function(){
                                        $("#paymentdetailsframe").remove();
                                    }});
                                }catch(e){}
                                

                                function changeIFrameSrc(lid){
                                    $("#popupReceipt").append('<iframe id="paymentdetailsframe" src="./paymentdetails?id='+lid+'" width="640" height="480" seamless=""></iframe>');
                                }
                            });
                        </script>
                        <div data-role="popup" id="popupReceipt" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                            <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
<!--                                            <iframe id="paymentdetailsframe" src="" width="640" height="480" seamless=""></iframe>-->
                        </div>
                        <?php }
                        displayHTMLPageFooter();
                    }
                    else
                    {
                        setNotification("No such lot exists.",DT_NOTIF_ERROR);
                    }
                    $stmt->close();
                    dbClose();
                }else{
                    header("Location:./lots");
                }
            }
            else
            {
                header("Location: ./");
            }
            break;
        case "lotpaymentss":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
//                $get_length = filter_input(INPUT_GET, "length");
//                $get_start = filter_input(INPUT_GET, "start");
//                $get_draw = filter_input(INPUT_GET, "draw");
//                $get_search = filter_input(INPUT_GET, "search[value]");
                
                $stmt=$conn->prepare("SELECT a.id,a.amount,a.lot,a.startdate,a.enddate,b.ornumber,b.payee,b.paymentdate,b.transactiondate FROM ledgeritem a, ledger b WHERE a.id=b.id AND a.lot=?");
                
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                
                $lotid=filter_input(INPUT_GET, "id");
                $stmt->bind_param('i',$lotid);
                $stmt->execute();
                $stmt->store_result();
//                $json = array(
//                    "draw" => $get_draw,
//                    "recordsFiltered" => $stmt->num_rows
//                );
                $jsondata = array();
                
                if($stmt->num_rows > 0)
                {
                    $stmt->bind_result($id,$amount,$lot,$startdate,$enddate,$ornumber,$payee,$paymentdate,$transactiondate);
                    while($stmt->fetch()){
//                        $jsondata[]=array($id,$amount,$lot,$startdate,$enddate,$ornumber,$payee,$paymentdate,$transactiondate);
                        $jsondata[]=array(
                            "id"=>$id,
                            "amount"=>$amount,
                            "lot"=>$lot,
                            "startdate"=>$startdate,
                            "enddate"=>$enddate,
                            "ornumber"=>$ornumber,
                            "payee"=>$payee,
                            "paymentdate"=>$paymentdate,
                            "transactiondate"=>$transactiondate
                        );
                    }
                    $json["data"]=$jsondata;
                }
//                else
//                {
//                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
//                }
                
                $stmt->free_result();
//                $stmt=$conn->prepare("SELECT COUNT(*) FROM homeowner");
                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                $stmt->execute();
//                $stmt->bind_result($cnttotal);
//                while($stmt->fetch()){
//                    $json["recordsTotal"]=$cnttotal;
//                }
//                
//                $stmt->free_result();
                $stmt->close();
                dbClose();
                echo json_encode($json);
            }
            break;
        case "addpayment":
            
            if(isLoggedIn())
            {
                global $conn;
                $qs=false;
                dbConnect();
                $conn->autocommit(FALSE);
                
                $stmt=$conn->prepare("INSERT INTO ledger(ornumber,paymentdate,payee,homeowner,user) VALUES(?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $ornumber=filter_input(INPUT_POST, "ornumber");
                $paymentdate=filter_input(INPUT_POST, "paymentdate");
                $homeowner=filter_input(INPUT_POST, "homeowner");
                $payee=filter_input(INPUT_POST, "payee");
                
                $stmt->bind_param('sssii',$ornumber,$paymentdate,$payee,$homeowner,$userid);
                $qs=$stmt->execute();
                $ledgerid=$stmt->insert_id;
                $stmt->close();

                $amount=filter_input_array(INPUT_POST)["amt"];
                foreach($amount as $key => $lot)
                {
                    if($qs)
                    {
                        if($lot['amount']>0)
                        {
                            $stmt=$conn->prepare("INSERT INTO ledgeritem(id,amount,lot,startdate,enddate) VALUES(?,?,?,?,?)");
                            if($stmt === false) {
                                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                            }
                            $startdate=$lot['start']."-01";
                            $enddate=date("Y-m-t", strtotime($lot['end']."-01"));

                            $stmt->bind_param('idiss',$ledgerid, $lot['amount'], $key, $startdate, $enddate);
                            $qs=$stmt->execute();
                            $stmt->close();
                        }
                    }
                    else 
                    {
                        break 1;
                    }
                }
                
                if($qs)
                {
                    $conn->commit();
                    setNotification("Payment with OR Number $ornumber has been added.");
                }
                else
                {
                    $conn->rollback();
                    setNotification("There was an error in processing the payment.",DT_NOTIF_ERROR);
                }
                $conn->autocommit(TRUE);
                dbClose();
                header("Location: ./homeowner?id=".  filter_input(INPUT_POST, "homeowner"));
            }
            break;
        case "paymentlistss":
            if(isLoggedIn())
            {
                $table = 'ledger a, ledgeritem b, lot c';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.paymentdate','dt'=>0,"alias"=>"paymentdate",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'a.ornumber','dt'=>1,"alias"=>"ornumber",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'a.payee','dt'=>2,"alias"=>"payee",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'SUM(b.amount)','dt'=>3,"alias"=>"amount",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.number_format($d,2).'</a>';}),
                    array('db'=>'a.id','dt'=>4,"alias"=>"uid")     
                );
                $addwhere="a.id=b.id AND c.id=b.lot AND a.homeowner=".filter_input(INPUT_GET, "id");
                $group="GROUP BY a.id";
                $counttable="ledger";
                if(!is_null(filter_input(INPUT_GET, "id")))
                {
                    $countwhere="homeowner=".filter_input(INPUT_GET, "id");
                }
                else
                {
                    $countwhere="";
                }
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere ));
            }
            break;
        case "paymentdetails":
            if((isLoggedIn()) && (!is_null($ledgerid=filter_input(INPUT_GET, "id"))))
            {
                displayHTMLHead(); ?>
                
                <body>
                    <div data-role="page">
                        <header data-role="header">
                            <h1>Order of Payment</h1>
                        </header>
                        <div data-role="main">
                <?php
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("SELECT a.id, a.ornumber, a.payee, a.paymentdate, a.homeowner, a.transactiondate, a.user, "
                        . "b.lastname, b.firstname, b.middlename, c.fullname FROM ledger a, homeowner b, user c "
                        . "WHERE a.id=? AND a.homeowner=b.id AND a.user=c.id");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt->bind_param('i',$ledgerid);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows==1)
                {
                    $stmt->bind_result($id,$ornumber,$payee,$paymentdate,$homeownerid,$transactiondate,$userid,$lastname,$firstname,$middlename,$fullname);
                    while($stmt->fetch()){ ?>
                        <table data-role="table" class="ui-body-d ui-shadow table-stripe ui-responsive">
                            <thead><tr></tr></thead>
                            <tbody>
                                <tr>
                                    <th>OR Number</th>
                                    <td><?php echo $ornumber; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Date</th>
                                    <td><?php echo $paymentdate; ?></td>
                                </tr>
                                <tr>
                                    <th>Account Name</th>
                                    <td><?php echo $lastname.", ".$firstname." ".  substr($middlename,0,1)."."; ?></td>
                                </tr>
                                <tr>
                                    <th>Payor</th>
                                    <td><?php echo $payee; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Received by</th>
                                    <td><?php echo $fullname; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php }
                    $stmt->close();
                    
                    $stmt=$conn->prepare("SELECT a.id,a.amount,a.lot,a.startdate,a.enddate,b.code FROM ledgeritem a, lot b WHERE a.lot=b.id AND a.id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$ledgerid);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if($stmt->num_rows>0)
                    { ?>
                        <table data-role="table" class="ui-body-d ui-shadow table-stripe ui-responsive">
                            <thead>
                                <tr>
                                    <th>Lot Code</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $stmt->bind_result($id,$amount,$lot,$startdate,$enddate,$code);
                        $total=0;
                        while($stmt->fetch()){ 
                            $total += $amount; ?>
                                <tr data-theme="cd">
                                    <td><?php echo $code; ?></td>
                                    <td><?php echo $startdate; ?></td>
                                    <td><?php echo $enddate; ?></td>
                                    <td><?php echo number_format($amount,2); ?></td>
                                </tr>
                        <?php } ?>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th><?php echo number_format($total,2); ?></th>
                                </tr>
                            </tbody>
                        </table>
                        <?php
                        $stmt->close();
                    }
                }
                else
                {
                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
                }
                dbClose(); ?>
                        </div>
                    </div>
                </body>
                <?php
                displayHTMLFooter();
            }
            else
            {
                header("Location: ./homeowners");
            }
            break;
        default :
            displayHTMLPageHeader();
            if(!isLoggedIn())
            {
                //echo "<h1>Welcome to Santa Isabel Village Homeowners Association, Inc.</h1>";
            }
            displayHTMLPageFooter();
    }
}
