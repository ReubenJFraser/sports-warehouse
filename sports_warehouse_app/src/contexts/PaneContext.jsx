// React context for pane state (open/close, content type)
import React, { createContext, useContext, useState } from 'react';

const PaneContext = createContext();

export const PaneProvider = ({ children }) => {
  // State for which content is shown in the left pane and drawer visibility
  const [paneContent, setPaneContent] = useState('contact');  // default to contact form
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [drawerOpen, setDrawerOpen] = useState(false);

  const value = {
    paneContent, setPaneContent,
    selectedProduct, setSelectedProduct,
    drawerOpen, setDrawerOpen
  };

  return (
    <PaneContext.Provider value={value}>
      {children}
    </PaneContext.Provider>
  );
};

// Custom hook to use the context
export const usePaneContext = () => useContext(PaneContext);
