import axios from 'axios';

// 后端 API 地址（容器/本地开发均为 8000 端口）
const API_BASE = import.meta.env.VITE_API_BASE || 'http://localhost:8000/api';

export const api = axios.create({ baseURL: API_BASE });

// 图片代理：部分来源站有防盗链，需要后端代理
export function proxiedImage(url) {
  if (!url) return null;
  if (url.includes('playwoool.com')) {
    return `${API_BASE}/proxy-image?url=${encodeURIComponent(url)}`;
  }
  return url;
}

export default api;
