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
                $_SESSION['permlist']=parsePermission($_SESSION['permission']);
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
                setNotification("Wrong Username and/or Password.",DT_NOTIF_ERROR);
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_ADD))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_UPDATE))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_DELETE))
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_DELETE))
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
            if(isLoggedIn() && checkPermission(DT_PERM_USER_VIEW))
            {
                displayHTMLPageHeader("User Management"); ?>                
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <?php if(checkPermission(DT_PERM_USER_ADD)): ?><a href="./adduserform" data-role="button" data-icon="plus" data-inline="true" data-theme="d">Add User</a><?php endif; ?>
                    <a href="./inactiveusers" data-role="button" data-icon="forbidden" data-theme="b">Deleted Users</a>
                </fieldset>
                <?php if(checkPermission(DT_PERM_USER_ADD)): ?>
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
                <?php endif; ?>
                
                <?php if(checkPermission(DT_PERM_USER_DELETE)): ?>
                <div data-role="popup" id="confirmUserDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                    <header data-role="header">
                      <h1>Deactivate User?</h1>
                      <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                    </header>
                    <div data-role="main" class="ui-content ui-body">
                        <form action="./deleteuser" method="post" target="_top">
                            <input type="hidden" name="uid" value="0" id="uid"/>
                            <div>Deactivate user?</div>
                            <!--<div class='ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left ui-shadow' style='margin-bottom: 0.5em; background-color: #FF9;'>Deactivate User?</div>-->
                            <fieldset data-role="controlgroup" data-type="horizontal">
                                <input type="submit" data-role="button" value="Delete" data-theme="e"/>
                                <a href="./users" data-role="button" data-rel="back" data-theme="b">Cancel</a>
                            </fieldset>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                
                
                <div class="ui-content ui-body-a ui-corner-all">
                    <table id="tbluserlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                
                <script type="text/javascript">                    
                    $(document).on("pagecreate",function(){
                        ul = setAsDataTable("*#tbluserlist","./userlistss");

//                            ulapi = ul.api();

                        $("#tbluserlist").on( "init.dt", function() {
                            $("#tbluserlist_wrapper").enhanceWithin();
                            $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                            $("#tbluserlist_filter input").on("change",function(){
                                ul.search($(this).val()).draw();
                            });
                        });
                        
                        <?php if(checkPermission(DT_PERM_USER_DELETE)): ?>
                        $("#tbluserlist").on( "draw.dt", function() {
                            $(".deluser").click(function(event){
                                event.preventDefault();
                                var uid=$(this).data("uid");
                                $("#uid").attr("value",uid);
                                $("#confirmUserDelete").popup("open",{"transition":"pop"});
                            });
                        });
                        <?php endif; ?>

                        $("#ppaymentmodecash").click(function(){
                            $("#pcheckno").textinput("disable");
                        });
                        $("#ppaymentmodecheck").click(function(){
                            $("#pcheckno").textinput("enable");
                        });


                    });
                    
                </script>
                <?php displayHTMLPageFooter();
            }else{header("Location: ./");}
            break;
        case "inactiveusers":
            if(isLoggedIn() && (checkPermission(DT_PERM_USER_VIEW) || checkPermission(DT_PERM_USER_DELETE)))
            {
                displayHTMLPageHeader("Deactivated Users"); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <a href="./users" data-rel="back" data-role="button" data-icon="back" data-inline="true" data-theme="d">Back to Users</a>
                    <a href="./inactiveusers" data-role="button" data-icon="forbidden" data-theme="b" class="ui-disabled">Deleted Users</a>
                </fieldset>
                
                <div class="ui-content ui-body-a ui-corner-all">
                    <table id="tbluserlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                
                <script type="text/javascript">                    
                    $(document).on("pagecreate",function(){
                        ul = setAsDataTable("*#tbluserlist","./inactiveuserlistss");

//                            ulapi = ul.api();

                        $("#tbluserlist").on( "init.dt", function() {
                            $("#tbluserlist_wrapper").enhanceWithin();
                            $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                            $("#tbluserlist_filter input").on("change",function(){
                                ul.search($(this).val()).draw();
                            });
                        });

                    });
                    
                </script>
                <?php displayHTMLPageFooter();
            }else{header("Location: ./");}
            break;
        case "adduserform":
            if(isLoggedIn()){
                $uid=filter_input(INPUT_GET, "id");
                $f=false;
                if($uid){
                    if((checkPermission(DT_PERM_USER_UPDATE))||($uid==$_SESSION["uid"]))
                    {
                        global $conn;
                        dbConnect();
                        $stmt=$conn->prepare("SELECT id,fullname,username,permission FROM user WHERE id=?");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param('i',$uid);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($uid,$fullname,$username,$permission);
                        while($stmt->fetch()):

                        endwhile;
                        $stmt->free_result();
                        $stmt->close();

                        dbClose();

                        $p=parsePermission($permission);
                        $f=true;
                    }else{
                        header("Location: ./users");
                        break;
                    }
                }else if(!checkPermission(DT_PERM_USER_ADD)){
                    header("Location: ./users");
                    break;
                }
                
                
                displayHTMLPageHeader($f?'Update User ('.$username.')':'User Registration');?>
                <div>
                    <form action="<?php echo $f?"edituser":"adduser"; ?>" method="post" data-ajax="false">
                        <?php if(!$f): ?>
                            <label for="pusername">Username</label>
                            <input id="pusername" name="pusername" type="text" required="true"/>
                        <?php else: ?>
                            <input type="hidden" name="uid" value="<?php echo $uid; ?>"/>
                        <?php endif; ?>

                        <label for="plastname">Full Name</label>
                        <input id="pfullname" name="pfullname" type="text" required="true" <?php echo $f?'value="'.$fullname.'"':''; ?>/>

                        <?php if(($uid==$_SESSION["uid"])|| checkPermission(DT_PERM_USER_ADD)): ?>
                            <label for="ppassword">Password</label>
                            <input id="ppassword" name="ppassword" type="password" onchange="$('#pconfirm').prop('pattern',this.value);" <?php echo $f?'placeholder="Leave this blank if you don\'t want to change password."':'required="true"'; ?>/>

                            <label for="pconfirm">Confirm Password</label>
                            <input id="pconfirm" name="pconfirm" type="password" <?php echo $f?'placeholder="Leave this blank if you don\'t want to change password."':'required="true"'; ?>/>
                        <?php endif; ?>

                        <?php if(!$f && checkPermission(DT_PERM_USER_ADD)): ?>
                            <div class="ui-content ui-body ui-body-e ui-corner-all">
                                <label for="pquestion">Security Question</label>
                                <input id="pquestion" name="pquestion" type="text" required="true" placeholder="e.g. What is the name of my first pet?"/>
                                <label for="panswer">Security Answer</label>
                                <input id="panswer" name="panswer" type="text" required="true" placeholder="Answer to the Security Question"/>
                            </div>
                        <?php endif; ?>

                        <?php if(checkPermission(DT_PERM_USER_UPDATE)|| checkPermission(DT_PERM_USER_ADD)): ?>
                        <hr/>

                        <div class="ui-corner-all custom-corners">
                            <header data-role="header" class="ui-bar-e ui-bar">Permissions</header>
                            <div class=" ui-body ui-body-a" style="padding-top:0px; padding-bottom:0px;">
                                <input type="hidden" name="p[]" value="1"/>

                                <div data-role="collapsible" data-collapsed="false" data-inset="false" data-theme="b">
                                    <h4>Lot Management</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="lot1" value="4" <?php echo $f?(checkPermission(DT_PERM_LOT_VIEW, $p)?'checked="checked"':''):'checked="checked"'; ?>>
                                        <label for="lot1">View</label>
                                        <input type="checkbox" name="p[]" id="lot2" value="64" <?php echo $f&&checkPermission(DT_PERM_LOT_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="lot2">Add</label>
                                        <input type="checkbox" name="p[]" id="lot3" value="256" <?php echo $f&&checkPermission(DT_PERM_LOT_UPDATE, $p)?'checked="checked"':''; ?>>
                                        <label for="lot3">Update</label>
                                        <input type="checkbox" name="p[]" id="lot4" value="128" <?php echo $f&&checkPermission(DT_PERM_LOT_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="lot4">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Homeowner Management</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="home1" value="8" <?php echo $f?(checkPermission(DT_PERM_HOMEOWNER_VIEW, $p)?'checked="checked"':''):'checked="checked"'; ?>>
                                        <label for="home1">View</label>
                                        <input type="checkbox" name="p[]" id="home2" value="16384"<?php echo $f&&checkPermission(DT_PERM_HOMEOWNER_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="home2">Add</label>
                                        <input type="checkbox" name="p[]" id="home3" value="32768"<?php echo $f&&checkPermission(DT_PERM_HOMEOWNER_UPDATE, $p)?'checked="checked"':''; ?>>
                                        <label for="home3">Update</label>
                                        <input type="checkbox" name="p[]" id="home4" value="65536"<?php echo $f&&checkPermission(DT_PERM_HOMEOWNER_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="home4">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>User Management</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="user1" value="32" <?php echo $f&&checkPermission(DT_PERM_USER_VIEW, $p)?'checked="checked"':''; ?>>
                                        <label for="user1">View</label>
                                        <input type="checkbox" name="p[]" id="user2" value="1048576" <?php echo $f&&checkPermission(DT_PERM_USER_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="user2">Add</label>
                                        <input type="checkbox" name="p[]" id="user3" value="2097152" <?php echo $f&&checkPermission(DT_PERM_USER_UPDATE, $p)?'checked="checked"':''; ?>>
                                        <label for="user3">Update</label>
                                        <input type="checkbox" name="p[]" id="user4" value="4194304" <?php echo $f&&checkPermission(DT_PERM_USER_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="user4">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Payments</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="pay1" value="16" <?php echo $f?(checkPermission(DT_PERM_PAYMENT_VIEW, $p)?'checked="checked"':''):'checked="checked"'; ?>>
                                        <label for="pay1">View</label>
                                        <input type="checkbox" name="p[]" id="pay2" value="512"<?php echo $f&&checkPermission(DT_PERM_PAYMENT_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="pay2">Add</label>
                                        <input type="checkbox" name="p[]" id="pay3" value="1024"<?php echo $f&&checkPermission(DT_PERM_PAYMENT_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="pay3">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Charges</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="charge1" value="2048" <?php echo $f?(checkPermission(DT_PERM_CHARGE_VIEW, $p)?'checked="checked"':''):'checked="checked"'; ?>>
                                        <label for="charge1">View</label>
                                        <input type="checkbox" name="p[]" id="charge2" value="4096" <?php echo $f&&checkPermission(DT_PERM_CHARGE_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="charge2">Add</label>
                                        <input type="checkbox" name="p[]" id="charge3" value="8192" <?php echo $f&&checkPermission(DT_PERM_CHARGE_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="charge3">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Transactions</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="cash1" value="131072" <?php echo $f&&checkPermission(DT_PERM_CASHFLOW_VIEW, $p)?'checked="checked"':''; ?>>
                                        <label for="cash1">View</label>
                                        <input type="checkbox" name="p[]" id="cash2" value="262144" <?php echo $f&&checkPermission(DT_PERM_CASHFLOW_ADD, $p)?'checked="checked"':''; ?>>
                                        <label for="cash2">Add</label>
                                        <input type="checkbox" name="p[]" id="cash3" value="524288" <?php echo $f&&checkPermission(DT_PERM_CASHFLOW_DELETE, $p)?'checked="checked"':''; ?>>
                                        <label for="cash3">Delete</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Settings</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="settings1" value="8388608" <?php echo $f?(checkPermission(DT_PERM_SETTINGS_VIEW, $p)?'checked="checked"':''):'checked="checked"'; ?>>
                                        <label for="settings1">View</label>
                                        <input type="checkbox" name="p[]" id="settings2" value="16777216" <?php echo $f&&checkPermission(DT_PERM_SETTINGS_UPDATE, $p)?'checked="checked"':''; ?>>
                                        <label for="settings2">Update</label>
                                    </fieldset>
                                </div>

                                <div data-role="collapsible" data-inset="false" data-theme="b">
                                    <h4>Reports</h4>
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="checkbox" name="p[]" id="reports1" value="2" <?php echo $f&&checkPermission(DT_PERM_REPORTS_VIEW, $p)?'checked="checked"':''; ?>>
                                        <label for="reports1">View</label>
<!--                                        <input type="checkbox" name="p[]" id="settings2" value="16777216">
                                        <label for="cash2">Update</label>-->
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="submit" data-role="button" value="Submit" data-theme="d" />
                    </form>
                </div>
                <?php displayHTMLPageFooter();
                        
            }else{header("Location: ./");}
            break;
        case "adduser":
            if(isLoggedIn() && checkPermission(DT_PERM_USER_ADD))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO user(`fullname`,`username`,`password`,`permission`,`question`,`answer`) VALUES(?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $pfullname=filter_input(INPUT_POST, "pfullname");
                $pusername=filter_input(INPUT_POST, "pusername");
                $ppassword=md5(filter_input(INPUT_POST, "ppassword"));
                $pquestion=filter_input(INPUT_POST, "pquestion");
                $panswer=md5(strtoupper(filter_input(INPUT_POST, "panswer")));
                $pcount=filter_input_array(INPUT_POST)["p"];

                $permission=0;
                while(list($key,$val)=each($pcount)) {
                    $permission += intval($val);
                }
                
                $stmt->bind_param('sssiss',$pfullname,$pusername,$ppassword,$permission,$pquestion,$panswer);
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
        case "edituser":
            $uid=filter_input(INPUT_POST, "uid");
            if(isLoggedIn() && $uid && (checkPermission(DT_PERM_USER_UPDATE)||$_SESSION["uid"]==$uid))
            {
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $pfullname=filter_input(INPUT_POST, "pfullname");
                $ppassword=filter_input(INPUT_POST, "ppassword");
                $pcount=filter_input_array(INPUT_POST)["p"];
                $permission=0;
                $err=true;
                while(list($key,$val)=each($pcount)) {
                    $permission += intval($val);
                }
                
                global $conn;
                dbConnect();
                
                $conn->autocommit(FALSE);
                
                $stmt=$conn->prepare("UPDATE user SET `fullname`=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt->bind_param('si',$pfullname,$uid);
                $err=$stmt->execute()&&$err;
                if($err && $_SESSION["uid"]==$uid){
                    $_SESSION["fullname"]=$pfullname;
                }
                
                if($ppassword!=""){
                    $stmt=$conn->prepare("UPDATE user SET `password`=? WHERE id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('si',md5($ppassword),$uid);
                    $err=$stmt->execute()&&$err;
                }
                
                if(!is_null($pcount)){
                    $stmt=$conn->prepare("UPDATE user SET `permission`=? WHERE id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('ii',$permission,$uid);
                    $err=$stmt->execute()&&$err;
                    if($err && $_SESSION["uid"]==$uid){
                        $_SESSION['permlist']=parsePermission($permission);
                    }
                }
                
                if($err){
                    $conn->commit();
                    setNotification("User ".$pfullname." has been edited.");
                }else{
                    $conn->rollback();
                    setNotification("There was a problem updating the record.",DT_NOTIF_ERROR);
                }
                
                $conn->autocommit(TRUE);
                
                dbClose();
                header("Location: ./adduserform?id=".$uid);
            }
            else{header("Location: ./");}
            break;
        case "deleteuser":
            if(isLoggedIn() && checkPermission(DT_PERM_USER_DELETE)){
                $uid=filter_input(INPUT_POST, "uid");
                if($uid!=$_SESSION["uid"])
                {
                    global $conn;
                    dbConnect();
                    $stmt=$conn->prepare("UPDATE user SET active=0 WHERE id=?");
                    if($stmt === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt->bind_param('i',$uid);
                    $stmt->execute();
                    $stmt->close();

                    setNotification("User has been deactivated.");
                    dbClose();
                }
                else{
                    setNotification("You cannot deactivate your own account.",DT_NOTIF_ERROR);
                }
                    
                header("Location: ./users");
            }else{header("Location: ./");}
            break;
        case "restoreuser":
            if(isLoggedIn() && checkPermission(DT_PERM_USER_DELETE)){
                $uid=filter_input(INPUT_GET, "uid");
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE user SET active=1 WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt->bind_param('i',$uid);
                $stmt->execute();
                $stmt->close();

                setNotification("User has been reactivated.");
                dbClose();
                    
                header("Location: ./inactiveusers");
            }else{header("Location: ./");}
            break;
        case "resetpasswordform":
            $uname=filter_input(INPUT_POST, "uusername");
            if(!$uname){
                setNotification("No such user exists.",DT_NOTIF_ERROR);
                header("Location: ./");
                break;
            }
            global $conn;
            dbConnect();
            $stmt=$conn->prepare("SELECT id, username, question FROM user WHERE username=?");
            if($stmt === false) {
                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
            }
            $stmt->bind_param('s',$uname);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows==1)
            {
                $stmt->bind_result($uid,$uname,$question);
                while($stmt->fetch()){}
            }
            else
            {
                setNotification("No such user exists.",DT_NOTIF_ERROR);
                header("Location: ./");
                break;
            }
            $stmt->free_result();
            $stmt->close();
            displayHTMLPageHeader("Reset Password"); ?>
                <article>
                    
                    <form action="./resetpassword" method="post">
                        <input type="hidden" name="uid" value="<?php echo $uid; ?>"/>
                        <ul data-role="listview" data-inset="true">
                            <li data-role="list-divider">Security Question</li>
                            <li>
                                <h3><?php echo $question; ?></h3>
                            </li>
                            <li data-role="list-divider"><label for="answer">Security Answer</label></li>
                            <li>
                                <input type="text" id="answer" name="answer" required="true"/>
                            </li>
                        </ul>
                        <div class="ui-body ui-body-a ui-corner-all">
                            <label for="ppassword">New Password</label>
                            <input id="ppassword" name="ppassword" type="password" onchange="$('#pconfirm').prop('pattern',this.value);"/>

                            <label for="pconfirm">Confirm New Password</label>
                            <input id="pconfirm" name="pconfirm" type="password"/>
                        </div>
                        
                        <input type="submit" data-role="button" value="Reset Password" data-theme="d"/>
                    </form>
                </article>
            
            <?php displayHTMLPageFooter();
            dbClose();
            break;
        case "resetpassword":
            $uid=filter_input(INPUT_POST, "uid");
            $answer=md5(strtoupper(filter_input(INPUT_POST, "answer")));
            $password=md5(filter_input(INPUT_POST, "ppassword"));
            
            global $conn;
            dbConnect();
            $stmt=$conn->prepare("SELECT id FROM user WHERE id=? AND answer=?");
            if($stmt === false) {
                trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
            }
            $stmt->bind_param('is',$uid,$answer);
            $stmt->execute();
            $stmt->store_result();
            if($stmt->num_rows>0)
            {
                $stmt->bind_result($uid);
                while($stmt->fetch()){
                    $stmt2=$conn->prepare("UPDATE user SET password=? WHERE id=?");
                    if($stmt2 === false) {
                        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                    }
                    $stmt2->bind_param('si',$password,$uid);
                    $stmt2->execute();
                    $stmt2->close();

                    setNotification("Password was successfully changed. You may now log in.");
                }
            }
            else
            {
                setNotification("The Security Answer didn't match the Security Question.",DT_NOTIF_ERROR);
            }
            $stmt->free_result();
            $stmt->close();
            header("Location: ./");
            break;
        case "homeowner":
            if((!is_null(filter_input(INPUT_GET, "id")))&&(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_VIEW)))
            {
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
                        $stmt2=$conn->prepare("SELECT a.id,a.code,a.homeowner,a.dateacquired,a.lotsize,a.housenumber,a.street,a.lot,a.block,a.phase,COALESCE(COUNT(g.id),0),a.caretaker,a.dateadded,a.user,a.active FROM lot a LEFT JOIN resident g ON g.household=a.id INNER JOIN settings f ON f.id=? WHERE a.homeowner=? AND a.active=1 GROUP BY a.id");
                        if($stmt2 === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $householdid=filter_input(INPUT_GET, "id");
                        $stmt2->bind_param('ii',$_SESSION["settings"]["id"],$householdid);
                        $stmt2->execute();
                        $stmt2->store_result();
                        $lotcount=$stmt2->num_rows;
                        displayHTMLPageHeader("Homeowner ".$lastname.", ".$firstname." ".  substr($middlename, 0, 1).".");
                        if($lotcount<=0): 
                            if($active):?>
                                <?php if(checkPermission(DT_PERM_HOMEOWNER_DELETE)): ?>
                                    <a href="#confirmHomeownerDelete" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Delete Homeowner</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if(checkPermission(DT_PERM_HOMEOWNER_DELETE)): ?>
                                    <a href="./activatehomeowner?id=<?php echo $id; ?>" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Reactivate Homeowner</a>
                                <?php endif; ?>
                        <?php endif;
                            endif; ?>
                        <?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)): ?>
                            <a href="#addHomeowner" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Homeowner</a>
                        <?php endif; ?>
                        <fieldset data-role="controlgroup" data-type="horizontal" class="pagetitleheader"><div class="ui-btn ui-btn-d">Name</div> <div class="ui-btn"><?php echo "$lastname, $firstname " . substr($middlename, 0, 1) . "."; ?></div></fieldset>
                        
                        <?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)){ displayHomeownerForm("./updatehomeowner",$lastname,$firstname,$middlename,$contactno,$email,$id,$bond,$bonddesc,$gatepass); } ?>
                        <?php if(checkPermission(DT_PERM_HOMEOWNER_DELETE)): ?>
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
                        <?php endif; ?>
                        
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
                                      <?php if(checkPermission(DT_PERM_CHARGE_VIEW)): ?><li><a href="#chargesTab" data-ajax="false" class="ui-btn-active">Charges</a></li><?php endif; ?>
                                      <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?><li><a href="#paymentsTab" data-ajax="false">Payments</a></li><?php endif; ?>
                                      <li><a href="#lotsTab" data-ajax="false">Registered Lots</a></li>
                                      <li><a href="#stickerTab" data-ajax="false">Gate Pass Stickers</a></li>
                                    </ul>
                                </div>
                                <?php if(checkPermission(DT_PERM_CHARGE_VIEW)): ?>
                                <div id="chargesTab" class="ui-body-d ui-content">
                                    <div>
                                        <?php if(!checkPermission(DT_PERM_PAYMENT_ADD) && checkPermission(DT_PERM_CHARGE_VIEW)): ?>
                                        <a href="./charges?id=<?php echo $uid; ?>" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d">View Charges</a>
                                        <?php endif; ?>
                                        <table id="tblchargeslist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>                                            

                                            </tbody>
                                            <?php
                                                $stmt5=$conn->prepare("SELECT COALESCE(SUM(a.amount),0) FROM charges a WHERE a.homeowner=? AND a.active=1");
                                                if($stmt5 === false) {
                                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                                }
                                                $stmt5->bind_param('i',$householdid);
                                                $stmt5->execute();
                                                $stmt5->store_result();
                                                $stmt5->bind_result($tamount);

                                                while($stmt5->fetch()){}
                                                $stmt5->free_result();
                                                $stmt5->close();
                                                
                                                $stmt5=$conn->prepare("SELECT COALESCE(SUM(d.amountpaid*e.active),0) FROM ledgeritem d LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1 WHERE e.homeowner=?");
                                                if($stmt5 === false) {
                                                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                                                }
                                                $stmt5->bind_param('i',$householdid);
                                                $stmt5->execute();
                                                $stmt5->store_result();
                                                $stmt5->bind_result($tamountpaid);

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
                                <?php endif; ?>
                                
                                <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>
                                <div id="paymentsTab" class="ui-body-d ui-content">
                                    <div>
                                        <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?>
                                        <a href="./charges?id=<?php echo $uid; ?>" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d">Add Payment</a>
                                        <?php endif; ?>
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
                                <?php endif; ?>
                                
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
                                                <?php if(checkPermission(DT_PERM_LOT_VIEW)): ?><a href='./lot?id=<?php echo $id; ?>' data-role='button' data-icon='info' data-iconpos='left' data-inline="true" data-theme="d">Lot Details</a><?php endif; ?>
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
                                        <?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)): ?>
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
                                        <?php endif; ?>
                                        <?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)): ?>
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
                                        <?php endif; ?>
                                        
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
                                                            <td class="textamount"><?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)): ?><a href="./deletesticker?id=<?php echo $sid; ?>&hid=<?php echo $householdid; ?>" data-role="button" data-icon="delete" data-iconpos="notext" data-theme="b" class="delsticker" data-sid="<?php echo $sid; ?>">Delete Gate Pass Sticker</a><?php endif; ?></td>
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
                                    <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>
                                    pl = setAsDataTable("#tblpaymentlist","./paymentlistss?id=<?php echo $uid; ?>",[{"targets":[5],"visible":false,"searchable":false}],[[0,"desc"]]);
                                    <?php endif; ?>
                                    <?php if(checkPermission(DT_PERM_CHARGE_VIEW)): ?>
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
                                    <?php endif; ?>
//                                    cl = $("#tblchargeslist").dataTable({"processing":true,"retrieve":true,"autoWidth":false});
//                                    clapi=cl.api();
//                                    cl.fnAdjustColumnSizing();

                                    <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>
                                    $("#tblpaymentlist").on( "draw.dt", function() {
                                        $("a.paymentdetailslink").click(function(){
                                            changeIFrameSrc($(this)[0].dataset.ledgerid);
                                        });
                                    });
                                    <?php endif; ?>
                                        
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
                                <?php if(checkPermission(DT_PERM_HOMEOWNER_UPDATE)): ?>
                                $(".delsticker").click(function(event){
                                    event.preventDefault();
                                    var sid=$(this).data("sid");
                                    $("#delstickerbtn").attr("href","./deletesticker?id="+sid+"&hid=<?php echo $householdid; ?>");
                                    $("#confirmStickerDelete").popup("open",{"transition":"pop"});
                                });
                                <?php endif; ?>

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
            if((!is_null(filter_input(INPUT_GET, "id")))&&(isLoggedIn()) && (checkPermission(DT_PERM_CHARGE_VIEW)||  checkPermission(DT_PERM_PAYMENT_ADD)))
            {
                displayHTMLPageHeader("Add Payment");

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
                                <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?>
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
                                <?php endif; ?>
                            <!--</fieldset>-->
<!--                        <ul data-role="listview" data-inset="true">
                            <li style="padding-left:40px;">
                                <span class="fauxtable textbold">Description</span><span class="fauxtable textbold">Date posted</span><span class="fauxtable textbold" style="text-align:right;">Credit</span><span class="fauxtable textbold" style="text-align:right;">Debit</span>
                            </li>
                        </ul>-->
                    
                        <!--<fieldset data-role="controlgroup">-->
                        <!--<legend>Vertical:</legend>-->
                        <?php if(checkPermission(DT_PERM_CHARGE_VIEW)|| checkPermission(DT_PERM_PAYMENT_ADD)): ?>
                        <table data-role="table" class="table table-striped table-bordered dt stripe ui-responsive" data-mode="reflow">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Date Posted</th>
                                    <th>Debit</th>
                                    <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?><th>Credit</th><?php endif; ?>
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
                                    <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?>
                                    <td style="vertical-align:middle;" class="textamount debitcell">
                                        <input class="amtcell textamount" type="number" step="0.01" data-wrapper-class="ui-amtcell" name="charges[<?php echo $id; ?>][amountpaid]" id="amtpaid-<?php echo $id; ?>" value="0" />
                                        <input type="hidden" name="charges[<?php echo $id; ?>][id]" id="chkcharge-<?php echo $id; ?>" value="<?php echo $id; ?>">
                                        <input type="hidden" name="charges[<?php echo $id; ?>][amount]" id="amt-<?php echo $id; ?>" value="<?php echo $amount; ?>" />
                                        <input type="hidden" name="charges[<?php echo $id; ?>][description]" id="desc-<?php echo $id; ?>" value="<?php echo $description; ?>" />
                                    </td>
                                    <?php endif; ?>
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
                                    <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?><th class="textamount" id="totaldebit">0.00</th><?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                        <?php endif; ?>
                            
                        <!--</fieldset>-->
                    
<!--                        <ul data-role="listview" data-inset="true">
                            <li style="padding-left:40px;">
                                <span class="fauxtable textbold">Total</span><span class="fauxtable textbold"></span><span class="fauxtable textbold" style="text-align:right;"><?php echo number_format($totalcredit,2); ?></span><span class="fauxtable textbold" style="text-align:right;" id="totaldebit">0.00</span>
                            </li>
                        </ul>-->
                            <fieldset data-role="controlgroup" data-type="horizontal">
                                <?php if(checkPermission(DT_PERM_PAYMENT_ADD)): ?><input type="submit" data-role="button" value="Submit" data-theme="d"/><?php endif; ?>
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
            if(isLoggedIn() && checkPermission(DT_PERM_CHARGE_ADD)){
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
            }else{header("Location: ./");}
            break;
        case "chargelistss":
            if(isLoggedIn() && (checkPermission(DT_PERM_PAYMENT_VIEW) || checkPermission(DT_PERM_CHARGE_VIEW)))
            {
                //SELECT a.id, a.amountpaid, a.dateposted, a.description, a.amount FROM charges a WHERE a.amountpaid<a.amount AND a.homeowner=? ORDER BY a.dateposted
                $table = 'charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"id"),
                    array('db'=>'a.dateposted','dt'=>1,"alias"=>"dateposted"),
                    array('db'=>'a.description','dt'=>2,"alias"=>"description"),
                    array('db'=>'a.amount','dt'=>3,"alias"=>"amount"),
                    array('db'=>'COALESCE(SUM(d.amountpaid*e.active),0)','dt'=>4,"alias"=>"amountpaid"),
                    array('db'=>'(a.amount-coalesce(SUM(d.amountpaid*e.active),0))','dt'=>5,"alias"=>"balance")
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
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_UPDATE)){
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
            }else{header("Location: ./");}
            break;
        case "deletesticker":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_UPDATE)){
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
            }else{header("Location: ./");}
            break;
        case "confirmdeletesticker":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_UPDATE)){
                if(!is_null($sid=filter_input(INPUT_GET, "id")))
                {
                displayHTMLHead("Confirm Delete"); ?>
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Confirm Delete?</h1>
                        </header>
                        <div data-role="main">
                            <a href="./deletesticker?id=<?php echo $sid; ?>" data-role="button">Delete</a>
                            <a href="./homeowners" data-rel="back" data-role="button">Cancel</a>
                        </div>
                    </div>
                        
            <?php
                displayHTMLFooter();
                }else{header("Location: ./");}
            }else{header("Location: ./");}
            break;
        case "addresident":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_UPDATE)){
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
            }else{header("Location: ./");}
            break;
        case "deleteresident":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_UPDATE)){
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
            }else{header("Location: ./");}
            break;
        case "confirmdeleteledger":
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT_DELETE)){
                if(!is_null($lid=filter_input(INPUT_GET, "id")))
                {
                displayHTMLHead("Cancel Payment"); ?>
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Cancel Payment</h1>
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
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT_DELETE)){
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
            if(isLoggedIn() && checkPermission(DT_PERM_CHARGE_DELETE)){
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
        case "cashflow":
            if(isLoggedIn() && checkPermission(DT_PERM_CASHFLOW_VIEW)){

                displayHTMLPageHeader("Subdivision Transactions"); ?>
                    <?php if(checkPermission(DT_PERM_CASHFLOW_ADD)): ?>
                        <a href="#addCashFlow" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Transaction</a>
                        <div data-role="popup" id="addCashFlow" data-dismissible="false" data-overlay-theme="b" class="">
                            <header data-role="header">
                              <h1>Add Transaction</h1>
                              <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                            </header>
                            <div role="main" class="ui-content">
                                <form action="./addcashflow" method="post" data-ajax="false">
                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <legend>Transaction Type</legend>
                                        <input type="radio" name="ptype" id="ptype0" value="-1" checked="checked">
                                        <label for="ptype0">Debit (Expense)</label>
                                        <input type="radio" name="ptype" id="ptype1" value="1">
                                        <label for="ptype1">Credit (Income)</label>
                                    </fieldset>

                                    <label for="pornumber">OR Number</label>
                                    <input type="text" name="pornumber" id="pornumber"/>


                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <legend>Mode of Payment</legend>
                                        <input type="radio" name="ppaymentmode" id="ppaymentmodecash" value="Cash" checked="checked">
                                        <label for="ppaymentmodecash">Cash</label>
                                        <input type="radio" name="ppaymentmode" id="ppaymentmodecheck" value="Check">
                                        <label for="ppaymentmodecheck">Check</label>
                                        <label for="pcheckno">Check Number</label>
                                        <input type="text" name="pcheckno" id="pcheckno" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Check Number" disabled="disabled"/>
                                    </fieldset>

                                    <label for="pdescription">Description</label>
                                    <input type="text" name="pdescription" id="pdescription" required="true"/>

                                    <label for="pamount">Amount</label>
                                    <input type="number" step="0.01" name="pamount" id="pamount" required="true"/>

                                    <label for="premarks">Remarks</label>
                                    <textarea name="premarks" id="premarks"></textarea>

                                    <fieldset data-role="controlgroup" data-type="horizontal">
                                        <input type="submit" value="Add" data-theme="d"/>
                                        <a href="#" data-rel="back" data-role="button">Cancel</a>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(checkPermission(DT_PERM_CASHFLOW_DELETE)): ?>    
                    <div data-role="popup" id="confirmTransactionDelete" data-dismissible="false" data-overlay-theme="b" class="confirmDialog">
                        <header data-role="header">
                          <h1>Delete Transaction</h1>
                          <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                        </header>
                        <div data-role="main" class="ui-content ui-body">
                            <form action="./deletecashflow" method="post" target="_top">
                                <input type="hidden" name="chargeid" value="0" id="chargeid"/>
                                <label for="cancelremarks">Reason for Cancellation</label>
                                <textarea required="true" id="cancelremarks" name="cancelremarks"></textarea>
                                <fieldset data-role="controlgroup" data-type="horizontal">
                                    <input type="submit" data-role="button" value="Delete" data-theme="e"/>
                                    <a href="./cashflow" data-role="button" data-rel="back" data-theme="b">Cancel</a>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                        
                    <div class="ui-content ui-body-a ui-corner-all">
                        <table id="tblcashflow" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                            <thead>
                                <tr>
                                    <th data-priority="1">Type</th>
                                    <th data-priority="1">Transaction Date</th>
                                    <th data-priority="1">OR Number</th>
                                    <th data-priority="1">Mode of Payment</th>
                                    <th data-priority="1">Description</th>
                                    <th data-priority="1">Debit</th>
                                    <th data-priority="1">Credit</th>
                                    <th data-priority="1">Remarks</th>
                                    <th data-priority="1"></th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                        
                        
                    <script type="text/javascript">
                        $(document).on("pagecreate",function(){
                            ul = setAsDataTable("#tblcashflow","./cashflowlistss",
                            [
                                {
                                    "render":function(data,type,row){
                                        return "<div class='textamount'>"+(!data?"":parseFloat(data).toFixed(2))+"</div>";
                                    },
                                    "targets":[5,6]
                                },
                                {
                                    "visible":false,
                                    "searchable":false,
                                    "targets":[0]
                                },
                                {
                                    "sortable":false,
                                    "searchable":false,
                                    "targets":[8]
                                },
                            ],[[1,"desc"]]);
                            
//                            ulapi = ul.api();
                            
                            $("#tblcashflow").on( "init.dt", function() {
                                $("#tblcashflow_wrapper").enhanceWithin();
                                $(".dataTables_wrapper div.ui-select>div.ui-btn").addClass("ui-btn-a");
                                $("#tblcashflow_filter input").on("change",function(){
                                    ul.search($(this).val()).draw();
                                });
                            });
                            
                            $("#tblcashflow").on( "draw.dt", function() {
                                <?php if(checkPermission(DT_PERM_CASHFLOW_DELETE)): ?>
                                $(".delcash").click(function(event){
                                    event.preventDefault();
                                    var sid=$(this).data("cid");
                                    $("#chargeid").attr("value",sid);
                                    $("#confirmTransactionDelete").popup("open",{"transition":"pop"});
                                });
                                <?php endif; ?>
                            });
                            
                            $("#ppaymentmodecash").click(function(){
                                $("#pcheckno").textinput("disable");
                            });
                            $("#ppaymentmodecheck").click(function(){
                                $("#pcheckno").textinput("enable");
                            });
                            
                            
                        });
                    </script>
                <?php displayHTMLPageFooter();
                
//                header("Location: ./lots");
            }else{header("Location: ./");}
            break;
        case "addcashflow":
            if(isLoggedIn() && checkPermission(DT_PERM_CASHFLOW_ADD)){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("INSERT INTO cashflows(type,ornumber,paymentmode,checkno,description,amount,remarks,user) VALUES(?,?,?,?,?,?,?,?)");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $userid=(isLoggedIn()?$_SESSION["uid"]:0);
                $type=filter_input(INPUT_POST, "ptype");
                $ornumber=filter_input(INPUT_POST, "pornumber");
                $paymentmode=filter_input(INPUT_POST, "ppaymentmode");
                $checkno=filter_input(INPUT_POST, "pcheckno");
                $description=filter_input(INPUT_POST, "pdescription");
                $amount=filter_input(INPUT_POST, "pamount");
                $remarks=filter_input(INPUT_POST, "premarks");
                
                $stmt->bind_param('issssdsi',$type,$ornumber,$paymentmode,$checkno,$description,$amount,$remarks,$userid);
                $stmt->execute();
//                $newuserid = $stmt->insert_id;
//                echo $conn->error;
                $stmt->close();

                setNotification("Transaction has been added");
                dbClose();
                header("Location: ./cashflow");
            }else{header("Location: ./");}
            break;
        case "cashflowlistss":
            if(isLoggedIn() && checkPermission(DT_PERM_CASHFLOW_VIEW))
            {
                $table = 'cashflows';
                $primaryKey = 'id';
                $columns = array(
                    
                    array('db'=>'type','dt'=>0),
                    array('db'=>'transactiondate','dt'=>1),
                    array('db'=>'ornumber','dt'=>2),
                    array('db'=>'IF(`paymentmode`="Check",CONCAT("Check (",`checkno`,")"),`paymentmode`)','dt'=>3),
                    array('db'=>'description','dt'=>4),
                    array('db'=>"IF(`type`<0,`amount`,'')",'dt'=>5),
                    array('db'=>"IF(`type`>=0,`amount`,'')",'dt'=>6),
                    array('db'=>'remarks','dt'=>7),
                    array('db'=>'id','dt'=>8,'formatter'=>function($d,$row){return checkPermission(DT_PERM_CASHFLOW_DELETE)?"<a href='#' data-enhanced='true' class='delcash ui-link ui-btn ui-icon-delete ui-btn-icon-notext ui-shadow ui-corner-all' data-role='button' data-icon='delete' data-iconpos='notext' data-cid='".$d."' title='Delete Transaction' >Delete Transaction</a>":"";})
//                    array('db'=>'username','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
//                    array('db'=>'fullname','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";})
                );
                
                $addwhere="active=1";
                $group="";
                $counttable="cashflows";
                $countwhere="active=1";
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere));
            }else{header("Location: ./");}
            break;
        case "deletecashflow":
            if(isLoggedIn() && checkPermission(DT_PERM_CASHFLOW_DELETE)){
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE cashflows SET active=0, cancelremarks=?, canceluser=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $lid=filter_input(INPUT_POST, "chargeid");
                $cancelremarks=filter_input(INPUT_POST, "cancelremarks");
                $canceluser=$_SESSION["uid"];
                
                $stmt->bind_param('sii',$cancelremarks,$canceluser,$lid);
                $stmt->execute();
                $stmt->close();

                setNotification("The transaction has been deleted.");
                dbClose();
                header("Location: ./cashflow");
            }else{header("Location: ./");}
            break;
        case "homeowners":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_VIEW))
            {
                displayHTMLPageHeader("Homeowners"); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <?php if(checkPermission(DT_PERM_HOMEOWNER_ADD)): ?><a href="#addHomeowner" data-role="button" data-icon="plus" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Homeowner</a><?php endif; ?>
                    <a href="./inactivehomeowners" data-role="button" data-icon="forbidden" data-theme="b">Deleted Homeowners</a>
                </fieldset>
                
                <?php if(checkPermission(DT_PERM_HOMEOWNER_ADD)): ?><?php displayHomeownerForm(); ?><?php endif; ?>
                
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
                            hol = setAsDataTable("#tblhomeownerlist","./homeownerlistss",[{"targets":[0],"visible":false,"searchable":false},{"targets":[3],"searchable":false}],[[1,"asc"]]);
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
            if(isLoggedIn() && (checkPermission(DT_PERM_HOMEOWNER_VIEW) || checkPermission(DT_PERM_HOMEOWNER_DELETE)))
            {
                displayHTMLPageHeader("Deactivated Homeowners"); ?>
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
                                            return '<?php if(checkPermission(DT_PERM_HOMEOWNER_DELETE)): ?><a href="./activatehomeowner?id='+data+'" data-role="button" data-iconpos="left" data-icon="check" data-ajax="false" data-mini="true">Reactivate Homeowner</a><?php endif; ?>';
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
            }else{header("Location: ./");}
            break;
        case "homeownerlistss":
            if(isLoggedIn() && checkPermission(DT_PERM_HOMEOWNER_VIEW))
            {
                $table = 'homeowner a LEFT JOIN charges b ON a.id=b.homeowner LEFT JOIN ledgeritem d ON d.chargeid=b.id LEFT JOIN ledger e ON e.id=d.ledgerid';
                $primaryKey = 'id';
                $columns = array(
                    //array('db'=>'id','dt'=>0),
                    array('db'=>'formatName(a.lastname,a.firstname,a.middlename)','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"aliascols"=>"a.lastname,a.firstname,a.middlename"),
                    array('db'=>'a.contactno','dt'=>2,"alias"=>"contactno", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.email','dt'=>3,"alias"=>"email", 'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'((SELECT SUM(amount) FROM charges WHERE homeowner=a.id)-SUM(coalesce(d.amountpaid,0)*coalesce(e.active,0)))*a.active','dt'=>4,"alias"=>"balance" ,'formatter'=>function($d,$row){return "<a href='./homeowner?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";}),
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
            }else{header("Location: ./");}
            break;
        case "userlistss":
            if(isLoggedIn() && checkPermission(DT_PERM_USER_VIEW))
            {
                $table = 'user';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'id','dt'=>'DT_RowId'),
                    array('db'=>'username','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'fullname','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'id','dt'=>2,'formatter'=>function($d,$row){return (checkPermission(DT_PERM_USER_DELETE)?"<a href='#' data-enhanced='true' class='deluser ui-link ui-btn ui-icon-delete ui-btn-icon-notext ui-shadow ui-corner-all ui-btn-inline ".($d==1?"ui-disabled":"")."' data-role='button' data-icon='delete' data-iconpos='notext' data-uid='".$d."' title='Deactivate User' >Deactivate User</a>":"").(checkPermission(DT_PERM_USER_UPDATE)?"<a href='./adduserform?id=".$d."' data-enhanced='true' class='ui-link ui-btn ui-icon-edit ui-btn-icon-notext ui-shadow ui-corner-all ".(!checkPermission(DT_PERM_USER_UPDATE)?"ui-disabled":"")." ui-btn-inline' data-role='button' data-icon='edit' data-iconpos='notext' title='Edit User' >Edit User</a>":"");})
                );
                $addwhere="active=1";
                $group="";
                $counttable="user";
                $countwhere="active=1";
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere));
            }else{header("Location: ./");}
            break;
        case "inactiveuserlistss":
            if(isLoggedIn() && (checkPermission(DT_PERM_USER_VIEW) || checkPermission(DT_PERM_USER_DELETE)))
            {
                $table = 'user';
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'id','dt'=>'DT_RowId'),
                    array('db'=>'username','dt'=>0, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'fullname','dt'=>1, 'formatter'=>function($d,$row){return "<a href='./user?id=".$row['id']."' class='tablecelllink'>".$d."</a>";}),
                    array('db'=>'id','dt'=>2,'formatter'=>function($d,$row){return checkPermission(DT_PERM_USER_DELETE)?"<a href='./restoreuser?uid=".$d."' data-enhanced='true' class='deluser ui-link ui-btn ui-icon-check ui-btn-icon-left ui-shadow ui-corner-all' data-role='button' data-icon='check' data-uid='".$d."' title='Reactivate User' >Reactivate User</a>":"";})
                );
                $addwhere="active=0";
                $group="";
                $counttable="user";
                $countwhere="active=0";
                $sql_details = array('user'=>DT_DB_USER,'pass'=>DT_DB_PASSWORD,'db'=>DT_DB_NAME,'host'=>DT_DB_SERVER);
                require('ssp.class.php');
                echo json_encode(SSP::customQuery(filter_input_array(INPUT_GET), $sql_details, $table, $primaryKey, $columns, $addwhere, $group, $counttable,$countwhere));
            }else{header("Location: ./");}
            break;
        case "inactiveownerlistss":
            if(isLoggedIn() && (checkPermission(DT_PERM_HOMEOWNER_VIEW)||checkPermission(DT_PERM_HOMEOWNER_DELETE)))
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
            }else{header("Location: ./");}
            break;
        case "setasowner":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_UPDATE))
            {
                global $conn;
                dbConnect();
                $stmt=$conn->prepare("UPDATE lot SET homeowner=?, dateacquired=? WHERE id=?");
                if($stmt === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $homeowner=filter_input(INPUT_POST, "owner-filter-menu");
                $dateacquired=filter_input(INPUT_POST, "dateacquired");
//                $numberinhousehold=filter_input(INPUT_POST, "numberinhousehold");
//                $numberinhouseholdc=filter_input(INPUT_POST, "numberinhouseholdc");
                $lotid=filter_input(INPUT_POST, "lotid");
                if($homeowner==0)
                {
                    setNotification("No owner selected.",DT_NOTIF_WARNING);
                }
                else
                {
                    $stmt->bind_param('isi',$homeowner,$dateacquired,$lotid);
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_UPDATE))
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
            if(isLoggedIn() & checkPermission(DT_PERM_LOT_DELETE))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_DELETE))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_VIEW))
            {
                displayHTMLPageHeader("Lot Management"); ?>
                <fieldset data-role="controlgroup" data-type="horizontal">
                    <?php if(checkPermission(DT_PERM_LOT_ADD)): ?><a href="#addLotForm" data-role="button" data-icon="plus" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="d">Add Lot</a><?php endif; ?>
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
            }else{header("Location: ./");}
            break;
        case "addlot":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_ADD))
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
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_UPDATE))
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
            if(isLoggedIn() && (checkPermission(DT_PERM_LOT_VIEW) || checkPermission(DT_PERM_LOT_DELETE)))
            {
                displayHTMLPageHeader("Deactivated Lots"); ?>
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
            }else{header("Location: ./");}
            break;
        case "inactivelotlistss":
            if(isLoggedIn() && (checkPermission(DT_PERM_LOT_VIEW)||  checkPermission(DT_PERM_LOT_DELETE)))
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
            }else{header("Location: ./");}
            break;
        case "lotlistss":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_VIEW))
            {
                $table = 'lot a 
LEFT JOIN homeowner b ON a.homeowner=b.id 
LEFT JOIN charges c ON a.id=c.lot AND c.active=1 
LEFT JOIN ledgeritem d ON d.chargeid=c.id 
LEFT JOIN ledger e ON d.ledgerid=e.id 
INNER JOIN settings f ON f.id='.$_SESSION["settings"]["id"];
                $primaryKey = 'id';
                $columns = array(
                    array('db'=>'a.id','dt'=>0,"alias"=>"uid", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'a.code','dt'=>1,"alias"=>"code", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'formatAddress(a.housenumber,a.lot,a.block,a.street,a.phase)','dt'=>2, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"alias"=>"address","aliascols"=>"a.housenumber,a.lot,a.block,a.street,a.phase"),
                    array('db'=>'a.lotsize','dt'=>3,"alias"=>"lotsize", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";}),
                    array('db'=>'(a.lotsize*f.price)','dt'=>4, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";},"alias"=>"dues","aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'formatName(b.lastname,b.firstname,b.middlename)','dt'=>5,"alias"=>"homeowner", 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink' data-ajax='false'>".$d."</a>";},"aliascols"=>"b.lastname,b.firstname,b.middlename"),
                    array('db'=>'((SELECT SUM(amount) FROM charges WHERE lot=a.id)-coalesce(SUM(d.amountpaid*e.active),0)*b.active)','dt'=>6,"alias"=>"balance","aliascols"=>"c.amount,c.amountpaid",'DT_RowData'=>function($d,$row){return date('Ymd',  strtotime($row['enddate']));}, 'formatter'=>function($d,$row){return "<a href='./lot?id=".$row['uid']."' class='tablecelllink textamount' data-ajax='false'>".number_format($d,2)."</a>";})
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
            }else{header("Location: ./");}
            break;
        case "lot":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_VIEW))
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
                        
                        $stmt->bind_result($id, $code, $homeowner, $dateacquired, $lotsize, $housenumber, $street, $lot, $block, $phase, $numberinhousehold, $numberinhouseholdc, $active, $homeownername, $active);
                        while($stmt->fetch()){ ?><?php 
                            displayHTMLPageHeader("Lot ".$code);
                            if($homeowner==0): 
                                if($active>0):?>
                                    <?php if(checkPermission(DT_PERM_LOT_DELETE)): ?><a href="#confirmLotDelete" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Remove Lot</a> <?php endif; ?><?php 
                                else: ?>
                                    <?php if(checkPermission(DT_PERM_LOT_DELETE)): ?><a href="./activatelot?id=<?php echo $id; ?>" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" class="editbtns" data-theme="a">Reactivate Lot</a><?php endif; ?> <?php
                                endif;
                            endif;?>
                            <fieldset data-role="controlgroup" data-type="horizontal" class="editbtns">
                                <?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?><a href="#addLotForm" data-role="button" data-icon="edit" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" class="editbtns" data-theme="a">Update Lot</a><?php endif; ?>
                                <?php if($homeowner>0):?><a href="./bill?id=<?php echo $lid; ?>" data-role="button" data-icon="bars" data-iconpos="left" data-inline="true" class="editbtns" data-theme="a" target="_blank">Print Billing Statement</a><?php endif; ?>
                            </fieldset>
                            <?php displayLotForm("./updatelot", $code, $lotsize, $housenumber, $street, $lot, $block, $phase, $id) ?>
                            
                            <?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?>
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
                            <?php endif; ?>
                                    
                            <?php if(checkPermission(DT_PERM_LOT_DELETE)): ?>
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
                            <?php endif; ?>
                            
                            <?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?>
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
                            <?php endif; ?>
                                    
                                    
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
                                                <td><?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?><a href="./deleteresident?id=<?php echo $rid; ?>&lid=<?php echo $lid; ?>" data-role="button" data-icon="delete" data-iconpos="notext" data-mini="true" class="delresident">Delete Resident</a><?php endif; ?></td>
                                            </tr>
                                    <?php endwhile;
                                }
                                else{
                                    ?>
                                    <tr>
                                        <td colspan="4"><em>No record found.</em></td>
                                    </tr>    
                                    <?php
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
                                    
                                    $householdsize=$numberinhousehold=$numberinhouseholdc=0;

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
                                    <li><a href="<?php echo checkPermission(DT_PERM_HOMEOWNER_VIEW)?"./homeowner?id=".$homeowner:"#"; ?>"><span class="infoheader">Name</span> <?php echo $homeownername; ?></a><?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?><a href="#confirmOwnerDelete" data-icon="delete" data-theme="b" data-rel="popup" data-position-to="window" data-transition="pop">Remove Owner</a><?php endif; ?></li>
                                    <li><span class="infoheader">Date Acquired</span> <?php echo $dateacquired; ?></li>
                                    <li><a href="#showResidents" data-rel="popup" data-position-to="window" data-transition="pop"><span class="infoheader">Household Size</span> <?php echo $householdsize; ?> (<?php echo $numberinhousehold; ?> Adult<?php echo ($numberinhousehold==1?"":"s"); ?>, <?php echo $numberinhouseholdc; ?> Child<?php echo ($numberinhouseholdc==1?"":"ren"); ?>)</a><?php if(checkPermission(DT_PERM_LOT_UPDATE)): ?><a href="#addResident" data-icon="plus" data-theme="b" data-rel="popup" data-position-to="window" data-transition="pop">Add</a><?php endif; ?></li>
                                    
                                <?php elseif(($active>0) && checkPermission(DT_PERM_LOT_UPDATE)): ?>
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
<!--                                        <label for="numberinhousehold">Adults in Household</label>
                                        <input type="number" name="numberinhousehold" id="numberinhousehold" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Adults in Household"/>
                                        <label for="numberinhouseholdc">Children in Household</label>
                                        <input type="number" name="numberinhouseholdc" id="numberinhouseholdc" data-wrapper-class="controlgroup-textinput ui-btn" placeholder="Children in Household"/>-->
                                        <input type="hidden" name="lotid" value="<?php echo $id; ?>"/>
                                        <input type="submit" value="Add" data-role="button" data-icon="plus" data-theme="d"/>
                                        </fieldset>
                                        <div class="ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left">Select the name of the owner and acquisition date.</div>
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
                                        <?php if(checkPermission(DT_PERM_CHARGE_ADD)||checkPermission(DT_PERM_PAYMENT_ADD)||checkPermission(DT_PERM_CHARGE_VIEW)): ?>
                                        <fieldset data-role="controlgroup" data-type="horizontal">
                                            <?php if(checkPermission(DT_PERM_PAYMENT_ADD)||checkPermission(DT_PERM_CHARGE_VIEW)): ?><a href="./charges?id=<?php echo $homeowner; ?>" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d">Add Payment</a><?php endif; ?>
                                            <?php if(checkPermission(DT_PERM_CHARGE_ADD)): ?><a href="#addCharges" data-role="button" data-icon="plus" data-inline="true" id="addPaymentBtns" data-theme="d" data-rel="popup" data-position-to="window" data-transition="pop">Add Charge</a><?php endif; ?>
                                        </fieldset>
                                        <?php endif; ?>
                                        <?php if(checkPermission(DT_PERM_CHARGE_ADD)): ?>
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
                                    <?php endif; ?>
                                    <?php if(checkPermission(DT_PERM_CHARGE_DELETE)): ?>
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
                                    <?php endif; ?>
                                    
                                    <table id="lotpaymentlist" class="table table-striped table-bordered dt stripe ui-responsive" data-role="table" data-mode="reflow">
                                        <thead>
                                            
                                            <tr>
                                                <!--<th data-priority="1">ID</th>-->
                                                <th data-priority="1" rowspan="2">Date Posted</th>
                                                <th data-priority="1" rowspan="2">Description</th>
                                                <th data-priority="4" colspan="3">Latest Payment</th>
                                                <th data-priority="1" rowspan="2">Debit</th>
                                                <th data-priority="1" rowspan="2">Credit</th>
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
                                                        return (row[8]==="0"?'<a class="textamount tablecelllink paymentdetailslink ui-link" style="display:inline-block;">'+parseFloat(data).toFixed(2)+'</a>':'<a href="#popupReceipt" <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>data-rel="popup" data-position-to="window"<?php endif; ?> class="tablecelllink paymentdetailslink textamount" data-ledgerid="'+row[8]+'">'+parseFloat(data).toFixed(2)+'</a>');
                                                    },
                                                    "searchable":false,
                                                    "targets":[5,6,7,]
                                                },
                                                {
                                                    "render":function(data,type,row){
                                                        return (!row[8]?"<a class='tablecelllink paymentdetailslink ui-link'>"+(!data?"":data)+"</a>":'<a href="#popupReceipt" <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>data-rel="popup" data-position-to="window"<?php endif; ?> class="tablecelllink paymentdetailslink" data-ledgerid="'+row[8]+'">'+(!data?"":data)+'</a>');
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
                                            <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>
                                            $("a.paymentdetailslink[href]").click(function(){
                                                changeIFrameSrc($(this)[0].dataset.ledgerid);
                                            });
                                            
                                            if(istblinit)
                                            {
//                                                $(".delcharge").button();
                                            }
//                                            $(".delcharge").off("click",confirmdelcharge);
                                            $(".delcharge").on("click",confirmdelcharge);
                                            <?php endif; ?>
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
                                        <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?>$("#popupReceipt").append('<iframe id="paymentdetailsframe" src="./paymentdetails?id='+lid+'" width="640" height="480" seamless=""></iframe>');<?php endif; ?>
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
            if(isLoggedIn() && (checkPermission(DT_PERM_PAYMENT_VIEW) || checkPermission(DT_PERM_LOT_VIEW)))
            {
                $table = 'charges a LEFT JOIN ledgeritem c ON c.chargeid=a.id LEFT JOIN ledger b ON c.ledgerid=b.id LEFT JOIN (SELECT id, MAX(transactiondate) AS transactiondate FROM ledger GROUP BY id) m ON b.id=m.id  ';
                $primaryKey = 'a.id';
                $columns = array(
//                    array('db'=>'a.id','dt'=>0,"alias"=>"id"),
                    array('db'=>'a.dateposted','dt'=>0,"alias"=>"dateposted"),
                    array('db'=>'a.description','dt'=>1,"alias"=>"description"),
                    array('db'=>'b.transactiondate','dt'=>2,"alias"=>"transactiondate"),
                    array('db'=>'b.ornumber','dt'=>3,"alias"=>"ornumber"),
                    array('db'=>'b.payee','dt'=>4,"alias"=>"payee"),
                    array('db'=>'COALESCE(a.amount,0)','dt'=>5,"alias"=>"amount"),
                    array('db'=>'COALESCE(SUM(c.amountpaid*b.active),0)','dt'=>6,"alias"=>"amountpaid"),
                    array('db'=>'(a.amount-COALESCE(SUM(c.amountpaid*b.active),0))','dt'=>7),
                    array('db'=>'b.id',"alias"=>"ledgerid",'dt'=>8),
                    array('db'=>'a.id','dt'=>9,"alias"=>"id",'formatter'=>function($d,$row){return checkPermission(DT_PERM_CHARGE_DELETE)?"<a href='#' data-enhanced='true' class='delcharge ui-link ui-btn ui-icon-delete ui-btn-icon-notext ui-shadow ui-corner-all ".($row[3]==""?"":"ui-disabled")."' data-role='button' data-icon='delete' data-iconpos='notext' data-cid='".$d."' title='Delete Charge' >Delete Charge</a>":"";})
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
            }else{header("Location: ./");}
            break;
        case "addpayment":
            if(isLoggedIn() && checkPermission(DT_PERM_PAYMENT_ADD))
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
                    setNotification("Payment with OR Number $ornumber has been added. <a href='#'>Print</a>");
                }
                else
                {
                    $conn->rollback();
                    setNotification("There was an error in processing the payment.",DT_NOTIF_ERROR);
                }
                $conn->autocommit(TRUE);
                dbClose();
                header("Location: ./homeowner?id=".  filter_input(INPUT_POST, "homeowner"));
            }else{header("Location: ./");}
            break;
        case "paymentlistss":
            if(isLoggedIn() && (checkPermission(DT_PERM_PAYMENT_VIEW)||  checkPermission(DT_PERM_HOMEOWNER_VIEW)))
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
            }else{header("Location: ./");}
            break;
        case "paymentdetails":
            if((isLoggedIn()) && (!is_null($ledgerid=filter_input(INPUT_GET, "id"))))
            {
                displayHTMLHead("Order of Payment"); ?>
                

                    <div data-role="page">
                        <header data-role="header">
                            <h1>Order of Payment</h1>
                            <?php if(checkPermission(DT_PERM_PAYMENT_VIEW)): ?><a href="./orderpayment?id=<?php echo $ledgerid; ?>" data-role="button" target="_blank">Print</a><?php endif; ?>
                            <?php if(checkPermission(DT_PERM_PAYMENT_DELETE)): ?><a href="./confirmdeleteledger?id=<?php echo $ledgerid; ?>" data-role="button">Cancel Payment</a><?php endif; ?>
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
            if((isLoggedIn()) && (!is_null($ledgerid=filter_input(INPUT_GET, "id"))) && checkPermission(DT_PERM_PAYMENT_VIEW))
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
            if(isLoggedIn() && checkPermission(DT_PERM_SETTINGS_UPDATE))
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
                $_SESSION['settings']['assocname']=$assocname;
                $_SESSION['settings']['acronym']=$acronym;
                $_SESSION['settings']['subdname']=$subdname;
                $_SESSION['settings']['brgy']=$brgy;
                $_SESSION['settings']['city']=$city;
                $_SESSION['settings']['province']=$province;
                $_SESSION['settings']['zipcode']=$zipcode;
                $_SESSION['settings']['contactno']=$contactno;
                $_SESSION['settings']['email']=$email;
                $_SESSION['settings']['price']=$price;
                $_SESSION['settings']['interest']=$interest;
                //$_SESSION['settings']['intgraceperiod']
                $stmt->close();

                setNotification("Settings has been saved.");
                dbClose();
                header("Location: ./");
            }else{header("Location: ./");}
            break;
        case "bill":
            if(isLoggedIn() && checkPermission(DT_PERM_LOT_VIEW)):
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
            else:
                header("Location: ./");
            endif;
            break;
        case "reports":
            if(isLoggedIn() && checkPermission(DT_PERM_REPORTS_VIEW)){
                displayHTMLPageHeader();
                $ownerselect=''; 
                $lotselect='';
                global $conn;
                dbConnect();
                $stmt2=$conn->prepare("SELECT id,formatName(lastname,firstname,middlename) AS name,contactno,email FROM homeowner WHERE active=1 ORDER BY lastname ASC");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt2->execute();
                $stmt2->store_result();

                if($stmt2->num_rows>0){
                    $stmt2->bind_result($uid,$uname,$ucontactno,$uemail);
                    while($stmt2->fetch()):
                        $ownerselect .= '<option value="'.$uid.'">'.$uname.'</option>'; 
                    endwhile;
                }
                $stmt2->free_result();
                
                $stmt2=$conn->prepare("SELECT id,code, formatAddress(housenumber,lot,block,street,phase) AS address FROM lot WHERE active=1");
                if($stmt2 === false) {
                    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                }
                $stmt2->execute();
                $stmt2->store_result();

                if($stmt2->num_rows>0){
                    $stmt2->bind_result($lid,$code,$address);
                    while($stmt2->fetch()):
                        $lotselect .= '<option value="'.$lid.'" title="'.$address.'">'.$code.' ('.$address.')</option>'; 
                    endwhile;
                }
                $stmt2->free_result();
                $stmt2->close();
                dbClose();
                ?>
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
                        
                        <form action="./report?t=homeownerbalances" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Homeowner Balances</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=gatepasslist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Gate Pass Issued</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=homeownercharges" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Charges per Homeowner</legend>
                                <label for="owner-filter-menu">Select lot owner</label>
                                <select id="owner-filter-menu" name="owner-filter-menu" data-native-menu="false" required="true" data-inline="true">
                                    <?php echo $ownerselect; ?>
                                </select>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh01" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh01" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh01" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh01" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allcharges" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Charges List</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh02" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh02" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh02" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh02" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allchargescancel" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Cancelled Charges</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh02" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh02" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh02" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh02" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=lotcharges" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Charges per Lot</legend>
                                <label for="lot-filter-menu">Select Lot</label>
                                <select id="lot-filter-menu" name="lot-filter-menu" data-native-menu="false" required="true" data-inline="true">
                                    <?php echo $lotselect; ?>
                                </select>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh03" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh03" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh03" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh03" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allpayments" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Payments List</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allpaymentscancel" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Cancelled Payments</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allpaymentscash" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Cash Payments</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allpaymentscheck" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Check Payments</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=allreceipts" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Official Receipts Issued</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=alltransactions" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Subdivision Transactions</legend>
                                <div class="ui-grid-a">
                                    <div class="ui-block-a">
                                        <label for="startdateh04" data-inline="true">From</label>
                                        <input type="date" data-inline="true" name="startdate" id="startdateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                    <div class="ui-block-b">
                                        <label for="enddateh04" data-inline="true">To</label>
                                        <input type="date" data-inline="true" name="enddate" id="enddateh04" value="<?php echo date("Y-m-d"); ?>"/>
                                    </div>
                                </div>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=userlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>User List</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=userlistinactive" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Inactive User List</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                        <form action="./report?t=residentlist" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Resident List</legend>
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
                        
                        <form action="./report?t=lotbalances" method="post" target="_blank">
                            <fieldset data-role="collapsible" data-theme="a" data-inset="false">
                                <legend>Lot Arrears</legend>
                                <input type="submit" value="Generate" data-inline="true"/>
                            </fieldset>
                        </form>
                        
                    </div>
                    <div data-role="collapsible">
                        <h3>Financial</h3>
                    
                    </div>
                </div>
                
                <script type="text/javascript">
                    $(document).on("pagecreate",function(){
                        
                    })
                    .on( "listviewcreate", "#filter-menu-menu, .ui-selectmenu-list.ui-listview", function( event ) {
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
//                            return;
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
//                            return;
                        }
                        listview = data.toPage.jqmData( "listview" ),
                        form = listview.jqmData( "filter-form" );
                        // Put the form back in the popup. It goes ahead of the listview.
                        listview.before( form );
                    });
                </script>
                
                <?php displayHTMLPageFooter();
            }
            break;
        case "report":
            if(isLoggedIn() && checkPermission(DT_PERM_REPORTS_VIEW)){
                $resultset=array();
                $resultcolumns=array();
                $resultfooter=null;
                $resultclasses=array();
                $title="";
                $subtitle="";
                $msg="";

                global $conn;
                dbConnect();

                switch(filter_input(INPUT_GET, "t"))
                {
                    case "homeownerlist":
                        $title="List of Homeowners";
                        $msg="";
                        $stmt=$conn->prepare("SELECT a.`lastname`, a.`firstname`, a.`middlename`, a.`contactno`, a.`email`, FORMAT(a.`bond`,2), COALESCE(COUNT(b.`id`),0) FROM `homeowner` a LEFT JOIN gatepass b ON a.id=b.homeowner WHERE `active`=1 GROUP BY a.id");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Last Name","First Name", "Middle Name", "Contact No.", "Email","Bond","Issued Gatepass"];
                        $resultclasses = ["","","","","","textamount","textamount"];
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
                        $stmt=$conn->prepare("SELECT a.`lastname`, a.`firstname`, a.`middlename`, a.`contactno`, a.`email`, FORMAT(a.`bond`,2), COALESCE(COUNT(b.`id`),0) FROM `homeowner` a LEFT JOIN gatepass b ON a.id=b.homeowner WHERE `active`=0 GROUP BY a.id");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Last Name","First Name", "Middle Name", "Contact No.", "Email","Bond","Issued Gatepass"];
                        $resultclasses = ["","","","","","textamount","textamount"];
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
                        $stmt=$conn->prepare('SELECT formatName(`lastname`,`firstname`,`middlename`) AS fullname, `bond`, bonddesc FROM `homeowner` WHERE `active`=1 AND `bond`>0');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Name","Bond","Remarks"];
                        $resultclasses = ["","textamount total",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($name,$bond,$bonddesc);
                            while($stmt->fetch()){
                                $resultset[]=array($name,  number_format($bond,2),$bonddesc);
                                
                            }
                            $resultfooter=array("Total Bonds","","");
                        }
                        break;
                    case "homeownerbalances":
                        $title="List of Homeowners Balances";
                        $msg="";
                        $stmt=$conn->prepare('SELECT formatName(a.lastname,a.firstname,a.middlename), (SELECT COUNT(id) FROM lot WHERE homeowner=a.id) AS lots, COALESCE(((SELECT SUM(amount) FROM charges WHERE homeowner=a.id)-SUM(coalesce(d.amountpaid,0)*coalesce(e.active,0)))*a.active,0) AS balance
			FROM homeowner a LEFT JOIN charges b ON a.id=b.homeowner LEFT JOIN ledgeritem d ON d.chargeid=b.id LEFT JOIN ledger e ON e.id=d.ledgerid
                        WHERE a.active=1
                        GROUP BY a.id
                        ORDER BY formatName(a.lastname,a.firstname,a.middlename) ASC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Name","Number of Lots","Balance"];
                        $resultclasses = ["","textamount total","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($name,$lotnos,$balance);
                            $totalbal=0;
                            while($stmt->fetch()){
                                $totalbal += $balance;
                                $resultset[]=array($name, $lotnos, number_format($balance,2));
                                
                            }
                            $resultfooter=array("Total Balances","",number_format($totalbal,2));
                        }
                        break;
                    case "gatepasslist":
                        $title="List of Gate Pass Stickers";
                        $msg="";
                        $stmt=$conn->prepare('SELECT a.transactiondate, a.serial,formatName(b.lastname,b.firstname,b.middlename) AS homeowner, a.plateno, a.model, a.remarks FROM gatepass a INNER JOIN homeowner b ON a.homeowner=b.id');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Date Issued","Control No.","Homeowner","Plate No.","Model","Remarks"];
                        $resultclasses = ["","","","","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$serial,$homeowner,$plateno,$model,$remarks);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$serial,$homeowner,$plateno,$model,$remarks);
                                
                            }
                            $resultfooter=array("","","","","","");
                        }
                        break;
                        
                    case "lotbalances":
                        $title="Lot Arrears";
                        $msg="";
                        $stmt=$conn->prepare('SELECT a.code AS code, formatAddress(a.housenumber,a.lot,a.block,a.street,a.phase) AS address, a.lotsize AS lotsize, (a.lotsize*f.price) AS dues, formatName(b.lastname,b.firstname,b.middlename) AS homeowner, COALESCE((SELECT SUM(amount) FROM charges WHERE lot=a.id)-coalesce(SUM(d.amountpaid*e.active),0)*b.active,0) AS balance
			FROM lot a 
LEFT JOIN homeowner b ON a.homeowner=b.id 
LEFT JOIN charges c ON a.id=c.lot AND c.active=1 
LEFT JOIN ledgeritem d ON d.chargeid=c.id 
LEFT JOIN ledger e ON d.ledgerid=e.id 
INNER JOIN settings f ON f.id=1
                        WHERE a.active=1
                        GROUP BY a.id');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Lot Code","Address","Lot Size (Sq. M.)","Monthly Dues","Homeowner","Balance"];
                        $resultclasses = ["","","textamount","textamount","","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($lotcode,$address,$lotsize,$dues,$homeowner,$balance);
                            $totalbal=0;
                            while($stmt->fetch()){
                                $totalbal += $balance;
                                $resultset[]=array($lotcode,$address,number_format($lotsize),number_format($dues,2),$homeowner,  number_format($balance,2));
                                
                            }
                            $resultfooter=array("Total","","","","",  number_format($totalbal,2));
                        }
                        break;
                    case "homeownercharges":
                        $uid=filter_input(INPUT_POST, "owner-filter-menu");
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $stmt=$conn->prepare('SELECT formatName(lastname,firstname,middlename),contactno,email FROM homeowner WHERE id=?');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param('i',$uid);
                        $stmt->execute();

                        $stmt->store_result();
                        $stmt->bind_result($name,$contactno,$email);
                        if($stmt->num_rows>0)
                        {
                            while($stmt->fetch()){}
                        }
                        $stmt->free_result();
                        
                        $title="List of Charges for ".$name;
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT SQL_CALC_FOUND_ROWS a.dateposted AS dateposted, a.description AS description, a.amount AS amount, COALESCE(SUM(d.amountpaid*e.active),0) AS amountpaid, (a.amount-coalesce(SUM(d.amountpaid*e.active),0)) AS balance
			FROM charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1
                        WHERE a.active=1 AND a.homeowner=? AND a.dateposted>=? AND a.dateposted<=?
                        GROUP BY a.id
                        ORDER BY dateposted DESC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("iss",$uid,$startdate,$enddate);
                        $resultcolumns = ["Date","Description","Debit","Credit","Balance"];
                        $resultclasses = ["","","textamount total","textamount total","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($dateposted,$description,$debit,$credit,$balance);
                            $totalbal=0;
                            $totalc=0;
                            $totald=0;
                            while($stmt->fetch()){
                                $totalbal += $balance;
                                $totalc += $credit;
                                $totald += $debit;
                                $resultset[]=array($dateposted,$description,number_format($debit,2),number_format($credit,2),number_format($balance,2));
                                
                            }
                            $resultfooter=array("Total","",number_format($totald,2),number_format($totalc,2),number_format($totalbal,2));
                        }
                        break;
                    case "lotcharges":
                        $lid=filter_input(INPUT_POST, "lot-filter-menu");
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $stmt=$conn->prepare('SELECT id,code, formatAddress(housenumber,lot,block,street,phase) AS address FROM lot WHERE id=?');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param('i',$lid);
                        $stmt->execute();

                        $stmt->store_result();
                        $stmt->bind_result($lotid,$code,$address);
                        if($stmt->num_rows>0)
                        {
                            while($stmt->fetch()){}
                        }
                        $stmt->free_result();
                        
                        $title="List of Charges for Lot ".$code;
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT SQL_CALC_FOUND_ROWS a.dateposted AS dateposted, a.description AS description, a.amount AS amount, COALESCE(SUM(d.amountpaid*e.active),0) AS amountpaid, (a.amount-coalesce(SUM(d.amountpaid*e.active),0)) AS balance
			FROM charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1 INNER JOIN lot c ON c.id=a.lot
                        WHERE a.active=1 AND a.lot=? AND a.dateposted>=? AND a.dateposted<=?
                        GROUP BY a.id
                        ORDER BY dateposted DESC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("iss",$lid,$startdate,$enddate);
                        $resultcolumns = ["Date","Description","Debit","Credit","Balance"];
                        $resultclasses = ["","","textamount total","textamount total","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($dateposted,$description,$debit,$credit,$balance);
                            while($stmt->fetch()){
                                $resultset[]=array($dateposted,$description,number_format($debit,2),number_format($credit,2),number_format($balance,2));
                                
                            }
                            $resultfooter=array("Total","","","","");
                        }
                        break;
                    case "allcharges":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="List of Charges";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT SQL_CALC_FOUND_ROWS a.dateposted AS dateposted, a.description AS description, formatName(b.lastname,b.firstname,b.middlename) AS owner, c.code, a.amount AS amount, COALESCE(SUM(d.amountpaid*e.active),0) AS amountpaid, (a.amount-coalesce(SUM(d.amountpaid*e.active),0)) AS balance
			FROM charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1 INNER JOIN homeowner b ON b.id=a.homeowner INNER JOIN lot c ON a.lot=c.id
                        WHERE a.active=1 AND a.dateposted>=? AND a.dateposted<=?
                        GROUP BY a.id
                        ORDER BY dateposted DESC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Date","Description","Homeowner","Lot Code","Debit","Credit","Balance"];
                        $resultclasses = ["","","","","textamount total","textamount total","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($dateposted,$description,$owner,$lcode,$debit,$credit,$balance);
                            $totalbal=0;
                            $totalc=0;
                            $totald=0;
                            while($stmt->fetch()){
                                $totalbal += $balance;
                                $totalc += $credit;
                                $totald += $debit;
                                $resultset[]=array($dateposted,$description,$owner,$lcode,number_format($debit,2),number_format($credit,2),number_format($balance,2));
                                
                            }
                            $resultfooter=array("Total","","","",number_format($totald,2),number_format($totalc,2),number_format($totalbal,2));
                        }
                        break;
                    case "allchargescancel":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Cancelled Charges";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT SQL_CALC_FOUND_ROWS a.dateposted AS dateposted, a.description AS description, formatName(b.lastname,b.firstname,b.middlename) AS owner, c.code, a.amount AS amount, COALESCE(SUM(d.amountpaid*e.active),0) AS amountpaid, (a.amount-coalesce(SUM(d.amountpaid*e.active),0)) AS balance, a.cancelremarks,g.fullname
			FROM charges a LEFT JOIN ledgeritem d ON d.chargeid=a.id LEFT JOIN ledger e ON e.id=d.ledgerid AND e.active=1 INNER JOIN homeowner b ON b.id=a.homeowner INNER JOIN lot c ON a.lot=c.id INNER JOIN user g ON a.canceluser=g.id
                        WHERE a.active=0 AND a.dateposted>=? AND a.dateposted<=?
                        GROUP BY a.id
                        ORDER BY dateposted DESC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Date","Description","Homeowner","Lot Code","Debit","Credit","Balance","Cancellation Remarks","Cancelled By"];
                        $resultclasses = ["","","","","textamount total","textamount total","textamount total","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($dateposted,$description,$owner,$lcode,$debit,$credit,$balance,$cancelremarks,$canceluser);
                            while($stmt->fetch()){
                                $resultset[]=array($dateposted,$description,$owner,$lcode,number_format($debit,2),number_format($credit,2),number_format($balance,2),$cancelremarks,$canceluser);
                                
                            }
                            $resultfooter=array("Total","","","","","","","","");
                        }
                        break;
                    case "allpayments":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Payments List";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT b.transactiondate,b.ornumber,c.description,b.payee,d.code,b.paymentmode,c.amount,a.amountpaid FROM ledgeritem a INNER JOIN ledger b ON a.ledgerid=b.id INNER JOIN charges c ON a.chargeid=c.id INNER JOIN lot d ON c.lot=d.id WHERE b.active=1 AND a.transactiondate>=? AND a.transactiondate<=? GROUP BY a.id ORDER BY b.transactiondate ASC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Payment Date","OR Number","Description","Paid By","Lot","Mode of Payment","Amount","Amount Paid"];
                        $resultclasses = ["","","","","","","textamount","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,$amount,$amountpaid);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,number_format($amount,2),number_format($amountpaid,2));
                                
                            }
                            $resultfooter=array("Total","","","","","","","");
                        }
                        break;
                    case "allpaymentscancel":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Cancelled Payments";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT b.transactiondate,b.ornumber,c.description,b.payee,d.code,b.paymentmode,c.amount,a.amountpaid,b.cancelremarks,g.fullname FROM ledgeritem a INNER JOIN ledger b ON a.ledgerid=b.id INNER JOIN charges c ON a.chargeid=c.id INNER JOIN lot d ON c.lot=d.id INNER JOIN user g ON b.canceluser=g.id WHERE b.active=0 AND a.transactiondate>=? AND a.transactiondate<=? GROUP BY a.id ORDER BY b.transactiondate ASC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Payment Date","OR Number","Description","Paid By","Lot","Mode of Payment","Amount","Amount Paid","Cancellation Remarks","Cancelled By"];
                        $resultclasses = ["","","","","","","textamount","textamount total","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,$amount,$amountpaid,$cancelremarks,$canceluser);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,number_format($amount,2),number_format($amountpaid,2),$cancelremarks,$canceluser);
                                
                            }
                            $resultfooter=array("Total","","","","","","","","","");
                        }
                        break;
                    case "allpaymentscash":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Cash Payments";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT b.transactiondate,b.ornumber,c.description,b.payee,d.code,b.paymentmode,c.amount,a.amountpaid FROM ledgeritem a INNER JOIN ledger b ON a.ledgerid=b.id INNER JOIN charges c ON a.chargeid=c.id INNER JOIN lot d ON c.lot=d.id WHERE b.active=1 AND a.transactiondate>=? AND a.transactiondate<=? AND b.paymentmode="Cash" GROUP BY a.id ORDER BY b.transactiondate ASC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Payment Date","OR Number","Description","Paid By","Lot","Mode of Payment","Amount","Amount Paid"];
                        $resultclasses = ["","","","","","","textamount","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,$amount,$amountpaid);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,number_format($amount,2),number_format($amountpaid,2));
                                
                            }
                            $resultfooter=array("Total","","","","","","","");
                        }
                        break;
                    case "allpaymentscheck":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Check Payments";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT b.transactiondate,b.ornumber,c.description,b.payee,d.code,b.paymentmode,b.checkno,c.amount,a.amountpaid FROM ledgeritem a INNER JOIN ledger b ON a.ledgerid=b.id INNER JOIN charges c ON a.chargeid=c.id INNER JOIN lot d ON c.lot=d.id WHERE b.active=1 AND a.transactiondate>=? AND a.transactiondate<=? AND b.paymentmode="Check" GROUP BY a.id ORDER BY b.transactiondate ASC');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Payment Date","OR Number","Description","Paid By","Lot","Mode of Payment","Check No.","Amount","Amount Paid"];
                        $resultclasses = ["","","","","","","","textamount","textamount total"];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,$checkno,$amount,$amountpaid);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$description,$payee,$lotcode,$paymentmode,$checkno,number_format($amount,2),number_format($amountpaid,2));
                                
                            }
                            $resultfooter=array("Total","","","","","","","","");
                        }
                        break;
                    case "allreceipts":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Official Receipts Issued";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare('SELECT a.transactiondate,a.ornumber,a.paymentmode,a.checkno,a.payee,c.fullname,SUM(b.amountpaid),a.remarks FROM ledger a LEFT JOIN ledgeritem b ON b.ledgerid=a.id INNER JOIN user c ON a.user=c.id WHERE a.active=1 AND a.transactiondate>=? AND a.transactiondate<=? GROUP BY a.id');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Date","OR Number","Mode of Payment","Check No.","Paid By","Received By","Amount","Remarks"];
                        $resultclasses = ["","","","","","","textamount total",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$paymentmode,$checkno,$payee,$user,$amount,$remarks);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$paymentmode,$checkno,$payee,$user,number_format($amount,2),$remarks);
                                
                            }
                            $resultfooter=array("Total","","","","","","","");
                        }
                        break;
                    case "alltransactions":
                        $startdate=filter_input(INPUT_POST, "startdate");
                        $enddate=filter_input(INPUT_POST, "enddate");
                        
                        $title="Subdivision Transactions";
                        $msg="From ".date("M-d-Y", strtotime($startdate))." to ".date("M-d-Y", strtotime($enddate));
                        $subtitle=$msg;
                        $stmt=$conn->prepare("SELECT transactiondate, ornumber,`paymentmode`, description, IF(`type`<0,`amount`,0), IF(`type`>=0,`amount`,0), remarks FROM cashflows WHERE active=1 AND transactiondate>=? AND transactiondate<=? ORDER BY transactiondate DESC");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $stmt->bind_param("ss",$startdate,$enddate);
                        $resultcolumns = ["Date","OR Number","Mode of Payment","Description","Debit","Credit","Remarks"];
                        $resultclasses = ["","","","","textamount total","textamount total",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($transactiondate,$ornumber,$paymentmode,$description,$debit,$credit,$remarks);
                            while($stmt->fetch()){
                                $resultset[]=array($transactiondate,$ornumber,$paymentmode,$description,number_format($debit,2),number_format($credit,2),$remarks);
                                
                            }
                            $resultfooter=array("Total","","","","","","");
                        }
                        break;
                    case "userlist":
                        $title="User List";
                        $msg="";
                        $stmt=$conn->prepare('SELECT username,fullname,datereg FROM user WHERE active=1');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Username","Full Name","Date Registered"];
                        $resultclasses = ["","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($username,$fullname,$datereg);
                            while($stmt->fetch()){
                                $resultset[]=array($username,$fullname,$datereg);
                                
                            }
                            $resultfooter=array("","","");
                        }
                        break;
                    case "userlistinactive":
                        $title="Inactive User List";
                        $msg="";
                        $stmt=$conn->prepare('SELECT username,fullname,datereg FROM user WHERE active=0');
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Username","Full Name","Date Registered"];
                        $resultclasses = ["","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($username,$fullname,$datereg);
                            while($stmt->fetch()){
                                $resultset[]=array($username,$fullname,$datereg);
                                
                            }
                            $resultfooter=array("","","");
                        }
                        break;
                    case "residentlist":
                        $title="Resident List";
                        $msg="";
                        $stmt=$conn->prepare("SELECT a.fullname,a.gender,b.description,IF(b.ischild=1,'Yes','No') AS minor,c.code,formatAddress(c.housenumber,c.lot,c.block,c.street,c.phase) AS address FROM resident a INNER JOIN status b ON a.status=b.id INNER JOIN lot c ON a.household=c.id");
                        if($stmt === false) {
                            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
                        }
                        $resultcolumns = ["Name","Gender","Description","Minor","Lot Code","Address"];
                        $resultclasses = ["","","","","",""];
                        $stmt->execute();

                        $stmt->store_result();
                        if($stmt->num_rows>0)
                        {
                            $stmt->bind_result($fullname,$gender,$description,$minor,$code,$address);
                            while($stmt->fetch()){
                                $resultset[]=array($fullname,$gender,$description,$minor,$code,$address);
                                
                            }
                            $resultfooter=array("","","","","","");
                        }
                        break;
                }


                $stmt->close();
                dbClose();
                displayPlainHTMLHeader($title)?>
                        <script type="text/javascript">
                            $(document).ready(function() {
                                rptbl=$('#tblreport').dataTable({
                                    paging:false,
                                    "footerCallback": function ( row, data, start, end, display ) {
                                        var api = this.api(), data;

                                        // Remove the formatting to get integer data for summation
                                        var intVal = function ( i ) {
                                            return typeof i === 'string' ?
                                                i.replace(/[\$,]/g, '')*1 :
                                                typeof i === 'number' ?
                                                    i : 0;
                                        };

                                        // Total over all pages
//                                        data = api.column( 4 ).data();
//                                        total = data.length ?
//                                            data.reduce( function (a, b) {
//                                                    return intVal(a) + intVal(b);
//                                            } ) :
//                                            0;

                                        // Total over this page
                                        for(i=0; i<this.fnSettings().aoColumns.length; i++)
                                        {
                                            data = api.column( i, { page: 'current'} ).data();
                                            pageTotal = data.length?(data.length===1?intVal(data[0]):data.reduce(function(a, b){return intVal(a)+intVal(b);})):0;
                                        
                                            // Update footer
//                                            if($(api.column(i).footer().className)==="textamount"){
                                                try{
                                                    $(api.column(i).footer()).filter(".total").html(numberWithCommas(pageTotal.toFixed(2)));
                                                }catch(e){}
//                                            }
                                        }

                                        
                                    }
                                });
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

                        <div class="soapage">
                            <div id="pagetitle">
                          <?php displayPrintHeader(); ?>
                          <h3><?php echo $title; ?></h3>
                          <div class="sub-title"><?php echo $subtitle; ?></div>
                            </div>
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
                          <footer><div class="gentimestamp">Generated on <?php date_default_timezone_set("Asia/Manila"); echo date('Y-m-d h:i:s A', time());?></div></footer>
                        </div>
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