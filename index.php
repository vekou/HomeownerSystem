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
                
                $stmt2=$conn->prepare("SELECT `id`, `subdname`, `brgy`, `city`, `province`, `zipcode`, `contactno`, `email`, `price`, `interest`, `intgraceperiod` FROM `settings` WHERE id=?");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $settingsid=DT_SETTINGS_ID;
                $stmt2->bind_param('i',$settingsid);
                $stmt2->execute();
                $stmt2->store_result();
                if($stmt->num_rows==1)
                {
                    $stmt2->bind_result($_SESSION['settings']['id'],$_SESSION['settings']['subdname'],$_SESSION['settings']['brgy'],$_SESSION['settings']['city'],$_SESSION['settings']['province'],$_SESSION['settings']['zipcode'],$_SESSION['settings']['contactno'],$_SESSION['settings']['email'],$_SESSION['settings']['price'],$_SESSION['settings']['interest'],$_SESSION['settings']['intgraceperiod']);
                    while($stmt2->fetch()){}
                }
                $stmt2->free_result();
                $stmt2->close();
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
        case "removehomeowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE homeowner SET active=0 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=filter_input(INPUT_POST, "uid");
                $stmt->bind_param('i',$userid);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("Homeowner has been deleted.");
                dbClose();
                header("Location: ./homeowners");
            }
            else{header("Location: ./");}
            break;
        case "activatehomeowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE homeowner SET active=1 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=filter_input(INPUT_GET, "id");
                $stmt->bind_param('i',$userid);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("Homeowner has been reactivated.");
                dbClose();
                header("Location: ./inactivehomeowners");
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

                $stmt=$conn->prepare("SELECT id,lastname,firstname,middlename,contactno,email,user,dateadded,active FROM homeowner WHERE id=?");
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
                    $stmt->bind_result($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded,$active);
                    while($stmt->fetch()){ 
                        $lotlist=array();
                        $stmt2=$conn->prepare("SELECT id,code,homeowner,dateacquired,lotsize,housenumber,street,lot,block,phase,numberinhousehold,caretaker,dateadded,user,active FROM lot WHERE homeowner=? AND active=1");
                        if($stmt2 === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $householdid=filter_input(INPUT_GET, "id");
                        $stmt2->bind_param('i',$householdid);
                        $stmt2->execute();
                        $stmt2->store_result();
                        $lotcount=$stmt2->num_rows;
                        
                        if($lotcount<=0): 
                            if($active):?>
                                <a href="#confirmHomeownerDelete" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Delete Homeowner</a>
                            <?php else: ?>
                                <a href="./activatehomeowner?id=<?php echo $id; ?>" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Reactivate Homeowner</a>
                        <?php endif;
                            endif; ?>
                        <a href="#addHomeowner" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Homeowner</a>
                        <!--<h1><?php echo "$lastname, $firstname " . substr($middlename, 0, 1) . "."; ?></h1>-->
                        <fieldset data-role="controlgroup" data-type="horizontal" class="pagetitleheader"><div class="ui-btn ui-btn-d">Name</div> <div class="ui-btn"><?php echo "$lastname, $firstname " . substr($middlename, 0, 1) . "."; ?></div></fieldset>
                        
                        <?php displayHomeownerForm("./updatehomeowner",$lastname,$firstname,$middlename,$contactno,$email,$id); ?>
                        <div data-role="popup" id="confirmHomeownerDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                            <header data-role="header">
                              <h1>Confirm Delete?</h1>
                              <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                            </header>
                            <div role="main" class="ui-content">
                                <form action="./removehomeowner" method="post" data-ajax="false">
                                    <input type="hidden" name="uid" value="<?php echo $id; ?>"/>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <legend>Remove this homeowner?</legend>
                                        <div class="ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left">You can still recover the record later.</div>
                                        <hr/>
                                        <input type="submit" value="Delete" data-theme="e"/>
                                        <a href="#" data-rel="back" data-role="button">Cancel</a>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                        
                        <ul data-role="listview" data-inset="true" id="homeownercontactinfo">
                            <li data-role="list-divider">Contact Information</li>
                            <li data-icon="false"><a href="tel:<?php echo $contactno; ?>"><img src="css/images/icons-png/phone-black.png" alt="phone" class="ui-li-icon ui-corner-none"/><?php echo $contactno; ?></a></li>
                            <li data-icon="false"><a href="mailto:<?php echo $email; ?>"><img src="css/images/icons-png/mail-black.png" alt="mail" class="ui-li-icon ui-corner-none"/><?php echo $email; ?></a></li>
                        </ul>                        
                        
                        <div>
                            <div data-role="tabs" id="tabs">
                                <div data-role="navbar">
                                    <ul>
                                      <li><a href="#paymentsTab" data-ajax="false" class="ui-btn-active">Payments</a></li>
                                      <li><a href="#lotsTab" data-ajax="false">Registered Lots</a></li>
                                    </ul>
                                </div>
                                
                                <div id="paymentsTab" class="ui-body-d ui-content">
                                    <div>
                                        <a href="#paymentform" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" id="addPaymentBtns" data-theme="d">Add Payment</a>
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
                                        
                                    </div>
                                </div>
                                <div id="lotsTab" class="ui-body-d ui-content">
                                    <!--<a href="#addLotForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add Lot</a>-->
                                    <?php // displayLotForm($uid); ?>
                                    <div data-role="popup" id="popupLot" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                                        <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
<!--                                            <iframe id="paymentdetailsframe" src="" width="640" height="480" seamless=""></iframe>-->
                                    </div>
                                    <ul data-role="listview" data-inset="true" data-theme="a">
                                    <?php
                                        

                                        if($lotcount>0)
                                        {

                                            $stmt2->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $caretaker, $dateadded, $userid, $active);

                                            while($stmt2->fetch()){ ?>
                                                <li data-role='collapsible' data-collapsed-icon='carat-r' data-expanded-icon='carat-u' data-inset='false'>
                                                <h2><?php echo $code; ?> (<?php echo $housenumber; ?> Lot <?php echo $lot; ?> Block <?php echo $block; ?> <?php echo $street; ?> Phase <?php echo $phase; ?>)</h2>
                                                <table class='tbldata'><tr><th>Address</th><td><?php echo $housenumber; ?> Lot <?php echo $lot; ?> Block <?php echo $block; ?> <?php echo $street; ?> Phase <?php echo $phase; ?></td></tr>
                                                <tr><th>Acquisition Date</th><td><?php echo $dateacquired; ?></td></tr>
                                                <tr><th>Lot Size</th><td><?php echo $lotsize; ?> sq. m</td></tr>
                                                <tr><th>Household size</th><td><?php echo $numberinhousehold; ?></td></tr></table>
                                                <a href='./lot?id=<?php echo $id; ?>' data-role='button' data-icon='info' data-iconpos='left' data-inline="true" data-theme="d">Lot Details</a>
                                                </li><?php 
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
                                </div>
                            </div>
                            <div data-role="popup" data-dismissible="false" id="paymentform" data-overlay-theme="b">
                                <header data-role="header">
                                <h1>Add Payment</h1>
                                <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <section class="ui-content">
                                    
                                        <form method="post" action="./addpayment" data-ajax="false">
                                            <label for="paymentdate">Payment Date</label>
                                            <input type="date" id="paymentdate" name="paymentdate" data-role="date" value="<?php echo date("m/d/Y"); ?>" required="true"/>
                                            <label for="ornumber">OR Number</label>
                                            <input type="text" id="ornumber" name="ornumber" required="true" />
                                            <label for="payee">Paid by</label>
                                            <input type="text" id="payee" name="payee" required="true" />
                                            <input type="hidden" name="homeowner" value="<?php echo $uid; ?>"/>
                                        
                                            <div class="ui-bar ui-bar-a">
                                            <h4>Amount</h4>
                                            </div>
                                            <table id="tblPaymentForm">
                                                <thead>
                                                    <tr>
                                                        <th>Lot Code</th>
                                                        <th>Start Date</th>
                                                        <th>End Date</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                                    
                                                    
                                                <tfoot>
                                                    <tr>
                                                        <th>Total</th>
                                                        <th id="paymentTotal" class="textamount" colspan="3">0.00</th>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <select id="paymenttype">
                                                                <?php if($lotcount>0): ?>
                                                                    <optgroup label="Monthly dues">
                                                                    <?php foreach($lotlist as $loti): ?>
                                                                        <option value="<?php echo $loti["id"]; ?>" title="<?php echo $loti["address"]; ?>" data-infoaddress="<?php echo $loti["address"]; ?>" data-infocode="<?php echo $loti["lotcode"]; ?>" data-infosize="<?php echo $loti["lotsize"]; ?>"><?php echo $loti["lotcode"]; ?></option>
                                                                    <?php endforeach; ?>
                                                                    </optgroup>
                                                                <?php endif; ?>
                                                                    <option value="0">Other payments...</option>
                                                            </select>
                                                        </td>
                                                        <td><a href="#" id="btnAddPayment" data-role="button" data-icon="plus">Add Payment</a></td>
                                                    </tr>
                                                </tfoot>
                                                </tbody>
                                            </table>
                                            <label for="remarks">Remarks</label>
                                            <textarea name="remarks" id="remarks" placeholder="Remarks (optional)"></textarea>
                                            <input type="submit" value="Submit" <?php echo ($lotcount<=0)?"disabled='true'":""; ?>/>
                                        </form>
                                </section>
                            </div>
                        </div>
                        <script type="text/javascript">
                            $(document).on("pagecreate",function(){
                                var additioncounter=0;
                                try{
                                    pl = setAsDataTable("#tblpaymentlist","./paymentlistss?id=<?php echo $uid; ?>",[{"targets":[4],"visible":false,"searchable":false}],[[0,"desc"]]);


//                                                    var plapi=pl.api();

                                    $("#tblpaymentlist").on( "draw.dt", function() {
                                        $("a.paymentdetailslink").click(function(){
                                            changeIFrameSrc($(this)[0].dataset.ledgerid);
                                        });
                                    });

                                    $("#tblpaymentlist").on( "init.dt", function() {
                                        $("#tblpaymentlist_wrapper").enhanceWithin();
                                        $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                        $("#tblpaymentlist_filter input").on("change",function(){
                                            pl.search($(this).val()).draw();
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
                                    $("#popupReceipt").append('<iframe id="paymentdetailsframe" src="./paymentdetails?id='+lid+'" width="640" height="480" seamless=""></iframe>');
                                }
                                                                
                                $("#btnAddPayment").click(function(){
                                    additioncounter++;
                                    if($("#paymenttype").val() > 0)
                                    {
                                        $("#tblPaymentForm tbody").append('<tr><th><label title="'+$("#paymenttype option:selected").attr("data-infoaddress")+'">'+$("#paymenttype option:selected").attr("data-infocode")+'</label></th><td><input type="month" name="amt[lot]['+additioncounter+'][start]" id="lotstart'+$("#paymenttype option:selected").val()+'" required="true"/></td><td><input type="month" name="amt[lot]['+additioncounter+'][end]" id="lotend'+$("#paymenttype option:selected").val()+'" required="true"/></td><td><input type="number" step="0.01" name="amt[lot]['+additioncounter+'][amount]" id="lotamt'+$("#paymenttype option:selected").val()+'" value="0.00" class="textamount" required="true"/></td><td><a href="#" class="paymentitemremove" data-role="button" data-icon="delete" data-iconpos="notext">Remove</a><input type="hidden" name="amt[lot]['+additioncounter+'][lotcode]" value="'+$("#paymenttype option:selected").attr("data-infocode")+'"/><input type="hidden" name="amt[lot]['+additioncounter+'][lotid]" value="'+$("#paymenttype option:selected").val()+'"/></td></tr>').enhanceWithin();
                                        $(".paymentitemremove").click(removePaymentItem);
                                    }
                                    else
                                    {
                                        $("#tblPaymentForm tbody").append('<tr><th colspan="3"><input type="text" name="amt[misc]['+additioncounter+'][desc]" placeholder="Description" required="true"/></th><td><input type="number" name="amt[misc]['+additioncounter+'][amount]" value="0.00" class="textamount" /></td><td><a href="#" class="paymentitemremove" data-role="button" data-icon="delete" data-iconpos="notext">Remove</a></td></tr>').enhanceWithin();
                                    }
                                });
                                
                                function removePaymentItem(){
                                    $(this).parent().parent().remove();
                                }
                            });
                        </script>

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
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="#addHomeowner" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Homeowner</a>
                    <a href="./inactivehomeowners" data-role="button" data-icon="forbidden" data-theme="b">Deleted Homeowners</a>
                </fieldset>
                
                <?php displayHomeownerForm(); ?>
                
                <div class="ui-content ui-body-a ui-corner-all">
                    <table id="tblhomeownerlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                        <thead>
                            <tr>
                                <th  rowspan="2">Name</th>
                                <th data-priority="3" rowspan="2">Contact Number</th>
                                <th data-priority="4" rowspan="2">Email Address</th>
                                <th colspan="3">Latest Payment</th>
                                <th data-priority="2" rowspan="2">Option</th>
                            </tr>
                            <tr>
                                <th data-priority="1">Payment Date</th>
                                <th data-priority="2">OR Number</th>
                                <th data-priority="2">Amount</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    <div id="ttools"></div>
                </div>
                <script type="text/javascript">
                    //var hol;
                    $(document).on("pagecreate",function(event, ui){
//                        try{
                            hol = setAsDataTable("#tblhomeownerlist","./homeownerlistss",[{"targets":[6],"visible":false,"searchable":false}],[[0,"asc"]]);
//                            holapi=hol.api();

                            $("#tblhomeownerlist").on( "draw.dt", function() {
                                $("a.tblhomeownerlistbtn").button();
                            });

                            $("#tblhomeownerlist").on( "init.dt", function() {
                                $("#tblhomeownerlist_wrapper").enhanceWithin();
                                $("#tblhomeownerlist_filter input").on("change",function(){
                                    hol.search($(this).val()).draw();
                                });
                            });
                            
                            var tableTools = new $.fn.dataTable.TableTools( hol, {
                            "buttons": [
                                "copy",
                                "csv",
                                "xls",
                                "pdf",
                                { "type": "print", "buttonText": "Print me!" }
                            ],
                            "sSwfPath":"./plugin/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
                        } );

                        $( tableTools.fnContainer() ).appendTo('#ttools');
//                        }catch(e){}
                    });
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "inactivehomeowners":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="./homeowners" data-role="button" data-icon="back" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Back to Homeowners List</a>
                    <a href="./inactivehomeowners" data-role="button" data-icon="forbidden" data-theme="a" class="ui-disabled">Deleted Homeowners</a>
                </fieldset>
                <div class="ui-content ui-body-a ui-corner-all">
                    <table id="tblhomeownerlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                        <thead>
                            <tr>
                                <th data-priority="1">Name</th>
                                <th data-priority="2">Contact Number</th>
                                <th data-priority="3">Email Address</th>
                                <th data-priority="4">Option</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    $(document).on("pagecreate",function(event, ui){
                        try{
                            ul = $("#tblhomeownerlist").dataTable({
                                ajax:"./inactiveownerlistss",
                                columns:[
                                    {data:"name"},
                                    {data:"contactno"},
                                    {data:"email"},
                                    {data:"id"}
                                ],
                                columnDefs:[
                                    {
                                        "render":function(data,type,row){
                                            return '<a href="./homeowner?id='+row["id"]+'" class="tablecelllink paymentdetailslink" data-ajax="false">'+data+'</a>';
                                        },
                                        "targets":[0,1,2]
                                    },
                                    {
                                        "render":function(data,type,row){
                                            return '<a href="./activatehomeowner?id='+data+'" data-role="button" data-iconpos="left" data-icon="check" data-ajax="false" data-mini="true">Reactivate Homeowner</a>';
                                        },
                                        "targets":[3]
                                    }
                                ],
                                order:[[0,"desc"]],
                                retrieve:true
                            });
                            
                            ulapi = ul.api();
                            
                            $("#tblhomeownerlist").on( "init.dt", function() {
                                $("#tblhomeownerlist_wrapper").enhanceWithin();
                                $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                $("#tblhomeownerlist_filter input").on("change",function(){
                                    ul.search($(this).val()).draw();
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
                $table = 'homeowner a LEFT JOIN ledger b ON a.id=b.homeowner LEFT JOIN ledgeritem c ON b.id=c.id LEFT JOIN (SELECT id,homeowner, MAX(paymentdate) AS lpaymentdate FROM ledger GROUP BY homeowner) m ON m.homeowner=a.id';
                $primaryKey = 'id';
                $columns = array(
                    //array('db'=>'id','dt'=>0),
                    array('db'=>'CONCAT(a.lastname,", ",a.firstname," ",SUBSTR(a.middlename,1,1),".")','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"name","aliascols"=>"a.lastname,a.firstname,a.middlename"),
                    array('db'=>'a.contactno','dt'=>1,"alias"=>"contactno", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.email','dt'=>2,"alias"=>"email", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'m.lpaymentdate','dt'=>3,"alias"=>"paymentdate" ,'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".(($d=="")?"":date('M d, Y',strtotime($d)))."</a>";}),
                    array('db'=>'b.ornumber','dt'=>4,"alias"=>"ornumber", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'(SELECT SUM(n.amount) FROM ledgeritem n WHERE n.id=b.id)','dt'=>5,"alias"=>"amount", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".(($d>0)?number_format($d,2):"")."</a>";}),
                    array('db'=>'a.id','dt'=>6,"alias"=>"uid","aliascols"=>"a.id", 'formatter'=>function($d,$row){return "<a href='#' class='tblhomeownerlistbtn' data-role='button' data-iconpos='notext' data-icon='edit'>Edit</a>";})
                );
                $addwhere="(m.lpaymentdate=b.paymentdate OR b.paymentdate IS NULL) AND a.active=1";
                $group="GROUP BY a.id";
                $counttable="homeowner";
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
        case "inactiveownerlistss":
            if(isLoggedIn())
            {
                global $conn;
                $jsondata = array();
                $json["data"]=array();
                
                dbConnect();
                
                $stmt2=$conn->prepare("SELECT id,CONCAT(lastname,', ',firstname,' ',SUBSTR(middlename,1,1),'.') AS name,contactno,email FROM homeowner WHERE active=0 ORDER BY lastname ASC");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt2->execute();
                $stmt2->store_result();
                
                if($stmt2->num_rows>0){
                    $stmt2->bind_result($id,$name,$contactno,$email);
                    while($stmt2->fetch()):
                        $jsondata[]=array(
                            "id"=>$id,
                            "name"=>$name,
                            "contactno"=>$contactno,
                            "email"=>$email
                        );
                    endwhile;
                    $json["data"]=$jsondata;
                }
                $stmt2->free_result();
                $stmt2->close();
                dbClose();
                echo json_encode($json);
            }
            break;
        case "setasowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET homeowner=?, dateacquired=?, numberinhousehold=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $homeowner=filter_input(INPUT_POST, "owner-filter-menu");
                $dateacquired=filter_input(INPUT_POST, "dateacquired");
                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
                $lotid=filter_input(INPUT_POST, "lotid");
                if($homeowner==0)
                {
                    setNotification("No owner selected.",DT_NOTIF_WARNING);
                }
                else
                {
                    $stmt->bind_param('isii',$homeowner,$dateacquired,$numberinhousehold,$lotid);
                    $stmt->execute();
                    $stmt->close();

                    setNotification("Successfully set owner.");
                }
                dbClose();
                header("Location: ./lot?id=".$lotid);
            }
            else{header("Location: ./");}
            break;
        case "removeowner":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET homeowner=0, dateacquired='', numberinhousehold=0 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lotid=filter_input(INPUT_POST, "lotid");

                $stmt->bind_param('i',$lotid);
                $stmt->execute();
                $stmt->close();

                setNotification("Successfully removed owner.");
                dbClose();
                header("Location: ./lot?id=".$lotid);
            }
            else{header("Location: ./");}
            break;
        case "removelot":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET active=0 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lotid=filter_input(INPUT_POST, "lotid");
                $homeowner=filter_input(INPUT_POST, "homeowner");
                
                if($homeowner==0)
                {
                    $stmt->bind_param('i',$lotid);
                    $stmt->execute();
                    $stmt->close();

                    setNotification("Successfully removed lot.");
                }
                else
                {
                    setNotification("You cannot remove a lot with an owner.");
                }
                dbClose();
                header("Location: ./lots");
            }
            else{header("Location: ./");}
            break;
        case "activatelot":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET active=1 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lotid=filter_input(INPUT_GET, "id");
                
                $stmt->bind_param('i',$lotid);
                $stmt->execute();
                $stmt->close();

                setNotification("Successfully reactivated lot.");
                dbClose();
                header("Location: ./inactivelots");
            }
            else{header("Location: ./");}
            break;
        case "lots":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="#addLotForm" data-role="button" data-icon="plus" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Lot</a>
                    <a href="./inactivelots" data-role="button" data-icon="forbidden" data-theme="b">Deleted Lots</a>
                </fieldset>
<!--                <form action="./addlot" method="post" data-ajax="false">-->
                    <?php displayLotForm(); ?>
                <!--</form>-->
                <div class="ui-content ui-body ui-body-a ui-corner-all">
                    <table id="tbllotlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                        <thead>
                            <tr>
                                <th data-priority="5" rowspan="2">ID</th>
                                <th data-priority="1" rowspan="2">Lot Code</th>
                                <th data-priority="2" rowspan="2">Address</th>
                                <th data-priority="4" rowspan="2">Lot Size</th>
                                <th data-priority="3" rowspan="2">Owner</th>
                                <th data-priority="3" rowspan="2">Active</th>
                                <th data-priority="3" colspan="3">Latest Payment</th>
                                <th data-priority="3" rowspan="2">End Date</th>
                                <th data-priority="3" rowspan="2">Balance</th>
                            </tr>
                            <tr>
                                <th >Month</th>
                                <th data-priority="3">OR Number</th>
                                <th data-priority="3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    //var hol;
                    $(document).on("pagecreate",function(event, ui){
                        try{
                            hol = setAsDataTable("#tbllotlist","./lotlistss",
                            [
                                {"targets":[0,5,9],"visible":false,"searchable":false},
                            ],[[0,"asc"]]);
//                            var holapi=hol.api();

                            $("#tbllotlist").on( "draw.dt", function() {
                                $("a.tblhomeownerlistbtn").button();
                            });

                            $("#tbllotlist").on( "init.dt", function() {
                                $("#tbllotlist_wrapper").enhanceWithin();
                                $("#tbllotlist_filter input").on("change",function(){
                                    hol.search($(this).val()).draw();
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
                $stmt=$conn->prepare("INSERT INTO lot(code, lotsize, housenumber, street, lot, block, phase, user) VALUES (?,?,?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
//                $homeowner=filter_input(INPUT_POST, "homeowner");
                $code=filter_input(INPUT_POST, "code");
//                $dateacquired=filter_input(INPUT_POST, "dateacquired");
                $lotsize=filter_input(INPUT_POST, "lotsize");
                $housenumber=filter_input(INPUT_POST, "housenumber");
                $street=filter_input(INPUT_POST, "street");
                $lot=filter_input(INPUT_POST, "lot");
                $block=filter_input(INPUT_POST, "block");
                $phase=filter_input(INPUT_POST, "phase");
//                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
                
                $stmt->bind_param('sdsssssi',$code, $lotsize, $housenumber, $street, $lot, $block, $phase, $userid);
                $stmt->execute();
                $lastid=$stmt->insert_id;
                $stmt->close();
                
                setNotification("Lot $code has been added.");
                dbClose();
                header("Location: ./lot?id=".$lastid);
            }
            else{header("Location: ./");}
            break;
        case "updatelot":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET code=?, lotsize=?, housenumber=?, street=?, lot=?, block=?, phase=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lotid=filter_input(INPUT_POST, "lotid");
//                $homeowner=filter_input(INPUT_POST, "homeowner");
                $code=filter_input(INPUT_POST, "code");
//                $dateacquired=filter_input(INPUT_POST, "dateacquired");
                $lotsize=filter_input(INPUT_POST, "lotsize");
                $housenumber=filter_input(INPUT_POST, "housenumber");
                $street=filter_input(INPUT_POST, "street");
                $lot=filter_input(INPUT_POST, "lot");
                $block=filter_input(INPUT_POST, "block");
                $phase=filter_input(INPUT_POST, "phase");
//                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
                
                $stmt->bind_param('sdsssssi',$code, $lotsize, $housenumber, $street, $lot, $block, $phase, $lotid);
                $stmt->execute();
                $stmt->close();
                
                setNotification("Lot $code has been updated.");
                dbClose();
                header("Location: ./lot?id=".$lotid);
            }
            else{header("Location: ./");}
            break;
        case "inactivelots":
            if(isLoggedIn())
            {
                displayHTMLPageHeader(); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="./lots" data-role="button" data-icon="back" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Back to Lots List</a>
                    <a href="./inactivelots" data-role="button" data-icon="forbidden" data-theme="a" class="ui-disabled">Deleted Lots</a>
                </fieldset>
                <div class="ui-content ui-body-a ui-corner-all">
                    <table id="tbllotlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                        <thead>
                            <tr>
                                <th data-priority="1">Lot Code</th>
                                <th data-priority="2">Address</th>
                                <th data-priority="3">Lot Size</th>
                                <th data-priority="4">Option</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    $(document).on("pagecreate",function(event, ui){
                        try{
                            ul = $("#tbllotlist").dataTable({
                                ajax:"./inactivelotlistss",
                                columns:[
                                    {data:"code"},
                                    {data:"housenumber"},
                                    {data:"lotsize"},
                                    {data:"id"}
                                ],
                                columnDefs:[
                                    {
                                        "render":function(data,type,row){
                                            return '<a href="./lot?id='+row["id"]+'" class="tablecelllink paymentdetailslink" data-ajax="false">'+data+'</a>';
                                        },
                                        "targets":[0,2]
                                    },
                                    {
                                        "render":function(data,type,row){
                                            return '<a href="./lot?id='+row["id"]+'" class="tablecelllink paymentdetailslink" data-ajax="false">'+row['housenumber']+" Lot "+row["lot"]+" Block "+row["block"]+" "+row["street"]+" Phase "+row["phase"]+'</a>';
                                        },
                                        "targets":[1]
                                    },
                                    {
                                        "render":function(data,type,row){
                                            return '<a href="./activatelot?id='+data+'" data-role="button" data-iconpos="left" data-icon="check" data-ajax="false" data-mini="true">Reactivate Lot</a>';
                                        },
                                        "targets":[3]
                                    }
                                ],
                                order:[[0,"desc"]],
                                retrieve:true<?php echo (filter_input(INPUT_GET, "print")==1?",paging:false":""); ?>
                            });
                            
                            ulapi = ul.api();
                            
                            $("#tbllotlist").on( "init.dt", function() {
                                $("#tbllotlist_wrapper").enhanceWithin();
                                $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                $("#tbllotlist_filter input").on("change",function(){
                                    ul.search($(this).val()).draw();
                                });
                            });
                        }catch(e){}
                    });
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "inactivelotlistss":
            if(isLoggedIn())
            {
                global $conn;
                $jsondata = array();
                $json["data"]=array();
                
                dbConnect();
                
                $stmt2=$conn->prepare("SELECT `id`, `code`, `homeowner`, `dateacquired`, `lotsize`, `housenumber`, `street`, `lot`, `block`, `phase`, `numberinhousehold`, `caretaker`, `dateadded`, `user`, `active` FROM `lot` WHERE `active`=0");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt2->execute();
                $stmt2->store_result();
                
                if($stmt2->num_rows>0){
                    $stmt2->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $caretaker, $dateadded, $userid, $active);
                    while($stmt2->fetch()):
                        $jsondata[]=array(
                            "id"=>$id, 
                            "code"=>$code, 
                            "homeowner"=>$homeowner, 
                            "dateacquired"=>$dateacquired, 
                            "lotsize"=>$lotsize, 
                            "housenumber"=>$housenumber, 
                            "street"=>$street, 
                            "lot"=>$lot, 
                            "block"=>$block, 
                            "phase"=>$phase, 
                            "numberinhousehold"=>$numberinhousehold, 
                            "caretaker"=>$caretaker, 
                            "dateadded"=>$dateadded, 
                            "user"=>$userid,
                            "active"=>$active
                        );
                    endwhile;
                    $json["data"]=$jsondata;
                }
                $stmt2->free_result();
                $stmt2->close();
                dbClose();
                echo json_encode($json);
            }
            break;
        case "lotlistss":
            if(isLoggedIn())
            {
                $table = 'lot a LEFT JOIN homeowner b ON a.homeowner=b.id LEFT JOIN ledgeritem c ON a.id=c.lot LEFT JOIN ledger d ON c.id=d.id LEFT JOIN(SELECT lot, MAX(enddate) AS maxdate FROM ledgeritem GROUP BY lot) m ON m.lot=a.id, settings f';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"uid", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.code','dt'=>1,"alias"=>"code", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'CONCAT(a.housenumber," Lot",a.lot," Block",a.block," ",a.street," Phase",a.phase)','dt'=>2, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"address","aliascols"=>"a.housenumber,a.lot,a.block,a.street,a.phase"),
                    array('db'=>'a.lotsize','dt'=>3,"alias"=>"lotsize", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'CONCAT(b.lastname,", ",b.firstname," ",SUBSTR(b.middlename,1,1),".")','dt'=>4, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"homeowner","aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'a.active','dt'=>5,"alias"=>"active", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'IF(MONTH(c.startdate)=MONTH(c.enddate) AND YEAR(c.startdate)=YEAR(c.enddate),DATE_FORMAT(c.startdate,"%b %Y"),CONCAT(DATE_FORMAT(c.startdate,"%b %Y"),"-",DATE_FORMAT(c.enddate,"%b %Y")))','dt'=>6,"alias"=>"period","aliascols"=>"c.startdate,c.enddate",'DT_RowData'=>function($d,$row){return date('Ymd',  strtotime($row['enddate']));}, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'d.ornumber','dt'=>7,"alias"=>"ornumber", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'c.amount','dt'=>8,"alias"=>"amount", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";}),
                    array('db'=>'c.enddate','dt'=>9,"alias"=>"enddate", 'formatter'=>function($d,$row){return $d;}),
                    array('db'=>'getArrears(f.price*a.lotsize,1+f.interest, TIMESTAMPDIFF(MONTH,c.enddate,CURDATE()))','dt'=>10,"alias"=>"arrears", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";})
                );
                $addwhere="a.active=1 AND (m.maxdate=c.enddate OR m.maxdate IS NULL) AND f.id=1";
                $group="GROUP BY a.id";
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
                    //$stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername FROM lot a, homeowner b WHERE a.homeowner=b.id AND a.id=?");
                    $stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername, a.active FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id WHERE a.id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$lid);
                    $stmt->execute();
                    $stmt->store_result();
                    if($stmt->num_rows==1)
                    {
                        displayHTMLPageHeader();
                        $stmt->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $active, $homeownername, $active);
                        while($stmt->fetch()){ ?><?php 
                            if($homeowner==0): 
                                if($active>0):?>
                                    <a href="#confirmLotDelete" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Remove Lot</a> <?php 
                                else: ?>
                                    <a href="./activatelot?id=<?php echo $id; ?>" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" class="editbtns" data-theme="a">Reactivate Lot</a> <?php
                                endif;
                            endif;?>
                            <a href="#addLotForm" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Lot</a>
                            <?php displayLotForm("./updatelot", $code, $lotsize, $housenumber, $street, $lot, $block, $phase, $id) ?>
                            
                            <div data-role="popup" id="confirmOwnerDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                                <header data-role="header">
                                  <h1>Confirm Remove?</h1>
                                  <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <div role="main" class="ui-content">
                                    <form action="./removeowner" method="post" data-ajax="false">
                                        <input type="hidden" name="lotid" value="<?php echo $id; ?>"/>
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <legend>Remove <?php echo $homeownername; ?> as owner of this lot?</legend>
                                            <input type="submit" value="Delete" data-theme="e"/>
                                            <a href="#" data-rel="back" data-role="button">Cancel</a>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                            <div data-role="popup" id="confirmLotDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                                <header data-role="header">
                                  <h1>Confirm Remove?</h1>
                                  <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <div role="main" class="ui-content">
                                    <form action="./removelot" method="post" data-ajax="false">
                                        <input type="hidden" name="lotid" value="<?php echo $id; ?>"/>
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <legend>Remove this lot?</legend>
                                            <div class="ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left">You can still recover this lot later.</div>
                                            <input type="submit" value="Delete" data-theme="e"/>
                                            <a href="#" data-rel="back" data-role="button">Cancel</a>
                                        </fieldset>
                                    </form>
                                </div>
                            </div>
                            
                            
                            <fieldset data-role="controlgroup" data-type="horizontal" class="pagetitleheader"><div class="ui-btn ui-btn-d">Lot Code</div> <div class="ui-btn"><?php echo $code; ?></div></fieldset>

                            <ul data-role="listview" data-inset="true">
                                <li data-role="list-divider">Address</li>
                                <li><span class="infoheader">House Number</span> <?php echo $housenumber; ?></li>
                                <li><span class="infoheader">Lot</span> <?php echo $lot; ?></li>
                                <li><span class="infoheader">Block</span> <?php echo $block; ?></li>
                                <li><span class="infoheader">Street</span> <?php echo $street; ?></li>
                                <li><span class="infoheader">Phase</span> <?php echo $phase; ?></li>
                                <li><span class="infoheader">Lot Size</span> <?php echo $lotsize; ?> sq. m.</li>
                                <?php if($homeowner>0): ?>
                                    <li data-role="list-divider">Ownership</li>
                                    <li><a href="./homeowner?id=<?php echo $homeowner; ?>"><span class="infoheader">Name</span> <?php echo $homeownername; ?></a><a href="#confirmOwnerDelete" data-icon="delete" data-theme="b" data-rel="popup" data-position-to="window" data-transition="pop">Remove Owner</a></li>
                                    <li><span class="infoheader">Date Acquired</span> <?php echo $dateacquired; ?></li>
                                    <li><span class="infoheader">Household Size</span> <?php echo $numberinhousehold; ?></li>
                                <?php elseif($active>0): ?>
                                    <li>
                                        <form data-ajax="false" method="post" action="./setasowner">
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <legend><span class="infoheader">Add Owner</span></legend>
                                        <label for="owner-filter-menu">Select lot owner</label>
                                        <select id="owner-filter-menu" name="owner-filter-menu" data-native-menu="false" required="true">
                                            <option>Select owner</option>
                                            <?php
                                            $stmt2=$conn->prepare("SELECT id,CONCAT(lastname,', ',firstname,' ',SUBSTR(middlename,1,1),'.') AS name,contactno,email FROM homeowner ORDER BY lastname ASC");
                                            if($stmt2 === false) {
                                                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                            }
                                            $stmt2->execute();
                                            $stmt2->store_result();

                                            if($stmt2->num_rows>0){
                                                $stmt2->bind_result($uid,$uname,$ucontactno,$uemail);
                                                while($stmt2->fetch()):?>
                                                    <option value="<?php echo $uid; ?>" title="<?php echo $ucontactno.'/'.$uemail;?>"><?php echo $uname; ?></option><?php 
                                                endwhile;
                                            }
                                            $stmt2->free_result();
                                            $stmt2->close();
                                            ?>
                                        </select>
                                        <label for="dateacquired">Date acquired</label>
                                        <input type="date" name="dateacquired" id="dateacquired" data-wrapper-class="controlgroup-dateinput ui-btn" placeholder="Date acquired"/>
                                        <label for="numberinhousehold">Household size</label>
                                        <input type="number" name="numberinhousehold" id="numberinhousehold" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Household size"/>
                                        <input type="hidden" name="lotid" value="<?php echo $id; ?>"/>
                                        <input type="submit" value="Add" data-role="button" data-icon="plus" data-theme="d"/>
                                        </fieldset>
                                        <div class="ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left">Select the name of the owner, acquisition date and the household size.</div>
                                    </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                            <div class="ui-corner-all">
                                <header data-role="header" class="ui-bar-d ui-bar">
                                  Payment
                                </header>
                                <div class="ui-body-a ui-content">
                                    <table id="lotpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                        <thead>
                                            <tr>
                                                <th data-priority="1">Date</th>
                                                <th data-priority="3">OR Number</th>
                                                <th data-priority="1">Month</th>
                                                <th data-priority="4">Paid by</th>
                                                <th data-priority="2">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <script type="text/javascript">
                                $(document).on("pagecreate",function(){
                                    try{
                                        pl = $("#lotpaymentlist").dataTable({
                                            ajax:"./lotpaymentss?id=<?php echo $lid; ?>",
                                            columns:[
                                                {data:"paymentdate"},
                                                {data:"ornumber"},
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
                                                    "targets":[4]
                                                },
                                                {
                                                    "render":function(data,type,row){
                                                        return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'+row.id+'">'+data+'</a>';
                                                    },
                                                    "targets":[0,1,3,]
                                                },
                                                {
                                                    "render":function(data,type,row){
                                                        sd=new Date(row['startdate']);
                                                        ed=new Date(row['enddate']);
                                                        out="";
                                                        
                                                        if((!isNaN(sd.getMonth()))&&(!isNaN(ed.getMonth())))
                                                        {
                                                            if((sd.getMonth()===ed.getMonth())&&(sd.getYear()===ed.getYear()))
                                                            {
                                                                out = monthnames[ed.getMonth()]+" "+ed.getFullYear();
                                                            }
                                                            else
                                                            {
                                                                out = monthnames[sd.getMonth()]+" "+sd.getFullYear()+"-"+monthnames[ed.getMonth()]+" "+ed.getFullYear();
                                                            }
                                                        }
                                                        return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'+row.id+'">'+out+'</a>';
                                                    },
                                                    "targets":[2]
                                                },
                                                {
                                                    "visible":false,
                                                    "targets":[5]
                                                }
                                            ],
                                            order:[[0,"desc"]],
                                            retrieve:true
                                        });
                                        
//                                        ul = $("#tblUserList").dataTable({
//                                            ajax:"./ownerlistss",
//                                            columns:[
//                                                {data:"id"},
//                                                {data:"name"},
//                                                {data:"contactno"},
//                                                {data:"email"}
//                                            ],
//                                            columnDefs:[
//                                                {
//                                                    "render":function(data,type,row){
//                                                        return '<a href="#" class="tablecelllink paymentdetailslink" data-ajax="false">'+data+'</a>';
//                                                    },
//                                                    "targets":[0,1,2,3]
//                                                }
//                                            ],
//                                            order:[[0,"desc"]],
//                                            retrieve:true
//                                        });

//                                        var plapi=pl.api();
//                                        var ulapi=ul.api();

                                        $("#lotpaymentlist").on( "draw.dt", function() {
                                            $("a.paymentdetailslink").click(function(){
                                                changeIFrameSrc($(this)[0].dataset.ledgerid);
                                            });
                                        });

                                        $("#lotpaymentlist").on( "init.dt", function() {
                                            $("#lotpaymentlist_wrapper").enhanceWithin();
//                                            $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                            $("#lotpaymentlist_filter input").on("change",function(){
                                                pl.search($(this).val()).draw();
                                            });
                                        });
                                        
//                                        $("#tblUserList").on( "init.dt", function() {
//                                            $("#tblUserList_wrapper").enhanceWithin();
//                                            $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
//                                            $("#tblUserList_filter input").on("change",function(){
//                                                ulapi.search($(this).val()).draw();
//                                            });
//                                        });

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
                                })
                                
                                //$.mobile.document
                                    // The custom selectmenu plugin generates an ID for the listview by suffixing the ID of the
                                    // native widget with "-menu". Upon creation of the listview widget we want to place an
                                    // input field before the list to be used for a filter.
                                    .on( "listviewcreate", "#filter-menu-menu,#owner-filter-menu-menu", function( event ) {
                                        var input,
                                            list = $( event.target ),
                                            form = list.jqmData( "filter-form" );
                                        // We store the generated form in a variable attached to the popup so we avoid creating a
                                        // second form/input field when the listview is destroyed/rebuilt during a refresh.
                                        if ( !form ) {
                                            input = $( "<input data-type='search'></input>" );
                                            form = $( "<form></form>" ).append( input );
                                            input.textinput();
                                            list
                                                .before( form )
                                                .jqmData( "filter-form", form ) ;
                                            form.jqmData( "listview", list );
                                        }
                                        // Instantiate a filterable widget on the newly created listview and indicate that the
                                        // generated input form element is to be used for the filtering.
                                        list.filterable({
                                            input: input,
                                            children: "> li:not(:jqmData(placeholder='true'))"
                                        });
                                    })
                                    // The custom select list may show up as either a popup or a dialog, depending on how much
                                    // vertical room there is on the screen. If it shows up as a dialog, then the form containing
                                    // the filter input field must be transferred to the dialog so that the user can continue to
                                    // use it for filtering list items.
                                    .on( "pagecontainerbeforeshow", function( event, data ) {
                                        var listview, form,
                                            id = data.toPage && data.toPage.attr( "id" );
                                        if ( !( id === "filter-menu-dialog" || id === "owner-filter-menu-dialog" ) ) {
                                            return;
                                        }
                                        listview = data.toPage.find( "ul" );
                                        form = listview.jqmData( "filter-form" );
                                        // Attach a reference to the listview as a data item to the dialog, because during the
                                        // pagecontainerhide handler below the selectmenu widget will already have returned the
                                        // listview to the popup, so we won't be able to find it inside the dialog with a selector.
                                        data.toPage.jqmData( "listview", listview );
                                        // Place the form before the listview in the dialog.
                                        listview.before( form );
                                    })
                                    // After the dialog is closed, the form containing the filter input is returned to the popup.
                                    .on( "pagecontainerhide", function( event, data ) {
                                        var listview, form,
                                            id = data.toPage && data.toPage.attr( "id" );
                                        if ( !( id === "filter-menu-dialog" || id === "owner-filter-menu-dialog" ) ) {
                                            return;
                                        }
                                        listview = data.toPage.jqmData( "listview" ),
                                        form = listview.jqmData( "filter-form" );
                                        // Put the form back in the popup. It goes ahead of the listview.
                                        listview.before( form );
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
                $json["data"]=array();
                
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
//            var_dump($_POST);
            if(isLoggedIn())
            {
                global $conn;
                $qs=false;
                dbConnect();
                $conn->autocommit(FALSE);
                
                $stmt=$conn->prepare("INSERT INTO ledger(ornumber,paymentdate,payee,homeowner,user,remarks) VALUES(?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $ornumber=filter_input(INPUT_POST, "ornumber");
                $paymentdate=filter_input(INPUT_POST, "paymentdate");
                $homeowner=filter_input(INPUT_POST, "homeowner");
                $payee=filter_input(INPUT_POST, "payee");
                $remarks=filter_input(INPUT_POST, "remarks");
                
                $stmt->bind_param('sssiis',$ornumber,$paymentdate,$payee,$homeowner,$userid,$remarks);
                $qs=$stmt->execute();
                $ledgerid=$stmt->insert_id;
                $stmt->close();
                

                $amount=filter_input_array(INPUT_POST)["amt"];
                
                if(array_key_exists("lot", $amount))
                {
                    foreach($amount["lot"] as $key => $lot)
                    {
                        if($qs)
                        {
                            if($lot['amount']>0)
                            {
                                $stmt=$conn->prepare("INSERT INTO ledgeritem(id,amount,lot,`desc`,startdate,enddate) VALUES(?,?,?,?,?,?)");
                                if($stmt === false) {
                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                }
                                $startdate=$lot['start']."-01";
                                $enddate=date("Y-m-t", strtotime($lot['end']."-01"));

                                $stmt->bind_param('idisss',$ledgerid, $lot['amount'], $key, $lot['lotcode'], $startdate, $enddate);
                                $qs=$stmt->execute();
                                $stmt->close();
                            }
                        }
                        else 
                        {
                            break 1;
                        }
                    }
                }
                
                if(array_key_exists("misc", $amount))
                {
                    foreach($amount["misc"] as $key => $lot)
                    {
//                        var_dump($lot['desc']);
                        if($qs)
                        {
                            if($lot['amount']>0)
                            {
                                $stmt=$conn->prepare("INSERT INTO ledgeritem(id,amount,lot,`desc`) VALUES(?,?,?,?)");
                                if($stmt === false) {
                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                }
                                $ldesc=$lot['desc'];
                                $lamount = $lot['amount'];
                                $lotid=0;

                                $stmt->bind_param('idis',$ledgerid, $lamount, $lotid, $ldesc);
                                $qs=$stmt->execute();
                                $stmt->close();
                            }
                        }
                        else 
                        {
                            break 1;
                        }
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
                    array('db'=>'a.paymentdate','dt'=>0,"alias"=>"paymentdate",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.'<!--'.date("Ymd",  strtotime($d)).'-->'.$d.'</a>';}),
                    array('db'=>'a.ornumber','dt'=>1,"alias"=>"ornumber",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'a.payee','dt'=>2,"alias"=>"payee",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'SUM(b.amount)','dt'=>3,"alias"=>"amount",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.number_format($d,2).'</a>';}),
                    array('db'=>'a.id','dt'=>4,"alias"=>"uid")     
                );
                $addwhere="a.id=b.id AND (c.id=b.lot OR b.lot=0) AND a.homeowner=".filter_input(INPUT_GET, "id");
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
                    
                    $stmt=$conn->prepare("SELECT a.id,a.amount,a.lot,a.startdate,a.enddate,b.code FROM ledgeritem a LEFT JOIN lot b ON a.lot=b.id WHERE a.id=?");
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
                                    <th>Month</th>
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
                                    <td><?php echo ((date("n",strtotime($startdate))==date("n",strtotime($enddate)))&&(date("Y",strtotime($startdate))==date("Y",strtotime($enddate))))?date("M Y",  strtotime($enddate)):date("M Y-",  strtotime($startdate)).date("M Y",  strtotime($enddate)); ?></td>
                                    <td><?php echo number_format($amount,2); ?></td>
                                </tr>
                        <?php } ?>
                                <tr>
                                    <th colspan="2">Total</th>
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
        case "savesettings":
            if(isLoggedIn())
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE `settings` SET `subdname`=?,`brgy`=?,`city`=?,`province`=?,`zipcode`=?,`contactno`=?,`email`=?,`price`=?,`interest`=?,`intgraceperiod`=? WHERE id=?");
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
            }else{header("Location: ./");}
        case "report":
            
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("SELECT * FROM homeowner");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
//                $postusername=filter_input(INPUT_POST, "uid");
//                $postpassword=md5(filter_input(INPUT_POST, "password"));
//                $stmt->bind_param('ss',$postusername,$postpassword);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows>0)
                {
                    $stmt->bind_result($_SESSION['uid'],$_SESSION['fullname'],$_SESSION['username'], $_SESSION['permission']);
                    while($stmt->fetch()){}
                    $_SESSION['permlist']=  parsePermission($_SESSION['permission']);
                    //writeLog($_SESSION["fullname"]."(".$_SESSION["uid"].") logged in to the system.");

                    $stmt2=$conn->prepare("SELECT `id`, `subdname`, `brgy`, `city`, `province`, `zipcode`, `contactno`, `email`, `price`, `interest`, `intgraceperiod` FROM `settings` WHERE id=?");
                    if($stmt2 === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $settingsid=DT_SETTINGS_ID;
                    $stmt2->bind_param('i',$settingsid);
                    $stmt2->execute();
                    $stmt2->store_result();
                    if($stmt->num_rows==1)
                    {
                        $stmt2->bind_result($_SESSION['settings']['id'],$_SESSION['settings']['subdname'],$_SESSION['settings']['brgy'],$_SESSION['settings']['city'],$_SESSION['settings']['province'],$_SESSION['settings']['zipcode'],$_SESSION['settings']['contactno'],$_SESSION['settings']['email'],$_SESSION['settings']['price'],$_SESSION['settings']['interest'],$_SESSION['settings']['intgraceperiod']);
                        while($stmt2->fetch()){}
                    }
                    $stmt2->free_result();
                    $stmt2->close();
                }
                else
                {
                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
                }
                $stmt->close();
                dbClose();
            ?>
                
                <!DOCTYPE html>
                <html>
                  <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Report</title>
<!--                    <link rel="stylesheet" href="./css/jquery.mobile.structure-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile.theme-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile.external-png-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile.icons-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile.inline-png-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/jquery.mobile.inline-svg-1.4.3.min.css" />
                    <link rel="stylesheet" href="./css/staisabelgreen.min.css" />-->
                    <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables.min.css" />
                    <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables_themeroller.min.css" />
                    <link rel="stylesheet" href="./plugin/DataTables-1.10.0/integration/bootstrap/bin/bootstrap.css" />
                    <link rel="stylesheet" href="./plugin/DataTables-1.10.0/integration/bootstrap/bin/dataTables.bootstrap.css" />
                    <link rel="stylesheet" href="./plugin/DataTables-1.10.0/extensions/TableTools/css/dataTables.tableTools.min.css" />

                    <link rel="stylesheet" href="./css/default.css" />
                    <script src="./js/jquery-2.1.1.min.js"></script>
<!--                    <script src="./js/overridejqm.js"></script>-->
                    <!--<script src="./js/jquery.mobile-1.4.3.min.js"></script>-->
                    <script src="./plugin/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>
                    <script src="./plugin/DataTables-1.10.0/integration/bootstrap/bin/dataTables.bootstrap.js"></script>
                    <!--<script src="./plugin/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js"></script>-->
                    <!--<script src="./js/default.js"></script>-->
                    
                    <script type="text/javascript">
                        
                    </script>
                  </head>
                  <body>
                      
                      
                      
                  </body>
                </html>  
            <?php
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
