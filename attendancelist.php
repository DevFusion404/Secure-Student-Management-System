<?php require_once 'security.php'; 


include_once 'database.php';
if (!isset($_SESSION['user'])||$_SESSION['role']!='Teacher') {
  # code...
  header('Location:./logout.php');
  exit;
}

// CSRF: reject any POST without a valid token before any data is changed.
include_once 'csrf.php';
verifyCSRFOnPost();
?>
<?php


//include_once 'database.php';

?>


<!DOCTYPE html>

<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Attendance Report Report</title><link rel="icon" href="../img/favicon2.png">
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css">

  <link rel="stylesheet" href="bower_components/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="plugins/timepicker/bootstrap-timepicker.min.css">
  <!-- bootstrap datepicker -->
  <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="bower_components/select2/dist/css/select2.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-green sidebar-mini" data-active-menu="attendance">
<div class="wrapper">

  <!-- Main Header -->
 <?php include_once 'header.php'; ?>
  <!-- Left side column. contains the logo and sidebar -->
  <?php include_once 'sidebar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Attendance Report
        <small>Attendance Report Details</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Attendance Report</a></li>
        <li class="active">Details</li>
      </ol>
    </section>

    <!-- Main content -->


    <section class="content">

 <div class="row">
 <div class="col-xs-4">

   

         <div class="alert alert-success alert-dismissible" style="display: none;" id="truemsg">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i> Success!</h4>
                New Attendance Report Successfully added
              </div>





          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Session Details</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <form role="form" method="POST" >
              <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
              <div class="box-body">

                  <div class="form-group">
                  <label for="exampleInputPassword1">Attendance ID</label>
                  <!-- XSS: Escape request values before rendering them in form fields. -->
                  <input name="sid" type="text" class="form-control" id="exampleInputPassword1" disabled="disabled" value="<?php echo xssEscape($_GET['aid'] ?? ''); ?>">
                </div>

                  <div class="form-group">
                  <label for="exampleInputPassword1">Date</label>
                  <!-- XSS: Escape request values before rendering them in form fields. -->
                  <input name="sid" type="text" class="form-control" id="exampleInputPassword1" disabled="disabled" value="<?php echo xssEscape($_GET['date'] ?? ''); ?>">
                </div>


                  <div class="form-group">
                  <label for="exampleInputPassword1">Subject ID</label>
                  <!-- XSS: Escape request values before rendering them in form fields. -->
                  <input name="sid" type="text" class="form-control" id="exampleInputPassword1" disabled="disabled" value="<?php echo xssEscape($_GET['subject'] ?? ''); ?>">
                </div>


               

                <div class="form-group">
                  <label for="exampleInputPassword1">Start Time</label>
                  <!-- XSS: Escape request values before rendering them in form fields. -->
                  <input name="sid" type="text" class="form-control" id="exampleInputPassword1" disabled="disabled" value="<?php echo xssEscape($_GET['stime'] ?? ''); ?>">
                </div>


                 


               


       
              </div>
              <!-- /.box-body -->

