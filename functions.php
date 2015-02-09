<?php
//Define Conn Properties
$conn;
$systempage;
define('DT_NOTIF_NORMAL', 0);
define('DT_NOTIF_WARNING', 1);
define('DT_NOTIF_ERROR', 2);
define('DT_DB_SERVER', 'localhost');
define('DT_DB_USER', "root");
define('DT_DB_PASSWORD', "P@ssw00rd");
define('DT_DB_NAME', "homeowner");
define('DT_LOG_NAME',"Homeowner");
define('DT_PAGE_TITLE',"Homeowner System");
define('DT_PERMISSION_COUNT', 25);

define('DT_PERM_LOGIN',0); //1
define('DT_PERM_REPORTS_VIEW',1); //2
define('DT_PERM_LOT_VIEW',2); //4
define('DT_PERM_HOMEOWNER_VIEW',3); //8
define('DT_PERM_PAYMENT_VIEW',4); //16
define('DT_PERM_USER_VIEW',5); //32
define('DT_PERM_LOT_ADD',6); //64
define('DT_PERM_LOT_DELETE',7); //128
define('DT_PERM_LOT_UPDATE',8); //256
define('DT_PERM_PAYMENT_ADD',9); //512
define('DT_PERM_PAYMENT_DELETE',10); //1024
define('DT_PERM_CHARGE_VIEW',11); //2048
define('DT_PERM_CHARGE_ADD',12); //4096
define('DT_PERM_CHARGE_DELETE',13); //8192
define('DT_PERM_HOMEOWNER_ADD',14); //16384
define('DT_PERM_HOMEOWNER_UPDATE',15); //32768
define('DT_PERM_HOMEOWNER_DELETE',16); //65536
define('DT_PERM_CASHFLOW_VIEW',17); //131072
define('DT_PERM_CASHFLOW_ADD',18); //262144
define('DT_PERM_CASHFLOW_DELETE',19); //524288
define('DT_PERM_USER_ADD',20); //1048576
define('DT_PERM_USER_UPDATE',21); //2097152
define('DT_PERM_USER_DELETE',22); //4194304
define('DT_PERM_SETTINGS_VIEW',23); //8388608
define('DT_PERM_SETTINGS_UPDATE',24); //16777216

define('DT_SETTINGS_ID',1);

