-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 08:51 PM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `adminID` int(11) NOT NULL,
  `firstName` text NOT NULL,
  `lastName` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminID`, `firstName`, `lastName`) VALUES
(1, 'Alice', 'Johnson'),
(2, 'Benjamin', 'Lopez'),
(3, 'Clara', 'Hughes'),
(4, 'Daniel', 'Parker'),
(5, 'Elena', 'Mitchell');

-- --------------------------------------------------------

--
-- Table structure for table `dispenserecord`
--

CREATE TABLE `dispenserecord` (
  `prescriptionItemID` int(11) NOT NULL,
  `pharmacyID` int(11) NOT NULL,
  `dispenseID` int(11) NOT NULL,
  `quantityDispensed` int(11) NOT NULL,
  `dateDispensed` date NOT NULL,
  `pharmacistName` text NOT NULL,
  `status` text NOT NULL,
  `nextAvailableDates` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dispenserecord`
--

INSERT INTO `dispenserecord` (`prescriptionItemID`, `pharmacyID`, `dispenseID`, `quantityDispensed`, `dateDispensed`, `pharmacistName`, `status`, `nextAvailableDates`) VALUES
(1, 1, 1, 7, '2025-01-01', 'Maria Reyes', 'Dispensed', '2025-01-31'),
(2, 2, 2, 5, '2025-01-06', 'John Cruz', 'Dispensed', '2025-02-05'),
(3, 3, 3, 30, '2025-01-12', 'Anna Santos', 'Dispensed', '2025-02-11'),
(4, 4, 4, 14, '2025-01-14', 'Mark Dela Cruz', 'Dispensed', '2025-02-13'),
(5, 5, 5, 3, '2025-01-18', 'Paula Ramos', 'Dispensed', '2025-02-17'),
(6, 6, 6, 14, '2025-01-20', 'Liza Garcia', 'Dispensed', '2025-02-19'),
(7, 7, 7, 10, '2025-01-23', 'Rafael Lopez', 'Dispensed', '2025-02-22'),
(8, 8, 8, 30, '2025-01-25', 'Carla Mendoza', 'Dispensed', '2025-02-24'),
(9, 9, 9, 14, '2025-01-28', 'Simon Cruz', 'Dispensed', '2025-02-27'),
(1, 1, 10, 1, '2002-01-01', 'dan', 'dispensed', '2002-01-02'),
(1, 1, 11, 1, '2002-01-01', 'john', 'Dispensed', '2002-01-02');

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
  `clinicAddress` text NOT NULL,
  `status` enum('active','pending') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctorID`, `firstName`, `lastName`, `specialization`, `licenseNumber`, `email`, `clinicAddress`, `status`) VALUES
(1, 'Antonio', 'Santos', 'Cardiology', 12345, 'antonio.santos@medph.com', 'St. Luke\'s Medical Center, Quezon City', 'active'),
(2, 'Maria', 'Reyes', 'Pediatrics', 23456, 'maria.reyes@childcareph.com', 'The Medical City, Pasig', 'active'),
(3, 'Jose', 'Ramos', 'Dermatology', 34567, 'jose.ramos@skincareph.com', 'Makati Medical Center, Makati', 'active'),
(4, 'Ana', 'Garcia', 'Neurology', 45678, 'ana.garcia@neuroph.com', 'Cardinal Santos Hospital, San Juan', 'active'),
(5, 'Carlo', 'Lim', 'Orthopedics', 56789, 'carlo.lim@orthohealth.com', 'Asian Hospital and Medical Center, Alabang', 'active'),
(6, 'Liza', 'Mendoza', 'Internal Medicine', 67890, 'liza.mendoza@medcenter.com', 'Perpetual Help Medical Center, Las Piñas', 'active'),
(7, 'Mark', 'De Guzman', 'Ophthalmology', 78901, 'mark.deguzman@visioncare.com', 'Manila Doctors Hospital, Manila', 'active'),
(8, 'Patricia', 'Lopez', 'Obstetrics', 89012, 'patricia.lopez@womenhealth.com', 'UERM Medical Center, Sta. Mesa', 'active'),
(9, 'David', 'Chua', 'ENT', 90123, 'david.chua@earnoseph.com', 'Chinese General Hospital, Manila', 'active'),
(10, 'Sophia', 'Del Rosario', 'Psychiatry', 10123, 'sophia.delrosario@mindhealth.com', 'National Center for Mental Health, Mandaluyong', 'pending');

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
  `email` text NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patientID`, `firstName`, `lastName`, `birthDate`, `gender`, `contactNumber`, `address`, `email`,`password`) VALUES
