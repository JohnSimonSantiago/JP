// src/services/UserService.js
import axios from 'axios';
import { useUserStore } from '../store/UserStore'; // Import your Pinia store

const userStore = useUserStore();

export async function fetchUserData() {
  try {
    const response = await axios.get('/api/user');
    const userId = response.data.id;
    userStore.setUserId(userId);
  } catch (error) {
    console.error('Error fetching user data:', error);
  }
}