function displayControlPanel()
{?>
    <section class="" data-role="panel" id="userpanel" data-position="right" data-position-fixed="true" data-display="overlay" data-theme="b"><?php
    if(isLoggedIn())
    {?>
        <header><h1><?php echo DT_PAGE_TITLE; ?></h1></header>
        <article>
            <div data-role="collapsibleset" data-inset="false" data-collapsed-icon="user" data-expanded-icon="user">
                <fieldset data-role="collapsible">
                    <legend>User Info</legend>
                    <ul data-role="listview" data-inset="false">
                        <li><span class="infoheader">Name</span><?php echo $_SESSION['fullname']; ?></li>
                        <li><span class="infoheader">Username</span><?php echo $_SESSION['username']; ?></li>
                        <li data-theme="a" data-icon="edit""><a href="adduserform?id=<?php echo $_SESSION["uid"]; ?>">Edit Account</a></li>
                    </ul>
                </fieldset>
                <?php if(checkPermission(DT_PERM_SETTINGS_VIEW)): ?>
                <fieldset data-role="collapsible" data-collapsed-icon="gear" data-expanded-icon="gear">
                    <legend>Settings</legend>
                    <form method="post" action="./savesettings" data-ajax="false">
                        
                        <label for="s_assocname">Homeowners Association Name</label>
                        <input type="text" name="assocname" id="s_assocname" value="<?php echo $_SESSION['settings']['assocname']; ?>"/>
                        
                        <label for="s_acronym">Acronym</label>
                        <input type="text" name="acronym" id="s_acronym" value="<?php echo $_SESSION['settings']['acronym']; ?>"/>
                    
                        <label for="s_subdname">Subdivision</label>
                        <input type="text" name="subdname" id="s_subdname" value="<?php echo $_SESSION['settings']['subdname']; ?>"/>

                        <label for="s_brgy">Barangay</label>
                        <input type="text" name="brgy" id="s_brgy" value="<?php echo $_SESSION['settings']['brgy']; ?>"/>

                        <label for="s_city">City</label>
                        <input type="text" name="city" id="s_city" value="<?php echo $_SESSION['settings']['city']; ?>"/>

                        <label for="s_province">Province</label>
                        <input type="text" name="province" id="s_province" value="<?php echo $_SESSION['settings']['province']; ?>"/>

                        <label for="s_zipcode">Zip Code</label>
                        <input type="text" name="zipcode" id="s_zipcode" value="<?php echo $_SESSION['settings']['zipcode']; ?>"/>

                        <label for="s_contactno">Contact Number</label>
                        <input type="tel" name="contactno" id="s_contactno" value="<?php echo $_SESSION['settings']['contactno']; ?>"/>

                        <label for="s_email">Email Address</label>
                        <input type="email" name="email" id="s_email" value="<?php echo $_SESSION['settings']['email']; ?>"/>

                        <label for="s_price">Price/Sq. Meter</label>
                        <input type="number" step="0.01" name="price" id="s_price" value="<?php echo $_SESSION['settings']['price']; ?>"/>

                        <label for="s_interest">Monthly Interest</label>
                        <input type="number" step="0.001" name="interest" id="s_interest" value="<?php echo $_SESSION['settings']['interest']; ?>"/>

<!--                        <label for="s_intgraceperiod">Interest Grace Period (Months)</label>
                        <input type="number" name="intgraceperiod" id="s_intgraceperiod" value="<?php echo $_SESSION['settings']['intgraceperiod']; ?>"/>-->

                        <input type="hidden" name="id" value="<?php echo DT_SETTINGS_ID; ?>"/>
                        <input type="submit" value="Save Settings" data-role="button" data-icon="check" data-mini="true" <?php if(!checkPermission(DT_PERM_SETTINGS_UPDATE)): ?>disabled="disabled"<?php endif; ?>/>
                    </form>
                </fieldset>
                <?php endif; ?>
            </div>
          <a href="./logout" data-role="button" data-icon="power" data-iconpos="left" data-ajax="false" data-theme="e">Logout</a>
        </article><?php
    }
    else
    {?>
        <header><h1>Login</h1></header>
        <article>
          <form action="./login" method="post" data-ajax="false">
              <label for="uid">Username</label>
              <input type="text" name="uid" id="uid"/>

              <label for="password">Password</label>
              <input type="password" name="password" id="password"/>

              <input type="hidden" name="lasturl" value="<?php echo urlencode(curPageURL()); ?>"/>
              <input type="submit" value="Login" data-icon="forward"/>

          </form>
          <a href="#resetPassword" data-role="button" data-icon="lock" data-iconpos="left" data-inline="true" data-rel="popup" data-position-to="window" data-transition="pop" data-theme="e" data-mini="true" class="ui-mini" style="font-size:12.5px;">Reset Password</a>
          
        </article>
        <div data-role="popup" id="resetPassword" data-dismissible="false" data-overlay-theme="b" class="">
            <header data-role="header">
              <h1>Forgot Password?</h1>
              <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
            </header>
            <div role="main" class="ui-content">
                <form action="./resetpasswordform" method="post" data-ajax="false">
                    <div></div>
                    <div class="ui-body ui-body-a ui-corner-all ui-icon-info ui-btn-icon-left">Your password cannot be retrieved but can be<br/> changed.</div>
                    <label for="uusername">If you forgot or simply want to change your password,<br/> enter your username below.</label>
                    <input type="text" name="uusername" id="uusername"/>
                    <fieldset data-role="controlgroup" data-type="horizontal">
                        <input type="submit" value="Reset Password" data-theme="d"/>
                        <a href="#" data-rel="back" data-role="button">Cancel</a>
                    </fieldset>
                </form>
            </div>
        </div>
    <?php
    }?>
    </section><?php
}

