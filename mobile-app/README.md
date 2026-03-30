# Creator Market Mobile App

Application mobile React Native pour la plateforme Creator Market.

## 🚀 Fonctionnalités

### 👤 Authentification
- Connexion/Inscription sécurisée
- Session persistante
- Gestion des tokens

### 🏪 Gestion des boutiques
- Affichage des boutiques populaires
- Détails des boutiques
- Dashboard vendeur
- Statistiques en temps réel

### 📦 Gestion des produits
- Ajout de produits
- Modification des produits
- Suppression de produits
- Upload d'images

### 📊 Dashboard
- Statistiques des ventes
- Suivi des commandes
- Actions rapides
- Analytics

### 🎨 Design
- Thème clair/sombre
- Interface moderne
- Animations fluides
- Responsive design

## 📋 Prérequis

- Node.js 16+
- React Native CLI
- Android Studio (pour Android)
- Xcode (pour iOS)
- React Native Debugger

## 🛠️ Installation

```bash
# Cloner le projet
git clone <repository-url>
cd mobile-app

# Installer les dépendances
npm install

# Pour iOS
cd ios && pod install && cd ..

# Démarrer Metro
npm start

# Lancer l'application
npm run android  # ou npm run ios
```

## 📱 Structure du projet

```
mobile-app/
├── src/
│   ├── components/     # Composants réutilisables
│   ├── screens/       # Écrans de l'application
│   ├── services/      # Services API
│   ├── contexts/      # Contextes React
│   ├── navigation/    # Navigation
│   ├── utils/         # Utilitaires
│   ├── assets/        # Images et ressources
│   └── store/         # Redux Store
├── android/           # Configuration Android
├── ios/               # Configuration iOS
└── package.json
```

## 🔧 Configuration

### Variables d'environnement

Créer un fichier `.env` à la racine :

```env
API_BASE_URL=http://localhost:3307/projet_de_stage
```

### Configuration API

Modifier `src/services/api.js` pour pointer vers votre backend :

```javascript
const API_BASE_URL = 'http://votre-domaine.com/api';
```

## 📱 Écrans principaux

- **SplashScreen** : Écran de chargement
- **LoginScreen** : Connexion utilisateur
- **RegisterScreen** : Inscription utilisateur
- **HomeScreen** : Accueil avec les boutiques
- **DashboardScreen** : Tableau de bord vendeur
- **ProductScreen** : Détails d'un produit
- **BoutiqueScreen** : Boutique d'un vendeur
- **ProfileScreen** : Profil utilisateur

## 🎨 Thèmes

L'application supporte deux thèmes :
- **Light** : Thème clair par défaut
- **Dark** : Thème sombre

Le thème est sauvegardé localement et persiste entre les sessions.

## 🔐 Sécurité

- Token JWT pour l'authentification
- Validation des entrées
- Gestion sécurisée des mots de passe
- Protection CSRF

## 📊 Services API

### Authentification
- `authService.login()` - Connexion
- `authService.register()` - Inscription
- `authService.logout()` - Déconnexion

### Boutiques
- `boutiqueService.getPopularBoutiques()` - Boutiques populaires
- `boutiqueService.getBoutiqueDetails()` - Détails boutique
- `boutiqueService.addProduct()` - Ajouter produit
- `boutiqueService.updateProduct()` - Modifier produit
- `boutiqueService.deleteProduct()` - Supprimer produit

## 🚀 Déploiement

### Android
```bash
# Générer l'APK
cd android
./gradlew assembleRelease

# L'APK sera dans android/app/build/outputs/apk/release/
```

### iOS
```bash
# Générer l'IPA
cd ios
xcodebuild -workspace CreatorMarket.xcworkspace -scheme CreatorMarket -configuration Release -destination generic/platform=iOS -archivePath CreatorMarket.xcarchive archive
```

## 🔧 Développement

### Ajouter un nouvel écran

1. Créer le fichier dans `src/screens/`
2. Ajouter la navigation dans `src/navigation/AppNavigator.js`
3. Importer les dépendances nécessaires

### Styles

L'application utilise les thèmes définis dans `ThemeContext`. Utilisez `useTheme()` pour accéder aux couleurs :

```javascript
const { theme } = useTheme();
```

### Navigation

La navigation utilise React Navigation v6. Les écrans sont organisés en :
- Stack Navigator pour la navigation principale
- Tab Navigator pour la navigation connectée

## 🐛 Débuggage

Utiliser React Native Debugger pour :
- Inspecter le réseau
- Debugger le JavaScript
- Voir les logs
- Inspecter le storage

## 📝 Notes

- L'application se connecte au backend PHP existant
- Les images sont uploadées via FormData
- Le thème est persistant localement
- Les tokens sont gérés automatiquement

## 🤝 Contribution

1. Forker le projet
2. Créer une branche feature
3. Commiter les changements
4. Pousser vers la branche
5. Créer une Pull Request

## 📄 Licence

MIT License
