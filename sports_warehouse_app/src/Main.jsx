// React entry point, sets up Router, Theme, Context

// src/main.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { ThemeProvider } from '@mui/material/styles';
import theme from './theme';
import { PaneProvider } from './contexts/PaneContext';
import App from './App';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <BrowserRouter>
      <ThemeProvider theme={theme}>
        <PaneProvider>
          <Routes>
            {/* Define routes - currently only one route for the home page */}
            <Route path="/" element={<App />} />
          </Routes>
        </PaneProvider>
      </ThemeProvider>
    </BrowserRouter>
  </React.StrictMode>
);
