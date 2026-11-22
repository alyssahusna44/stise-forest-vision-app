<?php
// includes/ForestGenerator.php
require_once 'SpeciesManager.php'; 
require_once 'db_connection.php';

class ForestGenerator {
    private $conn;
    private $speciesManager; 
    private $blockConfig; 

    public function __construct(mysqli $conn, SpeciesManager $speciesManager) { 
        $this->conn = $conn;
        $this->speciesManager = $speciesManager; 
        $this->blockConfig = [
            1 => ['name' => 'Mersawa', 'diameters' => ['5-15' => 15, '15-30' => 12, '30-45' => 4, '45-60' => 2, '60+' => 2]],
            2 => ['name' => 'Keruing', 'diameters' => ['5-15' => 21, '15-30' => 18, '30-45' => 6, '45-60' => 4, '60+' => 4]],
            3 => ['name' => 'Dip Commercial', 'diameters' => ['5-15' => 21, '15-30' => 18, '30-45' => 6, '45-60' => 4, '60+' => 4]],
            4 => ['name' => 'Dip Noncommercial', 'diameters' => ['5-15' => 30, '15-30' => 27, '30-45' => 9, '45-60' => 5, '60+' => 3]],
            5 => ['name' => 'NonDip Commercial', 'diameters' => ['5-15' => 30, '15-30' => 27, '30-45' => 9, '45-60' => 4, '60+' => 4]],
            6 => ['name' => 'NonDip Noncommercial', 'diameters' => ['5-15' => 39, '15-30' => 36, '30-45' => 12, '45-60' => 7, '60+' => 4]],
            7 => ['name' => 'Others', 'diameters' => ['5-15' => 44, '15-30' => 42, '30-45' => 14, '45-60' => 9, '60+' => 4]],
        ];
    }

    /**
     * Generates the entire forest across multiple blocks and inserts into the database.
     * @param int $numBlocksI Number of blocks along the I-axis (rows).
     * @param int $numBlocksJ Number of blocks along the J-axis (columns).
     */
    public function generateForest($numBlocksI = 10, $numBlocksJ = 10) {
        echo "Generating forest...<br>";

        // Prepare the SQL statement once for efficiency, inserting into 'base_forest'
        $stmt = $this->conn->prepare("INSERT INTO base_forest (tree_id, block_i, block_j, coord_x, coord_y, species_id, species_name, species_group, diameter, diameter_class, height, is_dipterocarp, volume) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("SQL prepare failed for base_forest insert: " . $this->conn->error);
        }

        for ($i = 1; $i <= $numBlocksI; $i++) {
            for ($j = 1; $j <= $numBlocksJ; $j++) {
                echo "Generating trees for Block ($i, $j)...<br>";
                $this->generateTreesForBlock($i, $j, $stmt);
            }
        }
        $stmt->close();
        echo "Forest generation complete!<br>";
    }

