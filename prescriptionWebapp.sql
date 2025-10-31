-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 31, 2025 at 08:19 AM
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

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctorID`, `firstName`, `lastName`, `specialization`, `licenseNumber`, `email`, `clinicAddress`) VALUES
(1, 'Antonio', 'Santos', 'Cardiology', 12345, 'antonio.santos@medph.com', 'St. Luke\'s Medical Center, Quezon City'),
(2, 'Maria', 'Reyes', 'Pediatrics', 23456, 'maria.reyes@childcareph.com', 'The Medical City, Pasig'),
(3, 'Jose', 'Ramos', 'Dermatology', 34567, 'jose.ramos@skincareph.com', 'Makati Medical Center, Makati'),
(4, 'Ana', 'Garcia', 'Neurology', 45678, 'ana.garcia@neuroph.com', 'Cardinal Santos Hospital, San Juan'),
(5, 'Carlo', 'Lim', 'Orthopedics', 56789, 'carlo.lim@orthohealth.com', 'Asian Hospital and Medical Center, Alabang'),
(6, 'Liza', 'Mendoza', 'Internal Medicine', 67890, 'liza.mendoza@medcenter.com', 'Perpetual Help Medical Center, Las Piñas'),
(7, 'Mark', 'De Guzman', 'Ophthalmology', 78901, 'mark.deguzman@visioncare.com', 'Manila Doctors Hospital, Manila'),
(8, 'Patricia', 'Lopez', 'Obstetrics', 89012, 'patricia.lopez@womenhealth.com', 'UERM Medical Center, Sta. Mesa'),
(9, 'David', 'Chua', 'ENT', 90123, 'david.chua@earnoseph.com', 'Chinese General Hospital, Manila'),
(10, 'Sophia', 'Del Rosario', 'Psychiatry', 10123, 'sophia.delrosario@mindhealth.com', 'National Center for Mental Health, Mandaluyong');

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
  `manufacturer` text NOT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication`
--

INSERT INTO `medication` (`medicationID`, `genericName`, `brandName`, `form`, `strength`, `manufacturer`, `stock`) VALUES
(1, 'Paracetamol', 'Biogesic', 'Tablet', 500, 'Unilab', 100),
(2, 'Ibuprofen', 'Advil', 'Capsule', 200, 'Pfizer', 100),
(3, 'Amoxicillin', 'Amoxil', 'Capsule', 500, 'GSK', 100),
(4, 'Loratadine', 'Claritin', 'Tablet', 10, 'Bayer', 100),
(5, 'Metformin', 'Glucophage', 'Tablet', 850, 'Merck', 100),
(6, 'Simvastatin', 'Zocor', 'Tablet', 20, 'MSD', 100),
(7, 'Omeprazole', 'Losec', 'Capsule', 40, 'AstraZeneca', 100),
(8, 'Cetirizine', 'Virlix', 'Tablet', 10, 'Unilab', 100),
(9, 'Amlodipine', 'Norvasc', 'Tablet', 5, 'Pfizer', 100),
(10, 'Salbutamol', 'Ventolin', 'Syrup', 2, 'GSK', 100);

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

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patientID`, `firstName`, `lastName`, `birthDate`, `gender`, `contactNumber`, `address`, `email`) VALUES
(1, 'Juan', 'Dela Cruz', '1990-05-12', 'Male', 912345678, 'Batangas City', 'juan.delacruz@example.com'),
(2, 'Maria', 'Santos', '1988-09-23', 'Female', 923456789, 'Quezon City', 'maria.santos@example.com'),
(3, 'Jose', 'Reyes', '1975-02-10', 'Male', 934567890, 'Cebu City', 'jose.reyes@example.com'),
(4, 'Ana', 'Ramos', '1995-11-30', 'Female', 945678901, 'Davao City', 'ana.ramos@example.com'),
(5, 'Carlos', 'Garcia', '1982-03-15', 'Male', 956789012, 'Pasig City', 'carlos.garcia@example.com'),
(6, 'Liza', 'Torres', '2000-07-08', 'Female', 967890123, 'Iloilo City', 'liza.torres@example.com'),
(7, 'Mark', 'Lim', '1998-04-25', 'Male', 978901234, 'Makati City', 'mark.lim@example.com'),
(8, 'Patricia', 'Mendoza', '1993-06-18', 'Female', 989012345, 'Taguig City', 'patricia.mendoza@example.com'),
(9, 'Andrew', 'Lopez', '1987-12-01', 'Male', 990123456, 'Manila', 'andrew.lopez@example.com'),
(10, 'Sophia', 'De Guzman', '1999-10-05', 'Female', 901234567, 'Cavite', 'sophia.deguzman@example.com');

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
