<?php require_once 'security.php';
require_once 'input-validation.php';

// Supporting Input Validation: allow only this page's expected request fields and formats.
validateRequestFields(
  array(),
  array('csrf_token' => 'token', 'submit' => 'action', 'sid' => 'id', 'pid' => 'id', 'tid' => 'id', 'nic' => 'nic', 'fname' => 'name', 'lname' => 'name', 'classroom' => 'id', 'dob' => 'date', 'gender' => 'gender', 'address' => 'text', 'parent' => 'id', 'email' => 'email', 'job' => 'text', 'contact' => 'contact', 'skill' => 'longtext')
);


include_once 'database.php';
$sid = $pid = $tid = $fname = $lname = $classroom = $dob = $gender = '';
$address = $parent = $email = $nic = $contact = $occupation = $skill = '';
if (!isset($_SESSION['user'])) {
    // Redirect unauthenticated users and terminate execution
    // to prevent protected page content from being served.
    header('Location:./logout.php');
    exit();

}

// CSRF: reject any POST without a valid token before any data is changed.
include_once 'csrf.php';
verifyCSRFOnPost();

// Prevent a user from submitting a different profile ID in the request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionUid = $_SESSION['uid'] ?? null;
    $role = $_SESSION['role'] ?? '';

    if ($role === 'Student' && isset($_POST['sid']) && trim((string) $_POST['sid']) !== (string) $sessionUid) {
        http_response_code(403);
        exit('Forbidden: you can only update your own profile.');
    }

    if ($role === 'Parent' && isset($_POST['pid']) && trim((string) $_POST['pid']) !== (string) $sessionUid) {
        http_response_code(403);
        exit('Forbidden: you can only update your own profile.');
    }

    if ($role === 'Teacher' && isset($_POST['tid']) && trim((string) $_POST['tid']) !== (string) $sessionUid) {
        http_response_code(403);
        exit('Forbidden: you can only update your own profile.');
    }
}
?>
<?php


