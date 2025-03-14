import React from 'react';
import { Box } from '@mui/material';

const Logo = () => (
  <Box
    id="sw-logo"
    sx={{
      display: 'block',
      maxHeight: '100px', // Increased maximum height
      width: 'auto',
      margin: '0 auto',
    }}
  >
    <img 
      src="/images/logo/sports-warehouse-logo.svg" 
      alt="Sports Warehouse Logo" 
      style={{ maxHeight: '100px', width: 'auto' }} 
    />
  </Box>
);

export default Logo;
