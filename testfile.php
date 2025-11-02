<form action="actions/dispense.php" method="POST">
    Prescription Item ID: <input type="number" name="prescriptionItemID" required><br><br>
    Pharmacy ID: <input type="number" name="pharmacyID" required><br><br>
    Dispense ID: <input type="number" name="dispenseID" required><br><br>
    Quantity Dispensed: <input type="number" name="quantityDispensed" required><br><br>
    Date Dispensed: <input type="date" name="dateDispensed" required><br><br>
    Pharmacist Name: <input type="text" name="pharmacistName" required><br><br>
    Status: <input type="text" name="status" required><br><br>
    Next Available Date: <input type="date" name="nextAvailableDates"><br><br>
    
    <button type="submit">Submit</button>
</form>

