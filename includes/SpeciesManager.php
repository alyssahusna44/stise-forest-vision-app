<?php
// includes/SpeciesManager.php
require_once __DIR__ . '/../config.php'; // Ensure config is available for DB_NAME etc.

class SpeciesManager {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /**
     * Imports species data from a CSV file into the 'species' table.
     * @param string $csvFilePath Path to the species CSV file.
     */
    public function importCsvToDb($csvFilePath) {
        // Clear existing data in the species table before importing
        $this->conn->query("TRUNCATE TABLE species");
        echo "Truncated 'species' table.<br>";

        if (($handle = fopen($csvFilePath, "r")) !== FALSE) {
            $header = fgetcsv($handle); // Read and skip header row

            // Trim header values to remove potential whitespace, making them safe for array_combine
            $header = array_map('trim', $header);

            // Prepare for batch insertion
            // CORRECTED: "issisi" -> species_id (int), species_code (string), local_name (string), species_group (int), roy_class (string), is_dipterocarp (int)
            $stmt = $this->conn->prepare("INSERT INTO species (species_id, species_code, local_name, species_group, roy_class, is_dipterocarp) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("SQL prepare failed for species import: " . $this->conn->error);
            }

            $importedCount = 0;
            $rowNum = 1; // Start from 1 for data rows (after header)
            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowNum++;
                // Trim data values as well
                $data = array_map('trim', $data);

                // Ensure the number of columns matches the header/table structure
                if (count($header) !== count($data)) {
                    echo "Skipping row $rowNum due to column mismatch: " . implode(',', $data) . "<br>";
                    continue;
                }
                $species = array_combine($header, $data);

                // Ensure 'is_dipterocarp' is treated as an integer (0 or 1)
                $is_dipterocarp_val = (int)$species['is_dipterocarp'];

                // Bind parameters with the corrected type string
                $stmt->bind_param("issisi", // Corrected
                    $species['species_id'],
                    $species['species_code'],
                    $species['local_name'],
                    $species['species_group'], // This is INT in DB
                    $species['roy_class'],     // This is VARCHAR in DB
                    $is_dipterocarp_val        // This is BOOLEAN/INT in DB
                );

                if ($stmt->execute()) {
                    $importedCount++;
                } else {
                    echo "Error inserting species row $rowNum (" . $species['local_name'] . "): " . $stmt->error . "<br>";
                }
            }
            fclose($handle);
            $stmt->close();
            echo "Successfully imported $importedCount species into the database.<br>";
        } else {
            die("Error: Could not open species CSV file at $csvFilePath. Please check the path and permissions.");
        }
    }

    /**
     * Fetches species data from the 'species' table filtered by species group.
     * @param int $groupId The species group ID.
     * @return array An array of species rows.
     */
    public function getSpeciesByGroup($groupId) {
        $species = [];
        // Only select columns needed by ForestGenerator to keep data transfer lean
        $stmt = $this->conn->prepare("SELECT species_id, local_name, is_dipterocarp FROM species WHERE species_group = ?");
        if (!$stmt) {
            die("SQL prepare failed for getSpeciesByGroup: " . $this->conn->error);
        }
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Ensure is_dipterocarp is a boolean in PHP, even if it's 0/1 from DB
            $row['is_dipterocarp'] = (bool)$row['is_dipterocarp'];
            $species[] = $row;
        }
        $stmt->close();
        return $species;
    }

    /**
     * Checks if the species table is populated.
     * @return bool True if species table has data, false otherwise.
     */
    public function isSpeciesTablePopulated() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM species");
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['count'] > 0;
        }
        return false;
    }
}
?>