(1, 'Juan', 'Dela Cruz', '1990-05-12', 'Male', 912345678, 'Batangas City', 'juan.delacruz@example.com', 'Juan123'),
(2, 'Maria', 'Santos', '1988-09-23', 'Female', 923456789, 'Quezon City', 'maria.santos@example.com', 'Maria123'),
(3, 'Jose', 'Reyes', '1975-02-10', 'Male', 934567890, 'Cebu City', 'jose.reyes@example.com', 'Jose123'),
(4, 'Ana', 'Ramos', '1995-11-30', 'Female', 945678901, 'Davao City', 'ana.ramos@example.com', 'Ana123'),
(5, 'Carlos', 'Garcia', '1982-03-15', 'Male', 956789012, 'Pasig City', 'carlos.garcia@example.com', 'Carlos123'),
(6, 'Liza', 'Torres', '2000-07-08', 'Female', 967890123, 'Iloilo City', 'liza.torres@example.com', 'Liza123'),
(7, 'Mark', 'Lim', '1998-04-25', 'Male', 978901234, 'Makati City', 'mark.lim@example.com', 'Mark123'),
(8, 'Patricia', 'Mendoza', '1993-06-18', 'Female', 989012345, 'Taguig City', 'patricia.mendoza@example.com', 'Patricia123'),
(9, 'Andrew', 'Lopez', '1987-12-01', 'Male', 990123456, 'Manila', 'andrew.lopez@example.com', 'Andrew123'),
(10, 'Sophia', 'De Guzman', '1999-10-05', 'Female', 901234567, 'Cavite', 'sophia.deguzman@example.com', 'Sophia123');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy`
--

CREATE TABLE `pharmacy` (
  `pharmacyID` int(11) NOT NULL,
  `name` text NOT NULL,
  `address` text NOT NULL,
  `contactNumber` varchar(20) NOT NULL,
  `email` text NOT NULL,
  `clinicAddress` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy`
--

INSERT INTO `pharmacy` (`pharmacyID`, `name`, `address`, `contactNumber`, `email`, `clinicAddress`) VALUES
(1, 'HealthPlus Pharmacy', '123 Rizal St', '09171234567', 'healthplus@example.com', 'Unit 5 Medical Plaza Baguio'),
(2, 'CareWell Pharmacy', '45 Session Road', '09182345678', 'carewell@example.com', 'Room 204 Saint Louis Hospital'),
(3, 'MediServe Pharmacy', '88 Aurora Hill', '09273456789', 'mediserve@example.com', '2F Pines Doctors Clinic'),
(4, 'WellLife Pharmacy', '21 Legarda Road', '09384567890', 'welllife@example.com', 'G5 University Clinic Center'),
(5, 'GreenCare Pharmacy', '67 Marcos Highway', '09495678901', 'greencare@example.com', 'Health Wing - Baguio General Hospital'),
(6, 'CityMeds Pharmacy', '34 Bonifacio St', '09771234098', 'citymeds@example.com', 'Room 12 Baguio Medical Center'),
(7, 'Starlight Pharmacy', '10 Navy Base St', '09982340987', 'starlight@example.com', 'Suite 6 SLU Family Clinic');

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

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescriptionID`, `medicationID`, `patientID`, `issueDate`, `expirationDate`, `refillCount`, `refillInterval`, `status`) VALUES
(1, 1, 1, '2025-01-02', '2025-04-02', 2, '2025-02-01', 'Active'),
(2, 2, 2, '2025-01-05', '2025-04-05', 1, '2025-02-04', 'Active'),
(3, 3, 3, '2025-01-10', '2025-04-10', 0, '2025-02-09', 'Expired'),
(4, 4, 4, '2025-02-01', '2025-05-01', 3, '2025-03-01', 'Active'),
(5, 5, 5, '2025-02-10', '2025-05-10', 1, '2025-03-12', 'Active'),
(6, 6, 6, '2025-02-15', '2025-05-15', 0, '2025-03-17', 'Active'),
(7, 7, 7, '2025-02-20', '2025-05-20', 2, '2025-03-22', 'Active'),
(8, 8, 8, '2025-03-01', '2025-06-01', 1, '2025-04-01', 'Active'),
(9, 9, 9, '2025-03-05', '2025-06-05', 0, '2025-04-04', 'Active'),
(10, 10, 10, '2025-03-10', '2025-06-10', 2, '2025-04-09', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptionitem`
--

CREATE TABLE `prescriptionitem` (
  `prescriptionItemID` int(11) NOT NULL,
  `prescriptionID` int(11) NOT NULL,
  `medicationID` int(11) NOT NULL,
  `dosage` text NOT NULL,
  `frequency` text NOT NULL,
  `duration` text NOT NULL,
  `instructions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptionitem`
--

INSERT INTO `prescriptionitem` (`prescriptionItemID`, `prescriptionID`, `medicationID`, `dosage`, `frequency`, `duration`, `instructions`) VALUES
(1, 1, 1, '1 tablet', 'Once daily', '7 days', 'Take after meals'),
(2, 2, 2, '1 tablet', 'Twice daily', '5 days', 'Take with water'),
(3, 3, 3, '5 ml', 'Three times daily', '10 days', 'Shake well before use'),
(4, 4, 4, '1 tablet', 'Once daily', '14 days', 'Avoid alcohol'),
(5, 5, 5, '2 tablets', 'Once daily', '3 days', 'Take before breakfast'),
(6, 6, 6, '1 capsule', 'Twice daily', '7 days', 'Swallow whole'),
(7, 7, 7, '10 ml', 'Two times daily', '5 days', 'Store in fridge'),
(8, 8, 8, '1 tablet', 'Three times daily', '10 days', 'With food'),
(9, 9, 9, '5 ml', 'Once daily', '14 days', 'Use measuring cup'),
(10, 10, 10, '1 capsule', 'Once daily', '7 days', 'Drink plenty of water');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dispenserecord`
--
ALTER TABLE `dispenserecord`
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
-- Indexes for table `prescriptionitem`
--
ALTER TABLE `prescriptionitem`
  ADD PRIMARY KEY (`prescriptionItemID`),
  ADD KEY `PrescID` (`prescriptionID`),
  ADD KEY `medicationID` (`medicationID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
