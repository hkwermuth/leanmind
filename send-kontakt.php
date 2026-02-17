<?php
/**
 * LeanMind kontaktformular – sender mail til hannah@leanmind.dk
 * Virker fra både /kontakt.html (da) og /en/contact.html (en).
 */
mb_internal_encoding("UTF-8");
header("Content-Type: text/html; charset=UTF-8");

$isEnglish = (trim($_POST["lang"] ?? "") === "en");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    if ($isEnglish) {
        $msg = "Method not allowed.";
    } else {
        $msg = "Metoden er ikke tilladt.";
    }
    http_response_code(405);
    echo "<!DOCTYPE html><html lang=\"" . ($isEnglish ? "en" : "da") . "\"><head><meta charset=\"UTF-8\"><title>Error</title></head><body><p>" . htmlspecialchars($msg, ENT_QUOTES, "UTF-8") . "</p></body></html>";
    exit;
}

$name    = trim($_POST["name"] ?? "");
$email   = trim($_POST["email"] ?? "");
$phone   = trim($_POST["phone"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

// Validering
if ($name === "" || $email === "" || $subject === "" || $message === "") {
    $err = $isEnglish
        ? "Please fill in all required fields and try again."
        : "Der mangler nogle obligatoriske felter. Gå venligst tilbage og prøv igen.";
    echo errorPage($err, $isEnglish);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = $isEnglish
        ? "The email address does not look valid."
        : "Email-adressen ser ikke korrekt ud.";
    echo errorPage($err, $isEnglish);
    exit;
}

// Modtager og emne
$to = "hannah@leanmind.dk";
$mailSubject = "Kontaktformular LeanMind: " . $subject;

// Mailtekst (UTF-8)
$body  = "Navn: " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Telefon: " . $phone . "\n\n";
$body .= "Besked:\n" . $message . "\n";

$headers  = "From: " . $name . " <" . $email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$success = @mail($to, $mailSubject, $body, $headers);

if ($success) {
    echo thankYouPage($name, true, $isEnglish);
} else {
    echo thankYouPage($name, false, $isEnglish);
}

function thankYouPage($name, $success, $isEnglish) {
    $nameEsc = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
    $lang = $isEnglish ? "en" : "da";
    $title = $isEnglish ? "Thank you - LeanMind" : "Tak for din besked - LeanMind";
    $homeUrl = $isEnglish ? "en/index.html" : "index.html";
    $contactUrl = $isEnglish ? "en/contact.html" : "kontakt.html";
    $homeLabel = $isEnglish ? "Back to home" : "Tilbage til forsiden";
    $contactLabel = $isEnglish ? "Send another message" : "Send en ny besked";

    if ($success) {
        $heading = $isEnglish ? "Thank you for your message" : "Tak for din besked";
        $text = $isEnglish
            ? "Thanks, $nameEsc. I will get back to you as soon as possible."
            : "Tak, $nameEsc. Jeg vender tilbage til dig snarest muligt.";
    } else {
        $heading = $isEnglish ? "Something went wrong" : "Der opstod en fejl";
        $text = $isEnglish
            ? "Sorry, your message could not be sent. Please try again later or email <a href=\"mailto:hannah@leanmind.dk\">hannah@leanmind.dk</a> directly."
            : "Beklager, din besked kunne ikke sendes. Prøv igen senere eller skriv direkte til <a href=\"mailto:hannah@leanmind.dk\">hannah@leanmind.dk</a>.";
    }

    $stylesHref = $isEnglish ? "../styles.css" : "styles.css";
    return <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title</title>
    <link rel="stylesheet" href="$stylesHref">
</head>
<body>
    <main class="content-section" style="max-width:600px; margin:4rem auto; padding:2rem;">
        <div class="content-frame frame--pink" style="padding:2rem;">
            <h1>$heading</h1>
            <p>$text</p>
            <p><a href="$homeUrl" class="btn-primary">$homeLabel</a> &nbsp; <a href="$contactUrl">$contactLabel</a></p>
        </div>
    </main>
</body>
</html>
HTML;
}

function errorPage($message, $isEnglish) {
    $lang = $isEnglish ? "en" : "da";
    $title = $isEnglish ? "Error - LeanMind" : "Fejl - LeanMind";
    $back = $isEnglish ? "Back to contact form" : "Tilbage til kontaktformular";
    $url = $isEnglish ? "contact.html" : "kontakt.html";
    $msgEsc = htmlspecialchars($message, ENT_QUOTES, "UTF-8");
    $stylesHref = $isEnglish ? "../styles.css" : "styles.css";
    return <<<HTML
<!DOCTYPE html>
<html lang="$lang">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$title</title>
    <link rel="stylesheet" href="$stylesHref">
</head>
<body>
    <main class="content-section" style="max-width:600px; margin:4rem auto; padding:2rem;">
        <div class="content-frame frame--pink" style="padding:2rem;">
            <h1>$title</h1>
            <p>$msgEsc</p>
            <p><a href="$url" class="btn-primary">$back</a></p>
        </div>
    </main>
</body>
</html>
HTML;
}
