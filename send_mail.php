<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);
    
    $to = "adizul299@gmail.com";  // Email penerima (yours)
    $subject = "Pesan dari Website";
    $headers = "From: " . $email . "\r\n" . "Reply-To: " . $email;

    $body = "Nama: $name\nEmail: $email\n\nPesan:\n$message";

    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('Pesan berhasil dikirim!'); window.location.href='index.html';</script>";
    } else {
        echo "<script>alert('Gagal mengirim pesan. Coba lagi!'); window.location.href='index.html';</script>";
    }
}
?>
