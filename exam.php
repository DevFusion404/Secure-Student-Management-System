<?php require_once 'security.php';
require_once 'input-validation.php';

// Supporting Input Validation: allow only this page's expected request fields and formats.
validateRequestFields(
  array('update' => 'id'),
  array('csrf_token' => 'token', 'submit' => 'action', 'subject' => 'id', 'teacher' => 'id', 'classroom' => 'id', 'date' => 'date', 'stime' => 'time', 'etime' => 'time', 'id' => 'id', 'fname' => 'name', 'lname' => 'name', 'email' => 'email', 'dob' => 'date', 'gender' => 'gender', 'address' => 'text', 'skill' => 'longtext', 'contact' => 'contact')
);


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

$id =$fname =$lname = $classroom = $dob = $gender = $address = $parent=" ";


if(isset($_GET['update'])){
  $stmt = $conn->prepare("SELECT * FROM exam WHERE id = ?");
  $stmt->bind_param("s", $_GET['update']);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

      $id = $row['id'];
      $fname = $row['fname'];
      $lname = $row['lname'];
      $contact = $row['contact'];
      $skill = $row['skill'];
      $dob = date_format(new DateTime($row['bday']),'m/d/Y');
                //echo $dob;
      $gender = $row['gender'];
      $address = $row['address'];
      $email=$row['email'];

    }
  }
}

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
  <title> Dashboard</title><link rel="icon" href="../img/favicon2.png">
  <!-- Tell the browser to be responsive to screen width -->
  <?php include_once 'header.php'; ?>
  <!-- Date/time picker styles required by the exam form. -->
  <link href="assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css" rel="stylesheet">


</head>

<body class="nav-md">
  <div class="container body">
    <div class="main_container">
      <div class="col-md-3 left_col">
        <?php include_once 'sidebar.php'; ?>

      </div>

      <?php include_once 'nav-menu.php'; ?>

      <!-- page content -->
      <div class="right_col" role="main">
        <div class="row">
          <div class="col-md-3">
            <div class="x_panel">
              <div class="x_title">
                <h2><?php echo (isset($_GET['update']))?"Update exam":"Add exam"; ?></h2>
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                  </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a>
                  </li>
                </ul>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">

                <?php if (!isset($_GET['update'])) {
                  if (isset($_POST['submit'])) {
                    $subject = $_POST['subject'];
                    $teacher = $_POST['teacher'];
                    $classroom = $_POST['classroom'];

                    $date = date_format(new DateTime($_POST['date']),'Y-m-d');
                //echo $dob;
               // $day = $_POST['day'];
                    $stime = $_POST['stime'];
                    $etime = $_POST['etime'];






                    try {




                      $stmt = $conn->prepare("INSERT INTO exam(subject, teacher, classroom, `date`, stime, etime) VALUES (?, ?, ?, ?, ?, ?)");
                      $stmt->bind_param("ssssss", $subject, $teacher, $classroom, $date, $stime, $etime);

                      if ($stmt->execute()) {
                       echo "<span class='js-show-truemsg' hidden></span>";
                     } else {
                     }

                   } catch (Exception $e) {

                   }





                # code...
                 }

                 ?>
               <?php }elseif (isset($_GET['update'])) { ?>

                <div class="alert alert-success alert-dismissible" style="display: none;" id="truemsg">
                  <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                  <h4><i class="icon fa fa-check"></i> Success!</h4>
                  Update Student Successfully
                </div>

                <?php

                if (isset($_POST['submit'])) {
                  $id = $_POST['id'];
                  $fname = $_POST['fname'];
                  $lname = $_POST['lname'];
                  $email = $_POST['email'];
                  $dob = date_format(new DateTime($_POST['dob']),'Y-m-d');
                //echo $dob;
                  $gender = $_POST['gender'];
                  $address = $_POST['address'];

                  $skill = $_POST['skill'];

                  $contact = $_POST['contact'];



                  try {


                    $stmt = $conn->prepare("UPDATE exam SET fname = ?, lname = ?, bday = ?, address = ?, gender = ?, skill = ?, contact = ?, email = ? WHERE id = ?");
                    $stmt->bind_param("sssssssss", $fname, $lname, $dob, $address, $gender, $skill, $contact, $email, $id);

                   // $sql = "INSERT INTO Exam (id,fname,lname,bday,address,gender,skill,contact,email) VALUES ('".$id."', '".$fname."', '".$lname."','".$dob."','".$address."','".$gender."','".$skill."','".$contact."','".$email."')";

                    if ($stmt->execute()) {
                     echo "<span class='js-show-truemsg' hidden></span>";
                   } else {
                   }

                 } catch (Exception $e) {

                 }






                # code...
               }
             }

             ?>


             <form role="form" method="POST" >
               <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
              <div class="box-body">



                <div class="form-group">
                  <label>Subject</label>
                  <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" name="subject"><option >Select Subject</option>
                    <?php
                    $sql = "SELECT * FROM subject";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                   // output data of each row
                     while($row = $result->fetch_assoc()) {
                      // XSS: Escape dynamic database values before rendering them.
                      echo "<option value='".xssEscape($row["sid"])."' >".xssEscape($row["title"])."_ID:".xssEscape($row["sid"])."</option>";
                    }
                  }
                  ?>
                </select>
              </div>




              <div class="form-group">
                <label>Exam Hall (Class Room)</label>
                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" name="classroom"><option >Select Class Room</option>
                  <?php
                  $sql = "SELECT * FROM classroom";
                  $result = $conn->query($sql);
                  if ($result->num_rows > 0) {
                   // output data of each row
                   while($row = $result->fetch_assoc()) {
                    // XSS: Escape dynamic database values before rendering them.
                    echo "<option value='".xssEscape($row["hno"])."' >".xssEscape($row["title"])."_ID:".xssEscape($row["hno"])."</option>";
                  }
                }
                ?>
              </select>
            </div>

            <div class="form-group">
              <label>Teacher in Charge </label>
              <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" name="teacher"><option >Select Teacher</option>
                <?php
                $sql = "SELECT * FROM teacher";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                   // output data of each row
                 while($row = $result->fetch_assoc()) {
                  // XSS: Escape dynamic database values before rendering them.
                  echo "<option value='".xssEscape($row["tid"])."' >".xssEscape($row["fname"])." ".xssEscape($row["lname"])."_ID:".xssEscape($row["tid"])."</option>";
                }
              }
              ?>
            </select>
          </div>

          <div class="form-group">

            <label>Date</label>

            <div class="input-group">
              <button type="button" class="input-group-addon" data-open-picker="exam-date" aria-label="Select exam date">
                <i class="fa fa-calendar"></i>
              </button>
              <input type="date" name='date' class="form-control pull-right" id="exam-date" required>
            </div>
            <!-- /.input group -->

          </div>



          <div class="bootstrap-timepicker">
            <div class="form-group">
              <label>Start Time:</label>

              <div class="input-group">
                <input name="stime" type="time" class="form-control" id="exam-start-time" required>

                <button type="button" class="input-group-addon" data-open-picker="exam-start-time" aria-label="Select start time">
                  <i class="fa fa-clock-o"></i>
                </button>
              </div>
              <!-- /.input group -->
            </div>
            <!-- /.form group -->
          </div>


          <div class="bootstrap-timepicker">
            <div class="form-group">
              <label>End Time:</label>

              <div class="input-group">
                <input name="etime" type="time" class="form-control" id="exam-end-time" required>

                <button type="button" class="input-group-addon" data-open-picker="exam-end-time" aria-label="Select end time">
                  <i class="fa fa-clock-o"></i>
                </button>
              </div>
              <!-- /.input group -->
            </div>
            <!-- /.form group -->
          </div>











        </div>
        <!-- /.box-body -->

        <div class="box-footer">
          <button type="submit" name="submit" value="submit" class="btn btn-primary">Add Exam</button>
        </div>
      </form>

    </div>
  </div>




