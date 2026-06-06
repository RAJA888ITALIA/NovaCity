<?php
$PageTitle = "Contatti";
include 'includes/header.php';
?>

<section class="contact">
    <div class="container">
        <h1>Contatti</h1>

        <p>
            Per assistenza o informazioni, puoi contattarci via email:
        </p>

        <p>
            <b>novacity.verify@gmail.com</b> oppure chiamaci al numero <b>1112223333</b>
        </p>

        <h3>Orari di assistenza</h3>
        <ul class="schedule">
            <li>Lunedì: 09:00 - 18:00</li>
            <li>Martedì: 09:00 - 18:00</li>
            <li>Mercoledì: 09:00 - 18:00</li>
            <li>Giovedì: 09:00 - 18:00</li>
            <li>Venerdì: 09:00 - 18:00</li>
            <li>Sabato: 09:00 - 13:00</li>
        </ul>

        <p>
            Ti risponderemo il prima possibile.
        </p>
    </div>
</section>

<style>
/* Big-box styling for contact page */
.contact {
    padding: 40px 20px;
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    color: #333;
}

.contact .container {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 40px;
    box-shadow: 0 0 20px rgba(0,0,0,0.07);
    border-radius: 10px;
    text-align: center;
}

.contact h1 {
    font-size: 2em;
    margin-bottom: 25px;
}

.contact h3 {
    margin-top: 25px;
    margin-bottom: 15px;
    font-size: 1.3em;
}

.contact p {
    font-size: 1em;
    line-height: 1.6;
    margin-bottom: 15px;
}

.contact .schedule {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.contact .schedule li {
    font-size: 1em;
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .contact .container {
        padding: 25px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
