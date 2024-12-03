<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "connection/db.php";
include_once('head.php');

// Define treatment outcomes
$outcomes = [
    'CURED',
    'TREATMENT COMPLETE',
    'TREATMENT FAILED',
    'DIED',
    'LOST TO FOLLOW UP',
    'NOT EVALUATED'
];

// Add location filter query
$locationsQuery = "
    SELECT l.id, m.location as municipality, b.name as barangay 
    FROM locations l
    JOIN municipalities m ON l.municipality_id = m.id 
    JOIN barangays b ON l.barangay_id = b.id
    ORDER BY m.location, b.name";
$locations = $conn->query($locationsQuery);

// Get selected filters from URL parameters
$selectedMunicipality = isset($_GET['municipality']) ? (int)$_GET['municipality'] : 0;
$selectedAgeRange = isset($_GET['age_range']) ? $_GET['age_range'] : '';
$selectedGender = isset($_GET['gender']) ? $_GET['gender'] : '';

// Build filter conditions
$municipalityCondition = $selectedMunicipality > 0 ? "AND m.id = $selectedMunicipality" : "";
$genderCondition = $selectedGender ? "AND p.gender = '$selectedGender'" : "";

// Age range condition
$ageCondition = "";
if ($selectedAgeRange) {
    switch ($selectedAgeRange) {
        case '0-14':
            $ageCondition = "AND p.age BETWEEN 0 AND 14";
            break;
        case '15-24':
            $ageCondition = "AND p.age BETWEEN 15 AND 24";
            break;
        case '25-54':
            $ageCondition = "AND p.age BETWEEN 25 AND 54";
            break;
        case '55+':
            $ageCondition = "AND p.age >= 55";
            break;
    }
}

// Combine all conditions
$filterConditions = "$municipalityCondition $genderCondition $ageCondition";

// Get statistics for the cards
$thisWeekPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
    $filterConditions
")->fetch_assoc();

$totalConfined = $conn->query("
    SELECT COUNT(*) as count 
    FROM lab_results lr
    JOIN patients p ON lr.patient_id = p.id
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE lr.treatment_outcome IS NULL
    $filterConditions
")->fetch_assoc();

$newPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    $filterConditions
")->fetch_assoc();

$totalAnnualPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
    $filterConditions
")->fetch_assoc();

// Get recent patients for the table
$recentPatientsSql = "
    SELECT p.*, 
        m.location as municipality_name,
        b.name as barangay_name 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE 1=1 
    $filterConditions
    ORDER BY p.created_at DESC 
    LIMIT 10";

$recentPatients = $conn->query($recentPatientsSql);

// This Week's Patients comparison
$lastWeekPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 WEEK), INTERVAL 1 WEEK)
    AND p.created_at < DATE_SUB(NOW(), INTERVAL 1 WEEK)
    $filterConditions
")->fetch_assoc();

$weeklyChange = $lastWeekPatients['count'] > 0 
    ? round((($thisWeekPatients['count'] - $lastWeekPatients['count']) / $lastWeekPatients['count']) * 100, 1)
    : 0;

// Number of Confined comparison
$lastMonthConfined = $conn->query("
    SELECT COUNT(*) as count 
    FROM lab_results l
    JOIN patients p ON l.patient_id = p.id
    JOIN locations loc ON p.location_id = loc.id
    JOIN municipalities m ON loc.municipality_id = m.id
    JOIN barangays b ON loc.barangay_id = b.id
    WHERE l.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 MONTH), INTERVAL 1 MONTH)
    AND l.created_at < DATE_SUB(NOW(), INTERVAL 1 MONTH)
    AND l.treatment_outcome IS NULL
    $filterConditions
")->fetch_assoc();

$confinedChange = $lastMonthConfined['count'] > 0
    ? round((($totalConfined['count'] - $lastMonthConfined['count']) / $lastMonthConfined['count']) * 100, 1)
    : 0;

// New Patients (24h) comparison
$previousDayPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 24 HOUR), INTERVAL 24 HOUR)
    AND p.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    $filterConditions
")->fetch_assoc();

$dailyChange = $previousDayPatients['count'] > 0
    ? round((($newPatients['count'] - $previousDayPatients['count']) / $previousDayPatients['count']) * 100, 1)
    : 0;

