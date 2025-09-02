/* eslint-disable no-unused-vars */
import axios from 'axios';
import { createContext, useState } from 'react';
import * as authHelper from '../_helpers';
// Base API URL for Laravel backend
// Prefer VITE_APP_API_URL if provided; otherwise default to local Laravel API v1
// Example expected value: http://localhost:8000/api/v1
const API_URL = import.meta.env.VITE_APP_API_URL || 'https://mandalikakorprirun.com/api/v1';
export const LOGIN_URL = `${API_URL}/login`;
export const REGISTER_URL = `${API_URL}/register`;
export const FORGOT_PASSWORD_URL = `${API_URL}/forgot-password`;
export const RESET_PASSWORD_URL = `${API_URL}/reset-password`;
// Laravel provides a `me` endpoint in `AuthController@me`
export const GET_USER_URL = `${API_URL}/me`;
const AuthContext = createContext(null);
const AuthProvider = ({
  children
}) => {
  const [loading, setLoading] = useState(true);
  const [auth, setAuth] = useState(authHelper.getAuth());
  const [currentUser, setCurrentUser] = useState();
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
  const saveAuth = auth => {
    setAuth(auth);
    if (auth) {
      authHelper.setAuth(auth);
    } else {
      authHelper.removeAuth();
    }
  };
  const login = async (email, password) => {
    try {
      const { data } = await axios.post(LOGIN_URL, { email, password });
      // Laravel returns: { message, token, user }
      const auth = { access_token: data?.token };
      saveAuth(auth);
      // Prefer user from login response to avoid extra round-trip
      if (data?.user) {
        setCurrentUser(data.user);
      } else {
        const { data: user } = await getUser();
        setCurrentUser(user);
      }
    } catch (error) {
      saveAuth(undefined);
      throw new Error(`Error ${error}`);
    }
  };
  const register = async (payload) => {
    try {
      // Payload should match Laravel: { name, email, password, uid, nik?, no_hp?, device_name? }
      const { data } = await axios.post(REGISTER_URL, payload);
      // Laravel returns: { message, token, data }
      const auth = { access_token: data?.token };
      saveAuth(auth);
      if (data?.data) {
        setCurrentUser(data.data);
      } else {
        const { data: user } = await getUser();
        setCurrentUser(user);
      }
    } catch (error) {
      saveAuth(undefined);
      throw new Error(`Error ${error}`);
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
  const logout = () => {
    saveAuth(undefined);
    setCurrentUser(undefined);
  };
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
    verify
  }}>
    {children}
  </AuthContext.Provider>;
};
export { AuthContext, AuthProvider };