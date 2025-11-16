-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 07:08 AM
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
(10, 'Sophia', 'Del Rosario', 'Psychiatry', 10123, 'sophia.delrosario@mindhealth.com', 'National Center for Mental Health, Mandaluyong', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `medicationID` int(11) UNSIGNED NOT NULL,
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
(9, 'Amlodipine', 'Norvasc', 'Tablet', 5, 'Pfizer', 100);

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
  `doctorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patientID`, `firstName`, `lastName`, `birthDate`, `gender`, `contactNumber`, `address`, `email`, `doctorID`) VALUES
(1, 'Juan', 'Dela Cruz', '1990-05-12', 'Male', 912345678, 'Batangas City', 'juan.delacruz@example.com', 1),
(2, 'Maria', 'Santos', '1988-09-23', 'Female', 923456789, 'Quezon City', 'maria.santos@example.com', 2),
(3, 'Jose', 'Reyes', '1975-02-10', 'Male', 934567890, 'Cebu City', 'jose.reyes@example.com', 3),
(4, 'Ana', 'Ramos', '1995-11-30', 'Female', 945678901, 'Davao City', 'ana.ramos@example.com', 4),
(5, 'Carlos', 'Garcia', '1982-03-15', 'Male', 956789012, 'Pasig City', 'carlos.garcia@example.com', 5),
(6, 'Liza', 'Torres', '2000-07-08', 'Female', 967890123, 'Iloilo City', 'liza.torres@example.com', 6),
(7, 'Mark', 'Lim', '1998-04-25', 'Male', 978901234, 'Makati City', 'mark.lim@example.com', 7),
(8, 'Patricia', 'Mendoza', '1993-06-18', 'Female', 989012345, 'Taguig City', 'patricia.mendoza@example.com', 8),
(9, 'Andrew', 'Lopez', '1987-12-01', 'Male', 990123456, 'Manila', 'andrew.lopez@example.com', 9),
(10, 'Sophia', 'De Guzman', '1999-10-05', 'Female', 901234567, 'Cavite', 'sophia.deguzman@example.com', 10);

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
(6, 'CityMeds Pharmacy', '34 Bonifacio St', '09771234098', 'citymeds@example.com', 'Room 12 Baguio Medical Center');

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
  `refillInterval` date NOT NULL,
  `status` text NOT NULL,
  `doctorID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescriptionID`, `medicationID`, `patientID`, `issueDate`, `expirationDate`, `refillInterval`, `status`, `doctorID`) VALUES
