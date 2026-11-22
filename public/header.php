<?php
// public/header.php - Common header, navigation, and initial HTML for the Forest Simulation app

// Include necessary configuration and database connection
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/SpeciesManager.php'; // Required for header if you display species count

$conn = getDbConnection(); // Get a database connection for potential use in header/nav

// You can add common PHP functions or data fetching here if needed across multiple pages
$speciesManager = new SpeciesManager($conn);
$totalSpeciesInDb = 0;
if ($speciesManager->isSpeciesTablePopulated()) {
    $resultSpecies = $conn->query("SELECT COUNT(*) as total FROM species");
    if ($resultSpecies) {
        $rowSpecies = $resultSpecies->fetch_assoc();
        $totalSpeciesInDb = $rowSpecies['total'];
    }
}
$conn->close(); // Close connection here as it will be reopened in index.php for main logic

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forest Vision</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js"></script>
</head>

<body>
    <header class="main-header">
        <div class="header-nav">
            <div class="logo">
                <a href="home.php" style="text-decoration: none; color: inherit;">Forest Vision</a>
            </div>
            <nav>
                <ul>
                    <!-- <li><a href="home.php">Home</a></li> -->
                    <li><a href="forest_generation.php">Forest Generation</a></li>
                    <li><a href="final_output.php">Final Output Table</a></li>
                    <li><a href="stand_tables.php">Stand Tables</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropbtn" id="visualisationDropdown">Visualisation ▼</a>
                        <div class="dropdown-content">
                            <a href="visualisation.php">Diameter Distribution (Histograms)</a>
                            <a href="spatial_visualisation.php">Spatial Distribution (Map)</a>
                    <li><a href="analysis.php">Cutting Regime Analysis</a></li>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">

    <script>
        // Get the dropdown button and its parent container
        const dropdownButton = document.getElementById('visualisationDropdown');
        const dropdownContainer = dropdownButton.parentElement; // This is the <li> with class "dropdown"

        // Toggle the 'show' class on the dropdown container when the button is clicked
        dropdownButton.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior (e.g., navigating to #)
            dropdownContainer.classList.toggle('show');
            event.stopPropagation(); // Stop propagation to prevent the document click listener from immediately closing it
        });

        // Close the dropdown if the user clicks outside of it
        document.addEventListener('click', function(event) {
            if (!dropdownContainer.contains(event.target)) {
                // If the click is outside the dropdown container, remove the 'show' class
                dropdownContainer.classList.remove('show');
            }
        });
    </script>