    /**
     * Generates all trees for a single block, ensuring unique coordinates and tree_ids.
     * @param int $blockI The i-coordinate of the block.
     * @param int $blockJ The j-coordinate of the block.
     * @param mysqli_stmt $stmt The prepared SQL statement for insertion into base_forest.
     */
    private function generateTreesForBlock($blockI, $blockJ, $stmt) {
        $treesToGenerate = 0;
        foreach ($this->blockConfig as $groupData) {
            foreach ($groupData['diameters'] as $count) {
                $treesToGenerate += $count;
            }
        }

        // Generate a pool of unique (x,y) coordinates for the block
        $tempCoords = [];
        for ($x = 0; $x < 100; $x++) {
            for ($y = 0; $y < 100; $y++) {
                $tempCoords[] = sprintf("%02d%02d", $x, $y); // Store as 'XXYY' string
            }
        }

        if ($treesToGenerate > count($tempCoords)) {
            die("Error: Not enough unique coordinate slots (100x100 = 10000) for $treesToGenerate trees in a block. Please adjust tree count or block size.");
        }
        shuffle($tempCoords);
        $selectedCoords = array_slice($tempCoords, 0, $treesToGenerate);

        $coordIndex = 0; // Index to iterate through our selected unique coordinates

        foreach ($this->blockConfig as $groupId => $groupData) {
            foreach ($groupData['diameters'] as $diameterClass => $count) {
                for ($k = 0; $k < $count; $k++) {
                    if ($coordIndex >= count($selectedCoords)) {
                        echo "Warning: Ran out of pre-generated unique coordinates for Block ($blockI, $blockJ) before all trees were created.";
                        break 2;
                    }

                    $currentCoordString = $selectedCoords[$coordIndex];
                    $coordX = (int)substr($currentCoordString, 0, 2);
                    $coordY = (int)substr($currentCoordString, 2, 2);
                    $coordIndex++;

                    $treeId = sprintf("T%02d%02d%02d%02d", $blockI, $blockJ, $coordX, $coordY);

                    // Fetch species from DB via SpeciesManager
                    $speciesOptions = $this->speciesManager->getSpeciesByGroup($groupId);
                    if (empty($speciesOptions)) {
                        echo "Warning: No species found in DB for species_group_id: $groupId. Skipping tree generation for this group.";
                        continue;
                    }
                    $randomSpecies = $speciesOptions[array_rand($speciesOptions)];
                    $speciesId = $randomSpecies['species_id']; // Get species_id
                    $speciesName = $randomSpecies['local_name'];
                    $isDipterocarp = $randomSpecies['is_dipterocarp'];

                    $diameterCm = $this->getRandomDiameter($diameterClass);
                    $diameterM = $diameterCm / 100;

                    $height = $this->getRandomHeight($diameterCm);

                    $volume = $this->calculateVolume($isDipterocarp, $diameterM, $height, $diameterCm);

                    // Bind parameters for base_forest table (note new species_id parameter)
                    $stmt->bind_param("siiiiisidsssd",
                        $treeId,
                        $blockI,
                        $blockJ,
                        $coordX,
                        $coordY,
                        $speciesId,        // New: species_id
                        $speciesName,
                        $groupId,
                        $diameterM,
                        $diameterClass,
                        $height,
                        $isDipterocarp,
                        $volume
                    );

                    if (!$stmt->execute()) {
                        echo "Error inserting tree $treeId into base_forest: " . $stmt->error . "<br>";
                    }
                }
            }
        }
    }

    /**
     * Generates a random diameter within the specified diameter class range.
     * @param string $diameterClass E.g., '5-15', '15-30', '60+'.
     * @return float Diameter in centimeters.
     */
    private function getRandomDiameter($diameterClass) {
        switch ($diameterClass) {
            case '5-15': return rand(50, 149) / 10;
            case '15-30': return rand(150, 299) / 10;
            case '30-45': return rand(300, 449) / 10;
            case '45-60': return rand(450, 599) / 10;
            case '60+': return rand(600, 1000) / 10;
            default: return 0;
        }
    }

    /**
     * Generates a random height based on the tree's diameter. (Simplified model)
     * @param float $diameterCm Tree diameter in centimeters.
     * @return float Height in meters.
     */
    private function getRandomHeight($diameterCm) {
        if ($diameterCm < 15) {
            return rand(50, 100) / 10;
        } elseif ($diameterCm < 30) {
            return rand(100, 180) / 10;
        } elseif ($diameterCm < 45) {
            return rand(180, 250) / 10;
        } elseif ($diameterCm < 60) {
            return rand(250, 320) / 10;
        } else {
            return rand(300, 400) / 10;
        }
    }

    /**
     * Calculates the volume of a tree.
     * @param bool $isDipterocarp True if Dipterocarp, false otherwise.
     * @param float $diameterM DBH in meters.
     * @param float $heightM Tree height in meters.
     * @param float $diameterCm Original diameter in centimeters (for class checks).
     * @return float Calculated volume in cubic meters.
     */
    private function calculateVolume($isDipterocarp, $diameterM, $heightM, $diameterCm) {
        $volume = 0;
        $D = $diameterM;
        $L = $heightM;

        if ($isDipterocarp) {
            if ($diameterCm < 15) {
                $volume = 0.022 + 3.4 * ($D * $D);
            } elseif ($diameterCm >= 15 && $diameterCm < 45) {
                $volume = -0.0971 + 9.503 * ($D * $D);
            } else { // $diameterCm >= 45cm
                $volume = 0.015 + 2.137 * ($D * $D) + 0.513 * ($D * $D) * $L;
            }
        } else { // NonDipterocarp
            if ($diameterCm < 30) {
                $volume = 0.03 + 2.8 * ($D * $D);
            } elseif ($diameterCm >= 30 && $diameterCm < 45) {
                $volume = -0.331 + 6.694 * ($D * $D);
            } else { // $diameterCm >= 45cm
                $volume = -0.0023 + 2.942 * ($D * $D) + 0.262 * ($D * $D) * $L;
            }
        }
        return $volume;
    }
}
?>