if($_SESSION['role']=='Student'){
  $sid = $_SESSION['uid'];


  if (isset($_POST['submit'])) {

    // Use the authenticated user's ID instead of trusting a
    // user-supplied student ID to prevent unauthorized profile updates.
    $sid = $_SESSION['uid'];


    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $classroom = $_POST['classroom'];
    $dob = date_format(
        new DateTime($_POST['dob']),
        'Y-m-d'
    );
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    $parent = $_POST['parent'];

    $_SESSION['user'] = $fname.' '.$lname;

    try {

      $stmt = $conn->prepare("UPDATE student SET fname = ?, lname = ?, bday = ?, address = ?, gender = ?, parent = ?, classroom = ? WHERE sid = ?");
      $stmt->bind_param("ssssssss", $fname, $lname, $dob, $address, $gender, $parent, $classroom, $sid);


                   // $sql = "INSERT INTO student (sid,fname,lname,bday,address,gender,parent,classroom) VALUES ('".$sid."', '".$fname."', '".$lname."','".$dob."','".$address."','".$gender."','".$parent."','".$classroom."')";

      if ($stmt->execute()) {
       echo "<span class='js-show-truemsg' hidden></span>";
     } else {
     }

   } catch (Exception $e) {

   }






                # code...
 } else {
  $stmt = $conn->prepare("SELECT * FROM student WHERE sid = ?");
  $stmt->bind_param("s", $sid);
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

}else if($_SESSION['role']=='Parent'){
  $pid = $_SESSION['uid'];



  if (isset($_POST['submit'])) {
    $nic = $_POST['nic'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];

               // $dob = date_format(new DateTime($_POST['dob']),'Y-m-d');
                //echo $dob;
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $job = $_POST['job'];

    $contact = $_POST['contact'];

    $_SESSION['user'] = $fname.' '.$lname;

    try {


     $stmt = $conn->prepare("UPDATE parent SET fname = ?, lname = ?, address = ?, gender = ?, job = ?, contact = ?, nic = ? WHERE pid = ?");
     $stmt->bind_param("ssssssss", $fname, $lname, $address, $gender, $job, $contact, $nic, $pid);

                   // $sql = "INSERT INTO Parent (fname,lname,address,gender,job,contact,nic,email) VALUES ( '".$fname."', '".$lname."','".$address."','".$gender."','".$job."','".$contact."','".$nic."','".$email."')";

     if ($stmt->execute()) {
       echo "<span class='js-show-truemsg' hidden></span>";
     } else {
     }

   } catch (Exception $e) {

   }






                # code...
 } else {
  $stmt = $conn->prepare("SELECT * FROM parent WHERE pid = ?");
  $stmt->bind_param("s", $pid);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

      $nic = $row['nic'];
      $fname = $row['fname'];
      $lname = $row['lname'];
      $contact = $row['contact'];
      $occupation = $row['job'];
      $dob = date_format(new DateTime($row['bday']),'Y-m-d');
                //echo $dob;
      $gender = $row['gender'];
      $address = $row['address'];
      $email=$row['email'];

    }
  }
}
}else if($_SESSION['role']=='Teacher'){
  $tid = $_SESSION['uid'];


  if (isset($_POST['submit'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $dob = date_format(new DateTime($_POST['dob']),'Y-m-d');
                //echo $dob;
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    $skill = $_POST['skill'];

    $_SESSION['user'] = $fname.' '.$lname;

    $contact = $_POST['contact'];
    try {
     $stmt = $conn->prepare("UPDATE teacher SET fname = ?, lname = ?, bday = ?, address = ?, gender = ?, skill = ?, contact = ? WHERE tid = ?");
     $stmt->bind_param("ssssssss", $fname, $lname, $dob, $address, $gender, $skill, $contact, $tid);

                   // $sql = "INSERT INTO Teacher (tid,fname,lname,bday,address,gender,skill,contact,email) VALUES ('".$tid."', '".$fname."', '".$lname."','".$dob."','".$address."','".$gender."','".$skill."','".$contact."','".$email."')";

     if ($stmt->execute()) {
       echo "<span class='js-show-truemsg' hidden></span>";
     } else {
     }

   } catch (Exception $e) {

   }






                # code...
 } else {
  $stmt = $conn->prepare("SELECT * FROM teacher WHERE tid = ?");
  $stmt->bind_param("s", $tid);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {

      $tid = $row['tid'];
      $fname = $row['fname'];
      $lname = $row['lname'];
      $contact = $row['contact'];
      $skill = $row['skill'];
      $dob = date_format(new DateTime($row['bday']),'Y-m-d');
                //echo $dob;
      $gender = $row['gender'];
      $address = $row['address'];
      $email=$row['email'];

    }
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
          <div class="col-md-12">
            <div class="x_panel">
              <div class="x_title">
                <h2>User Profile</h2>
                <ul class="nav navbar-right panel_toolbox">
                  <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                  </li>
                  <li><a class="close-link"><i class="fa fa-close"></i></a>
                  </li>
                </ul>
                <div class="clearfix"></div>
              </div>
              <div class="x_content">

                <form method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">














                  <div class="container rounded bg-white mt-5 mb-5">
                    <div class="row">
                      <div class="col-md-3 border-right">
                        <div class="d-flex flex-column align-items-center text-center p-3 py-5"><img class="rounded-circle mt-5" width="150px" src="https://st3.depositphotos.com/15648834/17930/v/600/depositphotos_179308454-stock-illustration-unknown-person-silhouette-glasses-profile.jpg"><span class="font-weight-bold">Edogaru</span><span class="text-black-50">edogaru@mail.com.my</span><span> </span></div>
                      </div>
                      <div class="col-md-9 border-right">
                        <div class="p-3 py-5">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-right">Profile Settings</h4>
                          </div>
                          <div class="row mt-2">
                            <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                            <div class="col-md-6"><label class="labels">First Name</label><input type="text" name="fname" class="form-control" placeholder="first name" value="<?php echo xssEscape($fname); ?>"></div>
                            <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                            <div class="col-md-6"><label class="labels">Last Name</label><input type="text" name="lname" class="form-control" value="<?php echo xssEscape($lname); ?>" placeholder="surname"></div>
                          </div>
                          <div class="row mt-3">

                            <?php if($_SESSION['role']=='Student'){ ?>

                             <div class="col-md-12">
                              <div class="form-group">
                                <label for="exampleInputPassword1">Student ID</label>
                                <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                                <input name="sid" type="text" class="form-control" id="exampleInputPassword1" readonly required value="<?php echo xssEscape($sid); ?>">
                              </div>
                            </div>

                            <div class="col-md-12">
                              <div class="form-group">

                                <label>Date of Birth</label>

                                <div class="input-group date">
                                  <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                                  <input type="date" name='dob' class="form-control pull-right" id="datepicker" placeholder="Select Student's Data of Birth" value="<?php echo xssEscape($dob); ?>">
                                </div>
                                <!-- /.input group -->

                              </div>
                            </div>

                            <div class="col-md-12">
                              <div class="form-group">
                                <label for="exampleInputPassword1">Gender</label>
                                <div class="radio ">
                                  <label><input type="radio" name="gender" value="Male"  <?php if($gender=='Male'){echo 'checked';} ?>> Male</label>
                                </div>
                                <div class="radio ">
                                  <label><input type="radio" name="gender" value="Female" <?php if($gender=='Female'){echo 'checked';} ?>> Female</label>

                                </div>

                              </div>
                            </div>

                            <div class="col-md-12">
                              <div class="form-group">
                                <label for="exampleInputPassword1">Email</label>
                                <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                                <input name="email" type="email" class="form-control" id="exampleInputPassword1"  disabled required value="<?php echo xssEscape($email); ?>">
                              </div>
                            </div>



                            <div class="col-md-12">
                              <div class="form-group">
                                <label for="exampleFormControlTextarea1">Address</label>
                                <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                                <textarea name="address" class="form-control" id="exampleFormControlTextarea1" rows="2"><?php echo xssEscape($address); ?></textarea>
                              </div>
                            </div>
                            <div class="col-md-12">
                              <div class="form-group">
                                <label>Class Room</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true" name="classroom"><option >Select Class Room</option>
                                  <?php
                                  $sql = "SELECT * FROM classroom";
                                  $result = $conn->query($sql);
                                  if ($result->num_rows > 0) {
                   // output data of each row
                                   while($row = $result->fetch_assoc()) {
                                    // XSS: Escape dynamic database values before rendering them.
                                    echo "<option "; if($classroom==$row["hno"]){echo 'selected="selected"';} echo " value='".xssEscape($row["hno"])."' >".xssEscape($row["title"])."_ID:".xssEscape($row["hno"])."</option>";
                                  }
                                }
                                ?>
                              </select>
                            </div>
                          </div>


                          <div class="col-md-12">
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


                                  // XSS: Escape dynamic database values before rendering them.


                                  // XSS: Escape dynamic values before rendering them in HTML.
                                  echo "<option "; if($parent==$row["pid"]){echo 'selected="selected"';} echo " value='".xssEscape($row["pid"])."' >".xssEscape($row["fname"])." ".xssEscape($row["lname"])." - ID:".xssEscape($row["pid"])."</option>";
                                }
                              }

                              ?>
                            </select>

                          </div>
                        </div>


                      <?php } else if($_SESSION['role']=='Parent'){ ?>



                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">National Identity Card</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="nic" type="text" class="form-control" id="exampleInputPassword1"  required value="<?php echo xssEscape($nic); ?>">
                          </div>
                        </div>


                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Gender</label>
                            <div class="radio ">
                              <label style="width: 100px"><input type="radio" name="gender" value="Male" <?php if($gender=='Male'){echo 'checked';} ?>> Male</label>
                              <label style="width: 100px"><input type="radio" name="gender" value="Female" <?php if($gender=='Female'){echo 'checked';} ?>> Female</label>

                            </div>

                          </div>
                        </div>


                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Email</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="email" type="email" class="form-control" id="exampleInputPassword1" disabled required value="<?php echo xssEscape($email); ?>">
                          </div>
                        </div>


                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleFormControlTextarea1">Address</label>
                            <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                            <textarea name="address" class="form-control" id="exampleFormControlTextarea1" rows="2"><?php echo xssEscape($address); ?></textarea>
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Contact</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="contact" type="text" class="form-control" id="exampleInputPassword1"  required value="<?php echo xssEscape($contact); ?>">
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Occupation</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="job" type="text" class="form-control" id="exampleInputPassword1"  required value="<?php echo xssEscape($occupation); ?>">
                          </div>
                        </div>

                      <?php } else { ?>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Teacher ID</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="tid" type="text" class="form-control" id="exampleInputPassword1" readonly required value="<?php echo xssEscape($tid); ?>">
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">

                            <label>Date of Birth</label>

                            <div class="input-group date">
                              <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                              <input type="date" name='dob' class="form-control pull-right" id="datepicker" value="<?php echo xssEscape($dob); ?>">
                            </div>
                            <!-- /.input group -->

                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Gender</label>
                            <div class="radio ">
                              <label style="width: 100px"><input type="radio" name="gender" value="Male" <?php if($gender=='Male'){echo 'checked';} ?>> Male</label>
                              <label style="width: 100px"><input type="radio" name="gender" value="Female" <?php if($gender=='Female'){echo 'checked';} ?>> Female</label>

                            </div>

                          </div>
                        </div>


                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Email</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="email" type="email" class="form-control" id="exampleInputPassword1" disabled=""> required value="<?php echo xssEscape($email); ?>">
                          </div>
                        </div>


                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleFormControlTextarea1">Address</label>
                            <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                            <textarea name="address" class="form-control" id="exampleFormControlTextarea1" rows="2"><?php echo xssEscape($address); ?></textarea>
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleInputPassword1">Contact</label>
                            <!-- XSS: Escape untrusted values before rendering them in HTML. -->
                            <input name="contact" type="text" class="form-control" id="exampleInputPassword1"  required value="<?php echo xssEscape($contact); ?>">
                          </div>
                        </div>

                        <div class="col-md-12">
                          <div class="form-group">
                            <label for="exampleFormControlTextarea1">Skills</label>
                            <!-- XSS: Escape untrusted values before rendering them in this form field. -->
                            <textarea name="skill" class="form-control" id="exampleFormControlTextarea1" rows="2"><?php echo xssEscape($skill); ?></textarea>
                          </div>
                        </div>
                      <?php } ?>

                    </div>
                    <div class="mt-5 text-center">
                      <button class="btn btn-primary profile-button" type="submit" name="submit">Save Profile</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </form>

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