<div class="box-footer">

                  <?php

                  $stmt = $conn->prepare("SELECT * FROM attendancereport WHERE aid = ?");
                  $stmt->bind_param("s", $_GET['aid']);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
            // XSS: URL-encode request values and escape the rendered link attribute.
            $viewUrl = 'attendancelist.php?' . http_build_query(array('view' => $_GET['aid'] ?? '', 'aid' => $_GET['aid'] ?? '', 'date' => $_GET['date'] ?? '', 'subject' => $_GET['subject'] ?? '', 'stime' => $_GET['stime'] ?? ''), '', '&', PHP_QUERY_RFC3986);
            // XSS: Escape dynamic values before rendering them in HTML.
            echo ' <a href="' . xssEscape($viewUrl) . '" class="btn btn-primary">View Attendance</a>';
              
                                  }else{
                                  // XSS: URL-encode request values and escape the rendered link attribute.
                                  $markUrl = 'attendancelist.php?' . http_build_query(array('mark' => $_GET['aid'] ?? '', 'class' => $_GET['class'] ?? '', 'aid' => $_GET['aid'] ?? '', 'date' => $_GET['date'] ?? '', 'subject' => $_GET['subject'] ?? '', 'stime' => $_GET['stime'] ?? ''), '', '&', PHP_QUERY_RFC3986);
                                  // XSS: Escape dynamic values before rendering them in HTML.
                                  echo '<a href="' . xssEscape($markUrl) . '" class="btn btn-primary">Mark Attendance</a>';
                                  }

                  ?>


              
               
                
              </div>
            </form>

              <?php

              if (isset($_POST['submit'])) {
             
                $sid = $_POST['schedule'];
               

               $date = date_format(new DateTime($_POST['date']),'Y-m-d');


                  try {

                    $stmt = $conn->prepare("INSERT INTO attendance Report (`date`, sid) VALUES (?, ?)");
                    $stmt->bind_param("ss", $date, $sid);

                  if ($stmt->execute()) {
                         echo "<span class='js-show-truemsg' hidden></span>";
                      } else {
                            }
                    
                  } catch (Exception $e) {
                    
                  }





                  
                # code...
                                            }

              ?>



          </div></div>

          <div class="col-xs-8">

            <?php if(isset($_GET['mark'])){ ?>




          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Students Attendance</h3>
            </div>
            
            <!-- /.box-header -->
            <div class="box-body"><form action="" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>Attendance</th>
                  
                  
                </tr>
                </thead>
                <tbody>


                  <?php

                  $stmt = $conn->prepare("SELECT * FROM student WHERE classroom = ?");
                  $stmt->bind_param("s", $_GET['class']);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    $x=0;
                   // output data of each row
                     while($row = $result->fetch_assoc()) {
                      // XSS: Escape dynamic database values before rendering them.
                      echo "<tr><td> " . xssEscape($row["sid"]). " </td><td> " . xssEscape($row["fname"])." " . xssEscape($row["lname"])." </td>
                      <td><div class='form-group'>
                 <input type='hidden' name='sid[]'' value='".xssEscape($row["sid"])."' />
                 <!-- XSS: Escape request values before rendering them in form fields. -->
                 <input type='hidden' name='aid[]'' value='".xssEscape($_GET["aid"] ?? '')."' />
                  <div class='radio '>
  <label style='width: 100px'><input type='radio' name='att[".$x."]' value='Present' checked> &nbsp&nbsp&nbspPresent</label>
  <label style='width: 100px'><input type='radio' name='att[".$x."]' value='Absent' checked> &nbsp&nbsp&nbspAbsent</label>

</div>
                 
                </div></td>


                      </tr>"; $x++;
                       }
                                  }

                  ?>


                </tbody>
                <tfoot>
                 
                </tfoot>
              </table>
            </div>
            <div class="box-footer">
                <button type="submit" name="submitatt" value="submit" class="btn btn-primary">Submit</button>
              </div>

            </form>
            <!-- /.box-body -->
          </div>


          <?php }elseif (isset($_GET['view'])) { ?>




                

          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Students Attendance</h3>
            </div>
            
            <!-- /.box-header -->
            <div class="box-body"><form action="" method="post">
              <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
              <table id="example1" class="table table-bordered table-striped">
                <thead>
                <tr>
                  <th>Student ID</th>
                  <th>Name</th>
                  <th>Attendance</th>
                  
                  
                </tr>
                </thead>
                <tbody>


                  <?php

                  $stmt = $conn->prepare("SELECT * FROM attendancereport, student WHERE aid = ? AND attendancereport.sid = student.sid");
                  $stmt->bind_param("s", $_GET['aid']);
                  $stmt->execute();
                  $result = $stmt->get_result();

                  if ($result->num_rows > 0) {
                    
                   // output data of each row
                     while($row = $result->fetch_assoc()) {
                      // XSS: Escape dynamic database values before rendering them.
                      echo "<tr><td> " . xssEscape($row["sid"]). " </td><td> " . xssEscape($row["fname"])." " . xssEscape($row["lname"])." </td>
                      <td>" . xssEscape($row["status"]). " </td>


                      </tr>"; 
                       }
                                  }

                  ?>


                </tbody>
                <tfoot>
                 
                </tfoot>
              </table>
            </div>
         

        
            <!-- /.box-body -->
          </div>







<?php



            # code...
          } ?>
            
          </div>
          <!-- /.box -->

          

        </div>

      <!--------------------------
        | Your Page Content Here |
        -------------------------->
   
    </section>

    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Main Footer -->
  <?php include_once 'footer.php'; ?>
  
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
  immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="bower_components/select2/dist/js/select2.full.min.js"></script>
<!-- Select2 -->
<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>


<script src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="plugins/timepicker/bootstrap-timepicker.min.js"></script>

<!-- bootstrap color picker -->
<script src="bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
<!-- bootstrap time picker -->
<script src="plugins/timepicker/bootstrap-timepicker.min.js"></script>

<script src="bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- iCheck 1.0.1 -->
<script src="plugins/iCheck/icheck.min.js"></script>
<!-- FastClick -->
<script src="bower_components/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<!-- Page behaviour (replaces inline scripts; required by the CSP) -->
<script src="assets/js/app.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. -->
</body>
</html>

<?php 

if(isset($_POST['submitatt']))
{
    foreach ($_POST['att'] as $id => $att)
    {


        $sid = $_POST['sid'][$id];
        $aid = $_POST['aid'][$id];
       
         
        $attendance = $conn->prepare("INSERT INTO attendancereport (aid,sid,status) VALUES (?, ?, ?)");
        $attendance->bind_param("iss", $aid,$sid, $att);
        $attendance->execute();

         echo "<span class='js-show-truemsg' hidden></span>";
    }
     
    if ($conn->affected_rows>0) {
        $msg = "Attendance has been added successfully";
    }
} ?>
