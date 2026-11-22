<?php

ob_start(); // Start output buffering to catch any stray output before headers

// Moved error reporting after ob_start() to ensure output is buffered
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db_connection.php';

header('Content-Type: application/json');

$conn = getDbConnection();

// Check if database connection was successful (getDbConnection() now returns false on failure)
if (!$conn) {
    ob_end_clean();
    echo json_encode(['error' => 'Database connection failed. Please check db_connection.php and config.php.']);
    exit();
}

$regime = $_GET['regime'] ?? null;
$plotType = $_GET['plot_type'] ?? null; // e.g., 'current_state', '30yr_projection'

// Validate required parameters
if (!$regime || !$plotType) {
    ob_end_clean();
    echo json_encode(['error' => 'Missing regime or plot_type parameter.']);
    exit();
}

$tableNameToDisplay = "simulation_results_" . (int)$regime;

// Security check: ensure the table name is in the expected format
if (!preg_match('/^simulation_results_\d+$/', $tableNameToDisplay)) {
    ob_end_clean();
    echo json_encode(['error' => 'Invalid table name format.']);
    exit();
}

// Verify if the specific simulation table exists
$checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");
if (!$checkTableExists || $checkTableExists->num_rows === 0) {
    ob_end_clean();
    echo json_encode(['error' => "Simulation results table '$tableNameToDisplay' not found. Please run the simulation first for regime " . (int)$regime . "."]);
    exit();
}

// Determine which diameter column to use based on the plot type
$diameterColumn = ($plotType === '30yr_projection') ? 'diameter30' : 'diameter';

// Base SQL query to fetch tree data along with species group names
$sql = "
    SELECT
        sr.coord_x,
        sr.coord_y,
        sr.{$diameterColumn},
        sr.status,
        sr.damage_stem,
        sr.damage_crown,
        sgn.speciesgroup_name
    FROM
        {$tableNameToDisplay} sr
    JOIN
        species s ON sr.species_id = s.species_id
    JOIN
        species_group_names sgn ON s.species_group = sgn.id
    WHERE
        sr.coord_x IS NOT NULL AND sr.coord_y IS NOT NULL
        AND sr.{$diameterColumn} IS NOT NULL
";

// Removed the status filter so all tree statuses (stand, keep, cut, victim) are included.
// if ($plotType === 'current_state') {
//     $sql .= " AND sr.status IN ('keep', 'stand')";
// }

$result = $conn->query($sql);

if (!$result) {
    ob_end_clean();
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit();
}

$plotData = [];
$minX = PHP_FLOAT_MAX;
$maxX = PHP_FLOAT_MIN;
$minY = PHP_FLOAT_MAX;
$maxY = PHP_FLOAT_MIN;

// Define base colors for species groups (you can add more as needed for groups 1-7)
// These colors will be the base background for datasets.
$speciesGroupColors = [
    'Dipterocarps' => 'rgba(46, 125, 50, 0.7)',     // Forest Green
    'Non-Dipterocarps' => 'rgba(129, 199, 132, 0.7)', // Lighter Green
    'Mersawa' => 'rgba(0, 100, 0, 0.7)', // Dark Green
    'Keruing' => 'rgba(50, 205, 50, 0.7)', // Lime Green
    'Dip Commercial' => 'rgba(34, 139, 34, 0.7)', // Forest Green
    'Dip Non Commercial' => 'rgba(144, 238, 144, 0.7)', // Light Green
    'NonDip Commercial' => 'rgba(0, 128, 0, 0.7)', // Green
    'NonDip Non Commercial' => 'rgba(173, 255, 47, 0.7)', // GreenYellow
    'Others' => 'rgba(169, 169, 169, 0.7)' // Dark Grey for others
];

// Define styling rules based on tree status. These will influence point style and border.
$statusStyling = [
    'stand' => ['pointStyle' => 'circle', 'borderColor' => 'rgba(0,0,0,0.5)', 'labelSuffix' => ' (Stand)'],
    'keep'  => ['pointStyle' => 'circle', 'borderColor' => 'rgba(0,0,0,0.7)', 'labelSuffix' => ' (Kept)'], // Slightly darker border for kept trees
    'cut'   => ['pointStyle' => 'crossRot', 'borderColor' => 'rgba(100, 100, 100, 0.9)', 'forceBgColor' => 'rgba(100, 100, 100, 0.6)', 'labelSuffix' => ' (Cut)'], // Grey for cut trees
    'victim' => ['pointStyle' => 'triangle', 'borderColor' => 'rgba(255, 140, 0, 0.9)', 'forceBgColor' => 'rgba(255, 165, 0, 0.8)', 'labelSuffix' => ' (Victim)'], // Orange for victim trees
    'default' => ['pointStyle' => 'circle', 'borderColor' => 'rgba(0,0,0,0.5)', 'labelSuffix' => ''] // Fallback for unknown status
];

