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
define('DT_DB_NAME', "documenttracker");
define('DT_LOG_NAME',"DocumentTracker");
define('DT_PAGE_TITLE',"Document Tracker");
define('DT_PERMISSION_COUNT', 6);
define('DT_PERM_ADDDOC',0);
define('DT_PERM_EDITDOC',1);
define('DT_PERM_RECEIVEDOC',2);
define('DT_PERM_EDITDOCTRACK',3);
define('DT_PERM_USERMGMNT',4);
define('DT_PERM_AUDITLOG',5);

function displayUserInfo()
{?>
    <section class="" data-role="panel" id="userpanel" data-position="right" data-position-fixed="true" data-display="overlay"><?php
    if(isLoggedIn())
    {?>
        <header><h1><?php echo $_SESSION['fullname']; ?></h1></header>
        <article>
          <table id="tbluserinfo" class="ui-body ui-responsive" data-role="table" data-mode="reflow">
            <thead>
              <tr>
                <th></th>
                <th><?php print_r($_SESSION['permlist']); ?></th>
              </tr>
            </thead>
            <tbody>
            <tr>
              <td>ID Number</td>
              <td><?php echo $_SESSION['uid']; ?></td>
            </tr>
            <tr>
              <td>Department</td>
              <td><?php echo $_SESSION['department']; ?></td>
            </tr>
            <tr>
              <td>Section</td>
              <td><?php echo $_SESSION['section']; ?></td>
            </tr>
            </tbody>
          </table>
          <a href="./logout" data-role="button" data-icon="power" data-iconpos="left" data-ajax="false">Logout</a>
        </article><?php
    }
    else
    {?>
        <header><h1>Login</h1></header>
        <article>
          <form action="./login" method="post" data-ajax="false">
              <label for="uid">ID Number</label>
              <input type="text" name="uid" id="uid"/>

              <label for="password">Password</label>
              <input type="password" name="password" id="password"/>

              <input type="hidden" name="lasturl" value="<?php echo urlencode(curPageURL()); ?>"/>
              <input type="submit" value="Login" data-icon="forward"/>

          </form>
        </article><?php
    }?>
    </section><?php
}

function displayHTMLPageHeader($pagetitle=DT_PAGE_TITLE)
{?>
    <!DOCTYPE html>
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo $pagetitle; ?></title>
        <link rel="stylesheet" href="./css/jquery.mobile.structure-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile.theme-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile.external-png-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile.icons-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile.inline-png-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/jquery.mobile.inline-svg-1.4.2.min.css" />
        <link rel="stylesheet" href="./css/default.css" />
        <script src="./js/jquery-2.1.1.min.js"></script>
        <script src="./js/jquery.mobile-1.4.2.min.js"></script>
        <script src="./js/default.js"></script>
      </head>
      <body>
        <div data-role="page">
        <header data-role="header">
          <h1>Document Tracker</h1>
          <a href="./" data-icon="home" data-iconpos="notext" class="ui-btn-left">Home</a>
          <a href="#userpanel" data-icon="user" data-iconpos="notext" class="ui-btn-right">Account</a>
        <?php
        if(isLoggedIn()):
        ?>
          <div data-role="navbar">
              <ul>
                  <?php if(checkPermission(DT_PERM_ADDDOC)): ?><li><a href="./add" data-icon="plus">Add Document</a></li><?php endif;?>
                  <?php if(checkPermission(DT_PERM_USERMGMNT)): ?><li><a href="./users" data-icon="edit">User Management</a></li><?php endif;?>
                  <?php if(checkPermission(DT_PERM_AUDITLOG)): ?><li><a href="./" data-icon="eye">Audit Log</a></li><?php endif;?>
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
            <form action="./" method="get">
                <div data-role="controlgroup" data-type="horizontal" id="searchform">
                  <label for="q" class="ui-hidden-accessible">Search for Tracking Number</label>
                  <input type="search" name="q" id="q" placeholder="Enter Tracking Number" autofocus="true" data-wrapper-class="controlgroup-textinput ui-btn" value="<?php echo (isset($_GET['q'])?$_GET['q']:""); ?>"/>
                        <input type="submit" data-icon="search" value="Search" data-iconpos="notext"/>
                    </div>
            </form>
        <?php
        displaySearchResult();
}

function displayHTMLPageFooter(){
    ?>
        </div>
        <footer data-role="footer" data-position="fixed">
          <!--<h1>Quezon Document Tracker</h1>&COPY;2014 Developed by The Aitenshi Project-->
        </footer>
        <?php displayUserInfo(); ?>
        </div>
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
    if(isset($_COOKIE['notifmsg']) && isset($_COOKIE['notiftype']))
    {
        ?>
        <ul data-role="listview" data-inset="true" id="notif" class="notification">
        <li data-iconpos="left" data-icon="<?php switch($_COOKIE['notiftype']){case DT_NOTIF_NORMAL:echo "info"; break; case DT_NOTIF_WARNING:echo "alert"; break; case DT_NOTIF_ERROR: echo "delete"; break;} ?>" class="notif<?php echo $_COOKIE['notiftype']; ?>"><a href="#" class=""><?php echo $_COOKIE['notifmsg']; ?></a></li>
        </ul>
        <?php
        setcookie("notifmsg",null,time()-3600);
        setcookie("notiftype",null,time()-3600);
    }
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

function checkPermission($p)
{
    return (isset($_SESSION['permlist'])?(($_SESSION['permlist'][$p]=="1")?true:false):false);
}
?>
