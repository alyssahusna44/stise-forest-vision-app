# Forest Vision: Sustainable Forest Management Simulator

A PHP and MySQL web application that simulates a virtual tropical forest over 30 years and compares different harvesting strategies (cutting regimes) by their long-term impact on timber yield and forest structure.

## What it does

- **Generates a base forest:** 100 hectares (10 x 10 blocks, 50,000 trees) with species, diameter, height, spatial coordinates and volume for every tree.
- **Simulates cutting regimes:** harvest trees above a diameter-at-breast-height (DBH) threshold of 45, 50, 55, 60 or 65 cm. Each tree is classified as **Cut**, **Victim** (collateral logging damage), **Keep** or **Stand**.
- **Projects growth 30 years ahead** for the remaining trees.
- **Reports and visualises the results:**
  - Final output table (production, damage and remainder volume by species group)
  - Stand tables by species group and diameter class (production, damage, remainder, 30-year projections)
  - Diameter distribution histograms (current vs 30-year projection)
  - Interactive spatial map of trees per block (zoom and pan)
  - Cutting regime analysis page with recommendations

In the simulated data, the 55-60 cm regime gave the best balance between timber yield and long-term forest structure.

## Tech stack

- **Backend:** PHP (mysqli), MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Charts:** [Chart.js](https://www.chartjs.org/) 3.9.1 and chartjs-plugin-zoom (loaded from a CDN, so an internet connection is needed)

## Getting started

### Requirements

- PHP 7.4 or newer with the `mysqli` extension enabled
- MySQL or MariaDB
- [XAMPP](https://www.apachefriends.org/) or Laragon is the easiest way to get all of the above on Windows

### Setup

1. **Clone the repo**

   ```bash
   git clone https://github.com/alyssahusna44/stise-forest-vision-app.git
   cd stise-forest-vision-app
   ```

2. **Create the database.** Start MySQL, open phpMyAdmin, and create a database named `forest_db` (collation `utf8mb4_general_ci`). Then import one of the files in `data/`:
   - `forest_db.sql`: empty schema only
   - `forest_db_with_data.sql`: schema plus the initial dataset

3. **Create your config file.** Copy `config.example.php` to `config.php` and set your own database details.

4. **Run the app.** Either:
   - **XAMPP:** copy the project folder into `htdocs`, start Apache and MySQL, then open `http://localhost/stise-forest-vision-app/public/home.php`
   - **PHP built-in server:** from the project root (not from `public/`), run `php -S localhost:8000` and open `http://localhost:8000/public/home.php`

### First-time workflow

1. Open **Forest Generation**.
2. Click **Import Species from CSV** (loads `data/species.csv` into the `species` table).
3. Click **Generate New Base Forest** (fills `base_forest` with the 50,000 trees).
4. Click **Run Simulation** for each regime you want (45, 50, 55, 60, 65 cm). Each run creates its own `simulation_results_XX` table.
5. Explore **Final Output Table**, **Stand Tables**, **Visualisation** and **Cutting Regime Analysis**.

> Running a simulation clears the previous results for that regime. Generating a new base forest clears the existing tree data.

## Project structure

```
api/        JSON endpoints used by the charts (histogram and plot data)
data/       Database schema, schema with data, and species.csv
includes/   Core classes: db connection, ForestGenerator, ForestSimulator, SpeciesManager
public/     Web pages, CSS and images (start at home.php)
config.php  Database configuration (not committed; copy from config.example.php)
```

## Database

| Table | Purpose |
|---|---|
| `species` | Species list with species group, royalty class and dipterocarp flag |
| `species_group_names` | Names of the species groups |
| `diameter_classes` | Diameter class ranges (5-15, 15-30, 30-45, 45-60, 60+ cm) |
| `base_forest` | The generated Year 0 forest, one row per tree |
| `simulation_results_XX` | Per-tree results for the XX cm regime (status, production, damage, 30-year projection) |

## Limitations

- The forest is a simplified virtual environment with predefined species groups, not real inventory data.
- Intended for learning and demonstration.

## License

This project was part of academic coursework under the subject ISB46803 SELECTED TOPICS IN SOFTWARE ENGINEERING in UniKL MIIT.