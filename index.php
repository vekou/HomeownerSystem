<?php
//Initialize script 
ob_start();
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
                
                $stmt2=$conn->prepare("SELECT `id`, `assocname`, `acronym`, `subdname`, `brgy`, `city`, `province`, `zipcode`, `contactno`, `email`, `price`, `interest`, `intgraceperiod` FROM `settings` WHERE id=?");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $settingsid=DT_SETTINGS_ID;
                $stmt2->bind_param('i',$settingsid);
                $stmt2->execute();
                $stmt2->store_result();
                if($stmt->num_rows==1)
                {
                    $stmt2->bind_result($_SESSION['settings']['id'],$_SESSION['settings']['assocname'],$_SESSION['settings']['acronym'],$_SESSION['settings']['subdname'],$_SESSION['settings']['brgy'],$_SESSION['settings']['city'],$_SESSION['settings']['province'],$_SESSION['settings']['zipcode'],$_SESSION['settings']['contactno'],$_SESSION['settings']['email'],$_SESSION['settings']['price'],$_SESSION['settings']['interest'],$_SESSION['settings']['intgraceperiod']);
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO homeowner(lastname,firstname,middlename,contactno,email,user,bond,bonddesc) VALUES(?,?,?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $plastname=filter_input(INPUT_POST, "plastname");
                $pfirstname=filter_input(INPUT_POST, "pfirstname");
                $pmiddlename=filter_input(INPUT_POST, "pmiddlename");
                $pcontactno=filter_input(INPUT_POST, "pcontactno");
                $pemail=filter_input(INPUT_POST, "pemail");
                $pbond=filter_input(INPUT_POST, "pbond");
                $pbonddesc=filter_input(INPUT_POST, "pbonddesc");
                $stmt->bind_param('sssssiss',$plastname,$pfirstname,$pmiddlename,$pcontactno,$pemail,$userid,$pbond,$pbonddesc);
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE homeowner SET `lastname`=?,`firstname`=?,`middlename`=?,`contactno`=?,`email`=?, `bond`=?, `bonddesc`=?, `gatepass`=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=filter_input(INPUT_POST, "uid");
                $plastname=filter_input(INPUT_POST, "plastname");
                $pfirstname=filter_input(INPUT_POST, "pfirstname");
                $pmiddlename=filter_input(INPUT_POST, "pmiddlename");
                $pcontactno=filter_input(INPUT_POST, "pcontactno");
                $pemail=filter_input(INPUT_POST, "pemail");
                $pbond=filter_input(INPUT_POST,"pbond");
                $pbonddesc=filter_input(INPUT_POST,"pbonddesc");
                $pgatepass=filter_input(INPUT_POST, "pgatepass")||0;
                $stmt->bind_param('sssssdsii',$plastname,$pfirstname,$pmiddlename,$pcontactno,$pemail,$pbond,$pbonddesc,$pgatepass,$userid);
                $stmt->execute();
//                $newuserid = $stmt->insert_id;
//                var_dump($pbond);
                $stmt->close();

                setNotification("$plastname, $pfirstname ".substr($pmiddlename, 0, 1).". has been updated.");
                dbClose();
                header("Location: ./homeowner?id=".$userid);
            }
            else{header("Location: ./");}
            break;
        case "removehomeowner":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_USERMGMNT))
            {
                displayHTMLPageHeader(); ?>
                <a href="#addUserForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add User</a>
                <div data-role="popup" id="addUserForm" data-dismissible="false" data-overlay-theme="b">
                  <header data-role="header">
                    <h1>User</h1>
                    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                  </header>
                  <div role="main" class="ui-content">
                    <form action="adduser" method="post" data-ajax="false">
                        
                        <label for="pusername">Username</label>
                        <input id="pusername" name="pusername" type="text" required="true"/>
                        <label for="ppassword">Password</label>
                        <input id="ppassword" name="ppassword" type="password" onchange="$('#pconfirm').prop('pattern',this.value);" required="true"/>
                        <label for="pconfirm">Confirm Password</label>
                        <input id="pconfirm" name="pconfirm" type="password" required="true"/>
                        <label for="plastname">Full Name</label>
                        <input id="pfullname" name="pfullname" type="text" required="true"/>
                        <fieldset data-role="controlgroup">
                            <legend>Permissions</legend>
                            <input type="checkbox" name="p[]" id="checkbox01" value="1" checked="">
                            <label for="checkbox01">Login</label>
                            <input type="checkbox" name="p[]" id="checkbox02" value="2">
                            <label for="checkbox02">Reports</label>
                            <input type="checkbox" name="p[]" id="checkbox03" value="4" checked="">
                            <label for="checkbox03">Lot Management</label>
                            <input type="checkbox" name="p[]" id="checkbox04" value="8">
                            <label for="checkbox04">Homeowner Management</label>
                            <input type="checkbox" name="p[]" id="checkbox05" value="16">
                            <label for="checkbox05">Payments</label>
                            <input type="checkbox" name="p[]" id="checkbox06" value="32">
                            <label for="checkbox06">User Management</label>
                        </fieldset>
                        <input type="submit" data-role="button" value="Submit" data-theme="d" />
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
<!--                            <th>ID</th>-->
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
        case "adduser":
            if(isLoggedIn() && checkPermission(DT_PERM_USERMGMNT))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO user(`fullname`,`username`,`password`,`permission`) VALUES(?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $pfullname=filter_input(INPUT_POST, "pfullname");
                $pusername=filter_input(INPUT_POST, "pusername");
                $ppassword=md5(filter_input(INPUT_POST, "ppassword"));
                $pcount=filter_input_array(INPUT_POST)["p"];

                $permission=0;
                while(list($key,$val)=each($pcount)) {
                    $permission += intval($val);
                }
                
                $stmt->bind_param('sssi',$pfullname,$pusername,$ppassword,$permission);
                $stmt->execute();
                if($conn->errno==1062)
                {
                    $stmt->close();
                    setNotification("Username is already in use. Please specify another username.",DT_NOTIF_ERROR);
                    dbClose();
                    header("Location: ./users");
                }
                else
                {
                    $newuserid = $stmt->insert_id;
                    $stmt->close();
                    setNotification($pfullname." has been added.");
                    dbClose();
                    header("Location: ./users");
                }
            }
            else{header("Location: ./");}
            break;
        case "homeowner":
            if((!is_null(filter_input(INPUT_GET, "id")))&&(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT)))
            {
                displayHTMLPageHeader();
                global $conn;
                dbConnect();
                $uid = filter_input(INPUT_GET, "id");

                $stmt=$conn->prepare("SELECT id,lastname,firstname,middlename,contactno,email,user,dateadded,active,bond,bonddesc,gatepass FROM homeowner WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }

                $postusername=filter_input(INPUT_POST, "uid");
                $stmt->bind_param('i',$uid);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0)
                {
                    $stmt->bind_result($id,$lastname,$firstname,$middlename,$contactno,$email,$user,$dateadded,$active,$bond,$bonddesc,$gatepass);
                    while($stmt->fetch()){ 
                        $lotlist=array();
                        $stmt2=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.caretaker,a.dateadded,a.user,a.active FROM lot a,settings f WHERE a.homeowner=? AND a.active=1 GROUP BY a.id");
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
                        
                        <?php displayHomeownerForm("./updatehomeowner",$lastname,$firstname,$middlename,$contactno,$email,$id,$bond,$bonddesc,$gatepass); ?>
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
                            <li data-role="list-divider">Bonds</li>
                            <li data-icon="false"><img src="css/images/icons-png/lock-black.png" alt="phone" class="ui-li-icon ui-corner-none"/><?php echo "<span class='infoheader'>Amount:</span> ".number_format($bond,2); ?></li>
                            <li data-icon="false"><img src="css/images/icons-png/lock-black.png" alt="phone" class="ui-li-icon ui-corner-none"/><?php echo "<span class='infoheader'>Description:</span> ".$bonddesc; ?></li>
                            <!--<li data-icon="false"><img src="css/images/icons-png/star-black.png" alt="phone" class="ui-li-icon ui-corner-none"/><?php echo "<span class='infoheader'>Sticker:</span> ".($gatepass==1?"Yes":"No"); ?></li>-->
                        </ul>                        
                        
                        <div>
                            <div data-role="tabs" id="tabs">
                                <div data-role="navbar">
                                    <ul>
                                      <li><a href="#chargesTab" data-ajax="false" class="ui-btn-active">Charges</a></li>
                                      <li><a href="#paymentsTab" data-ajax="false">Payments</a></li>
                                      <li><a href="#lotsTab" data-ajax="false">Registered Lots</a></li>
                                      <li><a href="#stickerTab" data-ajax="false">Gate Pass Stickers</a></li>
                                    </ul>
                                </div>
                                
                                <div id="chargesTab" class="ui-body-d ui-content">
                                    <div>
                                        
                                        <table id="tblchargeslist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Credit</th>
                                                    <th>Debit</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>                                            

                                            </tbody>
                                            <?php
                                                $stmt5=$conn->prepare("SELECT COALESCE(SUM(a.amount),0), COALESCE(SUM(d.amountpaid*e.active),0) FROM charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid WHERE a.homeowner=? AND a.active=1");
                                                if($stmt5 === false) {
                                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                                }
                                                $stmt5->bind_param('i',$householdid);
                                                $stmt5->execute();
                                                $stmt5->store_result();
                                                $stmt5->bind_result($tamount,$tamountpaid);

                                                while($stmt5->fetch()){}
                                                $stmt5->free_result();
                                                $stmt5->close();
                                                
                                                $totalbalance=$tamount-$tamountpaid;
                                            ?>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3">Total</th>
                                                    <th class="textamount" ><?php echo number_format($tamount,2); ?></th>
                                                    <th class="textamount" ><?php echo number_format($tamountpaid,2); ?></th>
                                                    <th class="textamount" ><?php echo number_format($totalbalance,2); ?></th>
                                                </tr>
                                            </tfoot>
                                        </table>                                        
                                    </div>
                                </div>
                                <div id="paymentsTab" class="ui-body-d ui-content">
                                    <div>
                                        <a href="./charges?id=<?php echo $uid; ?>" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d">Add Payment</a>
                                        <table id="tblpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                            <thead>
                                                <tr>
                                                    <th data-priority="1">Date</th>
                                                    <th data-priority="1">Mode of Payment</th>
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
                                                <tr><th>Household size</th><td><?php echo $numberinhousehold; ?></td></tr>
<!--                                                <tr><th>Arrears</th><td><?php // echo number_format($arrears,2); ?></td></tr>--></table>
                                                <a href='./lot?id=<?php echo $id; ?>' data-role='button' data-icon='info' data-iconpos='left' data-inline="true" data-theme="d">Lot Details</a>
                                                </li><?php 
                                                $lotinfo=array();
                                                $lotinfo["id"]=$id;
                                                $lotinfo["lotsize"]=$lotsize;
                                                $lotinfo["lotcode"]=$code;
                                                $lotinfo["address"]=$housenumber." Lot ".$lot." Block ".$block." ".$street." Phase ".$phase;
//                                                $lotinfo["arrears"]=$arrears;
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
                                <div id="stickerTab" class="ui-body-d ui-content">
                                    <div>
                                        <a href="#stickerLot" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="<?php echo ($totalbalance>0?'ui-disabled':''); ?>">Add Gate Pass Sticker</a>
                                        <div data-role="popup" id="stickerLot" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                                            <header data-role="header">
                                                <h1>Gate Pass Sticker</h1>
                                                <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                            </header>
                                            <div role="main" class="ui-content">
                                                <form method="post" action="./addsticker">
                                                    <label for="sserial">Control Number</label>
                                                    <input type="text" id="sserial" name="sserial"/>

                                                    <label for="splateno">Plate Number</label>
                                                    <input type="text" id="splateno" name="splateno"/>

                                                    <label for="smodel">Vehicle Model</label>
                                                    <input type="text" id="smodel" name="smodel"/>

                                                    <label for="sremarks">Remarks</label>
                                                    <textarea name="sremarks" id="sremarks"></textarea>

                                                    <input type="hidden" name="shomeowner" value="<?php echo $householdid; ?>"/>
                                                    <input type="submit" value="Submit"/>
                                                </form>
                                            </div>
                                        </div>
                                        <?php if($totalbalance>0){echo "<div class='ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left ui-shadow' style='margin-bottom: 0.5em; background-color: #FF9;'>You cannot add a Gate Pass Sticker if the homeowner has an outstanding balance.</div>"; } ?>
                                        
                                        <div data-role="popup" id="confirmStickerDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                                            <header data-role="header">
                                              <h1>Delete Gate Pass?</h1>
                                              <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                            </header>
                                            <div role="main" class="ui-content">
                                                <div>Delete this Gate Pass Sticker from User?</div>
                                                <fieldset data-role="controlgroup" data-type="horizontal">
                                                    <a href="./deletesticker" data-role="button" id="delstickerbtn" data-theme="d">Delete</a>
                                                    <a href="./homeowner?id=<?php echo $householdid; ?>" data-role="button" data-rel="back">Cancel</a>
                                                </fieldset>
                                            </div>
                                        </div>
                                        
                                        <table id="tblstickerlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                            <thead>
                                                <tr>
                                                    <th data-priority="1">Date</th>
                                                    <th data-priority="1">Control No.</th>
                                                    <th data-priority="3">Plate No.</th>
                                                    <th data-priority="4">Model</th>
                                                    <th data-priority="2" colspan="2">Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                <?php
                                                    $stmt2=$conn->prepare("SELECT id,serial,plateno,model,remarks,transactiondate FROM gatepass WHERE homeowner=?");
                                                    if($stmt2 === false) {
                                                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                                    }
                                                    $stmt2->bind_param('i',$householdid);
                                                    $stmt2->execute();
                                                    $stmt2->store_result();
                                                    $stmt2->bind_result($sid,$sserial,$splateno,$smodel,$sremarks,$stransactiondate);
                                                    while($stmt2->fetch()){ ?>
                                                        <tr>
                                                            <td><?php echo $stransactiondate; ?></td>
                                                            <td><?php echo $sserial; ?></td>
                                                            <td><?php echo $splateno; ?></td>
                                                            <td><?php echo $smodel; ?></td>
                                                            <td><?php echo $sremarks; ?></td>
                                                            <td class="textamount"><a href="./deletesticker?id=<?php echo $sid; ?>&hid=<?php echo $householdid; ?>" data-role="button" data-icon="delete" data-iconpos="notext" data-theme="b" class="delsticker" data-sid="<?php echo $sid; ?>">Delete Gate Pass Sticker</a></td>
                                                        </tr>
                                                    <?php }
                                                    $stmt2->free_result();
                                                    $stmt2->close();
                                                ?>
                                            </tbody>
                                        </table>
<!--                                        <div data-role="popup" id="popupReceipt" data-overlay-theme="a" data-theme="a" data-corners="false" data-tolerance="15,15">
                                            <a href="#" data-rel="back" class="ui-btn ui-btn-b ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                            <iframe id="paymentdetailsframe" src="" width="640" height="480" seamless=""></iframe>
                                        </div>-->
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script type="text/javascript">
                            var additioncounter=0;
                            var itemcounter=0;
                            var clapi;
                            $(document).on("pagecreate",function(){
                                
                                try{
                                    pl = setAsDataTable("#tblpaymentlist","./paymentlistss?id=<?php echo $uid; ?>",[{"targets":[5],"visible":false,"searchable":false}],[[0,"desc"]]);
                                    cl = setAsDataTable("#tblchargeslist","./chargelistss?id=<?php echo $uid; ?>",[
                                                {
                                                    "render":function(data,type,row){
                                                        return "<div class='textamount'>"+parseFloat(data).toFixed(2)+"</div>";
                                                    },
                                                    "targets":[3,4,5]
                                                },
                                                {
                                                    "visible":false,
                                                    "searchable":false,
                                                    "targets":[0]
                                                },
                                                {
                                                    "searchable":false,
                                                    "targets":[5]
                                                }
                                            ],[[1,"desc"]]);
//                                    cl = $("#tblchargeslist").dataTable({"processing":true,"retrieve":true,"autoWidth":false});
//                                    clapi=cl.api();
//                                    cl.fnAdjustColumnSizing();


                                    $("#tblpaymentlist").on( "draw.dt", function() {
                                        $("a.paymentdetailslink").click(function(){
                                            changeIFrameSrc($(this)[0].dataset.ledgerid);
                                        });
                                    });

                                    $("#tblpaymentlist, #tblchargeslist").on( "init.dt", function() {
                                        $("#tblpaymentlist_wrapper").enhanceWithin();
                                        $("#tblchargeslist_wrapper").enhanceWithin();
                                        $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                        $("#tblpaymentlist_filter input").on("change",function(){
                                            pl.search($(this).val()).draw();
                                        });
                                        $("#tblchargeslist_filter input").on("change",function(){
                                            cl.search($(this).val()).draw();
                                        });
                                    });
                                    
                                }catch(e){}
                                $("#popupReceipt").on({popupafterclose:function(){
                                  $("#paymentdetailsframe").remove();
                                }});
                                $("#popupLot").on({popupafterclose:function(){
                                  $("#lotdetailsframe").remove();
                                }});
                            
                                $(".delsticker").click(function(event){
                                    event.preventDefault();
                                    var sid=$(this).data("sid");
                                    $("#delstickerbtn").attr("href","./deletesticker?id="+sid+"&hid=<?php echo $householdid; ?>");
                                    $("#confirmStickerDelete").popup("open",{"transition":"pop"});
                                });

                                function changeIFrameSrc(lid){
                                    $("#popupReceipt").append('<iframe id="paymentdetailsframe" src="./paymentdetails?id='+lid+'" width="640" height="480" seamless=""></iframe>');
                                }
                                                                
                                $("#btnAddPayment").click(function(){
                                    additioncounter++;
                                    if($("#paymenttype").val() > 0)
                                    {
                                        $("#tblPaymentForm tbody").append('<tr><th><label title="'+$("#paymenttype option:selected").attr("data-infoaddress")+'">'+$("#paymenttype option:selected").attr("data-infocode")+'</label></th><td><input type="month" name="amt[lot]['+additioncounter+'][start]" id="lotstart'+$("#paymenttype option:selected").val()+'" required="true"/></td><td><input type="month" name="amt[lot]['+additioncounter+'][end]" id="lotend'+$("#paymenttype option:selected").val()+'" required="true"/></td><td><input type="number" step="0.01" name="amt[lot]['+additioncounter+'][amount]" id="lotamt'+$("#paymenttype option:selected").val()+'" value="0.00" class="textamount" required="true"/></td><td><a href="#" class="paymentitemremove" data-role="button" data-icon="delete" data-iconpos="notext">Remove</a><input type="hidden" name="amt[lot]['+additioncounter+'][lotcode]" value="'+$("#paymenttype option:selected").attr("data-infocode")+'"/><input type="hidden" name="amt[lot]['+additioncounter+'][lotid]" value="'+$("#paymenttype option:selected").val()+'"/></td></tr>').enhanceWithin();
                                        $("#paymentform").popup("reposition", {positionTo: 'window'});
                                    }
                                    else
                                    {
                                        $("#tblPaymentForm tbody").append('<tr><th colspan="3"><input type="text" name="amt[misc]['+additioncounter+'][desc]" placeholder="Description" required="true"/></th><td><input type="number" name="amt[misc]['+additioncounter+'][amount]" value="0.00" class="textamount" /></td><td><a href="#" class="paymentitemremove" data-role="button" data-icon="delete" data-iconpos="notext">Remove</a></td></tr>').enhanceWithin();
                                    }
                                    itemcounter++;
                                    $(".paymentitemremove").click(removePaymentItem);
                                    $("#paymentbutton").button("enable");
                                    $("#paymentbutton").button("refresh");
//                                    window.alert(itemcounter);
                                });
                                
                                function removePaymentItem(){
                                    $(this).parent().parent().remove();
                                    itemcounter--;
//                                    if(itemcounter<=0){
//                                        $("#paymentbutton").button("disable");
//                                        $("#paymentbutton").button("refresh");
//                                    }
//                                    window.alert(itemcounter);
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
        case "charges":
            if((!is_null(filter_input(INPUT_GET, "id")))&&(isLoggedIn()))
            {
                displayHTMLPageHeader();

                global $conn;
                dbConnect();
                $uid = filter_input(INPUT_GET, "id");
                
                $stmt=$conn->prepare("SELECT a.id, formatName(a.lastname,a.firstname,a.middlename) AS fullname FROM homeowner a WHERE a.id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('i',$uid);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows > 0)
                {
                    $stmt->bind_result($id,$fullname);
                    while($stmt->fetch()){ ?>
                        <fieldset data-role="controlgroup" data-type="horizontal" class="pagetitleheader"><div class="ui-btn ui-btn-d">Name</div> <div class="ui-btn"><?php echo $fullname; ?></div></fieldset>
                    <?php }
                }
                $stmt->free_result();
                $stmt->close();
                
                

                $stmt=$conn->prepare("SELECT a.id, a.dateposted, a.description, a.amount, SUM(COALESCE(c.amountpaid,0)) AS amtpaid FROM charges a LEFT JOIN ledgeritem c ON a.id=c.chargeid WHERE a.homeowner=? GROUP BY a.id HAVING a.amount>SUM(COALESCE(c.amountpaid,0)) ORDER BY a.dateposted");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('i',$uid);
                $stmt->execute();
                $stmt->store_result();

                if($stmt->num_rows > 0)
                {
                    $stmt->bind_result($id,$dateposted,$description,$amount, $amountpaid);?>
                        <form id="frmcharges" method="post" action="./addpayment">
<!--                            <fieldset data-role="controlgroup">-->
                                <label for="ornumber">OR Number</label>
                                <input type="text" name="ornumber" id="ornumber" required="true" />
                                <label for="payee">Paid by</label>
                                <input type="text" name="payee" id="payee" required="true" />
                                <fieldset data-role="controlgroup" data-type="horizontal">
                                    <legend>Mode of Payment</legend>
                                    <input type="radio" name="paymentmode" id="paymentmodecash" value="Cash" checked="checked">
                                    <label for="paymentmodecash">Cash</label>
                                    <input type="radio" name="paymentmode" id="paymentmodecheck" value="Check">
                                    <label for="paymentmodecheck">Check</label>
                                    <label for="checkno">Check Number</label>
                                    <input type="text" name="checkno" id="checkno" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Check Number" disabled="disabled"/>
                                </fieldset>
                                <label for="remarks">Remarks</label>
                                <textarea id="remarks" name="remarks"></textarea>
                                <input type="hidden" name="homeowner" value="<?php echo $uid; ?>"/>
                            <!--</fieldset>-->
<!--                        <ul data-role="listview" data-inset="true">
                            <li style="padding-left:40px;">
                                <span class="fauxtable textbold">Description</span><span class="fauxtable textbold">Date posted</span><span class="fauxtable textbold" style="text-align:right;">Credit</span><span class="fauxtable textbold" style="text-align:right;">Debit</span>
                            </li>
                        </ul>-->
                    
                        <!--<fieldset data-role="controlgroup">-->
                        <!--<legend>Vertical:</legend>-->
                        <table data-role="table" class="table table-striped table-bordered dt stripe ui-responsive" data-mode="reflow">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Date Posted</th>
                                    <th>Credit</th>
                                    <th>Debit</th>
                                </tr>
                            </thead>
                            <tbody>
                    <?php
                    $totalcredit=0;
                    while($stmt->fetch()){
                        $totalcredit += ($amount-$amountpaid);
                        ?>
                                <tr>
                                    <td style="vertical-align:middle;"><?php echo $description; ?></td>
                                    <td style="vertical-align:middle;"><?php echo $dateposted; ?></td>
                                    <td style="vertical-align:middle;" class="textamount"><?php echo number_format($amount-$amountpaid,2); ?></td>
                                    <td style="vertical-align:middle;" class="textamount debitcell">
                                        <input class="amtcell textamount" type="number" step="0.01" data-wrapper-class="ui-amtcell" name="charges[<?php echo $id; ?>][amountpaid]" id="amtpaid-<?php echo $id; ?>" value="0" />
                                        <input type="hidden" name="charges[<?php echo $id; ?>][id]" id="chkcharge-<?php echo $id; ?>" value="<?php echo $id; ?>">
                                        <input type="hidden" name="charges[<?php echo $id; ?>][amount]" id="amt-<?php echo $id; ?>" value="<?php echo $amount; ?>" />
                                        <input type="hidden" name="charges[<?php echo $id; ?>][description]" id="desc-<?php echo $id; ?>" value="<?php echo $description; ?>" />
                                    </td>
                                </tr>
<!--                        <input type="checkbox" name="charges[<?php echo $id; ?>][id]" id="chkcharge-<?php echo $id; ?>" value="<?php echo $id; ?>">
                        <label for="chkcharge-<?php echo $id; ?>"><span class="fauxtable"><?php echo $description; ?></span><span class="fauxtable"><?php echo $dateposted; ?></span><span class="fauxtable creditcell" style="text-align:right;"><?php echo number_format($amount,2); ?></span><span class="fauxtable debitcell" style="text-align:right;" id="txtcharge-<?php echo $id; ?>">0.00</span></label>
                        <input class="amtcell" type="hidden" name="charges[<?php echo $id; ?>][amountpaid]" id="amtpaid-<?php echo $id; ?>" value="0" />
                        <input type="hidden" name="charges[<?php echo $id; ?>][amount]" id="amt-<?php echo $id; ?>" value="<?php echo $amount; ?>" />
                        <input type="hidden" name="charges[<?php echo $id; ?>][description]" id="desc-<?php echo $id; ?>" value="<?php echo $description; ?>" />-->
                        <?php
                    }
                    ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total</th>
                                    <th class="textamount"><?php echo number_format($totalcredit,2); ?></th>
                                    <th class="textamount" id="totaldebit">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                            
                        <!--</fieldset>-->
                    
<!--                        <ul data-role="listview" data-inset="true">
                            <li style="padding-left:40px;">
                                <span class="fauxtable textbold">Total</span><span class="fauxtable textbold"></span><span class="fauxtable textbold" style="text-align:right;"><?php echo number_format($totalcredit,2); ?></span><span class="fauxtable textbold" style="text-align:right;" id="totaldebit">0.00</span>
                            </li>
                        </ul>-->
                            <fieldset data-role="controlgroup" data-type="horizontal">
                                <input type="submit" data-role="button" value="Submit" data-theme="d"/>
                                <a href="./homeowner?id=<?php echo $uid; ?>" data-rel="back" data-role="button" data-theme="a">Cancel</a>
                            </fieldset>
                            
                        </form>
                    <?php
                }
                $stmt->free_result();
                $stmt->close();
                ?>
                        <script type="text/javascript">
                            $(document).on("pagecreate",function(){
                                $("#frmcharges input.amtcell").change(function(){
//                                    var cid=$(this).val();
//                                    if($(this).is(":checked")){
//                                        var amtpaid=$(this).parent().children("label").children(".creditcell").text();
//                                        $(this).parent().children("label").children(".debitcell").text(amtpaid);
//                                        $("#amtpaid-"+cid).val(parseFloat(amtpaid.replace(/,/g,'')));
//                                    }else{
////                                        $("#txtcharge-"+id).textinput("disable").val("1.00");
//                                        $(this).parent().children("label").children(".debitcell").text("0.00");
//                                        $("#amtpaid-"+cid).val(0);
//                                    }
                                    $("#totaldebit").text(numberWithCommas(getTotalDebit().toFixed(2)));
                                });
                                
                                $("#paymentmodecash").click(function(){
                                    $("#checkno").textinput("disable");
                                });
                                $("#paymentmodecheck").click(function(){
                                    $("#checkno").textinput("enable");
                                });
                                
                                function getTotalDebit(){
                                    var t=0;
                                    $("#frmcharges .amtcell").each(function(index){
                                        t += parseFloat($(this).val());
                                    });
                                    return t;
                                }
                            });
                            
                            $(document).on("contextmenu", ".debitcell", function(e){
                                window.alert("partial");
                                return false;
                            });
                        </script>
                <?php
                displayHTMLPageFooter();
                dbClose();
            }
            break;
        case "addcharge":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO charges(homeowner,description,amount,uid,lot,winterest) VALUES(?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $homeowner=filter_input(INPUT_POST, "uid");
                $lid=filter_input(INPUT_POST, "lid");
                $pdescription=filter_input(INPUT_POST, "pdescription");
                $pamount=filter_input(INPUT_POST, "pamount");
                $pinterest=filter_input(INPUT_POST, "pinterest");
                if(!$pinterest){$pinterest=0;}
                $stmt->bind_param('isdiii',$homeowner,$pdescription,$pamount,$userid,$lid,$pinterest);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("$pdescription has been charged to homeowner.");
                dbClose();
                header("Location: ./lot?id=".$lid);
            }
            break;
        case "chargelistss":
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT))
            {
                //SELECT a.id, a.amountpaid, a.dateposted, a.description, a.amount FROM charges a WHERE a.amountpaid<a.amount AND a.homeowner=? ORDER BY a.dateposted
                $table = 'charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"id"),
                    array('db'=>'a.dateposted','dt'=>1,"alias"=>"dateposted"),
                    array('db'=>'a.description','dt'=>2,"alias"=>"description"),
                    array('db'=>'a.amount','dt'=>3,"alias"=>"amount"),
                    array('db'=>'COALESCE(d.amountpaid*e.active,0)','dt'=>4,"alias"=>"amountpaid"),
                    array('db'=>'(SUM(a.amount)-coalesce(SUM(d.amountpaid),0))','dt'=>5,"alias"=>"balance")
                );
                $addwhere="a.active=1 AND a.homeowner=".filter_input(INPUT_GET, "id");
                $group="GROUP BY a.id";
                $counttable="charges";
                if(!is_null(filter_input(INPUT_GET, "id")))
                {
                    $countwhere="active=1 AND homeowner=".filter_input(INPUT_GET, "id");
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
        case "addsticker":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO gatepass(serial,homeowner,plateno,model,remarks,userid) VALUES(?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $homeowner=filter_input(INPUT_POST, "shomeowner");
                $serial=filter_input(INPUT_POST, "sserial");
                $plateno=filter_input(INPUT_POST, "splateno");
                $model=filter_input(INPUT_POST, "smodel");
                $remarks=filter_input(INPUT_POST, "sremarks");
                
                $stmt->bind_param('sisssi',$serial,$homeowner,$plateno,$model,$remarks,$userid);
                $stmt->execute();
                $newuserid = $stmt->insert_id;
                $stmt->close();

                setNotification("Sticker $serial has been added.");
                dbClose();
                header("Location: ./homeowner?id=".$userid);
            }
            break;
        case "deletesticker":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("DELETE FROM gatepass WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $sid=filter_input(INPUT_GET, "id");
                $hid=filter_input(INPUT_GET, "hid");
                
                $stmt->bind_param('i',$sid);
                $stmt->execute();
                $stmt->close();

                setNotification("Sticker has been deleted.");
                dbClose();
                header("Location: ./homeowner?id=".$hid);
            }
            break;
        case "confirmdeletesticker":
            if(isLoggedIn()){
                if(!is_null($sid=filter_input(INPUT_GET, "id")))
                {
                displayHTMLHead(); ?>
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Confirm Delete?</h1>
                            <a href="./deletesticker?id=<?php echo $sid; ?>" data-role="button">Delete</a>
                            <a href="./homeowners" data-rel="back" data-role="button">Cancel</a>
                        </header>
                        <div data-role="main">
                        </div>
                    </div>
                        
            <?php
                displayHTMLFooter();
                }else{header("Location: ./");}
            }else{header("Location: ./");}
            break;
        case "addresident":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO resident(fullname,gender,household,status,user) VALUES(?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $fullname=filter_input(INPUT_POST, "pfullname");
                $gender=filter_input(INPUT_POST, "pgender");
                $lotid=filter_input(INPUT_POST, "lid");
                $status=filter_input(INPUT_POST, "pstatus");

                
                $stmt->bind_param('ssiii',$fullname,$gender,$lotid,$status,$userid);
                $stmt->execute();
                $stmt->close();

                setNotification("Resident $fullname has been added.");
                dbClose();
                header("Location: ./lot?id=".$lotid);
            }
            break;
        case "deleteresident":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("DELETE FROM resident WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $rid=filter_input(INPUT_GET, "id");
                $lid=filter_input(INPUT_GET, "lid");
                
                $stmt->bind_param('i',$rid);
                $stmt->execute();
                $stmt->close();

                setNotification("Resident has been deleted.");
                dbClose();
                header("Location: ./lot?id=".$lid);
            }
            break;
        case "confirmdeleteledger":
            if(isLoggedIn()){
                if(!is_null($lid=filter_input(INPUT_GET, "id")))
                {
                displayHTMLHead(); ?>
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Confirm Delete?</h1>
                        </header>
                        <div data-role="main" class="ui-content ui-body">
                            <form action="./deleteledger" method="post" target="_top">
                                <input type="hidden" name="ledgerid" value="<?php echo $lid; ?>"/>
                                <label for="cancelremarks">Reason for Cancellation</label>
                                <textarea required="true" id="cancelremarks" name="cancelremarks"></textarea>
                                <fieldset data-role="controlgroup" data-type="horizontal">
                                    <input type="submit" data-role="button" value="Delete" data-theme="e"/>
                                    <a href="./paymentdetails?id=<?php echo $lid; ?>" data-role="button" data-rel="back" data-theme="b">Cancel</a>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                        
            <?php
                displayHTMLFooter();
                }else{header("Location: ./");}
            }else{header("Location: ./");}
            break;
        case "deleteledger":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE ledger SET active=0, cancelremarks=?, canceluser=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lid=filter_input(INPUT_POST, "ledgerid");
                $cancelremarks=filter_input(INPUT_POST, "cancelremarks");
                $canceluser=$_SESSION["uid"];
                
                $stmt->bind_param('sii',$cancelremarks,$canceluser,$lid);
                $stmt->execute();
                $stmt->close();

                setNotification("Payment has been cancelled.");
                dbClose();
                header("Location: ./lots");
            }else{header("Location: ./");}
            break;
        case "deletecharges":
            if(isLoggedIn()){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE charges SET active=0, cancelremarks=?, canceluser=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lid=filter_input(INPUT_POST, "chargeid");
                $cancelremarks=filter_input(INPUT_POST, "cancelremarks");
                $canceluser=$_SESSION["uid"];
                
                $stmt->bind_param('sii',$cancelremarks,$canceluser,$lid);
                $stmt->execute();
                $stmt->close();

                setNotification("The charge has been reversed.");
                dbClose();
                header("Location: ./lots");
            }else{header("Location: ./");}
            break;
        case "homeowners":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
                                <th>ID</th>
                                <th>Name</th>
                                <th>Contact Number</th>
                                <th>Email Address</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <script type="text/javascript">
                    //var hol;
                    $(document).on("pagecreate",function(event, ui){
//                        try{
                            hol = setAsDataTable("#tblhomeownerlist","./homeownerlistss",[{"targets":[0],"visible":false,"searchable":false}],[[1,"asc"]]);
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
                            
//                            var tableTools = new $.fn.dataTable.TableTools( hol, {
//                            "buttons": [
//                                "copy",
//                                "csv",
//                                "xls",
//                                "pdf",
//                                { "type": "print", "buttonText": "Print me!" }
//                            ],
//                            "sSwfPath":"./plugin/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf"
//                        } );
//
//                        $( tableTools.fnContainer() ).appendTo('#ttools');
//                        }catch(e){}
                    });
                </script>
                <?php displayHTMLPageFooter();
            }
            break;
        case "inactivehomeowners":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
            {
                $table = 'homeowner a LEFT JOIN charges b ON a.id=b.homeowner LEFT JOIN ledgeritem d ON d.chargeid=b.id LEFT JOIN ledger e ON e.id=d.ledgerid';
                $primaryKey = 'id';
                $columns = array(
                    //array('db'=>'id','dt'=>0),
                    array('db'=>'formatName(a.lastname,a.firstname,a.middlename)','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"aliascols"=>"a.lastname,a.firstname,a.middlename"),
                    array('db'=>'a.contactno','dt'=>2,"alias"=>"contactno", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.email','dt'=>3,"alias"=>"email", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'(coalesce(SUM(b.amount),0)-SUM(coalesce(d.amountpaid,0)*coalesce(e.active,0)))*a.active','dt'=>4,"alias"=>"balance" ,'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";}),
                    array('db'=>'a.id','dt'=>0,"alias"=>"uid","aliascols"=>"a.id", 'formatter'=>function($d,$row){return "<a href='#' class='tblhomeownerlistbtn' data-role='button' data-iconpos='notext' data-icon='edit'>Edit</a>";})
                );
                $addwhere="a.active=1";
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
            if(isLoggedIn() && checkPermission(DT_PERM_USERMGMNT))
            {
                $table = 'user';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'id','dt'=>'DT_RowId'),
                    array('db'=>'username','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'fullname','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";})
                );
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns));
            }
            break;
        case "inactiveownerlistss":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET homeowner=?, dateacquired=?, numberinhousehold=?, numberinhouseholdc=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $homeowner=filter_input(INPUT_POST, "owner-filter-menu");
                $dateacquired=filter_input(INPUT_POST, "dateacquired");
                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
                $numberinhouseholdc=filter_input(INPUT_POST, "numberinhouseholdc");
                $lotid=filter_input(INPUT_POST, "lotid");
                if($homeowner==0)
                {
                    setNotification("No owner selected.",DT_NOTIF_WARNING);
                }
                else
                {
                    $stmt->bind_param('isiii',$homeowner,$dateacquired,$numberinhousehold,$numberinhouseholdc,$lotid);
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNERMGMNT))
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
            if(isLoggedIn() & checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
            {
                displayHTMLPageHeader(); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="#addLotForm" data-role="button" data-icon="plus" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Lot</a>
                    <a href="./inactivelots" data-role="button" data-icon="forbidden" data-theme="b">Deleted Lots</a>
                    <a href="./bill" data-role="button" data-icon="bars" data-theme="a" target="_blank">Print All Billing Statements</a>
                </fieldset>
<!--                <form action="./addlot" method="post" data-ajax="false">-->
                    <?php displayLotForm(); ?>
                <!--</form>-->
                <div class="ui-content ui-body ui-body-a ui-corner-all">
                    <table id="tbllotlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                        <thead>
                            <tr>
                                <th data-priority="5">ID</th>
                                <th data-priority="1">Lot Code</th>
                                <th data-priority="2">Address</th>
                                <th data-priority="4">Lot Size</th>
                                <th data-priority="3">Monthly Due</th>
                                <th data-priority="3">Owner</th>
                                <th data-priority="3">Balance</th>
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
                                {"targets":[0],"visible":false,"searchable":false},
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
            {
                $table = 'lot a LEFT JOIN homeowner b ON a.homeowner=b.id LEFT JOIN charges c ON a.id=c.lot AND c.active=1 LEFT JOIN ledgeritem d ON d.chargeid=c.id LEFT JOIN ledger e ON d.ledgerid=e.id INNER JOIN settings f ON f.id='.$_SESSION["settings"]["id"];
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"uid", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.code','dt'=>1,"alias"=>"code", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'formatAddress(a.housenumber,a.lot,a.block,a.street,a.phase)','dt'=>2, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"address","aliascols"=>"a.housenumber,a.lot,a.block,a.street,a.phase"),
                    array('db'=>'a.lotsize','dt'=>3,"alias"=>"lotsize", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'(a.lotsize*f.price)','dt'=>4, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";},"alias"=>"dues","aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'formatName(b.lastname,b.firstname,b.middlename)','dt'=>5,"alias"=>"homeowner", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'(SUM(c.amount)-coalesce(SUM(d.amountpaid*e.active),0)*b.active)','dt'=>6,"alias"=>"balance","aliascols"=>"c.amount,c.amountpaid",'DT_RowData'=>function($d,$row){return date('Ymd',  strtotime($row['enddate']));}, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";})
                );
                $addwhere="a.active=1";
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
            {
                $printview = filter_input(INPUT_GET, "print");
                if(is_null($printview)){$printview=0;}
                if(!is_null($lid=filter_input(INPUT_GET, "id")))
                {
                    global $conn;
                    dbConnect();
                    //$stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername FROM lot a, homeowner b WHERE a.homeowner=b.id AND a.id=?");
                    $stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.numberinhouseholdc,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername, a.active FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id WHERE a.id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$lid);
                    $stmt->execute();
                    $stmt->store_result();
                    if($stmt->num_rows==1)
                    {
                        if($printview<=0){displayHTMLPageHeader();}
                        $stmt->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $numberinhouseholdc, $active, $homeownername, $active);
                        while($stmt->fetch()){ ?><?php 
                            if($homeowner==0): 
                                if($active>0):?>
                                    <a href="#confirmLotDelete" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Remove Lot</a> <?php 
                                else: ?>
                                    <a href="./activatelot?id=<?php echo $id; ?>" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" class="editbtns" data-theme="a">Reactivate Lot</a> <?php
                                endif;
                            endif;?>
                            <fieldset data-role="controlgroup" data-type="horizontal" class="editbtns">
                                <a href="#addLotForm" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Lot</a>
                                <?php if($homeowner>0):?><a href="./bill?id=<?php echo $lid; ?>" data-role="button" data-icon="bars" data-iconpos="left" data-inline="true" class="editbtns" data-theme="a" target="_blank">Print Billing Statement</a><?php endif; ?>
                            </fieldset>
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
                                    
                            <div data-role="popup" id="addResident" data-dismissible="false" data-overlay-theme="b">
                                <header data-role="header">
                                  <h1>Add Resident</h1>
                                  <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <div role="main" class="ui-content">
                                    <form action="./addresident" method="post" data-ajax="false">
                                        <label for="pfullname">Full Name</label>
                                        <input id="pfullname" name="pfullname" type="text" placeholder="Full Name" />
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <legend>Gender</legend>
                                            <input type="radio" name="pgender" id="genderu" value="Unspecified" checked="checked">
                                            <label for="genderu">Unspecified</label>
                                            <input type="radio" name="pgender" id="genderm" value="Male">
                                            <label for="genderm">Male</label>
                                            <input type="radio" name="pgender" id="genderf" value="Female">
                                            <label for="genderf">Female</label>
                                        </fieldset>
                                        <label for="pstatus">Residential Classification</label>
                                        <select id="pstatus" name="pstatus" data-native-menu="false" required="true">
                                            <?php
                                            $stmt2=$conn->prepare("SELECT id,description FROM status ORDER BY description ASC");
                                            if($stmt2 === false) {
                                                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                            }
                                            $stmt2->execute();
                                            $stmt2->store_result();

                                            if($stmt2->num_rows>0){
                                                $stmt2->bind_result($stid,$stdescription);
                                                while($stmt2->fetch()):?>
                                                    <option value="<?php echo $stid; ?>" title="<?php echo $stdescription;?>"><?php echo $stdescription; ?></option><?php 
                                                endwhile;
                                            }
                                            $stmt2->free_result();
                                            $stmt2->close();
                                            ?>
                                        <input type="hidden" name="lid" value="<?php echo $lid; ?>"/>
                                        <input type="submit" value="Submit" data-icon="plus" data-theme="d"/>
                                    </form>
                                </div>
                             </div>
                                    
                                    
                            <div data-role="popup" id="showResidents" data-dismissible="false" data-overlay-theme="b">
                                <header data-role="header">
                                  <h1>Show Residents</h1>
                                  <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                </header>
                                <div role="main" class="">
                                    <table data-role="table" class="table table-striped table-bordered dt stripe ui-responsive">
                                        <thead>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Classification</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                            <?php
                                $stmt2=$conn->prepare("SELECT a.id,a.fullname,a.gender,b.description FROM resident a INNER JOIN status b ON a.status=b.id WHERE household=?");
                                if($stmt2 === false) {
                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                }
                                $stmt2->bind_param('i',$lid);
                                $stmt2->execute();
                                $stmt2->store_result();

                                if($stmt2->num_rows>0){
                                    $stmt2->bind_result($rid,$rfullname,$rgender,$rstatus);
                                    while($stmt2->fetch()): ?>
                                            <tr>
                                                <td><?php echo $rfullname; ?></td>
                                                <td><?php echo $rgender; ?></td>
                                                <td><?php echo $rstatus; ?></td>
                                                <td><a href="./deleteresident?id=<?php echo $rid; ?>&lid=<?php echo $lid; ?>" data-role="button" data-icon="delete" data-iconpos="notext" data-mini="true" class="delresident">Delete Resident</a></td>
                                            </tr>
                                    <?php endwhile;
                                }
                                $stmt2->free_result();
                                $stmt2->close();
                            ?>
                                        </tbody>
                                    </table>
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
                                <?php if($homeowner>0): 
                                    $stmt2=$conn->prepare("SELECT b.ischild, COUNT(a.id) FROM resident a INNER JOIN status b ON a.status=b.id WHERE household=? GROUP BY ischild");
                                    if($stmt2 === false) {
                                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                    }
                                    $stmt2->bind_param('i',$lid);
                                    $stmt2->execute();
                                    $stmt2->store_result();

                                    if($stmt2->num_rows>0){
                                        $stmt2->bind_result($ischild,$householdsize);
                                        while($stmt2->fetch()):
                                            if($ischild==1){
                                                $numberinhouseholdc=$householdsize;
                                            }else{
                                                $numberinhousehold=$householdsize;
                                            }
                                        endwhile;
                                    }
                                    $stmt2->free_result();
                                    $stmt2->close();
                                    ?>
                                    <li data-role="list-divider">Ownership</li>
                                    <li><a href="./homeowner?id=<?php echo $homeowner; ?>"><span class="infoheader">Name</span> <?php echo $homeownername; ?></a><a href="#confirmOwnerDelete" data-icon="delete" data-theme="b" data-rel="popup" data-position-to="window" data-transition="pop">Remove Owner</a></li>
                                    <li><span class="infoheader">Date Acquired</span> <?php echo $dateacquired; ?></li>
                                    <li><a href="#showResidents" data-rel="popup" data-position-to="window" data-transition="pop"><span class="infoheader">Household Size</span> <?php echo $householdsize; ?> (<?php echo $numberinhousehold; ?> Adult<?php echo ($numberinhousehold==1?"":"s"); ?>, <?php echo $numberinhouseholdc; ?> Child<?php echo ($numberinhouseholdc==1?"":"ren"); ?>)</a><a href="#addResident" data-icon="plus" data-theme="b" data-rel="popup" data-position-to="window" data-transition="pop">Add</a></li>
                                    
                                <?php elseif($active>0): ?>
                                    <li>
                                        <form data-ajax="false" method="post" action="./setasowner">
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <legend><span class="infoheader">Add Owner</span></legend>
                                        <label for="owner-filter-menu">Select lot owner</label>
                                        <select id="owner-filter-menu" name="owner-filter-menu" data-native-menu="false" required="true">
                                            <option>Select owner</option>
                                            <?php
                                            $stmt2=$conn->prepare("SELECT id,formatName(lastname,firstname,middlename) AS name,contactno,email FROM homeowner ORDER BY lastname ASC");
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
                                        <label for="numberinhousehold">Adults in Household</label>
                                        <input type="number" name="numberinhousehold" id="numberinhousehold" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Adults in Household"/>
                                        <label for="numberinhouseholdc">Children in Household</label>
                                        <input type="number" name="numberinhouseholdc" id="numberinhouseholdc" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Children in Household"/>
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
                                  Statement of Account
                                </header>
                                <div class="ui-body-a ui-content">
                                    <?php if($homeowner>0):?>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <a href="./charges?id=<?php echo $homeowner; ?>" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d">Add Payment</a>
                                        <a href="#addCharges" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d" data-rel="popup" data-position-to="window" data-transition="pop">Add Charge</a>
                                    </fieldset>
                                        <div data-role="popup" id="addCharges" data-dismissible="false" data-overlay-theme="b">
                                            <header data-role="header">
                                              <h1>Add Charges</h1>
                                              <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                            </header>
                                            <div role="main" class="ui-content">
                                                <form action="./addcharge" method="post" data-ajax="false">
                                                    <label for="pdescription">Charge Description</label>
                                                    <input id="pdescription" name="pdescription" type="text" required="true" placeholder="Description" />
                                                    <label for="pamount">Price</label>
                                                    <input id="pamount" name="pamount" type="number" required="true" value="0.00" step="0.01" />
                                                    <label for="pinterest">Apply interest</label>
                                                    <input type="checkbox" name="pinterest" id="pinterest" value="1" data-mini="true"/>
                                                    <input type="hidden" name="uid" value="<?php echo $homeowner; ?>"/>
                                                    <input type="hidden" name="lid" value="<?php echo $lid; ?>"/>
                                                    <input type="submit" value="Submit" data-icon="check" data-theme="d"/>
                                                </form>
                                            </div>
                                         </div>
                                    <?php endif; ?>
                                    
                                    <div data-role="popup" id="confirmChargeDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                                        <header data-role="header">
                                          <h1>Reverse Charges?</h1>
                                          <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                        </header>
                                        <div data-role="main" class="ui-content ui-body">
                                            <form action="./deletecharges" method="post" target="_top">
                                                <input type="hidden" name="chargeid" value="0" id="chargeid"/>
                                                <label for="cancelremarks">Reason for Cancellation</label>
                                                <textarea required="true" id="cancelremarks" name="cancelremarks"></textarea>
                                                <fieldset data-role="controlgroup" data-type="horizontal">
                                                    <input type="submit" data-role="button" value="Delete" data-theme="e"/>
                                                    <a href="./lot?id=<?php echo $lid; ?>" data-role="button" data-rel="back" data-theme="b">Cancel</a>
                                                </fieldset>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <table id="lotpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                        <thead>
                                            
                                            <tr>
                                                <!--<th data-priority="1">ID</th>-->
                                                <th data-priority="1" rowspan="2">Date Posted</th>
                                                <th data-priority="1" rowspan="2">Description</th>
                                                <th data-priority="4" colspan="3">Latest Payment</th>
                                                <th data-priority="1" rowspan="2">Credit</th>
                                                <th data-priority="1" rowspan="2">Debit</th>
                                                <th data-priority="1" rowspan="2">Balance</th>
                                                <th data-priority="1" rowspan="2">Ledger ID</th>
                                                <th data-priority="1" rowspan="2"></th>
                                            </tr>
                                            <tr>
                                                <th data-priority="3">Payment Date</th>
                                                <th data-priority="4">OR Number</th>
                                                <th data-priority="2">Paid By</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                        
                                        <?php
                                            $stmt4=$conn->prepare("SELECT m.amount,n.amountpaid FROM (SELECT a.id AS id, SUM(a.amount) AS amount FROM charges a WHERE a.active=1 AND a.lot=?) m INNER JOIN(SELECT a.id AS id, SUM(c.amountpaid*b.active) AS amountpaid FROM charges a LEFT JOIN ledgeritem c ON a.id=c.chargeid LEFT JOIN ledger b ON b.id=c.ledgerid WHERE a.active=1 AND a.lot=?) n ON m.id=n.id");
                                            if($stmt4 === false) {
                                                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                            }
                                            $stmt4->bind_param('ii',$lid,$lid);
                                            $stmt4->execute();
                                            $stmt4->store_result();
                                            if($stmt4->num_rows==1)
                                            {
                                                $stmt4->bind_result($ttamount,$ttamountpaid);
                                                while($stmt4->fetch()){
                                                }
                                            }
                                            $stmt4->free_result();
                                            $stmt4->close();
                                            
                                        ?>
                                        <tfoot>
                                            <tr>
                                                <!--<th data-priority="1"></th>-->
                                                <th data-priority="1" colspan="5">Total</th>
                                                <th data-priority="1"><?php echo number_format($ttamount,2); ?></th>
                                                <th data-priority="1"><?php echo number_format($ttamountpaid,2); ?></th>
                                                <th data-priority="1" class="textamount"><?php echo number_format($ttamount-$ttamountpaid,2); ?></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            
                            <script type="text/javascript">
                                $(document).on("pagecreate",function(){
//                                    try{
                                        var istblinit=false;
                                        pl = setAsDataTable("#lotpaymentlist","./lotpaymentss?id=<?php echo $lid; ?>",[
                                                {
                                                    "render":function(data,type,row){
                                                        return (row[8]==="0"?'<a class="textamount tablecelllink paymentdetailslink ui-link" style="display:inline-block;">'+parseFloat(data).toFixed(2)+'</a>':'<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink textamount" data-ledgerid="'+row[8]+'">'+parseFloat(data).toFixed(2)+'</a>');
                                                    },
                                                    "targets":[5,6,7,]
                                                },
                                                {
                                                    "render":function(data,type,row){
                                                        return (!row[8]?"<a class='tablecelllink paymentdetailslink ui-link'>"+(!data?"":data)+"</a>":'<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'+row[8]+'">'+(!data?"":data)+'</a>');
                                                    },
                                                    "targets":[0,1,2,3,4,8]
                                                },
                                                {
                                                    "visible":false,
                                                    "searchable":false,
                                                    "targets":[8]
                                                },
                                                {
                                                    "searchable":false,
                                                    "sortable":false,
                                                    "targets":[9]
                                                }
                                            ],[[0,"desc"]]
    );
                                        

                                        $("#lotpaymentlist").on( "init.dt", function() {
                                            $("#lotpaymentlist_wrapper").enhanceWithin();
//                                            $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                            $("#lotpaymentlist_filter input").on("change",function(){
                                                pl.search($(this).val()).draw();
                                            });
                                            
                                            istblinit=true;
                                        });
                                        
                                        function confirmdelcharge(event){
                                            event.preventDefault();
                                            var sid=$(this).data("cid");
                                            $("#chargeid").attr("value",sid);
                                            $("#confirmChargeDelete").popup("open",{"transition":"pop"});
                                        }
                                        
                                        $("#lotpaymentlist").on( "draw.dt", function() {
                                            $("a.paymentdetailslink[href]").click(function(){
                                                changeIFrameSrc($(this)[0].dataset.ledgerid);
                                            });
                                            
                                            if(istblinit)
                                            {
//                                                $(".delcharge").button();
                                            }
//                                            $(".delcharge").off("click",confirmdelcharge);
                                            $(".delcharge").on("click",confirmdelcharge);
                                        });
                                        
                                        $(".delresident").click(function(event){
                                            if(!confirm("Delete resident?"))
                                            {
                                                event.preventDefault();
                                            }
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
//                                    }catch(e){}


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
                        if($printview<=0){displayHTMLPageFooter();}
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
        case "lotprint":
            if(isLoggedIn() && checkPermission(DT_PERM_LOTMGMNT))
            {
                if(!is_null($lid=filter_input(INPUT_GET, "id")))
                {
                    global $conn;
                    dbConnect();
                    //$stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername FROM lot a, homeowner b WHERE a.homeowner=b.id AND a.id=?");
                    $stmt=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,a.numberinhousehold,a.numberinhouseholdc,a.active,CONCAT(b.lastname,', ',b.firstname,' ', SUBSTR(b.middlename,1,1),'.') AS homeownername, a.active FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id WHERE a.id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$lid);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if($stmt->num_rows==1)
                    {
                        $stmt->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $numberinhouseholdc, $active, $homeownername, $active);
                        while($stmt->fetch()){  ?>
                            
                         <!DOCTYPE html>
                            <html>
                              <head>
                                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1">
                                <title><?php echo "Lot Code: ".$code; ?></title>
                                <!--<link rel="stylesheet" href="./css/staisabelgreen.min.css" />-->
                                <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables.css" />
                                <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables_themeroller.css" />
                                <link rel="stylesheet" href="./plugin/DataTables-1.10.0/extensions/TableTools/css/dataTables.tableTools.css" />
                                <link rel="stylesheet" href="./css/reportstyle.css" />
                                <link rel="stylesheet" href="./css/default.css" />

                                <script src="./js/jquery-2.1.1.min.js"></script>
                                <script src="./plugin/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>
                                <script src="./plugin/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js"></script>

                              </head>
                              <body>   
                                  <h1>Lot Information</h1>
                            
                            
                            
                                  <div><span class="infoheader">Lot Code:</span> <?php echo $code; ?></div>
                                  <div><span class="infoheader">Address:</span> <?php echo $housenumber." ".$street.", Lot ".$lot." Block ".$block." Phase ".$phase; ?></div>
                                  <div><span class="infoheader">Lot Size:</span> <?php echo $lotsize; ?> sq. m.</div>
                                  <hr/>
                                  <?php if($homeowner>0): ?>
                                  <div><span class="infoheader">Homeowner:</span> <?php echo $homeownername; ?></div>
                                  <div><span class="infoheader">Date Acquired:</span> <?php echo $dateacquired; ?></div>
                                  <div><span class="infoheader">Household Size:</span> <?php echo $numberinhousehold+$numberinhouseholdc; ?> (<?php echo $numberinhousehold; ?> Adult<?php echo ($numberinhousehold==1?"":"s"); ?>, <?php echo $numberinhouseholdc; ?> Child<?php echo ($numberinhouseholdc==1?"":"ren"); ?>)</div>
                                  <hr/>
                                    <?php 
                                        $stmt2=$conn->prepare("SELECT a.id,a.amount,a.lot,a.startdate,a.enddate,b.ornumber,b.payee,b.paymentdate,b.transactiondate, m.maxdate, TIMESTAMPDIFF(MONTH,m.maxdate,CURDATE()) AS monthcount, getArrears(f.price*c.lotsize,1+f.interest, TIMESTAMPDIFF(MONTH,m.maxdate,CURDATE())) AS `arrears` FROM ledgeritem a INNER JOIN ledger b ON a.id=b.id INNER JOIN (SELECT lot, MAX(enddate) AS maxdate FROM ledgeritem GROUP BY lot) m ON m.lot=a.lot INNER JOIN lot c ON c.id=a.lot INNER JOIN settings f ON f.id=? WHERE a.lot=? AND a.enddate=m.maxdate");
                                        if($stmt2 === false) {
                                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                        }
                                        $stmt2->bind_param('ii',$_SESSION["settings"]["id"],$lid);
                                        $stmt2->execute();
                                        $stmt2->store_result();

                                        if($stmt2->num_rows>0){
                                            $stmt2->bind_result($id,$amount,$lot,$startdate,$enddate,$ornumber,$payee,$paymentdate,$transactiondate,$maxdate,$monthcount,$arrears);
                                            while($stmt2->fetch()):?>
                                                <div><span class="infoheader">Monthly Due</span> <?php echo number_format($lotsize*$_SESSION["settings"]["price"],2); ?></div>
                                                <div><span class="infoheader">Date of Payment</span> <?php echo $paymentdate; ?></div>
                                                <div><span class="infoheader">Month</span> <?php if(date("M Y",  strtotime($startdate))==date("M Y",  strtotime($enddate))){ echo date("M Y",  strtotime($enddate));}else{echo date("M Y",  strtotime($startdate))."-".date("M Y",  strtotime($enddate));} ?></div>
                                                <div><span class="infoheader">OR Number</span> <?php echo $ornumber; ?></div>
                                                <div><span class="infoheader">Paid By</span> <?php echo $payee; ?></div>
                                                <div><span class="infoheader">Amount</span> <?php echo number_format($amount,2); ?></div>
                                                <div><span class="infoheader">Unpaid Months</span> <?php echo $monthcount; ?></div>
                                                <div><span class="infoheader">Arrears</span> <strong style="font-size:1.2em;"><?php echo number_format($arrears,2); ?></strong></div>
                                            <?php endwhile;
                                        }
                                        else
                                        {
                                            ?>
                                                <li><em>No payments made.</em></li>    
                                            <?php
                                        }
                                        $stmt2->free_result();
                                        $stmt2->close();
                                    ?>
                                  <?php endif; ?>

                            
<!--                            <div class="ui-corner-all">
                                <header data-role="header" class="ui-bar-d ui-bar">
                                  Payment History
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
                            </div>-->
                            
                        
                        <?php }
                        displayHTMLFooter();
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
            if(isLoggedIn() && (checkPermission(DT_PERM_PAYMENT) || checkPermission(DT_PERM_LOTMGMNT)))
            {
                $table = 'charges a LEFT JOIN ledger b ON a.ledgerid=b.id AND COALESCE(b.active,0)=1 LEFT JOIN ledgeritem c ON c.chargeid=a.id LEFT JOIN (SELECT id, MAX(transactiondate) AS transactiondate FROM ledger GROUP BY id) m ON b.id=m.id ';
                $primaryKey = 'a.id';
                $columns = array(
//                    array('db'=>'a.id','dt'=>0,"alias"=>"id"),
                    array('db'=>'a.dateposted','dt'=>0,"alias"=>"dateposted"),
                    array('db'=>'a.description','dt'=>1,"alias"=>"description"),
                    array('db'=>'b.transactiondate','dt'=>2,"alias"=>"transactiondate"),
                    array('db'=>'b.ornumber','dt'=>3,"alias"=>"ornumber"),
                    array('db'=>'b.payee','dt'=>4,"alias"=>"payee"),
                    array('db'=>'COALESCE(a.amount,0)','dt'=>5,"alias"=>"amount"),
                    array('db'=>'COALESCE(SUM(c.amountpaid),0)','dt'=>6,"alias"=>"amountpaid"),
                    array('db'=>'(a.amount-COALESCE(SUM(c.amountpaid),0))','dt'=>7),
                    array('db'=>'b.id',"alias"=>"ledgerid",'dt'=>8),
                    array('db'=>'a.id','dt'=>9,"alias"=>"id",'formatter'=>function($d,$row){return "<a href='#' data-enhanced='true' class='delcharge ui-link ui-btn ui-icon-delete ui-btn-icon-notext ui-shadow ui-corner-all ".($row[3]==""?"":"ui-disabled")."' data-role='button' data-icon='delete' data-iconpos='notext' data-cid='".$d."' title='Delete Charge' >Delete Charge</a>";})
                );
                $addwhere="a.active=1 AND a.lot=".filter_input(INPUT_GET, "id");
                $group="GROUP BY a.id";
                $counttable="charges a LEFT JOIN ledger b ON a.ledgerid=b.id";
                if(!is_null(filter_input(INPUT_GET, "id")))
                {
                    $countwhere="(b.active=1 OR b.active IS NULL) AND a.lot=".filter_input(INPUT_GET, "id");
                }
                else
                {
                    $countwhere="";
                }
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere ));
                
//                global $conn;
//                dbConnect();
////                $get_length = filter_input(INPUT_GET, "length");
////                $get_start = filter_input(INPUT_GET, "start");
////                $get_draw = filter_input(INPUT_GET, "draw");
////                $get_search = filter_input(INPUT_GET, "search[value]");
//                
//                $stmt=$conn->prepare("SELECT a.id,a.dateposted,b.transactiondate,a.description,b.ornumber,b.payee,a.amount,a.amountpaid, (a.amount-a.amountpaid) AS balance FROM charges a LEFT JOIN ledger b ON a.ledgerid=b.id WHERE a.lot=?");
//                
//                if($stmt === false) {
//                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                }
//                
//                $lotid=filter_input(INPUT_GET, "id");
//                $stmt->bind_param('i',$lotid);
//                $stmt->execute();
//                $stmt->store_result();
////                $json = array(
////                    "draw" => $get_draw,
////                    "recordsFiltered" => $stmt->num_rows
////                );
//                $jsondata = array();
//                $json["data"]=array();
//                
//                if($stmt->num_rows > 0)
//                {
//                    $stmt->bind_result($id,$dateposted,$paymentdate,$description,$ornumber,$payee,$amount,$amountpaid,$balance);
//                    while($stmt->fetch()){
////                        $jsondata[]=array($id,$amount,$lot,$startdate,$enddate,$ornumber,$payee,$paymentdate,$transactiondate);
//                        $jsondata[]=array(
//                            "id"=>$id,
//                            "dateposted"=>$dateposted,
//                            "paymentdate"=>$paymentdate,
//                            "description"=>$description,
//                            "ornumber"=>$ornumber,
//                            "payee"=>$payee,
//                            "amount"=>$amount,
//                            "amountpaid"=>$amountpaid,
//                            "balance"=>$balance
//                        );
//                    }
//                    $json["data"]=$jsondata;
//                }
////                else
////                {
////                    setNotification("Wrong ID Number and/or password.",DT_NOTIF_ERROR);
////                }
//                
//                $stmt->free_result();
////                $stmt=$conn->prepare("SELECT COUNT(*) FROM homeowner");
//                
////                if($stmt === false) {
////                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
////                }
////                $stmt->execute();
////                $stmt->bind_result($cnttotal);
////                while($stmt->fetch()){
////                    $json["recordsTotal"]=$cnttotal;
////                }
////                
////                $stmt->free_result();
//                $stmt->close();
//                dbClose();
//                echo json_encode($json);
            }
            break;
        case "addpayment":
//            var_dump($_POST);
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT))
            {
                global $conn;
                $qs=false;
                dbConnect();
                $conn->autocommit(FALSE);
                
                $stmt=$conn->prepare("INSERT INTO ledger(ornumber,payee,homeowner,user,remarks,paymentmode,checkno) VALUES(?,?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $ornumber=filter_input(INPUT_POST, "ornumber");
                $homeowner=filter_input(INPUT_POST, "homeowner");
                $payee=filter_input(INPUT_POST, "payee");
                $remarks=filter_input(INPUT_POST, "remarks");
                $paymentmode=filter_input(INPUT_POST, "paymentmode");
                $checkno=filter_input(INPUT_POST, "checkno");
                
                $stmt->bind_param('ssiisss',$ornumber,$payee,$homeowner,$userid,$remarks,$paymentmode,$checkno);
                $qs=$stmt->execute();
                $ledgerid=$stmt->insert_id;
                $stmt->close();
                
                $charges=filter_input_array(INPUT_POST)["charges"];
                foreach($charges as $key=>$charge)
                {
                    if($qs)
                    {
                        if($charge["amountpaid"]>0) 
                        {
                            $stmt=$conn->prepare("UPDATE charges SET ledgerid=?, amountpaid=(amountpaid + ?) WHERE id=?");
                            if($stmt === false) {
                                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                            }

                            $stmt->bind_param('idi',$ledgerid, $charge["amountpaid"], $charge["id"]);
                            $qs=$stmt->execute();
                            $stmt->close();
                            
                            if($qs)
                            {
                                $stmt=$conn->prepare("INSERT INTO ledgeritem(ledgerid,chargeid,description,amount,uid,amountpaid) VALUES(?,?,?,?,?,?)");
                                if($stmt === false) {
                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                }

                                $stmt->bind_param('iisdid',$ledgerid, $charge["id"], $charge["description"], $charge["amount"], $userid, $charge["amountpaid"]);
                                $qs=$stmt->execute();
                                $stmt->close();
                            }
                        }
                    }else{
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
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT))
            {
                $table = 'ledger a INNER JOIN ledgeritem b ON a.id=b.ledgerid';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.transactiondate','dt'=>0,"alias"=>"transactiondate",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.'<!--'.date("Ymd",  strtotime($d)).'-->'.$d.'</a>';}),
                    array('db'=>'a.paymentmode','dt'=>1,"alias"=>"paymentmode",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'a.ornumber','dt'=>2,"alias"=>"ornumber",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'a.payee','dt'=>3,"alias"=>"payee",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink" data-ledgerid="'.$row['uid'].'">'.$d.'</a>';}),
                    array('db'=>'SUM(b.amountpaid)','dt'=>4,"alias"=>"amount",'formatter'=>function($d,$row){return '<a href="#popupReceipt" data-rel="popup" data-position-to="window" class="tablecelllink paymentdetailslink textamount" data-ledgerid="'.$row['uid'].'">'.number_format($d,2).'</a>';}),
                    array('db'=>'a.id','dt'=>5,"alias"=>"uid")
                );
                $addwhere="a.active=1 AND a.homeowner=".filter_input(INPUT_GET, "id");
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
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Order of Payment</h1>
                            <a href="./orderpayment?id=<?php echo $ledgerid; ?>" data-role="button" target="_blank">Print</a>
                            <a href="./confirmdeleteledger?id=<?php echo $ledgerid; ?>" data-role="button">Cancel Payment</a>
                        </header>
                        <div data-role="main" class="">
                <?php
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("SELECT a.id, a.ornumber, a.payee, a.homeowner, a.transactiondate, a.user, "
                        . "formatName(b.lastname, b.firstname, b.middlename), c.fullname, a.remarks, a.paymentmode, a.checkno FROM ledger a INNER JOIN homeowner b ON a.homeowner=b.id INNER JOIN user c ON a.user=c.id "
                        . "WHERE a.id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt->bind_param('i',$ledgerid);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows==1)
                {
                    $stmt->bind_result($id,$ornumber,$payee,$homeownerid,$transactiondate,$userid,$ownername,$fullname,$remarks,$paymentmode,$checkno);
                    while($stmt->fetch()){ ?>
                        <table data-role="table" class="ui-body-d ui-shadow ui-responsive">
                            <thead><tr></tr></thead>
                            <tbody>
                                <tr>
                                    <th>OR Number</th>
                                    <td><?php echo $ornumber; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Date</th>
                                    <td><?php echo $transactiondate; ?></td>
                                </tr>
                                <tr>
                                    <th>Account Name</th>
                                    <td><?php echo $ownername; ?></td>
                                </tr>
                                <tr>
                                    <th>Mode of Payment</th>
                                    <td><?php echo $paymentmode.($paymentmode=="Check"?" ($checkno)":"");?></td>
                                </tr>
                                <tr>
                                    <th>Paid by</th>
                                    <td><?php echo $payee; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Received by</th>
                                    <td><?php echo $fullname; ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td><?php echo $remarks; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php }
                    $stmt->close();
                    
                    $stmt=$conn->prepare("SELECT a.id,a.description,a.amount,a.amountpaid FROM ledgeritem a WHERE a.ledgerid=?");
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
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $stmt->bind_result($id,$description,$amount,$amountpaid);
                        $totalamt=0;
                        $totalamtpaid=0;
                        while($stmt->fetch()){ 
                            $totalamt += $amount;
                            $totalamtpaid += $amountpaid; ?>
                                <tr data-theme="cd">
                                    <td><?php echo $description; ?></td>
                                    <td><?php echo number_format($amount,2); ?></td>
                                    <td><?php echo number_format($amountpaid,2); ?></td>
                                </tr>
                        <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th><?php echo number_format($totalamt,2); ?></th>
                                    <th><?php echo number_format($totalamtpaid,2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                        <?php
                        $stmt->close();
                    }
                }
                else
                {
//                    setNotification("No ledger exists.",DT_NOTIF_ERROR);
                }
                dbClose(); ?>
                        </div>
                    </div>
                <?php
                displayHTMLFooter();
            }
            else
            {
                header("Location: ./homeowners");
            }
            break;
        case "orderpayment":
            if((isLoggedIn()) && (!is_null($ledgerid=filter_input(INPUT_GET, "id"))))
            {
                displayPlainHTMLHeader("Order of Payment"); ?>
                <div class="soapage">
                <?php
                displayPrintHeader();
                ?>
                    <h3 class="printtitle">Order of Payment</h3>
                <?php
                global $conn;
                dbConnect();
                $stmt=$conn->prepare('SELECT a.id, a.ornumber, a.payee, a.homeowner, a.transactiondate, a.user, formatName(b.lastname, b.firstname, b.middlename) AS name, c.fullname, a.remarks, a.paymentmode, a.checkno FROM ledger a INNER JOIN homeowner b ON a.homeowner=b.id INNER JOIN user c ON a.user=c.id WHERE a.id=?');
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt->bind_param('i',$ledgerid);
                $stmt->execute();
                $stmt->store_result();
                if($stmt->num_rows==1)
                {
                    $stmt->bind_result($id,$ornumber,$payee,$homeownerid,$transactiondate,$userid,$ownername,$fullname,$remarks,$paymentmode,$checkno);
                    while($stmt->fetch()){ ?>
                        <table data-role="table" class="ui-body-d ui-shadow table-stripe ui-responsive printacctinfo">
                            <thead><tr></tr></thead>
                            <tbody>
                                <tr>
                                    <th>OR Number</th>
                                    <td>:</td>
                                    <td><?php echo $ornumber; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Date</th><td>:</td>
                                    <td><?php echo $transactiondate; ?></td>
                                </tr>
                                <tr>
                                    <th>Account Name</th><td>:</td>
                                    <td><?php echo $ownername; ?></td>
                                </tr>
                                <tr>
                                    <th>Mode of Payment</th><td>:</td>
                                    <td><?php echo $paymentmode.($paymentmode=="Check"?" ($checkno)":"");?></td>
                                </tr>
                                <tr>
                                    <th>Paid by</th><td>:</td>
                                    <td><?php echo $payee; ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Received by</th><td>:</td>
                                    <td><?php echo $fullname; ?></td>
                                </tr>
                                <tr>
                                    <th>Remarks</th><td>:</td>
                                    <td><?php echo $remarks; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php }
                    $stmt->close();
                    
                    $stmt=$conn->prepare("SELECT a.id,a.description,a.amount,a.amountpaid FROM ledgeritem a WHERE a.ledgerid=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$ledgerid);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if($stmt->num_rows>0)
                    { ?>
                        <table data-role="table" class="ui-body-d ui-shadow table-stripe ui-responsive tblcharges">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $stmt->bind_result($id,$description,$amount,$amountpaid);
                        $totalamt=0;
                        $totalamtpaid=0;
                        while($stmt->fetch()){ 
                            $totalamt += $amount;
                            $totalamtpaid += $amountpaid; ?>
                                <tr data-theme="cd">
                                    <td><?php echo $description; ?></td>
                                    <td class="textamount"><?php echo number_format($amount,2); ?></td>
                                    <td class="textamount"><?php echo number_format($amountpaid,2); ?></td>
                                </tr>
                        <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="printtotalbal">Total</th>
                                    <th class="textamount"><?php echo number_format($totalamt,2); ?></th>
                                    <th class="textamount"><?php echo number_format($totalamtpaid,2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                        <footer>
                            <div>Prepared by:</div>
                            <div class="printpreparedby"><?php echo $_SESSION["fullname"]; ?></div>
                            <div class="gentimestamp">Generated on <?php date_default_timezone_set("Asia/Manila"); echo date('Y-m-d h:i:s A', time());?></div>
                        </footer>
                        <?php
                        $stmt->close();
                    }
                }
                dbClose(); ?>
                        </div>
                <?php
                displayPlainHTMLFooter();
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
                $stmt=$conn->prepare("UPDATE `settings` SET `assocname`=?, `acronym`=?, `subdname`=?,`brgy`=?,`city`=?,`province`=?,`zipcode`=?,`contactno`=?,`email`=?,`price`=?,`interest`=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $assocname=filter_input(INPUT_POST, "assocname");
                $acronym=filter_input(INPUT_POST, "acronym");
                $subdname=filter_input(INPUT_POST,"subdname");
                $brgy=filter_input(INPUT_POST,"brgy");
                $city=filter_input(INPUT_POST,"city");
                $province=filter_input(INPUT_POST, "province");
                $zipcode=filter_input(INPUT_POST,"zipcode");
                $contactno=filter_input(INPUT_POST,"contactno");
                $email=filter_input(INPUT_POST,"email");
                $price=filter_input(INPUT_POST,"price");
                $interest=filter_input(INPUT_POST,"interest");
                $uid=filter_input(INPUT_POST,"id");
                
                $stmt->bind_param('sssssssssddi',$assocname,$acronym,$subdname,$brgy,$city,$province,$zipcode,$contactno,$email,$price,$interest,$uid);
                $stmt->execute();
                $stmt->close();

                setNotification("Settings has been saved.");
                dbClose();
                header("Location: ./");
            }else{header("Location: ./");}
            break;
        case "bill":
            global $conn;
            dbConnect();
            $lid=filter_input(INPUT_GET, "id");
//            $stmt=$conn->prepare("SELECT a.id,a.code,formatAddress(a.housenumber,a.lot,a.block,a.street,a.phase) AS address,a.lotsize,formatName(b.lastname,b.firstname,b.middlename) AS fullname,(a.lotsize*f.price) AS dues,(SUM(c.amount)-SUM(c.amountpaid)) AS balance FROM lot a INNER JOIN homeowner b ON a.homeowner=b.id LEFT JOIN charges c ON a.id=c.lot INNER JOIN settings f ON f.id=? WHERE a.active=1 AND c.active=1 ".(!$lid?"":"AND a.id=".$lid)." GROUP BY a.id ORDER BY fullname");
            $stmt=$conn->prepare("SELECT a.id,a.code,formatAddress(a.housenumber,a.lot,a.block,a.street,a.phase) AS address,a.lotsize,formatName(b.lastname,b.firstname,b.middlename) AS fullname,(a.lotsize*f.price) AS dues

FROM lot a 
INNER JOIN homeowner b ON a.homeowner=b.id
INNER JOIN settings f ON f.id=? 

WHERE a.active=1 ".(!$lid?"":"AND a.id=".$lid)."  GROUP BY a.id ORDER BY fullname");
            
            if($stmt === false) {
                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
            }
            $stmt->bind_param('i',$_SESSION["settings"]["id"]);
            $stmt->execute();
            $stmt->store_result();
            $conn->autocommit(FALSE);
            if($stmt->num_rows>0)
            {
                $stmt->bind_result($id,$code,$address,$lotsize,$fullname,$dues);
                displayPlainHTMLHeader("Billing Statement");
                while($stmt->fetch()){
                    $stmt4=$conn->prepare("SELECT (m.amount-COALESCE(n.amountpaid,0)) AS balance FROM (SELECT a.id AS id, SUM(a.amount) AS amount FROM charges a WHERE a.active=1 AND a.lot=?) m LEFT JOIN(SELECT a.id AS id, SUM(c.amountpaid*b.active) AS amountpaid FROM charges a LEFT JOIN ledgeritem c ON a.id=c.chargeid LEFT JOIN ledger b ON b.id=c.ledgerid WHERE a.active=1 AND a.lot=?) n ON m.id=n.id");
                    if($stmt4 === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt4->bind_param('ii',$id,$id);
                    $stmt4->execute();
                    $stmt4->store_result();
                    if($stmt4->num_rows==1)
                    {
                        $stmt4->bind_result($balance);
                        while($stmt4->fetch()){
                            formatBill($id, $code, $address, $lotsize, $fullname, $dues, $balance);
                        }
                    }
                    $stmt4->free_result();
                    $stmt4->close();
                    
                }
                displayPlainHTMLFooter();
            }
            $conn->commit();
            $conn->autocommit(TRUE);
            $stmt->close();
            dbClose();
            break;
        case "reports":
            if(isLoggedIn() && checkPermission(DT_PERM_REPORTS)){
                displayHTMLPageHeader();?>
                <h1>Reports</h1>
                <div data-role="collapsibleset" data-theme="d" data-content-theme="a">
                    <div data-role="collapsible">
                        <h3>Homeowners</h3>
                            
                        <form action="./report?t=homeownerlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>List of all Homeowners</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=inactivehomeownerlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>List of all Deleted Homeowners</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=homeownerwithbonds" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Homeowner with Bonds</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                            
                    </div>
                    <div data-role="collapsible">
                        <h3>Lots</h3>
                    
                        <form action="./report?t=lotlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>List of Lots</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=inactivelotlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>List of Deleted Lots</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=lotwitharrears" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Lots with Arrears</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                    </div>
                    <div data-role="collapsible">
                        <h3>Financial</h3>
                    
                        <form action="./report?t=detailedpayments" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Detailed Payments</legend>
                                <label for="startdate01">From</label>
                                <input type="date" name="startdate" id="startdate01" value="<?php echo date("Y-m-d"); ?>"/>
                                <label for="enddate01" data-inline="true">To</label>
                                <input type="date" name="enddate" id="enddate01" value="<?php echo date("Y-m-d"); ?>"/>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                    </div>
                </div>
                
                <?php displayHTMLPageFooter();
            }
            break;
        case "report":
            if(isLoggedIn() && checkPermission(DT_PERM_REPORTS)){
                $resultset=array();
                $resultcolumns=array();
                $resultfooter=null;
                $resultclasses=array();
                $title="";
                $msg="";

                global $conn;
                dbConnect();

                switch(filter_input(INPUT_GET, "t"))
                {
                    case "homeownerlist":
                        $title="List of Homeowners";
                        $msg="";
                        $stmt=$conn->prepare("SELECT `lastname`, `firstname`, `middlename`, `contactno`, `email`, FORMAT(`bond`,2), `gatepass` FROM `homeowner` WHERE `active`=1");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Last Name","First Name", "Middle Name", "Contact No.", "Email","Bond","Gatepass"];
                        $resultclasses = ["","","","","","textamount",""];
        //                $postusername=filter_input(INPUT_POST, "uid");
        //                $postpassword=md5(filter_input(INPUT_POST, "password"));
        //                $stmt->bind_param('ss',$postusername,$postpassword);
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lastname,$firstname,$middlename,$contactno,$email,$bond,$gatepass);
                            while($stmt->fetch()){
                                $resultset[]=array($lastname,$firstname,$middlename,$contactno,$email,$bond,$gatepass);
                            }
                        }
                        break;
                    case "inactivehomeownerlist":
                        $title="List of Deleted Homeowners";
                        $msg="These homeowners can still be reactivated.";
                        $stmt=$conn->prepare("SELECT `lastname`, `firstname`, `middlename`, `contactno`, `email`, FORMAT(`bond`,2), `gatepass` FROM `homeowner` WHERE `active`=0");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Last Name","First Name", "Middle Name", "Contact No.", "Email","Bond","Gatepass"];
                        $resultclasses = ["","","","","","textamount",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lastname,$firstname,$middlename,$contactno,$email,$bond,$gatepass);
                            while($stmt->fetch()){
                                $resultset[]=array($lastname,$firstname,$middlename,$contactno,$email,$bond,$gatepass);
                            }
                        }
                        break;
                    case "lotlist":
                        $title="List of Lots";
                        $msg="";
                        $stmt=$conn->prepare('SELECT a.`code`, CONCAT(a.`housenumber`," ",a.`street`,", Lot ",a.`lot`," Block ",a.`block`," Phase ",a.`phase`) AS address, DATE_FORMAT(a.`dateacquired`,"%Y-%m-%d"),a.`lotsize`,a.`numberinhousehold`, CONCAT(b.`lastname`,", ",b.`firstname`," ",SUBSTR(b.`middlename`,1,1),".") AS fullname FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id WHERE a.active=1');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Lot Code","Address","Date Acquired","Lot Size","Number in Household","Owner"];
                        $resultclasses = ["","","","","","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lotcode,$address,$dateacquired,$lotsize,$numberinhousehold,$owner);
                            while($stmt->fetch()){
                                $resultset[]=array($lotcode,$address,$dateacquired,$lotsize,$numberinhousehold,$owner);
                            }
                        }
                        break;
                    case "inactivelotlist":
                        $title="List of Deleted Lots";
                        $msg="";
                        $stmt=$conn->prepare('SELECT a.`code`, CONCAT(a.`housenumber`," ",a.`street`,", Lot ",a.`lot`," Block ",a.`block`," Phase ",a.`phase`) AS address, a.`dateacquired`,a.`lotsize`,a.`numberinhousehold`, CONCAT(b.`lastname`,", ",b.`firstname`," ",SUBSTR(b.`middlename`,1,1),".") AS fullname FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id WHERE a.active=0');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Lot Code","Address","Date Acquired","Lot Size","Number in Household","Owner"];
                        $resultclasses = ["","","","","","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lotcode,$address,$dateacquired,$lotsize,$numberinhousehold,$owner);
                            while($stmt->fetch()){
                                $resultset[]=array($lotcode,$address,$dateacquired,$lotsize,$numberinhousehold,$owner);
                            }
                        }
                        break;
                    case "homeownerwithbonds":
                        $title="List of Homeowners with Bonds";
                        $msg="";
                        $stmt=$conn->prepare('SELECT CONCAT(`lastname`,", ",`firstname`," ",SUBSTR(`middlename`,1,1),".") AS fullname, FORMAT(`bond`,2) FROM `homeowner` WHERE `active`=1 AND `bond`>0');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Name","Bond"];
                        $resultclasses = ["","textamount"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($name,$bond);
                            $totalbond=0;
                            while($stmt->fetch()){
                                $resultset[]=array($name,$bond);
                                $totalbond += $bond;
                            }
                            $resultfooter=array("Total",$bond);
                        }
                        break;
                    case "detailedpayments":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");

                        $title="Detailed Payments";
                        $msg="From $startdate to $enddate";

                        $stmt=$conn->prepare('SELECT a.paymentdate AS paymentdate, a.ornumber AS ornumber, CONCAT(d.`lastname`,", ",d.`firstname`," ",SUBSTR(d.`middlename`,1,1),".") AS fullname, a.payee AS payee, SUM(b.amount) AS amount
                            FROM ledger a, ledgeritem b, lot c, homeowner d
                            WHERE a.id=b.id AND (c.id=b.lot OR b.lot=0) AND d.id=a.homeowner AND (a.paymentdate>=? AND a.paymentdate<=?)
                            GROUP BY a.id
                            ORDER BY paymentdate DESC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Date","OR Number","Account Name","Paid By","Amount"];
                        $resultclasses = ["","","","","textamount"];
                        $stmt->bind_param('ss',$startdate,$enddate);
                        $stmt->execute();

                        $stmt->store_result();
                        $totalamount=0;
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($date,$ornumber,$acctname,$payee,$amount);
                            while($stmt->fetch()){
                                $resultset[]=array($date,$ornumber,$acctname,$payee,  number_format($amount,2));
                                $totalamount += $amount;
                            }
                            $resultfooter=array("Total","","","",number_format($totalamount,2));
                        }
                        break;
                    case "lotwitharrears":
                        $title="Lots with Arrears";
                        $msg="";
                        $stmt=$conn->prepare('SELECT a.`code`, CONCAT(a.`housenumber`," ",a.`street`,", Lot ",a.`lot`," Block ",a.`block`," Phase ",a.`phase`) AS address, CONCAT(b.`lastname`,", ",b.`firstname`," ",SUBSTR(b.`middlename`,1,1),".") AS fullname, (a.lotsize*f.price) AS price, m.maxdate, TIMESTAMPDIFF(MONTH,m.maxdate,CURDATE()) AS monthcount ,getArrears(f.price*a.lotsize,1+f.interest, TIMESTAMPDIFF(MONTH,m.maxdate,CURDATE())) AS `arrears` FROM lot a LEFT JOIN homeowner b ON a.homeowner=b.id LEFT JOIN ledgeritem c ON a.id=c.lot LEFT JOIN ledger d ON c.id=d.id LEFT JOIN(SELECT lot, MAX(enddate) AS maxdate FROM ledgeritem GROUP BY lot) m ON m.lot=a.id, settings f WHERE a.active=1 AND TIMESTAMPDIFF(MONTH,m.maxdate,CURDATE())>0 GROUP BY a.id');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Lot Code","Address","Owner","Montly Due", "Last Paid","Backlog Months","Arrears"];
                        $resultclasses = ["","","","textamount","","","textamount"];
                        $stmt->execute();

                        $stmt->store_result();
                        $totalamount=0;
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lotcode,$address,$owner,$monthlydue,$lastpaid,$monthcount,$arrears);
                            while($stmt->fetch()){
                                $resultset[]=array($lotcode,$address,$owner,number_format($monthlydue,2),$lastpaid,$monthcount,number_format($arrears,2));
                                $totalamount += $arrears;
                            }
                            $resultfooter=array("Total","","","","","",number_format($totalamount,2));
                        }
                        break;
                }


                $stmt->close();
                dbClose();
                ?>

                    <!DOCTYPE html>
                    <html>
                      <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title><?php echo $title; ?></title>
                        <!--<link rel="stylesheet" href="./css/staisabelgreen.min.css" />-->
                        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables.css" />
                        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables_themeroller.css" />
                        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/extensions/TableTools/css/dataTables.tableTools.css" />
                        <link rel="stylesheet" href="./css/reportstyle.css" />

                        <script src="./js/jquery-2.1.1.min.js"></script>
                        <script src="./plugin/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>
                        <script src="./plugin/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js"></script>

                        <script type="text/javascript">
                            $(document).ready(function() {
                                rptbl=$('#tblreport').dataTable({paging:false});
                                var tableTools = new $.fn.dataTable.TableTools( rptbl, {
                                    "buttons": [
                                        "copy",
                                        "csv",
                                        "xls",
                                        "pdf",
                                        { "type": "print", "buttonText": "Print me!" }
                                    ],
                                    "sSwfPath":"./plugin/DataTables-1.10.0/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
                                    "aButtons": [
                                        "copy",
                                        "csv",
                                        "xls",
                                        {
                                            "sExtends": "pdf",
                                            "sPdfOrientation": "landscape",
                                            "sPdfMessage": "<?php echo $msg; ?>"
                                        },
                                        "print"
                                    ]
                                } );

                                $( tableTools.fnContainer() ).appendTo('#ttools');
                            } );
                        </script>
                      </head>
                      <body>
                          <h3 id="pagetitle"><?php echo $title; ?></h3>
                          <div id="ttools"></div>
                          <table id="tblreport" width="100%" class="display">
                              <thead>
                                  <tr>
                                  <?php foreach($resultcolumns as $col): ?>
                                    <th><?php echo $col; ?></th>
                                  <?php endforeach; ?>
                                  </tr>
                              </thead>
                              <tbody>
                                <?php $i=0; foreach ($resultset as $row): ?>
                                    <tr>
                                        <?php $j=0; foreach($row as $cell): ?>
                                            <td class="<?php echo $resultclasses[$j]; ?>"><?php echo $cell; $j++; ?></td>
                                        <?php endforeach; $i++; ?>
                                    </tr>
                                <?php endforeach; ?>
                              </tbody>
                              <?php if(!is_null($resultfooter)): ?>
                              <tfoot>
                                  <tr>
                                      <?php $i=0; foreach($resultfooter as $foot): ?>
                                      <th class="<?php echo $resultclasses[$i]; ?>"><?php echo $foot; $i++; ?></th>
                                      <?php endforeach; ?>
                                  </tr>
                              </tfoot>
                              <?php endif; ?>
                          </table>


                      </body>
                    </html>  
                <?php
            }
            break;
        case "insertmonthlydues":
            // <editor-fold defaultstate="collapsed" desc="comment">
            global $conn;
            dbConnect();
            $stmt=$conn->prepare("SELECT TIMESTAMPDIFF(MONTH,applieddues,CURDATE()),DATE_FORMAT(CURDATE(),'%b %Y') FROM settings WHERE id=?");
            if($stmt === false) {
                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
            }
            $settingsid=DT_SETTINGS_ID;
            $stmt->bind_param("i",$settingsid);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows>0)
            {
                $stmt->bind_result($applieddues,$monthFormat);
                while($stmt->fetch()){
                    if(is_null($applieddues) || $applieddues>0)
                    {
                        $sql="INSERT INTO charges(`lot`,`homeowner`,`description`,`amount`,`uid`,`winterest`) VALUES";
                        $sqlwhere = array();
                        $stmt3=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.lotsize,f.price,(a.lotsize*f.price) AS due, (SUM(b.amount)-SUM(b.amountpaid)) AS balance, f.interest FROM lot a LEFT JOIN charges b ON b.lot=a.id AND b.winterest=1 INNER JOIN settings f ON f.id=? WHERE a.homeowner<>0 GROUP BY a.id");
                        if($stmt3 === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $settingsid=DT_SETTINGS_ID;
                        $stmt3->bind_param('i',$settingsid);
                        $stmt3->execute();
                        $stmt3->store_result();
                        if($stmt3->num_rows>0)
                        {
                            $stmt3->bind_result($lid,$lcode,$lhomeowner,$llotsize,$lprice,$ldue,$lbalance,$linterest);
                            while($stmt3->fetch()){
                                if($lbalance>0)
                                {
                                    array_push($sqlwhere, "($lid,$lhomeowner,'$lcode interest for $monthFormat',".(floatval($lbalance)*floatval($linterest)).",0,1)");
                                }
                                array_push($sqlwhere, "($lid,$lhomeowner,'$lcode due for $monthFormat',$ldue,0,1)");
                            }
                            $sql .= implode(",", $sqlwhere);
                            echo $sql;
                        }
                        $stmt3->free_result();
                        $stmt3->close();
                        
                        
                        $stmt2=$conn->prepare($sql);
                        
                        if($stmt2 === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt2->execute();
                        $stmt2->close();
                        
                        $stmt3=$conn->prepare("UPDATE settings SET `applieddues`=CONCAT(YEAR(CURDATE()),'-',MONTH(CURDATE()),'-01') WHERE id=?");
                        if($stmt3 === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt3->bind_param('i',$settingsid);
                        $stmt3->execute();
                        $stmt3->close();

                        setNotification("Monthly dues for $monthFormat has been charged to homeowners.");
                    }
                    else
                    {
//                        setNotification("There is already an existing monthly due for this month.",DT_NOTIF_ERROR);
                        echo "There is already an existing monthly due for this month.";
                    }
                }
            }
            else
            {
//                setNotification("There was an error in fetching the data.",DT_NOTIF_ERROR);
            }
            $stmt->close();
            dbClose();
//            header("Location: ./");
            break;
            // </editor-fold>
        default :
            displayHTMLPageHeader();
            if(!isLoggedIn())
            {
                ?>
                <img class="pagelogo" src="images/staisabellogo.png" alt="Santa Isabel Logo"/>
                <h1>Santa Isabel Village Homeowners Association, Inc.</h1>
                <p>Welcome to the Homeowner System. With this system, you can:</p>
                <ul> 
                    <li>Manage and organize data regarding the subdivision's lots</li>
                    <li>Manage information regarding the homeowners</li>
                    <li>Encode payments to effectively monitor finances.</li>
                    <li>Compute for each homeowner's arrears with compounded interest.</li>
                </ul>
                <?php
            }
            displayHTMLPageFooter();
    }
}
ob_end_flush();
?>