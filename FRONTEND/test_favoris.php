<?php
// Script de test pour vérifier les favoris
session_start();
header('Content-Type: text/html; charset=utf-8');

echo "<h1>🧪 Test du système de favoris</h1>";

// Simuler une requête POST avec boutique_id
if (isset($_GET['test'])) {
    $_POST['boutique_id'] = $_GET['test'];
    include 'favoris.php';
    exit;
}

echo "<h2>📋 État actuel</h2>";
echo "<ul>";
echo "<li>User ID: " . ($_SESSION['user_id'] ?? 'NON CONNECTÉ') . "</li>";
echo "</ul>";

// Tester quelques boutiques
echo "<h2>🛍️ Boutiques disponibles pour test</h2>";
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
        'root', '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->query("SELECT id, nom FROM boutiques LIMIT 5");
    $boutiques = $stmt->fetchAll();

    echo "<ul>";
    foreach ($boutiques as $b) {
        echo "<li>";
        echo "<strong>{$b['nom']}</strong> (ID: {$b['id']}) ";
        echo "<a href='?test={$b['id']}' style='color:blue'>[Tester favori]</a>";
        echo "</li>";
    }
    echo "</ul>";

    if (isset($_SESSION['user_id'])) {
        echo "<h2>❤️ Vos favoris actuels</h2>";
        $stmt = $pdo->prepare("SELECT b.nom, f.date_ajout FROM favoris f JOIN boutiques b ON f.boutique_id = b.id WHERE f.utilisateur_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $favoris = $stmt->fetchAll();

        if (empty($favoris)) {
            echo "<p>Aucun favori pour le moment.</p>";
        } else {
            echo "<ul>";
            foreach ($favoris as $f) {
                echo "<li>{$f['nom']} (ajouté le {$f['date_ajout']})</li>";
            }
            echo "</ul>";
        }
    }

} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur DB: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='marcher.php'>Retour au marché</a> | <a href='dashboard.php'>Dashboard</a></p>";
?>