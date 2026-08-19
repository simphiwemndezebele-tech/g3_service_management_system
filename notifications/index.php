<?php

session_start();

require_once("../includes/permissions.php");

// All logged-in roles can view their own notifications
requireRole(['Manager', 'Reception', 'Technician']);

include("../config/db.php");

$user_id = $_SESSION['user_id'] ?? 0;


/* ==============================
   Mark Notification As Read
   ============================== */

if (isset($_GET['read'])) {

    $notification_id = (int) $_GET['read'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE notifications
         SET is_read = 1
         WHERE id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $notification_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit();
}


/* ==============================
   Mark All Notifications As Read
   ============================== */

if (isset($_GET['read_all'])) {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE notifications
         SET is_read = 1
         WHERE user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit();
}


/* ==============================
   Get Notifications
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        title,
        message,
        type,
        reference_id,
        is_read,
        created_at
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

include("../includes/header.php");
include("../includes/sidebar.php");

?>

<div class="main-content">

    <h1>🔔 Notifications</h1>

    <p>
        View your system notifications and updates.
    </p>

    <br>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <a
            href="index.php?read_all=1"
            class="btn btn-add"
            onclick="return confirm('Mark all notifications as read?');"
        >
            ✅ Mark All as Read
        </a>

        <br><br>


        <?php while ($notification = mysqli_fetch_assoc($result)): ?>

            <div
                class="card"
                style="
                    margin-bottom:15px;
                    border-left:
                    5px solid
                    <?php
                    echo $notification['is_read']
                        ? '#ccc'
                        : '#007bff';
                    ?>;
                "
            >

                <h2>

                    <?php
                    echo htmlspecialchars(
                        $notification['title']
                    );
                    ?>

                    <?php if (!$notification['is_read']): ?>

                        <span
                            style="
                                font-size:12px;
                                background:#007bff;
                                color:white;
                                padding:4px 8px;
                                border-radius:10px;
                            "
                        >
                            NEW
                        </span>

                    <?php endif; ?>

                </h2>


                <p>

                    <?php
                    echo htmlspecialchars(
                        $notification['message']
                    );
                    ?>

                </p>


                <small>

                    <?php
                    echo date(
                        "d M Y, h:i A",
                        strtotime(
                            $notification['created_at']
                        )
                    );
                    ?>

                </small>


                <?php if (!$notification['is_read']): ?>

                    <br><br>

                    <a
                        href="index.php?read=<?php echo $notification['id']; ?>"
                        class="btn btn-search"
                    >
                        ✔ Mark as Read
                    </a>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>


    <?php else: ?>

        <div class="card">

            <h2>🎉 No Notifications</h2>

            <p>
                You currently have no notifications.
            </p>

        </div>

    <?php endif; ?>

</div>

<?php include("../includes/footer.php"); ?>