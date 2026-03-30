import { createSlice } from '@reduxjs/toolkit';

const initialState = {
  boutiques: [],
  currentBoutique: null,
  products: [],
  stats: null,
  orders: [],
  favorites: [],
  isLoading: false,
  error: null,
};

const boutiqueSlice = createSlice({
  name: 'boutique',
  initialState,
  reducers: {
    setLoading: (state, action) => {
      state.isLoading = action.payload;
    },
    setError: (state, action) => {
      state.error = action.payload;
      state.isLoading = false;
    },
    setBoutiques: (state, action) => {
      state.boutiques = action.payload;
      state.isLoading = false;
      state.error = null;
    },
    setCurrentBoutique: (state, action) => {
      state.currentBoutique = action.payload;
    },
    setProducts: (state, action) => {
      state.products = action.payload;
    },
    addProduct: (state, action) => {
      state.products.unshift(action.payload);
    },
    updateProduct: (state, action) => {
      const index = state.products.findIndex(p => p.id === action.payload.id);
      if (index !== -1) {
        state.products[index] = action.payload;
      }
    },
    removeProduct: (state, action) => {
      state.products = state.products.filter(p => p.id !== action.payload);
    },
    setStats: (state, action) => {
      state.stats = action.payload;
    },
    setOrders: (state, action) => {
      state.orders = action.payload;
    },
    addOrder: (state, action) => {
      state.orders.unshift(action.payload);
    },
    updateOrder: (state, action) => {
      const index = state.orders.findIndex(o => o.id === action.payload.id);
      if (index !== -1) {
        state.orders[index] = action.payload;
      }
    },
    setFavorites: (state, action) => {
      state.favorites = action.payload;
    },
    toggleFavorite: (state, action) => {
      const productId = action.payload;
      const index = state.favorites.findIndex(f => f.id === productId);
      if (index !== -1) {
        state.favorites.splice(index, 1);
      } else {
        state.favorites.push({ id: productId });
      }
    },
    clearError: (state) => {
      state.error = null;
    },
  },
});

export const {
  setLoading,
  setError,
  setBoutiques,
  setCurrentBoutique,
  setProducts,
  addProduct,
  updateProduct,
  removeProduct,
  setStats,
  setOrders,
  addOrder,
  updateOrder,
  setFavorites,
  toggleFavorite,
  clearError,
} = boutiqueSlice.actions;

export default boutiqueSlice.reducer;
