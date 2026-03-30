import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
  ActivityIndicator,
  Image,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';

import { useTheme } from '../contexts/ThemeContext';
import { boutiqueService } from '../services/boutiqueService';

const BoutiqueScreen = ({ navigation, route }) => {
  const [boutiques, setBoutiques] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedCategory, setSelectedCategory] = useState('all');
  
  const { theme } = useTheme();
  const { boutiqueId } = route.params || {};

  const categories = [
    { id: 'all', name: 'Toutes', icon: 'apps' },
    { id: 'beauty', name: 'Beauté', icon: 'face' },
    { id: 'fashion', name: 'Mode', icon: 'checkroom' },
    { id: 'tech', name: 'Tech', icon: 'devices' },
    { id: 'food', name: 'Nourriture', icon: 'restaurant' },
  ];

  useEffect(() => {
    loadBoutiques();
  }, [boutiqueId]);

  const loadBoutiques = async () => {
    try {
      let data;
      if (boutiqueId) {
        data = await boutiqueService.getBoutiqueProducts(boutiqueId);
        setBoutiques(data || []);
      } else {
        data = await boutiqueService.getPopularBoutiques();
        setBoutiques(data.produits || []);
      }
    } catch (error) {
      console.error('Erreur chargement boutiques:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    await loadBoutiques();
    setRefreshing(false);
  };

  const renderBoutiqueItem = ({ item }) => (
    <TouchableOpacity
      style={[styles.boutiqueCard, { backgroundColor: theme.surface }]}
      onPress={() => navigation.navigate('Product', { productId: item.id })}
    >
      <Image
        source={item.image ? { uri: item.image } : require('../assets/placeholder.png')}
        style={styles.boutiqueImage}
      />
      <View style={styles.boutiqueInfo}>
        <Text style={[styles.boutiqueName, { color: theme.text }]}>
          {item.nom}
        </Text>
        <Text style={[styles.boutiqueLocation, { color: theme.textSecondary }]} numberOfLines={2}>
          {item.description || 'Pas de description'}
        </Text>
        <View style={styles.priceLocation}>
          <View style={styles.priceContainer}>
            <Text style={[styles.price, { color: theme.primary }]}>
              {item.prix} FCFA
            </Text>
          </View>
          <View style={styles.locationContainer}>
            <Icon name="location-on" size={14} color={theme.textSecondary} />
            <Text style={[styles.locationText, { color: theme.textSecondary }]}>
              {item.localisation}
            </Text>
          </View>
        </View>
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={[styles.whatsappButton, { backgroundColor: '#25D366' }]}
            onPress={() => {
              // Ouvrir WhatsApp
              const message = `Bonjour, je suis intéressé(e) par votre produit : ${item.nom}`;
              const url = `https://wa.me/${item.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(message)}`;
              // Ouvrir le lien WhatsApp
            }}
          >
            <Icon name="whatsapp" size={16} color="#FFFFFF" />
            <Text style={styles.whatsappText}>Commander</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.favoriteButton, { backgroundColor: theme.border }]}
          >
            <Icon name="favorite-border" size={16} color={theme.textSecondary} />
          </TouchableOpacity>
        </View>
      </View>
    </TouchableOpacity>
  );

  const renderCategoryItem = ({ item }) => (
    <TouchableOpacity
      style={[
        styles.categoryItem,
        { 
          backgroundColor: selectedCategory === item.id ? theme.primary : theme.surface,
          borderColor: theme.border
        }
      ]}
      onPress={() => setSelectedCategory(item.id)}
    >
      <Icon 
        name={item.icon} 
        size={20} 
        color={selectedCategory === item.id ? '#FFFFFF' : theme.textSecondary} 
      />
      <Text style={[
        styles.categoryText,
        { 
          color: selectedCategory === item.id ? '#FFFFFF' : theme.text,
          fontWeight: selectedCategory === item.id ? 'bold' : 'normal'
        }
      ]}>
        {item.name}
      </Text>
    </TouchableOpacity>
  );

  const renderHeader = () => (
    <View>
      <View style={styles.header}>
        <Text style={[styles.title, { color: theme.text }]}>
          {boutiqueId ? 'Produits de la boutique' : 'Toutes les boutiques'}
        </Text>
        <Text style={[styles.subtitle, { color: theme.textSecondary }]}>
          {boutiqueId ? 'Découvrez nos produits' : 'Explorez les meilleures boutiques'}
        </Text>
      </View>
      
      {!boutiqueId && (
        <View style={styles.categoriesContainer}>
          <FlatList
            data={categories}
            renderItem={renderCategoryItem}
            keyExtractor={item => item.id}
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.categoriesList}
          />
        </View>
      )}
    </View>
  );

  if (isLoading) {
    return (
      <View style={[styles.container, { backgroundColor: theme.background }]}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={theme.primary} />
          <Text style={[styles.loadingText, { color: theme.textSecondary }]}>
            Chargement...
          </Text>
        </View>
      </View>
    );
  }

  return (
    <View style={[styles.container, { backgroundColor: theme.background }]}>
      <FlatList
        data={boutiques}
        renderItem={renderBoutiqueItem}
        keyExtractor={(item) => item.id.toString()}
        ListHeaderComponent={renderHeader}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            colors={[theme.primary]}
          />
        }
        showsVerticalScrollIndicator={false}
        numColumns={2}
        columnWrapperStyle={styles.row}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    fontSize: 16,
  },
  header: {
    padding: 20,
    paddingBottom: 10,
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 14,
  },
  categoriesContainer: {
    marginBottom: 10,
  },
  categoriesList: {
    paddingHorizontal: 20,
  },
  categoryItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 20,
    marginRight: 10,
    borderWidth: 1,
  },
  categoryText: {
    fontSize: 12,
    marginLeft: 5,
  },
  list: {
    padding: 20,
  },
  row: {
    justifyContent: 'space-between',
  },
  boutiqueCard: {
    borderRadius: 15,
    marginBottom: 20,
    overflow: 'hidden',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    width: '48%',
  },
  boutiqueImage: {
    width: '100%',
    height: 150,
    resizeMode: 'cover',
  },
  boutiqueInfo: {
    padding: 12,
  },
  boutiqueName: {
    fontSize: 14,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  boutiqueLocation: {
    fontSize: 12,
    marginBottom: 8,
    height: 32,
  },
  priceLocation: {
    marginBottom: 10,
  },
  priceContainer: {
    marginBottom: 5,
  },
  price: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  locationContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  locationText: {
    fontSize: 11,
    marginLeft: 2,
  },
  actionButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  whatsappButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 15,
    flex: 1,
    marginRight: 8,
    justifyContent: 'center',
  },
  whatsappText: {
    color: '#FFFFFF',
    fontSize: 11,
    fontWeight: 'bold',
    marginLeft: 4,
  },
  favoriteButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default BoutiqueScreen;
