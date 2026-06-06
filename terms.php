<?php
$PageTitle = "Termini di Servizio";
include 'includes/header.php';
?>

<section class="legal">
    <div class="container">
        <h1>Termini di Servizio</h1>

        <p>Utilizzando NovaCity accetti i seguenti termini.</p>

        <h3>Uso del servizio</h3>
        <p>
            NovaCity è una piattaforma informativa e di segnalazione urbana.
            L’uso improprio del servizio è vietato.
        </p>

        <h3>Account</h3>
        <p>
            Sei responsabile della sicurezza del tuo account e delle credenziali.
        </p>

        <h3>Sospensione</h3>
        <p>
            NovaCity si riserva il diritto di sospendere o rimuovere account
            che violano i presenti termini.
        </p>

        <h3>Responsabilità</h3>
        <p>
            Il servizio è fornito "così com’è", senza garanzie.
        </p>
    </div>
</section>

<style>
/* Embedded CSS to make this page match your site's style */
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
    padding: 30px 40px;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    border-radius: 8px;
}

.legal h1 {
    font-size: 2em;
    margin-bottom: 20px;
    text-align: center;
}

.legal h3 {
    font-size: 1.2em;
    margin-top: 25px;
    margin-bottom: 10px;
    color: #222;
}

.legal p {
    font-size: 1em;
    line-height: 1.6;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .legal .container {
        padding: 20px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>
