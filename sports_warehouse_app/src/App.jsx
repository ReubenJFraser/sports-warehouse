import React, { useState, useMemo } from 'react';
import { Box, Drawer, CssBaseline, useMediaQuery } from '@mui/material';
import { usePaneContext } from './contexts/PaneContext';
import Header from './components/Header';  // Include Header component
import SidebarContent from './components/SidebarContent';  // Sidebar content for the form
import VideoSection from './components/VideoSection'; // Component for video and context
import UnderConstructionImage from './components/UnderConstructionImage';  // Under Construction image component
import FABButton from './components/FABButton';  // FAB button component

// Theme imports for dynamic theming (light/dark mode)
import { ThemeProvider, createTheme } from '@mui/material/styles';
import themeConfig from './theme'; // Theme configuration

const drawerWidth = 240;  // width of the sidebar drawer

function App() {
  const { drawerOpen, setDrawerOpen, setPaneContent } = usePaneContext();
  
  // Use useMediaQuery to check the user's system preference for dark mode
  const prefersDarkMode = useMediaQuery('(prefers-color-scheme: dark)');

  // Dynamically create the theme based on dark mode preference
  const theme = useMemo(() => {
    return createTheme({
      ...themeConfig,
      palette: {
        ...themeConfig.palette,
        mode: prefersDarkMode ? 'dark' : 'light', // Dynamically set mode based on user preference
      },
    });
  }, [prefersDarkMode]);

  // Function to open the form (secondary pane)
  const handleOpenDrawer = (contentType) => {
    setDrawerOpen(true);  // Open the drawer
    setPaneContent(contentType);  // Set content type for Drawer
  };

  // State to manage video playback status
  const [videoEnded, setVideoEnded] = useState(false);  // Track if the video has ended

  // Function to handle when the video ends
  const handleVideoEnd = () => {
    setVideoEnded(true);  // Set videoEnded to true when the video finishes
  };

  return (
    <ThemeProvider theme={theme}> {/* Wrap your app with the ThemeProvider */}
      <Box sx={{ display: 'flex' }}>
        <CssBaseline /> {/* Resets CSS for consistent Material UI styling */}

        {/* Top navigation bar */}
        <Header /> {/* Ensure the Header component is part of the layout */}

        {/* Main content area on the left; reserve space on the right for the form */}
        <Box
          component="main"
          sx={{
            flexGrow: 1,
            p: 2,
            mr: { md: `${drawerWidth}px` } // reserve right margin for the sidebar on desktop
          }}
        >
          <VideoSection onVideoEnd={handleVideoEnd} />
          <UnderConstructionImage videoEnded={videoEnded} />
        </Box>
        
        {/* Permanent Drawer for desktop/tablet, anchored on the right */}
        <Drawer
          variant="permanent"
          anchor="right"
          open
          sx={{
            display: { xs: 'none', md: 'block' },
            width: drawerWidth,
            flexShrink: 0,
            '& .MuiDrawer-paper': {
              width: drawerWidth,
              boxSizing: 'border-box',
            },
          }}
        >
          <SidebarContent />
        </Drawer>
        
        {/* Temporary Drawer for mobile, anchored on the right */}
        <Drawer
          variant="temporary"
          anchor="right"
          open={drawerOpen}
          onClose={() => setDrawerOpen(false)}
          ModalProps={{ keepMounted: true }}
          sx={{
            display: { xs: 'block', md: 'none' },
            '& .MuiDrawer-paper': {
              width: drawerWidth,
              boxSizing: 'border-box',
            },
          }}
        >
          <SidebarContent />
        </Drawer>

        {/* Always visible FAB Button */}
        <FABButton />
      </Box>
    </ThemeProvider>
  );
}

export default App;





