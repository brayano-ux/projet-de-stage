<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = getDB();

    // Boutiques actives
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM boutiques WHERE statut = 'active'");
    $boutiques = $stmt->fetch()['count'];

    // Utilisateurs inscrits
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs");
    $utilisateurs = $stmt->fetch()['count'];

    // Commandes ce mois
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM commandes WHERE MONTH(date_commande) = MONTH(CURRENT_DATE()) AND YEAR(date_commande) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $commandes = $stmt->fetch()['count'];

    // Chiffre d'affaires ce mois
    $stmt = $pdo->prepare("SELECT SUM(montant) as sum FROM commandes WHERE MONTH(date_commande) = MONTH(CURRENT_DATE()) AND YEAR(date_commande) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $revenus = $stmt->fetch()['sum'] ?? 0;

    echo json_encode([
        'boutiques' => $boutiques,
        'utilisateurs' => $utilisateurs,
        'commandes' => $commandes,
        'revenus' => $revenus
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>