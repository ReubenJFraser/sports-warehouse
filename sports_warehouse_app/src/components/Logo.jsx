// src/components/Logo.jsx
import React from 'react';
import { Box } from '@mui/material';

const Logo = () => (
  <Box
    id="sw-logo"
    sx={{
      width: '100%',
      height: '100%', // Let the container fill the header's height
      display: 'flex',
      justifyContent: 'center',
      alignItems: 'center',
    }}
  >
    <Box
      component="img"
      src="/images/logo/sports-warehouse-logo.svg"
      alt="Sports Warehouse Logo"
      sx={{
        // On extra-small screens, use full width minus some padding.
        // On medium and up, clamp the width so it never grows beyond 800px.
        width: {
          xs: 'calc(100% - 32px)',
          md: 'clamp(450px, 50vw, 800px)',
        },
        height: 'auto', // Maintain aspect ratio
      }}
    />
  </Box>
);

export default Logo;










