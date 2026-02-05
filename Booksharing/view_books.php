<?php
include('db_connect.php');
session_start();

$message = '';

// 🔒 Database connection check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🗑️ Delete book (with safety)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM shared_books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $message = "<div class='alert alert-success text-center'>Book deleted successfully!</div>";
}

// 📘 Add new book with validation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_title = trim($_POST['book_title']);
    $author = trim($_POST['author']);
    $owner = trim($_POST['owner']);
    $contact = trim($_POST['contact']);
    $status = trim($_POST['status']);

    // Server-side validation
    if (empty($book_title) || empty($author) || empty($owner) || empty($contact) || empty($status)) {
        $message = "<div class='alert alert-danger text-center'>All fields are required!</div>";
    } elseif (!preg_match("/^[a-zA-Z0-9\s,'-]*$/", $book_title)) {
        $message = "<div class='alert alert-danger text-center'>Invalid book title format!</div>";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact)) {
        $message = "<div class='alert alert-danger text-center'>Please enter a valid 10-digit contact number!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO shared_books (book_title, author, owner, contact, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $book_title, $author, $owner, $contact, $status);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success text-center'>Book shared successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger text-center'>Error adding book. Please try again!</div>";
        }
        $stmt->close();
    }
}

// 📚 Fetch all books
$result = $conn->query("SELECT * FROM shared_books ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Books - CampusHubX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            font-family: 'Poppins', sans-serif;
        }

        h2 {
            color: #0056b3;
            font-weight: 700;
        }

        .table {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-share {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            border-radius: 50px;
            padding: 8px 20px;
        }

        .btn-share:hover {
            background-color: #0056b3;
        }

        .delete-icon {
            color: red;
            font-size: 22px;
            text-decoration: none;
        }

        .delete-icon:hover {
            color: darkred;
        }

        .status-available {
            color: green;
            font-weight: 600;
        }

        .status-taken {
            color: orange;
            font-weight: 600;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        .no-books {
            text-align: center;
            font-weight: 500;
            color: #555;
            padding: 30px 0;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📘 Shared Books</h2>
            <button class="btn btn-share" data-bs-toggle="modal" data-bs-target="#addBookModal">+ Share New</button>
        </div>

        <?= $message ?>

        <table class="table table-striped text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Book Title</th>
                    <th>Author</th>
                    <th>Owner</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['book_title']); ?></td>
                            <td><?= htmlspecialchars($row['author']); ?></td>
                            <td><?= htmlspecialchars($row['owner']); ?></td>
                            <td><?= htmlspecialchars($row['contact']); ?></td>
                            <td>
                                <span
                                    class="<?= strtolower($row['status']) == 'available' ? 'status-available' : 'status-taken'; ?>">
                                    <?= htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_books.php?delete=<?= $row['id']; ?>" class="delete-icon"
                                    onclick="return confirm('Are you sure you want to delete this book?');">🗑️</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-books">📚 No books shared yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 📘 Modal: Add New Book -->
    <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" onsubmit="return validateBookForm()">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookModalLabel">📚 Share a New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Book Title</label>
                        <input type="text" class="form-control" name="book_title" id="book_title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Author</label>
                        <input type="text" class="form-control" name="author" id="author" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Owner</label>
                        <input type="text" class="form-control" name="owner" id="owner" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" name="contact" id="contact" required maxlength="10">
                        <div class="form-text">Enter a valid 10-digit mobile number.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" id="status" required>
                            <option value="Available">Available</option>
                            <option value="Taken">Taken</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Share</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // 🧠 Client-side form validation
        function validateBookForm() {
            const title = document.getElementById("book_title").value.trim();
            const author = document.getElementById("author").value.trim();
            const owner = document.getElementById("owner").value.trim();
            const contact = document.getElementById("contact").value.trim();

            if (title === "" || author === "" || owner === "" || contact === "") {
                alert("All fields are required!");
                return false;
            }
            if (!/^\d{10}$/.test(contact)) {
                alert("Please enter a valid 10-digit contact number!");
                return false;
            }
            return true;
        }
    </script>

</body>

</html>