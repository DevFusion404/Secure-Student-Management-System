<?php require_once 'security.php';
require_once 'input-validation.php';

// Supporting Input Validation: allow only this page's expected request fields and formats.
validateRequestFields(
  array('update' => 'id'),
  array('csrf_token' => 'token', 'submit' => 'action', 'sid' => 'id', 'fname' => 'name', 'lname' => 'name', 'classroom' => 'id', 'dob' => 'date', 'gender' => 'gender', 'address' => 'text', 'parent' => 'id', 'email' => 'email')
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

$sid =$fname =$lname = $classroom = $dob = $gender = $address = $parent=" ";
$email = '';
$studentMessage = '';
$studentMessageType = '';

function studentHtml($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function studentRejectInvalidInput($statusCode = 422) {
  http_response_code($statusCode);
  exit('Invalid student form input.');
}

function studentPostValue($name) {
  if (!isset($_POST[$name]) || !is_string($_POST[$name])) {
    studentRejectInvalidInput();
  }
  return trim($_POST[$name]);
}

function studentDateValue($value) {
  $date = DateTime::createFromFormat('!Y-m-d', $value);
  $errors = DateTime::getLastErrors();
  if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
    studentRejectInvalidInput();
  }
  return $date->format('Y-m-d');
}

function studentValidateInput($sid, $fname, $lname, $email, $classroom, $dob, $gender, $address, $parent, $validateSid = true) {
  if (($validateSid && (strlen($sid) === 0 || strlen($sid) > 25))
      || strlen($fname) === 0 || strlen($fname) > 50
      || strlen($lname) === 0 || strlen($lname) > 50
      || !filter_var($email, FILTER_VALIDATE_EMAIL)
      || strlen($email) > 50
      || strlen($classroom) === 0 || strlen($classroom) > 25
      || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $dob)
      || !in_array($gender, array('Male', 'Female'), true)
      || strlen($address) > 250
      || !ctype_digit($parent)) {
    studentRejectInvalidInput();
  }
}


