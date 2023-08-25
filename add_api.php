<?php
require './connection.php';
// Retrieve form data
$mail = $_POST['mail'];
$apiKey = $_POST['api_key'];
$status = 0;
// Prepare and execute the SQL query to insert data into the table
$stmt = $conn->prepare("INSERT INTO api_keys (mail, api_key, status) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $mail, $apiKey, $status);

if ($stmt->execute()) {
    echo "API Key saved successfully!";
}
// Close the prepared statement and database connection
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Save API Keys</title>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
</head>

<body>
    <h2>Enter Email and API Key</h2>
    <a href='https://the-odds-api.com/account/' target="_blank">https://the-odds-api.com/account/</a>
    <form method="POST" action="">
        <label>Email:</label>
        <input type="email" name="mail" required><br><br>

        <label>API Key:</label>
        <input type="text" name="api_key" required><br><br>

        <input type="submit" value="Save API Key">
    </form>

    <table id="myTable">
        <thead>
            <tr>
                <th>Id</th>
                <th>Email</th>
                <th>API Key</th>
            </tr>
        </thead>
        <tbody>
            <!-- PHP code to generate table rows dynamically -->
            <?php

            // Fetch data from the database
            $sql = "SELECT * FROM api_keys ORDER BY id DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    if ($row['status'] == 1) {
                        $color = 'green';
                        $key = $row['api_key'] . ' (ACTIVE)';
                    } else {
                        $color = 'red';
                        $key = $row['api_key'];
                    }
                    echo "<tr style='background:" . $color . ";color:" . 'white' . "'>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['mail'] . "</td>";
                    echo "<td>" . $key . "</td>";
                    echo "</tr>";
                }
            }
            ?>
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#myTable').DataTable({
                "order": []
            });
        });
    </script>

</body>

</html>