// Define damage-specific border colors (can override status border if applicable)
$damageBorderColors = [
    'Stem Damage' => 'rgba(255, 165, 0, 1)',   // Orange
    'Crown Damage' => 'rgba(255, 99, 71, 1)',  // Tomato
    'Both Damages' => 'rgba(255, 0, 0, 1)',    // Red
];


// To handle unique datasets for Chart.js, grouped by Species Group AND Status
$groupedDatasets = [];

while ($row = $result->fetch_assoc()) {
    $x = (float)$row['coord_x'];
    $y = (float)$row['coord_y'];
    $diameter = (float)$row[$diameterColumn];
    $speciesGroup = $row['speciesgroup_name'] ?? 'Others'; // Default to 'Others' if group name is null
    $status = $row['status'] ?? 'default'; // Default to 'default' if status is null
    $damageStem = $row['damage_stem'];
    $damageCrown = $row['damage_crown'];

    // Determine point radius (scaled from diameter in meters)
    $pointRadius = $diameter * 100 / 20;
    if ($pointRadius < 2) $pointRadius = 2; // Minimum size for visibility
    if ($pointRadius > 25) $pointRadius = 25; // Maximum size to prevent oversized points

    // Get base styling from status
    $currentStatusStyle = $statusStyling[$status] ?? $statusStyling['default'];
    $pointStyle = $currentStatusStyle['pointStyle'];
    $pointBorderColor = $currentStatusStyle['borderColor'];

    // Apply damage-specific border color if damage is present (can override status border)
    if ($damageStem === 'yes' && $damageCrown === 'yes') {
        $pointBorderColor = $damageBorderColors['Both Damages'];
        $pointStyle = 'triangle'; // Example: use triangle for both damages
    } elseif ($damageStem === 'yes') {
        $pointBorderColor = $damageBorderColors['Stem Damage'];
        $pointStyle = 'rect'; // Example: use square for stem damage
    } elseif ($damageCrown === 'yes') {
        $pointBorderColor = $damageBorderColors['Crown Damage'];
        $pointStyle = 'star'; // Example: use star for crown damage
    }

    // Determine dataset key for grouping in Chart.js
    // Datasets will be grouped by Species Group AND Status for clear legend entries.
    $datasetKey = $speciesGroup . '_' . $status;
    $datasetLabel = $speciesGroup . ($currentStatusStyle['labelSuffix'] ?? '');

    if (!isset($groupedDatasets[$datasetKey])) {
        $groupedDatasets[$datasetKey] = [
            'label' => $datasetLabel,
            'data' => [],
            // Default background color from species group, overridden by status if defined (e.g., for cut/victim)
            'backgroundColor' => $currentStatusStyle['forceBgColor'] ?? ($speciesGroupColors[$speciesGroup] ?? 'rgba(150, 150, 150, 0.7)'),
            'borderColor' => 'rgba(0,0,0,0.5)', // Placeholder, actual border applied per point
            'pointRadius' => [], // Individual radius for each point
            'pointStyle' => [],  // Individual style for each point
            'borderWidth' => 1,
            'type' => 'scatter',
            'parsing' => false, // We provide x,y,r directly
            'hidden' => false
        ];
    }

    // Add point data and specific styling for this point
    $groupedDatasets[$datasetKey]['data'][] = ['x' => $x, 'y' => $y, 'r' => $pointRadius];
    $groupedDatasets[$datasetKey]['pointRadius'][] = $pointRadius;
    $groupedDatasets[$datasetKey]['pointStyle'][] = $pointStyle;
    $groupedDatasets[$datasetKey]['borderColor'][] = $pointBorderColor; // Set border color per point

    $minX = min($minX, $x);
    $maxX = max($maxX, $x);
    $minY = min($minY, $y);
    $maxY = max($maxY, $y);
}

$result->free();
$conn->close();

// Convert associative array of datasets to indexed array for Chart.js
$datasetsForChartJs = array_values($groupedDatasets);

// Determine plot title
$plotTitle = '';
if ($plotType === 'current_state') {
    $plotTitle = "Current Forest Spatial Distribution (Regime: " . $regime . "cm DBH)";
} elseif ($plotType === '30yr_projection') {
    $plotTitle = "30-Year Projected Forest Spatial Distribution (Regime: " . $regime . "cm DBH)";
}


ob_end_clean();
echo json_encode([
    'datasets' => $datasetsForChartJs,
    'plotTitle' => $plotTitle,
    'xRange' => ['min' => floor($minX / 10) * 10, 'max' => ceil($maxX / 10) * 10], // Rounded for cleaner axis
    'yRange' => ['min' => floor($minY / 10) * 10, 'max' => ceil($maxY / 10) * 10]
]);

?>