if(isset($_GET['update'])){
  $stmt = $conn->prepare("SELECT * FROM student WHERE sid = ?");
  $stmt->bind_param("s", $_GET['update']);
  $stmt->execute();
  $result = $stmt->get_result();
  if (!is_string($_GET['update'])) {
    studentRejectInvalidInput(400);
  }
  $stmt = $conn->prepare("SELECT * FROM student WHERE sid = ?");
  $stmt->bind_param("s", $_GET['update']);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      $sid = $row['sid'];
      $fname = $row['fname'];
      $lname = $row['lname'];
      $classroom = $row['classroom'];
      $email = $row['email'];
      $dob = date_format(new DateTime($row['bday']),'Y-m-d');
                //echo $dob;
      $gender = $row['gender'];
      $address = $row['address'];
      $parent=$row['parent'];

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
                <h2><?php echo (isset($_GET['update']))?"Update student":"Add student"; ?></h2>
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
                    if (studentPostValue('submit') !== 'submit') {
                      studentRejectInvalidInput();
                    }
                    $sid = studentPostValue('sid');
                    $fname = studentPostValue('fname');
                    $lname = studentPostValue('lname');
                    $email = studentPostValue('email');
                    $classroom = studentPostValue('classroom');
                    $dob = studentDateValue(studentPostValue('dob'));
                    $gender = studentPostValue('gender');
                    $address = studentPostValue('address');
                    $parent = isset($_POST['parent']) ? studentPostValue('parent') : '0';
                    studentValidateInput($sid, $fname, $lname, $email, $classroom, $dob, $gender, $address, $parent);





                      try {




                        $stmt = $conn->prepare("INSERT INTO student (sid, fname, lname, bday, address, gender, parent, classroom, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssssss", $sid, $fname, $lname, $dob, $address, $gender, $parent, $classroom, $email);
                        if ($stmt->execute()) {
                         echo "<span class='js-show-truemsg' hidden></span>";
                       } else {
                       }
                        if ($stmt->execute()) {
                          $studentMessage = 'Student created successfully.';
                          $studentMessageType = 'success';
                        } else {
                          $studentMessage = 'Unable to create the student. The student ID may already exist.';
                          $studentMessageType = 'danger';
                        }

                      } catch (Exception $e) {
                        $studentMessage = 'Unable to create the student. Please verify the details and try again.';
                        $studentMessageType = 'danger';
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
                    if (studentPostValue('submit') !== 'submit') {
                      studentRejectInvalidInput();
                    }
                    studentPostValue('sid');
                    $sid = $_GET['update'];
                    $fname = studentPostValue('fname');
                    $lname = studentPostValue('lname');
                    $classroom = studentPostValue('classroom');
                    $email = studentPostValue('email');
                    $dob = studentDateValue(studentPostValue('dob'));
                    $gender = studentPostValue('gender');
                    $address = studentPostValue('address');
                    $parent = studentPostValue('parent');
                    studentValidateInput($sid, $fname, $lname, $email, $classroom, $dob, $gender, $address, $parent, false);





                    try {

                      $stmt = $conn->prepare("UPDATE student SET fname = ?, lname = ?, bday = ?, address = ?, gender = ?, parent = ?, classroom = ?, email = ? WHERE sid = ?");
                      $stmt->bind_param("sssssssss", $fname, $lname, $dob, $address, $gender, $parent, $classroom, $email, $sid);


                   // $sql = "INSERT INTO student (sid,fname,lname,bday,address,gender,parent,classroom) VALUES ('".$sid."', '".$fname."', '".$lname."','".$dob."','".$address."','".$gender."','".$parent."','".$classroom."')";

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

                <?php if ($studentMessage !== '') { ?>
                  <div class="alert alert-<?php echo $studentMessageType; ?> alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo studentHtml($studentMessage); ?>
                  </div>
                <?php } ?>


               <form role="form" method="POST" >
                 <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="box-body">

                 <div class="form-group">
                  <label for="exampleInputPassword1">Student ID</label>
                  <input name="sid" type="text" class="form-control" id="exampleInputPassword1" required value="<?php echo studentHtml($sid); ?>">
                </div>

                <div class="form-group">
                  <label for="exampleInputPassword1">First Name</label>
                  <input name="fname" type="text" class="form-control" id="exampleInputPassword1" required value="<?php echo studentHtml($fname); ?>">
                </div>

                <div class="form-group">
                  <label for="exampleInputPassword1">Last Name</label>
                  <input name="lname" type="text" class="form-control" id="exampleInputPassword1" required value="<?php echo studentHtml($lname); ?>">
                </div>

                <div class="form-group">

                  <label>Date of Birth</label>

                  <div class="input-group date">
                    <input type="date" name="dob" class="form-control pull-right" id="datepicker" placeholder="Select Student's Data of Birth" value="<?php echo studentHtml($dob); ?>">
                  </div>
                  <!-- /.input group -->

                </div>

                <div class="form-group">
                  <label for="exampleInputPassword1">Gender</label>
                  <div class="radio ">
                    <label><input type="radio" name="gender" value="Male"  <?php if($gender=='Male'){echo 'checked';} ?>> Male</label>
                  </div>
                  <div class="radio ">
                    <label><input type="radio" name="gender" value="Female" <?php if($gender=='Female'){echo 'checked';} ?>> Female</label>

                  </div>

                </div>

                <div class="form-group">
                  <label for="exampleInputPassword1">Email</label>
                  <input name="email" type="email" class="form-control" id="exampleInputPassword1" required value="<?php echo studentHtml($email); ?>">
                </div>



                <div class="form-group">
                  <label for="exampleFormControlTextarea1">Address</label>
                  <textarea name="address" class="form-control" id="exampleFormControlTextarea1" rows="2"><?php echo studentHtml($address); ?></textarea>
                </div>
                <div class="form-group">
                  <label>Class Room</label>
                  <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" name="classroom"><option >Select Class Room</option>
                    <?php
                    $sql = "SELECT * FROM classroom";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                   // output data of each row
                     while($row = $result->fetch_assoc()) {
                       $selected = ($classroom == $row["hno"]) ? ' selected="selected"' : '';
                       echo '<option' . $selected . ' value="' . studentHtml($row["hno"]) . '">' . studentHtml($row["title"]) . '_ID:' . studentHtml($row["hno"]) . '</option>';
                    }
                  }
                  ?>
                </select>
              </div>


              <div class="form-group">

                <label>Parent</label>
                <select name="parent" class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" >
                 <option value="0">Select Parent</option>

                 <?php

                 $sql = "SELECT * FROM parent";
                 $result = $conn->query($sql);

                 if ($result->num_rows > 0) {
                   // output data of each row
                   while($row = $result->fetch_assoc()) {


                    $selected = ($parent == $row["pid"]) ? ' selected="selected"' : '';
                    echo '<option' . $selected . ' value="' . studentHtml($row["pid"]) . '">' . studentHtml($row["fname"]) . ' ' . studentHtml($row["lname"]) . ' - ID:' . studentHtml($row["pid"]) . '</option>';
                  }
                }

                ?>
              </select>

            </div>
          </div>
          <!-- /.box-body -->

          <div class="box-footer">
            <button type="submit" name="submit" value="submit" class="btn btn-primary"><?php echo isset($_GET['update']) ? 'Update Student' : 'Add Student'; ?></button>
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
                   <th>SID</th>
                   <th>Name</th>
                   <th>DOB</th>
                   <th>Gender</th>
                   <th>Address</th>
                   <th>Classroom</th>
                   <th>Parent</th>
                   <th>Actions</th>
                 </tr>
               </thead>


               <tbody>
                 <?php

                 $sql = "SELECT * FROM student";
                 $result = $conn->query($sql);

                 if ($result->num_rows > 0) {
                   // output data of each row
                   while($row = $result->fetch_assoc()) {
                    $class = (isset($_GET['update']) && $_GET['update'] == $row["sid"])?'parent':'';
                    echo "<tr class='{$class}'><td>" . studentHtml($row["sid"]) . "</td><td>" . studentHtml($row["fname"]) . " " . studentHtml($row["lname"]) . "</td><td>" . studentHtml($row["bday"]) . "</td><td>" . studentHtml($row["gender"]) . "</td><td>" . studentHtml($row["address"]) . "</td><td>" . studentHtml($row["classroom"]) . "</td><td>" . studentHtml($row["parent"]) . "</td><td><a href='student.php?update=" . rawurlencode($row["sid"]) . "'><small class='btn btn-sm btn-primary'>Update</small></a></td></tr>";
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
