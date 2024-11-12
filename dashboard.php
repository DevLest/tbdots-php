<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

// Add location filter query
$locationsQuery = "
    SELECT l.id, m.location as municipality, b.name as barangay 
    FROM locations l
    JOIN municipalities m ON l.municipality_id = m.id 
    JOIN barangays b ON l.barangay_id = b.id
    ORDER BY m.location, b.name";
$locations = $conn->query($locationsQuery);

// Get selected location from URL parameter
$selectedLocation = isset($_GET['location']) ? (int)$_GET['location'] : 0;

// Modify the location condition for all queries
$locationCondition = $selectedLocation > 0 ? "AND p.location_id = $selectedLocation" : "";

// Get statistics for the cards
$thisWeekPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    $locationCondition
")->fetch_assoc();

$totalConfined = $conn->query("
    SELECT COUNT(*) as count 
    FROM lab_results l
    JOIN patients p ON l.patient_id = p.id
    WHERE l.treatment_outcome IS NULL
    $locationCondition
")->fetch_assoc();

$newPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    $locationCondition
")->fetch_assoc();

$totalAnnualPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
    $locationCondition
")->fetch_assoc();

// Get recent patients for the table
$recentPatientsSql = "
    SELECT p.*, 
        m.location as municipality_name,
        b.name as barangay_name 
    FROM patients p
    LEFT JOIN municipalities m ON p.location_id = m.id
    LEFT JOIN barangays b ON b.municipality_id = m.id
    WHERE 1=1 
    $locationCondition
    ORDER BY p.created_at DESC 
    LIMIT 10";

$recentPatients = $conn->query($recentPatientsSql);

// This Week's Patients comparison
$lastWeekPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 WEEK), INTERVAL 1 WEEK)
    AND created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK)
    $locationCondition
")->fetch_assoc();

$weeklyChange = $lastWeekPatients['count'] > 0 
    ? round((($thisWeekPatients['count'] - $lastWeekPatients['count']) / $lastWeekPatients['count']) * 100, 1)
    : 0;

// Number of Confined comparison
$lastMonthConfined = $conn->query("
    SELECT COUNT(*) as count 
    FROM lab_results l
    JOIN patients p ON l.patient_id = p.id
    WHERE l.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 MONTH), INTERVAL 1 MONTH)
    AND l.created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    AND l.treatment_outcome IS NULL
    $locationCondition
")->fetch_assoc();

$confinedChange = $lastMonthConfined['count'] > 0
    ? round((($totalConfined['count'] - $lastMonthConfined['count']) / $lastMonthConfined['count']) * 100, 1)
    : 0;

// New Patients (24h) comparison
$previousDayPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 24 HOUR), INTERVAL 24 HOUR)
    AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    $locationCondition
")->fetch_assoc();

$dailyChange = $previousDayPatients['count'] > 0
    ? round((($newPatients['count'] - $previousDayPatients['count']) / $previousDayPatients['count']) * 100, 1)
    : 0;

// Annual Patients comparison
$previousYearPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    WHERE created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 YEAR), INTERVAL 1 YEAR)
    AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
    $locationCondition
")->fetch_assoc();

$annualChange = $previousYearPatients['count'] > 0
    ? round((($totalAnnualPatients['count'] - $previousYearPatients['count']) / $previousYearPatients['count']) * 100, 1)
    : 0;