// Annual Patients comparison
$previousYearPatients = $conn->query("
    SELECT COUNT(*) as count 
    FROM patients p
    JOIN locations l ON p.location_id = l.id
    JOIN municipalities m ON l.municipality_id = m.id
    JOIN barangays b ON l.barangay_id = b.id
    WHERE p.created_at >= DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 YEAR), INTERVAL 1 YEAR)
    AND p.created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
    $filterConditions
")->fetch_assoc();

$annualChange = $previousYearPatients['count'] > 0
    ? round((($totalAnnualPatients['count'] - $previousYearPatients['count']) / $previousYearPatients['count']) * 100, 1)
    : 0;

// Debugging: Print the query to ensure it's correct
$query = "
    SELECT 
        DATE_FORMAT(l.treatment_outcome_date, '%b') as month,
        DATE_FORMAT(l.treatment_outcome_date, '%Y-%m') as month_year,
        COUNT(*) as count
    FROM lab_results l
    JOIN patients p ON l.patient_id = p.id
    JOIN locations loc ON p.location_id = loc.id
    JOIN municipalities m ON loc.municipality_id = m.id
    JOIN barangays b ON loc.barangay_id = b.id
    WHERE l.treatment_outcome = 'CURED'
    AND l.treatment_outcome_date >= DATE_SUB(NOW(), INTERVAL 9 MONTH)
    $filterConditions
    GROUP BY DATE_FORMAT(l.treatment_outcome_date, '%Y-%m'), 
             DATE_FORMAT(l.treatment_outcome_date, '%b')
    ORDER BY month_year ASC";

// Execute the query
$result = $conn->query($query);

// Check for query errors
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Fetch the data
$healedPatients = $result->fetch_all(MYSQLI_ASSOC);

// Create arrays for labels and data
$healedLabels = [];
$healedData = [];

// Get last 12 months instead of 9
$months = [];
for ($i = 11; $i >= 0; $i--) {  // Changed from 8 to 11
    $monthDate = date('Y-m', strtotime("-$i months"));
    $monthLabel = date('M', strtotime("-$i months"));
    $months[$monthDate] = [
        'label' => $monthLabel,
        'count' => 0
    ];
}

// Fill in actual data
foreach ($healedPatients as $record) {
    if (isset($months[$record['month_year']])) {
        $months[$record['month_year']]['count'] = (int)$record['count'];
    }
}

// Convert to JSON for JavaScript
$healedLabels = json_encode(array_column($months, 'label'));
$healedData = json_encode(array_column($months, 'count'));

// Modify the treatment outcomes query to get 12 months and most recent cases
$treatmentOutcomesQuery = "
    SELECT 
        l.treatment_outcome,
        DATE_FORMAT(l.treatment_outcome_date, '%b') as month,
        DATE_FORMAT(l.treatment_outcome_date, '%Y-%m') as month_year,
        COUNT(*) as count
    FROM (
        SELECT 
            patient_id,
            treatment_outcome,
            treatment_outcome_date,
            ROW_NUMBER() OVER (PARTITION BY patient_id ORDER BY case_number DESC) as rn
        FROM lab_results
        WHERE treatment_outcome IS NOT NULL
    ) l
    JOIN patients p ON l.patient_id = p.id
    JOIN locations loc ON p.location_id = loc.id
    JOIN municipalities m ON loc.municipality_id = m.id
    JOIN barangays b ON loc.barangay_id = b.id
    WHERE l.rn = 1  -- Only get the most recent case for each patient
    AND l.treatment_outcome_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    $filterConditions
    GROUP BY 
        l.treatment_outcome, 
        DATE_FORMAT(l.treatment_outcome_date, '%b'),
        DATE_FORMAT(l.treatment_outcome_date, '%Y-%m')
    ORDER BY month_year ASC";

$treatmentResults = $conn->query($treatmentOutcomesQuery);

// Initialize data structure for all months and outcomes
$treatmentData = [];
foreach ($outcomes as $outcome) {
    $treatmentData[$outcome] = [];
    foreach ($months as $monthDate => $monthInfo) {
        $treatmentData[$outcome][$monthInfo['label']] = 0;
    }
}

// Fill in actual data
while ($row = $treatmentResults->fetch_assoc()) {
    if (isset($treatmentData[$row['treatment_outcome']][$row['month']])) {
        $treatmentData[$row['treatment_outcome']][$row['month']] = (int)$row['count'];
    }
}
// Convert to JSON for JavaScript
$monthLabels = json_encode(array_column($months, 'label'));
$treatmentDataJSON = json_encode($treatmentData);

