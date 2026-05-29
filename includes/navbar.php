<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

    <div class="container-fluid">

        <a class="navbar-brand fw-bold" href="#">CampusHubX</a>

        <div class="ms-auto d-flex align-items-center">
            <!-- DARK MODE BUTTON -->
            <button id="darkModeToggle" class="btn btn-outline-light me-2">
                🌙
            </button>
            <div class="dropdown me-2">
                <button class="btn btn-outline-light position-relative" data-bs-toggle="dropdown">
                    🔔
                    <span class="badge bg-danger">
                        <?= $notifCount ?>
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end p-2" style="width:500px; max-height:400px; overflow-y:auto;">

                    <!-- Notifications will come here -->
                    <div id="notifBox">
                        <li class="dropdown-item text-muted">Loading...</li>
                    </div>

                </ul>
            </div>
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    👤 Welcome,
                    <?= htmlspecialchars($userName) ?>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <a class="dropdown-item" href="profile.php">Profile</a>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            Logout
                        </a>
                    </li>

                </ul>
            </div>

        </div>

    </div>

</nav>