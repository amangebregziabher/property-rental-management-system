<?php
require_once __DIR__ . '/../app/helpers/Validator.php';

function run_test($name, $expected, $actual) {
    echo $name . ": ";
    if ($expected === $actual) {
        echo "[PASS]\n";
    } else {
        echo "[FAIL] Expected " . json_encode($expected) . ", got " . json_encode($actual) . "\n";
    }
}

echo "Running Validator Tests...\n\n";

// Test 1: Required fields
$data = ['name' => '', 'age' => '25'];
$v = new Validator($data);
$v->validate('name', 'required');
$v->validate('age', 'required');
run_test("Required Field (Empty)", false, $v->isValid());
$errors = $v->getErrors();
run_test("Required Error Message", "name is required.", $errors['name'][0] ?? '');

// Test 2: Numeric
$data = ['price' => 'abc', 'qty' => '10'];
$v = new Validator($data);
$v->validate('price', 'numeric');
$v->validate('qty', 'numeric');
$errors = $v->getErrors();
run_test("Numeric Check (Invalid)", "price must be a number.", $errors['price'][0] ?? '');
run_test("Numeric Check (Valid)", true, empty($errors['qty']));

// Test 3: Min value (Numeric)
$data = ['age' => '17', 'income' => '1000'];
$v = new Validator($data);
$v->validate('age', 'min:18');
$v->validate('income', 'min:500');
$errors = $v->getErrors();
run_test("Min Numeric (Fail)", "age must be at least 18.", $errors['age'][0] ?? '');
run_test("Min Numeric (Pass)", true, empty($errors['income']));

// Test 4: Email
$data = ['email1' => 'bad-email', 'email2' => 'good@example.com'];
$v = new Validator($data);
$v->validate('email1', 'email');
$v->validate('email2', 'email');
$errors = $v->getErrors();
run_test("Email Invalid", "email1 must be a valid email address.", $errors['email1'][0] ?? '');
run_test("Email Valid", true, empty($errors['email2']));

// Test 5: Phone
$data = ['phone1' => '123', 'phone2' => '+1 (555) 123-4567'];
$v = new Validator($data);
$v->validate('phone1', 'phone');
$v->validate('phone2', 'phone');
$errors = $v->getErrors();
run_test("Phone Invalid", "phone1 must be a valid phone number.", $errors['phone1'][0] ?? '');
run_test("Phone Valid", true, empty($errors['phone2']));

echo "\nTests Completed.\n";
?>
