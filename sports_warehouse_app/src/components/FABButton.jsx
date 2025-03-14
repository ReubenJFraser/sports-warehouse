import React from 'react';
import { Button } from '@mui/material';
import { usePaneContext } from '../contexts/PaneContext';
import AddCommentIcon from '@mui/icons-material/AddComment';

const FABButton = () => {
  const { setDrawerOpen, setPaneContent } = usePaneContext();

  const handleClick = () => {
    setDrawerOpen(true);  // Open the drawer when FAB is clicked
    setPaneContent('contact');  // Set the content to contact form
  };

  return (
    <Button
      variant="contained"
      onClick={handleClick}
      sx={{
        position: 'fixed',
        bottom: 16,
        right: 16,
        width: 56,
        height: 56,
        borderRadius: '50%',
        backgroundColor: '#ff4081', // Pink FAB background
        color: '#fff',
        border: 'none',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: '2rem',
        cursor: 'pointer',
        boxShadow: 3,
        '&:hover': {
          backgroundColor: '#e91e63', // Hover effect
        },
        '&.pulse': {
          animation: 'pulse 1.5s infinite', // Pulse effect for FAB (if needed)
        },
      }}
    >
      <AddCommentIcon />
    </Button>
  );
};

export default FABButton;
