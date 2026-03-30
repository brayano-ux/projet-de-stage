import { configureStore } from '@reduxjs/toolkit';
import authSlice from './slices/authSlice';
import boutiqueSlice from './slices/boutiqueSlice';

export const store = configureStore({
  reducer: {
    auth: authSlice,
    boutique: boutiqueSlice,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({
      serializableCheck: {
        ignoredActions: ['persist/PERSIST'],
      },
    }),
});
