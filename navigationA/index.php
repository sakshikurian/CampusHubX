<?php
session_start();

$output = "";
$userId = (int) $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
// ROOMS
$rooms = [
"AX-501A","AX-501B","AX-502","AX-503A","AX-503B",
"AX-504","AX-505A","AX-505B","AX-506","AX-507",
"AX-508","AX-509","AX-510","AX-511","AX-512",
"AX-513A","AX-513B","AX-514A","AX-514B",
"AX-515A","AX-515B","AX-516B"
];

// RUN DIJKSTRA WHEN FORM SUBMITTED
if(isset($_POST['find']))
{
    $map = array_flip($rooms);

    $src = $map[$_POST['source']];
    $dest = $map[$_POST['destination']];

    // WINDOWS → use dijkstra.exe
    $command = "dijkstra $src $dest";

    $output = shell_exec($command);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Campus Navigation - CampusHubX</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
body { background:#cfe2f3; }
:root {
            --brand: #0f4c81;
            --brand-dark: #0a2f4f;
            --brand-soft: #e9f4ff;
            --accent: #ff9f1c;
            --success-soft: #e9f8f1;
            --danger-soft: #ffe8ea;
            --surface: rgba(255, 255, 255, 0.9);
            --text-main: #16324f;
            --text-muted: #5f7488;
            --border-soft: rgba(15, 76, 129, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 76, 129, 0.12);
        }
        * {
            font-family: "Manrope", sans-serif;
        }
.navbar-shell {
            background: linear-gradient(120deg, var(--brand-dark), var(--brand));
            box-shadow: 0 14px 30px rgba(10, 47, 79, 0.24);
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 1.2rem;
        }
.container-main { max-width:1200px; margin:40px auto; }
.header-section { background:white; padding:30px; border-radius:15px; }
.search-box { background:white; padding:20px; border-radius:10px; margin-top:20px; }
/* DARK MODE */
body.dark-mode {
    background: #121212 !important;
    color: #ffffff;
}

/* CARDS */
.dark-mode .card,
.dark-mode .header-section,
.dark-mode .search-box {
    background: #1e1e1e !important;
    color: #fff;
    border: 1px solid #333;
}

/* SELECT */
.dark-mode .form-select {
    background: #2a2a2a;
    color: #fff;
    border: 1px solid #555;
}

/* BUTTONS */
.dark-mode .btn-primary {
    background: #0d6efd;
}

.dark-mode .btn-success {
    background: #198754;
}

.dark-mode .btn-danger {
    background: #dc3545;
}

/* ALERT */
.dark-mode .alert {
    background: #1f1f1f;
    color: #fff;
    border: 1px solid #333;
}

/* TEXT */
.dark-mode p,
.dark-mode h1,
.dark-mode h5,
.dark-mode label {
    color: #fff;
}
#map {
    height: 400px;
}
@media (max-width: 768px) {

    .container-main {
        margin: 20px;
    }

    .header-section {
        padding: 20px;
    }

    h1 {
        font-size: 24px;
    }

    .row.g-3 > div {
        width: 100%;
    }

    .col-md-5,
    .col-md-2 {
        width: 100%;
    }

    .btn {
        width: 100%;
    }

    .mt-3 button {
        width: 100%;
        margin-bottom: 10px;
    }
    
#map {
        height: 250px;
    }


}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-shell py-3">
        <div class="container">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark">CN</div>
                <div>
                    <a class="navbar-brand fw-bold mb-0" href="../dashboard.php">Campus Navigation</a>
                    <div class="small text-white-50">Find your way around campus</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 text-white mt-3 mt-lg-0">
                <button id="darkModeToggle" class="btn btn-outline-light me-2">
                    🌙
                </button>
                <div class="text-end">
                    <div class="fw-semibold">Welcome back, <?= htmlspecialchars($userName) ?></div>
                    <div class="small text-white-50">Community learning dashboard</div>
                </div>
                <a href="../logout.php" class="btn btn-outline-light btn-sm rounded-pill px-3">Logout</a>
            </div>
        </div>
    </nav>

<div class="container-main">

<div class="header-section mb-4">
<h1><i class="fas fa-map-marked-alt"></i> Campus Navigation</h1>
<p>Find your way around campus</p>
</div>

<!-- FIND ROUTE -->
<form method="POST">
<div class="card shadow-sm p-4 mb-4">
<h5 class="fw-bold mb-3">Find Route</h5>

<div class="row g-3">

<!-- SOURCE -->
<div class="col-md-5">
<label>From</label>
<select name="source" class="form-select" required>
<option value="">Select Source</option>
<?php
foreach($rooms as $room){
echo "<option>$room</option>";
}
?>
</select>
</div>

<!-- DEST -->
<div class="col-md-5">
<label>To</label>
<select name="destination" class="form-select" required>
<option value="">Select Destination</option>
<?php
foreach($rooms as $room){
echo "<option>$room</option>";
}
?>
</select>
</div>

<!-- BUTTON -->
<div class="col-md-2 d-flex align-items-end">
<button type="submit" name="find" class="btn btn-primary w-100">
🔄 Find
</button>
</div>

