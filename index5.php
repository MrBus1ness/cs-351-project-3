<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'proj3'; 
$user = 'hunter'; 
$pass = 'hunter';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle student search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT student_id, student_name, gpa, highschool FROM graduates WHERE student_id LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['student_id']) && isset($_POST['student_name']) && isset($_POST['gpa']) && isset($_POST['highschool'])) {
        // Insert new entry
        $student_id = htmlspecialchars($_POST['student_id']);
        $student_name = htmlspecialchars($_POST['student_name']);
        $gpa = htmlspecialchars($_POST['gpa']);
        $highschool = htmlspecialchars($_POST['highschool']);
        
        $insert_sql = 'INSERT INTO graduates (student_id, student_name, gpa, highschool) VALUES (:student_id, :student_name, :gpa, :highschool)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['student_id' => $student_id, 'student_name' => $student_name, 'gpa' => $gpa, 'highschool' => $highschool]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM graduates WHERE student_id = :student_id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['student_id' => $delete_id]);
    } elseif (isset($_POST['update_id']) && isset($_POST['new_gpa'])) {
        // Update the gpa field
        $update_id = (int) $_POST['update_id'];
        $new_gpa = htmlspecialchars($_POST['new_gpa']);

        $update_sql = 'UPDATE graduates SET gpa = :new_gpa WHERE student_id = :student_id';
        $stmt_update = $pdo->prepare($update_sql);
        $stmt_update->execute(['student_id' => $update_id, 'new_gpa' => $new_gpa]);
    }

}

// Get all students for main table
$sql = 'SELECT student_id, student_name, gpa, highschool FROM graduates';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>College Offers</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">College Offer Distribution System</h1>
        <h3 class="hero-subtitle">Send High School Graduates Offers To Our College</h3>
        
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a Student</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Student ID:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>GPA</th>
                                    <th>Highschool</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gpa']); ?></td>
                                    <td><?php echo htmlspecialchars($row['highschool']); ?></td>
                                    <td>
                                        <form action="index1.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['student_id']; ?>">
                                            <input type="submit" value="Deny!">
                                        </form>
                                    </td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" id="update_id" name="update_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                            <label for="new_gpa">New GPA:</label>
                                            <input type="text" id="new_gpa" name="new_gpa">
                                            <input type="submit" value="Update GPA">
                                        </form>
                                    </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No students found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Books in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>GPA</th>
                    <th>Highschool</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['gpa']); ?></td>
                    <td><?php echo htmlspecialchars($row['highschool']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['student_id']; ?>">
                            <input type="submit" value="Deny!">
                        </form>
                    </td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" id="update_id" name="update_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                            <label for="new_gpa">New GPA:</label>
                            <input type="text" id="new_gpa" name="new_gpa">
                            <input type="submit" value="Update GPA">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Add Student</h2>
        <form action="index5.php" method="post">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" required>
            <br><br>
            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" required>
            <br><br>
            <label for="gpa">GPA:</label>
            <input type="text" id="gpa" name="gpa" required>
            <br><br>
            <label for="highschool">Highschool:</label>
            <input type="text" id="highschool" name="highschool" required>
            <br><br>
            <input type="submit" value="Add Entry">
        </form>
    </div>
</body>
</html>