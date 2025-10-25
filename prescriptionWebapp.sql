-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 25, 2025 at 05:50 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dispenseRecord`
--
ALTER TABLE `dispenseRecord`
  ADD PRIMARY KEY (`dispenseID`),
  ADD KEY `PrescItemID` (`prescriptionItemID`),
  ADD KEY `pharmacyID` (`pharmacyID`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctorID`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`medicationID`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patientID`);

--
-- Indexes for table `pharmacy`
--
ALTER TABLE `pharmacy`
  ADD PRIMARY KEY (`pharmacyID`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`prescriptionID`),
  ADD KEY `patientID` (`patientID`),
  ADD KEY `medID` (`medicationID`);

--
-- Indexes for table `prescriptionItem`
--
ALTER TABLE `prescriptionItem`
  ADD PRIMARY KEY (`prescriptionItemID`),
  ADD KEY `PrescID` (`prescriptionID`),
  ADD KEY `medicationID` (`medicationID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dispenseRecord`
--
ALTER TABLE `dispenseRecord`
  ADD CONSTRAINT `PrescItemID` FOREIGN KEY (`prescriptionItemID`) REFERENCES `prescriptionItem` (`prescriptionItemID`),
  ADD CONSTRAINT `pharmacyID` FOREIGN KEY (`pharmacyID`) REFERENCES `pharmacy` (`pharmacyID`);

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `medID` FOREIGN KEY (`medicationID`) REFERENCES `medication` (`medicationID`),
  ADD CONSTRAINT `patientID` FOREIGN KEY (`patientID`) REFERENCES `patient` (`patientID`);

--
-- Constraints for table `prescriptionItem`
--
ALTER TABLE `prescriptionItem`
  ADD CONSTRAINT `PrescID` FOREIGN KEY (`prescriptionID`) REFERENCES `prescription` (`prescriptionID`),
  ADD CONSTRAINT `medicationID` FOREIGN KEY (`medicationID`) REFERENCES `medication` (`medicationID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