function displayHTMLHead($pagetitle=DT_PAGE_TITLE)
{?>
    <!DOCTYPE html>
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $pagetitle; ?></title>
        <link rel="stylesheet" href="./css/jquery.mobile.structure-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile.theme-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile.external-png-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile.icons-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile.inline-png-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/jquery.mobile.inline-svg-1.4.3.min.css" media="screen" />
        <link rel="stylesheet" href="./css/staisabelgreen.min.css" />

<!--        <link rel="stylesheet" href="./plugin/jquery-ui-1.11.0.custom/jquery-ui.css" media="screen" />
        <link rel="stylesheet" href="./plugin/jquery-ui-1.11.0.custom/jquery-ui.min.css" media="screen" />
        <link rel="stylesheet" href="./plugin/jquery-ui-1.11.0.custom/jquery-ui.structure.min.css" media="screen" />-->
        
        <!--<link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables.min.css" />-->
        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/media/css/jquery.dataTables_themeroller.min.css" />
        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/integration/bootstrap/bin/bootstrap.css" />
        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/integration/bootstrap/bin/dataTables.bootstrap.css" />
        <link rel="stylesheet" href="./plugin/DataTables-1.10.0/extensions/TableTools/css/dataTables.tableTools.min.css" />
        <link rel="stylesheet" href="./plugin/jquery-mobile-datepicker-wrapper-master/jquery.mobile.datepicker.css" />
        <link rel="stylesheet" href="./plugin/jquery-mobile-datepicker-wrapper-master/jquery.mobile.datepicker.theme.css" />
        <!--<link rel="stylesheet" href="./plugin/jquery-mobile-datepicker-wrapper-master/theme-template.css" />-->
        
        <link rel="stylesheet" href="./css/default.css" />
        <link rel="icon" type="image/png" href="./images/favicon.png" />
        <script src="./js/jquery-2.1.1.min.js"></script>
        <script src="./js/overridejqm.js"></script>
        <script src="./js/jquery.mobile-1.4.3.min.js"></script>
        
        <script src="./plugin/jquery-mobile-datepicker-wrapper-master/external/jquery-ui/datepicker.js"></script>
        <!--<script src="./plugin/jquery-ui-1.11.0.custom/jquery-ui.min.js"></script>-->
        <script src="./plugin/jquery-mobile-datepicker-wrapper-master/jquery.mobile.datepicker.js"></script>
        
        <script src="./plugin/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>
        <script src="./plugin/DataTables-1.10.0/integration/bootstrap/bin/dataTables.bootstrap.js"></script>
        <script src="./plugin/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
        <script src="./js/default.js"></script>
      </head>
    <?php
}

function displayHTMLPageHeader($pagetitle=DT_PAGE_TITLE)
{ 
    displayHTMLHead($pagetitle); ?>
      <body>
        <div data-role="page">
        <header data-role="header">
          <h1><?php echo $pagetitle; ?></h1>
          <a href="./" data-icon="home" data-iconpos="notext" class="ui-btn-left" data-rel="back">Home</a>
          <a href="#userpanel" data-icon="user" data-iconpos="left" class="ui-btn-right">
              <?php echo (isLoggedIn()?$_SESSION['username']:"Log-in"); ?></a>
        <?php
        if(isLoggedIn()):
        ?>
          <div data-role="navbar">
              <ul>
                  <?php if(!checkPermission(DT_PERM_LOT_VIEW)&&!checkPermission(DT_PERM_HOMEOWNER_VIEW)&&!checkPermission(DT_PERM_CASHFLOW_VIEW)&&!checkPermission(DT_PERM_USER_VIEW)&&!checkPermission(DT_PERM_REPORTS_VIEW)): ?>
                  <li><a href="./" data-icon="home">Home</a></li>
                  <?php endif; ?>
                  <?php if(checkPermission(DT_PERM_LOT_VIEW)): ?><li><a href="./lots" data-icon="location">Lot Management</a></li><?php endif;?>
                  <?php if(checkPermission(DT_PERM_HOMEOWNER_VIEW)): ?><li><a href="./homeowners" data-icon="home">Homeowners</a></li><?php endif;?>
                  <?php if(checkPermission(DT_PERM_CASHFLOW_VIEW)): ?><li><a href="./cashflow" data-icon="recycle">Transactions</a></li><?php endif; ?>
                  <?php if(checkPermission(DT_PERM_USER_VIEW)): ?><li><a href="./users" data-icon="user">User Management</a></li><?php endif;?>
                  <?php if(checkPermission(DT_PERM_REPORTS_VIEW)): ?><li><a href="./reports" data-icon="bullets">Reports</a></li><?php endif;?>
              </ul>
          </div>
        <?php
        endif;
        ?>
        </header>
        <div role="main" class="ui-content">
        <?php
            displayNotification();
        ?>
<!--            <form action="./" method="get">
                <div data-role="controlgroup" data-type="horizontal" id="searchform">
                  <label for="q" class="ui-hidden-accessible">Search for Tracking Number</label>
                  <input type="search" name="q" id="q" placeholder="Enter Tracking Number" autofocus="true" data-wrapper-class="controlgroup-textinput ui-btn" value="<?php echo (isset($_GET['q'])?$_GET['q']:""); ?>"/>
                        <input type="submit" data-icon="search" value="Search" data-iconpos="notext"/>
                    </div>
            </form>-->
        <?php
        displaySearchResult();
}

