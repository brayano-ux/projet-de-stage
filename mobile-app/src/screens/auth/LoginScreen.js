import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';

import { useAuth } from '../../contexts/AuthContext';
import { useTheme } from '../../contexts/ThemeContext';

const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const { login, error, clearError } = useAuth();
  const { theme } = useTheme();

  React.useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      clearError();
    }
  }, [error, clearError]);

  const handleLogin = async () => {
    if (!email || !password) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    setIsLoading(true);
    try {
      await login(email, password);
    } catch (error) {
      // L'erreur est gérée dans le contexte
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <LinearGradient
        colors={[theme.primary, theme.secondary]}
        style={styles.header}
      >
        <Icon name="store" size={60} color="#FFFFFF" />
        <Text style={styles.appName}>Creator Market</Text>
        <Text style={styles.tagline}>Connectez-vous à votre boutique</Text>
      </LinearGradient>

      <View style={[styles.form, { backgroundColor: theme.surface }]}>
        <View style={styles.inputGroup}>
          <Icon name="email" size={20} color={theme.textSecondary} style={styles.inputIcon} />
          <TextInput
            style={[styles.input, { color: theme.text, borderColor: theme.border }]}
            placeholder="Email"
            placeholderTextColor={theme.textSecondary}
            value={email}
            onChangeText={setEmail}
            keyboardType="email-address"
            autoCapitalize="none"
          />
        </View>

        <View style={styles.inputGroup}>
          <Icon name="lock" size={20} color={theme.textSecondary} style={styles.inputIcon} />
          <TextInput
            style={[styles.input, { color: theme.text, borderColor: theme.border }]}
            placeholder="Mot de passe"
            placeholderTextColor={theme.textSecondary}
            value={password}
            onChangeText={setPassword}
            secureTextEntry={!showPassword}
          />
          <TouchableOpacity
            style={styles.eyeIcon}
            onPress={() => setShowPassword(!showPassword)}
          >
            <Icon
              name={showPassword ? "visibility" : "visibility-off"}
              size={20}
              color={theme.textSecondary}
            />
          </TouchableOpacity>
        </View>

        <TouchableOpacity
          style={[styles.forgotPassword]}
          onPress={() => Alert.alert('Mot de passe oublié', 'Fonctionnalité à venir')}
        >
          <Text style={[styles.forgotPasswordText, { color: theme.primary }]}>
            Mot de passe oublié ?
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.loginButton, { backgroundColor: theme.primary }]}
          onPress={handleLogin}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color="#FFFFFF" size="small" />
          ) : (
            <Text style={styles.loginButtonText}>Se connecter</Text>
          )}
        </TouchableOpacity>

        <View style={styles.registerLink}>
          <Text style={[styles.registerText, { color: theme.text }]}>
            Pas encore de compte ?{' '}
          </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Register')}>
            <Text style={[styles.registerLinkText, { color: theme.primary }]}>
              S'inscrire
            </Text>
          </TouchableOpacity>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 30,
  },
  appName: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginTop: 20,
  },
  tagline: {
    fontSize: 16,
    color: '#FFFFFF',
    opacity: 0.8,
    marginTop: 10,
  },
  form: {
    flex: 1,
    borderTopLeftRadius: 30,
    borderTopRightRadius: 30,
    paddingHorizontal: 30,
    paddingTop: 40,
  },
  inputGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 20,
    borderWidth: 1,
    borderRadius: 10,
    paddingHorizontal: 15,
  },
  inputIcon: {
    marginRight: 10,
  },
  input: {
    flex: 1,
    height: 50,
    fontSize: 16,
  },
  eyeIcon: {
    padding: 5,
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginBottom: 30,
  },
  forgotPasswordText: {
    fontSize: 14,
  },
  loginButton: {
    height: 50,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 30,
  },
  loginButtonText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: 'bold',
  },
  registerLink: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  registerText: {
    fontSize: 14,
  },
  registerLinkText: {
    fontSize: 14,
    fontWeight: 'bold',
  },
});

export default LoginScreen;
