import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  ActivityIndicator,
  TouchableOpacity,
} from 'react-native';
import LinearGradient from 'react-native-linear-gradient';
import Icon from 'react-native-vector-icons/MaterialIcons';

import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';
import { boutiqueService } from '../services/boutiqueService';

const DashboardScreen = ({ navigation }) => {
  const [stats, setStats] = useState(null);
  const [recentOrders, setRecentOrders] = useState([]);
  const [isLoading, setIsLoading] = useState(true);

  const { theme } = useTheme();
  const { user } = useAuth();

  useEffect(() => {
    loadDashboardData();
  }, []);

  const loadDashboardData = async () => {
    try {
      const data = await boutiqueService.getDashboardStats();
      setStats(data.stats || {});
      setRecentOrders(data.commandes || []);
    } catch (error) {
      console.error('Erreur chargement dashboard:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const StatCard = ({ icon, title, value, color, trend }) => (
    <LinearGradient
      colors={[color, color + '88']}
      style={styles.statCard}
    >
      <Icon name={icon} size={24} color="#FFFFFF" style={styles.statIcon} />
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statTitle}>{title}</Text>
      {trend && (
        <View style={styles.trend}>
          <Icon name={trend > 0 ? "trending-up" : "trending-down"} size={12} color="#FFFFFF" />
          <Text style={styles.trendText}>{Math.abs(trend)}%</Text>
        </View>
      )}
    </LinearGradient>
  );

  const OrderItem = ({ order }) => (
    <TouchableOpacity
      style={[styles.orderItem, { backgroundColor: theme.surface }]}
      onPress={() => navigation.navigate('Orders')}
    >
      <View style={styles.orderHeader}>
        <Text style={[styles.orderNumber, { color: theme.text }]}>
          Commande #{order.id}
        </Text>
        <Text style={[
          styles.orderStatus,
          { 
            color: order.statut === 'livre' ? '#27AE60' : 
                   order.statut === 'annule' ? '#E74C3C' : '#F39C12'
          }
        ]}>
          {order.statut}
        </Text>
      </View>
      <Text style={[styles.orderClient, { color: theme.textSecondary }]}>
        {order.nom_client}
      </Text>
      <Text style={[styles.orderProduct, { color: theme.text }]}>
        {order.produit_nom}
      </Text>
      <Text style={[styles.orderAmount, { color: theme.primary }]}>
        {order.montant} FCFA
      </Text>
    </TouchableOpacity>
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
    <ScrollView 
      style={[styles.container, { backgroundColor: theme.background }]}
      showsVerticalScrollIndicator={false}
    >
      <View style={styles.header}>
        <Text style={[styles.welcome, { color: theme.text }]}>
          Bonjour, {user?.nom || 'Vendeur'} 👋
        </Text>
        <Text style={[styles.subtitle, { color: theme.textSecondary }]}>
          Voici l'état de votre boutique
        </Text>
      </View>

      <View style={styles.statsGrid}>
        <StatCard
          icon="visibility"
          title="Vues"
          value={stats?.vues || '0'}
          color="#3498DB"
          trend={12}
        />
        <StatCard
          icon="people"
          title="Visiteurs"
          value={stats?.visiteurs || '0'}
          color="#9B59B6"
          trend={8}
        />
        <StatCard
          icon="shopping-cart"
          title="Commandes"
          value={stats?.commandes || '0'}
          color="#E67E22"
          trend={15}
        />
        <StatCard
          icon="attach-money"
          title="Revenus"
          value={stats?.revenus || '0 FCFA'}
          color="#27AE60"
          trend={23}
        />
      </View>

      <View style={styles.quickActions}>
        <Text style={[styles.sectionTitle, { color: theme.text }]}>
          Actions rapides
        </Text>
        <View style={styles.actionButtons}>
          <TouchableOpacity
            style={[styles.actionButton, { backgroundColor: theme.primary }]}
            onPress={() => navigation.navigate('AddProduct')}
          >
            <Icon name="add" size={24} color="#FFFFFF" />
            <Text style={styles.actionButtonText}>Ajouter</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionButton, { backgroundColor: theme.secondary }]}
            onPress={() => navigation.navigate('Orders')}
          >
            <Icon name="list" size={24} color="#FFFFFF" />
            <Text style={styles.actionButtonText}>Commandes</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={[styles.actionButton, { backgroundColor: '#E74C3C' }]}
            onPress={() => navigation.navigate('Boutiques')}
          >
            <Icon name="store" size={24} color="#FFFFFF" />
            <Text style={styles.actionButtonText}>Ma boutique</Text>
          </TouchableOpacity>
        </View>
      </View>

      <View style={styles.recentOrders}>
        <View style={styles.sectionHeader}>
          <Text style={[styles.sectionTitle, { color: theme.text }]}>
            Commandes récentes
          </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Orders')}>
            <Text style={[styles.seeAll, { color: theme.primary }]}>
              Voir tout
            </Text>
          </TouchableOpacity>
        </View>
        {recentOrders.length > 0 ? (
          recentOrders.slice(0, 5).map(order => (
            <OrderItem key={order.id} order={order} />
          ))
        ) : (
          <View style={[styles.emptyState, { backgroundColor: theme.surface }]}>
            <Icon name="shopping-cart" size={48} color={theme.textSecondary} />
            <Text style={[styles.emptyText, { color: theme.textSecondary }]}>
              Aucune commande pour le moment
            </Text>
          </View>
        )}
      </View>
    </ScrollView>
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
  welcome: {
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  subtitle: {
    fontSize: 14,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 20,
    justifyContent: 'space-between',
  },
  statCard: {
    width: '48%',
    borderRadius: 15,
    padding: 20,
    marginBottom: 15,
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
  },
  statIcon: {
    marginBottom: 10,
  },
  statValue: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 5,
  },
  statTitle: {
    fontSize: 12,
    color: '#FFFFFF',
    opacity: 0.8,
  },
  trend: {
    flexDirection: 'row',
    alignItems: 'center',
    position: 'absolute',
    top: 15,
    right: 15,
  },
  trendText: {
    color: '#FFFFFF',
    fontSize: 10,
    marginLeft: 2,
  },
  quickActions: {
    padding: 20,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 15,
  },
  actionButtons: {
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionButton: {
    flex: 1,
    alignItems: 'center',
    padding: 15,
    borderRadius: 10,
    marginHorizontal: 5,
  },
  actionButtonText: {
    color: '#FFFFFF',
    fontSize: 12,
    marginTop: 5,
  },
  recentOrders: {
    padding: 20,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 15,
  },
  seeAll: {
    fontSize: 14,
    fontWeight: 'bold',
  },
  orderItem: {
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    elevation: 2,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 2,
  },
  orderHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 5,
  },
  orderNumber: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  orderStatus: {
    fontSize: 12,
    fontWeight: 'bold',
  },
  orderClient: {
    fontSize: 14,
    marginBottom: 5,
  },
  orderProduct: {
    fontSize: 14,
    marginBottom: 5,
  },
  orderAmount: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  emptyState: {
    padding: 40,
    borderRadius: 10,
    alignItems: 'center',
  },
  emptyText: {
    marginTop: 10,
    fontSize: 14,
  },
});

export default DashboardScreen;