function displayPlainHTMLHeader($title)
{
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
                        <link rel="icon" type="image/png" href="./images/favicon.png" />

                        <script src="./js/jquery-2.1.1.min.js"></script>
                        <script src="./plugin/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>
                        <script src="./plugin/DataTables-1.10.0/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
                        <script src="./js/default.js"></script>
                        
                        <script type="text/javascript">
                            $(document).ready(function() {
                                
                            } );
                        </script>
                      </head>
                      <body>
    <?php
}

function displayHTMLFooter()
{ ?>
    </html>      
    <?php
}

function displayHTMLPageFooter(){
    ?>
        </div>
        <footer data-role="footer" data-position="fixed">
          <!--<h1>Quezon Document Tracker</h1>&COPY;2014 Developed by The Aitenshi Project-->
          ©2014 Homeowner System &CenterDot; The Aitenshi Project
        </footer>
        <?php displayControlPanel(); ?>
        </div>
      </body>    
    <?php
    displayHTMLFooter();
}

function displayPlainHTMLFooter(){
    ?>
        </body>
        </html>
    <?php
}

function dbConnect(){
  global $conn;
  $conn=new mysqli(DT_DB_SERVER, DT_DB_USER, DT_DB_PASSWORD, DT_DB_NAME);
  if($conn->connect_error)
  {
    trigger_error("<p><strong>Database connection failed<strong></p>".$conn->connect_error, E_USER_ERROR);
  }
}

function dbClose()
{
  global $conn;
  $conn->close();
}

