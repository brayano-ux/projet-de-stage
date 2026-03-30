import React from 'react';
import { createStackNavigator } from '@react-navigation/stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import Icon from 'react-native-vector-icons/MaterialIcons';

import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';

// Screens
import SplashScreen from '../screens/SplashScreen';
import LoginScreen from '../screens/auth/LoginScreen';
import RegisterScreen from '../screens/auth/RegisterScreen';
import HomeScreen from '../screens/HomeScreen';
import BoutiqueScreen from '../screens/BoutiqueScreen';
import ProductScreen from '../screens/ProductScreen';
import DashboardScreen from '../screens/DashboardScreen';
import ProfileScreen from '../screens/ProfileScreen';
import FavoritesScreen from '../screens/FavoritesScreen';
import OrdersScreen from '../screens/OrdersScreen';
import AddProductScreen from '../screens/AddProductScreen';
import EditProductScreen from '../screens/EditProductScreen';

const Stack = createStackNavigator();
const Tab = createBottomTabNavigator();

// Navigation principale (connecté)
const MainTabNavigator = () => {
  const { theme } = useTheme();

  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        tabBarIcon: ({ focused, color, size }) => {
          let iconName;

          switch (route.name) {
            case 'Home':
              iconName = 'home';
              break;
            case 'Boutiques':
              iconName = 'store';
              break;
            case 'Dashboard':
              iconName = 'dashboard';
              break;
            case 'Favorites':
              iconName = 'favorite';
              break;
            case 'Profile':
              iconName = 'person';
              break;
            default:
              iconName = 'help';
          }

          return <Icon name={iconName} size={size} color={color} />;
        },
        tabBarActiveTintColor: theme.primary,
        tabBarInactiveTintColor: theme.textSecondary,
        tabBarStyle: {
          backgroundColor: theme.background,
          borderTopColor: theme.border,
        },
        headerStyle: {
          backgroundColor: theme.background,
          borderBottomColor: theme.border,
        },
        headerTintColor: theme.text,
      })}
    >
      <Tab.Screen 
        name="Home" 
        component={HomeScreen}
        options={{ title: 'Accueil' }}
      />
      <Tab.Screen 
        name="Boutiques" 
        component={BoutiqueScreen}
        options={{ title: 'Boutiques' }}
      />
      <Tab.Screen 
        name="Dashboard" 
        component={DashboardScreen}
        options={{ title: 'Tableau de bord' }}
      />
      <Tab.Screen 
        name="Favorites" 
        component={FavoritesScreen}
        options={{ title: 'Favoris' }}
      />
      <Tab.Screen 
        name="Profile" 
        component={ProfileScreen}
        options={{ title: 'Profil' }}
      />
    </Tab.Navigator>
  );
};

// Navigation authentification
const AuthNavigator = () => {
  const { theme } = useTheme();

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: theme.background,
          borderBottomColor: theme.border,
        },
        headerTintColor: theme.text,
      }}
    >
      <Stack.Screen 
        name="Login" 
        component={LoginScreen}
        options={{ headerShown: false }}
      />
      <Stack.Screen 
        name="Register" 
        component={RegisterScreen}
        options={{ title: 'Inscription' }}
      />
    </Stack.Navigator>
  );
};

// Navigation principale
const AppNavigator = () => {
  const { isAuthenticated, isLoading } = useAuth();
  const { theme } = useTheme();

  if (isLoading) {
    return <SplashScreen />;
  }

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: {
          backgroundColor: theme.background,
          borderBottomColor: theme.border,
        },
        headerTintColor: theme.text,
      }}
    >
      {isAuthenticated ? (
        <>
          <Stack.Screen 
            name="Main" 
            component={MainTabNavigator}
            options={{ headerShown: false }}
          />
          <Stack.Screen 
            name="Product" 
            component={ProductScreen}
            options={{ title: 'Produit' }}
          />
          <Stack.Screen 
            name="Orders" 
            component={OrdersScreen}
            options={{ title: 'Mes commandes' }}
          />
          <Stack.Screen 
            name="AddProduct" 
            component={AddProductScreen}
            options={{ title: 'Ajouter un produit' }}
          />
          <Stack.Screen 
            name="EditProduct" 
            component={EditProductScreen}
            options={{ title: 'Modifier le produit' }}
          />
        </>
      ) : (
        <Stack.Screen 
          name="Auth" 
          component={AuthNavigator}
          options={{ headerShown: false }}
        />
      )}
    </Stack.Navigator>
  );
};

export default AppNavigator;
