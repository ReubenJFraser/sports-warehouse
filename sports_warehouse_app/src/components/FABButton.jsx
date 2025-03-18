import React from 'react';
import { Button } from '@mui/material';
import { usePaneContext } from '../contexts/PaneContext';
import AddCommentOutlinedIcon from '@mui/icons-material/AddCommentOutlined';
import CancelOutlinedIcon from '@mui/icons-material/CancelOutlined';
import { useMediaQuery } from '@mui/material';

const FABButton = () => {
  const { paneContent, setPaneContent } = usePaneContext();
  const isMobile = useMediaQuery('(max-width:600px)');
  const formOpen = paneContent === 'contact';

  const handleClick = () => {
    // Toggle the contact form: if it's open, close it; if it's closed, open it
    setPaneContent(formOpen ? '' : 'contact');
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
      }}
    >
      {/* On mobile, if the form is open, show the cancel icon; otherwise show the add comment icon */}
      {isMobile && formOpen ? <CancelOutlinedIcon /> : <AddCommentOutlinedIcon />}
    </Button>
  );
};

export default FABButton;

