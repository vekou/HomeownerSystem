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
//        case "add":
//            if(isLoggedIn() && checkPermission(DT_PERM_ADDDOC))
//            {
//                displayHTMLPageHeader();?>
                <header><h1>Add Document</h1></header>
                <article>
                <form action="./adddoc" method="post" data-ajax="false">
                    <label for="documentnumber">Document Number</label>
                    <input type="text" name="documentnumber" id="documentnumber"/>

                    <label for="remarks">Remarks</label>
                    <input type="text" name="remarks" id="remarks"/>

                    <input type="submit" value="Add" data-icon="plus" data-ajax="false"/>
                </form>
                </article>
                //<?php displayHTMLPageFooter();
//            }else{header("Location: ./");}
//            break;
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
//        case "receive":
//            if(isLoggedIn() && checkPermission(DT_PERM_RECEIVEDOC))
//            {
//                if(!is_null(filter_input(INPUT_POST, "trackingnumber")))
//                {
//                    global $conn;
//                    dbConnect();
//                    $stmt=$conn->prepare("INSERT INTO documentlog(trackingnumber,remarks,user) VALUES(?,?,?)");
//                    if($stmt === false) {
//                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
//                    }
//                    $userid=(isLoggedIn()?$_SESSION["uid"]:0);
//                    $posttrackingnumber=  filter_input(INPUT_POST, "trackingnumber");
//                    $posttxtremarks=  filter_input(INPUT_POST, "txtremarks");
//                    $stmt->bind_param('isi',$posttrackingnumber,$posttxtremarks,$userid);
//                    $stmt->execute();
//
//                    setNotification("Document ".filter_input(INPUT_POST, "trackingnumber")."'s status has been updated.");
//                    writeLog("Document ".filter_input(INPUT_POST, "trackingnumber")." was received at ".$_SESSION["department"]." (".$_SESSION["section"].").");
//                    dbClose();
//                    header("Location: ./?q=".filter_input(INPUT_POST, "trackingnumber"));
//                }
//                else
//                {
//                    header("Location: ./");
//                }
//            }else{header("Location: ./");}
//            break;
        case "regform":
            if(isLoggedIn() && checkPermission(DT_PERM_USERMGMNT))
            {
                displayHTMLPageHeader();?>
                <header><h1>User Registration</h1></header>
                <article>
                <form action="./reguser" method="post" data-ajax="false">
                    <label for="uid">Employee ID Number</label>
                    <input type="number" name="uid" id="uid" required="true"/>

                    <label for="fullname">Full Name</label>
                    <input type="text" name="fullname" id="fullname" required="true"/>
                    
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required="true" onchange="$('#password2').prop('pattern',this.value);"/>
                    
                    <label for="password2">Confirm Password</label>
                    <input type="password" name="password2" id="password2" required="true"/>
                    
                    <label for="department">Department</label>
                    <input type="text" name="department" id="department" required="true"/>
                    
                    <label for="section">Section</label>
                    <input type="text" name="section" id="section" required="true"/>
                    
                    <fieldset data-role="controlgroup">
                        <legend>Permissions</legend>
                        <input type="checkbox" name="p[]" id="checkbox01" value="1" checked="">
                        <label for="checkbox01">Add Document</label>
                        <input type="checkbox" name="p[]" id="checkbox02" value="2">
                        <label for="checkbox02">Edit Document</label>
                        <input type="checkbox" name="p[]" id="checkbox03" value="4" checked="">
                        <label for="checkbox03">Receive Document</label>
                        <input type="checkbox" name="p[]" id="checkbox04" value="8">
                        <label for="checkbox04">Edit Document Track</label>
                        <input type="checkbox" name="p[]" id="checkbox05" value="16">
                        <label for="checkbox05">User Management</label>
                        <input type="checkbox" name="p[]" id="checkbox06" value="32">
                        <label for="checkbox06">Audit Log</label>
                    </fieldset>

                    <input type="submit" value="Register" data-icon="edit" data-ajax="false"/>
                </form>
                </article>
                <?php displayHTMLPageFooter();
            }else{header("Location: ./");}
            break;
        case "reguser":
            if(isLoggedIn() && checkPermission(DT_PERM_USERMGMNT))
            {
                if(!is_null(filter_input(INPUT_POST, "uid")))
                {
                    global $conn;
                    dbConnect();
                    $stmt=$conn->prepare("INSERT INTO user(uid,password,fullname,department,section,permission) VALUES(?,?,?,?,?,?)");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        break;
                    }
                    $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                    $uid = filter_input(INPUT_POST, "uid");
                    $password=md5(filter_input(INPUT_POST, "password"));
                    $fullname=filter_input(INPUT_POST, "fullname");
                    $department=filter_input(INPUT_POST, "department");
                    $section=filter_input(INPUT_POST, "section");
                    $pcount=filter_input(INPUT_POST, "p");
                    $permission=0;
                    while(list($key,$val)=@each($pcount)) {
                        $permission += intval($val);
                    }
                    
                    $stmt->bind_param('issssi',$uid,$password,$fullname,$department,$section,$permission);
                    $stmt->execute();

                    setNotification("User ".$fullname."(".$uid.") has been registered.");
                    writeLog("User ".$fullname."(".$uid.") has been registered.");
                    dbClose();
                    header("Location: ./");
                }
                else
                {
                    header("Location: ./");
                }
            }else{header("Location: ./");}
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
                    <form action="addhomeowner" method="post" data-ajax="false">
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
        case "homeowners":
            if(isLoggedIn())
            {
                if(isset($_GET['id']))
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
                        while($stmt->fetch()){?>
                            <h1><?php echo "$lastname, $firstname " . substr($middlename, 0, 1) . "."; ?></h1>
                            <ul data-role="listview" data-inset="true">
                                <li data-icon="phone"><a href="tel:<?php echo $contactno; ?>"><?php echo $contactno; ?></a></li>
                                <li data-icon="mail"><a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a></li>
                            </ul>
                            <hr/>
                            <div>
                                <a href="#addLotForm" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add Lot</a>
                                <div data-role="popup" id="addLotForm" data-dismissible="false" data-overlay-theme="b">
                                    <header data-role="header">
                                      <h1>Add Lot</h1>
                                      <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                                    </header>
                                    <div role="main" class="ui-content">
                                      <form action="addlot" method="post" data-ajax="false">
                                          <label for="code">Lot Code</label>
                                          <input type="text" id="code" name="code"/>
                                          <label for="dateacquired">Date Acquired</label>
                                          <input type="text" id="dateacquired" name="dateacquired"/>
                                          <label for="lotsize">Lot Size (sq. m)</label>
                                          <input type="text" id="lotsize" name="lotsize"/>
                                          <label for="housenumber">House Number</label>
                                          <input type="text" id="housenumber" name="housenumber"/>
                                          <label for="street">Street</label>
                                          <input type="text" id="street" name="street"/>
                                          <label for="lot">Lot</label>
                                          <input type="text" id="lot" name="lot"/>
                                          <label for="block">Block</label>
                                          <input type="text" id="block" name="block"/>
                                          <label for="phase">Phase</label>
                                          <input type="text" id="phase" name="phase"/>
                                          <label for="numberinhousehold">Number in Household</label>
                                          <input type="number" id="numberinhousehold" name="numberinhousehold"/>
                                        
                                          <input type="submit" value="Add Lot" data-icon="arrow-d"/>
                                      </form>
                                    </div>
                                  </div>
                                <ul data-role="listview" data-inset="true">
                                    <li data-role="list-divider">Lot</li>
                                </ul>
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
                else
                {
                    displayHTMLPageHeader(); ?>
                    <a href="#addHomeowner" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop">Add Homeowner</a>
                    <div data-role="popup" id="addHomeowner" data-dismissible="false" data-overlay-theme="b">
                      <header data-role="header">
                        <h1>Add Homeowner</h1>
                        <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                      </header>
                      <div role="main" class="ui-content">
                        <form action="addhomeowner" method="post" data-ajax="false">
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
                    <table id="tblhomeownerlist" class="table table-striped table-bordered dt stripe"><!--ui-responsive table-stroke ui-table ui-table-reflow-->
                        <thead>
                            <tr>
    <!--                            <th>ID</th>-->
                                <th>Name</th>
                                <th>Contact Number</th>
                                <th>Email Address</th>
                                <th>Option</th>
    <!--                            <th>Email</th>-->
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    <script type="text/javascript">
                        $(document).ready(function(){
                            setAsDataTable("*#tblhomeownerlist","./homeownerlistss");

                            $("#tblhomeownerlist").on( "draw.dt", function( e, settings, data ) {
                                $("a.tblhomeownerlistbtn").button();
                            });
                        });
                    </script>
                    <?php displayHTMLPageFooter();
                }
            }
            break;
        case "homeownerlistss":
            if(isLoggedIn())
            {
                $table = 'homeowner';
                $primaryKey = 'id';
                $columns = array(
                    //array('db'=>'id','dt'=>0),
                    array('db'=>'CONCAT(lastname,", ",firstname," ",SUBSTR(middlename,1,1),".")','dt'=>0, 'formatter'=>function($d,$row){return "<a href='#' class='tablecelllink'>".$d."</a>";},"alias"=>"name","aliascols"=>"lastname,firstname,middlename"),
                    array('db'=>'contactno','dt'=>1, 'formatter'=>function($d,$row){return "<a href='#' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'email','dt'=>2, 'formatter'=>function($d,$row){return "<a href='#' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'id','dt'=>3, 'formatter'=>function($d,$row){return "<a href='#' class='tblhomeownerlistbtn' data-role='button' data-iconpos='notext' data-icon='edit'>Edit</a>";}),
                );
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns));
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
        default :
            displayHTMLPageHeader();
            if(!isLoggedIn())
            {
                //echo "<h1>Welcome to Santa Isabel Village Homeowners Association, Inc.</h1>";
            }
            displayHTMLPageFooter();
    }
}