// Update the municipality statistics query to use the correct join condition
$municipalityStatsQuery = "
    SELECT 
        m.id as municipality_id,
        m.location as municipality_name,
        COUNT(p.id) as patient_count,
        COUNT(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK) THEN 1 END) as weekly_count,
        COUNT(CASE WHEN p.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR) THEN 1 END) as yearly_count
    FROM municipalities m
    LEFT JOIN locations l ON l.municipality_id = m.id
    LEFT JOIN patients p ON p.location_id = l.id
    GROUP BY m.id, m.location
    ORDER BY m.location";

$municipalityStats = $conn->query($municipalityStatsQuery);

// Define arrays of icons and gradient colors to cycle through
$icons = [
    'location_city',
    'apartment',
    'home_work',
    'business',
    'domain',
    'house',
    'maps_home_work',
    'other_houses'
];

$gradients = [
    'primary' => 'bg-gradient-primary shadow-primary',
    'success' => 'bg-gradient-success shadow-success',
    'info' => 'bg-gradient-info shadow-info',
    'warning' => 'bg-gradient-warning shadow-warning',
    'danger' => 'bg-gradient-danger shadow-danger',
    'dark' => 'bg-gradient-dark shadow-dark',
    'secondary' => 'bg-gradient-secondary shadow-secondary',
    'light' => 'bg-gradient-light shadow-light'
];
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
                <div class="col-md-3 mb-3">
                    <label class="form-label">Municipality</label>
                    <select name="municipality" class="form-select" onchange="updateBarangays(this.value)">
                        <option value="0">All Municipalities</option>
                        <?php 
                        $municipalitiesQuery = "SELECT DISTINCT m.id, m.location FROM municipalities m ORDER BY m.location";
                        $municipalities = $conn->query($municipalitiesQuery);
                        while($mun = $municipalities->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $mun['id']; ?>" <?php echo $selectedMunicipality == $mun['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mun['location']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label class="form-label">Age Range</label>
                    <select name="age_range" class="form-select">
                        <option value="">All Ages</option>
                        <option value="0-14" <?php echo $selectedAgeRange == '0-14' ? 'selected' : ''; ?>>0-14 years</option>
                        <option value="15-24" <?php echo $selectedAgeRange == '15-24' ? 'selected' : ''; ?>>15-24 years</option>
                        <option value="25-54" <?php echo $selectedAgeRange == '25-54' ? 'selected' : ''; ?>>25-54 years</option>
                        <option value="55+" <?php echo $selectedAgeRange == '55+' ? 'selected' : ''; ?>>55+ years</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">All Genders</option>
                        <option value="M" <?php echo $selectedGender == 'M' ? 'selected' : ''; ?>>Male</option>
                        <option value="F" <?php echo $selectedGender == 'F' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn btn-primary w-100 mb-2">Apply Filters</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <?php 
        $iconIndex = 0;
        $gradientIndex = 0;
        $gradientClasses = array_values($gradients);
        
        while($stat = $municipalityStats->fetch_assoc()): 
            // Cycle through icons and gradients
            $icon = $icons[$iconIndex % count($icons)];
            $gradient = $gradientClasses[$gradientIndex % count($gradientClasses)];
            
            // Increment counters
            $iconIndex++;
            $gradientIndex++;
        ?>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card" onclick="filterByMunicipality(<?php echo $stat['municipality_id']; ?>, '<?php echo htmlspecialchars($stat['municipality_name'], ENT_QUOTES); ?>')" style="cursor: pointer;">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape <?php echo $gradient; ?> text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10"><?php echo $icon; ?></i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize"><?php echo htmlspecialchars($stat['municipality_name']); ?></p>
                        <h4 class="mb-0"><?php echo $stat['patient_count']; ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">
                        <span class="text-primary text-sm font-weight-bolder me-2">
                            <?php echo $stat['weekly_count']; ?> this week
                        </span>
                        <span class="text-success text-sm font-weight-bolder">
                            <?php echo $stat['yearly_count']; ?> this year
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
      </div>
      <div class="row mt-4">
        <?php foreach ($outcomes as $outcome): ?>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card z-index-2">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
              <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                <div class="chart">
                  <canvas id="chart-<?php echo strtolower(str_replace(' ', '-', $outcome)); ?>" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
            </div>
            <div class="card-body">
              <h6 class="mb-0"><?php echo ucwords(strtolower($outcome)); ?></h6>
              <p class="text-sm">Monthly statistics</p>
              <hr class="dark horizontal">
              <div class="d-flex">
                <i class="material-icons text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm">updated just now</p>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="row mt-4">
        <?php while($stat = $municipalityStats->fetch_assoc()): ?>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">location_city</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize"><?php echo htmlspecialchars($stat['municipality_name']); ?></p>
                        <h4 class="mb-0"><?php echo $stat['patient_count']; ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">
                    <p class="mb-0">
                        <span class="text-primary text-sm font-weight-bolder me-2">
                            <?php echo $stat['weekly_count']; ?> this week
                        </span>
                        <span class="text-success text-sm font-weight-bolder">
                            <?php echo $stat['yearly_count']; ?> this year
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
      </div>
      <div class="row mb-4">
        <div class="col-lg-12 col-md-6 mb-md-0 mb-4">
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
    // Initialize charts for each treatment outcome
    const treatmentData = <?php echo $treatmentDataJSON; ?>;
    const monthLabels = <?php echo $monthLabels; ?>;
    console.log(treatmentData);
    const chartColors = {
        'CURED': {
            border: 'rgba(52, 152, 219, 1)',  // Soft blue
            background: 'rgba(52, 152, 219, 0.1)'
        },
        'TREATMENT COMPLETE': {
            border: 'rgba(46, 204, 113, 1)',  // Soft green
            background: 'rgba(46, 204, 113, 0.1)'
        },
        'TREATMENT FAILED': {
            border: 'rgba(231, 76, 60, 1)',   // Soft red
            background: 'rgba(231, 76, 60, 0.1)'
        },
        'DIED': {
            border: 'rgba(149, 165, 166, 1)', // Soft gray
            background: 'rgba(149, 165, 166, 0.1)'
        },
        'LOST TO FOLLOW UP': {
            border: 'rgba(243, 156, 18, 1)',  // Soft orange
            background: 'rgba(243, 156, 18, 0.1)'
        },
        'NOT EVALUATED': {
            border: 'rgba(155, 89, 182, 1)',  // Soft purple
            background: 'rgba(155, 89, 182, 0.1)'
        }
    };

    // Create charts for each outcome
    Object.entries(treatmentData).forEach(([outcome, data]) => {
        const canvasId = `chart-${outcome.toLowerCase().replace(/ /g, '-')}`;
        const ctx = document.getElementById(canvasId);
        
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: outcome,
                        data: Object.values(data),
                        borderColor: chartColors[outcome].border,
                        backgroundColor: chartColors[outcome].background,
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: chartColors[outcome].border,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: '#fff'  // Make legend text white
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5],
                                color: 'rgba(255,255,255,0.1)'  // Make grid lines lighter
                            },
                            ticks: {
                                color: '#fff'  // Make y-axis labels white
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false
                            },
                            ticks: {
                                color: '#fff'  // Make x-axis labels white
                            }
                        }
                    }
                }
            });
        }
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
  <!-- Add this CSS to style the chart containers -->
  <style>
  .chart {
      background: linear-gradient(195deg, #42424a 0%, #191919 100%);
      border-radius: 6px;
      padding: 10px;
  }
  </style>
  <script>
  function filterByMunicipality(municipalityId, municipalityName) {
      // Create a form and submit it
      const form = document.createElement('form');
      form.method = 'GET';
      form.action = 'dashboard.php';

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'municipality';
      input.value = municipalityId;

      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
  }
  </script>
  <script>
  function updateBarangays(municipalityId) {
      const barangaySelect = document.getElementById('barangaySelect');
      barangaySelect.innerHTML = '<option value="0">All Barangays</option>';
      
      if (municipalityId > 0) {
          fetch(`get_barangays.php?municipality_id=${municipalityId}`)
              .then(response => response.json())
              .then(barangays => {
                  barangays.forEach(barangay => {
                      const option = document.createElement('option');
                      option.value = barangay.id;
                      option.textContent = barangay.name;
                      barangaySelect.appendChild(option);
                  });
              });
      }
  }
  </script>
</body>

</html>