// Add this query near your other statistics queries
$healedPatients = $conn->query("
    SELECT 
        DATE_FORMAT(l.treatment_outcome_date, '%b') as month,
        DATE_FORMAT(l.treatment_outcome_date, '%Y-%m') as month_year,
        COUNT(*) as count
    FROM lab_results l
    JOIN patients p ON l.patient_id = p.id
    WHERE l.treatment_outcome = 'CURED'
    AND l.treatment_outcome_date >= DATE_SUB(NOW(), INTERVAL 9 MONTH)
    $locationCondition
    GROUP BY DATE_FORMAT(l.treatment_outcome_date, '%Y-%m'), 
             DATE_FORMAT(l.treatment_outcome_date, '%b')
    ORDER BY month_year ASC
")->fetch_all(MYSQLI_ASSOC);

// Create arrays for labels and data
$healedLabels = [];
$healedData = [];

// Get last 9 months
$months = [];
for ($i = 8; $i >= 0; $i--) {
    $month = date('M', strtotime("-$i months"));
    $months[$month] = 0;
}

// Fill in actual data
foreach ($healedPatients as $record) {
    $months[$record['month']] = (int)$record['count'];
}

// Convert to JSON for JavaScript
$healedLabels = json_encode(array_keys($months));
$healedData = json_encode(array_values($months));
?>

<body class="g-sidenav-show  bg-gray-200">
  <?php
    include_once('sidebar.php');
  ?>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <?php
      include_once('navbar.php');
    ?>
    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body p-3">
              <form method="GET" class="row align-items-center">
                <div class="col-md-10">
                  <select name="location" class="form-select" onchange="this.form.submit()">
                    <option value="0">All Locations</option>
                    <?php while($loc = $locations->fetch_assoc()): ?>
                      <option value="<?php echo $loc['id']; ?>" <?php echo $selectedLocation == $loc['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($loc['municipality'] . ' - ' . $loc['barangay']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="col-md-2">
                  <a href="dashboard.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">weekend</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">This weeks Patients</p>
                <h4 class="mb-0"><?php echo $thisWeekPatients['count']; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
              <p class="mb-0">
                <span class="text-<?php echo $weeklyChange >= 0 ? 'success' : 'danger'; ?> text-sm font-weight-bolder">
                  <?php echo ($weeklyChange >= 0 ? '+' : '') . $weeklyChange; ?>%
                </span> 
                than last week
              </p>
            </div>
          </div>
        </div>
        <!-- <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">person</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Number of Confined</p>
                <h4 class="mb-0"><?php echo $totalConfined['count']; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
              <p class="mb-0">
                <span class="text-<?php echo $confinedChange >= 0 ? 'success' : 'danger'; ?> text-sm font-weight-bolder">
                  <?php echo ($confinedChange >= 0 ? '+' : '') . $confinedChange; ?>%
                </span> 
                than last month
              </p>
            </div>
          </div>
        </div> -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">person</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">New Patients</p>
                <h4 class="mb-0"><?php echo $newPatients['count']; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
              <p class="mb-0">
                <span class="text-<?php echo $dailyChange >= 0 ? 'success' : 'danger'; ?> text-sm font-weight-bolder">
                  <?php echo ($dailyChange >= 0 ? '+' : '') . $dailyChange; ?>%
                </span> 
                than yesterday
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">weekend</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Total Patients / Annually</p>
                <h4 class="mb-0"><?php echo $totalAnnualPatients['count']; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-3">
              <p class="mb-0">
                <span class="text-<?php echo $annualChange >= 0 ? 'success' : 'danger'; ?> text-sm font-weight-bolder">
                  <?php echo ($annualChange >= 0 ? '+' : '') . $annualChange; ?>%
                </span> 
                than Last Year
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <!-- <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card z-index-2 ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0 ">Clinic Visits</h6>
              <p class="text-sm ">Last Campaign Performance</p>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> campaign sent 2 days ago </p>
              </div>
            </div>
          </div>
        </div> -->
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card z-index-2  ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0 "> Death Toll </h6>
              <p class="text-sm "> (<span class="font-weight-bolder">+15%</span>) increase in today death count. </p>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm"> updated 4 min ago </p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mt-4 mb-3">
          <div class="card z-index-2 ">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-dark shadow-dark border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-line-tasks" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0 ">Totally Healed</h6>
              <p class="text-sm ">Last Campaign Performance</p>
              <hr class="dark horizontal">
              <div class="d-flex ">
                <i class="material-icons text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm">just updated</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-4">
        <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>New Patients</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1">30 done</span> this month
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <div class="dropdown float-lg-end pe-4">
                    <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-ellipsis-v text-secondary"></i>
                    </a>
                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5" aria-labelledby="dropdownTable">
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                      <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Adrress</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Age</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php while($patient = $recentPatients->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex px-2 py-1">
                                <div class="d-flex flex-column justify-content-center">
                                    <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($patient['fullname']); ?></h6>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($patient['barangay_name']) . ', ' . htmlspecialchars($patient['municipality_name']); ?></h6>
                            </div>
                        </td>
                        <td class="align-middle text-center text-sm">
                            <span class="text-xs font-weight-bold"><?php echo $patient['age']; ?></span>
                        </td>
                        <td class="text-center">
                            <span class="text-xs font-weight-bold"><?php echo $patient['treatment_outcome'] ?? 'Active'; ?></span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- <div class="col-lg-4 col-md-6">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6>Performance overview</h6>
              <p class="text-sm">
                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
                <span class="font-weight-bold">24%</span> this month
              </p>
            </div>
            <div class="card-body p-3">
              <div class="timeline timeline-one-side">
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-icons text-success text-gradient">notifications</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">300, Discharge this month</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">23 DEC 7:20 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-icons text-danger text-gradient">code</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Good service</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">24 DEC 11 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-icons text-info text-gradient">shopping_cart</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Admitted Rates</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">24 DEC 9:34 PM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-icons text-warning text-gradient">credit_card</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Indigency</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">20 DEC 2:20 AM</p>
                  </div>
                </div>
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="material-icons text-primary text-gradient">key</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Death</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">18 DEC 4:54 AM</p>
                  </div>
                </div>
                <div class="timeline-block">
                  <span class="timeline-step">
                    <i class="material-icons text-dark text-gradient">payments</i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">Healed</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">17 DEC</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div> -->
      </div>
      <?php
        include_once('footer.php');
      ?>
    </div>
  </main>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/chartjs.min.js"></script>
  <script>
    // var ctx = document.getElementById("chart-bars").getContext("2d");

    // new Chart(ctx, {
    //   type: "bar",
    //   data: {
    //     labels: ["M", "T", "W", "T", "F", "S", "S"],
    //     datasets: [{
    //       label: "Sales",
    //       tension: 0.4,
    //       borderWidth: 0,
    //       borderRadius: 4,
    //       borderSkipped: false,
    //       backgroundColor: "rgba(255, 255, 255, .8)",
    //       data: [50, 20, 10, 22, 50, 10, 40],
    //       maxBarThickness: 6
    //     }, ],
    //   },
    //   options: {
    //     responsive: true,
    //     maintainAspectRatio: false,
    //     plugins: {
    //       legend: {
    //         display: false,
    //       }
    //     },
    //     interaction: {
    //       intersect: false,
    //       mode: 'index',
    //     },
    //     scales: {
    //       y: {
    //         grid: {
    //           drawBorder: false,
    //           display: true,
    //           drawOnChartArea: true,
    //           drawTicks: false,
    //           borderDash: [5, 5],
    //           color: 'rgba(255, 255, 255, .2)'
    //         },
    //         ticks: {
    //           suggestedMin: 0,
    //           suggestedMax: 500,
    //           beginAtZero: true,
    //           padding: 10,
    //           font: {
    //             size: 14,
    //             weight: 300,
    //             family: "Roboto",
    //             style: 'normal',
    //             lineHeight: 2
    //           },
    //           color: "#fff"
    //         },
    //       },
    //       x: {
    //         grid: {
    //           drawBorder: false,
    //           display: true,
    //           drawOnChartArea: true,
    //           drawTicks: false,
    //           borderDash: [5, 5],
    //           color: 'rgba(255, 255, 255, .2)'
    //         },
    //         ticks: {
    //           display: true,
    //           color: '#f8f9fa',
    //           padding: 10,
    //           font: {
    //             size: 14,
    //             weight: 300,
    //             family: "Roboto",
    //             style: 'normal',
    //             lineHeight: 2
    //           },
    //         }
    //       },
    //     },
    //   },
    // });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    new Chart(ctx2, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Mobile apps",
          tension: 0,
          borderWidth: 0,
          pointRadius: 5,
          pointBackgroundColor: "rgba(255, 255, 255, .8)",
          pointBorderColor: "transparent",
          borderColor: "rgba(255, 255, 255, .8)",
          borderColor: "rgba(255, 255, 255, .8)",
          borderWidth: 4,
          backgroundColor: "transparent",
          fill: true,
          data: [50, 40, 300, 320, 500, 350, 200, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    var ctx3 = document.getElementById("chart-line-tasks").getContext("2d");

    new Chart(ctx3, {
      type: "line",
      data: {
        labels: <?php echo $healedLabels; ?>,
        datasets: [{
          label: "Healed Patients",
          tension: 0,
          borderWidth: 0,
          pointRadius: 5,
          pointBackgroundColor: "rgba(255, 255, 255, .8)",
          pointBorderColor: "transparent",
          borderColor: "rgba(255, 255, 255, .8)",
          borderWidth: 4,
          backgroundColor: "transparent",
          fill: true,
          data: <?php echo $healedData; ?>,
          maxBarThickness: 6
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#f8f9fa',
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
  </script>
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