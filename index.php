<?php
session_start();

// Se l’utente è già loggato, viene mandato al dashboard
if (isset($_SESSION['user_id'])) 
{
    header('Location: dashboard.php');
    exit();
}

$PageTitle = "NovaCity";
include 'includes/header.php';
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h1>NovaCity</h1>
        <p>La piattaforma digitale che mette in connessione cittadini e amministrazione per una città più sicura, moderna ed efficiente.</p>
        <p>Un progetto di <strong>Faisal Raja Zain</strong></p>
        <div class="hero-buttons">
            <a href="register.php" class="btn primary">Inizia ora</a>
            <a href="login.php" class="btn secondary">Accedi</a>
        </div>
    </div>
</section>

<!-- CHI SIAMO -->
<section class="section">
    <div class="container">
        <h2>Chi Siamo</h2>
        <p>NovaCity nasce per migliorare la qualità della vita urbana, offrendo uno strumento semplice e veloce per segnalare problemi e collaborare con la propria città.</p>
        <ul>
          <p><strong>Area utente</strong>: permette ai cittadini di segnalare problemi, allegare immagini e monitorare lo stato delle segnalazioni.</p>

          <p><strong>Area amministratore</strong>: consente di gestire le segnalazioni, assegnare interventi e aggiornare lo stato dei lavori in tempo reale.</p>
        </ul>
    </div>
</section>

<!-- MISSIONE -->
<section class="section light">
    <div class="container">
        <h2>🎯 La Nostra Missione</h2>
        <div class="cards">
            <div class="card">🚧<br>Buche stradali</div>
            <div class="card">💡<br>Lampioni guasti</div>
            <div class="card">🚔<br>Atti di vandalismo</div>
            <div class="card">🧹<br>Pulizia/Rifiuti</div>
            <div class="card">⚠️<br>Disagi urbani</div>
            <div class="card">🌐<br>Tanto Altro</div>
        </div>
    </div>
</section>

<!-- DOVE OPERIAMO -->
<section class="section">
    <div class="container split">
        <div>
            <h2>📍 Dove Operiamo</h2>
            <p>
                Attualmente NovaCity non è attiva, ma presto sarà disponibile.
                <br>NovaCity ha in programma di espandersi in diverse città italiane.
                Ecco l'elenco delle città in cui prevediamo di estendere la nostra presenza.
                <br>
                <a href="#cityModal" class="city-link">Elenco Città</a>
            </p>
        </div>
        <div class="highlight-box">
            <h3>🚀 La tua città, più vicina</h3>
            <p>
                Ogni segnalazione contribuisce a rendere l’ambiente urbano
                più sicuro ed efficiente per tutti.
            </p>
        </div>
    </div>
</section>

<!-- CALL TO ACTION -->
<section class="cta">
    <h2>Partecipa al cambiamento</h2>
    <p>Registrati ora e contribuisci a migliorare la tua città.</p>
    <a href="register.php" class="btn primary large">Registrati Gratis</a>
</section>

<!-- Modal content -->
<div id="cityModal" class="modal">
    <div class="modal-content">
        <!-- Close Button (Cross) -->
        <a href="#" class="close">&times;</a>
        <h2>Elenco delle Città</h2>
        <ul>
            <li>Novara</li>
            <li>Milano</li>
            <li>Torino</li>
        </ul>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
