import React, { createContext, useContext, useState, useEffect } from 'react';
import { AsyncStorage } from 'react-native';

// Thèmes
const themes = {
  light: {
    primary: '#FF6B6B',
    secondary: '#4ECDC4',
    background: '#FFFFFF',
    surface: '#F8F9FA',
    text: '#2C3E50',
    textSecondary: '#7F8C8D',
    border: '#E1E8ED',
    error: '#E74C3C',
    success: '#27AE60',
    warning: '#F39C12',
    shadow: 'rgba(0, 0, 0, 0.1)',
  },
  dark: {
    primary: '#FF6B6B',
    secondary: '#4ECDC4',
    background: '#1A1A1A',
    surface: '#2D2D2D',
    text: '#FFFFFF',
    textSecondary: '#B0B0B0',
    border: '#404040',
    error: '#E74C3C',
    success: '#27AE60',
    warning: '#F39C12',
    shadow: 'rgba(0, 0, 0, 0.3)',
  },
};

// Context
const ThemeContext = createContext();

// Provider
export const ThemeProvider = ({ children }) => {
  const [themeName, setThemeName] = useState('light');
  const [theme, setTheme] = useState(themes.light);

  // Charger le thème sauvegardé
  useEffect(() => {
    const loadTheme = async () => {
      try {
        const savedTheme = await AsyncStorage.getItem('theme');
        if (savedTheme && themes[savedTheme]) {
          setThemeName(savedTheme);
          setTheme(themes[savedTheme]);
        }
      } catch (error) {
        console.warn('Erreur chargement thème:', error);
      }
    };

    loadTheme();
  }, []);

  // Changer de thème
  const toggleTheme = async () => {
    const newThemeName = themeName === 'light' ? 'dark' : 'light';
    const newTheme = themes[newThemeName];

    try {
      await AsyncStorage.setItem('theme', newThemeName);
      setThemeName(newThemeName);
      setTheme(newTheme);
    } catch (error) {
      console.warn('Erreur sauvegarde thème:', error);
    }
  };

  const setThemeByName = async (name) => {
    if (themes[name]) {
      try {
        await AsyncStorage.setItem('theme', name);
        setThemeName(name);
        setTheme(themes[name]);
      } catch (error) {
        console.warn('Erreur changement thème:', error);
      }
    }
  };

  const value = {
    theme,
    themeName,
    toggleTheme,
    setThemeByName,
    isDark: themeName === 'dark',
  };

  return (
    <ThemeContext.Provider value={value}>
      {children}
    </ThemeContext.Provider>
  );
};

// Hook
export const useTheme = () => {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme doit être utilisé dans un ThemeProvider');
  }
  return context;
};
