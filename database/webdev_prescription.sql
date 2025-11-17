-- --------------------------------------------------------
-- FULL DATABASE: webdev_prescription (CLEAN + FIXED)
-- --------------------------------------------------------

DROP DATABASE IF EXISTS webdev_prescription;
CREATE DATABASE webdev_prescription;
USE webdev_prescription;

-- --------------------------------------------------------
-- ADMINS
-- --------------------------------------------------------

CREATE TABLE admins (
                        adminID INT PRIMARY KEY AUTO_INCREMENT,
                        firstName TEXT NOT NULL,
                        lastName TEXT NOT NULL
);

INSERT INTO admins (firstName, lastName) VALUES
                                             ('Alice','Johnson'),
                                             ('Benjamin','Lopez'),
                                             ('Clara','Hughes'),
                                             ('Daniel','Parker'),
                                             ('Elena','Mitchell');

-- --------------------------------------------------------
-- DOCTOR
-- --------------------------------------------------------

CREATE TABLE doctor (
                        doctorID INT PRIMARY KEY AUTO_INCREMENT,
                        firstName TEXT NOT NULL,
                        lastName TEXT NOT NULL,
                        specialization TEXT NOT NULL,
                        licenseNumber INT NOT NULL,
                        email TEXT NOT NULL,
                        clinicAddress TEXT NOT NULL,
                        status ENUM('active','pending') NOT NULL DEFAULT 'pending'
);

INSERT INTO doctor (firstName, lastName, specialization, licenseNumber, email, clinicAddress, status) VALUES
                                                                                                          ('Antonio','Santos','Cardiology',12345,'antonio.santos@medph.com','St. Luke’s Medical Center','active'),
                                                                                                          ('Maria','Reyes','Pediatrics',23456,'maria.reyes@childcareph.com','The Medical City','active'),
                                                                                                          ('Jose','Ramos','Dermatology',34567,'jose.ramos@skincareph.com','Makati Medical Center','active'),
                                                                                                          ('Ana','Garcia','Neurology',45678,'ana.garcia@neuroph.com','Cardinal Santos Hospital','active'),
                                                                                                          ('Carlo','Lim','Orthopedics',56789,'carlo.lim@orthohealth.com','Asian Hospital','active'),
                                                                                                          ('Liza','Mendoza','Internal Medicine',67890,'liza.mendoza@medcenter.com','Perpetual Help Medical Center','active'),
                                                                                                          ('Mark','De Guzman','Ophthalmology',78901,'mark.deguzman@visioncare.com','Manila Doctors Hospital','active'),
                                                                                                          ('Patricia','Lopez','Obstetrics',89012,'patricia.lopez@womenhealth.com','UERM Medical Center','active'),
                                                                                                          ('David','Chua','ENT',90123,'david.chua@earnoseph.com','Chinese General Hospital','active'),
                                                                                                          ('Sophia','Del Rosario','Psychiatry',10123,'sophia.delrosario@mindhealth.com','NCMH','active');

-- --------------------------------------------------------
-- PATIENT
-- --------------------------------------------------------

CREATE TABLE patient (
                         patientID INT PRIMARY KEY AUTO_INCREMENT,
                         firstName TEXT NOT NULL,
                         lastName TEXT NOT NULL,
                         birthDate DATE NOT NULL,
                         gender TEXT NOT NULL,
                         contactNumber VARCHAR(20) NOT NULL,
                         address TEXT NOT NULL,
                         email TEXT NOT NULL,
                         doctorID INT NOT NULL,
                         healthCondition TEXT,
                         allergies TEXT,
                         currentMedication TEXT,
                         knownDiseases TEXT,

                         FOREIGN KEY (doctorID) REFERENCES doctor(doctorID)
);

INSERT INTO patient (firstName, lastName, birthDate, gender, contactNumber, address, email, doctorID) VALUES
                                                                                                          ('Juan','Dela Cruz','1990-05-12','Male','0912345678','Batangas City','juan@example.com',1),
                                                                                                          ('Maria','Santos','1988-09-23','Female','0923456789','Quezon City','maria@example.com',2),
                                                                                                          ('Jose','Reyes','1975-02-10','Male','0934567890','Cebu City','jose@example.com',3),
                                                                                                          ('Ana','Ramos','1995-11-30','Female','0945678901','Davao City','ana@example.com',4),
                                                                                                          ('Carlos','Garcia','1982-03-15','Male','0956789012','Pasig','carlos@example.com',5),
                                                                                                          ('Liza','Torres','2000-07-08','Female','0967890123','Iloilo','liza@example.com',6),
                                                                                                          ('Mark','Lim','1998-04-25','Male','0978901234','Makati','mark@example.com',7),
                                                                                                          ('Patricia','Mendoza','1993-06-18','Female','0989012345','Taguig','patricia@example.com',8),
                                                                                                          ('Andrew','Lopez','1987-12-01','Male','0990123456','Manila','andrew@example.com',9),
                                                                                                          ('Sophia','De Guzman','1999-10-05','Female','0912345677','Cavite','sophia@example.com',10);

