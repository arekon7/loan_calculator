<?php

// 1st task data, uncomment next 4 lines if necessary
//$ann_inter_prc = 12;     // annual interest expressed in percents (%)
//$P = 5000;             // ammount borrowed (loan's principal)
//$N = 24;                //  number of monthly payments (loan's term)
//$date = new DateTime('2017-04-15');   // first payment date

$interest_rate_change = 9;      // new interest after specified date
$dateToChangeInterest = new DateTime('2017-09-02');     // date when change interest

// Read data from input form
$ann_inter_prc = $_POST["inter_rate"];     // annual interest expressed in percents (%)
$P = $_POST["ammount"];                 // ammount borrowed (loan's principal)
$N = $_POST["no_months"];                //  number of monthly payments (loan's term)
$date = new DateTime($_POST["date"]);   // first payment date
echo $ann_inter_prc . ' ' . $P . ' '. $N . ' '. $date->format('Y-m-d');

function calculateInterestRateDecimal(){
    global $ann_inter_prc;
    $r = ($ann_inter_prc/12)/100;     //monthly interest rate, expressed as a decimal, not percentage
    return $r;
}

$r = calculateInterestRateDecimal();

// montly payment 
function calculateMonthlyPayment(){
    global $r, $P, $N;
    $c = ($r * $P)/(1 - pow((1 + $r), -$N));
    //$c = round($c, 2);
    $c = floor($c * 100) / 100;
    return $c;
}

$c = calculateMonthlyPayment();

// remaining ammount to pay after $n monthly payment
function calculateRemainingAmmount($n){
    global $P, $N, $c, $r;
    $REM = (pow((1 + $r), $n) * $P) - ((pow((1+$r), $n)-1) / $r) * $c;
    return round($REM, 2);
}


// principal payment at $n'th payment
function calculatePrincipalPayment($n){
    $n--;
    global $P, $N, $c, $r, $REM;
    $PRINC = calculateRemainingAmmount($n) - calculateRemainingAmmount($n+1);
    return round($PRINC, 2);
}


// interest payment at $n'th payment
function calculateInterestPayment($n){
    global $P, $N, $c, $r, $REM, $PRINC;
    $INTER = $c - calculatePrincipalPayment($n);
    return round($INTER, 2);
}

$result = "Payment#, Payment date, Remaining amount, Principal payment, Interest payment, Total payment, Interest rate \n";

// create result content
function createResultContent(){
    global $n, $P, $N, $c, $r, $REM, $PRINC, $INTER, $ann_inter_prc, $date, $result;
    $result .= $n.', ' . $date->format('Y-m-d').', '.$REM.', '.$PRINC.', '.$INTER.', '.$c.', '.$ann_inter_prc.",\n ";
}

// Generate chart with constant interest rate
for ($i = 1; $i <= $N; $i++){
    $n = $i;
    $REM = calculateRemainingAmmount($i-1);
    $PRINC = calculatePrincipalPayment($i);
    $INTER = calculateInterestPayment($i);
    createResultContent();
    $date->modify('+1 month');
}
echo $result;

$chart_file_1 = fopen("chart1.csv", "w") or die("Unable to open file!");
fwrite($chart_file_1, $result);
fclose($chart_file_1);


//------- 2nd part --- Interest changed to 9% after 2017-09-02 -------------------------
echo '<br>--- 2nd part --- Interest changed to 9% after 2017-09-02 --------- <br>';

$date->modify('2017-04-15');        // reset date

// Generate 2nd chart with changing interest rate

$result = "Payment#, Payment date, Remaining amount, Principal payment, Interest payment, Total payment, Interest rate \n";
$interestChanged = 0;   // interest changing indicator
$iteration_no = $N;     // iteration number, equals to total number of payments (loan term)
$new_interest_payment_no = 1;   // payment counter after interest change

for ($i = 1; $i <= $iteration_no; $i++){
    if(($dateToChangeInterest < $date) AND ($interestChanged == 0)){    //if reached date to change interest 
        $ann_inter_prc = $interest_rate_change;     // new interest rate
        $P = calculateRemainingAmmount($i-1);
        $N = $N - $i + 1;
        $r = calculateInterestRateDecimal();
        $c = calculateMonthlyPayment();
        $interestChanged = 1;
    } 
    if($interestChanged == 1){      // if interest already changed
        $REM = calculateRemainingAmmount($new_interest_payment_no - 1);
        $PRINC = calculatePrincipalPayment($new_interest_payment_no);
        $INTER = calculateInterestPayment($new_interest_payment_no);
        $new_interest_payment_no++;
    }else{                      // if interest not changed
        $REM = calculateRemainingAmmount($i-1);
        $PRINC = calculatePrincipalPayment($i);
        $INTER = calculateInterestPayment($i);
    }
    
    createResultContent();
    $date->modify('+1 month');
}

echo $result. '<br>';

//write to .csv file
$chart_file_2 = fopen("chart2.csv", "w") or die("Unable to open file!");
fwrite($chart_file_2, $result);
fclose($chart_file_2);


echo "<b>Result files generated in root folder</b>";








