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

const HomeScreen = ({ navigation }) => {
  const [boutiques, setBoutiques] = useState([]);
  const [isLoading, setIsLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  
  const { theme } = useTheme();

  useEffect(() => {
    loadBoutiques();
  }, []);

  const loadBoutiques = async () => {
    try {
      const data = await boutiqueService.getPopularBoutiques();
      setBoutiques(data.produits || []);
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
      onPress={() => {
        // Naviguer vers les produits de cette boutique
        navigation.navigate('Boutiques', { boutiqueId: item.boutique_id });
      }}
    >
      <Image
        source={item.image ? { uri: item.image } : require('../assets/placeholder.png')}
        style={styles.boutiqueImage}
      />
      <View style={styles.boutiqueInfo}>
        <Text style={[styles.boutiqueName, { color: theme.text }]}>
          {item.nom}
        </Text>
        <Text style={[styles.boutiqueLocation, { color: theme.textSecondary }]}>
          <Icon name="location-on" size={14} /> {item.localisation}
        </Text>
        <View style={styles.priceContainer}>
          <Text style={[styles.price, { color: theme.primary }]}>
            {item.prix} FCFA
          </Text>
        </View>
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={[styles.whatsappButton, { backgroundColor: '#25D366' }]}
            onPress={() => {
              // Ouvrir WhatsApp
            }}
          >
            <Icon name="whatsapp" size={16} color="#FFFFFF" />
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

  const renderHeader = () => (
    <View style={styles.header}>
      <Text style={[styles.title, { color: theme.text }]}>
        Découvrez les boutiques
      </Text>
      <Text style={[styles.subtitle, { color: theme.textSecondary }]}>
        Les meilleurs produits près de chez vous
      </Text>
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
  list: {
    padding: 20,
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
  },
  boutiqueImage: {
    width: '100%',
    height: 200,
    resizeMode: 'cover',
  },
  boutiqueInfo: {
    padding: 15,
  },
  boutiqueName: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  boutiqueLocation: {
    fontSize: 14,
    marginBottom: 10,
  },
  priceContainer: {
    marginBottom: 15,
  },
  price: {
    fontSize: 20,
    fontWeight: 'bold',
  },
  actionButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  whatsappButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderRadius: 20,
    flex: 1,
    marginRight: 10,
    justifyContent: 'center',
  },
  favoriteButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default HomeScreen;
