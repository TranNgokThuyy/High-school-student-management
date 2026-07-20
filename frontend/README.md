# Frontend React

Frontend gọi Laravel qua `/api` và dùng session cookie + CSRF, không lưu access token trong localStorage.

```bash
npm install
npm run dev
```

Chạy backend ở `http://127.0.0.1:8000`; Vite sẽ proxy `/api` sang backend.
