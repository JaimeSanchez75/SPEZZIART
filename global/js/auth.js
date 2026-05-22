"use strict";
let accessToken = null;
let refreshPromise = null;
let failedQueue = [];
const processQueue = (error, token = null) =>  // Procesa la cola de peticiones que fallaron por token expirado. Si se obtuvo un nuevo token, se resuelven las promesas con el nuevo token. Si hubo un error al refrescar, se rechazan las promesas con el error.
{
    failedQueue.forEach(prom => 
    {
        if (error) {prom.reject(error);} 
        else {prom.resolve(token);}
    });
    failedQueue = [];
};
export async function authFetch(url, options = {})  // Función wrapper para fetch que maneja la autenticación con tokens. Si el token ha expirado, intenta refrescarlo automáticamente y reintentar la petición original. Si el refresco falla, redirige a login.
{
    if (!accessToken) {await refreshAccessToken();}
    const headers = 
    {
        'Content-Type': 'application/json',
        ...options.headers
    };
    if (accessToken) {headers['Authorization'] = `Bearer ${accessToken}`;}
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {headers['X-CSRF-Token'] = csrfMeta.getAttribute('content');}
    try 
    {
        let response = await fetch(url, { ...options, headers });
        if (response.status === 401) 
        {
            const clone = response.clone();
            try 
            {
                const data = await clone.json();
                if (data.code === 'token_expired') 
                {
                    const newToken = await refreshAccessToken();
                    headers['Authorization'] = `Bearer ${newToken}`;
                    response = await fetch(url, { ...options, headers });
                } 
                else 
                {
                    await logout();
                    window.location.href = '/pages/login';
                    throw new Error('Sesión inválida');
                }
            } 
            catch (e) 
            {
                await logout();
                window.location.href = '/pages/login';
                throw e;
            }
        }
        return response;
    } 
    catch (error) 
    {
        console.error('Fetch error:', error);
        throw error;
    }
}
export async function refreshAccessToken()  // Refresca el token de acceso si ha expirado.
{
    if (refreshPromise) {return refreshPromise;}
    refreshPromise = new Promise(async (resolve, reject) => 
    {
        try 
        {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch
            (   '/auth/refresh', 
            {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-CSRF-Token': csrfToken }
            });
            if (!response.ok) {throw new Error('Refresh failed');}
            const data = await response.json();
            accessToken = data.access_token;
            resolve(accessToken);
            processQueue(null, accessToken);
        } 
        catch (error) 
        {
            accessToken = null;
            processQueue(error, null);
            reject(error);
        } 
        finally {refreshPromise = null;}
    });
    return refreshPromise;
}
export async function loginWithFormData(formData)  // Inicia sesión con los datos del formulario.
{
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {formData.append('csrf_token', csrfToken);}
    const response = await fetch('/auth/login', 
    {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await response.json();
    if (data.success) 
    {
        accessToken = data.access_token;
        sessionStorage.setItem('user', JSON.stringify(data.user));
        return data;
    } 
    else {throw new Error(data.error || 'Error en login');}
}
export async function register(userData, formData) // Registra un nuevo usuario con los datos del formulario.
{
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {formData.append('csrf_token', csrfToken);}
    const response = await fetch('/auth/register', 
    {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await response.json();
    if (data.success) 
    {
        accessToken = data.access_token;
        sessionStorage.setItem('user', JSON.stringify(data.user));
        return data;
    } 
    else {throw new Error(data.error || 'Error en registro');}
}
export async function logout() // Cierra la sesión del usuario.
{
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const response = await fetch('/auth/logout', 
    {
        method: 'POST',
        credentials: 'include',
        headers: { 'X-CSRF-Token': csrfToken }
    });
    const data = await response.json();
    accessToken = null;
    sessionStorage.removeItem('user');
    return data; // { success: true, redirect: 'login' }
}
export function isAuthenticated() {return !!accessToken;} // Devuelve true si el usuario está autenticado (tiene un token de acceso válido en memoria).
export function getStoredUser()  // Devuelve el objeto del usuario almacenado en sessionStorage, o null si no hay ninguno. Este objeto se guarda al iniciar sesión o registrarse exitosamente, y se elimina al cerrar sesión. No es una fuente de verdad para la autenticación, solo un almacenamiento temporal de los datos del usuario.
{
    const userStr = sessionStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
}
export function setAccessToken(token) {accessToken = token;} // Función para establecer manualmente el token de acceso, útil para pruebas o casos especiales.
export async function initAuth() // Función de inicialización que se puede llamar al cargar la página para intentar refrescar el token automáticamente si es necesario. Devuelve true si el usuario queda autenticado después de la inicialización, o false si no se pudo autenticar.
{
    try 
    {
        await refreshAccessToken();
        return true;
    } 
    catch {return false;}
}