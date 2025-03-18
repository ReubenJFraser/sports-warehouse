import React, { useMemo, useState } from 'react';
import { Box, CssBaseline, useMediaQuery, Slide } from '@mui/material';
import Grid from '@mui/material/Grid2'; // Using Grid2 with new API
import { usePaneContext } from './contexts/PaneContext';
import Header from './components/Header';
import SidebarContent from './components/SidebarContent';
import VideoSection from './components/VideoSection';
import UnderConstructionImage from './components/UnderConstructionImage';
import FABButton from './components/FABButton';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import themeConfig from './theme';

const App = () => {
  const { paneContent } = usePaneContext();
  const formOpen = paneContent === 'contact';

  const prefersDarkMode = useMediaQuery('(prefers-color-scheme: dark)');
  const theme = useMemo(() => {
    return createTheme({
      ...themeConfig,
      palette: {
        ...themeConfig.palette,
        mode: prefersDarkMode ? 'dark' : 'light',
      },
    });
  }, [prefersDarkMode]);

  const [videoEnded, setVideoEnded] = useState(false);
  const handleVideoEnd = () => {
    setVideoEnded(true);
  };

  // When the form is open, we want minimal top margin (matching the container’s own padding)
  // When closed, we use 128px so that the video container appears centered below the header.
  const mainMarginTop = formOpen ? 2 : '128px'; // theme.spacing(2) equals 16px if default spacing is 8px

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <Header />
      {/* Main content container with conditional top margin */}
      <Box sx={{ p: 2, mt: mainMarginTop }}>
        <Grid container spacing={2}>
          {/* Video Section: full width on xs; 8 columns on md when form is open, 12 when not */}
          <Grid size={{ xs: 12, md: formOpen ? 8 : 12 }}>
            <VideoSection onVideoEnd={handleVideoEnd} />
            <UnderConstructionImage videoEnded={videoEnded} />
          </Grid>
          {/* Form container: slides in when paneContent equals "contact" */}
          {formOpen && (
            <Grid size={{ xs: 12, md: 4 }}>
              <Slide in={formOpen} direction="left" mountOnEnter unmountOnExit>
                <Box
                  sx={{
                    border: '1px solid',
                    borderColor: 'grey.300',
                    p: 2,
                    borderRadius: 1,
                    backgroundColor: theme.palette.background.paper,
                  }}
                >
                  <SidebarContent />
                </Box>
              </Slide>
            </Grid>
          )}
        </Grid>
      </Box>
      <FABButton />
    </ThemeProvider>
  );
};

export default App;


