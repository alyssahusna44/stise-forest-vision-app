-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 05:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `forest_db`
--
CREATE DATABASE IF NOT EXISTS `forest_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `forest_db`;

-- --------------------------------------------------------

--
-- Table structure for table `base_forest`
--

CREATE TABLE `base_forest` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diameter_classes`
--

CREATE TABLE `diameter_classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `min_diameter` decimal(10,2) NOT NULL,
  `max_diameter` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulation_results_45`
--

CREATE TABLE `simulation_results_45` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `prod` decimal(10,4) DEFAULT NULL,
  `cut_angle` decimal(5,2) DEFAULT NULL,
  `damage_stem` decimal(10,4) DEFAULT NULL,
  `damage_crown` decimal(10,4) DEFAULT NULL,
  `diameter30` decimal(5,2) DEFAULT NULL,
  `volume30` decimal(10,4) DEFAULT NULL,
  `prod30` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulation_results_50`
--

CREATE TABLE `simulation_results_50` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `prod` decimal(10,4) DEFAULT NULL,
  `cut_angle` decimal(5,2) DEFAULT NULL,
  `damage_stem` decimal(10,4) DEFAULT NULL,
  `damage_crown` decimal(10,4) DEFAULT NULL,
  `diameter30` decimal(5,2) DEFAULT NULL,
  `volume30` decimal(10,4) DEFAULT NULL,
  `prod30` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulation_results_55`
--

CREATE TABLE `simulation_results_55` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `prod` decimal(10,4) DEFAULT NULL,
  `cut_angle` decimal(5,2) DEFAULT NULL,
  `damage_stem` decimal(10,4) DEFAULT NULL,
  `damage_crown` decimal(10,4) DEFAULT NULL,
  `diameter30` decimal(5,2) DEFAULT NULL,
  `volume30` decimal(10,4) DEFAULT NULL,
  `prod30` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulation_results_60`
--

CREATE TABLE `simulation_results_60` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `prod` decimal(10,4) DEFAULT NULL,
  `cut_angle` decimal(5,2) DEFAULT NULL,
  `damage_stem` decimal(10,4) DEFAULT NULL,
  `damage_crown` decimal(10,4) DEFAULT NULL,
  `diameter30` decimal(5,2) DEFAULT NULL,
  `volume30` decimal(10,4) DEFAULT NULL,
  `prod30` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulation_results_65`
--

CREATE TABLE `simulation_results_65` (
  `tree_id` varchar(20) NOT NULL,
  `block_i` int(11) DEFAULT NULL,
  `block_j` int(11) DEFAULT NULL,
  `coord_x` int(11) DEFAULT NULL,
  `coord_y` int(11) DEFAULT NULL,
  `species_id` int(11) DEFAULT NULL,
  `species_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `diameter` decimal(5,2) DEFAULT NULL,
  `diameter_class` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL,
  `prod` decimal(10,4) DEFAULT NULL,
  `cut_angle` decimal(5,2) DEFAULT NULL,
  `damage_stem` decimal(10,4) DEFAULT NULL,
  `damage_crown` decimal(10,4) DEFAULT NULL,
  `diameter30` decimal(5,2) DEFAULT NULL,
  `volume30` decimal(10,4) DEFAULT NULL,
  `prod30` decimal(10,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `species`
--

CREATE TABLE `species` (
  `species_id` int(11) NOT NULL,
  `species_code` varchar(10) DEFAULT NULL,
  `local_name` varchar(100) DEFAULT NULL,
  `species_group` int(11) DEFAULT NULL,
  `roy_class` varchar(50) DEFAULT NULL,
  `is_dipterocarp` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `species_group_names`
--

CREATE TABLE `species_group_names` (
  `species_group` int(11) NOT NULL,
  `speciesgroup_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `base_forest`
--
ALTER TABLE `base_forest`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `diameter_classes`
--
ALTER TABLE `diameter_classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `simulation_results_45`
--
ALTER TABLE `simulation_results_45`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `simulation_results_50`
--
ALTER TABLE `simulation_results_50`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `simulation_results_55`
--
ALTER TABLE `simulation_results_55`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `simulation_results_60`
--
ALTER TABLE `simulation_results_60`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `simulation_results_65`
--
ALTER TABLE `simulation_results_65`
  ADD PRIMARY KEY (`tree_id`);

--
-- Indexes for table `species`
--
ALTER TABLE `species`
  ADD PRIMARY KEY (`species_id`);

--
-- Indexes for table `species_group_names`
--
ALTER TABLE `species_group_names`
  ADD PRIMARY KEY (`species_group`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diameter_classes`
--
ALTER TABLE `diameter_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
