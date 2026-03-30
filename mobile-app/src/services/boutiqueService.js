import api from './api';

export const boutiqueService = {
  // Obtenir les boutiques populaires
  getPopularBoutiques: async () => {
    try {
      const response = await api.get('/marcher.php');
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Obtenir les détails d'une boutique
  getBoutiqueDetails: async (boutiqueId) => {
    try {
      const response = await api.get(`/index.php?boutique_id=${boutiqueId}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Obtenir les produits d'une boutique
  getBoutiqueProducts: async (boutiqueId) => {
    try {
      const response = await api.get(`/index.php?boutique_id=${boutiqueId}`);
      return response.data.produits || [];
    } catch (error) {
      throw error;
    }
  },

  // Créer/Mettre à jour une boutique
  updateBoutique: async (boutiqueData) => {
    try {
      const formData = new FormData();
      Object.keys(boutiqueData).forEach(key => {
        if (boutiqueData[key]) {
          formData.append(key, boutiqueData[key]);
        }
      });

      const response = await api.post('/boutique.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Ajouter un produit
  addProduct: async (productData) => {
    try {
      const formData = new FormData();
      Object.keys(productData).forEach(key => {
        if (productData[key]) {
          formData.append(key, productData[key]);
        }
      });

      const response = await api.post('/ajouter_produit.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Modifier un produit
  updateProduct: async (productId, productData) => {
    try {
      const formData = new FormData();
      formData.append('id', productId);
      Object.keys(productData).forEach(key => {
        if (productData[key]) {
          formData.append(key, productData[key]);
        }
      });

      const response = await api.post('/modifier_produit.php', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Supprimer un produit
  deleteProduct: async (productId) => {
    try {
      const response = await api.post('/supprimer_produit', {
        id: productId,
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Obtenir les statistiques du dashboard
  getDashboardStats: async () => {
    try {
      const response = await api.get('/dashboard.php');
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  // Suivre les visiteurs (tracking)
  trackVisitor: async (boutiqueId) => {
    try {
      await api.get(`/tracker_visiteur.php?boutique_id=${boutiqueId}`);
    } catch (error) {
      console.warn('Erreur tracking visiteur:', error);
    }
  },
};
