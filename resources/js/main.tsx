import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import '../css/main.css';

// React 18 の書き方
const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
