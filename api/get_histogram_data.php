<?php
// api/get_histogram_data.php - Provides JSON data for various forest structure histograms

// IMPORTANT: Start output buffering immediately. This catches any stray output
// (like whitespace or PHP errors) BEFORE the JSON header is sent,
// which is a common cause of "Unexpected token <" errors.
ob_start();

// Include the database connection. The path is relative to this file's location.
// __DIR__ is the directory of the current file (api/). '/../' goes up one level (to FOREST_VISION/).
// Then '/includes/db_connection.php' points to the correct file.
require_once __DIR__ . '/../includes/db_connection.php';

// Set the HTTP header to indicate that the response content is JSON.
// This MUST be set before any data is echoed.
header('Content-Type: application/json');

// Get a database connection
$conn = getDbConnection();

// Get parameters from the URL query string (e.g., ?regime=45&chart_type=current_distribution)
$regime = $_GET['regime'] ?? null;
$chartType = $_GET['chart_type'] ?? null; // e.g., 'current_distribution', '30yr_projection', 'initial_distribution'

// Basic validation for required parameters
if (!$regime) {
    ob_end_clean(); // Clean the output buffer before echoing error
    echo json_encode(['error' => 'No simulation regime specified.']);
    exit();
}

if (!$chartType) {
    ob_end_clean(); // Clean the output buffer before echoing error
    echo json_encode(['error' => 'No chart type specified.']);
    exit();
}

// Construct the table name for simulation results.
// (int)$regime casts to integer, adding a layer of safety.
$tableNameToDisplay = "simulation_results_" . (int)$regime;

// Security check: ensure the constructed table name matches expected format
// This prevents SQL injection through the table name.
if (!preg_match('/^simulation_results_\d+$/', $tableNameToDisplay)) {
    ob_end_clean(); // Clean the output buffer
    echo json_encode(['error' => 'Invalid table name format.']);
    exit();
}

// Verify if the specific simulation table exists in the database
$checkTableExists = $conn->query("SHOW TABLES LIKE '$tableNameToDisplay'");
if (!$checkTableExists || $checkTableExists->num_rows === 0) {
    ob_end_clean(); // Clean the output buffer
    echo json_encode(['error' => "Simulation results table '$tableNameToDisplay' not found. Please run the simulation first for the selected regime."]);
    exit();
}

// --- Fetch Diameter Classes ---
// This data defines the bins for your histograms. It comes from the 'diameter_classes' table.
// Ensure this table is populated in your database as per forest_db.pdf.
$diameterClasses = [];
$sqlDiameterClasses = "SELECT id, class_name, min_diameter, max_diameter FROM diameter_classes ORDER BY id ASC";
$resultDiameterClasses = $conn->query($sqlDiameterClasses);

if ($resultDiameterClasses) {
    while ($row = $resultDiameterClasses->fetch_assoc()) {
        $diameterClasses[] = $row;
    }
    $resultDiameterClasses->free(); // Free result memory
} else {
    ob_end_clean(); // Clean the output buffer
    echo json_encode(['error' => 'Error fetching diameter classes: ' . $conn->error]);
    exit();
}

// --- Helper function to process raw data into histogram counts ---
// This function takes a raw SQL result and aggregates tree counts into diameter classes.
function processHistogramData($rawResult, $db_connection, $diameterColumn, $diameterClasses)
{
    $histogramData = [];
    // Initialize counts for all diameter classes to zero
    foreach ($diameterClasses as $dc) {
        $histogramData[$dc['id']] = 0;
    }

    if (!$rawResult) {
        // If the database query failed, return the initialized array
        return $histogramData;
    }

    while ($row = $rawResult->fetch_assoc()) {
        $diameterCm = (float)($row[$diameterColumn] ?? 0.0); // Get the diameter value, converting to float

        $diameterClassId = null;
        // Find which diameter class the current tree belongs to
        foreach ($diameterClasses as $dc) {
            $min = (float) $dc['min_diameter'];
            $max = ($dc['max_diameter'] !== null) ? (float) $dc['max_diameter'] : null;

            if ($max === null) {
                // This handles the open-ended class, e.g., '60+'
                if ($diameterCm >= $min) {
                    $diameterClassId = $dc['id'];
                    break; // Found the class
                }
            } else {
                // This handles closed classes, e.g., '15-30'
                if ($diameterCm >= $min && $diameterCm < $max) {
                    $diameterClassId = $dc['id'];
                    break; // Found the class
                }
            }
        }

        if ($diameterClassId !== null) {
            $histogramData[$diameterClassId]++; // Increment the count for this diameter class
        }
    }
    return $histogramData;
}


