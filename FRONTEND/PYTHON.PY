class Produits:
    def __init__(self):
        self.inventaire = {
            'Banane': {'prix': 2000, 'quantite': 20},
            'Plantain': {'prix': 5000, 'quantite': 50},
            'igname': {'prix': 6000, 'quantite': 10},
            'Mais': {'prix': 100, 'quantite': 5}
        }

    def ajouter_produits(self, nom, prix, quantite):
        self.inventaire[nom] = {'prix': prix, 'quantite': quantite}
        return "Produit ajouter avec succes!!!!"

    def retirer_produits(self, nom, quantite):
        if nom in self.inventaire:
            if self.inventaire[nom]['quantite'] >= quantite:
                self.inventaire[nom]['quantite'] -= quantite
            else:
                self.inventaire[nom]['quantite'] = 0
                return "Quantité insuffisante"
        else:
            return "Produit introuvable"

    def modifier_produits(self, nom, prix, quantite):
        if nom in self.inventaire:
            self.inventaire[nom]['prix'] = prix
            self.inventaire[nom]['quantite'] = quantite

    def supprimer_produits(self, nom):
        if nom in self.inventaire:
            del self.inventaire[nom]
        else:
            return "Ce produit n'existe pas"
    def afficher_inventaire(self):
     print("📦 INVENTAIRE ACTUEL")
     print("-" * 40)
     print(f"{'Produit':<15}{'Prix (FCFA)':<12}{'Quantité':<10}{'Valeur'}")
     print("-" * 40)
     total = 0
     for nom, infos in self.inventaire.items():
         prix = infos['prix']
         quantite = infos['quantite']
         valeur = prix * quantite
         total += valeur
         print(f"{nom:<15}{prix:<12}{quantite:<10}{valeur}")
     print("-" * 40)
     print(f"💰 Valeur totale du stock : {total} FCFA")
    #  application
inventaire = Produits()

# Ajout d'un produit
nom = input("Entrez le nom de l'article : ")
prix = int(input("Entrez le prix de l'article : "))
quantite = float(input("Entrez la quantité : "))
inventaire.ajouter_produits(nom, prix, quantite)

# Retrait d'un produit
nom = input("Entrez le nom de l'article à retirer : ")
quantite = float(input("Entrez la quantité à retirer : "))
resultat = inventaire.retirer_produits(nom, quantite)

if resultat:
    print(resultat)
else:
    print("Retrait effectué avec succès.")



