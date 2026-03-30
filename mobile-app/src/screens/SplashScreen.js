import React, { useEffect } from 'react';
import { View, Text, StyleSheet } from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';
import SplashScreen from 'react-native-splash-screen';

const SplashScreen = () => {
  useEffect(() => {
    // Cacher le splash screen natif après 2 secondes
    const timer = setTimeout(() => {
      SplashScreen.hide();
    }, 2000);

    return () => clearTimeout(timer);
  }, []);

  return (
    <LinearGradient
      colors={['#FF6B6B', '#4ECDC4']}
      style={styles.container}
    >
      <View style={styles.content}>
        <Icon name="store" size={80} color="#FFFFFF" style={styles.icon} />
        <Text style={styles.title}>Creator Market</Text>
        <Text style={styles.subtitle}>Votre boutique en ligne</Text>
      </View>
    </LinearGradient>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  content: {
    alignItems: 'center',
  },
  icon: {
    marginBottom: 20,
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 10,
  },
  subtitle: {
    fontSize: 16,
    color: '#FFFFFF',
    opacity: 0.8,
  },
});

export default SplashScreen;
