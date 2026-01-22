<?php
echo "<h2>Test PHPMailer Installation</h2>";

// Test 1: Autoload existe ?
$autoloadPath = __DIR__ . '/vendor/autoload.php';
echo "<p><strong>1. Autoload file exists:</strong> ";
echo file_exists($autoloadPath) ? "✅ YES" : "❌ NO";
echo "</p>";

// Test 2: Charger l'autoload
if (file_exists($autoloadPath)) {
    require $autoloadPath;
    echo "<p><strong>2. Autoload loaded:</strong> ✅ YES</p>";
} else {
    echo "<p><strong>2. Autoload loaded:</strong> ❌ NO</p>";
    die();
}

// Test 3: Classe PHPMailer existe ?
echo "<p><strong>3. PHPMailer class exists:</strong> ";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "✅ YES";
} else {
    echo "❌ NO";
}
echo "</p>";

// Test 4: Essayer de créer une instance
echo "<p><strong>4. Create PHPMailer instance:</strong> ";
try {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo "✅ SUCCESS - Instance created";
    echo "<br>Version: " . $mail::VERSION;
} catch (Exception $e) {
    echo "❌ FAILED - " . $e->getMessage();
}
echo "</p>";

// Test 5: Vérifier le fichier source
$sourcePath = __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
echo "<p><strong>5. PHPMailer source file exists:</strong> ";
echo file_exists($sourcePath) ? "✅ YES" : "❌ NO";
echo "</p>";

echo "<hr>";
echo "<p>Si tout est ✅, PHPMailer est correctement installé !</p>";
