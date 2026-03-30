import api from './api';

export const authService = {
  // Connexion utilisateur
  login: async (email, password) => {
    try {
      const response = await api.post('/connexion.php', {
        email,
        password,
      });
      
      if (response.data.success) {
        // Stocker le token et les infos utilisateur
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('user_info', JSON.stringify(response.data.user));
        return response.data;
      }
      throw new Error(response.data.message || 'Erreur de connexion');
    } catch (error) {
      throw error;
    }
  },

  // Inscription utilisateur
  register: async (userData) => {
    try {
      const response = await api.post('/inscription.php', userData);
      
      if (response.data.success) {
        return response.data;
      }
      throw new Error(response.data.message || 'Erreur d\'inscription');
    } catch (error) {
      throw error;
    }
  },

  // Déconnexion
  logout: () => {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_info');
  },

  // Vérifier si l'utilisateur est connecté
  isAuthenticated: () => {
    const token = localStorage.getItem('auth_token');
    return !!token;
  },

  // Obtenir les infos utilisateur
  getUserInfo: () => {
    const userInfo = localStorage.getItem('user_info');
    return userInfo ? JSON.parse(userInfo) : null;
  },

  // Mettre à jour le profil
  updateProfile: async (userData) => {
    try {
      const response = await api.post('/profil.php', userData);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
};