</div>

<div class="col-md-9">

  <div class="x_panel">
    <div class="x_title">
      <h2>All <small>Students</small></h2>
      <ul class="nav navbar-right panel_toolbox">
        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="#">Settings 1</a>
            <a class="dropdown-item" href="#">Settings 2</a>
          </div>
        </li>
        <li><a class="close-link"><i class="fa fa-close"></i></a>
        </li>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <div class="row">
        <div class="col-sm-12">
          <div class="card-box table-responsive">
            <p class="text-muted font-13 m-b-30">
              School Management System
            </p>
            <table id="datatable-buttons" class="table table-striped table-bordered" style="width:100%">
              <thead>
                <tr>
                  <th>Exam ID</th>
                  <th>Subject</th>
                  <th>Teacher In Charge</th>
                  <th>Location (Classroom)</th>
                  <th>Date</th>
                  <th>Start Time</th>
                  <th>End Time</th>

                </tr>
              </thead>


              <tbody>
               <?php

               $sql = "SELECT * FROM Exam";
               $result = $conn->query($sql);

               if ($result->num_rows > 0) {
                   // output data of each row
                 while($row = $result->fetch_assoc()) {
                  // XSS: Escape dynamic database values before rendering them.
                  echo "<tr><td> " . xssEscape($row["id"]). " </td><td> " . xssEscape($row["subject"])." </td><td> " . xssEscape($row["teacher"])." </td><td> " . xssEscape($row["classroom"]). "</td><td>" . xssEscape($row["date"]). "</td><td>" . xssEscape($row["stime"]). "</td><td>" . xssEscape($row["etime"]). "</td></tr>";
                }
              }

              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

</div>
<!-- /.box -->

</div>

</div>
<!-- /page content -->

<!-- footer content -->
<footer>
  <div class="pull-right">
    Gentelella - Bootstrap Admin Template by <a href="https://colorlib.com">Colorlib</a>
  </div>
  <div class="clearfix"></div>
</footer>
<!-- /footer content -->
</div>
</div>
<?php include_once 'footer.php'; ?>

</body>

</html>
