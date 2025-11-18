-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 12:54 PM
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
-- Database: `webdev_prescription`
--

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
  `email` text NOT NULL,
  `doctorID` int(11) NOT NULL,
  `healthCondition` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `currentMedication` text DEFAULT NULL,
  `knownDiseases` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patientID`, `firstName`, `lastName`, `birthDate`, `gender`, `contactNumber`, `address`, `email`, `doctorID`, `healthCondition`, `allergies`, `currentMedication`, `knownDiseases`) VALUES
(1, 'Juan', 'Dela Cruz', '1990-05-12', 'Male', 912345678, 'Batangas City', 'juan.delacruz@example.com', 1, NULL, NULL, NULL, NULL),
(2, 'Maria', 'Santos', '1988-09-23', 'Female', 923456789, 'Quezon City', 'maria.santos@example.com', 2, NULL, NULL, NULL, NULL),
(3, 'Jose', 'Reyes', '1975-02-10', 'Male', 934567890, 'Cebu City', 'jose.reyes@example.com', 3, NULL, NULL, NULL, NULL),
(4, 'Ana', 'Ramos', '1995-11-30', 'Female', 945678901, 'Davao City', 'ana.ramos@example.com', 4, NULL, NULL, NULL, NULL),
(5, 'Carlos', 'Garcia', '1982-03-15', 'Male', 956789012, 'Pasig City', 'carlos.garcia@example.com', 5, NULL, NULL, NULL, NULL),
(6, 'Liza', 'Torres', '2000-07-08', 'Female', 967890123, 'Iloilo City', 'liza.torres@example.com', 6, NULL, NULL, NULL, NULL),
(7, 'Mark', 'Lim', '1998-04-25', 'Male', 978901234, 'Makati City', 'mark.lim@example.com', 7, NULL, NULL, NULL, NULL),
(8, 'Patricia', 'Mendoza', '1993-06-18', 'Female', 989012345, 'Taguig City', 'patricia.mendoza@example.com', 8, NULL, NULL, NULL, NULL),
(9, 'Andrew', 'Lopez', '1987-12-01', 'Male', 990123456, 'Manila', 'andrew.lopez@example.com', 9, NULL, NULL, NULL, NULL),
(10, 'Sophia', 'De Guzman', '1999-10-05', 'Female', 901234567, 'Cavite', 'sophia.deguzman@example.com', 10, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patientID`),
  ADD KEY `fk_patient_doctor` (`doctorID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patientID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_doctor` FOREIGN KEY (`doctorID`) REFERENCES `doctor` (`doctorID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
