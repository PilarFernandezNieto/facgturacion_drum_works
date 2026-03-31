import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api', // Ajustar según el host/puerto del backend
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

// Interceptor para añadir el token en cada petición
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token_sesion');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

export default api;
