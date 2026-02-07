<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Student';

// Sample location data (hardcoded for UI-only version)
$locations = [
    ['name' => 'Main Library', 'icon' => 'fa-book', 'building' => 'Building A', 'description' => 'Study halls and research resources'],
    ['name' => 'Student Center', 'icon' => 'fa-users', 'building' => 'Building B', 'description' => 'Recreation and student activities'],
    ['name' => 'Computer Lab', 'icon' => 'fa-laptop', 'building' => 'Building C', 'description' => 'Mac and PC workstations'],
    ['name' => 'Cafeteria', 'icon' => 'fa-utensils', 'building' => 'Building D', 'description' => 'Dining and food court'],
    ['name' => 'Admin Office', 'icon' => 'fa-briefcase', 'building' => 'Building E', 'description' => 'Registration and records'],
    ['name' => 'Sports Complex', 'icon' => 'fa-dumbbell', 'building' => 'Building F', 'description' => 'Gym and athletic facilities'],
    ['name' => 'Science Block', 'icon' => 'fa-flask', 'building' => 'Building G', 'description' => 'Labs and lecture halls'],
    ['name' => 'Arts Building', 'icon' => 'fa-palette', 'building' => 'Building H', 'description' => 'Studios and performance spaces']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Navigation - CampusHubX</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #cfe2f3;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #007bff !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container-main {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .header-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-section h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .header-section p {
            color: #666;
            margin: 0;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .search-input {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
        }

        .search-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .location-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .location-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .location-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .location-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 24px;
            color: white;
        }

        .location-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .location-building {
            font-size: 14px;
            color: #007bff;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .location-description {
            font-size: 13px;
            color: #666;
            line-height: 1.4;
        }

        .map-view-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .map-view-btn:hover {
            background: #0056b3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .back-btn {
            background: white;
            color: #007bff;
            border: 2px solid #007bff;
            padding: 10px 25px;
            border-radius: 20px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #007bff;
            color: white;
        }

        .coming-soon-badge {
            background: #ffc107;
            color: #333;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="../dashboard.php">
                CampusHubX
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome,
                    <?= htmlspecialchars($_SESSION['user_name']); ?>!
                </span>

                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-main">

        <!-- Header Section -->
        <div class="header-section">
            <h1><i class="fas fa-map-marked-alt me-2"></i>Campus Navigation</h1>
            <p>Find your way around campus - Discover buildings, facilities, and services</p>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control search-input border-start-0"
                    placeholder="Search for buildings, facilities, or services..." id="searchInput"
                    onkeyup="filterLocations()">
            </div>
            <small class="text-muted d-block mt-2">
                <i class="fas fa-info-circle"></i> Search is UI-only for now
                <span class="coming-soon-badge">DATABASE COMING SOON</span>
            </small>
        </div>

        <!-- Map View Button -->
        <div class="text-center mb-4 map-view-btn">

            <i class=" fas fa-map me-2"></i>View Campus Map

        </div>

        <!-- Locations Grid -->
        <div class="location-grid" id="locationGrid">
            <?php foreach ($locations as $location): ?>
                <div class="location-card" data-name="<?php echo strtolower($location['name']); ?>">
                    <div class="location-icon">
                        <i class="fas <?php echo $location['icon']; ?>"></i>
                    </div>
                    <div class="location-name">
                        <?php echo htmlspecialchars($location['name']); ?>
                    </div>
                    <div class="location-building">
                        <?php echo htmlspecialchars($location['building']); ?>
                    </div>
                    <div class="location-description">
                        <?php echo htmlspecialchars($location['description']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" style="display: none;" class="text-center">
            <div class="alert alert-info">
                <i class="fas fa-search me-2"></i>No locations found. Try a different search term.
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Search Filtering Script -->
    <script>
        function filterLocations() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.location-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const locationName = card.getAttribute('data-name');
                if (locationName.includes(searchInput)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide no results message
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0 && searchInput !== '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
    </script>
</body>

</html>