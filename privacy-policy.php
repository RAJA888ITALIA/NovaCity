<?php
$PageTitle = "Privacy Policy";
include 'includes/header.php';
?>

<section class="legal">
    <div class="container">
        <h1>Privacy Policy</h1>

        <p>Utilizzando NovaCity accetti la nostra politica sulla privacy.</p>

        <h2>Dati raccolti</h2>
        <ul>
            <li>Nome e cognome</li>
            <li>Indirizzo email</li>
            <li>Password (criptata)</li>
        </ul>

        <h2>Finalità</h2>
        <ul>
            <li>Creazione e gestione dell’account</li>
            <li>Accesso ai servizi della piattaforma</li>
            <li>Comunicazioni di sicurezza (verifica email, reset password)</li>
        </ul>

        <h2>Conservazione</h2>
        <p>I dati non vengono venduti né condivisi con terze parti.</p>

        <h2>Contatti</h2>
        <p>Per qualsiasi richiesta: <b>novacity.verify@gmail.com</b></p>
    </div>
</section>

<style>
/* Embedded CSS for one big box style */
.legal {
    padding: 40px 20px;
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    color: #333;
}

.legal .container {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    padding: 40px;
    box-shadow: 0 0 20px rgba(0,0,0,0.07);
    border-radius: 10px;
}

.legal h1 {
    font-size: 2em;
    margin-bottom: 25px;
    text-align: center;
}

.legal h2 {
    font-size: 1.3em;
    margin-top: 25px;
    margin-bottom: 15px;
    color: #222;
}

.legal p, .legal ul {
    font-size: 1em;
    line-height: 1.6;
    margin-bottom: 15px;
}

.legal ul {
    padding-left: 20px;
}

@media (max-width: 768px) {
    .legal .container {
        padding: 25px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
