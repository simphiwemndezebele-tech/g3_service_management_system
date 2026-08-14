<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

// Customers
$total_customers = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers")
);

// Machines
$total_machines = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM machines")
);

// Technicians
$total_technicians = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM technicians")
);

// Pending Requests
$pending_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='Pending'")
);

// In Progress Requests
$progress_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='In Progress'")
);

// Completed Requests
$completed_requests = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM service_requests WHERE status='Completed'")
);
$total_requests = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM service_requests")
);
// Total Job Cards
$total_job_cards = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM job_cards")
);
// Open Job Cards
$open_job_cards = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM job_cards
     WHERE status IN ('Open', 'In Progress')")
);
// Aging Service Requests
$aging_requests = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM service_requests
     WHERE status IN ('Pending', 'In Progress')
     AND request_date < DATE_SUB(CURDATE(), INTERVAL 3 DAY)")
);
// Recent Service Requests
$recent_requests = mysqli_query($conn, "

SELECT

sr.request_number,
m.asset_number,
sr.status

FROM service_requests sr

INNER JOIN machines m

ON sr.machine_id=m.id

ORDER BY sr.id DESC

LIMIT 5

");

// Recent Machines

$recent_machines = mysqli_query($conn, "

SELECT

asset_number,
machine_model

FROM machines

ORDER BY id DESC

LIMIT 5

");
// Recent Job Cards

$recent_job_cards = mysqli_query($conn, "

SELECT

jc.job_card_number,
c.customer_name,
t.full_name AS technician_name,
jc.status

FROM job_cards jc

LEFT JOIN customers c
ON jc.customer_id = c.id

LEFT JOIN technicians t
ON jc.technician_id = t.id

ORDER BY jc.id DESC

LIMIT 5

");
// Requests by Status
$pending = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM service_requests WHERE status='Pending'"));

$progress = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM service_requests WHERE status='In Progress'"));

$completed = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) AS total FROM service_requests WHERE status='Completed'"));
?>

<div class="main-content">

<h1>Dashboard</h1>
<h3>Welcome back, <?php echo $_SESSION['full_name']; ?> 👋</h3>

<p>Here's today's service overview.</p>

<div class="dashboard-cards">

<a href="../customers/view_customers.php" class="card-link">
    <div class="card">
        <h2>👥 Customers</h2>
        <h1 class="customers-count"><?php echo $total_customers['total']; ?></h1>
    </div>
</a>

<a href="../machines/view_machines.php" class="card-link">
    <div class="card">
        <h2>🖨 Machines</h2>
        <h1 class="machines-count"><?php echo $total_machines['total']; ?></h1>
    </div>
</a>

<?php if ($_SESSION['role'] === 'Manager'): ?>

<a href="../technicians/view_technicians.php" class="card-link">

    <div class="card">

        <h2>👨‍🔧 Technicians</h2>

        <h1 class="technicians-count">
            <?php echo $total_technicians['total']; ?>
        </h1>

    </div>

</a>

<?php endif; ?>

<a href="../service_requests/view_requests.php?status=Pending" class="card-link">
    <div class="card">
        <h2>🟡 Pending</h2>
        <h1 class="pending-count"><?php echo $pending_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=In+Progress" class="card-link">
    <div class="card">
        <h2>🔵 In Progress</h2>
        <h1 class="progress-count"><?php echo $progress_requests['total']; ?></h1>
    </div>
</a>

<a href="../service_requests/view_requests.php?status=Completed" class="card-link">
    <div class="card">
        <h2>🟢 Completed</h2>
        <h1 class="completed-count"><?php echo $completed_requests['total']; ?></h1>
    </div>
</a>

<a href="../job_cards/view_job_cards.php" class="card-link">
    <div class="card">
        <h2>📋 Job Cards</h2>
        <h1 class="job-cards-count">
            <?php echo $total_job_cards['total']; ?>
        </h1>
    </div>
</a>

</div>
<hr><br>

<div style="display:flex;justify-content:center;gap:30px;flex-wrap:wrap;margin-top:20px;">

    <div class="card" style="flex:1;min-width:500px;max-width:650px;padding:20px;">
        <h3>System Overview</h3>
        <div style="height:300px;">
    <canvas id="overviewChart"></canvas>
</div>
    </div>

   <div class="card" style="flex:1;min-width:500px;max-width:650px;padding:20px;">
        <h3>Service Requests Status</h3>
       <div style="height:300px;">
    <canvas id="requestChart"></canvas>
</div>
    </div>

</div>

<div class="dashboard-sections">

<div class="dashboard-box">

<h2>🛠 Recent Service Requests</h2>

<table>

<tr>

<th>Request</th>

<th>Machine</th>

<th>Status</th>

</tr>

<?php while($row=mysqli_fetch_assoc($recent_requests)){ ?>

<tr>

<td><?php echo $row['request_number']; ?></td>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['status']; ?></td>

</tr>

<?php } ?>

</table>

</div>

<div class="dashboard-box">

<h2>🖨 Recently Added Machines</h2>

<table>

<tr>

<th>Asset No.</th>

<th>Model</th>

</tr>

<?php while($row=mysqli_fetch_assoc($recent_machines)){ ?>

<tr>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['machine_model']; ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>
<div class="dashboard-sections">

<div class="dashboard-box">

<h2>📋 Recent Job Cards</h2>

<table>

<tr>

<th>Job Card</th>
<th>Customer</th>
<th>Technician</th>
<th>Status</th>

</tr>

<?php

if(mysqli_num_rows($recent_job_cards) > 0){

    while($row = mysqli_fetch_assoc($recent_job_cards)){

?>

<tr>

<td>
<?php echo htmlspecialchars($row['job_card_number']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['customer_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['technician_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['status']); ?>
</td>

</tr>

<?php

    }

}else{

?>

<tr>

<td colspan="4" style="text-align:center;">

No Job Cards Found.

</td>

</tr>

<?php

}

?>

</table>

</div>

</div>
<!-- ==============================
     ATTENTION REQUIRED
================================ -->

<div class="dashboard-box attention-section">

    <h2>⚠️ Attention Required</h2>

    <div class="attention-cards">


        <!-- Pending Requests -->

        <a href="../service_requests/view_requests.php?status=Pending"
           class="attention-card">

            <div class="attention-icon">
                🟡
            </div>

            <div class="attention-info">

                <h3>Pending Requests</h3>

                <h1>
                    <?php echo $pending_requests['total']; ?>
                </h1>

                <p>
                    Awaiting attention
                </p>

            </div>

        </a>



        <!-- In Progress -->

        <a href="../service_requests/view_requests.php?status=In+Progress"
           class="attention-card">

            <div class="attention-icon">
                🔵
            </div>

            <div class="attention-info">

                <h3>In Progress</h3>

                <h1>
                    <?php echo $progress_requests['total']; ?>
                </h1>

                <p>
                    Currently being handled
                </p>

            </div>

        </a>



        <!-- Open Job Cards -->

        <a href="../job_cards/view_job_cards.php"
           class="attention-card">

            <div class="attention-icon">
                📋
            </div>

            <div class="attention-info">

                <h3>Open Job Cards</h3>

                <h1>
                    <?php echo $open_job_cards['total']; ?>
                </h1>

                <p>
                    Require completion
                </p>

            </div>

        </a>

        <a href="../service_requests/view_requests.php?aging=1"
   class="attention-card">

    <div class="attention-icon">
        ⚠️
    </div>

    <div class="attention-info">

        <h3>Aging Requests</h3>

        <h1>
            <?php echo $aging_requests['total']; ?>
        </h1>

        <p>
            Older than 3 days
        </p>

    </div>

</a>


    </div>

</div>

</div>

<?php
include("../includes/footer.php");
?>
<script>

// Doughnut Chart
new Chart(document.getElementById('overviewChart'),{

    type:'doughnut',

    data:{
        labels:['Customers','Machines','Technicians','Requests'],
        datasets:[{
            data:[
                <?php echo $total_customers['total']; ?>,
                <?php echo $total_machines['total']; ?>,
                <?php echo $total_technicians['total']; ?>,
                <?php echo $total_requests['total']; ?>
            ]
        }]
    },

    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                position:'bottom'
            }
        }
    }

});

// Bar Chart

new Chart(document.getElementById('requestChart'),{

    type:'bar',

    data:{
        labels:['Pending','In Progress','Completed'],
        datasets:[{
            label:'Requests',
            data:[
                <?php echo $pending['total']; ?>,
                <?php echo $progress['total']; ?>,
                <?php echo $completed['total']; ?>
            ],
            borderWidth:1
        }]
    },

    options:{
        responsive:true,
        maintainAspectRatio:false,
        scales:{
            y:{
                beginAtZero:true
            }
        },
        plugins:{
            legend:{
                display:false
            }
        }
    }

});

</script>