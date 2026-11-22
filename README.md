# Forest Vision: Sustainable Forest Management Simulator

A PHP and MySQL web application that simulates sustainable forest management and visualises the long-term (30-year) impact of different harvesting strategies.

Built for **ISB46803 Selected Topics in Software Engineering** (UniKL MIIT, 2025).

## What it does

1. **Species management:** import tree species data from a CSV file into the database.
2. **Base forest generation:** create a realistic virtual forest to start from.
3. **Cutting-regime simulation:** apply different minimum cutting diameters (45, 50, 55, 60 and 65 cm DBH) and project the forest 30 years ahead.
4. **Results and analysis:** final output, stand tables and comparative analysis of each regime.
5. **Visualisation:** diameter-distribution histograms and an interactive spatial scatter plot (zoom and pan) of tree locations.

The accompanying report recommends the 55–60 cm DBH cutting regime as the best balance between ecological integrity and economic return.

## Tech stack

- **Backend:** PHP, MySQL (via `mysqli`)
- **Frontend:** HTML, CSS, JavaScript
- **Charts:** Chart.js 3.9.1 and chartjs-plugin-zoom

## Project structure

```
api/        JSON endpoints that feed the charts (histogram and spatial data)
data/       Database schema, schema with sample data, and species.csv
includes/   Core classes: ForestGenerator, ForestSimulator, SpeciesManager, DB connection
public/     Web pages, stylesheet and image assets
config.example.php   Database settings template
```

## Setup

You need a local PHP and MySQL environment (for example XAMPP).

1. Place the project folder in your web server directory (for example `htdocs` in XAMPP).
2. Create a MySQL database named `forest_db`.
3. Import `data/forest_db_with_data.sql` (schema plus sample data), or `data/forest_db.sql` for an empty schema.
4. Copy `config.example.php` to `config.php` and set your database details.
5. Start Apache and MySQL, then open the `public/` folder in your browser, for example `http://localhost/<project-folder>/public/`.

## Documentation

The full project report (database design, page walkthroughs, code structure, findings and recommendations) is in [`docs/Forest_Vision_Project_Report.pdf`](docs/Forest_Vision_Project_Report.pdf).

## Authors

- Alyssa Husna binti Jamarizan: application code and project documentation
- Hairiyana Shazliyana binti Hawazil: presentation slides

## Status

Coursework project. Development is currently on hold.
