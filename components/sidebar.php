<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'trips.php' ? 'active' : ''; ?>" href="trips.php">
                    <i class="bi bi-truck"></i> My Trips
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'vehicle.php' ? 'active' : ''; ?>" href="vehicle.php">
                    <i class="bi bi-car-front"></i> Vehicle
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'fuel.php' ? 'active' : ''; ?>" href="fuel.php">
                    <i class="bi bi-fuel-pump"></i> Fuel
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="bi bi-person"></i> Profile
                </a>
            </li>
        </ul>
    </div>
</div> 