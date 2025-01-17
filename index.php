<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pro_1todojs";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$edit_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_btn'])) {
        $task = trim($_POST['task_name']);
        $emp_names = trim($_POST['emp_names']);
        $task_date = trim($_POST['task_date']);
        $deadline_date = trim($_POST['deadline_date']);
        $status = trim($_POST['status']);
        if ($task !== '' && $task_date !== '' && $deadline_date !== '') {
            $task = $conn->real_escape_string($task);
            $emp_names = $conn->real_escape_string($emp_names);
            $task_date = $conn->real_escape_string($task_date);
            $deadline_date = $conn->real_escape_string($deadline_date);
            $status = $conn->real_escape_string($status);
            $sql = "INSERT INTO tasks (task_name, emp_names, task_date, deadline_date, status) VALUES ('$task', '$emp_names', '$task_date', '$deadline_date', '$status')";
            $conn->query($sql);
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (isset($_POST['delete_btn'])) {
        $id = intval($_POST['task_id']);
        $sql = "DELETE FROM tasks WHERE id=$id";
        $conn->query($sql);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (isset($_POST['edit_btn'])) {
        $edit_id = intval($_POST['task_id']);
    }
    if (isset($_POST['update_btn'])) {
        $id = intval($_POST['task_id']);
        $task = trim($_POST['task_name']);
        $emp_names = trim($_POST['emp_names']);
        $task_date = trim($_POST['task_date']);
        $deadline_date = trim($_POST['deadline_date']);
        $status = trim($_POST['status']);
        if ($task !== '' && $task_date !== '' && $deadline_date !== '') {
            $task = $conn->real_escape_string($task);
            $emp_names = $conn->real_escape_string($emp_names);
            $task_date = $conn->real_escape_string($task_date);
            $deadline_date = $conn->real_escape_string($deadline_date);
            $status = $conn->real_escape_string($status);
            $sql = "UPDATE tasks SET task_name='$task', emp_names='$emp_names', task_date='$task_date', deadline_date='$deadline_date', status='$status', updated_at=NOW() WHERE id=$id";
            $conn->query($sql);
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (isset($_POST['export_csv'])) {
        $filename = "tasks_export_" . date('Y-m-d') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        $output = fopen('php://output', 'w');
        fputcsv($output, array('SR NO.', 'Task', 'Date', 'Deadline', 'Emp Names', 'Status', 'Last Updated'));
        $export_sql = "SELECT * FROM tasks";
        $export_result = $conn->query($export_sql);
        if ($export_result->num_rows > 0) {
            $sr_no = 1;
            while($row = $export_result->fetch_assoc()) {
                fputcsv($output, array(
                    $sr_no++,
                    $row['task_name'],
                    $row['task_date'],
                    $row['deadline_date'],
                    $row['emp_names'],
                    $row['status'],
                    $row['updated_at']
                ));
            }
        }
        fclose($output);
        exit();
    }
}

$sql = "SELECT * FROM tasks";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Task Management</title>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; }
        form { margin: 0; }
        .action-btn { margin-right: 5px; }
        .export-btn { margin-bottom: 10px; }
        #toggle_form_btn { margin-bottom: 10px; }
        #task_form { display: block; }
        .container { width: 90%; margin: 20px auto; }
        .form-group { margin-bottom: 10px; }
        input, select { padding: 5px; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task Management</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="logout.php">Logout</a></p>
        <button id="toggle_form_btn">Hide Form</button>
        <div id="task_form">
            <form method="post" action="">
                <div class="form-group">
                    <input type="text" name="task_name" placeholder="Task Name" required />
                </div>
                <div class="form-group">
                    <input type="text" name="emp_names" placeholder="Emp Names" />
                </div>
                <div class="form-group">
                    <input type="date" name="task_date" placeholder="Task Date" required />
                </div>
                <div class="form-group">
                    <input type="date" name="deadline_date" placeholder="Deadline Date" required />
                </div>
                <div class="form-group">
                    <select name="status">
                        <option value="---" selected>---</option>
                        <option value="Completed">Completed</option>
                        <option value="Approval Pending">Approval Pending</option>
                        <option value="Working">Working</option>
                        <option value="Assets Pending">Assets Pending</option>
                        <option value="Content Pending">Content Pending</option>
                    </select>
                </div>
                <input type="submit" name="submit_btn" value="Add Task" />
            </form>
        </div>
        <form method="post" action="">
            <input type="submit" name="export_csv" value="Export to CSV" class="export-btn" />
        </form>
        <h2>Task List</h2>
        <table id="tasks_table" class="display">
            <thead>
                <tr>
                    <th>SR NO.</th>
                    <th>Task</th>
                    <th>Date</th>
                    <th>Deadline</th>
                    <th>Emp Names</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $sr_no = 1;
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $sr_no++ . "</td>";
                        if ($edit_id == $row['id']) {
                            echo "<td>
                                    <form method='post'>
                                        <input type='hidden' name='task_id' value='" . $row['id'] . "' />
                                        <input type='text' name='task_name' value='" . htmlspecialchars($row['task_name']) . "' required />
                                </td>
                                <td>
                                        <input type='date' name='task_date' value='" . htmlspecialchars($row['task_date']) . "' required />
                                </td>
                                <td>
                                        <input type='date' name='deadline_date' value='" . htmlspecialchars($row['deadline_date']) . "' required />
                                </td>
                                <td>
                                        <input type='text' name='emp_names' value='" . htmlspecialchars($row['emp_names']) . "' />
                                </td>
                                <td>
                                        <select name='status'>
                                            <option value='---' " . ($row['status'] == '---' ? 'selected' : '') . ">---</option>
                                            <option value='Completed' " . ($row['status'] == 'Completed' ? 'selected' : '') . ">Completed</option>
                                            <option value='Approval Pending' " . ($row['status'] == 'Approval Pending' ? 'selected' : '') . ">Approval Pending</option>
                                            <option value='Working' " . ($row['status'] == 'Working' ? 'selected' : '') . ">Working</option>
                                            <option value='Assets Pending' " . ($row['status'] == 'Assets Pending' ? 'selected' : '') . ">Assets Pending</option>
                                            <option value='Content Pending' " . ($row['status'] == 'Content Pending' ? 'selected' : '') . ">Content Pending</option>
                                        </select>
                                </td>
                                <td>" . htmlspecialchars($row['updated_at']) . "</td>
                                <td>
                                        <input type='submit' name='update_btn' value='Save' />
                                    </form>
                                </td>";
                        } else {
                            echo "<td>" . htmlspecialchars($row['task_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['task_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['deadline_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['emp_names']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
                            echo "<td>
                                    <form method='post' style='display:inline;'>
                                        <input type='hidden' name='task_id' value='" . $row['id'] . "' />
                                        <input type='submit' name='delete_btn' value='Delete' class='action-btn' onclick=\"return confirm('Are you sure you want to delete this task?')\" />
                                    </form>
                                    <form method='post' style='display:inline; margin-left:5px;'>
                                        <input type='hidden' name='task_id' value='" . $row['id'] . "' />
                                        <input type='submit' name='edit_btn' value='Update' class='action-btn' />
                                    </form>
                                </td>";
                        }
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function(){
            $('#tasks_table').DataTable();

            $('#toggle_form_btn').click(function(){
                $('#task_form').toggle();
                if($('#task_form').is(':visible')){
                    $('#toggle_form_btn').text('Hide Form');
                } else {
                    $('#toggle_form_btn').text('Show Form');
                }
            });
        });
    </script>
</body>
</html>