</div>
</div>
</form>

<!-- RESULT -->
<?php if($output != "") { ?>
<div class="card mt-3 p-3">
    <h5 class="fw-bold mb-2">📍 Route Steps</h5>

    <div id="routeData" style="white-space: pre-line;">
        <?php echo $output; ?>
    </div>
</div>
<?php } ?>
<div class="mt-3">
    <button onclick="startNavigation()" class="btn btn-success">▶ Start</button>
    <button onclick="nextStep()" class="btn btn-primary">➡ Next Step</button>
    <button onclick="stopSpeech()" class="btn btn-danger">⛔ Stop</button>
</div>
<div class="card mt-4 p-3">
    <h5>Live Route Map</h5>
    <div id="map" style="height:400px; border-radius:10px;"></div>
</div>
</div>
<script>
let steps = [];
let currentStep = 0;

function extractSteps() {

    let text = document.getElementById("routeData").innerText;

    steps = text
        .split("\n")
        .filter(line => line.trim().startsWith("Step"));

    currentStep = 0;
}

function speak(text) {
    window.speechSynthesis.cancel();

    let speech = new SpeechSynthesisUtterance();

    speech.text = text
    .replace(/AX-/g, "")
    .replace(/->/g, "then go to")
    .replace(/Step \d+:/g, "Next,");

    speech.rate = 1;
    speech.lang = "en-US";

    window.speechSynthesis.speak(speech);
}

function startNavigation() {
    extractSteps();

    if (steps.length === 0) {
        alert("No steps found!");
        return;
    }

    currentStep = 0;
    speak(steps[currentStep]);
}

function nextStep() {
    if (steps.length === 0) {
        alert("Click Start first!");
        return;
    }

    currentStep++;

    if (currentStep >= steps.length) {
        speak("You have reached your destination");
        return;
    }

    speak(steps[currentStep]);
}

function stopSpeech() {
    window.speechSynthesis.cancel();
}
</script>
<script>
const toggleBtn = document.getElementById("darkModeToggle");

if (localStorage.getItem("darkMode") === "enabled") {
    document.body.classList.add("dark-mode");
    toggleBtn.textContent = "☀️";
}

toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("darkMode", "enabled");
        toggleBtn.textContent = "☀️";
    } else {
        localStorage.setItem("darkMode", "disabled");
        toggleBtn.textContent = "🌙";
    }
});
</script>
<script>

// 1️⃣ MAP INIT
const map = L.map('map', {
    crs: L.CRS.Simple,
    minZoom: -1
});

const bounds = [[0, 0], [826, 1403]];
L.imageOverlay('floor.png', bounds).addTo(map);
map.fitBounds(bounds);


// 2️⃣ ROOM COORDS
const roomCoords = {
    "AX-501A": [200, 580],
    "AX-501B": [200, 500],
    "AX-502": [80, 450],
    "AX-503A": [200, 420],
    "AX-503B": [200, 350],
    "AX-504": [80, 300],
    "AX-505A": [160, 80],
    "AX-505B": [80, 80],
    "AX-506": [300, 80],
    "AX-507": [350, 450],
    "AX-508": [420, 80],
    "AX-509": [450, 450],
    "AX-510": [540, 80],
    "AX-511": [550, 450],
    "AX-512": [780, 80],
    "AX-513A": [650, 480],
    "AX-513B": [650, 400],
    "AX-514A": [780, 500],
    "AX-514B": [780, 580],
    "AX-515A": [650, 620],
    "AX-515B": [650, 550],
    "AX-516B": [780, 700]
};


// 3️⃣ ✅ DRAW ROUTE (PUT HERE)
function drawRoute() {
if (!document.getElementById("routeData")) return;
    const text = document.getElementById("routeData").innerText;

    let matches = text.match(/AX-\d+[A-Z]?/g);
    if (!matches) return;

    let path = [];

    matches.forEach(room => {
        if (roomCoords[room]) {
            path.push(roomCoords[room]);
        }
    });

    if (path.length === 0) return;

    let polyline = L.polyline(path, {
        color: '#0f4c81',
        weight: 5
    }).addTo(map);

    L.marker(path[0]).addTo(map).bindPopup("Start");
    L.marker(path[path.length - 1]).addTo(map).bindPopup("Destination");

    map.fitBounds(polyline.getBounds());

    let i = 0;

let movingMarker = L.circleMarker(path[0], {
    radius: 6,
    color: 'red'
}).addTo(map);

setInterval(() => {
    if (i < path.length) {
        movingMarker.setLatLng(path[i]);
        i++;
    }
}, 700);
}


// 4️⃣ AUTO RUN (AFTER FUNCTION)

window.addEventListener("load", () => {
    const el = document.getElementById("routeData");
    if (el && el.innerText.trim() !== "") {
        drawRoute();
    }
});

// 5️⃣ DEBUG CLICK (OPTIONAL)
map.on('click', function(e) {
    console.log(e.latlng);
});

</script>
</body>
</html>