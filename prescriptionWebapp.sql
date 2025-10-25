-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 25, 2025 at 05:22 AM
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
-- Database: `prescriptionWebapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `dispenseRecord`
--

CREATE TABLE `dispenseRecord` (
  `prescriptionItemID` int(11) NOT NULL,
  `pharmacyID` int(11) NOT NULL,
  `dispenseID` int(11) NOT NULL,
  `quantityDispensed` int(11) NOT NULL,
  `dateDispensed` date NOT NULL,
  `pharmacistName` text NOT NULL,
  `status` text NOT NULL,
  `nextAvailableDates` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctorID` int(11) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `specialization` text NOT NULL,
  `licenseNumber` int(11) NOT NULL,
  `email` text NOT NULL,
  `clinicAddress` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medicationID` int(11) NOT NULL,
  `genericName` text NOT NULL,
  `brandName` text NOT NULL,
  `form` text NOT NULL,
  `strength` int(11) NOT NULL,
  `manufacturer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patientID` int(11) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL,
  `birthDate` date NOT NULL,
  `gender` text NOT NULL,
  `contactNumber` int(11) NOT NULL,
  `address` text NOT NULL,
  `email` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy`
--

CREATE TABLE `pharmacy` (
  `pharmacyID` int(11) NOT NULL,
  `name` text NOT NULL,
  `address` text NOT NULL,
  `contactNumber` int(11) NOT NULL,
  `email` text NOT NULL,
  `clinicAddress` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `prescriptionID` int(11) NOT NULL,
  `medicationID` int(11) NOT NULL,
  `patientID` int(11) NOT NULL,
  `issueDate` date NOT NULL,
  `expirationDate` date NOT NULL,
  `refillCount` int(11) NOT NULL,
  `refillInterval` date NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptionItem`
--

CREATE TABLE `prescriptionItem` (
  `prescriptionItemID` int(11) NOT NULL,
  `prescriptionID` int(11) NOT NULL,
  `medicationID` int(11) NOT NULL,
  `dosage` text NOT NULL,
  `frequency` text NOT NULL,
  `duration` text NOT NULL,
  `instructions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
