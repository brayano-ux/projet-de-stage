import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
  ScrollView,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';

import { useAuth } from '../../contexts/AuthContext';
import { useTheme } from '../../contexts/ThemeContext';

const RegisterScreen = ({ navigation }) => {
  const [formData, setFormData] = useState({
    nom: '',
    email: '',
    password: '',
    confirmPassword: '',
    telephone: '',
  });
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const { register, error, clearError } = useAuth();
  const { theme } = useTheme();

  React.useEffect(() => {
    if (error) {
      Alert.alert('Erreur', error);
      clearError();
    }
  }, [error, clearError]);

  const handleInputChange = (field, value) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleRegister = async () => {
    const { nom, email, password, confirmPassword, telephone } = formData;

    // Validation
    if (!nom || !email || !password || !confirmPassword || !telephone) {
      Alert.alert('Erreur', 'Veuillez remplir tous les champs');
      return;
    }

    if (password !== confirmPassword) {
      Alert.alert('Erreur', 'Les mots de passe ne correspondent pas');
      return;
    }

    if (password.length < 6) {
      Alert.alert('Erreur', 'Le mot de passe doit contenir au moins 6 caractères');
      return;
    }

    setIsLoading(true);
    try {
      await register({
        nom,
        email,
        password,
        telephone,
      });
      Alert.alert('Succès', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
      navigation.navigate('Login');
    } catch (error) {
      // L'erreur est gérée dans le contexte
    } finally {
      setIsLoading(false);
    }
  };

  const InputField = ({ icon, placeholder, value, onChangeText, secureTextEntry, showToggle, onToggle }) => (
    <View style={[styles.inputGroup, { borderColor: theme.border }]}>
      <Icon name={icon} size={20} color={theme.textSecondary} style={styles.inputIcon} />
      <TextInput
        style={[styles.input, { color: theme.text }]}
        placeholder={placeholder}
        placeholderTextColor={theme.textSecondary}
        value={value}
        onChangeText={onChangeText}
        secureTextEntry={secureTextEntry && !showToggle}
      />
      {showToggle && (
        <TouchableOpacity style={styles.eyeIcon} onPress={onToggle}>
          <Icon
            name={showToggle ? "visibility" : "visibility-off"}
            size={20}
            color={theme.textSecondary}
          />
        </TouchableOpacity>
      )}
    </View>
  );

  return (
    <ScrollView style={[styles.container, { backgroundColor: theme.background }]}>
      <LinearGradient
        colors={[theme.primary, theme.secondary]}
        style={styles.header}
      >
        <Icon name="store" size={60} color="#FFFFFF" />
        <Text style={styles.appName}>Creator Market</Text>
        <Text style={styles.tagline}>Créez votre boutique</Text>
      </LinearGradient>

      <View style={[styles.form, { backgroundColor: theme.surface }]}>
        <InputField
          icon="person"
          placeholder="Nom complet"
          value={formData.nom}
          onChangeText={(value) => handleInputChange('nom', value)}
        />

        <InputField
          icon="email"
          placeholder="Email"
          value={formData.email}
          onChangeText={(value) => handleInputChange('email', value)}
          keyboardType="email-address"
          autoCapitalize="none"
        />

        <InputField
          icon="phone"
          placeholder="Téléphone"
          value={formData.telephone}
          onChangeText={(value) => handleInputChange('telephone', value)}
          keyboardType="phone-pad"
        />

        <InputField
          icon="lock"
          placeholder="Mot de passe"
          value={formData.password}
          onChangeText={(value) => handleInputChange('password', value)}
          secureTextEntry={true}
          showToggle={showPassword}
          onToggle={() => setShowPassword(!showPassword)}
        />

        <InputField
          icon="lock"
          placeholder="Confirmer le mot de passe"
          value={formData.confirmPassword}
          onChangeText={(value) => handleInputChange('confirmPassword', value)}
          secureTextEntry={true}
          showToggle={showConfirmPassword}
          onToggle={() => setShowConfirmPassword(!showConfirmPassword)}
        />

        <TouchableOpacity
          style={[styles.registerButton, { backgroundColor: theme.primary }]}
          onPress={handleRegister}
          disabled={isLoading}
        >
          {isLoading ? (
            <ActivityIndicator color="#FFFFFF" size="small" />
          ) : (
            <Text style={styles.registerButtonText}>S'inscrire</Text>
          )}
        </TouchableOpacity>

        <View style={styles.loginLink}>
          <Text style={[styles.loginText, { color: theme.text }]}>
            Déjà un compte ?{' '}
          </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Login')}>
            <Text style={[styles.loginLinkText, { color: theme.primary }]}>
              Se connecter
            </Text>
          </TouchableOpacity>
        </View>
      </View>
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  header: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 30,
    paddingVertical: 60,
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
    paddingBottom: 30,
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
  registerButton: {
    height: 50,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 30,
    marginTop: 10,
  },
  registerButtonText: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: 'bold',
  },
  loginLink: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loginText: {
    fontSize: 14,
  },
  loginLinkText: {
    fontSize: 14,
    fontWeight: 'bold',
  },
});

export default RegisterScreen;