function curPageURL() {
  $pageURL = "//";
  if ($_SERVER["SERVER_PORT"] != "80") {
    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } else {
    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
  return $pageURL;
}

function setNotification($msg, $type=DT_NOTIF_NORMAL)
{
  setcookie("notifmsg", $msg);
  setcookie("notiftype",$type);
}

function displayNotification()
{
    if(!is_null(filter_input(INPUT_COOKIE, "notifmsg")) && !is_null(filter_input(INPUT_COOKIE, "notiftype")))
    {
        $notif_msg=filter_input(INPUT_COOKIE, "notifmsg");
        $notif_type=filter_input(INPUT_COOKIE, "notiftype");
        setcookie("notifmsg",null,time()-3600);
        setcookie("notiftype",null,time()-3600);
        printNotification($notif_msg, $notif_type);
    }
}

function printNotification($notif_msg,$notif_type=DT_NOTIF_NORMAL)
{
    ?>
        <ul data-role="listview" data-inset="true" id="notif" class="notification">
            <li data-iconpos="left" data-icon="<?php switch($notif_type){case DT_NOTIF_NORMAL:echo "info"; break; case DT_NOTIF_WARNING:echo "alert"; break; case DT_NOTIF_ERROR: echo "delete"; break;} ?>" class="notif<?php echo $notif_type; ?>"><a href="#" class=""><?php echo $notif_msg; ?></a></li>
        </ul>
    <?php
}

function writeLog($msg, $type="Info")
{
  global $conn;
  $stmt=$conn->prepare("INSERT INTO auditlog(type,user,page,msg) VALUES(?,?,?,?)");
  if($stmt === false) {
    trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
  }
  $userid=(isLoggedIn()?$_SESSION['uid']:0);
  $page=(isset($_GET['page'])?$_GET['page']:"dashboard");
  $stmt->bind_param('siss',$type,$userid,$page,$msg);
  $stmt->execute();
}

function displaySearchResult()
{
  if((isset($_GET['q'])) && ($_GET['q']!='')):
    if(isset($_GET['q']))
    {
      global $conn;
      dbConnect();
      $stmt=$conn->prepare("SELECT a.*, b.uid, b.fullname, b.department, b.section FROM document a INNER JOIN user b ON a.author=b.uid WHERE trackingnumber=?");
      if($stmt === false) {
        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
      }
      $stmt->bind_param('i',$_GET['q']);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($r_trackingnumber, $r_documentnumber, $r_remarks, $r_datecreated, $r_author, $r_uid, $r_fullname, $r_department, $r_section);

      if($stmt->num_rows <= 0)
      {
        ?>
          <h1>Nothing found</h1>
          <p>Please verify that you have the correct tracking number and try again.</p>
        <?php
      }
      else
      {
        while($stmt->fetch()){
          ?>
          <div data-role="collapsible" data-collapsed="false">
              <h4>Tracking #: <?php printf("%08d",$r_trackingnumber); ?></h4>
              <table class="documenttableinfo">
                <thead>
                  <tr>
                    <th></th>
                    <th><?php print_r($_SESSION['permission']); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Document Number</td>
                    <td><?php echo $r_documentnumber; ?></td>
                  </tr>
                  <tr>
                    <td>Remarks</td>
                    <td><?php echo $r_remarks; ?></td>
                  </tr>
                  <tr>
                    <td>Date received</td>
                    <td><?php echo $r_datecreated; ?></td>
                  </tr>
                  <tr>
                    <td>Staff</td>
                    <td><?php echo $r_author; ?></td>
                  </tr>
                  <tr>
                    <td>Department</td>
                    <td><?php echo $r_department." (".$r_section.")"; ?></td>
                  </tr>
                </tbody>
              </table>
              <?php
              if(isLoggedIn() && checkPermission(DT_PERM_RECEIVEDOC)):
              ?>
                <a href="#receiveDialog<?php echo $r_trackingnumber; ?>" data-role="button" data-inline="true" data-icon="arrow-d" data-rel="popup" data-position-to="window" data-transition="pop">Receive Document</a>
                <div data-role="popup" id="receiveDialog<?php echo $r_trackingnumber; ?>" data-dismissible="false" data-overlay-theme="b">
                  <header data-role="header">
                    <h1>Receive Document</h1>
                    <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
                  </header>
                  <div role="main" class="ui-content">
                    <h3>Tracking #: <?php printf("%08d",$r_trackingnumber); ?></h3>
                    <form action="./receive" method="post" data-ajax="false">
                      <label for="txtremarks" class="ui-hidden-accessible">Remarks</label>
                      <textarea name="txtremarks" id="txtremarks" placeholder="Remarks"></textarea>
                      <input type="hidden" name="trackingnumber" value="<?php printf("%08d",$r_trackingnumber); ?>"/>
                      <input type="submit" value="Receive" data-icon="arrow-d"/>
                    </form>
                  </div>
                </div>
              <?php
              endif;
              ?>
          </div>
        <?php
          global $conn;
          dbConnect();
          $stmt2=$conn->prepare("SELECT a.*, b.uid, b.fullname, b.department, b.section FROM documentlog a INNER JOIN user b ON a.user=b.uid WHERE trackingnumber=?");
          if($stmt2 === false) {
            trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
          }
          $stmt2->bind_param('i',$_GET['q']);
          $stmt2->execute();
          $stmt2->store_result();
          $stmt2->bind_result($r2_logid,$r2_trackingnumber,$r2_ts,$r2_remarks,$r2_user,$r2_uid,$r2_fullname,$r2_department,$r2_section);
        ?>
        <h1>Document Log</h1>
        <table data-role='table' class='ui-responsive table-stripe ui-body-a ui-shadow'>
          <thead>
            <tr>
              <th>Date</th>
              <th>Remarks</th>
              <th>Staff</th>
              <th>Department</th>
            </tr>
          </thead>
          <tbody>
          <?php
            while($stmt2->fetch())
            {
              ?>
                <tr>
                  <td><?php echo $r2_ts;?></td>
                  <td><?php echo $r2_remarks;?></td>
                  <td><?php echo $r2_fullname." (".$r2_uid.")";?></td>
                  <td><?php echo $r2_department." (".$r2_section.")";?></td>
                </tr>
              <?php
            }
          ?>
          </tbody>
        </table>
          <?php
          $stmt2->close();
        }
      }
      $stmt->close();
    }
  endif;
}

function isLoggedIn()
{
    return (isset($_SESSION['uid'])?true:false);
}

function parsePermission($p){
    return str_split(strrev(str_pad(decbin($p), DT_PERMISSION_COUNT, "0", STR_PAD_LEFT)));
}

function checkPermission($p,$pl=NULL)
{
    if(is_null($pl)){
        $pl=$_SESSION['permlist'];
    }
    return (isset($pl)?(($pl[$p]=="1")?true:false):false);
}

function displayHomeownerForm($action='./addhomeowner',$lastname='',$firstname='',$middlename='',$contactnumber='',$email='',$uid='', $bond='0',$bonddesc='',$gatepass='0')
{?>
    <div data-role="popup" id="addHomeowner" data-dismissible="false" data-overlay-theme="b">
        <header data-role="header">
          <h1>Homeowner Form</h1>
          <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
        </header>
        <div role="main" class="ui-content">
            <form action="<?php echo $action; ?>" method="post" data-ajax="false">
                <label for="plastname">Last Name</label>
                <input id="plastname" name="plastname" type="text" required="true" value="<?php echo $lastname; ?>"/>
                <label for="pfirstname">First Name</label>
                <input id="pfirstname" name="pfirstname" type="text" required="true" value="<?php echo $firstname; ?>"/>
                <label for="pmiddlename">Middle Name</label>
                <input id="pmiddlename" name="pmiddlename" type="text" required="true" value="<?php echo $middlename; ?>"/>
                <label for="pcontactno">Contact Number</label>
                <input id="pcontactno" name="pcontactno" type="tel" value="<?php echo $contactnumber; ?>"/>
                <label for="pemail">Email Address</label>
                <input id="pemail" name="pemail" type="email" value="<?php echo $email; ?>"/>
                <label for="pbond">Bond</label>
                <input id="pbond" name="pbond" type="number" value="<?php echo $bond; ?>"/>
                <label for="pbonddesc">Bond Description</label>
                <textarea id="pbonddesc" name="pbonddesc"><?php echo $bonddesc; ?></textarea>
<!--                <label for="pgatepass">Gate Pass Sticker</label>
                <input id="pgatepass" name="pgatepass" type="checkbox" value="1" <?php echo ($gatepass>0?"checked='true'":""); ?> />-->
                <input type="hidden" name="pgatepass" value="1"/>
                <input type="hidden" name="uid" value="<?php echo $uid; ?>"/>
                <input type="submit" value="Submit" data-icon="check"/>
            </form>
        </div>
     </div>
<?php
}
       
function displayLotForm($action='./addlot',$code='',$lotsize='',$housenumber='',$street='',$lot='',$block='',$phase='',$lotid=''){
    ?>
    <div data-role="popup" id="addLotForm" data-dismissible="false" data-overlay-theme="b">
        <header data-role="header">
          <h1>Lot Information</h1>
          <a href="#" data-rel="back" class="ui-btn ui-corner-all ui-shadow ui-btn-a ui-icon-delete ui-btn-icon-notext ui-btn-right">Close</a>
        </header>
        <div role="main" class="ui-content">
          <form action="<?php echo $action; ?>" method="post" data-ajax="false">
              <label for="code">Lot Code</label>
              <input type="text" id="code" name="code" value="<?php echo $code; ?>" required="true"/>
              <!--<label for="dateacquired">Date Acquired</label>-->
              <!--<input type="date" id="dateacquired" name="dateacquired" data-role="date"  value="<?php echo $dateacquired; ?>"/>-->
              <label for="lotsize">Lot Size (sq. m)</label>
              <input type="number" id="lotsize" name="lotsize" step="0.1" value="<?php echo $lotsize; ?>"/>
              <label for="housenumber">House Number</label>
              <input type="text" id="housenumber" name="housenumber" value="<?php echo $housenumber; ?>"/>
              <label for="street">Street</label>
              <input type="text" id="street" name="street" value="<?php echo $street; ?>"/>
              <label for="lot">Lot</label>
              <input type="text" id="lot" name="lot" value="<?php echo $lot; ?>"/>
              <label for="block">Block</label>
              <input type="text" id="block" name="block" value="<?php echo $block; ?>"/>
              <label for="phase">Phase</label>
              <input type="text" id="phase" name="phase" value="<?php echo $phase; ?>"/>
              <!--<label for="numberinhousehold">Number in Household</label>-->
              <!--<input type="number" id="numberinhousehold" name="numberinhousehold" value="<?php echo $numberinhousehold; ?>"/>-->
              <!--<input type="hidden" name="homeowner" id="homeowner" value="<?php echo $homeowner; ?>"/>-->
              <input type="hidden" name="lotid" id="lotid" value="<?php echo $lotid; ?>" required="true"/>

              <input type="submit" value="Submit" data-icon="arrow-d"/>
          </form>
        </div>
    </div>    
    <?php
}

function formatBill($id,$code,$address,$lotsize,$fullname,$dues,$balance)
{
    global $conn;
//    $stmt=$conn->prepare("SELECT a.id,a.dateposted,a.description,(SUM(a.amount)-SUM(a.amountpaid)) AS balance FROM charges a WHERE a.amount>a.amountpaid AND a.active=1 AND a.lot=? GROUP BY a.id");
    $stmt=$conn->prepare("SELECT a.id, a.dateposted, a.description, a.amount, SUM(COALESCE(c.amountpaid*b.active,0)) AS amtpaid FROM charges a LEFT JOIN ledgeritem c ON a.id=c.chargeid LEFT JOIN ledger b ON b.id=c.ledgerid WHERE a.lot=? AND a.active=1 GROUP BY a.id HAVING a.amount>SUM(COALESCE(c.amountpaid,0)) ORDER BY a.dateposted, a.description");
    if($stmt === false) {
        trigger_error('<strong>Error:</strong> '.$conn->error, E_USER_ERROR);
    }
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $stmt->store_result();
    ?>
    <div class="soapage">
        <?php displayPrintHeader(); ?>
        <h3 class="printtitle">Billing Statement</h3>
        <table class="printacctinfo">
            <tr>
                <th>Account Name</th>
                <td>:</td>
                <td><?php echo $fullname; ?></td>
            </tr>
            <tr>
                <th>Lot Code</th>
                <td>:</td>
                <td><?php echo $code; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td>:</td>
                <td><?php echo $address; ?></td>
            </tr>
            <tr>
                <th>Lot Size</th>
                <td>:</td>
                <td><?php echo $lotsize; ?> sq. m.</td>
            </tr>
            <tr>
                <th>Monthly Due Amount</th>
                <td>:</td>
                <td><?php echo $dues; ?></td>
            </tr>
        </table>

        <table class="tblcharges">
            <thead>
                <th>Date</th>
                <th>Description</th>
                <th>Balance</th>
            </thead>
            <tbody>
        <?php
        if($stmt->num_rows>0)
        {
            $stmt->bind_result($cid,$dateposted,$description,$camount,$camountpaid);
            $totalbal=0;
            while($stmt->fetch()){ 
                $totalbal += ($camount-$camountpaid); ?>
                <tr>
                    <td><?php echo $dateposted; ?></td>
                    <td><?php echo $description; ?></td>
                    <td class="textamount"><?php echo number_format(($camount-$camountpaid),2); ?></td>
                </tr>
            <?php }
            if(round($balance,2) < round($totalbal,2)){ ?>
                <tr>
                    <td>--</td>
                    <td><em>Less from Discounts &amp; Advanced Payments</em></td>
                    <td class="textamount"><?php echo number_format($balance-$totalbal,2); ?></td>
                </tr>
            <?php
            }
        }
        else
        {
            ?>
                <tr>
                    <td colspan="3" class="nocharges">No charges.</td>
                </tr>
            <?php
        }
        ?>
            </tbody>
            <tfoot>
                <th colspan="2" class="printtotalbal">Total</th>
                <th class="textamount" class="printbal"><?php echo number_format($balance,2); ?></th>
            </tfoot>
        </table>
        <footer>
            <div>Prepared by:</div>
            <div class="printpreparedby"><?php echo $_SESSION["fullname"]; ?></div>
            <div class="gentimestamp">Generated on <?php date_default_timezone_set("Asia/Manila"); echo date('Y-m-d h:i:s A', time());?></div>
        </footer>
    </div>
    <?php
    $stmt->close();
}

function displayPrintHeader()
{
    ?>
        <header class="printheader">
            <img src="images/staisabellogo.jpg" alt="Santa Isabel Logo" class="headerlogo"/>
            <div class="subdname"><?php echo $_SESSION["settings"]["assocname"]; ?></div>
            <div><?php echo $_SESSION["settings"]["subdname"],", ".$_SESSION["settings"]["brgy"].", ".$_SESSION["settings"]["city"].", ".$_SESSION["settings"]["province"]." ".$_SESSION["settings"]["zipcode"]; ?></div>
            <div><?php echo "<strong>Tel.:</strong> ".$_SESSION["settings"]["contactno"]." / <strong>Email:</strong> ".$_SESSION["settings"]["email"] ?></div>
        </header>
    <?php
}

function forceDownload($f,$size,$content)
{
	header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$f);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . $size);
	echo $content;
}
?>
