<?php

require_once __DIR__ . '/envParser.php';
require_once __DIR__ . '/services/Validator.php';
require_once __DIR__ . '/services/ApiService.php';

$errors = [];
$successMessage = "";
$firstName = $lastName = $phone = $email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $validator = new Validator();
    $errors = $validator->validateLeadForm($_POST);

    $firstName = $_POST['firstName'] ?? '';
    $lastName  = $_POST['lastName'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $email     = $_POST['email'] ?? '';

    if (empty($errors)) {
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $landingUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $leadData = [
            "firstName"   => $firstName,
            "lastName"    => $lastName,
            "phone"       => $phone,
            "email"       => $email,
            "countryCode" => "GB",
            "box_id"      => 28,
            "offer_id"    => 5,
            "landingUrl"  => $landingUrl,
            "ip"          => $userIp,
            "password"    => "qwerty12",
            "language"    => "en"
        ];

        $apiService = new ApiService($token, $apiUrl);
        $result = $apiService->sendLead($leadData);

        if ($result['success']) {
            $successMessage = "Lead successfully sent! ID: " . $result['id'];
            $firstName = $lastName = $phone = $email = "";
        } else {
            $errors['api'] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Management - Add Lead</title>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav>
                <ul class="header__wrapper">
                    <li><a href="/" class="header__link">Add lead</a></li>
                    <li><a href="/leads.php" class="header__link">View leads</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="apply">
            <div class="container">

                <?php if (!empty($successMessage)): ?>
                    <div class="apply__success"><?= htmlspecialchars($successMessage) ?></div>
                <?php endif; ?>

                <form class="apply__form" method="post">

                    <label for="firstName">
                        <input type="text" placeholder="First name" required id="firstName" name="firstName" maxlength="255"
                               value="<?= isset($firstName) ? htmlspecialchars($firstName) : '' ?>">
                        <?php if (isset($errors['firstName'])): ?>
                            <span class="apply__error"><?= $errors['firstName'] ?></span>
                        <?php endif; ?>
                    </label>

                    <label for="lastName">
                        <input type="text" placeholder="Last name" required id="lastName" name="lastName" maxlength="255"
                               value="<?= isset($lastName) ? htmlspecialchars($lastName) : '' ?>">
                        <?php if (isset($errors['lastName'])): ?>
                            <span class="apply__error"><?= $errors['lastName'] ?></span>
                        <?php endif; ?>
                    </label>

                    <label for="phone">
                        <input type="text" maxlength="12" placeholder="Phone number" required id="phone" name="phone"
                               value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <span class="apply__error"><?= $errors['phone'] ?></span>
                        <?php endif; ?>
                    </label>

                    <label for="email">
                        <input type="email" required placeholder="example@gmail.com" maxlength="255" id="email" name="email"
                               value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                        <?php if (isset($errors['email'])): ?>
                            <span class="apply__error"><?= $errors['email'] ?></span>
                        <?php endif; ?>
                    </label>

                    <?php if (isset($errors['api'])): ?>
                        <div style="color: #ef4444; font-size: 0.9rem; margin-bottom: 15px; text-align: center;">
                            <?= htmlspecialchars($errors['api']) ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit">Apply</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>