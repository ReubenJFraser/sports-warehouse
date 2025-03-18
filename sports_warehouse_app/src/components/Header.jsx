// src/components/Header.jsx
import React from 'react';
import { AppBar, Toolbar, Box } from '@mui/material';
import Logo from './Logo';

const Header = () => {
  return (
    <AppBar
      position="static"
      sx={{
        // For viewports up to 899px, use 15vh.
        // For wider screens, add extra height proportionally.
        height: {
          xs: '15vh',
          md: 'calc(15vh + ((100vw - 899px) * 0.05))',
        },
        backgroundColor: (theme) => theme.palette.custom.surfaceBright,
        justifyContent: 'center',
      }}
    >
      <Toolbar
        disableGutters
        sx={{
          height: '100%',
          minHeight: {
            xs: '15vh',
            md: 'calc(15vh + ((100vw - 899px) * 0.05))',
          },
          px: 2,
          py: { xs: 0, sm: 1, md: 2, lg: 3 },
          justifyContent: 'center',
        }}
      >
        <Box
          sx={{
            flexGrow: 1,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            width: '100%',
          }}
        >
          <Logo />
        </Box>
      </Toolbar>
    </AppBar>
  );
};

export default Header;