// --- Define SQL Query based on the requested chart type ---
$diameterColumnSelect = '';    // The SQL expression to select the diameter column
$diameterColumnInResult = '';  // The name of the column in the PHP result set (alias or actual name)
$whereClause = '';             // The WHERE clause to filter tree statuses
$chartTitlePrefix = '';        // A descriptive title for the chart

switch ($chartType) {
    case 'current_distribution':
        // For visualizing the forest structure of trees that *remain* after simulation.
        // The 'diameter' column in simulation_results tables is typically in meters, so convert to cm.
        $diameterColumnSelect = '(diameter * 100) AS diameter_cm';
        $diameterColumnInResult = 'diameter_cm'; // This is the alias used in the SELECT statement
        $whereClause = "WHERE status IN ('keep', 'stand') AND diameter IS NOT NULL"; // Filter for kept or stand trees
        $chartTitlePrefix = 'Current Forest Structure (Remaining Trees)';
        break;
    case '30yr_projection':
        // For visualizing the projected forest structure after 30 years.
        // The 'diameter30' column is assumed to be in cm already based on typical usage.
        $diameterColumnSelect = 'diameter30';
        $diameterColumnInResult = 'diameter30'; // This is the actual column name in the table
        $whereClause = "WHERE diameter30 IS NOT NULL"; // Include all trees that have a 30-year projection
        $chartTitlePrefix = '30-Year Projected Forest Structure';
        break;
    case 'initial_distribution':
        // For visualizing the forest structure *before* any simulation or harvesting impacts.
        // Assuming this can be derived from the 'simulation_results_XX' table, where 'diameter' is the initial diameter.
        // If you have a separate 'base_forest' or 'initial_trees' table that holds initial data, adjust the FROM clause here.
        $diameterColumnSelect = '(diameter * 100) AS diameter_cm';
        $diameterColumnInResult = 'diameter_cm';
        $whereClause = "WHERE diameter IS NOT NULL"; // Consider all trees initially present
        $chartTitlePrefix = 'Initial Forest Structure (All Trees)';
        break;
    default:
        ob_end_clean(); // Clean the output buffer
        echo json_encode(['error' => 'Invalid chart_type specified.']);
        exit();
}

// Construct the full SQL query
$sqlHistogram = "
    SELECT
        $diameterColumnSelect
    FROM
        $tableNameToDisplay
    $whereClause
";

$resultHistogram = $conn->query($sqlHistogram);

// Process the raw SQL results into counts per diameter class
$dataForHistogram = processHistogramData($resultHistogram, $conn, $diameterColumnInResult, $diameterClasses);

if ($resultHistogram) $resultHistogram->free(); // Release memory used by the result set
$conn->close(); // Close the database connection

// --- Prepare data for Chart.js consumption ---
$labels = []; // Labels for the X-axis (diameter class names)
$counts = []; // Data points for the Y-axis (number of trees)

foreach ($diameterClasses as $dc) {
    $labels[] = $dc['class_name']; // e.g., '5-15cm', '15-30cm', '60+'
    $counts[] = $dataForHistogram[$dc['id']] ?? 0; // Get the count for this class, defaulting to 0 if no trees were found for it
}

// Assemble the final JSON structure for Chart.js
$chartData = [
    'labels' => $labels,
    'datasets' => [
        [
            'label' => 'Number of Trees', // Dataset label for the chart legend/tooltip
            'data' => $counts,
            'backgroundColor' => 'rgba(75, 192, 192, 0.6)', // Bar color
            'borderColor' => 'rgba(75, 192, 192, 1)',      // Bar border color
            'borderWidth' => 1
        ]
    ],
    // Combine the dynamic prefix with the regime for a complete chart title
    'chartTitle' => $chartTitlePrefix . " for " . $regime . "cm DBH Regime"
];

// End output buffering and send the JSON response to the client.
ob_end_clean(); // Discard any output that was buffered
echo json_encode($chartData); // Output the JSON string
exit(); // Stop script execution to ensure no further output
?>