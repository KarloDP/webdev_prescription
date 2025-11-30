-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 30, 2025 at 02:32 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

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

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `adminID` int NOT NULL AUTO_INCREMENT,
  `firstName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`adminID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminID`, `firstName`, `lastName`, `password`) VALUES
(1, 'Alice', 'Johnson', 'alice'),
(2, 'Benjamin', 'Lopez', 'benjamin'),
(3, 'Clara', 'Hughes', 'clara'),
(4, 'Daniel', 'Parker', 'daniel'),
(5, 'Elena', 'Mitchell', 'elena'),
(6, 'Clara', 'Hughes', 'clara');

-- --------------------------------------------------------

--
-- Table structure for table `dispenserecord`
--

DROP TABLE IF EXISTS `dispenserecord`;
CREATE TABLE IF NOT EXISTS `dispenserecord` (
  `prescriptionItemID` int NOT NULL,
  `pharmacyID` int NOT NULL,
  `dispenseID` int NOT NULL,
  `quantityDispensed` int NOT NULL,
  `dateDispensed` date NOT NULL,
  `pharmacistName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nextAvailableDates` date NOT NULL,
  PRIMARY KEY (`dispenseID`),
  KEY `PrescItemID` (`prescriptionItemID`),
  KEY `pharmacyID` (`pharmacyID`)
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

DROP TABLE IF EXISTS `doctor`;
CREATE TABLE IF NOT EXISTS `doctor` (
  `doctorID` int NOT NULL AUTO_INCREMENT,
  `firstName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `specialization` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `licenseNumber` int NOT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clinicAddress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`doctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctorID`, `firstName`, `lastName`, `password`, `specialization`, `licenseNumber`, `email`, `clinicAddress`, `status`) VALUES
