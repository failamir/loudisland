/* eslint-disable no-unused-vars */
import axios from 'axios';
import { createContext, useEffect, useRef, useState } from 'react';
import * as authHelper from '../_helpers';
// Base API URL for Laravel backend
// Prefer VITE_APP_API_URL if provided; otherwise default to local Laravel API v1
// Example expected value: http://localhost:8000/api/v1
const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';
export const LOGIN_URL = `${API_URL}/login`;
export const REGISTER_URL = `${API_URL}/register`;
export const FORGOT_PASSWORD_URL = `${API_URL}/forgot-password`;
export const RESET_PASSWORD_URL = `${API_URL}/reset-password`;
// Laravel provides a `me` endpoint in `AuthController@me`
export const GET_USER_URL = `${API_URL}/me`;
export const REFRESH_URL = `${API_URL}/refresh`;
const AuthContext = createContext(null);
const AuthProvider = ({
  children
}) => {
  const [loading, setLoading] = useState(true);
  const [auth, setAuth] = useState(authHelper.getAuth());
  const [currentUser, setCurrentUser] = useState();
  const refreshTimeoutRef = useRef(null);

  const clearRefreshTimeout = () => {
    if (refreshTimeoutRef.current) {
      clearTimeout(refreshTimeoutRef.current);
      refreshTimeoutRef.current = null;
    }
  };

  const scheduleTokenRefresh = expiresAt => {
    clearRefreshTimeout();
    if (!expiresAt) {
      return;
    }
    const now = Date.now();
    const refreshBeforeMs = 5 * 60 * 1000;
    let delay = expiresAt - now - refreshBeforeMs;
    if (delay <= 0) {
      delay = 0;
    }
    refreshTimeoutRef.current = setTimeout(() => {
      refreshToken();
    }, delay);
  };
  const verify = async () => {
    if (auth) {
      try {
        const {
          data: user
        } = await getUser();
        setCurrentUser(user);
      } catch {
        saveAuth(undefined);
        setCurrentUser(undefined);
      }
    }
  };

  const applyAuthFromTokenResponse = data => {
    const accessToken = data?.access_token || data?.token;
    if (!accessToken) {
      return;
    }
    const now = Date.now();
    const expiresInSeconds = data?.access_token_expires_in || data?.expires_in;
    const expiresAt = expiresInSeconds ? now + expiresInSeconds * 1000 : undefined;
    const nextAuth = expiresAt ? { access_token: accessToken, expires_at: expiresAt } : { access_token: accessToken };
    saveAuth(nextAuth);
    if (expiresAt) {
      scheduleTokenRefresh(expiresAt);
    }
  };
  const saveAuth = auth => {
    setAuth(auth);
    if (auth) {
      authHelper.setAuth(auth);
    } else {
      authHelper.removeAuth();
      clearRefreshTimeout();
    }
  };
  const login = async (email, password) => {
    try {
      const { data } = await axios.post(LOGIN_URL, { email, password });
      // Laravel returns: { message, token, user }
      applyAuthFromTokenResponse(data);
      // Prefer user from login response to avoid extra round-trip
      if (data?.user) {
        setCurrentUser(data.user);
      } else {
        const { data: user } = await getUser();
        setCurrentUser(user);
      }
    } catch (error) {
      saveAuth(undefined);
      // Rethrow original error to allow callers to detect HTTP status codes (e.g., 503 maintenance)
      throw error;
    }
  };
  const register = async (payload) => {
    try {
      // Payload should match Laravel: { name, email, password, uid, nik?, no_hp?, device_name? }
      const { data } = await axios.post(REGISTER_URL, payload);
      // Laravel returns: { message, token, data }
      applyAuthFromTokenResponse(data);
      if (data?.data) {
        setCurrentUser(data.data);
      } else {
        const { data: user } = await getUser();
        setCurrentUser(user);
      }
    } catch (error) {
      saveAuth(undefined);
      // Rethrow original error to allow callers to detect HTTP status codes (e.g., 503 maintenance)
      throw error;
    }
  };
  const requestPasswordResetLink = async email => {
    await axios.post(FORGOT_PASSWORD_URL, {
      email
    });
  };
  const changePassword = async (email, token, password, password_confirmation) => {
    await axios.post(RESET_PASSWORD_URL, {
      email,
      token,
      password,
      password_confirmation
    });
  };
  const getUser = async () => {
    return await axios.get(GET_USER_URL);
  };
  const refreshToken = async () => {
    try {
      const { data } = await axios.post(REFRESH_URL);
      applyAuthFromTokenResponse(data);
    } catch (error) {
      saveAuth(undefined);
      setCurrentUser(undefined);
      throw error;
    }
  };
  const logout = () => {
    saveAuth(undefined);
    setCurrentUser(undefined);
  };
  useEffect(() => {
    if (auth?.expires_at) {
      scheduleTokenRefresh(auth.expires_at);
    }
    return () => {
      clearRefreshTimeout();
    };
  }, []);
  return <AuthContext.Provider value={{
    loading,
    setLoading,
    auth,
    saveAuth,
    currentUser,
    setCurrentUser,
    login,
    register,
    requestPasswordResetLink,
    changePassword,
    getUser,
    logout,
    verify,
    refreshToken
  }}>
    {children}
  </AuthContext.Provider>;
};
export { AuthContext, AuthProvider };