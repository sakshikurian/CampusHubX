<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Student';

// FCRIT Campus coordinates
$campus_lat = 19.0655;
$campus_lng = 73.0072;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Map - CampusHubX</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .map-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .map-container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        #googleMap {
            width: 100%;
            height: 500px;
            border-radius: 10px;
            border: 3px solid #007bff;
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

        .map-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-item i {
            color: #007bff;
            margin-right: 10px;
            width: 20px;
        }

        .directions-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .directions-btn:hover {
            background: #218838;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="fas fa-compass me-2"></i>CampusHubX
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome,
                    <?php echo htmlspecialchars($username); ?>!
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-main">
        <!-- Back Button -->
        <a href="index.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i>Back to Navigation
        </a>

        <!-- Map Container -->
        <div class="map-container">
            <h2><i class="fas fa-map-marked-alt me-2"></i>Fr. C. Rodrigues Institute of Technology - Campus Map</h2>

            <!-- Google Map -->
            <iframe id="googleMap"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3771.1583889144956!2d73.00496507518995!3d19.065510482119366!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3be7c3d88e48c23d%3A0xf71af3d1a3381eb4!2sFr.%20Conceicao%20Rodrigues%20Institute%20of%20Technology!5e0!3m2!1sen!2sin!4v1707142857000!5m2!1sen!2sin"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>

            <!-- Campus Info -->
            <div class="map-info">
                <h4><i class="fas fa-info-circle me-2"></i>Campus Information</h4>

                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <strong>Address:</strong> Agnel Technical Education Complex, Sector 9-A, Vashi, Navi Mumbai,
                    Maharashtra - 400703
                </div>

                <div class="info-item">
                    <i class="fas fa-phone"></i>
                    <strong>Contact:</strong> (022) 27661924, 27660619, 27660714
                </div>

                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <strong>Email:</strong> principal@fcrit.ac.in
                </div>

                <div class="info-item">
                    <i class="fas fa-train"></i>
                    <strong>Nearest Station:</strong> Vashi Railway Station (2 km away)
                </div>

                <div class="info-item">
                    <i class="fas fa-bus"></i>
                    <strong>Bus Stop:</strong> Vashi Bus Station (0.5 km away)
                </div>

                <a href="https://www.google.com/maps/dir/?api=1&destination=19.0655,73.0072" target="_blank"
                    class="directions-btn">
                    <i class="fas fa-directions me-2"></i>Get Directions from My Location
                </a>
            </div>
        </div>

        <!-- Campus Buildings Overview -->
        <div class="map-container">
            <h3 class="mb-3">Campus Buildings & Facilities</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="info-item">
                        <i class="fas fa-book"></i>
                        <strong>Central Library:</strong> 33,000+ volumes, reading halls, journals
                    </div>
                    <div class="info-item">
                        <i class="fas fa-laptop"></i>
                        <strong>Computer Labs:</strong> State-of-the-art labs for all departments
                    </div>
                    <div class="info-item">
                        <i class="fas fa-flask"></i>
                        <strong>Engineering Labs:</strong> Well-equipped labs for practical training
                    </div>
                    <div class="info-item">
                        <i class="fas fa-home"></i>
                        <strong>Hostels:</strong> Separate boys and girls hostel facilities
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="info-item">
                        <i class="fas fa-utensils"></i>
                        <strong>Cafeteria:</strong> Hygienic food and refreshments
                    </div>
                    <div class="info-item">
                        <i class="fas fa-dumbbell"></i>
                        <strong>Gymnasium:</strong> Fitness center for students
                    </div>
                    <div class="info-item">
                        <i class="fas fa-medkit"></i>
                        <strong>Medical Center:</strong> On-campus health facilities
                    </div>
                    <div class="info-item">
                        <i class="fas fa-wifi"></i>
                        <strong>Wi-Fi Campus:</strong> Full campus connectivity
                    </div>
                </div>
            </div>
        </div>

        <!-- Departments -->
        <div class="map-container">
            <h3 class="mb-3">Academic Departments</h3>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-desktop"></i> Computer Engineering
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-cogs"></i> Mechanical Engineering
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-bolt"></i> Electrical Engineering
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-broadcast-tower"></i> Electronics & Telecommunication
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-server"></i> Information Technology
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <div class="info-item">
                        <i class="fas fa-graduation-cap"></i> Basic Sciences & Humanities
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>