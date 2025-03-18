// React context for pane state (open/close, content type)
import React, { createContext, useContext, useState } from 'react';

const PaneContext = createContext();

export const PaneProvider = ({ children }) => {
  // Set default paneContent to an empty string so the form is hidden on initial load.
  const [paneContent, setPaneContent] = useState('');
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [drawerOpen, setDrawerOpen] = useState(false);

  const value = {
    paneContent, 
    setPaneContent,
    selectedProduct, 
    setSelectedProduct,
    drawerOpen, 
    setDrawerOpen,
  };

  return (
    <PaneContext.Provider value={value}>
      {children}
    </PaneContext.Provider>
  );
};

export const usePaneContext = () => useContext(PaneContext);