(1, 2, 2, '2025-02-02', '2025-04-03', '2025-02-01', 'Expired', 1),
(2, 2, 2, '2025-01-05', '2025-04-05', '2025-02-04', 'Active', 2),
(3, 3, 3, '2025-01-10', '2025-04-10', '2025-02-09', 'Expired', 3),
(4, 4, 4, '2025-02-01', '2025-05-01', '2025-03-01', 'Active', 4),
(5, 5, 5, '2025-02-10', '2025-05-10', '2025-03-12', 'Active', 5),
(6, 6, 6, '2025-02-15', '2025-05-15', '2025-03-17', 'Active', 6),
(7, 7, 7, '2025-02-20', '2025-05-20', '2025-03-22', 'Active', 7),
(8, 8, 8, '2025-03-01', '2025-06-01', '2025-04-01', 'Active', 8),
(9, 9, 9, '2025-03-05', '2025-06-05', '2025-04-04', 'Active', 9);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptionitem`
--

CREATE TABLE `prescriptionitem` (
  `doctorID` int(11) NOT NULL,
  `prescriptionItemID` int(11) NOT NULL,
  `prescriptionID` int(11) NOT NULL,
  `medicationID` int(11) UNSIGNED NOT NULL,
  `dosage` text NOT NULL,
  `frequency` text NOT NULL,
  `duration` text NOT NULL,
  `prescribed_amount` int(11) NOT NULL,
  `refill_count` int(11) NOT NULL,
  `instructions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptionitem`
--

INSERT INTO `prescriptionitem` (`doctorID`, `prescriptionItemID`, `prescriptionID`, `medicationID`, `dosage`, `frequency`, `duration`, `prescribed_amount`, `refill_count`, `instructions`) VALUES
(3, 44, 101, 1, '500 mg', '3 times a day', '5 days', 15, 0, 'Take after meals'),
(3, 45, 101, 3, '500 mg', '2 times a day', '7 days', 14, 0, 'Complete the full course'),
(3, 46, 101, 8, '10 mg', 'Once daily', '10 days', 10, 1, 'Take at night'),
(4, 47, 102, 2, '200 mg', 'Every 6 hours', '3 days', 12, 0, 'Take with water'),
(4, 48, 102, 4, '10 mg', 'Once daily', '5 days', 5, 0, 'Avoid allergens while taking'),
(2, 49, 103, 5, '850 mg', 'Twice a day', '30 days', 60, 2, 'Take with breakfast and dinner'),
(2, 50, 104, 7, '40 mg', 'Once daily', '14 days', 14, 1, 'Take before breakfast'),
(2, 51, 104, 9, '5 mg', 'Once daily', '30 days', 30, 1, 'Monitor blood pressure'),
(5, 52, 105, 6, '20 mg', 'Once daily', '30 days', 30, 2, 'Take at the same time daily'),
(5, 53, 105, 1, '500 mg', 'As needed', '7 days', 20, 0, 'For fever or pain'),
(6, 54, 106, 3, '500 mg', 'Three times a day', '10 days', 30, 0, 'Finish all medication'),
(6, 55, 106, 1, '500 mg', 'Every 8 hours', '7 days', 21, 0, 'Take with food'),
(6, 56, 107, 2, '200 mg', 'Every 4 hours', '2 days', 12, 0, 'Do not exceed recommended dose'),
(7, 57, 108, 4, '10 mg', 'Once daily', '14 days', 14, 1, 'Non-drowsy formula'),
(7, 58, 108, 8, '10 mg', 'Once daily', '10 days', 10, 1, 'Take before bed'),
(8, 59, 109, 5, '850 mg', 'Twice a day', '60 days', 120, 3, 'Long-term maintenance'),
(8, 60, 109, 9, '5 mg', 'Once daily', '30 days', 30, 2, 'Check BP weekly'),
(3, 61, 110, 7, '40 mg', 'Once daily', '21 days', 21, 1, 'Take before eating'),
(3, 62, 110, 2, '200 mg', 'Every 8 hours', '5 days', 15, 0, 'Hydrate well'),
(9, 63, 111, 6, '20 mg', 'Once daily', '30 days', 30, 1, 'Maintain routine schedule'),
(4, 64, 112, 3, '500 mg', 'Twice daily', '10 days', 20, 0, 'Complete dosage'),
(4, 65, 112, 4, '10 mg', 'Once daily', '5 days', 5, 0, 'Avoid cold drinks'),
(10, 66, 113, 1, '500 mg', 'Every 6 hours', '6 days', 24, 0, 'Pain management'),
(10, 67, 113, 6, '20 mg', 'Once daily', '30 days', 30, 2, 'Take regularly'),
(3, 68, 114, 8, '10 mg', 'Once daily', '7 days', 7, 0, 'Take at bedtime'),
(11, 69, 115, 2, '200 mg', 'Every 6 hours', '4 days', 16, 0, 'With food'),
(11, 70, 116, 9, '5 mg', 'Once daily', '90 days', 90, 3, 'Long-term therapy'),
(11, 71, 116, 5, '850 mg', 'Twice daily', '90 days', 180, 3, 'Maintain diet'),
(12, 72, 117, 7, '40 mg', 'Once daily', '30 days', 30, 1, 'Avoid spicy food'),
(12, 73, 117, 3, '500 mg', 'Every 12 hours', '10 days', 20, 0, 'Take until symptoms improve'),
(6, 74, 118, 1, '500 mg', 'As needed', '10 days', 30, 0, 'Do not exceed 4 doses/day'),
(6, 75, 118, 8, '10 mg', 'Once daily', '5 days', 5, 0, 'For allergies'),
(5, 76, 119, 6, '20 mg', 'Once daily', '14 days', 14, 0, 'Morning dose recommended'),
(5, 77, 119, 4, '10 mg', 'Once daily', '14 days', 14, 1, 'Avoid alcohol'),
(7, 78, 120, 2, '200 mg', 'Every 6 hours', '5 days', 20, 0, 'Pain or fever relief'),
(7, 79, 120, 3, '500 mg', 'Twice daily', '7 days', 14, 0, 'Complete regimen'),
(7, 80, 120, 9, '5 mg', 'Once daily', '30 days', 30, 1, 'Blood pressure control');

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
  ADD PRIMARY KEY (`patientID`),
  ADD KEY `fk_patient_doctor` (`doctorID`);

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
  ADD KEY `medID` (`medicationID`),
  ADD KEY `fk_prescription_doctor` (`doctorID`);

--
-- Indexes for table `prescriptionitem`
--
ALTER TABLE `prescriptionitem`
  ADD PRIMARY KEY (`prescriptionItemID`),
  ADD KEY `fk_medicationID` (`medicationID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `prescriptionitem`
--
ALTER TABLE `prescriptionitem`
  MODIFY `prescriptionItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_doctor` FOREIGN KEY (`doctorID`) REFERENCES `doctor` (`doctorID`);

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `fk_prescription_doctor` FOREIGN KEY (`doctorID`) REFERENCES `doctor` (`doctorID`);

--
-- Constraints for table `prescriptionitem`
--
ALTER TABLE `prescriptionitem`
  ADD CONSTRAINT `fk_medicationID` FOREIGN KEY (`medicationID`) REFERENCES `medication` (`medicationID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