(1, 'Antonio', 'Santos', 'doctor', 'Cardiology', 12345, 'antonio.santos@medph.com', 'St. Luke\'s Medical Center, Quezon City', 'active'),
(2, 'Maria', 'Reyes', 'doctor', 'Pediatrics', 23456, 'maria.reyes@childcareph.com', 'The Medical City, Pasig', 'active'),
(3, 'Jose', 'Ramos', 'doctor', 'Dermatology', 34567, 'jose.ramos@skincareph.com', 'Makati Medical Center, Makati', 'active'),
(4, 'Ana', 'Garcia', 'doctor', 'Neurology', 45678, 'ana.garcia@neuroph.com', 'Cardinal Santos Hospital, San Juan', 'active'),
(5, 'Carlo', 'Lim', 'doctor', 'Orthopedics', 56789, 'carlo.lim@orthohealth.com', 'Asian Hospital and Medical Center, Alabang', 'active'),
(6, 'Liza', 'Mendoza', 'doctor', 'Internal Medicine', 67890, 'liza.mendoza@medcenter.com', 'Perpetual Help Medical Center, Las Piñas', 'active'),
(7, 'Mark', 'De Guzman', 'doctor', 'Ophthalmology', 78901, 'mark.deguzman@visioncare.com', 'Manila Doctors Hospital, Manila', 'active'),
(8, 'Patricia', 'Lopez', 'doctor', 'Obstetrics', 89012, 'patricia.lopez@womenhealth.com', 'UERM Medical Center, Sta. Mesa', 'active'),
(9, 'David', 'Chua', 'doctor', 'ENT', 90123, 'david.chua@earnoseph.com', 'Chinese General Hospital, Manila', 'active'),
(10, 'Sophia', 'Del Rosario', 'doctor', 'Psychiatry', 10123, 'sophia.delrosario@mindhealth.com', 'National Center for Mental Health, Mandaluyong', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

DROP TABLE IF EXISTS `medication`;
CREATE TABLE IF NOT EXISTS `medication` (
  `medicationID` int UNSIGNED NOT NULL,
  `genericName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `brandName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `form` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `strength` int NOT NULL,
  `manufacturer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `stock` int DEFAULT NULL,
  PRIMARY KEY (`medicationID`)
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

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `patientID` int NOT NULL AUTO_INCREMENT,
  `firstName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `birthDate` date NOT NULL,
  `gender` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contactNumber` int NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doctorID` int DEFAULT NULL,
  `healthCondition` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `allergies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `currentMedication` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `knownDiseases` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`patientID`),
  KEY `fk_patient_doctor` (`doctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patientID`, `firstName`, `lastName`, `password`, `birthDate`, `gender`, `contactNumber`, `address`, `email`, `doctorID`, `healthCondition`, `allergies`, `currentMedication`, `knownDiseases`) VALUES
(1, 'Juan', 'Dela Cruz', 'patient', '1990-05-12', 'Male', 912345678, 'Batangas City', 'juan.delacruz@example.com', 1, NULL, NULL, NULL, NULL),
(2, 'Maria', 'Santos', 'patient', '1988-09-23', 'Female', 923456789, 'Quezon City', 'maria.santos@example.com', 2, NULL, NULL, NULL, NULL),
(3, 'Jose', 'Reyes', 'patient', '1975-02-10', 'Male', 934567890, 'Cebu City', 'jose.reyes@example.com', 3, NULL, NULL, NULL, NULL),
(4, 'Ana', 'Ramos', 'patient', '1995-11-30', 'Female', 945678901, 'Davao City', 'ana.ramos@example.com', 4, NULL, NULL, NULL, NULL),
(5, 'Carlos', 'Garcia', 'patient', '1982-03-15', 'Male', 956789012, 'Pasig City', 'carlos.garcia@example.com', 5, NULL, NULL, NULL, NULL),
(6, 'Liza', 'Torres', 'patient', '2000-07-08', 'Female', 967890123, 'Iloilo City', 'liza.torres@example.com', 6, NULL, NULL, NULL, NULL),
(7, 'Mark', 'Lim', 'patient', '1998-04-25', 'Male', 978901234, 'Makati City', 'mark.lim@example.com', 7, NULL, NULL, NULL, NULL),
(8, 'Patricia', 'Mendoza', 'patient', '1993-06-18', 'Female', 989012345, 'Taguig City', 'patricia.mendoza@example.com', 8, NULL, NULL, NULL, NULL),
(9, 'Andrew', 'Lopez', 'patient', '1987-12-01', 'Male', 990123456, 'Manila', 'andrew.lopez@example.com', 9, NULL, NULL, NULL, NULL),
(10, 'Sophia', 'De Guzman', 'patient', '1999-10-05', 'Female', 901234567, 'Cavite', 'sophia.deguzman@example.com', 10, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy`
--

DROP TABLE IF EXISTS `pharmacy`;
CREATE TABLE IF NOT EXISTS `pharmacy` (
  `pharmacyID` int NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contactNumber` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `clinicAddress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`pharmacyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacy`
--

INSERT INTO `pharmacy` (`pharmacyID`, `name`, `password`, `address`, `contactNumber`, `email`, `clinicAddress`, `status`) VALUES
(1, 'HealthPlus Pharmacy', 'pharmacy', '123 Rizal St', '09171234567', 'healthplus@example.com', 'Unit 5 Medical Plaza Baguio', 'active'),
(2, 'CareWell Pharmacy', 'pharmacy', '45 Session Road', '09182345678', 'carewell@example.com', 'Room 204 Saint Louis Hospital', 'active'),
(3, 'MediServe Pharmacy', 'pharmacy', '88 Aurora Hill', '09273456789', 'mediserve@example.com', '2F Pines Doctors Clinic', 'active'),
(4, 'WellLife Pharmacy', 'pharmacy', '21 Legarda Road', '09384567890', 'welllife@example.com', 'G5 University Clinic Center', 'active'),
(5, 'GreenCare Pharmacy', 'pharmacy', '67 Marcos Highway', '09495678901', 'greencare@example.com', 'Health Wing - Baguio General Hospital', 'pending'),
(6, 'CityMeds Pharmacy', 'pharmacy', '34 Bonifacio St', '09771234098', 'citymeds@example.com', 'Room 12 Baguio Medical Center', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

DROP TABLE IF EXISTS `prescription`;
CREATE TABLE IF NOT EXISTS `prescription` (
  `prescriptionID` int NOT NULL AUTO_INCREMENT,
  `patientID` int NOT NULL,
  `issueDate` date NOT NULL,
  `expirationDate` date NOT NULL,
  `status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `doctorID` int NOT NULL,
  PRIMARY KEY (`prescriptionID`),
  KEY `patientID` (`patientID`),
  KEY `fk_prescription_doctor` (`doctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescriptionID`, `patientID`, `issueDate`, `expirationDate`, `status`, `doctorID`) VALUES
(1, 2, '2025-02-02', '2025-04-03', 'Expired', 1),
(2, 2, '2025-01-05', '2025-04-05', 'Active', 2),
(3, 3, '2025-01-10', '2025-04-10', 'Expired', 3),
(4, 4, '2025-02-01', '2025-05-01', 'Active', 4),
(5, 5, '2025-02-10', '2025-05-10', 'Active', 5),
(6, 6, '2025-02-15', '2025-05-15', 'Active', 6),
(7, 7, '2025-02-20', '2025-05-20', 'Active', 7),
(8, 8, '2025-03-01', '2025-06-01', 'Active', 8),
(9, 9, '2025-03-05', '2025-06-05', 'Active', 9);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptionitem`
--

DROP TABLE IF EXISTS `prescriptionitem`;
CREATE TABLE IF NOT EXISTS `prescriptionitem` (
  `doctorID` int NOT NULL,
  `prescriptionItemID` int NOT NULL AUTO_INCREMENT,
  `prescriptionID` int NOT NULL,
  `medicationID` int UNSIGNED NOT NULL,
  `dosage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `frequency` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `duration` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prescribed_amount` int NOT NULL,
  `refill_count` int NOT NULL,
  `refillInterval` date NOT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`prescriptionItemID`),
  KEY `fk_medicationID` (`medicationID`),
  KEY `fk_prescriptionID` (`prescriptionID`),
  KEY `fk_doctorID` (`doctorID`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptionitem`
--

INSERT INTO `prescriptionitem` (`doctorID`, `prescriptionItemID`, `prescriptionID`, `medicationID`, `dosage`, `frequency`, `duration`, `prescribed_amount`, `refill_count`, `refillInterval`, `instructions`) VALUES
(1, 1, 1, 2, '500 mg', '3 times a day', '5 days', 15, 0, '0000-00-00', 'Take after meals'),
(1, 82, 1, 3, '500 mg', '2 times a day', '7 days', 14, 0, '0000-00-00', 'Complete the full course'),
(2, 83, 2, 4, '10 mg', 'Once daily', '10 days', 10, 1, '0000-00-00', 'Take at night'),
(2, 84, 2, 2, '200 mg', 'Every 6 hours', '3 days', 12, 0, '0000-00-00', 'Take with water'),
(3, 85, 3, 1, '500 mg', 'As needed', '7 days', 20, 0, '0000-00-00', 'For fever or pain'),
(3, 86, 3, 3, '500 mg', 'Three times a day', '10 days', 30, 0, '0000-00-00', 'Finish full course'),
(4, 87, 4, 5, '850 mg', 'Twice a day', '30 days', 60, 2, '0000-00-00', 'Take with breakfast and dinner'),
(5, 88, 5, 6, '20 mg', 'Once daily', '30 days', 30, 2, '0000-00-00', 'Take at the same time daily'),
(5, 89, 5, 1, '500 mg', 'Every 6 hours', '6 days', 24, 0, '0000-00-00', 'Pain management'),
(6, 90, 6, 7, '40 mg', 'Once daily', '14 days', 14, 1, '0000-00-00', 'Take before breakfast'),
(6, 91, 6, 2, '200 mg', 'Every 8 hours', '5 days', 15, 0, '0000-00-00', 'Hydrate well'),
(7, 92, 7, 9, '5 mg', 'Once daily', '30 days', 30, 1, '0000-00-00', 'Monitor blood pressure'),
(7, 93, 7, 3, '500 mg', 'Twice daily', '7 days', 14, 0, '0000-00-00', 'Complete regimen'),
(8, 94, 8, 8, '10 mg', 'Once daily', '10 days', 10, 1, '0000-00-00', 'Take before bed'),
(8, 95, 8, 5, '850 mg', 'Twice a day', '60 days', 120, 3, '0000-00-00', 'Long-term maintenance'),
(9, 96, 9, 4, '10 mg', 'Once daily', '14 days', 14, 1, '0000-00-00', 'Non-drowsy formula'),
(9, 97, 9, 6, '20 mg', 'Once daily', '30 days', 30, 2, '0000-00-00', 'Take regularly'),
(1, 98, 1, 8, '10 mg', 'Once daily', '7 days', 7, 0, '0000-00-00', 'For allergies'),
(2, 99, 2, 9, '5 mg', 'Once daily', '90 days', 90, 3, '0000-00-00', 'Long-term therapy'),
(3, 100, 3, 5, '850 mg', 'Twice daily', '90 days', 180, 3, '0000-00-00', 'Maintain diet'),
(4, 101, 4, 1, '500 mg', 'As needed', '10 days', 30, 0, '0000-00-00', 'Do not exceed 4 doses/day'),
(5, 102, 5, 2, '200 mg', 'Every 4 hours', '2 days', 12, 0, '0000-00-00', 'Do not exceed recommended dose'),
(6, 103, 6, 3, '500 mg', 'Every 12 hours', '10 days', 20, 0, '0000-00-00', 'Take until symptoms improve'),
(7, 104, 7, 1, '500 mg', 'Every 8 hours', '7 days', 21, 0, '0000-00-00', 'Take with food'),
(8, 105, 8, 2, '200 mg', 'Every 6 hours', '5 days', 20, 0, '0000-00-00', 'Pain or fever relief'),
(9, 106, 9, 7, '40 mg', 'Once daily', '30 days', 30, 1, '0000-00-00', 'Avoid spicy food'),
(1, 107, 1, 9, '5 mg', 'Once daily', '30 days', 30, 1, '0000-00-00', 'Check BP weekly'),
(2, 108, 2, 6, '20 mg', 'Once daily', '14 days', 14, 0, '0000-00-00', 'Morning dose recommended'),
(3, 109, 3, 4, '10 mg', 'Once daily', '14 days', 14, 1, '0000-00-00', 'Avoid alcohol');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_doctor` FOREIGN KEY (`doctorID`) REFERENCES `doctor` (`doctorID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `fk_prescription_doctor` FOREIGN KEY (`doctorID`) REFERENCES `patient` (`patientID`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `prescriptionitem`
--
ALTER TABLE `prescriptionitem`
  ADD CONSTRAINT `fk_doctorID` FOREIGN KEY (`doctorID`) REFERENCES `doctor` (`doctorID`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_medicationID` FOREIGN KEY (`medicationID`) REFERENCES `medication` (`medicationID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prescriptionID` FOREIGN KEY (`prescriptionID`) REFERENCES `prescription` (`prescriptionID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
