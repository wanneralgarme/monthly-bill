<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $monthly_bill = isset($_POST['bill']) ? floatval($_POST['bill']) : 0;
    $rate_per_kWh = isset($_POST['billing_rate']) ? floatval($_POST['billing_rate']) : 0;
    $sun_hours = isset($_POST['sun_hours']) ? floatval($_POST['sun_hours']) : 0;
    $capacity_per_panel = isset($_POST['capacity_per_panel']) ? floatval($_POST['capacity_per_panel']) : 0;
    $average_demand_load = isset($_POST['average_demand_load']) ? floatval($_POST['average_demand_load']) : 0;
    $battery_nominal_voltage_use = isset($_POST['battery_nominal_voltage_use']) ? floatval($_POST['battery_nominal_voltage_use']) : 0;
    $average_demand_load_at_night = isset($_POST['average_demand_load_at_night']) ? floatval($_POST['average_demand_load_at_night']) : 0;
    $investment_capital = isset($_POST['investment']) ? floatval($_POST['investment']) : 0;

    // Validation
    if ($monthly_bill <= 0) $errors[] = "Monthly Bill must be greater than 0.";
    if ($rate_per_kWh <= 0) $errors[] = "Billing Rate per kWh must be greater than 0.";
    if ($sun_hours <= 0) $errors[] = "Average Sun Hours per Day must be greater than 0.";
    if ($capacity_per_panel <= 0) $errors[] = "Panel Capacity must be greater than 0.";
    if ($average_demand_load <= 0) $errors[] = "Average Demand Load must be greater than 0.";
    if ($battery_nominal_voltage_use <= 0) $errors[] = "Battery Nominal Voltage must be greater than 0.";
    if ($average_demand_load_at_night < 0) $errors[] = "Nighttime Load Usage cannot be negative.";
    if ($investment_capital < 0) $errors[] = "Investment cannot be negative.";

    if (!empty($errors)) {
        echo "<div class='results' style='color: red;'><h2>Input Error</h2><ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul></div>";
    } else {
        // Constants
        $days = 30;

        // Calculations
        $total_ave_harvest_per_day = (($monthly_bill/$days)/$rate_per_kWh)*1000;
        $total_solar_panel_needed = ($total_ave_harvest_per_day / $capacity_per_panel)/$sun_hours;
        $power_inverter_needed = ((($capacity_per_panel*$total_solar_panel_needed) / 1000)*$average_demand_load)/0.8;
        $storage_requirments_in_AH = ($total_ave_harvest_per_day/$battery_nominal_voltage_use)*$average_demand_load_at_night;
        
        // Set up Calulations by bill
        $total_harvest_per_day_with_setup = $capacity_per_panel * $total_solar_panel_needed * $sun_hours;
        $ave_du_billing_rate_per_kwhr = ($total_harvest_per_day_with_setup/1000)*$rate_per_kWh;
        $total_savings_per_day = $ave_du_billing_rate_per_kwhr;
        $total_savings_per_month = $total_savings_per_day * $days;
        $total_savings_per_year = $total_savings_per_month * 12;
        $Estimated_ROI = $total_savings_per_year > 0 ? $investment_capital / $total_savings_per_year : 0;

        // Output results
        echo "<div class='results'>";
        echo "<h2>Solar System Estimation</h2>";
        echo "<p>Total ave. harvest per day: <b>" . round($total_ave_harvest_per_day) . " W</b></p>";
        echo "<p>Total solar panel needed: <b>" . round($total_solar_panel_needed,2) . " Panels</b></p>";
        echo "<p>Power inverter needed: <b>" . round($power_inverter_needed,2) . " kW</b></p>";
        echo "<p>Storage requirements: <b>" . round($storage_requirments_in_AH, 2) . " AH</b></p>";

        echo "<h2>ROI Computation</h2>";
        echo "<p>Total harvest per day with setup: <b>" . round($total_harvest_per_day_with_setup) . " Wh</b></p>";
        echo "<p>Ave. DU billing rate per kWh: <b>" . round($ave_du_billing_rate_per_kwhr,2) . " Php/day</b></p>";
        echo "<p>Total savings per day: <b>" . round($total_savings_per_day,2) . " Php</b></p>";
        echo "<p>Total savings per month: <b>" . round($total_savings_per_month,2) . " Php</b></p>";
        echo "<p>Total savings per year: <b>" . round($total_savings_per_year,2) . " Php</b></p>";
        echo "<p class='highlight'>Estimated ROI: <b>" . round($Estimated_ROI,2) . " years</b></p>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Solar Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f9f9;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            text-align: center;
            color: #1b4332;
        }
        form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            box-sizing: border-box;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 12px 0 5px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #bbb;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
            box-sizing: border-box;
        }
        button {
            display: block;
            width: 100%;
            padding: 12px;
            background: #2d6a4f;
            color: #fff;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #1b4332;
        }
        .results {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background: #e9f5e9;
            border: 1px solid #a3d9a5;
            border-radius: 12px;
        }
        .results p {
            margin: 8px 0;
            font-size: 15px;
        }
        .highlight {
            font-size: 18px;
            color: #2d6a4f;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <h1>Solar Set up Requirement Calculator</h1>
    <form method="post">
        <label>Monthly Bill (₱):</label>
        <input type="number" step="0.01" name="bill" value="<?php echo isset($_POST['bill']) ? htmlspecialchars($_POST['bill']) : ''; ?>" required>

        <label>Billing Rate per kWh (₱):</label>
        <input type="number" step="0.01" name="billing_rate" value="<?php echo isset($_POST['billing_rate']) ? htmlspecialchars($_POST['billing_rate']) : ''; ?>" required>

        <label>Average Sun Hours per Day:</label>
        <input type="number" step="0.01" name="sun_hours" value="<?php echo isset($_POST['sun_hours']) ? htmlspecialchars($_POST['sun_hours']) : ''; ?>" required>

        <label>Panel Capacity (W):</label>
        <input type="number" step="0.01" name="capacity_per_panel" value="<?php echo isset($_POST['capacity_per_panel']) ? htmlspecialchars($_POST['capacity_per_panel']) : ''; ?>" required>

        <label>Average Demand Load (%):</label>
        <input type="number" step="0.01" name="average_demand_load" value="<?php echo isset($_POST['average_demand_load']) ? htmlspecialchars($_POST['average_demand_load']) : ''; ?>" required>

        <label>Battery Nominal Voltage (V):</label>
        <input type="number" step="0.01" name="battery_nominal_voltage_use" value="<?php echo isset($_POST['battery_nominal_voltage_use']) ? htmlspecialchars($_POST['battery_nominal_voltage_use']) : ''; ?>" required>

        <label>Nighttime Load Usage (%):</label>
        <input type="number" step="0.01" name="average_demand_load_at_night" value="<?php echo isset($_POST['average_demand_load_at_night']) ? htmlspecialchars($_POST['average_demand_load_at_night']) : ''; ?>" required>

        <label>Investment (₱):</label>
        <input type="number" step="0.01" name="investment" value="<?php echo isset($_POST['investment']) ? htmlspecialchars($_POST['investment']) : ''; ?>">

        <button type="submit">Calculate</button>
    </form>
</body>
</html>
