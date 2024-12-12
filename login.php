<?php
session_start();

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once "connection/db.php";

$username = $password = "";
$username_err = $password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }

    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    if(empty($username_err) && empty($password_err)){
      $sql = "SELECT users.id, username, first_name, last_name, password, roles.module, roles.id as role_id
              FROM users 
              INNER JOIN roles ON users.role = roles.id 
              WHERE username = ?";

      if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("s", $param_username);

        $param_username = $username;

        if($stmt->execute()){
          $stmt->store_result();

          if($stmt->num_rows == 1){
            $stmt->bind_result($id, $username, $first_name, $last_name, $hashed_password, $module, $role_id);
            if($stmt->fetch()){
              if(md5($password) === $hashed_password){
                session_start();
                $_SESSION["user_id"] = $id;
                $_SESSION["role_id"] = $role_id;
                $_SESSION["username"] = $username;
                $_SESSION["fullname"] = $first_name . " " . $last_name;
                $_SESSION["module"] = json_decode($module);

                header("location: dashboard.php");
              } else{
                $password_err = "The password you entered was not valid.";
              }
            }
          } else{
            $username_err = "No account found with that username.";
          }
        } else{
          echo "Oops! Something went wrong. Please try again later.";
        }

        $stmt->close();
      }
    }

    $conn->close();
}

include_once('head.php');
?>

<body class="bg-gray-200">
  <div class="container position-sticky z-index-sticky top-0">
    <div class="row">
      <div class="col-12">
      </div>
    </div>
  </div>
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1497294815431-9365093b7331?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn3 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign in</h4>
                  <!-- <div class="row mt-3">
                    <div class="col-2 text-center ms-auto">
                      <a class="btn btn-link px-3" href="javascript:;">
                        <i class="fa fa-facebook text-white text-lg"></i>
                      </a>
                    </div>
                    <div class="col-2 text-center px-1">
                      <a class="btn btn-link px-3" href="javascript:;">
                        <i class="fa fa-github text-white text-lg"></i>
                      </a>
                    </div>
                    <div class="col-2 text-center me-auto">
                      <a class="btn btn-link px-3" href="javascript:;">
                        <i class="fa fa-google text-white text-lg"></i>
                      </a>
                    </div>
                  </div>
                </div> -->
              </div>
              <div class="card-body">
                <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                  <div class="input-group input-group-outline my-3">
                    <label class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control">
                  </div>
                  <?php if(!empty($username_err)): ?>
                    <div class="text-danger text-xs"><?php echo $username_err; ?></div>
                  <?php endif; ?>
                  
                  <div class="input-group input-group-outline mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control">
                  </div>
                  <?php if(!empty($password_err)): ?>
                    <div class="text-danger text-xs"><?php echo $password_err; ?></div>
                  <?php endif; ?>
                  
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2">Sign in</button>
                  </div>
                  <!-- <p class="mt-4 text-sm text-center">
                    Don't have an account?
                    <a href="../pages/sign-up.html" class="text-primary text-gradient font-weight-bold">Sign up</a>
                  </p> -->
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer bottom-2 py-4">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                made with <i class="fa fa-heart" aria-hidden="true"></i> by
                <a href="#" class="font-weight-bold text-white" target="_blank">TBDots</a>
                for a better web.
              </div>
            </div>
            <div class="col-12 col-md-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="#" class="nav-link text-white" target="_blank">TBDots</a>
                </li>
                <li class="nav-item">
                  <a href="#/presentation" class="nav-link text-white" target="_blank">About Us</a>
                </li>
                <li class="nav-item">
                  <a href="#/blog" class="nav-link text-white" target="_blank">Blog</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/material-dashboard.min.js?v=3.1.0"></script>
</body>

</html>