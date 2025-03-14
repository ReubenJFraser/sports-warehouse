import React from 'react';
import { Box, Button } from '@mui/material';
import { usePaneContext } from '../contexts/PaneContext';
import ContactForm from './ContactForm';  // Contact form component for the Drawer
import ProductDetails from './ProductDetails';  // Product details component (if applicable)

const SidebarContent = () => {
  const { paneContent, setPaneContent, setDrawerOpen } = usePaneContext();

  // Close the Drawer without doing anything (continue watching video)
  const handleCloseDrawer = () => {
    setDrawerOpen(false); // Close the drawer
  };

  // Open the contact form inside the Drawer
  const handleOpenForm = () => {
    setPaneContent('contact');  // Set content to 'contact' (show form)
    setDrawerOpen(true);  // Open the drawer to show the contact form
  };

  // Render the content inside the Drawer
  if (paneContent === 'contact') {
    return <ContactForm />;  // Show contact form if paneContent is 'contact'
  } else if (paneContent === 'details') {
    return <ProductDetails />; // Show product details if paneContent is 'details'
  } else {
    // If no content is selected, show the message with the option to either watch the video or fill out the form
    return (
      <Box sx={{ p: 2 }}>
        <p>You can either fill out a contact form or continue watching the video.</p>
        <Button onClick={handleCloseDrawer} color="primary" sx={{ mr: 2 }}>
          Continue Watching
        </Button>
        <Button onClick={handleOpenForm} color="primary">
          Fill Out Form
        </Button>
      </Box>
    );
  }
};

export default SidebarContent;

