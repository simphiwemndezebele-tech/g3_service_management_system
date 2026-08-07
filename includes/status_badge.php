<?php

function statusBadge($status)
{
    switch ($status) {

        case "Pending":
            return "<span class='badge badge-warning'>🟡 Pending</span>";

        case "In Progress":
            return "<span class='badge badge-primary'>🔵 In Progress</span>";

        case "Completed":
            return "<span class='badge badge-success'>🟢 Completed</span>";

        case "Available":
            return "<span class='badge badge-success'>🟢 Available</span>";

        case "Busy":
            return "<span class='badge badge-primary'>🔵 Busy</span>";

        case "On Leave":
            return "<span class='badge badge-danger'>🔴 On Leave</span>";

        default:
            return "<span class='badge'>$status</span>";
    }
}
?>