-- --------------------------------------------------------
-- MEDICATION
-- --------------------------------------------------------

CREATE TABLE medication (
                            medicationID INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                            genericName TEXT NOT NULL,
                            brandName TEXT NOT NULL,
                            form TEXT NOT NULL,
                            strength INT NOT NULL,
                            manufacturer TEXT NOT NULL,
                            stock INT DEFAULT NULL
);

INSERT INTO medication (genericName, brandName, form, strength, manufacturer, stock) VALUES
                                                                                         ('Paracetamol','Biogesic','Tablet',500,'Unilab',100),
                                                                                         ('Ibuprofen','Advil','Capsule',200,'Pfizer',100),
                                                                                         ('Amoxicillin','Amoxil','Capsule',500,'GSK',100),
                                                                                         ('Loratadine','Claritin','Tablet',10,'Bayer',100),
                                                                                         ('Metformin','Glucophage','Tablet',850,'Merck',100),
                                                                                         ('Simvastatin','Zocor','Tablet',20,'MSD',100),
                                                                                         ('Omeprazole','Losec','Capsule',40,'AstraZeneca',100),
                                                                                         ('Cetirizine','Virlix','Tablet',10,'Unilab',100),
                                                                                         ('Amlodipine','Norvasc','Tablet',5,'Pfizer',100);

-- --------------------------------------------------------
-- PRESCRIPTION (NO MEDICATIONID HERE ANYMORE)
-- --------------------------------------------------------

CREATE TABLE prescription (
                              prescriptionID INT PRIMARY KEY AUTO_INCREMENT,
                              patientID INT NOT NULL,
                              issueDate DATE NOT NULL,
                              expirationDate DATE NOT NULL,
                              refillInterval VARCHAR(50) NOT NULL,
                              status TEXT NOT NULL,
                              doctorID INT NOT NULL,

                              FOREIGN KEY (doctorID) REFERENCES doctor(doctorID),
                              FOREIGN KEY (patientID) REFERENCES patient(patientID)
);

-- --------------------------------------------------------
-- PRESCRIPTION ITEM (medication-level refill)
-- --------------------------------------------------------

CREATE TABLE prescriptionitem (
                                  prescriptionItemID INT PRIMARY KEY AUTO_INCREMENT,
                                  prescriptionID INT NOT NULL,
                                  medicationID INT UNSIGNED NOT NULL,
                                  doctorID INT NOT NULL,
                                  dosage TEXT NOT NULL,
                                  frequency TEXT NOT NULL,
                                  duration TEXT NOT NULL,
                                  prescribed_amount INT NOT NULL DEFAULT 0,
                                  refill_count INT NOT NULL DEFAULT 0,
                                  instructions TEXT NOT NULL,

                                  FOREIGN KEY (prescriptionID) REFERENCES prescription(prescriptionID) ON DELETE CASCADE,
                                  FOREIGN KEY (medicationID) REFERENCES medication(medicationID) ON UPDATE CASCADE,
                                  FOREIGN KEY (doctorID) REFERENCES doctor(doctorID)
);

-- --------------------------------------------------------
-- PHARMACY
-- --------------------------------------------------------

CREATE TABLE pharmacy (
                          pharmacyID INT PRIMARY KEY AUTO_INCREMENT,
                          name TEXT NOT NULL,
                          address TEXT NOT NULL,
                          contactNumber VARCHAR(20) NOT NULL,
                          email TEXT NOT NULL,
                          clinicAddress TEXT NOT NULL
);

INSERT INTO pharmacy (name, address, contactNumber, email, clinicAddress) VALUES
                                                                              ('HealthPlus Pharmacy','123 Rizal St','09171234567','healthplus@example.com','Unit 5 Medical Plaza'),
                                                                              ('CareWell Pharmacy','Session Road','09182345678','carewell@example.com','Saint Louis Hospital'),
                                                                              ('MediServe Pharmacy','Aurora Hill','09273456789','mediserve@example.com','Pines Doctors Clinic'),
                                                                              ('WellLife Pharmacy','Legarda Road','09384567890','welllife@example.com','University Clinic Center'),
                                                                              ('GreenCare Pharmacy','Marcos Highway','09495678901','greencare@example.com','BGH'),
                                                                              ('CityMeds Pharmacy','Bonifacio St','09771234098','citymeds@example.com','BMC');

-- --------------------------------------------------------
-- DISPENSE RECORD
-- --------------------------------------------------------

CREATE TABLE dispenserecord (
                                dispenseID INT PRIMARY KEY AUTO_INCREMENT,
                                prescriptionItemID INT NOT NULL,
                                pharmacyID INT NOT NULL,
                                quantityDispensed INT NOT NULL,
                                dateDispensed DATE NOT NULL,
                                pharmacistName TEXT NOT NULL,
                                status TEXT NOT NULL,
                                nextAvailableDates DATE NOT NULL,

                                FOREIGN KEY (prescriptionItemID) REFERENCES prescriptionitem(prescriptionItemID),
                                FOREIGN KEY (pharmacyID) REFERENCES pharmacy(pharmacyID)
);
