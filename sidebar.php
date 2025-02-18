

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="dashboard.php">
        <img src="../assets/img/icons/logo.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold text-white">TBDots</span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'dashboard.php') echo 'active bg-gradient-primary'; ?>" href="dashboard.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">dashboard</i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <?php if(isset($_SESSION['module']) && in_array(1, $_SESSION['module'])): ?>
          <li class="nav-item">
            <a class="nav-link text-white <?php if($current_page == 'users.php') echo 'active bg-gradient-primary'; ?>" href="users.php">
              <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                <i class="material-icons opacity-10">group</i>
              </div>
              <span class="nav-link-text ms-1">Users</span>
            </a>
          </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['module']) && in_array(5, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'physician.php') echo 'active bg-gradient-primary'; ?>" href="physician.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">medication</i>
            </div>
            <span class="nav-link-text ms-1">Physicians</span>
          </a>
        </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['module']) && in_array(9, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'patients.php') echo 'active bg-gradient-primary'; ?>" href="patients.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">wheelchair_pickup</i>
            </div>
            <span class="nav-link-text ms-1">Patients</span>
          </a>
        </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['module']) && in_array(14, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'laboratory.php') echo 'active bg-gradient-primary'; ?>" href="laboratory.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">science</i>
            </div>
            <span class="nav-link-text ms-1">Laboratory</span>
          </a>
        </li>
        <?php endif; ?>
        <?php if(isset($_SESSION['module']) && in_array(17, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'activity_logs.php') echo 'active bg-gradient-primary'; ?>" href="activity_logs.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">history</i>
            </div>
            <span class="nav-link-text ms-1">Activity Logs</span>
          </a>
        </li>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['module']) && in_array(18, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'inventory.php') echo 'active bg-gradient-primary'; ?>" href="inventory.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">inventory</i>
            </div>
            <span class="nav-link-text ms-1">Inventory</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if(isset($_SESSION['module']) && in_array(21, $_SESSION['module'])): ?>
        <li class="nav-item">
          <a class="nav-link text-white <?php if($current_page == 'roles_permissions.php') echo 'active bg-gradient-primary'; ?>" href="roles_permissions.php">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">settings</i>
            </div>
            <span class="nav-link-text ms-1">Roles and Permissions</span>
          </a>
        </li>
        <?php endif; ?>
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/tables.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">table_view</i>
            </div>
            <span class="nav-link-text ms-1">Tables</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/billing.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">receipt_long</i>
            </div>
            <span class="nav-link-text ms-1">Billing</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/virtual-reality.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">view_in_ar</i>
            </div>
            <span class="nav-link-text ms-1">Virtual Reality</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/rtl.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">format_textdirection_r_to_l</i>
            </div>
            <span class="nav-link-text ms-1">RTL</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/notifications.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">notifications</i>
            </div>
            <span class="nav-link-text ms-1">Notifications</span>
          </a>
        </li> -->
        <!-- <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Account pages</h6>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/profile.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">person</i>
            </div>
            <span class="nav-link-text ms-1">Profile</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/sign-in.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">login</i>
            </div>
            <span class="nav-link-text ms-1">Sign In</span>
          </a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link text-white " href="../pages/sign-up.html">
            <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="material-icons opacity-10">assignment</i>
            </div>
            <span class="nav-link-text ms-1">Sign Up</span>
          </a>
        </li> -->
      </ul>
    </div>
  </aside>