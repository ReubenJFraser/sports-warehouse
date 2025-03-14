import React from 'react';
import { AppBar, Toolbar, Box, IconButton } from '@mui/material';
import MenuIcon from '@mui/icons-material/Menu';
import { usePaneContext } from '../contexts/PaneContext';
import Logo from './Logo';

const Header = () => {
  const { setDrawerOpen } = usePaneContext();

  const handleMenuClick = () => {
    setDrawerOpen(true);
  };

  return (
    <AppBar
      position="static"
      // For light mode, use a pure white header (surface-bright) to contrast with the off-white body (background)
      sx={{
        backgroundColor: (theme) => theme.palette.custom.surfaceBright,
        height: '128px',
        justifyContent: 'center',
      }}
    >
      <Toolbar disableGutters sx={{ height: '128px', px: 2 }}>
        <IconButton
          edge="start"
          color="inherit"
          aria-label="menu"
          onClick={handleMenuClick}
          sx={{ mr: 2, display: { md: 'none' } }}
        >
          <MenuIcon />
        </IconButton>
        <Box
          sx={{
            flexGrow: 1,
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
          }}
        >
          <Logo />
        </Box>
      </Toolbar>
    </AppBar>
  );
};

export default Header;


